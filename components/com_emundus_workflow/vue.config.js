const path = require('path');

module.exports = {
    lintOnSave: false,
    outputDir: path.resolve(__dirname, '../../media/com_emundus_workflow'),
    assetsDir: '../com_emundus_workflow',

    css: {
        modules: false,
        extract: {
            filename: '[name]_workflow.css',
            chunkFilename: '[name]_workflow.css',
        },
    },
    configureWebpack: {
        output: {
            filename: '[name]_workflow.js',
            chunkFilename: '[name]_workflow.js',
        },
    },
};
