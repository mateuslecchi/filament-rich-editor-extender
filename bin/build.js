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

// Add an entry per JS extension as they are created, e.g.:
//
// compile({
//     ...defaultOptions,
//     entryPoints: ['./resources/js/filament/filament-rich-editor-extender/YourExtension.js'],
//     outfile: './resources/js/dist/filament/filament-rich-editor-extender/YourExtension.js',
// })
