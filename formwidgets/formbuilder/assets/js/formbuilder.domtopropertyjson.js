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

    function listToJson(list) {
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

            result[fieldName] = values

            // TODO: for the Repeater we should check if the control element
            // has containers. See "Form builder" section in the Builder's notes.ft.
        }

        return result
    }

    function containerToJson(container) {
        var controlLists = container.children,
            result = {
                fields: {} // The "fields" property name is fixed, but could be customized later if needed.
            }

        for (var i=0, len=controlLists.length; i<len; i++) {
            var controlList = controlLists[i]

            if (controlList.tagName !== 'UL') {
                throw new Error('Control container can contain only UL elements.')
            }

            if (!controlList.hasAttribute('data-control-list')) {
                throw new Error('Control container can contain only UL elements with data-control-list attribute.')
            }

            var listProperties = listToJson(controlList),
                listName = String(controlList.getAttribute('data-control-list'))

            if (listName.length > 0) {
                if (result[listName] === undefined) {
                    result[listName] = {
                        fields: {}
                    }
                }

                result[listName].fields = listProperties
            }
            else {
                result.fields = listProperties
            }
        }

        return result
    }

    var DomToJson = {}

    DomToJson.convert = function(rootContainer) {
        return containerToJson(rootContainer)
    }

    $.oc.builder.formbuilder.domToPropertyJson = DomToJson

}(window.jQuery);