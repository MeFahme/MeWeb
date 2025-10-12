create database garage;
use garage;

DROP TABLE IF EXISTS product_models;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS models;
DROP TABLE IF EXISTS brands;

CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    logo VARCHAR(255)
);


CREATE TABLE models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255),  -- Kolom untuk gambar model
    FOREIGN KEY (brand_id) 
        REFERENCES brands(id) 
        ON DELETE CASCADE
);


CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10, 0) NOT NULL,
    image VARCHAR(255)
);

CREATE TABLE product_models (
    product_id INT NOT NULL,
    model_id INT NOT NULL,
    
    PRIMARY KEY (product_id, model_id),
    
    FOREIGN KEY (product_id) 
        REFERENCES products(id) 
        ON DELETE CASCADE,
        
    FOREIGN KEY (model_id) 
        REFERENCES models(id) 
        ON DELETE CASCADE
);-- Hapus tabel lama jika sudah ada
DROP TABLE IF EXISTS product_models;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS models;
DROP TABLE IF EXISTS brands;

-- 1. Tabel Brands (Merek Motor)
-- Kolom 'logo' akan menyimpan path gambar logo yang diupload.
CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    logo VARCHAR(255) -- Kolom untuk menyimpan path logo
);

-- 2. Tabel Models (Model Motor: Vario, NMAX)
-- Kolom 'image' ditambahkan untuk gambar model.
-- brand_id terhubung ke brands.id.
CREATE TABLE models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    image VARCHAR(255),  -- Kolom untuk gambar model
    FOREIGN KEY (brand_id) 
        REFERENCES brands(id) 
        ON DELETE CASCADE
);

-- 3. Tabel Products (Produk yang dijual: Piston, Filter)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10, 0) NOT NULL,
    image VARCHAR(255) -- Kolom untuk gambar produk
);

-- 4. Tabel Relasi product_models (Menghubungkan Produk ke Model)
CREATE TABLE product_models (
    product_id INT NOT NULL,
    model_id INT NOT NULL,
    
    PRIMARY KEY (product_id, model_id),
    
    FOREIGN KEY (product_id) 
        REFERENCES products(id) 
        ON DELETE CASCADE,
        
    FOREIGN KEY (model_id) 
        REFERENCES models(id) 
        ON DELETE CASCADE
);
