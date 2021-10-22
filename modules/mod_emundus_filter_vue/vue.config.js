const path = require('path');

module.exports = {
  lintOnSave: false,
  outputDir: path.resolve(__dirname, '../../media/mod_emundus_filter_vue'),
  assetsDir: '../mod_emundus_filter_vue',

  css: {
    requireModuleExtension: false,
    extract: {
      filename: '[name]_filter.css',
      chunkFilename: '[name]_filter.css',
    },
  },
  configureWebpack: {
    output: {
      filename: '[name]_filter.js',
      chunkFilename: '[name]_filter.js',
    },
  },
};
