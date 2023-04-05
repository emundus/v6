(function () {
  'use strict';

  /**
   * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */

  /**
   * Toggles the display of inline help DIVs
   *
   * @param {String} toggleClass The class name of the DIVs to toggle display for
   */
  Joomla.toggleInlineHelp = function (toggleClass) {
    [].slice.call(document.querySelectorAll("div." + toggleClass)).forEach(function (elDiv) {
      // Toggle the visibility of the node by toggling the 'd-none' Bootstrap class.
      elDiv.classList.toggle('d-none'); // The ID of the description whose visibility is toggled.

      var myId = elDiv.id; // The ID of the control described by this node (same ID, minus the '-desc' suffix).

      var controlId = myId ? myId.substr(0, myId.length - 5) : null; // Get the control described by this node.

      var elControl = controlId ? document.getElementById(controlId) : null; // Is this node hidden?

      var isHidden = elDiv.classList.contains('d-none'); // If we do not have a control we will exit early

      if (!controlId || !elControl) {
        return;
      } // Unset the aria-describedby attribute in the control when the description is hidden and vice–versa.


      if (isHidden && elControl.hasAttribute('aria-describedby')) {
        elControl.removeAttribute('aria-describedby');
      } else if (!isHidden) {
        elControl.setAttribute('aria-describedby', myId);
      }
    });
  }; // Initialisation. Clicking on anything with the button-inlinehelp class will toggle the inline help.


  [].slice.call(document.querySelectorAll('.button-inlinehelp')).forEach(function (elToggler) {
    var _elToggler$dataset$cl;

    // The class of the DIVs to toggle visibility on is defined by the data-class attribute of the click target.
    var toggleClass = (_elToggler$dataset$cl = elToggler.dataset.class) != null ? _elToggler$dataset$cl : 'hide-aware-inline-help';
    var collection = document.getElementsByClassName(toggleClass); // no description => hide inlinehelp button

    if (collection.length === 0) {
      elToggler.classList.add('d-none');
      return;
    } // Add the click handler.


    elToggler.addEventListener('click', function (event) {
      event.preventDefault();
      Joomla.toggleInlineHelp(toggleClass);
    });
  });

})();
