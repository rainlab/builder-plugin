/*
 * Converts control properties from DOM elements to JSON format.
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.formbuilder === undefined)
        $.oc.builder.formbuilder = {}

    function getItemPropertyValues(item)  {
        for (var i=0, len=item.children.length; i<len; i++) {
            var child = item.children[i]

            if (child.tagName === 'INPUT' && child.hasAttribute('data-inspector-values')) {
                return $.parseJSON(child.value)
            }
        }

        return false
    }

    function listToJson(list, injectProperties) {
        var listItems = list.children,
            result = {}

        for (var i=0, len=listItems.length; i<len; i++) {
            var listItem = listItems[i]

            if (!listItem.hasAttribute('data-control-type')) {
                // There could be other items - placeholders
                // and clear-row elements
                continue
            }

            var values = getItemPropertyValues(listItem)
            if (values === null) {
                throw new Error('Property values are not found for a control list item.')
            }

            if (values['oc.fieldName'] === undefined) {
                throw new Error('Field name property is not found for a control.')
            }

            var fieldName = values['oc.fieldName']

            values.type = listItem.getAttribute('data-control-type')
            delete values['oc.fieldName']

            if (injectProperties !== undefined) {
                values = $.extend(values, injectProperties)
            }

            result[fieldName] = values

            // TODO: for the Repeater we should check if the control element
            // has containers. See "Form builder" section in the Builder's notes.ft.
        }

        return result
    }

    function getControlListContainerLists(controlListContainer) {
        var controlLists = controlListContainer.querySelectorAll('[data-control-list]'),
            result = [],
            listName = null

        for (var i=0, len=controlLists.length; i<len; i++) {
            var controlList = controlLists[i],
                currentListName = String(controlList.getAttribute('data-control-list'))

            if (currentListName.length === 0 || (listName !== null && currentListName != listName)) {
                throw new Error('Lists in control list containers should have names, and the name should be equal for all lists in a container.')
            }

            result.push(controlList)
        }

        return result
    }

    function mergeListContainerControlsToResult(result, container) {
        var listContainerType = String(container.getAttribute('data-control-list-container-type'))

        if (listContainerType.length === 0) {
            throw new Error('Control list container type is not specified')
        }

        if (listContainerType !== 'tabs') {
            // Other container types could be added here.
            //
            throw new Error('Unknown control list container type: '+listContainerType)
        }

        var controlLists = getControlListContainerLists(container),
            globalTabsProperties = $.oc.builder.formbuilder.tabManager.getGlobalTabsProperties(container)

        for (var i=0, len=controlLists.length; i<len; i++) {
            var controlList = controlLists[i],
                tabTitle = $.oc.builder.formbuilder.tabManager.getElementTabTitle(controlList),
                injectProperties = {
                    tab: tabTitle
                },
                listControls = listToJson(controlList, injectProperties),
                listName = String(controlList.getAttribute('data-control-list'))

            if (result[listName] === undefined) {
                result[listName] = {}
            }

            result[listName] = $.extend(result[listName], globalTabsProperties)

            mergeControlListControlsToResult(result, listControls, listName)
        }
    }

    function mergeControlListControlsToResult(result, controls, listName) {
        if (listName.length > 0) {
            if (result[listName] === undefined) {
                result[listName] = {
                    fields: {}
                }
            }

            result[listName].fields = $.extend(result[listName].fields, controls)
        }
        else {
            result.fields = $.extend(result.fields, controls)
        }
    }

    function containerToJson(container) {
        var containerElements = container.children,
            result = {
                fields: {} // The "fields" property name is fixed, but could be customized later if needed.
            }

        for (var i=0, len=containerElements.length; i<len; i++) {
            var currentElement = containerElements[i],
                isControlListContainer = currentElement.hasAttribute('data-control-list-container')

            if (currentElement.tagName !== 'UL' && !isControlListContainer) {
                throw new Error('Control container can contain only UL elements and control list containers.')
            }

            if (isControlListContainer) {
                mergeListContainerControlsToResult(result, currentElement)
                continue
            } 
            else {
                if (!currentElement.hasAttribute('data-control-list')) {
                    throw new Error('Control container can contain only UL elements with data-control-list attribute or control list containers.')
                }
            }

            // This part processes control lists (UL) defined directly in the control container.
            // Lists can have names, in that case a property with the corresponding name
            // is created in the result object, and controls are injected to the fields sub-property 
            // of that property (result.listname.fields). If a list doesn't have a name, its controls
            // are injected directly to the result's fields property (result.fields).
            //

            var controlList = currentElement,
                listControls = listToJson(controlList),
                listName = String(controlList.getAttribute('data-control-list'))

            mergeControlListControlsToResult(result, listControls, listName)
        }

        return result
    }

    var DomToJson = {}

    DomToJson.convert = function(rootContainer) {
        return containerToJson(rootContainer)
    }

    $.oc.builder.formbuilder.domToPropertyJson = DomToJson

}(window.jQuery);