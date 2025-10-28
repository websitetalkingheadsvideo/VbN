#!/usr/bin/env node

/**
 * Laws Agent MCP Server
 * Provides MCP tool interface to the Laws Agent API
 */

const http = require('http');
const https = require('https');
const url = require('url');

// Configuration
const API_BASE_URL = process.env.LAWS_AGENT_URL || 'http://localhost';
const PROJECT_PATH = process.env.PROJECT_PATH || 'G:\\VbN';

/**
 * Make HTTP request to Laws Agent API
 */
function callLawsAgentAPI(question, category = null, system = null) {
    return new Promise((resolve, reject) => {
        const apiUrl = `${API_BASE_URL}/admin/api_laws_agent.php`;
        const params = new URLSearchParams({
            action: 'ask',
            question: question
        });
        
        if (category) params.append('category', category);
        if (system) params.append('system', system);
        
        const fullUrl = `${apiUrl}?${params.toString()}`;
        const urlObj = url.parse(fullUrl);
        
        const protocol = urlObj.protocol === 'https:' ? https : http;
        
        const options = {
            hostname: urlObj.hostname,
            port: urlObj.port || (urlObj.protocol === 'https:' ? 443 : 80),
            path: urlObj.path,
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        };
        
        const req = protocol.request(options, (res) => {
            let data = '';
            
            res.on('data', (chunk) => {
                data += chunk;
            });
            
            res.on('end', () => {
                try {
                    const result = JSON.parse(data);
                    resolve(result);
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
        
        req.end();
    });
}

/**
 * Format API response for MCP
 */
function formatResponse(apiResponse) {
    if (!apiResponse.success) {
        return {
            content: [{
                type: 'text',
                text: `Error: ${apiResponse.error || 'Unknown error'}`
            }],
            isError: true
        };
    }
    
    let formattedText = `**Question:** ${apiResponse.question}\n\n`;
    formattedText += `**Answer:**\n${apiResponse.answer}\n\n`;
    
    if (apiResponse.sources && apiResponse.sources.length > 0) {
        formattedText += `**Sources:**\n`;
        apiResponse.sources.forEach((source, index) => {
            formattedText += `${index + 1}. ${source.book} (Page ${source.page}) - ${source.category}, ${source.system}\n`;
        });
    }
    
    if (apiResponse.ai_model) {
        formattedText += `\n*Powered by ${apiResponse.ai_model}*`;
    }
    
    return {
        content: [{
            type: 'text',
            text: formattedText
        }],
        isError: false
    };
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
                const response = await callLawsAgentAPI(
                    args.question,
                    args.category || null,
                    args.system || null
                );
                
                return formatResponse(response);
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
        // Read from stdin, write to stdout (MCP protocol)
        process.stdin.setEncoding('utf8');
        
        let buffer = '';
        
        process.stdin.on('data', async (chunk) => {
            buffer += chunk;
            
            // Process complete JSON messages
            const lines = buffer.split('\n');
            buffer = lines.pop(); // Keep incomplete line in buffer
            
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
                            version: '1.0.0'
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

