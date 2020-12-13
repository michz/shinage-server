;(function ($, window, document, undefined) {
    "use strict";

    // Create the defaults once
    var pluginName = "mztxOwnerChooser";

    // The actual plugin constructor
    function Plugin(element, options) {
        this._name = pluginName;
        this.element = element;
        this.getPossibleOwnersUrl = $('#chooseOwnerDialog').data('getPossibleOwnersUrl');
        this.modal = null;
        this.settings = $.extend(
            {
                callbackApprove: undefined,
                callbackCancel: undefined,
                callbackAlways: undefined
            },
            options
        );

        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        init: function() {
            var that = this;
            if (this.modal === null) {
                this.modal = $('#chooseOwnerDialog');
                this.modal.modal({
                    onApprove: function () {
                        if (that.settings.callbackApprove !== undefined) {
                            var e = {
                                id: $('select', that.modal).val(),
                                name: $('select option:selected', that.modal).html()
                            };
                            that.settings.callbackApprove(e);
                        }

                        if (that.settings.callbackAlways !== undefined) {
                            that.settings.callbackAlways();
                        }
                    },
                    onDeny: function () {
                        if (that.settings.callbackCancel !== undefined) {
                            that.settings.callbackCancel();
                        }

                        if (that.settings.callbackAlways !== undefined) {
                            that.settings.callbackAlways();
                        }
                    }
                });
            }

            this.modal.modal('show');
            this.loadPossibleOwners();
        },
        loadPossibleOwners: function () {
            window.ajaxLoadShow();
            $.ajax({
                method: 'get',
                url: this.getPossibleOwnersUrl,
                success: function (data) {
                    window.ajaxLoadHide();
                    var $select = $('select', this.modal);
                    $select.empty();
                    Object.keys(data).forEach(function (k) {
                        $select.append('<option value="' + k + '">' + data[k] + '</option>');
                    });
                },
                error: function () {
                    $.notify("{{ 'Could not fetch possible owners'|trans([], 'flashes')|escape('js') }}", { className: 'error', autoHideDelay: 5000 });
                },
                complete: function () {
                    // @TODO Hide loader
                }
            });
        }
    });

    // A really lightweight plugin wrapper around the constructor, preventing against multiple instantiations
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            // Allow multiple instantiation this time!
            //if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName, new Plugin(this, options));
            //}
        });
    };
})(jQuery, window, document);
