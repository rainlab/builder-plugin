/*
 * Code List
 */
+function ($) { "use strict";

    var CodeList = function (form, alias) {
        this.$form = $(form);
        this.alias = alias;

        this.$form.on('ajaxSuccess', $.proxy(this.onAjaxSuccess, this));
        this.$form.on('click', 'ul.list > li.directory > a', $.proxy(this.onDirectoryClick, this));
        this.$form.on('click', 'ul.list > li.file > a', $.proxy(this.onFileClick, this));
        this.$form.on('click', 'p.parent > a', $.proxy(this.onDirectoryClick, this));
        this.$form.on('click', 'a[data-control=delete-asset]', $.proxy(this.onDeleteClick, this));
        this.$form.on('oc.list.setActiveItem', $.proxy(this.onSetActiveItem, this));

        this.setupUploader();
    }

    // Event handlers
    // =================

    CodeList.prototype.onDirectoryClick = function(e) {
        this.gotoDirectory(
            $(e.currentTarget).data('path'),
            $(e.currentTarget).parent().hasClass('parent')
        );

        return false;
    }

    CodeList.prototype.gotoDirectory = function(path, gotoParent) {
        var $container = $('div.list-container', this.$form),
            self = this;

        if (gotoParent !== undefined && gotoParent) {
            $container.addClass('goBackward');
        }
        else {
            $container.addClass('goForward');
        }

        $.oc.stripeLoadIndicator.show();
        this.$form.request(this.alias+'::onOpenDirectory', {
            data: {
                path: path,
                d: 0.2
            },
            complete: function() {
                self.updateUi()
                $container.trigger('oc.scrollbar.gotoStart')
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $container.removeClass('goForward goBackward')
                alert(jqXHR.responseText.length ? jqXHR.responseText : jqXHR.statusText)
            }
        }).always(function(){
            $.oc.stripeLoadIndicator.hide()
        })
    }

    CodeList.prototype.onDeleteClick = function(e) {
        var $el = $(e.currentTarget),
            self = this;

        if (!confirm($el.data('confirmation'))) {
            return false;
        }

        this.$form.request(this.alias+'::onDeleteFiles', {
            success: function(data) {
                if (data.error !== undefined && $.type(data.error) === 'string' && data.error.length) {
                    $.oc.flashMsg({text: data.error, 'class': 'error'});
                }
            },
            complete: function() {
                self.refresh();
            }
        });

        return false;
    }

    CodeList.prototype.onAjaxSuccess = function() {
        this.updateUi();
    }

    CodeList.prototype.onUploadFail = function(file, message) {
        if (file.xhr.status === 413) {
            message = 'Server rejected the file because it was too large, try increasing post_max_size';
        }
        if (!message) {
            message = 'Error uploading file';
        }

        $.oc.alert(message);

        this.refresh();
    }

    CodeList.prototype.onUploadSuccess = function(file, data) {
        if (data !== 'success') {
            $.oc.alert(data);
        }
    }

    CodeList.prototype.onUploadComplete = function(file, data) {
        $.oc.stripeLoadIndicator.hide();
        this.refresh();
    }

    CodeList.prototype.onUploadStart = function() {
        $.oc.stripeLoadIndicator.show();
    }

    CodeList.prototype.onFileClick = function(event) {
        var $link = $(event.currentTarget),
            $li = $link.parent();

        var e = $.Event('open.oc.list', {relatedTarget: $li.get(0), clickEvent: event});
        this.$form.trigger(e, this);

        if (e.isDefaultPrevented()) {
            return false;
        }
    }

    CodeList.prototype.onSetActiveItem = function(event, dataId) {
        $('ul li.file', this.$form).removeClass('active');
        if (dataId) {
            $('ul li.file[data-id="'+dataId+'"]', this.$form).addClass('active');
        }
    }

    // Service functions
    // =================

    CodeList.prototype.updateUi = function() {
        $('button[data-control=asset-tools]', self.$form).trigger('oc.triggerOn.update');
    }

    CodeList.prototype.refresh = function() {
        var self = this;

        this.$form.request(this.alias+'::onRefresh', {
            complete: function() {
                self.updateUi();
            }
        });
    }

    CodeList.prototype.setupUploader = function() {
        var self = this,
            $link = $('[data-control="upload-assets"]', this.$form),
            uploaderOptions = {
                method: 'POST',
                url: window.location,
                paramName: 'file_data',
                previewsContainer: $('<div />').get(0),
                clickable: $link.get(0),
                timeout: 0,
                headers: {}
            };

        // Add CSRF token to headers
        var token = $('meta[name="csrf-token"]').attr('content');
        if (token) {
            uploaderOptions.headers['X-CSRF-TOKEN'] = token;
        }

        var dropzone = new Dropzone($('<div />').get(0), uploaderOptions);
        dropzone.on('error', $.proxy(self.onUploadFail, self));
        dropzone.on('success', $.proxy(self.onUploadSuccess, self));
        dropzone.on('complete', $.proxy(self.onUploadComplete, self));
        dropzone.on('sending', function(file, xhr, formData) {
            $.each(self.$form.serializeArray(), function (index, field) {
                formData.append(field.name, field.value);
            });
            xhr.setRequestHeader('X-OCTOBER-REQUEST-HANDLER', self.alias + '::onUpload');
            self.onUploadStart();
        });
    }

    $(document).on('render', function() {
        var $container = $('#code-list-container');
        if ($container.data('oc.codeListAttached') === true) {
            return;
        }

        $container.data('oc.codeListAttached', true);
        new CodeList(
            $container.closest('form'),
            $container.data('alias')
        );
    });

}(window.jQuery);
