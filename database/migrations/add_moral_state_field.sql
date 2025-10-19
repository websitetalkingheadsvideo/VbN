-- Add current_moral_state field to character_morality table
ALTER TABLE character_morality 
ADD COLUMN current_moral_state VARCHAR(50) DEFAULT 'Conflicted';

-- Update existing records with default moral states based on humanity level
UPDATE character_morality 
SET current_moral_state = CASE 
    WHEN humanity >= 8 THEN 'Compassionate'
    WHEN humanity >= 5 THEN 'Conflicted'
    WHEN humanity >= 3 THEN 'Hardened'
    ELSE 'Callous'
END
WHERE current_moral_state = 'Conflicted';
