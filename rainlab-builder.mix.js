/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your theme assets. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

module.exports = (mix) => {
    mix.less('plugins/rainlab/builder/assets/less/builder.less', 'plugins/rainlab/builder/assets/css/');

    mix.combine([
        'plugins/rainlab/builder/assets/js/builder.dataregistry.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.base.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.plugin.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.databasetable.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.model.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.modelform.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.modellist.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.permission.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.menus.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.imports.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.code.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.version.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.localization.js',
        'plugins/rainlab/builder/assets/js/builder.index.entity.controller.js',
        'plugins/rainlab/builder/assets/js/builder.index.js',
        'plugins/rainlab/builder/assets/js/builder.localizationinput.js',
        'plugins/rainlab/builder/assets/js/builder.inspector.editor.localization.js',
        'plugins/rainlab/builder/assets/js/builder.table.processor.localization.js',
        'plugins/rainlab/builder/assets/js/builder.codelist.js'
    ], 'plugins/rainlab/builder/assets/js/build-min.js');
}
