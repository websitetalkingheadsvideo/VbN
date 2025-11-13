#!/usr/bin/env node

/**
 * Laws Agent MCP Server v2
 * Direct database + API implementation (bypasses PHP authentication)
 */

const http = require('http');
const https = require('https');
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

function requireEnv(name) {
    const value = process.env[name];
    if (!value || String(value).trim() === '') {
        throw new Error(`Missing required environment variable: ${name}`);
    }
    return value;
}

function getDbConfigFromEnv() {
    return {
        host: requireEnv('DB_HOST'),
        user: requireEnv('DB_USER'),
        password: requireEnv('DB_PASS'),
        database: requireEnv('DB_NAME'),
        ssl: { rejectUnauthorized: false },
    };
}

const ANTHROPIC_API_KEY = process.env.ANTHROPIC_API_KEY;
const ANTHROPIC_MODEL = process.env.ANTHROPIC_MODEL || 'claude-sonnet-4-20250514';

async function getDbConnection() {
    try {
        const config = getDbConfigFromEnv();
        return await mysql.createConnection(config);
    } catch (error) {
        throw new Error(`Database connection failed: ${error.message}`);
    }
}

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
            sql += ' AND r.category = ?';
            params.push(category);
        }

        if (system) {
            sql += ' AND r.system_type = ?';
            params.push(system);
        }

        sql += ' ORDER BY relevance DESC LIMIT ?';
        params.push(limit);

        const [rows] = await connection.query(sql, params);
        return rows;
    } catch (error) {
        throw new Error(`Search failed: ${error.message}`);
    }
}

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

function readKnowledgeBaseFiles() {
    const knowledgeBasePath = path.join(__dirname, '..', 'knowledge-base');
    let knowledgeContent = '';

    try {
        if (!fs.existsSync(knowledgeBasePath)) {
            return '';
        }

        const files = fs.readdirSync(knowledgeBasePath);
        const textFiles = files.filter((file) =>
            ['.txt', '.md', '.mdx', '.json'].some((ext) => file.endsWith(ext))
        );

        if (textFiles.length === 0) {
            return '';
        }

        knowledgeContent = '\n\n=== Knowledge Base Reference Files ===\n\n';

        textFiles.forEach((file, index) => {
            try {
                const filePath = path.join(knowledgeBasePath, file);
                const content = fs.readFileSync(filePath, 'utf8');
                knowledgeContent += `[Knowledge Base File ${index + 1}] ${file}:\n${content}\n\n`;
            } catch (error) {
                console.error(`Error reading ${file}: ${error.message}`);
            }
        });
    } catch (error) {
        console.error(`Knowledge base error: ${error.message}`);
    }

    return knowledgeContent;
}

function buildContextFromResults(results) {
    if (!results || results.length === 0) {
        return 'No relevant rulebook content found.';
    }

    let context = 'Context from VTM/MET rulebooks:\n\n';

    results.forEach((result, i) => {
        const sourceNum = i + 1;
        const excerpt = extractExcerpt(result.page_text, 800);

        context += `[Source ${sourceNum}] ${result.book_title} (Page ${result.page_number}, Category: ${result.category}, System: ${result.system_type}):\n${excerpt}\n\n`;
    });

    const knowledgeContent = readKnowledgeBaseFiles();
    if (knowledgeContent) {
        context += knowledgeContent;
    }

    return context;
}

async function callAnthropicAPI(question, context) {
    if (!ANTHROPIC_API_KEY || String(ANTHROPIC_API_KEY).trim() === '') {
        return Promise.reject(
            new Error('ANTHROPIC_API_KEY is not set. Please configure your API key in the environment.')
        );
    }
    return new Promise((resolve, reject) => {
        const systemPrompt = `You are a helpful assistant answering questions about Vampire: The Masquerade and Mind's Eye Theatre rules. Baseline edition is Laws of the Night Revised. Do not reference V5 or the Second Inquisition. Answer based on the provided context from official rulebooks and any appended knowledge-base files. Always cite your sources by including [Book Name, Page X] citations in your response.

IMPORTANT: When asked about "Camarilla traditions" or "the Traditions," you should always mention the Six Traditions that govern vampire society:
1. The Masquerade - Conceal vampiric nature from mortals at all times
2. Domain - A Prince (or rightful lord) holds the city; respect granted rights
3. Progeny - Do not Embrace without the Prince’s explicit leave
4. Accounting - A sire is responsible for a childe until formal Release
5. Hospitality - Present yourself to the Prince upon entering a city
6. Destruction - Only the Prince (or empowered elder) may grant Final Death

These are fundamental laws of the Camarilla (LotN Revised), even if specific details aren't found in the search results.`;

        const userPrompt = `${question}\n\n${context}`;

        const data = {
            model: ANTHROPIC_MODEL,
            max_tokens: 2000,
            messages: [
                {
                    role: 'user',
                    content: userPrompt,
                },
            ],
            system: systemPrompt,
        };

        const options = {
            hostname: 'api.anthropic.com',
            port: 443,
            path: '/v1/messages',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'x-api-key': ANTHROPIC_API_KEY,
                'anthropic-version': '2023-06-01',
            },
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
                            model: result.model,
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

async function askLawsAgent(question, category = null, system = null) {
    try {
        const connection = await getDbConnection();

        try {
            const searchResults = await searchRulebooks(connection, question, category, system, 5);

            const isTraditionQuestion = /\b(traditions?|masquerade|domain|progeny|accounting|hospitality|destruction)\b/i.test(
                question
            );

            if ((!searchResults || searchResults.length === 0) && !isTraditionQuestion) {
                return {
                    success: true,
                    question,
                    answer:
                        "I couldn't find any relevant information in the rulebooks to answer that question. Please try rephrasing or being more specific.",
                    sources: [],
                    ai_model: ANTHROPIC_MODEL,
                    searched: true,
                    results_found: 0,
                };
            }

            let context = '';
            if (searchResults && searchResults.length > 0) {
                context = buildContextFromResults(searchResults);
            } else {
                context =
                    'No specific rulebook excerpts found, but answer based on fundamental knowledge of the Six Traditions.';
                const knowledgeContent = readKnowledgeBaseFiles();
                if (knowledgeContent) {
                    context += '\n\n' + knowledgeContent;
                }
            }

            const aiResponse = await callAnthropicAPI(question, context);

            const sources =
                searchResults && searchResults.length > 0
                    ? searchResults.map((result) => ({
                          book: result.book_title,
                          page: result.page_number,
                          category: result.category,
                          system: result.system_type,
                          excerpt: extractExcerpt(result.page_text, 300),
                          relevance: parseFloat(result.relevance),
                      }))
                    : [];

            return {
                success: true,
                question,
                answer: aiResponse.answer,
                sources,
                ai_model: aiResponse.model,
                searched: true,
                results_found: searchResults.length,
            };
        } finally {
            await connection.end();
        }
    } catch (error) {
        return {
            success: false,
            error: error.message,
            question,
        };
    }
}

class LawsAgentMCPServer {
    constructor() {
        this.tools = [
            {
                name: 'query_laws_agent',
                description:
                    'Ask VTM/MET rules questions to the Laws Agent. Powered by AI with access to 31 official rulebooks covering game mechanics, disciplines, clans, lore, and more.',
                inputSchema: {
                    type: 'object',
                    properties: {
                        question: {
                            type: 'string',
                            description:
                                'The rules question to ask (e.g., "How does Celerity work?", "What are the Camarilla traditions?")',
                        },
                        category: {
                            type: 'string',
                            enum: ['Core', 'Faction', 'Supplement', 'Blood Magic', 'Journal', 'Other'],
                            description: 'Optional: Filter by book category to narrow search',
                        },
                        system: {
                            type: 'string',
                            enum: ['MET-VTM', 'MET', 'VTM', 'MTA', 'WOD', 'Wraith'],
                            description: 'Optional: Filter by game system to narrow search',
                        },
                    },
                    required: ['question'],
                },
            },
        ];
    }

    async handleToolCall(toolName, args) {
        if (toolName === 'query_laws_agent') {
            try {
                const response = await askLawsAgent(args.question, args.category || null, args.system || null);

                if (!response.success) {
                    return {
                        content: [
                            {
                                type: 'text',
                                text: `Error: ${response.error || 'Unknown error'}`,
                            },
                        ],
                        isError: true,
                    };
                }

                let formattedText = `**Question:** ${response.question}\n\n`;
                formattedText += `**Answer:**\n${response.answer}\n\n`;

                if (response.sources && response.sources.length > 0) {
                    formattedText += '**Sources:**\n';
                    response.sources.forEach((source, index) => {
                        formattedText += `${index + 1}. ${source.book} (Page ${source.page}) - ${source.category}, ${source.system}\n`;
                    });
                }

                if (response.ai_model) {
                    formattedText += `\n*Powered by ${response.ai_model}*`;
                }

                return {
                    content: [
                        {
                            type: 'text',
                            text: formattedText,
                        },
                    ],
                    isError: false,
                };
            } catch (error) {
                return {
                    content: [
                        {
                            type: 'text',
                            text: `Failed to query Laws Agent: ${error.message}`,
                        },
                    ],
                    isError: true,
                };
            }
        }

        return {
            content: [
                {
                    type: 'text',
                    text: `Unknown tool: ${toolName}`,
                },
            ],
            isError: true,
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
                            tools: {},
                        },
                        serverInfo: {
                            name: 'laws-agent',
                            version: '2.0.0',
                        },
                    },
                };

            case 'tools/list':
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result: {
                        tools: this.tools,
                    },
                };

            case 'tools/call':
                const result = await this.handleToolCall(message.params.name, message.params.arguments || {});

                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    result,
                };

            default:
                return {
                    jsonrpc: '2.0',
                    id: message.id,
                    error: {
                        code: -32601,
                        message: `Method not found: ${message.method}`,
                    },
                };
        }
    }
}

if (require.main === module) {
    const server = new LawsAgentMCPServer();
    server.run().catch((error) => {
        console.error('Fatal error:', error);
        process.exit(1);
    });
}

module.exports = LawsAgentMCPServer;


