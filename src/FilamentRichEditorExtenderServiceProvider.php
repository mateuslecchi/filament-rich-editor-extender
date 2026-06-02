<?php

namespace MateusLecchi\FilamentRichEditorExtender;

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
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command->askToStarRepoOnGitHub('mateuslecchi/filament-rich-editor-extender');
            });
    }

    public function bootingPackage(): void
    {
        // Register your extensions here as they are added, e.g.:
        //
        // FilamentAsset::register([
        //     Js::make('filament-rich-editor-extender/your-extension', __DIR__.'/../resources/js/dist/filament/filament-rich-editor-extender/YourExtension.js')->loadedOnRequest(),
        // ]);
        //
        // RichEditor::configureUsing(function (RichEditor $richEditor) {
        //     $richEditor->plugins([
        //         YourPlugin::make(),
        //     ]);
        // });
    }
}
