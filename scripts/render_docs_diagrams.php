<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$inputPath = $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'FEATURE_FLOWS_AND_ERD.md';
$diagramDir = $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'diagrams';
$sourceDir = $diagramDir . DIRECTORY_SEPARATOR . 'src';

if (!is_file($inputPath)) {
    fwrite(STDERR, "Markdown file not found: {$inputPath}" . PHP_EOL);
    exit(1);
}

if (!is_dir($diagramDir) && !mkdir($diagramDir, 0777, true) && !is_dir($diagramDir)) {
    fwrite(STDERR, "Unable to create diagram directory: {$diagramDir}" . PHP_EOL);
    exit(1);
}

if (!is_dir($sourceDir) && !mkdir($sourceDir, 0777, true) && !is_dir($sourceDir)) {
    fwrite(STDERR, "Unable to create source directory: {$sourceDir}" . PHP_EOL);
    exit(1);
}

$content = file_get_contents($inputPath);
if ($content === false) {
    fwrite(STDERR, "Unable to read markdown file." . PHP_EOL);
    exit(1);
}

$lines = preg_split("/\r\n|\n|\r/", $content);
if (!is_array($lines)) {
    fwrite(STDERR, "Unable to split markdown file." . PHP_EOL);
    exit(1);
}

$heading = 'diagram';
$diagramIndex = 0;
$inMermaid = false;
$buffer = [];
$output = [];

$slugify = static function (string $text): string {
    $text = preg_replace('/^#+\s*/', '', trim($text));
    $text = preg_replace('/^\d+(?:\.\d+)*\.?\s*/', '', $text);
    $text = strtolower((string) $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', (string) $text);
    $text = trim((string) $text, '-');

    return $text !== '' ? $text : 'diagram';
};

$normalizeSource = static function (string $source): string {
    $trimmed = ltrim($source);
    if (!str_starts_with($trimmed, 'flowchart')) {
        return $source;
    }

    $quoteLabel = static function (string $content): string {
        $content = trim($content);
        if (preg_match('/^".*"$/', $content) === 1) {
            return $content;
        }

        $content = str_replace('"', '&quot;', $content);

        return '"' . $content . '"';
    };

    $source = preg_replace_callback(
        '/\[([^\[\]\r\n]+)\]/',
        static function (array $matches) use ($quoteLabel): string {
            return '[' . $quoteLabel($matches[1]) . ']';
        },
        $source
    );

    $source = preg_replace_callback(
        '/\{([^{}\r\n]+)\}/',
        static function (array $matches) use ($quoteLabel): string {
            return '{' . $quoteLabel($matches[1]) . '}';
        },
        $source
    );

    return $source;
};

$renderSvg = static function (string $source, string $svgPath): void {
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: text/plain\r\nAccept: image/svg+xml\r\n",
            'content' => $source,
            'timeout' => 60,
        ],
    ]);

    $svg = @file_get_contents('https://kroki.io/mermaid/svg', false, $context);
    if ($svg === false || stripos($svg, '<svg') === false) {
        $error = error_get_last();
        throw new RuntimeException(
            'Render failed for ' . basename($svgPath) . ': ' . ($error['message'] ?? 'unknown error')
        );
    }

    if (file_put_contents($svgPath, $svg) === false) {
        throw new RuntimeException('Unable to write SVG file: ' . $svgPath);
    }
};

foreach ($lines as $line) {
    if (!$inMermaid && preg_match('/^#{2,6}\s+(.+)$/', $line, $matches) === 1) {
        $heading = $matches[1];
        $output[] = $line;
        continue;
    }

    if (!$inMermaid && trim($line) === '```mermaid') {
        $inMermaid = true;
        $buffer = [];
        continue;
    }

    if ($inMermaid && trim($line) === '```') {
        $inMermaid = false;
        $diagramIndex++;

        $slug = $slugify($heading);
        $baseName = str_pad((string) $diagramIndex, 2, '0', STR_PAD_LEFT) . '-' . $slug;
        $sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $baseName . '.mmd';
        $svgPath = $diagramDir . DIRECTORY_SEPARATOR . $baseName . '.svg';

        $diagramSource = $normalizeSource(implode(PHP_EOL, $buffer) . PHP_EOL);
        file_put_contents($sourcePath, $diagramSource);
        $renderSvg($diagramSource, $svgPath);

        $output[] = '![' . $heading . '](diagrams/' . $baseName . '.svg)';
        $output[] = '';
        $output[] = '[Source Mermaid](diagrams/src/' . $baseName . '.mmd)';
        continue;
    }

    if ($inMermaid) {
        $buffer[] = $line;
        continue;
    }

    $output[] = $line;
}

if ($inMermaid) {
    fwrite(STDERR, "Unclosed Mermaid block detected." . PHP_EOL);
    exit(1);
}

$newContent = implode(PHP_EOL, $output) . PHP_EOL;
if (file_put_contents($inputPath, $newContent) === false) {
    fwrite(STDERR, "Unable to write updated markdown file." . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Rendered ' . $diagramIndex . ' diagrams.' . PHP_EOL);
