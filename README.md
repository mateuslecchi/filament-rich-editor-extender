# A set of extra extensions for Filament RichEditor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mateuslecchi/filament-rich-editor-extender.svg?style=flat-square)](https://packagist.org/packages/mateuslecchi/filament-rich-editor-extender)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mateuslecchi/filament-rich-editor-extender/run-tests.yml?branch=1.x&label=tests&style=flat-square)](https://github.com/mateuslecchi/filament-rich-editor-extender/actions?query=workflow%3Arun-tests+branch%3A1.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mateuslecchi/filament-rich-editor-extender/fix-php-code-style-issues.yml?branch=1.x&label=code%20style&style=flat-square)](https://github.com/mateuslecchi/filament-rich-editor-extender/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3A1.x)
[![Total Downloads](https://img.shields.io/packagist/dt/mateuslecchi/filament-rich-editor-extender.svg?style=flat-square)](https://packagist.org/packages/mateuslecchi/filament-rich-editor-extender)

Extra goodies for the Filament Forms RichEditor (v5). This package ships small, focused extensions that plug straight into your existing editor.

### Currently included:
- YouTube embeds.
- Twitch embeds (videos, clips and channels).
- X (Twitter) post embeds.
- More to come...

## Requirements

| Package Version | PHP Version | Laravel Version | Filament Forms Version |
|:---------------:|:-----------:|:---------------:|:----------------------:|
|       1.x       |    8.4+     |       13+       |          5.x           |

## Installation

You can install the package via composer:

```bash
composer require mateuslecchi/filament-rich-editor-extender
```

Then run the package installer:

```bash
php artisan filament-rich-editor-extender:install
```

## Usage

Once installed, all tools will be available for all RichEditor instances. The plugin registers itself automatically, so no additional configuration is required.

### YouTube

Add the `youtube` button to your editor's toolbar. It opens a modal asking for a YouTube URL and inserts the video as an embed.

```php
use Filament\Forms\Components\RichEditor;

RichEditor::make('content')
    ->label('Content')
    ->toolbarButtons([
        'youtube',
    ]);
```

Embeds use the privacy-enhanced `youtube-nocookie.com` domain by default. See
[Displaying stored content](#displaying-stored-content) for how to register the
`YoutubePlugin` when rendering, and [Sanitization](#sanitization) for the host allowlist.

### Twitch

Add the `twitch` button to your editor's toolbar. It opens a modal asking for a Twitch URL (video, clip or channel) and inserts it as an embed.

```php
use Filament\Forms\Components\RichEditor;

RichEditor::make('content')
    ->label('Content')
    ->toolbarButtons([
        'twitch',
    ]);
```

> ⚠️ **Twitch requires a `parent` domain.** Every Twitch embed must declare the
> domain(s) it is served from, or the player refuses to load. Publish the config
> and set `twitch.parent` to the domain(s) where your *rendered* content is shown
> (no scheme, no path). The live editor preview derives its parent from the current
> host automatically — this setting only applies to server-side rendered embeds.

```php
// config/filament-rich-editor-extender.php
'twitch' => [
    'parent' => ['example.com', 'www.example.com'],
],
```

See [Displaying stored content](#displaying-stored-content) for how to register the
`TwitchPlugin` when rendering, and [Sanitization](#sanitization) for the host allowlist.

### X (Twitter)

Add the `x` button to your editor's toolbar. It opens a modal asking for an X (or
legacy Twitter) post URL and inserts the post as an embed.

```php
use Filament\Forms\Components\RichEditor;

RichEditor::make('content')
    ->label('Content')
    ->toolbarButtons([
        'x',
    ]);
```

Posts are embedded via an `<iframe>` pointing at `platform.twitter.com`, so the
embed survives sanitization and server-side rendering just like the other media.
Both `x.com/.../status/<id>` and `twitter.com/.../status/<id>` URLs are accepted —
the package extracts the post id and builds the embed URL for you. See
[Displaying stored content](#displaying-stored-content) for how to register the
`XPlugin` when rendering.

> ℹ️ **Resizing.** An X post iframe has a fixed height, so tall posts (with images
> or quotes) would be cropped. The editor resizes them automatically. On your
> **front-end** pages, include the small resize script so displayed posts fit their
> content — see [Resizing X embeds on the front-end](#resizing-x-embeds-on-the-front-end).

### Storage modes (HTML or JSON)

Filament can store `RichEditor` content as **HTML** (its native default) or as a structured **JSON** document. **Every embed in this package works in both** — pick what fits your app:

| | HTML (default) | JSON |
|---|---|---|
| Column | `text` / `longText`, **no cast** | `json`, or cast the attribute to `array` |
| Field | `->youtubeStorage()` (or nothing) | `->youtubeStorage('json')` (or Filament's `->json()`) |
| Stored value | ready-to-use HTML | structured document |

Use the per-field helper to follow the package's configured default:

```php
use Filament\Forms\Components\RichEditor;

RichEditor::make('content')
    ->youtubeStorage()       // HTML by default; ->youtubeStorage('json') for JSON
    ->toolbarButtons(['youtube', 'twitch', 'x']);
```

> The `->youtubeStorage()` helper toggles the **field's** storage mode (HTML vs JSON);
> it is not YouTube-specific and applies regardless of which embeds the field uses.

The default mode is read from `config('filament-rich-editor-extender.storage')` (`'html'` out of the box). The helper is **opt-in per field** — it never changes the storage mode of your other `RichEditor` fields. For JSON mode, remember to make the column array-castable:

```php
protected function casts(): array
{
    return ['content' => 'array']; // JSON mode only
}
```

> ⚠️ Switching an existing field between HTML and JSON changes the stored format. Migrate the column/cast **and** the existing rows accordingly — the package does not convert data for you.

### Displaying stored content

When you render stored content, the renderer needs to know about each plugin so it can turn the stored node back into an `<iframe>`. Filament's global editor configuration is **not** inherited by `RichContentRenderer`, so register the plugins you use on the content explicitly.

The idiomatic way is via the model, using Filament's `HasRichContent`. Register only the plugins whose embeds your content can contain:

```php
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use MateusLecchi\FilamentRichEditorExtender\Plugins\TwitchPlugin;
use MateusLecchi\FilamentRichEditorExtender\Plugins\XPlugin;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;

class Post extends Model implements HasRichContent
{
    use InteractsWithRichContent;

    protected function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->plugins([
                YoutubePlugin::make(),
                TwitchPlugin::make(),
                XPlugin::make(),
            ]);
    }
}
```

```blade
{!! $post->renderRichContent('content') !!}
```

Or render directly:

```php
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use MateusLecchi\FilamentRichEditorExtender\Plugins\TwitchPlugin;
use MateusLecchi\FilamentRichEditorExtender\Plugins\XPlugin;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;

RichContentRenderer::make($post->content)
    ->plugins([
        YoutubePlugin::make(),
        TwitchPlugin::make(),
        XPlugin::make(),
    ])
    ->toHtml();
```

> **Twitch only:** server-side rendered embeds use the `twitch.parent` config value
> for their `parent` query parameter. Set it to the domain(s) where this content is
> displayed, otherwise the Twitch player will refuse to load. See [Twitch](#twitch).

### Resizing X embeds on the front-end

X (Twitter) post iframes have a fixed height, so taller posts get cropped unless the
iframe is resized to its content. **In the editor this happens automatically.** For
your front-end pages, this package ships a tiny (~1 KB) script that listens for the
height the X embed reports and resizes each post to fit.

Publish it to your `public` directory:

```bash
php artisan vendor:publish --tag=filament-rich-editor-extender-assets
```

Then include it once on pages that display X embeds (e.g. in your layout):

```blade
<script src="{{ asset('vendor/filament-rich-editor-extender/XEmbed.js') }}" async></script>
```

The script is self-initializing and watches for embeds added to the page later, so it
works with content loaded dynamically too. It only touches `platform.twitter.com`
iframes; YouTube and Twitch embeds keep their configured dimensions and need nothing.

### Sanitization

Filament renders rich content through `Str::sanitizeHtml()`, whose sanitizer strips `<iframe>` by default — which would remove every embed. To prevent that, this package extends Filament's **application-wide** sanitizer config to allow the embed `<iframe>`, restricting its `src` to a host allowlist (any other host's iframe `src` is dropped, leaving a harmless empty iframe). Other media is unaffected.

Each platform contributes its own hosts, and the allowlists are merged into a single sanitizer. You can toggle or customize each one. Publish the config to control it:

```bash
php artisan vendor:publish --tag=filament-rich-editor-extender-config
```

```php
// config/filament-rich-editor-extender.php
'youtube' => [
    'sanitizer' => [
        'enabled' => true, // set false to opt out of the global change
        'allowed_hosts' => ['youtube-nocookie.com', 'youtube.com'],
    ],
],

'twitch' => [
    'parent' => ['example.com'], // domain(s) serving your rendered embeds
    'sanitizer' => [
        'enabled' => true,
        'allowed_hosts' => ['player.twitch.tv', 'clips.twitch.tv'],
    ],
],

'x' => [
    'sanitizer' => [
        'enabled' => true,
        'allowed_hosts' => ['platform.twitter.com'],
    ],
],
```

## AI agents (Laravel Boost)

This package ships [Laravel Boost](https://laravel.com/docs/boost) guidelines so AI
coding agents know how to use it correctly — adding the toolbar buttons, choosing a
storage mode, registering the plugins when displaying content, the Twitch `parent`
requirement and the sanitizer hosts.

If your app uses Laravel Boost, the guidelines are picked up automatically. To pull
them in (or refresh them after installing this package), run:

```bash
php artisan boost:install
# or, to scan for newly installed packages:
php artisan boost:update --discover
```

The guidelines live at `resources/boost/guidelines/core.blade.php` in this package —
no configuration is required on your side.

## Translations

The package ships with translations (currently `en` and `pt_BR`). To customize them in your app, publish the language files:

```bash
php artisan vendor:publish --tag=filament-rich-editor-extender-translations
```

They will be copied to `lang/vendor/filament-rich-editor-extender`. Contributions with new locales are welcome.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Mateus Lecchi](https://github.com/mateuslecchi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
