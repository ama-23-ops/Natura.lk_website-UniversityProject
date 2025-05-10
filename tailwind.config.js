/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.{html,js,php}",
    "./includes/**/*.php",
    "./admin/**/*.php",
    "./customer/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0D9488', // teal-600
        secondary: '#F9F9F9',
        accent: '#222222',
        background: '#0D9488', // teal-600
        panel: '#F9F9F9',
        'text-primary': '#222222',
        'text-secondary': '#F9F9F9',
        'border-primary': '#222222',
        success: '#28a745',
        'success-light': '#d4edda',
        'success-text': '#155724',
        failed: '#dc3545',
        'failed-light': '#f8d7da',
        'failed-text': '#721c24',
        warning: '#ffc107',
        'warning-light': '#fff3cd',
        'warning-text': '#856404',
      },
      fontFamily: {
        'poppins': ['Poppins', 'sans-serif'],
      },
      boxShadow: {
        'card': '0 4px 6px rgba(0, 0, 0, 0.1)',
      },
      borderRadius: {
        'card': '15px',
      },
      spacing: {
        'form-padding': '2.5rem',
      },
      minHeight: {
        'screen-without-header': 'calc(100vh - 64px)', // Adjust based on your header height
      },
      transitionTimingFunction: {
        'smooth-in-out': 'cubic-bezier(0.4, 0, 0.2, 1)',
      },
      scale: {
        '102': '1.02',
        '105': '1.05',
      },
    },
  },
  plugins: [
    function({ addBase }) {
      addBase({
        'input:focus': {
          'outline': 'none',
          'box-shadow': 'none',
        },
      });
    },
  ],
}
