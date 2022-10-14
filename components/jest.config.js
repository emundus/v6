module.exports = {
  preset: '@vue/cli-plugin-unit-jest/presets/no-babel',
  transformIgnorePatterns: ["/node_modules/(?!(vue-swatches|tinymce|vwave))"],
  testTimeout: 5000,
  moduleNameMapper: {
    '^@/components/(.*)$': '<rootDir>/com_emundus/src/components/$1'
  }
};
