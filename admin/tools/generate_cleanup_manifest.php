<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

if (PHP_SAPI === 'cli') {
    $_SESSION['user_id'] = $_SESSION['user_id'] ?? 0;
    $_SESSION['role'] = $_SESSION['role'] ?? 'admin';
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(302);
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../../includes/version.php';

const CLEANUP_TARGET_EXTENSIONS = ['php', 'json', 'md', 'jpg', 'jpeg', 'png', 'webp', 'gif'];
const CLEANUP_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
const CLEANUP_REFERENCE_EXTENSIONS = ['json', 'md', 'php'];
const CLEANUP_HASH_ALGO = 'sha256';
const CLEANUP_SKIP_DIRECTORIES = [
    'ComfyUI',
    'node_modules',
    'vendor',
    '.git',
    '.svn',
    '.taskmaster',
    '.cursor',
    'tmp\\cache',
    'tmp/cache',
    'tests',
];
const CLEANUP_LOG_FILE = __DIR__ . '/../../archive/cleanup_manifest_error.log';

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        if ($haystack === '') {
            return false;
        }

        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

set_exception_handler(function (Throwable $throwable): void {
    logCleanupError('exception', $throwable->getMessage(), [
        'file' => $throwable->getFile(),
        'line' => $throwable->getLine(),
        'trace' => $throwable->getTraceAsString(),
    ]);
    respondWithError(500, 'Failed to generate cleanup manifest. See log for details.');
});

$projectRoot = realpath(__DIR__ . '/../../');

if ($projectRoot === false) {
    respondWithError(500, 'Failed to resolve project root absolute path.');
}

logCleanupError('start', 'Manifest generation started', [
    'projectRoot' => $projectRoot,
    'sapi' => PHP_SAPI,
]);

try {
    $manifest = buildCleanupManifest($projectRoot);
} catch (Throwable $throwable) {
    logCleanupError('build', $throwable->getMessage(), [
        'file' => $throwable->getFile(),
        'line' => $throwable->getLine(),
        'trace' => $throwable->getTraceAsString(),
    ]);
    error_log(sprintf(
        '[cleanup_manifest] build error at %s:%d -> %s',
        $throwable->getFile(),
        $throwable->getLine(),
        $throwable->getMessage()
    ));
    respondWithError(500, 'Failed to build cleanup manifest: ' . $throwable->getMessage());
}

$manifestPath = $projectRoot . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . 'cleanup_manifest.json';

try {
    ensureDirectory(dirname($manifestPath));
    writeJsonFile($manifestPath, $manifest);
} catch (RuntimeException $exception) {
    logCleanupError('write', $exception->getMessage(), [
        'manifestPath' => $manifestPath,
    ]);
    respondWithError(500, $exception->getMessage());
}

respondWithJson([
    'status' => 'ok',
    'generatedAt' => $manifest['meta']['generatedAt'] ?? null,
    'manifestPath' => $manifestPath,
    'summary' => $manifest['summary'] ?? [],
]);


/**
 * @param string $projectRoot
 * @return array<string, mixed>
 */
function buildCleanupManifest(string $projectRoot): array
{
    $fileIndex = collectTargetFiles($projectRoot, CLEANUP_TARGET_EXTENSIONS);
    $images = enrichImageMetadata($fileIndex['images'] ?? []);
    $phpClassification = classifyPhpFiles($fileIndex['php'] ?? [], $projectRoot);
    $referenceUsage = findReferenceUsage(
        $fileIndex,
        $projectRoot,
        CLEANUP_REFERENCE_EXTENSIONS
    );
    $imageDuplicates = detectImageDuplicates($images);

    return [
        'meta' => [
            'generatedAt' => (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM),
            'projectRoot' => $projectRoot,
            'version' => defined('LOTN_VERSION') ? LOTN_VERSION : null,
        ],
        'summary' => [
            'php' => [
                'active' => count($phpClassification['active']),
                'inactive' => count($phpClassification['inactive']),
                'archived' => count($phpClassification['archived']),
                'unresolvedIncludes' => count($phpClassification['unresolved']),
            ],
            'json' => [
                'referenced' => count($referenceUsage['json']['referenced']),
                'unreferenced' => count($referenceUsage['json']['unreferenced']),
            ],
            'md' => [
                'referenced' => count($referenceUsage['md']['referenced']),
                'unreferenced' => count($referenceUsage['md']['unreferenced']),
            ],
            'images' => [
                'total' => count($images),
                'duplicateGroups' => count($imageDuplicates),
            ],
        ],
        'php' => $phpClassification,
        'json' => $referenceUsage['json'],
        'md' => $referenceUsage['md'],
        'images' => [
            'all' => $images,
            'duplicates' => $imageDuplicates,
        ],
    ];
}

/**
 * @param string $projectRoot
 * @param array<int, string> $extensions
 * @return array<string, array<int, array<string, mixed>>>
 */
function collectTargetFiles(string $projectRoot, array $extensions): array
{
    $normalizedExtensions = normalizeExtensionList($extensions);
    $result = [
        'php' => [],
        'json' => [],
        'md' => [],
        'images' => [],
        'other' => [],
    ];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator(
                $projectRoot,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            ),
            function (SplFileInfo $current, $key, $iterator) use ($projectRoot): bool {
                if ($current->isDir()) {
                    $relativeDir = substr($current->getPathname(), strlen($projectRoot) + 1);

                    if (shouldSkipDirectory($relativeDir)) {
                        return false;
                    }
                }

                return true;
            }
        )
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo instanceof SplFileInfo) {
            continue;
        }

        if ($fileInfo->isDir()) {
            continue;
        }

        $extension = strtolower($fileInfo->getExtension());

        if (!isset($normalizedExtensions[$extension])) {
            continue;
        }

        $relativePath = substr($fileInfo->getPathname(), strlen($projectRoot) + 1);

        if (shouldSkipDirectory($relativePath)) {
            continue;
        }

        $entry = [
            'path' => $fileInfo->getPathname(),
            'relativePath' => $relativePath,
            'size' => $fileInfo->getSize(),
            'modifiedAt' => date(DateTimeInterface::ATOM, $fileInfo->getMTime()),
        ];

        if ($extension === 'php') {
            $result['php'][] = $entry;
            continue;
        }

        if ($extension === 'json') {
            $result['json'][] = $entry;
            continue;
        }

        if ($extension === 'md') {
            $result['md'][] = $entry;
            continue;
        }

        if (in_array($extension, CLEANUP_IMAGE_EXTENSIONS, true)) {
            $result['images'][] = array_merge(
                $entry,
                ['hash' => null]
            );
            continue;
        }

        $result['other'][] = $entry;
    }

    return $result;
}

/**
 * @param array<int, array<string, mixed>> $images
 * @return array<int, array<string, mixed>>
 */
function enrichImageMetadata(array $images): array
{
    $enriched = [];

    foreach ($images as $image) {
        $path = $image['path'] ?? '';

        if ($path === '') {
            continue;
        }

        $hash = $image['hash'] ?? null;

        if ($hash === null) {
            $hash = hash_file(CLEANUP_HASH_ALGO, $path);
        }

        if ($hash === false) {
            continue;
        }

        $image['hash'] = $hash;
        $enriched[] = $image;
    }

    return $enriched;
}

/** @psalm-pure */
function shouldSkipDirectory(string $relativePath): bool
{
    if ($relativePath === '') {
        return false;
    }

    $normalized = str_replace('\\', '/', $relativePath);

    foreach (CLEANUP_SKIP_DIRECTORIES as $skip) {
        $normalizedSkip = str_replace('\\', '/', $skip);

        if ($normalizedSkip === '') {
            continue;
        }

        if (str_starts_with($normalized, $normalizedSkip)) {
            return true;
        }
    }

    return false;
}

/**
 * @param array<int, string> $extensions
 * @return array<string, string>
 */
function normalizeExtensionList(array $extensions): array
{
    $normalized = [];

    foreach ($extensions as $extension) {
        $trimmedExtension = strtolower(trim($extension));

        if ($trimmedExtension === '') {
            continue;
        }

        $normalized[$trimmedExtension] = $trimmedExtension;
    }

    return $normalized;
}

/**
 * @param array<int, array<string, mixed>> $phpFiles
 * @param string $projectRoot
 * @return array<string, mixed>
 */
function classifyPhpFiles(array $phpFiles, string $projectRoot): array
{
    $archived = [];
    $active = [];
    $inactiveCandidates = [];
    $unresolved = [];

    $graph = buildPhpIncludeGraph($phpFiles, $projectRoot);
    $entryPoints = determinePhpEntryPoints($projectRoot);
    $reachable = traversePhpGraph($graph, $entryPoints);

    foreach ($phpFiles as $file) {
        $relativePath = $file['relativePath'] ?? '';
        $path = $file['path'] ?? '';

        if ($relativePath === '') {
            continue;
        }

        if (str_starts_with($relativePath, 'archive' . DIRECTORY_SEPARATOR)) {
            $archived[] = $file;
            continue;
        }

        if (isset($reachable[$path])) {
            $active[] = $file;
            continue;
        }

        $inactiveCandidates[] = $file;
    }

    foreach ($graph['unresolved'] as $issue) {
        $unresolved[] = $issue;
    }

    return [
        'active' => $active,
        'inactive' => $inactiveCandidates,
        'archived' => $archived,
        'unresolved' => $unresolved,
    ];
}

/**
 * @param array<int, array<string, mixed>> $phpFiles
 * @param string $projectRoot
 * @return array<string, mixed>
 */
function buildPhpIncludeGraph(array $phpFiles, string $projectRoot): array
{
    $graph = [];
    $unresolved = [];
    $index = [];

    foreach ($phpFiles as $file) {
        $path = $file['path'] ?? '';

        if ($path === '') {
            continue;
        }

        $index[$path] = $file;
        $graph[$path] = [];
    }

    foreach ($phpFiles as $file) {
        $path = $file['path'] ?? '';

        if ($path === '') {
            continue;
        }

        $includes = extractPhpIncludes($path);

        foreach ($includes as $include) {
            $resolved = resolveIncludePath($include, $path, $projectRoot);

            if ($resolved === null) {
                $unresolved[] = [
                    'source' => $path,
                    'include' => $include,
                ];
                continue;
            }

            if (!isset($graph[$path])) {
                $graph[$path] = [];
            }

            $graph[$path][$resolved] = true;

            if (!isset($graph[$resolved])) {
                $graph[$resolved] = [];
            }
        }
    }

    return [
        'graph' => $graph,
        'unresolved' => $unresolved,
    ];
}

/**
 * @param string $filePath
 * @return array<int, string>
 */
function extractPhpIncludes(string $filePath): array
{
    $content = file_get_contents($filePath);

    if ($content === false) {
        return [];
    }

    $pattern = '/\b(include|include_once|require|require_once)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i';
    $matches = [];
    $result = [];

    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $result[] = $match[2];
    }

    return $result;
}

/**
 * @param string $include
 * @param string $sourcePath
 * @param string $projectRoot
 * @return string|null
 */
function resolveIncludePath(string $include, string $sourcePath, string $projectRoot): ?string
{
    if ($include === '') {
        return null;
    }

    if (str_starts_with($include, DIRECTORY_SEPARATOR)) {
        $candidate = realpath($projectRoot . $include);
        return $candidate !== false ? $candidate : null;
    }

    if (str_starts_with($include, './') || str_starts_with($include, '../')) {
        $sourceDir = dirname($sourcePath);
        $candidate = realpath($sourceDir . DIRECTORY_SEPARATOR . $include);

        return $candidate !== false ? $candidate : null;
    }

    $candidate = realpath(dirname($sourcePath) . DIRECTORY_SEPARATOR . $include);

    if ($candidate !== false) {
        return $candidate;
    }

    $projectCandidate = realpath($projectRoot . DIRECTORY_SEPARATOR . $include);

    if ($projectCandidate !== false) {
        return $projectCandidate;
    }

    return null;
}

/**
 * @param string $projectRoot
 * @return array<int, string>
 */
function determinePhpEntryPoints(string $projectRoot): array
{
    $candidates = [
        'index.php',
        'dashboard.php',
        'login.php',
        'register.php',
        'logout.php',
        'lotn_char_create.php',
        'load_character.php',
        'save_character.php',
        'admin/admin_panel.php',
    ];

    $entryPoints = [];

    foreach ($candidates as $relativePath) {
        $fullPath = realpath($projectRoot . DIRECTORY_SEPARATOR . $relativePath);

        if ($fullPath !== false) {
            $entryPoints[] = $fullPath;
        }
    }

    return $entryPoints;
}

/**
 * @param array<string, array<string, bool>> $graph
 * @param array<int, string> $entryPoints
 * @return array<string, bool>
 */
function traversePhpGraph(array $graph, array $entryPoints): array
{
    $visited = [];
    $queue = [];

    foreach ($entryPoints as $entryPoint) {
        $queue[] = $entryPoint;
    }

    while (count($queue) > 0) {
        $current = array_shift($queue);

        if ($current === null) {
            continue;
        }

        if (isset($visited[$current])) {
            continue;
        }

        $visited[$current] = true;

        $neighbors = $graph[$current] ?? [];

        foreach ($neighbors as $neighbor => $_) {
            if (!isset($visited[$neighbor])) {
                $queue[] = $neighbor;
            }
        }
    }

    return $visited;
}

/**
 * @param array<string, array<int, array<string, mixed>>> $fileIndex
 * @param string $projectRoot
 * @param array<int, string> $extensions
 * @return array<string, array<string, array<int, array<string, mixed>>>>
 */
function findReferenceUsage(array $fileIndex, string $projectRoot, array $extensions): array
{
    $referencers = collectReferencerFiles($projectRoot);
    $results = [
        'json' => [
            'referenced' => [],
            'unreferenced' => [],
        ],
        'md' => [
            'referenced' => [],
            'unreferenced' => [],
        ],
    ];

    foreach ($extensions as $extension) {
        if (!isset($fileIndex[$extension])) {
            continue;
        }

        foreach ($fileIndex[$extension] as $file) {
            $relativePath = $file['relativePath'] ?? '';

            if ($relativePath === '') {
                continue;
            }

            if (str_starts_with($relativePath, 'archive' . DIRECTORY_SEPARATOR)) {
                $results[$extension]['referenced'][] = array_merge(
                    $file,
                    ['usage' => 'archived']
                );
                continue;
            }

            $basename = basename($relativePath);
            $isReferenced = isFileReferenced($basename, $referencers);

            if ($isReferenced) {
                $results[$extension]['referenced'][] = array_merge(
                    $file,
                    ['usage' => 'referenced']
                );
                continue;
            }

            $results[$extension]['unreferenced'][] = array_merge(
                $file,
                ['usage' => 'unreferenced']
            );
        }
    }

    return $results;
}

/**
 * @param string $basename
 * @param array<int, array<string, string>> $referencers
 * @return bool
 */
function isFileReferenced(string $basename, array $referencers): bool
{
    foreach ($referencers as $referencer) {
        $content = $referencer['content'] ?? '';

        if ($content === '') {
            continue;
        }

        if (strpos($content, $basename) !== false) {
            return true;
        }
    }

    return false;
}

/** @return array<int, array<string, mixed>> */
function collectReferencerFiles(string $projectRoot): array
{
    $referencers = [];
    $referencerExtensions = ['php', 'js', 'ts', 'tsx', 'jsx', 'json', 'md'];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator(
                $projectRoot,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            ),
            function (SplFileInfo $current, $key, $iterator) use ($projectRoot): bool {
                if ($current->isDir()) {
                    $relativeDir = substr($current->getPathname(), strlen($projectRoot) + 1);

                    if (shouldSkipDirectory($relativeDir)) {
                        return false;
                    }
                }

                return true;
            }
        )
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo instanceof SplFileInfo) {
            continue;
        }

        if ($fileInfo->isDir()) {
            continue;
        }

        $extension = strtolower($fileInfo->getExtension());

        if (!in_array($extension, $referencerExtensions, true)) {
            continue;
        }

        $path = $fileInfo->getPathname();
        $content = file_get_contents($path);

        if ($content === false) {
            continue;
        }

        $referencers[] = [
            'path' => $path,
            'content' => $content,
        ];
    }

    return $referencers;
}

/**
 * @param array<int, array<string, mixed>> $images
 * @return array<int, array<string, mixed>>
 */
function detectImageDuplicates(array $images): array
{
    $hashMap = [];
    $duplicates = [];

    foreach ($images as $image) {
        $path = $image['path'] ?? '';

        if ($path === '') {
            continue;
        }

        $hash = hash_file(CLEANUP_HASH_ALGO, $path);

        if ($hash === false) {
            continue;
        }

        $image['hash'] = $hash;
        $hashMap[$hash][] = $image;
    }

    foreach ($hashMap as $hash => $group) {
        if (count($group) < 2) {
            continue;
        }

        $duplicates[] = [
            'hash' => $hash,
            'files' => $group,
        ];
    }

    return $duplicates;
}

/**
 * @param string $directory
 * @return void
 */
function ensureDirectory(string $directory): void
{
    if (is_dir($directory)) {
        return;
    }

    $created = mkdir($directory, 0775, true);

    if ($created === false) {
        throw new RuntimeException(sprintf('Failed to create archive directory: %s', $directory));
    }
}

/**
 * @param string $path
 * @param array<string, mixed> $data
 * @return void
 */
function writeJsonFile(string $path, array $data): void
{
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($encoded === false) {
        throw new RuntimeException('Failed to encode cleanup manifest JSON.');
    }

    $bytes = file_put_contents($path, $encoded);

    if ($bytes === false) {
        throw new RuntimeException(sprintf('Unable to write cleanup manifest to %s', $path));
    }
}

/**
 * @param int $status
 * @param string $message
 * @return void
 */
function respondWithError(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $message,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit();
}

/**
 * @param array<string, mixed> $payload
 * @return void
 */
function respondWithJson(array $payload): void
{
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit();
}

/**
 * @param string $context
 * @param string $message
 * @param array<string, mixed> $extra
 * @return void
 */
function logCleanupError(string $context, string $message, array $extra = []): void
{
    $record = [
        'timestamp' => date(DateTimeInterface::ATOM),
        'context' => $context,
        'message' => $message,
        'extra' => $extra,
    ];

    $encoded = json_encode($record, JSON_UNESCAPED_SLASHES);

    if ($encoded === false) {
        return;
    }

    $logDirectory = dirname(CLEANUP_LOG_FILE);

    if (!is_dir($logDirectory)) {
        @mkdir($logDirectory, 0775, true);
    }

    file_put_contents(CLEANUP_LOG_FILE, $encoded . PHP_EOL, FILE_APPEND);
}

