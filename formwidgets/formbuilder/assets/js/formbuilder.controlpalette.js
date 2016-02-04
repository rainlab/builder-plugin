/*
 * Manages the control palette loading and displaying
 */
+function ($) { "use strict";

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var ControlPalette = function() {
        Base.call(this)

        this.controlPaletteMarkup = null
        this.popoverMarkup = null
        this.containerMarkup = null
        this.$popoverContainer = null
    }

    ControlPalette.prototype = Object.create(BaseProto)
    ControlPalette.prototype.constructor = ControlPalette

    // INTERNAL METHODS
    // ============================

    ControlPalette.prototype.loadControlPalette = function(element, controlId) {
        if (this.controlPaletteMarkup === null) {
            var data = {
                controlId: controlId
            }

            $.oc.stripeLoadIndicator.show()
            $(element).request('onModelFormLoadControlPalette', {
                data: data
            }).done(
                this.proxy(this.controlPaletteMarkupLoaded)
            ).always(function(){
                $.oc.stripeLoadIndicator.hide()
            })
        }
        else {
            this.showControlPalette(controlId, true)
        }
    }

    ControlPalette.prototype.controlPaletteMarkupLoaded = function(responseData) {
        this.controlPaletteMarkup = responseData.markup

        this.showControlPalette(responseData.controlId)
    }

    ControlPalette.prototype.getControlById = function(controlId) {
        return document.body.querySelector('li[data-builder-control-id="'+controlId+'"]')
    }

    ControlPalette.prototype.showControlPalette = function(controlId, initControls) {
        if (this.getContainerPreference()) {
            this.showControlPalletteInContainer(controlId, initControls)
        }
        else {
            this.showControlPalletteInPopup(controlId, initControls)
        }
    }

    ControlPalette.prototype.assignControlIdToTemplate = function(template, controlId) {
        return template.replace('%c', controlId)
    }

    ControlPalette.prototype.markPlaceholderPaletteOpen = function(control) {
        $(control).addClass('control-palette-open')
    }

    ControlPalette.prototype.markPlaceholderPaletteNotOpen = function(control) {
        $(control).removeClass('control-palette-open')
    }

    ControlPalette.prototype.getContainerPreference = function() {
return true
        if (!Modernizr.localstorage) {
            return false
        }

        return localStorage.getItem('oc.builder.controlPaletteUseContainer') === "true"
    }

    ControlPalette.prototype.addControl = function(ev) {
        var $target = $(ev.currentTarget),
            controlId = $target.closest('[data-control-palette-controlid]').attr('data-control-palette-controlid')

        ev.preventDefault()
        ev.stopPropagation()

        if (!controlId) {
            return false;
        }

        var control = this.getControlById(controlId)
        if (!control) {
            return false
        }

        if ($(control).hasClass('loading-control')) {
            return false
        }

        $target.trigger('close.oc.popover')

        var promise = $.oc.builder.formbuilder.controller.addControlFromControlPalette(controlId, 
            $target.data('builderControlType'), 
            $target.data('builderControlName'))

        promise.done(function() {
            $.oc.inspector.manager.createInspector(control)
        })

        return false
    }

    //
    // Popover wrapper
    //

    ControlPalette.prototype.showControlPalletteInPopup = function(controlId, initControls) {
        var control = this.getControlById(controlId)

        if (!control) {
            return
        }

        var $control = $(control)

        $control.ocPopover({
            content: this.assignControlIdToTemplate(this.getPopoverMarkup(), controlId),
            highlightModalTarget: true,
            modal: true,
            placement: 'below',
            containerClass: 'control-inspector',
            offset: 15,
            width: 400
        })

        var $popoverContainer = $control.data('oc.popover').$container

        if (initControls) {
            // Initialize the scrollpad control in the popup only when the
            // popup is created from the cached markup string
            $popoverContainer.trigger('render')
        }
    }

    ControlPalette.prototype.getPopoverMarkup = function() {
        if (this.popoverMarkup !== null) {
            return this.popoverMarkup
        }

        var outerMarkup = $('script[data-template=control-palette-popover]').html()

        this.popoverMarkup = outerMarkup.replace('%s', this.controlPaletteMarkup)

        return this.popoverMarkup
    }

    //
    // Container wrapper
    //

    ControlPalette.prototype.showControlPalletteInContainer = function(controlId, initControls) {
        var control = this.getControlById(controlId)

        if (!control) {
            return
        }

        var inspectorManager = $.oc.inspector.manager,
            $container = inspectorManager.getContainerElement($(control))

        // If the container is already in use, apply values to the inspectable elements
        if (!inspectorManager.applyValuesFromContainer($container) || !inspectorManager.containerHidingAllowed($container)) {
            return
        }

        // Dispose existing Inspector
        $.oc.foundation.controlUtils.disposeControls($container.get(0))

        this.markPlaceholderPaletteOpen(control)

        var template = this.assignControlIdToTemplate(this.getContainerMarkup(), controlId)
        $container.append(template)

        $container.find('[data-control-palette-controlid]').one('dispose-control', this.proxy(this.onRemovePaletteFromContainer))

        if (initControls) {
            // Initialize the scrollpad control in the container only when the
            // palette is created from the cached markup string
            $container.trigger('render')
        }
    }
 
    ControlPalette.prototype.onRemovePaletteFromContainer = function(ev) {
        var $container = $(ev.target),
            controlId = $container.attr('data-control-palette-controlid'),
            control = this.getControlById(controlId)

        if (control) {
            this.markPlaceholderPaletteNotOpen(control)
        }

        $container.remove()
    }

    ControlPalette.prototype.getContainerMarkup = function() {
        if (this.containerMarkup !== null) {
            return this.containerMarkup
        }

        var outerMarkup = $('script[data-template=control-palette-container]').html()

        this.containerMarkup = outerMarkup.replace('%s', this.controlPaletteMarkup)

        return this.containerMarkup
    }

    $(document).ready(function(){
        // There is a single instance of the control palette manager.
        $.oc.builder.formbuilder.controlPalette = new ControlPalette()
    })

}(window.jQuery);