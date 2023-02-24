/*
 * Blueprint Importer widget class.
 *
 * There is only a single instance of the Blueprint Importer class and it handles
 * as many import builder user interfaces as needed.
 *
 */
+function ($) { "use strict";

    if ($.oc.builder.blueprintbuilder === undefined) {
        $.oc.builder.blueprintbuilder = {};
    }

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype;

    var BlueprintBuilder = function() {
        Base.call(this);

        this.init();
    }

    BlueprintBuilder.prototype = Object.create(BaseProto)
    BlueprintBuilder.prototype.constructor = BlueprintBuilder

    // INTERNAL METHODS
    // ============================

    BlueprintBuilder.prototype.init = function() {
        this.registerHandlers();
    }

    BlueprintBuilder.prototype.registerHandlers = function() {

    }

    // BUILDER API METHODS
    // ============================

    BlueprintBuilder.prototype.addBlueprintItem = function(ev) {

    }

    BlueprintBuilder.prototype.removeBlueprint = function(ev) {

    }

    $(document).ready(function(){
        // There is a single instance of the form builder. All operations
        // are stateless, so instance properties or DOM references are not needed.
        $.oc.builder.blueprintbuilder.controller = new BlueprintBuilder();
    })

}(window.jQuery);
