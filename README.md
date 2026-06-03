# A set of extra extensions for Filament RichEditor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mateuslecchi/filament-rich-editor-extender.svg?style=flat-square)](https://packagist.org/packages/mateuslecchi/filament-rich-editor-extender)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mateuslecchi/filament-rich-editor-extender/run-tests.yml?branch=1.x&label=tests&style=flat-square)](https://github.com/mateuslecchi/filament-rich-editor-extender/actions?query=workflow%3Arun-tests+branch%3A1.x)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mateuslecchi/filament-rich-editor-extender/fix-php-code-style-issues.yml?branch=1.x&label=code%20style&style=flat-square)](https://github.com/mateuslecchi/filament-rich-editor-extender/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3A1.x)
[![Total Downloads](https://img.shields.io/packagist/dt/mateuslecchi/filament-rich-editor-extender.svg?style=flat-square)](https://packagist.org/packages/mateuslecchi/filament-rich-editor-extender)

Extra goodies for the Filament Forms RichEditor (v5). This package ships small, focused extensions that plug straight into your existing editor.

### Currently included:
- YouTube embeds.
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

Embeds use the privacy-enhanced `youtube-nocookie.com` domain by default.

#### Saving

Nothing special is required. Filament stores `RichEditor` content as a structured JSON document, so the YouTube node is persisted as data (the `<iframe>` is only generated at render time). Make sure the model column can hold that document — use a `json`/`longText` column and cast the attribute to `array`:

```php
protected function casts(): array
{
    return ['content' => 'array'];
}
```

#### Displaying

When you render stored content, the renderer needs to know about the plugin so it can turn the YouTube node back into an `<iframe>`. Filament's global editor configuration is not inherited by `RichContentRenderer`, so register the plugin on the content explicitly.

The idiomatic way is via the model, using Filament's `HasRichContent`:

```php
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;

class Post extends Model implements HasRichContent
{
    use InteractsWithRichContent;

    protected function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->plugins([YoutubePlugin::make()]);
    }
}
```

```blade
{!! $post->renderRichContent('content') !!}
```

Or render directly:

```php
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;

RichContentRenderer::make($post->content)
    ->plugins([YoutubePlugin::make()])
    ->toHtml();
```

#### Sanitization

Filament renders rich content through `Str::sanitizeHtml()`, whose sanitizer strips `<iframe>` by default — which would remove the embed. To prevent that, this package extends Filament's **application-wide** sanitizer config to allow a YouTube `<iframe>`, restricting its `src` to YouTube hosts (any other host's iframe `src` is dropped). Other media is unaffected.

If you'd rather control this, publish the config and tweak it:

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
```

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
