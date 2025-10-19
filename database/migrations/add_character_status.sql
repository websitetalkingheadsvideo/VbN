-- Add status field to characters table
-- Run this in phpMyAdmin or via migration script

ALTER TABLE `characters` 
ADD COLUMN `status` ENUM('draft', 'finalized', 'active', 'dead', 'missing') 
NOT NULL DEFAULT 'draft' 
AFTER `finalized`;

-- Update existing characters based on old finalized field (if it exists)
-- If finalized = 1, set status to 'finalized', otherwise 'draft'
UPDATE `characters` 
SET `status` = CASE 
    WHEN `finalized` = 1 THEN 'finalized' 
    ELSE 'draft' 
END;

-- Optional: Drop old finalized column after migration
-- ALTER TABLE `characters` DROP COLUMN `finalized`;

