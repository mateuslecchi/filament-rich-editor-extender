<?php

namespace MateusLecchi\FilamentRichEditorExtender\Extensions;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class Youtube extends Node
{
    public static $name = 'youtube';

    public function addOptions(): array
    {
        return [
            'width' => 640,
            'height' => 480,
            'controls' => true,
            'nocookie' => true,
            'allowFullscreen' => true,
            'rel' => 1,
            'HTMLAttributes' => [],
        ];
    }

    public function addAttributes(): array
    {
        return [
            // The node matches the wrapping `div[data-youtube-video]`, so the iframe
            // attributes are recovered from the inner <iframe> when parsing HTML back
            // into a node (HTML storage mode). In JSON storage the node is data and
            // these closures are not used.
            'src' => [
                'default' => null,
                'parseHTML' => fn ($DOMNode): ?string => $this->iframeAttribute($DOMNode, 'src'),
            ],
            'start' => [
                'default' => 0,
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
                'tag' => 'div[data-youtube-video]',
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
        $embedUrl = $this->getEmbedUrlFromYoutubeUrl(
            $HTMLAttributes['src'] ?? null,
            (int) ($HTMLAttributes['start'] ?? 0),
        );

        return [
            'div',
            ['data-youtube-video' => 'true'],
            [
                'iframe',
                HTML::mergeAttributes($this->options['HTMLAttributes'], [
                    'src' => $embedUrl,
                    'width' => $HTMLAttributes['width'] ?? $this->options['width'],
                    'height' => $HTMLAttributes['height'] ?? $this->options['height'],
                    'allowfullscreen' => $this->options['allowFullscreen'] ? 'true' : null,
                    'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
                ]),
            ],
        ];
    }

    /**
     * Port of the Tiptap JS extension's `getEmbedUrlFromYoutubeUrl()` so that
     * server-side rendering matches what the editor produces.
     */
    protected function getEmbedUrlFromYoutubeUrl(?string $url, int $startAt = 0): ?string
    {
        if (blank($url) || ! $this->isValidYoutubeUrl($url)) {
            return null;
        }

        // Already an embed URL.
        if (str_contains($url, '/embed/')) {
            return $url;
        }

        $isPlaylist = false;
        $id = null;

        if (str_contains($url, 'youtu.be')) {
            $id = collect(explode('/', $url))->filter()->last();
        } elseif (preg_match('/(?:(v|list)=|shorts\/)([-\w]+)/', $url, $matches)) {
            $isPlaylist = $matches[1] === 'list';
            $id = $matches[2];
        }

        if (blank($id)) {
            return null;
        }

        $outputUrl = $this->getYoutubeEmbedUrl($isPlaylist).$id;

        $params = [];

        if (! $this->options['controls']) {
            $params[] = 'controls=0';
        }

        if ($startAt > 0) {
            $params[] = "start={$startAt}";
        }

        if ($this->options['rel'] !== null) {
            $params[] = 'rel='.$this->options['rel'];
        }

        if (filled($params)) {
            $outputUrl .= ($isPlaylist ? '&' : '?').implode('&', $params);
        }

        return $outputUrl;
    }

    protected function getYoutubeEmbedUrl(bool $isPlaylist = false): string
    {
        $domain = $this->options['nocookie'] ? 'https://www.youtube-nocookie.com' : 'https://www.youtube.com';

        return $isPlaylist
            ? "{$domain}/embed/videoseries?list="
            : "{$domain}/embed/";
    }

    protected function isValidYoutubeUrl(string $url): bool
    {
        return (bool) preg_match(
            '/^((?:https?:)?\/\/)?((?:www|m|music)\.)?((?:youtube\.com|youtu\.be|youtube-nocookie\.com))(\/(?:[\w-]+\?v=|embed\/|v\/)?)([\w-]+)(\S+)?$/',
            $url,
        );
    }
}
