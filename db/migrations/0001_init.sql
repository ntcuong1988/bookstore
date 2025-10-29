-- description: Create core tables: users, books
-- Create core tables
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(20) UNIQUE NULL,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*
INSERT INTO users (username,password_hash,role) VALUES
('admin','$2y$10$6pnTglYHlWGE8v4Z7/V9Ee2FV/wDhec7nU5ba7.LX0Vy5Qqn5I3FG','admin');

INSERT INTO books (sku,title,author,price,stock,description) VALUES
('BK001','Clean Code','Robert C. Martin',320000,999,'Sổ tay thực hành viết mã sạch.'),
('BK002','The Pragmatic Programmer','Andrew Hunt & David Thomas',350000,999,'Tư duy thực dụng cho lập trình viên.'),
('BK003','Design Patterns','Erich Gamma et al.',420000,999,'Mẫu thiết kế hướng đối tượng.');
*/