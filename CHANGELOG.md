# Changelog

All notable changes to `filament-rich-editor-extender` will be documented in this file.

## v1.0.0 - 2026-06-03

- Initial release.
- YouTube extension: toolbar button + modal to embed YouTube videos, with privacy-enhanced (`youtube-nocookie.com`) server-side rendering.
- Works in both **HTML** and **JSON** storage modes; opt-in per field via the `->youtubeStorage()` RichEditor helper, with the default mode set in config (`storage`).
- Allows the YouTube `<iframe>` through Filament's HTML sanitizer, restricting its `src` to YouTube hosts; configurable via the `youtube.sanitizer` config.
- Publishable config (`filament-rich-editor-extender-config` tag).
- Translations (`en`, `pt_BR`), publishable via the `filament-rich-editor-extender-translations` tag.
