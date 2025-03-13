CREATE TABLE IF NOT EXISTS withdraw_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    address VARCHAR(100) NOT NULL,
    amount DECIMAL(18,8) NOT NULL,
    txid VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL,
    timestamp INT NOT NULL
);


CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    value TEXT NOT NULL
);

-- Alapértelmezett beállítások beszúrása
INSERT INTO settings (name, value) VALUES 
('site_name', 'Mini Zero Faucet'),
('zerochain_api', 'npeJ7oPsCa'),
('zerochain_privatekey', 'L2BYY5hhVCuE9YJHBCrHCZTMzwNpdazN3Th4XhJALe9i8cicVjpK'),
('min_payout', '20000'),
('max_payout', '40000'),
('daily_limit', '0'),
('claim_interval', '30'),
('admin_username', 'admin'),
('admin_password', '$2y$10$W9hvVqLady2ivV791Nz9zOeqvjASvUTYxlcA9kW25EROz1RgjVsai');