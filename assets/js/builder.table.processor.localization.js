/*
 * Localiztion cell processor for the table control.
 */

+function ($) { "use strict";

    // NAMESPACE CHECK
    // ============================

    if ($.oc.table === undefined)
        throw new Error("The $.oc.table namespace is not defined. Make sure that the table.js script is loaded.");

    if ($.oc.table.processor === undefined)
        throw new Error("The $.oc.table.processor namespace is not defined. Make sure that the table.processor.base.js script is loaded.");

    // CLASS DEFINITION
    // ============================

    var Base = $.oc.table.processor.string,
        BaseProto = Base.prototype

    var LocalizationProcessor = function(tableObj, columnName, columnConfiguration) {
        //
        // State properties
        //

        this.localizationInput = null
        this.popupDisplayed = false

        //
        // Parent constructor
        //

        Base.call(this, tableObj, columnName, columnConfiguration)
    }

    LocalizationProcessor.prototype = Object.create(BaseProto)
    LocalizationProcessor.prototype.constructor = LocalizationProcessor

    LocalizationProcessor.prototype.dispose = function() {
        this.removeLocalizationInput()

        BaseProto.dispose.call(this)
    }

    /*
     * Forces the processor to hide the editor when the user navigates
     * away from the cell. Processors can update the sell value in this method.
     * Processors must clear the reference to the active cell in this method.
     */
    LocalizationProcessor.prototype.onUnfocus = function() {
        if (!this.activeCell || this.popupDisplayed)
            return

        this.removeLocalizationInput()

        BaseProto.onUnfocus.call(this)
    }

    LocalizationProcessor.prototype.onBeforePopupShow = function() {
        this.popupDisplayed = true
    }

    LocalizationProcessor.prototype.onAfterPopupHide = function() {
        this.popupDisplayed = false
    }

    /*
     * Renders the cell in the normal (no edit) mode
     */
    LocalizationProcessor.prototype.renderCell = function(value, cellContentContainer) {
        BaseProto.renderCell.call(this, value, cellContentContainer)
    }

    LocalizationProcessor.prototype.buildEditor = function(cellElement, cellContentContainer, isClick) {
        BaseProto.buildEditor.call(this, cellElement, cellContentContainer, isClick)

        $.oc.foundation.element.addClass(cellContentContainer, 'autocomplete-container')
        this.buildLocalizationEditor()
    }

    LocalizationProcessor.prototype.buildLocalizationEditor = function() {
        var input = this.getInput()

        this.localizationInput = new $.oc.builder.localizationInput(input, $(input), {
            plugin: this.getPluginCode(input),
            beforePopupShowCallback: $.proxy(this.onBeforePopupShow, this),
            afterPopupHideCallback: $.proxy(this.onAfterPopupHide, this),
            autocompleteOptions: {
                menu: '<ul class="autocomplete dropdown-menu table-widget-autocomplete localization"></ul>',
                bodyContainer: true
            }
        })
    }

    LocalizationProcessor.prototype.getInput = function() {
        if (!this.activeCell) {
            return null
        }

        return this.activeCell.querySelector('.string-input')
    }

    LocalizationProcessor.prototype.getPluginCode = function(input) {
        var $form = $(input).closest('form'),
            $input = $form.find('input[name=plugin_code]')

        if (!$input.length) {
            throw new Error('The input "plugin_code" should be defined in the form in order to use the localization table processor.')
        }

        return $input.val()
    }

    LocalizationProcessor.prototype.removeLocalizationInput = function() {
        if (!this.localizationInput) {
            return
        }

        this.localizationInput.dispose()

        this.localizationInput = null
    }

    $.oc.table.processor.builderLocalization = LocalizationProcessor;
}(window.jQuery);