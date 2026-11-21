CREATE DATABASE IF NOT EXISTS personal_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE personal_finance;
CREATE TABLE users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE financial_entities (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(150) NOT NULL, entity_type ENUM('personal','business') NOT NULL,
 description TEXT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 UNIQUE KEY uq_entity_name(user_id,name)
) ENGINE=InnoDB;
CREATE TABLE accounts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 financial_entity_id BIGINT UNSIGNED NOT NULL, name VARCHAR(100) NOT NULL,
 account_type ENUM('cash','bank','savings','ewallet','other') NOT NULL DEFAULT 'bank',
 opening_balance DECIMAL(15,2) NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(financial_entity_id) REFERENCES financial_entities(id),
 INDEX(user_id,financial_entity_id)
) ENGINE=InnoDB;
CREATE TABLE categories (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL,
 name VARCHAR(100) NOT NULL, type ENUM('income','expense') NOT NULL,
 icon VARCHAR(60) DEFAULT 'circle',
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 UNIQUE KEY uq_category(user_id,name,type)
) ENGINE=InnoDB;
CREATE TABLE transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 account_id BIGINT UNSIGNED NOT NULL, category_id BIGINT UNSIGNED NULL,
 type ENUM('income','expense','transfer') NOT NULL, amount DECIMAL(15,2) NOT NULL,
 transaction_date DATE NOT NULL, description VARCHAR(255) NOT NULL,
 reference_no VARCHAR(100) NULL, transfer_account_id BIGINT UNSIGNED NULL,
 status ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'completed',
 notes TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(account_id) REFERENCES accounts(id),
 FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
 FOREIGN KEY(transfer_account_id) REFERENCES accounts(id) ON DELETE SET NULL,
 INDEX(user_id,transaction_date), INDEX(account_id,status)
) ENGINE=InnoDB;
CREATE TABLE contacts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(150) NOT NULL, phone VARCHAR(30) NULL, email VARCHAR(190) NULL, notes TEXT NULL,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE debts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 financial_entity_id BIGINT UNSIGNED NOT NULL, contact_id BIGINT UNSIGNED NULL,
 account_id BIGINT UNSIGNED NULL, title VARCHAR(180) NOT NULL,
 debt_type ENUM('payable','receivable') NOT NULL, original_amount DECIMAL(15,2) NOT NULL,
 interest_rate DECIMAL(7,4) NOT NULL DEFAULT 0, start_date DATE NOT NULL, due_date DATE NULL,
 status ENUM('active','settled','cancelled') NOT NULL DEFAULT 'active', notes TEXT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(financial_entity_id) REFERENCES financial_entities(id),
 FOREIGN KEY(contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
 FOREIGN KEY(account_id) REFERENCES accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE debt_payments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, debt_id BIGINT UNSIGNED NOT NULL,
 transaction_id BIGINT UNSIGNED NULL, amount DECIMAL(15,2) NOT NULL,
 payment_date DATE NOT NULL, notes TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(debt_id) REFERENCES debts(id) ON DELETE CASCADE,
 FOREIGN KEY(transaction_id) REFERENCES transactions(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE plans (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 financial_entity_id BIGINT UNSIGNED NOT NULL, name VARCHAR(150) NOT NULL, description TEXT NULL,
 target_amount DECIMAL(15,2) NOT NULL DEFAULT 0, target_date DATE NULL,
 priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
 status ENUM('planned','saving','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(financial_entity_id) REFERENCES financial_entities(id)
) ENGINE=InnoDB;
CREATE TABLE plan_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, plan_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(180) NOT NULL, estimated_cost DECIMAL(15,2) NOT NULL DEFAULT 0,
 actual_cost DECIMAL(15,2) NULL, quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
 priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
 status ENUM('planned','purchased','cancelled') NOT NULL DEFAULT 'planned',
 purchase_date DATE NULL, transaction_id BIGINT UNSIGNED NULL, notes TEXT NULL,
 FOREIGN KEY(plan_id) REFERENCES plans(id) ON DELETE CASCADE,
 FOREIGN KEY(transaction_id) REFERENCES transactions(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE budgets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 financial_entity_id BIGINT UNSIGNED NOT NULL, category_id BIGINT UNSIGNED NOT NULL,
 budget_month DATE NOT NULL, limit_amount DECIMAL(15,2) NOT NULL,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(financial_entity_id) REFERENCES financial_entities(id),
 FOREIGN KEY(category_id) REFERENCES categories(id),
 UNIQUE KEY uq_budget(user_id,financial_entity_id,category_id,budget_month)
) ENGINE=InnoDB;
CREATE TABLE recurring_transactions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 financial_entity_id BIGINT UNSIGNED NOT NULL, account_id BIGINT UNSIGNED NOT NULL,
 category_id BIGINT UNSIGNED NULL, type ENUM('income','expense') NOT NULL,
 description VARCHAR(255) NOT NULL, amount DECIMAL(15,2) NOT NULL,
 frequency ENUM('weekly','fortnightly','monthly','yearly') NOT NULL DEFAULT 'monthly',
 start_date DATE NOT NULL, next_due_date DATE NULL, end_date DATE NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 FOREIGN KEY(financial_entity_id) REFERENCES financial_entities(id),
 FOREIGN KEY(account_id) REFERENCES accounts(id),
 FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL,
 INDEX(user_id,is_active,next_due_date)
) ENGINE=InnoDB;
INSERT INTO categories(user_id,name,type,icon) VALUES
(NULL,'Salary','income','wallet'),(NULL,'Business Sales','income','briefcase'),
(NULL,'Freelance Income','income','laptop'),(NULL,'Other Income','income','circle-dollar-sign'),
(NULL,'Food and Groceries','expense','basket'),(NULL,'Transport','expense','car'),
(NULL,'Debt Repayment','expense','hand-coins'),(NULL,'Housing','expense','house'),
(NULL,'Utilities','expense','bolt'),(NULL,'Shopping','expense','cart'),
(NULL,'Furniture','expense','couch'),(NULL,'Home Improvement','expense','paintbrush'),
(NULL,'Supplies','expense','box'),(NULL,'Equipment','expense','wrench'),
(NULL,'Subscriptions','expense','calendar'),(NULL,'Other Expenses','expense','ellipsis');
