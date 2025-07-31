const mix = require('laravel-mix');

mix.js('assets/js/src/app.js', 'assets/js')
   .postCss('assets/css/src/styles.css', 'assets/css', [
     require('tailwindcss'),
     require('autoprefixer'),
   ]);
