// Standalone front-end script: include it on pages that display stored X (Twitter)
// embeds so the post iframes resize to their content instead of being cropped.
// The editor bundle initializes the same logic automatically.
import { initXEmbedResizing } from './x-resize'

initXEmbedResizing()
