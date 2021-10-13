const path = require('path');

module.exports = {
  lintOnSave: false,
  outputDir: path.resolve(__dirname, '../../media/com_emundus'),
  assetsDir: '../com_emundus',

  css: {
    requireModuleExtension: false,
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
