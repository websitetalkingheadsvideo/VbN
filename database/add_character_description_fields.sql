-- Character Description Fields Migration Script
-- Adds appearance, biography, and notes columns to characters table
-- Following MySQL best practices with utf8mb4_unicode_ci collation
-- 
-- Created: 2025-01-08
-- Purpose: Support character description tab in character creation form
--
-- This script is idempotent - safe to run multiple times
-- Uses IF NOT EXISTS to prevent errors if columns already exist

-- Start transaction for atomic operation
START TRANSACTION;

-- Check and add appearance column
-- Physical description of the character
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'characters' 
    AND COLUMN_NAME = 'appearance'
);

SET @sql_appearance = IF(@col_exists = 0,
    'ALTER TABLE characters ADD COLUMN appearance TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT ''Physical appearance description of the character'' AFTER biography',
    'SELECT ''Column appearance already exists'' AS message'
);

PREPARE stmt FROM @sql_appearance;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add notes column  
-- Additional character notes and details
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'characters' 
    AND COLUMN_NAME = 'notes'
);

SET @sql_notes = IF(@col_exists = 0,
    'ALTER TABLE characters ADD COLUMN notes TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT ''Additional character notes, player notes, or storyteller notes'' AFTER appearance',
    'SELECT ''Column notes already exists'' AS message'
);

PREPARE stmt FROM @sql_notes;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify biography column exists and has proper character set
-- Update biography column if it exists but doesn't have utf8mb4 collation
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'characters' 
    AND COLUMN_NAME = 'biography'
);

SET @col_collation = (
    SELECT COLLATION_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'characters' 
    AND COLUMN_NAME = 'biography'
    LIMIT 1
);

SET @sql_biography = IF(@col_exists > 0 AND @col_collation != 'utf8mb4_unicode_ci',
    'ALTER TABLE characters MODIFY COLUMN biography TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT ''Character background story and history''',
    'SELECT ''Column biography already has correct collation or does not exist'' AS message'
);

PREPARE stmt FROM @sql_biography;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Optional: Add FULLTEXT indexes for search functionality
-- Uncomment these if you need to search biography and notes content
-- Note: FULLTEXT indexes require MyISAM or InnoDB with innodb_ft_min_token_size configured

-- CREATE FULLTEXT INDEX idx_characters_biography_ft ON characters(biography);
-- CREATE FULLTEXT INDEX idx_characters_notes_ft ON characters(notes);

-- Commit transaction
COMMIT;

-- Verify the changes
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_SET_NAME,
    COLLATION_NAME,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'characters'
AND COLUMN_NAME IN ('appearance', 'biography', 'notes')
ORDER BY ORDINAL_POSITION;

