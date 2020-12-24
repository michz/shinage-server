// @TODO Transform this Javascript monster into a jQuery plugin to better bind it and better instantiate it

// @source https://stackoverflow.com/a/20141791
(function($) {
    $.fn.isAfter = function(sel) {
        return this.prevAll().filter(sel).length !== 0;
    };

    $.fn.isBefore = function(sel) {
        return this.nextAll().filter(sel).length !== 0;
    };
})(jQuery);

window.SlideshowEditor = {
    container: null,
    selectedSlide: null,
    slides: [],
    saveUrl: "",
    virtualBaseUrl: 'pool://',
    poolBaseUrl: '',
    messages: {},
    prototypes: {
        image: {"type":"Image", "duration":5000, "title":"Slide", "transition":"", "src":"//via.placeholder.com/500x380"},
        video: {"type":"Video", "duration":0, "title":"Slide", "transition":"", "src":"//via.placeholder.com/500x380"}
    },
    init: function (container, saveUrl) {
        this.saveUrl = saveUrl;
        this.container = $(container);
        this.poolBaseUrl = $(container).data('poolBaseUrl');
        this.messages.savedSuccessfully = $(container).data('messageSavedSuccessfully');
        this.messages.failedSaving = $(container).data('messageFailedSaving');
        this.messages.saving = $(container).data('messageSaving');

        // sortable slide list
        var that = this;
        $("#slides", this.container).sortable({
            items: "> .slide",
            delay: 150, // prevent accidental drag when selecting
            placeholder: 'placeholder slide',
            update: function () {
                that.saveSlides();
            }/*,
            helper: function (e, item) {
                console.log(e, item);
                //Basically, if you grab an unhighlighted item to drag, it will deselect (unhighlight) everything else
                if (!item.hasClass('selected')) {
                    item.addClass('selected').siblings().removeClass('selected');
                }

                //////////////////////////////////////////////////////////////////////
                //HERE'S HOW TO PASS THE SELECTED ITEMS TO THE `stop()` FUNCTION:

                //Clone the selected items into an array
                var elements = item.parent().children('.selected').clone();

                //Add a property to `item` called 'multidrag` that contains the
                //  selected items, then remove the selected items from the source list
                item.data('multidrag', elements).siblings('.selected').remove();

                //Now the selected items exist in memory, attached to the `item`,
                //  so we can access them later when we get to the `stop()` callback

                //Create the helper
                var helper = $('<li/>');
                return helper.append(elements);
            }
             */
        });

        // @TODO Multiselect
        // https://stackoverflow.com/questions/3774755/jquery-sortable-select-and-drag-multiple-list-items

        // select slide handler
        $(this.container).on("click", "#slides > .slide", null, $.proxy(this.selectSlide, this));
        $(this.container).on("click", "#slides > .slide .removeSlide", null, $.proxy(this.removeSlideButton, this));

        $(".settings input", this.container).on("change", $.proxy(this.settingsChanged, this));

        $("#btnSaveSettings").on('click', $.proxy(this.saveSlides, this));
        $("#tabAdd .add").on('click', $.proxy(this.addSlideButton, this));

        return this;
    },
    addSlideButton: function () {
        //var slide = $(e.currentTarget).data('prototype');
        //this.appendSlide(slide);
        //this.saveSlides();

        $("#choseImageOverlay")
            .modal('setting', 'transition', 'fade up')
            .modal('setting', 'observeChanges', true)
            .modal('setting', 'onVisible', function() {
                $("#selectFilesPane").trigger("resize");
            })
            .modal('setting', 'onApprove', $.proxy(function () {
                var that = this;
                var elFinderInstance = $("#selectFilesPane").elfinder('instance');
                var success = false;
                elFinderInstance.exec('getfile').done(function (selectedFiles) {
                    if (undefined === selectedFiles || selectedFiles.length === 0) {
                        return false;
                    }

                    for (var i = 0; i < selectedFiles.length; i++) {
                        var file = selectedFiles[i];
                        if (file.mime === 'directory') {
                            continue;
                        }

                        var slide = {};
                        if (file.mime.startsWith('image')) {
                            slide = that.prototypes.image;
                        } else if (file.mime.startsWith('video')) {
                            slide = that.prototypes.video;
                        } else {
                            continue;
                        }

                        success = true;
                        slide.src = that.generatePoolUrlFromElFinderFile(file);
                        that.appendSlide(slide);
                        that.saveSlides();
                    }

                    return false;
                });

                return success;
            }, this))
            .modal('show');

        //initElFinder();
    },
    selectSlide: function (e) {
        var $alreadySelected = $('#slides > .slide.selected', this.container);
        var $item = $(e.currentTarget);
        var slide = $item.data("slide");

        if (e.ctrlKey === true && e.shiftKey === false) {
            // CTRL Click
            $item.toggleClass('selected');
        } else if (e.ctrlKey === false && e.shiftKey === true && $alreadySelected.length > 0) {
            // Shift Click (and current selection is not empty!)
            if ($item.isBefore($alreadySelected.first())) {
                $alreadySelected.first().prevUntil($item).addClass('selected');
            } else if ($item.isAfter($alreadySelected.last())) {
                $alreadySelected.last().nextUntil($item).addClass('selected');
            }

            // In either case: select the clicked element, even if it is in the middle / somewhere in between
            $item.addClass('selected');
        } else if (e.ctrlKey === true && e.shiftKey === true) {
            // Invalid key combination, ignore
        } else {
            // Just normal click
            $('#slides > .slide', this.container).removeClass('selected');
            $(e.currentTarget).addClass('selected');
        }

        // Write properties in slide settings pane
        // @TODO Adjust for multiselect
        for (var property in slide) {
            if (slide.hasOwnProperty(property)) {
                var value = slide[property];
                var input = $("#tabSlide .settings input[name=" + property + "]", this.container);
                if (input.data('transformer')) {
                    value = this[input.data('transformer') + 'Get'](value);
                }

                input.val(value);
            }
        }

        // this is the SlideshowEditor object; e.currentTarget the event handler's element
        $("#tabSlide .settings", this.container).hide();
        $("#tabSlide .settings." + slide.type, this.container).show();

        this.selectedSlide = slide;
        $('.tabular.menu .item').tab('change tab', 'tabSlide');
    },
    removeSlideButton: function (e) {
        var slide = $(e.currentTarget).parent();
        if (slide.hasClass('selected')) {
            $("#tabSlide .settings." + slide.data("slide").type, this.container).hide();
            $('.tabular.menu .item').tab('change tab', 'tabAdd');
        }

        slide.remove();
        this.saveSlides();
        e.stopPropagation();
        e.preventDefault();
    },
    settingsChanged: function (e) {
        if (this.selectedSlide === null) {
            return;
        }

        var key = $(e.currentTarget).attr('name');
        var value = $(e.currentTarget).val();
        if ($(e.currentTarget).data('transformer')) {
            value = this[$(e.currentTarget).data('transformer') + 'Set'](value);
        }

        this.selectedSlide[key] = value;
        this.saveSlides();
    },
    transformMillisecondsGet: function (value) {
        return Number.parseFloat(value) / 1000.0;
    },
    transformMillisecondsSet: function (value) {
        value = value.replace(/,/g, '.');
        return Number.parseFloat(value) * 1000;
    },
    appendSlide: function (slide) {
        var slideDiv = $("#prototypes .prototype.slide." + slide.type, this.container).clone();
        slideDiv.removeClass("prototype");
        slideDiv.attr('data-slide', JSON.stringify(slide));
        this.provisionSlide(slideDiv, slide);

        // add remove button
        slideDiv.append("<div class='removeSlide'><i class='remove icon'></i></div>");

        $("#slides", this.container).append(slideDiv);
        return this;
    },
    loadSlides: function (slides) {
        for (var i = 0; i < slides.length; i++) {
            this.appendSlide(slides[i]);
        }
        return this;
    },
    provisionSlide: function (div, slide) {
        if (slide.type === "Image") {
            this.provisionImageSlide(div, slide);
        }
        if (slide.type === "Video") {
            this.provisionVideoSlide(div, slide);
        }
        return this;
    },
    provisionImageSlide: function (div, slide) {
        $("img", div).attr("src", this.generateRealUrlFromPoolUrl(slide.src));
        return this;
    },
    provisionVideoSlide: function (div, slide) {
        var filename = slide.src.replace(/^.*[\\\/]/, '');
        $(".videoFileName", div).text(filename);
        return this;
    },
    serialize: function() {
        var data = [];
        $("#slides .slide", this.container).each(function () {
            data.push($(this).data("slide"));
        });
        return data;
    },
    saveSlides: function () {
        var data = this.serialize();
        $.post({
            "url": this.saveUrl,
            "data": {
                "slides": JSON.stringify(data)
            },
            "dataType": "json",
            'beforeSend': $.proxy(function () {
                $(document).trigger('notify-hide');
                $.notify(this.messages.saving, {
                    'autoHide': false,
                    'className': 'info',
                    'clickToHide': false
                });
            }, this),
            'success': $.proxy(function () {
                $('.notifyjs-wrapper').trigger('notify-hide');
                $.notify(this.messages.savedSuccessfully, 'success');
            }, this),
            'error': $.proxy(function () {
                $('.notifyjs-wrapper').trigger('notify-hide');
                $.notify(this.messages.failedSaving, 'error');
            }, this)
        }).done(function () {
            // do nothing by now
        }).fail(function () {
            // @TODO handle save error
            // console.log("Error saving slides: ");
            // console.log(e);
        });
    },

    generatePoolUrlFromElFinderFile: function (file) {
        var url = file.url;
        url = url.replace(this.poolBaseUrl, this.virtualBaseUrl);
        return url;
    },

    generateRealUrlFromPoolUrl: function (poolUrl) {
        return poolUrl.replace(this.virtualBaseUrl, this.poolBaseUrl);
    }
};


