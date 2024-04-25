module.exports = {
  setupFiles: ['<rootDir>/com_emundus/tests/setup/jest-setup-file.js'],
  preset: '@vue/cli-plugin-unit-jest/presets/no-babel',
  transformIgnorePatterns: ['/node_modules/(?!(vue-swatches|tinymce|vwave|@fortawesome))'],
  testTimeout: 5000,
  testEnvironment: 'jsdom',
  moduleNameMapper: {
    '^@/components/(.*)$': '<rootDir>/com_emundus/src/components/$1',
    '^@/views/(.*)$': '<rootDir>/com_emundus/src/views/$1',
    '^@/services/(.*)$': '<rootDir>/com_emundus/src/services/$1',
  }
};
