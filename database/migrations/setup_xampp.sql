-- LOTN Character Creator - Database Setup Script
-- Run this in phpMyAdmin or MySQL command line after creating the database

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS lotn_characters;
USE lotn_characters;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Create characters table
CREATE TABLE IF NOT EXISTS characters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    character_name VARCHAR(100) NOT NULL,
    player_name VARCHAR(100) NOT NULL,
    chronicle VARCHAR(100) DEFAULT 'Valley by Night',
    nature VARCHAR(50) NOT NULL,
    demeanor VARCHAR(50) NOT NULL,
    concept VARCHAR(200) NOT NULL,
    clan VARCHAR(50) NOT NULL,
    generation INT NOT NULL,
    sire VARCHAR(100),
    pc BOOLEAN DEFAULT TRUE,
    biography TEXT,
    equipment TEXT,
    total_xp INT DEFAULT 30,
    spent_xp INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create character_traits table
CREATE TABLE IF NOT EXISTS character_traits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    trait_name VARCHAR(100) NOT NULL,
    trait_category ENUM('Physical', 'Social', 'Mental') NOT NULL,
    trait_type ENUM('positive', 'negative') DEFAULT 'positive',
    xp_cost INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Create character_abilities table
CREATE TABLE IF NOT EXISTS character_abilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    ability_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100),
    level INT DEFAULT 1,
    xp_cost INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Create disciplines table (master list of all disciplines)
CREATE TABLE IF NOT EXISTS disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    category ENUM('Clan', 'BloodSorcery', 'Advanced') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create discipline_powers table (individual powers for each discipline)
CREATE TABLE IF NOT EXISTS discipline_powers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discipline_id INT NOT NULL,
    level INT NOT NULL CHECK (level >= 1 AND level <= 5),
    name VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (discipline_id) REFERENCES disciplines(id) ON DELETE CASCADE,
    UNIQUE KEY unique_discipline_level (discipline_id, level)
);

-- Create clans table (master list of all clans)
CREATE TABLE IF NOT EXISTS clans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    weakness TEXT,
    theme TEXT,
    playstyle TEXT,
    availability ENUM('PC Available', 'Admin Approval') DEFAULT 'PC Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create clan_disciplines table (which disciplines each clan can access)
CREATE TABLE IF NOT EXISTS clan_disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clan_id INT NOT NULL,
    discipline_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (clan_id) REFERENCES clans(id) ON DELETE CASCADE,
    FOREIGN KEY (discipline_id) REFERENCES disciplines(id) ON DELETE CASCADE,
    UNIQUE KEY unique_clan_discipline (clan_id, discipline_id)
);

-- Create character_discipline_powers table (replaces old character_disciplines)
CREATE TABLE IF NOT EXISTS character_discipline_powers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    discipline_id INT NOT NULL,
    power_id INT NOT NULL,
    xp_cost INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (discipline_id) REFERENCES disciplines(id) ON DELETE CASCADE,
    FOREIGN KEY (power_id) REFERENCES discipline_powers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_character_power (character_id, power_id)
);

-- Create character_backgrounds table
CREATE TABLE IF NOT EXISTS character_backgrounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    background_name VARCHAR(100) NOT NULL,
    level INT NOT NULL CHECK (level >= 1 AND level <= 5),
    details TEXT,
    xp_cost INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Create character_merits_flaws table
CREATE TABLE IF NOT EXISTS character_merits_flaws (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('merit', 'flaw') NOT NULL,
    point_value INT NOT NULL,
    description TEXT,
    xp_bonus INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Create character_morality table
CREATE TABLE IF NOT EXISTS character_morality (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    path_name VARCHAR(50) DEFAULT 'Humanity',
    path_rating INT DEFAULT 7 CHECK (path_rating >= 1 AND path_rating <= 10),
    conscience INT DEFAULT 1 CHECK (conscience >= 1 AND conscience <= 5),
    self_control INT DEFAULT 1 CHECK (self_control >= 1 AND self_control <= 5),
    courage INT DEFAULT 1 CHECK (courage >= 1 AND courage <= 5),
    willpower_permanent INT DEFAULT 5 CHECK (willpower_permanent >= 1 AND willpower_permanent <= 10),
    willpower_current INT DEFAULT 5,
    humanity INT DEFAULT 7 CHECK (humanity >= 1 AND humanity <= 10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Create character_derangements table
CREATE TABLE IF NOT EXISTS character_derangements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    derangement_name VARCHAR(100) NOT NULL,
    description TEXT,
    severity ENUM('mild', 'moderate', 'severe') DEFAULT 'mild',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Create character_equipment table
CREATE TABLE IF NOT EXISTS character_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    item_name VARCHAR(200) NOT NULL,
    item_type VARCHAR(50),
    description TEXT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Create character_influences table
CREATE TABLE IF NOT EXISTS character_influences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    influence_type VARCHAR(100) NOT NULL,
    level INT NOT NULL CHECK (level >= 1 AND level <= 5),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Create character_rituals table
CREATE TABLE IF NOT EXISTS character_rituals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    ritual_name VARCHAR(200) NOT NULL,
    ritual_type ENUM('Thaumaturgy', 'Necromancy', 'Other') NOT NULL,
    level INT NOT NULL CHECK (level >= 1 AND level <= 5),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Create character_status table
CREATE TABLE IF NOT EXISTS character_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    sect_status VARCHAR(100),
    clan_status VARCHAR(100),
    city_status VARCHAR(100),
    health_levels VARCHAR(50) DEFAULT 'Healthy',
    blood_pool_current INT DEFAULT 10,
    blood_pool_maximum INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT IGNORE INTO users (username, password, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'admin');

-- Insert test user
INSERT IGNORE INTO users (username, password, email, role) 
VALUES ('testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'test@example.com', 'user');

-- Show completion message
SELECT 'Database setup completed successfully!' as message;
SELECT 'Default admin login: admin / password' as admin_info;
SELECT 'Test user login: testuser / password' as test_info;
