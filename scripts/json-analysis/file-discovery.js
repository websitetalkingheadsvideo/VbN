/**
 * File Discovery Module
 * Scans the reference/Characters/Added to Database/ folder for all JSON character files
 */

const fs = require('fs');
const path = require('path');

/**
 * Discover all JSON files in the specified directory
 * @param {string} directoryPath - Path to directory containing JSON files
 * @returns {Promise<Array<{filename: string, fullPath: string, size: number, parseStatus: string}>>}
 */
async function discoverJSONFiles(directoryPath) {
  const results = [];
  
  try {
    const files = fs.readdirSync(directoryPath);
    
    for (const file of files) {
      if (file.endsWith('.json')) {
        const fullPath = path.join(directoryPath, file);
        const stats = fs.statSync(fullPath);
        
        results.push({
          filename: file,
          fullPath: fullPath,
          size: stats.size,
          parseStatus: 'pending',
          lastModified: stats.mtime
        });
      }
    }
    
    console.log(`Found ${results.length} JSON files in ${directoryPath}`);
    return results;
  } catch (error) {
    console.error(`Error discovering JSON files: ${error.message}`);
    throw error;
  }
}

/**
 * Create file inventory with metadata
 * @param {Array} files - Array of file objects from discoverJSONFiles
 * @returns {Object} Inventory object with summary and details
 */
function createFileInventory(files) {
  const totalSize = files.reduce((sum, file) => sum + file.size, 0);
  
  return {
    summary: {
      totalFiles: files.length,
      totalSize: totalSize,
      averageSize: Math.round(totalSize / files.length),
      scanDate: new Date().toISOString()
    },
    files: files.map(file => ({
      filename: file.filename,
      size: file.size,
      sizeKB: Math.round(file.size / 1024 * 100) / 100,
      parseStatus: file.parseStatus,
      lastModified: file.lastModified
    }))
  };
}

module.exports = {
  discoverJSONFiles,
  createFileInventory
};

