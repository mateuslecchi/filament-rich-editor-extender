<?php

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use MateusLecchi\FilamentRichEditorExtender\Plugins\TwitchPlugin;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

it('keeps a Twitch iframe through Filament html sanitization', function () {
    $html = '<div data-twitch-video="true"><iframe src="https://player.twitch.tv/?video=1234567890&parent=example.com" width="640" height="480" allowfullscreen="true"></iframe></div>';

    expect(app(HtmlSanitizerInterface::class)->sanitize($html))
        ->toContain('<iframe')
        ->toContain('player.twitch.tv')
        ->toContain('data-twitch-video');
});

it('drops the src of a non-Twitch iframe', function () {
    $html = '<iframe src="https://evil.example.com/x"></iframe>';

    expect(app(HtmlSanitizerInterface::class)->sanitize($html))
        ->not->toContain('evil.example.com');
});

it('renders a stored Twitch node to a sanitized iframe via RichContentRenderer', function () {
    config()->set('filament-rich-editor-extender.twitch.parent', ['example.com']);

    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'twitch',
                'attrs' => ['src' => 'https://www.twitch.tv/videos/1234567890', 'time' => null],
            ],
        ],
    ];

    $html = RichContentRenderer::make($document)
        ->plugins([TwitchPlugin::make()])
        ->toHtml();

    expect($html)
        ->toContain('<iframe')
        // The sanitizer HTML-encodes `=` in attribute values, so match around it.
        ->toContain('player.twitch.tv')
        ->toContain('1234567890');
});

it('preserves the Twitch node through a JSON document round-trip', function () {
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'twitch',
                'attrs' => ['src' => 'https://www.twitch.tv/videos/1234567890', 'time' => null],
            ],
        ],
    ];

    $roundTripped = RichContentRenderer::make($document)
        ->plugins([TwitchPlugin::make()])
        ->toArray();

    $node = collect($roundTripped['content'] ?? [])->firstWhere('type', 'twitch');

    expect($node)->not->toBeNull()
        ->and($node['attrs']['src'])->toBe('https://www.twitch.tv/videos/1234567890');
});
