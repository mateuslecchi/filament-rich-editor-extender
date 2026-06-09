import * as esbuild from 'esbuild'

async function compile(options) {
    const context = await esbuild.context(options)

    await context.rebuild()
    await context.dispose()
}

const defaultOptions = {
    define: {
        'process.env.NODE_ENV': `'production'`,
    },
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: false,
    sourcesContent: false,
    treeShaking: true,
    target: ['es2020'],
    minify: true,
}

compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/filament/filament-rich-editor-extender/Youtube.js'],
    outfile: './resources/js/dist/filament/filament-rich-editor-extender/Youtube.js',
})

compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/filament/filament-rich-editor-extender/Twitch.js'],
    outfile: './resources/js/dist/filament/filament-rich-editor-extender/Twitch.js',
})

compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/filament/filament-rich-editor-extender/X.js'],
    outfile: './resources/js/dist/filament/filament-rich-editor-extender/X.js',
})

compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/filament/filament-rich-editor-extender/XEmbed.js'],
    outfile: './resources/js/dist/filament/filament-rich-editor-extender/XEmbed.js',
})
