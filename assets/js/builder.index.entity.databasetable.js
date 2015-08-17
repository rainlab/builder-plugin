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

    var DatabaseTable = function() {
        Base.call(this, 'databaseTable')
    }

    DatabaseTable.prototype = Object.create(BaseProto)
    DatabaseTable.prototype.constructor = DatabaseTable

    // PUBLIC METHODS
    // ============================

    DatabaseTable.prototype.cmdCreateTable = function(ev) {
        $.oc.builder.indexController.openOrLoadMasterTab($(ev.target), 'onDatabaseTableCreate', this.newTabId())
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

    // EVENT HANDLERS
    // ============================

    // INTERNAL METHODS
    // ============================

    DatabaseTable.prototype.validateTable = function($target) {
        var tableObj = this.getTableObject($target)

        tableObj.unfocusTable()
        return tableObj.validate()
    }

    DatabaseTable.prototype.getTableData = function($target) {
        var tableObj = this.getTableObject($target)
console.log(tableObj.dataSource.getAllData())
        return tableObj.dataSource.getAllData()
    }

    DatabaseTable.prototype.getTableObject = function($target) {
        var $form = $target.closest('form'),
            $table = $form.find('[data-control=table]'),
            tableObj = $table.data('oc.table')

        if (!tableObj) {
            throw new Error('Table object is not found on the database table tab')
        }

        return tableObj
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.databaseTable = new DatabaseTable()

}(window.jQuery);