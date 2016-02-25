/*
 * Builder Index controller Controller entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Controller = function(indexController) {
        Base.call(this, 'controller', indexController)
    }

    Controller.prototype = Object.create(BaseProto)
    Controller.prototype.constructor = Controller

    // PUBLIC METHODS
    // ============================

    Controller.prototype.cmdCreateController = function(ev) {
        var $form = $(ev.currentTarget),
            self = this,
            pluginCode = $form.data('pluginCode'),
            behaviorsSelected = $form.find('input[name="behaviors[]"]:checked').length,
            promise = null

        // If behaviors were selected, open a new tab after the 
        // controller is saved. Otherwise just update the controller
        // list.
        if (behaviorsSelected) {
            promise = this.indexController.openOrLoadMasterTab(
                $form, 
                'onControllerCreate', 
                this.makeTabId(pluginCode+'-new-controller'), 
                {}
            )
        }
        else {
            promise = $form.request('onControllerCreate')
        }

        promise.done(function(data){
            $form.trigger('close.oc.popup')
            self.updateDataRegistry(data)
        }).always($.oc.builder.indexController.hideStripeIndicatorProxy)
    }

    Controller.prototype.cmdOpenController = function(ev) {
        var controller = $(ev.currentTarget).data('id'),
            pluginCode = $(ev.currentTarget).data('pluginCode')

        this.indexController.openOrLoadMasterTab($(ev.target), 'onControllerOpen', this.makeTabId(pluginCode+'-'+controller), {
            controller: controller
        })
    }

    Controller.prototype.cmdSaveController = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form'),
            $inspectorContainer = $form.find('.inspector-container')

        if (!$.oc.inspector.manager.applyValuesFromContainer($inspectorContainer)) {
            return
        }

        $target.request('onControllerSave').done(
            this.proxy(this.saveControllerDone)
        )
    }

    // EVENT HANDLERS
    // ============================

    // INTERNAL METHODS
    // ============================

    Controller.prototype.saveControllerDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()
        
        this.getIndexController().unchangeTab($masterTabPane)
    }

    Controller.prototype.updateDataRegistry = function(data) {
        if (data.builderResponseData.registryData !== undefined) {
            var registryData = data.builderResponseData.registryData

            $.oc.builder.dataRegistry.set(registryData.pluginCode, 'controller-urls', null, registryData.urls)
        }
    }

    Controller.prototype.getControllerList = function() {
        return $('#layout-side-panel form[data-content-id=controller] [data-control=filelist]')
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.controller = Controller;

}(window.jQuery);