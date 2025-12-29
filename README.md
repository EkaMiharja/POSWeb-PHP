# Sales Management System (Sistem Penjualan)

A web-based point of sale (POS) system built with PHP and MySQL, featuring role-based access control for Admin, Cashier, and Owner roles. The system manages products, categories, transactions, and generates sales reports.

## Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 4.6.2
- **Backend:** PHP 8.2.12
- **Database:** MySQL (MariaDB 10.4.32)
- **CSS Framework:** AdminLTE 3.2
- **Icons:** Font Awesome 6.4.0
- **Server:** Apache (XAMPP)

## Features Overview

### Authentication & Authorization

- Multi-role login system (Admin, Cashier, Owner)
- Session-based authentication
- Role-based page access control

### Product Management

- CRUD operations for products
- Product categorization
- Stock management with automatic updates
- Product images upload
- Discount management
- Multiple unit measurements (satuan)

### Transaction Processing

- Point of sale interface for cashiers
- Real-time stock validation before checkout
- Multiple payment methods (Cash, E-Wallet, Credit Card, Debit Card)
- Automatic order number generation
- Transaction history tracking

### Reporting & Analytics

- Date-range based sales reports
- Total transaction count
- Revenue calculations
- Product sales statistics
- Export functionality

## Page Functions

### 1. index.php (Login Page)

The main entry point of the application. Handles user authentication and redirects users to appropriate pages based on their role:

- **Admin** → Redirected to user.php
- **Cashier** → Redirected to daftarproduk.php
- **Owner** → Redirected to laporan.php

### 2. user.php (User Management)

Admin-only page for managing system users. Functions include:

- Add new users with role assignment
- Edit existing user credentials
- Delete users from the system
- View all registered users
- Manage roles: Admin, Kasir (Cashier), Owner

### 3. kategori.php (Category Management)

Manages product categories for better organization. Features:

- Create new product categories
- Edit category names and dates
- Delete categories
- View all categories with timestamps
- Category list displayed in table format

### 4. produk.php (Product Management)

Complete product inventory management interface. Functions:

- Add new products with details (name, category, price, stock, discount)
- Upload product images
- Edit product information
- Delete products
- Search and filter products
- Category-based organization
- Stock level monitoring

### 5. daftarproduk.php (POS/Cashier Page)

The main point of sale interface for cashiers. Features:

- Product catalog display with images
- Shopping cart functionality
- Real-time stock validation
- Automatic stock deduction on checkout
- Multiple payment method selection
- Customer name recording
- Order number generation
- Transaction processing with validation

### 6. detailPenjualan.php (Transaction Details)

Manages detailed transaction records. Functions:

- Add manual transaction entries
- Edit transaction details
- Delete transaction records
- Multi-product selection per transaction
- Order tracking by order number
- Payment method management

### 7. laporan.php (Sales Reports)

Owner-accessible reporting dashboard. Features:

- Date range filtering (start date to end date)
- Total transaction count
- Total revenue calculation
- Total items sold count
- Detailed transaction list view
- Product breakdown per transaction
- Print/export functionality

### 8. profile.php (User Profile)

Personal profile management for logged-in users. Functions:

- Update first name and last name
- Edit email address
- Update physical address
- Modify phone number
- Session-based profile access
- Auto-create profile on first save

### 9. config.php (Database Configuration)

Database connection configuration file containing:

- Database host settings
- Database name (penjualan_db)
- Username and password credentials
- MySQL connection initialization

## Database Structure

Database name: `penjualan_db`

### Tables Overview

#### 1. users

Stores system user credentials and roles.

```
Columns:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- nama (VARCHAR 100) - Full name
- username (VARCHAR 100, UNIQUE) - Login username
- password (VARCHAR 255) - Password (plain text)
- role (VARCHAR 50) - User role (admin/kasir/owner)
```

#### 2. kategori

Product category master data.

```
Columns:
- id_kategori (INT, PRIMARY KEY, AUTO_INCREMENT)
- nama_kategori (VARCHAR 100) - Category name
- tanggal (DATETIME) - Creation timestamp
```

#### 3. produk

Product inventory information.

```
Columns:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- username (VARCHAR 50) - Creator username
- nama (VARCHAR 100) - Product name
- gambar (VARCHAR 255) - Image filename
- satuan (VARCHAR 50) - Unit of measurement
- kategori (VARCHAR 50) - Product category
- stok (INT) - Stock quantity
- harga (DECIMAL 15,2) - Price
- keterangan (TEXT) - Description
- diskon (DECIMAL 5,2) - Discount percentage
```

#### 4. transaksi

Transaction/sales records.

```
Columns:
- id_transaksi (INT, PRIMARY KEY, AUTO_INCREMENT)
- order_number (VARCHAR 50) - Unique order identifier
- customer_name (VARCHAR 100) - Customer name
- nama_produk (TEXT) - Product names (comma-separated)
- payment_method (VARCHAR 50) - Payment type
- total (DECIMAL 15,2) - Total amount
- tanggal (DATETIME) - Transaction timestamp
```

#### 5. profile

User profile information.

```
Columns:
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- nama_depan (VARCHAR 100) - First name
- nama_belakang (VARCHAR 100) - Last name
- email (VARCHAR 150, UNIQUE) - Email address
- alamat (TEXT) - Address
- no_handphone (VARCHAR 20) - Phone number
- username (VARCHAR 50) - Associated user account
```

## Installation

1. Install XAMPP or similar PHP development environment
2. Clone or copy this project to `htdocs` directory
3. Create database named `penjualan_db`
4. Import the SQL file:
   ```
   Import: assets/penjualan_db.sql
   ```
5. Configure database credentials in `config.php` if needed
6. Access the application via browser:
   ```
   http://localhost/RPLKELOMPOK/
   ```

## Default Login Credentials

**Admin Account:**

- Username: `Eka`
- Password: `888`

**Cashier Account:**

- Username: `Rania`
- Password: `111`

## Directory Structure

```
RPLKELOMPOK/
├── assets/
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   ├── img/                 # Image assets
│   ├── plugins/             # Third-party libraries
│   └── penjualan_db.sql     # Database schema
├── uploads/                 # Product images storage
├── *.php                    # Application pages
└── config.php               # Database configuration
```

## Security Notes

- This system uses plain text passwords (not recommended for production)
- SQL queries are vulnerable to SQL injection
- No input validation/sanitization implemented
- Session management is basic
- Recommended to implement proper security measures before production use

## Future Improvements

- Implement password hashing (bcrypt/Argon2)
- Add prepared statements for SQL queries
- Input validation and sanitization
- CSRF protection
- Role-based middleware
- Inventory alerts for low stock
- Barcode scanning integration
- Receipt printing functionality
- Multi-branch support

## License

This project is developed for educational purposes as part of RPL (Rekayasa Perangkat Lunak) coursework.
