(function () {
  'use strict';

  function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
    try {
      var info = gen[key](arg);
      var value = info.value;
    } catch (error) {
      reject(error);
      return;
    }

    if (info.done) {
      resolve(value);
    } else {
      Promise.resolve(value).then(_next, _throw);
    }
  }

  function _asyncToGenerator(fn) {
    return function () {
      var self = this,
          args = arguments;
      return new Promise(function (resolve, reject) {
        var gen = fn.apply(self, args);

        function _next(value) {
          asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
        }

        function _throw(err) {
          asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
        }

        _next(undefined);
      });
    };
  }

  /**
   * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */
  if (!Joomla) {
    throw new Error('Joomla API is not properly initialized');
  }

  Joomla.MediaManager = Joomla.MediaManager || {};

  var Edit = /*#__PURE__*/function () {
    function Edit() {
      var _this = this;

      // Get the options from Joomla.optionStorage
      this.options = Joomla.getOptions('com_media', {});

      if (!this.options) {
        throw new Error('Initialization error "edit-images.js"');
      }

      this.extension = this.options.uploadPath.split('.').pop();
      this.fileType = ['jpeg', 'jpg'].includes(this.extension) ? 'jpeg' : this.extension;
      this.options.currentUrl = new URL(window.location.href); // Initiate the registry

      this.original = {
        filename: this.options.uploadPath.split('/').pop(),
        extension: this.extension,
        contents: "data:image/" + this.fileType + ";base64," + this.options.contents
      }; // eslint-disable-next-line no-promise-executor-return

      this.previousPluginDeactivated = new Promise(function (resolve) {
        return resolve;
      });
      this.history = {};
      this.current = this.original;
      this.plugins = {};
      this.baseContainer = document.getElementById('media-manager-edit-container');

      if (!this.baseContainer) {
        throw new Error('The image preview container is missing');
      }

      this.createImageContainer(this.original);
      Joomla.MediaManager.Edit = this;
      window.dispatchEvent(new CustomEvent('media-manager-edit-init')); // Once the DOM is ready, initialize everything

      customElements.whenDefined('joomla-tab').then( /*#__PURE__*/_asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
        var tabContainer, tabsUlElement, links;
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                tabContainer = document.getElementById('myTab');
                tabsUlElement = tabContainer.firstElementChild;
                links = [].slice.call(tabsUlElement.querySelectorAll('button[aria-controls]')); // Couple the tabs with the plugin objects

                links.forEach(function (link, index) {
                  var tab = document.getElementById(link.getAttribute('aria-controls'));

                  if (index === 0) {
                    tab.insertAdjacentElement('beforeend', _this.baseContainer);
                  }

                  link.addEventListener('joomla.tab.hidden', function (_ref2) {
                    var target = _ref2.target;

                    if (!target) {
                      // eslint-disable-next-line no-promise-executor-return
                      _this.previousPluginDeactivated = new Promise(function (resolve) {
                        return resolve;
                      });
                      return;
                    }

                    _this.previousPluginDeactivated = new Promise(function (resolve, reject) {
                      _this.plugins[target.getAttribute('aria-controls').replace('attrib-', '')].Deactivate(_this.imagePreview).then(resolve).catch(function (e) {
                        // eslint-disable-next-line no-console
                        console.log(e);
                        reject();
                      });
                    });
                  });
                  link.addEventListener('joomla.tab.shown', function (_ref3) {
                    var target = _ref3.target;
                    // Move the image container to the correct tab
                    tab.insertAdjacentElement('beforeend', _this.baseContainer);

                    _this.previousPluginDeactivated.then(function () {
                      return _this.plugins[target.getAttribute('aria-controls').replace('attrib-', '')].Activate(_this.imagePreview);
                    }).catch(function (e) {
                      // eslint-disable-next-line no-console
                      console.log(e);
                    });
                  });
                });
                tabContainer.activateTab(0, false);

              case 5:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      })));
      this.addHistoryPoint = this.addHistoryPoint.bind(this);
      this.createImageContainer = this.createImageContainer.bind(this);
      this.Reset = this.Reset.bind(this);
      this.Undo = this.Undo.bind(this);
      this.Redo = this.Redo.bind(this);
      this.createProgressBar = this.createProgressBar.bind(this);
      this.updateProgressBar = this.updateProgressBar.bind(this);
      this.removeProgressBar = this.removeProgressBar.bind(this);
      this.upload = this.upload.bind(this); // Create history entry

      window.addEventListener('mediaManager.history.point', this.addHistoryPoint.bind(this));
    }
    /**
     * Creates a history snapshot
     * PRIVATE
     */


    var _proto = Edit.prototype;

    _proto.addHistoryPoint = function addHistoryPoint() {
      if (this.original !== this.current) {
        var key = Object.keys(this.history).length;

        if (this.history[key] && this.history[key - 1] && this.history[key] === this.history[key - 1]) {
          return;
        }

        this.history[key + 1] = this.current;
      }
    }
    /**
     * Creates the images for edit and preview
     * PRIVATE
     */
    ;

    _proto.createImageContainer = function createImageContainer(data) {
      if (!data.contents) {
        throw new Error('Initialization error "edit-images.js"');
      }

      this.imagePreview = document.createElement('img');
      this.imagePreview.src = data.contents;
      this.imagePreview.id = 'image-preview';
      this.imagePreview.style.height = 'auto';
      this.imagePreview.style.maxWidth = '100%';
      this.baseContainer.appendChild(this.imagePreview);
    } // Reset the image to the initial state
    ;

    _proto.Reset = function Reset() {
      var _this2 = this;

      this.current.contents = "data:image/" + this.fileType + ";base64," + this.options.contents;
      this.imagePreview.setAttribute('src', this.current.contents);
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          _this2.imagePreview.setAttribute('width', _this2.imagePreview.naturalWidth);

          _this2.imagePreview.setAttribute('height', _this2.imagePreview.naturalHeight);
        });
      });
    } // @TODO History
    // eslint-disable-next-line class-methods-use-this
    ;

    _proto.Undo = function Undo() {} // @TODO History
    // eslint-disable-next-line class-methods-use-this
    ;

    _proto.Redo = function Redo() {} // @TODO Create the progress bar
    // eslint-disable-next-line class-methods-use-this
    ;

    _proto.createProgressBar = function createProgressBar() {} // @TODO Update the progress bar
    // eslint-disable-next-line class-methods-use-this
    ;

    _proto.updateProgressBar = function updateProgressBar() {} // @TODO Remove the progress bar
    // eslint-disable-next-line class-methods-use-this
    ;

    _proto.removeProgressBar = function removeProgressBar() {}
    /**
     * Uploads
     * Public
     */
    ;

    _proto.upload = function upload(url, stateChangeCallback) {
      var _this3 = this,
          _JSON$stringify;

      var format = Joomla.MediaManager.Edit.original.extension === 'jpg' ? 'jpeg' : Joomla.MediaManager.Edit.original.extension;

      if (!format) {
        // eslint-disable-next-line prefer-destructuring
        format = /data:image\/(.+);/gm.exec(Joomla.MediaManager.Edit.original.contents)[1];
      }

      if (!format) {
        throw new Error('Unable to determine image format');
      }

      this.xhr = new XMLHttpRequest();

      if (typeof stateChangeCallback === 'function') {
        this.xhr.onreadystatechange = stateChangeCallback;
      }

      this.xhr.upload.onprogress = function (e) {
        _this3.updateProgressBar(e.loaded / e.total * 100);
      };

      this.xhr.onload = function () {
        var resp;

        try {
          resp = JSON.parse(_this3.xhr.responseText);
        } catch (er) {
          resp = null;
        }

        if (resp) {
          if (_this3.xhr.status === 200) {
            if (resp.success === true) {
              _this3.removeProgressBar();
            }

            if (resp.status === '1') {
              Joomla.renderMessages({
                success: [resp.message]
              }, 'true');

              _this3.removeProgressBar();
            }
          }
        } else {
          _this3.removeProgressBar();
        }

        _this3.xhr = null;
      };

      this.xhr.onerror = function () {
        _this3.removeProgressBar();

        _this3.xhr = null;
      };

      this.xhr.open('PUT', url, true);
      this.xhr.setRequestHeader('Content-Type', 'application/json');
      this.createProgressBar();
      this.xhr.send(JSON.stringify((_JSON$stringify = {
        name: Joomla.MediaManager.Edit.options.uploadPath.split('/').pop(),
        content: Joomla.MediaManager.Edit.current.contents.replace("data:image/" + format + ";base64,", '')
      }, _JSON$stringify[Joomla.MediaManager.Edit.options.csrfToken] = 1, _JSON$stringify)));
    };

    return Edit;
  }(); // Initiate the Editor API
  // eslint-disable-next-line no-new


  new Edit();
  /**
   * Compute the current URL
   *
   * @param {boolean} isModal is the URL for a modal window
   *
   * @return {{}} the URL object
   */

  var getUrl = function getUrl(isModal) {
    var newUrl = Joomla.MediaManager.Edit.options.currentUrl;
    var params = new URLSearchParams(newUrl.search);
    params.set('view', 'media');
    params.delete('path');
    params.delete('mediatypes');
    var uploadPath = Joomla.MediaManager.Edit.options.uploadPath;
    var fileDirectory = uploadPath.split('/');
    fileDirectory.pop();
    fileDirectory = fileDirectory.join('/'); // If we are in root add a backslash

    if (fileDirectory.endsWith(':')) {
      fileDirectory = fileDirectory + "/";
    }

    params.set('path', fileDirectory); // Respect the images_only URI param

    var mediaTypes = document.querySelector('input[name="mediatypes"]');
    params.set('mediatypes', mediaTypes && mediaTypes.value ? mediaTypes.value : '0');

    if (isModal) {
      params.set('tmpl', 'component');
    }

    newUrl.search = params;
    return newUrl;
  }; // Customize the Toolbar buttons behavior


  Joomla.submitbutton = function (task) {
    var url = new URL(Joomla.MediaManager.Edit.options.apiBaseUrl + "&task=api.files&path=" + Joomla.MediaManager.Edit.options.uploadPath);

    switch (task) {
      case 'apply':
        Joomla.MediaManager.Edit.upload(url, null);
        Joomla.MediaManager.Edit.imagePreview.src = Joomla.MediaManager.Edit.current.contents;
        Joomla.MediaManager.Edit.original = Joomla.MediaManager.Edit.current;
        Joomla.MediaManager.Edit.history = {};

        _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2() {
          var activeTab;
          return regeneratorRuntime.wrap(function _callee2$(_context2) {
            while (1) {
              switch (_context2.prev = _context2.next) {
                case 0:
                  activeTab = [].slice.call(document.querySelectorAll('joomla-tab-element')).filter(function (tab) {
                    return tab.hasAttribute('active');
                  });
                  _context2.prev = 1;
                  _context2.next = 4;
                  return Joomla.MediaManager.Edit.plugins[activeTab[0].id.replace('attrib-', '')].Deactivate(Joomla.MediaManager.Edit.imagePreview);

                case 4:
                  _context2.next = 6;
                  return Joomla.MediaManager.Edit.plugins[activeTab[0].id.replace('attrib-', '')].Activate(Joomla.MediaManager.Edit.imagePreview);

                case 6:
                  _context2.next = 11;
                  break;

                case 8:
                  _context2.prev = 8;
                  _context2.t0 = _context2["catch"](1);
                  // eslint-disable-next-line no-console
                  console.log(_context2.t0);

                case 11:
                case "end":
                  return _context2.stop();
              }
            }
          }, _callee2, null, [[1, 8]]);
        }))();

        break;

      case 'save':
        Joomla.MediaManager.Edit.upload(url, function () {
          if (Joomla.MediaManager.Edit.xhr.readyState === XMLHttpRequest.DONE) {
            if (window.self !== window.top) {
              window.location = getUrl(true);
            } else {
              window.location = getUrl();
            }
          }
        });
        break;

      case 'cancel':
        if (window.self !== window.top) {
          window.location = getUrl(true);
        } else {
          window.location = getUrl();
        }

        break;

      case 'reset':
        Joomla.MediaManager.Edit.Reset('initial');
        break;
    }
  };

})();
