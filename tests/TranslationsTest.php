<?php

it('registers the package translations namespace', function () {
    app()->setLocale('en');

    expect(trans('filament-rich-editor-extender::youtube.action.label'))
        ->toBe('Insert YouTube video');
});

it('resolves translations for other locales', function () {
    app()->setLocale('pt_BR');

    expect(trans('filament-rich-editor-extender::youtube.action.label'))
        ->toBe('Inserir vídeo do YouTube');
});
