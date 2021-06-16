const path = require('path');

module.exports = {
    lintOnSave: false,
    outputDir: path.resolve(__dirname, '../../media/com_emundus_messenger'),
    assetsDir: '../com_emundus_messenger',

    css: {
        modules: false,
        extract: {
            filename: '[name]_messenger.css',
            chunkFilename: '[name]_messenger.css',
        },
    },
    configureWebpack: {
        output: {
            filename: '[name]_messenger.js',
            chunkFilename: '[name]_messenger.js',
        },
    },
};
