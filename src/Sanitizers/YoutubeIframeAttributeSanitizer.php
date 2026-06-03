<?php

namespace MateusLecchi\FilamentRichEditorExtender\Sanitizers;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Restricts the `src` of `<iframe>` elements to YouTube hosts.
 *
 * Filament's `RichContentRenderer::toHtml()` runs every node's HTML through
 * Symfony's `HtmlSanitizer`, whose `allowSafeElements()` list does not include
 * `<iframe>`. We allow the element globally (see the service provider) so the
 * YouTube embed survives, and this sanitizer narrows the only meaningful attack
 * surface back down: a non-YouTube `src` is dropped, leaving a harmless empty
 * iframe. Image/media hosts are left untouched, so other media keeps working.
 */
class YoutubeIframeAttributeSanitizer implements AttributeSanitizerInterface
{
    /**
     * @param  array<string>  $allowedHostSuffixes
     */
    public function __construct(
        protected array $allowedHostSuffixes = [
            'youtube-nocookie.com',
            'youtube.com',
        ],
    ) {}

    /**
     * @return array<string>
     */
    public function getSupportedElements(): ?array
    {
        return ['iframe'];
    }

    /**
     * @return array<string>
     */
    public function getSupportedAttributes(): ?array
    {
        return ['src'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        $host = parse_url($value, PHP_URL_HOST);

        if (! is_string($host)) {
            return null;
        }

        $host = strtolower($host);

        foreach ($this->allowedHostSuffixes as $suffix) {
            if ($host === $suffix || str_ends_with($host, '.'.$suffix)) {
                return $value;
            }
        }

        return null;
    }
}
