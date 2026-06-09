<?php

namespace MateusLecchi\FilamentRichEditorExtender\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;

class XAction
{
    public static function make(): Action
    {
        return Action::make('x')
            ->label(__('filament-rich-editor-extender::x.action.label'))
            ->modalHeading(__('filament-rich-editor-extender::x.action.modal.heading'))
            ->modalWidth(Width::Large)
            ->schema([
                TextInput::make('url')
                    ->label(__('filament-rich-editor-extender::x.action.modal.form.url.label'))
                    ->url()
                    ->required()
                    ->inputMode('url')
                    ->placeholder('https://x.com/.../status/...'),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component): void {
                $component->runCommands(
                    [
                        EditorCommand::make(
                            'setXPost',
                            arguments: [[
                                'src' => $data['url'],
                            ]],
                        ),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );
            });
    }
}
