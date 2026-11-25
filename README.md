# UniResolve

A campus issue reporting site. Students report problems (broken lights, Wi-Fi, leaks...), each issue gets a reference number like `UR-2026-000012`, and staff update it until it is fixed.

Built with plain **PHP 8, MySQL, HTML, CSS and JavaScript** (no frameworks, nothing to install with Composer).

## Features
- Student sign-up and sign-in; staff and admin accounts (role-based access).
- Report an issue: category, urgency, location, description. The department is chosen automatically from the category.
- Every issue has: reference number, category, urgency, status, department, submission date.
- Status history + comment thread on each issue. Staff can post updates, change status and leave internal notes (hidden from students).
- Admins assign issues to a department and/or staff member, and create staff/admin accounts.
- Dashboard with counts and simple charts (by status, urgency, category, department, last 14 days, average time to resolve).
- Security: `password_hash` (bcrypt), prepared statements (PDO), CSRF tokens on every form, output escaping, session ID regeneration, login lockout after 5 wrong tries, `.env` and `includes/` blocked from the web.

## Roles
| Role | Can do |
|---|---|
| student | Report issues, see only their own, comment |
| staff | See all issues, post updates, change status, internal notes |
| admin | Everything staff can do + assign issues + manage users |

## Folder map
```
index.php login.php register.php logout.php   pages
report.php issues.php issue.php dashboard.php admin_users.php
seed.php            creates demo accounts (delete after use)
includes/           database, security helpers, header/footer
assets/             style.css, app.js
database/schema.sql tables + departments + categories
.env.example        copy to .env and fill in
```

## Run it on your computer (XAMPP, easiest)
1. Install **XAMPP** (apachefriends.org). Open the XAMPP Control Panel and **Start** Apache and MySQL.
2. Copy the `uniresolve` folder into `C:\xampp\htdocs\` (Mac: `/Applications/XAMPP/htdocs/`).
3. Go to `http://localhost/phpmyadmin` > **New** > name it `uniresolve` > Create.
4. Click that database > **Import** > choose `database/schema.sql` > **Import**.
5. In the project folder, copy `.env.example` to a new file named `.env`. Set `DB_USER=root`, `DB_PASS=` (empty, XAMPP default), `DB_NAME=uniresolve`.
6. Seed demo accounts: open `http://localhost/uniresolve/seed.php?token=` + the `SEED_TOKEN` value from your `.env`.
   The page prints three logins **once**. Write them down. Then delete `seed.php`.
7. Open `http://localhost/uniresolve/` and sign in.

## Publish free (InfinityFree is one option; any free PHP + MySQL host works)
Free hosts change their rules, so read their pages as you go.
1. Sign up at infinityfree.com and create a hosting account (choose the free subdomain option).
2. In the control panel open **MySQL Databases**, create a database. Note the host name, database name and user shown there (the host is NOT `localhost` on most free hosts).
3. Open **phpMyAdmin** from the panel > select your database > **Import** > `database/schema.sql`.
4. Make your `.env` file on your computer from `.env.example` using the values from step 2. Type the database password yourself on your computer only.
5. Open **Online File Manager** (or an FTP client) > go to the `htdocs` folder > upload everything inside the `uniresolve` folder (including `.env`, `.htaccess`, `includes`, `assets`, `database`).
6. Visit `https://yoursite/seed.php?token=YOUR_SEED_TOKEN`, save the logins, then **delete `seed.php`** in File Manager.
7. Open your site and run `CHECKLIST.md`.

## Put the code on GitHub
1. github.com > **New repository** > name `uniresolve` > Create (do not tick "add README").
2. Easiest: click **uploading an existing file**, drag in the project files (NOT `.env`), and **Commit changes**.
3. Or, with Git installed, in the project folder:
   `git init`, `git add .`, `git commit -m "UniResolve"`, `git branch -M main`, `git remote add origin <your repo URL>`, `git push -u origin main`.
   `.gitignore` already keeps `.env` out.

## Troubleshooting
- "Database connection failed": check `.env` values (host, name, user, password).
- Blank page: ask your host to show PHP errors, or check you have PHP 8.0 or newer.
- 419 error: the page was open too long; refresh it and retry.
- Locked out after wrong passwords: wait 5 minutes.
