/*
 * Builder Index controller Model List entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var ModelList = function(indexController) {
        this.cachedModelFieldsPromises = {}

        Base.call(this, 'modelList', indexController)
    }

    ModelList.prototype = Object.create(BaseProto)
    ModelList.prototype.constructor = ModelList

    ModelList.prototype.registerHandlers = function() {
        $(document).on('autocompleteitems.oc.table', 'form[data-sub-entity="model-list"] [data-control=table]', this.proxy(this.onAutocompleteItems))
    }

    // PUBLIC METHODS
    // ============================

    ModelList.prototype.cmdCreateList = function(ev) {
        var $link = $(ev.currentTarget),
            data = {
                model_class: $link.data('modelClass')
            }

        this.indexController.openOrLoadMasterTab($link, 'onModelListCreateOrOpen', this.newTabId(), data)
    }

    ModelList.prototype.cmdSaveList = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form')

        if (!this.validateTable($target)) {
            return
        }

        $target.request('onModelListSave', {
            data: {
                columns: this.getTableData($target)
            }
        }).done(
            this.proxy(this.saveListDone)
        )
    }

    ModelList.prototype.cmdOpenList = function(ev) {
        var list = $(ev.currentTarget).data('list'),
            model = $(ev.currentTarget).data('modelClass')

        this.indexController.openOrLoadMasterTab($(ev.target), 'onModelListCreateOrOpen', this.makeTabId(model+'-'+list), {
            file_name: list,
            model_class: model
        })
    }

    ModelList.prototype.cmdDeleteList = function(ev) {
        var $target = $(ev.currentTarget)
        $.oc.confirm($target.data('confirm'), this.proxy(this.deleteConfirmed))
    }

    // INTERNAL METHODS
    // ============================

    ModelList.prototype.saveListDone = function(data) {
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

    ModelList.prototype.deleteConfirmed = function() {
        var $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form')

        $.oc.stripeLoadIndicator.show()
        $form.request('onModelListDelete').always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(
            this.proxy(this.deleteDone)
        )
    }

    ModelList.prototype.deleteDone = function(data) {
        var $masterTabPane = this.getMasterTabsActivePane()

        this.getIndexController().unchangeTab($masterTabPane)
        this.forceCloseTab($masterTabPane)

        this.updateDataRegistry(data)
    }

    ModelList.prototype.getTableControlObject = function($target) {
        var $form = $target.closest('form'),
            $table = $form.find('[data-control=table]'),
            tableObj = $table.data('oc.table')

        if (!tableObj) {
            throw new Error('Table object is not found on the model list tab')
        }

        return tableObj
    }

    ModelList.prototype.getModelList = function() {
        return $('#layout-side-panel form[data-content-id=models] [data-control=filelist]')
    }

    ModelList.prototype.validateTable = function($target) {
        var tableObj = this.getTableControlObject($target)

        tableObj.unfocusTable()
        return tableObj.validate()
    }

    ModelList.prototype.getTableData = function($target) {
        var tableObj = this.getTableControlObject($target)

        return tableObj.dataSource.getAllData()
    }

    ModelList.prototype.loadModelFields = function(table, callback) {
        var $form = $(table).closest('form'),
            modelClass = $form.find('input[name=model_class]').val(),
            cachedFields = $form.data('oc.model-field-cache')

        if (cachedFields !== undefined) {
            callback(cachedFields)

            return
        }

        if (this.cachedModelFieldsPromises[modelClass] === undefined) {
            this.cachedModelFieldsPromises[modelClass] = $form.request('onModelFormGetModelFields', {
                data: {
                    'as_plain_list': 1
                }
            })
        }

        if (callback === undefined) {
            return
        }

        this.cachedModelFieldsPromises[modelClass].done(function(data){
            $form.data('oc.model-field-cache', data.responseData.options)

            callback(data.responseData.options)
        })
    }

    ModelList.prototype.updateDataRegistry = function(data) {
        if (data.builderResponseData.registryData !== undefined) {
            var registryData = data.builderResponseData.registryData

            $.oc.builder.dataRegistry.set(registryData.pluginCode, 'model-lists', registryData.modelClass, registryData.lists)

            $.oc.builder.dataRegistry.clearCache(registryData.pluginCode, 'plugin-lists')
        }
    }

    // EVENT HANDLERS
    // ============================

    ModelList.prototype.onAutocompleteItems = function(ev, data) {
        if (data.columnConfiguration.fillFrom === 'model-fields') {
            ev.preventDefault()

            this.loadModelFields(ev.target, data.callback)

            return false
        }
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.modelList = ModelList;

}(window.jQuery);