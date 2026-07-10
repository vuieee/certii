# Corporate Training & Certification Tracker

Tracks employee training completion, certification validity, and upcoming renewals across Employee, Manager, and Admin roles.

## Installation (Laragon)

1. Place the `certii` folder inside your Laragon `www` directory, e.g. `C:\laragon\www\certii`. If you already have another project in `www`, just make sure the folder name `certii` doesn't collide with it.
2. Open phpMyAdmin (or the Laragon MySQL terminal) and import `database/schema.sql`. This creates the `corporate_training_db` database, its tables, and the demo accounts below.
3. Start Apache and MySQL in Laragon.
4. Visit `http://localhost/certii/` or `http://certii.test` if Laragon's auto virtual hosts are enabled.

## Demo Accounts

Passwords are BCRYPT hashed in the database; the plaintext for all three is `password123`.

| Role | Email | Password |
| :--- | :--- | :--- |
| Administrator | `admin@example.com` | `password123` |
| Manager | `manager@example.com` | `password123` |
| Employee | `employee@example.com` | `password123` |

## Security Notes

- Every dashboard page is guarded by role before any output is rendered; users landing on a page outside their role are redirected to their own dashboard.
- All queries built from user input use PDO prepared statements.
- Passwords are hashed with `password_hash()` and verified with `password_verify()`.
