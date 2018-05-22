const path = require('path');

module.exports = {
    plugins: [
        require('stylelint')(),
        require('tailwindcss')(path.resolve(__dirname, 'tailwind.config.js')),
    ],
};
