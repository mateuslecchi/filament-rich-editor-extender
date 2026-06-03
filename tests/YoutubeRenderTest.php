<?php

use MateusLecchi\FilamentRichEditorExtender\Extensions\Youtube;
use Tiptap\Editor;
use Tiptap\Nodes\Document;
use Tiptap\Nodes\Paragraph;
use Tiptap\Nodes\Text;

function renderYoutube(string $src, int $start = 0): string
{
    $editor = new Editor([
        'extensions' => [new Document, new Paragraph, new Text, new Youtube],
    ]);

    $editor->setContent([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'youtube',
                'attrs' => ['src' => $src, 'start' => $start],
            ],
        ],
    ]);

    return $editor->getHTML();
}

it('wraps the embed in a data-youtube-video div', function () {
    expect(renderYoutube('https://www.youtube.com/watch?v=dQw4w9WgXcQ'))
        ->toContain('<div data-youtube-video="true">')
        ->toContain('<iframe ');
});

it('converts a watch URL into a privacy-enhanced embed URL', function () {
    expect(renderYoutube('https://www.youtube.com/watch?v=dQw4w9WgXcQ'))
        ->toContain('src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=1"');
});

it('supports short youtu.be URLs', function () {
    expect(renderYoutube('https://youtu.be/dQw4w9WgXcQ'))
        ->toContain('src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=1"');
});

it('supports shorts URLs', function () {
    expect(renderYoutube('https://www.youtube.com/shorts/abc123DEFgh'))
        ->toContain('src="https://www.youtube-nocookie.com/embed/abc123DEFgh?rel=1"');
});

it('includes a start time when provided', function () {
    expect(renderYoutube('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 30))
        ->toContain('start=30');
});

it('renders default dimensions on the iframe', function () {
    expect(renderYoutube('https://www.youtube.com/watch?v=dQw4w9WgXcQ'))
        ->toContain('width="640"')
        ->toContain('height="480"');
});
