-- 此 SQL 模式用於 MySQL 資料庫。
-- 本文件主要用於資料庫結構的參考與文件化。
-- 實際的資料表創建和資料插入應透過 Laravel 的遷移 (migrations) 和填充 (seeders) 執行。

-- 創建 properties 資料表
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    address VARCHAR(255) NOT NULL,
    area DECIMAL(8, 2) NOT NULL COMMENT '面積 (坪)', -- 面積使用 DECIMAL 類型，精度 8，小數點後 2 位
    price DECIMAL(12, 2) NOT NULL COMMENT '價格 (萬)', -- 價格使用 DECIMAL 類型，精度 12，小數點後 2 位
    description TEXT NULL,
    image_url VARCHAR(255) NULL,
    latitude DECIMAL(10, 7) NULL,
    longitude DECIMAL(10, 7) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 為 address 欄位添加索引以加快查詢速度
CREATE INDEX idx_address ON properties (address);

-- 為 area 和 price 欄位添加複合索引以加快篩選和排序速度
CREATE INDEX idx_area_price ON properties (area, price);

-- 創建 api_keys 資料表 (用於後端 API 金鑰驗證)
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    client_name VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 插入初始測試資料供示範 (注意：此處的插入語句與 PropertySeeder.php 中的保持一致)
INSERT INTO properties (address, area, price, description, image_url, latitude, longitude, created_at, updated_at) VALUES
('台北市大安區新生南路一段', 35.50, 3500.00, '捷運站旁，生活機能極佳，學區房。採光良好，社區管理完善。', 'https://placehold.co/800x600/E0F2F7/4299E1?text=建案一', 25.03396, 121.56447, NOW(), NOW()),
('台北市信義區忠孝東路五段', 50.25, 5800.00, '近台北101，視野開闊，豪宅首選。高樓層景觀，交通便利。', 'https://placehold.co/800x600/F0F8FF/4682B4?text=建案二', 25.03362, 121.56543, NOW(), NOW()),
('台北市中山區南京東路三段', 22.80, 2300.00, '小資首選，交通便利，獨立套房。新裝修，拎包入住。', 'https://placehold.co/800x600/FFFACD/DAA520?text=建案三', 25.05069, 121.54359, NOW(), NOW()),
('新北市板橋區文化路一段', 45.00, 2800.00, '生活機能完善，近百貨公司。公園旁，適合家庭居住。', 'https://placehold.co/800x600/E6E6FA/7B68EE?text=建案四', 25.01188, 121.46467, NOW(), NOW()),
('台北市士林區士林夜市旁', 28.10, 2950.00, '熱鬧商圈，生活機能便利，投資自住皆宜。', 'https://placehold.co/800x600/F5F5DC/8B4513?text=建案五', 25.08836, 121.52479, NOW(), NOW()),
('台北市文山區景美街', 30.00, 2500.00, '近景美夜市，生活機能便利，交通便捷。', 'https://placehold.co/800x600/DDA0DD/800080?text=建案六', 24.9958, 121.5385, NOW(), NOW()),
('新北市新莊區中正路', 40.00, 3200.00, '近捷運站，學區房，適合小家庭。', 'https://placehold.co/800x600/98FB98/006400?text=建案七', 25.0408, 121.4429, NOW(), NOW()),
('台北市南港區忠孝東路七段', 55.00, 6000.00, '高檔住宅區，環境清幽，視野極佳。', 'https://placehold.co/800x600/AFEEEE/008B8B?text=建案八', 25.0504, 121.6191, NOW(), NOW()),
('新北市淡水區學府路', 20.00, 1500.00, '近淡水大學，適合學生或單身人士。', 'https://placehold.co/800x600/F0E68C/B8860B?text=建案九', 25.1764, 121.4485, NOW(), NOW()),
('台北市大安區忠孝東路四段', 48.00, 5200.00, '精華地段，交通便利，商業繁榮。', 'https://placehold.co/800x600/ADD8E6/4682B4?text=建案十', 25.0425, 121.5542, NOW(), NOW());
