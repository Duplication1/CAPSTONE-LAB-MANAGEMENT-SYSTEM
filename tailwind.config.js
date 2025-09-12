/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./view/**/*.{php,html,js}",
    "./view/admin/**/*.{php,html,js}",
    "./view/student/**/*.{php,html,js}",
    "./view/itstaff/**/*.{php,html,js}",
    "./view/professor/**/*.{php,html,js}",
    "./components/**/*.{php,html,js}",
    "./js/**/*.js"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}