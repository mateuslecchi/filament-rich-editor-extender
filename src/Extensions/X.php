<?php

namespace MateusLecchi\FilamentRichEditorExtender\Extensions;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class X extends Node
{
    public static $name = 'x';

    public function addOptions(): array
    {
        return [
            'width' => 550,
            'height' => 600,
            'allowFullscreen' => true,
            'HTMLAttributes' => [],
        ];
    }

    public function addAttributes(): array
    {
        return [
            // The node matches the wrapping `div[data-x-post]`, so the iframe
            // attributes are recovered from the inner <iframe> when parsing HTML back
            // into a node (HTML storage mode). In JSON storage the node is data and
            // these closures are not used.
            'src' => [
                'default' => null,
                'parseHTML' => fn ($DOMNode): ?string => $this->iframeAttribute($DOMNode, 'src'),
            ],
            'width' => [
                'default' => $this->options['width'],
                'parseHTML' => fn ($DOMNode): ?string => $this->iframeAttribute($DOMNode, 'width'),
            ],
            'height' => [
                'default' => $this->options['height'],
                'parseHTML' => fn ($DOMNode): ?string => $this->iframeAttribute($DOMNode, 'height'),
            ],
        ];
    }

    public function parseHTML(): array
    {
        // tiptap-php's DOMParser only supports a single `tag` or `tag[attr]` selector
        // (no descendant selectors), so we match the wrapper div, not the iframe.
        return [
            [
                'tag' => 'div[data-x-post]',
            ],
        ];
    }

    protected function iframeAttribute(\DOMElement $DOMNode, string $attribute): ?string
    {
        $iframe = $DOMNode->getElementsByTagName('iframe')->item(0);

        return $iframe?->getAttribute($attribute) ?: null;
    }

    public function renderHTML($node, $HTMLAttributes = []): array
    {
        $embedUrl = $this->getEmbedUrlFromXUrl($HTMLAttributes['src'] ?? null);

        return [
            'div',
            ['data-x-post' => 'true'],
            [
                'iframe',
                HTML::mergeAttributes($this->options['HTMLAttributes'], [
                    'src' => $embedUrl,
                    'width' => $HTMLAttributes['width'] ?? $this->options['width'],
                    'height' => $HTMLAttributes['height'] ?? $this->options['height'],
                    'allowfullscreen' => $this->options['allowFullscreen'] ? 'true' : null,
                    'scrolling' => 'no',
                    'frameborder' => '0',
                ]),
            ],
        ];
    }

    /**
     * Turns an X/Twitter post URL (or an already-built embed URL) into the
     * iframe embed URL, mirroring the JS extension so SSR matches the editor.
     */
    protected function getEmbedUrlFromXUrl(?string $url): ?string
    {
        $id = $this->getTweetId($url);

        return $id !== null
            ? "https://platform.twitter.com/embed/Tweet.html?id={$id}"
            : null;
    }

    protected function getTweetId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        // A standard status URL (twitter.com / x.com /<user>/status/<id>) or an
        // already-built embed URL (platform.twitter.com/embed/Tweet.html?id=<id>).
        if (preg_match('/\/status\/(\d+)/', $url, $matches)
            || preg_match('/[?&]id=(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
