/*
 * Builder Index controller Code entity controller
 */
+function ($) { "use strict";

    if ($.oc.builder === undefined) {
        $.oc.builder = {};
    }

    if ($.oc.builder.entityControllers === undefined) {
        $.oc.builder.entityControllers = {};
    }

    var Base = $.oc.builder.entityControllers.base,
        BaseProto = Base.prototype;

    var Code = function(indexController) {
        Base.call(this, 'code', indexController);
    }

    Code.prototype = Object.create(BaseProto);
    Code.prototype.constructor = Code;

    // PUBLIC METHODS
    // ============================

    Code.prototype.registerHandlers = function() {
    }

    Code.prototype.cmdCreateCode = function(ev) {
        this.indexController.openOrLoadMasterTab($(ev.target), 'onCodeOpen', this.newTabId());
    }

    Code.prototype.cmdOpenCode = function(ev) {
        var path = $(ev.currentTarget).data('path'),
            pluginCode = $(ev.currentTarget).data('pluginCode');

        var result = this.indexController.openOrLoadMasterTab($(ev.target), 'onCodeOpen', this.makeTabId(pluginCode+'-'+path), {
            fileName: path
        });

        if (result !== false) {
            result.done(this.proxy(this.updateFormEditorMode, this));
        }
    }

    Code.prototype.cmdSaveCode = function(ev) {
        var $target = $(ev.currentTarget),
            $form = $target.closest('form'),
            $inspectorContainer = $form.find('.inspector-container')

        if (!$.oc.inspector.manager.applyValuesFromContainer($inspectorContainer)) {
            return
        }

        $target.request('onCodeSave').done(
            this.proxy(this.saveCodeDone)
        )
    }

    Code.prototype.saveCodeDone = function(data) {
        if (data['builderResponseData'] === undefined) {
            throw new Error('Invalid response data');
        }

        var $masterTabPane = this.getMasterTabsActivePane();

        this.getIndexController().unchangeTab($masterTabPane);

        this.updateFormEditorMode();
    }

    Code.prototype.getCodeList = function() {
        return $('#layout-side-panel form[data-content-id=code] .control-codelist')
    }

    Code.prototype.updateFormEditorMode = function() {
        var $masterTabPane = this.getMasterTabsActivePane();

        var modes = {
            css: "css",
            htm: "html",
            html: "html",
            js: "javascript",
            json: "json",
            less: "less",
            md: "markdown",
            sass: "sass",
            scss: "scss",
            txt: "plain_text",
            yaml: "yaml",
            xml: "xml",
            php: "php"
        };

        var fileName = $('input[name=fileName]', $masterTabPane).val(),
            parts = fileName.split('.'),
            extension = 'txt',
            mode = 'plain_text',
            editor = $('[data-control=codeeditor]', $masterTabPane);

        if (parts.length >= 2) {
            extension = parts.pop().toLowerCase();
        }

        if (modes[extension] !== undefined) {
            mode = modes[extension];
        }

        var setEditorMode = function() {
            window.setTimeout(function() {
                editor.data('oc.codeEditor').editor.getSession().setMode({path: 'ace/mode/'+mode})
            }, 200);
        };

        setEditorMode();
    }

    // REGISTRATION
    // ============================

    $.oc.builder.entityControllers.code = Code;

}(window.jQuery);
