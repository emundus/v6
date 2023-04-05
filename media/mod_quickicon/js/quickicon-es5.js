(function () {
  'use strict';

  /**
   * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */

  /**
   * Every quickicon with an ajax request url loads data and set them into the counter element
   * Also the data name is set as singular or plural.
   * A SR-only text is added
   * The class pulse gets 'warning', 'success' or 'error', depending on the retrieved data.
   */
  if (!Joomla) {
    throw new Error('Joomla API was not properly initialized');
  }

  Array.from(document.querySelectorAll('.quickicon')).forEach(function (quickicon) {
    var counter = quickicon.querySelector('.quickicon-amount');

    if (!counter) {
      return;
    }

    if (counter.dataset.url) {
      Joomla.request({
        url: counter.dataset.url,
        method: 'GET',
        onSuccess: function onSuccess(resp) {
          var response;

          try {
            response = JSON.parse(resp);
          } catch (error) {
            quickicon.classList.add('error');
          }

          if (Object.prototype.hasOwnProperty.call(response, 'data')) {
            var name = quickicon.querySelector('.quickicon-name');
            var nameSpan = document.createElement('span');
            quickicon.classList.add(response.data > 0 ? 'warning' : 'success'); // Set name in singular or plural

            if (response.data.name && name) {
              nameSpan.textContent = response.data.name;
              name.replaceChild(nameSpan, name.firstChild);
            } // Set amount of number into counter span


            counter.textContent = "\u200E" + response.data.amount; // Insert screenreader text

            var sronly = quickicon.querySelector('.quickicon-sr-desc');

            if (response.data.sronly && sronly) {
              sronly.textContent = response.data.sronly;
            }
          } else {
            quickicon.classList.add('error');
          }
        },
        onError: function onError() {
          quickicon.classList.add('error');
        }
      });
    }
  });

})();
