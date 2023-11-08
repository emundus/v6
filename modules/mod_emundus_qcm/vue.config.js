const path = require('path');

module.exports = {
    lintOnSave: false,
    outputDir: path.resolve('../../media/mod_emundus_qcm'),
    assetsDir: '../mod_emundus_qcm',

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
