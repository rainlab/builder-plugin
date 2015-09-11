/*
 * Builder Index controller Model Form entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var ModelForm = function(indexController) {
        Base.call(this, 'modelForm', indexController)
    }

    ModelForm.prototype = Object.create(BaseProto)
    ModelForm.prototype.constructor = ModelForm

    // PUBLIC METHODS
    // ============================

    ModelForm.prototype.cmdCreateForm = function(ev) {
        this.indexController.openOrLoadMasterTab($(ev.target), 'onModelFormCreateOrOpen', this.newTabId())
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.modelForm = ModelForm;

}(window.jQuery);