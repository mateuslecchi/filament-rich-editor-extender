<?php

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use MateusLecchi\FilamentRichEditorExtender\Plugins\XPlugin;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

it('keeps an X iframe through Filament html sanitization', function () {
    $html = '<div data-x-post="true"><iframe src="https://platform.twitter.com/embed/Tweet.html?id=20" width="550" height="600" allowfullscreen="true"></iframe></div>';

    expect(app(HtmlSanitizerInterface::class)->sanitize($html))
        ->toContain('<iframe')
        ->toContain('platform.twitter.com')
        ->toContain('data-x-post');
});

it('drops the src of a non-X iframe', function () {
    $html = '<iframe src="https://evil.example.com/x"></iframe>';

    expect(app(HtmlSanitizerInterface::class)->sanitize($html))
        ->not->toContain('evil.example.com');
});

it('renders a stored X node to a sanitized iframe via RichContentRenderer', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'x',
                'attrs' => ['src' => 'https://x.com/jack/status/20'],
            ],
        ],
    ];

    $html = RichContentRenderer::make($document)
        ->plugins([XPlugin::make()])
        ->toHtml();

    expect($html)
        ->toContain('<iframe')
        ->toContain('platform.twitter.com')
        ->toContain('Tweet.html');
});

it('preserves the X node through a JSON document round-trip', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'x',
                'attrs' => ['src' => 'https://x.com/jack/status/20'],
            ],
        ],
    ];

    $roundTripped = RichContentRenderer::make($document)
        ->plugins([XPlugin::make()])
        ->toArray();

    $node = collect($roundTripped['content'] ?? [])->firstWhere('type', 'x');

    expect($node)->not->toBeNull()
        ->and($node['attrs']['src'])->toBe('https://x.com/jack/status/20');
});
