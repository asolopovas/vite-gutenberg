import fs from 'fs/promises'
import {defineConfig} from 'vite'
import gutenberg from '../vite-gutenberg-plugin/dist/index'
import react from '@vitejs/plugin-react'
import svgr from '@honkhonk/vite-plugin-svgr'
import {log} from 'console'

const external = {
    'jquery': 'window.jQuery',
    'lodash-es': 'window.lodash',
    'lodash': 'window.lodash',
    'moment': 'window.moment',
    'react-dom': 'window.ReactDOM',
    'react': 'window.React',
}
const nsExclude = ['icons', 'interface']
const ns = '@wordpress/'
const wordpressMatch = new RegExp(`^${ns}(?!(${nsExclude.join('|')})).*$`) // /^@wordpress\/(?!(icons|interface)).*$/
const keyPath = `./ssl`
const hmrHost = 'localhost'

export default defineConfig({
    plugins: [
        svgr(),
        gutenberg({
            input: [
                './src/blocks/test/index.jsx',
            ],
        }),
        react({
            jsxRuntime: 'classic',
            jsxImportSource: '@wordpress/element',
        }),
    ],
    esbuild: {
        loader: "jsx",
        include: /src\/.*\.jsx?$/,
        exclude: [],
    },
    optimizeDeps: {
        esbuildOptions: {
            plugins: [
                {
                    name: "load-js-files-as-jsx",
                    setup(build) {
                        build.onLoad({filter: /src\/.*\.js$/}, async (args) => ({
                            loader: "jsx",
                            contents: await fs.readFile(args.path, "utf8"),
                        }))
                    },
                },
            ],
        },
    },
})
