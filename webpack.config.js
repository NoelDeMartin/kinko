const webpack = require('webpack');
const path = require('path');
const ClearPlugin = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

const inProduction = process.env.NODE_ENV === 'production';

// TODO extract vendors
// TODO review hot loading
// TODO implement cache busting
// TODO implement code splitting (js & css)

module.exports = {

    entry: {
        app: [
            './resources/vue/app.ts',
            './resources/styles/app.scss',
        ],
        auth: [
            './resources/styles/auth.scss',
        ],
    },

    output: {
        path: path.resolve(__dirname, 'public'),
        filename: 'js/[name].js',
    },

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
            [ 'public/js', 'public/css' ],
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
        },
    },

};
