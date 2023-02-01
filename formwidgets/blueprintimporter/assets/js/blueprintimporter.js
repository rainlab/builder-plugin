/*
 * Blueprint Importer widget class.
 *
 * There is only a single instance of the Blueprint Importer class and it handles
 * as many import builder user interfaces as needed.
 *
 */
+function ($) { "use strict";

    if ($.oc.builder.blueprintimporter === undefined) {
        $.oc.builder.blueprintimporter = {};
    }

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype;

    var BlueprintImporter = function() {
        Base.call(this);

        this.init();
    }

    BlueprintImporter.prototype = Object.create(BaseProto)
    BlueprintImporter.prototype.constructor = BlueprintImporter

    // INTERNAL METHODS
    // ============================

    BlueprintImporter.prototype.init = function() {
        this.registerHandlers();
    }

    BlueprintImporter.prototype.registerHandlers = function() {

    }

    // BUILDER API METHODS
    // ============================

    BlueprintImporter.prototype.addBlueprintItem = function(ev) {

    }

    BlueprintImporter.prototype.removeBlueprint = function(ev) {

    }

    $(document).ready(function(){
        // There is a single instance of the form builder. All operations
        // are stateless, so instance properties or DOM references are not needed.
        $.oc.builder.blueprintimporter.controller = new BlueprintImporter();
    })

}(window.jQuery);
