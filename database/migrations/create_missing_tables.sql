-- Create missing character_disciplines table
CREATE TABLE IF NOT EXISTS character_disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    discipline_name VARCHAR(100) NOT NULL,
    level INT NOT NULL DEFAULT 1,
    xp_cost INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
);
