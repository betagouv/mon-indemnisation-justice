const Encore = require('@symfony/webpack-encore');
const path = require('node:path');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath(`public/${Encore.isProduction() ? 'build' : 'preview'}/`)
    // public path used by the web server to access the output path
    .setPublicPath(Encore.isProduction() ? '/build' : '/preview')
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .copyFiles({
        from: './node_modules/alpinejs/dist',
        to: 'alpinejs/[name].[ext]',
    })
    .copyFiles({
        from: './node_modules/remixicon',
        to: 'remixicon/[name].[ext]',
    })

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    .configureDevServerOptions(options => {
        // Webpack dev server configuration https://webpack.js.org/configuration/dev-server/
        options.server = 'http';
        options.allowedHosts = 'all';
        options.liveReload = true;
        options.hot = true;
        options.compress = false;
        options.host = '0.0.0.0';
        options.port = 8081;
        options.headers = {
            "Access-Control-Allow-Origin": "*",
            "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
            "Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
        };
        options.client = {
                overlay: true,
                reconnect: true,
                webSocketURL: 'ws://localhost:8081/ws',
        };
        options.static = {
            watch: false
        };
        options.watchFiles = {
            paths: ['src/**/*.php', 'templates/**/*'],
        };
    })

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    // enables Sass/SCSS support
    //.enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())
    .enableReactPreset()
    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
    .copyFiles([
      { from: './assets/images', to: 'images/[path][name].[ext]' },
      { from: './node_modules/remixicon/fonts', to: 'remixicon/fonts/[path][name].[ext]' },
    ])
;

module.exports = Encore.getWebpackConfig();
