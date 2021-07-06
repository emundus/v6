const path = require('path');

module.exports = {
    lintOnSave: false,
    outputDir: path.resolve(__dirname, '../../media/mod_emundus_messenger_notifications'),
    assetsDir: '../mod_emundus_messenger_notifications',

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
