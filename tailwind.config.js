/** @type {import('tailwindcss').Config} */
const plugin = require('tailwindcss/plugin')

module.exports = {
    content: [
        "./templates/g5_helium/html/**/*.{html,js,php}",
        "./modules/**/src/*.{html,js,php}",
        "./modules/**/tmpl/*.{html,js,php}",
        "./plugins/fabrik_element/**/*.{html,js,php}",
        "./components/com_emundus/helpers/**/*.{html,js,php,vue}",
        "./components/com_emundus/models/**/*.{html,js,php,vue}",
        "./components/com_emundus/src/**/*.{html,js,php,vue}",
        "./components/com_emundus/views/**/*.{html,js,php,vue}",
        "./components/com_fabrik/layouts/**/*.{html,js,php}",
        "./components/com_fabrik/views/**/*.{html,js,php}",
    ],
    safelist: [
        {
            pattern: /label-/
        },
        {
            pattern: /(py|px|p)-/
        }
    ],
    theme: {
        extend: {
            colors: {
                red: {
                    800: 'var(--red-800)',
                },
                neutral: {
                    500: 'var(--neutral-500)',
                    600: 'var(--neutral-600)',
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
                    lineHeight: '30.5px',
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
            })
        })
    ],
}
