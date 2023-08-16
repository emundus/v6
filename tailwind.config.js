/** @type {import('tailwindcss').Config} */
const plugin = require('tailwindcss/plugin')

module.exports = {
    content: [
        "./templates/g5_helium/html/**/*.{html,js,php}",
        "./modules/**/*.{html,js,php}",
        "./plugins/fabrik_element/**/*.{html,js,php}",
        "./components/com_emundus/**/*.{html,js,php,vue}",
        "./components/com_fabrik/**/*.{html,js,php}",
    ],
    safelist: [
        {
            pattern: /label-/
        },
    ],
    theme: {
        extend: {
            colors: {
                red: {
                    800: 'var(--red-800)',
                },
                neutral: {
                    500: 'var(--neutral-500)',
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
                    fontSize: 'var(--em-coordinator-h1)',
                    fontStyle: 'normal',
                    lineHeight: '28.8px',
                    fontWeight: 500,
                },
                '.em-default-title-2': {
                    color: 'var(--em-default-title-color-1)',
                    fontFamily: 'var(--em-applicant-font-title)',
                    fontSize: 'var(--em-coordinator-h2)',
                    fontStyle: 'normal',
                    lineHeight: '26.4px',
                    fontWeight: 500,
                },
                '.em-default-title-3': {
                    color: 'var(--em-default-title-color-1)',
                    fontFamily: 'var(--em-applicant-font-title)',
                    fontSize: 'var(--em-coordinator-h3)',
                    fontStyle: 'normal',
                    lineHeight: '24.2px',
                    fontWeight: 500,
                }
            })
        })
    ],
}
