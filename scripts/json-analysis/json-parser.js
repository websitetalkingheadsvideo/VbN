/**
 * JSON Parser Module
 * Reads and parses JSON character files, handles errors gracefully
 */

const fs = require('fs');

/**
 * Parse a single JSON file
 * @param {string} filePath - Full path to JSON file
 * @returns {Promise<{success: boolean, data: Object|null, error: string|null, structure: Object|null}>}
 */
async function parseJSONFile(filePath) {
  try {
    const fileContent = fs.readFileSync(filePath, 'utf8');
    const data = JSON.parse(fileContent);
    
    // Analyze structure
    const structure = analyzeStructure(data);
    
    return {
      success: true,
      data: data,
      structure: structure,
      error: null
    };
  } catch (error) {
    return {
      success: false,
      data: null,
      structure: null,
      error: error.message
    };
  }
}

/**
 * Analyze the structure of a parsed JSON object
 * @param {Object} data - Parsed JSON data
 * @param {string} path - Current path in the object (for nested structures)
 * @returns {Object} Structure analysis
 */
function analyzeStructure(data, path = '') {
  const structure = {
    fields: [],
    nestedObjects: [],
    arrays: [],
    types: {}
  };
  
  if (typeof data !== 'object' || data === null) {
    return structure;
  }
  
  for (const [key, value] of Object.entries(data)) {
    const currentPath = path ? `${path}.${key}` : key;
    const valueType = Array.isArray(value) ? 'array' : typeof value;
    
    structure.fields.push({
      path: currentPath,
      name: key,
      type: valueType,
      isArray: Array.isArray(value),
      arrayLength: Array.isArray(value) ? value.length : null,
      sampleValue: getSampleValue(value)
    });
    
    if (valueType === 'object' && value !== null && !Array.isArray(value)) {
      structure.nestedObjects.push(currentPath);
      const nested = analyzeStructure(value, currentPath);
      structure.fields.push(...nested.fields);
      structure.nestedObjects.push(...nested.nestedObjects);
      structure.arrays.push(...nested.arrays);
    }
    
    if (Array.isArray(value)) {
      structure.arrays.push({
        path: currentPath,
        length: value.length,
        itemType: value.length > 0 ? typeof value[0] : 'unknown',
        sampleItems: value.slice(0, 3)
      });
    }
  }
  
  return structure;
}

/**
 * Get a sample value (truncated if too long)
 * @param {*} value - Value to sample
 * @returns {*} Sample value
 */
function getSampleValue(value) {
  if (typeof value === 'string') {
    return value.length > 100 ? value.substring(0, 100) + '...' : value;
  }
  if (Array.isArray(value)) {
    return value.slice(0, 3);
  }
  if (typeof value === 'object' && value !== null) {
    const keys = Object.keys(value);
    return keys.length > 3 ? keys.slice(0, 3) : keys;
  }
  return value;
}

/**
 * Parse multiple JSON files
 * @param {Array<{fullPath: string}>} files - Array of file objects with fullPath property
 * @returns {Promise<Array>} Array of parse results
 */
async function parseAllJSONFiles(files) {
  const results = [];
  
  for (const file of files) {
    const parseResult = await parseJSONFile(file.fullPath);
    results.push({
      filename: file.filename,
      ...parseResult
    });
    
    // Update file parse status
    file.parseStatus = parseResult.success ? 'success' : 'error';
  }
  
  return results;
}

module.exports = {
  parseJSONFile,
  parseAllJSONFiles,
  analyzeStructure
};

