# Laws Agent MCP Setup Guide

## Overview

The Laws Agent MCP server provides Cursor AI access to the VTM/MET rules database through the `query_laws_agent` tool. It bypasses PHP authentication by connecting directly to the database and Anthropic API.

## Prerequisites

1. **Node.js** installed (v16+)
2. **MySQL database** with rulebooks tables populated
3. **Anthropic API key** configured
4. **Cursor AI** with MCP support

## Installation

### 1. Install Dependencies

```bash
cd G:\VbN\agents\laws_agent\scripts
npm install
```

This installs `mysql2` for database connectivity.

### 2. Configure Environment

The MCP server reads configuration from `.cursor/mcp.json`. Ensure these environment variables are set:

- `ANTHROPIC_API_KEY`: Your Anthropic API key
- `DB_HOST`: Database host (default: vdb5.pit.pair.com)
- `DB_USER`: Database username (default: working_64)
- `DB_PASS`: Database password (default: pcf577#1)
- `DB_NAME`: Database name (default: working_vbn)
- `ANTHROPIC_MODEL`: Claude model to use (default: claude-sonnet-4-20250514)

### 3. Restart Cursor

After configuring `.cursor/mcp.json`, restart Cursor to load the new MCP server.

## Usage

### In Cursor AI

Simply ask questions like:

```
Use the Laws Agent to tell me how Celerity works in MET
```

or

```
Query the Laws Agent: What are the Camarilla traditions?
```

### Supported Parameters

The `query_laws_agent` tool accepts:

- **question** (required): The rules question to ask
- **category** (optional): Filter by category (Core, Faction, Supplement, Blood Magic, Journal, Other)
- **system** (optional): Filter by system (MET-VTM, MET, VTM, MTA, WOD, Wraith)

### Example Queries

```
"How does Celerity work in Laws of the Night Revised?"
"What disciplines does clan Toreador have?"
"What are the character creation rules for starting disciplines?"
"What are the Six Traditions of the Camarilla?"
```

## How It Works

1. **Database Search**: Full-text search across 31 rulebooks (~4,500 pages)
2. **Context Building**: Top 5 relevant pages are formatted as context
3. **AI Synthesis**: Anthropic Claude generates a comprehensive answer
4. **Source Citations**: Every answer includes book name and page references

## Troubleshooting

### MCP Server Not Loading

1. Check `.cursor/mcp.json` configuration
2. Verify Node.js is installed: `node --version`
3. Check npm dependencies: `npm list` in `agents/laws_agent/scripts/` directory
4. Restart Cursor AI

### Database Connection Errors

1. Verify database credentials in `.cursor/mcp.json`
2. Test database connection manually:
   ```sql
   SELECT COUNT(*) FROM rulebooks;
   SELECT COUNT(*) FROM rulebook_pages;
   ```

### API Errors

1. Verify `ANTHROPIC_API_KEY` is correct
2. Check API credits/balance on Anthropic website
3. Verify model name matches available models

### No Results Found

1. Ensure rulebooks are imported into database
2. Check search query phrasing
3. Try broader/narrower questions

## Testing

Test the MCP server directly:

```bash
cd G:\VbN\agents\laws_agent\scripts
node mcp_laws_agent_v2.js
```

Then send JSON-RPC messages via stdin to test manually.

## Architecture

```
Cursor AI
    ↓
.cursor/mcp.json (configuration)
    ↓
node agents/laws_agent/scripts/mcp_laws_agent_v2.js
    ↓
    ├─→ MySQL Database (rulebooks, rulebook_pages)
    └─→ Anthropic API (Claude)
    ↓
Formatted Response with Citations
```

## Security Notes

- Database credentials are stored in `.cursor/mcp.json` (local file, not committed)
- API key is passed via environment variables
- No authentication bypass for web users (MCP only)
- Direct database access requires MySQL permissions

## Files

- `agents/laws_agent/scripts/mcp_laws_agent_v2.js` - MCP server implementation
- `agents/laws_agent/scripts/package.json` - Node.js dependencies
- `.cursor/mcp.json` - Cursor configuration
- `docs/LAWS_AGENT.md` - Full documentation
- `agents/laws_agent/api.php` - Web API (PHP authentication required)

## Support

For issues:
1. Check `LAWS_AGENT_IMPLEMENTATION_SUMMARY.md`
2. Review `docs/LAWS_AGENT.md` for API details
3. Test database connectivity
4. Verify Anthropic API status

---

**Status**: ✅ Configured and ready to use
**Date**: January 2025
