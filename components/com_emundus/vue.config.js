const path = require('path');

module.exports = {
  lintOnSave: false,
  outputDir: path.resolve(__dirname, '../../media/com_emundus_vue'),
  assetsDir: '../com_emundus_vue',
  css: {
    loaderOptions: {
      sass: {
        additionalData: `
          @import "@/assets/css/main.scss";
          @import "@/assets/css/floating-vue.scss";
        `
      }
    },
    extract: {
      filename: '[name]_emundus.css',
      chunkFilename: '[name]_emundus.css',
    },
  },
  configureWebpack: {
    resolve: {
      alias: {
        '/images/emundus/arrow.svg': path.join(__dirname, '../../images/emundus/arrow.svg')
      }
    },
    output: {
      filename: '[name]_emundus.js',
      chunkFilename: '[name]_emundus.js',
    },
  },
};
