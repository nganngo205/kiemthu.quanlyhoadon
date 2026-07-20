CREATE DATABASE IF NOT EXISTS quanlyhoadon DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quanlyhoadon;

CREATE TABLE IF NOT EXISTS product (
  id VARCHAR(50) PRIMARY KEY,
  barcode VARCHAR(8) DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  price DOUBLE NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  barcode_seq INT AUTO_INCREMENT,
  UNIQUE KEY barcode_seq_unique (barcode_seq),
  UNIQUE KEY barcode_unique (barcode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoice (
  id VARCHAR(64) PRIMARY KEY,
  session_id VARCHAR(100),
  total DOUBLE NOT NULL,
  method VARCHAR(20),
  status VARCHAR(20),
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cart (
  id VARCHAR(64) PRIMARY KEY,
  status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cart_item (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cart_id VARCHAR(64),
  product_id VARCHAR(50),
  quantity INT NOT NULL,
  price DOUBLE NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoice_item (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id VARCHAR(64),
  product_id VARCHAR(50),
  name VARCHAR(100),
  unit_price DOUBLE,
  quantity INT,
  subtotal DOUBLE,
  FOREIGN KEY (invoice_id) REFERENCES invoice(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



ALTER TABLE product AUTO_INCREMENT = 1;


INSERT INTO product (id, barcode, name, price, quantity) VALUES
('SP000001', NULL, 'Gạo ST25 5kg', 145000, 50),
('SP000002', NULL, 'Nước mắm Nam Ngư 500ml', 25000, 200),
('SP000003', NULL, 'Dầu ăn Tường An 1L', 55000, 150);


UPDATE product SET barcode = LPAD(barcode_seq, 8, '0') WHERE barcode IS NULL;
