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
        this.entityControllers = {}

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
        var promise = $form.request(
                serverHandlerName, 
                { data: requestData }
            )
            .done(this.proxy(this.addMasterTab))
            .always(
                this.hideStripeIndicatorProxy
            )

        return promise
    }

    Builder.prototype.getMasterTabActivePane = function() {
        return this.$masterTabs.find('> .tab-content > .tab-pane.active')
    }

    Builder.prototype.unchangeTab = function($pane) {
        $pane.find('form').trigger('unchange.oc.changeMonitor')
    }

    Builder.prototype.triggerCommand = function(command, ev) {
        var commandParts = command.split(':')

        if (commandParts.length === 2) {
            var entity = commandParts[0],
                commandToExecute = commandParts[1]

            if (this.entityControllers[entity] === undefined) {
                throw new Error('Unknown entity type: ' + entity)
            }

            this.entityControllers[entity].invokeCommand(commandToExecute, ev)
        }
    }

    // INTERNAL METHODS
    // ============================

    Builder.prototype.init = function() {
        this.$masterTabs = $('#builder-master-tabs')
        this.$sidePanel = $('#builder-side-panel')

        this.masterTabsObj = this.$masterTabs.data('oc.tab')
        this.hideStripeIndicatorProxy = this.proxy(this.hideStripeIndicator)
        new $.oc.tabFormExpandControls(this.$masterTabs)

        this.createEntityControllers()
        this.registerHandlers()
    }

    Builder.prototype.createEntityControllers = function() {
        for (var controller in $.oc.builder.entityControllers) {
            if (controller == "base") {
                continue
            }

            this.entityControllers[controller] = new $.oc.builder.entityControllers[controller](this)
        }
    }

    Builder.prototype.registerHandlers = function() {
        $(document).on('click', '[data-builder-command]', this.proxy(this.onCommand))
        $(document).on('submit', '[data-builder-command]', this.proxy(this.onCommand))

        this.$masterTabs.on('changed.oc.changeMonitor', this.proxy(this.onFormChanged))
        this.$masterTabs.on('unchanged.oc.changeMonitor', this.proxy(this.onFormUnchanged))
        this.$masterTabs.on('shown.bs.tab', this.proxy(this.onTabShown))
        this.$masterTabs.on('afterAllClosed.oc.tab', this.proxy(this.onAllTabsClosed))
        this.$masterTabs.on('closed.oc.tab', this.proxy(this.onTabClosed))
        this.$masterTabs.on('autocompleteitems.oc.inspector', this.proxy(this.onDataRegistryItems))
        this.$masterTabs.on('dropdownoptions.oc.inspector', this.proxy(this.onDataRegistryItems))

        for (var controller in this.entityControllers) {
            if (this.entityControllers[controller].registerHandlers !== undefined) {
                this.entityControllers[controller].registerHandlers()
            }
        }
    }

    Builder.prototype.hideStripeIndicator = function() {
        $.oc.stripeLoadIndicator.hide()
    }

    Builder.prototype.addMasterTab = function(data) {
        this.masterTabsObj.addTab(data.tabTitle, data.tab, data.tabId, 'oc-' + data.tabIcon)

        if (data.isNewRecord) {
            var $masterTabPane = this.getMasterTabActivePane()

            $masterTabPane.find('form').one('ready.oc.changeMonitor', this.proxy(this.onChangeMonitorReady))
        }
    }

    Builder.prototype.updateModifiedCounter = function() {
        var counters = {
            database: { menu: 'database', count: 0 },
            models: { menu: 'models', count: 0 },
            permissions: { menu: 'permissions', count: 0 },
            menus: { menu: 'menus', count: 0 },
            versions: { menu: 'versions', count: 0 },
            localization: { menu: 'localization', count: 0 },
            controller: { menu: 'controllers', count: 0 }
        }

        $('> div.tab-content > div.tab-pane[data-modified] > form', this.$masterTabs).each(function(){
            var entity = $(this).data('entity')
            counters[entity].count++
        })

        $.each(counters, function(type, data){
            $.oc.sideNav.setCounter('builder/' + data.menu, data.count);
        })
    }

    Builder.prototype.getFormPluginCode = function(formElement) {
        var $form = $(formElement).closest('form'),
            $input = $form.find('input[name="plugin_code"]'),
            code = $input.val()

        if (!code) {
            throw new Error('Plugin code input is not found in the form.')
        }

        return code
    }

    Builder.prototype.setPageTitle = function(title) {
        $.oc.layout.setPageTitle(title.length ? (title + ' | ') : title)
    }

    Builder.prototype.getFileLists = function() {
        return $('[data-control=filelist]', this.$sidePanel)
    }

    Builder.prototype.dataToInspectorArray = function(data) {
        var result = []

        for (var key in data) {
            var item = {
                title: data[key],
                value: key
            }
            result.push(item)
        }

        return result
    }

    // EVENT HANDLERS
    // ============================

    Builder.prototype.onCommand = function(ev) {
        if (ev.currentTarget.tagName == 'FORM' && ev.type == 'click') {
            // The form elements could have data-builder-command attribute,
            // but for them we only handle the submit event and ignore clicks. 

            return
        }

        var command = $(ev.currentTarget).data('builderCommand')
        this.triggerCommand(command, ev)

        // Prevent default for everything except drop-down menu items
        //
        var $target = $(ev.currentTarget)
        if (ev.currentTarget.tagName === 'A' && $target.attr('role') == 'menuitem' && $target.attr('href') == 'javascript:;') {
            return
        }

        ev.preventDefault()
        return false
    }

    Builder.prototype.onFormChanged = function(ev) {
        $('.form-tabless-fields', ev.target).trigger('modified.oc.tab')
        this.updateModifiedCounter()
    }

    Builder.prototype.onFormUnchanged = function(ev) {
        $('.form-tabless-fields', ev.target).trigger('unmodified.oc.tab')
        this.updateModifiedCounter()
    }

    Builder.prototype.onTabShown = function(ev) {
        var $tabControl = $(ev.target).closest('[data-control=tab]')

        if ($tabControl.attr('id') != this.$masterTabs.attr('id')) {
            return
        }

        var dataId = $(ev.target).closest('li').attr('data-tab-id'),
            title = $(ev.target).attr('title')

        if (title) {
            this.setPageTitle(title)
        }

        this.getFileLists().fileList('markActive', dataId)

        $(window).trigger('resize')
    }

    Builder.prototype.onAllTabsClosed = function(ev) {
        this.setPageTitle('')
        this.getFileLists().fileList('markActive', null)
    }

    Builder.prototype.onTabClosed = function(ev, tab, pane) {
        $(pane).find('form').off('ready.oc.changeMonitor', this.proxy(this.onChangeMonitorReady))

        this.updateModifiedCounter()
    }

    Builder.prototype.onChangeMonitorReady = function(ev) {
        $(ev.target).trigger('change')
    }

    Builder.prototype.onDataRegistryItems = function(ev, data) {
        var self = this

        if (data.propertyDefinition.fillFrom == 'model-classes' || 
            data.propertyDefinition.fillFrom == 'model-forms' || 
            data.propertyDefinition.fillFrom == 'model-lists' || 
            data.propertyDefinition.fillFrom == 'controller-urls' ||
            data.propertyDefinition.fillFrom == 'model-columns' || 
            data.propertyDefinition.fillFrom == 'plugin-lists' || 
            data.propertyDefinition.fillFrom == 'permissions') {
            ev.preventDefault()

            var subtype = null,
                subtypeProperty = data.propertyDefinition.subtypeFrom

            if (subtypeProperty !== undefined) {
                subtype = data.values[subtypeProperty]
            }

            $.oc.builder.dataRegistry.get($(ev.target), this.getFormPluginCode(ev.target), data.propertyDefinition.fillFrom, subtype, function(response){
                data.callback({
                    options: self.dataToInspectorArray(response)
                })
            })
        }
    }

    // INITIALIZATION
    // ============================

    $(document).ready(function(){
        $.oc.builder.indexController = new Builder()
    })

}(window.jQuery);