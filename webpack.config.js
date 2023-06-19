const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');

module.exports = {
    entry: './app/assets/js/app.js',
    output: {
        filename: 'app.js',
        pathinfo: true,
        path: path.resolve(__dirname, 'public/js'),
    },
    mode: 'production',
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'postcss-loader',
                ],
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '../css/app.css',
        }),

        new BrowserSyncPlugin({
            host: 'localhost',
            port: 3000,
            proxy: 'http://localhost:8000/',
            open: false,
            files: [
                {
                    match: [
                        '**/*.php',
                        '**/*.twig',
                        '**/*.html',
                        '**/*.css',
                        '**/*.js',
                    ],
                    fn: function(event, file) {
                        if (event === 'change') {
                            const bs = require('browser-sync').get('bs-webpack-plugin');
                            bs.reload();
                        }
                    }
                }
            ]
        })
    ],
    resolve: {
        fallback: {
        }

    }
};
