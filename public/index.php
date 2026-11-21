<?php
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Finance Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
    :root {
        --navy: #183b4e;
        --blue: #27548a;
        --gold: #dda853
    }

    body {
        background: #f4f6fa;
        color: #1f2937
    }

    .navbar {
        background: var(--navy)
    }

    .card {
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 4px 20px #183b4e0c
    }

    .side a {
        display: block;
        padding: .7rem;
        color: #475569;
        text-decoration: none;
        border-radius: .6rem
    }

    .side a:hover,
    .side a.active {
        background: #e8eef6;
        color: var(--blue)
    }

    .stat {
        font-size: 1.5rem;
        font-weight: 700
    }

    .form-control,
    .form-select {
        padding: .7rem;
        border-radius: .65rem
    }
    </style>
</head>

<body>
    <?php if (in_array($page, ['login', 'register'], true)): ?><div class="container py-5" style="max-width:470px">
        <div class="text-center mb-4"><i class="fa-solid fa-wallet fs-1 text-primary"></i>
            <h1 class="h3 mt-2">Finance Manager</h1>
            <p class="text-secondary">Personal finances + Oritech Limited</p>
        </div><?php if ($error): ?><div class="alert alert-danger"><?php echo e($error) ?></div><?php endif?><div
            class="card p-4">
            <h2 class="h5"><?php echo $page === 'register' ? 'Create owner account' : 'Sign in' ?></h2>
            <form method="post"><input type="hidden" name="csrf_token"
                    value="<?php echo e(csrf()) ?>"><?php if ($page === 'register'): ?><label
                    class="form-label mt-2">Full
                    name</label><input class="form-control mb-3" name="name" required><?php endif?><label
                    class="form-label mt-2">Email</label><input class="form-control mb-3" name="email" type="email"
                    required><label class="form-label">Password
                    <?php echo $page === 'register' ? '(minimum 10 characters)' : '' ?></label><input
                    class="form-control mb-3" name="password" type="password"
                    <?php echo $page === 'register' ? 'minlength="10"' : '' ?> required><button
                    class="btn btn-primary w-100"><?php echo $page === 'register' ? 'Create account' : 'Sign in' ?></button>
            </form>
            <div class="small text-center mt-3"><?php if ($page === 'register'): ?>Already registered? <a
                    href="?page=login">Sign in</a><?php else: ?>First time? <a href="?page=register">Create owner
                    account</a><?php endif?></div>
        </div>
    </div>
    <?php else: ?><nav class="navbar navbar-dark px-3 py-3"><a class="navbar-brand" href="?page=dashboard"><i
                class="fa-solid fa-wallet me-2" style="color:var(--gold)"></i>Finance Manager</a>
        <div class="d-flex gap-2 align-items-center">
            <form method="post" action="?page=switch"><input type="hidden" name="csrf_token"
                    value="<?php echo e(csrf()) ?>"><select class="form-select form-select-sm" name="entity_id"
                    onchange="this.form.submit()"><?php foreach ($entities as $x): ?><option
                        value="<?php echo $x['id'] ?>" <?php echo $x['id'] == $ent['id'] ? 'selected' : '' ?>>
                        <?php echo e($x['name']) ?></option><?php endforeach?></select>
            </form><a class="btn btn-outline-light btn-sm" href="?page=logout">Sign out</a>
        </div>
    </nav>
    <div class="container-fluid py-4 px-lg-4">
        <div class="row g-4">
            <aside class="col-lg-2">
                <div class="card p-3 side"><a class="active" href="?page=dashboard"><i
                            class="fa-solid fa-chart-pie me-2"></i>Dashboard</a><a href="?page=accounts"><i
                            class="fa-solid fa-building-columns me-2"></i>Accounts</a><a href="?page=transactions"><i
                            class="fa-solid fa-arrow-right-arrow-left me-2"></i>Transactions</a><a href="?page=debts"><i
                            class="fa-solid fa-hand-holding-dollar me-2"></i>Debts</a><a href="?page=plans"><i
                            class="fa-solid fa-list-check me-2"></i>Purchase plans</a><a href="?page=budgets"><i
                            class="fa-solid fa-chart-column me-2"></i>Budgets</a>
                    <hr><small class="text-secondary">Signed in as <?php echo e($_SESSION['name']) ?></small>
                </div>
            </aside>
            <main class="col-lg-10">
                <?php if ($page === 'accounts'): ?><h1 class="h3 mb-1">Accounts</h1>
                <p class="text-secondary">Accounts for <?php echo e($ent['name']) ?></p>
                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="card p-4">
                            <h2 class="h5">Add account</h2>
                            <form id="accountForm"><input type="hidden" name="csrf_token"
                                    value="<?php echo e(csrf()) ?>"><label class="form-label">Name</label><input
                                    class="form-control mb-3" name="name" required><label
                                    class="form-label">Type</label><select class="form-select mb-3" name="account_type">
                                    <option value="bank">Bank</option>
                                    <option value="cash">Cash</option>
                                    <option value="savings">Savings</option>
                                    <option value="ewallet">E-wallet</option>
                                    <option value="other">Other</option>
                                </select><label class="form-label">Opening balance (R)</label><input
                                    class="form-control mb-3" type="number" step="0.01" name="opening_balance" value="0"
                                    required>
                                <div id="accountMsg" class="small mb-2"></div><button class="btn btn-primary">Save
                                    account</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card p-4">
                            <h2 class="h5">Active accounts</h2><?php if (! $accounts): ?><p class="text-secondary">No
                                accounts yet.</p><?php else: ?><table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th class="text-end">Opening balance</th>
                                    </tr>
                                </thead>
                                <tbody><?php foreach ($accounts as $a): ?><tr>
                                        <td><?php echo e($a['name']) ?></td>
                                        <td><?php echo e($a['account_type']) ?></td>
                                        <td class="text-end"><?php echo cash($a['opening_balance']) ?></td>
                                    </tr><?php endforeach?></tbody>
                            </table><?php endif?>
                        </div>
                    </div>
                </div>
                <?php elseif ($page === 'transactions'): ?><h1 class="h3">Transactions</h1>
                <p class="text-secondary">Add income or expenses from the dashboard. Recent transactions appear there.
                </p><a class="btn btn-primary" href="?page=dashboard">Go to dashboard</a>
                <?php elseif (in_array($page, ['debts', 'plans', 'budgets', 'reports'], true)): ?><div class="card p-5">
                    <h1 class="h4"><?php echo e(ucfirst($page)) ?></h1>
                    <p class="text-secondary">Database tables are ready; this module's forms and reports are the next
                        build stage.</p><a href="?page=dashboard" class="btn btn-primary align-self-start">Back to
                        dashboard</a>
                </div>
                <?php else: ?><div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                    <div>
                        <h1 class="h3 mb-1">Dashboard</h1>
                        <p class="text-secondary mb-0"><?php echo e($ent['name']) ?> · South African rand (ZAR)</p>
                    </div><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#txModal"><i
                            class="fa-solid fa-plus me-2"></i>Add transaction</button>
                </div>
                <div id="loadError" class="alert alert-danger d-none"></div>
                <div class="row g-3 mb-4">
                    <?php foreach ([['Account balance', 'balance'], ['Total income', 'income'], ['Total expenses', 'expenses'], ['Net cash flow', 'cash_flow']] as [$label, $id]): ?>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card p-3">
                            <div class="text-secondary small"><?php echo e($label) ?></div>
                            <div class="stat mt-2" id="<?php echo $id ?>">R 0.00</div>
                        </div>
                    </div><?php endforeach?>
                </div>
                <div class="card p-4">
                    <h2 class="h5">Recent transactions</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="recent">
                                <tr>
                                    <td colspan="5">Loading…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal fade" id="txModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="txForm">
                                <div class="modal-header">
                                    <h2 class="modal-title fs-5">Add transaction</h2><button type="button"
                                        class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body"><input type="hidden" name="csrf_token"
                                        value="<?php echo e(csrf()) ?>"><label class="form-label">Account</label><select
                                        class="form-select mb-3" name="account_id" required>
                                        <option value="">Select account</option><?php foreach ($accounts as $a): ?>
                                        <option value="<?php echo $a['id'] ?>"><?php echo e($a['name']) ?></option>
                                        <?php endforeach?>
                                    </select>
                                    <div class="row">
                                        <div class="col-6"><label class="form-label">Type</label><select
                                                class="form-select mb-3" name="type">
                                                <option value="expense">Expense</option>
                                                <option value="income">Income</option>
                                            </select></div>
                                        <div class="col-6"><label class="form-label">Amount (R)</label><input
                                                class="form-control mb-3" type="number" min="0.01" step="0.01"
                                                name="amount" required></div>
                                    </div><label class="form-label">Description</label><input class="form-control mb-3"
                                        name="description" required><label class="form-label">Date</label><input
                                        class="form-control" type="date" name="transaction_date"
                                        value="<?php echo date('Y-m-d') ?>" required>
                                    <div id="txMsg" class="small mt-3"></div><?php if (! $accounts): ?><p
                                        class="small text-danger mt-2">Create an account first.</p><?php endif?>
                                </div>
                                <div class="modal-footer"><button type="button" class="btn btn-light"
                                        data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"
                                        <?php echo ! $accounts ? 'disabled' : '' ?>>Save</button></div>
                            </form>
                        </div>
                    </div>
                </div><?php endif?>
            </main>
        </div>
    </div><?php endif?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function money(v) {
        return new Intl.NumberFormat('en-ZA', {
            style: 'currency',
            currency: 'ZAR'
        }).format(Number(v || 0));
    }

    function safe(v) {
        return $('<div>').text(v == null ? '' : String(v)).html();
    }

    function refresh() {
        if (!$('#recent').length) return;
        $.getJSON('?page=api').done(d => {
            $('#balance').text(money(d.balance));
            $('#income').text(money(d.income));
            $('#expenses').text(money(d.expenses));
            $('#cash_flow').text(money(d.cash_flow));
            let h = '';
            if (!d.recent.length) h =
                '<tr><td colspan=\"5\" class=\"text-secondary\">No transactions yet.</td></tr>';
            d.recent.forEach(t => {
                h += '<tr><td>' + safe(t.transaction_date) + '</td><td>' + safe(t.description) +
                    '</td><td>' + safe(t.account_name) + '</td><td>' + safe(t.type) +
                    '</td><td class=\"text-end\">' + money(t.amount) + '</td></tr>';
            });
            $('#recent').html(h);
        }).fail(() => $('#loadError').removeClass('d-none').text('Could not load dashboard. Check database setup.'));
    }
    $(function() {
        refresh();
        $('#txForm').on('submit', function(e) {
            e.preventDefault();
            $.post('?page=transactions', $(this).serialize()).done(r => {
                $('#txMsg').removeClass('text-danger').addClass('text-success').text(r.message);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('txModal')).hide();
                this.reset();
                refresh();
            }).fail(x => $('#txMsg').addClass('text-danger').text(x.responseJSON?.message ||
                'Could not save transaction.'));
        });
        $('#accountForm').on('submit', function(e) {
            e.preventDefault();
            $.post('?page=accounts', $(this).serialize()).done(r => $('#accountMsg').removeClass(
                    'text-danger').addClass('text-success').text(r.message + ' Reloading…') &&
                setTimeout(() => location.reload(), 500)).fail(x => $('#accountMsg').addClass(
                'text-danger').text(x.responseJSON?.message || 'Could not save account.'));
        });
    });
    </script>
</body>

</html>