<?php

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;

/**
 * The HTML a YouTube node renders to (and what gets stored in HTML mode).
 */
function youtubeEmbedHtml(): string
{
    $document = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'youtube',
                'attrs' => ['src' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'start' => 0],
            ],
        ],
    ];

    return RichContentRenderer::make($document)
        ->plugins([YoutubePlugin::make()])
        ->getEditor()
        ->getHTML();
}

it('preserves the YouTube node when parsing stored HTML back to a document', function () {
    $json = RichContentRenderer::make(youtubeEmbedHtml())
        ->plugins([YoutubePlugin::make()])
        ->toArray();

    $node = collect($json['content'] ?? [])->firstWhere('type', 'youtube');

    expect($node)->not->toBeNull()
        ->and($node['attrs']['src'])->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ');
});

it('round-trips HTML to the same embed iframe (idempotent)', function () {
    $once = youtubeEmbedHtml();

    $twice = RichContentRenderer::make($once)
        ->plugins([YoutubePlugin::make()])
        ->getEditor()
        ->getHTML();

    expect($twice)
        ->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->toEqual($once);
});

it('renders stored HTML to a sanitized iframe via RichContentRenderer', function () {
    $html = RichContentRenderer::make(youtubeEmbedHtml())
        ->plugins([YoutubePlugin::make()])
        ->toHtml();

    expect($html)
        ->toContain('<iframe')
        ->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ');
});

/**
 * Reads the protected `isJson` property without triggering `isJson()`, which on a
 * container-less RichEditor would fall through to `getContentAttribute()`.
 * Returns true for JSON mode, null when the field was left untouched (HTML mode).
 */
function richEditorIsJsonFlag(RichEditor $editor): ?bool
{
    return (new ReflectionProperty(RichEditor::class, 'isJson'))->getValue($editor);
}

it('youtubeStorage macro applies JSON mode when requested', function () {
    expect(richEditorIsJsonFlag(RichEditor::make('content')->youtubeStorage('json')))->toBeTrue()
        ->and(richEditorIsJsonFlag(RichEditor::make('content')->youtubeStorage('html')))->toBeNull();
});

it('youtubeStorage macro follows the configured default', function () {
    config()->set('filament-rich-editor-extender.storage', 'json');
    expect(richEditorIsJsonFlag(RichEditor::make('content')->youtubeStorage()))->toBeTrue();

    config()->set('filament-rich-editor-extender.storage', 'html');
    expect(richEditorIsJsonFlag(RichEditor::make('content')->youtubeStorage()))->toBeNull();
});
