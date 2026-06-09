import Twitch from '@tiptap/extension-twitch'

// Twitch requires the embed's `parent` to match the domain it is served from.
// In the live editor that domain is wherever the admin panel runs, so we derive
// it from the current host. Server-side rendered embeds use the `twitch.parent`
// value from the package config instead (see config/filament-rich-editor-extender.php).
export default Twitch.configure({
    parent: [typeof window !== 'undefined' ? window.location.hostname : 'localhost'],
})
