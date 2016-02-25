/*
 * Menu Builder widget class.
 *
 * There is only a single instance of the Menu Builder class and it handles
 * as many menu builder user interfaces as needed.
 *
 */
+function ($) { "use strict";

    if ($.oc.builder.menubuilder === undefined)
        $.oc.builder.menubuilder = {}

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var MenuBulder = function() {
        Base.call(this)

        this.init()
    }

    MenuBulder.prototype = Object.create(BaseProto)
    MenuBulder.prototype.constructor = MenuBulder

    // INTERNAL METHODS
    // ============================

    MenuBulder.prototype.init = function() {
        this.registerHandlers()
    }

    MenuBulder.prototype.registerHandlers = function() {
        $(document).on('change', '.builder-menu-editor li.item', this.proxy(this.onItemChange))
        $(document).on('dragged.list.sortable', '.builder-menu-editor li.item', this.proxy(this.onItemDragged))
        $(document).on('livechange', '.builder-menu-editor li.item', this.proxy(this.onItemLiveChange))
    }

    MenuBulder.prototype.getParentList = function(element) {
        return $(element).closest('ul')
    }

    MenuBulder.prototype.findForm = function(item) {
        return $(item).closest('form')
    }

    MenuBulder.prototype.getElementListItem = function(element) {
        return $(element).closest('li')
    }

    MenuBulder.prototype.getMenuBuilderControlElement = function(element) {
        return $(element).closest('[data-control=builder-menu-editor]')
    }

    MenuBulder.prototype.getTemplateMarkup = function(element, templateAttribute) {
        var $builderControl = this.getMenuBuilderControlElement(element)

        return $builderControl.find('script['+templateAttribute+']').html()
    }

    MenuBulder.prototype.getItemProperties = function(item) {
        return $.parseJSON($(item).find('> input[data-inspector-values]').val())
    }

    MenuBulder.prototype.itemCodeExistsInList = function($list, code) {
        var valueInputs = $list.find('> li.item > input[data-inspector-values]')

        for (var i=valueInputs.length-1; i>=0; i--) {
            var value = String(valueInputs[i].value)

            if (value.length === 0) {
                continue
            }

            var properties = $.parseJSON(value)

            if (properties['code'] == code) {
                return true
            }
        }

        return false
    }

    MenuBulder.prototype.replacePropertyValue = function($item, property, value) {
        var input = $item.find(' > input[data-inspector-values]'),
            properties = $.parseJSON(input.val())

        properties[property] = value
        input.val(JSON.stringify(properties))
    }

    MenuBulder.prototype.generateItemCode = function($parentList, baseCode) {
        var counter = 1,
            code = baseCode

        while (this.itemCodeExistsInList($parentList, code)) {
            counter ++
            code = baseCode + counter
        }

        return code
    }

    MenuBulder.prototype.updateItemVisualProperties = function(item) {
        var properties = this.getItemProperties(item),
            $item = $(item),
            $form = this.findForm(item),
            pluginCode = $form.find('input[name=plugin_code]').val()

        $item.find('> .item-container > span.title').attr('data-localization-key', properties.label)

        $.oc.builder.dataRegistry.getLocalizationString($item, pluginCode, properties.label, function(label){
            $item.find('> .item-container > span.title').text(label)
        })

        $item.find('> .item-container > i').attr('class', properties.icon)
    }

    MenuBulder.prototype.findInspectorContainer = function($element) {
        var $containerRoot = $element.closest('[data-inspector-container]')

        return $containerRoot.find('.inspector-container')
    }

    // BUILDER API METHODS
    // ============================

    MenuBulder.prototype.addMainMenuItem = function(ev) {
        var newItemMarkup = this.getTemplateMarkup(ev.currentTarget, 'data-main-menu-template'),
            $item = $(newItemMarkup),
            $list = this.getParentList(ev.currentTarget),
            newCode = this.generateItemCode($list, 'main-menu-item')

        this.replacePropertyValue($item, 'code', newCode)

        this.getElementListItem(ev.currentTarget).before($item)
        $(this.findForm(ev.currentTarget)).trigger('change')
    }

    MenuBulder.prototype.addSideMenuItem = function(ev) {
        var newItemMarkup = this.getTemplateMarkup(ev.currentTarget, 'data-side-menu-template'),
            $item = $(newItemMarkup),
            $list = this.getParentList(ev.currentTarget),
            newCode = this.generateItemCode($list, 'side-menu-item')

        this.replacePropertyValue($item, 'code', newCode)

        this.getElementListItem(ev.currentTarget).before($item)
        $(this.findForm(ev.currentTarget)).trigger('change')
    }

    MenuBulder.prototype.getJson = function(form) {
        var mainMenuItems = form.querySelectorAll('ul.builder-main-menu > li.item'),
            result = []

        for (var i=0,lenOuter=mainMenuItems.length; i < lenOuter; i++) {
            var mainMenuItem = mainMenuItems[i],
                mainMenuItemConfig = this.getItemProperties(mainMenuItem)

            if (mainMenuItemConfig['sideMenu'] !== undefined) {
                delete mainMenuItemConfig['sideMenu']
            }

            var sideMenuItems = mainMenuItem.querySelectorAll('ul.builder-submenu > li.item')
            for (var j=0,lenInner=sideMenuItems.length; j < lenInner; j++) {
                var sideMenuItem = sideMenuItems[j],
                    sideMenuItemConfig = this.getItemProperties(sideMenuItem)

                if (mainMenuItemConfig['sideMenu'] === undefined) {
                    mainMenuItemConfig['sideMenu'] = []
                }

                mainMenuItemConfig['sideMenu'].push(sideMenuItemConfig)
            }

            result.push(mainMenuItemConfig)
        }

        return JSON.stringify(result)
    }

    MenuBulder.prototype.deleteMenuItem = function(ev) {
        var item = this.getElementListItem(ev.currentTarget)

        if ($(item).hasClass('inspector-open')) {
            var $inspectorContainer = this.findInspectorContainer($(item))
            $.oc.foundation.controlUtils.disposeControls($inspectorContainer.get(0))
        }

        var subitems = item.get(0).querySelectorAll('li.inspector-open')
        for (var i=subitems.length-1; i>=0; i--) {
            var $inspectorContainer = this.findInspectorContainer($(subitems[i]))
            $.oc.foundation.controlUtils.disposeControls($inspectorContainer.get(0))
        }

        $(this.findForm(ev.currentTarget)).trigger('change')

        $(item).remove()
    }

    // EVENT HANDLERS
    // ============================

    MenuBulder.prototype.onItemLiveChange = function(ev) {
        this.updateItemVisualProperties(ev.currentTarget)

        $(this.findForm(ev.currentTarget)).trigger('change')  // Set modified state for the form

        ev.stopPropagation()
        return false
    }

    MenuBulder.prototype.onItemChange = function(ev) {
        this.updateItemVisualProperties(ev.currentTarget)

        ev.stopPropagation()
        return false
    }

    MenuBulder.prototype.onItemDragged = function(ev) {
         $(this.findForm(ev.target)).trigger('change')
    }

    $(document).ready(function(){
        // There is a single instance of the form builder. All operations
        // are stateless, so instance properties or DOM references are not needed.
        $.oc.builder.menubuilder.controller = new MenuBulder()
    })

}(window.jQuery);