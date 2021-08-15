let mix = require('laravel-mix');

mix.setPublicPath('assets');
mix.setResourceRoot('../');

mix.js('resources/main.js', 'assets/js/wpdebuglog-admin.js').vue()
   .sass('resources/app.scss', 'assets/css/wpdebuglog-admin.css')


