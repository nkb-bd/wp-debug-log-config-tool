let mix = require('laravel-mix');
const path = require('path');

mix.setPublicPath('dist');
mix.setResourceRoot('/');

// Improve file watching configuration
mix.webpackConfig({
    watchOptions: {
        ignored: /node_modules/,
        aggregateTimeout: 300,
        poll: 1000 // Check for changes every second
    }
});

// Enable source maps for better debugging
if (!mix.inProduction()) {
    mix.webpackConfig({
        devtool: 'source-map'
    });
    mix.sourceMaps();
}

mix.js('resources/main.js', 'wpdebuglog-admin.js').vue();
mix.sass('resources/assets/main.scss', 'wpdebuglog-admin-css.css').options({
    processCssUrls: false
});

