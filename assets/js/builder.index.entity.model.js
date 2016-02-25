/*
 * Builder Index controller Model entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Model = function(indexController) {
        Base.call(this, 'model', indexController)
    }

    Model.prototype = Object.create(BaseProto)
    Model.prototype.constructor = Model

    // PUBLIC METHODS
    // ============================

    Model.prototype.cmdCreateModel = function(ev) {
        var $target = $(ev.currentTarget)

        $target.one('shown.oc.popup', this.proxy(this.onModelPopupShown))

        $target.popup({
            handler: 'onModelLoadPopup'
        })
    }

    Model.prototype.cmdApplyModelSettings = function(ev) {
        var $form = $(ev.currentTarget),
            self = this

        $.oc.stripeLoadIndicator.show()
        $form.request('onModelSave').always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(function(data){
            $form.trigger('close.oc.popup')

            self.applyModelSettingsDone(data)
        })
    }

    // EVENT HANDLERS
    // ============================

    Model.prototype.onModelPopupShown = function(ev, button, popup) {
        $(popup).find('input[name=className]').focus()
    }

    // INTERNAL METHODS
    // ============================

    Model.prototype.applyModelSettingsDone = function(data) {
        if (data.builderResponseData.registryData !== undefined) {
            var registryData = data.builderResponseData.registryData

            $.oc.builder.dataRegistry.set(registryData.pluginCode, 'model-classes', null, registryData.models)
        }
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.model = Model;

}(window.jQuery);