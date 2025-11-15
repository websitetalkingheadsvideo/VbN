<?php
declare(strict_types=1);

/**
 * Laws of the Night Markdown Loader
 * 
 * Loads and indexes markdown files from agents/Laws_of_the_Night/ directory
 * for use by the Laws Agent as a primary knowledge source.
 */
class LawsOfTheNightLoader
{
    private string $basePath;
    private array $index = [];
    private ?int $lastIndexTime = null;
    private array $fileCache = [];

    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath ?? __DIR__ . '/../Laws_of_the_Night';
    }

    /**
     * Get the base path for Laws of the Night files
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Load and index all markdown files
     * 
     * @return array{success: bool, files_loaded: int, errors: array<string>}
     */
    public function loadIndex(): array
    {
        $errors = [];
        $filesLoaded = 0;
        $this->index = [];

        if (!is_dir($this->basePath)) {
            return [
                'success' => false,
                'files_loaded' => 0,
                'errors' => ["Directory not found: {$this->basePath}"],
            ];
        }

        $files = $this->scanDirectory($this->basePath);
        
        foreach ($files as $filePath) {
            $result = $this->parseMarkdownFile($filePath);
            if ($result['success']) {
                $this->index[] = $result['data'];
                $filesLoaded++;
            } else {
                $errors[] = $filePath . ': ' . $result['error'];
            }
        }

        $this->lastIndexTime = time();

        return [
            'success' => true,
            'files_loaded' => $filesLoaded,
            'errors' => $errors,
        ];
    }

    /**
     * Recursively scan directory for markdown files
     * 
     * @return array<string> Array of file paths
     */
    private function scanDirectory(string $dir): array
    {
        $files = [];
        
        if (!is_readable($dir)) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $filePath = $file->getRealPath();
                if ($filePath !== false) {
                    // Skip TOC.md and metadata.json (not markdown content files)
                    $basename = basename($filePath);
                    if ($basename !== 'TOC.md') {
                        $files[] = $filePath;
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Parse a markdown file and extract metadata and content
     * 
     * @return array{success: bool, data?: array, error?: string}
     */
    private function parseMarkdownFile(string $filePath): array
    {
        if (!is_readable($filePath)) {
            return ['success' => false, 'error' => 'File not readable'];
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return ['success' => false, 'error' => 'Failed to read file'];
        }

        // Get path relative to Laws_of_the_Night directory only
        $relativePath = str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $filePath);
        $relativePath = str_replace('\\', '/', $relativePath);
        
        // Remove any leading path components (like /usr/home/.../agents/)
        // Keep only the path within Laws_of_the_Night/
        if (strpos($relativePath, 'Laws_of_the_Night/') !== false) {
            $relativePath = substr($relativePath, strpos($relativePath, 'Laws_of_the_Night/') + strlen('Laws_of_the_Night/'));
        }
        
        // Determine category from path
        $category = $this->determineCategory($relativePath);
        
        // Extract frontmatter
        $frontmatter = $this->extractFrontmatter($content);
        $bodyContent = $this->extractBody($content);

        // Build index entry
        $entry = [
            'id' => md5($filePath),
            'file_path' => $filePath,
            'relative_path' => $relativePath,
            'title' => $frontmatter['title'] ?? $this->extractTitleFromPath($relativePath),
            'chapter' => $frontmatter['chapter'] ?? null,
            'section' => $frontmatter['section'] ?? null,
            'tags' => $frontmatter['tags'] ?? [],
            'category' => $category,
            'content' => $bodyContent,
            'content_lower' => mb_strtolower($bodyContent),
            'modified_time' => filemtime($filePath),
        ];

        return ['success' => true, 'data' => $entry];
    }

    /**
     * Determine content category from file path
     */
    private function determineCategory(string $relativePath): string
    {
        if (strpos($relativePath, 'chapters/') === 0) {
            return 'chapter';
        }
        if (strpos($relativePath, 'clans/') === 0) {
            return 'clan';
        }
        if (strpos($relativePath, 'disciplines/') === 0) {
            return 'discipline';
        }
        return 'other';
    }

    /**
     * Extract YAML frontmatter from markdown content
     * 
     * @return array<string, mixed>
     */
    private function extractFrontmatter(string $content): array
    {
        $frontmatter = [];

        // Check for YAML frontmatter (between --- markers)
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            $yamlContent = $matches[1];
            
            // Simple YAML parser for basic key-value pairs
            $lines = explode("\n", $yamlContent);
            $currentKey = null;
            $currentValue = null;
            $inArray = false;
            $arrayItems = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                // Check if it's an array item (starts with -)
                if (preg_match('/^-\s*(.+)$/', $line, $arrayMatch)) {
                    if ($currentKey === 'tags') {
                        $arrayItems[] = trim($arrayMatch[1]);
                    }
                    continue;
                }

                // Check if it's a key-value pair
                if (preg_match('/^(\w+):\s*(.+)$/', $line, $kvMatch)) {
                    // Save previous array if we were in one
                    if ($inArray && $currentKey === 'tags') {
                        $frontmatter[$currentKey] = $arrayItems;
                        $arrayItems = [];
                        $inArray = false;
                    }

                    $key = trim($kvMatch[1]);
                    $value = trim($kvMatch[2]);
                    
                    // Remove quotes if present
                    $value = trim($value, '"\'');
                    
                    // Check if value is a number
                    if (is_numeric($value)) {
                        $value = (int) $value;
                    }

                    $frontmatter[$key] = $value;
                    $currentKey = $key;
                    $currentValue = $value;
                }
            }

            // Save final array if we were in one
            if ($inArray && $currentKey === 'tags' && !empty($arrayItems)) {
                $frontmatter[$currentKey] = $arrayItems;
            }
        }

        return $frontmatter;
    }

    /**
     * Extract body content (everything after frontmatter)
     */
    private function extractBody(string $content): string
    {
        // Remove frontmatter if present
        $body = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
        return trim($body);
    }

    /**
     * Extract title from file path as fallback
     */
    private function extractTitleFromPath(string $relativePath): string
    {
        $basename = basename($relativePath, '.md');
        // Remove numbered prefixes like "02_Clan_"
        $basename = preg_replace('/^\d+_/', '', $basename);
        // Replace underscores with spaces
        $basename = str_replace('_', ' ', $basename);
        return $basename;
    }

    /**
     * Search the index for matching content
     * 
     * @param string $query Search query
     * @param string|null $category Filter by category (chapter, clan, discipline)
     * @param int $limit Maximum results to return
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, ?string $category = null, int $limit = 10): array
    {
        if (empty($this->index)) {
            $this->loadIndex();
        }

        $queryLower = mb_strtolower($query);
        $queryWords = preg_split('/\s+/', $queryLower);
        $results = [];

        foreach ($this->index as $entry) {
            // Category filter
            if ($category !== null && $entry['category'] !== $category) {
                continue;
            }

            $score = 0;
            $matchedWords = 0;

            // Check title match (highest weight)
            $titleLower = mb_strtolower($entry['title']);
            if (strpos($titleLower, $queryLower) !== false) {
                $score += 100;
            }
            foreach ($queryWords as $word) {
                if (strpos($titleLower, $word) !== false) {
                    $score += 20;
                    $matchedWords++;
                }
            }

            // Check section match
            if (!empty($entry['section'])) {
                $sectionLower = mb_strtolower($entry['section']);
                if (strpos($sectionLower, $queryLower) !== false) {
                    $score += 50;
                }
            }

            // Check content match
            $contentLower = $entry['content_lower'];
            foreach ($queryWords as $word) {
                $count = substr_count($contentLower, $word);
                if ($count > 0) {
                    $score += min($count * 2, 30); // Cap content matches
                    $matchedWords++;
                }
            }

            // Check tags
            if (!empty($entry['tags'])) {
                foreach ($entry['tags'] as $tag) {
                    $tagLower = mb_strtolower($tag);
                    if (strpos($tagLower, $queryLower) !== false) {
                        $score += 15;
                    }
                }
            }

            // Only include results with some matches
            if ($score > 0) {
                $entry['relevance'] = $score;
                $entry['matched_words'] = $matchedWords;
                $results[] = $entry;
            }
        }

        // Sort by relevance (descending)
        usort($results, static function ($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Get all indexed entries
     * 
     * @return array<int, array<string, mixed>>
     */
    public function getAllEntries(): array
    {
        if (empty($this->index)) {
            $this->loadIndex();
        }
        return $this->index;
    }

    /**
     * Get index statistics
     * 
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        if (empty($this->index)) {
            $this->loadIndex();
        }

        $stats = [
            'total_files' => count($this->index),
            'by_category' => [],
            'last_indexed' => $this->lastIndexTime,
        ];

        foreach ($this->index as $entry) {
            $category = $entry['category'];
            if (!isset($stats['by_category'][$category])) {
                $stats['by_category'][$category] = 0;
            }
            $stats['by_category'][$category]++;
        }

        return $stats;
    }

    /**
     * Get a formatted excerpt from content
     */
    public function getExcerpt(string $content, int $maxLength = 300): string
    {
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);

        if (strlen($content) <= $maxLength) {
            return $content;
        }

        $excerpt = substr($content, 0, $maxLength);
        $lastPeriod = strrpos($excerpt, '.');

        if ($lastPeriod !== false && $lastPeriod > $maxLength * 0.7) {
            return substr($content, 0, $lastPeriod + 1);
        }

        return $excerpt . '...';
    }
}

