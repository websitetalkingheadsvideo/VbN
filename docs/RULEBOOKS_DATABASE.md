# Rulebooks Database System

Complete documentation for the VbN Rulebooks Database - a searchable database of VTM/MET rulebook content.

## Overview

This system extracts text content from PDF rulebooks, stores it in a MySQL database, and provides a searchable interface for finding rules, lore, and game content.

## Components

### 1. PDF Extraction (`scripts/extract_pdfs.py`)
Extracts text content from all PDF files in `reference/Books/` using PyMuPDF.

**Output:**
- JSON files with full extracted content (`data/extracted_rulebooks/*.json`)
- Plain text files for easy reading (`data/extracted_rulebooks/*.txt`)
- Extraction summary with statistics (`data/extracted_rulebooks/_extraction_summary.json`)

**Usage:**
```bash
python scripts/extract_pdfs.py
```

### 2. Database Schema (`database/RULEBOOKS_SCHEMA.md`)
Six tables for comprehensive rulebook storage:

1. **`rulebooks`** - Book metadata (title, category, system, page count)
2. **`rulebook_pages`** - Individual page content with full-text search
3. **`rulebook_sections`** - Chapters and sections (hierarchical)
4. **`rulebook_rules`** - Extracted game rules and mechanics
5. **`rulebook_search_terms`** - Keyword index for fast lookups
6. **`rulebook_cross_references`** - Links between different books

### 3. Table Creation (`database/create_rulebooks_tables.php`)
Creates all database tables with proper indexes and relationships.

**Usage:**
```bash
php database/create_rulebooks_tables.php
```

Or via web browser:
```
http://localhost/database/create_rulebooks_tables.php
```

### 4. Data Import (`database/import_rulebooks.php`)
Imports extracted PDF data into the database.

**Features:**
- Automatic category detection (Core, Faction, Supplement, etc.)
- System type identification (VTM, MET, MTA, etc.)
- Book code extraction (e.g., "5017", "5040")
- Page-level content storage
- Progress tracking and error handling

**Usage:**
```bash
php database/import_rulebooks.php
```

### 5. Search API (`admin/api_rulebooks_search.php`)
RESTful API for searching rulebook content.

**Endpoints:**

#### Search All Content
```
GET /admin/api_rulebooks_search.php?action=search&q=Celerity&category=Faction&system=MET-VTM&limit=50
```

Returns:
```json
{
  "success": true,
  "results": [
    {
      "rulebook_id": 15,
      "book_title": "Anarch Guide",
      "category": "Faction",
      "system_type": "MET-VTM",
      "page_number": 42,
      "snippet": "...use of <mark>Celerity</mark> to enhance...",
      "relevance": 12.34
    }
  ],
  "count": 1
}
```

#### Search Rules
```
GET /admin/api_rulebooks_search.php?action=search_rules&q=combat&rule_category=Combat
```

#### Search Terms
```
GET /admin/api_rulebooks_search.php?action=search_terms&term=Celerity&type=discipline
```

#### Get All Books
```
GET /admin/api_rulebooks_search.php?action=books&category=Core
```

#### Get Specific Page
```
GET /admin/api_rulebooks_search.php?action=page&rulebook_id=15&page=42
```

#### Get Available Filters
```
GET /admin/api_rulebooks_search.php?action=filters
```

Returns:
```json
{
  "success": true,
  "filters": {
    "categories": ["Core", "Faction", "Supplement", "Blood Magic", "Journal"],
    "systems": ["MET-VTM", "MET", "VTM", "MTA", "WOD", "Wraith"]
  }
}
```

### 6. Web Interface (`admin/rulebooks_search.php`)
User-friendly web interface for searching rulebooks.

**Features:**
- Full-text search with context snippets
- Filter by category and system
- Browse all books
- Highlighted search results
- Relevance scoring

**Access:**
```
http://localhost/admin/rulebooks_search.php
```

## Complete Setup Guide

### Step 1: Install Dependencies

```bash
# Install Python PDF libraries
pip install pypdf pymupdf

# Ensure PHP and MySQL are running (XAMPP)
```

### Step 2: Extract PDFs

```bash
cd G:\VbN
python scripts/extract_pdfs.py
```

**Output:** `data/extracted_rulebooks/` directory with JSON and TXT files

### Step 3: Create Database Tables

```bash
php database/create_rulebooks_tables.php
```

Or navigate to:
```
http://localhost/database/create_rulebooks_tables.php
```

### Step 4: Import Data

```bash
php database/import_rulebooks.php
```

This will:
- Import all successfully extracted rulebooks
- Create book metadata records
- Import page-by-page content
- Set up full-text search indexes

### Step 5: Use the System

**Web Interface:**
```
http://localhost/admin/rulebooks_search.php
```

**API:**
```javascript
// Search for "Celerity"
fetch('/admin/api_rulebooks_search.php?action=search&q=Celerity')
  .then(r => r.json())
  .then(data => console.log(data.results));
```

## Database Statistics

From our extraction (31 PDFs):

- **Total Pages:** ~4,500+ pages extracted
- **Categories:**
  - Core: 3 books
  - Faction: 3 books (Camarilla, Anarch, Sabbat)
  - Supplement: 7 books
  - Blood Magic: 2 books
  - Journal: 8 books
  - Other: 8 books

- **Systems:**
  - MET-VTM: Primary focus
  - MET: General Mind's Eye Theatre
  - VTM: Vampire: The Masquerade
  - MTA: Mage: The Ascension
  - WOD: World of Darkness
  - Wraith: Wraith: The Oblivion

## Search Examples

### Find Discipline Information
```
q=Celerity&action=search
```

### Find Combat Rules
```
q=grappling&action=search_rules&rule_category=Combat
```

### Find Clan Information
```
q=Toreador&action=search&system=MET-VTM
```

### Browse Faction Guides
```
action=books&category=Faction
```

## Advanced Features

### Full-Text Search
All text content is indexed with MySQL's FULLTEXT search:
- Natural language mode by default
- Boolean mode support for advanced queries
- Relevance scoring

### Cross-References (Future)
The `rulebook_cross_references` table supports:
- "See also" references
- Prerequisites
- Expansions
- Contradictions between editions

### Rules Extraction (Future)
The `rulebook_rules` table can store:
- Structured rule data
- Discipline powers
- Merit/Flaw descriptions
- Combat mechanics
- Social challenge rules

## Troubleshooting

### PDFs Not Extracting
- Some PDFs may be corrupted or protected
- Check `data/extracted_rulebooks/_extraction_summary.json` for page counts
- Books with 0 pages are skipped during import

### Database Connection Issues
- Verify XAMPP is running
- Check `includes/connect.php` for correct credentials
- Ensure MySQL database exists

### Search Not Working
- Verify tables were created successfully
- Check that data was imported (run import script)
- Ensure full-text indexes are built

### Missing Results
- Full-text search requires minimum word lengths (usually 4 chars)
- Try different search terms
- Check category/system filters

## File Structure

```
G:\VbN\
├── reference/
│   └── Books/                    # Original PDF files
│       ├── MET - VTM - Camarilla Guide (5017).pdf
│       ├── MET - VTM - Anarch Guide (5040).pdf
│       └── ...
├── data/
│   └── extracted_rulebooks/      # Extracted content
│       ├── _extraction_summary.json
│       ├── MET - VTM - Camarilla Guide (5017).json
│       ├── MET - VTM - Camarilla Guide (5017).txt
│       └── ...
├── scripts/
│   └── extract_pdfs.py           # PDF extraction script
├── database/
│   ├── create_rulebooks_tables.php
│   ├── import_rulebooks.php
│   ├── RULEBOOKS_SCHEMA.md       # Database schema documentation
│   └── SCHEMA_UPDATE_README.md
├── admin/
│   ├── api_rulebooks_search.php  # Search API
│   └── rulebooks_search.php      # Web interface
└── docs/
    └── RULEBOOKS_DATABASE.md     # This file
```

## Future Enhancements

1. **AI-Powered Section Detection**
   - Automatically identify chapters/sections
   - Build table of contents

2. **Rule Extraction**
   - Parse specific rule types
   - Structure discipline powers
   - Extract merits/flaws

3. **Search Term Index**
   - Auto-generate keyword index
   - Identify important terms
   - Build synonym list

4. **Cross-Reference Mapping**
   - Detect references between books
   - Build relationship graph
   - Show related content

5. **Advanced Search Features**
   - Boolean operators (AND, OR, NOT)
   - Phrase searching
   - Wildcard support
   - Filter by date ranges

6. **Export Features**
   - Export search results
   - PDF snippets
   - Formatted references

7. **Integration**
   - Link to character sheets
   - Reference in questionnaire
   - ST toolkit integration

## API Integration Examples

### JavaScript/Fetch
```javascript
async function searchRulebooks(query) {
  const response = await fetch(
    `/admin/api_rulebooks_search.php?action=search&q=${encodeURIComponent(query)}`
  );
  return await response.json();
}
```

### PHP
```php
function search_rulebooks($query, $category = null) {
    $params = ['action' => 'search', 'q' => $query];
    if ($category) $params['category'] = $category;
    
    $url = 'admin/api_rulebooks_search.php?' . http_build_query($params);
    $response = file_get_contents($url);
    return json_decode($response, true);
}
```

### jQuery
```javascript
$.ajax({
  url: '/admin/api_rulebooks_search.php',
  data: {
    action: 'search',
    q: 'Celerity',
    category: 'Faction'
  },
  success: function(data) {
    console.log(data.results);
  }
});
```

## Maintenance

### Re-extracting PDFs
If PDFs are updated or corrupted:
```bash
python scripts/extract_pdfs.py
php database/import_rulebooks.php  # Re-import
```

### Rebuilding Indexes
```sql
-- Rebuild full-text indexes if search is slow
ALTER TABLE rulebook_pages DROP INDEX ft_content;
ALTER TABLE rulebook_pages ADD FULLTEXT INDEX ft_content (page_text);
```

### Clearing Data
```sql
-- Clear all rulebook data
TRUNCATE rulebook_cross_references;
TRUNCATE rulebook_search_terms;
TRUNCATE rulebook_rules;
TRUNCATE rulebook_sections;
TRUNCATE rulebook_pages;
TRUNCATE rulebooks;
```

## Performance Notes

- **Full-text search** is very fast even with thousands of pages
- **Page count:** ~4,500 pages = ~50MB of text content
- **Search time:** Typically < 100ms for most queries
- **Index size:** FULLTEXT indexes add ~2x storage overhead

## Support

For issues or questions:
1. Check this documentation
2. Review `database/RULEBOOKS_SCHEMA.md` for schema details
3. Check extraction summary for data issues
4. Verify database tables exist and have data

---

**Last Updated:** January 2025
**Version:** 1.0
**Status:** Initial Implementation Complete

