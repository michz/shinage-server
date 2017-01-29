/**
 * Created by michi on 06.01.17.
 */


var ajaxLoadShow = function() {
    $('#ajax-loading').show();
};

var ajaxLoadHide = function() {
    $('#ajax-loading').hide();
};


// start initialization process
$(document).ready(function() {
    var id = 0;
    var lastSlide = null;

    slides.forEach(function(s) {
        //alert(s.file);
        console.log(s);

        //make sure there is no trailing slash
        img_base = img_base.replace(/\/+$/, '');

        var n = document.createElement("div");
        $(n).addClass('slide-img');
        $(n).addClass('slide-'+id);
        $(n).append("<img src='" + img_base + '/' + s.file_path + "'>");
        $("body").append(n);
        s.id = 'slide-'+id;
        s.next_slide = 'slide-' + (id+1);
        lastSlide = s;
        id++;
    });

    lastSlide.next_slide = 'slide-0';

    //if (slides != undefined) {
    //}
    //
    //TODO: Warten bis Bilder geladen sind?
    // http://stackoverflow.com/a/5623965


    showSlide(getSlide('slide-0'));
    $("#block-loading").hide();
});

var getSlide = function(id) {
    for (var i = 0; i < slides.length; ++i) {
        if (slides[i].id == id) return slides[i];
    }
};

var showSlide = function(s) {
    var div = $('.' + s.id);

    // new slide above previous
    div.css('z-index', 2);
    $('.slide-active').css('z-index', 1);

    // fadeout old slide and remove when finished
    $('.slide-active').animate({opacity:0}, 300, function() {
        $(this).removeClass('slide-active');
    });

    // fadein new slide and fixate when finished
    div.animate({opacity:1}, 300, function() {
        div.addClass('slide-active');
    });

    // next slide after *duration* seconds
    setTimeout(function() { showSlide(getSlide(s.next_slide)); }, s.duration * 1000);
};



/*
var available_types = ['img'];

function Slide(type) {
    this.type = type;
    this.file = "";
    this.duration = 10000;

    this.getDuration = function() {
        return this.duration;
    };
}
    */
