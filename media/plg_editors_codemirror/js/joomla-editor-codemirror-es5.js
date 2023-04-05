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

  var CodemirrorEditor = /*#__PURE__*/function (_HTMLElement) {
    _inheritsLoose(CodemirrorEditor, _HTMLElement);

    function CodemirrorEditor() {
      var _this;

      _this = _HTMLElement.call(this) || this;
      _this.instance = '';
      _this.host = window.location.origin;
      _this.element = _this.querySelector('textarea');
      _this.refresh = _this.refresh.bind(_assertThisInitialized(_this)); // Observer instance to refresh the Editor when it become visible, eg after Tab switching

      _this.intersectionObserver = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting && _this.instance) {
          _this.instance.refresh();
        }
      }, {
        threshold: 0
      });
      return _this;
    }

    var _proto = CodemirrorEditor.prototype;

    _proto.attributeChangedCallback = function attributeChangedCallback(attr, oldValue, newValue) {
      switch (attr) {
        case 'options':
          if (oldValue && newValue !== oldValue) {
            this.refresh(this.element);
          }

          break;
      }
    };

    _proto.connectedCallback = /*#__PURE__*/function () {
      var _connectedCallback = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
        var _this2 = this;

        var cmPath, addonsPath, that;
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                cmPath = this.getAttribute('editor');
                addonsPath = this.getAttribute('addons');
                _context.next = 4;
                return import(this.host + "/" + cmPath);

              case 4:
                if (!this.options.keyMapUrl) {
                  _context.next = 7;
                  break;
                }

                _context.next = 7;
                return import(this.host + "/" + this.options.keyMapUrl);

              case 7:
                _context.next = 9;
                return import(this.host + "/" + addonsPath);

              case 9:
                that = this; // For mode autoloading.

                window.CodeMirror.modeURL = this.getAttribute('mod-path'); // Fire this function any time an editor is created.

                window.CodeMirror.defineInitHook(function (editor) {
                  var _map;

                  // Try to set up the mode
                  var mode = window.CodeMirror.findModeByName(editor.options.mode || '') || window.CodeMirror.findModeByExtension(editor.options.mode || '');
                  window.CodeMirror.autoLoadMode(editor, typeof mode === 'object' ? mode.mode : editor.options.mode);

                  if (mode && mode.mime) {
                    // Fix the x-php error
                    if (['text/x-php', 'application/x-httpd-php', 'application/x-httpd-php-open'].includes(mode.mime)) {
                      editor.setOption('mode', 'php');
                    } else if (mode.mime === 'text/html') {
                      editor.setOption('mode', mode.mode);
                    } else {
                      editor.setOption('mode', mode.mime);
                    }
                  }

                  var toggleFullScreen = function toggleFullScreen() {
                    that.instance.setOption('fullScreen', !that.instance.getOption('fullScreen'));
                    var header = document.getElementById('subhead');

                    if (header) {
                      var header1 = document.getElementById('header');
                      header1.classList.toggle('hidden');
                      header.classList.toggle('hidden');
                      that.instance.display.wrapper.style.top = header.getBoundingClientRect().height + "px";
                    }
                  };

                  var closeFullScreen = function closeFullScreen() {
                    that.instance.getOption('fullScreen');
                    that.instance.setOption('fullScreen', false);

                    if (!that.instance.getOption('fullScreen')) {
                      var header = document.getElementById('subhead');

                      if (header) {
                        var header1 = document.getElementById('header');
                        header.classList.toggle('hidden');
                        header1.classList.toggle('hidden');
                        that.instance.display.wrapper.style.top = header.getBoundingClientRect().height + "px";
                      }
                    }
                  };

                  var map = (_map = {
                    'Ctrl-Q': toggleFullScreen
                  }, _map[that.getAttribute('fs-combo')] = toggleFullScreen, _map.Esc = closeFullScreen, _map);
                  editor.addKeyMap(map);

                  var makeMarker = function makeMarker() {
                    var marker = document.createElement('div');
                    marker.className = 'CodeMirror-markergutter-mark';
                    return marker;
                  }; // Handle gutter clicks (place or remove a marker).


                  editor.on('gutterClick', function (ed, n, gutter) {
                    if (gutter !== 'CodeMirror-markergutter') {
                      return;
                    }

                    var info = ed.lineInfo(n);
                    var hasMarker = !!info.gutterMarkers && !!info.gutterMarkers['CodeMirror-markergutter'];
                    ed.setGutterMarker(n, 'CodeMirror-markergutter', hasMarker ? null : makeMarker());
                  });
                  /* Some browsers do something weird with the fieldset which doesn't
                    work well with CodeMirror. Fix it. */

                  if (that.parentNode.tagName.toLowerCase() === 'fieldset') {
                    that.parentNode.style.minWidth = 0;
                  }
                }); // Register Editor

                this.instance = window.CodeMirror.fromTextArea(this.element, this.options);

                this.instance.disable = function (disabled) {
                  return _this2.setOption('readOnly', disabled ? 'nocursor' : false);
                };

                Joomla.editors.instances[this.element.id] = this.instance; // Watch when the element in viewport, and refresh the editor

                this.intersectionObserver.observe(this);

              case 16:
              case "end":
                return _context.stop();
            }
          }
        }, _callee, this);
      }));

      function connectedCallback() {
        return _connectedCallback.apply(this, arguments);
      }

      return connectedCallback;
    }();

    _proto.disconnectedCallback = function disconnectedCallback() {
      // Remove from the Joomla API
      delete Joomla.editors.instances[this.element.id]; // Remove from observer

      this.intersectionObserver.unobserve(this);
    };

    _proto.refresh = function refresh(element) {
      this.instance.fromTextArea(element, this.options);
    };

    _createClass(CodemirrorEditor, [{
      key: "options",
      get: function get() {
        return JSON.parse(this.getAttribute('options'));
      },
      set: function set(value) {
        this.setAttribute('options', value);
      }
    }], [{
      key: "observedAttributes",
      get: function get() {
        return ['options'];
      }
    }]);

    return CodemirrorEditor;
  }( /*#__PURE__*/_wrapNativeSuper(HTMLElement));

  customElements.define('joomla-editor-codemirror', CodemirrorEditor);

})();
