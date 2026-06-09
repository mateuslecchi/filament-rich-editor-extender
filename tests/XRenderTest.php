<?php

use MateusLecchi\FilamentRichEditorExtender\Extensions\X;
use Tiptap\Editor;
use Tiptap\Nodes\Document;
use Tiptap\Nodes\Paragraph;
use Tiptap\Nodes\Text;

function renderX(string $src): string
{
    $editor = new Editor([
        'extensions' => [new Document, new Paragraph, new Text, new X],
    ]);

    $editor->setContent([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'x',
                'attrs' => ['src' => $src],
            ],
        ],
    ]);

    return $editor->getHTML();
}

it('wraps the embed in a data-x-post div', function () {
    expect(renderX('https://x.com/jack/status/20'))
        ->toContain('<div data-x-post="true">')
        ->toContain('<iframe ');
});

it('converts an x.com status URL into the embed URL', function () {
    expect(renderX('https://x.com/jack/status/20'))
        ->toContain('src="https://platform.twitter.com/embed/Tweet.html?id=20"');
});

it('supports legacy twitter.com URLs', function () {
    expect(renderX('https://twitter.com/jack/status/20'))
        ->toContain('platform.twitter.com/embed/Tweet.html?id=20');
});

it('ignores trailing query strings on the status URL', function () {
    expect(renderX('https://x.com/jack/status/20?s=46&t=abc'))
        ->toContain('Tweet.html?id=20"');
});

it('renders default dimensions on the iframe', function () {
    expect(renderX('https://x.com/jack/status/20'))
        ->toContain('width="550"')
        ->toContain('height="600"');
});
