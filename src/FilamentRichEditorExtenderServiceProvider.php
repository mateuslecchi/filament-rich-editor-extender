<?php

namespace MateusLecchi\FilamentRichEditorExtender;

use Filament\Forms\Components\RichEditor;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
    }
}
