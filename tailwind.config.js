/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./modules/**/*.{html,js,php}"],
  theme: {
    extend: {
      colors: {
        amber: {
          700: "var(--main-500)",
        },
      },
      spacing: {
        2: 'var(--spacing-2)',
      }
    },
  },
  plugins: [],
}
