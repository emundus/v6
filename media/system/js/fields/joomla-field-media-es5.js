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

  function _defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  function _createClass(Constructor, protoProps, staticProps) {
    if (protoProps) _defineProperties(Constructor.prototype, protoProps);
    if (staticProps) _defineProperties(Constructor, staticProps);
    Object.defineProperty(Constructor, "prototype", {
      writable: false
    });
    return Constructor;
  }

  function _inheritsLoose(subClass, superClass) {
    subClass.prototype = Object.create(superClass.prototype);
    subClass.prototype.constructor = subClass;

    _setPrototypeOf(subClass, superClass);
  }

  function _getPrototypeOf(o) {
    _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
      return o.__proto__ || Object.getPrototypeOf(o);
    };
    return _getPrototypeOf(o);
  }

  function _setPrototypeOf(o, p) {
    _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
      o.__proto__ = p;
      return o;
    };

    return _setPrototypeOf(o, p);
  }

  function _isNativeReflectConstruct() {
    if (typeof Reflect === "undefined" || !Reflect.construct) return false;
    if (Reflect.construct.sham) return false;
    if (typeof Proxy === "function") return true;

    try {
      Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {}));
      return true;
    } catch (e) {
      return false;
    }
  }

  function _construct(Parent, args, Class) {
    if (_isNativeReflectConstruct()) {
      _construct = Reflect.construct;
    } else {
      _construct = function _construct(Parent, args, Class) {
        var a = [null];
        a.push.apply(a, args);
        var Constructor = Function.bind.apply(Parent, a);
        var instance = new Constructor();
        if (Class) _setPrototypeOf(instance, Class.prototype);
        return instance;
      };
    }

    return _construct.apply(null, arguments);
  }

  function _isNativeFunction(fn) {
    return Function.toString.call(fn).indexOf("[native code]") !== -1;
  }

  function _wrapNativeSuper(Class) {
    var _cache = typeof Map === "function" ? new Map() : undefined;

    _wrapNativeSuper = function _wrapNativeSuper(Class) {
      if (Class === null || !_isNativeFunction(Class)) return Class;

      if (typeof Class !== "function") {
        throw new TypeError("Super expression must either be null or a function");
      }

      if (typeof _cache !== "undefined") {
        if (_cache.has(Class)) return _cache.get(Class);

        _cache.set(Class, Wrapper);
      }

      function Wrapper() {
        return _construct(Class, arguments, _getPrototypeOf(this).constructor);
      }

      Wrapper.prototype = Object.create(Class.prototype, {
        constructor: {
          value: Wrapper,
          enumerable: false,
          writable: true,
          configurable: true
        }
      });
      return _setPrototypeOf(Wrapper, Class);
    };

    return _wrapNativeSuper(Class);
  }

  function _assertThisInitialized(self) {
    if (self === void 0) {
      throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    }

    return self;
  }

  /**
   * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */
  if (!Joomla) {
    throw new Error('Joomla API is not properly initiated');
  }
  /**
   * Extract the extensions
   *
   * @param {*} path
   * @returns {string}
   */


  var getExtension = function getExtension(path) {
    var parts = path.split(/[#]/);

    if (parts.length > 1) {
      return parts[1].split(/[?]/)[0].split('.').pop().trim();
    }

    return path.split(/[#?]/)[0].split('.').pop().trim();
  };

  var JoomlaFieldMedia = /*#__PURE__*/function (_HTMLElement) {
    _inheritsLoose(JoomlaFieldMedia, _HTMLElement);

    function JoomlaFieldMedia() {
      var _this;

      _this = _HTMLElement.call(this) || this;
      _this.onSelected = _this.onSelected.bind(_assertThisInitialized(_this));
      _this.show = _this.show.bind(_assertThisInitialized(_this));
      _this.clearValue = _this.clearValue.bind(_assertThisInitialized(_this));
      _this.modalClose = _this.modalClose.bind(_assertThisInitialized(_this));
      _this.setValue = _this.setValue.bind(_assertThisInitialized(_this));
      _this.updatePreview = _this.updatePreview.bind(_assertThisInitialized(_this));
      _this.validateValue = _this.validateValue.bind(_assertThisInitialized(_this));
      _this.markValid = _this.markValid.bind(_assertThisInitialized(_this));
      _this.markInvalid = _this.markInvalid.bind(_assertThisInitialized(_this));
      _this.mimeType = '';
      return _this;
    }

    var _proto = JoomlaFieldMedia.prototype;

    // attributeChangedCallback(attr, oldValue, newValue) {}
    _proto.connectedCallback = function connectedCallback() {
      this.button = this.querySelector(this.buttonSelect);
      this.inputElement = this.querySelector(this.input);
      this.buttonClearEl = this.querySelector(this.buttonClear);
      this.modalElement = this.querySelector('.joomla-modal');
      this.buttonSaveSelectedElement = this.querySelector(this.buttonSaveSelected);
      this.previewElement = this.querySelector('.field-media-preview');

      if (!this.button || !this.inputElement || !this.buttonClearEl || !this.modalElement || !this.buttonSaveSelectedElement) {
        throw new Error('Misconfiguaration...');
      }

      this.button.addEventListener('click', this.show); // Bootstrap modal init

      if (this.modalElement && window.bootstrap && window.bootstrap.Modal && !window.bootstrap.Modal.getInstance(this.modalElement)) {
        Joomla.initialiseModal(this.modalElement, {
          isJoomla: true
        });
      }

      if (this.buttonClearEl) {
        this.buttonClearEl.addEventListener('click', this.clearValue);
      }

      this.supportedExtensions = Joomla.getOptions('media-picker', {});

      if (!Object.keys(this.supportedExtensions).length) {
        throw new Error('Joomla API is not properly initiated');
      }

      this.inputElement.removeAttribute('readonly');
      this.inputElement.addEventListener('change', this.validateValue);
      this.updatePreview();
    };

    _proto.disconnectedCallback = function disconnectedCallback() {
      if (this.button) {
        this.button.removeEventListener('click', this.show);
      }

      if (this.buttonClearEl) {
        this.buttonClearEl.removeEventListener('click', this.clearValue);
      }

      if (this.inputElement) {
        this.inputElement.removeEventListener('change', this.validateValue);
      }
    };

    _proto.onSelected = function onSelected(event) {
      event.preventDefault();
      event.stopPropagation();
      this.modalClose();
      return false;
    };

    _proto.show = function show() {
      this.modalElement.open();
      Joomla.selectedMediaFile = {};
      this.buttonSaveSelectedElement.addEventListener('click', this.onSelected);
    };

    _proto.modalClose = /*#__PURE__*/function () {
      var _modalClose = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                _context.prev = 0;
                _context.next = 3;
                return Joomla.getMedia(Joomla.selectedMediaFile, this.inputElement, this);

              case 3:
                _context.next = 8;
                break;

              case 5:
                _context.prev = 5;
                _context.t0 = _context["catch"](0);
                Joomla.renderMessages({
                  error: [Joomla.Text._('JLIB_APPLICATION_ERROR_SERVER')]
                });

              case 8:
                Joomla.selectedMediaFile = {};
                Joomla.Modal.getCurrent().close();

              case 10:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this, [[0, 5]]);
      }));

      function modalClose() {
        return _modalClose.apply(this, arguments);
      }

      return modalClose;
    }();

    _proto.setValue = function setValue(value) {
      this.inputElement.value = value;
      this.validatedUrl = value;
      this.mimeType = Joomla.selectedMediaFile.fileType;
      this.updatePreview(); // trigger change event both on the input and on the custom element

      this.inputElement.dispatchEvent(new Event('change'));
      this.dispatchEvent(new CustomEvent('change', {
        detail: {
          value: value
        },
        bubbles: true
      }));
    };

    _proto.validateValue = /*#__PURE__*/function () {
      var _validateValue = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee2(event) {
        var _this2 = this;

        var value, hashedUrl, urlParts, rest;
        return regeneratorRuntime.wrap(function _callee2$(_context2) {
          while (1) {
            switch (_context2.prev = _context2.next) {
              case 0:
                value = event.target.value;

                if (!(this.validatedUrl === value || value === '')) {
                  _context2.next = 3;
                  break;
                }

                return _context2.abrupt("return");

              case 3:
                if (/^(http(s)?:\/\/).+$/.test(value)) {
                  try {
                    fetch(value).then(function (response) {
                      if (response.status === 200) {
                        _this2.validatedUrl = value;

                        _this2.markValid();
                      } else {
                        _this2.validatedUrl = value;

                        _this2.markInvalid();
                      }
                    });
                  } catch (err) {
                    this.validatedUrl = value;
                    this.markInvalid();
                  }
                } else {
                  if (/^\//.test(value)) {
                    value = value.substring(1);
                  }

                  hashedUrl = value.split('#');
                  urlParts = hashedUrl[0].split('/');
                  rest = urlParts.slice(1);
                  fetch(Joomla.getOptions('system.paths').rootFull + "/" + value).then(function (response) {
                    return response.blob();
                  }).then(function (blob) {
                    if (blob.type.includes('image')) {
                      var img = new Image();
                      img.src = URL.createObjectURL(blob);

                      img.onload = function () {
                        _this2.inputElement.value = urlParts[0] + "/" + rest.join('/') + "#joomlaImage://local-" + urlParts[0] + "/" + rest.join('/') + "?width=" + img.width + "&height=" + img.height;
                        _this2.validatedUrl = urlParts[0] + "/" + rest.join('/') + "#joomlaImage://local-" + urlParts[0] + "/" + rest.join('/') + "?width=" + img.width + "&height=" + img.height;

                        _this2.markValid();
                      };
                    } else if (blob.type.includes('audio')) {
                      _this2.mimeType = blob.type;
                      _this2.inputElement.value = value;
                      _this2.validatedUrl = value;

                      _this2.markValid();
                    } else if (blob.type.includes('video')) {
                      _this2.mimeType = blob.type;
                      _this2.inputElement.value = value;
                      _this2.validatedUrl = value;

                      _this2.markValid();
                    } else if (blob.type.includes('application/pdf')) {
                      _this2.mimeType = blob.type;
                      _this2.inputElement.value = value;
                      _this2.validatedUrl = value;

                      _this2.markValid();
                    } else {
                      _this2.validatedUrl = value;

                      _this2.markInvalid();
                    }
                  }).catch(function () {
                    _this2.setValue(value);

                    _this2.validatedUrl = value;

                    _this2.markInvalid();
                  });
                }

              case 4:
              case "end":
                return _context2.stop();
            }
          }
        }, _callee2, this);
      }));

      function validateValue(_x) {
        return _validateValue.apply(this, arguments);
      }

      return validateValue;
    }();

    _proto.markValid = function markValid() {
      this.inputElement.removeAttribute('required');
      this.inputElement.removeAttribute('pattern');

      if (document.formvalidator) {
        document.formvalidator.validate(this.inputElement);
      }
    };

    _proto.markInvalid = function markInvalid() {
      this.inputElement.setAttribute('required', '');
      this.inputElement.setAttribute('pattern', '/^(http://INVALID/).+$/');

      if (document.formvalidator) {
        document.formvalidator.validate(this.inputElement);
      }
    };

    _proto.clearValue = function clearValue() {
      this.setValue('');
      this.validatedUrl = '';
      this.inputElement.removeAttribute('required');
      this.inputElement.removeAttribute('pattern');

      if (document.formvalidator) {
        document.formvalidator.validate(this.inputElement);
      }
    };

    _proto.updatePreview = function updatePreview() {
      var _this3 = this;

      if (['true', 'static'].indexOf(this.preview) === -1 || this.preview === 'false' || !this.previewElement) {
        return;
      } // Reset preview


      if (this.preview) {
        var value = this.inputElement.value;
        var supportedExtensions = this.supportedExtensions;

        if (!value) {
          this.buttonClearEl.style.display = 'none';
          this.previewElement.innerHTML = Joomla.sanitizeHtml('<span class="field-media-preview-icon"></span>');
        } else {
          var type;
          this.buttonClearEl.style.display = '';
          this.previewElement.innerHTML = '';
          var ext = getExtension(value);
          if (supportedExtensions.images.includes(ext)) type = 'images';
          if (supportedExtensions.audios.includes(ext)) type = 'audios';
          if (supportedExtensions.videos.includes(ext)) type = 'videos';
          if (supportedExtensions.documents.includes(ext)) type = 'documents';
          var previewElement;
          var mediaType = {
            images: function images() {
              if (supportedExtensions.images.includes(ext)) {
                previewElement = new Image();
                previewElement.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
                previewElement.setAttribute('alt', '');
              }
            },
            audios: function audios() {
              if (supportedExtensions.audios.includes(ext)) {
                previewElement = document.createElement('audio');
                previewElement.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
                previewElement.setAttribute('controls', '');
              }
            },
            videos: function videos() {
              if (supportedExtensions.videos.includes(ext)) {
                previewElement = document.createElement('video');
                var previewElementSource = document.createElement('source');
                previewElementSource.src = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
                previewElementSource.type = _this3.mimeType;
                previewElement.setAttribute('controls', '');
                previewElement.setAttribute('width', _this3.previewWidth);
                previewElement.setAttribute('height', _this3.previewHeight);
                previewElement.appendChild(previewElementSource);
              }
            },
            documents: function documents() {
              if (supportedExtensions.documents.includes(ext)) {
                previewElement = document.createElement('object');
                previewElement.data = /http/.test(value) ? value : Joomla.getOptions('system.paths').rootFull + value;
                previewElement.type = _this3.mimeType;
                previewElement.setAttribute('width', _this3.previewWidth);
                previewElement.setAttribute('height', _this3.previewHeight);
              }
            }
          }; // @todo more checks

          if (this.givenType && ['images', 'audios', 'videos', 'documents'].includes(this.givenType)) {
            mediaType[this.givenType]();
          } else if (type && ['images', 'audios', 'videos', 'documents'].includes(type)) {
            mediaType[type]();
          } else {
            return;
          }

          this.previewElement.style.width = this.previewWidth;
          this.previewElement.appendChild(previewElement);
        }
      }
    };

    _createClass(JoomlaFieldMedia, [{
      key: "type",
      get: function get() {
        return this.getAttribute('type');
      },
      set: function set(value) {
        this.setAttribute('type', value);
      }
    }, {
      key: "basePath",
      get: function get() {
        return this.getAttribute('base-path');
      },
      set: function set(value) {
        this.setAttribute('base-path', value);
      }
    }, {
      key: "rootFolder",
      get: function get() {
        return this.getAttribute('root-folder');
      },
      set: function set(value) {
        this.setAttribute('root-folder', value);
      }
    }, {
      key: "url",
      get: function get() {
        return this.getAttribute('url');
      },
      set: function set(value) {
        this.setAttribute('url', value);
      }
    }, {
      key: "modalContainer",
      get: function get() {
        return this.getAttribute('modal-container');
      },
      set: function set(value) {
        this.setAttribute('modal-container', value);
      }
    }, {
      key: "input",
      get: function get() {
        return this.getAttribute('input');
      },
      set: function set(value) {
        this.setAttribute('input', value);
      }
    }, {
      key: "buttonSelect",
      get: function get() {
        return this.getAttribute('button-select');
      },
      set: function set(value) {
        this.setAttribute('button-select', value);
      }
    }, {
      key: "buttonClear",
      get: function get() {
        return this.getAttribute('button-clear');
      },
      set: function set(value) {
        this.setAttribute('button-clear', value);
      }
    }, {
      key: "buttonSaveSelected",
      get: function get() {
        return this.getAttribute('button-save-selected');
      },
      set: function set(value) {
        this.setAttribute('button-save-selected', value);
      }
    }, {
      key: "modalWidth",
      get: function get() {
        return parseInt(this.getAttribute('modal-width'), 10);
      },
      set: function set(value) {
        this.setAttribute('modal-width', value);
      }
    }, {
      key: "modalHeight",
      get: function get() {
        return parseInt(this.getAttribute('modal-height'), 10);
      },
      set: function set(value) {
        this.setAttribute('modal-height', value);
      }
    }, {
      key: "previewWidth",
      get: function get() {
        return parseInt(this.getAttribute('preview-width'), 10);
      },
      set: function set(value) {
        this.setAttribute('preview-width', value);
      }
    }, {
      key: "previewHeight",
      get: function get() {
        return parseInt(this.getAttribute('preview-height'), 10);
      },
      set: function set(value) {
        this.setAttribute('preview-height', value);
      }
    }, {
      key: "preview",
      get: function get() {
        return this.getAttribute('preview');
      },
      set: function set(value) {
        this.setAttribute('preview', value);
      }
    }, {
      key: "previewContainer",
      get: function get() {
        return this.getAttribute('preview-container');
      }
    }], [{
      key: "observedAttributes",
      get: function get() {
        return ['type', 'base-path', 'root-folder', 'url', 'modal-container', 'modal-width', 'modal-height', 'input', 'button-select', 'button-clear', 'button-save-selected', 'preview', 'preview-width', 'preview-height'];
      }
    }]);

    return JoomlaFieldMedia;
  }( /*#__PURE__*/_wrapNativeSuper(HTMLElement));

  customElements.define('joomla-field-media', JoomlaFieldMedia);

})();
