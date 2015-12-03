/*
 * Builder Index controller Version entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Version = function(indexController) {
        Base.call(this, 'version', indexController)
    }

    Version.prototype = Object.create(BaseProto)
    Version.prototype.constructor = Version

    // PUBLIC METHODS
    // ============================

    Version.prototype.cmdCreateVersion = function(ev) {
        var $link = $(ev.currentTarget),
            versionType = $link.data('versionType')

        this.indexController.openOrLoadMasterTab($link, 'onVersionCreateOrOpen', this.newTabId(), {
            version_type: versionType
        })
    }

    Version.prototype.cmdSaveVersion = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form')

        $target.request('onVersionSave').done(
            this.proxy(this.saveVersionDone)
        )
    }

    Version.prototype.cmdOpenVersion = function(ev) {
        var versionNumber = $(ev.currentTarget).data('id'),
            pluginCode = $(ev.currentTarget).data('pluginCode')

        this.indexController.openOrLoadMasterTab($(ev.target), 'onVersionCreateOrOpen', this.makeTabId(pluginCode+'-'+versionNumber), {
            original_version: versionNumber
        })
    }

    Version.prototype.cmdDeleteVersion = function(ev) {
    }

    // INTERNAL METHODS
    // ============================

    Version.prototype.saveVersionDone = function(data) {
        if (data['builderRepsonseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()

        $masterTabPane.find('input[name=original_version]').val(data.builderRepsonseData.savedVersion)
        this.updateMasterTabIdAndTitle($masterTabPane, data.builderRepsonseData)
        this.unhideFormDeleteButton($masterTabPane)

        this.getVersionList().fileList('markActive', data.builderRepsonseData.tabId)
        this.getIndexController().unchangeTab($masterTabPane)
    }

    // Version.prototype.deleteDone = function() {
    //     var $masterTabPane = this.getMasterTabsActivePane()

    //     this.getIndexController().unchangeTab($masterTabPane)
    //     this.forceCloseTab($masterTabPane)
    // }

    Version.prototype.getVersionList = function() {
        return $('#layout-side-panel form[data-content-id=version] [data-control=filelist]')
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.version = Version;

}(window.jQuery);