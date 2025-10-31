/**
 * Data Transformer Module
 * Transforms extracted data into standardized format for database insertion
 */

/**
 * Transform coterie data to database format
 * @param {Array} coteries - Extracted coterie data
 * @param {Object} options - Transformation options
 * @returns {Array} Transformed coterie data
 */
function transformCoteriesForDatabase(coteries, options = {}) {
  const format = options.format || 'json'; // 'json' or 'table'
  
  if (format === 'json') {
    return coteries.map(coterie => ({
      name: coterie.name || 'Unknown',
      type: coterie.type || 'group',
      role: coterie.role || null,
      description: coterie.description || null,
      source: coterie.source || 'unknown'
    }));
  } else {
    // Format for table insertion
    return coteries.map(coterie => ({
      coterie_name: coterie.name || 'Unknown',
      coterie_type: coterie.type || 'group',
      role: coterie.role || null,
      description: coterie.description || null,
      source_field: coterie.source || 'unknown'
    }));
  }
}

/**
 * Transform relationship data to database format
 * @param {Array} relationships - Extracted relationship data
 * @param {Object} options - Transformation options
 * @returns {Array} Transformed relationship data
 */
function transformRelationshipsForDatabase(relationships, options = {}) {
  const format = options.format || 'json'; // 'json' or 'table'
  
  if (format === 'json') {
    return relationships.map(rel => ({
      character_name: rel.character_name || 'Unknown',
      character_id: rel.character_id || null,
      type: rel.type || 'unknown',
      subtype: rel.subtype || null,
      strength: rel.strength || null,
      description: rel.description || null,
      source: rel.source || 'unknown'
    }));
  } else {
    // Format for table insertion
    return relationships.map(rel => ({
      related_character_name: rel.character_name || 'Unknown',
      related_character_id: rel.character_id || null,
      relationship_type: rel.type || 'unknown',
      relationship_subtype: rel.subtype || null,
      strength: rel.strength || null,
      description: rel.description || null,
      source_field: rel.source || 'unknown'
    }));
  }
}

/**
 * Clean and normalize character names for matching
 * @param {string} name - Character name
 * @returns {string} Normalized name
 */
function normalizeCharacterName(name) {
  if (!name) return '';
  
  return name
    .trim()
    .replace(/\s+/g, ' ')
    .replace(/\([^)]+\)/g, '') // Remove parenthetical descriptions
    .trim();
}

/**
 * Attempt to link relationship to existing character in database
 * @param {string} characterName - Name to match
 * @param {Array} existingCharacters - Array of {id, character_name} objects
 * @returns {number|null} Character ID if match found
 */
function linkToExistingCharacter(characterName, existingCharacters) {
  const normalized = normalizeCharacterName(characterName).toLowerCase();
  
  for (const char of existingCharacters) {
    const charNormalized = normalizeCharacterName(char.character_name).toLowerCase();
    
    // Exact match
    if (charNormalized === normalized) {
      return char.id;
    }
    
    // Partial match (e.g., "Sebastian" matches "Sebastian (twin)")
    if (normalized.includes(charNormalized) || charNormalized.includes(normalized)) {
      return char.id;
    }
    
    // Match last name or first name if they're common
    const nameParts = normalized.split(' ');
    const charParts = charNormalized.split(' ');
    
    if (nameParts.length > 1 && charParts.length > 1) {
      if (nameParts[nameParts.length - 1] === charParts[charParts.length - 1]) {
        return char.id;
      }
    }
  }
  
  return null;
}

module.exports = {
  transformCoteriesForDatabase,
  transformRelationshipsForDatabase,
  normalizeCharacterName,
  linkToExistingCharacter
};

