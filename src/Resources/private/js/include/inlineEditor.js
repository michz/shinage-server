;(function ($, window, document, undefined) {
    "use strict";

    // Create the defaults once
    var pluginName = "mztxInlineEditor";

    // The actual plugin constructor
    function Plugin(element /*, options */) {
        this.element = element;
        this.subject = $(element).data('contentEditableSubject');
        this.url = $(element).data('contentEditableSaveCallback');

        if ($(element).data('contentEditableAllowNewline') === undefined) {
            this.allowNewline = true;
        } else {
            this.allowNewline = $(element).data('contentEditableAllowNewline');
        }

        this._name = pluginName;
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        init: function() {
            $(this.element).attr('contenteditable', 'true');
            $(this.element).addClass('dimmable');

            // If newline is allowed, save with CTRL+Enter, otherwise with enter
            if (this.allowNewline == true) {
                $(this.element).css('white-space', 'pre-wrap');

                $(this.element).on('keypress', $.proxy(function (event) {
                    if ((event.charCode == 10 || event.charCode == 13) && event.ctrlKey == true) {
                        this.element.blur();
                        return false;
                    }

                    return true;
                }, this));
            } else {
                $(this.element).on('keypress', $.proxy(function (event) {
                    if (event.charCode == 10 || event.charCode == 13) {
                        this.element.blur();
                        return false;
                    }

                    return true;
                }, this));
            }

            $(this.element).on('blur', $.proxy(function () {
                var value = $(this.element).get(0).innerText;
                var dimmer = $('<div class="ui active inverted dimmer"><div class="ui loader"></div></div>');
                $(this.element).append(dimmer);

                this.save(value, function () {
                    dimmer.remove();
                });
            }, this));
        },
        save: function (value, callback) {
            $.ajax({
                method: 'post',
                url: this.url,
                data: {
                    subject: this.subject,
                    value: value
                },
                success: function () {
                    $.notify('Änderung gespeichert.', 'success');
                },
                error: function () {
                    $.notify('Fehler beim Speichern der Änderung.', 'error');
                },
                complete: function () {
                    callback();
                }
            });
        }
    });

    // A really lightweight plugin wrapper around the constructor, preventing against multiple instantiations
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            }
        });
    };

    $('[data-content-editable="true"]').mztxInlineEditor();
})(jQuery, window, document);
