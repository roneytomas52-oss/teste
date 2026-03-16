(function ($) {
    "use strict";

    $(window).on("load", () => {
        $("#landing-loader").fadeOut(500);
    });

    $(document).ready(function () {
        $(".nav-toggle").on("click", () => {
            $(".nav-toggle").toggleClass("active");
            $(".menu").toggleClass("active");
        });

        $("a[href*='#']").on("click", function (e) {
            const target = $(this.getAttribute("href"));
            if (target.length) {
                e.preventDefault();
                $("html, body").animate({ scrollTop: target.offset().top - 80 }, 500);
                $(".menu").removeClass("active");
                $(".nav-toggle").removeClass("active");
            }
        });

        const header = $("header");
        $(window).on("scroll", function () {
            if ($(this).scrollTop() > 20) {
                header.addClass("active");
            } else {
                header.removeClass("active");
            }
        });

        if ($(".wow").length) {
            const wow = new WOW({
                boxClass: "wow",
                animateClass: "animated",
                offset: 0,
                mobile: true,
                live: true,
            });
            wow.init();
        }
    });
})(jQuery);
