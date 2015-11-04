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
        this.updateControlBodyTimer = null

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

        $(document).on('change', '.builder-control-list > li.control', this.proxy(this.onControlChange))
        $(document).on('livechange', '.builder-control-list > li.control', this.proxy(this.onControlLiveChange))
        $(document).on('autocompleteitems.oc.inspector', '.builder-control-list > li.control', this.proxy(this.onAutocompleteItems))
        $(document).on('dropdownoptions.oc.inspector', '.builder-control-list > li.control', this.proxy(this.onDropdownOptions))
    }

    FormBuilder.prototype.targetIsPlaceholder = function(ev) {
        return ev.target.getAttribute('data-builder-placeholder')
    }

    FormBuilder.prototype.sourceIsControlPalette = function(ev) {
        return ev.dataTransfer.types.indexOf('builder/source/palette') >= 0
    }

    FormBuilder.prototype.findControlContainer = function(element) {
        var current = element

        while (current) {
            if (current.hasAttribute('data-contol-container') ) {
                return current
            }

            current = current.parentNode
        }

        return null
    }

    FormBuilder.prototype.findForm = function(element) {
        var current = element

        while (current) {
            if (current.tagName === 'FORM') {
                return current
            }

            current = current.parentNode
        }

        return null
    }

    FormBuilder.prototype.fieldNameExistsInContainer = function(container, fieldName) {
        var valueInputs = container.querySelectorAll('li.control[data-inspectable] input[data-inspector-values]')

        for (var i=valueInputs.length-1; i>=0; i--) {
            var value = String(valueInputs[i].value)

            if (value.length === 0) {
                continue
            }

            var properties = $.parseJSON(value)

            if (properties['oc.fieldName'] == fieldName) {
                return true
            }
        }

        return false
    }

    FormBuilder.prototype.generateFieldName = function(controlType, placeholder) {
        var controlContainer = this.findControlContainer(placeholder)

        if (!controlContainer) {
            throw new Error('Cannot find control container for a placeholder.')
        }

        var counter = 1,
            fieldName = controlType + counter

        while (this.fieldNameExistsInContainer(controlContainer, fieldName)) {
            counter ++
            fieldName = controlType + counter
        }

        return fieldName
    }

    FormBuilder.prototype.addControlToPlaceholder = function(placeholder, controlType, controlName) {
        // Duplicate the placeholder and place it after 
        // the existing one
        placeholder.insertAdjacentHTML('afterend', placeholder.outerHTML)

        // Create the clear-row element after the current placeholder
        this.appendClearRowElement(placeholder)

        // Replace the placeholder class with control
        // loading indicator
        $.oc.foundation.element.removeClass(placeholder, 'placeholder')
        $.oc.foundation.element.addClass(placeholder, 'loading-control')
        placeholder.innerHTML = ''
        placeholder.removeAttribute('data-builder-placeholder')

        var fieldName = this.generateFieldName(controlType, placeholder)

        // Send request to the server to load the 
        // control markup, Inspector data schema, inspector title, etc.
        var data = {
            controlType: controlType,
            controlId: this.getControlId(placeholder),
            properties: {
                'label': controlName,
                'span': 'auto',
                'oc.fieldName': fieldName
            }
        }
        $(placeholder).request('onModelFormRenderControlWrapper', {
            data: data
        }).done(this.proxy(this.controlWrapperMarkupLoaded))

        this.reflow(placeholder)
    }

    FormBuilder.prototype.reflow = function(li) {
        var list = li.parentNode,
            items = list.children,
            prevSpan = null

        for (var i=0, len = items.length; i < len; i++) {
            var item = items[i],
                itemSpan = item.getAttribute('data-builder-span')

            if ($.oc.foundation.element.hasClass(item, 'clear-row')) {
                continue
            }

            if (itemSpan == 'auto') {
                $.oc.foundation.element.removeClass(item, 'span-left')
                $.oc.foundation.element.removeClass(item, 'span-full')
                $.oc.foundation.element.removeClass(item, 'span-right')

                if (prevSpan == 'left') {
                    $.oc.foundation.element.addClass(item, 'span-right')
                    prevSpan = 'right'
                }
                else {
                    if (!$.oc.foundation.element.hasClass(item, 'placeholder')) {
                        $.oc.foundation.element.addClass(item, 'span-left')
                    } 
                    else {
                        $.oc.foundation.element.addClass(item, 'span-full')
                    }

                    prevSpan = 'left'
                }
            } 
            else {
                $.oc.foundation.element.removeClass(item, 'span-left')
                $.oc.foundation.element.removeClass(item, 'span-full')
                $.oc.foundation.element.removeClass(item, 'span-right')
                $.oc.foundation.element.addClass(item, 'span-' + itemSpan)

                prevSpan = itemSpan
            }
        }
    }

    FormBuilder.prototype.getControlId = function(li) {
        if (li.hasAttribute('data-builder-control-id')) {
            return li.getAttribute('data-builder-control-id')
        }

        this.placeholderIdIndex++
        li.setAttribute('data-builder-control-id', this.placeholderIdIndex)

        return this.placeholderIdIndex
    }

    FormBuilder.prototype.controlWrapperMarkupLoaded = function(responseData) {
        var placeholder = document.body.querySelector('li[data-builder-control-id="'+responseData.controlId+'"]')
        if (!placeholder) {
            return
        }

        placeholder.setAttribute('data-inspectable', true)
        placeholder.setAttribute('data-control-type', responseData.type)

        placeholder.setAttribute('data-inspector-title', responseData.controlTitle)
        placeholder.setAttribute('data-inspector-description', responseData.description)

        placeholder.innerHTML = responseData.markup
        $.oc.foundation.element.removeClass(placeholder, 'loading-control')
    }

    FormBuilder.prototype.appendClearRowElement = function(li) {
        li.insertAdjacentHTML('afterend', '<li class="clear-row"></li>');
    }

    FormBuilder.prototype.controlBodyMarkupLoaded = function(responseData) {
        var li = document.body.querySelector('li[data-builder-control-id="'+responseData.controlId+'"]')
        if (!li) {
            return
        }

        var wrapper = li.querySelector('.control-wrapper')

        wrapper.innerHTML = responseData.markup
    }

    FormBuilder.prototype.getControlProperties = function(li) {
        var properties = li.querySelector('[data-inspector-values]').value
            
        return $.parseJSON(properties)
    }

    FormBuilder.prototype.setControlSpanFromProperties = function(li, properties) {
        if (properties.span === undefined) {
            return
        }

        li.setAttribute('data-builder-span', properties.span)
        this.reflow(li)
    }

    FormBuilder.prototype.findRootControlWrapper = function(control) {
        var current = control

        while (current) {
            if (current.hasAttribute('data-root-control-wrapper')) {
                return current
            }

            current = current.parentNode
        }

        throw new Error('Cannot find root control wrapper.')
    }
    
    FormBuilder.prototype.startUpdateControlBody = function(controlId) {
        this.clearUpdateControlBodyTimer()

        var self = this
        this.updateControlBodyTimer = window.setTimeout(function(){
            self.updateControlBody(controlId)
        }, 300)
    }

    FormBuilder.prototype.clearUpdateControlBodyTimer = function() {
        if (this.updateControlBodyTimer === null) {
            return
        }

        clearTimeout(this.updateControlBodyTimer)
        this.updateControlBodyTimer = null
    }

    FormBuilder.prototype.updateControlBody = function(controlId) {
        var control = document.body.querySelector('li[data-builder-control-id="'+controlId+'"]')
        if (!control) {
            return
        }

        this.clearUpdateControlBodyTimer()

        var rootWrapper = this.findRootControlWrapper(control),
            controls = rootWrapper.querySelectorAll('li.control.updating-control')

        for (var i=controls.length-1; i>=0; i--) {
            $.oc.foundation.element.removeClass(controls[i], 'updating-control')
        }

        $.oc.foundation.element.addClass(control, 'updating-control')

        var controlType = control.getAttribute('data-control-type'),
            properties = this.getControlProperties(control),
            data = {
                controlType: controlType,
                controlId: controlId,
                properties: properties
            }

        $(control).request('onModelFormRenderControlBody', {
            data: data
        }).done(
            this.proxy(this.controlBodyMarkupLoaded)
        ).always(function(){
            $.oc.foundation.element.removeClass(control, 'updating-control')
        })
    }

    FormBuilder.prototype.loadModelFields = function(control, callback) {
        var $form = $(this.findForm(control)),
            cachedFields = $form.data('oc.model-field-cache')

        if (cachedFields !== undefined) {
            callback({
                options: cachedFields
            })

            return
        }

        $form.request('onModelFormGetModelFields')
            .done(function(data){
                $form.data('oc.model-field-cache', data.responseData.options)
                callback({
                    options: data.responseData.options
                })
            })
    }

    FormBuilder.prototype.getContainerFieldNames = function(control, callback) {
        var controlWrapper = this.findRootControlWrapper(control),
            fieldNames = $.oc.builder.formbuilder.domToPropertyJson.getAllControlNames(controlWrapper),
            options = []

        options.push({
            title: '---',
            value: ''
        })

        for (var i=0, len=fieldNames.length; i<len; i++){
            options.push({
                title: fieldNames[i],
                value: fieldNames[i]
            })
        }

        callback({options: options})
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
        ev.dataTransfer.setData('builder/control/name', ev.target.getAttribute('data-builder-control-name'));
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

            this.addControlToPlaceholder(ev.target,
                ev.dataTransfer.getData('builder/control/type'),
                ev.dataTransfer.getData('builder/control/name'))

            $(ev.target.parentNode).trigger('change')
        }
    }

    FormBuilder.prototype.onControlChange = function(ev) {
        // Control has changed (with Inspector) -
        // update the control markup with AJAX

        var li = ev.currentTarget,
            properties = this.getControlProperties(li)

        this.setControlSpanFromProperties(li, properties)
        this.updateControlBody(this.getControlId(li))
    }

    FormBuilder.prototype.onControlLiveChange = function(ev) {
        $(ev.currentTarget.parentNode).trigger('change')  // Set modified state for the form

        var li = ev.currentTarget,
            propertiesParsed = this.getControlProperties(li)

        this.setControlSpanFromProperties(li, propertiesParsed)
        this.startUpdateControlBody(this.getControlId(li))
    }

    FormBuilder.prototype.onAutocompleteItems = function(ev, data) {

        if (data.property === 'oc.fieldName') {
            ev.preventDefault()
            this.loadModelFields(ev.target, data.callback)
        }
    }

    FormBuilder.prototype.onDropdownOptions = function(ev, data) {
        if (data.property === 'trigger.field' || data.property == 'preset.field' || data.property == 'defaultFrom') {
            this.getContainerFieldNames(ev.target, data.callback)
            ev.preventDefault()
        }
    }

    $(document).ready(function(){
        // There is a single instance of the form builder. All operations
        // are stateless, so instance properties or DOM references are not needed.
        new FormBuilder()
    })

}(window.jQuery);