/*
 * Builder client-side Index page controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var Builder = function() {
        Base.call(this)

        this.$masterTabs = null
        this.masterTabsObj = null
        this.hideStripeIndicatorProxy = null

        this.init()
    }

    Builder.prototype = Object.create(BaseProto)
    Builder.prototype.constructor = Builder

    Builder.prototype.dispose = function() {
        // We don't really care about disposing the 
        // index controller, as it's used only once
        // and always exists during the page life.
        BaseProto.dispose.call(this)
    }

    // PUBLIC METHODS
    // ============================

    Builder.prototype.openOrLoadMasterTab = function($form, serverHandlerName, tabId, data) {
        if (this.masterTabsObj.goTo(tabId))
            return false

        var requestData = data === undefined ? {} : data

        $.oc.stripeLoadIndicator.show()
        $form.request(
            serverHandlerName, 
            { data: requestData }
        ).done(
            this.proxy(this.addMasterTab)
        ).always(
            this.hideStripeIndicatorProxy
        )
    }

    // INTERNAL METHODS
    // ============================

    Builder.prototype.init = function() {
        this.$masterTabs = $('#builder-master-tabs')
        this.masterTabsObj = this.$masterTabs.data('oc.tab')
        this.hideStripeIndicatorProxy = this.proxy(this.hideStripeIndicator)
        new $.oc.tabFormExpandControls(this.$masterTabs)

        this.registerHandlers()
    }

    Builder.prototype.registerHandlers = function() {
        $(document).on('click', '[data-builder-command]', this.proxy(this.onCommand))

        this.$masterTabs.on('changed.oc.changeMonitor', this.proxy(this.formChanged))
        this.$masterTabs.on('unchanged.oc.changeMonitor', this.proxy(this.formUnchanged))
    }

    Builder.prototype.hideStripeIndicator = function() {
        $.oc.stripeLoadIndicator.hide()
    }

    Builder.prototype.addMasterTab = function(data) {
var tabId = null,
    icon = ''
        this.masterTabsObj.addTab(data.tabTitle, data.tab, tabId, icon)
    }

    Builder.prototype.formChanged = function(ev) {
        $('.form-tabless-fields', ev.target).trigger('modified.oc.tab')
        this.updateModifiedCounter()
    }

    Builder.prototype.formUnchanged = function(ev) {
        $('.form-tabless-fields', ev.target).trigger('unmodified.oc.tab')
        this.updateModifiedCounter()
    }

    Builder.prototype.updateModifiedCounter = function() {
        throw new Error('Not implemented yet')
    }

    // EVENT HANDLERS
    // ============================

    Builder.prototype.onCommand = function(ev) {
        var command = $(ev.currentTarget).data('builderCommand'),
            commandParts = command.split(':')

        if (commandParts.length === 2) {
            var entity = commandParts[0],
                commandToExecute = commandParts[1]

            if ($.oc.builder.entityControllers[entity] === undefined) {
                throw new Error('Unknown entity type: ' + entity)
            }

            $.oc.builder.entityControllers[entity].invokeCommand(commandToExecute, ev)
        }

        ev.preventDefault()
        return false
    }

    // INITIALIZATION
    // ============================

    $(document).ready(function(){
        $.oc.builder.indexController = new Builder()
    })

}(window.jQuery);