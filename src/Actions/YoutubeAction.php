<?php

namespace MateusLecchi\FilamentRichEditorExtender\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;

class YoutubeAction
{
    public static function make(): Action
    {
        return Action::make('youtube')
            ->label(__('filament-rich-editor-extender::youtube.action.label'))
            ->modalHeading(__('filament-rich-editor-extender::youtube.action.modal.heading'))
            ->modalWidth(Width::Large)
            ->schema([
                TextInput::make('url')
                    ->label(__('filament-rich-editor-extender::youtube.action.modal.form.url.label'))
                    ->url()
                    ->required()
                    ->inputMode('url')
                    ->placeholder('https://www.youtube.com/watch?v=...'),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component): void {
                $component->runCommands(
                    [
                        EditorCommand::make(
                            'setYoutubeVideo',
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
