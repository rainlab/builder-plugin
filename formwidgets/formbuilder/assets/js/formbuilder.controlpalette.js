/*
 * Manages the control palette loading and displaying
 */
+function ($) { "use strict";

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var ControlPalette = function() {
        Base.call(this)

        this.controlPaletteMarkup = null
        this.controlId = null
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
        var control = this.getControlById(controlId)

        if (!control) {
            return
        }

        this.controlId = controlId

        var $control = $(control)

        $control.ocPopover({
            content: this.controlPaletteMarkup,
            highlightModalTarget: true,
            modal: true,
            placement: 'below',
            containerClass: 'control-inspector',
            offset: 15,
            width: 400
        })

        this.$popoverContainer = $control.data('oc.popover').$container

        if (initControls) {
            // Initialize the scrollpad control in the popup only when the
            // popup is created from the cached markup string
            this.$popoverContainer.trigger('render')
        }

        $control.one('hide.oc.popover', this.proxy(this.onHide))
        this.$popoverContainer.on('click', 'a[data-builder-control-palette-control]', this.proxy(this.onControlClick))
    }

    ControlPalette.prototype.onHide = function(ev) {
        this.$popoverContainer.off('click', 'a[data-builder-control-palette-control]', this.proxy(this.onControlClick))
        this.$popoverContainer = null
    }

    ControlPalette.prototype.onControlClick = function(ev) {
        var $target = $(ev.currentTarget)

        // control = this.getControlById(controlId)

        // if (control) {
        //     $(control).removeClass('popover-highlight')
        // }

        $target.trigger('close.oc.popover')

        $.oc.builder.formbuilder.controller.addControlFromControlPalette(this.controlId, $target.data('builderControlType'), $target.data('builderControlName'))

        ev.preventDefault()
        ev.stopPropagation()

        return false
    }

    $(document).ready(function(){
        // There is a single instance of the control palette manager.
        $.oc.builder.formbuilder.controlPalette = new ControlPalette()
    })

}(window.jQuery);