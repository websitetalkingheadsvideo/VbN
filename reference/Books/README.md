# Books Directory

This directory contains complete PDF books and reference materials for the Vampire: The Masquerade (VTM) and Mind's Eye Theatre (MET) systems.

## 🔍 Searchable Database

**All rulebooks are now available in a searchable database!**

### Quick Access
- **🧛 Laws Agent (AI-Powered):** http://localhost/admin/laws_agent.php
- **Web Interface:** http://localhost/admin/rulebooks_search.php
- **API:** http://localhost/admin/api_rulebooks_search.php
- **Documentation:** See `docs/RULEBOOKS_DATABASE.md` and `docs/LAWS_AGENT.md`

### Features
- **NEW: AI-Powered Laws Agent** - Ask questions in natural language and get intelligent answers with citations
- Full-text search across all ~4,500+ pages
- Filter by category, system, and book
- View excerpts with highlighted search terms
- Browse all available books
- RESTful API for integration
- MCP tool for Cursor AI integration

### Database Status
✅ **31 PDFs extracted** (4,500+ pages)
✅ **Database schema created** (6 tables)
✅ **Search interface ready**

## File Types
- **PDF Files**: Complete rulebooks, guides, and supplements
- **Markdown Files**: Project documentation and notes
- **Media Files**: Images and videos for reference

## Book Categories

### Core Rulebooks
- MET - VTM - Introductory Kit.pdf
- MET - VTM - Reference Guide.pdf
- MET - VTM - Storyteller's Guide.pdf

### Faction Guides
- MET - VTM - Camarilla Guide (5017).pdf
- MET - VTM - Anarch Guide (5040).pdf
- MET - VTM - Sabbat Guide (5018).pdf

### Supplements
- MET - VTM - Laws of Elysium (5012).pdf
- MET - VTM - Liber des Goules (5006).pdf
- MET - VTM - Counsel of Primogen.pdf

### Blood Magic
- VTM - Blood Magic - Secrets of Thaumaturgy.pdf
- VTM - Blood Sacrifice - The Thaumaturgy Companion.pdf

### Journals and Epics
- MET - Journal 1-8 (5401-5408).pdf
- MET - Dark Epics.pdf
- WOD - Dark Epics.pdf

### Other Systems
- MET - WTO - Oblivion (5400).pdf
- MTA - Laws of Ascension (5022).pdf
- MTA - Laws of Ascension Companion (5033).pdf

## Usage Notes
- All PDF files are complete books and should be treated as reference materials
- Use these for rules clarification, lore research, and system understanding
- The Valley by Night specific materials are for this chronicle's setting and history

## Technical Details

### Extracted Data Location
All PDFs have been extracted to text format in:
```
G:\VbN\data\extracted_rulebooks\
```

Each book has:
- `.json` file with full content and metadata
- `.txt` file with plain text for easy reading

### Re-importing Data
If you need to rebuild the database:
```bash
# 1. Re-extract PDFs (if needed)
python scripts/extract_pdfs.py

# 2. Create tables (if not already created)
php database/create_rulebooks_tables.php

# 3. Import data
php database/import_rulebooks.php
```

### Search Examples
- Find discipline: "Celerity"
- Find combat rules: "grappling"
- Find clan info: "Toreador"
- Find merit: "Iron Will"

For complete documentation, see `docs/RULEBOOKS_DATABASE.md`
