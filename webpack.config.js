const path = require('path');

module.exports = {
    entry: './assets/js/gutenberg.js',
    output: {
        path: path.resolve(__dirname, 'assets/js'),
        filename: 'gutenberg.build.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: 'babel-loader'
            }
        ]
    },
    externals: {
        '@wordpress/i18n': ['wp', 'i18n'],
        '@wordpress/plugins': ['wp', 'plugins'],
        '@wordpress/editor': ['wp', 'editor'],
        '@wordpress/components': ['wp', 'components'],
        '@wordpress/data': ['wp', 'data'],
        '@wordpress/element': ['wp', 'element']
    },
    resolve: {
        extensions: ['.js']
    },
    mode: 'development'
};
