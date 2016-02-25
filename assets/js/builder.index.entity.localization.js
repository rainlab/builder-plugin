/*
 * Builder Index controller Localization entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Localization = function(indexController) {
        Base.call(this, 'localization', indexController)
    }

    Localization.prototype = Object.create(BaseProto)
    Localization.prototype.constructor = Localization

    // PUBLIC METHODS
    // ============================

    Localization.prototype.cmdCreateLanguage = function(ev) {
        this.indexController.openOrLoadMasterTab($(ev.target), 'onLanguageCreateOrOpen', this.newTabId())
    }

    Localization.prototype.cmdOpenLanguage = function(ev) {
        var language = $(ev.currentTarget).data('id'),
            pluginCode = $(ev.currentTarget).data('pluginCode')

        this.indexController.openOrLoadMasterTab($(ev.target), 'onLanguageCreateOrOpen', this.makeTabId(pluginCode+'-'+language), {
            original_language: language
        })
    }

    Localization.prototype.cmdSaveLanguage = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form')

        $target.request('onLanguageSave').done(
            this.proxy(this.saveLanguageDone)
        )
    }

    Localization.prototype.cmdDeleteLanguage = function(ev) {
        var $target = $(ev.currentTarget)
        $.oc.confirm($target.data('confirm'), this.proxy(this.deleteConfirmed))
    }

    Localization.prototype.cmdCopyMissingStrings = function(ev) {
        var $form = $(ev.currentTarget),
            language = $form.find('select[name=language]').val(),
            $masterTabPane = this.getMasterTabsActivePane()

        $form.trigger('close.oc.popup')

        $.oc.stripeLoadIndicator.show()
        $masterTabPane.find('form').request('onLanguageCopyStringsFrom', {
            data: {
                copy_from: language
            }
        }).always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(
            this.proxy(this.copyStringsFromDone)
        )
    }

    // EVENT HANDLERS
    // ============================

    // INTERNAL BUILDER API
    // ============================

    Localization.prototype.languageUpdated = function(plugin) {
        var languageForm = this.findDefaultLanguageForm(plugin)

        if (!languageForm) {
            return
        }

        var $languageForm = $(languageForm)

        if (!$languageForm.hasClass('oc-data-changed')) {
            this.updateLanguageFromServer($languageForm)
        }
        else {
            // If there are changes - merge language from server
            // in the background. As this operation is not 100% 
            // reliable, it could be a good idea to display a
            // warning when the user navigates to the tab.

            this.mergeLanguageFromServer($languageForm)
        }
    }

    Localization.prototype.updateOnScreenStrings = function(plugin) {
        var stringElements = document.body.querySelectorAll('span[data-localization-key][data-plugin="'+plugin+'"]')

        $.oc.builder.dataRegistry.get($('#builder-plugin-selector-panel form'), plugin, 'localization', null, function(data){
            for (var i=stringElements.length-1; i>=0; i--) {
                var stringElement = stringElements[i],
                    stringKey = stringElement.getAttribute('data-localization-key')

                if (data[stringKey] !== undefined) {
                    stringElement.textContent = data[stringKey]
                }
                else {
                    stringElement.textContent = stringKey
                }
            }
        })
    }

    // INTERNAL METHODS
    // ============================

    Localization.prototype.saveLanguageDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()
        
        $masterTabPane.find('input[name=original_language]').val(data.builderResponseData.language)
        this.updateMasterTabIdAndTitle($masterTabPane, data.builderResponseData)
        this.unhideFormDeleteButton($masterTabPane)

        this.getLanguageList().fileList('markActive', data.builderResponseData.tabId)
        this.getIndexController().unchangeTab($masterTabPane)

        if (data.builderResponseData.registryData !== undefined) {
            var registryData = data.builderResponseData.registryData

            $.oc.builder.dataRegistry.set(registryData.pluginCode, 'localization', null, registryData.strings, {suppressLanguageEditorUpdate: true})
            $.oc.builder.dataRegistry.set(registryData.pluginCode, 'localization', 'sections', registryData.sections)
        }
    }

    Localization.prototype.getLanguageList = function() {
        return $('#layout-side-panel form[data-content-id=localization] [data-control=filelist]')
    }

    Localization.prototype.getCodeEditor = function($tab) {
        return $tab.find('div[data-field-name=strings] div[data-control=codeeditor]').data('oc.codeEditor').editor
    }

    Localization.prototype.deleteConfirmed = function() {
        var $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form')

        $.oc.stripeLoadIndicator.show()
        $form.request('onLanguageDelete').always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(
            this.proxy(this.deleteDone)
        )
    }

    Localization.prototype.deleteDone = function() {
        var $masterTabPane = this.getMasterTabsActivePane()

        this.getIndexController().unchangeTab($masterTabPane)
        this.forceCloseTab($masterTabPane)
    }

    Localization.prototype.copyStringsFromDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var responseData = data.builderResponseData,
            $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form'),
            codeEditor = this.getCodeEditor($masterTabPane),
            newStringMessage = $form.data('newStringMessage'),
            mismatchMessage = $form.data('structureMismatch')

        codeEditor.getSession().setValue(responseData.strings)

        var annotations = []
        for (var i=responseData.updatedLines.length-1; i>=0; i--) {
            var line = responseData.updatedLines[i]

            annotations.push({
                row: line, 
                column: 0,
                text: newStringMessage,
                type: 'warning'
            })
        }

        codeEditor.getSession().setAnnotations(annotations)

        if (responseData.mismatch) {
            $.oc.alert(mismatchMessage)
        }
    }

    Localization.prototype.findDefaultLanguageForm = function(plugin) {
        var forms = document.body.querySelectorAll('form[data-entity=localization]')

        for (var i=forms.length-1; i>=0; i--) {
            var form = forms[i],
                pluginInput = form.querySelector('input[name=plugin_code]'),
                languageInput = form.querySelector('input[name=original_language]')

            if (!pluginInput || pluginInput.value != plugin) {
                continue
            }

            if (!languageInput) {
                continue
            }

            if (form.getAttribute('data-default-language') == languageInput.value) {
                return form
            }
        }

        return null
    }

    Localization.prototype.updateLanguageFromServer = function($languageForm) {
        var self = this

        $languageForm.request('onLanguageGetStrings').done(function(data) {
            self.updateLanguageFromServerDone($languageForm, data)
        })
    }

    Localization.prototype.updateLanguageFromServerDone = function($languageForm, data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var responseData = data.builderResponseData,
            $tabPane = $languageForm.closest('.tab-pane'),
            codeEditor = this.getCodeEditor($tabPane)

        if (!responseData.strings) {
            return
        }

        codeEditor.getSession().setValue(responseData.strings)
        this.unmodifyTab($tabPane)
    }

    Localization.prototype.mergeLanguageFromServer = function($languageForm) {
        var language = $languageForm.find('input[name=original_language]').val(),
            self = this

        $languageForm.request('onLanguageCopyStringsFrom', {
            data: {
                copy_from: language
            }
        }).done(function(data) {
            self.mergeLanguageFromServerDone($languageForm, data)
        })
    }

    Localization.prototype.mergeLanguageFromServerDone = function($languageForm, data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var responseData = data.builderResponseData,
            $tabPane = $languageForm.closest('.tab-pane'),
            codeEditor = this.getCodeEditor($tabPane)

        codeEditor.getSession().setValue(responseData.strings)
        codeEditor.getSession().setAnnotations([])
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.localization = Localization;

}(window.jQuery);