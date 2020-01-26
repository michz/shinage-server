;(function ($, window, document, undefined) {
    "use strict";

    // Create the defaults once
    var pluginName = "mztxScheduler";

    // The actual plugin constructor
    function Plugin(element /*, options */) {
        this.element = element;

        this.screen_colors = [
            {
                dark: '#010432',
                normal: '#9A9EF6',
                light: '#D9DAF6'
            },
            {
                dark: '#012E2D',
                normal: '#8AF4F0',
                light: '#D3F4F3'
            },
            {
                dark: '#4C3902',
                normal: '#FBDE8D',
                light: '#FBF2D8'
            },
            {
                dark: '#4C1002',
                normal: '#FBA28D',
                light: '#FBDFD8'
            },
            {
                dark: '#354802',
                normal: '#DBFA8D',
                light: '#F0FAD7'
            },
            {
                dark: '#1D0130',
                normal: '#CD91F5',
                light: '#E9D6F5'
            }
        ];
        this.screen_colors_count = this.screen_colors.length;
        this.global_evt_src = 1;

        this.url_get_schedule = $(this.element).data('urlGetSchedule');
        this.url_add_scheduled = $(this.element).data('urlAddScheduled');
        this.url_change_scheduled = $(this.element).data('urlChangeScheduled');
        this.url_delete_scheduled = $(this.element).data('urlDeleteScheduled');

        this._name = pluginName;
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        init: function () {
            var todayYear = moment().year();
            var todayMonth = moment().month();
            var todayDay = moment().date();

            var tmpView = 'agendaWeek';
            var vars = window.location.hash.split("&");
            for (var url_i = 0; url_i < vars.length; url_i++) {
                if (vars[url_i].match("^#year")) {
                    todayYear = Number.parseInt(vars[url_i].substring(6), 10);
                }
                if (vars[url_i].match("^month")) {
                    todayMonth = Number.parseInt(vars[url_i].substring(6), 10) - 1;
                }
                if (vars[url_i].match("^day")) {
                    todayDay = Number.parseInt(vars[url_i].substring(4), 10);
                }
                if (vars[url_i].match("^view")) {
                    tmpView = vars[url_i].substring(5);
                }
                if (vars[url_i].match("^selectedScreen")) {
                    this.setSelectedScreen(vars[url_i].substring(15));
                }
            }

            var today = moment();
            today.year(todayYear);
            today.month(todayMonth);
            today.date(todayDay);

            // @TODO visible screens

            $('.calendar', this.element).fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                height: 'auto',
                locale: 'de',
                defaultView: tmpView,
                defaultDate: today,
                viewRender: $.proxy(function (view) {
                    this.setUrl(view);
                }, this),
                navLinks: true,     // can click day/week names to navigate views
                editable: true,
                eventLimit: true,   // allow "more" link when too many events
                nowIndicator: true,
                allDaySlot: false,
                snapDuration: '00:05:00',
                events: [],
                selectable: true,
                selectHelper: true,
                select: $.proxy(this.placePresentationBySelection, this),
                eventResize: $.proxy(this.resizePresentation, this),
                eventRender: $.proxy(function(event, element) {
                    if (event.className[0] == 'fc-helper') {
                        var col = this.screen_colors[$(this.getSelectedScreen()).data('color-set')];
                        $(element).css('background-color', col.dark);
                        $(element).css('color', col.light);
                    }
                    if (!event.presentation) {
                        return;
                    }

                    element.append('<div class="fc-event-title">' + event.presentation.title + '</div>');
                    element.find('.fc-content').append("<div class='event-delete'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QECDRkgLhOZPwAAAJZJREFUOMutU0kOxDAIMz11PoOUR+T/x/5gvjA9jedQ6FBET9RSJBKb4CyAJBwk4fO8nvmscfJNclQJFg/T4AIjN/4xC2cz8NvJh8oZ6g4szjicBGsVVhsVRnYxk+B7E1+PmC5JbxLimsYcOQMR32wF8EGNF4DdtSKCBU0sqbpadRZaGqcp54FLbD1j+yO1v3K7mbrt/ANBpmKW31STdQAAAABJRU5ErkJggg=='></div>");
                    element.find('.fc-content .event-delete').on('click', $.proxy(function(e) {
                        this.deletePresentation(event);
                        e.stopPropagation();
                    }, this));
                }, this),
                eventDrop: $.proxy(this.movePresentation, this)
            });

            $('.sched-screen-list .item', this.element).on("click", $.proxy(function (e) {
                $('.sched-screen-list .item', this.element).removeClass('selected');
                $(e.currentTarget).addClass('selected');
                this.setUrl($('.calendar', this.element).fullCalendar('getView'));
            }, this));
            $('.sched-screen-list .item .selector', this.element).on("click", $.proxy(function (e) {
                var button = $(e.currentTarget).parent();
                button.toggleClass('visible');
                e.stopPropagation();

                if (button.hasClass('visible')) {
                    this.addEventSource(button.get(0), button.data('event-src'));
                    this.setScreenColor(button.get(0));
                } else {
                    $('.calendar', this.element).fullCalendar('removeEventSource', button.data('event-src'));
                    this.setScreenColor(button.get(0));
                }
            }, this));

            $('.sched-screen-list .item:first', this.element).trigger('click');


            var global_i = 0;
            $('.sched-screen-list .item', this.element).each($.proxy(function (k, o) {
                var i = global_i % this.screen_colors_count;
                $(o).data('color-set', i);
                this.setScreenColor(o);
                global_i++;
            }, this));

            $('.sched-screen-list .visible', this.element).each($.proxy(function(key, o) {
                $(o).data('event-src', this.global_evt_src);
                this.addEventSource(o, this.global_evt_src);
                this.global_evt_src++;
            }, this));

            this.initCreateDialog();
        },
        setUrl: function (view) {
            var moment = view.start;
            if (moment) {
                window.location.hash =
                    'year=' + moment.format('YYYY') +
                    '&month=' + moment.format('M') +
                    '&day=' + moment.format('DD') +
                    '&view=' + view.name +
                    '&selectedScreen=' + $(this.getSelectedScreen()).data('guid');
            }
        },
        addEventSource: function (screen, id) {
            var colset = this.screen_colors[$(screen).data('color-set')];
            var bg_col = colset.dark;
            var fg_col = colset.light;
            $('.calendar', this.element).fullCalendar('addEventSource',
                {
                    id: id,
                    url: this.url_get_schedule,
                    type: 'post',
                    data: {
                        screen:  $(screen).data('guid')
                    },
                    error: function () {
                        // @TODO use notify
                        alert('there was an error while fetching events!');
                    },
                    color: bg_col,
                    textColor: fg_col
                }
            );
        },
        saveChanged: function (event, revertFunc) {
            // @TODO Read messages from translations
            window.ajaxLoadShow();
            $.ajax({
                url: this.url_change_scheduled,
                method: 'post',
                data: {
                    'id':           event.id,
                    'screen':       event.screen,
                    'presentation': event.title,
                    'start':        event.start.format('YYYY-MM-DD HH:mm:ss'),
                    'end':          event.end.format('YYYY-MM-DD HH:mm:ss')
                }
            }).fail(function() {
                if (revertFunc != undefined) {
                    revertFunc();
                }
                $.notify("Leider ist beim Ändern des Eintrags ein Fehler aufgetreten.", "error");
            }).done(function() {
                $('.calendar', this.element).fullCalendar('refetchEventSources', event.source);
                $.notify("Änderung gespeichert.", "success");
            }).always(function () {
                window.ajaxLoadHide();
            });
        },
        getSelectedScreen: function () {
            return $('.sched-screen-list .selected', this.element).get(0);
        },
        setSelectedScreen: function (guid) {
            $('.sched-screen-list [data^="' + guid + '"]', this.element).removeClass('selected');
            $('.sched-screen-list [data="' + guid + '"]', this.element).addClass('selected');
            return this;
        },
        setScreenColor: function (screen) {
            var col = this.screen_colors[$(screen).data('color-set')];
            if ($(screen).hasClass('visible')) {
                $(screen).css('background-color', col.dark);
                $(screen).css('color', col.light);
                $(screen).children('.selector').css('background-color', col.normal);
            } else {
                $(screen).css('background-color', col.light);
                $(screen).css('color', col.normal);
            }
        },
        placePresentationBySelection: $.proxy(function (start, end) {
            this.showCreateDialog(
                start,
                end,
                $.proxy(function () {
                    $('.calendar', this.element).fullCalendar('refetchEvents');
                }, this)
            );
        }, this),
        resizePresentation: function(event, delta, revertFunc) {
            this.saveChanged(event, revertFunc);
        },
        movePresentation: function(event, delta, revertFunc) {
            this.saveChanged(event, revertFunc);
        },
        deletePresentation: function (event) {
            // @TODO Move this to dialog or anything other translatable
            var r = confirm("Soll die Präsentation  \"" + event.presentation.title + "\" (" +
                event.start.format('DD.MM.YYYY HH:mm') + ") wirklich entfernt werden?");

            if (r !== true) {
                return;
            }

            /** global: url_delete_scheduled */
            window.ajaxLoadShow();
            $.ajax({
                url: this.url_delete_scheduled,
                method: 'POST',
                data: {
                    'id':  event.id
                }
            }).done(function() {
                $('.calendar', this.element).fullCalendar('refetchEventSources', event.source);
                $.notify("Präsentation entfernt.", "success");
            }).always(function () {
                window.ajaxLoadHide();
            });
        },

        showCreateDialog: function (start, stop, success) {
            $('#scheduler-diag-place').find('#date-start').val(start.format('DD.MM.YYYY HH:mm'));
            $('#scheduler-diag-place').find('#date-end').val(stop.format('DD.MM.YYYY HH:mm'));

            $('#scheduler-diag-place').data('successCallback', success);
            $('#scheduler-diag-place').modal('show');
        },

        initCreateDialog: function () {
            var that = this;
            $('#scheduler-diag-place').modal({
                closable: true,
                onApprove: function () {
                    var form = $('form', this);

                    window.ajaxLoadShow();
                    $.ajax({
                        type: 'post',
                        url: form.attr('action'),
                        data: form.serialize()
                    })
                    .fail(function () {
                        $.notify("Beim Eintragen ist leider ein Fehler aufgetreten.", "error");
                    })
                    .done(function () {
                        $.notify("Die Präsentation wurde eingetragen.", "success");
                        var cb = $('#scheduler-diag-place').data('successCallback');
                        cb();
                    })
                    .always(function () {
                        window.ajaxLoadHide();
                    });
                },
                onHide: function () {
                    $('.calendar', that.element).fullCalendar('unselect');
                },
                onHidden: function () {
                    $('form', this).trigger('reset');
                },
                onShow: function () {
                    $('#inp-screen', this).empty();
                    $('#inp-screen', this).append($('#screen-prototype').clone());
                    $('#inp-pres', this).empty();
                    $('#inp-pres', this).append($('#presentation-prototype').clone());
                    $('#inp-screen', this).children('select').val($(that.getSelectedScreen()).data('guid'));

                    $.datetimepicker.setLocale('de');
                    $('.date-pick', this).datetimepicker({
                        format: 'd.m.Y H:i'
                    });
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

    $('[data-scheduler="true"]').mztxScheduler();
})(jQuery, window, document);
