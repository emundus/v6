(function () {
  'use strict';

  /**
   * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */
  (function (Joomla) {
    Joomla.typeHasChanged = function (element) {
      // Display the loading indication
      document.body.appendChild(document.createElement('joomla-core-loader'));
      document.querySelector('input[name=task]').value = 'field.reload';
      element.form.submit();
    };
  })(Joomla);

})();
