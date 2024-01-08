var vw = ($('html').css('font-size').replace('px', ''));
function _sticky() {
    var winscroll = $(window).scrollTop();
    if (winscroll >= vw * 10.625) {
        $("body").addClass("sticky");
    }
}

function init_slider() {
    if ($(".banner_slider").length) {
        var swiper = new Swiper(".banner_slider", {
            spaceBetween: 0,
            loop: true,
            effect: "fade",
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
    }
    if ($(".list_slider").length) {
        $(".list_slider").each(function (index, value) {
            if ($(window).width() > 992) {
                var space = $(this).data("space");
            } else {
                var space = $(this).data("space-mb");
            }
            var swiper2 = new Swiper(this, {
                slidesPerView: "auto",
                spaceBetween: vw * space,
                loop: true,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                }
            });
        });
    }
    if ($(".vuisong_slider").length) {
        $(".vuisong_slider").each(function (index, value) {
            if ($(window).width() > 992) {
                var space = $(this).data("space");
            } else {
                var space = $(this).data("space-mb");
            }
            var swiper2 = new Swiper(this, {
                slidesPerView: "auto",
                spaceBetween: vw * space,
                centeredSlides: true,
                loop: true,
                navigation: {
                    nextEl: ".vuisongvungvang .swiper-button-next",
                    prevEl: ".vuisongvungvang .swiper-button-prev",
                }
            });
        });
    }
}

function _scrollTo(hash) {
    if (hash && $(hash).length) {
        let nav = $("header").height();
        $("html").animate({
            scrollTop: $(hash).offset().top - nav
        });
    }
}
function custom_form_select() {
    $("select.form-select").on("change", function () {
        "" !== $(this).val()
            ? $(this).addClass("shown")
            : $(this).removeClass("shown");
    });
}
function callPopup(url) {
    $.fancybox.open({
        src: url,
        type: "inline",
        opts: {
            margin: 0,
            padding: 0,
            touch: false,
            focus: false,
            keyboard: false,
            clickContent: false,
            clickOutside: false,
            clickSlide: false,
            autoFocus: false,
            smallBtn: false,
            toolbar: false,
            dblclickContent: false,
            dblclickSlide: false,
            dblclickOutside: false,
            mobile: {
                preventCaptionOverlap: false,
                idleTime: false,
                clickContent: false,
                clickSlide: false,
                dblclickContent: false,
                dblclickSlide: false,
            },
            afterLoad: function (instance, current) {
                instance.update();
            },
            beforeClose: function () {
                $(".error").tooltip("hide");
            },
        },
    });
}
$(function () {
    $('.filter_attribute').click(function(){
        var listAttribute = '';

        $('.filter_attribute').each(function(){
            if($(this).is(':checked'))
            listAttribute = listAttribute + $(this).attr('data') + ',';
        })

        var currentURL = window.location.href;
        //alert(currentURL);

        $('.row.row_sp').hide();
        $('.loading').show();

        $.ajax({
            url: currentURL + '?filter_atb=true' + '&atb_id=' +listAttribute,  // URL to fetch data from
            method: 'GET', // HTTP method
            success: function(data) {
               $('.row.row_sp').html(data);
               $('.row.row_sp').show();
               $('.loading').hide();
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.error(status + ": " + error); // Log error to console
            }
        });
    })

    $("#loader").show().delay(1000).fadeOut("fast");
    if($(".nav3").length){
        $('.nav3').appendTo('header');
        $(".header_height").height($("header").height());
    }
    init_slider();
    SVGInjector($(".cta_arrow,.dropdown_arrow"));
    custom_form_select();

});
$(window).on("load", function () {
});
var lastScrollTop = 0;
$(window).on("scroll", function (event) {
    var st = $(this).scrollTop();
    if (st > lastScrollTop) {
        _sticky();
    } else {
        $("body").removeClass("sticky");
    }
    lastScrollTop = st;
    const scrollSpy = new bootstrap.ScrollSpy($("#page"), {
        target: '.nav3 .nav',
        //rootMargin:'0px 0px -61%'
    })
});
