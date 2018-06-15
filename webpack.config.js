const webpack = require('webpack');
const path = require('path');

const inProduction = process.env.NODE_ENV === 'production';

/* Loaders */
const CustomLoader = require('custom-loader');

CustomLoader.loaders = {
    'vue-tslint-fixer': function (source) {
        if (this.resourcePath.endsWith('.vue')) {
            return source.trim() + '\n';
        }
        return source;
    },
};

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
                        'tslint-loader',
                        'custom-loader?name=vue-tslint-fixer',
                    ],
                },

                {
                    test: /\.vue$/,
                    use: 'vue-loader',
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
                                    path: path.resolve(__dirname, 'resources/assets/styles'),
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
            extensions: ['*', '.js', '.ts'],
            alias: {
                '@': path.join(__dirname, 'resources/vue'),
                '@js': path.join(__dirname, 'resources/assets/js'),
                'vue$': 'vue/dist/vue.esm.js',
            },
        },

    };
}

module.exports = [
    {
        entry: {
            app: [
                'babel-polyfill',
                './resources/vue/entry-client.ts',
                './resources/vue/app.scss',
            ],
            auth: [
                './resources/assets/styles/auth.scss',
            ],
            store: [
                'babel-polyfill',
                './resources/assets/js/store.ts',
                './resources/assets/styles/store.scss',
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
            render: [
                'babel-polyfill',
                './resources/vue/entry-server.ts',
            ],
        },
        output: {
            path: path.resolve(__dirname, 'scripts/vue-ssr'),
        },
        target: 'node',
        ...config(['scripts/vue-ssr']),
    },
];
