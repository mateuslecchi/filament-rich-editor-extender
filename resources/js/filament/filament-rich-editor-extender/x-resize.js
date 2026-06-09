// Auto-resizes X (Twitter) `platform.twitter.com/embed/Tweet.html` iframes.
//
// A raw Tweet.html iframe has a fixed height, so taller posts (with images/quotes)
// get cropped. The embed, however, reports its rendered height to the parent window:
//   1. it answers a `{ element, query: 'height' }` message with `{ element, height }`;
//   2. it also proactively posts `twttr.embed` `twttr.private.resize` messages as its
//      media finishes loading.
// We handle both and re-ask a few times, since tweet media grows the frame after load.

const TWITTER_ORIGIN = 'https://platform.twitter.com'
const IFRAME_SELECTOR = 'iframe[src*="platform.twitter.com/embed/Tweet.html"]'

let initialized = false

function requestHeight(iframe) {
    if (!iframe.id) {
        iframe.id = 'x-embed-' + Math.random().toString(36).slice(2, 11)
    }

    try {
        iframe.contentWindow?.postMessage({ element: iframe.id, query: 'height' }, TWITTER_ORIGIN)
    } catch (e) {
        // Cross-origin window not ready yet; the proactive resize message still covers us.
    }
}

function bindIframe(iframe) {
    if (iframe.dataset.xResizeBound) {
        return
    }

    iframe.dataset.xResizeBound = '1'

    const ask = () => requestHeight(iframe)

    iframe.addEventListener('load', () => {
        ask()
        // Tweet media loads after the frame, growing its height — re-ask a few times.
        ;[400, 1200, 2500].forEach((delay) => setTimeout(ask, delay))
    })

    if (iframe.contentWindow) {
        ask()
    }
}

function scan(root = document) {
    root.querySelectorAll?.(IFRAME_SELECTOR).forEach(bindIframe)
}

function applyHeight(iframe, height) {
    const px = parseInt(height, 10)

    if (iframe && px > 0) {
        iframe.style.height = px + 'px'
    }
}

export function initXEmbedResizing() {
    if (initialized || typeof window === 'undefined') {
        return
    }

    initialized = true

    window.addEventListener('message', (event) => {
        if (event.origin !== TWITTER_ORIGIN || !event.data) {
            return
        }

        const data = event.data

        // Query/response protocol: { element, height }.
        if (data.element && data.height) {
            applyHeight(document.getElementById(data.element), data.height)

            return
        }

        // Proactive protocol: { 'twttr.embed': { method, params: [{ height }] } }.
        const embed = data['twttr.embed']

        if (embed?.method === 'twttr.private.resize' && Array.isArray(embed.params)) {
            const params = embed.params[0] || {}
            const height = params.height ?? params.data?.height

            if (!height) {
                return
            }

            document.querySelectorAll(IFRAME_SELECTOR).forEach((iframe) => {
                if (iframe.contentWindow === event.source) {
                    applyHeight(iframe, height)
                }
            })
        }
    })

    const start = () => {
        scan()

        new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                mutation.addedNodes?.forEach((node) => {
                    if (node.nodeType !== 1) {
                        return
                    }

                    if (node.matches?.(IFRAME_SELECTOR)) {
                        bindIframe(node)
                    }

                    scan(node)
                })
            }
        }).observe(document.body, { childList: true, subtree: true })
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start)
    } else {
        start()
    }
}
