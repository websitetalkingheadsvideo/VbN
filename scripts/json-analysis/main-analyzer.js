/**
 * Main JSON Analysis Script
 * Orchestrates the analysis of character JSON files for Coterie and Relationships data
 */

const { discoverJSONFiles, createFileInventory } = require('./file-discovery');
const { parseAllJSONFiles, analyzeStructure } = require('./json-parser');
const { findCoterieFields } = require('./coterie-mapper');
const { findRelationshipFields } = require('./relationships-mapper');
const fs = require('fs');
const path = require('path');

const JSON_DIRECTORY = path.join(__dirname, '../../reference/Characters/Added to Database');
const OUTPUT_DIR = path.join(__dirname, '../../docs');
const REPORT_FILE = path.join(OUTPUT_DIR, 'json-analysis-report.md');

/**
 * Main analysis function
 */
async function analyzeAllFiles() {
  console.log('='.repeat(60));
  console.log('JSON Analysis for Coterie & Relationships Fields');
  console.log('='.repeat(60));
  console.log();
  
  try {
    // Phase 1: File Discovery
    console.log('Phase 1: Discovering JSON files...');
    const jsonFiles = await discoverJSONFiles(JSON_DIRECTORY);
    const inventory = createFileInventory(jsonFiles);
    
    console.log(`Found ${jsonFiles.length} JSON files`);
    console.log(`Total size: ${(inventory.summary.totalSize / 1024).toFixed(2)} KB`);
    console.log();
    
    // Phase 2: Parse and analyze structure
    console.log('Phase 2: Parsing JSON files and analyzing structure...');
    const parseResults = await parseAllJSONFiles(jsonFiles);
    
    const successCount = parseResults.filter(r => r.success).length;
    const errorCount = parseResults.filter(r => !r.success).length;
    
    console.log(`Successfully parsed: ${successCount}`);
    console.log(`Parse errors: ${errorCount}`);
    console.log();
    
    if (errorCount > 0) {
      console.log('Files with parse errors:');
      parseResults
        .filter(r => !r.success)
        .forEach(r => console.log(`  - ${r.filename}: ${r.error}`));
      console.log();
    }
    
    // Phase 3: Coterie Field Detection
    console.log('Phase 3: Detecting Coterie fields...');
    const coterieAnalysis = [];
    for (const result of parseResults) {
      if (result.success && result.data) {
        const coterieFields = findCoterieFields(result.data);
        if (coterieFields.length > 0) {
          coterieAnalysis.push({
            filename: result.filename,
            fields: coterieFields
          });
        }
      }
    }
    console.log(`Found coterie data in ${coterieAnalysis.length} files`);
    console.log();
    
    // Phase 4: Relationship Field Detection
    console.log('Phase 4: Detecting Relationship fields...');
    const relationshipAnalysis = [];
    for (const result of parseResults) {
      if (result.success && result.data) {
        const relationshipFields = findRelationshipFields(result.data);
        if (relationshipFields.length > 0) {
          relationshipAnalysis.push({
            filename: result.filename,
            fields: relationshipFields
          });
        }
      }
    }
    console.log(`Found relationship data in ${relationshipAnalysis.length} files`);
    console.log();
    
    // Generate comprehensive report
    console.log('Phase 5: Generating analysis report...');
    const report = generateReport({
      inventory,
      parseResults,
      coterieAnalysis,
      relationshipAnalysis
    });
    
    // Ensure output directory exists
    if (!fs.existsSync(OUTPUT_DIR)) {
      fs.mkdirSync(OUTPUT_DIR, { recursive: true });
    }
    
    // Write report
    fs.writeFileSync(REPORT_FILE, report, 'utf8');
    console.log(`Report written to: ${REPORT_FILE}`);
    console.log();
    
    // Summary
    console.log('Analysis Complete!');
    console.log(`Total files analyzed: ${jsonFiles.length}`);
    console.log(`Files with coterie data: ${coterieAnalysis.length}`);
    console.log(`Files with relationship data: ${relationshipAnalysis.length}`);
    
  } catch (error) {
    console.error('Analysis failed:', error);
    process.exit(1);
  }
}

/**
 * Generate comprehensive markdown report
 */
function generateReport(analysisData) {
  const { inventory, parseResults, coterieAnalysis, relationshipAnalysis } = analysisData;
  
  let report = `# JSON Field Analysis Report\n`;
  report += `# Coterie & Relationships Data Mapping\n\n`;
  report += `**Generated:** ${new Date().toISOString()}\n\n`;
  
  // Executive Summary
  report += `## Executive Summary\n\n`;
  report += `- Total JSON files analyzed: ${inventory.summary.totalFiles}\n`;
  report += `- Files successfully parsed: ${parseResults.filter(r => r.success).length}\n`;
  report += `- Files with parse errors: ${parseResults.filter(r => !r.success).length}\n`;
  report += `- Files with Coterie data: ${coterieAnalysis.length}\n`;
  report += `- Files with Relationship data: ${relationshipAnalysis.length}\n`;
  report += `- Total size: ${(inventory.summary.totalSize / 1024).toFixed(2)} KB\n\n`;
  
  // File Inventory
  report += `## File Inventory\n\n`;
  report += `| Filename | Size (KB) | Parse Status |\n`;
  report += `|----------|-----------|--------------|\n`;
  inventory.files.forEach(file => {
    report += `| ${file.filename} | ${file.sizeKB} | ${file.parseStatus} |\n`;
  });
  report += `\n`;
  
  // Coterie Analysis
  report += `## Coterie Field Analysis\n\n`;
  
  // Group findings by source type
  const coterieBySource = {
    explicit_fields: [],
    biography_text: [],
    background_details: [],
    research_notes: []
  };
  
  coterieAnalysis.forEach(fileAnalysis => {
    fileAnalysis.fields.forEach(field => {
      if (!coterieBySource[field.source]) {
        coterieBySource[field.source] = [];
      }
      coterieBySource[field.source].push({
        filename: fileAnalysis.filename,
        ...field
      });
    });
  });
  
  if (coterieAnalysis.length === 0) {
    report += `No explicit coterie fields found in JSON structure.\n\n`;
  } else {
    // Show explicit fields first
    if (coterieBySource.explicit_fields.length > 0 || coterieBySource.research_notes.length > 0) {
      report += `### Explicit Coterie Fields Found\n\n`;
      [...coterieBySource.explicit_fields, ...coterieBySource.research_notes].forEach(field => {
        report += `- **File**: ${field.filename}\n`;
        report += `  - **Path**: \`${field.path}\`\n`;
        report += `  - **Type**: ${field.dataType}\n`;
        if (field.sample) {
          const sampleStr = JSON.stringify(field.sample);
          const sampleDisplay = sampleStr.length > 150 ? sampleStr.substring(0, 150) + '...' : sampleStr;
          report += `  - **Sample**: ${sampleDisplay}\n`;
        }
        if (field.matches && field.matches.length > 0) {
          report += `  - **Matches**:\n`;
          field.matches.forEach(match => {
            report += `    - ${match.context || match.value || JSON.stringify(match)}\n`;
          });
        }
        report += `\n`;
      });
    }
    
    // Show biography text matches
    if (coterieBySource.biography_text.length > 0) {
      report += `### Coterie Mentions in Biography Text\n\n`;
      coterieBySource.biography_text.forEach(field => {
        if (field.matches && field.matches.length > 0) {
          report += `**${field.filename}**:\n`;
          field.matches.forEach(match => {
            report += `- **Type**: ${match.type} (keyword: "${match.keyword}")\n`;
            report += `  - **Context**: "...${match.context}..."\n`;
          });
          report += `\n`;
        }
      });
    }
    
    // Show background details
    if (coterieBySource.background_details.length > 0) {
      report += `### Group Affiliations in Background Details\n\n`;
      coterieBySource.background_details.forEach(field => {
        if (field.matches && field.matches.length > 0) {
          report += `**${field.filename}**:\n`;
          field.matches.forEach(match => {
            report += `- **Field**: ${match.field}\n`;
            report += `  - **Value**: ${match.value || match.context}\n`;
            report += `  - **Type**: ${match.type}\n`;
          });
          report += `\n`;
        }
      });
    }
  }
  
  report += `\n`;
  report += `\n`;
  
  // Relationship Analysis
  report += `## Relationships Field Analysis\n\n`;
  
  // Group findings by source type
  const relationshipBySource = {
    explicit_field: [],
    background_details: [],
    biography_text: [],
    merits_flaws: []
  };
  
  relationshipAnalysis.forEach(fileAnalysis => {
    fileAnalysis.fields.forEach(field => {
      const sourceKey = field.source === 'explicit_field' ? 'explicit_field' : 
                       field.source === 'background_details' ? 'background_details' :
                       field.source === 'biography_text' ? 'biography_text' :
                       field.source === 'merits_flaws' ? 'merits_flaws' : 'explicit_field';
      if (!relationshipBySource[sourceKey]) {
        relationshipBySource[sourceKey] = [];
      }
      relationshipBySource[sourceKey].push({
        filename: fileAnalysis.filename,
        ...field
      });
    });
  });
  
  if (relationshipAnalysis.length === 0) {
    report += `No explicit relationship fields found in JSON structure.\n\n`;
  } else {
    // Show explicit fields (sire)
    if (relationshipBySource.explicit_field.length > 0) {
      report += `### Explicit Relationship Fields\n\n`;
      relationshipBySource.explicit_field.forEach(field => {
        report += `- **File**: ${field.filename}\n`;
        report += `  - **Path**: \`${field.path}\`\n`;
        report += `  - **Type**: ${field.relationshipType || 'unknown'}\n`;
        if (field.sample) {
          report += `  - **Value**: ${field.sample}\n`;
        }
        report += `\n`;
      });
    }
    
    // Show background details
    if (relationshipBySource.background_details.length > 0) {
      report += `### Relationships in Background Details\n\n`;
      relationshipBySource.background_details.forEach(field => {
        if (field.matches && field.matches.length > 0) {
          report += `**${field.filename}**:\n`;
          field.matches.forEach(match => {
            report += `- **Type**: ${match.type} (from ${match.baseType || 'unknown'})\n`;
            if (match.extractedName) {
              report += `  - **Name**: ${match.extractedName}\n`;
            }
            report += `  - **Text**: ${match.text || match.context}\n`;
            if (match.hasDescription) {
              report += `  - **Has Description**: Yes\n`;
            }
            report += `\n`;
          });
        }
      });
    }
    
    // Show merits_flaws
    if (relationshipBySource.merits_flaws.length > 0) {
      report += `### Relationship-Based Merits\n\n`;
      relationshipBySource.merits_flaws.forEach(field => {
        if (field.matches && field.matches.length > 0) {
          report += `**${field.filename}**:\n`;
          field.matches.forEach(match => {
            report += `- **Merit**: ${match.meritName}\n`;
            if (match.relatedPerson) {
              report += `  - **Related Person**: ${match.relatedPerson}\n`;
            }
            report += `  - **Type**: ${match.type}\n`;
            if (match.description) {
              const descDisplay = match.description.length > 100 ? match.description.substring(0, 100) + '...' : match.description;
              report += `  - **Description**: ${descDisplay}\n`;
            }
            report += `\n`;
          });
        }
      });
    }
    
    // Show biography text matches
    if (relationshipBySource.biography_text.length > 0) {
      report += `### Relationship Mentions in Biography Text\n\n`;
      relationshipBySource.biography_text.forEach(field => {
        if (field.matches && field.matches.length > 0) {
          report += `**${field.filename}**:\n`;
          field.matches.forEach(match => {
            report += `- **Type**: ${match.type} (keyword: "${match.keyword}")\n`;
            report += `  - **Context**: "...${match.context}..."\n`;
          });
          report += `\n`;
        }
      });
    }
  }
  report += `\n`;
  
  // Data Quality Assessment
  report += `## Data Quality Assessment\n\n`;
  report += `### Completeness\n`;
  report += `- Files with complete data: ${parseResults.filter(r => r.success).length}/${inventory.summary.totalFiles}\n\n`;
  report += `### Consistency\n`;
  report += `- Format variations across files will be documented in detailed mapping\n\n`;
  
  // Schema Design Recommendations
  report += `## Database Schema Design Recommendations\n\n`;
  report += `Based on the analysis findings, here are recommended database schemas:\n\n`;
  
  report += `### Coterie Field Schema\n\n`;
  report += `The \`coterie\` field should store an array of coterie/organization memberships:\n\n`;
  report += `**Option 1: JSON Column (Recommended for flexibility)**\n`;
  report += `\`\`\`sql\n`;
  report += `ALTER TABLE characters ADD COLUMN coterie JSON;\n`;
  report += `\`\`\`\n\n`;
  report += `**JSON Structure:**\n`;
  report += `\`\`\`json\n`;
  report += `[\n`;
  report += `  {\n`;
  report += `    "name": "Anarch faction",\n`;
  report += `    "type": "faction",\n`;
  report += `    "role": "member",\n`;
  report += `    "description": "De facto gathering place for Anarchs in Phoenix",\n`;
  report += `    "source": "biography"\n`;
  report += `  },\n`;
  report += `  {\n`;
  report += `    "name": "Talon to Harpy",\n`;
  report += `    "type": "coterie",\n`;
  report += `    "role": "Talon",\n`;
  report += `    "leader": "Cordelia Prescott",\n`;
  report += `    "source": "backgroundDetails"\n`;
  report += `  }\n`;
  report += `]\n`;
  report += `\`\`\`\n\n`;
  
  report += `**Option 2: Separate Table (Recommended for queries)**\n`;
  report += `\`\`\`sql\n`;
  report += `CREATE TABLE character_coteries (\n`;
  report += `  id INT PRIMARY KEY AUTO_INCREMENT,\n`;
  report += `  character_id INT NOT NULL,\n`;
  report += `  coterie_name VARCHAR(255) NOT NULL,\n`;
  report += `  coterie_type VARCHAR(50), -- 'faction', 'coterie', 'organization', etc.\n`;
  report += `  role VARCHAR(100), -- 'member', 'leader', 'Talon', etc.\n`;
  report += `  description TEXT,\n`;
  report += `  source_field VARCHAR(50), -- 'biography', 'backgroundDetails', 'research_notes'\n`;
  report += `  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n`;
  report += `  FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,\n`;
  report += `  INDEX idx_character_coteries (character_id)\n`;
  report += `);\n`;
  report += `\`\`\`\n\n`;
  
  report += `### Relationships Field Schema\n\n`;
  report += `The \`relationships\` field should store an array of character relationships:\n\n`;
  report += `**Option 1: JSON Column (Recommended for flexibility)**\n`;
  report += `\`\`\`sql\n`;
  report += `ALTER TABLE characters ADD COLUMN relationships JSON;\n`;
  report += `\`\`\`\n\n`;
  report += `**JSON Structure:**\n`;
  report += `\`\`\`json\n`;
  report += `[\n`;
  report += `  {\n`;
  report += `    "character_name": "Sebastian",\n`;
  report += `    "character_id": null, -- Link to character_id if exists in database\n`;
  report += `    "type": "twin",\n`;
  report += `    "subtype": "brother",\n`;
  report += `    "strength": "inseparable",\n`;
  report += `    "description": "Twin brother Sebastian (inseparable)",\n`;
  report += `    "source": "backgroundDetails.Allies"\n`;
  report += `  },\n`;
  report += `  {\n`;
  report += `    "character_name": "Toreador Primogen",\n`;
  report += `    "character_id": null,\n`;
  report += `    "type": "sire",\n`;
  report += `    "description": "Toreador Primogen (sire)",\n`;
  report += `    "source": "sire"\n`;
  report += `  },\n`;
  report += `  {\n`;
  report += `    "character_name": "Cordelia Prescott",\n`;
  report += `    "character_id": null,\n`;
  report += `    "type": "mentor",\n`;
  report += `    "description": "Being trained by Cordelia Prescott in Kindred social politics",\n`;
  report += `    "source": "backgroundDetails.Mentor"\n`;
  report += `  }\n`;
  report += `]\n`;
  report += `\`\`\`\n\n`;
  
  report += `**Option 2: Separate Table (Recommended for queries and character linking)**\n`;
  report += `\`\`\`sql\n`;
  report += `CREATE TABLE character_relationships (\n`;
  report += `  id INT PRIMARY KEY AUTO_INCREMENT,\n`;
  report += `  character_id INT NOT NULL,\n`;
  report += `  related_character_id INT NULL, -- NULL if character not in database\n`;
  report += `  related_character_name VARCHAR(255) NOT NULL, -- Name from JSON if character_id is NULL\n`;
  report += `  relationship_type VARCHAR(50) NOT NULL, -- 'sire', 'mentor', 'ally', 'contact', 'twin', 'sibling', etc.\n`;
  report += `  relationship_subtype VARCHAR(50), -- 'brother', 'sister', etc. for siblings/twins\n`;
  report += `  strength VARCHAR(100), -- 'inseparable', 'strong', 'weak', numeric from backgrounds if available\n`;
  report += `  description TEXT,\n`;
  report += `  source_field VARCHAR(50), -- 'sire', 'backgroundDetails.Allies', 'backgroundDetails.Mentor', etc.\n`;
  report += `  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n`;
  report += `  FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,\n`;
  report += `  FOREIGN KEY (related_character_id) REFERENCES characters(id) ON DELETE SET NULL,\n`;
  report += `  INDEX idx_character_relationships (character_id),\n`;
  report += `  INDEX idx_related_character (related_character_id)\n`;
  report += `);\n`;
  report += `\`\`\`\n\n`;
  
  report += `### Recommended Approach\n\n`;
  report += `**For Coterie:** Use Option 2 (separate table) if you need to query/filter by coterie. Use Option 1 (JSON) if queries are rare and flexibility is more important.\n\n`;
  report += `**For Relationships:** Use Option 2 (separate table) - this enables:\n`;
  report += `- Linking to existing characters via character_id\n`;
  report += `- Querying all relationships involving a character\n`;
  report += `- Finding reciprocal relationships\n`;
  report += `- Filtering by relationship type\n`;
  report += `- Better performance for relationship queries\n\n`;
  
  report += `## Next Steps\n\n`;
  report += `1. Review detailed field mappings in sections above\n`;
  report += `2. Choose schema approach (JSON vs separate tables) based on query needs\n`;
  report += `3. Create extraction scripts for identified fields\n`;
  report += `4. Implement transformation rules for standardization\n`;
  report += `5. Generate migration scripts\n`;
  report += `6. Create data extraction and population scripts\n\n`;
  
  return report;
}

// Run if called directly
if (require.main === module) {
  analyzeAllFiles().catch(console.error);
}

module.exports = { analyzeAllFiles, generateReport };

