/*
 * Builder client-side plugin data registry
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined)
        $.oc.builder = {}

    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var DataRegistry = function() {
        this.data = {}
        this.requestCache = {}
        this.callbackCache = {}

        Base.call(this)
    }

    /* 
     * Example: 
     * $.oc.builder.dataRegistry.set('rainlab.blog', 'model.forms', 'Categories', formsArray)
     * $.oc.builder.dataRegistry.set('rainlab.blog', 'localization', null, stringsArray) // The registry contains only default language
     */
    DataRegistry.prototype.set = function(plugin, type, subtype, data, params) {
        this.storeData(plugin, type, subtype, data)

        if (type == 'localization' && !subtype) {
            this.localizationUpdated(plugin, params)
        }
    }

    /* 
     * Example: 
     * $.oc.builder.dataRegistry.get($form, 'rainlab.blog', 'model.forms', 'Categories', function(data){ ... })
     */
    DataRegistry.prototype.get = function($formElement, plugin, type, subtype, callback) {
        if (this.data[plugin] === undefined 
            || this.data[plugin][type] === undefined 
            || this.data[plugin][type][subtype] === undefined
            || this.isCacheObsolete(this.data[plugin][type][subtype].timestamp)) {

            return this.loadDataFromServer($formElement, plugin, type, subtype, callback)
        }

        callback(this.data[plugin][type][subtype].data)
    }

    // INTERNAL METHODS
    // ============================

    DataRegistry.prototype.makeCacheKey = function(plugin, type, subtype) {
        var key = plugin + '-' + type

        if (subtype) {
            key += '-' + subtype
        }

        return key
    }

    DataRegistry.prototype.isCacheObsolete = function(timestamp) {
        return (Date.now() - timestamp) > 60000*5 // 5 minutes cache TTL
    }

    DataRegistry.prototype.loadDataFromServer = function($formElement, plugin, type, subtype, callback) {
        var self = this,
            cacheKey = this.makeCacheKey(plugin, type, subtype)

        if (this.requestCache[cacheKey] === undefined) {
            this.requestCache[cacheKey] = $formElement.request('onPluginDataRegistryGetData', {
                data: {
                    registry_plugin_code: plugin,
                    registry_data_type: type,
                    registry_data_subtype: subtype
                }
            }).done(
                function(data) {
                    if (data.registryData === undefined) {
                        throw new Error('Invalid data registry response.')
                    }

                    self.storeData(plugin, type, subtype, data.registryData)
                    self.applyCallbacks(cacheKey, data.registryData)

                    self.requestCache[cacheKey] = undefined
                }
            )
        }

        this.addCallbackToQueue(callback, cacheKey)

        return this.requestCache[cacheKey]
    }

    DataRegistry.prototype.addCallbackToQueue = function(callback, key) {
        if (this.callbackCache[key] === undefined) {
            this.callbackCache[key] = []
        }

        this.callbackCache[key].push(callback)
    }

    DataRegistry.prototype.applyCallbacks = function(key, registryData) {
        if (this.callbackCache[key] === undefined) {
            return
        }

        for (var i=this.callbackCache[key].length-1; i>=0; i--) {
            this.callbackCache[key][i](registryData);
        }

        delete this.callbackCache[key]
    }

    DataRegistry.prototype.storeData  = function(plugin, type, subtype, data) {
        if (this.data[plugin] === undefined) {
            this.data[plugin] = {}
        }

        if (this.data[plugin][type] === undefined) {
            this.data[plugin][type] = {}
        }

        var dataItem = {
            timestamp: Date.now(),
            data: data
        }

        this.data[plugin][type][subtype] = dataItem
    }

    DataRegistry.prototype.clearCache = function(plugin, type) {
        if (this.data[plugin] === undefined) {
            return
        }

        if (this.data[plugin][type] === undefined) {
            return
        }

        this.data[plugin][type] = undefined
    }

    // LOCALIZATION-SPECIFIC METHODS
    // ============================

    DataRegistry.prototype.getLocalizationString = function($formElement, plugin, key, callback) {
        this.get($formElement, plugin, 'localization', null, function(data){
            if (data[key] !== undefined) {
                callback(data[key])
                return
            }

            callback(key)
        })
    }

    DataRegistry.prototype.localizationUpdated = function(plugin, params) {
        $.oc.builder.localizationInput.updatePluginInputs(plugin)

        if (params === undefined || !params.suppressLanguageEditorUpdate) {
            $.oc.builder.indexController.entityControllers.localization.languageUpdated(plugin)
        }

        $.oc.builder.indexController.entityControllers.localization.updateOnScreenStrings(plugin)
    }

    $.oc.builder.dataRegistry = new DataRegistry()
}(window.jQuery);