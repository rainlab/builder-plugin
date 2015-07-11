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

    var EntityBase = function() {
        Base.call(this)
    }

    EntityBase.prototype = Object.create(BaseProto)
    EntityBase.prototype.constructor = EntityBase

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

    $.oc.builder.entityControllers.base = EntityBase;
}(window.jQuery);