# Personal & Oritech Finance Manager

A small, server-rendered PHP finance tracker for managing personal and business cash flow in South African rand (ZAR). The application is designed for local XAMPP use and currently supports one owner account with separate financial workspaces for **Personal Finances** and **Oritech Limited**.

## Current functionality

- Owner registration and sign-in with hashed passwords.
- CSRF-protected form submissions and session-based authentication.
- Automatic creation of the Personal Finances and Oritech Limited entities during registration.
- Switching between the personal and business entities from the navigation bar.
- Creating active accounts for banks, cash, savings, e-wallets, or other account types.
- Recording completed income and expense transactions against an account.
- Dashboard totals for account balance, total income, total expenses, and net cash flow.
- Recent-transaction listing with ZAR formatting.
- Responsive Bootstrap 5 interface with Font Awesome icons and jQuery AJAX requests.

The navigation also includes pages for debts, purchase plans, and budgets. Their database tables are included, but those screens are placeholders until their forms, calculations, and reports are implemented. Recurring transactions, contacts, categories, debt payments, and plan items are likewise represented in the schema but are not yet exposed through the interface.

## Technology

- PHP 8.1 or later
- MariaDB or MySQL
- PDO with prepared statements
- Bootstrap 5.3, Font Awesome, and jQuery loaded from CDNs
- Apache through XAMPP

## Project layout

```text
app/bootstrap.php   Session, environment loading, database connection, auth, CSRF, and helpers
database/schema.sql Database and table definitions plus default categories
public/index.php    HTML interface, dashboard JavaScript, and page markup
public/config.php   Routing, authentication, CRUD handlers, and dashboard API responses
```

## Local XAMPP setup

1. Place the project in the XAMPP web directory, for example:

   ```text
   C:\xampp_lite_8_5\www\projects\personal\personal_finance_manager
   ```

2. Start Apache and MySQL/MariaDB in the XAMPP control panel.

3. Import [`database/schema.sql`](database/schema.sql) using phpMyAdmin or the MySQL client. The script creates the `personal_finance` database and all required tables.

4. Create a `.env` file in the project root. The application reads these optional values and falls back to the defaults shown below:

   ```dotenv
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=personal_finance
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Open the public entry point in a browser. Depending on the Apache alias or virtual-host configuration, this will typically be one of:

   ```text
   http://localhost/projects/personal/personal_finance_manager/public/
   http://localhost/personal_finance_manager/public/
   ```

6. Choose **Create owner account**. Registration creates the owner’s two financial entities and signs the user in automatically.

## Using the application

After signing in:

1. Select **Personal Finances** or **Oritech Limited** from the entity selector.
2. Open **Accounts** and add at least one account with its opening balance.
3. Use **Add transaction** on the dashboard to record income or expenses.
4. Review the calculated balance, income, expenses, net cash flow, and recent transactions on the dashboard.

All account and transaction operations are scoped to the signed-in owner and currently selected entity.

## Important limitations

- This is an early-stage local finance manager, not a production-ready accounting system.
- Transactions are currently limited to completed income and expenses; transfer workflows and editing/deleting records are not implemented in the UI.
- The debts, purchase plans, and budgets pages are placeholders.
- Categories and recurring transactions exist in the database schema but are not yet managed from the UI.
- The frontend depends on external CDNs for Bootstrap, Font Awesome, and jQuery.

## Production considerations

Before using the application with sensitive financial information, configure HTTPS, keep `.env` outside the public web root where possible, disable error disclosure, restrict database access, add backups, review authorization boundaries, and complete security testing. Do not commit `.env` or real financial data to source control.
