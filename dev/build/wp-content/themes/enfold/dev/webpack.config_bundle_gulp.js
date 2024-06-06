/**
 * To bundle gulpfile.js and use it without node_modules
 * =====================================================
 *
 * ATTENTION: Whenever you make changes to any of the gulpfile*.*
 * =========  you must follow the steps below to rebuild the bundled file
 *
 * - Rename package_bundle_gulp.json to package.json
 *
 * > npm install
 * > npm run bundle
 *
 * On Windows Systems:
 * ===================
 *
 * To minify all:
 * > set ENFOLD_MINIFY_TASK=minall&&node minifyEnfold.js
 *
 * To delete all:
 * > set ENFOLD_MINIFY_TASK=delall&&node minifyEnfold.js
 *
 * @since 5.2
 */
const path = require('path');
const nodeExternals = require('webpack-node-externals');

module.exports = {
    entry: path.resolve(__dirname, 'gulpfile.js'),
    output: {
        filename: 'minifyEnfold.js',
        path: path.resolve(__dirname, '../dev')
    },
    target: 'node',
    resolve: {
        fallback: {
            assert: false,
            buffer: false,
            console: false,
            constants: false,
            crypto: false,
            domain: false,
            events: false,
            http: false,
            https: false,
            os: false,
            path: false,
            punycode: false,
            process: false,
            querystring: false,
            stream: false,
            string_decoder: false,
            sys: false,
            timers: false,
            tty: false,
            url: false,
            vm: false,
            zlib: false,
            fs: false
        }
    },
    mode: 'production',
    module: {
        rules: [
            {
                test: /\.m?js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env']
                    }
                }
            }
        ]
    }
};
