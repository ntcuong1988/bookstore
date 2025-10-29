-- description: Seed demo data (users + books)
INSERT INTO users (username,password_hash,role) VALUES
('admin','$2y$10$6pnTglYHlWGE8v4Z7/V9Ee2FV/wDhec7nU5ba7.LX0Vy5Qqn5I3FG','admin');

INSERT INTO books (sku,title,author,price,stock,description) VALUES
('BK001','Clean Code','Robert C. Martin',320000,999,'Sổ tay thực hành viết mã sạch.'),
('BK002','The Pragmatic Programmer','Andrew Hunt & David Thomas',350000,999,'Tư duy thực dụng cho lập trình viên.'),
('BK003','Design Patterns','Erich Gamma et al.',420000,999,'Mẫu thiết kế hướng đối tượng.');
