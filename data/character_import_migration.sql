-- ============================================================================
-- Character Import Database Migration
-- Prepares database for importing JSON character data
-- ============================================================================

-- ============================================================================
-- SECTION 1: DISCIPLINES & BLOOD MAGIC SYSTEM
-- ============================================================================

-- Create disciplines master table if not exists
CREATE TABLE IF NOT EXISTS disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    parent_discipline VARCHAR(50) DEFAULT NULL,
    description TEXT,
    clan_specific BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_parent (parent_discipline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add parent_discipline to existing disciplines table
-- This allows Blood Magic paths to reference their parent (Thaumaturgy/Necromancy)
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE disciplines 
ADD COLUMN parent_discipline VARCHAR(50) DEFAULT NULL AFTER name,
ADD INDEX idx_parent (parent_discipline);

-- ============================================================================
-- SECTION 2: ABILITIES SYSTEM
-- ============================================================================

-- Create abilities master table with categories
CREATE TABLE IF NOT EXISTS abilities_master (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    category ENUM('Physical', 'Social', 'Mental', 'Optional') NOT NULL,
    requires_specialization BOOLEAN DEFAULT FALSE,
    description TEXT,
    example_specializations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Populate abilities_master with standard abilities
INSERT INTO abilities_master (name, category, requires_specialization) VALUES
-- Physical Abilities
('Athletics', 'Physical', FALSE),
('Brawl', 'Physical', FALSE),
('Dodge', 'Physical', FALSE),
('Firearms', 'Physical', FALSE),
('Melee', 'Physical', FALSE),
('Security', 'Physical', FALSE),
('Stealth', 'Physical', FALSE),
('Survival', 'Physical', FALSE),

-- Social Abilities
('Animal Ken', 'Social', FALSE),
('Empathy', 'Social', FALSE),
('Expression', 'Social', TRUE),
('Intimidation', 'Social', FALSE),
('Leadership', 'Social', FALSE),
('Subterfuge', 'Social', FALSE),
('Streetwise', 'Social', FALSE),
('Etiquette', 'Social', FALSE),
('Performance', 'Social', TRUE),

-- Mental Abilities
('Academics', 'Mental', TRUE),
('Computer', 'Mental', FALSE),
('Finance', 'Mental', FALSE),
('Investigation', 'Mental', FALSE),
('Law', 'Mental', FALSE),
('Linguistics', 'Mental', FALSE),
('Medicine', 'Mental', FALSE),
('Occult', 'Mental', FALSE),
('Politics', 'Mental', FALSE),
('Science', 'Mental', TRUE),

-- Optional Abilities
('Alertness', 'Optional', FALSE),
('Awareness', 'Optional', FALSE),
('Drive', 'Optional', FALSE),
('Crafts', 'Optional', TRUE),
('Firecraft', 'Optional', FALSE),

-- Additional abilities that may appear
('Art', 'Mental', TRUE),
('Area Knowledge', 'Mental', TRUE),
('Esoterica', 'Mental', TRUE),
('Pilot', 'Physical', TRUE)
ON DUPLICATE KEY UPDATE name=name;

-- Create character ability specializations table
CREATE TABLE IF NOT EXISTS character_ability_specializations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    ability_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(200) NOT NULL,
    is_primary BOOLEAN DEFAULT TRUE,
    xp_cost INT DEFAULT 0,
    grants_bonus BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character (character_id),
    INDEX idx_ability (ability_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- SECTION 3: CHARACTER TABLE ENHANCEMENTS
-- ============================================================================

-- Add equipment field if missing
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE characters 
ADD COLUMN equipment TEXT AFTER biography;

-- Add XP tracking fields
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE characters 
ADD COLUMN total_xp INT DEFAULT 30 AFTER equipment;

ALTER TABLE characters 
ADD COLUMN spent_xp INT DEFAULT 0 AFTER total_xp;

-- Add notes field for ST/gameplay notes (separate from biography)
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE characters 
ADD COLUMN notes TEXT AFTER spent_xp;

-- Add custom_data JSON field for flexible character-specific data
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE characters 
ADD COLUMN custom_data JSON AFTER notes;

-- ============================================================================
-- SECTION 3B: ABILITIES TABLE ENHANCEMENT
-- ============================================================================

-- Add level column to character_abilities if missing
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE character_abilities
ADD COLUMN level INT DEFAULT 1 AFTER specialization;

-- ============================================================================
-- SECTION 3C: TRAITS TABLE ENHANCEMENT
-- ============================================================================

-- Ensure character_traits has proper structure
-- Note: Will show error if columns already exist, which is safe to ignore

-- First, modify existing trait_type column to be the correct ENUM
ALTER TABLE character_traits
MODIFY COLUMN trait_type ENUM('positive', 'negative') DEFAULT 'positive';

-- Add trait_category if it doesn't exist (may fail if already there)
ALTER TABLE character_traits
ADD COLUMN trait_category ENUM('Physical', 'Social', 'Mental') NOT NULL AFTER trait_name;

-- ============================================================================
-- SECTION 4: MERITS & FLAWS ENHANCEMENTS
-- ============================================================================

-- Add category field to merits/flaws (Physical, Social, Mental, Supernatural)
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE character_merits_flaws 
ADD COLUMN category VARCHAR(50) AFTER type;

-- Add point_value column if missing
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE character_merits_flaws 
ADD COLUMN point_value INT NOT NULL DEFAULT 0 AFTER type;

-- ============================================================================
-- SECTION 5: RITUALS SYSTEM
-- ============================================================================

-- Add is_custom flag to character rituals
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE character_rituals 
ADD COLUMN is_custom BOOLEAN DEFAULT FALSE AFTER level;

-- Create rituals master table for validation (to be populated later)
CREATE TABLE IF NOT EXISTS rituals_master (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) UNIQUE NOT NULL,
    type ENUM('Thaumaturgy', 'Necromancy', 'Other') NOT NULL,
    level INT NOT NULL CHECK (level >= 0 AND level <= 5),
    description TEXT NOT NULL,
    system_text TEXT,
    requirements TEXT,
    source VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- SECTION 6: VERIFY EXISTING TABLES
-- ============================================================================

-- Ensure character_status table exists with blood pool fields
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add health_levels column if missing
-- Note: Will show error if column already exists, which is safe to ignore
ALTER TABLE character_status
ADD COLUMN health_levels VARCHAR(50) DEFAULT 'Healthy' AFTER character_id;

-- Add blood pool columns if missing
-- Note: Will show error if columns already exist, which is safe to ignore
ALTER TABLE character_status
ADD COLUMN blood_pool_current INT DEFAULT 10 AFTER health_levels;

ALTER TABLE character_status
ADD COLUMN blood_pool_maximum INT DEFAULT 10 AFTER blood_pool_current;

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================

-- Display success message
SELECT 'Character import database migration completed successfully!' AS Status;

-- Display summary of changes
SELECT 'Summary: Added parent_discipline, abilities_master, ability_specializations, notes, custom_data, ritual flags, and merit/flaw categories' AS Changes;

