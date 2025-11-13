/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Livewire/**/*.php",
    "./app/Http/Livewire/**/*.php",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
  safelist: [
    'animate-pulse',
    'animate-bounce',
    'animate-spin',
    'animate-ping',
    'animate-fade-in',
    'animate-slide-up',
    'animate-bounce-in',
    'animate-float',
    'animate-float-slow',
    'animate-float-reverse'
  ]
}