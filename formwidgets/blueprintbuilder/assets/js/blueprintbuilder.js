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
        $(document).on('click', '.tailor-blueprint-list > li div[data-builder-remove-blueprint]', this.proxy(this.onRemoveBlueprint))
    }

    // BUILDER API METHODS
    // ============================

    BlueprintBuilder.prototype.onRemoveBlueprint = function(ev) {
        this.removeBlueprint($(ev.target).closest('li'));

        ev.preventDefault();
        ev.stopPropagation();

        return false;
    }

    BlueprintBuilder.prototype.removeBlueprint = function($control) {
        var $container = $('.blueprint-container:first', $control);

        if ($container.hasClass('inspector-open')) {
            var $inspectorContainer = this.findInspectorContainer($container);
            $.oc.foundation.controlUtils.disposeControls($inspectorContainer.get(0));
        }

        $control.remove();
    }

    BlueprintBuilder.prototype.findInspectorContainer = function($element) {
        var $containerRoot = $element.closest('[data-inspector-container]')

        return $containerRoot.find('.inspector-container')
    }

    $(document).ready(function(){
        // There is a single instance of the form builder. All operations
        // are stateless, so instance properties or DOM references are not needed.
        $.oc.builder.blueprintbuilder.controller = new BlueprintBuilder();
    })

}(window.jQuery);
