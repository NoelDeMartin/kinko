const webpack = require('webpack');
const path = require('path');

const inProduction = process.env.NODE_ENV === 'production';

/* Plugins */
const ClearPlugin = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

/* Config */

// TODO extract vendors
// TODO review hot loading
// TODO implement cache busting
// TODO implement code splitting (js & css)

function config(cleanDirs) {
    return {

        module: {

            rules: [

                {
                    test: /\.js$/,
                    use: ['babel-loader', 'eslint-loader'],
                    exclude: file => (
                        /node_modules/.test(file) &&
                        !/\.vue\.js/.test(file)
                    ),
                },

                {
                    test: /\.ts$/,
                    use: [
                        'babel-loader',
                        {
                            loader: 'ts-loader',
                            options: {
                                appendTsSuffixTo: [/\.vue$/],
                            },
                        },
                    ],
                },

                {
                    test: /\.vue$/,
                    use: [
                        {
                            loader: 'vue-loader',
                            options: {
                                compilerOptions: {
                                    preserveWhitespace: false,
                                },
                            },
                        },
                    ],
                },

                {
                    test: /\.scss$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        'css-loader',
                        {
                            loader: 'postcss-loader',
                            options: {
                                config: {
                                    path: path.resolve(__dirname, 'resources/styles'),
                                },
                            },
                        },
                        'sass-loader',
                    ],
                },

            ],
        },

        plugins: [
            new ClearPlugin(
                cleanDirs,
                {
                    dist: __dirname,
                    verbose: true,
                    dry: false,
                }
            ),
            new VueLoaderPlugin(),
            new MiniCssExtractPlugin({ filename: 'css/[name].css' }),
            new webpack.LoaderOptionsPlugin({ minimize: inProduction }),
        ],

        resolve: {
            extensions: ['*', '.js', '.ts', '.vue'],
            alias: {
                '@': path.join(__dirname, 'resources/js'),
                'vue$': 'vue/dist/vue.esm.js',
            },
        },

    };
}

module.exports = [
    {
        entry: {
            app: [
                './resources/js/entry-client.ts',
                './resources/styles/main.scss',
            ],
            web: [
                './resources/js/web.ts',
                './resources/styles/main.scss',
            ],
        },
        output: {
            path: path.resolve(__dirname, 'public'),
            filename: 'js/[name].js',
        },
        ...config(['public/js', 'public/css']),
    },
    {
        entry: {
            render: './resources/js/entry-server.ts',
        },
        output: {
            path: path.resolve(__dirname, 'scripts/ssr'),
        },
        target: 'node',
        ...config(['scripts/ssr']),
    },
];
