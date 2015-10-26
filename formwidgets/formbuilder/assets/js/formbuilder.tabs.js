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
        $layoutBody.on('change', 'div.inspector-trigger.tab-control', this.proxy(this.onTabChange))

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

    TabManager.prototype.tabHasControls = function($tab) {
        return this.findTabPanel($tab).find('ul[data-control-list] li.control:not(.placeholder)').length > 0
    }

    TabManager.prototype.createNewTab = function($tabControl) {
        var tabTemplate = $('[data-tab-template]').html(),
            panelTemplate = $('[data-panel-template]').html(),
            $newTab = $(tabTemplate),
            $newTabControl = this.getTabList($tabControl).find('> li[data-builder-new-tab]')
        
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

    TabManager.prototype.closeTab = function($tab) {
        var $tabControl = this.findTabControl($tab)

        if (this.tabHasControls($tab)) {
            if (!confirm($tabControl.data('tabCloseConfirmation'))) {
                return
            }
        }

        var $prevTab = $tab.prev(),
            $tabPanel = this.findTabPanel($tab)

        $tab.remove()
        $tabPanel.remove()

        if ($prevTab.length > 0) {
            this.gotoTab($prevTab)
        }
        else {
            this.createNewTab($tabControl)
        }
    }

    TabManager.prototype.updateTabProperties = function($tab) {
        var properties = $tab.find('[data-inspector-values]').val(),
            propertiesParsed = $.parseJSON(properties)

        $tab.find('[data-tab-title]').text(propertiesParsed.title)
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

    $(document).ready(function(){
        // There is a single instance of the tabs manager.
        new TabManager()
    })

}(window.jQuery);