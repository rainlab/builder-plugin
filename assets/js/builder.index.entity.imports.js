/*
 * Builder Index controller Imports entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined) {
        $.oc.builder = {};
    }

    if ($.oc.builder.entityControllers === undefined) {
        $.oc.builder.entityControllers = {};
    }

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype;

    var Imports = function(indexController) {
        Base.call(this, 'imports', indexController);
    }

    Imports.prototype = Object.create(BaseProto);
    Imports.prototype.constructor = Imports;

    // PUBLIC METHODS
    // ============================

    Imports.prototype.cmdOpenImports = function(ev) {
        var currentPlugin = this.getSelectedPlugin();

        if (!currentPlugin) {
            alert('Please select a plugin first');
            return;
        }

        this.indexController.openOrLoadMasterTab($(ev.target), 'onImportsOpen', this.makeTabId(currentPlugin));
    }

    Imports.prototype.cmdSaveImports = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form');

        $target.request('onImportsSave', {
            data: {}
        }).done(
            this.proxy(this.saveImportsDone)
        );
    }

    Imports.prototype.cmdAddBlueprintItem = function(ev) {
        $.oc.builder.blueprintimporter.controller.addBlueprintItem(ev)
    }

    Imports.prototype.cmdRemoveBlueprintItem = function(ev) {
        $.oc.builder.blueprintimporter.controller.removeBlueprint(ev)
    }

    // INTERNAL METHODS
    // ============================

    Imports.prototype.saveImportsDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data');
        }

        var $masterTabPane = this.getMasterTabsActivePane();

        this.getIndexController().unchangeTab($masterTabPane);
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.imports = Imports;

}(window.jQuery);
