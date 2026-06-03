<?php

namespace MateusLecchi\FilamentRichEditorExtender;

use Closure;
use Filament\Forms\Components\RichEditor;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;
use MateusLecchi\FilamentRichEditorExtender\Sanitizers\YoutubeIframeAttributeSanitizer;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class FilamentRichEditorExtenderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-rich-editor-extender')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('mateuslecchi/filament-rich-editor-extender');
            });
    }

    public function bootingPackage(): void
    {
        FilamentAsset::register([
            Js::make('filament-rich-editor-extender/youtube', __DIR__.'/../resources/js/dist/filament/filament-rich-editor-extender/Youtube.js')->loadedOnRequest(),
        ]);

        RichEditor::configureUsing(function (RichEditor $richEditor): void {
            $richEditor->plugins([
                YoutubePlugin::make(),
            ]);
        });

        static::registerStorageMacro();

        $this->allowYoutubeEmbedsInSanitizedHtml();
    }

    /**
     * Opt-in, per-field storage mode helper. Lets a RichEditor field follow the
     * package's configured default (`storage`) without forcing any mode globally:
     *
     *   RichEditor::make('content')->youtubeStorage()         // uses config default
     *   RichEditor::make('content')->youtubeStorage('json')   // force JSON
     *   RichEditor::make('content')->youtubeStorage('html')   // force HTML
     *
     * 'html' is Filament's native behavior, so we leave the field untouched (this
     * also preserves any model-level `HasRichContent->json()` registration).
     */
    protected static function registerStorageMacro(): void
    {
        // Defined in a static context so the closure captures no `$this`; binding the
        // scope to RichEditor lets `$this` resolve to the field (the macro is rebound
        // to the instance by Filament's Macroable at call time).
        RichEditor::macro('youtubeStorage', Closure::bind(
            function (?string $mode = null): RichEditor {
                $mode ??= config('filament-rich-editor-extender.storage', 'html');

                return $mode === 'json' ? $this->json() : $this;
            },
            null,
            RichEditor::class,
        ));
    }

    /**
     * Filament renders rich content through `Str::sanitizeHtml()`, whose Symfony
     * `HtmlSanitizerConfig` only allows "safe" elements — and `<iframe>` is not one
     * of them, so YouTube embeds get stripped on display. When enabled, we extend the
     * bound config to allow the YouTube `<iframe>` (with the `src` restricted to the
     * configured hosts by a dedicated attribute sanitizer) and the `data-youtube-video`
     * wrapper attribute. This affects the application-wide sanitizer; see the config file.
     */
    protected function allowYoutubeEmbedsInSanitizedHtml(): void
    {
        if (! config('filament-rich-editor-extender.youtube.sanitizer.enabled', true)) {
            return;
        }

        $allowedHosts = config('filament-rich-editor-extender.youtube.sanitizer.allowed_hosts', [
            'youtube-nocookie.com',
            'youtube.com',
        ]);

        $this->app->extend(
            HtmlSanitizerConfig::class,
            fn (HtmlSanitizerConfig $config): HtmlSanitizerConfig => $config
                ->allowElement('iframe', [
                    'src',
                    'width',
                    'height',
                    'allow',
                    'allowfullscreen',
                    'frameborder',
                    'referrerpolicy',
                    'title',
                    'loading',
                ])
                ->allowAttribute('data-youtube-video', allowedElements: '*')
                ->withAttributeSanitizer(new YoutubeIframeAttributeSanitizer($allowedHosts)),
        );
    }
}
