/**
 * Relationships Field Mapper
 * Searches for fields related to connections, associations, family, friends, mentors, etc.
 */

/**
 * Find all fields that could contain relationship data
 * @param {Object} data - Parsed JSON data
 * @param {string} path - Current path in the object
 * @returns {Array} Array of matched fields with metadata
 */
function findRelationshipFields(data, path = '') {
  const relationshipKeywords = [
    'relationship', 'connection', 'association', 'link',
    'family', 'relative', 'spouse', 'partner', 'child', 'parent',
    'friend', 'colleague', 'contact', 'network',
    'mentor', 'mentee', 'supervisor', 'subordinate',
    'collaborator', 'coworker', 'teammate',
    'enemy', 'rival', 'opponent', 'competitor',
    'ally', 'allies', 'sire', 'childe', 'brother', 'sister',
    'twin', 'sibling'
  ];
  
  const matches = [];
  
  if (typeof data !== 'object' || data === null) {
    return matches;
  }
  
  for (const [key, value] of Object.entries(data)) {
    const currentPath = path ? `${path}.${key}` : key;
    const lowerKey = key.toLowerCase();
    
    // Check if field name matches relationship keywords
    if (relationshipKeywords.some(keyword => lowerKey.includes(keyword))) {
      matches.push({
        path: currentPath,
        fieldName: key,
        dataType: typeof value,
        isArray: Array.isArray(value),
        sample: getSample(value),
        relationshipType: inferRelationshipType(key, value),
        source: 'field_name_match'
      });
    }
    
    // Special handling for sire field (parent-child relationship)
    if (key.toLowerCase() === 'sire' && typeof value === 'string') {
      matches.push({
        path: currentPath,
        fieldName: key,
        dataType: 'string',
        sample: value,
        relationshipType: 'sire',
        source: 'explicit_field'
      });
    }
    
    // Check backgroundDetails for relationship data
    if (key.toLowerCase() === 'backgrounddetails' && typeof value === 'object') {
      const relationshipMatches = findRelationshipsInBackgrounds(value);
      if (relationshipMatches.length > 0) {
        matches.push({
          path: currentPath,
          fieldName: key,
          dataType: 'object',
          matches: relationshipMatches,
          source: 'background_details'
        });
      }
    }
    
    // Check merits_flaws for relationship-based merits
    if (key.toLowerCase() === 'merits_flaws' && Array.isArray(value)) {
      const meritMatches = findRelationshipsInMerits(value);
      if (meritMatches.length > 0) {
        matches.push({
          path: currentPath,
          fieldName: key,
          dataType: 'array',
          matches: meritMatches,
          source: 'merits_flaws'
        });
      }
    }
    
    // Check biography text for relationship mentions
    if (key.toLowerCase() === 'biography' && typeof value === 'string') {
      const biographyMatches = findRelationshipsInText(value);
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
    
    // Recurse into nested objects
    if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
      matches.push(...findRelationshipFields(value, currentPath));
    }
  }
  
  return matches;
}

/**
 * Extract relationships from backgroundDetails
 * @param {Object} backgrounds - backgroundDetails object
 * @returns {Array} Array of relationship matches
 */
function findRelationshipsInBackgrounds(backgrounds) {
  const matches = [];
  
  // Check Mentor, Allies, Contacts fields
  const relationshipFields = ['Mentor', 'Allies', 'Contacts'];
  
  for (const field of relationshipFields) {
    if (backgrounds[field] && typeof backgrounds[field] === 'string') {
      const relationships = parseRelationshipString(backgrounds[field], field.toLowerCase());
      matches.push(...relationships);
    }
  }
  
  return matches;
}

/**
 * Parse a relationship string from backgroundDetails
 * @param {string} text - Relationship description text
 * @param {string} baseType - Base relationship type (mentor, allies, contacts)
 * @returns {Array} Array of parsed relationships
 */
function parseRelationshipString(text, baseType) {
  const relationships = [];
  
  // Try to extract names and relationship types from text
  // Example: "Twin brother Sebastian (inseparable), Toreador Primogen (sire)"
  
  // Split by comma to get individual relationships
  const parts = text.split(',').map(p => p.trim());
  
  for (const part of parts) {
    // Look for patterns like "Name (description)" or "Type Name"
    const match = part.match(/(?:([^(]+)\s*\(([^)]+)\)|([^()]+))/);
    
    if (match) {
      const fullText = match[0];
      
      // Extract relationship type indicators
      let relationshipType = baseType;
      const lowerText = fullText.toLowerCase();
      
      if (lowerText.includes('twin')) relationshipType = 'twin';
      else if (lowerText.includes('brother')) relationshipType = 'sibling';
      else if (lowerText.includes('sister')) relationshipType = 'sibling';
      else if (lowerText.includes('sire')) relationshipType = 'sire';
      else if (lowerText.includes('mentor')) relationshipType = 'mentor';
      else if (lowerText.includes('ally')) relationshipType = 'ally';
      else if (lowerText.includes('contact')) relationshipType = 'contact';
      
      // Try to extract name (look for capitalized words that might be names)
      const nameMatch = fullText.match(/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/);
      const extractedName = nameMatch ? nameMatch[1] : null;
      
      relationships.push({
        text: fullText,
        extractedName: extractedName,
        type: relationshipType,
        baseType: baseType,
        context: fullText,
        hasDescription: fullText.includes('(')
      });
    }
  }
  
  return relationships;
}

/**
 * Find relationships in merits_flaws array
 * @param {Array} merits - merits_flaws array
 * @returns {Array} Array of relationship-based merits
 */
function findRelationshipsInMerits(merits) {
  const matches = [];
  
  for (const merit of merits) {
    if (merit.name && typeof merit.name === 'string') {
      const lowerName = merit.name.toLowerCase();
      
      if (lowerName.includes('rapport') || 
          lowerName.includes('bond') ||
          lowerName.includes('connection') ||
          lowerName.includes('relationship')) {
        
        // Try to extract related person name from merit
        const nameMatch = merit.name.match(/\(([^)]+)\)/);
        const relatedPerson = nameMatch ? nameMatch[1] : null;
        
        matches.push({
          meritName: merit.name,
          relatedPerson: relatedPerson,
          description: merit.description || '',
          type: inferMeritRelationshipType(merit.name),
          category: merit.category || ''
        });
      }
    }
  }
  
  return matches;
}

/**
 * Search biography text for relationship mentions
 * @param {string} text - Biography text
 * @returns {Array} Array of relationship matches
 */
function findRelationshipsInText(text) {
  const matches = [];
  const lowerText = text.toLowerCase();
  
  // Patterns to look for
  const patterns = [
    { keyword: 'sire', type: 'sire' },
    { keyword: 'childe', type: 'childe' },
    { keyword: 'embraced', type: 'embrace_relationship' },
    { keyword: 'twin', type: 'twin' },
    { keyword: 'brother', type: 'sibling' },
    { keyword: 'sister', type: 'sibling' },
    { keyword: 'mentor', type: 'mentor' },
    { keyword: 'ally', type: 'ally' },
    { keyword: 'friend', type: 'friend' },
    { keyword: 'enemy', type: 'enemy' }
  ];
  
  for (const pattern of patterns) {
    if (lowerText.includes(pattern.keyword)) {
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
 * Infer relationship type from field name and value
 */
function inferRelationshipType(fieldName, value) {
  const lowerName = fieldName.toLowerCase();
  
  if (lowerName.includes('sire')) return 'sire';
  if (lowerName.includes('mentor')) return 'mentor';
  if (lowerName.includes('ally')) return 'ally';
  if (lowerName.includes('contact')) return 'contact';
  if (lowerName.includes('friend')) return 'friend';
  if (lowerName.includes('enemy')) return 'enemy';
  if (lowerName.includes('family')) return 'family';
  
  return 'unknown';
}

/**
 * Infer relationship type from merit name
 */
function inferMeritRelationshipType(meritName) {
  const lowerName = meritName.toLowerCase();
  
  if (lowerName.includes('rapport')) return 'rapport';
  if (lowerName.includes('bond')) return 'bond';
  if (lowerName.includes('connection')) return 'connection';
  
  return 'merit_relationship';
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
  findRelationshipFields
};

