<?php

namespace MateusLecchi\FilamentRichEditorExtender\Extensions;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class Twitch extends Node
{
    public static $name = 'twitch';

    public function addOptions(): array
    {
        return [
            'width' => 640,
            'height' => 480,
            'allowFullscreen' => true,
            'autoplay' => false,
            'muted' => false,
            // Twitch requires the `parent` of every embed to match the domain(s)
            // it is served from. Configure this for your app; 'localhost' only
            // works in local development.
            'parent' => ['localhost'],
            'HTMLAttributes' => [],
        ];
    }

    public function addAttributes(): array
    {
        return [
            // The node matches the wrapping `div[data-twitch-video]`, so the iframe
            // attributes are recovered from the inner <iframe> when parsing HTML back
            // into a node (HTML storage mode). In JSON storage the node is data and
            // these closures are not used.
            'src' => [
                'default' => null,
                'parseHTML' => fn ($DOMNode): ?string => $this->iframeAttribute($DOMNode, 'src'),
            ],
            'time' => [
                'default' => null,
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
                'tag' => 'div[data-twitch-video]',
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
        $embedUrl = $this->getEmbedUrlFromTwitchUrl(
            $HTMLAttributes['src'] ?? null,
            $HTMLAttributes['time'] ?? null,
        );

        return [
            'div',
            ['data-twitch-video' => 'true'],
            [
                'iframe',
                HTML::mergeAttributes($this->options['HTMLAttributes'], [
                    'src' => $embedUrl,
                    'width' => $HTMLAttributes['width'] ?? $this->options['width'],
                    'height' => $HTMLAttributes['height'] ?? $this->options['height'],
                    'allowfullscreen' => $this->options['allowFullscreen'] ? 'true' : null,
                    'scrolling' => 'no',
                    'frameborder' => '0',
                    'allow' => 'autoplay; fullscreen',
                ]),
            ],
        ];
    }

    /**
     * Port of the Tiptap JS extension's `getEmbedUrlFromTwitchUrl()` so that
     * server-side rendering matches what the editor produces.
     */
    protected function getEmbedUrlFromTwitchUrl(?string $url, ?string $time = null): ?string
    {
        if (blank($url)) {
            return null;
        }

        // Already an embed URL.
        if (str_contains($url, 'player.twitch.tv') || str_contains($url, 'clips.twitch.tv/embed')) {
            return $url;
        }

        $identifier = $this->getTwitchIdentifier($url);

        if ($identifier === null) {
            return null;
        }

        [$type, $id] = $identifier;

        $params = [];

        foreach ($this->options['parent'] as $parent) {
            $params[] = 'parent='.$parent;
        }

        $params[] = 'autoplay='.($this->options['autoplay'] ? 'true' : 'false');
        $params[] = 'muted='.($this->options['muted'] ? 'true' : 'false');

        if ($type === 'clip') {
            array_unshift($params, 'clip='.$id);

            return 'https://clips.twitch.tv/embed?'.implode('&', $params);
        }

        if ($this->options['allowFullscreen']) {
            $params[] = 'allowfullscreen=true';
        }

        if ($type === 'video') {
            array_unshift($params, 'video='.$id);

            if (filled($time)) {
                $params[] = 'time='.$time;
            }

            return 'https://player.twitch.tv/?'.implode('&', $params);
        }

        // channel
        array_unshift($params, 'channel='.$id);

        return 'https://player.twitch.tv/?'.implode('&', $params);
    }

    /**
     * Detects the content type and id of a Twitch URL.
     *
     * @return array{0: string, 1: string}|null [type, id] where type is 'clip'|'video'|'channel'
     */
    protected function getTwitchIdentifier(string $url): ?array
    {
        if (preg_match('/clips\.twitch\.tv\/([\w-]+)/', $url, $matches)) {
            return ['clip', $matches[1]];
        }

        if (preg_match('/twitch\.tv\/videos\/(\d+)/', $url, $matches)) {
            return ['video', $matches[1]];
        }

        if (preg_match('/twitch\.tv\/[\w-]+\/clip\/([\w-]+)/', $url, $matches)) {
            return ['clip', $matches[1]];
        }

        if (preg_match('/twitch\.tv\/([\w-]+)/', $url, $matches)) {
            return ['channel', $matches[1]];
        }

        return null;
    }
}
