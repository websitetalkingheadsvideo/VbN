-- Migration Script: Add Coterie and Relationships Fields
-- Based on analysis findings from JSON character files
-- Run this SQL script in phpMyAdmin or your database management tool

-- Create character_coteries table
CREATE TABLE IF NOT EXISTS character_coteries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    character_id INT NOT NULL,
    coterie_name VARCHAR(255) NOT NULL,
    coterie_type VARCHAR(50) DEFAULT NULL,
    role VARCHAR(100) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    source_field VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character_coteries (character_id),
    INDEX idx_coterie_name (coterie_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create character_relationships table
CREATE TABLE IF NOT EXISTS character_relationships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    character_id INT NOT NULL,
    related_character_id INT DEFAULT NULL,
    related_character_name VARCHAR(255) NOT NULL,
    relationship_type VARCHAR(50) NOT NULL,
    relationship_subtype VARCHAR(50) DEFAULT NULL,
    strength VARCHAR(100) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    source_field VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (related_character_id) REFERENCES characters(id) ON DELETE SET NULL,
    INDEX idx_character_relationships (character_id),
    INDEX idx_related_character (related_character_id),
    INDEX idx_relationship_type (relationship_type),
    INDEX idx_related_name (related_character_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add Coterie and Relationships JSON columns to characters table
-- Note: MySQL doesn't support "IF NOT EXISTS" for ALTER TABLE ADD COLUMN
-- If columns already exist, you'll get an error - that's okay, just comment out these lines

ALTER TABLE characters 
ADD COLUMN Coterie JSON DEFAULT NULL;

ALTER TABLE characters 
ADD COLUMN Relationships JSON DEFAULT NULL;

-- Verify tables were created
SELECT 'Migration completed successfully!' AS status;
SELECT COUNT(*) AS coteries_table_exists FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'character_coteries';
SELECT COUNT(*) AS relationships_table_exists FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'character_relationships';

