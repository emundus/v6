const path = require('path');

module.exports = {
    lintOnSave: false,
    outputDir: path.resolve(__dirname, '../../media/mod_emundus_evaluations'),
    assetsDir: '../mod_emundus_evaluations',

    css: {
        modules: false,
        extract: {
            filename: '[name].css',
            chunkFilename: '[name].css',
        },
    },
    configureWebpack: {
        output: {
            filename: '[name].js',
            chunkFilename: '[name].js',
        },
    },
};
