module.exports = {
  setupFiles: ['<rootDir>/com_emundus/tests/setup/jest-setup-file.js'],
  preset: '@vue/cli-plugin-unit-jest/presets/no-babel',
  transformIgnorePatterns: ['/node_modules/(?!(vue-swatches|tinymce|vwave|@fortawesome))'],
  testTimeout: 5000,
  testEnvironment: 'jsdom',
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/com_emundus/src/$1',
  }
};
