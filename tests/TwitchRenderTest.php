<?php

use MateusLecchi\FilamentRichEditorExtender\Extensions\Twitch;
use Tiptap\Editor;
use Tiptap\Nodes\Document;
use Tiptap\Nodes\Paragraph;
use Tiptap\Nodes\Text;

function renderTwitch(string $src, ?string $time = null): string
{
    $editor = new Editor([
        'extensions' => [new Document, new Paragraph, new Text, new Twitch(['parent' => ['example.com']])],
    ]);

    $editor->setContent([
        'type' => 'doc',
        'content' => [
            [
                'type' => 'twitch',
                'attrs' => ['src' => $src, 'time' => $time],
            ],
        ],
    ]);

    return $editor->getHTML();
}

it('wraps the embed in a data-twitch-video div', function () {
    expect(renderTwitch('https://www.twitch.tv/videos/1234567890'))
        ->toContain('<div data-twitch-video="true">')
        ->toContain('<iframe ');
});

it('converts a video URL into a player.twitch.tv embed URL', function () {
    expect(renderTwitch('https://www.twitch.tv/videos/1234567890'))
        ->toContain('https://player.twitch.tv/?video=1234567890')
        ->toContain('parent=example.com');
});

it('converts a clips.twitch.tv URL into a clips embed URL', function () {
    expect(renderTwitch('https://clips.twitch.tv/AwesomeClipName'))
        ->toContain('https://clips.twitch.tv/embed?clip=AwesomeClipName')
        ->toContain('parent=example.com');
});

it('converts a channel clip URL into a clips embed URL', function () {
    expect(renderTwitch('https://www.twitch.tv/somechannel/clip/AwesomeClipName'))
        ->toContain('clips.twitch.tv/embed?clip=AwesomeClipName');
});

it('converts a channel URL into a channel embed URL', function () {
    expect(renderTwitch('https://www.twitch.tv/somechannel'))
        ->toContain('https://player.twitch.tv/?channel=somechannel')
        ->toContain('parent=example.com');
});

it('includes a start time for videos when provided', function () {
    expect(renderTwitch('https://www.twitch.tv/videos/1234567890', '1h2m3s'))
        ->toContain('time=1h2m3s');
});

it('renders default dimensions on the iframe', function () {
    expect(renderTwitch('https://www.twitch.tv/videos/1234567890'))
        ->toContain('width="640"')
        ->toContain('height="480"');
});
