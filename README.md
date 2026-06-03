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

Embeds use the privacy-enhanced `youtube-nocookie.com` domain by default. Stored content is rendered to a responsive `<iframe>` automatically when you display it (e.g. via `RichContentRenderer`), so no extra work is needed on the front end.

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
