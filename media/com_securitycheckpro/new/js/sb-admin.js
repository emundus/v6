jQuery(document).ready(function() {
	(function($) {
	  "use strict"; // Start of use strict
	  // Configure tooltips for collapsed side navigation
	  $('.navbar2-sidenav [data-toggle="tooltip"]').tooltip({
		template: '<div class="tooltip navbar2-sidenav2-tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
	  })
	  // Toggle the side navigation
	  $("#sidenavToggler").click(function(e) {
		e.preventDefault();
		$("body").toggleClass("sidenav2-toggled");
		$(".navbar2-sidenav .nav2-link-collapse2").addClass("collapsed");
		$(".navbar2-sidenav .sidenav2-second-level, .navbar2-sidenav .sidenav2-third-level").removeClass("show");
	  });
	  // Force the toggled class to be removed when a collapsible nav link is clicked
	  $(".navbar2-sidenav .nav2-link-collapse2").click(function(e) {
		e.preventDefault();
		$("body").removeClass("sidenav2-toggled");
	  });
	  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
	  $('body.fixed-nav .navbar2-sidenav, body.fixed-nav .sidenav2-toggler, body.fixed-nav .navbar2-collapse2').on('mousewheel DOMMouseScroll', function(e) {
		var e0 = e.originalEvent,
		  delta = e0.wheelDelta || -e0.detail;
		this.scrollTop += (delta < 0 ? 1 : -1) * 30;
		e.preventDefault();
	  });
	  // Scroll to top button appear
	  $(document).scroll(function() {
		var scrollDistance = $(this).scrollTop();
		if (scrollDistance > 100) {
		  $('.scroll-to-top').fadeIn();
		} else {
		  $('.scroll-to-top').fadeOut();
		}
	  });
	  // Configure tooltips globally
	  $('[data-toggle="tooltip"]').tooltip()
	  // Smooth scrolling using jQuery easing
	  $(document).on('click', 'a.scroll-to-top', function(event) {
		var $anchor = $(this);
		$('html, body').stop().animate({
		  scrollTop: (0)
		}, 1000, 'easeInOutExpo');
		event.preventDefault();
	  });
	})(jQuery); // End of use strict
});
