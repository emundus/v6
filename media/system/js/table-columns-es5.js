(function () {
  'use strict';

  /**
   * TableColumns class for toggle visibility of <table> columns.
   */
  var TableColumns = /*#__PURE__*/function () {
    function TableColumns($table, tableName) {
      var _this = this;

      this.$table = $table;
      this.tableName = tableName;
      this.storageKey = "joomla-tablecolumns-" + this.tableName;
      this.$headers = [].slice.call($table.querySelector('thead tr').children);
      this.$rows = [].slice.call($table.querySelectorAll('tbody tr'));
      this.listOfHidden = []; // Load previous state

      this.loadState(); // Find protected columns

      this.protectedCols = [0];

      if (this.$rows[0]) {
        [].slice.call(this.$rows[0].children).forEach(function ($el, index) {
          if ($el.nodeName === 'TH') {
            _this.protectedCols.push(index); // Make sure it's not in the list of hidden


            var ih = _this.listOfHidden.indexOf(index);

            if (ih !== -1) {
              _this.listOfHidden.splice(ih, 1);
            }
          }
        });
      } // Set up toggle menu


      this.createControls(); // Restore state

      this.listOfHidden.forEach(function (index) {
        _this.toggleColumn(index, true);
      });
    }
    /**
     * Create a controls to select visible columns
     */


    var _proto = TableColumns.prototype;

    _proto.createControls = function createControls() {
      var _this2 = this;

      var $divouter = document.createElement('div');
      $divouter.setAttribute('class', 'dropdown float-end pb-2');
      var $divinner = document.createElement('div');
      $divinner.setAttribute('class', 'dropdown-menu dropdown-menu-end');
      $divinner.setAttribute('data-bs-popper', 'static'); // Create a toggle button

      var $button = document.createElement('button');
      $button.type = 'button';
      $button.textContent = Joomla.Text._('JGLOBAL_COLUMNS');
      $button.classList.add('btn', 'btn-primary', 'btn-sm', 'dropdown-toggle');
      $button.setAttribute('data-bs-toggle', 'dropdown');
      $button.setAttribute('data-bs-auto-close', 'false');
      $button.setAttribute('aria-haspopup', 'true');
      $button.setAttribute('aria-expanded', 'false');
      var $ul = document.createElement('ul');
      $ul.setAttribute('class', 'list-unstyled p-2 text-nowrap mb-0');
      $ul.setAttribute('id', 'columnList'); // Collect a list of headers for dropdown

      this.$headers.forEach(function ($el, index) {
        // Skip the first column, unless it's a th, as we don't want to display the checkboxes
        if (index === 0 && $el.nodeName !== 'TH') return;
        var $li = document.createElement('li');
        var $label = document.createElement('label');
        var $input = document.createElement('input');
        $input.classList.add('form-check-input', 'me-1');
        $input.type = 'checkbox';
        $input.name = 'table[column][]';
        $input.checked = _this2.listOfHidden.indexOf(index) === -1;
        $input.disabled = _this2.protectedCols.indexOf(index) !== -1;
        $input.value = index; // Find the header name

        var $titleEl = $el.querySelector('span');
        var title = $titleEl ? $titleEl.textContent.trim() : '';

        if (!title) {
          $titleEl = $el.querySelector('span.visually-hidden') || $el;
          title = $titleEl.textContent.trim();
        }

        if (title.includes(':')) {
          title = title.split(':', 2)[1].trim();
        }

        $label.textContent = title;
        $label.insertAdjacentElement('afterbegin', $input);
        $li.appendChild($label);
        $ul.appendChild($li);
      });
      this.$table.insertAdjacentElement('beforebegin', $divouter);
      $divouter.appendChild($button);
      $divouter.appendChild($divinner);
      $divinner.appendChild($ul); // Listen to checkboxes change

      $ul.addEventListener('change', function (event) {
        _this2.toggleColumn(parseInt(event.target.value, 10));

        _this2.saveState();
      }); // Remove "media query" classes, which may prevent toggling from working.

      this.$headers.forEach(function ($el) {
        $el.classList.remove('d-none', 'd-xs-table-cell', 'd-sm-table-cell', 'd-md-table-cell', 'd-lg-table-cell', 'd-xl-table-cell', 'd-xxl-table-cell');
      });
      this.$rows.forEach(function ($row) {
        [].slice.call($row.children).forEach(function ($el) {
          $el.classList.remove('d-none', 'd-xs-table-cell', 'd-sm-table-cell', 'd-md-table-cell', 'd-lg-table-cell', 'd-xl-table-cell', 'd-xxl-table-cell');
        });
      });
      this.$button = $button;
      this.$menu = $ul;
      this.updateCounter();
    }
    /**
     * Update button text
     */
    ;

    _proto.updateCounter = function updateCounter() {
      // Don't count the checkboxes column in the total
      var total = this.$headers.length - 1;
      var visible = total - this.listOfHidden.length;
      this.$button.textContent = visible + "/" + total + " " + Joomla.Text._('JGLOBAL_COLUMNS');
    }
    /**
     * Toggle column visibility
     *
     * @param {Number} index  The column index
     * @param {Boolean} force To force hide
     */
    ;

    _proto.toggleColumn = function toggleColumn(index, force) {
      // Skip incorrect index
      if (!this.$headers[index]) return; // Skip the protected columns

      if (this.protectedCols.indexOf(index) !== -1) return;
      var i = this.listOfHidden.indexOf(index);

      if (i === -1) {
        this.listOfHidden.push(index);
      } else if (force !== true) {
        this.listOfHidden.splice(i, 1);
      }

      this.$headers[index].classList.toggle('d-none', force);
      this.$rows.forEach(function ($col) {
        $col.children[index].classList.toggle('d-none', force);
      });
      this.updateCounter();
    }
    /**
     * Save state, list of hidden columns
     */
    ;

    _proto.saveState = function saveState() {
      window.localStorage.setItem(this.storageKey, this.listOfHidden.join(','));
    }
    /**
     * Load state, list of hidden columns
     */
    ;

    _proto.loadState = function loadState() {
      var stored = window.localStorage.getItem(this.storageKey);

      if (stored) {
        this.listOfHidden = stored.split(',').map(function (val) {
          return parseInt(val, 10);
        });
      }
    };

    return TableColumns;
  }();

  if (window.innerWidth > 992) {
    // Look for dataset name else page-title
    [].concat(document.querySelectorAll('table')).forEach(function ($table) {
      var tableName = $table.dataset.name ? $table.dataset.name : document.querySelector('.page-title').textContent.trim().replace(/[^a-z0-9]/gi, '-').toLowerCase(); // Skip unnamed table

      if (!tableName) {
        return;
      }
      /* eslint-disable-next-line no-new */


      new TableColumns($table, tableName);
    });
  }

})();
