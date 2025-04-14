// barrier-free - high contrast

window.addEventListener('DOMContentLoaded', function () {
  const contrast = document.cookie.match(/contrast=([^;]+)/);

  if (contrast && contrast[1] === 'on') {
	document.body.classList.add('high-contrast');
	document.documentElement.classList.add('high-contrast');

	// Alle Buttons aktualisieren
	document.querySelectorAll('.contrast-toggle').forEach(btn => {
	  btn.setAttribute('aria-pressed', 'true');
	});
  }

  // EventListener für alle Buttons
  document.querySelectorAll('.contrast-toggle').forEach(btn => {
	btn.addEventListener('click', function () {
	  const isActive = document.body.classList.toggle('high-contrast');
	  document.documentElement.classList.toggle('high-contrast', isActive);

	  // Alle Buttons updaten (für Sync)
	  document.querySelectorAll('.contrast-toggle').forEach(b => {
		b.setAttribute('aria-pressed', isActive);
	  });

	  // Cookie setzen, 30 Tage
	  document.cookie = "contrast=" + (isActive ? "on" : "off") + "; path=/; max-age=2592000";

	  // Seite neu laden
	  location.reload();
	});
  });
});

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.icon_attribute').forEach(function (el) {
	el.addEventListener('click', function (e) {
	  e.stopPropagation(); // 💥 verhindert Klick auf <a>
	  e.preventDefault();  // optional – falls du z. B. <a> im Icon hättest
	});
  });
});
/*
function removeImagesForHighContrast() {
  if (document.body.classList.contains('high-contrast')) {
	const icons = document.querySelectorAll('.icon'); // Klassenname beliebig
	icons.forEach(icon => icon.remove());
  }
}
// 1. Beim Laden der Seite: Zustand aus localStorage anwenden
  if (localStorage.getItem('contrast') === 'on') {
	document.body.classList.add('high-contrast');
	document.getElementById('contrast-toggle').setAttribute('aria-pressed', 'true');
	removeImagesForHighContrast();
  }

  // 2. Button-Click: Umschalten + speichern
  document.getElementById('contrast-toggle').addEventListener('click', function () {
	const body = document.body;
	const isActive = body.classList.toggle('high-contrast');

	this.setAttribute('aria-pressed', isActive);

	if (isActive) {
	  localStorage.setItem('contrast', 'on');
	  removeImagesForHighContrast(); 
	} else {
	  localStorage.removeItem('contrast');
	}
  });
 */ 

$(document).on('click', '.zustimmen', function() {
	event.preventDefault();
	$('#cookie_disclaimer').slideUp("slow");
	var nDays = 60;
	var aDays = 720;
	var cookieValue = "true";
	var today = new Date();
	var expire = new Date();
	var expireDel = new Date();
	expire.setTime(today.getTime() + 3600000*24*nDays);
	expireDel.setTime(today.getTime() - 3600000*24*100);
	var cookieName = "cookie_disclaimer";
	document.cookie = cookieName+"="+escape(cookieValue)+";expires="+expire.toGMTString()+";path=/";
	// Die aktuelle URL ohne den Query-String erhalten
	var urlWithoutQueryString = window.location.href.split('?')[0];	
	// Die URL umschreiben, um den Query-String zu entfernen
	window.history.replaceState({}, document.title, urlWithoutQueryString);
	// Zur neuen URL navigieren
	window.location.href = urlWithoutQueryString;
})

var vw = ($('html').css('font-size').replace('px', ''));
function _sticky() {
    var winscroll = $(window).scrollTop();
    if (winscroll >= vw * 10.625) {
        $("body").addClass("sticky");
    }
}

// $(document).ready(function() {
// 	$('.categoryHL').on('click', function() {
// 		var isopen = $(this).hasClass('open');
// 		$('.body').removeClass('showw');
// 		$('.categoryHL').removeClass('open');
// 		// $(this).next('.body').toggleClass('show');
// 		// $(this).toggleClass('open');
// 		if(isopen) {
// 			$(this).next('.body').removeClass('showw');
// 			$(this).removeClass('open');			
// 		} else {
// 			$(this).next('.body').addClass('showw');
// 			$(this).addClass('open');			
// 		}
// 	});
// });


$(document).ready(function() {
	$('.linkbox, .CTA').on("click", function(event) {
		// Wenn der Klick auf ein <a> oder Kind davon ging → nichts tun
		if ($(event.target).closest('a').length) {
			return;
		}
		event.preventDefault();
		var URL = $(this).attr("ref");
		console.log(URL);
		location.href = URL;		
	});
	// Beim Klick auf .mega-link das Mega-Menü anzeigen/verstecken
	$('.mega-link').click(function() {
		// Schließe alle anderen Mega-Menüs
		$('.mega-menu').not($(this).find('.mega-menu')).hide();		
		// Zeige oder verstecke das aktuelle Mega-Menü
		$(this).find('.mega-menu').toggle();
	});	
	// Schließe das Mega-Menü, wenn außerhalb davon geklickt wird
	$(document).click(function(event) {
		if (!$(event.target).closest('.mega-link').length) {
			$('.mega-menu').hide();
		}
	});
});

function init_slider() {
    // if ($(".banner_slider").length) {
    //     var swiper = new Swiper(".banner_slider", {
    //         spaceBetween: 0,
    //         loop: true,
    //         effect: "fade",
    //         autoplay: {
    //             delay: 4000,
    //             disableOnInteraction: false,
    //         },
    //         navigation: {
    //             nextEl: ".swiper-button-next",
    //             prevEl: ".swiper-button-prev",
    //         },
    //         pagination: {
    //             el: ".swiper-pagination",
    //             clickable: true,
    //         },
    //     });
    // }
    // if ($(".list_slider").length) {
    //     $(".list_slider").each(function (index, value) {
    //         if ($(window).width() > 992) {
    //             var space = $(this).data("space");
    //         } else {
    //             var space = $(this).data("space-mb");
    //         }
    //         var swiper2 = new Swiper(this, {
    //             slidesPerView: "auto",
    //             spaceBetween: vw * space,
    //             loop: true,
    //             navigation: {
    //                 nextEl: ".swiper-button-next",
    //                 prevEl: ".swiper-button-prev",
    //             }
    //         });
    //     });
    // }
    // if ($(".vuisong_slider").length) {
    //     $(".vuisong_slider").each(function (index, value) {
    //         if ($(window).width() > 992) {
    //             var space = $(this).data("space");
    //         } else {
    //             var space = $(this).data("space-mb");
    //         }
    //         var swiper2 = new Swiper(this, {
    //             slidesPerView: "auto",
    //             spaceBetween: vw * space,
    //             centeredSlides: true,
    //             loop: true,
    //             navigation: {
    //                 nextEl: ".vuisongvungvang .swiper-button-next",
    //                 prevEl: ".vuisongvungvang .swiper-button-prev",
    //             }
    //         });
    //     });
    // }
}

if ($(".start_swiper").length) {
	var swiper = new Swiper(".start_swiper", {
	  slidesPerView: 1,
	  spaceBetween: 100,
	  loop: true,
	  autoplay: {
		  delay: 5000,
		  disableOnInteraction: false,
	  },
	  navigation: {
		  nextEl: ".swiper-button-next",
		  prevEl: ".swiper-button-prev",
	  },
	  breakpoints: {
		  768: {
			slidesPerView: 2,
			spaceBetween: 20,
		  },
		  900: {
			  slidesPerView: 3,
			  spaceBetween: 20,
		  },
		  1100: {
			slidesPerView: 4,
			spaceBetween: 40,
		  },
		  1600: {
			slidesPerView: 5,
			spaceBetween: 50,
		  },
		},
	  // pagination: {
		// el: ".swiper-pagination",
		// clickable: true,
	  // },
	});
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

  // Alle Tooltips auf der Seite aktivieren
  document.addEventListener('DOMContentLoaded', function () {
	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
	tooltipTriggerList.forEach(function (tooltipTriggerEl) {
	  new bootstrap.Tooltip(tooltipTriggerEl);
	});
  });
  
  
$(function () {
    $('.filter_attribute').click(function(){
        var listAttribute = '';

        $('.filter_attribute').each(function(){
            if($(this).is(':checked') && !$(this).hasClass('filter_manufactor'))
            listAttribute = listAttribute + $(this).attr('data') + ',';
        })

        var listManufactor = '';

        $('.filter_manufactor').each(function(){
            if($(this).is(':checked'))
            listManufactor = listManufactor + $(this).attr('data') + ',';
        })

        var currentURL = window.location.href;
        //alert(currentURL);

        $('.product .row_product').hide();
        $('.loading').show();

        $.ajax({
            url: (!currentURL.includes('order') && !currentURL.includes('atb') && !currentURL.includes('manufactor')) ? currentURL + '?filter_atb=true' + '&atb_id=' +listAttribute + '&manufactor_id=' +listManufactor : currentURL + '&filter_atb=true' + '&atb_id=' +listAttribute + '&manufactor_id=' +listManufactor,  // URL to fetch data from
            method: 'GET', // HTTP method
            success: function(data) {
               $('.product .row_product').html(data);
               $('.product .row_product').show();
               $('.loading').hide();

               if($('#list-view').hasClass('active'))
                 $('#list-view').click();

               if($('#grid-view').hasClass('active'))
                 $('#grid-view').click();
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

	// ScrollSpy nur ausführen, wenn #page existiert
	var pageElement = document.getElementById("page");
	if (pageElement) {
		const scrollSpy = new bootstrap.ScrollSpy(pageElement, {
			target: '.nav3 .nav',
		});
	}
});