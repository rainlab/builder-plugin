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

    // INTERNAL METHODS
    // ============================

    Builder.prototype.init = function() {
        this.registerHandlers()


    }

    Builder.prototype.registerHandlers = function() {
        $(document).on('click', '[data-builder-command]', this.proxy(this.onCommand))
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
        new Builder()
    })

}(window.jQuery);