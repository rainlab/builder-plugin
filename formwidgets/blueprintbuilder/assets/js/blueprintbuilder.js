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

        this.updateBlueprintTimer = null;

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
        $(document).on('livechange', '.tailor-blueprint-list > li.blueprint', this.proxy(this.onBlueprintLiveChange))
    }

    // BUILDER API METHODS
    // ============================

    BlueprintBuilder.prototype.onBlueprintLiveChange = function(ev) {
        var $li = $(ev.currentTarget).closest('li');

        this.startUpdateBlueprintBody($li.data('blueprint-uuid'));

        ev.stopPropagation();
        return false;
    }

    BlueprintBuilder.prototype.startUpdateBlueprintBody = function(uuid) {
        this.clearUpdateBlueprintBodyTimer();

        var self = this;
        this.updateBlueprintTimer = window.setTimeout(function(){
            self.updateBlueprintBody(uuid);
        }, 300);
    }

    BlueprintBuilder.prototype.clearUpdateBlueprintBodyTimer = function() {
        if (this.updateBlueprintTimer === null) {
            return;
        }

        clearTimeout(this.updateBlueprintTimer);
        this.updateBlueprintTimer = null;
    }

    BlueprintBuilder.prototype.updateBlueprintBody = function(uuid) {
        var $blueprint = $('li[data-blueprint-uuid="'+uuid+'"]');
        if (!$blueprint.length) {
            return;
        }

        this.clearUpdateBlueprintBodyTimer();
        $blueprint.addClass('updating-blueprint');

        var properties = this.getBlueprintProperties($blueprint),
            data = {
                blueprint_uuid: uuid,
                properties: properties
            };

        $blueprint.request('onRefreshBlueprintContainer', {
            data: data
        }).done(
            this.proxy(this.blueprintMarkupLoaded)
        ).always(function(){
            $blueprint.removeClass('updating-blueprint');
        });
    }

    BlueprintBuilder.prototype.blueprintMarkupLoaded = function(responseData) {
        var $li = $('li[data-blueprint-uuid="'+responseData.blueprintUuid+'"]');
        if (!$li.length) {
            return;
        }

        $('.blueprint-body:first', $li).html(responseData.markup);
    }

    BlueprintBuilder.prototype.getBlueprintProperties = function($blueprint) {
        var value = $('input[data-inspector-values]', $blueprint).val();

        if (value) {
            return $.parseJSON(value);
        }

        throw new Error('Inspector values element is not found in control.');
    }

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
