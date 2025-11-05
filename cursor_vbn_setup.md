# VbN Setup
_Exported on 11/4/2025 at 23:03:22 MST from Cursor (2.0.54)_

---

**User**

Ask Laws Agent: Summarize the Camarilla tradition

---

**Cursor**


Querying the Laws Agent about the Camarilla tradition.


The Laws Agent returned an overview of the Camarilla sect. Asking specifically about the Six Traditions that govern vampire society:


Querying about Camarilla laws and traditions:


Trying one more query:


## Camarilla overview

**Structure:**
- Six full member clans: Brujah, Malkavian, Nosferatu, Toreador, Tremere, and Ventrue
- Other clans can join but are often viewed with suspicion and have limited power

**Governance:**
- Inner Circle: one representative from each member clan (typically the eldest)
- Justicars: six powerful vampires appointed by the Inner Circle to act as their representatives

**Philosophy:**
- Maintains the Masquerade (hiding from humans)
- Opposed to the Sabbat, which believes vampires should rule openly over humans

The Laws Agent doesn't have specific details about the Six Traditions (Masquerade, Domain, Progeny, Accounting, Hospitality, Destruction) in its current knowledge base, though it notes they exist.

Need details on a specific Tradition or aspect of Camarilla law?

---

**User**

can you tell the agent to return Masquerade, Domain, Progeny, Accounting, Hospitality, Destruction when asked about the traditions

---

**Cursor**


The Laws Agent queries rulebooks and isn't directly configurable. Checking for documentation on how it's set up:


Checking the MCP server implementation for a system prompt or instructions we can modify:


Updating the system prompt in the MCP server to include knowledge of the Six Traditions:


```javascript
#!/usr/bin/env node

/**
 * Laws Agent MCP Server v2
 * Direct database + API implementation (bypasses PHP authentication)
 */

const http = require('http');
const https = require('https');
const mysql = require('mysql2/promise');

// Configuration from environment or defaults
const DB_CONFIG = {
    host: process.env.DB_HOST || 'vdb5.pit.pair.com',
    user: process.env.DB_USER || 'working_64',
    password: process.env.DB_PASS || 'pcf577#1',
    database: process.env.DB_NAME || 'working_vbn',
    ssl: {
        rejectUnauthorized: false
    }
};

const ANTHROPIC_API_KEY = process.env.ANTHROPIC_API_KEY || 'your-anthropic-api-key-here';
const ANTHROPIC_MODEL = process.env.ANTHROPIC_MODEL || 'claude-sonnet-4-20250514';

/**
 * Get database connection
 */
async function getDbConnection() {
    try {
        const connection = await mysql.createConnection(DB_CONFIG);
        return connection;
    } catch (error) {
        throw new Error(`Database connection failed: ${error.message}`);
    }
}

/**
 * Search rulebooks for relevant content
 */
async function searchRulebooks(connection, query, category = null, system = null, limit = 5) {
    try {
        let sql = `
            SELECT 
                r.id as rulebook_id,
                r.title as book_title,
                r.category,
                r.system_type,
                rp.page_number,
                rp.page_text,
                MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM rulebook_pages rp
            JOIN rulebooks r ON rp.rulebook_id = r.id
            WHERE MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE)
        `;
        
        const params = [query, query];
        
        if (category) {
            sql += ` AND r.category = ?`;
            params.push(category);
        }
        
        if (system) {
            sql += ` AND r.system_type = ?`;
            params.push(system);
        }
        
        sql += ` ORDER BY relevance DESC LIMIT ?`;
        params.push(limit);
        
        const [rows] = await connection.query(sql, params);
        return rows;
    } catch (error) {
        throw new Error(`Search failed: ${error.message}`);
    }
}

/**
 * Extract excerpt from text
 */
function extractExcerpt(text, maxChars = 800) {
    text = text.replace(/\s+/g, ' ').trim();
    
    if (text.length <= maxChars) {
        return text;
    }
    
    const excerpt = text.substring(0, maxChars);
    const lastPeriod = excerpt.lastIndexOf('.');
    
    if (lastPeriod !== false && lastPeriod > maxChars * 0.7) {
        return text.substring(0, lastPeriod + 1);
    }
    
    return excerpt + '...';
}

/**
 * Build context from search results
 */
function buildContextFromResults(results) {
    if (!results || results.length === 0) {
        return "No relevant rulebook content found.";
    }
    
    let context = "Context from VTM/MET rulebooks:\n\n";
    
    results.forEach((result, i) => {
        const sourceNum = i + 1;
        const excerpt = extractExcerpt(result.page_text, 800);
        
        context += `[Source ${sourceNum}] ${result.book_title} (Page ${result.page_number}, Category: ${result.category}, System: ${result.system_type}):\n${excerpt}\n\n`;
    });
    
    return context;
}

/**
 * Call Anthropic API
 */
async function callAnthropicAPI(question, context) {
    return new Promise((resolve, reject) => {
        const systemPrompt = `You are a helpful assistant answering questions about Vampire: The Masquerade and Mind's Eye Theatre rules. Answer questions based on the provided context from official rulebooks. Always cite your sources by including [Book Name, Page X] citations in your response.

IMPORTANT: When asked about "Camarilla traditions" or "the Traditions," you should always mention the Six Traditions that govern vampire society:
1. The Masquerade - Keep the existence of vampires secret from mortals
2. Domain - Respect the domain rights of other vampires
3. Progeny - Do not create childer without permission
4. Accounting - Honor the debts you owe to other vampires
5. Hospitality - Honor the hospitality of other vampires and extend your own
6. Destruction - Do not destroy another vampire without permission

These are fundamental laws of the Camarilla, even if specific details aren't found in the search results.`;
        
        const userPrompt = `${question}\n\n${context}`;
        
        const data = {
            model: ANTHROPIC_MODEL,
            max_tokens: 2000,
            messages: [
                {
                    role: 'user',
                    content: userPrompt
                }
            ],
            system: systemPrompt
        };
        
        const options = {
            hostname: 'api.anthropic.com',
            port: 443,
            path: '/v1/messages',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-api-key': ANTHROPIC_API_KEY,
                'anthropic-version': '2023-06-01'
            }
        };
        
        const req = https.request(options, (res) => {
            let data = '';
            
            res.on('data', (chunk) => {
                data += chunk;
            });
            
            res.on('end', () => {
                try {
                    const result = JSON.parse(data);
                    
                    if (res.statusCode !== 200) {
                        reject(new Error(`API error: ${result.error?.message || 'Unknown error'}`));
                        return;
                    }
                    
                    if (result.content && result.content[0] && result.content[0].text) {
                        resolve({
                            answer: result.content[0].text,
                            model: result.model
                        });
                    } else {
                        reject(new Error('Unexpected API response format'));
                    }
                } catch (error) {
                    reject(new Error(`Failed to parse API response: ${error.message}`));
                }
            });
        });
        
        req.on('error', (error) => {
            reject(new Error(`API request failed: ${error.message}`));
        });
        
        req.setTimeout(60000, () => {
            req.abort();
            reject(new Error('API request timed out'));
        });
        
        req.write(JSON.stringify(data));
        req.end();
    });
}

/**
 * Ask Laws Agent (main function)
 */
async function askLawsAgent(question, category = null, system = null) {
    try {
        // Get database connection
        const connection = await getDbConnection();
        
        try {
            // Search rulebooks
            const searchResults = await searchRulebooks(connection, question, category, system, 5);
            
            if (!searchResults || searchResults.length === 0) {
                return {
                    success: true,
                    question: question,
                    answer: "I couldn't find any relevant information in the rulebooks to answer that question. Please try rephrasing or being more specific.",
                    sources: [],
                    ai_model: ANTHROPIC_MODEL,
                    searched: true,
                    results_found: 0
                };
            }
            
            // Build context
            const context = buildContextFromResults(searchResults);
            
            // Call Anthropic API
            const aiResponse = await callAnthropicAPI(question, context);
            
            // Format sources
            const sources = searchResults.map(result => ({
                book: result.book_title,
                page: result.page_number,
                category: result.category,
                system: result.system_type,
                excerpt: extractExcerpt(result.page_text, 300),
                relevance: parseFloat(result.relevance)
            }));
            
            return {
                success: true,
                question: question,
                answer: aiResponse.answer,
                sources: sources,
                ai_model: aiResponse.model,
                searched: true,
                results_found: searchResults.length
            };
        } finally {
            await connection.end();
        }
    } catch (error) {
        return {
            success: false,
            error: error.message,
            question: question
        };
    }
}

/**
 * MCP Server Implementation
 */
class LawsAgentMCPServer {
    constructor() {
        this.tools = [{
            name: 'query_laws_agent',
            description: 'Ask VTM/MET rules questions to the Laws Agent. Powered by AI with access to 31 official rulebooks covering game mechanics, disciplines, clans, lore, and more.',
            inputSchema: {
                type: 'object',
                properties: {
                    question: {
                        type: 'string',
                        description: 'The rules question to ask (e.g., "How does Celerity work?", "What are the Camarilla traditions?")'
                    },
                    category: {
                        type: 'string',
                        enum: ['Core', 'Faction', 'Supplement', 'Blood Magic', 'Journal', 'Other'],
                        description: 'Optional: Filter by book category to narrow search'
                    },
                    system: {
                        type: 'string',
                        enum: ['MET-VTM', 'MET', 'VTM', 'MTA', 'WOD', 'Wraith'],
                        description: 'Optional: Filter by game system to narrow search'
                    }
                },
                required: ['question']
            }
        }];
    }
    
    async handleToolCall(toolName, args) {
        if (toolName === 'query_laws_agent') {
            try {
                const response = await askLawsAgent(
                    args.question,
                    args.category || null,
                    args.system || null
                );
                
                if (!response.success) {
                    return {
                        content: [{
                            type: 'text',
                            text: `Error: ${response.error || 'Unknown error'}`
                        }],
                        isError: true
                    };
                }
                
                let formattedText = `**Question:** ${response.question}\n\n`;
                formattedText += `**Answer:**\n${response.answer}\n\n`;
                
                if (response.sources && response.sources.length > 0) {
                    formattedText += `**Sources:**\n`;
                    response.sources.forEach((source, index) => {
                        formattedText += `${index + 1}. ${source.book} (Page ${source.page}) - ${source.category}, ${source.system}\n`;
                    });
                }
                
                if (response.ai_model) {
                    formattedText += `\n*Powered by ${response.ai_model}*`;
                }
                
                return {
                    content: [{
                        type: 'text',
                        text: formattedText
                    }],
                    isError: false
                };
            } catch (error) {
                return {
                    content: [{
                        type: 'text',
                        text: `Failed to query Laws Agent: ${error.message}`
                    }],
                    isError: true
                };
            }
        }
        
        return {
            content: [{
                type: 'text',
                text: `Unknown tool: ${toolName}`
            }],
            isError: true
        };
    }
    
    async run() {
        process.stdin.setEncoding('utf8');
        
        let buffer = '';
        
        process.stdin.on('data', async (chunk) => {
            buffer += chunk;
            
            const lines = buffer.split('\n');
            buffer = lines.pop();
            
            for (const line of lines) {
                if (!line.trim()) continue;
                
                try {
                    const message = JSON.parse(line);
                    const response = await this.handleMessage(message);
                    
                    if (response) {
                        process.stdout.write(JSON.stringify(response) + '\n');
                    }
                } catch (error) {
                    console.error('Error processing message:', error);
                }
            }
        });
        
        process.stdin.on('end', () => {
            process.exit(0);
        });
    }
    
    async handleMessage(message) {
        switch (message.method) {
            case 'initialize':
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result: {
                        protocolVersion: '2024-11-05',
                        capabilities: {
                            tools: {}
                        },
                        serverInfo: {
                            name: 'laws-agent',
                            version: '2.0.0'
                        }
                    }
                };
                
            case 'tools/list':
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result: {
                        tools: this.tools
                    }
                };
                
            case 'tools/call':
                const result = await this.handleToolCall(
                    message.params.name,
                    message.params.arguments || {}
                );
                
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result
                };
                
            default:
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    error: {
                        code: -32601,
                        message: `Method not found: ${message.method}`
                    }
                };
        }
    }
}

// Run server
if (require.main === module) {
    const server = new LawsAgentMCPServer();
    server.run().catch(error => {
        console.error('Fatal error:', error);
        process.exit(1);
    });
}

module.exports = LawsAgentMCPServer;

```

Updated the Laws Agent's system prompt to include the Six Traditions. It will mention them when asked about Camarilla traditions.

**Changes made:**
- Updated the system prompt in `scripts/mcp_laws_agent_v2.js` to include the Six Traditions
- The agent will now mention Masquerade, Domain, Progeny, Accounting, Hospitality, and Destruction when asked about traditions

**Important:** Restart Cursor for the changes to take effect, since the MCP server loads on startup.

After restarting, when you ask "What are the Camarilla traditions?" the agent should list all six traditions even if the search results don't include full details.

Should I test this or make any adjustments to the tradition descriptions?

---

**User**

Use tool query_laws_agent with question: "List and briefly explain the Six Traditions (Masquerade, Domain, Progeny, Accounting, Hospitality, Destruction) and cite core sources."; category: "Core"; system: "VTM"

---

**Cursor**


Querying the Laws Agent about the Six Traditions to verify the update:


The agent returned no results, likely because the MCP server hasn't been restarted. Restart Cursor to load the updated system prompt.

Noting that the code returns early when there are no search results, so the system prompt isn't used. Updating it to still invoke the AI for tradition questions even with no results:


Updating the code to call the AI for tradition-related questions even when there are no search results, so the system prompt can respond:


```javascript
#!/usr/bin/env node

/**
 * Laws Agent MCP Server v2
 * Direct database + API implementation (bypasses PHP authentication)
 */

const http = require('http');
const https = require('https');
const mysql = require('mysql2/promise');

// Configuration from environment or defaults
const DB_CONFIG = {
    host: process.env.DB_HOST || 'vdb5.pit.pair.com',
    user: process.env.DB_USER || 'working_64',
    password: process.env.DB_PASS || 'pcf577#1',
    database: process.env.DB_NAME || 'working_vbn',
    ssl: {
        rejectUnauthorized: false
    }
};

const ANTHROPIC_API_KEY = process.env.ANTHROPIC_API_KEY || 'your-anthropic-api-key-here';
const ANTHROPIC_MODEL = process.env.ANTHROPIC_MODEL || 'claude-sonnet-4-20250514';

/**
 * Get database connection
 */
async function getDbConnection() {
    try {
        const connection = await mysql.createConnection(DB_CONFIG);
        return connection;
    } catch (error) {
        throw new Error(`Database connection failed: ${error.message}`);
    }
}

/**
 * Search rulebooks for relevant content
 */
async function searchRulebooks(connection, query, category = null, system = null, limit = 5) {
    try {
        let sql = `
            SELECT 
                r.id as rulebook_id,
                r.title as book_title,
                r.category,
                r.system_type,
                rp.page_number,
                rp.page_text,
                MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM rulebook_pages rp
            JOIN rulebooks r ON rp.rulebook_id = r.id
            WHERE MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE)
        `;
        
        const params = [query, query];
        
        if (category) {
            sql += ` AND r.category = ?`;
            params.push(category);
        }
        
        if (system) {
            sql += ` AND r.system_type = ?`;
            params.push(system);
        }
        
        sql += ` ORDER BY relevance DESC LIMIT ?`;
        params.push(limit);
        
        const [rows] = await connection.query(sql, params);
        return rows;
    } catch (error) {
        throw new Error(`Search failed: ${error.message}`);
    }
}

/**
 * Extract excerpt from text
 */
function extractExcerpt(text, maxChars = 800) {
    text = text.replace(/\s+/g, ' ').trim();
    
    if (text.length <= maxChars) {
        return text;
    }
    
    const excerpt = text.substring(0, maxChars);
    const lastPeriod = excerpt.lastIndexOf('.');
    
    if (lastPeriod !== false && lastPeriod > maxChars * 0.7) {
        return text.substring(0, lastPeriod + 1);
    }
    
    return excerpt + '...';
}

/**
 * Build context from search results
 */
function buildContextFromResults(results) {
    if (!results || results.length === 0) {
        return "No relevant rulebook content found.";
    }
    
    let context = "Context from VTM/MET rulebooks:\n\n";
    
    results.forEach((result, i) => {
        const sourceNum = i + 1;
        const excerpt = extractExcerpt(result.page_text, 800);
        
        context += `[Source ${sourceNum}] ${result.book_title} (Page ${result.page_number}, Category: ${result.category}, System: ${result.system_type}):\n${excerpt}\n\n`;
    });
    
    return context;
}

/**
 * Call Anthropic API
 */
async function callAnthropicAPI(question, context) {
    return new Promise((resolve, reject) => {
        const systemPrompt = `You are a helpful assistant answering questions about Vampire: The Masquerade and Mind's Eye Theatre rules. Answer questions based on the provided context from official rulebooks. Always cite your sources by including [Book Name, Page X] citations in your response.

IMPORTANT: When asked about "Camarilla traditions" or "the Traditions," you should always mention the Six Traditions that govern vampire society:
1. The Masquerade - Keep the existence of vampires secret from mortals
2. Domain - Respect the domain rights of other vampires
3. Progeny - Do not create childer without permission
4. Accounting - Honor the debts you owe to other vampires
5. Hospitality - Honor the hospitality of other vampires and extend your own
6. Destruction - Do not destroy another vampire without permission

These are fundamental laws of the Camarilla, even if specific details aren't found in the search results.`;
        
        const userPrompt = `${question}\n\n${context}`;
        
        const data = {
            model: ANTHROPIC_MODEL,
            max_tokens: 2000,
            messages: [
                {
                    role: 'user',
                    content: userPrompt
                }
            ],
            system: systemPrompt
        };
        
        const options = {
            hostname: 'api.anthropic.com',
            port: 443,
            path: '/v1/messages',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-api-key': ANTHROPIC_API_KEY,
                'anthropic-version': '2023-06-01'
            }
        };
        
        const req = https.request(options, (res) => {
            let data = '';
            
            res.on('data', (chunk) => {
                data += chunk;
            });
            
            res.on('end', () => {
                try {
                    const result = JSON.parse(data);
                    
                    if (res.statusCode !== 200) {
                        reject(new Error(`API error: ${result.error?.message || 'Unknown error'}`));
                        return;
                    }
                    
                    if (result.content && result.content[0] && result.content[0].text) {
                        resolve({
                            answer: result.content[0].text,
                            model: result.model
                        });
                    } else {
                        reject(new Error('Unexpected API response format'));
                    }
                } catch (error) {
                    reject(new Error(`Failed to parse API response: ${error.message}`));
                }
            });
        });
        
        req.on('error', (error) => {
            reject(new Error(`API request failed: ${error.message}`));
        });
        
        req.setTimeout(60000, () => {
            req.abort();
            reject(new Error('API request timed out'));
        });
        
        req.write(JSON.stringify(data));
        req.end();
    });
}

/**
 * Ask Laws Agent (main function)
 */
async function askLawsAgent(question, category = null, system = null) {
    try {
        // Get database connection
        const connection = await getDbConnection();
        
        try {
            // Search rulebooks
            const searchResults = await searchRulebooks(connection, question, category, system, 5);
            
            // Check if question is about Camarilla traditions/Traditions
            const isTraditionQuestion = /\b(traditions?|masquerade|domain|progeny|accounting|hospitality|destruction)\b/i.test(question);
            
            // If no results but it's a tradition question, still call AI with system prompt knowledge
            if ((!searchResults || searchResults.length === 0) && !isTraditionQuestion) {
                return {
                    success: true,
                    question: question,
                    answer: "I couldn't find any relevant information in the rulebooks to answer that question. Please try rephrasing or being more specific.",
                    sources: [],
                    ai_model: ANTHROPIC_MODEL,
                    searched: true,
                    results_found: 0
                };
            }
            
            // Build context (may be empty for tradition questions without results)
            const context = searchResults && searchResults.length > 0 
                ? buildContextFromResults(searchResults)
                : "No specific rulebook excerpts found, but answer based on fundamental knowledge of the Six Traditions.";
            
            // Call Anthropic API
            const aiResponse = await callAnthropicAPI(question, context);
            
            // Format sources
            const sources = searchResults.map(result => ({
                book: result.book_title,
                page: result.page_number,
                category: result.category,
                system: result.system_type,
                excerpt: extractExcerpt(result.page_text, 300),
                relevance: parseFloat(result.relevance)
            }));
            
            return {
                success: true,
                question: question,
                answer: aiResponse.answer,
                sources: sources,
                ai_model: aiResponse.model,
                searched: true,
                results_found: searchResults.length
            };
        } finally {
            await connection.end();
        }
    } catch (error) {
        return {
            success: false,
            error: error.message,
            question: question
        };
    }
}

/**
 * MCP Server Implementation
 */
class LawsAgentMCPServer {
    constructor() {
        this.tools = [{
            name: 'query_laws_agent',
            description: 'Ask VTM/MET rules questions to the Laws Agent. Powered by AI with access to 31 official rulebooks covering game mechanics, disciplines, clans, lore, and more.',
            inputSchema: {
                type: 'object',
                properties: {
                    question: {
                        type: 'string',
                        description: 'The rules question to ask (e.g., "How does Celerity work?", "What are the Camarilla traditions?")'
                    },
                    category: {
                        type: 'string',
                        enum: ['Core', 'Faction', 'Supplement', 'Blood Magic', 'Journal', 'Other'],
                        description: 'Optional: Filter by book category to narrow search'
                    },
                    system: {
                        type: 'string',
                        enum: ['MET-VTM', 'MET', 'VTM', 'MTA', 'WOD', 'Wraith'],
                        description: 'Optional: Filter by game system to narrow search'
                    }
                },
                required: ['question']
            }
        }];
    }
    
    async handleToolCall(toolName, args) {
        if (toolName === 'query_laws_agent') {
            try {
                const response = await askLawsAgent(
                    args.question,
                    args.category || null,
                    args.system || null
                );
                
                if (!response.success) {
                    return {
                        content: [{
                            type: 'text',
                            text: `Error: ${response.error || 'Unknown error'}`
                        }],
                        isError: true
                    };
                }
                
                let formattedText = `**Question:** ${response.question}\n\n`;
                formattedText += `**Answer:**\n${response.answer}\n\n`;
                
                if (response.sources && response.sources.length > 0) {
                    formattedText += `**Sources:**\n`;
                    response.sources.forEach((source, index) => {
                        formattedText += `${index + 1}. ${source.book} (Page ${source.page}) - ${source.category}, ${source.system}\n`;
                    });
                }
                
                if (response.ai_model) {
                    formattedText += `\n*Powered by ${response.ai_model}*`;
                }
                
                return {
                    content: [{
                        type: 'text',
                        text: formattedText
                    }],
                    isError: false
                };
            } catch (error) {
                return {
                    content: [{
                        type: 'text',
                        text: `Failed to query Laws Agent: ${error.message}`
                    }],
                    isError: true
                };
            }
        }
        
        return {
            content: [{
                type: 'text',
                text: `Unknown tool: ${toolName}`
            }],
            isError: true
        };
    }
    
    async run() {
        process.stdin.setEncoding('utf8');
        
        let buffer = '';
        
        process.stdin.on('data', async (chunk) => {
            buffer += chunk;
            
            const lines = buffer.split('\n');
            buffer = lines.pop();
            
            for (const line of lines) {
                if (!line.trim()) continue;
                
                try {
                    const message = JSON.parse(line);
                    const response = await this.handleMessage(message);
                    
                    if (response) {
                        process.stdout.write(JSON.stringify(response) + '\n');
                    }
                } catch (error) {
                    console.error('Error processing message:', error);
                }
            }
        });
        
        process.stdin.on('end', () => {
            process.exit(0);
        });
    }
    
    async handleMessage(message) {
        switch (message.method) {
            case 'initialize':
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result: {
                        protocolVersion: '2024-11-05',
                        capabilities: {
                            tools: {}
                        },
                        serverInfo: {
                            name: 'laws-agent',
                            version: '2.0.0'
                        }
                    }
                };
                
            case 'tools/list':
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result: {
                        tools: this.tools
                    }
                };
                
            case 'tools/call':
                const result = await this.handleToolCall(
                    message.params.name,
                    message.params.arguments || {}
                );
                
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result
                };
                
            default:
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    error: {
                        code: -32601,
                        message: `Method not found: ${message.method}`
                    }
                };
        }
    }
}

// Run server
if (require.main === module) {
    const server = new LawsAgentMCPServer();
    server.run().catch(error => {
        console.error('Fatal error:', error);
        process.exit(1);
    });
}

module.exports = LawsAgentMCPServer;

```

Fixing a potential bug: if there are no search results, we still try to map them. Updating the code:


```javascript
#!/usr/bin/env node

/**
 * Laws Agent MCP Server v2
 * Direct database + API implementation (bypasses PHP authentication)
 */

const http = require('http');
const https = require('https');
const mysql = require('mysql2/promise');

// Configuration from environment or defaults
const DB_CONFIG = {
    host: process.env.DB_HOST || 'vdb5.pit.pair.com',
    user: process.env.DB_USER || 'working_64',
    password: process.env.DB_PASS || 'pcf577#1',
    database: process.env.DB_NAME || 'working_vbn',
    ssl: {
        rejectUnauthorized: false
    }
};

const ANTHROPIC_API_KEY = process.env.ANTHROPIC_API_KEY || 'your-anthropic-api-key-here';
const ANTHROPIC_MODEL = process.env.ANTHROPIC_MODEL || 'claude-sonnet-4-20250514';

/**
 * Get database connection
 */
async function getDbConnection() {
    try {
        const connection = await mysql.createConnection(DB_CONFIG);
        return connection;
    } catch (error) {
        throw new Error(`Database connection failed: ${error.message}`);
    }
}

/**
 * Search rulebooks for relevant content
 */
async function searchRulebooks(connection, query, category = null, system = null, limit = 5) {
    try {
        let sql = `
            SELECT 
                r.id as rulebook_id,
                r.title as book_title,
                r.category,
                r.system_type,
                rp.page_number,
                rp.page_text,
                MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
            FROM rulebook_pages rp
            JOIN rulebooks r ON rp.rulebook_id = r.id
            WHERE MATCH(rp.page_text) AGAINST(? IN NATURAL LANGUAGE MODE)
        `;
        
        const params = [query, query];
        
        if (category) {
            sql += ` AND r.category = ?`;
            params.push(category);
        }
        
        if (system) {
            sql += ` AND r.system_type = ?`;
            params.push(system);
        }
        
        sql += ` ORDER BY relevance DESC LIMIT ?`;
        params.push(limit);
        
        const [rows] = await connection.query(sql, params);
        return rows;
    } catch (error) {
        throw new Error(`Search failed: ${error.message}`);
    }
}

/**
 * Extract excerpt from text
 */
function extractExcerpt(text, maxChars = 800) {
    text = text.replace(/\s+/g, ' ').trim();
    
    if (text.length <= maxChars) {
        return text;
    }
    
    const excerpt = text.substring(0, maxChars);
    const lastPeriod = excerpt.lastIndexOf('.');
    
    if (lastPeriod !== false && lastPeriod > maxChars * 0.7) {
        return text.substring(0, lastPeriod + 1);
    }
    
    return excerpt + '...';
}

/**
 * Build context from search results
 */
function buildContextFromResults(results) {
    if (!results || results.length === 0) {
        return "No relevant rulebook content found.";
    }
    
    let context = "Context from VTM/MET rulebooks:\n\n";
    
    results.forEach((result, i) => {
        const sourceNum = i + 1;
        const excerpt = extractExcerpt(result.page_text, 800);
        
        context += `[Source ${sourceNum}] ${result.book_title} (Page ${result.page_number}, Category: ${result.category}, System: ${result.system_type}):\n${excerpt}\n\n`;
    });
    
    return context;
}

/**
 * Call Anthropic API
 */
async function callAnthropicAPI(question, context) {
    return new Promise((resolve, reject) => {
        const systemPrompt = `You are a helpful assistant answering questions about Vampire: The Masquerade and Mind's Eye Theatre rules. Answer questions based on the provided context from official rulebooks. Always cite your sources by including [Book Name, Page X] citations in your response.

IMPORTANT: When asked about "Camarilla traditions" or "the Traditions," you should always mention the Six Traditions that govern vampire society:
1. The Masquerade - Keep the existence of vampires secret from mortals
2. Domain - Respect the domain rights of other vampires
3. Progeny - Do not create childer without permission
4. Accounting - Honor the debts you owe to other vampires
5. Hospitality - Honor the hospitality of other vampires and extend your own
6. Destruction - Do not destroy another vampire without permission

These are fundamental laws of the Camarilla, even if specific details aren't found in the search results.`;
        
        const userPrompt = `${question}\n\n${context}`;
        
        const data = {
            model: ANTHROPIC_MODEL,
            max_tokens: 2000,
            messages: [
                {
                    role: 'user',
                    content: userPrompt
                }
            ],
            system: systemPrompt
        };
        
        const options = {
            hostname: 'api.anthropic.com',
            port: 443,
            path: '/v1/messages',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-api-key': ANTHROPIC_API_KEY,
                'anthropic-version': '2023-06-01'
            }
        };
        
        const req = https.request(options, (res) => {
            let data = '';
            
            res.on('data', (chunk) => {
                data += chunk;
            });
            
            res.on('end', () => {
                try {
                    const result = JSON.parse(data);
                    
                    if (res.statusCode !== 200) {
                        reject(new Error(`API error: ${result.error?.message || 'Unknown error'}`));
                        return;
                    }
                    
                    if (result.content && result.content[0] && result.content[0].text) {
                        resolve({
                            answer: result.content[0].text,
                            model: result.model
                        });
                    } else {
                        reject(new Error('Unexpected API response format'));
                    }
                } catch (error) {
                    reject(new Error(`Failed to parse API response: ${error.message}`));
                }
            });
        });
        
        req.on('error', (error) => {
            reject(new Error(`API request failed: ${error.message}`));
        });
        
        req.setTimeout(60000, () => {
            req.abort();
            reject(new Error('API request timed out'));
        });
        
        req.write(JSON.stringify(data));
        req.end();
    });
}

/**
 * Ask Laws Agent (main function)
 */
async function askLawsAgent(question, category = null, system = null) {
    try {
        // Get database connection
        const connection = await getDbConnection();
        
        try {
            // Search rulebooks
            const searchResults = await searchRulebooks(connection, question, category, system, 5);
            
            // Check if question is about Camarilla traditions/Traditions
            const isTraditionQuestion = /\b(traditions?|masquerade|domain|progeny|accounting|hospitality|destruction)\b/i.test(question);
            
            // If no results but it's a tradition question, still call AI with system prompt knowledge
            if ((!searchResults || searchResults.length === 0) && !isTraditionQuestion) {
                return {
                    success: true,
                    question: question,
                    answer: "I couldn't find any relevant information in the rulebooks to answer that question. Please try rephrasing or being more specific.",
                    sources: [],
                    ai_model: ANTHROPIC_MODEL,
                    searched: true,
                    results_found: 0
                };
            }
            
            // Build context (may be empty for tradition questions without results)
            const context = searchResults && searchResults.length > 0 
                ? buildContextFromResults(searchResults)
                : "No specific rulebook excerpts found, but answer based on fundamental knowledge of the Six Traditions.";
            
            // Call Anthropic API
            const aiResponse = await callAnthropicAPI(question, context);
            
            // Format sources (handle empty results)
            const sources = (searchResults && searchResults.length > 0) 
                ? searchResults.map(result => ({
                    book: result.book_title,
                    page: result.page_number,
                    category: result.category,
                    system: result.system_type,
                    excerpt: extractExcerpt(result.page_text, 300),
                    relevance: parseFloat(result.relevance)
                }))
                : [];
            
            return {
                success: true,
                question: question,
                answer: aiResponse.answer,
                sources: sources,
                ai_model: aiResponse.model,
                searched: true,
                results_found: searchResults.length
            };
        } finally {
            await connection.end();
        }
    } catch (error) {
        return {
            success: false,
            error: error.message,
            question: question
        };
    }
}

/**
 * MCP Server Implementation
 */
class LawsAgentMCPServer {
    constructor() {
        this.tools = [{
            name: 'query_laws_agent',
            description: 'Ask VTM/MET rules questions to the Laws Agent. Powered by AI with access to 31 official rulebooks covering game mechanics, disciplines, clans, lore, and more.',
            inputSchema: {
                type: 'object',
                properties: {
                    question: {
                        type: 'string',
                        description: 'The rules question to ask (e.g., "How does Celerity work?", "What are the Camarilla traditions?")'
                    },
                    category: {
                        type: 'string',
                        enum: ['Core', 'Faction', 'Supplement', 'Blood Magic', 'Journal', 'Other'],
                        description: 'Optional: Filter by book category to narrow search'
                    },
                    system: {
                        type: 'string',
                        enum: ['MET-VTM', 'MET', 'VTM', 'MTA', 'WOD', 'Wraith'],
                        description: 'Optional: Filter by game system to narrow search'
                    }
                },
                required: ['question']
            }
        }];
    }
    
    async handleToolCall(toolName, args) {
        if (toolName === 'query_laws_agent') {
            try {
                const response = await askLawsAgent(
                    args.question,
                    args.category || null,
                    args.system || null
                );
                
                if (!response.success) {
                    return {
                        content: [{
                            type: 'text',
                            text: `Error: ${response.error || 'Unknown error'}`
                        }],
                        isError: true
                    };
                }
                
                let formattedText = `**Question:** ${response.question}\n\n`;
                formattedText += `**Answer:**\n${response.answer}\n\n`;
                
                if (response.sources && response.sources.length > 0) {
                    formattedText += `**Sources:**\n`;
                    response.sources.forEach((source, index) => {
                        formattedText += `${index + 1}. ${source.book} (Page ${source.page}) - ${source.category}, ${source.system}\n`;
                    });
                }
                
                if (response.ai_model) {
                    formattedText += `\n*Powered by ${response.ai_model}*`;
                }
                
                return {
                    content: [{
                        type: 'text',
                        text: formattedText
                    }],
                    isError: false
                };
            } catch (error) {
                return {
                    content: [{
                        type: 'text',
                        text: `Failed to query Laws Agent: ${error.message}`
                    }],
                    isError: true
                };
            }
        }
        
        return {
            content: [{
                type: 'text',
                text: `Unknown tool: ${toolName}`
            }],
            isError: true
        };
    }
    
    async run() {
        process.stdin.setEncoding('utf8');
        
        let buffer = '';
        
        process.stdin.on('data', async (chunk) => {
            buffer += chunk;
            
            const lines = buffer.split('\n');
            buffer = lines.pop();
            
            for (const line of lines) {
                if (!line.trim()) continue;
                
                try {
                    const message = JSON.parse(line);
                    const response = await this.handleMessage(message);
                    
                    if (response) {
                        process.stdout.write(JSON.stringify(response) + '\n');
                    }
                } catch (error) {
                    console.error('Error processing message:', error);
                }
            }
        });
        
        process.stdin.on('end', () => {
            process.exit(0);
        });
    }
    
    async handleMessage(message) {
        switch (message.method) {
            case 'initialize':
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result: {
                        protocolVersion: '2024-11-05',
                        capabilities: {
                            tools: {}
                        },
                        serverInfo: {
                            name: 'laws-agent',
                            version: '2.0.0'
                        }
                    }
                };
                
            case 'tools/list':
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result: {
                        tools: this.tools
                    }
                };
                
            case 'tools/call':
                const result = await this.handleToolCall(
                    message.params.name,
                    message.params.arguments || {}
                );
                
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result
                };
                
            default:
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    error: {
                        code: -32601,
                        message: `Method not found: ${message.method}`
                    }
                };
        }
    }
}

// Run server
if (require.main === module) {
    const server = new LawsAgentMCPServer();
    server.run().catch(error => {
        console.error('Fatal error:', error);
        process.exit(1);
    });
}

module.exports = LawsAgentMCPServer;

```

Checking for linting errors:


Updated the Laws Agent to handle tradition questions:

1. **Enhanced system prompt** — includes the Six Traditions
2. **Improved handling** — for tradition-related questions, it still calls the AI even if no search results are found, so the system prompt can respond
3. **Pattern detection** — detects questions about traditions (keywords: traditions, masquerade, domain, progeny, accounting, hospitality, destruction)

**Important:** Restart Cursor for these changes to take effect. The MCP server loads on startup, so the updated code won't run until you restart.

After restarting, when you ask about the Six Traditions, the agent should:
- List all six traditions even if the rulebooks don't have detailed entries
- Provide brief explanations based on the system prompt
- Still cite sources if it finds relevant rulebook pages

Should I test again after you restart, or make any other adjustments?

