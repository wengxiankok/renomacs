<?php
// Audit HTTP snapshots, not a replacement for a rendered browser/accessibility test.
$report = []; $documents = [];
foreach (['home' => '/', 'contact' => '/contact-us/', 'privacy' => '/privacy-policy/', 'terms' => '/terms-condition/'] as $name => $path) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(file_get_contents('/qa/' . $name . '.html'));
    libxml_clear_errors();
    $documents[$path] = $dom;
}
foreach ($documents as $path => $dom) {
    $xp = new DOMXPath($dom);
    $texts = fn($query) => array_map(fn($node) => trim($node->textContent), iterator_to_array($xp->query($query)));
    $issues = [];
    foreach ($xp->query('//a[@href]') as $a) {
        $href = html_entity_decode($a->getAttribute('href'));
        if (!trim($a->textContent) && !$a->getAttribute('aria-label') && !$xp->query('.//img[string-length(@alt)>0]', $a)->length) {
            $issues[] = ['type' => 'unnamed-link', 'href' => $href];
        }
        if (str_starts_with($href, 'http://localhost:8080') || str_starts_with($href, '/')) {
            $parts = parse_url($href); $target = $parts['path'] ?? '/';
            $target = '/' . trim($target, '/') . ($target === '/' ? '' : '/');
            if (isset($documents[$target], $parts['fragment'])) {
                $found = false;
                foreach ($documents[$target]->getElementsByTagName('*') as $node) { if ($node->getAttribute('id') === $parts['fragment']) { $found = true; break; } }
                if (!$found) { $issues[] = ['type' => 'missing-anchor', 'href' => $href]; }
            } elseif (!isset($documents[$target]) && !str_contains($target, '/wp-')) { $issues[] = ['type'=>'unlisted-internal-path','href'=>$href]; }
        }
    }
    $report[$path] = [
        'title' => $texts('//title'), 'h1' => $texts('//h1'),
        'headings' => $texts('//main//h1 | //main//h2'),
        'metaDescriptionCount' => $xp->query('//meta[@name="description"]')->length,
        'canonicalCount' => $xp->query('//link[@rel="canonical"]')->length,
        'imagesWithoutAlt' => $xp->query('//img[not(@alt)]')->length,
        'inertQuoteCta' => $xp->query('//div[contains(@class,"btn") and normalize-space(text())="Get a Quote"]')->length,
        'issues' => $issues,
        'pageText' => mb_substr(preg_replace('/\s+/', ' ', implode(' ', $texts('//main//p'))), 0, 6000),
    ];
}
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
