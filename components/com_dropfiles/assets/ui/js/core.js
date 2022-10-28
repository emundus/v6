(function ($) {
  $(document).ready(function ($) {
    var dropfiles_core = {
      scrolls: {},
      scrollBarSettings: { // document: http://manos.malihu.gr/jquery-custom-content-scroller/
        axis: 'y',
        theme: 'dark',
        scrollInertia: 100,
        live: true,
        autoHideScrollbar: true,
        contentTouchScroll: false,
        autoExpandScrollbar: true,
      },
      init: function () {
        if (typeof(dropfiles) === 'undefined') {
          dropfiles = {};
        }
        if (typeof dropfiles !== 'object') {
          console.error('Fail on load dropfiles object!');
          return false;
        }

        // Add border-radius to visible tb in table
        $(document).on('load mouseover mouseout', '#mybootstrap tr.file td', this.addLastBorder);
        $(document).on('change', '#mybootstrap #rightcol .switch input[type="checkbox"]', this.switch);
        $(document).on('click', '#dropfiles-hamburger', function() {
          var leftCol = $('#mycategories');
          if (leftCol.css('left') == '-250px') {
            leftCol.css('left', '0');
          } else {
            leftCol.css('left', '-250px');
          }

        });

        // Events trigger
        $(document).on('dropfiles_category_click', '#categorieslist .dd-content', this.dropfilesCategoryClick);
        $(document).on('dropfiles_preview_updated', '#mybootstrap #preview', this.dropfilesPreviewUpdated);
        $(document).on('dropfiles_category_param_loaded', this.initCategoryFieldset);
        $(document).on('dropfiles_field_settings_status', this.applyFieldSettings);
        $(document).on('dropfiles_item_settings_status', this.applyItemSettings);
        $(document).on('dropfiles_file_hide_column_status', this.initFileColumnset);

        // Hide right column on 1366 of width
        $(window).on('resize', this.dropfilesshowHideRightCol);
        this.dropfilesshowHideRightCol();
        if($('body').hasClass('com_dropfiles')) {
          this.loadPreviousCategory();
        }
        this.dropfilesAddMainClass();
        this.restoreCategoriesState();
        // Init categories state handler
        $('#categorieslist').on('click', 'button', function(e) {
          setTimeout(function() {dropfiles_core.saveCategoriesState();}, 100);
        });
        if($('#toolbar').length) {
          $('#toolbar').parents('.container-fluid').addClass('fluid-toolbar');
        }
        if($('.dropfiles-sync-buttons').length) {
          $('.dropfiles-sync-buttons + .nested').addClass('hasCloud');
          $('#newcategory').addClass('withSyncCloud');
        }
      },
      addLastBorder: function (e) {
        var $this = $(this);
        $this.parent().find('td').removeClass('bfirst blast');
        $this.parent().find('td:visible:first').addClass('bfirst');
        $this.parent().find('td:visible:last').addClass('blast');
      },
      switch: function (e) {
        var $this = $(this);
        var ref = $this.attr('name').replace('jform_params_', '');
        if($this.attr('name') == 'jform_state') {
          $('input[name="jform[state]"]').val($this.prop('checked') ? 1 : 0);
        }
        $('select[name="jform[params][' + ref + ']"]').val($this.prop('checked') ? 1 : 0);
      },
      loadPreviousCategory: function () {
        var catId = localStorage.getItem('dropfilesSelectedCatId');
        if (catId) {
          var previousCat = $('[data-id-category="' + catId + '"]:not(.active) .dd-content').first();
          previousCat.click();
        }
      },
      applyFieldSettings: function (e) {
        if($('#rightcol .ju-switch-button select')) {
          $('#rightcol .ju-switch-button select').each(function () {
            if($(this).val() == 1) {
              $(this).parents('.ju-switch-button').find('input[name="' + $(this).attr('id') + '"]').prop('checked',true);
            }
          });
        }
      },
      applyItemSettings: function (e) {
        if($('#rightcol .ju-switch-button input[name="jform[state]"][id="jform_state0"]')) {
          $('#rightcol .ju-switch-button input[name="jform[state]"][id="jform_state0"]').each(function () {
            if($(this).attr('checked') == 'checked') {
              $(this).parents('.ju-switch-button').find('input[name="jform_state"]').prop('checked',true);
            }
          });
        }
      },
      dropfilesCategoryClick: function (e) {
        // Save category
        localStorage.setItem('dropfilesSelectedCatId', $(e.target).parent().data('id-category'));
      },
      dropfilesPreviewUpdated: function (e) {
        // Move toolbar to position
        if($('#preview .file').length === 0) {
          $('#preview .restableMenu').remove();
        }
        // todo: checkbox not recheck when load other category

        // Init Drop block for overlay
        // Remove old overlay
        if ($('#dropfiles-overlay').length) {
          $('#dropfiles-overlay').remove();
        }
        var dropOverlay = $('<div id="dropfiles-overlay" class="dropfiles-overlay hide"><div class="dropfiles-overlay-inner">' + Joomla.JText._('COM_DROPFILES_FILE_TO_UPLOAD', 'Drop file here to upload') + '</div></div>');
        $('#mybootstrap').append(dropOverlay);
        Dropfiles.uploader.assignDrop($('#dropfiles-overlay'));
        $('#dropfiles-overlay').on('drop', function () {
          $(this).addClass('hide');
        });
        // Show overlay on drag to #preview
        $('#preview').on("dragenter", function (e) {
          if (e.target === this) {
            return;
          }

          $('#dropfiles-overlay').removeClass('hide');
        });
        $(document).on("dragleave", function (e) {
          // Detect is real dragleave
          if (e.originalEvent.pageX !== 0 || e.originalEvent.pageY !== 0) {
            return false;
          }

          $('#dropfiles-overlay').addClass('hide');
        });

        // Check correct state for flip icon
        var rightCol = $('#rightcol');
        var flipButton = $('.dropfiles-flip');
        if (!rightCol.is(':visible')) {
          flipButton.css('transform', 'scale(-1)');
        } else {
          flipButton.css('transform', 'scale(1)');
        }
      },
      initCategoryFieldset: function () {
        dropfiles_core.initCategoryFieldsetState();
        $('.categoryblock legend').unbind('click').on('click', function (e) {
          var $this = $(this);
          if ($this.hasClass('collapsed')) {
            $this.removeClass('collapsed');
          } else {
            $this.addClass('collapsed');
          }
          if($this.hasClass('main-legend') && $this.hasClass('collapsed')) {
            $('.categoryblock .category-visibility-ordering-section').hide();
            if($('.categoryblock .theme-section').hasClass('hide-border')) {
              $('.categoryblock .theme-section').removeClass('hide-border');
            }
          } else if($this.hasClass('main-legend') && $this.hasClass('collapsed') === false) {
            $('.categoryblock .category-visibility-ordering-section').show();
            $('.categoryblock .theme-section').addClass('hide-border');
          }
          $this.parent().find('div.control-group:not(".hidden")').slideToggle(150, 'swing', function () {
            dropfiles_core.saveCategoryFieldsetState();
          });

        });
      },
      initCategoryFieldsetState: function () {
        var dropfilesFieldsetState = localStorage.getItem('dropfilesFieldsetState');

        if (dropfilesFieldsetState) {
          dropfilesFieldsetState = JSON.parse(dropfilesFieldsetState);
          if (dropfilesFieldsetState.length) {
            $.each(dropfilesFieldsetState, function (index, fieldset) {
              if (parseInt(fieldset.state) === 0) {
                $('#' + fieldset.id).find('div.control-group:not(".hidden")').hide();
                $('#' + fieldset.id + ' legend').addClass('collapsed');
                if($('#' + fieldset.id + ' legend').hasClass('main-legend')
                    && $('#' + fieldset.id + ' legend').hasClass('collapsed')) {
                  $('.categoryblock .category-visibility-ordering-section').hide();
                }
              }
            });
          }
        }
      },
      saveCategoryFieldsetState: function () {
        var fieldsets = $('.categoryblock .category-section');
        if (fieldsets.length) {
          var dropfilesFieldsetState = [];
          $.each(fieldsets, function (index, fieldset) {
            var item = {id: $(fieldset).prop('id'), state: 1};
            if ($(fieldset).find('legend').length && $(fieldset).find('legend').hasClass('collapsed')) {
              item.state = 0;
            }
            dropfilesFieldsetState.push(item);
          });
          localStorage.setItem('dropfilesFieldsetState', JSON.stringify(dropfilesFieldsetState));
        }
      },
      initFileColumnset: function (e) {
        dropfiles_core.initFileColumnState();
        $('#preview input[name="restable-toggle-cols"]').unbind('click').on('click', function (e) {
          dropfiles_core.saveFileColumnState();
        });
      },
      initFileColumnState: function () {
        var dropfilesFieldColumnState = localStorage.getItem('dropfilesFileColumnState');

        if (dropfilesFieldColumnState) {
          dropfilesFieldColumnState = JSON.parse(dropfilesFieldColumnState);
          if (dropfilesFieldColumnState.length) {
            $.each(dropfilesFieldColumnState, function (index, fieldset) {
              if (parseInt(fieldset.state) == 0) {
                $('#' + fieldset.id).prop('checked', false);
              }
            });
          }
        }
      },
      saveFileColumnState: function () {
        var fileColumnsets = $('#preview input[name="restable-toggle-cols"]');
        if (fileColumnsets.length) {
          var dropfilesFileColumnState = [];
          $.each(fileColumnsets, function (index, fieldset) {
            var item = {id: $(fieldset).prop('id'), state: 1};
            if(!$(fieldset).is(':checked')) {
              item.state = 0;
            }
            dropfilesFileColumnState.push(item);
          });
          localStorage.setItem('dropfilesFileColumnState', JSON.stringify(dropfilesFileColumnState));
        }
      },
      dropfilesshowHideRightCol: function () {
        // Do not run this on an iframe
        if ($('#insertcategory').length === 0 && $('#insertfile').length === 0) {
          if (1366 >= window.innerWidth) {
            dropfiles_core.hideRightCol();
          } else {
            dropfiles_core.showRightCol();
          }
        }
      },
      hideRightCol: function () {
        var rightCol = $('#rightcol');
        var flipButton = $('.dropfiles-flip');
        if (rightCol.is(':visible')) {
          rightCol.addClass('hide').removeClass('show');
          flipButton.css('transform', 'scale(-1)');
        }
      },
      showRightCol: function () {
        var rightCol = $('#rightcol');
        var flipButton = $('.dropfiles-flip');
        if (!rightCol.is(':visible')) {
          rightCol.addClass('show').removeClass('hide');
          flipButton.css('transform', 'scale(1)');
        }
      },
      dropfilesAddMainClass: function () {
        if($('.managebootstrap').length > 0) {
          $('.managebootstrap').parents('body').addClass('dropfilesfilemanagebody');
        }
        if($('#insertcategory').length > 0) {
          $('#insertcategory').parents('body').addClass('dropfileseditpopupbody');
        }
      },
      getCategoriesState : function() {
        var categoriesState = localStorage.getItem('dropfilesCategoriesState');
        if (categoriesState) {
          return JSON.parse(categoriesState);

        } else {
          // Get current state then save
          var openCategories = $('li.dd-collapsed');
          var currentState = [];
          $.each(openCategories, function (index, li) {
            currentState.push($(li).attr('data-id-category'));
          });
          localStorage.setItem('dropfilesCategoriesState', JSON.stringify(currentState));
          return currentState;
        }
      },
      saveCategoriesState : function() {
      var openCategories = $('li.dd-collapsed');
      var currentState = [];
      $.each(openCategories, function (index, li) {
        currentState.push($(li).attr('data-id-category'));
      });
      localStorage.setItem('dropfilesCategoriesState', JSON.stringify(currentState));
    },
      restoreCategoriesState : function() {
      var openCategoriesId = dropfiles_core.getCategoriesState();
      if (openCategoriesId.length) {
        $.each(openCategoriesId, function (index, catId) {
          $('#categorieslist li[data-id-category="'+catId+'"]').addClass('dd-collapsed');
        });
      }
    },
    };

    dropfiles_core.init();

  });
})(jQuery);
