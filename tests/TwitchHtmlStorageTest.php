<?php

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use MateusLecchi\FilamentRichEditorExtender\Plugins\TwitchPlugin;

/**
 * The HTML a Twitch node renders to (and what gets stored in HTML mode).
 */
function twitchEmbedHtml(): string
{
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

    return RichContentRenderer::make($document)
        ->plugins([TwitchPlugin::make()])
        ->getEditor()
        ->getHTML();
}

it('preserves the Twitch node when parsing stored HTML back to a document', function () {
    $json = RichContentRenderer::make(twitchEmbedHtml())
        ->plugins([TwitchPlugin::make()])
        ->toArray();

    $node = collect($json['content'] ?? [])->firstWhere('type', 'twitch');

    expect($node)->not->toBeNull()
        ->and($node['attrs']['src'])->toContain('player.twitch.tv/?video=1234567890');
});

it('round-trips HTML to the same embed iframe (idempotent)', function () {
    $once = twitchEmbedHtml();

    $twice = RichContentRenderer::make($once)
        ->plugins([TwitchPlugin::make()])
        ->getEditor()
        ->getHTML();

    expect($twice)
        ->toContain('player.twitch.tv/?video=1234567890')
        ->toEqual($once);
});

it('renders stored HTML to a sanitized iframe via RichContentRenderer', function () {
    $html = RichContentRenderer::make(twitchEmbedHtml())
        ->plugins([TwitchPlugin::make()])
        ->toHtml();

    expect($html)
        ->toContain('<iframe')
        // The sanitizer HTML-encodes `=` in attribute values, so match around it.
        ->toContain('player.twitch.tv')
        ->toContain('1234567890');
});
