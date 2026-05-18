# Woodland Library Backend

This backend implements a secure PHP MVC app for the Woodland Library project.

## Setup

1. Copy `Backend` into your XAMPP `htdocs` folder or serve it from your web root.
2. Create the database from `Backend/database/schema.sql`.
3. Update `Backend/config/database.php` with your MySQL credentials.
4. Ensure PHP sessions and file uploads are enabled.
5. Open `http://localhost/your-backend-path/public/` in your browser.

## Key features

- MVC structure with controllers, models, views
- PDO-based MySQL connection
- Secure auth with sessions and remember-me cookies
- CSRF protection on POST forms
- Book and shelf management
- Server-side validation
- Static assets in `public/assets/css` and `public/assets/js`

## Notes

- Place cover images and uploads under `public/uploads/`.
- `public/assets/css/style.css` contains the wooden-themed UI styling.
- `public/assets/js/app.js` handles simple confirmation interactions.
