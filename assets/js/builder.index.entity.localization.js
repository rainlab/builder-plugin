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


    // INTERNAL METHODS
    // ============================

    Localization.prototype.saveLanguageDone = function(data) {
        if (data['builderRepsonseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()
        
        $masterTabPane.find('input[name=original_language]').val(data.builderRepsonseData.language)
        this.updateMasterTabIdAndTitle($masterTabPane, data.builderRepsonseData)
        this.unhideFormDeleteButton($masterTabPane)

        this.getLanguageList().fileList('markActive', data.builderRepsonseData.tabId)
        this.getIndexController().unchangeTab($masterTabPane)
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
        if (data['builderRepsonseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var responseData = data.builderRepsonseData,
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

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.localization = Localization;

}(window.jQuery);