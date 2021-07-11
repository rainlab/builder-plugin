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
        $(document).on('click', '.builder-control-list > li.control div[data-builder-remove-control]', this.proxy(this.onRemoveControl))
        $(document).on('click', '.builder-control-list > li.placeholder', this.proxy(this.onPlaceholderClick))
        $(document).on('showing.oc.inspector', '.builder-control-list > li.control', this.proxy(this.onInspectorShowing))
        $(document).on('livechange', '.builder-control-list > li.control', this.proxy(this.onControlLiveChange))
        $(document).on('autocompleteitems.oc.inspector', '.builder-control-list > li.control', this.proxy(this.onAutocompleteItems))
        $(document).on('dropdownoptions.oc.inspector', '.builder-control-list > li.control', this.proxy(this.onDropdownOptions))
    }

    FormBuilder.prototype.getControlId = function(li) {
        if (li.hasAttribute('data-builder-control-id')) {
            return li.getAttribute('data-builder-control-id')
        }

        this.placeholderIdIndex++
        li.setAttribute('data-builder-control-id', this.placeholderIdIndex)

        return this.placeholderIdIndex
    }

    // PROPERTY HELPERS
    // ============================

    FormBuilder.prototype.getControlProperties = function(li) {
        var children = li.children

        for (var i=children.length-1; i>=0; i--) {
            var element = children[i]

            if (element.tagName === 'INPUT' && element.hasAttribute('data-inspector-values')) {
                return $.parseJSON(element.value)
            }
        }

        throw new Error('Inspector values element is not found in control.')
    }

    FormBuilder.prototype.setControlProperties = function(li, propertiesObj) {
        var propertiesStr = JSON.stringify(propertiesObj),
            valuesInput = li.querySelector('[data-inspector-values]')

        valuesInput.value = propertiesStr
    }

    FormBuilder.prototype.loadModelFields = function(control, callback) {
        var $form = $(this.findForm(control)),
            pluginCode = $.oc.builder.indexController.getFormPluginCode($form),
            modelClass = $form.find('input[name=model_class]').val()

        $.oc.builder.dataRegistry.get($form, pluginCode, 'model-columns', modelClass, function(response){
            callback({
                options: $.oc.builder.indexController.dataToInspectorArray(response)
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

    // FLOW MANAGEMENT
    // ============================

    FormBuilder.prototype.reflow = function(li, listElement) {
        if (!li && !listElement) {
            throw new Error('Invalid call of the reflow method. Either li or list parameter should be not empty.')
        }

        var list = listElement ? listElement : li.parentNode,
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

    FormBuilder.prototype.setControlSpanFromProperties = function(li, properties) {
        if (properties.span === undefined) {
            return
        }

        li.setAttribute('data-builder-span', properties.span)
        this.reflow(li)
    }

    FormBuilder.prototype.appendClearRowElement = function(li) {
        li.insertAdjacentHTML('afterend', '<li class="clear-row"></li>');
    }

    FormBuilder.prototype.patchControlSpan = function(li, span) {
        li.setAttribute('data-builder-span', span)

        var properties = this.getControlProperties(li)
        properties.span = span
        this.setControlProperties(li, properties)
    }

    // DRAG AND DROP
    // ============================

    FormBuilder.prototype.targetIsPlaceholder = function(ev) {
        if (!ev.target.getAttribute) {
            return false // In Gecko ev.target could be a text node
        }

        return ev.target.getAttribute('data-builder-placeholder')
    }

    FormBuilder.prototype.dataTransferContains = function(ev, element) {
        if (ev.dataTransfer.types.indexOf !== undefined){
            return ev.dataTransfer.types.indexOf(element) >= 0
        }

        return ev.dataTransfer.types.contains(element)
    }

    FormBuilder.prototype.sourceIsContainer = function(ev) {
        return this.dataTransferContains(ev, 'builder/source/container')
    }

    FormBuilder.prototype.startDragFromContainer = function(ev) {
        ev.dataTransfer.effectAllowed = 'move'

        var controlId = this.getControlId(ev.target)
        ev.dataTransfer.setData('builder/source/container', 'true')
        ev.dataTransfer.setData('builder/control/id', controlId)
        ev.dataTransfer.setData(controlId, controlId)
    }

    FormBuilder.prototype.dropTargetIsChildOf = function(target, ev) {
        var current = target

        while (current) {
            if (this.elementIsControl(current) && this.dataTransferContains(ev, this.getControlId(current))) {
                return true
            }

            current = current.parentNode
        }

        return false
    }

    FormBuilder.prototype.dropFromContainerToPlaceholderOrControl = function(ev, targetControl) {
        var targetElement = targetControl ? targetControl : ev.target

        $.oc.foundation.event.stop(ev)
        this.stopHighlightingTargets(targetElement)

        var controlId = ev.dataTransfer.getData('builder/control/id'),
            originalControl = document.body.querySelector('li[data-builder-control-id="'+controlId+'"]')

        if (!originalControl) {
            return
        }

        var isSameList = originalControl.parentNode === targetElement.parentNode,
            originalList = originalControl.parentNode,
            $originalClearRow = $(originalControl).next()

        targetElement.parentNode.insertBefore(originalControl, targetElement)

        this.appendClearRowElement(originalControl)
        if ($originalClearRow.hasClass('clear-row')) {
            $originalClearRow.remove()
        }

        if (!$.oc.foundation.element.hasClass(originalControl, 'inspector-open')) {
            this.patchControlSpan(originalControl, 'auto')
        }

        this.reflow(targetElement)

        if (!isSameList) {
            this.reflow(null, originalList)
        }

        $(targetElement).closest('form').trigger('change')
    }

    FormBuilder.prototype.elementContainsPoint = function(point, element) {
        var elementPosition = $.oc.foundation.element.absolutePosition(element),
            elementRight = elementPosition.left + element.offsetWidth,
            elementBottom = elementPosition.top + element.offsetHeight

        return point.x >= elementPosition.left && point.x <= elementRight
                && point.y >= elementPosition.top && point.y <= elementBottom
    }

    FormBuilder.prototype.stopHighlightingTargets = function(target, excludeTarget) {
        var rootWrapper = this.findRootControlWrapper(target),
            controls = rootWrapper.querySelectorAll('li.control.drag-over')

        for (var i=controls.length-1; i>= 0; i--) {
            if (!excludeTarget || target !== controls[i]) {
                $.oc.foundation.element.removeClass(controls[i], 'drag-over')
            }
        }
    }

    // UPDATING CONTROLS
    // ============================

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

    FormBuilder.prototype.controlBodyMarkupLoaded = function(responseData) {
        var li = document.body.querySelector('li[data-builder-control-id="'+responseData.controlId+'"]')
        if (!li) {
            return
        }

        var wrapper = li.querySelector('.control-wrapper')

        wrapper.innerHTML = responseData.markup
    }

    // ADDING CONTROLS
    // ============================

    FormBuilder.prototype.generateFieldName = function(controlType, placeholder) {
        var controlContainer = this.findControlContainer(placeholder)

        if (!controlContainer) {
            throw new Error('Cannot find control container for a placeholder.')
        }

        // Replace any banned characters
        controlType = controlType.replace(/[^a-zA-Z0-9_\[\]]/g, '')

        var counter = 1,
            fieldName = controlType + counter

        while (this.fieldNameExistsInContainer(controlContainer, fieldName)) {
            counter ++
            fieldName = controlType + counter
        }

        return fieldName
    }

    FormBuilder.prototype.addControlToPlaceholder = function(placeholder, controlType, controlName, noNewPlaceholder, fieldName) {
        // Duplicate the placeholder and place it after
        // the existing one
        if (!noNewPlaceholder) {
            var newPlaceholder = $(placeholder.outerHTML)

            newPlaceholder.removeAttr('data-builder-control-id')
            newPlaceholder.removeClass('control-palette-open')

            placeholder.insertAdjacentHTML('afterend', newPlaceholder.get(0).outerHTML)
        }

        // Create the clear-row element after the current placeholder
        this.appendClearRowElement(placeholder)

        // Replace the placeholder class with control
        // loading indicator
        $.oc.foundation.element.removeClass(placeholder, 'placeholder')
        $.oc.foundation.element.addClass(placeholder, 'loading-control')
        $.oc.foundation.element.removeClass(placeholder, 'control-palette-open')
        placeholder.innerHTML = ''
        placeholder.removeAttribute('data-builder-placeholder')

        if (!fieldName) {
            fieldName = this.generateFieldName(controlType, placeholder)
        }

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
        this.reflow(placeholder)

        return $(placeholder).request('onModelFormRenderControlWrapper', {
            data: data
        }).done(this.proxy(this.controlWrapperMarkupLoaded))
    }

    FormBuilder.prototype.controlWrapperMarkupLoaded = function(responseData) {
        var placeholder = document.body.querySelector('li[data-builder-control-id="'+responseData.controlId+'"]')
        if (!placeholder) {
            return
        }

        placeholder.setAttribute('draggable', true)
        placeholder.setAttribute('data-inspectable', true)
        placeholder.setAttribute('data-control-type', responseData.type)

        placeholder.setAttribute('data-inspector-title', responseData.controlTitle)
        placeholder.setAttribute('data-inspector-description', responseData.description)

        placeholder.innerHTML = responseData.markup
        $.oc.foundation.element.removeClass(placeholder, 'loading-control')
    }

    FormBuilder.prototype.displayControlPaletteForPlaceholder = function(element) {
        $.oc.builder.formbuilder.controlPalette.loadControlPalette(element, this.getControlId(element))
    }

    FormBuilder.prototype.addControlFromControlPalette = function(placeholderId, controlType, controlName) {
        var placeholder = document.body.querySelector('li[data-builder-control-id="'+placeholderId+'"]')
        if (!placeholder) {
            return
        }

        return this.addControlToPlaceholder(placeholder, controlType, controlName)
    }

    // REMOVING CONTROLS
    // ============================

    FormBuilder.prototype.removeControl = function($control) {
        if ($control.hasClass('inspector-open')) {
            var $inspectorContainer = this.findInspectorContainer($control)
            $.oc.foundation.controlUtils.disposeControls($inspectorContainer.get(0))
        }

        var $nextControl = $control.next() // Even if the removed element was alone, there's always a placeholder element
        $control.remove()

        this.reflow($nextControl.get(0))
        $nextControl.trigger('change')
    }

    // DOM HELPERS
    // ============================

    FormBuilder.prototype.findControlContainer = function(element) {
        var current = element

        while (current) {
            if (current.hasAttribute && current.hasAttribute('data-control-container') ) {
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

    FormBuilder.prototype.findControlList = function(element) {
        var current = element

        while (current) {
            if (current.hasAttribute('data-control-list')) {
                return current
            }

            current = current.parentNode
        }

        throw new Error('Cannot find control list for an element.')
    }

    FormBuilder.prototype.findPlaceholder = function(controlList) {
        var children = controlList.children

        for (var i=children.length-1; i>=0; i--) {
            var element = children[i]

            if (element.tagName === 'LI' && $.oc.foundation.element.hasClass(element, 'placeholder')) {
                return element
            }
        }

        throw new Error('Cannot find placeholder in a control list.')
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

    FormBuilder.prototype.findInspectorContainer = function($element) {
        var $containerRoot = $element.closest('[data-inspector-container]')

        return $containerRoot.find('.inspector-container')
    }

    FormBuilder.prototype.elementIsControl = function(element) {
        return element.tagName === 'LI' && element.hasAttribute('data-control-type') && $.oc.foundation.element.hasClass(element, 'control')
    }

    FormBuilder.prototype.getClosestControl = function(element) {
        var current = element

        while (current) {
            if (this.elementIsControl(current)) {
                return current
            }

            current = current.parentNode
        }

        return null
    }

    // EVENT HANDLERS
    // ============================

    FormBuilder.prototype.onDragStart = function(ev) {
        if (this.elementIsControl(ev.target)) {
            this.startDragFromContainer(ev)

            return
        }
    }

    FormBuilder.prototype.onDragOver = function(ev) {
        var targetLi = ev.target

        if (ev.target.tagName !== 'LI') {
            targetLi = this.getClosestControl(ev.target)
        }

        if (!targetLi || targetLi.tagName != 'LI') {
            return
        }

        var sourceIsContainer = this.sourceIsContainer(ev),
            elementIsControl = this.elementIsControl(targetLi)

        if ((this.targetIsPlaceholder(ev) || elementIsControl) && sourceIsContainer) {
            // Do not allow dropping controls to themselves or their
            // children controls.
            if (sourceIsContainer && elementIsControl && this.dropTargetIsChildOf(targetLi, ev)) {
                return false
            }

            // Dragging from container over a placeholder or another control.
            // Allow the drop.
            $.oc.foundation.event.stop(ev)
            ev.dataTransfer.dropEffect = 'move'
            return
        }
    }

    FormBuilder.prototype.onDragEnter = function(ev) {
        var targetLi = ev.target

        if (ev.target.tagName !== 'LI') {
            targetLi = this.getClosestControl(ev.target)
        }

        if (!targetLi || targetLi.tagName != 'LI') {
            return
        }

        var sourceIsContainer = this.sourceIsContainer(ev)

        if (this.targetIsPlaceholder(ev) && sourceIsContainer) {
            // Do not allow dropping controls to themselves or their
            // children controls.
            if (sourceIsContainer && this.dropTargetIsChildOf(ev.target, ev)) {
                this.stopHighlightingTargets(ev.target, true)
                return
            }

            // Dragging from a container over a placeholder.
            // Highlight the placeholder.
            $.oc.foundation.element.addClass(ev.target, 'drag-over')
            return
        }

        var elementIsControl = this.elementIsControl(targetLi)

        if (elementIsControl && sourceIsContainer) {
            // Do not allow dropping controls to themselves or their
            // children controls.
            if (sourceIsContainer && elementIsControl && this.dropTargetIsChildOf(targetLi, ev)) {
                this.stopHighlightingTargets(targetLi, true)
                return
            }

            // Dragging from a container over another control.
            // Highlight the other control.
            $.oc.foundation.element.addClass(targetLi, 'drag-over')

            this.stopHighlightingTargets(targetLi, true)

            return
        }
    }

    FormBuilder.prototype.onDragLeave = function(ev) {
        var targetLi = ev.target

        if (ev.target.tagName !== 'LI') {
            targetLi = this.getClosestControl(ev.target)
        }

        if (!targetLi || targetLi.tagName != 'LI') {
            return
        }

        if (this.targetIsPlaceholder(ev) && this.sourceIsContainer(ev)) {
            // Dragging from a container over a placeholder.
            // Stop highlighting the placeholder.
            this.stopHighlightingTargets(ev.target)

            return
        }

        if (this.elementIsControl(targetLi) && this.sourceIsContainer(ev)) {
            // Dragging from a container over another control.
            // Stop highlighting the other control.
            var mousePosition = $.oc.foundation.event.pageCoordinates(ev)

            if (!this.elementContainsPoint(mousePosition, targetLi)) {
                this.stopHighlightingTargets(targetLi)
            }
        }
    }

    FormBuilder.prototype.onDragDrop = function(ev) {
        var targetLi = ev.target

        if (ev.target.tagName !== 'LI') {
            targetLi = this.getClosestControl(ev.target)
        }

        if (!targetLi || targetLi.tagName != 'LI') {
            return
        }

        var elementIsControl = this.elementIsControl(targetLi),
            sourceIsContainer = this.sourceIsContainer(ev)

        if ((elementIsControl || this.targetIsPlaceholder(ev)) && sourceIsContainer) {
            this.stopHighlightingTargets(targetLi)

            if (this.dropTargetIsChildOf(targetLi, ev)) {
                return
            }

            // Dropped from a container to a placeholder or another control.
            // Stop highlighting the placeholder, move the control.
            this.dropFromContainerToPlaceholderOrControl(ev, targetLi)
            return
        }
    }

    FormBuilder.prototype.onControlChange = function(ev) {
        // Control has changed (with Inspector) -
        // update the control markup with AJAX

        var li = ev.currentTarget,
            properties = this.getControlProperties(li)

        this.setControlSpanFromProperties(li, properties)
        this.updateControlBody(this.getControlId(li))

        ev.stopPropagation()
        return false
    }

    FormBuilder.prototype.onControlLiveChange = function(ev) {
        $(this.findForm(ev.currentTarget)).trigger('change')  // Set modified state for the form

        var li = ev.currentTarget,
            propertiesParsed = this.getControlProperties(li)

        this.setControlSpanFromProperties(li, propertiesParsed)
        this.startUpdateControlBody(this.getControlId(li))

        ev.stopPropagation()
        return false
    }

    FormBuilder.prototype.onAutocompleteItems = function(ev, data) {
        if (data.propertyDefinition.fillFrom === 'model-fields') {
            ev.preventDefault()
            this.loadModelFields(ev.target, data.callback)
        }
    }

    FormBuilder.prototype.onDropdownOptions = function(ev, data) {
        if (data.propertyDefinition.fillFrom === 'form-controls') {
            this.getContainerFieldNames(ev.target, data.callback)
            ev.preventDefault()
        }
    }

    FormBuilder.prototype.onRemoveControl = function(ev) {
        this.removeControl($(ev.target).closest('li.control'))

        ev.preventDefault()
        ev.stopPropagation()

        return false
    }

    FormBuilder.prototype.onInspectorShowing = function(ev) {
        if ($(ev.target).find('input[data-non-inspectable-control]').length > 0) {
            ev.preventDefault()
            return false
        }
    }

    FormBuilder.prototype.onPlaceholderClick = function(ev) {
        this.displayControlPaletteForPlaceholder(ev.target)
        ev.stopPropagation()
        ev.preventDefault()
        return false;
    }

    $(document).ready(function(){
        // There is a single instance of the form builder. All operations
        // are stateless, so instance properties or DOM references are not needed.
        $.oc.builder.formbuilder.controller = new FormBuilder()
    })

}(window.jQuery);