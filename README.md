# One More Game

A PHP/MySQL capstone web app for finding, creating, and joining pickup basketball games.

## Main flow
Create Account → Login → Court Status / Map → Browse or Create Games → Join Games → Account → Logout

## Local setup
Use XAMPP with Apache + MySQL. Import `sql/schema.sql` in phpMyAdmin, then visit:

`http://localhost/One-More-Game-main/`

Demo account: `Jesse` / `cool`

See `TESTING_INSTRUCTIONS.txt` for the full test checklist.

## Project structure
`index.php` is the single main application. The legacy `browse.php` route now redirects to the Browse Games tab so there is only one Browse implementation to maintain.
