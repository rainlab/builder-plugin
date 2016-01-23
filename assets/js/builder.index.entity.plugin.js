/*
 * Builder Index controller Plugin entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Plugin = function(indexController) {
        Base.call(this, 'plugin', indexController)

        this.popupZIndex = 5050 // This popup should be above the flyout overlay, which z-index is 5000
    }

    Plugin.prototype = Object.create(BaseProto)
    Plugin.prototype.constructor = Plugin

    // PUBLIC METHODS
    // ============================

    Plugin.prototype.cmdMakePluginActive = function(ev) {
        var $target = $(ev.currentTarget),
            selectedPluginCode = $target.data('pluginCode')

        this.makePluginActive(selectedPluginCode)
    }

    Plugin.prototype.cmdCreatePlugin = function(ev) {
        var $target = $(ev.currentTarget)

        $target.one('shown.oc.popup', this.proxy(this.onPluginPopupShown))

        $target.popup({
            handler: 'onPluginLoadPopup',
            zIndex: this.popupZIndex
        })
    }

    Plugin.prototype.cmdApplyPluginSettings = function(ev) {
        var $form = $(ev.currentTarget),
            self = this

        $.oc.stripeLoadIndicator.show()
        $form.request('onPluginSave').always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(function(data){
            $form.trigger('close.oc.popup')

            self.applyPluginSettingsDone(data)
        })
    }

    Plugin.prototype.cmdEditPluginSettings = function(ev) {
        var $target = $(ev.currentTarget)

        $target.one('shown.oc.popup', this.proxy(this.onPluginPopupShown))

        $target.popup({
            handler: 'onPluginLoadPopup',
            zIndex: this.popupZIndex,
            extraData: {
                pluginCode: $target.data('pluginCode')
            }
        })
    }

    // EVENT HANDLERS
    // ============================

    Plugin.prototype.onPluginPopupShown = function(ev, button, popup) {
        $(popup).find('input[name=name]').focus()
    }

    // INTERNAL METHODS
    // ============================

    Plugin.prototype.applyPluginSettingsDone = function(data) {
        if (data.responseData !== undefined && data.responseData.isNewPlugin !== undefined) {
            this.makePluginActive(data.responseData.pluginCode, true)
        }
    }

    Plugin.prototype.makePluginActive = function(pluginCode, updatePluginList) {
        var $form = $('#builder-plugin-selector-panel form').first()

        $.oc.stripeLoadIndicator.show()
        $form.request('onPluginSetActive', {
            data: {
                pluginCode: pluginCode,
                updatePluginList: (updatePluginList ? 1 : 0)
            }
        }).always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(
            this.proxy(this.makePluginActiveDone)
        )
    }

    Plugin.prototype.makePluginActiveDone = function(data) {
        var pluginCode = data.responseData.pluginCode

        $('#builder-plugin-selector-panel [data-control=filelist]').fileList('markActive', pluginCode)
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.plugin = Plugin;

}(window.jQuery);