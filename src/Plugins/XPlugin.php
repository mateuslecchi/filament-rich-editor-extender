<?php

namespace MateusLecchi\FilamentRichEditorExtender\Plugins;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Icons\Heroicon;
use MateusLecchi\FilamentRichEditorExtender\Actions\XAction;
use MateusLecchi\FilamentRichEditorExtender\Extensions\X;
use Tiptap\Core\Extension;

class XPlugin implements RichContentPlugin
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
            app(X::class),
        ];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('filament-rich-editor-extender/x'),
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('x')
                ->label(__('filament-rich-editor-extender::x.tool.label'))
                ->icon(Heroicon::Hashtag)
                ->action('x'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            XAction::make(),
        ];
    }
}
