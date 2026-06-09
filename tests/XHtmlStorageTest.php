<?php

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use MateusLecchi\FilamentRichEditorExtender\Plugins\XPlugin;

/**
 * The HTML an X node renders to (and what gets stored in HTML mode).
 */
function xEmbedHtml(): string
{
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'x',
                'attrs' => ['src' => 'https://x.com/jack/status/20'],
            ],
        ],
    ];

    return RichContentRenderer::make($document)
        ->plugins([XPlugin::make()])
        ->getEditor()
        ->getHTML();
}

it('preserves the X node when parsing stored HTML back to a document', function () {
    $json = RichContentRenderer::make(xEmbedHtml())
        ->plugins([XPlugin::make()])
        ->toArray();

    $node = collect($json['content'] ?? [])->firstWhere('type', 'x');

    expect($node)->not->toBeNull()
        ->and($node['attrs']['src'])->toContain('platform.twitter.com/embed/Tweet.html?id=20');
});

it('round-trips HTML to the same embed iframe (idempotent)', function () {
    $once = xEmbedHtml();

    $twice = RichContentRenderer::make($once)
        ->plugins([XPlugin::make()])
        ->getEditor()
        ->getHTML();

    expect($twice)
        ->toContain('platform.twitter.com/embed/Tweet.html?id=20')
        ->toEqual($once);
});

it('renders stored HTML to a sanitized iframe via RichContentRenderer', function () {
    $html = RichContentRenderer::make(xEmbedHtml())
        ->plugins([XPlugin::make()])
        ->toHtml();

    expect($html)
        ->toContain('<iframe')
        ->toContain('platform.twitter.com');
});
