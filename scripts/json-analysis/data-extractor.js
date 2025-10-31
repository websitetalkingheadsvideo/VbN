/**
 * Data Extractor Module
 * Extracts and transforms Coterie and Relationships data from character JSON files
 */

const { parseJSONFile } = require('./json-parser');
const { findCoterieFields } = require('./coterie-mapper');
const { findRelationshipFields } = require('./relationships-mapper');

/**
 * Extract coterie data from a character JSON file
 * @param {Object} characterData - Parsed character JSON data
 * @param {string} filename - Source filename for reference
 * @returns {Array} Array of coterie objects
 */
function extractCoterieData(characterData, filename) {
  const coteries = [];
  const coterieFields = findCoterieFields(characterData);
  
  // Extract from explicit research_notes.coterie
  if (characterData.research_notes && characterData.research_notes.coterie) {
    coteries.push({
      name: characterData.research_notes.coterie,
      type: 'coterie',
      source: 'research_notes.coterie',
      description: null
    });
  }
  
  // Extract from biography text
  if (characterData.biography) {
    const bioMatches = findCoterieInBiography(characterData.biography);
    bioMatches.forEach(match => {
      coteries.push({
        name: extractCoterieName(match.context),
        type: match.type,
        source: 'biography',
        description: match.context.substring(0, 200)
      });
    });
  }
  
  // Extract from backgroundDetails
  if (characterData.backgroundDetails) {
    const bgMatches = findCoterieInBackgrounds(characterData.backgroundDetails);
    bgMatches.forEach(match => {
      coteries.push({
        name: extractCoterieName(match.value || match.context),
        type: match.type || 'group',
        source: 'backgroundDetails',
        description: match.value || match.context,
        role: extractRole(match.value || match.context)
      });
    });
  }
  
  // Deduplicate by name
  return deduplicateCoteries(coteries);
}

/**
 * Extract relationship data from a character JSON file
 * @param {Object} characterData - Parsed character JSON data
 * @param {string} filename - Source filename for reference
 * @returns {Array} Array of relationship objects
 */
function extractRelationshipData(characterData, filename) {
  const relationships = [];
  
  // Extract from sire field
  if (characterData.sire && characterData.sire !== 'Unknown' && characterData.sire !== 'N/A') {
    relationships.push({
      character_name: extractCharacterName(characterData.sire),
      character_id: null, // Will be linked later if character exists in database
      type: 'sire',
      description: characterData.sire,
      source: 'sire',
      strength: null
    });
  }
  
  // Extract from backgroundDetails
  if (characterData.backgroundDetails) {
    // Extract from Mentor
    if (characterData.backgroundDetails.Mentor) {
      const mentorRels = parseRelationshipString(characterData.backgroundDetails.Mentor, 'mentor');
      mentorRels.forEach(rel => {
        relationships.push({
          character_name: rel.extractedName || extractCharacterName(rel.text),
          character_id: null,
          type: 'mentor',
          description: rel.text,
          source: 'backgroundDetails.Mentor',
          strength: null
        });
      });
    }
    
    // Extract from Allies
    if (characterData.backgroundDetails.Allies) {
      const allyRels = parseRelationshipString(characterData.backgroundDetails.Allies, 'ally');
      allyRels.forEach(rel => {
        relationships.push({
          character_name: rel.extractedName || extractCharacterName(rel.text),
          character_id: null,
          type: rel.type === 'twin' ? 'twin' : 'ally',
          subtype: rel.type === 'twin' ? extractSubtype(rel.text) : null,
          description: rel.text,
          source: 'backgroundDetails.Allies',
          strength: extractStrength(rel.text)
        });
      });
    }
    
    // Extract from Contacts
    if (characterData.backgroundDetails.Contacts) {
      const contactRels = parseRelationshipString(characterData.backgroundDetails.Contacts, 'contact');
      contactRels.forEach(rel => {
        relationships.push({
          character_name: rel.extractedName || extractCharacterName(rel.text),
          character_id: null,
          type: 'contact',
          description: rel.text,
          source: 'backgroundDetails.Contacts',
          strength: null
        });
      });
    }
  }
  
  // Extract from merits_flaws
  if (characterData.merits_flaws && Array.isArray(characterData.merits_flaws)) {
    characterData.merits_flaws.forEach(merit => {
      if (merit.name && (
          merit.name.toLowerCase().includes('rapport') ||
          merit.name.toLowerCase().includes('bond') ||
          merit.name.toLowerCase().includes('rivalry')
        )) {
        const nameMatch = merit.name.match(/\(([^)]+)\)/);
        const relatedPerson = nameMatch ? nameMatch[1] : null;
        
        if (relatedPerson) {
          relationships.push({
            character_name: relatedPerson,
            character_id: null,
            type: merit.name.toLowerCase().includes('rivalry') ? 'rival' : 'special_rapport',
            description: merit.description || merit.name,
            source: 'merits_flaws',
            strength: 'special'
          });
        }
      }
    });
  }
  
  // Deduplicate relationships
  return deduplicateRelationships(relationships);
}

/**
 * Find coterie mentions in biography text
 */
function findCoterieInBiography(biography) {
  const matches = [];
  const lowerBio = biography.toLowerCase();
  
  const patterns = [
    { keyword: 'anarch', type: 'faction', context: extractContext(biography, 'anarch') },
    { keyword: 'coterie', type: 'coterie', context: extractContext(biography, 'coterie') },
    { keyword: 'serves as', type: 'role', context: extractContext(biography, 'serves as') },
    { keyword: 'part of', type: 'membership', context: extractContext(biography, 'part of') },
    { keyword: 'de facto', type: 'informal_group', context: extractContext(biography, 'de facto') },
    { keyword: 'talon', type: 'role', context: extractContext(biography, 'talon') },
    { keyword: 'harpy', type: 'role', context: extractContext(biography, 'harpy') }
  ];
  
  patterns.forEach(pattern => {
    if (lowerBio.includes(pattern.keyword) && pattern.context) {
      matches.push({
        keyword: pattern.keyword,
        type: pattern.type,
        context: pattern.context
      });
    }
  });
  
  return matches;
}

/**
 * Find coterie in background details
 */
function findCoterieInBackgrounds(backgroundDetails) {
  const matches = [];
  
  // Check Status field for roles
  if (backgroundDetails.Status) {
    const status = backgroundDetails.Status.toLowerCase();
    if (status.includes('talon') || status.includes('harpy') || status.includes('primogen')) {
      matches.push({
        field: 'Status',
        value: backgroundDetails.Status,
        type: 'role',
        context: backgroundDetails.Status
      });
    }
  }
  
  return matches;
}

/**
 * Extract coterie name from text
 */
function extractCoterieName(text) {
  if (!text) return null;
  
  // Try to extract a name (capitalized words)
  const nameMatch = text.match(/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/);
  if (nameMatch) {
    return nameMatch[1];
  }
  
  // Fallback to first few words
  const words = text.split(' ').slice(0, 3).join(' ');
  return words.length > 50 ? words.substring(0, 50) + '...' : words;
}

/**
 * Extract character name from text
 */
function extractCharacterName(text) {
  if (!text) return 'Unknown';
  
  // Remove parenthetical descriptions
  const cleaned = text.replace(/\s*\([^)]+\)/g, '');
  
  // Extract name (capitalized words)
  const nameMatch = cleaned.match(/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/);
  if (nameMatch) {
    return nameMatch[1];
  }
  
  return cleaned.split(',')[0].trim();
}

/**
 * Extract relationship subtype (brother, sister, etc.)
 */
function extractSubtype(text) {
  if (!text) return null;
  const lower = text.toLowerCase();
  
  if (lower.includes('brother')) return 'brother';
  if (lower.includes('sister')) return 'sister';
  if (lower.includes('twin')) return 'twin';
  
  return null;
}

/**
 * Extract relationship strength
 */
function extractStrength(text) {
  if (!text) return null;
  const lower = text.toLowerCase();
  
  if (lower.includes('inseparable')) return 'inseparable';
  if (lower.includes('strong') || lower.includes('close')) return 'strong';
  if (lower.includes('weak') || lower.includes('distant')) return 'weak';
  
  return null;
}

/**
 * Extract role from text
 */
function extractRole(text) {
  if (!text) return null;
  const lower = text.toLowerCase();
  
  if (lower.includes('talon')) return 'Talon';
  if (lower.includes('harpy')) return 'Harpy';
  if (lower.includes('primogen')) return 'Primogen';
  if (lower.includes('member')) return 'Member';
  
  return null;
}

/**
 * Extract context around a keyword
 */
function extractContext(text, keyword) {
  const lowerText = text.toLowerCase();
  const index = lowerText.indexOf(keyword);
  
  if (index === -1) return null;
  
  const start = Math.max(0, index - 50);
  const end = Math.min(text.length, index + keyword.length + 50);
  return text.substring(start, end).trim();
}

/**
 * Parse relationship string from backgroundDetails
 */
function parseRelationshipString(text, baseType) {
  if (!text || typeof text !== 'string') return [];
  
  const relationships = [];
  const parts = text.split(',').map(p => p.trim()).filter(p => p.length > 0);
  
  for (const part of parts) {
    // Extract name
    const nameMatch = part.match(/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/);
    const extractedName = nameMatch ? nameMatch[1] : null;
    
    // Determine type
    let relationshipType = baseType;
    const lowerPart = part.toLowerCase();
    
    if (lowerPart.includes('twin')) relationshipType = 'twin';
    else if (lowerPart.includes('brother') || lowerPart.includes('sister')) relationshipType = 'sibling';
    else if (lowerPart.includes('sire')) relationshipType = 'sire';
    else if (lowerPart.includes('mentor')) relationshipType = 'mentor';
    
    relationships.push({
      text: part,
      extractedName: extractedName,
      type: relationshipType,
      baseType: baseType
    });
  }
  
  return relationships;
}

/**
 * Deduplicate coteries by name
 */
function deduplicateCoteries(coteries) {
  const seen = new Set();
  return coteries.filter(coterie => {
    const key = (coterie.name || '').toLowerCase();
    if (seen.has(key)) return false;
    seen.add(key);
    return true;
  });
}

/**
 * Deduplicate relationships by character_name and type
 */
function deduplicateRelationships(relationships) {
  const seen = new Set();
  return relationships.filter(rel => {
    const key = `${(rel.character_name || '').toLowerCase()}_${rel.type}`;
    if (seen.has(key)) return false;
    seen.add(key);
    return true;
  });
}

module.exports = {
  extractCoterieData,
  extractRelationshipData
};

