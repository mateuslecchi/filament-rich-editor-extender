<?php

namespace MateusLecchi\FilamentRichEditorExtender;

use Closure;
use Filament\Forms\Components\RichEditor;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use MateusLecchi\FilamentRichEditorExtender\Plugins\TwitchPlugin;
use MateusLecchi\FilamentRichEditorExtender\Plugins\XPlugin;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;
use MateusLecchi\FilamentRichEditorExtender\Sanitizers\IframeSrcHostSanitizer;
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
            Js::make('filament-rich-editor-extender/twitch', __DIR__.'/../resources/js/dist/filament/filament-rich-editor-extender/Twitch.js')->loadedOnRequest(),
            Js::make('filament-rich-editor-extender/x', __DIR__.'/../resources/js/dist/filament/filament-rich-editor-extender/X.js')->loadedOnRequest(),
        ]);

        RichEditor::configureUsing(function (RichEditor $richEditor): void {
            $richEditor->plugins([
                YoutubePlugin::make(),
                TwitchPlugin::make(),
                XPlugin::make(),
            ]);
        });

        static::registerStorageMacro();

        $this->allowMediaEmbedsInSanitizedHtml();
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
     * of them, so media embeds (YouTube, Twitch, …) get stripped on display. When
     * enabled, we extend the bound config to allow the embed `<iframe>` (with the
     * `src` restricted to the configured hosts by a single attribute sanitizer that
     * carries the union of every enabled platform's hosts) and each platform's
     * wrapper attribute. This affects the application-wide sanitizer; see the config.
     */
    protected function allowMediaEmbedsInSanitizedHtml(): void
    {
        // [config prefix, wrapper data attribute, default allowed hosts]
        $platforms = [
            ['youtube', 'data-youtube-video', ['youtube-nocookie.com', 'youtube.com']],
            ['twitch', 'data-twitch-video', ['player.twitch.tv', 'clips.twitch.tv']],
            ['x', 'data-x-post', ['platform.twitter.com']],
        ];

        $allowedHosts = [];
        $wrapperAttributes = [];

        foreach ($platforms as [$prefix, $wrapperAttribute, $defaultHosts]) {
            if (! config("filament-rich-editor-extender.{$prefix}.sanitizer.enabled", true)) {
                continue;
            }

            $allowedHosts = array_merge(
                $allowedHosts,
                config("filament-rich-editor-extender.{$prefix}.sanitizer.allowed_hosts", $defaultHosts),
            );

            $wrapperAttributes[] = $wrapperAttribute;
        }

        if ($allowedHosts === []) {
            return;
        }

        $this->app->extend(
            HtmlSanitizerConfig::class,
            function (HtmlSanitizerConfig $config) use ($allowedHosts, $wrapperAttributes): HtmlSanitizerConfig {
                $config = $config
                    ->allowElement('iframe', [
                        'src',
                        'width',
                        'height',
                        'allow',
                        'allowfullscreen',
                        'frameborder',
                        'scrolling',
                        'referrerpolicy',
                        'title',
                        'loading',
                    ])
                    ->withAttributeSanitizer(new IframeSrcHostSanitizer(array_values(array_unique($allowedHosts))));

                foreach ($wrapperAttributes as $wrapperAttribute) {
                    $config = $config->allowAttribute($wrapperAttribute, allowedElements: '*');
                }

                return $config;
            },
        );
    }
}
