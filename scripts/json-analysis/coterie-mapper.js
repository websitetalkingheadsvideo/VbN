/**
 * Coterie Field Mapper
 * Searches for fields related to groups, teams, organizations, coteries, factions
 */

/**
 * Find all fields that could contain coterie/group data
 * @param {Object} data - Parsed JSON data
 * @param {string} path - Current path in the object
 * @returns {Array} Array of matched fields with metadata
 */
function findCoterieFields(data, path = '') {
  const coterieKeywords = [
    'team', 'group', 'squad', 'circle', 'clan', 'guild',
    'organization', 'company', 'faction', 'party', 'collective',
    'members', 'associates', 'companions', 'peers', 'cohort',
    'alliance', 'coalition', 'consortium', 'syndicate',
    'coterie', 'pack', 'brood', 'sect', 'covenant'
  ];
  
  const matches = [];
  
  if (typeof data !== 'object' || data === null) {
    return matches;
  }
  
  for (const [key, value] of Object.entries(data)) {
    const currentPath = path ? `${path}.${key}` : key;
    const lowerKey = key.toLowerCase();
    
    // Check if field name matches coterie keywords
    if (coterieKeywords.some(keyword => lowerKey.includes(keyword))) {
      matches.push({
        path: currentPath,
        fieldName: key,
        dataType: typeof value,
        isArray: Array.isArray(value),
        sample: getSample(value),
        source: 'field_name_match'
      });
    }
    
    // Check biography text for coterie mentions
    if (key.toLowerCase() === 'biography' && typeof value === 'string') {
      const biographyMatches = findCoterieInText(value);
      if (biographyMatches.length > 0) {
        matches.push({
          path: currentPath,
          fieldName: key,
          dataType: 'text',
          matches: biographyMatches,
          source: 'biography_text'
        });
      }
    }
    
    // Check backgroundDetails for group affiliations
    if (key.toLowerCase() === 'backgrounddetails' && typeof value === 'object') {
      const backgroundMatches = findCoterieInBackgrounds(value);
      if (backgroundMatches.length > 0) {
        matches.push({
          path: currentPath,
          fieldName: key,
          dataType: 'object',
          matches: backgroundMatches,
          source: 'background_details'
        });
      }
    }
    
    // Check if field contains array of objects (potential group members)
    if (Array.isArray(value) && value.length > 0 && typeof value[0] === 'object') {
      // Could be a list of group members
      matches.push({
        path: currentPath,
        fieldName: key,
        dataType: 'array_of_objects',
        count: value.length,
        sample: value.slice(0, 3),
        source: 'array_of_objects'
      });
    }
    
    // Recurse into nested objects
    if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
      matches.push(...findCoterieFields(value, currentPath));
    }
  }
  
  return matches;
}

/**
 * Search biography text for coterie/group mentions
 * @param {string} text - Biography text
 * @returns {Array} Array of matches with context
 */
function findCoterieInText(text) {
  const matches = [];
  const lowerText = text.toLowerCase();
  
  // Patterns to look for
  const patterns = [
    { keyword: 'anarch', type: 'faction' },
    { keyword: 'coterie', type: 'coterie' },
    { keyword: 'pack', type: 'pack' },
    { keyword: 'faction', type: 'faction' },
    { keyword: 'sect', type: 'sect' },
    { keyword: 'clan', type: 'clan' },
    { keyword: 'organization', type: 'organization' },
    { keyword: 'serves as', type: 'role_in_group' },
    { keyword: 'member of', type: 'membership' },
    { keyword: 'part of', type: 'membership' },
    { keyword: 'de facto', type: 'informal_group' }
  ];
  
  for (const pattern of patterns) {
    if (lowerText.includes(pattern.keyword)) {
      // Extract context around the match
      const index = lowerText.indexOf(pattern.keyword);
      const start = Math.max(0, index - 50);
      const end = Math.min(text.length, index + pattern.keyword.length + 50);
      const context = text.substring(start, end).trim();
      
      matches.push({
        keyword: pattern.keyword,
        type: pattern.type,
        context: context
      });
    }
  }
  
  return matches;
}

/**
 * Search backgroundDetails for group affiliations
 * @param {Object} backgrounds - backgroundDetails object
 * @returns {Array} Array of matches
 */
function findCoterieInBackgrounds(backgrounds) {
  const matches = [];
  
  // Check Status field which may indicate coterie membership
  if (backgrounds.Status) {
    matches.push({
      field: 'Status',
      value: backgrounds.Status,
      type: 'status_description',
      potentialCoterie: true
    });
  }
  
  // Check other fields for group mentions
  for (const [key, value] of Object.entries(backgrounds)) {
    if (typeof value === 'string') {
      const lowerValue = value.toLowerCase();
      if (lowerValue.includes('faction') || 
          lowerValue.includes('coterie') || 
          lowerValue.includes('anarch') ||
          lowerValue.includes('talon') ||
          lowerValue.includes('harpy') ||
          lowerValue.includes('primogen')) {
        matches.push({
          field: key,
          value: value,
          type: 'group_mention',
          context: value
        });
      }
    }
  }
  
  return matches;
}

/**
 * Get sample value
 */
function getSample(value) {
  if (typeof value === 'string') {
    return value.length > 100 ? value.substring(0, 100) + '...' : value;
  }
  if (Array.isArray(value)) {
    return value.slice(0, 3);
  }
  return value;
}

module.exports = {
  findCoterieFields
};

