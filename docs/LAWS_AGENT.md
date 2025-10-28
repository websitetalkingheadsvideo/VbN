# Laws Agent - AI-Powered VTM/MET Rules Assistant

Complete documentation for the Laws Agent system - an AI-powered assistant that answers questions about Vampire: The Masquerade and Mind's Eye Theatre rules, mechanics, and lore.

## Overview

The Laws Agent is an AI assistant powered by Anthropic Claude that provides accurate answers to VTM/MET questions by searching through 31 official rulebooks and synthesizing information with citations.

### Key Features

- **AI-Powered Answers**: Uses Anthropic Claude for intelligent, contextual responses
- **31 Rulebooks**: Access to full content of VTM/MET rulebooks (~4,500+ pages)
- **Source Citations**: All answers include book titles and page numbers
- **Multi-Interface**: Web UI, RESTful API, and MCP tool support
- **Authenticated Access**: Secure access for logged-in, verified users
- **Fast Search**: Full-text search with relevance ranking

## Access Requirements

### User Requirements
1. **Account**: Must have a registered account
2. **Email Verification**: Account email must be verified
3. **Login**: Must be logged in to access

### System Requirements
- **Database**: Rulebooks database tables must exist and be populated
- **API Key**: Anthropic API key must be configured in `config.env`
- **Web Server**: Apache/nginx with PHP 7.4+

## Web Interface

### Access
Navigate to: `http://yourdomain.com/admin/laws_agent.php`

Or click the "🧛 Laws Agent" button in the site header (visible when logged in)

### Using the Web Interface

1. **Ask Questions**:
   - Type your question in the input field
   - Press Enter or click "Ask" button
   - Wait for AI to search and respond

2. **Filter Results** (Optional):
   - **Category**: Core, Faction, Supplement, Blood Magic, Journal
   - **System**: MET-VTM, MET, VTM, MTA, WOD, Wraith

3. **View Sources**:
   - Each answer includes source citations
   - Click on sources to view excerpts
   - Sources show book name, page number, category, and system

4. **Suggestion Chips**:
   - Click pre-made question chips for quick examples
   - Useful for first-time users

### Example Questions

**Disciplines:**
- "How does Celerity work in MET?"
- "What are the levels of Dominate?"
- "Can Thaumaturgy be learned by non-Tremere?"

**Clans:**
- "What disciplines do Toreador have?"
- "What is the Nosferatu clan weakness?"
- "Tell me about the Gangrel clan"

**Mechanics:**
- "Explain combat challenges"
- "How does the test system work?"
- "What are overbids?"

**Traditions & Politics:**
- "What are the Six Traditions of the Camarilla?"
- "How does the Praxis system work?"
- "What is the role of a Prince?"

**Lore:**
- "What is the Masquerade?"
- "Tell me about Gehenna"
- "What are Blood Bonds?"

## API Usage

### Base Endpoint
```
http://yourdomain.com/admin/api_laws_agent.php
```

### Authentication
- Must have active PHP session with `user_id` set
- User's `email_verified` must be TRUE in database
- Returns 401/403 errors if not authenticated

### Actions

#### 1. Ask Question

**Request:**
```http
GET /admin/api_laws_agent.php?action=ask&question=How+does+Celerity+work
```

**Optional Parameters:**
- `category`: Filter by book category (Core, Faction, Supplement, Blood Magic, Journal, Other)
- `system`: Filter by game system (MET-VTM, MET, VTM, MTA, WOD, Wraith)

**Response:**
```json
{
  "success": true,
  "question": "How does Celerity work?",
  "answer": "According to the MET - VTM Reference Guide (Page 42), Celerity is a Discipline that allows vampires to move with supernatural speed...",
  "sources": [
    {
      "book": "MET - VTM Reference Guide",
      "page": 42,
      "category": "Core",
      "system": "MET-VTM",
      "excerpt": "Celerity allows the vampire to...",
      "relevance": 12.5
    }
  ],
  "ai_model": "claude-3-5-sonnet-20241022",
  "searched": true,
  "results_found": 3
}
```

**Error Response:**
```json
{
  "success": false,
  "error": "Email verification required"
}
```

#### 2. Health Check

**Request:**
```http
GET /admin/api_laws_agent.php?action=health
```

**Response:**
```json
{
  "success": true,
  "status": "online",
  "api_configured": true,
  "database": "connected",
  "authenticated": true
}
```

### JavaScript Example

```javascript
async function askLawsAgent(question, category = null, system = null) {
  const params = new URLSearchParams({
    action: 'ask',
    question: question
  });
  
  if (category) params.append('category', category);
  if (system) params.append('system', system);
  
  const response = await fetch(`/admin/api_laws_agent.php?${params}`);
  const data = await response.json();
  
  if (data.success) {
    console.log('Answer:', data.answer);
    console.log('Sources:', data.sources);
  } else {
    console.error('Error:', data.error);
  }
}

// Usage
askLawsAgent('How does Celerity work?', 'Core', 'MET-VTM');
```

### PHP Example

```php
<?php
session_start();
// Assuming user is logged in

$question = urlencode('How does Celerity work?');
$url = "http://localhost/admin/api_laws_agent.php?action=ask&question={$question}";

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['success']) {
    echo "Answer: " . $data['answer'] . "\n";
    foreach ($data['sources'] as $source) {
        echo "Source: {$source['book']}, Page {$source['page']}\n";
    }
}
?>
```

## MCP Tool Integration

### For Cursor AI / Other MCP Clients

The Laws Agent is available as an MCP tool for AI assistants.

### Tool Definition

**Name**: `query_laws_agent`

**Description**: Ask VTM/MET rules questions to the Laws Agent

**Parameters**:
- `question` (required): The rules question
- `category` (optional): Book category filter
- `system` (optional): Game system filter

### Usage in Cursor

The tool is automatically available to AI assistants in Cursor. Simply ask:

> "Can you use the Laws Agent to tell me how Celerity works?"

The AI will call the tool and present the formatted response.

### MCP Server Setup

To run the MCP server standalone:

```bash
node scripts/mcp_laws_agent.js
```

**Environment Variables**:
- `LAWS_AGENT_URL`: Base URL of your VbN installation (default: `http://localhost`)
- `PROJECT_PATH`: Path to VbN project (default: `G:\VbN`)

### Example MCP Response

```json
{
  "content": [
    {
      "type": "text",
      "text": "**Question:** How does Celerity work?\n\n**Answer:**\nAccording to the MET - VTM Reference Guide...\n\n**Sources:**\n1. MET - VTM Reference Guide (Page 42) - Core, MET-VTM\n2. MET - VTM Camarilla Guide (Page 89) - Faction, MET-VTM\n\n*Powered by claude-3-5-sonnet-20241022*"
    }
  ],
  "isError": false
}
```

## Technical Architecture

### Components

1. **Anthropic Helper** (`includes/anthropic_helper.php`)
   - Loads API key from config
   - Makes API calls to Anthropic
   - Handles errors and responses

2. **Laws Agent API** (`admin/api_laws_agent.php`)
   - Authentication checking
   - Database searching
   - Context building
   - AI integration
   - Response formatting

3. **Web Interface** (`admin/laws_agent.php`)
   - Chat-style UI
   - Real-time messaging
   - Source display
   - Filter controls

4. **MCP Tool** (`scripts/mcp_laws_agent.js`)
   - Tool definition
   - API wrapper
   - Response formatting

### Data Flow

```
User Question
    ↓
Authentication Check
    ↓
Database Search (Full-Text)
    ↓
Extract Top 5 Results
    ↓
Build Context from Excerpts
    ↓
Call Anthropic API
    ↓
Format Response with Citations
    ↓
Return to User
```

### AI Prompt Structure

**System Prompt:**
```
You are an expert on Vampire: The Masquerade and Mind's Eye Theatre rules and lore. 
Your role is to answer questions based ONLY on the provided rulebook excerpts above.

IMPORTANT RULES:
1. Always cite your sources using the format: (Source [number]: [Book Title], Page [page])
2. If the answer requires information from multiple sources, cite all relevant sources
3. If the excerpts don't contain enough information to fully answer the question, say so clearly
4. Do not make up or assume information not present in the excerpts
5. Be concise but thorough in your explanations
6. Use the exact terminology from the rulebooks
```

**User Prompt:**
```
Context from VTM/MET rulebooks:

[Source 1] {Book Title} (Page {page}, Category: {category}, System: {system}):
{excerpt text}

[Source 2] ...

Question: {user question}
```

## Configuration

### Anthropic API Key

Configure in `config.env`:
```env
ANTHROPIC_API_KEY=sk-ant-api03-...
```

Or in `.taskmaster/config.json` with key stored in environment.

### Model Selection

Default model: `claude-3-5-sonnet-20241022`

Can be changed in API call:
```php
call_anthropic($prompt, $system_prompt, $max_tokens, 'claude-3-opus-20240229');
```

### Search Parameters

**Default Limits:**
- Results per query: 5 sources
- Excerpt length: 800 characters
- Max tokens: 1500 (for AI response)

Modify in `admin/api_laws_agent.php`:
```php
$search_results = search_rulebooks($conn, $question, $category, $system, 10); // More results
```

## Error Handling

### HTTP Status Codes

- `200 OK`: Success
- `400 Bad Request`: Missing or invalid parameters
- `401 Unauthorized`: Not logged in
- `403 Forbidden`: Email not verified
- `500 Internal Server Error`: AI API error or database error
- `503 Service Unavailable`: Rate limit exceeded

### Common Errors

**"Email verification required"**
- User account not verified
- Check `users.email_verified` in database
- User must verify email first

**"Anthropic API key not configured"**
- API key missing from config
- Check `config.env` file
- Verify key is valid

**"No relevant information found"**
- Search returned no results
- Try rephrasing question
- Try removing filters
- Check database is populated

**"AI service error"**
- Anthropic API error
- Check API key validity
- Check rate limits
- Check network connection

## Performance

### Response Times

- **Database Search**: ~50-100ms
- **AI Processing**: ~2-5 seconds
- **Total**: ~2-6 seconds per question

### Optimization Tips

1. **Cache Common Questions**: Store frequently asked questions
2. **Increase Search Limit**: More context = better answers
3. **Adjust Max Tokens**: Balance quality vs. speed
4. **Use Filters**: Category/system filters reduce search time

### Rate Limiting

Anthropic API limits:
- Claude 3.5 Sonnet: 50 requests/minute
- Consider implementing application-level rate limiting

## Security

### Authentication
- Session-based authentication required
- Email verification enforced
- No anonymous access

### Data Protection
- User questions not logged by default
- API responses ephemeral
- Sources from trusted rulebooks only

### SQL Injection Prevention
- All queries use prepared statements
- Parameters properly escaped
- Input validation on all endpoints

## Troubleshooting

### Laws Agent Not Accessible

1. Check user is logged in
2. Verify email is verified in database
3. Check navigation link is visible

### No Answers Returned

1. Verify rulebooks database is populated
2. Check full-text indexes exist
3. Try simpler questions
4. Check API logs for errors

### AI Errors

1. Verify Anthropic API key in `config.env`
2. Check API key has credits
3. Test with health check endpoint
4. Review error messages

### Sources Not Displaying

1. Check database has `rulebook_pages` data
2. Verify search is returning results
3. Check excerpt extraction logic
4. Review JavaScript console for errors

## Maintenance

### Update Rulebooks

If rulebooks are updated or added:

```bash
# Re-extract PDFs
python scripts/extract_pdfs.py

# Re-import to database
php database/import_rulebooks.php
```

### Monitor Usage

Track API usage:
- Check Anthropic dashboard for usage
- Monitor response times
- Review error logs

### Update AI Model

To update to a newer Claude model:

```php
// In admin/api_laws_agent.php
$ai_response = call_anthropic($prompt, $system_prompt, 1500, 'claude-3-opus-20240229');
```

## Future Enhancements

### Planned Features

1. **Conversation History**: Multi-turn conversations
2. **Bookmarked Answers**: Save favorite responses
3. **Advanced Search**: Boolean operators, wildcards
4. **Rule Comparisons**: Compare rules across editions
5. **Export Answers**: PDF/text export
6. **Voice Input**: Speech-to-text questions
7. **Mobile App**: Native mobile interface

### Integration Ideas

1. **Character Sheet Integration**: Link rules to character creation
2. **Session Prep Tool**: ST planning assistance
3. **Plot Hook Generator**: Story ideas from lore
4. **Combat Calculator**: Automated challenge resolution

## Support & Resources

- **Rulebooks Database**: See `docs/RULEBOOKS_DATABASE.md`
- **API Reference**: This document
- **Issue Reporting**: Contact system administrator
- **Feature Requests**: Submit to development team

---

**Last Updated:** January 2025  
**Version:** 1.0  
**Status:** Production Ready  
**AI Model:** Claude 3.5 Sonnet (Anthropic)

