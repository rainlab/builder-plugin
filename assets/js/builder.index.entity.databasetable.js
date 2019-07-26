/*
 * Builder Index controller Database Table entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var DatabaseTable = function(indexController) {
        Base.call(this, 'databaseTable', indexController)
    }

    DatabaseTable.prototype = Object.create(BaseProto)
    DatabaseTable.prototype.constructor = DatabaseTable

    // PUBLIC METHODS
    // ============================

    DatabaseTable.prototype.cmdCreateTable = function(ev) {
        var result = this.indexController.openOrLoadMasterTab($(ev.target), 'onDatabaseTableCreateOrOpen', this.newTabId())

        if (result !== false) {
            result.done(this.proxy(this.onTableLoaded, this))
        }
    }

    DatabaseTable.prototype.cmdOpenTable = function(ev) {
        var table = $(ev.currentTarget).data('id'),
            result = this.indexController.openOrLoadMasterTab($(ev.target), 'onDatabaseTableCreateOrOpen', this.makeTabId(table), {
                table_name: table
            })

        if (result !== false) {
            result.done(this.proxy(this.onTableLoaded, this))
        }
    }

    DatabaseTable.prototype.cmdSaveTable = function(ev) {
        var $target = $(ev.currentTarget)

        // The process of saving a database table:
        // - validate client-side
        // - validate columns on the server
        // - display a popup asking to enter the migration text
        // - generate the migration on the server and execute it
        // - drop the form modified flag

        if (!this.validateTable($target)) {
            return
        }

        var data = {
            'columns': this.getTableData($target)
        }

        $target.popup({
            extraData: data,
            handler: 'onDatabaseTableValidateAndShowPopup'
        })
    }

    DatabaseTable.prototype.cmdSaveMigration = function(ev) {
        var $target = $(ev.currentTarget)

        $.oc.stripeLoadIndicator.show()
        $target.request('onDatabaseTableMigrationApply').always(
            $.oc.builder.indexController.hideStripeIndicatorProxy
        ).done(
            this.proxy(this.saveMigrationDone)
        )
    }

    DatabaseTable.prototype.cmdDeleteTable = function(ev) {
        var $target = $(ev.currentTarget)
        $.oc.confirm($target.data('confirm'), this.proxy(this.deleteConfirmed))
    }

    DatabaseTable.prototype.cmdUnModifyForm = function() {
        var $masterTabPane = this.getMasterTabsActivePane()
        this.unmodifyTab($masterTabPane)
    }

    DatabaseTable.prototype.cmdAddIdColumn = function(ev) {
        var $target = $(ev.currentTarget),
            added = this.addIdColumn($target)

        if (!added) {
            alert($target.closest('form').attr('data-lang-id-exists'))
        }
    }

    DatabaseTable.prototype.cmdAddTimestamps = function(ev) {
        var $target = $(ev.currentTarget),
            added = this.addTimeStampColumns($target, ['created_at', 'updated_at'])

        if (!added) {
            alert($target.closest('form').attr('data-lang-timestamps-exist'))
        }
    }

    DatabaseTable.prototype.cmdAddSoftDelete = function(ev) {
        var $target = $(ev.currentTarget),
            added = this.addTimeStampColumns($target, ['deleted_at'])

        if (!added) {
            alert($target.closest('form').attr('data-lang-soft-deleting-exist'))
        }
    }

    // EVENT HANDLERS
    // ============================

    DatabaseTable.prototype.onTableCellChanged = function(ev, column, value, rowIndex) {
        var $target = $(ev.target)

        if ($target.data('alias') != 'columns') {
            return
        }

        if ($target.closest('form').data('entity') != 'database') {
            return
        }

        // Some migration-related rules are enforced here:
        //
        // 1. Checking Autoincrement checkbox automatically checks the Unsigned checkbox (this corresponds to the 
        //    logic internally implemented in Laravel schema builder) and PK
        // 2. Unchecking Unsigned unchecks Autoincrement
        // 3. Checking the PK column unchecks Nullable
        // 4. Checking Nullable unchecks PK
        // 6. Unchecking the PK unchecks Autoincrement

        var updatedRow = {}

        if (column == 'auto_increment' && value) {
            updatedRow.unsigned = 1
            updatedRow.primary_key = 1
        }

        if (column == 'unsigned' && !value) {
            updatedRow.auto_increment = 0
        }

        if (column == 'primary_key' && value) {
            updatedRow.allow_null = 0
        }

        if (column == 'allow_null' && value) {
            updatedRow.primary_key = 0
        }

        if (column == 'primary_key' && !value) {
            updatedRow.auto_increment = 0
        }

        $target.table('setRowValues', rowIndex, updatedRow)
    }

    DatabaseTable.prototype.onTableLoaded = function() {
        $(document).trigger('render')

        var $masterTabPane = this.getMasterTabsActivePane(),
            $form = $masterTabPane.find('form'),
            $toolbar = $masterTabPane.find('div[data-control=table] div.toolbar'),
            $addIdButton = $('<a class="btn oc-icon-clock-o builder-custom-table-button" data-builder-command="databaseTable:cmdAddIdColumn"></a>'),
            $addTimestampsButton = $('<a class="btn oc-icon-clock-o builder-custom-table-button" data-builder-command="databaseTable:cmdAddTimestamps"></a>'),
            $addSoftDeleteButton = $('<a class="btn oc-icon-refresh builder-custom-table-button" data-builder-command="databaseTable:cmdAddSoftDelete"></a>')

        $addIdButton.text($form.attr('data-lang-add-id'));
        $toolbar.append($addIdButton)

        $addTimestampsButton.text($form.attr('data-lang-add-timestamps'));
        $toolbar.append($addTimestampsButton)

        $addSoftDeleteButton.text($form.attr('data-lang-add-soft-delete'));
        $toolbar.append($addSoftDeleteButton)
    }

    // INTERNAL METHODS
    // ============================

    DatabaseTable.prototype.registerHandlers = function() {
        this.indexController.$masterTabs.on('oc.tableCellChanged', this.proxy(this.onTableCellChanged))
    }

    DatabaseTable.prototype.validateTable = function($target) {
        var tableObj = this.getTableControlObject($target)

        tableObj.unfocusTable()
        return tableObj.validate()
    }

    DatabaseTable.prototype.getTableData = function($target) {
        var tableObj = this.getTableControlObject($target)

        return tableObj.dataSource.getAllData()
    }

    DatabaseTable.prototype.getTableControlObject = function($target) {
        var $form = $target.closest('form'),
            $table = $form.find('[data-control=table]'),
            tableObj = $table.data('oc.table')

        if (!tableObj) {
            throw new Error('Table object is not found on the database table tab')
        }

        return tableObj
    }

    DatabaseTable.prototype.saveMigrationDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        $('#builderTableMigrationPopup').trigger('close.oc.popup')

        var $masterTabPane = this.getMasterTabsActivePane(),
            tabsObject = this.getMasterTabsObject()

        if (data.builderResponseData.operation != 'delete') {
            $masterTabPane.find('input[name=table_name]').val(data.builderResponseData.builderObjectName)
            this.updateMasterTabIdAndTitle($masterTabPane, data.builderResponseData)
            this.unhideFormDeleteButton($masterTabPane)

            this.getTableList().fileList('markActive', data.builderResponseData.tabId)
            this.getIndexController().unchangeTab($masterTabPane)

            this.updateTable(data.builderResponseData)
        }
        else {
            this.forceCloseTab($masterTabPane)
        }

        $.oc.builder.dataRegistry.clearCache(data.builderResponseData.pluginCode, 'model-columns')
    }

    DatabaseTable.prototype.getTableList = function() {
        return $('#layout-side-panel form[data-content-id=database] [data-control=filelist]')
    }

    DatabaseTable.prototype.deleteConfirmed = function() {
        var $masterTabPane = this.getMasterTabsActivePane()

        $masterTabPane.find('form').popup({
            handler: 'onDatabaseTableShowDeletePopup'
        })
    }

    DatabaseTable.prototype.getColumnNames = function($target) {
        var tableObj = this.getTableControlObject($target)

        tableObj.unfocusTable()

        var data = this.getTableData($target),
            result = []

        for (var index in data) {
            if (data[index].name !== undefined) {
                result.push($.trim(data[index].name))
            }
        }

        return result
    }

    DatabaseTable.prototype.addIdColumn = function($target) {
        var existingColumns = this.getColumnNames($target),
            added = false

        if (existingColumns.indexOf('id') === -1) {
            var tableObj = this.getTableControlObject($target),
                currentData = this.getTableData($target),
                rowData = {
                    name: 'id',
                    type: 'integer',
                    unsigned: true,
                    auto_increment: true,
                    primary_key: true,
                }

            if (currentData.length - 1 || currentData[0].name || currentData[0].type || currentData[0].length || currentData[0].unsigned || currentData[0].nullable || currentData[0].auto_increment || currentData[0].primary_key || currentData[0].default) {
                tableObj.addRecord('bottom', true)
            }

            tableObj.setRowValues(currentData.length - 1, rowData)

            // Forces the table to apply values
            // from the data source
            tableObj.addRecord('bottom', false)
            tableObj.deleteRecord()

            added = true
        }

        if (added) {
            $target.trigger('change')
        }

        return added
    }

    DatabaseTable.prototype.addTimeStampColumns = function($target, columns)
    {
        var existingColumns = this.getColumnNames($target),
            added = false

        for (var index in columns) {
            var column = columns[index]

            if (existingColumns.indexOf(column) === -1) {
                this.addTimeStampColumn($target, column)
                added = true
            }
        }

        if (added) {
            $target.trigger('change')
        }

        return added
    }

    DatabaseTable.prototype.addTimeStampColumn = function($target, column) {
        var tableObj = this.getTableControlObject($target),
            currentData = this.getTableData($target),
            rowData = {
                name: column,
                type: 'timestamp',
                'default': null,
                allow_null: true // Simplifies the case when a timestamp is added to a table with data
            }

        tableObj.addRecord('bottom', true)
        tableObj.setRowValues(currentData.length - 1, rowData)

        // Forces the table to apply values
        // from the data source
        tableObj.addRecord('bottom', false)
        tableObj.deleteRecord()
    }

    DatabaseTable.prototype.updateTable = function(data) {
        var tabsObject = this.getMasterTabsObject(),
            tabs = $('#builder-master-tabs').data('oc.tab'),
            tab = tabs.findByIdentifier(data.tabId)

        tabsObject.updateTab(tab, data.tableName, data.tab)
        this.onTableLoaded()
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.databaseTable = DatabaseTable;

}(window.jQuery);
