/*
 * Builder Index controller Permission entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Permission = function(indexController) {
        Base.call(this, 'permission', indexController)
    }

    Permission.prototype = Object.create(BaseProto)
    Permission.prototype.constructor = Permission

    // PUBLIC METHODS
    // ============================

    Permission.prototype.cmdOpenPermissions = function(ev) {
        this.indexController.openOrLoadMasterTab($(ev.target), 'onPermissionsOpen', this.makeTabId('plugin-permissions'))
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.permission = Permission;

}(window.jQuery);