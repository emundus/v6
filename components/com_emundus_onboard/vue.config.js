const path = require('path');

module.exports = {
  lintOnSave: false,
  outputDir: path.resolve(__dirname, '../../media/com_emundus_onboard'),
  assetsDir: '../com_emundus_onboard',

  css: {
    modules: false,
    extract: {
      filename: '[name]_onboard.css',
      chunkFilename: '[name]_onboard.css',
    },
  },
  configureWebpack: {
    output: {
      filename: '[name]_onboard.js',
      chunkFilename: '[name]_onboard.js',
    },
  },
};
