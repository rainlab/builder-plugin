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
        this.indexController.openOrLoadMasterTab($(ev.target), 'onDatabaseTableCreate', this.newTabId())
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
        if (data['builderRepsonseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        $('#builderTableMigrationPopup').trigger('close.oc.popup')

        var $masterTabPane = this.getMasterTabsActivePane(),
            tabsObject = this.getMasterTabsObject()

        $masterTabPane.find('input[name=table_name]').val(data.builderRepsonseData.tableName)

// TODO: the following code could be abstracted in the base entity controller
//
        tabsObject.updateIdentifier($masterTabPane, data.builderRepsonseData.tabId)
        tabsObject.updateTitle($masterTabPane, data.builderRepsonseData.tabTitle)

        this.getTableList().fileList('markActive', data.builderRepsonseData.tabId)
        this.getIndexController().unchageTab($masterTabPane)
    }

    DatabaseTable.prototype.getTableList = function() {
        return $('#layout-side-panel form[data-content-id=database] [data-control=filelist]')
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.databaseTable = DatabaseTable;

}(window.jQuery);