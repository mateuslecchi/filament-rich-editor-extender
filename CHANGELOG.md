# Changelog

All notable changes to `filament-rich-editor-extender` will be documented in this file.

## v1.1.3 - 2026-06-09

### Fixed

- **Laravel Boost guidelines** — escaped a Blade echo (`{!! $post->renderRichContent('content') !!}`) in `resources/boost/guidelines/core.blade.php` that
  Boost evaluated as real Blade, causing an "undefined `$post`" error during `boost:install`.
  The snippet is now wrapped in `@verbatim` and renders as literal text.

## v1.1.2 - 2026-06-09

### Added

- **Laravel Boost integration** — the package now ships AI agent guidelines at
  `resources/boost/guidelines/core.blade.php`. When consuming apps use Laravel Boost, these are
  picked up automatically (`php artisan boost:install`, or `php artisan boost:update --discover`), helping AI agents use the editor tools correctly: enabling the `youtube` /
  `twitch` / `x` toolbar buttons, choosing a storage mode, registering the plugins when
  displaying stored content, the Twitch `parent` requirement, and the iframe sanitizer hosts.

### Documentation

- README: new **"AI agents (Laravel Boost)"** section describing the bundled guidelines and
  how to load them.

Depois de publicar, confirme que o badge/versão no Packagist atualizou para v1.1.2 (pode levar
um minuto).

## v1.0.0 - 2026-06-03

- Initial release.
- YouTube extension: toolbar button + modal to embed YouTube videos, with privacy-enhanced (`youtube-nocookie.com`) server-side rendering.
- Works in both **HTML** and **JSON** storage modes; opt-in per field via the `->youtubeStorage()` RichEditor helper, with the default mode set in config (`storage`).
- Allows the YouTube `<iframe>` through Filament's HTML sanitizer, restricting its `src` to YouTube hosts; configurable via the `youtube.sanitizer` config.
- Publishable config (`filament-rich-editor-extender-config` tag).
- Translations (`en`, `pt_BR`), publishable via the `filament-rich-editor-extender-translations` tag.
