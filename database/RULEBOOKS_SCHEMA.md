# Rulebooks Database Schema

Complete database schema for storing and searching VTM/MET rulebook content.

## Tables Overview

### 1. `rulebooks`
Main metadata table for all rulebooks.

**Columns:**
- `id` - Primary key
- `filename` - Original PDF filename (unique)
- `title` - Book title
- `book_code` - Publisher code (e.g., 5017, 5040)
- `category` - Book category (Core, Faction, Supplement, Blood Magic, Journal, Other)
- `system_type` - Game system (VTM, MET, MTA, WOD, Wraith)
- `page_count` - Number of pages
- `file_path` - Path to extracted JSON
- `pdf_path` - Path to original PDF
- `author` - Book author (from PDF metadata)
- `subject` - Subject (from PDF metadata)
- `extraction_date` - When PDF was extracted
- `last_indexed` - Last indexing timestamp
- `status` - Processing status (extracted, indexed, parsed, complete)
- `created_at`, `updated_at` - Timestamps

**Indexes:**
- Full-text search on `title`
- Indexes on `category`, `system_type`, `status`

---

### 2. `rulebook_pages`
Individual page content from each rulebook.

**Columns:**
- `id` - Primary key
- `rulebook_id` - Foreign key to rulebooks table
- `page_number` - Page number in book
- `page_text` - Full text content (LONGTEXT)
- `word_count` - Number of words on page
- `has_tables` - Boolean flag
- `has_images` - Boolean flag
- `created_at`, `updated_at` - Timestamps

**Indexes:**
- Full-text search on `page_text`
- Unique constraint on (rulebook_id, page_number)
- Index on `page_number`

---

### 3. `rulebook_sections`
Chapters and sections within rulebooks.

**Columns:**
- `id` - Primary key
- `rulebook_id` - Foreign key to rulebooks table
- `section_title` - Section/chapter name
- `section_type` - Type (chapter, appendix, index, etc.)
- `start_page` - First page of section
- `end_page` - Last page of section
- `parent_section_id` - For nested sections (self-referencing FK)
- `section_order` - Display order
- `description` - Optional section description
- `created_at`, `updated_at` - Timestamps

**Indexes:**
- Full-text search on `section_title`
- Indexes on `rulebook_id`, `parent_section_id`

---

### 4. `rulebook_rules`
Specific game rules and mechanics extracted from rulebooks.

**Columns:**
- `id` - Primary key
- `rulebook_id` - Foreign key to rulebooks table
- `rule_name` - Name of the rule
- `rule_category` - Category (Combat, Social, Disciplines, Merits, etc.)
- `rule_text` - Full rule description (LONGTEXT)
- `page_reference` - Page reference (e.g., "p.142", "pp.50-52")
- `related_terms` - JSON array of related keywords
- `created_at`, `updated_at` - Timestamps

**Indexes:**
- Full-text search on `rule_name` and `rule_text`
- Indexes on `rulebook_id`, `rule_category`

---

### 5. `rulebook_search_terms`
Searchable terms and keywords from rulebooks.

**Columns:**
- `id` - Primary key
- `term` - Search term/keyword
- `term_type` - Type (discipline, clan, merit, flaw, rule, etc.)
- `rulebook_id` - Foreign key to rulebooks table
- `page_number` - Page where term appears
- `context_snippet` - Brief context around the term
- `importance` - Importance level (low, medium, high, critical)
- `created_at` - Timestamp

**Indexes:**
- Full-text search on `term` and `context_snippet`
- Indexes on `term`, `term_type`, `rulebook_id`, `importance`

---

### 6. `rulebook_cross_references`
Links and references between different rulebooks.

**Columns:**
- `id` - Primary key
- `source_rulebook_id` - Source book (FK)
- `source_page` - Source page number
- `target_rulebook_id` - Referenced book (FK)
- `target_page` - Referenced page number
- `reference_type` - Type (see also, requires, expands, contradicts)
- `reference_text` - Description of reference
- `created_at` - Timestamp

**Indexes:**
- Indexes on both `source_rulebook_id` and `target_rulebook_id`
- Index on `reference_type`

---

## Key Features

### Full-Text Search
- All text columns have FULLTEXT indexes for fast searching
- Supports natural language search across book titles, content, rules

### Relationships
- Proper foreign key constraints with CASCADE delete
- Self-referencing sections for hierarchical content
- Cross-reference table for inter-book connections

### JSON Storage
- `related_terms` stored as JSON for flexible data
- Can store arrays of keywords, synonyms, related concepts

### Status Tracking
- Books track their processing status
- `last_indexed` timestamp for incremental updates

---

## Usage Examples

### Search for a discipline across all books
```sql
SELECT r.title, rp.page_number, rp.page_text
FROM rulebook_pages rp
JOIN rulebooks r ON rp.rulebook_id = r.id
WHERE MATCH(rp.page_text) AGAINST('Celerity' IN NATURAL LANGUAGE MODE);
```

### Find all rules in a specific category
```sql
SELECT rule_name, rule_text, page_reference, r.title as book_title
FROM rulebook_rules rr
JOIN rulebooks r ON rr.rulebook_id = r.id
WHERE rr.rule_category = 'Disciplines';
```

### Get all books in Camarilla faction guide
```sql
SELECT * FROM rulebooks 
WHERE category = 'Faction' 
AND title LIKE '%Camarilla%';
```

### Find cross-references from one book to others
```sql
SELECT 
    sr.title as source_book,
    xr.source_page,
    tr.title as target_book,
    xr.target_page,
    xr.reference_type
FROM rulebook_cross_references xr
JOIN rulebooks sr ON xr.source_rulebook_id = sr.id
JOIN rulebooks tr ON xr.target_rulebook_id = tr.id
WHERE sr.filename = 'MET - VTM - Camarilla Guide (5017).pdf';
```

