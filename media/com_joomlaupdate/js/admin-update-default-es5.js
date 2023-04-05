(function () {
  'use strict';

  /**
   * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
   * @license     GNU General Public License version 2 or later; see LICENSE.txt
   */
  if (!Joomla) {
    throw new Error('Joomla API was not initialised properly');
  }

  Joomla.Update = window.Joomla.Update || {
    stat_total: 0,
    stat_files: 0,
    stat_inbytes: 0,
    stat_outbytes: 0,
    password: null,
    totalsize: 0,
    ajax_url: null,
    return_url: null,
    cached_instance: null,
    genericErrorMessage: function genericErrorMessage(message) {
      var headerDiv = document.getElementById('errorDialogLabel');
      var messageDiv = document.getElementById('errorDialogMessage');
      var progressDiv = document.getElementById('joomlaupdate-progress');
      var errorDiv = document.getElementById('joomlaupdate-error');
      headerDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_GENERIC');
      messageDiv.innerHTML = message;

      if (message.toLowerCase() === 'invalid login') {
        messageDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_BODY_INVALIDLOGIN');
      }

      progressDiv.classList.add('d-none');
      errorDiv.classList.remove('d-none');
    },
    handleErrorResponse: function handleErrorResponse(xhr) {
      var isForbidden = xhr.status === 403;
      var headerDiv = document.getElementById('errorDialogLabel');
      var messageDiv = document.getElementById('errorDialogMessage');
      var progressDiv = document.getElementById('joomlaupdate-progress');
      var errorDiv = document.getElementById('joomlaupdate-error');

      if (isForbidden) {
        headerDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_FORBIDDEN');
        messageDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_BODY_FORBIDDEN');
      } else {
        headerDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_HEAD_SERVERERROR');
        messageDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_ERRORMODAL_BODY_SERVERERROR');
      }

      progressDiv.classList.add('d-none');
      errorDiv.classList.remove('d-none');
    },
    startExtract: function startExtract() {
      // Reset variables
      Joomla.Update.stat_files = 0;
      Joomla.Update.stat_inbytes = 0;
      Joomla.Update.stat_outbytes = 0;
      Joomla.Update.cached_instance = null;
      document.getElementById('extbytesin').innerText = Joomla.Update.formatBytes(Joomla.Update.stat_inbytes);
      document.getElementById('extbytesout').innerText = Joomla.Update.formatBytes(Joomla.Update.stat_outbytes);
      document.getElementById('extfiles').innerText = Joomla.Update.stat_files;
      var postData = new FormData();
      postData.append('task', 'startExtract');
      postData.append('password', Joomla.Update.password); // Make the initial request to the extraction script

      Joomla.request({
        url: Joomla.Update.ajax_url,
        data: postData,
        method: 'POST',
        perform: true,
        onSuccess: function onSuccess(rawJson) {
          try {
            // If we can decode the response as JSON step through the update
            var data = JSON.parse(rawJson);
            Joomla.Update.stepExtract(data);
          } catch (e) {
            // Decoding failed; display an error
            Joomla.Update.genericErrorMessage(e.message);
          }
        },
        onError: Joomla.Update.handleErrorResponse
      });
    },
    stepExtract: function stepExtract(data) {
      // Did we get an error from the ZIP extraction engine?
      if (data.status === false) {
        Joomla.Update.genericErrorMessage(data.message);
        return;
      }

      var progressDiv = document.getElementById('progress-bar');
      var titleDiv = document.getElementById('update-title'); // Add data to variables

      Joomla.Update.stat_inbytes = data.bytesIn;
      Joomla.Update.stat_percent = data.percent;
      Joomla.Update.stat_percent = Joomla.Update.stat_percent || 100 * (Joomla.Update.stat_inbytes / Joomla.Update.totalsize); // Update GUI

      Joomla.Update.stat_outbytes = data.bytesOut;
      Joomla.Update.stat_files = data.files;

      if (Joomla.Update.stat_percent < 100) {
        progressDiv.classList.remove('bg-success');
        progressDiv.style.width = Joomla.Update.stat_percent + "%";
        progressDiv.setAttribute('aria-valuenow', Joomla.Update.stat_percent);
      } else if (Joomla.Update.stat_percent >= 100) {
        progressDiv.classList.add('bg-success');
        progressDiv.style.width = '100%';
        progressDiv.setAttribute('aria-valuenow', 100);
      }

      progressDiv.innerText = Joomla.Update.stat_percent.toFixed(1) + "%";
      document.getElementById('extbytesin').innerText = Joomla.Update.formatBytes(Joomla.Update.stat_inbytes);
      document.getElementById('extbytesout').innerText = Joomla.Update.formatBytes(Joomla.Update.stat_outbytes);
      document.getElementById('extfiles').innerText = Joomla.Update.stat_files; // Are we done extracting?

      if (data.done) {
        progressDiv.classList.add('bg-success');
        progressDiv.style.width = '100%';
        progressDiv.setAttribute('aria-valuenow', 100);
        titleDiv.innerHTML = Joomla.Text._('COM_JOOMLAUPDATE_UPDATING_COMPLETE');
        Joomla.Update.finalizeUpdate();
        return;
      } // This is required so we can get outside the scope of the previous XHR's success handler.


      window.setTimeout(function () {
        Joomla.Update.delayedStepExtract(data.instance);
      }, 50);
    },
    delayedStepExtract: function delayedStepExtract(instance) {
      Joomla.Update.cached_instance = instance;
      var postData = new FormData();
      postData.append('task', 'stepExtract');
      postData.append('password', Joomla.Update.password);

      if (instance) {
        postData.append('instance', instance);
      }

      Joomla.request({
        url: Joomla.Update.ajax_url,
        data: postData,
        method: 'POST',
        perform: true,
        onSuccess: function onSuccess(rawJson) {
          try {
            var newData = JSON.parse(rawJson);
            Joomla.Update.stepExtract(newData);
          } catch (e) {
            Joomla.Update.genericErrorMessage(e.message);
          }
        },
        onError: Joomla.Update.handleErrorResponse
      });
    },
    finalizeUpdate: function finalizeUpdate() {
      var postData = new FormData();
      postData.append('task', 'finalizeUpdate');
      postData.append('password', Joomla.Update.password);
      Joomla.request({
        url: Joomla.Update.ajax_url,
        data: postData,
        method: 'POST',
        perform: true,
        onSuccess: function onSuccess() {
          window.location = Joomla.Update.return_url;
        },
        onError: Joomla.Update.handleErrorResponse
      });
    },
    formatBytes: function formatBytes(bytes, decimals) {
      if (decimals === void 0) {
        decimals = 2;
      }

      if (bytes === 0) return "0 " + Joomla.Text._('JLIB_SIZE_BYTES');
      var k = 1024;
      var dm = decimals < 0 ? 0 : decimals;
      var sizes = [Joomla.Text._('JLIB_SIZE_BYTES'), Joomla.Text._('JLIB_SIZE_KB'), Joomla.Text._('JLIB_SIZE_MB'), Joomla.Text._('JLIB_SIZE_GB'), Joomla.Text._('JLIB_SIZE_TB'), Joomla.Text._('JLIB_SIZE_PB'), Joomla.Text._('JLIB_SIZE_EB'), Joomla.Text._('JLIB_SIZE_ZB'), Joomla.Text._('JLIB_SIZE_YB')];
      var i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
    },
    resumeButtonHandler: function resumeButtonHandler(e) {
      e.preventDefault();
      document.getElementById('joomlaupdate-progress').classList.remove('d-none');
      document.getElementById('joomlaupdate-error').classList.add('d-none');

      if (Joomla.Update.cached_instance === false) {
        Joomla.Update.startExtract();
      } else {
        Joomla.Update.delayedStepExtract(Joomla.Update.cached_instance);
      }
    },
    restartButtonHandler: function restartButtonHandler(e) {
      e.preventDefault();
      document.getElementById('joomlaupdate-progress').classList.remove('d-none');
      document.getElementById('joomlaupdate-error').classList.add('d-none');
      Joomla.Update.startExtract();
    }
  }; // Add click handlers for the Resume and Restart Update buttons in the error pane.

  var elResume = document.getElementById('joomlaupdate-resume');
  var elRestart = document.getElementById('joomlaupdate-restart');

  if (elResume) {
    elResume.addEventListener('click', Joomla.Update.resumeButtonHandler);
  }

  if (elRestart) {
    elRestart.addEventListener('click', Joomla.Update.restartButtonHandler);
  } // Start the update


  var JoomlaUpdateOptions = Joomla.getOptions('joomlaupdate');

  if (JoomlaUpdateOptions && Object.keys(JoomlaUpdateOptions).length) {
    Joomla.Update.password = JoomlaUpdateOptions.password;
    Joomla.Update.totalsize = JoomlaUpdateOptions.totalsize;
    Joomla.Update.ajax_url = JoomlaUpdateOptions.ajax_url;
    Joomla.Update.return_url = JoomlaUpdateOptions.return_url;
    Joomla.Update.startExtract();
  }

})();
