# JSON Analysis for Coterie & Relationships

This directory contains tools for analyzing character JSON files and extracting Coterie and Relationships data for database population.

## Overview

The analysis system extracts:
- **Coterie Data**: Group memberships, factions, organizations found in biography text, backgroundDetails, and research_notes
- **Relationships Data**: Character connections (sire, mentor, allies, contacts, etc.) found in sire field, backgroundDetails, merits_flaws, and biography

## Files

### Analysis Scripts
- `file-discovery.js` - Scans directory for JSON files
- `json-parser.js` - Parses and analyzes JSON structure
- `coterie-mapper.js` - Detects coterie/group data
- `relationships-mapper.js` - Detects relationship data
- `main-analyzer.js` - Main analysis orchestrator (generates report)

### Extraction Scripts
- `data-extractor.js` - Extracts coterie and relationship data from parsed JSON
- `transformer.js` - Transforms extracted data for database insertion
- `extract-all.js` - Processes all files and generates extracted-data.json

### Database Scripts (in `database/`)
- `migrate_coterie_relationships.php` - Creates database tables
- `populate_coterie_relationships.php` - Populates database from extracted data

## Usage

### Step 1: Run Analysis Report

Generate a comprehensive analysis report:

```bash
cd scripts/json-analysis
node main-analyzer.js
```

This generates `docs/json-analysis-report.md` with:
- Field mappings found
- Sample data
- Schema design recommendations

### Step 2: Extract Data

Extract coterie and relationships data from all character files:

```bash
node extract-all.js
```

This generates:
- `docs/json-analysis/extracted-data.json` - All extracted data
- `docs/json-analysis/extraction-summary.md` - Summary statistics

### Step 3: Create Database Tables

Run the migration script to create the database tables:

```bash
php database/migrate_coterie_relationships.php
```

This creates:
- `character_coteries` table
- `character_relationships` table
- Optional JSON columns on `characters` table

### Step 4: Populate Database

Import the extracted data into the database:

```bash
php database/populate_coterie_relationships.php
```

This script:
- Reads `docs/json-analysis/extracted-data.json`
- Matches character names to existing database characters
- Links relationships to existing characters when possible
- Inserts coteries and relationships

## Data Sources

The extraction looks for data in:

### Coterie Sources
- `research_notes.coterie` - Explicit coterie field
- `biography` - Text mentions of factions, groups, roles
- `backgroundDetails.Status` - Role descriptions (Talon, Harpy, etc.)

### Relationship Sources
- `sire` - Parent-child relationship
- `backgroundDetails.Mentor` - Mentor relationships
- `backgroundDetails.Allies` - Ally relationships
- `backgroundDetails.Contacts` - Contact relationships
- `merits_flaws` - Special Rapport, bonds, rivalries

## Output Format

### Extracted Coterie Format
```json
{
  "name": "Anarch faction",
  "type": "faction",
  "role": "member",
  "description": "De facto gathering place for Anarchs in Phoenix",
  "source": "biography"
}
```

### Extracted Relationship Format
```json
{
  "character_name": "Sebastian",
  "character_id": null,
  "type": "twin",
  "subtype": "brother",
  "strength": "inseparable",
  "description": "Twin brother Sebastian (inseparable)",
  "source": "backgroundDetails.Allies"
}
```

## Database Schema

See `docs/json-analysis-report.md` for detailed schema recommendations. Two approaches are supported:

1. **JSON Columns** - Store as JSON on `characters` table (flexible, simpler)
2. **Separate Tables** - Store in dedicated tables (queryable, linkable)

The migration script creates separate tables by default, with optional JSON columns.

## Notes

- Character name matching uses fuzzy logic (normalization, partial matching)
- Duplicate relationships/coteries are automatically deduplicated
- Source fields are tracked for data lineage
- Failed JSON parses are logged but don't stop the process

## Troubleshooting

**Issue**: `extract-all.js` fails with parse errors
- Check the JSON file syntax
- Some files may have malformed JSON (e.g., Warner Jefferson.json)

**Issue**: `populate_coterie_relationships.php` can't find characters
- Ensure characters exist in database first
- Character name matching is fuzzy but may miss some matches
- Check extracted-data.json for character names that need manual linking

**Issue**: Duplicate data
- The scripts include deduplication, but may need refinement
- Check database for existing records before re-running population

