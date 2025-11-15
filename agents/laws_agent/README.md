# Laws Agent - Laws of the Night Integration

## Overview

The Laws Agent now integrates with the `agents/Laws_of_the_Night/` folder system to provide file-based knowledge search alongside the existing database-backed rulebook search. This creates a hybrid knowledge system that prioritizes Laws of the Night Revised content while maintaining database fallback functionality.

## Architecture

### Components

1. **LawsOfTheNightLoader** (`markdown_loader.php`)
   - Recursively scans `agents/Laws_of_the_Night/` directory
   - Parses markdown files with YAML frontmatter
   - Builds searchable index with relevance scoring
   - Provides search API for file-based content

2. **Laws Agent API** (`api.php`)
   - Integrates file-based search with database search
   - Prioritizes file results over database results
   - Combines results from both sources
   - Maintains backward compatibility

3. **Frontend** (`index.php`)
   - Displays file-based sources with file paths
   - Shows source attribution (file vs database)

## File Structure

```
agents/Laws_of_the_Night/
├── chapters/          # Chapter markdown files
├── clans/             # Clan markdown files
├── disciplines/       # Discipline markdown files
├── metadata.json      # Structured metadata
└── TOC.md            # Table of contents
```

## How It Works

### Indexing Process

1. On each search request, the loader scans the `Laws_of_the_Night/` directory
2. Parses each `.md` file to extract:
   - YAML frontmatter (title, chapter, section, tags)
   - Full text content
   - File metadata (path, modification time)
3. Categorizes files by directory (chapter, clan, discipline)
4. Builds in-memory search index

### Search Process

1. **File-based search** runs first:
   - Searches indexed markdown files
   - Scores results by relevance (title > section > content > tags)
   - Returns top 10 matches

2. **Database search** runs as fallback:
   - Uses existing `rulebook_pages` table search
   - Maintains all existing functionality

3. **Results are combined**:
   - File results prioritized (+50 relevance boost)
   - Sorted by combined relevance
   - Limited to top 15 total results

### Source Attribution

Results include `source_type` field:
- `'file'` - From Laws of the Night markdown files
- `'database'` - From rulebook_pages table

File-based sources include:
- `file_path` - Relative path to source file
- `title` - Extracted from frontmatter or filename
- `section` - Section name from frontmatter

## Adding New Content

To add new Laws of the Night content:

1. **Place markdown files** in appropriate subdirectory:
   - `chapters/` for chapter content
   - `clans/` for clan information
   - `disciplines/` for discipline rules

2. **Include YAML frontmatter**:
   ```yaml
   ---
   title: "Your Title"
   chapter: 4
   section: "Your Section"
   tags:
   - MET
   - LawsOfTheNight
   - Discipline
   ---
   ```

3. **Content is automatically indexed** on next search request

## Admin/Debug Features

### Debug Statistics

Get indexing statistics:
```
GET /agents/laws_agent/api.php?action=debug_stats
```

Returns:
- Files loaded count
- Files by category
- Last indexed timestamp
- Base path
- Any errors

### Test Search

Test file-based search:
```
GET /agents/laws_agent/api.php?action=test_search&query=celerity&category=discipline
```

Returns:
- Search results with relevance scores
- File paths and excerpts
- Result count

## Configuration

The loader uses the default path:
```
agents/Laws_of_the_Night/
```

This can be customized by passing a path to the `LawsOfTheNightLoader` constructor.

## Error Handling

- **File system errors**: Silently fall back to database search
- **Malformed markdown**: File is skipped, error logged
- **Missing directory**: Returns empty results, falls back to database
- **Parse errors**: Individual file errors logged, other files still indexed

## Performance Considerations

- Indexing happens on-demand (each search request)
- Consider caching the index if performance becomes an issue
- File modification times are tracked for potential cache invalidation

## API Integration

The integration is transparent to API consumers. The `ask` action works exactly as before, but now includes file-based results:

```php
// Existing API call works unchanged
$response = ask_laws_agent($conn, "How does Celerity work?", "Core", "MET-VTM");

// Response includes both file and database sources
// File sources have source_type='file' and include file_path
```

## Future Enhancements

Potential improvements:
- Persistent index caching (file-based or database)
- Incremental indexing (only scan changed files)
- Full-text search optimization
- Category-specific search endpoints
- Admin UI for managing file-based content

