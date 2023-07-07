/** @type {import('tailwindcss').Config} */
const plugin = require('tailwindcss/plugin')

module.exports = {
    prefix: 'em-',
    content: [
        "./templates/g5_helium/html/**/*.{html,js,php}",
        "./modules/**/*.{html,js,php}",
        "./components/com_emundus/**/*.{html,js,php,vue}",
        "./components/com_fabrik/**/*.{html,js,php}",
    ],
    theme: {
        extend: {
            colors: {
                red: {
                    800: '$em-tchooz-red-800',
                },
                neutral: {
                    500: '$em-tchooz-neutral-500',
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
                '.default-title-1': {
                    color: 'var(--em-default-title-color-1)',
                    fontFamily: '$font-family-title',
                    fontSize: '24px',
                    fontStyle: 'normal',
                    lineHeight: '28.8px',
                }
            })
        })
    ],
}
