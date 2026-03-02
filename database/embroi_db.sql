    -- Create database
    CREATE DATABASE IF NOT EXISTS embroi_db;
    USE embroi_db;

    -- Users table
    CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Categories table
    CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Products table
    CREATE TABLE IF NOT EXISTS products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_id INT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    );

    -- Orders table
    CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
        shipping_address TEXT NOT NULL,
        contact_number VARCHAR(20) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );

    -- Order items table
    CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        size VARCHAR(20),
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    );

    -- Insert default admin user
    INSERT INTO users (username, password, email, role)
    VALUES ('admin', '$2y$10$8K1p/a0dR1xqM8K3h9z1eO9v1z1z1z1z1z1z1z1z1z1z1z1z1z1z1', 'admin@alinghera.com', 'admin');

    -- Insert categories
    INSERT INTO categories (name, description) VALUES
    ('Men', 'Embroidery products for men'),
    ('Women', 'Embroidery products for women'),
    ('Kids', 'Embroidery products for kids');

    -- Insert sample products
    INSERT INTO products (category_id, name, description, price, stock, image) VALUES
    (1, 'Men\'s Embroidered Polo', 'Handcrafted polo shirt with elegant embroidery design.', 1200.00, 25, 'assets/images/products/mens_polo.jpg'),
    (1, 'Men\'s Barong Tagalog', 'Traditional Filipino barong with intricate embroidery.', 2500.00, 15, 'assets/images/products/barong.jpg'),
    (2, 'Women\'s Embroidered Blouse', 'Beautiful blouse with floral embroidery pattern.', 950.00, 30, 'assets/images/products/womens_blouse.jpg'),
    (2, 'Embroidered Dress', 'Elegant dress with handcrafted embroidery details.', 1800.00, 20, 'assets/images/products/dress.jpg'),
    (3, 'Kids\' Embroidered Shirt', 'Cute shirt with colorful embroidered designs.', 650.00, 40, 'assets/images/products/kids_shirt.jpg');

    UPDATE users
    SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    WHERE username = 'admin';