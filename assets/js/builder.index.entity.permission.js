/*
 * Builder Index controller Permission entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype

    var Permission = function(indexController) {
        Base.call(this, 'permissions', indexController)
    }

    Permission.prototype = Object.create(BaseProto)
    Permission.prototype.constructor = Permission

    Permission.prototype.registerHandlers = function() {
        this.indexController.$masterTabs.on('oc.tableNewRow', this.proxy(this.onTableRowCreated))
    }

    // PUBLIC METHODS
    // ============================

    Permission.prototype.cmdOpenPermissions = function(ev) {
        var currentPlugin = this.getSelectedPlugin()

        if (!currentPlugin) {
            alert('Please select a plugin first')
            return
        }

        this.indexController.openOrLoadMasterTab($(ev.target), 'onPermissionsOpen', this.makeTabId(currentPlugin))
    }

    Permission.prototype.cmdSavePermissions = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form')

        if (!this.validateTable($target)) {
            return
        }

        $target.request('onPermissionsSave', {
            data: {
                permissions: this.getTableData($target)
            }
        }).done(
            this.proxy(this.savePermissionsDone)
        )
    }

    // INTERNAL METHODS
    // ============================

    Permission.prototype.getTableControlObject = function($target) {
        var $form = $target.closest('form'),
            $table = $form.find('[data-control=table]'),
            tableObj = $table.data('oc.table')

        if (!tableObj) {
            throw new Error('Table object is not found on permissions tab')
        }

        return tableObj
    }

    Permission.prototype.validateTable = function($target) {
        var tableObj = this.getTableControlObject($target)

        tableObj.unfocusTable()
        return tableObj.validate()
    }

    Permission.prototype.getTableData = function($target) {
        var tableObj = this.getTableControlObject($target)

        return tableObj.dataSource.getAllData()
    }

    Permission.prototype.savePermissionsDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data')
        }

        var $masterTabPane = this.getMasterTabsActivePane()

        this.getIndexController().unchangeTab($masterTabPane)
        $.oc.builder.dataRegistry.clearCache(data.builderResponseData.pluginCode, 'permissions')
    }

    // EVENT HANDLERS
    // ============================

    Permission.prototype.onTableRowCreated = function(ev, recordData) {
        var $target = $(ev.target)

        if ($target.data('alias') != 'permissions') {
            return
        }

        var $form = $target.closest('form')

        if ($form.data('entity') != 'permissions') {
            return
        }

        var pluginCode = $form.find('input[name=plugin_code]').val()
        
        recordData.permission = pluginCode.toLowerCase() + '.';
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.permission = Permission;

}(window.jQuery);