/*
 * Builder Index controller Menus entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Menus = function(indexController) {
        Base.call(this, 'menu', indexController)
    }

    Menus.prototype = Object.create(BaseProto)
    Menus.prototype.constructor = Menus

    // PUBLIC METHODS
    // ============================

    Menus.prototype.cmdOpenMenus = function(ev) {
        this.indexController.openOrLoadMasterTab($(ev.target), 'onMenusOpen', this.makeTabId('plugin-menus'))
    }

    Menus.prototype.cmdSaveMenus = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form')

        if (!this.validateTable($target)) {
            return
        }

        $target.request('onMenusSave', {
        }).done(
            this.proxy(this.saveMenusDone)
        )
    }

    // INTERNAL METHODS
    // ============================

    Menus.prototype.saveMenusDone = function(data) {
        if (data['builderRepsonseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()

        this.getIndexController().unchangeTab($masterTabPane)
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.menus = Menus;

}(window.jQuery);