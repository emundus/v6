(function () {
  'use strict';

  /**
   * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
   * @license     GNU General Public License version 2 or later; see LICENSE.txt
   */

  /**
   * Provides the manual-run functionality for tasks over the com_scheduler administrator backend.
   *
   * @package  Joomla.Components
   * @subpackage Scheduler.Tasks
   *
   * @since    4.1.0
   */
  if (!window.Joomla) {
    throw new Error('Joomla API was not properly initialised');
  }

  var initRunner = function initRunner() {
    var paths = Joomla.getOptions('system.paths');
    var token = Joomla.getOptions('com_scheduler.test-task.token');
    var uri = (paths ? paths.base + "/index.php" : window.location.pathname) + "?option=com_ajax&format=json&plugin=RunSchedulerTest&group=system&id=%d" + (token ? "&" + token + "=1" : '');
    var modal = document.getElementById('scheduler-test-modal'); // Task output template

    var template = "\n    <h4 class=\"scheduler-headline\">" + Joomla.Text._('COM_SCHEDULER_TEST_RUN_TASK') + "</h4>\n    <div>" + Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_STARTED') + "</div>\n    <div class=\"mt-3 text-center\"><span class=\"fa fa-spinner fa-spin fa-lg\"></span></div>\n  ";

    var sanitiseTaskOutput = function sanitiseTaskOutput(text) {
      return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2');
    }; // Trigger the task through a GET request, populate the modal with output on completion.


    var triggerTaskAndShowOutput = function triggerTaskAndShowOutput(e) {
      var button = e.relatedTarget;
      var id = parseInt(button.dataset.id, 10);
      var title = button.dataset.title;
      modal.querySelector('.modal-title').innerHTML = Joomla.Text._('COM_SCHEDULER_TEST_RUN_TITLE').replace('%d', id.toString());
      modal.querySelector('.modal-body > div').innerHTML = template.replace('%s', title);
      Joomla.request({
        url: uri.replace('%d', id.toString()),
        onSuccess: function onSuccess(data, xhr) {
          [].slice.call(modal.querySelectorAll('.modal-body > div > div')).forEach(function (el) {
            el.parentNode.removeChild(el);
          });
          var output = JSON.parse(data);

          if (output && output.success && output.data) {
            modal.querySelector('.modal-body > div').innerHTML += "<div>" + Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_COMPLETED') + "</div>";

            if (output.data.duration > 0) {
              modal.querySelector('.modal-body > div').innerHTML += "<div>" + Joomla.Text._('COM_SCHEDULER_TEST_RUN_DURATION').replace('%s', output.data.duration.toFixed(2)) + "</div>";
            }

            if (output.data.output) {
              var result = Joomla.sanitizeHtml(output.data.output, null, sanitiseTaskOutput); // Can use an indication for non-0 exit codes

              modal.querySelector('.modal-body > div').innerHTML += "<div>" + Joomla.Text._('COM_SCHEDULER_TEST_RUN_OUTPUT').replace('%s', result) + "</div>";
            }
          } else {
            modal.querySelector('.modal-body > div').innerHTML += "<div>" + Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_TERMINATED') + "</div>";
            modal.querySelector('.modal-body > div').innerHTML += "<div>" + Joomla.Text._('COM_SCHEDULER_TEST_RUN_OUTPUT').replace('%s', Joomla.Text._('JLIB_JS_AJAX_ERROR_OTHER').replace('%s', xhr.status)) + "</div>";
          }
        },
        onError: function onError(xhr) {
          modal.querySelector('.modal-body > div').innerHTML += "<div>" + Joomla.Text._('COM_SCHEDULER_TEST_RUN_STATUS_TERMINATED') + "</div>";
          var msg = Joomla.ajaxErrorsMessages(xhr);
          modal.querySelector('.modal-body > div').innerHTML += "<div>" + Joomla.Text._('COM_SCHEDULER_TEST_RUN_OUTPUT').replace('%s', msg.error) + "</div>";
        }
      });
    };

    var reloadOnClose = function reloadOnClose() {
      window.location.href = (paths ? paths.base + "/index.php" : window.location.pathname) + "?option=com_scheduler&view=tasks";
    };

    if (modal) {
      modal.addEventListener('show.bs.modal', triggerTaskAndShowOutput);
      modal.addEventListener('hidden.bs.modal', reloadOnClose);
    }

    document.removeEventListener('DOMContentLoaded', initRunner);
  };

  document.addEventListener('DOMContentLoaded', initRunner);

})();
