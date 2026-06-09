<?php

namespace MateusLecchi\FilamentRichEditorExtender\Plugins;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Icons\Heroicon;
use MateusLecchi\FilamentRichEditorExtender\Actions\TwitchAction;
use MateusLecchi\FilamentRichEditorExtender\Extensions\Twitch;
use Tiptap\Core\Extension;

class TwitchPlugin implements RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return array<Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [
            app(Twitch::class, ['options' => [
                'parent' => config('filament-rich-editor-extender.twitch.parent', ['localhost']),
            ]]),
        ];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('filament-rich-editor-extender/twitch'),
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('twitch')
                ->label(__('filament-rich-editor-extender::twitch.tool.label'))
                ->icon(Heroicon::VideoCamera)
                ->action('twitch'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            TwitchAction::make(),
        ];
    }
}
