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
                2: 'var(--spacing-2)',
            },
        },
    },
    plugins: [
        plugin(function ({ addComponents, theme }) {
            addComponents({
                '.em-default-title-1': {
                    color: 'var(--em-default-title-color-1)',
                    fontFamily: 'var(--em-applicant-font-title)',
                    fontSize: '24px',
                    fontStyle: 'normal',
                    lineHeight: '28.8px',
                    fontWeight: 500,
                },
                '.em-default-title-2': {
                    color: 'var(--em-default-title-color-1)',
                    fontFamily: 'var(--em-applicant-font-title)',
                    fontSize: '22px',
                    fontStyle: 'normal',
                    lineHeight: '26.4px',
                    fontWeight: 400,
                },
                '.em-default-title-3': {
                    color: 'var(--em-default-title-color-1)',
                    fontFamily: 'var(--em-applicant-font-title)',
                    fontSize: '20px',
                    fontStyle: 'normal',
                    lineHeight: '24.2px',
                    fontWeight: 400,
                }
            })
        })
    ],
}
