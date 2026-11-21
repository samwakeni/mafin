<?php
require_once dirname(__DIR__) . '/app/bootstrap.php';
$page = $_GET['page'] ?? 'dashboard';
if ($page === 'logout') {
    session_unset();
    session_destroy();
    header('Location: ?page=login');
    exit;
}
if (in_array($page, ['register', 'login'], true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $pdo = db();
    if ($page === 'register') {
        $name  = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pw    = $_POST['password'] ?? '';
        if (! $name || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 10) {$_SESSION['error'] = 'Enter a name, valid email and password of at least 10 characters.';
            header('Location: ?page=register');exit;}
        try { $pdo->beginTransaction();
            $s = $pdo->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)');
            $s->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT)]);
            $uid = (int) $pdo->lastInsertId();
            $s   = $pdo->prepare('INSERT INTO financial_entities(user_id,name,entity_type,description) VALUES(?,?,?,?)');
            $s->execute([$uid, 'Personal Finances', 'personal', 'Personal money management']);
            $eid = (int) $pdo->lastInsertId();
            $s->execute([$uid, 'Oritech Limited', 'business', 'Oritech business finances']);
            $pdo->commit();
            session_regenerate_id(true);
            $_SESSION['uid']       = $uid;
            $_SESSION['name']      = $name;
            $_SESSION['entity_id'] = $eid;
            header('Location: ?page=dashboard');exit;} catch (Throwable $e) {if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

            $_SESSION['error'] = 'Registration failed. Email may already exist; verify database setup.';
            header('Location: ?page=register');exit;}
    } else {
        $s = $pdo->prepare('SELECT id,name,password FROM users WHERE email=?');
        $s->execute([$email = strtolower(trim($_POST['email'] ?? ''))]);
        $u = $s->fetch();
        if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {session_regenerate_id(true);
            $_SESSION['uid']       = (int) $u['id'];
            $_SESSION['name']      = $u['name'];
            $_SESSION['entity_id'] = 0;
            header('Location: ?page=dashboard');exit;}
        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: ?page=login');exit;
    }
}
if (! in_array($page, ['login', 'register'], true)) {
    auth();
}

if ($page === 'switch' && $_SERVER['REQUEST_METHOD'] === 'POST') {verifyCsrf();
    $s = db()->prepare('SELECT id FROM financial_entities WHERE id=? AND user_id=?');
    $s->execute([(int) ($_POST['entity_id'] ?? 0), $_SESSION['uid']]);if ($s->fetch()) {
        $_SESSION['entity_id'] = (int) $_POST['entity_id'];
    }

    header('Location: ?page=dashboard');exit;}
if ($page === 'api') {
    $ent = entity();
    $uid = (int) $_SESSION['uid'];
    $eid = (int) $ent['id'];
    $s   = db()->prepare("SELECT COALESCE(SUM(CASE WHEN t.type='income' AND t.status='completed' THEN t.amount ELSE 0 END),0) income,COALESCE(SUM(CASE WHEN t.type='expense' AND t.status='completed' THEN t.amount ELSE 0 END),0) expenses FROM accounts a LEFT JOIN transactions t ON t.account_id=a.id WHERE a.user_id=? AND a.financial_entity_id=?");
    $s->execute([$uid, $eid]);
    $sum = $s->fetch();
    $s   = db()->prepare("SELECT COALESCE(SUM(a.opening_balance),0)+COALESCE(SUM(x.net),0) balance FROM accounts a LEFT JOIN (SELECT account_id,SUM(CASE WHEN type='income' THEN amount WHEN type='expense' THEN -amount ELSE 0 END) net FROM transactions WHERE status='completed' GROUP BY account_id) x ON x.account_id=a.id WHERE a.user_id=? AND a.financial_entity_id=? AND a.is_active=1");
    $s->execute([$uid, $eid]);
    $balance = $s->fetch()['balance'] ?? 0;
    $s       = db()->prepare("SELECT t.transaction_date,t.description,t.type,t.amount,a.name account_name FROM transactions t JOIN accounts a ON a.id=t.account_id WHERE t.user_id=? AND a.financial_entity_id=? ORDER BY t.transaction_date DESC,t.id DESC LIMIT 10");
    $s->execute([$uid, $eid]);
    jsonOut(['entity' => $ent, 'income' => (float) $sum['income'], 'expenses' => (float) $sum['expenses'], 'balance' => (float) $balance, 'cash_flow' => (float) $sum['income'] - (float) $sum['expenses'], 'recent' => $s->fetchAll()]);
}
if (in_array($page, ['accounts', 'transactions'], true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $ent = entity();
    $uid = (int) $_SESSION['uid'];
    if ($page === 'accounts') {
        $name    = trim($_POST['name'] ?? '');
        $type    = $_POST['account_type'] ?? 'bank';
        $opening = filter_var($_POST['opening_balance'] ?? '0', FILTER_VALIDATE_FLOAT);
        if (! $name || ! in_array($type, ['cash', 'bank', 'savings', 'ewallet', 'other'], true) || $opening === false) {
            jsonOut(['message' => 'Check account details.'], 422);
        }

        $s = db()->prepare('INSERT INTO accounts(user_id,financial_entity_id,name,account_type,opening_balance) VALUES(?,?,?,?,?)');
        $s->execute([$uid, $ent['id'], $name, $type, $opening]);
        jsonOut(['message' => 'Account saved.']);
    }
    $aid    = (int) ($_POST['account_id'] ?? 0);
    $type   = $_POST['type'] ?? '';
    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $desc   = trim($_POST['description'] ?? '');
    $date   = $_POST['transaction_date'] ?? date('Y-m-d');
    if (! $aid || ! in_array($type, ['income', 'expense'], true) || $amount === false || $amount <= 0 || ! $desc || ! preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $date)) {
        jsonOut(['message' => 'Enter valid transaction details.'], 422);
    }

    $s = db()->prepare('SELECT id FROM accounts WHERE id=? AND user_id=? AND financial_entity_id=? AND is_active=1');
    $s->execute([$aid, $uid, $ent['id']]);if (! $s->fetch()) {
        jsonOut(['message' => 'Account not found for this entity.'], 403);
    }

    $s = db()->prepare(
        'INSERT INTO transactions(user_id,account_id,type,amount,transaction_date,description,status) VALUES(?,?,?,?,?,?,\'completed\')');
    $s->execute([$uid, $aid, $type, $amount, $date, $desc]);
    jsonOut(['message' => 'Transaction saved.']);
}

$entities = [];
$accounts = [];
$ent      = null;
if (! in_array($page, ['login', 'register'], true)) {
    $s = db()->prepare('SELECT id,name,entity_type FROM financial_entities WHERE user_id=? AND is_active=1 ORDER BY id');
    $s->execute([$_SESSION['uid']]);
    $entities = $s->fetchAll();
    $ent      = entity();
    $s        = db()->prepare('SELECT * FROM accounts WHERE user_id=? AND financial_entity_id=? AND is_active=1 ORDER BY name');
    $s->execute([$_SESSION['uid'], $ent['id']]);
    $accounts = $s->fetchAll();
}

$error = $_SESSION['error'] ?? '';unset($_SESSION['error']);
function cash($v)
{
    return 'R ' . number_format((float) $v, 2, '.', ',');
}
