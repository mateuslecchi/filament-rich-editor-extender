import { Node, mergeAttributes, nodePasteRule } from '@tiptap/core'
import { initXEmbedResizing } from './x-resize'

// Keep the live editor preview from cropping tall posts: resize embeds as the
// Tweet.html iframe reports its height. The MutationObserver inside picks up
// embeds inserted into the editor after load.
initXEmbedResizing()

// Matches a tweet/post URL on either twitter.com or x.com and captures its id.
export const X_REGEX = /^https?:\/\/(www\.)?(twitter\.com|x\.com)\/[^/]+\/status\/(\d+)(\S*)?$/
const X_REGEX_GLOBAL = /https?:\/\/(www\.)?(twitter\.com|x\.com)\/[^/]+\/status\/(\d+)/g

export function getTweetId(url) {
    if (!url) {
        return null
    }

    const match = url.match(/\/status\/(\d+)/) || url.match(/[?&]id=(\d+)/)

    return match ? match[1] : null
}

export function getEmbedUrlFromXUrl(url) {
    const id = getTweetId(url)

    return id ? `https://platform.twitter.com/embed/Tweet.html?id=${id}` : null
}

export default Node.create({
    name: 'x',

    group: 'block',

    atom: true,

    draggable: true,

    addOptions() {
        return {
            width: 550,
            height: 600,
            addPasteHandler: true,
            HTMLAttributes: {},
        }
    },

    addAttributes() {
        return {
            src: {
                default: null,
                parseHTML: (element) => element.querySelector('iframe')?.getAttribute('src') || null,
            },
            width: {
                default: this.options.width,
                parseHTML: (element) => element.querySelector('iframe')?.getAttribute('width') || this.options.width,
            },
            height: {
                default: this.options.height,
                parseHTML: (element) => element.querySelector('iframe')?.getAttribute('height') || this.options.height,
            },
        }
    },

    parseHTML() {
        return [{ tag: 'div[data-x-post]' }]
    },

    addCommands() {
        return {
            setXPost:
                (options) =>
                ({ commands }) =>
                    commands.insertContent({
                        type: this.name,
                        attrs: options,
                    }),
        }
    },

    addPasteRules() {
        if (!this.options.addPasteHandler) {
            return []
        }

        return [
            nodePasteRule({
                find: X_REGEX_GLOBAL,
                type: this.type,
                getAttributes: (match) => ({ src: match.input }),
            }),
        ]
    },

    renderHTML({ HTMLAttributes }) {
        const embedUrl = getEmbedUrlFromXUrl(HTMLAttributes.src)

        return [
            'div',
            { 'data-x-post': 'true' },
            [
                'iframe',
                mergeAttributes(this.options.HTMLAttributes, {
                    src: embedUrl,
                    width: HTMLAttributes.width || this.options.width,
                    height: HTMLAttributes.height || this.options.height,
                    frameborder: '0',
                    scrolling: 'no',
                    allowfullscreen: 'true',
                }),
            ],
        ]
    },
})
