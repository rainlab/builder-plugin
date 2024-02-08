/*
 * Base class for Builder Index entity controllers
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.entityControllers === undefined)
        $.oc.builder.entityControllers = {}

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var EntityBase = function(typeName, indexController) {
        if (typeName === undefined) {
            throw new Error('The Builder entity type name should be set in the base constructor call.')
        }

        if (indexController === undefined) {
            throw new Error('The Builder index controller should be set when creating an entity controller.')
        }

        // The type name is used mostly for referring to
        // DOM objects.
        this.typeName = typeName

        this.indexController = indexController

        Base.call(this)
    }

    EntityBase.prototype = Object.create(BaseProto)
    EntityBase.prototype.constructor = EntityBase

    EntityBase.prototype.registerHandlers = function() {

    }

    EntityBase.prototype.invokeCommand = function(command, ev) {
        if (/^cmd[a-zA-Z0-9]+$/.test(command)) {
            if (this[command] !== undefined) {
                this[command].apply(this, [ev])
            }
            else {
                throw new Error('Unknown command: '+command)
            }
        }
        else {
            throw new Error('Invalid command: '+command)
        }
    }

    EntityBase.prototype.newTabId = function() {
        return this.typeName + Math.random()
    }

    EntityBase.prototype.makeTabId = function(objectName) {
        return this.typeName + '-' + objectName
    }

    EntityBase.prototype.getMasterTabsActivePane = function() {
        return this.indexController.getMasterTabActivePane()
    }

    EntityBase.prototype.getMasterTabsObject = function() {
        return this.indexController.masterTabsObj
    }

    EntityBase.prototype.getSelectedPlugin = function() {
        var activeItem = $('#PluginList-pluginList-plugin-list > ul > li.active')

        return activeItem.data('id')
    }

    EntityBase.prototype.getIndexController = function() {
        return this.indexController
    }

    EntityBase.prototype.updateMasterTabIdAndTitle = function($tabPane, responseData) {
        var tabsObject = this.getMasterTabsObject()

        tabsObject.updateIdentifier($tabPane, responseData.tabId)
        tabsObject.updateTitle($tabPane, responseData.tabTitle)
    }

    EntityBase.prototype.unhideFormDeleteButton = function($tabPane) {
        $('[data-control=delete-button]', $tabPane).removeClass('hide oc-hide')
    }

    EntityBase.prototype.forceCloseTab = function($tabPane) {
        $tabPane.trigger('close.oc.tab', [{force: true}])
    }

    EntityBase.prototype.unmodifyTab = function($tabPane) {
        this.indexController.unchangeTab($tabPane)
    }

    $.oc.builder.entityControllers.base = EntityBase;
}(window.jQuery);