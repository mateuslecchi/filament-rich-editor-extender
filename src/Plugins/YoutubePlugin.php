<?php

namespace MateusLecchi\FilamentRichEditorExtender\Plugins;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Icons\Heroicon;
use MateusLecchi\FilamentRichEditorExtender\Actions\YoutubeAction;
use MateusLecchi\FilamentRichEditorExtender\Extensions\Youtube;
use Tiptap\Core\Extension;

class YoutubePlugin implements RichContentPlugin
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
            app(Youtube::class),
        ];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('filament-rich-editor-extender/youtube'),
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('youtube')
                ->label(__('filament-rich-editor-extender::youtube.tool.label'))
                ->icon(Heroicon::PlayCircle)
                ->action('youtube'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            YoutubeAction::make(),
        ];
    }
}
