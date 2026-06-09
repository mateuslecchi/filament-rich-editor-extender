<?php

namespace MateusLecchi\FilamentRichEditorExtender\Sanitizers;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Restricts the `src` of `<iframe>` elements to an allowlist of hosts.
 *
 * Filament's `RichContentRenderer::toHtml()` runs every node's HTML through
 * Symfony's `HtmlSanitizer`, whose `allowSafeElements()` list does not include
 * `<iframe>`. We allow the element globally (see the service provider) so media
 * embeds (YouTube, Twitch, X, …) survive, and this sanitizer narrows the only
 * meaningful attack surface back down: an iframe whose `src` host is not on the
 * allowlist has its `src` dropped, leaving a harmless empty iframe.
 *
 * Filament binds a single shared `HtmlSanitizerConfig`, and chaining several
 * host-allowlist sanitizers for the same element/attribute would conflict (one
 * would drop a host another just allowed). So a single instance must carry the
 * union of every enabled plugin's hosts.
 */
class IframeSrcHostSanitizer implements AttributeSanitizerInterface
{
    /**
     * @param  array<string>  $allowedHostSuffixes
     */
    public function __construct(
        protected array $allowedHostSuffixes = [],
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
