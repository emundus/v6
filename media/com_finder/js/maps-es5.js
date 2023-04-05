(function () {
  'use strict';

  /**
   * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
   * @license     GNU General Public License version 2 or later; see LICENSE.txt
   */
  Joomla = window.Joomla || {};

  (function () {
    document.addEventListener('DOMContentLoaded', function () {
      Joomla.submitbutton = function (pressbutton) {
        // @todo replace with joomla-alert
        if (pressbutton === 'map.delete' && !window.confirm(Joomla.Text._('COM_FINDER_MAPS_CONFIRM_DELETE_PROMPT'))) {
          return false;
        }

        Joomla.submitform(pressbutton);
        return true;
      };
    });
  })();

})();
