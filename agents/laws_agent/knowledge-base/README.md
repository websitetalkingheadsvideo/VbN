# Laws Agent Knowledge Base

This folder contains reference files that the Laws Agent will automatically read and include in its responses.

## How It Works

When you query the Laws Agent, it will:
1. Search the MySQL database of rulebooks
2. **Automatically read all files from this folder** (`.txt`, `.md`, `.mdx`, `.json`)
3. Include the knowledge base content along with database search results

## Supported File Types

- `.txt` - Plain text files
- `.md` - Markdown files
- `.mdx` - Markdown extended files
- `.json` - JSON files

## Usage

Simply place any reference files you want the Laws Agent to use in this folder. For example:

- `six_traditions.md` - Detailed information about the Six Traditions
- `house_rules.txt` - Custom house rules for your game
- `npc_templates.json` - Reference NPC templates
- `discipline_notes.md` - Additional notes about disciplines

## Example: Six Traditions File

Create a file like `six_traditions.md`:

```markdown
# The Six Traditions of the Camarilla (LotN Revised)

1. **The Masquerade** — Conceal vampiric nature from mortals at all times
2. **Domain** — A Prince (or rightful lord) holds the city; respect granted rights
3. **Progeny** — Do not Embrace without the Prince’s explicit leave
4. **Accounting** — A sire is responsible for a childe until formal Release
5. **Hospitality** — Present yourself to the Prince upon entering a city
6. **Destruction** — Only the Prince (or empowered elder) may grant Final Death
```

The Laws Agent will automatically include this information in its responses when relevant.

## Notes

- Files are read in alphabetical order
- All text files in this folder are included in every query
- Keep files concise - very large files may impact performance
- Files are read on-demand when queries are made


