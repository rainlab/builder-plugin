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

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.localization = Localization;

}(window.jQuery);