# TE_Crown Hair — E-Commerce Platform

> A database-driven e-commerce platform developed for TE_Crown Hair, a small hair and beauty business based in Gauteng, South Africa.

## 📌 Project Overview

TE_Crown Hair is a web-based e-commerce platform designed to provide customers with a convenient way to browse hair and beauty products, create accounts, and manage their shopping experience online.

The platform also includes an administrative interface that allows authorised administrators to manage products, product categories, customers and orders.

The system is being developed as a final-year Web Development and E-Commerce project using PHP, MySQL, HTML, CSS and JavaScript.

## 🎯 Project Objectives

The main objectives of the platform are to:

- Provide TE_Crown Hair with a centralised online product catalogue.
- Allow customers to browse and search available products.
- Provide customer registration and secure authentication.
- Verify customer email addresses before account access.
- Provide shopping cart functionality.
- Develop a structured checkout and order-processing system.
- Provide role-based access for administrators.
- Allow administrators to manage products and categories.
- Store application data in a relational MySQL database.
- Provide a responsive user interface for different screen sizes.
- Provide a structured foundation for secure online payment processing.
- Improve the overall ordering process compared with manual social-media and direct-message orders.

## 🛠️ Technologies

- **PHP** — Server-side application development
- **MySQL** — Relational database management
- **HTML5** — Web page structure
- **CSS3** — Styling and responsive design
- **JavaScript** — Client-side functionality
- **Bootstrap 5** — UI components and responsive layout
- **Composer** — PHP dependency management
- **Brevo Transactional Email API** — Email verification

## ✨ Current Features

### Customer Website

- Product catalogue
- Product categories
- Product search
- Category filtering
- Product details
- Product variations
- Shopping cart
- Customer registration
- Email verification
- Secure password hashing
- Customer login and logout
- Customer account page
- Session timeout
- Responsive interface
- Dynamic product information from MySQL
- Product stock information
- Add-to-cart quantity validation

### Administration

- Administrator authentication
- Role-based access control
- Protected administration pages
- Administrative dashboard
- Product management
- Add products
- Edit products
- Activate/deactivate products
- Product image uploads
- Category management
- Category deletion protection when products are associated with a category
- Product stock information
- Database-driven product management

## 🔐 Security

The application currently implements several security measures, including:

- Password hashing using PHP's `password_hash()`
- Password verification using `password_verify()`
- Prepared SQL statements
- Session-based authentication
- Session ID regeneration after login
- Role-based access control
- Protected administrator pages
- 120-minute session timeout
- Email verification
- Cryptographically secure verification tokens
- Verification token expiration
- Environment variables for sensitive API credentials
- Database-driven authentication
- Server-side authorisation checks for administrator access

Sensitive configuration files are excluded from version control.

## 🗄️ Database

The application uses MySQL as its relational database management system.

The current database structure includes:

- `users`
- `categories`
- `products`
- `orders`
- `order_items`

The database schema is located in:

```text
database/schema.sql

The users table stores customer and administrator accounts, including email verification information.
The categories table stores product categories.
The products table stores product information including names, descriptions, prices, images, stock quantities and product status.
The orders and order_items tables provide the database structure required for customer orders and the individual products contained within those orders.

📁 Project Structure

TE_Crown_Hair/
│
├── admin/
│   ├── add-product.php
│   ├── categories.php
│   ├── customers.php
│   ├── edit-product.php
│   ├── index.php
│   ├── orders.php
│   └── products.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   │
│   ├── images/
│   │   ├── categories/
│   │   │   ├── accessories.png
│   │   │   ├── extensions.png
│   │   │   └── wigs.png
│   │   │
│   │   ├── products/
│   │   │   ├── body-wave-wig.png
│   │   │   ├── curly-hair-extensions.png
│   │   │   ├── deep-wave-wig.png
│   │   │   ├── premium-hair-brush.png
│   │   │   ├── premium-hair-extensions.png
│   │   │   ├── premium-wig-cap.png
│   │   │   └── straight-lace-wig.png
│   │   │
│   │   └── hero.png
│   │
│   ├── js/
│   │   ├── cart.js
│   │   └── checkout.js
│   │
│   └── logo/
│       └── logo.png
│
├── database/
│   └── schema.sql
│
├── includes/
│   ├── auth.php
│   ├── db.php
│   ├── footer.php
│   ├── header.php
│   ├── mail.php
│   └── navbar.php
│
├── pages/
│   ├── about.php
│   ├── account.php
│   ├── cart.php
│   ├── checkout.php
│   ├── contact.php
│   ├── login.php
│   ├── logout.php
│   ├── order-confirmation.php
│   ├── product.php
│   ├── register.php
│   ├── shop.php
│   └── verify-email.php
│
├── composer.json
├── composer.lock
├── index.php
└── README.md

🚀 Installation

Requirements

Before running the project, make sure the following are installed:
- PHP 8+
- MySQL
- Composer
- Modern web browser

Clone the Repository

git clone https://github.com/EsperancaPaulo/TE-Crown-Hair-E-Commerce.git

Enter the Project Directory

cd TE-Crown-Hair-E-Commerce

Install PHP Dependencies

composer install

Configure the Database
Create the MySQL database using the schema provided in:

database/schema.sql

The database connection is configured in:

includes/db.php

Update the database credentials if your local MySQL configuration requires different values.
Configure Email Verification
The application uses the Brevo Transactional Email API to send customer email verification messages.
Create a .env file in the project root and configure the required Brevo credentials.
Example:

BREVO_API_KEY=your_api_key_here
BREVO_SENDER_EMAIL=your_verified_sender_email
BREVO_SENDER_NAME=TE_Crown Hair

Important: Never commit .env or any other file containing API keys, passwords or other sensitive credentials to the repository.

Start the Development Server

The project can be started using PHP's built-in development server:

php -S localhost:8001

Open the application in a web browser:
http://localhost:8001

🛒 Customer Shopping Flow

The intended customer shopping process is:
Home Page
    ↓
Shop
    ↓
Product Details
    ↓
Select Product Options
    ↓
Add to Cart
    ↓
View Cart
    ↓
Login / Register
    ↓
Checkout
    ↓
Payment
    ↓
Order Confirmation
    ↓
Customer Order History

Customers can browse products, view individual product details, select available product options, add products to the shopping cart and proceed through the checkout process.
Authentication is required before protected customer actions such as checkout.

👑 Administrator Flow

The administrator system provides a separate protected interface.
Admin Login
    ↓
Admin Dashboard
    ↓
Manage Products
    ├── Add Product
    ├── Edit Product
    └── Activate / Deactivate Product
    ↓
Manage Categories
    ├── Add Category
    ├── Edit Category
    └── Delete Category
    ↓
Manage Customers
    ↓
Manage Orders


Administrator pages are protected using server-side role-based access control.
Customers cannot access administrator pages.


📈 Development Status

The project is actively under development as part of the final-year Web Development and E-Commerce project.


✅ Completed

- Customer registration
- Customer login and logout
- Administrator login and logout
- Email verification
- Password hashing
- Session-based authentication
- Session ID regeneration after login
- 120-minute session timeout
- Role-based access control
- Protected administrator pages
- Product catalogue
- Product search
- Category filtering
- Product details
- Product variations
- Shopping cart functionality
- Cart quantity validation
- Dynamic product information from MySQL
- Product management
- Add products
- Edit products
- Activate/deactivate products
- Product image uploads
- Category management
- Category deletion protection
- MySQL database structure
- Brevo transactional email integration
- Initial checkout interface
- Customer account interface
- Administrative dashboard
- Git version control
- GitHub repository


🚧 In Progress

- Database-driven order processing
- Customer-specific order history
- Complete checkout-to-order database flow
- Payment validation
- Payment processing implementation
- Inventory management
- Stock deduction after successful orders
- Out-of-stock protection during checkout
- Dynamic administrator customer management
- Dynamic administrator order management
- Order status workflow
- Low-stock notifications
- Cart counter integration
- Final responsive testing
- Production hosting
- Final system testing
- Final documentation and coding evidence


🧪 Testing

The application is being tested throughout development rather than only at the end of the project.
Current testing includes:
- Customer registration testing
- Email verification testing
- Customer login testing
- Administrator login testing
- Customer access restriction testing
- Administrator access restriction testing
- Product creation testing
- Product editing testing
- Product deactivation testing
- Category creation testing
- Category editing testing
- Category deletion protection testing
- Product search testing
- Category filtering testing
- Shopping cart testing
- Product quantity testing
- Checkout validation testing
Final end-to-end testing will be completed before deployment and submission.


🌐 Hosting

The project is currently being developed and tested locally.
The final application will be deployed to a suitable web hosting environment so that the completed e-commerce platform can be accessed remotely, as required by the project specification.
Production hosting configuration will include:
- PHP support
- MySQL database support
- Secure environment configuration
- HTTPS
- Working transactional email
- Database connectivity
- Uploaded product assets
- Customer and administrator authentication


💳 Payment Processing

The checkout system is being developed to support structured payment processing.
The planned payment workflow includes:
- Payment method selection
- Payment information validation
- Expiry-date validation
- Payment status handling
- Order creation after successful payment processing
- Inventory updates after successful orders
- Order confirmation
No real customer payment information should be stored directly in the application database.


📦 Inventory Management

The product database includes stock quantities for each product.
The completed inventory system will ensure that:
- Products display their available stock.
- Customers cannot purchase quantities greater than available stock.
- Out-of-stock products cannot be purchased.
- Stock is updated after successful order processing.
- Administrators can view product stock levels.
- Low-stock products can be identified by administrators.


📧 Email Verification

Customer email verification is implemented using the Brevo Transactional Email API.
During registration:
1. The customer's information is validated.
2. The password is securely hashed.
3. A cryptographically secure verification token is generated.
4. The token is stored in the database.
5. An expiry time is assigned to the token.
6. A verification email is sent to the customer.
7. The customer must verify the email address before logging in.
8. After successful verification, the account becomes available for login.
Verification tokens expire to prevent old verification links from being reused.


🔑 Authentication and Authorisation

The application uses session-based authentication.
Customers and administrators have different roles:

Customer
    ↓
Customer Website
Customer Account
Shopping Cart
Checkout
Order History

Administrator
    ↓
Admin Dashboard
Product Management
Category Management
Customer Management
Order Management

Administrator access is controlled on the server side using role checks.
Unauthenticated users are redirected to the login page when attempting to access protected areas.
Customers attempting to access administrator pages are denied access.


📝 Project Information

Project: TE_Crown Hair E-Commerce Platform
Client: TE_Crown Hair
Client Owner: Sonisia Guilundo
Location: Gauteng, South Africa
Business Type: Small Hair and Beauty Business
Project Type: Final-Year Web Development and E-Commerce Project
Developer: Esperanca Paulo
Role: Full-Stack Developer


🔗 Repository

The project source code is available on GitHub:
https://github.com/EsperancaPaulo/TE-Crown-Hair-E-Commerce


⚠️ Development Notice

This repository contains a final-year academic development project and is not currently intended for production transactions.
The system is still being developed and tested.
Payment processing, production hosting, database-driven order processing, inventory management and other deployment requirements are being implemented and tested as part of the development process.