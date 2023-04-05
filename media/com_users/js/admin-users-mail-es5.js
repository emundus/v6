(function () {
  'use strict';

  /**
   * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
   * @license     GNU General Public License version 2 or later; see LICENSE.txt
   */
  (function () {
    document.addEventListener('DOMContentLoaded', function () {
      Joomla.submitbutton = function (pressbutton) {
        var form = document.adminForm;
        var html = document.createElement('joomla-alert');

        if (pressbutton === 'mail.cancel') {
          Joomla.submitform(pressbutton);
          return;
        } // do field validation


        if (form.jform_subject.value === '') {
          html.innerText = Joomla.Text._('COM_USERS_MAIL_PLEASE_FILL_IN_THE_SUBJECT');
          form.insertAdjacentElement('afterbegin', html);
        } else if (form.jform_group.value < 0) {
          html.innerText = Joomla.Text._('COM_USERS_MAIL_PLEASE_SELECT_A_GROUP');
          form.insertAdjacentElement('afterbegin', html);
        } else if (form.jform_message.value === '') {
          html.innerText = Joomla.Text._('COM_USERS_MAIL_PLEASE_FILL_IN_THE_MESSAGE');
          form.insertAdjacentElement('afterbegin', html);
        } else {
          Joomla.submitform(pressbutton);
        }
      };
    });
  })();

})();
