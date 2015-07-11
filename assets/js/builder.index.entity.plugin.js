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

    var Plugin = function() {
        Base.call(this)
    }

    Plugin.prototype = Object.create(BaseProto)
    Plugin.prototype.constructor = Plugin

    // PUBLIC METHODS
    // ============================

    Plugin.prototype.cmdMakePluginActive = function(ev) {
        console.log('make active')
    }

    Plugin.prototype.cmdCreatePlugin = function(ev) {
        var $target = $(ev.target)

        $target.one('shown.oc.popup', this.proxy(this.onPluginPopupShown))

        $target.popup({
            handler: 'onPluginLoadPopup',
            zIndex: 5050 // This popup should be above the flyout overlay, which z-index is 5000
        })
    }

    // EVENT HANDLERS
    // ============================

    Plugin.prototype.onPluginPopupShown = function(ev, button, popup) {
        $(popup).find('input[name=name]').focus()
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.plugin = new Plugin()

}(window.jQuery);