/**
 * Extract All Data Script
 * Processes all JSON files and extracts Coterie and Relationships data
 */

const fs = require('fs');
const path = require('path');
const { discoverJSONFiles } = require('./file-discovery');
const { parseJSONFile } = require('./json-parser');
const { extractCoterieData, extractRelationshipData } = require('./data-extractor');
const { transformCoteriesForDatabase, transformRelationshipsForDatabase } = require('./transformer');

const JSON_DIRECTORY = path.join(__dirname, '../../reference/Characters/Added to Database');
const OUTPUT_DIR = path.join(__dirname, '../../docs/json-analysis');

/**
 * Extract data from all character files
 */
async function extractAllData() {
  console.log('Extracting Coterie and Relationships data from all character files...\n');
  
  // Discover all JSON files
  const jsonFiles = await discoverJSONFiles(JSON_DIRECTORY);
  console.log(`Found ${jsonFiles.length} JSON files\n`);
  
  const results = {
    characters: [],
    summary: {
      totalFiles: jsonFiles.length,
      successful: 0,
      failed: 0,
      totalCoteries: 0,
      totalRelationships: 0
    }
  };
  
  // Process each file
  for (const file of jsonFiles) {
    try {
      const parseResult = await parseJSONFile(file.fullPath);
      
      if (!parseResult.success) {
        console.log(`⚠️  Failed to parse ${file.filename}: ${parseResult.error}`);
        results.summary.failed++;
        continue;
      }
      
      const characterData = parseResult.data;
      const characterName = characterData.character_name || characterData.name || file.filename.replace('.json', '');
      
      // Extract coterie data
      const coteries = extractCoterieData(characterData, file.filename);
      const transformedCoteries = transformCoteriesForDatabase(coteries, { format: 'json' });
      
      // Extract relationship data
      const relationships = extractRelationshipData(characterData, file.filename);
      const transformedRelationships = transformRelationshipsForDatabase(relationships, { format: 'json' });
      
      results.characters.push({
        filename: file.filename,
        character_name: characterName,
        coteries: transformedCoteries,
        relationships: transformedRelationships,
        coterie_count: transformedCoteries.length,
        relationship_count: transformedRelationships.length
      });
      
      results.summary.successful++;
      results.summary.totalCoteries += transformedCoteries.length;
      results.summary.totalRelationships += transformedRelationships.length;
      
      console.log(`✅ ${characterName}: ${transformedCoteries.length} coteries, ${transformedRelationships.length} relationships`);
      
    } catch (error) {
      console.error(`❌ Error processing ${file.filename}:`, error.message);
      results.summary.failed++;
    }
  }
  
  // Save results
  if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
  }
  
  const outputFile = path.join(OUTPUT_DIR, 'extracted-data.json');
  fs.writeFileSync(outputFile, JSON.stringify(results, null, 2), 'utf8');
  
  // Generate summary report
  const summaryFile = path.join(OUTPUT_DIR, 'extraction-summary.md');
  generateSummaryReport(results, summaryFile);
  
  console.log(`\n✅ Extraction complete!`);
  console.log(`   Files processed: ${results.summary.successful}/${results.summary.totalFiles}`);
  console.log(`   Total coteries found: ${results.summary.totalCoteries}`);
  console.log(`   Total relationships found: ${results.summary.totalRelationships}`);
  console.log(`   Results saved to: ${outputFile}`);
  console.log(`   Summary saved to: ${summaryFile}`);
  
  return results;
}

/**
 * Generate summary report
 */
function generateSummaryReport(results, outputPath) {
  let report = `# Extraction Summary Report\n\n`;
  report += `**Generated:** ${new Date().toISOString()}\n\n`;
  
  report += `## Summary\n\n`;
  report += `- **Total Files**: ${results.summary.totalFiles}\n`;
  report += `- **Successfully Processed**: ${results.summary.successful}\n`;
  report += `- **Failed**: ${results.summary.failed}\n`;
  report += `- **Total Coteries Extracted**: ${results.summary.totalCoteries}\n`;
  report += `- **Total Relationships Extracted**: ${results.summary.totalRelationships}\n\n`;
  
  report += `## Characters with Most Coteries\n\n`;
  const topCoteries = [...results.characters]
    .sort((a, b) => b.coterie_count - a.coterie_count)
    .slice(0, 10);
  
  topCoteries.forEach(char => {
    report += `- **${char.character_name}**: ${char.coterie_count} coteries\n`;
  });
  
  report += `\n## Characters with Most Relationships\n\n`;
  const topRelationships = [...results.characters]
    .sort((a, b) => b.relationship_count - a.relationship_count)
    .slice(0, 10);
  
  topRelationships.forEach(char => {
    report += `- **${char.character_name}**: ${char.relationship_count} relationships\n`;
  });
  
  report += `\n## Sample Data\n\n`;
  
  // Show sample coterie
  const charWithCoterie = results.characters.find(c => c.coteries.length > 0);
  if (charWithCoterie) {
    report += `### Sample Coterie Data (${charWithCoterie.character_name})\n\n`;
    report += `\`\`\`json\n`;
    report += JSON.stringify(charWithCoterie.coteries[0], null, 2);
    report += `\n\`\`\`\n\n`;
  }
  
  // Show sample relationship
  const charWithRel = results.characters.find(c => c.relationships.length > 0);
  if (charWithRel) {
    report += `### Sample Relationship Data (${charWithRel.character_name})\n\n`;
    report += `\`\`\`json\n`;
    report += JSON.stringify(charWithRel.relationships[0], null, 2);
    report += `\n\`\`\`\n\n`;
  }
  
  fs.writeFileSync(outputPath, report, 'utf8');
}

// Run if called directly
if (require.main === module) {
  extractAllData().catch(console.error);
}

module.exports = { extractAllData };

