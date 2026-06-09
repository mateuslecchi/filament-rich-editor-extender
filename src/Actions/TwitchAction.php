<?php

namespace MateusLecchi\FilamentRichEditorExtender\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;

class TwitchAction
{
    public static function make(): Action
    {
        return Action::make('twitch')
            ->label(__('filament-rich-editor-extender::twitch.action.label'))
            ->modalHeading(__('filament-rich-editor-extender::twitch.action.modal.heading'))
            ->modalWidth(Width::Large)
            ->schema([
                TextInput::make('url')
                    ->label(__('filament-rich-editor-extender::twitch.action.modal.form.url.label'))
                    ->url()
                    ->required()
                    ->inputMode('url')
                    ->placeholder('https://www.twitch.tv/videos/...'),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component): void {
                $component->runCommands(
                    [
                        EditorCommand::make(
                            'setTwitchVideo',
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
