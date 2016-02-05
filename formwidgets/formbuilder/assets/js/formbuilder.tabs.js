/*
 * Manages tabs in the form builder area.
 */
+function ($) { "use strict";

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var TabManager = function() {
        Base.call(this)

        this.init()
    }

    TabManager.prototype = Object.create(BaseProto)
    TabManager.prototype.constructor = TabManager

    // INTERNAL METHODS
    // ============================

    TabManager.prototype.init = function() {
        this.registerHandlers()
    }

    TabManager.prototype.registerHandlers = function() {
        var $layoutBody = $('#layout-body')

        $layoutBody.on('click', 'li[data-builder-new-tab]', this.proxy(this.onNewTabClick))
        $layoutBody.on('click', 'div[data-builder-tab]', this.proxy(this.onTabClick))
        $layoutBody.on('click', 'div[data-builder-close-tab]', this.proxy(this.onTabCloseClick))
        $layoutBody.on('change livechange', 'ul.tabs > li div.inspector-trigger.tab-control', this.proxy(this.onTabChange))
        $layoutBody.on('hiding.oc.inspector', 'ul.tabs > li div.inspector-trigger.tab-control', this.proxy(this.onTabInspectorHiding))
    }

    TabManager.prototype.getTabList = function($tabControl) {
        return $tabControl.find('> ul.tabs')
    }

    TabManager.prototype.getPanelList = function($tabControl) {
        return $tabControl.find('> ul.panels')
    }

    TabManager.prototype.findTabControl = function($tab) {
        return $tab.closest('div.tabs')
    }

    TabManager.prototype.findTabPanel = function($tab) {
        var $tabControl = this.findTabControl($tab),
            tabIndex = $tab.index()

        return this.getPanelList($tabControl).find(' > li').eq(tabIndex)
    }

    TabManager.prototype.findPanelTab = function($panel) {
        var $tabControl = this.findTabControl($panel),
            tabIndex = $panel.index()

        return this.getTabList($tabControl).find(' > li').eq(tabIndex)
    }

    TabManager.prototype.findTabPanel = function($tab) {
        var $tabControl = this.findTabControl($tab),
            tabIndex = $tab.index()

        return this.getPanelList($tabControl).find(' > li').eq(tabIndex)
    }

    TabManager.prototype.findTabForm = function(tab) {
        return $(tab).closest('form')
    }

    TabManager.prototype.getGlobalTabsProperties = function(tabsContainer) {
        var properties = $(tabsContainer).find('.inspector-trigger.tab-control.global [data-inspector-values]').val()

        if (properties.length == 0) {
            properties = '{}'
        }
        
        return $.parseJSON(properties)
    }

    /*
     * Returns tab title an element belongs to
     */
    TabManager.prototype.getElementTabTitle = function(element) {
        var $panel = $(element).closest('li.tab-panel'),
            $tab = this.findPanelTab($panel),
            properties = $tab.find('[data-inspector-values]').val(),
            propertiesParsed = $.parseJSON(properties)

        return propertiesParsed.title
    }

    TabManager.prototype.tabHasControls = function($tab) {
        return this.findTabPanel($tab).find('ul[data-control-list] li.control:not(.placeholder)').length > 0
    }

    TabManager.prototype.tabNameExists = function($tabList, name, $ignoreTab) {
        var tabs = $tabList.get(0).children

        for (var i=0, len = tabs.length; i<len; i++) {
            if ($ignoreTab !== undefined && $ignoreTab.get(0) === tabs[i]) {
                continue
            }

            var currentTabName = $('[data-tab-title]', tabs[i]).text()

            if (currentTabName == name) {
                return true
            }
        }

        return false
    }

    TabManager.prototype.generateTabName = function($tabList, $tabControl) {
        var nameTemplate = $tabControl.data('tabNameTemplate'),
            tabs = $tabList.get(0).children,
            index = tabs.length
            name = nameTemplate.replace('%s', index)

        while (this.tabNameExists($tabList, name)) {
            name = nameTemplate.replace('%s', index)

            index++;
        }

        return name
    }

    TabManager.prototype.createNewTab = function($tabControl) {
        var tabTemplate = $('[data-tab-template]', $tabControl).html(),
            panelTemplate = $('[data-panel-template]', $tabControl).html(),
            $tabList = this.getTabList($tabControl),
            tabName = this.generateTabName($tabList, $tabControl),
            patchedTabTemplate = tabTemplate.replace(/tabtitle/mg, tabName)

        var $newTab = $(patchedTabTemplate),
            $newTabControl = $tabList.find('> li[data-builder-new-tab]')

        $('[data-tab-title]', $newTab).text(tabName)
        
        $newTab.insertBefore($newTabControl)
        this.getPanelList($tabControl).append(panelTemplate)

        this.gotoTab($newTab)
    }

    TabManager.prototype.gotoTab = function($tab) {
        var tabIndex = $tab.index(),
            $tabControl = this.findTabControl($tab),
            $tabList = this.getTabList($tabControl),
            $panelList = this.getPanelList($tabControl)

        $('> li', $tabList).removeClass('active')
        $tab.addClass('active')

        $('> li', $panelList).removeClass('active')
        $('> li', $panelList).eq(tabIndex).addClass('active')
    }

    TabManager.prototype.findInspectorContainer = function($element) {
        var $containerRoot = $element.closest('[data-inspector-container]')

        return $containerRoot.find('.inspector-container')
    }

    TabManager.prototype.closeTabInspectors = function($tab, $tabPanel) {
        if ($tab.find('.inspector-open').length === 0 && $tabPanel.find('.inspector-open').length === 0) {
            return
        }

        var $inspectorContainer = this.findInspectorContainer($tab)

        $.oc.foundation.controlUtils.disposeControls($inspectorContainer.get(0))
    }

    TabManager.prototype.closeTabControlPalette = function($tab, $tabPanel) {
        if ($tabPanel.find('.control-palette-open').length === 0) {
            return
        }

        var $inspectorContainer = this.findInspectorContainer($tab)

        $.oc.foundation.controlUtils.disposeControls($inspectorContainer.get(0))
    }

    TabManager.prototype.closeTab = function($tab) {
        var $tabControl = this.findTabControl($tab)

        if (this.tabHasControls($tab)) {
            if (!confirm($tabControl.data('tabCloseConfirmation'))) {
                return
            }

            $tab.trigger('change')
        }

        var $prevTab = $tab.prev(),
            $nextTab = $tab.next(),
            $tabPanel = this.findTabPanel($tab)

        this.closeTabInspectors($tab, $tabPanel)
        this.closeTabControlPalette($tab, $tabPanel)

        $tab.remove()
        $tabPanel.remove()

        if ($prevTab.length > 0) {
            this.gotoTab($prevTab)
        }
        else {
            if ($nextTab.length > 0 && !$nextTab.hasClass('new-tab')) {
                this.gotoTab($nextTab)
            }
            else {
                this.createNewTab($tabControl)
            }
        }
    }

    TabManager.prototype.updateTabProperties = function($tab) {
        var properties = $tab.find('[data-inspector-values]').val(),
            propertiesParsed = $.parseJSON(properties),
            $form = this.findTabForm($tab),
            pluginCode = $form.find('input[name=plugin_code]').val()

        $tab.find('[data-tab-title]').attr('data-localization-key', propertiesParsed.title)

        $.oc.builder.dataRegistry.getLocalizationString($form, pluginCode, propertiesParsed.title, function(title){
            $tab.find('[data-tab-title]').text(title)
        })
    }

    // EVENT HANDLERS
    // ============================

    TabManager.prototype.onNewTabClick = function(ev) {
        this.createNewTab($(ev.currentTarget).closest('div.tabs'))

        ev.stopPropagation()
        ev.preventDefault()

        return false
    }

    TabManager.prototype.onTabClick = function(ev) {
        this.gotoTab($(ev.currentTarget).closest('li'))

        ev.stopPropagation()
        ev.preventDefault()

        return false
    }

    TabManager.prototype.onTabCloseClick = function(ev) {
        this.closeTab($(ev.currentTarget).closest('li'))

        ev.stopPropagation()
        ev.preventDefault()

        return false
    }

    TabManager.prototype.onTabChange = function(ev) {
        this.updateTabProperties($(ev.currentTarget).closest('li'))
    }

    TabManager.prototype.onTabInspectorHiding = function(ev, data) {
        var $tab = $(ev.currentTarget).closest('li'),
            $tabControl = this.findTabControl($tab),
            $tabList = this.getTabList($tabControl)

        if (this.tabNameExists($tabList, data.values.title, $tab)) {
            alert($tabControl.data('tabAlreadyExists'))

            ev.preventDefault()
        }
    }

    $(document).ready(function(){
        // There is a single instance of the tabs manager.
        $.oc.builder.formbuilder.tabManager = new TabManager()
    })

}(window.jQuery);