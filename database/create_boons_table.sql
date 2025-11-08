-- Boons Table Creation
-- Tracks favors (boons) between NPCs, PCs, or future Agents
-- Created without foreign keys to work standalone until Agents table exists

CREATE TABLE IF NOT EXISTS boons (
  boon_id INT AUTO_INCREMENT PRIMARY KEY,
  giver_name VARCHAR(150) NOT NULL COMMENT 'Name of character giving the boon (text field until Agents table exists)',
  receiver_name VARCHAR(150) NOT NULL COMMENT 'Name of character receiving the boon (text field until Agents table exists)',
  -- Placeholders for future integration with agents/characters
  giver_ref INT NULL COMMENT 'Future: Reference to agents or characters table ID',
  receiver_ref INT NULL COMMENT 'Future: Reference to agents or characters table ID',
  boon_type ENUM('Trivial','Minor','Major','Life') NOT NULL DEFAULT 'Trivial',
  status ENUM('Owed','Called','Paid','Broken') NOT NULL DEFAULT 'Owed',
  description TEXT COMMENT 'Details about the boon',
  related_event VARCHAR(255) COMMENT 'Optional: Event or story context',
  date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
  date_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_giver_name (giver_name),
  INDEX idx_receiver_name (receiver_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TODO: When agents/characters table is available,
-- add foreign key constraints:
-- ALTER TABLE boons ADD CONSTRAINT fk_giver_ref FOREIGN KEY (giver_ref) REFERENCES agents(agent_id) ON DELETE SET NULL;
-- ALTER TABLE boons ADD CONSTRAINT fk_receiver_ref FOREIGN KEY (receiver_ref) REFERENCES agents(agent_id) ON DELETE SET NULL;

