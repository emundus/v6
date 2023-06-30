/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{html,js}"],
  theme: {
    extend: {
      colors: {
        clifford: "var(--red-600)",
      },
      spacing: {
        1: '8px'
      }
    },
  },
  plugins: [],
}
