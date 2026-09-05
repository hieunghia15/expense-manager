USE expense_manager;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample admin account (Password: 123admiN!)
INSERT INTO users (email, password, name)
VALUES ('admin@example.com', '$2y$12$lx0pcmkLjqr7cBV49vZmg.3UMm.fSfqUE68YZ.3tgzXcYZJBX.jl6', 'System Admin')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

