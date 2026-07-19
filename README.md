# 📚 Online Bookstore

A full-stack web application built with PHP, MySQL, Bootstrap 5, and JavaScript. Features user authentication, admin panel, shopping cart, order management, and complete CRUD operations.

## ✨ Features

### 🔐 Authentication
- User Registration with validation
- **Email OTP Verification** during registration (6-digit OTP, 10-min expiry)
- Secure Login with password hashing (`password_hash`)
- Role-based access (Admin / User)
- Forgot Password / Reset Password flow
- Session management
- Logout

### 👑 Admin Panel
- Analytics Dashboard with **5 Chart.js charts**
  - Total Users, Books, Orders, Revenue stat cards
  - Orders & Revenue bar/line chart (last 7 days)
  - Books by Category doughnut chart
  - New User Registrations line chart (last 7 days)
  - Order Status Distribution pie chart
  - Active Users (new this month)
  - Pending Orders count
  - Low Stock alerts
- Manage Books (CRUD with image upload, discount, rating)
- Manage Categories (CRUD)
- Manage Users (CRUD, role toggle, active period filter for 7/30/90 days)
- Manage Orders (CRUD, status updates)
- Search, filter, and pagination on all tables

### 👤 User Panel
- Browse books with search and filters
- View book details
- Shopping cart (add, update, remove)
- Checkout with order placement
- Order history
- Profile management with picture upload

### 🛒 Shopping Features
- **AJAX real-time search** (debounced, no page reload)
- Search by title or author
- Filter by category
- Price range filter
- Sort by newest, price, title
- Pagination
- Stock management

### 🛡️ Security
- SQL injection protection (prepared statements)
- XSS prevention (`htmlspecialchars`)
- Password hashing (`password_hash` / `password_verify`)
- Server-side validation
- Secure sessions
- Role-based access control

## 🗄️ Database Schema

| Table | Description |
|-------|-------------|
| `roles` | User roles (admin, user) |
| `users` | User accounts with profile |
| `categories` | Book categories |
| `books` | Book inventory |
| `cart` | Shopping cart items |
| `orders` | Order records |
| `order_items` | Order line items |

## 📂 Project Structure

```
online_bookstore/
├── admin/
│   ├── includes/
│   │   ├── admin_header.php
│   │   └── admin_footer.php
│   ├── dashboard.php
│   ├── manage_books.php
│   ├── manage_categories.php
│   ├── manage_users.php
│   └── manage_orders.php
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── images/
├── includes/
│   ├── config.php
│   ├── functions.php
│   ├── auth_check.php
│   ├── header.php
│   └── footer.php
├── uploads/
├── sql/
│   └── schema.sql
├── docs/
│   ├── er_diagram.html
│   └── wireframes.html
├── index.php
├── login.php
├── register.php
├── forgot_password.php
├── reset_password.php
├── logout.php
├── shop.php
├── book_details.php
├── cart.php
├── checkout.php
├── profile.php
└── README.md
```

## 🚀 Installation Guide

### Prerequisites
- XAMPP (PHP 8.0+, MySQL)
- Web browser

### Steps

1. **Download & Extract**
   - Copy the `online_bookstore` folder to `C:\xampp\htdocs\`

2. **Database Setup**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `online_bookstore`
   - Import `sql/schema.sql` (click Import, choose file, click Go)
   - Or run: `mysql -u root < sql/schema.sql`

3. **Configuration**
   - No configuration needed! Default settings:
     - Host: `localhost`
     - Username: `root`
     - Password: `""` (empty)
     - Database: `online_bookstore`
   - Update `includes/config.php` if your MySQL credentials differ

4. **Generate Placeholder Images (Optional)**
   - Visit: `http://localhost/online_bookstore/assets/images/placeholder.php`
   - This creates default book and user placeholder images

5. **Launch Application**
   - Open: `http://localhost/online_bookstore/`

### Default Admin Account
- **Email:** `admin@bookstore.com`
- **Password:** `Admin@123`

## 💻 Technology Stack

| Technology | Purpose |
|------------|---------|
| PHP 8+ | Server-side scripting |
| MySQL | Database |
| Bootstrap 5 | Frontend framework |
| HTML5 | Structure |
| CSS3 | Styling |
| JavaScript | Client-side interactivity |
| mysqli | Database connectivity |

## 🔒 Security Features

- **Prepared Statements** - All database queries use `mysqli_prepare()` to prevent SQL injection
- **Password Hashing** - `password_hash(PASSWORD_DEFAULT)` for secure credential storage
- **XSS Prevention** - `htmlspecialchars()` on all output
- **Input Validation** - Server-side validation for all forms
- **Session Security** - Session-based authentication with role verification
- **File Upload** - Restricted file types and size validation

## 📋 API / Page Reference

### Public Pages
- `index.php` - Home page with featured books
- `shop.php` - Browse with search, filters, pagination
- `book_details.php` - Single book view
- `cart.php` - Shopping cart management
- `checkout.php` - Order placement
- `profile.php` - User profile & order history

### Auth Pages
- `login.php` - User login
- `register.php` - User registration
- `forgot_password.php` - Password reset request
- `reset_password.php` - Password reset
- `logout.php` - Logout

### Admin Pages
- `admin/dashboard.php` - Analytics overview
- `admin/manage_books.php` - Book CRUD
- `admin/manage_categories.php` - Category CRUD
- `admin/manage_users.php` - User management
- `admin/manage_orders.php` - Order management

## 📸 Screenshots & Diagrams

- **ER Diagram:** `docs/er_diagram.html`
- **Wireframes:** `docs/wireframes.html`

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a pull request

## 📄 License

This project is open-source and available for educational purposes.

## 📋 Version History

### v2.0.0 — Task 5 Capstone Enhancements
- Email OTP verification during registration (`verify_otp.php`)
- AJAX real-time search & filtering (`search_ajax.php`)
- Enhanced Chart.js analytics (5 charts: Orders/Day, Revenue, User Registrations, Category & Status distribution)
- User registration trend chart on admin dashboard
- Order status distribution pie chart
- Active user period filter (7/30/90 days) in user management
- Deployment configuration (`.htaccess`, `DEPLOYMENT.md`)
- Comprehensive project report (`docs/project_report.html`)

### v1.0.0 — Task 4 Base Features
- Authentication, Admin Panel, CRUD, Cart, Checkout
- Discounts, Ratings, Wishlist, Quick View
- Book cover generation, ₹ pricing
