let mix = require('laravel-mix');

mix.setPublicPath('assets');
mix.setResourceRoot('../');

mix.js('src/main.js', 'assets/js/wpdebuglog-admin.js').vue()
   .sass('src/app.scss', 'assets/css/wpdebuglog-admin.css')
   // .copy('src/images', 'assets/images')

