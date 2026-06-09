## Filament Rich Editor Extender

This package adds embed tools to the **Filament Forms v5 `RichEditor`**: YouTube videos, Twitch (videos, clips and channels) and X (Twitter) posts. Each tool adds a toolbar button that opens a modal asking for a URL and inserts the content as an embed.

- The plugins register themselves automatically on every `RichEditor` (via `RichEditor::configureUsing`). You only need to add the toolbar buttons.
- Tool/button names: `youtube`, `twitch`, `x`.
- Plugin classes (namespace `MateusLecchi\FilamentRichEditorExtender\Plugins`): `YoutubePlugin`, `TwitchPlugin`, `XPlugin`.
- Embeds render as a `<div data-{platform}-...><iframe ...></iframe></div>` both in the editor and server-side, so they survive HTML and JSON storage.

### Enabling the embed buttons

Add the tool names to the field's `toolbarButtons()`. No other wiring is needed.

@verbatim
<code-snippet name="Add embed buttons to a RichEditor" lang="php">
use Filament\Forms\Components\RichEditor;

RichEditor::make('content')
    ->toolbarButtons([
        'bold', 'italic', 'link',
        'youtube', 'twitch', 'x',
    ]);
</code-snippet>
@endverbatim

### Storage modes (HTML or JSON)

Every embed works in both Filament storage modes. The `->youtubeStorage()` macro toggles the **field's** mode (it is not YouTube-specific):

- `->youtubeStorage()` — follows the package config default (`config('filament-rich-editor-extender.storage')`, `'html'` out of the box).
- `->youtubeStorage('json')` — force JSON (equivalent to Filament's `->json()`). Requires a `json` column or an `array` cast on the attribute.
- HTML mode needs a plain `text`/`longText` column with **no** cast.

@verbatim
<code-snippet name="Per-field storage mode" lang="php">
use Filament\Forms\Components\RichEditor;

RichEditor::make('content')
    ->youtubeStorage('json') // or ->youtubeStorage() for the configured default
    ->toolbarButtons(['youtube', 'twitch', 'x']);
</code-snippet>
@endverbatim

### Displaying stored content

`RichContentRenderer` does NOT inherit the editor's global plugin configuration. When rendering stored content you MUST register the plugins whose embeds the content may contain, or the nodes will not render as iframes.

@verbatim
<code-snippet name="Register plugins on the model (HasRichContent)" lang="php">
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use MateusLecchi\FilamentRichEditorExtender\Plugins\TwitchPlugin;
use MateusLecchi\FilamentRichEditorExtender\Plugins\XPlugin;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;

class Post extends Model implements HasRichContent
{
    use InteractsWithRichContent;

    protected function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->plugins([
                YoutubePlugin::make(),
                TwitchPlugin::make(),
                XPlugin::make(),
            ]);
    }
}
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Render stored content directly" lang="php">
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use MateusLecchi\FilamentRichEditorExtender\Plugins\YoutubePlugin;

RichContentRenderer::make($post->content)
    ->plugins([YoutubePlugin::make()])
    ->toHtml();
</code-snippet>
@endverbatim

@verbatim
In Blade, output rendered model content with `{!! $post->renderRichContent('content') !!}`.
@endverbatim

### Twitch requires a `parent` domain

Twitch embeds only load when their `parent` query parameter matches the domain serving them. The live editor preview derives this from the current host automatically, but **server-side rendered** embeds use the config value. Set it to the domain(s) where rendered content is displayed (no scheme, no path), otherwise the Twitch player stays blank.

@verbatim
<code-snippet name="Configure Twitch parent domain" lang="php">
// config/filament-rich-editor-extender.php
'twitch' => [
    'parent' => ['example.com', 'www.example.com'],
],
</code-snippet>
@endverbatim

### Resizing X (Twitter) embeds on the front-end

X post iframes have a fixed height and would crop tall posts. The **editor resizes them automatically**. For **front-end pages** that display stored X embeds, publish and include the bundled resize script, otherwise posts may appear cut off:

@verbatim
<code-snippet name="Enable X embed resizing on the front-end" lang="blade">
{{-- 1. php artisan vendor:publish --tag=filament-rich-editor-extender-assets --}}
{{-- 2. Include once on pages that render X embeds (e.g. your layout): --}}
<script src="{{ asset('vendor/filament-rich-editor-extender/XEmbed.js') }}" async></script>
</code-snippet>
@endverbatim

### Sanitization

Filament's `Str::sanitizeHtml()` strips `<iframe>` by default. This package extends the application-wide sanitizer config to allow the embed iframe, restricting its `src` to a merged host allowlist (YouTube, Twitch, X). Any other host's iframe `src` is dropped. Each platform's `sanitizer.enabled` and `sanitizer.allowed_hosts` are configurable in `config/filament-rich-editor-extender.php`. X (`platform.twitter.com`) needs no extra script or `parent`.

### Conventions

- Publish config: `php artisan vendor:publish --tag=filament-rich-editor-extender-config`.
- Publish translations (`en`, `pt_BR`): `php artisan vendor:publish --tag=filament-rich-editor-extender-translations`.
- When switching a field between HTML and JSON storage, migrate the column/cast AND existing rows — the package does not convert stored data.
