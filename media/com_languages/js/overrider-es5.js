(function () {
  'use strict';

  /**
   * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */
  (function (Joomla, document) {
    var Overrider = /*#__PURE__*/function () {
      function Overrider() {
        this.states = {
          refreshing: false,
          refreshed: false,
          counter: 0,
          searchString: '',
          searchType: 'value'
        };
        this.spinner = document.getElementById('overrider-spinner');
        this.spinnerBtn = document.getElementById('overrider-spinner-btn');
        this.moreResults = document.getElementById('more-results');
        this.moreResultsButton = document.getElementById('more-results-button');
        this.resultsContainer = document.getElementById('results-container');
        this.refreshStatus = document.getElementById('refresh-status');
      }
      /**
       * Method for refreshing the database cache of known language strings via Ajax
       *
       * @return  void
       *
       * @since   2.5
       */


      var _proto = Overrider.prototype;

      _proto.refreshCache = function refreshCache() {
        var _this = this;

        this.states.refreshing = true;
        this.refreshStatus.classList.add('show');
        Joomla.request({
          url: 'index.php?option=com_languages&task=strings.refresh&format=json',
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          onSuccess: function onSuccess(response) {
            if (response.error && response.message) {
              alert(response.message);
            }

            if (response.messages) {
              Joomla.renderMessages(response.messages);
            }

            _this.refreshStatus.classList.remove('show');

            _this.states.refreshing = false;
          },
          onError: function onError() {
            alert(Joomla.Text._('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR'));

            _this.refreshStatus.classList.remove('show');
          }
        });
      }
      /**
       * Method for searching known language strings via Ajax
       *
       * @param   more  Determines the limit start of the results
       *
       * @return  void
       *
       * @since   2.5
       */
      ;

      _proto.searchStrings = function searchStrings(more) {
        var _this2 = this;

        // Prevent searching if the cache is refreshed at the moment
        if (this.states.refreshing) {
          return;
        }

        var formSearchString = document.getElementById('jform_searchstring');
        var formSearchType = document.getElementById('jform_searchtype'); // Only update the used searchstring and searchtype if the search button
        // was used to start the search (that will be the case if 'more' is null)

        if (!more) {
          this.states.searchString = formSearchString.value;
          this.states.searchType = formSearchType.value || 'value'; // Remove the old results

          var oldResults = [].slice.call(document.querySelectorAll('.language-results'));
          oldResults.forEach(function (result) {
            result.parentNode.removeChild(result);
          });
        }

        if (!this.states.searchString) {
          formSearchString.classList.add('invalid');
          return;
        }

        if (more) {
          // If 'more' is greater than 0 we have already displayed some results for
          // the current searchstring, so display the spinner at the more link
          this.spinnerBtn.classList.add('show');
        } else {
          // Otherwise it is a new searchstring and we have to remove all previous results first
          this.moreResults.classList.remove('show');
          var childs = [].slice.call(document.querySelectorAll('#results-container div.language-results'));
          childs.forEach(function (child) {
            child.parentNode.removeChild(child);
          });
          this.resultsContainer.classList.add('show');
          this.spinner.classList.add('show');
        }

        Joomla.request({
          url: "index.php?option=com_languages&task=strings.search&format=json&searchstring=" + this.states.searchString + "&searchtype=" + this.states.searchType + "&more=" + more,
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          onSuccess: function onSuccess(resp) {
            var response = JSON.parse(resp);

            if (response.error && response.message) {
              alert(response.message);
            }

            if (response.messages) {
              Joomla.renderMessages(response.messages);
            }

            if (response.data) {
              if (response.data.results) {
                Joomla.overrider.insertResults(response.data.results);
              }

              if (response.data.more) {
                // If there are more results than the sent ones
                // display the more link
                _this2.states.more = response.data.more;
                _this2.moreResultsButton.disabled = false;

                _this2.moreResults.classList.add('show');
              } else {
                _this2.moreResultsButton.disabled = true;

                _this2.moreResults.classList.remove('show');
              }
            }

            _this2.spinnerBtn.classList.remove('show');

            _this2.spinner.classList.remove('show');
          },
          onError: function onError() {
            alert(Joomla.Text._('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR'));
            _this2.moreResultsButton.disabled = true;

            _this2.moreResults.classList.remove('show');

            _this2.resultsContainer.classList.remove('show');
          }
        });
      }
      /**
       * Method inserting the received results into the results container
       *
       * @param   results  An array of search result objects
       *
       * @return  void
       *
       * @since   2.5
       */
      ;

      _proto.insertResults = function insertResults(results) {
        var _this3 = this;

        // For creating an individual ID for each result we use a counter
        this.states.counter += 1; // Create a container into which all the results will be inserted

        var resultsDiv = document.createElement('div');
        resultsDiv.setAttribute('id', "language-results" + this.states.counter);
        resultsDiv.classList.add('language-results');
        resultsDiv.classList.add('list-group');
        resultsDiv.classList.add('mb-2');
        resultsDiv.classList.add('show'); // Create some elements for each result and insert it into the container

        results.forEach(function (item, index) {
          var a = document.createElement('a');
          a.setAttribute('onclick', "Joomla.overrider.selectString(" + _this3.states.counter + index + ");");
          a.setAttribute('href', '#');
          a.classList.add('list-group-item');
          a.classList.add('list-group-item-action');
          a.classList.add('flex-column');
          a.classList.add('align-items-start');
          var key = document.createElement('div');
          key.setAttribute('id', "override_key" + _this3.states.counter + index);
          key.setAttribute('title', item.file);
          key.classList.add('result-key');
          key.innerHTML = Joomla.sanitizeHtml(item.constant);
          var string = document.createElement('div');
          string.setAttribute('id', "override_string" + _this3.states.counter + index);
          string.classList.add('result-string');
          string.innerHTML = Joomla.sanitizeHtml(item.string);
          a.appendChild(key);
          a.appendChild(string);
          resultsDiv.appendChild(a);
        }); // If there aren't any results display an appropriate message

        if (!results.length) {
          var noresult = document.createElement('div');
          noresult.innerText = Joomla.Text._('COM_LANGUAGES_VIEW_OVERRIDE_NO_RESULTS');
          resultsDiv.appendChild(noresult);
        }

        if (this.moreResults) {
          this.moreResults.parentNode.insertBefore(resultsDiv, this.moreResults);
        }
      }
      /**
       * Inserts a specific constant/value pair into the form and scrolls the page back to the top
       *
       * @param   id  The ID of the element which was selected for insertion
       *
       * @return  void
       *
       * @since   2.5
       */
      // eslint-disable-next-line class-methods-use-this
      ;

      _proto.selectString = function selectString(id) {
        document.getElementById('jform_key').value = document.getElementById("override_key" + id).innerHTML;
        document.getElementById('jform_override').value = document.getElementById("override_string" + id).innerHTML;
      };

      return Overrider;
    }();

    document.addEventListener('DOMContentLoaded', function () {
      Joomla.overrider = new Overrider();
    });
  })(Joomla, document);

})();
