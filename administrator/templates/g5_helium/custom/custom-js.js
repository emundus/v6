jQuery(function ($) {
   //SCROLL SIDEBAR
   jQuery(document).ready(function () {

      /* if ($(window).width() > 991) {

          $(window).scroll(function () {
             var fenetre = $(window);
             var fenetreHeight = $(window).height();
             var scrollTop = fenetre.scrollTop();
             var topHeight = $('#g-navigation').height() + $('#g-feature').height() + $('#drawer').height();
             var sidebar = $('#g-sidebar');
             var sidebarHeight = sidebar.height() + 40;
             var footerHeight = $('#g-footer').height() + 40;
             var fullHeight = sidebarHeight + footerHeight;
             var sticky = $('.custom-sticky');
             var sidebarWidth = $('.size-25').width();
             var specHeight = sidebarHeight - footerHeight;
             var specHeight2 = footerHeight - sidebarHeight;
             var content = $('#g-main-mainbody');
             var contentHeight = content.height();

             $('#g-sidebar').css('width', sidebarWidth);
             $('.custom-sticky').css('width', sidebarWidth);

             if (fenetreHeight > fullHeight) {

                if (scrollTop > topHeight) {
                   sidebar.addClass('custom-sticky');
                } else if (scrollTop < topHeight) {
                   sidebar.removeClass('custom-sticky');
                }
             } else if (fenetreHeight <= fullHeight) {
                if (sidebarHeight >= footerHeight) {
                   if (scrollTop > (topHeight + (sidebarHeight - footerHeight)) - specHeight) {
                      sidebar.addClass('custom-sticky');
                      sticky = $('.custom-sticky');
                      sticky.css('top', 'unset');
                      sticky.css('bottom', footerHeight);
                   } else if (scrollTop < (topHeight + (sidebarHeight - footerHeight)) - specHeight) {
                      sidebar.removeClass('custom-sticky');
                   }
                }

                if (sidebarHeight < footerHeight) {
                   if (scrollTop > (topHeight + (footerHeight - sidebarHeight)) - specHeight2) {
                      sidebar.addClass('custom-sticky');
                      sticky = $('.custom-sticky');
                      sticky.css('bottom', footerHeight);
                   } else if (scrollTop < (topHeight + (footerHeight - sidebarHeight)) - specHeight2) {
                      sidebar.removeClass('custom-sticky');
                   } else if (scrollTop < contentHeight) {
                      sidebar.removeClass('custom-sticky');
                   }
                }
             }
          });
          $(window).scroll();
       }

      */

      /* SMOOTH-SCROLL DES ANCRES */
      $("#g-sidebar .em_module>.em_form>a[href*='#']:not([href='#'])").click(function () {
         if (
             location.hostname == this.hostname &&
             this.pathname.replace(/^\//, "") == location.pathname.replace(/^\//, "")
         ) {
            var anchor = $(this.hash);
            anchor = anchor.length ? anchor : $("[name=" + this.hash.slice(1) + "]");
            if (anchor.length) {
               $("html, body").animate({
                  scrollTop: anchor.offset().top
               }, 1500);
            }
         }
      });

      jQuery('.applicant-form').ready(function () {
         /*
           var labels = document.querySelectorAll('.applicant-form label[opts*="Validation"]');

           labels.forEach(function(label) {
              label.innerHTML += " <span style='color:red;'> *<span>";
            });
            */

         var selects = document.querySelectorAll('.applicant-form select');
         selects.forEach(function (select) {
            if (select.value === '') {
               select.style.color = 'red';
            }
            for (var i = 0; i < select.length; i++) {
               if (select[i].value === '') {
                  select[i].style.color = 'red'
               } else {
                  select[i].style.color = 'black';
               }
            }
         });

      });

      jQuery('.applicant-form select').on('change', function () {
         if (this.value != '') {
            this.style.color = 'black';
         } else {
            this.style.color = 'red';
         }
         for (var i = 0; i < this.length; i++) {
            if (this[i].value === '') {
               this[i].style.color = 'red'
            } else {
               this[i].style.color = 'black';
            }
         }
      });

      //burger menu
      $('.g-offcanvas-toggle').html('');
      $('.g-offcanvas-toggle').html('<a class="burger"><span></span></a>');

      $('.g-offcanvas-toggle').click(function () {
         $('.burger').toggleClass('active');
      });
      $('body').click(function (e) {
         if (!$(e.target).is('.burger,.burger>span')) {
            $('.burger').removeClass('active');
         }
      });

      /* REMOVE br FABRIK */
      $(".fabrikElement br").remove();

      /* Remplacer le titre de la pagre contact par un H1   */
      $(".view-contact .contact-name").unwrap().wrap("<h1 class='contact-name'></h1>");

      var form = $('#form_307');
      var parentForm = form.parent();
      parentForm.addClass('em-formRegistrationCenter');

      var widthInputForm = $('#form_307 fieldset .row-fluid .control-group .controls input#jos_emundus_users___lastname').width();
      var widthInputFormValidate = $('.login .form-validate .controls').width();
      var heightInputForm = $('#form_307 .row-fluid .control-group .controls input#jos_emundus_users___lastname').height();
      var heightInputFormValidate = $('.login .form-validate .controls').height();

      $('#form_307 .span4').css({
         'width': widthInputForm,
         'height': heightInputForm
      });
      $('#form_307 .span4 .btn-group button').css({
         'width': widthInputForm,
         'height': heightInputForm
      });
      /*Largeur égale entre le bouton retour et sauvegarder*/
      var width = $('.span4:not(offset) .btn-group').width();
      $('.goback-btn').css('width', width);

      $('.com_users .nav-tabs li:nth-of-type(2)').remove();

   });
});
