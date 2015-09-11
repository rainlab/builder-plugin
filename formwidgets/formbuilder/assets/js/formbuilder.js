/*
 * Form Builder widget class.
 *
 * There is only a single instance of the Form Builder class and it handles
 * as many form builder user interfaces as needed.
 *
 */
+function ($) { "use strict";

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var FormBuilder = function() {
        Base.call(this)

        this.placeholderIdIndex = 0

        this.init()
    }

    FormBuilder.prototype = Object.create(BaseProto)
    FormBuilder.prototype.constructor = FormBuilder

    // INTERNAL METHODS
    // ============================

    FormBuilder.prototype.init = function() {
        this.registerHandlers()
    }

    FormBuilder.prototype.registerHandlers = function() {
        document.addEventListener('dragstart', this.proxy(this.onDragStart))
        document.addEventListener('dragover', this.proxy(this.onDragOver))
        document.addEventListener('dragenter', this.proxy(this.onDragEnter))
        document.addEventListener('dragleave', this.proxy(this.onDragLeave))
        document.addEventListener('drop', this.proxy(this.onDragDrop), false);
    }

    FormBuilder.prototype.targetIsPlaceholder = function(ev) {
        return ev.target.getAttribute('data-builder-placeholder')
    }

    FormBuilder.prototype.sourceIsControlPalette = function(ev) {
        return ev.dataTransfer.types.indexOf('builder/source/palette') >= 0
    }

    FormBuilder.prototype.addControlToPlaceholder = function(placeholder, controlType) {
        // Duplicate the placeholder and place it after 
        // the existing one
        placeholder.insertAdjacentHTML('afterend', placeholder.outerHTML);

        // Replace the placeholder class with control
        // loading indicator
        $.oc.foundation.element.removeClass(placeholder, 'placeholder')
        $.oc.foundation.element.addClass(placeholder, 'loading-control')
        placeholder.innerHTML = ''
        placeholder.removeAttribute('data-builder-placeholder')


        // Send request to the server to load the 
        // control markup.
        var data = {
            controlType: controlType,
            controlId: this.getControlId(placeholder)
        }
        $(placeholder).request('onModelFormRenderField', {
            data: data
        }).done(this.proxy(this.controlMarkupLoaded))

        this.reflow()
    }

    FormBuilder.prototype.reflow = function() {
        throw new Error("To implement")
    }

    FormBuilder.prototype.getControlId = function(li) {
        if (li.hasAttribute('data-builder-control-id')) {
            return li.getAttribute('data-builder-control-id')
        }

        this.placeholderIdIndex++
        li.setAttribute('data-builder-control-id', this.placeholderIdIndex)

        return this.placeholderIdIndex
    }

    FormBuilder.prototype.controlMarkupLoaded = function(responseData) {
        var placeholder = document.body.querySelector('li[data-builder-control-id="'+responseData.controlId+'"]')
        if (!placeholder) {
            return
        }

        placeholder.innerHTML = responseData.markup
        $.oc.foundation.element.removeClass(placeholder, 'loading-control')
    }

    // EVENT HANDLERS
    // ============================

    FormBuilder.prototype.onDragStart = function(ev) {
        if (!ev.target.getAttribute('data-builder-control-palette-control')) {
            return
        }

        ev.dataTransfer.effectAllowed = 'move';
        ev.dataTransfer.setData('text/html', ev.target.innerHTML);
        ev.dataTransfer.setData('builder/source/palette', 'true');
        ev.dataTransfer.setData('builder/control/type', ev.target.getAttribute('data-builder-control-type'));
    }

    FormBuilder.prototype.onDragOver = function(ev) {
        if (ev.target.tagName != 'LI') {
            return
        }

        if (this.targetIsPlaceholder(ev) && this.sourceIsControlPalette(ev)) {
            // Dragging from the control palette over a placeholder.
            // Allow the drop.
            $.oc.foundation.event.stop(ev)
            ev.dataTransfer.dropEffect = 'move'
        }
    }

    FormBuilder.prototype.onDragEnter = function(ev) {
        if (ev.target.tagName != 'LI') {
            return
        }

        if (this.targetIsPlaceholder(ev) && this.sourceIsControlPalette(ev)) {
            // Dragging from the control palette over a placeholder.
            // Highlight the placeholder.
            $.oc.foundation.element.addClass(ev.target, 'drag-over')
        }
    }

    FormBuilder.prototype.onDragLeave = function(ev) {
        if (ev.target.tagName != 'LI') {
            return
        }

        if (this.targetIsPlaceholder(ev) && this.sourceIsControlPalette(ev)) {
            // Dragging from the control palette over a placeholder.
            // Stop highlighting the placeholder.
            $.oc.foundation.element.removeClass(ev.target, 'drag-over')
        }
    }

    FormBuilder.prototype.onDragDrop = function(ev) {
        if (ev.target.tagName != 'LI') {
            return
        }

        if (this.targetIsPlaceholder(ev) && this.sourceIsControlPalette(ev)) {
            // Dragging from the control palette over a placeholder.
            // Stop highlighting the placeholder.
            $.oc.foundation.event.stop(ev)
            $.oc.foundation.element.removeClass(ev.target, 'drag-over')

            this.addControlToPlaceholder(ev.target, ev.dataTransfer.getData('builder/control/type'))
        }
    }

    $(document).ready(function(){
        new FormBuilder()
    })

}(window.jQuery);