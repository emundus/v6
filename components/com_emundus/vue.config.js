const path = require('path');

module.exports = {
  lintOnSave: false,
  outputDir: path.resolve(__dirname, '../../media/com_emundus_vue'),
  assetsDir: '../com_emundus_vue',
  css: {
    extract: {
      filename: '[name]_emundus.css',
      chunkFilename: '[name]_emundus.css',
    },
  },
  configureWebpack: {
    output: {
      filename: '[name]_emundus.js',
      chunkFilename: '[name]_emundus.js',
    },
  },
};
