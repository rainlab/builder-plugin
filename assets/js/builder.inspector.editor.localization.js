/*
 * Inspector localization editor class.
 */
+function ($) { "use strict";

    var Base = $.oc.inspector.propertyEditors.string,
        BaseProto = Base.prototype

    var LocalizationEditor = function(inspector, propertyDefinition, containerCell, group) {
        this.localizationInput = null

        Base.call(this, inspector, propertyDefinition, containerCell, group)
    }

    LocalizationEditor.prototype = Object.create(BaseProto)
    LocalizationEditor.prototype.constructor = Base

    LocalizationEditor.prototype.dispose = function() {
        this.removeLocalizationInput()

        BaseProto.dispose.call(this)
    }

    LocalizationEditor.prototype.build = function() {
        var container = document.createElement('div'),
            editor = document.createElement('input'),
            placeholder = this.propertyDefinition.placeholder !== undefined ? this.propertyDefinition.placeholder : '',
            value = this.inspector.getPropertyValue(this.propertyDefinition.property)

        editor.setAttribute('type', 'text')
        editor.setAttribute('class', 'string-editor')
        editor.setAttribute('placeholder', placeholder)

        container.setAttribute('class', 'autocomplete-container')

        if (value === undefined) {
            value = this.propertyDefinition.default
        }

        if (value === undefined) {
            value = ''
        }

        editor.value = value

        $.oc.foundation.element.addClass(this.containerCell, 'text autocomplete')

        container.appendChild(editor)
        this.containerCell.appendChild(container)

        this.buildLocalizationEditor()
    }

    LocalizationEditor.prototype.buildLocalizationEditor = function() {
        this.localizationInput = new $.oc.builder.localizationInput(this.getInput(), this.getForm(), {
            plugin: this.getPluginCode()
        })
    }

    LocalizationEditor.prototype.removeLocalizationInput = function() {
        this.localizationInput.dispose()

        this.localizationInput = null
    }

    LocalizationEditor.prototype.supportsExternalParameterEditor = function() {
        return false
    }

    LocalizationEditor.prototype.registerHandlers = function() {
        BaseProto.registerHandlers.call(this)

        $(this.getInput()).on('change', this.proxy(this.onInputKeyUp))
    }

    LocalizationEditor.prototype.unregisterHandlers = function() {
        BaseProto.unregisterHandlers.call(this)

        $(this.getInput()).off('change', this.proxy(this.onInputKeyUp))
    }

    LocalizationEditor.prototype.getForm = function() {
        var inspectableElement = this.getRootSurface().getInspectableElement()

        if (!inspectableElement) {
            throw new Error('Cannot determine inspectable element in the Builder localization editor.')
        }

        return $(inspectableElement).closest('form')
    }

    LocalizationEditor.prototype.getPluginCode = function() {
        var $form = this.getForm(),
            $input = $form.find('input[name=plugin_code]')

        if (!$input.length) {
            throw new Error('The input "plugin_code" should be defined in the form in order to use the localization Inspector editor.')
        }

        return $input.val()
    }

    $.oc.inspector.propertyEditors.builderLocalization = LocalizationEditor
}(window.jQuery);