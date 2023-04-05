(function () {
  'use strict';

  /**
   * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */
  document.addEventListener('DOMContentLoaded', function () {
    var title = document.getElementById('jform_title');
    title.dpOldValue = title.value;
    title.addEventListener('change', function (_ref) {
      var currentTarget = _ref.currentTarget;
      var label = document.getElementById('jform_label');
      var changedTitle = currentTarget;

      if (changedTitle.dpOldValue === label.value) {
        label.value = changedTitle.value;
      }

      changedTitle.dpOldValue = changedTitle.value;
    });
  });

})();
