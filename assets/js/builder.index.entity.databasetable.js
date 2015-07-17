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

    // EVENT HANDLERS
    // ============================

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.databaseTable = new DatabaseTable()

}(window.jQuery);