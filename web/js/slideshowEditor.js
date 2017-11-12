SlideshowEditor = {
    container: null,
    slides: [],
    saveUrl: "",
    init: function (container, saveUrl) {
        this.saveUrl = saveUrl;
        this.container = $(container);

        // sortable slide list
        var that = this;
        $("#slides", this.container).sortable({
            "items": "> .slide",
            "update": function(e, ui) {
                var data = that.serialize();
                $.post({
                    "url": that.saveUrl,
                    "data": {
                        "slides": JSON.stringify(data)
                    },
                    "dataType": "json"
                }).done(function (content) {
                    console.log(content);
                }).fail(function (e) {
                    console.log("Fehler: "+e);
                });
            }
        });

        // select slide handler
        $(this.container).on("click", "#slides > .slide", null, $.proxy(this.selectSlide, this));

        return this;
    },
    selectSlide: function (e) {
        // this is the SlideshowEditor object; e.currentTarget the event handler's element
        console.log(e.currentTarget);
        // @TODO
    },
    appendSlide: function (slide) {
        var slideDiv = $("#prototypes .prototype.slide."+slide.type, this.container).clone();
        slideDiv.removeClass("prototype");
        slideDiv.attr('data-slide', JSON.stringify(slide));
        console.log(slideDiv);
        this.provisionSlide(slideDiv, slide);
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
        return this;
    },
    provisionImageSlide: function (div, slide) {
        $("img", div).attr("src", slide.src);
        return this;
    },
    serialize: function() {
        var data = [];
        var that = this;
        $("#slides .slide", this.container).each(function () {
            data.push($(this).data("slide"));
        });
        return data;
    }
};


