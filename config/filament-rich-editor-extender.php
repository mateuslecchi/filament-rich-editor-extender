<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage mode
    |--------------------------------------------------------------------------
    |
    | Default storage mode applied by the `->youtubeStorage()` RichEditor helper
    | when called without an argument:
    |
    |   'html' — Filament's native behavior. Content is stored as HTML. Use a
    |            text/longText column WITHOUT an `array` cast.
    |   'json' — Content is stored as a structured JSON document (more robust for
    |            custom nodes). Requires a `json` column or an `array` cast.
    |
    | This is only applied to fields that call `->youtubeStorage()`. It never
    | changes the storage mode of other RichEditors globally. When switching an
    | existing field between modes, migrate the column/cast and existing rows.
    |
    */

    'storage' => 'html',

    'youtube' => [

        /*
        |----------------------------------------------------------------------
        | YouTube embed sanitization
        |----------------------------------------------------------------------
        |
        | Filament renders rich content through `Str::sanitizeHtml()`, whose
        | Symfony HtmlSanitizer does not allow `<iframe>` by default, so YouTube
        | embeds get stripped on display. When `enabled` is true, this package
        | extends the *application-wide* sanitizer config to allow a YouTube
        | `<iframe>` (with its `src` restricted to the hosts below). Disable it
        | if you prefer to sanitize/render YouTube content yourself.
        |
        | Note: this affects every `Str::sanitizeHtml()` call in the app, since
        | Filament binds a single shared sanitizer config. The change is narrow:
        | only an iframe whose `src` host matches `allowed_hosts` keeps its src.
        |
        */

        'sanitizer' => [

            'enabled' => true,

            // Hosts (and their subdomains) whose iframe `src` is allowed to
            // survive sanitization. Any other host's iframe src is dropped.
            'allowed_hosts' => [
                'youtube-nocookie.com',
                'youtube.com',
            ],

        ],

    ],

    'twitch' => [

        /*
        |----------------------------------------------------------------------
        | Twitch embed parent domain(s)
        |----------------------------------------------------------------------
        |
        | Twitch requires every embed to declare the domain(s) it is served
        | from via a `parent` query parameter, otherwise the player refuses to
        | load. Set this to the domain(s) where your *rendered* content is
        | displayed (no scheme, no path), e.g. ['example.com', 'www.example.com'].
        | 'localhost' only works during local development.
        |
        | The live editor preview derives its own parent from the current host
        | automatically; this value only applies to server-side rendered embeds.
        |
        */

        'parent' => ['localhost'],

        /*
        |----------------------------------------------------------------------
        | Twitch embed sanitization
        |----------------------------------------------------------------------
        |
        | Like YouTube, Twitch embeds render as an `<iframe>`. When `enabled`
        | is true, the package keeps an iframe whose `src` host matches one of
        | the hosts below through the application-wide sanitizer.
        |
        */

        'sanitizer' => [

            'enabled' => true,

            'allowed_hosts' => [
                'player.twitch.tv',
                'clips.twitch.tv',
            ],

        ],

    ],

    'x' => [

        /*
        |----------------------------------------------------------------------
        | X (Twitter) embed sanitization
        |----------------------------------------------------------------------
        |
        | X posts are embedded as an `<iframe>` pointing at X's embed endpoint.
        | When `enabled` is true, the package keeps an iframe whose `src` host
        | matches one of the hosts below through the application-wide sanitizer.
        |
        */

        'sanitizer' => [

            'enabled' => true,

            'allowed_hosts' => [
                'platform.twitter.com',
            ],

        ],

    ],

];
