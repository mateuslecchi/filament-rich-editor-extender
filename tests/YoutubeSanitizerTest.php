<?php

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;
use MateusLecchi\FilamentRichEditorExtender\Sanitizers\IframeSrcHostSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

it('keeps a YouTube iframe through Filament html sanitization', function () {
    $html = '<div data-youtube-video="true"><iframe src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=1" width="640" height="480" allowfullscreen="true"></iframe></div>';

    expect(app(HtmlSanitizerInterface::class)->sanitize($html))
        ->toContain('<iframe')
        ->toContain('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->toContain('data-youtube-video');
});

it('drops the src of a non-YouTube iframe', function () {
    $html = '<iframe src="https://evil.example.com/x"></iframe>';

    expect(app(HtmlSanitizerInterface::class)->sanitize($html))
        ->not->toContain('evil.example.com');
});

it('renders a stored YouTube node to a sanitized iframe via RichContentRenderer', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'youtube',
                'attrs' => ['src' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'start' => 0],
            ],
        ],
    ];

    $html = RichContentRenderer::make($document)
        ->plugins([YoutubePlugin::make()])
        ->toHtml();

    expect($html)
        ->toContain('<iframe')
        ->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ');
});

it('preserves the YouTube node through a JSON document round-trip', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'youtube',
                'attrs' => ['src' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'start' => 0],
            ],
        ],
    ];

    $roundTripped = RichContentRenderer::make($document)
        ->plugins([YoutubePlugin::make()])
        ->toArray();

    $node = collect($roundTripped['content'] ?? [])->firstWhere('type', 'youtube');

    expect($node)->not->toBeNull()
        ->and($node['attrs']['src'])->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
});

it('honours a custom allowed-hosts list', function () {
    $sanitizer = new IframeSrcHostSanitizer(['vimeo.com']);
    $config = new HtmlSanitizerConfig;

    expect($sanitizer->sanitizeAttribute('iframe', 'src', 'https://player.vimeo.com/video/1', $config))
        ->toBe('https://player.vimeo.com/video/1')
        ->and($sanitizer->sanitizeAttribute('iframe', 'src', 'https://www.youtube.com/embed/x', $config))
        ->toBeNull();
});
