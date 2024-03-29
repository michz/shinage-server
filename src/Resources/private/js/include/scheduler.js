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
        this.url_change_scheduled = $(this.element).data('urlChangeScheduled');
        this.url_delete_scheduled = $(this.element).data('urlDeleteScheduled');

        this._name = pluginName;
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        init: function () {
            const that = this;
            let today = new Date();
            let tmpView = 'timeGridWeek';
            let vars = {};
            window.location.hash.split("&").forEach(function(v) {
                const splitted = v.replace(new RegExp('^[#]+'), '').split('=');
                vars[splitted[0]] = splitted[1];
            });

            // @TODO Represent visible screens in URL
            Object.keys(vars).forEach(function (key) {
                var value = vars[key];
                switch (key) {
                    case 'year':
                        today.setFullYear(parseInt(value, 10));
                        break;
                    case 'month':
                        today.setMonth(parseInt(value, 10) - 1);
                        break;
                    case 'day':
                        today.setDate(parseInt(value, 10));
                        break;
                    case 'view':
                        tmpView = value;
                        break;
                    case 'selectedScreen':
                        // @TODO Adjust for new fullcalendar version
                        that.setSelectedScreen(value);
                        break;
                }
            });

            this.calendarElement = $('.calendar', this.element);

            // eslint-disable-next-line no-undef
            this.calendar = new FullCalendar.Calendar(this.calendarElement.get(0), {
                allDaySlot: false,
                datesSet: () => {
                    this.setUrl();
                },
                editable: true,
                eventResize: $.proxy(this.resizePresentation, this),
                eventDrop: $.proxy(this.movePresentation, this),
                eventClick: $.proxy(this.clickScheduledPresentation, this),
                eventContent: $.proxy(function(contentEvent) {
                    let title = '';
                    const event = contentEvent.event;

                    if ('presentation' in contentEvent.event.extendedProps) {
                        title = `
                            <div class="fc-event-title-container">
                                <div class="fc-event-title fc-sticky">${contentEvent.event.extendedProps.presentation.title}</div>
                            </div>
                        `;
                    }

                    return { html: `
                        <div class="fc-event-main-frame">
                            <div class="fc-event-time">
                                ${String(event.start.getHours()).padStart(2, '0')}:${String(event.start.getMinutes()).padStart(2, '0')} -
                                ${String(event.end.getHours()).padStart(2, '0')}:${String(event.end.getMinutes()).padStart(2, '0')}
                            </div>
                            ${title}
                        </div>
                    ` };
                }, this),
                eventDidMount: $.proxy(function(contentEvent) {
                    if (contentEvent.isMirror) {
                        var col = this.screen_colors[$(this.getSelectedScreen()).data('color-set')];
                        $(contentEvent.el)
                            .css('background-color', col.dark)
                            .css('color', col.light);
                    }
                }, this),
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                height: 'auto',
                initialDate: today,
                initialView: tmpView,
                locale: 'de',
                nowIndicator: true,
                select: $.proxy(this.placePresentationBySelection, this),
                selectable: true,
                selectMirror: true,
                slotDuration: '00:30:00',
                snapDuration: '00:05:00'
            });
            this.calendar.render();

            $('.sched-screen-list .item', this.element).on("click", $.proxy(function (e) {
                this.setSelectedScreen($(e.currentTarget).data('guid'));
                this.setUrl();
            }, this));
            $('.sched-screen-list .item .selector', this.element).on("click", $.proxy(function (e) {
                const button = $(e.currentTarget).parent();
                button.toggleClass('visible');
                e.stopPropagation();

                if (button.hasClass('visible')) {
                    this.addEventSource(button.get(0), button.data('event-src'));
                    this.setScreenColor(button.get(0));
                } else {
                    const eventSource = this.calendar.getEventSourceById(button.data('event-src'));
                    if (undefined !== eventSource) {
                        eventSource.remove();
                    }

                    this.setScreenColor(button.get(0));
                }
            }, this));

            // If no screen is selected yet, preselect the first
            if ($('.sched-screen-list .selected', this.element).length < 1) {
                $('.sched-screen-list .item:first', this.element).trigger('click');
            }

            let global_i = 0;
            $('.sched-screen-list .item', this.element).each($.proxy(function (k, o) {
                $(o).data('color-set', global_i % this.screen_colors_count);
                this.setScreenColor(o);
                global_i++;
            }, this));

            $('.sched-screen-list .visible', this.element).each($.proxy(function(key, o) {
                $(o).data('event-src', this.global_evt_src);
                this.addEventSource(o, this.global_evt_src);
                this.global_evt_src++;
            }, this));

            $('#scheduler-diag-edit .action--delete').on('click', function (e) {
                const scheduledPresentation = $(e.currentTarget).closest('.modal')
                    .find('input[name="scheduledPresentationId"]').data('scheduled-presentation');
                that.hideEditDialog();
                that.deletePresentation(scheduledPresentation);
            });

            this.initEditDialog();
        },
        setUrl: function () {
            window.location.hash =
                'year=' + this.calendar.view.currentStart.getFullYear() +
                '&month=' + (this.calendar.view.currentStart.getMonth() + 1) +
                '&day=' + this.calendar.view.currentStart.getDate() +
                '&view=' + this.calendar.view.type +
                '&selectedScreen=' + $(this.getSelectedScreen()).data('guid');
        },
        addEventSource: function (screen, id) {
            const colset = this.screen_colors[$(screen).data('color-set')];
            this.calendar.addEventSource({
                id: id,
                url: this.url_get_schedule,
                method: 'POST',
                extraParams: {
                    screen:  $(screen).data('guid')
                },
                error: function () {
                    $.notify("Leider ist beim Laden der Einträge ein Fehler aufgetreten.", "error");
                },
                color: colset.dark,
                textColor: colset.light
            });
        },
        saveChanged: function (resizeEvent) {
            // @TODO Read messages from translations
            window.ajaxLoadShow();
            $.ajax({
                url: this.url_change_scheduled,
                method: 'post',
                data: this.getInternalScheduleEntryFromFullCalendarEvent(resizeEvent.event)
            }).fail(function() {
                $.notify("Leider ist beim Ändern des Eintrags ein Fehler aufgetreten.", "error");
            }).done(function() {
                resizeEvent.event.source.refetch();
                $.notify("Änderung gespeichert.", "success");
            }).always(function () {
                window.ajaxLoadHide();
            });
        },
        getSelectedScreen: function () {
            return $('.sched-screen-list .selected', this.element).get(0);
        },
        getSelectedScreenGuid: function () {
            return $('.sched-screen-list .selected', this.element).data('guid');
        },
        setSelectedScreen: function (guid) {
            $('.sched-screen-list .item[data-guid!="' + guid + '"]', this.element).removeClass('selected');
            $('.sched-screen-list .item[data-guid="' + guid + '"]', this.element).addClass('selected');
        },
        setScreenColor: function (screen) {
            const col = this.screen_colors[$(screen).data('color-set')];
            if ($(screen).hasClass('visible')) {
                $(screen).css('background-color', col.dark);
                $(screen).css('color', col.light);
                $(screen).children('.selector').css('background-color', col.normal);
            } else {
                $(screen).css('background-color', col.light);
                $(screen).css('color', col.normal);
            }
        },
        placePresentationBySelection: function (info) {
            this.showEditDialog(undefined, info.start, info.end);
        },
        resizePresentation: function(resizeEvent) {
            this.saveChanged(resizeEvent);
        },
        movePresentation: function(event, delta, revertFunc) {
            this.saveChanged(event, revertFunc);
        },
        deletePresentation: function (event) {
            const that = this;

            // @TODO Move this to dialog or anything other translatable
            const r = confirm("Soll die Präsentation  \"" + event.presentation.title + "\" (" +
                event.start + ") wirklich entfernt werden?");

            if (r !== true) {
                return;
            }

            window.ajaxLoadShow();
            $.ajax({
                url: this.url_delete_scheduled,
                method: 'POST',
                data: {
                    'id':  event.id
                }
            }).done(function() {
                that.calendar.refetchEvents();
                $.notify("Präsentation entfernt.", "success");
            }).always(function () {
                window.ajaxLoadHide();
            });
        },

        clickScheduledPresentation: function (e) {
            this.showEditDialog(e.event);
        },

        hideEditDialog: function () {
            $('#scheduler-diag-edit').modal('hide');
        },

        showEditDialog: function (event, start, stop) {
            const $schedulerDialog = $('#scheduler-diag-edit');

            if (event !== undefined) {
                // Edit an existing schedule item
                $schedulerDialog.find('.action--delete').css('visibility', 'visible');
                $schedulerDialog.data('messageSuccess', $schedulerDialog.data('messageEditSuccess'));
                $schedulerDialog.data('messageError', $schedulerDialog.data('messageEditError'));

                $schedulerDialog.find('input[name="scheduledPresentationId"]').val(event.id);
                $schedulerDialog.find('input[name="scheduledPresentationId"]').data('scheduled-presentation', this.getInternalScheduleEntryFromFullCalendarEvent(event, false));
                $schedulerDialog.find('select[name="presentation"]').val(event.extendedProps.presentation.id);
                $schedulerDialog.find('select[name="screen"]').val(event.extendedProps.screen);

                start = event.start;
                stop = event.end;
            } else {
                // Create a new schedule item
                $schedulerDialog.find('.action--delete').css('visibility', 'hidden');
                $schedulerDialog.data('messageSuccess', $schedulerDialog.data('messageCreateSuccess'));
                $schedulerDialog.data('messageError', $schedulerDialog.data('messageCreateError'));

                $schedulerDialog.find('input[name="scheduledPresentationId"]').val('');
                $schedulerDialog.find('input[name="scheduledPresentationId"]').data('scheduled-presentation', undefined);
                $schedulerDialog.find('select[name="presentation"]').val('');

                // Preselect currently selected screen
                $schedulerDialog.find('select[name="screen"]').val(this.getSelectedScreenGuid());
            }

            $schedulerDialog.find('input[name="start"]').val(this.formatDateTime(start));
            $schedulerDialog.find('input[name="end"]').val(this.formatDateTime(stop));

            $schedulerDialog.modal('show');
        },

        getInternalScheduleEntryFromFullCalendarEvent: function (event, flat = true) {
            let r = event.toPlainObject({ collapseExtendedProps: true });
            if (flat) {
                r.presentation = r.presentation.id;
            }

            r.start = this.formatDateTime(event.start);
            r.end = this.formatDateTime(event.end);

            return r;
        },

        formatDateTime: function (date) {
            return date.getFullYear()  + "-" +
                String(date.getMonth() + 1).padStart(2, '0') + "-" +
                String(date.getDate()).padStart(2, '0') + " " +
                String(date.getHours()).padStart(2, '0') + ":" +
                String(date.getMinutes()).padStart(2, '0') + ":" +
                String(date.getSeconds()).padStart(2, '0');
        },

        initEditDialog: function () {
            var that = this;
            $('#scheduler-diag-edit').modal({
                closable: true,
                onApprove: function () {
                    var form = $('form', this);

                    window.ajaxLoadShow();
                    $.ajax({
                        type: 'post',
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function () {
                            $.notify($('#scheduler-diag-edit').data('messageSuccess'), 'success');
                        },
                        error: function () {
                            $.notify($('#scheduler-diag-edit').data('messageError'), 'error');
                        },
                        complete: function () {
                            window.ajaxLoadHide();
                            that.calendar.refetchEvents();
                        }
                    });
                },
                onHide: function () {
                    that.calendar.unselect();
                },
                onHidden: function () {
                    $('form', this).trigger('reset');
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
