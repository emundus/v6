/** @type {import('tailwindcss').Config} */
const plugin = require('tailwindcss/plugin');

module.exports = {
    content: [
        "./templates/g5_helium/html/**/*.{html,js,php}",
        "./modules/**/src/*.{html,js,php}",
        "./modules/**/tmpl/*.{html,js,php}",
        "./plugins/fabrik_element/**/*.{html,js,php}",
        "./components/com_emundus/src/**/*.{html,js,php,vue}",
        "./components/com_emundus/views/**/*.{html,js,php,vue}",
        "./components/com_fabrik/layouts/**/*.{html,js,php}",
        "./components/com_fabrik/views/**/*.{html,js,php}",
        "./media/com_emundus/js/em_files.js",
        "./media/com_emundus/js/em_user.js",
        "./media/com_emundus/js/mixins/exports.js",
        "./media/com_emundus/js/mixins/utilities.js",
    ],
    safelist: [
        {
            pattern: /label-/
        },
        {
            pattern: /m(l|r|t|b|x|y)-/
        },
        {
            pattern: /p(l|r|t|b|x|y)-/
        }
    ],
    theme: {
        extend: {
            colors: {
                red: {
                    50: 'var(--red-50)',
                    100: 'var(--red-100)',
                    200: 'var(--red-200)',
                    300: 'var(--red-300)',
                    400: 'var(--red-400)',
                    500: 'var(--red-500)',
                    600: 'var(--red-600)',
                    700: 'var(--red-700)',
                    800: 'var(--red-800)',
                    900: 'var(--red-900)',
                },
                blue: {
                    50: 'var(--blue-50)',
                    100: 'var(--blue-100)',
                    200: 'var(--blue-200)',
                    300: 'var(--blue-300)',
                    400: 'var(--blue-400)',
                    500: 'var(--blue-500)',
                    600: 'var(--blue-600)',
                    700: 'var(--blue-700)',
                    800: 'var(--blue-800)',
                    900: 'var(--blue-900)',
                },
                orange: {
                    50: 'var(--orange-50)',
                    100: 'var(--orange-100)',
                    200: 'var(--orange-200)',
                    300: 'var(--orange-300)',
                    400: 'var(--orange-400)',
                    500: 'var(--orange-500)',
                    600: 'var(--orange-600)',
                    700: 'var(--orange-700)',
                    800: 'var(--orange-800)',
                    900: 'var(--orange-900)',
                },
                neutral: {
                    50: 'var(--neutral-50)',
                    100: 'var(--neutral-100)',
                    200: 'var(--neutral-200)',
                    300: 'var(--neutral-300)',
                    400: 'var(--neutral-400)',
                    500: 'var(--neutral-500)',
                    600: 'var(--neutral-600)',
                    700: 'var(--neutral-700)',
                    800: 'var(--neutral-800)',
                    900: 'var(--neutral-900)',
                },
                main: {
                    50: 'var(--main-50)',
                    100: 'var(--main-100)',
                    200: 'var(--main-200)',
                    300: 'var(--main-300)',
                    400: 'var(--main-400)',
                    500: 'var(--em-profile-color)',
                    600: 'var(--main-600)',
                    700: 'var(--main-700)',
                    800: 'var(--main-800)',
                    900: 'var(--main-900)',
                },
                green: {
                    500: 'var(--main-500)',
                }
            },
            spacing: {
                1: 'var(--em-spacing-1)',
                2: 'var(--em-spacing-2)',
                3: 'var(--em-spacing-3)',
                4: 'var(--em-spacing-4)',
                5: 'var(--em-spacing-5)',
                6: 'var(--em-spacing-6)',
                7: 'var(--em-spacing-7)',
                8: 'var(--em-spacing-8)',
                9: 'var(--em-spacing-9)',
                10: 'var(--em-spacing-10)',
                11: 'var(--em-spacing-11)',
                12: 'var(--em-spacing-12)',
            },
        },
    },
    plugins: [
        plugin(function ({ addComponents, theme }) {
            addComponents({
                '.em-default-title-1': {
                    color: 'var(--em-default-title-color-1)',
                    fontFamily: 'var(--em-applicant-font-title)',
                    fontSize: 'var(--em-applicant-h1)',
                    fontStyle: 'normal',
                    lineHeight: '28.8px',
                    fontWeight: 'var(--em-font-weight-500)',
                },
                '.em-default-title-2': {
                    color: 'var(--em-default-title-color-1)',
                    fontFamily: 'var(--em-applicant-font-title)',
                    fontSize: 'var(--em-applicant-h2)',
                    fontStyle: 'normal',
                    lineHeight: '26.4px',
                    fontWeight: 'var(--em-font-weight-500)',
                },
                '.em-default-title-3': {
                    color: 'var(--em-default-title-color-1)',
                    fontFamily: 'var(--em-applicant-font-title)',
                    fontSize: 'var(--em-applicant-h3)',
                    fontStyle: 'normal',
                    lineHeight: '24.2px',
                    fontWeight: 'var(--em-font-weight-500)',
                }
            });
        })
    ],
};
