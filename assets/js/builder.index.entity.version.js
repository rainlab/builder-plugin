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

        this.hiddenHints = {}
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
        var $target = $(ev.currentTarget)
        $.oc.confirm($target.data('confirm'), this.proxy(this.deleteConfirmed))
    }

    Version.prototype.cmdApplyVersion = function(ev) {
        var $target = $(ev.currentTarget),
            $pane  = $target.closest('div.tab-pane'),
            self = this

        this.showHintPopup($pane, 'builder-version-apply', function(){
            $target.request('onVersionApply').done(
                self.proxy(self.applyVersionDone)
            )
        })
    }

    Version.prototype.cmdRollbackVersion = function(ev) {
        var $target = $(ev.currentTarget),
            $pane  = $target.closest('div.tab-pane'),
            self = this


        this.showHintPopup($pane, 'builder-version-rollback', function(){
            $target.request('onVersionRollback').done(
                self.proxy(self.rollbackVersionDone)
            )
        })
    }

    // INTERNAL METHODS
    // ============================

    Version.prototype.saveVersionDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()
        this.updateUiAfterSave($masterTabPane, data)

        if (!data.builderResponseData.isApplied) {
            this.showSavedNotAppliedHint($masterTabPane)
        }
    }

    Version.prototype.showSavedNotAppliedHint = function($masterTabPane) {
        this.showHintPopup($masterTabPane, 'builder-version-save-unapplied')
    }

    Version.prototype.showHintPopup = function($masterTabPane, code, callback) {
        if (this.getDontShowHintAgain(code, $masterTabPane)) {
            if (callback) {
                callback.apply(this)
            }

            return
        }

        $masterTabPane.one('hide.oc.popup', this.proxy(this.onHintPopupHide))

        if (callback) {
            $masterTabPane.one('shown.oc.popup', function(ev, $element, $modal) {
                $modal.find('form').one('submit', function(ev) {
                    callback.apply(this)
                    ev.preventDefault()

                    $(ev.target).trigger('close.oc.popup')

                    return false
                })
            })
        }

        $masterTabPane.popup({
            content: this.getPopupContent($masterTabPane, code)
        })
    }

    Version.prototype.onHintPopupHide = function(ev, $element, $modal) {
        var cbValue = $modal.find('input[type=checkbox][name=dont_show_again]').is(':checked'),
            code = $modal.find('input[type=hidden][name=hint_code]').val()

        $modal.find('form').off('submit')

        if (!cbValue) {
            return
        }

        var $form = this.getMasterTabsActivePane().find('form[data-entity="versions"]')

        $form.request('onHideBackendHint', {
            data: {
                name: code
            }
        })

        this.setDontShowHintAgain(code)
    }

    Version.prototype.setDontShowHintAgain = function(code) {
        this.hiddenHints[code] = true
    }

    Version.prototype.getDontShowHintAgain = function(code, $pane) {
        if (this.hiddenHints[code] !== undefined) {
            return this.hiddenHints[code]
        }

        return $pane.find('input[type=hidden][data-hint-hidden="'+code+'"]').val() == "true"
    }

    Version.prototype.getPopupContent = function($pane, code) {
        var template = $pane.find('script[data-version-hint-template="'+code+'"]')

        if (template.length === 0) {
            throw new Error('Version popup template not found: '+code)
        }

        return template.html()
    }

    Version.prototype.updateUiAfterSave = function($masterTabPane, data) {
        $masterTabPane.find('input[name=original_version]').val(data.builderResponseData.savedVersion)
        this.updateMasterTabIdAndTitle($masterTabPane, data.builderResponseData)
        this.unhideFormDeleteButton($masterTabPane)

        this.getVersionList().fileList('markActive', data.builderResponseData.tabId)
        this.getIndexController().unchangeTab($masterTabPane)
    }

    Version.prototype.deleteConfirmed = function() {
        var $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form')

        $.oc.stripeLoadIndicator.show()
        $form.request('onVersionDelete').always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(
            this.proxy(this.deleteDone)
        )
    }

    Version.prototype.deleteDone = function() {
        var $masterTabPane = this.getMasterTabsActivePane()

        this.getIndexController().unchangeTab($masterTabPane)
        this.forceCloseTab($masterTabPane)
    }

    Version.prototype.applyVersionDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()

        this.updateUiAfterSave($masterTabPane, data)

        this.updateVersionsButtons()
    }

    Version.prototype.rollbackVersionDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()

        this.updateUiAfterSave($masterTabPane, data)

        this.updateVersionsButtons()
    }

    Version.prototype.getVersionList = function() {
        return $('#layout-side-panel form[data-content-id=version] [data-control=filelist]')
    }

    Version.prototype.updateVersionsButtons = function() {
        var tabsObject = this.getMasterTabsObject(),
            $tabs = tabsObject.$tabsContainer.find('> li'),
            $versionList = this.getVersionList()

        // Find all version tabs and update Apply and Rollback buttons
        // basing on the version statuses in the version list.
        for (var i=$tabs.length-1; i>=0; i--) {
            var $tab = $($tabs[i]),
                tabId = $tab.data('tabId')

            if (!tabId || String(tabId).length == 0) {
                continue
            }

            var $versionLi = $versionList.find('li[data-id="'+tabId+'"]')
            if (!$versionLi.length) {
                continue
            }

            var isApplied = $versionLi.data('applied'),
                $pane = tabsObject.findPaneFromTab($tab)

            if (isApplied) {
                $pane.find('[data-builder-command="version:cmdApplyVersion"]').addClass('hide')
                $pane.find('[data-builder-command="version:cmdRollbackVersion"]').removeClass('hide')
            }
            else {
                $pane.find('[data-builder-command="version:cmdApplyVersion"]').removeClass('hide')
                $pane.find('[data-builder-command="version:cmdRollbackVersion"]').addClass('hide')
            }
        }

    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.version = Version;

}(window.jQuery);