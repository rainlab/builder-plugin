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
        var $form = $(ev.currentTarget).closest('form'),
            $rootContainer = $('[data-root-control-wrapper] > [data-contol-container]', $form), 
            $inspectorContainer = $form.find('.inspector-container'),
            controls = $.oc.builder.formbuilder.domToPropertyJson.convert($rootContainer.get(0))
            
        if (!$.oc.inspector.manager.applyValuesFromContainer($inspectorContainer)) {
            return 
        }

        if (controls === false) {
            $.oc.flashMsg({
                text: $.oc.builder.formbuilder.domToPropertyJson.getLastError(),
                'class': 'error', 
                'interval': 5})

            return
        }

        var data = {
                controls: controls
            }

        $.oc.stripeLoadIndicator.show()
        $form.request('onModelFormSave', {
            data: data
        }).always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(
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

    // INTERNAL METHODS
    // ============================

    ModelForm.prototype.saveFormDone = function(data) {
        if (data['builderRepsonseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()

        $masterTabPane.find('input[name=file_name]').val(data.builderRepsonseData.builderObjectName)
        this.updateMasterTabIdAndTitle($masterTabPane, data.builderRepsonseData)
        this.unhideFormDeleteButton($masterTabPane)

        this.getModelList().fileList('markActive', data.builderRepsonseData.tabId)
        this.getIndexController().unchageTab($masterTabPane)
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

    ModelForm.prototype.deleteDone = function() {
        var $masterTabPane = this.getMasterTabsActivePane()

        this.getIndexController().unchageTab($masterTabPane)
        this.forceCloseTab($masterTabPane)
    }

    ModelForm.prototype.getModelList = function() {
        return $('#layout-side-panel form[data-content-id=models] [data-control=filelist]')
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.modelForm = ModelForm;

}(window.jQuery);