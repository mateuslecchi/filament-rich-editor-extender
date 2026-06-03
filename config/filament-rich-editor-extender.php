<?php

return [

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

];
