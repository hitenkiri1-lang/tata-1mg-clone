# Tata 1mg Clone - Online Medicine & Healthcare E-Commerce System

## Final Year Project

A complete e-commerce platform for online medicine ordering inspired by Tata 1mg, built using PHP Core (no framework).

---

## 📋 Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Default Credentials](#default-credentials)
- [Features Documentation](#features-documentation)
- [Security Features](#security-features)
- [Screenshots](#screenshots)

---

## ✨ Features

### User Panel Features
- ✅ User Registration & Login with validation
- ✅ Forgot Password (Security Question based)
- ✅ User Dashboard with statistics
- ✅ Browse medicines by category
- ✅ AJAX-based search with live suggestions
- ✅ View detailed medicine information
- ✅ Add to cart functionality
- ✅ Update cart (quantity, remove items)
- ✅ Checkout system with address management
- ✅ Multiple payment options (COD & Online Payment)
- ✅ Order confirmation and tracking
- ✅ Order history
- ✅ User profile management
- ✅ Secure logout

### Admin Panel Features
- ✅ Admin login with session security
- ✅ Dashboard with animated statistics
  - Total users
  - Total orders
  - Total revenue
  - Total medicines
  - Pending orders
  - Low stock alerts
- ✅ Category Management (Add/Edit/Delete)
- ✅ Medicine Management
  - Add medicine with image upload
  - Update medicine details
  - Delete medicine
  - Stock management
- ✅ Order Management
  - View all orders
  - Update order status
  - Order details view
- ✅ User Management
  - View all users
  - Block/Unblock users
- ✅ Payment Reports
- ✅ Secure logout

### UI/UX Features
- ✅ Responsive design (Mobile, Tablet, Desktop)
- ✅ Smooth animations (AOS library)
- ✅ Clean green & white theme (1mg inspired)
- ✅ Animated counters on dashboard
- ✅ Loading spinners
- ✅ Alert notifications
- ✅ Smooth transitions

---

## 🛠 Technology Stack

### Frontend
- HTML5
- CSS3
- Bootstrap 5.3.0
- JavaScript (ES6)
- jQuery 3.6.0
- Font Awesome 6.4.0
- AOS (Animate On Scroll) 2.3.1

### Backend
- PHP 7.4+ (Core - No Framework)
- Procedural + Basic OOP

### Database
- MySQL 5.7+

### Server
- Apache (XAMPP/WAMP/LAMP)

---

## 💻 System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Server
- Web Browser (Chrome, Firefox, Edge)
- XAMPP/WAMP/LAMP (Recommended: XAMPP)

---

## 📥 Installation Guide

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install XAMPP on your system
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Project Files
1. Extract the project folder
2. Copy the `tata-1mg-clone` folder to `C:\xampp\htdocs\` (Windows) or `/opt/lampp/htdocs/` (Linux)

### Step 3: Create Database
1. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click on "Import" tab
3. Choose the `database.sql` file from the project root
4. Click "Go" to import the database

**OR**

1. Create a new database named `tata_1mg_clone`
2. Copy the SQL from `database.sql` file
3. Paste and execute in the SQL tab

### Step 4: Configure Database Connection
1. Open `config/database.php`
2. Update database credentials if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tata_1mg_clone');
```

### Step 5: Set Permissions (Linux/Mac)
```bash
chmod -R 755 uploads/
chmod -R 755 assets/
```

### Step 6: Access the Application
- **User Panel**: [http://localhost/tata-1mg-clone/](http://localhost/tata-1mg-clone/)
- **Admin Panel**: [http://localhost/tata-1mg-clone/admin/](http://localhost/tata-1mg-clone/admin/)

---

## 📁 Project Structure

```
tata-1mg-clone/
│
├── admin/                      # Admin panel files
│   ├── includes/              # Admin header/footer
│   ├── dashboard.php          # Admin dashboard
│   ├── categories.php         # Category management
│   ├── medicines.php          # Medicine management
│   ├── orders.php             # Order management
│   ├── users.php              # User management
│   ├── reports.php            # Reports
│   ├── login.php              # Admin login
│   └── logout.php             # Admin logout
│
├── ajax/                       # AJAX request handlers
│   ├── add_to_cart.php        # Add to cart
│   ├── update_cart.php        # Update cart quantity
│   ├── remove_from_cart.php   # Remove from cart
│   └── search_medicines.php   # Search suggestions
│
├── assets/                     # Static assets
│   ├── css/
│   │   └── style.css          # Custom styles
│   ├── js/
│   │   └── main.js            # Custom JavaScript
│   └── images/                # Image assets
│
├── config/                     # Configuration files
│   ├── config.php             # General config
│   └── database.php           # Database config
│
├── includes/                   # Common includes
│   ├── header.php             # User panel header
│   ├── footer.php             # User panel footer
│   └── functions.php          # Common functions
│
├── uploads/                    # Uploaded files
│   └── medicines/             # Medicine images
│
├── user/                       # User panel files
│   ├── register.php           # User registration
│   ├── login.php              # User login
│   ├── logout.php             # User logout
│   ├── dashboard.php          # User dashboard
│   ├── profile.php            # User profile
│   ├── cart.php               # Shopping cart
│   ├── checkout.php           # Checkout page
│   ├── orders.php             # Order history
│   ├── order-details.php      # Order details
│   └── forgot-password.php    # Password recovery
│
├── index.php                   # Home page
├── medicines.php               # Medicine listing
├── medicine-details.php        # Medicine details
├── search.php                  # Search results
├── database.sql                # Database schema
└── README.md                   # Documentation
```

---

## 🗄 Database Schema

### Tables

1. **users** - User accounts
2. **admins** - Admin accounts
3. **categories** - Medicine categories
4. **medicines** - Medicine products
5. **cart** - Shopping cart items
6. **orders** - Order information
7. **order_items** - Order line items
8. **payments** - Payment records

### Relationships
- `medicines.category_id` → `categories.category_id`
- `cart.user_id` → `users.user_id`
- `cart.medicine_id` → `medicines.medicine_id`
- `orders.user_id` → `users.user_id`
- `order_items.order_id` → `orders.order_id`
- `order_items.medicine_id` → `medicines.medicine_id`
- `payments.order_id` → `orders.order_id`

---

## 🔐 Default Credentials

### Admin Panel
- **URL**: http://localhost/tata-1mg-clone/admin/
- **Username**: `admin`
- **Password**: `admin123`

### User Panel
- **URL**: http://localhost/tata-1mg-clone/
- **Email**: `user@test.com`
- **Password**: `user123`

---

## ⚠️ Admin Login Issue – Invalid Credentials Fix

If you encounter "Invalid credentials" error when trying to login to the admin panel, follow these steps to resolve the issue:

### Understanding the Issue

The admin login system uses **bcrypt password hashing** for security. Passwords stored in plain text in the database will not work. The password must be properly hashed using the bcrypt algorithm.

### Step-by-Step Fix

#### Step 1: Open phpMyAdmin
1. Navigate to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Select the `tata_1mg_clone` database from the left sidebar

#### Step 2: Check Admin Username
1. Click on the `admins` table
2. Click the "Browse" tab to view all records
3. Note the exact **username** in the table (e.g., `admin`)
4. **Important**: Use this exact username for login (case-sensitive)

#### Step 3: Generate Bcrypt Password Hash
1. Open [https://bcrypt-generator.com/](https://bcrypt-generator.com/) in your browser
2. Enter your desired password (e.g., `admin123`)
3. Set the "Cost" to **10** (default)
4. Click "Generate Hash"
5. Copy the generated bcrypt hash (starts with `$2y$` or `$2a$`)
   - Example: `$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJ`

#### Step 4: Update Password in Database
1. Return to phpMyAdmin
2. In the `admins` table, click the "Edit" icon (pencil) for the admin record
3. Find the `password` field
4. **Replace** the existing value with the bcrypt hash you generated
5. Click "Go" to save changes

#### Step 5: Login with Updated Credentials
1. Go to [http://localhost/tata-1mg-clone/admin/](http://localhost/tata-1mg-clone/admin/)
2. Enter the **username** from Step 2
3. Enter the **plain text password** you used in Step 3 (e.g., `admin123`)
4. Click "Login"

### Important Notes

- ✅ **Passwords are stored as bcrypt hashes** - Never store plain text passwords
- ✅ **Username is case-sensitive** - Use the exact username from the database
- ✅ **Password field must contain bcrypt hash** - Plain text will not work
- ✅ **Bcrypt hash starts with `$2y$` or `$2a$`** - Verify the hash format
- ✅ **Login uses plain text password** - The system hashes it automatically for comparison

### Example

**Database Record:**
```
username: admin
password: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

**Login Form:**
```
Username: admin
Password: admin123
```

The system will hash `admin123` and compare it with the stored hash.

### Troubleshooting

**Still can't login?**
1. Clear browser cache and cookies
2. Verify Apache and MySQL are running in XAMPP
3. Check `config/database.php` for correct database credentials
4. Ensure the `admins` table exists and has at least one record
5. Check browser console for JavaScript errors
6. Verify the bcrypt hash was copied completely (no spaces or line breaks)

---

## 📖 Features Documentation

### User Registration
- Full name, email, phone validation
- Password strength check (minimum 6 characters)
- Security question for password recovery
- Email uniqueness validation
- Password hashing using PHP password_hash()

### User Login
- Email and password authentication
- Session-based authentication
- Password verification using password_verify()
- Redirect to dashboard on success

### Medicine Browsing
- Category-wise filtering
- Search functionality with AJAX
- Pagination support
- Stock availability display
- Prescription requirement indicator
- Discount calculation and display

### Shopping Cart
- Add to cart (AJAX)
- Update quantity
- Remove items
- Real-time cart count update
- Cart total calculation

### Checkout Process
1. Review cart items
2. Enter/confirm shipping address
3. Select payment method (COD/Online)
4. Place order
5. Order confirmation

### Payment Methods
1. **Cash on Delivery (COD)**
   - Pay when order is delivered
   - No advance payment required

2. **Online Payment (Dummy)**
   - Simulated payment gateway
   - Instant payment confirmation
   - Transaction ID generation

### Order Tracking
- Order status: Pending → Confirmed → Shipped → Delivered
- Order history with filters
- Detailed order view
- Invoice generation

### Admin Dashboard
- Animated statistics counters
- Recent orders table
- Quick action buttons
- Low stock alerts
- Revenue reports

---

## 🔒 Security Features

1. **Password Security**
   - Passwords hashed using `password_hash()`
   - Bcrypt algorithm (PASSWORD_DEFAULT)

2. **SQL Injection Prevention**
   - `mysqli_real_escape_string()` for all inputs
   - Prepared statements where applicable

3. **XSS Prevention**
   - `htmlspecialchars()` for output
   - Input sanitization

4. **Session Security**
   - Session-based authentication
   - Session timeout
   - Secure session handling

5. **Input Validation**
   - Client-side validation (JavaScript)
   - Server-side validation (PHP)
   - Email format validation
   - Phone number validation

6. **File Upload Security**
   - File type validation
   - File size limits (5MB)
   - Unique filename generation
   - Allowed extensions check

---

## 🎨 UI/UX Highlights

### Animations
- Page load animations (AOS)
- Button hover effects
- Card transitions
- Smooth scrolling
- Loading spinners
- Alert fade-in/out

### Responsive Design
- Mobile-first approach
- Bootstrap 5 grid system
- Responsive navigation
- Touch-friendly buttons
- Optimized for all screen sizes

### Color Scheme
- Primary: Green (#1aab2a) - 1mg inspired
- Secondary: Purple gradient
- Accent: Orange (#ff6f61)
- Background: Light gray (#f5f5f5)
- Text: Dark gray (#333)

---

## 🚀 Future Enhancements

- Email notifications (SMTP integration)
- SMS notifications
- Real payment gateway integration
- Doctor consultation feature
- Lab test booking
- Medicine reminder system
- Wishlist functionality
- Product reviews and ratings
- Coupon/Promo code system
- Multi-language support

---

## 📝 Notes for Academic Submission

### Project Highlights
1. **Complete E-Commerce System** - All essential features implemented
2. **Security Best Practices** - Password hashing, SQL injection prevention
3. **Modern UI/UX** - Responsive design with animations
4. **AJAX Integration** - Dynamic search and cart operations
5. **Admin Panel** - Complete backend management system
6. **Well-Commented Code** - Easy to understand and maintain
7. **Database Design** - Normalized schema with proper relationships

### Documentation Included
- ✅ Complete source code
- ✅ Database SQL file
- ✅ Installation guide
- ✅ User manual
- ✅ Admin manual
- ✅ Code comments
- ✅ README file

---

## 👨‍💻 Developer Information

**Project Type**: Final Year Project  
**Technology**: PHP Core (No Framework)  
**Database**: MySQL  
**Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript, jQuery  
**Development Time**: Complete production-ready system  

---

## 📞 Support

For any queries or issues:
1. Check the installation guide
2. Verify database connection
3. Check Apache and MySQL services
4. Review error logs in XAMPP

---

## 📄 License

This project is developed for academic purposes as a final year project.

---

## 🙏 Acknowledgments

- Inspired by Tata 1mg website
- Bootstrap 5 for responsive design
- Font Awesome for icons
- AOS library for animations
- jQuery for AJAX functionality

---

**Happy Coding! 🚀**
