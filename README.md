#  Secure P2P Payment Platform

A secure, containerized **Peer-to-Peer (P2P) Payment Platform** built using **PHP, MySQL, Docker, and Apache**.

This project simulates a digital payment system where users can register, securely log in, maintain a wallet balance, transfer money to other users, view transaction history, and monitor account activity.

The primary focus of the project is **secure web application development**, with protection against common security issues such as CSRF, SQL injection, session fixation, brute-force login attempts, and improper input handling.

---

##  Clone & Test

### Prerequisites
- Git
- Docker Desktop

### Run the Project

~~~bash
git clone https://github.com/AbdullaOvais/secure-p2p-payment.git
cd secure-p2p-payment
~~~

Create a `.env` file in the project root:

~~~env
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=p2p_payment
MYSQL_USER=p2p_user
MYSQL_PASSWORD=p2p_password
~~~

Start the application:

~~~bash
docker compose up -d --build
~~~

Open:

**http://localhost:8080**

### Quick Test

1. Register two users.
2. Login with one user.
3. Send money to the second user.
4. Check the transaction history and activity logs.
5. Logout and login again to verify authentication.

To stop the application:

~~~bash
docker compose down
~~~
---
##  Features

###  User Management

- User registration with username and email validation
- Secure password hashing using PHP `password_hash()`
- Unique username and email constraints
- User wallet with account balance

###  Authentication

- Secure login and logout
- Password verification using `password_verify()`
- Session-based authentication
- Session fixation protection
- Secure session cookies
- Login success and failure logging

###  P2P Money Transfer

- Transfer money between registered users
- Receiver username validation
- Payment amount validation
- Self-transfer prevention
- Insufficient balance prevention
- Maximum transaction amount validation
- Atomic database transactions
- Row-level locking using `SELECT ... FOR UPDATE`

###  Transactions & Activity

- View transaction history
- View account activity logs
- Login activity tracking
- Failed login tracking
- Logout activity tracking
- Payment activity tracking
- IP address and timestamp logging

---

##  Security Features

Security is one of the main objectives of this project.

| Security Feature | Implementation |
|---|---|
|  Password Security | `password_hash()` and `password_verify()` |
|  SQL Injection Prevention | Prepared SQL statements |
|  CSRF Protection | Session-based CSRF tokens |
|  Secure Sessions | HttpOnly, Secure and SameSite cookies |
|  Session Fixation Protection | `session_regenerate_id(true)` |
|  Input Validation | Server-side validation |
|  Login Rate Limiting | 5 failed attempts within 5 minutes per IP |
|  Activity Logging | Login, logout, payment and failed-login events |
|  XSS Protection | `htmlspecialchars()` for output |
|  Transaction Integrity | MySQL transactions with commit/rollback |
|  Concurrent Transfer Protection | `SELECT ... FOR UPDATE` |

---

##  System Architecture

~~~text
                    ┌──────────────────────┐
                    │       Browser        │
                    │    User Interface    │
                    └──────────┬───────────┘
                               │
                               │ HTTP
                               ▼
                    ┌──────────────────────┐
                    │   Apache + PHP 8.2   │
                    │     Web Container    │
                    │                      │
                    │  Registration        │
                    │  Authentication      │
                    │  Payments            │
                    │  Transactions        │
                    │  Activity Logs       │
                    │  Security Controls   │
                    └──────────┬───────────┘
                               │
                               │ MySQL Connection
                               ▼
                    ┌──────────────────────┐
                    │      MySQL 8.0       │
                    │     DB Container     │
                    │                      │
                    │  users               │
                    │  transactions        │
                    │  activity_logs       │
                    └──────────────────────┘
~~~

The application runs using two Docker containers:

- **Web Container:** PHP 8.2 + Apache
- **Database Container:** MySQL 8.0

Docker Compose manages the containers, networking, and database volume.

---

## Technology Stack

### Backend

- PHP 8.2
- Apache

### Database

- MySQL 8.0
- MySQLi
- Prepared Statements

### Frontend

- HTML
- CSS

### Infrastructure

- Docker
- Docker Compose

### Security

- PHP Sessions
- CSRF Tokens
- Password Hashing
- Prepared SQL Statements
- Input Validation
- Login Rate Limiting
- Activity Logging

---

##  Project Structure

~~~text
secure-p2p-payment/
│
├── config/
│   ├── database.php
│   └── security.php
│
├── database/
│   └── schema.sql
│
├── public/
│   ├── index.php
│   ├── register.php
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── send_money.php
│   ├── transactions.php
│   └── activity_logs.php
│
├── .env
├── .gitignore
├── Dockerfile
├── docker-compose.yml
└── README.md
~~~

> `.env` contains local database credentials and is excluded from Git using `.gitignore`.

---

## 🗄️ Database Design

The application uses three main tables.

### 1. Users

Stores user account information.

~~~text
users
├── id
├── username
├── email
├── password_hash
├── balance
└── created_at
~~~

Passwords are never stored in plain text. Only secure password hashes are stored.

### 2. Transactions

Stores money transfer records.

~~~text
transactions
├── id
├── sender_id
├── receiver_id
├── amount
├── status
└── created_at
~~~

The `sender_id` and `receiver_id` reference users through foreign keys.

### 3. Activity Logs

Stores security and account activity.

~~~text
activity_logs
├── id
├── user_id
├── action
├── ip_address
└── created_at
~~~

Examples of logged actions:

~~~text
LOGIN_SUCCESS
LOGIN_FAILED
LOGOUT
Payment activity
~~~

This provides a basic audit trail for user and security activity.

---

##  Payment Flow

When a user sends money, the application follows this process:

~~~text
User enters receiver and amount
              │
              ▼
       Input validation
              │
              ▼
       Find receiver
              │
              ▼
    Prevent self-transfer
              │
              ▼
      Begin DB transaction
              │
              ▼
   Lock sender/receiver rows
       using FOR UPDATE
              │
              ▼
     Check sender balance
              │
        ┌─────┴─────┐
        │           │
 Insufficient     Enough
   balance        balance
        │           │
        ▼           ▼
      Reject    Update balances
                    │
                    ▼
            Insert transaction
                    │
                    ▼
              Log activity
                    │
                    ▼
                  COMMIT
                    │
                    ▼
            Payment successful
~~~

Database transactions ensure that balance updates and transaction records are handled atomically.

If an error occurs during the transfer, the transaction is rolled back.

---

##  Authentication Flow

### Registration

~~~text
User
  │
  ▼
Registration Form
  │
  ▼
Validate Input
  │
  ▼
Hash Password
  │
  ▼
Prepared SQL Query
  │
  ▼
Create User
~~~

### Login

~~~text
User
  │
  ▼
Login Form
  │
  ▼
Check Rate Limit
  │
  ▼
Find User
  │
  ▼
Verify Password
  │
  ├── Invalid → LOGIN_FAILED
  │
  └── Valid
        │
        ▼
session_regenerate_id(true)
        │
        ▼
Create Authenticated Session
        │
        ▼
LOGIN_SUCCESS
        │
        ▼
Dashboard
~~~

---

##  CSRF Protection

State-changing operations use CSRF protection.

The application generates a cryptographically secure token:

~~~php
bin2hex(random_bytes(32))
~~~

The token is stored in the user's session and included in forms.

The server verifies the submitted token using:

~~~php
hash_equals()
~~~

Requests containing an invalid or missing CSRF token are rejected.

---

##  Secure Session Cookies

The application configures session cookies using:

- `HttpOnly`
- `Secure`
- `SameSite=Lax`

### HttpOnly

Prevents client-side JavaScript from directly accessing the session cookie.

### Secure

Ensures the cookie is sent over HTTPS when HTTPS is enabled.

### SameSite

Provides additional protection against cross-site request attacks.

---

##  Session Fixation Protection

After successful authentication, the application regenerates the session ID:

~~~php
session_regenerate_id(true);
~~~

This helps prevent session fixation attacks by replacing the existing session identifier after login.

---

##  Login Rate Limiting

The application implements basic login rate limiting.

If an IP address reaches **5 failed login attempts within 5 minutes**, further login attempts are temporarily blocked.

Example:

~~~text
Attempt 1 → Allowed
Attempt 2 → Allowed
Attempt 3 → Allowed
Attempt 4 → Allowed
Attempt 5 → Allowed
Attempt 6 → Blocked
~~~

Failed login attempts are stored in the `activity_logs` table.

---

##  Input Validation

User-controlled input is validated on the server before processing.

### Username

- Minimum 3 characters
- Maximum 50 characters
- Letters, numbers and underscores only

### Email

- Validated using PHP email validation

### Password

- Minimum 8 characters
- Maximum 72 characters

### Payment Amount

- Must be greater than zero
- Maximum ₹1,000,000
- Maximum two decimal places

This prevents invalid data from reaching the application logic and database.

---

##  SQL Injection Prevention

The application uses prepared statements instead of directly concatenating user input into SQL queries.

Example:

~~~php
$stmt = $conn->prepare(
    "SELECT id, password_hash FROM users WHERE username = ?"
);

$stmt->bind_param("s", $username);
~~~

Prepared statements separate SQL instructions from user-provided data and help prevent SQL injection attacks.

---

##  Testing

The application has been tested for:

- User registration
- Duplicate username/email handling
- Successful login
- Invalid login credentials
- Login rate limiting
- Session authentication
- Logout
- Logout activity logging
- Money transfers
- Insufficient balance
- Self-transfer prevention
- Transaction history
- Activity logs
- CSRF validation
- PHP syntax validation
- Docker container execution
- MySQL connectivity

PHP syntax was also validated inside the Docker container.

Example:

~~~bash
docker exec secure_p2p_web php -l /var/www/html/public/login.php
~~~

---

##  Running the Project Locally

### Prerequisites

Install:

- Docker Desktop
- Git

Verify the installation:

~~~bash
docker --version
docker compose version
~~~

### 1. Clone the Repository

~~~bash
git clone https://github.com/AbdullaOvais/secure-p2p-payment.git
cd secure-p2p-payment
~~~

### 2. Create `.env`

Create a `.env` file in the project root:

~~~env
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=p2p_payment
MYSQL_USER=p2p_user
MYSQL_PASSWORD=p2p_password
~~~

> Do not commit `.env` to GitHub because it contains database credentials.

### 3. Start the Application

~~~bash
docker compose up -d --build
~~~

Check the containers:

~~~bash
docker compose ps
~~~

You should see:

~~~text
secure_p2p_web
secure_p2p_db
~~~

### 4. Open the Application

Open:

**http://localhost:8080**

---

##  Stop the Application

~~~bash
docker compose down
~~~

The MySQL database is stored in a Docker volume, so data persists across normal container restarts.

---

##  Restart the Application

~~~bash
docker compose up -d
~~~

For Dockerfile or configuration changes:

~~~bash
docker compose up -d --build
~~~

---

## 🔍 Useful Docker Commands

### Check containers

~~~bash
docker compose ps
~~~

### View web logs

~~~bash
docker compose logs web
~~~

### View database logs

~~~bash
docker compose logs db
~~~

### Access MySQL

~~~bash
docker exec -it secure_p2p_db mysql -u p2p_user -p
~~~

### Check PHP syntax

~~~bash
docker exec secure_p2p_web php -l /var/www/html/public/login.php
~~~

---

##  Environment & Secrets

Database credentials are provided through environment variables.

The application reads these values using:

~~~php
getenv("MYSQL_DATABASE")
getenv("MYSQL_USER")
getenv("MYSQL_PASSWORD")
~~~

Sensitive environment files are excluded from version control using `.gitignore`.

Therefore, local database credentials are not stored in the GitHub repository.

---

##  Future Improvements

Possible future enhancements include:

- Two-factor authentication (2FA)
- Email verification
- Password reset functionality
- Payment notifications
- Role-based access control
- Admin dashboard
- HTTPS/TLS deployment
- Redis-based distributed rate limiting
- Fraud detection
- Automated unit and integration testing
- CI/CD pipeline
- Production deployment
- Security monitoring and alerting

---

##  Learning Objectives

This project demonstrates practical implementation of:

- Secure authentication
- Password hashing
- Session management
- CSRF protection
- SQL injection prevention
- Input validation
- Login rate limiting
- Security activity logging
- Database transactions
- Concurrent transaction handling
- Docker containerization
- MySQL database design
- Secure web application development

---

##  Author

**Abdulla Ovais**

M.Tech in Computer Science & Engineering  
IIT Hyderabad

**GitHub:**  
https://github.com/AbdullaOvais

**Project Repository:**  
https://github.com/AbdullaOvais/secure-p2p-payment

---

## 📄 License

This project is intended for educational and demonstration purposes.
