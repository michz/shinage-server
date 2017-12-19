/**
 * Created by michi on 30.12.16.
 */


var screen_colors = [
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
var screen_colors_count = screen_colors.length;
var global_evt_src = 1;

// @TODO Full status display showing if ajax transactions are running

var placePresentationBySelection = function(start, end) {
    showCreateDialog(
        start,
        end,
        function() {
            $('#calendar').fullCalendar('refetchEvents');
        }
    );
};

var saveChanged = function (event, revertFunc) {
    /** global: uri_change_scheduled */
    ajaxLoadShow();
    $.ajax({
        url: uri_change_scheduled,
        method: 'POST',
        data: {
            'id':           event.id,
            'screen':       event.screen,
            'presentation': event.title,
            'start':        event.start.format('YYYY-MM-DD HH:mm:ss'),
            'end':          event.end.format('YYYY-MM-DD HH:mm:ss')
        }
    }).fail(function() {
        if (revertFunc != undefined) { revertFunc(); }
        $.notify("Leider ist beim Ändern des Eintrags ein Fehler aufgetreten.", "error");
    }).done(function() {
        $('#calendar').fullCalendar('refetchEventSources', event.source);
        $.notify("Änderung gespeichert.", "success");
    }).always(function () {
        ajaxLoadHide();
    });
}

var resizePresentation = function(event, delta, revertFunc) {
    saveChanged(event, revertFunc);
};

var movePresentation = function(event, delta, revertFunc) {
    saveChanged(event, revertFunc);
}

var deletePresentation = function(event) {
    var r = confirm("Soll die Präsentation  \"" + event.presentation.title + "\" (" +
        event.start.format('DD.MM.YYYY HH:mm') + ") wirklich entfernt werden?");

    if (r !== true) {
        return;
    }

    /** global: uri_delete_scheduled */
    ajaxLoadShow();
    $.ajax({
        url: uri_delete_scheduled,
        method: 'POST',
        data: {
            'id':  event.id
        }
    }).done(function() {
        $('#calendar').fullCalendar('refetchEventSources', event.source);
        $.notify("Präsentation entfernt.", "success");
    }).always(function () {
        ajaxLoadHide();
    });
};

var addEventSource = function(screen, id) {
    /** global: uri_get_schedule */
    var colset = screen_colors[$(screen).data('color-set')];
    var bg_col = colset.dark;
    var fg_col = colset.light;
    $('#calendar').fullCalendar('addEventSource',
        {
            id:         id,
            url:        uri_get_schedule,
            type:       'POST',
            data: {
                screen:  $(screen).data('guid'),
                param2: 'somethingelse'
            },
            error: function() {
                alert('there was an error while fetching events!');
            },
            color:      bg_col,
            textColor:  fg_col
            // TODO colors übernehmen
        }
    );
}

var getSelectedScreen = function() {
    return $('#sched-screen-list .selected').get(0);
}

var setScreenColor = function(screen) {
    var col = screen_colors[$(screen).data('color-set')];
    if ($(screen).hasClass('visible')) {
        $(screen).css('background-color', col.dark);
        $(screen).css('color', col.light);
        $(screen).children('.selector').css('background-color', col.normal);
    }
    else {
        $(screen).css('background-color', col.light);
        $(screen).css('color', col.normal);
    }
}

$(document).ready(function() {

    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        height: 'auto',
        locale: 'de',
        //defaultDate: '2016-12-12',
        defaultView: 'agendaWeek',
        navLinks: true,     // can click day/week names to navigate views
        editable: true,
        eventLimit: true,   // allow "more" link when too many events
        allDaySlot: false,
        snapDuration: '00:05:00',
        events: [],
        selectable: true,
        selectHelper: true,
        select: placePresentationBySelection,
        eventResize: resizePresentation,
        eventRender: function(event, element) {
            if (event.className[0] == 'fc-helper') {
                var col = screen_colors[$(getSelectedScreen()).data('color-set')];
                $(element).css('background-color', col.dark);
                $(element).css('color', col.light);
            }
            if (!event.presentation) {
                return;
            }

            element.append('<div class="fc-event-title">' + event.presentation.title + '</div>');
            element.find('.fc-content').append("<div class='event-delete'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QECDRkgLhOZPwAAAJZJREFUOMutU0kOxDAIMz11PoOUR+T/x/5gvjA9jedQ6FBET9RSJBKb4CyAJBwk4fO8nvmscfJNclQJFg/T4AIjN/4xC2cz8NvJh8oZ6g4szjicBGsVVhsVRnYxk+B7E1+PmC5JbxLimsYcOQMR32wF8EGNF4DdtSKCBU0sqbpadRZaGqcp54FLbD1j+yO1v3K7mbrt/ANBpmKW31STdQAAAABJRU5ErkJggg=='></div>");
            element.find('.fc-content .event-delete').on('click', function(e) {
                deletePresentation(event);
                e.stopPropagation();
            });
        },
        eventDrop: movePresentation
    });

    $('#sched-screen-list .item').on("click", function() {
        $('#sched-screen-list .item').removeClass('selected');
        $(this).addClass('selected');
    });
    $('#sched-screen-list .item .selector').on("click", function(e) {
        $(this).parent().toggleClass('visible');
        e.stopPropagation();

        //var col = screen_colors[$(this).parent().data('color-set')];
        if ($(this).parent().hasClass('visible')) {
            addEventSource($(this).parent().get(0), $(this).parent().data('event-src'));
            setScreenColor($(this).parent().get(0));
        } else {
            $('#calendar').fullCalendar('removeEventSource', $(this).parent().data('event-src'));
            setScreenColor($(this).parent().get(0));
        }
    });

    $('#sched-screen-list .item:first').trigger('click');


    var global_i = 0;
    $('#sched-screen-list .item').each(function() {
        var i = global_i % screen_colors_count;
        $(this).data('color-set', i);
        setScreenColor(this);
        global_i++;
    });

    $('#sched-screen-list .visible').each(function() {
        $(this).data('event-src', global_evt_src);
        addEventSource(this, global_evt_src);
        global_evt_src++;
    });
});
