/*
 * Converts control properties from DOM elements to JSON format.
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    if ($.oc.builder.formbuilder === undefined)
        $.oc.builder.formbuilder = {}

    function getControlPropertyValues(item)  {
        for (var i=0, len=item.children.length; i<len; i++) {
            var child = item.children[i]

            if (child.tagName === 'INPUT' && child.hasAttribute('data-inspector-values')) {
                return $.parseJSON(child.value)
            }
        }

        return false
    }

    function preProcessSpecialProperties(properties) {
        delete properties['oc.fieldName']

        if (String(properties['oc.comment']).length > 0 && properties['oc.commentPosition'] == 'above') {
            properties['commentAbove'] = properties['oc.comment']

            if (properties['comment'] !== undefined) {
                delete properties['comment']
            }

            delete properties['oc.comment']
            delete properties['oc.commentPosition']
        }

        if (String(properties['oc.comment']).length > 0 && properties['oc.commentPosition'] == 'below') {
            properties['comment'] = properties['oc.comment']

            if (properties['comentAbove'] !== undefined) {
                delete properties['comentAbove']
            }

            delete properties['oc.comment']
            delete properties['oc.commentPosition']
        }

        if (properties['oc.comment'] !== undefined) {
            if (String(properties['oc.comment']).length > 0) {
                properties['comment'] = properties['oc.comment']
            }

            delete properties['oc.comment']
        }
    }

    function parseControlControlContainer(control) {
        var children = control.children,
            result = {}

        for (var i=0, len=children.length; i<len; i++) {
            var child = children[i]

            if (child.tagName !== 'DIV' || !child.hasAttribute('data-control-container') || !child.hasAttribute('data-container-name')) {
                continue
            }

            var containerName = String(child.getAttribute('data-container-name')), 
                childControls = containerToJson(child)

            result[containerName] = childControls
        }

        return result
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

            var values = getControlPropertyValues(listItem)
            if (values === null) {
                throw new Error('Property values are not found for a control list item.')
            }

            if (values['oc.fieldName'] === undefined) {
                throw new Error('Field name property is not found for a control.')
            }

            var fieldName = values['oc.fieldName']

            values.type = listItem.getAttribute('data-control-type')
            preProcessSpecialProperties(values)

            if (injectProperties !== undefined) {
                values = $.extend(values, injectProperties)
            }

            if (result[fieldName] !== undefined) {
                throw new Error('Duplicate field name: ' + fieldName)
            }

            // If a control contains control containers, parse them
            // and assign parsed object to the current control property.

            var childControls = parseControlControlContainer(listItem)

            if (!$.isEmptyObject(childControls)) {
                values = $.extend(values, childControls)
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
                currentListName = String(controlList.getAttribute('data-control-list')),
                parentContainer = getControlListParentContainer(controlList)

            /*
             The check is disabled, because repeaters don't have list names. --ab Aug 7, 2016
             See https://github.com/rainlab/builder-plugin/issues/74

            if (currentListName.length === 0 || (listName !== null && currentListName != listName)) {
                throw new Error('Lists in control list containers should have names, and the name should be equal for all lists in a container.')
            }
            */

            if (parentContainer === controlListContainer) {
                result.push(controlList)
            }
        }

        return result
    }

    function getControlListParentContainer(controlList) {
        var parent = controlList

        while (parent) {
            // This deals with control lists inside Repeater inside tabs
            if (parent.hasAttribute('data-control-list-container') || parent.hasAttribute('data-control-container')) {
                return parent
            }

            parent = parent.parentNode
        }

        return null
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

            if (!$.isEmptyObject(listControls) || !$.isEmptyObject(listControls)) {
                // Create tabs sections only if there are controls.
                //
                if (result[listName] === undefined) {
                    result[listName] = {}
                }

                result[listName] = $.extend(result[listName], globalTabsProperties)
                mergeControlListControlsToResult(result, listControls, listName)
            }
        }
    }

    function objectHasProperties(object) {
        for (var k in object) {
            return true
        }

        return false
    }

    function mergeControlListControlsToResult(result, controls, listName) {
        // The "fields" property name is fixed, but could be customized later if needed.

        if (listName.length > 0) {
            if (objectHasProperties(controls)) {
                if (result[listName].fields === undefined) {
                    result[listName].fields = {}
                }

                result[listName].fields = $.extend(result[listName].fields, controls)
            }
        }
        else {
            if (objectHasProperties(controls)) {
                if (result.fields === undefined) {
                    result.fields = {}
                }

                result.fields = $.extend(result.fields, controls)
            }
        }
    }

    function containerToJson(container) {
        var containerElements = container.children,
            result = {}

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

    var lastConvertError = null,
        DomToJson = {}

    DomToJson.convert = function(rootContainer) {
        lastConvertError = null

        try {
           return JSON.stringify(containerToJson(rootContainer))
        }
        catch (ex) {
           lastConvertError = ex.message
           return false
        }
    }

    DomToJson.getLastError = function() {
        return lastConvertError
    }

    DomToJson.getAllControlNames = function(rootContainer) {
        var controls = rootContainer.querySelectorAll('ul[data-control-list] > li.control'),
            result = []

        for (var i=controls.length-1; i>=0; i--) {
            var properties = getControlPropertyValues(controls[i])

            if (typeof properties !== 'object') {
                continue
            }

            if (properties['oc.fieldName'] === undefined) {
                continue
            }

            var name = properties['oc.fieldName']

            if (result.indexOf(name) === -1) {
                result.push(name)
            }
        }

        result.sort()

        return result
    }


    $.oc.builder.formbuilder.domToPropertyJson = DomToJson

}(window.jQuery);