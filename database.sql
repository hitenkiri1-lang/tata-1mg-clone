-- =====================================================
-- TATA 1MG CLONE - DATABASE SCHEMA
-- Final Year Project
-- =====================================================

CREATE DATABASE IF NOT EXISTS tata_1mg_clone;
USE tata_1mg_clone;

-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    pincode VARCHAR(10),
    security_question VARCHAR(255),
    security_answer VARCHAR(255),
    status ENUM('active', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- ADMINS TABLE
-- =====================================================
CREATE TABLE admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- CATEGORIES TABLE
-- =====================================================
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    category_image VARCHAR(255),
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- MEDICINES TABLE
-- =====================================================
CREATE TABLE medicines (
    medicine_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    medicine_name VARCHAR(200) NOT NULL,
    manufacturer VARCHAR(100),
    description TEXT,
    composition TEXT,
    uses TEXT,
    side_effects TEXT,
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2),
    stock_quantity INT DEFAULT 0,
    medicine_image VARCHAR(255),
    prescription_required ENUM('yes', 'no') DEFAULT 'no',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- =====================================================
-- CART TABLE
-- =====================================================
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id) ON DELETE CASCADE
);

-- =====================================================
-- ORDERS TABLE
-- =====================================================
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cod', 'online') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    order_status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(50),
    shipping_state VARCHAR(50),
    shipping_pincode VARCHAR(10),
    shipping_phone VARCHAR(15),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =====================================================
-- ORDER ITEMS TABLE
-- =====================================================
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(medicine_id) ON DELETE CASCADE
);

-- =====================================================
-- PAYMENTS TABLE
-- =====================================================
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    transaction_id VARCHAR(100),
    payment_method ENUM('cod', 'online') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

-- =====================================================
-- INSERT DEFAULT ADMIN
-- Password: admin123 (hashed)
-- =====================================================
INSERT INTO admins (username, email, password, full_name) VALUES
('admin', 'admin@1mg.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- =====================================================
-- INSERT SAMPLE CATEGORIES
-- =====================================================
INSERT INTO categories (category_name, category_image, description) VALUES
('Pain Relief', 'pain-relief.jpg', 'Medicines for pain management and relief'),
('Vitamins & Supplements', 'vitamins.jpg', 'Essential vitamins and dietary supplements'),
('Diabetes Care', 'diabetes.jpg', 'Medicines and devices for diabetes management'),
('Heart Care', 'heart-care.jpg', 'Cardiovascular health medicines'),
('Cold & Cough', 'cold-cough.jpg', 'Medicines for cold, cough and flu'),
('Digestive Health', 'digestive.jpg', 'Medicines for digestive system'),
('Skin Care', 'skin-care.jpg', 'Dermatology and skin care products'),
('First Aid', 'first-aid.jpg', 'First aid essentials and emergency medicines');

-- =====================================================
-- INSERT SAMPLE MEDICINES
-- =====================================================
INSERT INTO medicines (category_id, medicine_name, manufacturer, description, composition, uses, side_effects, price, discount_price, stock_quantity, medicine_image, prescription_required) VALUES
(1, 'Dolo 650mg Tablet', 'Micro Labs Ltd', 'Effective pain relief and fever reducer', 'Paracetamol 650mg', 'Used for fever, headache, body pain, toothache', 'Nausea, allergic reactions (rare)', 30.00, 27.00, 500, 'dolo-650.jpg', 'no'),
(1, 'Combiflam Tablet', 'Sanofi India Ltd', 'Pain relief and anti-inflammatory', 'Ibuprofen 400mg + Paracetamol 325mg', 'Body pain, fever, inflammation', 'Stomach upset, dizziness', 25.00, 22.50, 300, 'combiflam.jpg', 'no'),
(2, 'HealthVit Vitamin C 1000mg', 'HealthVit', 'Immunity booster vitamin C supplement', 'Vitamin C 1000mg', 'Boosts immunity, antioxidant', 'Mild stomach upset', 299.00, 249.00, 200, 'vitamin-c.jpg', 'no'),
(2, 'Neurobion Forte Tablet', 'Merck Ltd', 'Vitamin B complex supplement', 'Vitamin B1, B6, B12', 'Nerve health, energy metabolism', 'Rare allergic reactions', 35.00, 32.00, 400, 'neurobion.jpg', 'no'),
(3, 'Glycomet 500mg Tablet', 'USV Ltd', 'Blood sugar control medicine', 'Metformin 500mg', 'Type 2 diabetes management', 'Nausea, diarrhea, stomach pain', 25.00, 23.00, 250, 'glycomet.jpg', 'yes'),
(4, 'Ecosprin 75mg Tablet', 'USV Ltd', 'Blood thinner for heart health', 'Aspirin 75mg', 'Prevents heart attack and stroke', 'Stomach irritation, bleeding risk', 15.00, 13.50, 600, 'ecosprin.jpg', 'yes'),
(5, 'Vicks Vaporub 50ml', 'Procter & Gamble', 'Topical ointment for cold relief', 'Camphor, Menthol, Eucalyptus oil', 'Cold, cough, nasal congestion', 'Skin irritation (rare)', 150.00, 135.00, 350, 'vicks.jpg', 'no'),
(6, 'Digene Gel', 'Abbott', 'Antacid for acidity and gas', 'Magnesium Hydroxide, Aluminium Hydroxide', 'Acidity, heartburn, gas', 'Constipation, diarrhea', 120.00, 108.00, 400, 'digene.jpg', 'no'),
(7, 'Lacto Calamine Lotion', 'Piramal Healthcare', 'Soothing skin lotion', 'Calamine, Zinc Oxide', 'Skin irritation, rashes, sunburn', 'Rare allergic reactions', 180.00, 162.00, 300, 'lacto-calamine.jpg', 'no'),
(8, 'Dettol Antiseptic Liquid 500ml', 'Reckitt Benckiser', 'Antiseptic disinfectant', 'Chloroxylenol', 'Wound cleaning, disinfection', 'Skin irritation if not diluted', 200.00, 180.00, 250, 'dettol.jpg', 'no');

-- =====================================================
-- INSERT SAMPLE USER (for testing)
-- Email: user@test.com
-- Password: user123
-- =====================================================
INSERT INTO users (full_name, email, phone, password, address, city, state, pincode, security_question, security_answer) VALUES
('Test User', 'user@test.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Test Street', 'Mumbai', 'Maharashtra', '400001', 'What is your favorite color?', 'blue');
