/*
 * Builder localization input control
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var LocalizationInput = function(input, form, options) {
        this.input = input
        this.form = form
        this.options = $.extend({}, LocalizationInput.DEFAULTS, options)
        this.disposed = false
        this.initialized = false
        this.newStringPopupMarkup = null

        Base.call(this)

        this.init()
    }

    LocalizationInput.prototype = Object.create(BaseProto)
    LocalizationInput.prototype.constructor = LocalizationInput

    LocalizationInput.prototype.dispose = function() {
        this.unregisterHandlers()

        this.form = null
        this.options = null
        this.disposed = true
        this.newStringPopupMarkup = null

        if (this.initialized) {
            $(this.input).autocomplete('destroy')
        }

        this.input = null

        BaseProto.dispose.call(this)
    }

    LocalizationInput.prototype.init = function() {
        if (!this.options.plugin) {
            throw new Error('The options.plugin value should be set in the localization input object.')
        }

        this.getContainer().addClass('localization-input-container')

        this.registerHandlers()
        this.loadDataAndBuild()
    }

    LocalizationInput.prototype.buildAddLink = function() {
        var $container = this.getContainer()

        if ($container.find('a.localization-trigger').length > 0) {
            return
        }

        var trigger = document.createElement('a')

        trigger.setAttribute('class', 'oc-icon-plus localization-trigger')
        trigger.setAttribute('href', '#')

        var pos = $container.position()
        $(trigger).css({
            top: pos.top + 4,
            right: 7
        })

        $container.append(trigger)
    }

    LocalizationInput.prototype.loadDataAndBuild = function() {
        this.showLoadingIndicator()

        var result = $.oc.builder.dataRegistry.get(this.form, this.options.plugin, 'localization', null, this.proxy(this.dataLoaded)),
            self = this

        if (result) {
            result.always(function(){
                self.hideLoadingIndicator()
            })
        }
    }

    LocalizationInput.prototype.dataLoaded = function(data) {
        if (this.disposed) {
            return
        }

        this.hideLoadingIndicator()

        $(this.input).autocomplete({
            source: data,
            matchWidth: true
        })

        this.initialized = true
    }

    LocalizationInput.prototype.getContainer = function() {
        return $(this.input).closest('.autocomplete-container')
    }

    LocalizationInput.prototype.showLoadingIndicator = function() {
        var $container = this.getContainer()

        $container.addClass('loading-indicator-container size-small')
        $container.loadIndicator()
    }

    LocalizationInput.prototype.hideLoadingIndicator = function() {
        var $container = this.getContainer()

        $container.loadIndicator('hide')
        $container.loadIndicator('destroy')

        $container.removeClass('loading-indicator-container')
    }

    // POPUP
    // ============================

    LocalizationInput.prototype.loadAndShowPopup = function() {
        if (this.newStringPopupMarkup === null) {
            $.oc.stripeLoadIndicator.show()
            $(this.input).request('onLanguageLoadAddStringForm')
                .done(
                    this.proxy(this.popupMarkupLoaded)
                ).always(function(){
                    $.oc.stripeLoadIndicator.hide()
                })
        }
        else {
            this.showPopup()
        }
    }

    LocalizationInput.prototype.popupMarkupLoaded = function(responseData) {
        this.newStringPopupMarkup = responseData.markup

        this.showPopup()
    }

    LocalizationInput.prototype.showPopup = function() {
        var $input = $(this.input)

        $input.popup({
            content: this.newStringPopupMarkup
        })

        var $content = $input.data('oc.popup').$content,
            $keyInput = $content.find('#language_string_key')

        $.oc.builder.dataRegistry.get(this.form, this.options.plugin, 'localization', 'sections', function(data){
            $keyInput.autocomplete({
                source: data,
                matchWidth: true
            })
        })

        $content.find('form').on('submit', this.proxy(this.onSubmitPopupForm))
    }

    LocalizationInput.prototype.stringCreated = function(data) {
        if (data.localizationData === undefined || data.registryData === undefined) {
            throw new Error('Invalid server response.')
        }
        
        var $input = $(this.input)

        $input.val(data.localizationData.key)

        $.oc.builder.dataRegistry.set(this.options.plugin, 'localization', null, data.registryData.strings)
        $.oc.builder.dataRegistry.set(this.options.plugin, 'localization', 'sections', data.registryData.sections)

        $input.data('oc.popup').hide()

        $input.trigger('change')
    }

    LocalizationInput.prototype.onSubmitPopupForm = function(ev) {
        var $form = $(ev.target)

        $.oc.stripeLoadIndicator.show()
        $form.request('onLanguageCreateString', {
            data: {
                plugin_code: this.options.plugin
            }
        })
        .done(
            this.proxy(this.stringCreated)
        ).always(function(){
            $.oc.stripeLoadIndicator.hide()
        })

        ev.preventDefault()
        return false
    }

    LocalizationInput.prototype.onPopupHidden = function(ev, link, popup) {
        $(popup).find('#language_string_key').autocomplete('destroy')
        $(popup).find('form').on('submit', this.proxy(this.onSubmitPopupForm))
    }

    // EVENT HANDLERS
    // ============================

    LocalizationInput.prototype.unregisterHandlers = function() {
        this.input.removeEventListener('focus', this.proxy(this.onInputFocus))

        this.getContainer().off('click', 'a.localization-trigger', this.proxy(this.onTriggerClick))
        $(this.input).off('hidden.oc.popup', this.proxy(this.onPopupHidden))
    }

    LocalizationInput.prototype.registerHandlers = function() {
        this.input.addEventListener('focus', this.proxy(this.onInputFocus))

        this.getContainer().on('click', 'a.localization-trigger', this.proxy(this.onTriggerClick))
        $(this.input).on('hidden.oc.popup', this.proxy(this.onPopupHidden))
    }

    LocalizationInput.prototype.onInputFocus = function() {
        this.buildAddLink()
    }

    LocalizationInput.prototype.onTriggerClick = function(ev) {
        this.loadAndShowPopup()

        ev.preventDefault()
        return false
    }

    LocalizationInput.DEFAULTS = {
        plugin: null
    }

    $.oc.builder.localizationInput = LocalizationInput

}(window.jQuery);