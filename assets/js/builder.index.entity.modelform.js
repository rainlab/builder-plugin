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
        var $link = $(ev.currentTarget),
            data = {
                model_class: $link.data('modelClass')
            }

        this.indexController.openOrLoadMasterTab($link, 'onModelFormCreateOrOpen', this.newTabId(), data)
    }

    ModelForm.prototype.cmdSaveForm = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form'),
            $rootContainer = $('[data-root-control-wrapper] > [data-control-container]', $form), 
            $inspectorContainer = $form.find('.inspector-container'),
            controls = $.oc.builder.formbuilder.domToPropertyJson.convert($rootContainer.get(0))

        if (!$.oc.inspector.manager.applyValuesFromContainer($inspectorContainer)) {
            return
        }

        if (controls === false) {
            $.oc.flashMsg({
                'text': $.oc.builder.formbuilder.domToPropertyJson.getLastError(),
                'class': 'error',
                'interval': 5
            })

            return
        }

        var data = {
                controls: controls
            }

        $target.request('onModelFormSave', {
            data: data
        }).done(
            this.proxy(this.saveFormDone)
        )
    }

    ModelForm.prototype.cmdOpenForm = function(ev) {
        var form = $(ev.currentTarget).data('form'),
            model = $(ev.currentTarget).data('modelClass')

        this.indexController.openOrLoadMasterTab($(ev.target), 'onModelFormCreateOrOpen', this.makeTabId(model+'-'+form), {
            file_name: form,
            model_class: model
        })
    }

    ModelForm.prototype.cmdDeleteForm = function(ev) {
        var $target = $(ev.currentTarget)
        $.oc.confirm($target.data('confirm'), this.proxy(this.deleteConfirmed))
    }

    ModelForm.prototype.cmdAddControl = function(ev) {
        $.oc.builder.formbuilder.controlPalette.addControl(ev)
    }

    ModelForm.prototype.cmdUndockControlPalette = function(ev) {
        $.oc.builder.formbuilder.controlPalette.undockFromContainer(ev)
    }

    ModelForm.prototype.cmdDockControlPalette = function(ev) {
        $.oc.builder.formbuilder.controlPalette.dockToContainer(ev)
    }

    ModelForm.prototype.cmdCloseControlPalette = function(ev) {
        $.oc.builder.formbuilder.controlPalette.closeInContainer(ev)
    }

    // INTERNAL METHODS
    // ============================

    ModelForm.prototype.saveFormDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()

        $masterTabPane.find('input[name=file_name]').val(data.builderResponseData.builderObjectName)
        this.updateMasterTabIdAndTitle($masterTabPane, data.builderResponseData)
        this.unhideFormDeleteButton($masterTabPane)

        this.getModelList().fileList('markActive', data.builderResponseData.tabId)
        this.getIndexController().unchangeTab($masterTabPane)

        this.updateDataRegistry(data)
    }

    ModelForm.prototype.updateDataRegistry = function(data) {
        if (data.builderResponseData.registryData !== undefined) {
            var registryData = data.builderResponseData.registryData

            $.oc.builder.dataRegistry.set(registryData.pluginCode, 'model-forms', registryData.modelClass, registryData.forms)
        }
    }

    ModelForm.prototype.deleteConfirmed = function() {
        var $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form')

        $.oc.stripeLoadIndicator.show()
        $form.request('onModelFormDelete').always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(
            this.proxy(this.deleteDone)
        )
    }

    ModelForm.prototype.deleteDone = function(data) {
        var $masterTabPane = this.getMasterTabsActivePane()

        this.getIndexController().unchangeTab($masterTabPane)
        this.forceCloseTab($masterTabPane)

        this.updateDataRegistry(data)
    }

    ModelForm.prototype.getModelList = function() {
        return $('#layout-side-panel form[data-content-id=models] [data-control=filelist]')
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.modelForm = ModelForm;

}(window.jQuery);