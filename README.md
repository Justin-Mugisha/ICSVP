# ICSVP - Inclusive Community Support and Volunteer Coordination Platform

A web platform that connects people and organizations who need help with volunteers who have the right skills. Built as a final-year Software Engineering project.

## Features

- **Four roles:** Volunteer, Individual, Organization, Admin, each with different permissions
- **Volunteers:** manage a profile and skills, browse open requests, apply, and track application status
- **Individuals and organizations:** post requests with required skills, review applicants ranked by skill match, accept or reject them
- **Skill matching:** a simple, explainable percentage formula (no machine learning)
- **Notifications:** created for the volunteer when an application is accepted or rejected
- **Admin:** dashboard statistics, view all users, requests and applications, manage the skill list
- **Security:** hashed passwords, session-based login, role-based route protection, ownership checks, prepared SQL statements, input validation

## Matching Formula

    Matching % = (matching skills / required skills) x 100

Example: a request needs First Aid, Emergency Response and Teaching. A volunteer has First Aid and Teaching. Match = 2 / 3 = 67%. Applicants are ranked from highest to lowest match.

## Tech Stack

| Layer    | Technology                                   |
|----------|----------------------------------------------|
| Frontend | React, Vite, React Router, Axios             |
| Backend  | PHP 8, Slim 4 framework, PDO, phpdotenv      |
| Database | MySQL                                        |
| Auth     | PHP sessions, password_hash (bcrypt)         |
| Tools    | XAMPP, Composer, npm, Git                    |

## Project Structure

    icsvp/
    +-- backend/
    |   +-- public/         index.php (entry point and routes), .htaccess
    |   +-- src/
    |   |   +-- Config/         database connection
    |   |   +-- Controllers/    request handling
    |   |   +-- Middleware/     login and role checks
    |   |   +-- Models/         SQL queries
    |   +-- database/       schema.sql, seed_skills.sql
    |   +-- composer.json
    +-- frontend/
        +-- src/
            +-- components/
            +-- context/        AuthContext (login state)
            +-- pages/
            +-- services/       Axios API modules

## Prerequisites

- XAMPP (Apache and MySQL, PHP 8.0 or newer)
- Composer
- Node.js 20 or newer (developed on v22)
- Apache `mod_rewrite` enabled and `AllowOverride All` for `htdocs` (the XAMPP defaults)

## Setup

The backend must be served at `http://localhost/icsvp/backend/public`, so clone the project into XAMPP's `htdocs` folder.

### 1. Clone

    cd C:\xampp\htdocs
    git clone https://github.com/Justin-Mugisha/ICSVP.git icsvp

### 2. Database

Start Apache and MySQL in the XAMPP Control Panel, then open phpMyAdmin (`http://localhost/phpmyadmin`):

1. Create a database named `icsvp_db`
2. Select it, open the **Import** tab, and import `backend/database/schema.sql`
3. Import `backend/database/seed_skills.sql` to load the starting skills

### 3. Backend

    cd icsvp\backend
    composer install
    copy .env.example .env

Edit `.env` if your MySQL user or password differs from the defaults.

### 4. Create an Admin Account

Admins cannot register through the app. Generate a password hash:

    php -r "echo password_hash('ChangeMe123!', PASSWORD_DEFAULT);"

Then run this SQL in phpMyAdmin, pasting the hash:

    INSERT INTO users (email, password_hash, role)
    VALUES ('admin@example.com', 'PASTE_HASH_HERE', 'admin');

### 5. Frontend

    cd ..\frontend
    npm install
    npm run dev

Open `http://localhost:5173`. Keep the `npm run dev` terminal open while you work.

### Changing the Location

If you place the project somewhere other than `htdocs/icsvp`, update two places:

- `backend/public/index.php` - the `setBasePath('/icsvp/backend/public')` line
- `frontend/src/services/api.js` - the `baseURL`

## API Summary

All routes are prefixed with `/api`. Everything except register and login requires a logged-in session.

| Area          | Endpoints                                                                 | Access                |
|---------------|---------------------------------------------------------------------------|-----------------------|
| Auth          | POST /auth/register, POST /auth/login, POST /auth/logout, GET /me         | Public / any user     |
| Volunteer     | GET, PUT /volunteers/profile; GET, POST /volunteers/skills; DELETE /volunteers/skills/{id}; GET /volunteers/applications; POST /requests/{id}/apply | Volunteer |
| Requests      | GET /requests, GET /requests/{id}, GET /skills                            | Any logged-in user    |
| Requester     | GET, PUT /requesters/profile; POST, GET /requesters/requests; PUT, DELETE /requesters/requests/{id}; GET /requesters/requests/{id}/applications; GET /requesters/requests/{id}/matches; PUT /requesters/applications/{id}/status | Individual, Organization |
| Notifications | GET /notifications, PUT /notifications/{id}/read                          | Any logged-in user    |
| Admin         | GET /admin/dashboard, /admin/users, /admin/requests, /admin/applications, /admin/skills; POST /admin/skills | Admin |

## Known Limitations

- The frontend does not yet display notifications (the API and database side works)
- Notifications are created on accept and reject only, not when a matching request is posted
- Admins can view users and manage skills, but cannot yet deactivate users from the interface
- Organizations are not verified; anyone can register as one
