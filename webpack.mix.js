let mix = require('laravel-mix');
const path = require('path');

mix.setPublicPath('dist');
mix.setResourceRoot('/');


mix.js('resources/main.js', 'wpdebuglog-admin.js').vue();
mix.sass('resources/assets/main.scss', 'wpdebuglog-admin-css.css').options({
    processCssUrls: false
});;

