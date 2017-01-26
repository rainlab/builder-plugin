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

        var result = this.indexController.openOrLoadMasterTab($link, 'onModelListCreateOrOpen', this.newTabId(), data)

        if (result !== false) {
            result.done(this.proxy(this.onListLoaded, this))
        }
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

        var result = this.indexController.openOrLoadMasterTab($(ev.target), 'onModelListCreateOrOpen', this.makeTabId(model+'-'+list), {
            file_name: list,
            model_class: model
        })

        if (result !== false) {
            result.done(this.proxy(this.onListLoaded, this))
        }
    }

    ModelList.prototype.cmdDeleteList = function(ev) {
        var $target = $(ev.currentTarget)
        $.oc.confirm($target.data('confirm'), this.proxy(this.deleteConfirmed))
    }

    ModelList.prototype.cmdAddDatabaseColumns = function(ev) {
        var $target = $(ev.currentTarget)

        $.oc.stripeLoadIndicator.show()
        $target.request('onModelListLoadDatabaseColumns').done(
            this.proxy(this.databaseColumnsLoaded)
        ).always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        )
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

    ModelList.prototype.databaseColumnsLoaded = function(data) {
        if (!$.isArray(data.responseData.columns)) {
            alert('Invalid server response')
        }
        
        var $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form'),
            existingColumns = this.getColumnNames($form),
            columnsAdded = false

        for (var i in data.responseData.columns) {
            var column = data.responseData.columns[i],
                type = this.mapType(column.type)

            if ($.inArray(column.name, existingColumns) !== -1) {
                continue
            }

            this.addColumn($form, column.name, type)
            columnsAdded = true
        }

        if (!columnsAdded) {
            alert($form.attr('data-lang-all-database-columns-exist'))
        }
        else {
            $form.trigger('change')
        }
    }

    ModelList.prototype.mapType = function(type) {
        switch (type) {
            case 'integer' : return 'number'
            case 'timestamp' : return 'datetime'
            default: return 'text'
        }
    }

    ModelList.prototype.addColumn = function($target, column, type) {
        var tableObj = this.getTableControlObject($target),
            currentData = this.getTableData($target),
            rowData = {
                field: column,
                label: column,
                type: type
            }

        tableObj.addRecord('bottom', true)
        tableObj.setRowValues(currentData.length-1, rowData)

        // Forces the table to apply values
        // from the data source
        tableObj.addRecord('bottom', false)
        tableObj.deleteRecord()
    }

    ModelList.prototype.getColumnNames = function($target) {
        var tableObj = this.getTableControlObject($target)

        tableObj.unfocusTable()

        var data = this.getTableData($target),
            result = []

        for (var index in data) {
            if (data[index].field !== undefined) {
                result.push($.trim(data[index].field))
            }
        }

        return result
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

    ModelList.prototype.onListLoaded = function() {
        $(document).trigger('render')

        var $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form'),
            $toolbar = $masterTabPane.find('div[data-control=table] div.toolbar'),
            $button = $('<a class="btn oc-icon-magic builder-custom-table-button" data-builder-command="modelList:cmdAddDatabaseColumns"></a>')

        $button.text($form.attr('data-lang-add-database-columns'));
        $toolbar.append($button)
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.modelList = ModelList;

}(window.jQuery);