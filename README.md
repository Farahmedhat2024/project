# StayEase — Clean NTI PHP Project

## Structure

- `index.php` — redirects to the properties listing.
- `login.php`, `register.php`, `logout.php`, `profile.php` — authentication/profile.
- `properties/` — listing, search/filter, and property details.
- `booking/` — create, confirm, cancel, and view bookings.
- `reviews/` — add/delete reviews.
- `admin/` — dashboard, users, properties, bookings, reviews.
- `config/` — single database connection.
- `includes/` — authentication guard and shared navbar.
- `assets/` — CSS and property images.
- `database.sql` — database dump matched to the project.

## Database

The supplied database uses MariaDB on port `3307`.

`config/database.php` is already configured for:

- host: `127.0.0.1`
- port: `3307`
- user: `root`
- password: empty
- database: `stayease`

If your MariaDB/MySQL uses another port, change `$port` in that file.

## Run

Put the project at:

`C:\xampp\htdocs\nti\final_project\PROJECT`

Then open:

`http://localhost/nti/final_project/PROJECT/`

## Admin

The supplied database contains an admin account:

- Email: `diaamariam@gmail.com`
- Password: `phantom`

The password is stored as a proper `password_hash()` value in the included `database.sql`.

## Important cleanup

The duplicate `stayease/` frontend, duplicate database connection files, Git metadata, and the temporary connection-test page were removed from this cleaned version.

## Main fixes

- Corrected registration dependencies and validation function.
- Unified all pages on one database connection.
- Configured MariaDB port `3307`.
- Removed the invalid `is_active` user toggle because the real `users` table has no such column.
- Fixed authentication redirects and session handling.
- Fixed booking `bind_param()` type mismatch.
- Fixed booking/property/review relative paths.
- Added property reviews to the details page.
- Added consistent authenticated navigation.
- Kept account deletion compatible with the database foreign-key rules.
