var JoomlaMediaManager = (function () {
  'use strict';

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

  function _unsupportedIterableToArray(o, minLen) {
    if (!o) return;
    if (typeof o === "string") return _arrayLikeToArray(o, minLen);
    var n = Object.prototype.toString.call(o).slice(8, -1);
    if (n === "Object" && o.constructor) n = o.constructor.name;
    if (n === "Map" || n === "Set") return Array.from(o);
    if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
  }

  function _arrayLikeToArray(arr, len) {
    if (len == null || len > arr.length) len = arr.length;

    for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i];

    return arr2;
  }

  function _createForOfIteratorHelperLoose(o, allowArrayLike) {
    var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"];
    if (it) return (it = it.call(o)).next.bind(it);

    if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") {
      if (it) o = it;
      var i = 0;
      return function () {
        if (i >= o.length) return {
          done: true
        };
        return {
          done: false,
          value: o[i++]
        };
      };
    }

    throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }

  var commonjsGlobal = typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};

  var check = function (it) {
    return it && it.Math == Math && it;
  };

  // https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
  var global$1e =
    // eslint-disable-next-line es/no-global-this -- safe
    check(typeof globalThis == 'object' && globalThis) ||
    check(typeof window == 'object' && window) ||
    // eslint-disable-next-line no-restricted-globals -- safe
    check(typeof self == 'object' && self) ||
    check(typeof commonjsGlobal == 'object' && commonjsGlobal) ||
    // eslint-disable-next-line no-new-func -- fallback
    (function () { return this; })() || Function('return this')();

  var objectGetOwnPropertyDescriptor = {};

  var fails$J = function (exec) {
    try {
      return !!exec();
    } catch (error) {
      return true;
    }
  };

  var fails$I = fails$J;

  // Detect IE8's incomplete defineProperty implementation
  var descriptors = !fails$I(function () {
    // eslint-disable-next-line es/no-object-defineproperty -- required for testing
    return Object.defineProperty({}, 1, { get: function () { return 7; } })[1] != 7;
  });

  var call$r = Function.prototype.call;

  var functionCall = call$r.bind ? call$r.bind(call$r) : function () {
    return call$r.apply(call$r, arguments);
  };

  var objectPropertyIsEnumerable = {};

  var $propertyIsEnumerable$2 = {}.propertyIsEnumerable;
  // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
  var getOwnPropertyDescriptor$6 = Object.getOwnPropertyDescriptor;

  // Nashorn ~ JDK8 bug
  var NASHORN_BUG = getOwnPropertyDescriptor$6 && !$propertyIsEnumerable$2.call({ 1: 2 }, 1);

  // `Object.prototype.propertyIsEnumerable` method implementation
  // https://tc39.es/ecma262/#sec-object.prototype.propertyisenumerable
  objectPropertyIsEnumerable.f = NASHORN_BUG ? function propertyIsEnumerable(V) {
    var descriptor = getOwnPropertyDescriptor$6(this, V);
    return !!descriptor && descriptor.enumerable;
  } : $propertyIsEnumerable$2;

  var createPropertyDescriptor$8 = function (bitmap, value) {
    return {
      enumerable: !(bitmap & 1),
      configurable: !(bitmap & 2),
      writable: !(bitmap & 4),
      value: value
    };
  };

  var FunctionPrototype$3 = Function.prototype;
  var bind$c = FunctionPrototype$3.bind;
  var call$q = FunctionPrototype$3.call;
  var callBind = bind$c && bind$c.bind(call$q);

  var functionUncurryThis = bind$c ? function (fn) {
    return fn && callBind(call$q, fn);
  } : function (fn) {
    return fn && function () {
      return call$q.apply(fn, arguments);
    };
  };

  var uncurryThis$O = functionUncurryThis;

  var toString$i = uncurryThis$O({}.toString);
  var stringSlice$a = uncurryThis$O(''.slice);

  var classofRaw$1 = function (it) {
    return stringSlice$a(toString$i(it), 8, -1);
  };

  var global$1d = global$1e;
  var uncurryThis$N = functionUncurryThis;
  var fails$H = fails$J;
  var classof$e = classofRaw$1;

  var Object$5 = global$1d.Object;
  var split$3 = uncurryThis$N(''.split);

  // fallback for non-array-like ES3 and non-enumerable old V8 strings
  var indexedObject = fails$H(function () {
    // throws an error in rhino, see https://github.com/mozilla/rhino/issues/346
    // eslint-disable-next-line no-prototype-builtins -- safe
    return !Object$5('z').propertyIsEnumerable(0);
  }) ? function (it) {
    return classof$e(it) == 'String' ? split$3(it, '') : Object$5(it);
  } : Object$5;

  var global$1c = global$1e;

  var TypeError$n = global$1c.TypeError;

  // `RequireObjectCoercible` abstract operation
  // https://tc39.es/ecma262/#sec-requireobjectcoercible
  var requireObjectCoercible$d = function (it) {
    if (it == undefined) throw TypeError$n("Can't call method on " + it);
    return it;
  };

  // toObject with fallback for non-array-like ES3 strings
  var IndexedObject$4 = indexedObject;
  var requireObjectCoercible$c = requireObjectCoercible$d;

  var toIndexedObject$b = function (it) {
    return IndexedObject$4(requireObjectCoercible$c(it));
  };

  // `IsCallable` abstract operation
  // https://tc39.es/ecma262/#sec-iscallable
  var isCallable$q = function (argument) {
    return typeof argument == 'function';
  };

  var isCallable$p = isCallable$q;

  var isObject$s = function (it) {
    return typeof it == 'object' ? it !== null : isCallable$p(it);
  };

  var global$1b = global$1e;
  var isCallable$o = isCallable$q;

  var aFunction = function (argument) {
    return isCallable$o(argument) ? argument : undefined;
  };

  var getBuiltIn$a = function (namespace, method) {
    return arguments.length < 2 ? aFunction(global$1b[namespace]) : global$1b[namespace] && global$1b[namespace][method];
  };

  var uncurryThis$M = functionUncurryThis;

  var objectIsPrototypeOf = uncurryThis$M({}.isPrototypeOf);

  var getBuiltIn$9 = getBuiltIn$a;

  var engineUserAgent = getBuiltIn$9('navigator', 'userAgent') || '';

  var global$1a = global$1e;
  var userAgent$5 = engineUserAgent;

  var process$3 = global$1a.process;
  var Deno = global$1a.Deno;
  var versions = process$3 && process$3.versions || Deno && Deno.version;
  var v8 = versions && versions.v8;
  var match, version$1;

  if (v8) {
    match = v8.split('.');
    // in old Chrome, versions of V8 isn't V8 = Chrome / 10
    // but their correct versions are not interesting for us
    version$1 = match[0] > 0 && match[0] < 4 ? 1 : +(match[0] + match[1]);
  }

  // BrowserFS NodeJS `process` polyfill incorrectly set `.v8` to `0.0`
  // so check `userAgent` even if `.v8` exists, but 0
  if (!version$1 && userAgent$5) {
    match = userAgent$5.match(/Edge\/(\d+)/);
    if (!match || match[1] >= 74) {
      match = userAgent$5.match(/Chrome\/(\d+)/);
      if (match) version$1 = +match[1];
    }
  }

  var engineV8Version = version$1;

  /* eslint-disable es/no-symbol -- required for testing */

  var V8_VERSION$3 = engineV8Version;
  var fails$G = fails$J;

  // eslint-disable-next-line es/no-object-getownpropertysymbols -- required for testing
  var nativeSymbol = !!Object.getOwnPropertySymbols && !fails$G(function () {
    var symbol = Symbol();
    // Chrome 38 Symbol has incorrect toString conversion
    // `get-own-property-symbols` polyfill symbols converted to object are not Symbol instances
    return !String(symbol) || !(Object(symbol) instanceof Symbol) ||
      // Chrome 38-40 symbols are not inherited from DOM collections prototypes to instances
      !Symbol.sham && V8_VERSION$3 && V8_VERSION$3 < 41;
  });

  /* eslint-disable es/no-symbol -- required for testing */

  var NATIVE_SYMBOL$3 = nativeSymbol;

  var useSymbolAsUid = NATIVE_SYMBOL$3
    && !Symbol.sham
    && typeof Symbol.iterator == 'symbol';

  var global$19 = global$1e;
  var getBuiltIn$8 = getBuiltIn$a;
  var isCallable$n = isCallable$q;
  var isPrototypeOf$8 = objectIsPrototypeOf;
  var USE_SYMBOL_AS_UID$1 = useSymbolAsUid;

  var Object$4 = global$19.Object;

  var isSymbol$6 = USE_SYMBOL_AS_UID$1 ? function (it) {
    return typeof it == 'symbol';
  } : function (it) {
    var $Symbol = getBuiltIn$8('Symbol');
    return isCallable$n($Symbol) && isPrototypeOf$8($Symbol.prototype, Object$4(it));
  };

  var global$18 = global$1e;

  var String$6 = global$18.String;

  var tryToString$5 = function (argument) {
    try {
      return String$6(argument);
    } catch (error) {
      return 'Object';
    }
  };

  var global$17 = global$1e;
  var isCallable$m = isCallable$q;
  var tryToString$4 = tryToString$5;

  var TypeError$m = global$17.TypeError;

  // `Assert: IsCallable(argument) is true`
  var aCallable$8 = function (argument) {
    if (isCallable$m(argument)) return argument;
    throw TypeError$m(tryToString$4(argument) + ' is not a function');
  };

  var aCallable$7 = aCallable$8;

  // `GetMethod` abstract operation
  // https://tc39.es/ecma262/#sec-getmethod
  var getMethod$7 = function (V, P) {
    var func = V[P];
    return func == null ? undefined : aCallable$7(func);
  };

  var global$16 = global$1e;
  var call$p = functionCall;
  var isCallable$l = isCallable$q;
  var isObject$r = isObject$s;

  var TypeError$l = global$16.TypeError;

  // `OrdinaryToPrimitive` abstract operation
  // https://tc39.es/ecma262/#sec-ordinarytoprimitive
  var ordinaryToPrimitive$1 = function (input, pref) {
    var fn, val;
    if (pref === 'string' && isCallable$l(fn = input.toString) && !isObject$r(val = call$p(fn, input))) return val;
    if (isCallable$l(fn = input.valueOf) && !isObject$r(val = call$p(fn, input))) return val;
    if (pref !== 'string' && isCallable$l(fn = input.toString) && !isObject$r(val = call$p(fn, input))) return val;
    throw TypeError$l("Can't convert object to primitive value");
  };

  var shared$5 = {exports: {}};

  var isPure = false;

  var global$15 = global$1e;

  // eslint-disable-next-line es/no-object-defineproperty -- safe
  var defineProperty$b = Object.defineProperty;

  var setGlobal$3 = function (key, value) {
    try {
      defineProperty$b(global$15, key, { value: value, configurable: true, writable: true });
    } catch (error) {
      global$15[key] = value;
    } return value;
  };

  var global$14 = global$1e;
  var setGlobal$2 = setGlobal$3;

  var SHARED = '__core-js_shared__';
  var store$4 = global$14[SHARED] || setGlobal$2(SHARED, {});

  var sharedStore = store$4;

  var store$3 = sharedStore;

  (shared$5.exports = function (key, value) {
    return store$3[key] || (store$3[key] = value !== undefined ? value : {});
  })('versions', []).push({
    version: '3.20.1',
    mode: 'global',
    copyright: '© 2021 Denis Pushkarev (zloirock.ru)'
  });

  var global$13 = global$1e;
  var requireObjectCoercible$b = requireObjectCoercible$d;

  var Object$3 = global$13.Object;

  // `ToObject` abstract operation
  // https://tc39.es/ecma262/#sec-toobject
  var toObject$g = function (argument) {
    return Object$3(requireObjectCoercible$b(argument));
  };

  var uncurryThis$L = functionUncurryThis;
  var toObject$f = toObject$g;

  var hasOwnProperty$1 = uncurryThis$L({}.hasOwnProperty);

  // `HasOwnProperty` abstract operation
  // https://tc39.es/ecma262/#sec-hasownproperty
  var hasOwnProperty_1 = Object.hasOwn || function hasOwn(it, key) {
    return hasOwnProperty$1(toObject$f(it), key);
  };

  var uncurryThis$K = functionUncurryThis;

  var id$2 = 0;
  var postfix = Math.random();
  var toString$h = uncurryThis$K(1.0.toString);

  var uid$7 = function (key) {
    return 'Symbol(' + (key === undefined ? '' : key) + ')_' + toString$h(++id$2 + postfix, 36);
  };

  var global$12 = global$1e;
  var shared$4 = shared$5.exports;
  var hasOwn$l = hasOwnProperty_1;
  var uid$6 = uid$7;
  var NATIVE_SYMBOL$2 = nativeSymbol;
  var USE_SYMBOL_AS_UID = useSymbolAsUid;

  var WellKnownSymbolsStore$1 = shared$4('wks');
  var Symbol$1 = global$12.Symbol;
  var symbolFor = Symbol$1 && Symbol$1['for'];
  var createWellKnownSymbol = USE_SYMBOL_AS_UID ? Symbol$1 : Symbol$1 && Symbol$1.withoutSetter || uid$6;

  var wellKnownSymbol$s = function (name) {
    if (!hasOwn$l(WellKnownSymbolsStore$1, name) || !(NATIVE_SYMBOL$2 || typeof WellKnownSymbolsStore$1[name] == 'string')) {
      var description = 'Symbol.' + name;
      if (NATIVE_SYMBOL$2 && hasOwn$l(Symbol$1, name)) {
        WellKnownSymbolsStore$1[name] = Symbol$1[name];
      } else if (USE_SYMBOL_AS_UID && symbolFor) {
        WellKnownSymbolsStore$1[name] = symbolFor(description);
      } else {
        WellKnownSymbolsStore$1[name] = createWellKnownSymbol(description);
      }
    } return WellKnownSymbolsStore$1[name];
  };

  var global$11 = global$1e;
  var call$o = functionCall;
  var isObject$q = isObject$s;
  var isSymbol$5 = isSymbol$6;
  var getMethod$6 = getMethod$7;
  var ordinaryToPrimitive = ordinaryToPrimitive$1;
  var wellKnownSymbol$r = wellKnownSymbol$s;

  var TypeError$k = global$11.TypeError;
  var TO_PRIMITIVE$1 = wellKnownSymbol$r('toPrimitive');

  // `ToPrimitive` abstract operation
  // https://tc39.es/ecma262/#sec-toprimitive
  var toPrimitive$2 = function (input, pref) {
    if (!isObject$q(input) || isSymbol$5(input)) return input;
    var exoticToPrim = getMethod$6(input, TO_PRIMITIVE$1);
    var result;
    if (exoticToPrim) {
      if (pref === undefined) pref = 'default';
      result = call$o(exoticToPrim, input, pref);
      if (!isObject$q(result) || isSymbol$5(result)) return result;
      throw TypeError$k("Can't convert object to primitive value");
    }
    if (pref === undefined) pref = 'number';
    return ordinaryToPrimitive(input, pref);
  };

  var toPrimitive$1 = toPrimitive$2;
  var isSymbol$4 = isSymbol$6;

  // `ToPropertyKey` abstract operation
  // https://tc39.es/ecma262/#sec-topropertykey
  var toPropertyKey$6 = function (argument) {
    var key = toPrimitive$1(argument, 'string');
    return isSymbol$4(key) ? key : key + '';
  };

  var global$10 = global$1e;
  var isObject$p = isObject$s;

  var document$3 = global$10.document;
  // typeof document.createElement is 'object' in old IE
  var EXISTS$1 = isObject$p(document$3) && isObject$p(document$3.createElement);

  var documentCreateElement$2 = function (it) {
    return EXISTS$1 ? document$3.createElement(it) : {};
  };

  var DESCRIPTORS$j = descriptors;
  var fails$F = fails$J;
  var createElement$1 = documentCreateElement$2;

  // Thank's IE8 for his funny defineProperty
  var ie8DomDefine = !DESCRIPTORS$j && !fails$F(function () {
    // eslint-disable-next-line es/no-object-defineproperty -- required for testing
    return Object.defineProperty(createElement$1('div'), 'a', {
      get: function () { return 7; }
    }).a != 7;
  });

  var DESCRIPTORS$i = descriptors;
  var call$n = functionCall;
  var propertyIsEnumerableModule$2 = objectPropertyIsEnumerable;
  var createPropertyDescriptor$7 = createPropertyDescriptor$8;
  var toIndexedObject$a = toIndexedObject$b;
  var toPropertyKey$5 = toPropertyKey$6;
  var hasOwn$k = hasOwnProperty_1;
  var IE8_DOM_DEFINE$1 = ie8DomDefine;

  // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
  var $getOwnPropertyDescriptor$1 = Object.getOwnPropertyDescriptor;

  // `Object.getOwnPropertyDescriptor` method
  // https://tc39.es/ecma262/#sec-object.getownpropertydescriptor
  objectGetOwnPropertyDescriptor.f = DESCRIPTORS$i ? $getOwnPropertyDescriptor$1 : function getOwnPropertyDescriptor(O, P) {
    O = toIndexedObject$a(O);
    P = toPropertyKey$5(P);
    if (IE8_DOM_DEFINE$1) try {
      return $getOwnPropertyDescriptor$1(O, P);
    } catch (error) { /* empty */ }
    if (hasOwn$k(O, P)) return createPropertyDescriptor$7(!call$n(propertyIsEnumerableModule$2.f, O, P), O[P]);
  };

  var objectDefineProperty = {};

  var global$$ = global$1e;
  var isObject$o = isObject$s;

  var String$5 = global$$.String;
  var TypeError$j = global$$.TypeError;

  // `Assert: Type(argument) is Object`
  var anObject$q = function (argument) {
    if (isObject$o(argument)) return argument;
    throw TypeError$j(String$5(argument) + ' is not an object');
  };

  var global$_ = global$1e;
  var DESCRIPTORS$h = descriptors;
  var IE8_DOM_DEFINE = ie8DomDefine;
  var anObject$p = anObject$q;
  var toPropertyKey$4 = toPropertyKey$6;

  var TypeError$i = global$_.TypeError;
  // eslint-disable-next-line es/no-object-defineproperty -- safe
  var $defineProperty$1 = Object.defineProperty;

  // `Object.defineProperty` method
  // https://tc39.es/ecma262/#sec-object.defineproperty
  objectDefineProperty.f = DESCRIPTORS$h ? $defineProperty$1 : function defineProperty(O, P, Attributes) {
    anObject$p(O);
    P = toPropertyKey$4(P);
    anObject$p(Attributes);
    if (IE8_DOM_DEFINE) try {
      return $defineProperty$1(O, P, Attributes);
    } catch (error) { /* empty */ }
    if ('get' in Attributes || 'set' in Attributes) throw TypeError$i('Accessors not supported');
    if ('value' in Attributes) O[P] = Attributes.value;
    return O;
  };

  var DESCRIPTORS$g = descriptors;
  var definePropertyModule$9 = objectDefineProperty;
  var createPropertyDescriptor$6 = createPropertyDescriptor$8;

  var createNonEnumerableProperty$a = DESCRIPTORS$g ? function (object, key, value) {
    return definePropertyModule$9.f(object, key, createPropertyDescriptor$6(1, value));
  } : function (object, key, value) {
    object[key] = value;
    return object;
  };

  var redefine$e = {exports: {}};

  var uncurryThis$J = functionUncurryThis;
  var isCallable$k = isCallable$q;
  var store$2 = sharedStore;

  var functionToString$1 = uncurryThis$J(Function.toString);

  // this helper broken in `core-js@3.4.1-3.4.4`, so we can't use `shared` helper
  if (!isCallable$k(store$2.inspectSource)) {
    store$2.inspectSource = function (it) {
      return functionToString$1(it);
    };
  }

  var inspectSource$4 = store$2.inspectSource;

  var global$Z = global$1e;
  var isCallable$j = isCallable$q;
  var inspectSource$3 = inspectSource$4;

  var WeakMap$2 = global$Z.WeakMap;

  var nativeWeakMap = isCallable$j(WeakMap$2) && /native code/.test(inspectSource$3(WeakMap$2));

  var shared$3 = shared$5.exports;
  var uid$5 = uid$7;

  var keys$3 = shared$3('keys');

  var sharedKey$4 = function (key) {
    return keys$3[key] || (keys$3[key] = uid$5(key));
  };

  var hiddenKeys$6 = {};

  var NATIVE_WEAK_MAP$1 = nativeWeakMap;
  var global$Y = global$1e;
  var uncurryThis$I = functionUncurryThis;
  var isObject$n = isObject$s;
  var createNonEnumerableProperty$9 = createNonEnumerableProperty$a;
  var hasOwn$j = hasOwnProperty_1;
  var shared$2 = sharedStore;
  var sharedKey$3 = sharedKey$4;
  var hiddenKeys$5 = hiddenKeys$6;

  var OBJECT_ALREADY_INITIALIZED = 'Object already initialized';
  var TypeError$h = global$Y.TypeError;
  var WeakMap$1 = global$Y.WeakMap;
  var set$6, get$4, has$2;

  var enforce = function (it) {
    return has$2(it) ? get$4(it) : set$6(it, {});
  };

  var getterFor = function (TYPE) {
    return function (it) {
      var state;
      if (!isObject$n(it) || (state = get$4(it)).type !== TYPE) {
        throw TypeError$h('Incompatible receiver, ' + TYPE + ' required');
      } return state;
    };
  };

  if (NATIVE_WEAK_MAP$1 || shared$2.state) {
    var store$1 = shared$2.state || (shared$2.state = new WeakMap$1());
    var wmget = uncurryThis$I(store$1.get);
    var wmhas = uncurryThis$I(store$1.has);
    var wmset = uncurryThis$I(store$1.set);
    set$6 = function (it, metadata) {
      if (wmhas(store$1, it)) throw new TypeError$h(OBJECT_ALREADY_INITIALIZED);
      metadata.facade = it;
      wmset(store$1, it, metadata);
      return metadata;
    };
    get$4 = function (it) {
      return wmget(store$1, it) || {};
    };
    has$2 = function (it) {
      return wmhas(store$1, it);
    };
  } else {
    var STATE = sharedKey$3('state');
    hiddenKeys$5[STATE] = true;
    set$6 = function (it, metadata) {
      if (hasOwn$j(it, STATE)) throw new TypeError$h(OBJECT_ALREADY_INITIALIZED);
      metadata.facade = it;
      createNonEnumerableProperty$9(it, STATE, metadata);
      return metadata;
    };
    get$4 = function (it) {
      return hasOwn$j(it, STATE) ? it[STATE] : {};
    };
    has$2 = function (it) {
      return hasOwn$j(it, STATE);
    };
  }

  var internalState = {
    set: set$6,
    get: get$4,
    has: has$2,
    enforce: enforce,
    getterFor: getterFor
  };

  var DESCRIPTORS$f = descriptors;
  var hasOwn$i = hasOwnProperty_1;

  var FunctionPrototype$2 = Function.prototype;
  // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
  var getDescriptor = DESCRIPTORS$f && Object.getOwnPropertyDescriptor;

  var EXISTS = hasOwn$i(FunctionPrototype$2, 'name');
  // additional protection from minified / mangled / dropped function names
  var PROPER = EXISTS && (function something() { /* empty */ }).name === 'something';
  var CONFIGURABLE = EXISTS && (!DESCRIPTORS$f || (DESCRIPTORS$f && getDescriptor(FunctionPrototype$2, 'name').configurable));

  var functionName = {
    EXISTS: EXISTS,
    PROPER: PROPER,
    CONFIGURABLE: CONFIGURABLE
  };

  var global$X = global$1e;
  var isCallable$i = isCallable$q;
  var hasOwn$h = hasOwnProperty_1;
  var createNonEnumerableProperty$8 = createNonEnumerableProperty$a;
  var setGlobal$1 = setGlobal$3;
  var inspectSource$2 = inspectSource$4;
  var InternalStateModule$a = internalState;
  var CONFIGURABLE_FUNCTION_NAME$2 = functionName.CONFIGURABLE;

  var getInternalState$7 = InternalStateModule$a.get;
  var enforceInternalState$1 = InternalStateModule$a.enforce;
  var TEMPLATE = String(String).split('String');

  (redefine$e.exports = function (O, key, value, options) {
    var unsafe = options ? !!options.unsafe : false;
    var simple = options ? !!options.enumerable : false;
    var noTargetGet = options ? !!options.noTargetGet : false;
    var name = options && options.name !== undefined ? options.name : key;
    var state;
    if (isCallable$i(value)) {
      if (String(name).slice(0, 7) === 'Symbol(') {
        name = '[' + String(name).replace(/^Symbol\(([^)]*)\)/, '$1') + ']';
      }
      if (!hasOwn$h(value, 'name') || (CONFIGURABLE_FUNCTION_NAME$2 && value.name !== name)) {
        createNonEnumerableProperty$8(value, 'name', name);
      }
      state = enforceInternalState$1(value);
      if (!state.source) {
        state.source = TEMPLATE.join(typeof name == 'string' ? name : '');
      }
    }
    if (O === global$X) {
      if (simple) O[key] = value;
      else setGlobal$1(key, value);
      return;
    } else if (!unsafe) {
      delete O[key];
    } else if (!noTargetGet && O[key]) {
      simple = true;
    }
    if (simple) O[key] = value;
    else createNonEnumerableProperty$8(O, key, value);
  // add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative
  })(Function.prototype, 'toString', function toString() {
    return isCallable$i(this) && getInternalState$7(this).source || inspectSource$2(this);
  });

  var objectGetOwnPropertyNames = {};

  var ceil = Math.ceil;
  var floor$8 = Math.floor;

  // `ToIntegerOrInfinity` abstract operation
  // https://tc39.es/ecma262/#sec-tointegerorinfinity
  var toIntegerOrInfinity$c = function (argument) {
    var number = +argument;
    // eslint-disable-next-line no-self-compare -- safe
    return number !== number || number === 0 ? 0 : (number > 0 ? floor$8 : ceil)(number);
  };

  var toIntegerOrInfinity$b = toIntegerOrInfinity$c;

  var max$4 = Math.max;
  var min$8 = Math.min;

  // Helper for a popular repeating case of the spec:
  // Let integer be ? ToInteger(index).
  // If integer < 0, let result be max((length + integer), 0); else let result be min(integer, length).
  var toAbsoluteIndex$7 = function (index, length) {
    var integer = toIntegerOrInfinity$b(index);
    return integer < 0 ? max$4(integer + length, 0) : min$8(integer, length);
  };

  var toIntegerOrInfinity$a = toIntegerOrInfinity$c;

  var min$7 = Math.min;

  // `ToLength` abstract operation
  // https://tc39.es/ecma262/#sec-tolength
  var toLength$a = function (argument) {
    return argument > 0 ? min$7(toIntegerOrInfinity$a(argument), 0x1FFFFFFFFFFFFF) : 0; // 2 ** 53 - 1 == 9007199254740991
  };

  var toLength$9 = toLength$a;

  // `LengthOfArrayLike` abstract operation
  // https://tc39.es/ecma262/#sec-lengthofarraylike
  var lengthOfArrayLike$h = function (obj) {
    return toLength$9(obj.length);
  };

  var toIndexedObject$9 = toIndexedObject$b;
  var toAbsoluteIndex$6 = toAbsoluteIndex$7;
  var lengthOfArrayLike$g = lengthOfArrayLike$h;

  // `Array.prototype.{ indexOf, includes }` methods implementation
  var createMethod$5 = function (IS_INCLUDES) {
    return function ($this, el, fromIndex) {
      var O = toIndexedObject$9($this);
      var length = lengthOfArrayLike$g(O);
      var index = toAbsoluteIndex$6(fromIndex, length);
      var value;
      // Array#includes uses SameValueZero equality algorithm
      // eslint-disable-next-line no-self-compare -- NaN check
      if (IS_INCLUDES && el != el) while (length > index) {
        value = O[index++];
        // eslint-disable-next-line no-self-compare -- NaN check
        if (value != value) return true;
      // Array#indexOf ignores holes, Array#includes - not
      } else for (;length > index; index++) {
        if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES || index || 0;
      } return !IS_INCLUDES && -1;
    };
  };

  var arrayIncludes = {
    // `Array.prototype.includes` method
    // https://tc39.es/ecma262/#sec-array.prototype.includes
    includes: createMethod$5(true),
    // `Array.prototype.indexOf` method
    // https://tc39.es/ecma262/#sec-array.prototype.indexof
    indexOf: createMethod$5(false)
  };

  var uncurryThis$H = functionUncurryThis;
  var hasOwn$g = hasOwnProperty_1;
  var toIndexedObject$8 = toIndexedObject$b;
  var indexOf$1 = arrayIncludes.indexOf;
  var hiddenKeys$4 = hiddenKeys$6;

  var push$9 = uncurryThis$H([].push);

  var objectKeysInternal = function (object, names) {
    var O = toIndexedObject$8(object);
    var i = 0;
    var result = [];
    var key;
    for (key in O) !hasOwn$g(hiddenKeys$4, key) && hasOwn$g(O, key) && push$9(result, key);
    // Don't enum bug & hidden keys
    while (names.length > i) if (hasOwn$g(O, key = names[i++])) {
      ~indexOf$1(result, key) || push$9(result, key);
    }
    return result;
  };

  // IE8- don't enum bug keys
  var enumBugKeys$3 = [
    'constructor',
    'hasOwnProperty',
    'isPrototypeOf',
    'propertyIsEnumerable',
    'toLocaleString',
    'toString',
    'valueOf'
  ];

  var internalObjectKeys$1 = objectKeysInternal;
  var enumBugKeys$2 = enumBugKeys$3;

  var hiddenKeys$3 = enumBugKeys$2.concat('length', 'prototype');

  // `Object.getOwnPropertyNames` method
  // https://tc39.es/ecma262/#sec-object.getownpropertynames
  // eslint-disable-next-line es/no-object-getownpropertynames -- safe
  objectGetOwnPropertyNames.f = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
    return internalObjectKeys$1(O, hiddenKeys$3);
  };

  var objectGetOwnPropertySymbols = {};

  // eslint-disable-next-line es/no-object-getownpropertysymbols -- safe
  objectGetOwnPropertySymbols.f = Object.getOwnPropertySymbols;

  var getBuiltIn$7 = getBuiltIn$a;
  var uncurryThis$G = functionUncurryThis;
  var getOwnPropertyNamesModule$2 = objectGetOwnPropertyNames;
  var getOwnPropertySymbolsModule$2 = objectGetOwnPropertySymbols;
  var anObject$o = anObject$q;

  var concat$2 = uncurryThis$G([].concat);

  // all object keys, includes non-enumerable and symbols
  var ownKeys$3 = getBuiltIn$7('Reflect', 'ownKeys') || function ownKeys(it) {
    var keys = getOwnPropertyNamesModule$2.f(anObject$o(it));
    var getOwnPropertySymbols = getOwnPropertySymbolsModule$2.f;
    return getOwnPropertySymbols ? concat$2(keys, getOwnPropertySymbols(it)) : keys;
  };

  var hasOwn$f = hasOwnProperty_1;
  var ownKeys$2 = ownKeys$3;
  var getOwnPropertyDescriptorModule$4 = objectGetOwnPropertyDescriptor;
  var definePropertyModule$8 = objectDefineProperty;

  var copyConstructorProperties$2 = function (target, source, exceptions) {
    var keys = ownKeys$2(source);
    var defineProperty = definePropertyModule$8.f;
    var getOwnPropertyDescriptor = getOwnPropertyDescriptorModule$4.f;
    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];
      if (!hasOwn$f(target, key) && !(exceptions && hasOwn$f(exceptions, key))) {
        defineProperty(target, key, getOwnPropertyDescriptor(source, key));
      }
    }
  };

  var fails$E = fails$J;
  var isCallable$h = isCallable$q;

  var replacement = /#|\.prototype\./;

  var isForced$4 = function (feature, detection) {
    var value = data[normalize(feature)];
    return value == POLYFILL ? true
      : value == NATIVE ? false
      : isCallable$h(detection) ? fails$E(detection)
      : !!detection;
  };

  var normalize = isForced$4.normalize = function (string) {
    return String(string).replace(replacement, '.').toLowerCase();
  };

  var data = isForced$4.data = {};
  var NATIVE = isForced$4.NATIVE = 'N';
  var POLYFILL = isForced$4.POLYFILL = 'P';

  var isForced_1 = isForced$4;

  var global$W = global$1e;
  var getOwnPropertyDescriptor$5 = objectGetOwnPropertyDescriptor.f;
  var createNonEnumerableProperty$7 = createNonEnumerableProperty$a;
  var redefine$d = redefine$e.exports;
  var setGlobal = setGlobal$3;
  var copyConstructorProperties$1 = copyConstructorProperties$2;
  var isForced$3 = isForced_1;

  /*
    options.target      - name of the target object
    options.global      - target is the global object
    options.stat        - export as static methods of target
    options.proto       - export as prototype methods of target
    options.real        - real prototype method for the `pure` version
    options.forced      - export even if the native feature is available
    options.bind        - bind methods to the target, required for the `pure` version
    options.wrap        - wrap constructors to preventing global pollution, required for the `pure` version
    options.unsafe      - use the simple assignment of property instead of delete + defineProperty
    options.sham        - add a flag to not completely full polyfills
    options.enumerable  - export as enumerable property
    options.noTargetGet - prevent calling a getter on target
    options.name        - the .name of the function if it does not match the key
  */
  var _export = function (options, source) {
    var TARGET = options.target;
    var GLOBAL = options.global;
    var STATIC = options.stat;
    var FORCED, target, key, targetProperty, sourceProperty, descriptor;
    if (GLOBAL) {
      target = global$W;
    } else if (STATIC) {
      target = global$W[TARGET] || setGlobal(TARGET, {});
    } else {
      target = (global$W[TARGET] || {}).prototype;
    }
    if (target) for (key in source) {
      sourceProperty = source[key];
      if (options.noTargetGet) {
        descriptor = getOwnPropertyDescriptor$5(target, key);
        targetProperty = descriptor && descriptor.value;
      } else targetProperty = target[key];
      FORCED = isForced$3(GLOBAL ? key : TARGET + (STATIC ? '.' : '#') + key, options.forced);
      // contained in target
      if (!FORCED && targetProperty !== undefined) {
        if (typeof sourceProperty == typeof targetProperty) continue;
        copyConstructorProperties$1(sourceProperty, targetProperty);
      }
      // add a flag to not completely full polyfills
      if (options.sham || (targetProperty && targetProperty.sham)) {
        createNonEnumerableProperty$7(sourceProperty, 'sham', true);
      }
      // extend global
      redefine$d(target, key, sourceProperty, options);
    }
  };

  var wellKnownSymbol$q = wellKnownSymbol$s;

  var TO_STRING_TAG$4 = wellKnownSymbol$q('toStringTag');
  var test$1 = {};

  test$1[TO_STRING_TAG$4] = 'z';

  var toStringTagSupport = String(test$1) === '[object z]';

  var global$V = global$1e;
  var TO_STRING_TAG_SUPPORT$2 = toStringTagSupport;
  var isCallable$g = isCallable$q;
  var classofRaw = classofRaw$1;
  var wellKnownSymbol$p = wellKnownSymbol$s;

  var TO_STRING_TAG$3 = wellKnownSymbol$p('toStringTag');
  var Object$2 = global$V.Object;

  // ES3 wrong here
  var CORRECT_ARGUMENTS = classofRaw(function () { return arguments; }()) == 'Arguments';

  // fallback for IE11 Script Access Denied error
  var tryGet = function (it, key) {
    try {
      return it[key];
    } catch (error) { /* empty */ }
  };

  // getting tag from ES6+ `Object.prototype.toString`
  var classof$d = TO_STRING_TAG_SUPPORT$2 ? classofRaw : function (it) {
    var O, tag, result;
    return it === undefined ? 'Undefined' : it === null ? 'Null'
      // @@toStringTag case
      : typeof (tag = tryGet(O = Object$2(it), TO_STRING_TAG$3)) == 'string' ? tag
      // builtinTag case
      : CORRECT_ARGUMENTS ? classofRaw(O)
      // ES3 arguments fallback
      : (result = classofRaw(O)) == 'Object' && isCallable$g(O.callee) ? 'Arguments' : result;
  };

  var global$U = global$1e;
  var classof$c = classof$d;

  var String$4 = global$U.String;

  var toString$g = function (argument) {
    if (classof$c(argument) === 'Symbol') throw TypeError('Cannot convert a Symbol value to a string');
    return String$4(argument);
  };

  var anObject$n = anObject$q;

  // `RegExp.prototype.flags` getter implementation
  // https://tc39.es/ecma262/#sec-get-regexp.prototype.flags
  var regexpFlags$1 = function () {
    var that = anObject$n(this);
    var result = '';
    if (that.global) result += 'g';
    if (that.ignoreCase) result += 'i';
    if (that.multiline) result += 'm';
    if (that.dotAll) result += 's';
    if (that.unicode) result += 'u';
    if (that.sticky) result += 'y';
    return result;
  };

  var fails$D = fails$J;
  var global$T = global$1e;

  // babel-minify and Closure Compiler transpiles RegExp('a', 'y') -> /a/y and it causes SyntaxError
  var $RegExp$2 = global$T.RegExp;

  var UNSUPPORTED_Y$2 = fails$D(function () {
    var re = $RegExp$2('a', 'y');
    re.lastIndex = 2;
    return re.exec('abcd') != null;
  });

  // UC Browser bug
  // https://github.com/zloirock/core-js/issues/1008
  var MISSED_STICKY = UNSUPPORTED_Y$2 || fails$D(function () {
    return !$RegExp$2('a', 'y').sticky;
  });

  var BROKEN_CARET = UNSUPPORTED_Y$2 || fails$D(function () {
    // https://bugzilla.mozilla.org/show_bug.cgi?id=773687
    var re = $RegExp$2('^r', 'gy');
    re.lastIndex = 2;
    return re.exec('str') != null;
  });

  var regexpStickyHelpers = {
    BROKEN_CARET: BROKEN_CARET,
    MISSED_STICKY: MISSED_STICKY,
    UNSUPPORTED_Y: UNSUPPORTED_Y$2
  };

  var internalObjectKeys = objectKeysInternal;
  var enumBugKeys$1 = enumBugKeys$3;

  // `Object.keys` method
  // https://tc39.es/ecma262/#sec-object.keys
  // eslint-disable-next-line es/no-object-keys -- safe
  var objectKeys$4 = Object.keys || function keys(O) {
    return internalObjectKeys(O, enumBugKeys$1);
  };

  var DESCRIPTORS$e = descriptors;
  var definePropertyModule$7 = objectDefineProperty;
  var anObject$m = anObject$q;
  var toIndexedObject$7 = toIndexedObject$b;
  var objectKeys$3 = objectKeys$4;

  // `Object.defineProperties` method
  // https://tc39.es/ecma262/#sec-object.defineproperties
  // eslint-disable-next-line es/no-object-defineproperties -- safe
  var objectDefineProperties = DESCRIPTORS$e ? Object.defineProperties : function defineProperties(O, Properties) {
    anObject$m(O);
    var props = toIndexedObject$7(Properties);
    var keys = objectKeys$3(Properties);
    var length = keys.length;
    var index = 0;
    var key;
    while (length > index) definePropertyModule$7.f(O, key = keys[index++], props[key]);
    return O;
  };

  var getBuiltIn$6 = getBuiltIn$a;

  var html$2 = getBuiltIn$6('document', 'documentElement');

  /* global ActiveXObject -- old IE, WSH */

  var anObject$l = anObject$q;
  var defineProperties$1 = objectDefineProperties;
  var enumBugKeys = enumBugKeys$3;
  var hiddenKeys$2 = hiddenKeys$6;
  var html$1 = html$2;
  var documentCreateElement$1 = documentCreateElement$2;
  var sharedKey$2 = sharedKey$4;

  var GT = '>';
  var LT = '<';
  var PROTOTYPE$2 = 'prototype';
  var SCRIPT = 'script';
  var IE_PROTO$1 = sharedKey$2('IE_PROTO');

  var EmptyConstructor = function () { /* empty */ };

  var scriptTag = function (content) {
    return LT + SCRIPT + GT + content + LT + '/' + SCRIPT + GT;
  };

  // Create object with fake `null` prototype: use ActiveX Object with cleared prototype
  var NullProtoObjectViaActiveX = function (activeXDocument) {
    activeXDocument.write(scriptTag(''));
    activeXDocument.close();
    var temp = activeXDocument.parentWindow.Object;
    activeXDocument = null; // avoid memory leak
    return temp;
  };

  // Create object with fake `null` prototype: use iframe Object with cleared prototype
  var NullProtoObjectViaIFrame = function () {
    // Thrash, waste and sodomy: IE GC bug
    var iframe = documentCreateElement$1('iframe');
    var JS = 'java' + SCRIPT + ':';
    var iframeDocument;
    iframe.style.display = 'none';
    html$1.appendChild(iframe);
    // https://github.com/zloirock/core-js/issues/475
    iframe.src = String(JS);
    iframeDocument = iframe.contentWindow.document;
    iframeDocument.open();
    iframeDocument.write(scriptTag('document.F=Object'));
    iframeDocument.close();
    return iframeDocument.F;
  };

  // Check for document.domain and active x support
  // No need to use active x approach when document.domain is not set
  // see https://github.com/es-shims/es5-shim/issues/150
  // variation of https://github.com/kitcambridge/es5-shim/commit/4f738ac066346
  // avoid IE GC bug
  var activeXDocument;
  var NullProtoObject = function () {
    try {
      activeXDocument = new ActiveXObject('htmlfile');
    } catch (error) { /* ignore */ }
    NullProtoObject = typeof document != 'undefined'
      ? document.domain && activeXDocument
        ? NullProtoObjectViaActiveX(activeXDocument) // old IE
        : NullProtoObjectViaIFrame()
      : NullProtoObjectViaActiveX(activeXDocument); // WSH
    var length = enumBugKeys.length;
    while (length--) delete NullProtoObject[PROTOTYPE$2][enumBugKeys[length]];
    return NullProtoObject();
  };

  hiddenKeys$2[IE_PROTO$1] = true;

  // `Object.create` method
  // https://tc39.es/ecma262/#sec-object.create
  var objectCreate = Object.create || function create(O, Properties) {
    var result;
    if (O !== null) {
      EmptyConstructor[PROTOTYPE$2] = anObject$l(O);
      result = new EmptyConstructor();
      EmptyConstructor[PROTOTYPE$2] = null;
      // add "__proto__" for Object.getPrototypeOf polyfill
      result[IE_PROTO$1] = O;
    } else result = NullProtoObject();
    return Properties === undefined ? result : defineProperties$1(result, Properties);
  };

  var fails$C = fails$J;
  var global$S = global$1e;

  // babel-minify and Closure Compiler transpiles RegExp('.', 's') -> /./s and it causes SyntaxError
  var $RegExp$1 = global$S.RegExp;

  var regexpUnsupportedDotAll = fails$C(function () {
    var re = $RegExp$1('.', 's');
    return !(re.dotAll && re.exec('\n') && re.flags === 's');
  });

  var fails$B = fails$J;
  var global$R = global$1e;

  // babel-minify and Closure Compiler transpiles RegExp('(?<a>b)', 'g') -> /(?<a>b)/g and it causes SyntaxError
  var $RegExp = global$R.RegExp;

  var regexpUnsupportedNcg = fails$B(function () {
    var re = $RegExp('(?<a>b)', 'g');
    return re.exec('b').groups.a !== 'b' ||
      'b'.replace(re, '$<a>c') !== 'bc';
  });

  /* eslint-disable regexp/no-empty-capturing-group, regexp/no-empty-group, regexp/no-lazy-ends -- testing */
  /* eslint-disable regexp/no-useless-quantifier -- testing */
  var call$m = functionCall;
  var uncurryThis$F = functionUncurryThis;
  var toString$f = toString$g;
  var regexpFlags = regexpFlags$1;
  var stickyHelpers$1 = regexpStickyHelpers;
  var shared$1 = shared$5.exports;
  var create$5 = objectCreate;
  var getInternalState$6 = internalState.get;
  var UNSUPPORTED_DOT_ALL = regexpUnsupportedDotAll;
  var UNSUPPORTED_NCG = regexpUnsupportedNcg;

  var nativeReplace = shared$1('native-string-replace', String.prototype.replace);
  var nativeExec = RegExp.prototype.exec;
  var patchedExec = nativeExec;
  var charAt$7 = uncurryThis$F(''.charAt);
  var indexOf = uncurryThis$F(''.indexOf);
  var replace$8 = uncurryThis$F(''.replace);
  var stringSlice$9 = uncurryThis$F(''.slice);

  var UPDATES_LAST_INDEX_WRONG = (function () {
    var re1 = /a/;
    var re2 = /b*/g;
    call$m(nativeExec, re1, 'a');
    call$m(nativeExec, re2, 'a');
    return re1.lastIndex !== 0 || re2.lastIndex !== 0;
  })();

  var UNSUPPORTED_Y$1 = stickyHelpers$1.BROKEN_CARET;

  // nonparticipating capturing group, copied from es5-shim's String#split patch.
  var NPCG_INCLUDED = /()??/.exec('')[1] !== undefined;

  var PATCH = UPDATES_LAST_INDEX_WRONG || NPCG_INCLUDED || UNSUPPORTED_Y$1 || UNSUPPORTED_DOT_ALL || UNSUPPORTED_NCG;

  if (PATCH) {
    patchedExec = function exec(string) {
      var re = this;
      var state = getInternalState$6(re);
      var str = toString$f(string);
      var raw = state.raw;
      var result, reCopy, lastIndex, match, i, object, group;

      if (raw) {
        raw.lastIndex = re.lastIndex;
        result = call$m(patchedExec, raw, str);
        re.lastIndex = raw.lastIndex;
        return result;
      }

      var groups = state.groups;
      var sticky = UNSUPPORTED_Y$1 && re.sticky;
      var flags = call$m(regexpFlags, re);
      var source = re.source;
      var charsAdded = 0;
      var strCopy = str;

      if (sticky) {
        flags = replace$8(flags, 'y', '');
        if (indexOf(flags, 'g') === -1) {
          flags += 'g';
        }

        strCopy = stringSlice$9(str, re.lastIndex);
        // Support anchored sticky behavior.
        if (re.lastIndex > 0 && (!re.multiline || re.multiline && charAt$7(str, re.lastIndex - 1) !== '\n')) {
          source = '(?: ' + source + ')';
          strCopy = ' ' + strCopy;
          charsAdded++;
        }
        // ^(? + rx + ) is needed, in combination with some str slicing, to
        // simulate the 'y' flag.
        reCopy = new RegExp('^(?:' + source + ')', flags);
      }

      if (NPCG_INCLUDED) {
        reCopy = new RegExp('^' + source + '$(?!\\s)', flags);
      }
      if (UPDATES_LAST_INDEX_WRONG) lastIndex = re.lastIndex;

      match = call$m(nativeExec, sticky ? reCopy : re, strCopy);

      if (sticky) {
        if (match) {
          match.input = stringSlice$9(match.input, charsAdded);
          match[0] = stringSlice$9(match[0], charsAdded);
          match.index = re.lastIndex;
          re.lastIndex += match[0].length;
        } else re.lastIndex = 0;
      } else if (UPDATES_LAST_INDEX_WRONG && match) {
        re.lastIndex = re.global ? match.index + match[0].length : lastIndex;
      }
      if (NPCG_INCLUDED && match && match.length > 1) {
        // Fix browsers whose `exec` methods don't consistently return `undefined`
        // for NPCG, like IE8. NOTE: This doesn' work for /(.?)?/
        call$m(nativeReplace, match[0], reCopy, function () {
          for (i = 1; i < arguments.length - 2; i++) {
            if (arguments[i] === undefined) match[i] = undefined;
          }
        });
      }

      if (match && groups) {
        match.groups = object = create$5(null);
        for (i = 0; i < groups.length; i++) {
          group = groups[i];
          object[group[0]] = match[group[1]];
        }
      }

      return match;
    };
  }

  var regexpExec$3 = patchedExec;

  var $$K = _export;
  var exec$5 = regexpExec$3;

  // `RegExp.prototype.exec` method
  // https://tc39.es/ecma262/#sec-regexp.prototype.exec
  $$K({ target: 'RegExp', proto: true, forced: /./.exec !== exec$5 }, {
    exec: exec$5
  });

  var FunctionPrototype$1 = Function.prototype;
  var apply$8 = FunctionPrototype$1.apply;
  var bind$b = FunctionPrototype$1.bind;
  var call$l = FunctionPrototype$1.call;

  // eslint-disable-next-line es/no-reflect -- safe
  var functionApply = typeof Reflect == 'object' && Reflect.apply || (bind$b ? call$l.bind(apply$8) : function () {
    return call$l.apply(apply$8, arguments);
  });

  // TODO: Remove from `core-js@4` since it's moved to entry points

  var uncurryThis$E = functionUncurryThis;
  var redefine$c = redefine$e.exports;
  var regexpExec$2 = regexpExec$3;
  var fails$A = fails$J;
  var wellKnownSymbol$o = wellKnownSymbol$s;
  var createNonEnumerableProperty$6 = createNonEnumerableProperty$a;

  var SPECIES$6 = wellKnownSymbol$o('species');
  var RegExpPrototype$1 = RegExp.prototype;

  var fixRegexpWellKnownSymbolLogic = function (KEY, exec, FORCED, SHAM) {
    var SYMBOL = wellKnownSymbol$o(KEY);

    var DELEGATES_TO_SYMBOL = !fails$A(function () {
      // String methods call symbol-named RegEp methods
      var O = {};
      O[SYMBOL] = function () { return 7; };
      return ''[KEY](O) != 7;
    });

    var DELEGATES_TO_EXEC = DELEGATES_TO_SYMBOL && !fails$A(function () {
      // Symbol-named RegExp methods call .exec
      var execCalled = false;
      var re = /a/;

      if (KEY === 'split') {
        // We can't use real regex here since it causes deoptimization
        // and serious performance degradation in V8
        // https://github.com/zloirock/core-js/issues/306
        re = {};
        // RegExp[@@split] doesn't call the regex's exec method, but first creates
        // a new one. We need to return the patched regex when creating the new one.
        re.constructor = {};
        re.constructor[SPECIES$6] = function () { return re; };
        re.flags = '';
        re[SYMBOL] = /./[SYMBOL];
      }

      re.exec = function () { execCalled = true; return null; };

      re[SYMBOL]('');
      return !execCalled;
    });

    if (
      !DELEGATES_TO_SYMBOL ||
      !DELEGATES_TO_EXEC ||
      FORCED
    ) {
      var uncurriedNativeRegExpMethod = uncurryThis$E(/./[SYMBOL]);
      var methods = exec(SYMBOL, ''[KEY], function (nativeMethod, regexp, str, arg2, forceStringMethod) {
        var uncurriedNativeMethod = uncurryThis$E(nativeMethod);
        var $exec = regexp.exec;
        if ($exec === regexpExec$2 || $exec === RegExpPrototype$1.exec) {
          if (DELEGATES_TO_SYMBOL && !forceStringMethod) {
            // The native String method already delegates to @@method (this
            // polyfilled function), leasing to infinite recursion.
            // We avoid it by directly calling the native @@method method.
            return { done: true, value: uncurriedNativeRegExpMethod(regexp, str, arg2) };
          }
          return { done: true, value: uncurriedNativeMethod(str, regexp, arg2) };
        }
        return { done: false };
      });

      redefine$c(String.prototype, KEY, methods[0]);
      redefine$c(RegExpPrototype$1, SYMBOL, methods[1]);
    }

    if (SHAM) createNonEnumerableProperty$6(RegExpPrototype$1[SYMBOL], 'sham', true);
  };

  var isObject$m = isObject$s;
  var classof$b = classofRaw$1;
  var wellKnownSymbol$n = wellKnownSymbol$s;

  var MATCH$1 = wellKnownSymbol$n('match');

  // `IsRegExp` abstract operation
  // https://tc39.es/ecma262/#sec-isregexp
  var isRegexp = function (it) {
    var isRegExp;
    return isObject$m(it) && ((isRegExp = it[MATCH$1]) !== undefined ? !!isRegExp : classof$b(it) == 'RegExp');
  };

  var uncurryThis$D = functionUncurryThis;
  var fails$z = fails$J;
  var isCallable$f = isCallable$q;
  var classof$a = classof$d;
  var getBuiltIn$5 = getBuiltIn$a;
  var inspectSource$1 = inspectSource$4;

  var noop$1 = function () { /* empty */ };
  var empty = [];
  var construct = getBuiltIn$5('Reflect', 'construct');
  var constructorRegExp = /^\s*(?:class|function)\b/;
  var exec$4 = uncurryThis$D(constructorRegExp.exec);
  var INCORRECT_TO_STRING = !constructorRegExp.exec(noop$1);

  var isConstructorModern = function isConstructor(argument) {
    if (!isCallable$f(argument)) return false;
    try {
      construct(noop$1, empty, argument);
      return true;
    } catch (error) {
      return false;
    }
  };

  var isConstructorLegacy = function isConstructor(argument) {
    if (!isCallable$f(argument)) return false;
    switch (classof$a(argument)) {
      case 'AsyncFunction':
      case 'GeneratorFunction':
      case 'AsyncGeneratorFunction': return false;
    }
    try {
      // we can't check .prototype since constructors produced by .bind haven't it
      // `Function#toString` throws on some built-it function in some legacy engines
      // (for example, `DOMQuad` and similar in FF41-)
      return INCORRECT_TO_STRING || !!exec$4(constructorRegExp, inspectSource$1(argument));
    } catch (error) {
      return true;
    }
  };

  isConstructorLegacy.sham = true;

  // `IsConstructor` abstract operation
  // https://tc39.es/ecma262/#sec-isconstructor
  var isConstructor$4 = !construct || fails$z(function () {
    var called;
    return isConstructorModern(isConstructorModern.call)
      || !isConstructorModern(Object)
      || !isConstructorModern(function () { called = true; })
      || called;
  }) ? isConstructorLegacy : isConstructorModern;

  var global$Q = global$1e;
  var isConstructor$3 = isConstructor$4;
  var tryToString$3 = tryToString$5;

  var TypeError$g = global$Q.TypeError;

  // `Assert: IsConstructor(argument) is true`
  var aConstructor$2 = function (argument) {
    if (isConstructor$3(argument)) return argument;
    throw TypeError$g(tryToString$3(argument) + ' is not a constructor');
  };

  var anObject$k = anObject$q;
  var aConstructor$1 = aConstructor$2;
  var wellKnownSymbol$m = wellKnownSymbol$s;

  var SPECIES$5 = wellKnownSymbol$m('species');

  // `SpeciesConstructor` abstract operation
  // https://tc39.es/ecma262/#sec-speciesconstructor
  var speciesConstructor$3 = function (O, defaultConstructor) {
    var C = anObject$k(O).constructor;
    var S;
    return C === undefined || (S = anObject$k(C)[SPECIES$5]) == undefined ? defaultConstructor : aConstructor$1(S);
  };

  var uncurryThis$C = functionUncurryThis;
  var toIntegerOrInfinity$9 = toIntegerOrInfinity$c;
  var toString$e = toString$g;
  var requireObjectCoercible$a = requireObjectCoercible$d;

  var charAt$6 = uncurryThis$C(''.charAt);
  var charCodeAt$3 = uncurryThis$C(''.charCodeAt);
  var stringSlice$8 = uncurryThis$C(''.slice);

  var createMethod$4 = function (CONVERT_TO_STRING) {
    return function ($this, pos) {
      var S = toString$e(requireObjectCoercible$a($this));
      var position = toIntegerOrInfinity$9(pos);
      var size = S.length;
      var first, second;
      if (position < 0 || position >= size) return CONVERT_TO_STRING ? '' : undefined;
      first = charCodeAt$3(S, position);
      return first < 0xD800 || first > 0xDBFF || position + 1 === size
        || (second = charCodeAt$3(S, position + 1)) < 0xDC00 || second > 0xDFFF
          ? CONVERT_TO_STRING
            ? charAt$6(S, position)
            : first
          : CONVERT_TO_STRING
            ? stringSlice$8(S, position, position + 2)
            : (first - 0xD800 << 10) + (second - 0xDC00) + 0x10000;
    };
  };

  var stringMultibyte = {
    // `String.prototype.codePointAt` method
    // https://tc39.es/ecma262/#sec-string.prototype.codepointat
    codeAt: createMethod$4(false),
    // `String.prototype.at` method
    // https://github.com/mathiasbynens/String.prototype.at
    charAt: createMethod$4(true)
  };

  var charAt$5 = stringMultibyte.charAt;

  // `AdvanceStringIndex` abstract operation
  // https://tc39.es/ecma262/#sec-advancestringindex
  var advanceStringIndex$3 = function (S, index, unicode) {
    return index + (unicode ? charAt$5(S, index).length : 1);
  };

  var toPropertyKey$3 = toPropertyKey$6;
  var definePropertyModule$6 = objectDefineProperty;
  var createPropertyDescriptor$5 = createPropertyDescriptor$8;

  var createProperty$5 = function (object, key, value) {
    var propertyKey = toPropertyKey$3(key);
    if (propertyKey in object) definePropertyModule$6.f(object, propertyKey, createPropertyDescriptor$5(0, value));
    else object[propertyKey] = value;
  };

  var global$P = global$1e;
  var toAbsoluteIndex$5 = toAbsoluteIndex$7;
  var lengthOfArrayLike$f = lengthOfArrayLike$h;
  var createProperty$4 = createProperty$5;

  var Array$8 = global$P.Array;
  var max$3 = Math.max;

  var arraySliceSimple = function (O, start, end) {
    var length = lengthOfArrayLike$f(O);
    var k = toAbsoluteIndex$5(start, length);
    var fin = toAbsoluteIndex$5(end === undefined ? length : end, length);
    var result = Array$8(max$3(fin - k, 0));
    for (var n = 0; k < fin; k++, n++) createProperty$4(result, n, O[k]);
    result.length = n;
    return result;
  };

  var global$O = global$1e;
  var call$k = functionCall;
  var anObject$j = anObject$q;
  var isCallable$e = isCallable$q;
  var classof$9 = classofRaw$1;
  var regexpExec$1 = regexpExec$3;

  var TypeError$f = global$O.TypeError;

  // `RegExpExec` abstract operation
  // https://tc39.es/ecma262/#sec-regexpexec
  var regexpExecAbstract = function (R, S) {
    var exec = R.exec;
    if (isCallable$e(exec)) {
      var result = call$k(exec, R, S);
      if (result !== null) anObject$j(result);
      return result;
    }
    if (classof$9(R) === 'RegExp') return call$k(regexpExec$1, R, S);
    throw TypeError$f('RegExp#exec called on incompatible receiver');
  };

  var apply$7 = functionApply;
  var call$j = functionCall;
  var uncurryThis$B = functionUncurryThis;
  var fixRegExpWellKnownSymbolLogic$3 = fixRegexpWellKnownSymbolLogic;
  var isRegExp$1 = isRegexp;
  var anObject$i = anObject$q;
  var requireObjectCoercible$9 = requireObjectCoercible$d;
  var speciesConstructor$2 = speciesConstructor$3;
  var advanceStringIndex$2 = advanceStringIndex$3;
  var toLength$8 = toLength$a;
  var toString$d = toString$g;
  var getMethod$5 = getMethod$7;
  var arraySlice$a = arraySliceSimple;
  var callRegExpExec = regexpExecAbstract;
  var regexpExec = regexpExec$3;
  var stickyHelpers = regexpStickyHelpers;
  var fails$y = fails$J;

  var UNSUPPORTED_Y = stickyHelpers.UNSUPPORTED_Y;
  var MAX_UINT32 = 0xFFFFFFFF;
  var min$6 = Math.min;
  var $push = [].push;
  var exec$3 = uncurryThis$B(/./.exec);
  var push$8 = uncurryThis$B($push);
  var stringSlice$7 = uncurryThis$B(''.slice);

  // Chrome 51 has a buggy "split" implementation when RegExp#exec !== nativeExec
  // Weex JS has frozen built-in prototypes, so use try / catch wrapper
  var SPLIT_WORKS_WITH_OVERWRITTEN_EXEC = !fails$y(function () {
    // eslint-disable-next-line regexp/no-empty-group -- required for testing
    var re = /(?:)/;
    var originalExec = re.exec;
    re.exec = function () { return originalExec.apply(this, arguments); };
    var result = 'ab'.split(re);
    return result.length !== 2 || result[0] !== 'a' || result[1] !== 'b';
  });

  // @@split logic
  fixRegExpWellKnownSymbolLogic$3('split', function (SPLIT, nativeSplit, maybeCallNative) {
    var internalSplit;
    if (
      'abbc'.split(/(b)*/)[1] == 'c' ||
      // eslint-disable-next-line regexp/no-empty-group -- required for testing
      'test'.split(/(?:)/, -1).length != 4 ||
      'ab'.split(/(?:ab)*/).length != 2 ||
      '.'.split(/(.?)(.?)/).length != 4 ||
      // eslint-disable-next-line regexp/no-empty-capturing-group, regexp/no-empty-group -- required for testing
      '.'.split(/()()/).length > 1 ||
      ''.split(/.?/).length
    ) {
      // based on es5-shim implementation, need to rework it
      internalSplit = function (separator, limit) {
        var string = toString$d(requireObjectCoercible$9(this));
        var lim = limit === undefined ? MAX_UINT32 : limit >>> 0;
        if (lim === 0) return [];
        if (separator === undefined) return [string];
        // If `separator` is not a regex, use native split
        if (!isRegExp$1(separator)) {
          return call$j(nativeSplit, string, separator, lim);
        }
        var output = [];
        var flags = (separator.ignoreCase ? 'i' : '') +
                    (separator.multiline ? 'm' : '') +
                    (separator.unicode ? 'u' : '') +
                    (separator.sticky ? 'y' : '');
        var lastLastIndex = 0;
        // Make `global` and avoid `lastIndex` issues by working with a copy
        var separatorCopy = new RegExp(separator.source, flags + 'g');
        var match, lastIndex, lastLength;
        while (match = call$j(regexpExec, separatorCopy, string)) {
          lastIndex = separatorCopy.lastIndex;
          if (lastIndex > lastLastIndex) {
            push$8(output, stringSlice$7(string, lastLastIndex, match.index));
            if (match.length > 1 && match.index < string.length) apply$7($push, output, arraySlice$a(match, 1));
            lastLength = match[0].length;
            lastLastIndex = lastIndex;
            if (output.length >= lim) break;
          }
          if (separatorCopy.lastIndex === match.index) separatorCopy.lastIndex++; // Avoid an infinite loop
        }
        if (lastLastIndex === string.length) {
          if (lastLength || !exec$3(separatorCopy, '')) push$8(output, '');
        } else push$8(output, stringSlice$7(string, lastLastIndex));
        return output.length > lim ? arraySlice$a(output, 0, lim) : output;
      };
    // Chakra, V8
    } else if ('0'.split(undefined, 0).length) {
      internalSplit = function (separator, limit) {
        return separator === undefined && limit === 0 ? [] : call$j(nativeSplit, this, separator, limit);
      };
    } else internalSplit = nativeSplit;

    return [
      // `String.prototype.split` method
      // https://tc39.es/ecma262/#sec-string.prototype.split
      function split(separator, limit) {
        var O = requireObjectCoercible$9(this);
        var splitter = separator == undefined ? undefined : getMethod$5(separator, SPLIT);
        return splitter
          ? call$j(splitter, separator, O, limit)
          : call$j(internalSplit, toString$d(O), separator, limit);
      },
      // `RegExp.prototype[@@split]` method
      // https://tc39.es/ecma262/#sec-regexp.prototype-@@split
      //
      // NOTE: This cannot be properly polyfilled in engines that don't support
      // the 'y' flag.
      function (string, limit) {
        var rx = anObject$i(this);
        var S = toString$d(string);
        var res = maybeCallNative(internalSplit, rx, S, limit, internalSplit !== nativeSplit);

        if (res.done) return res.value;

        var C = speciesConstructor$2(rx, RegExp);

        var unicodeMatching = rx.unicode;
        var flags = (rx.ignoreCase ? 'i' : '') +
                    (rx.multiline ? 'm' : '') +
                    (rx.unicode ? 'u' : '') +
                    (UNSUPPORTED_Y ? 'g' : 'y');

        // ^(? + rx + ) is needed, in combination with some S slicing, to
        // simulate the 'y' flag.
        var splitter = new C(UNSUPPORTED_Y ? '^(?:' + rx.source + ')' : rx, flags);
        var lim = limit === undefined ? MAX_UINT32 : limit >>> 0;
        if (lim === 0) return [];
        if (S.length === 0) return callRegExpExec(splitter, S) === null ? [S] : [];
        var p = 0;
        var q = 0;
        var A = [];
        while (q < S.length) {
          splitter.lastIndex = UNSUPPORTED_Y ? 0 : q;
          var z = callRegExpExec(splitter, UNSUPPORTED_Y ? stringSlice$7(S, q) : S);
          var e;
          if (
            z === null ||
            (e = min$6(toLength$8(splitter.lastIndex + (UNSUPPORTED_Y ? q : 0)), S.length)) === p
          ) {
            q = advanceStringIndex$2(S, q, unicodeMatching);
          } else {
            push$8(A, stringSlice$7(S, p, q));
            if (A.length === lim) return A;
            for (var i = 1; i <= z.length - 1; i++) {
              push$8(A, z[i]);
              if (A.length === lim) return A;
            }
            q = p = e;
          }
        }
        push$8(A, stringSlice$7(S, p));
        return A;
      }
    ];
  }, !SPLIT_WORKS_WITH_OVERWRITTEN_EXEC, UNSUPPORTED_Y);

  var TO_STRING_TAG_SUPPORT$1 = toStringTagSupport;
  var classof$8 = classof$d;

  // `Object.prototype.toString` method implementation
  // https://tc39.es/ecma262/#sec-object.prototype.tostring
  var objectToString$1 = TO_STRING_TAG_SUPPORT$1 ? {}.toString : function toString() {
    return '[object ' + classof$8(this) + ']';
  };

  var TO_STRING_TAG_SUPPORT = toStringTagSupport;
  var redefine$b = redefine$e.exports;
  var toString$c = objectToString$1;

  // `Object.prototype.toString` method
  // https://tc39.es/ecma262/#sec-object.prototype.tostring
  if (!TO_STRING_TAG_SUPPORT) {
    redefine$b(Object.prototype, 'toString', toString$c, { unsafe: true });
  }

  // iterable DOM collections
  // flag - `iterable` interface - 'entries', 'keys', 'values', 'forEach' methods
  var domIterables = {
    CSSRuleList: 0,
    CSSStyleDeclaration: 0,
    CSSValueList: 0,
    ClientRectList: 0,
    DOMRectList: 0,
    DOMStringList: 0,
    DOMTokenList: 1,
    DataTransferItemList: 0,
    FileList: 0,
    HTMLAllCollection: 0,
    HTMLCollection: 0,
    HTMLFormElement: 0,
    HTMLSelectElement: 0,
    MediaList: 0,
    MimeTypeArray: 0,
    NamedNodeMap: 0,
    NodeList: 1,
    PaintRequestList: 0,
    Plugin: 0,
    PluginArray: 0,
    SVGLengthList: 0,
    SVGNumberList: 0,
    SVGPathSegList: 0,
    SVGPointList: 0,
    SVGStringList: 0,
    SVGTransformList: 0,
    SourceBufferList: 0,
    StyleSheetList: 0,
    TextTrackCueList: 0,
    TextTrackList: 0,
    TouchList: 0
  };

  // in old WebKit versions, `element.classList` is not an instance of global `DOMTokenList`
  var documentCreateElement = documentCreateElement$2;

  var classList = documentCreateElement('span').classList;
  var DOMTokenListPrototype$2 = classList && classList.constructor && classList.constructor.prototype;

  var domTokenListPrototype = DOMTokenListPrototype$2 === Object.prototype ? undefined : DOMTokenListPrototype$2;

  var uncurryThis$A = functionUncurryThis;
  var aCallable$6 = aCallable$8;

  var bind$a = uncurryThis$A(uncurryThis$A.bind);

  // optional / simple context binding
  var functionBindContext = function (fn, that) {
    aCallable$6(fn);
    return that === undefined ? fn : bind$a ? bind$a(fn, that) : function (/* ...args */) {
      return fn.apply(that, arguments);
    };
  };

  var classof$7 = classofRaw$1;

  // `IsArray` abstract operation
  // https://tc39.es/ecma262/#sec-isarray
  // eslint-disable-next-line es/no-array-isarray -- safe
  var isArray$5 = Array.isArray || function isArray(argument) {
    return classof$7(argument) == 'Array';
  };

  var global$N = global$1e;
  var isArray$4 = isArray$5;
  var isConstructor$2 = isConstructor$4;
  var isObject$l = isObject$s;
  var wellKnownSymbol$l = wellKnownSymbol$s;

  var SPECIES$4 = wellKnownSymbol$l('species');
  var Array$7 = global$N.Array;

  // a part of `ArraySpeciesCreate` abstract operation
  // https://tc39.es/ecma262/#sec-arrayspeciescreate
  var arraySpeciesConstructor$1 = function (originalArray) {
    var C;
    if (isArray$4(originalArray)) {
      C = originalArray.constructor;
      // cross-realm fallback
      if (isConstructor$2(C) && (C === Array$7 || isArray$4(C.prototype))) C = undefined;
      else if (isObject$l(C)) {
        C = C[SPECIES$4];
        if (C === null) C = undefined;
      }
    } return C === undefined ? Array$7 : C;
  };

  var arraySpeciesConstructor = arraySpeciesConstructor$1;

  // `ArraySpeciesCreate` abstract operation
  // https://tc39.es/ecma262/#sec-arrayspeciescreate
  var arraySpeciesCreate$3 = function (originalArray, length) {
    return new (arraySpeciesConstructor(originalArray))(length === 0 ? 0 : length);
  };

  var bind$9 = functionBindContext;
  var uncurryThis$z = functionUncurryThis;
  var IndexedObject$3 = indexedObject;
  var toObject$e = toObject$g;
  var lengthOfArrayLike$e = lengthOfArrayLike$h;
  var arraySpeciesCreate$2 = arraySpeciesCreate$3;

  var push$7 = uncurryThis$z([].push);

  // `Array.prototype.{ forEach, map, filter, some, every, find, findIndex, filterReject }` methods implementation
  var createMethod$3 = function (TYPE) {
    var IS_MAP = TYPE == 1;
    var IS_FILTER = TYPE == 2;
    var IS_SOME = TYPE == 3;
    var IS_EVERY = TYPE == 4;
    var IS_FIND_INDEX = TYPE == 6;
    var IS_FILTER_REJECT = TYPE == 7;
    var NO_HOLES = TYPE == 5 || IS_FIND_INDEX;
    return function ($this, callbackfn, that, specificCreate) {
      var O = toObject$e($this);
      var self = IndexedObject$3(O);
      var boundFunction = bind$9(callbackfn, that);
      var length = lengthOfArrayLike$e(self);
      var index = 0;
      var create = specificCreate || arraySpeciesCreate$2;
      var target = IS_MAP ? create($this, length) : IS_FILTER || IS_FILTER_REJECT ? create($this, 0) : undefined;
      var value, result;
      for (;length > index; index++) if (NO_HOLES || index in self) {
        value = self[index];
        result = boundFunction(value, index, O);
        if (TYPE) {
          if (IS_MAP) target[index] = result; // map
          else if (result) switch (TYPE) {
            case 3: return true;              // some
            case 5: return value;             // find
            case 6: return index;             // findIndex
            case 2: push$7(target, value);      // filter
          } else switch (TYPE) {
            case 4: return false;             // every
            case 7: push$7(target, value);      // filterReject
          }
        }
      }
      return IS_FIND_INDEX ? -1 : IS_SOME || IS_EVERY ? IS_EVERY : target;
    };
  };

  var arrayIteration = {
    // `Array.prototype.forEach` method
    // https://tc39.es/ecma262/#sec-array.prototype.foreach
    forEach: createMethod$3(0),
    // `Array.prototype.map` method
    // https://tc39.es/ecma262/#sec-array.prototype.map
    map: createMethod$3(1),
    // `Array.prototype.filter` method
    // https://tc39.es/ecma262/#sec-array.prototype.filter
    filter: createMethod$3(2),
    // `Array.prototype.some` method
    // https://tc39.es/ecma262/#sec-array.prototype.some
    some: createMethod$3(3),
    // `Array.prototype.every` method
    // https://tc39.es/ecma262/#sec-array.prototype.every
    every: createMethod$3(4),
    // `Array.prototype.find` method
    // https://tc39.es/ecma262/#sec-array.prototype.find
    find: createMethod$3(5),
    // `Array.prototype.findIndex` method
    // https://tc39.es/ecma262/#sec-array.prototype.findIndex
    findIndex: createMethod$3(6),
    // `Array.prototype.filterReject` method
    // https://github.com/tc39/proposal-array-filtering
    filterReject: createMethod$3(7)
  };

  var fails$x = fails$J;

  var arrayMethodIsStrict$4 = function (METHOD_NAME, argument) {
    var method = [][METHOD_NAME];
    return !!method && fails$x(function () {
      // eslint-disable-next-line no-useless-call,no-throw-literal -- required for testing
      method.call(null, argument || function () { throw 1; }, 1);
    });
  };

  var $forEach$2 = arrayIteration.forEach;
  var arrayMethodIsStrict$3 = arrayMethodIsStrict$4;

  var STRICT_METHOD$3 = arrayMethodIsStrict$3('forEach');

  // `Array.prototype.forEach` method implementation
  // https://tc39.es/ecma262/#sec-array.prototype.foreach
  var arrayForEach = !STRICT_METHOD$3 ? function forEach(callbackfn /* , thisArg */) {
    return $forEach$2(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
  // eslint-disable-next-line es/no-array-prototype-foreach -- safe
  } : [].forEach;

  var global$M = global$1e;
  var DOMIterables$1 = domIterables;
  var DOMTokenListPrototype$1 = domTokenListPrototype;
  var forEach$1 = arrayForEach;
  var createNonEnumerableProperty$5 = createNonEnumerableProperty$a;

  var handlePrototype$1 = function (CollectionPrototype) {
    // some Chrome versions have non-configurable methods on DOMTokenList
    if (CollectionPrototype && CollectionPrototype.forEach !== forEach$1) try {
      createNonEnumerableProperty$5(CollectionPrototype, 'forEach', forEach$1);
    } catch (error) {
      CollectionPrototype.forEach = forEach$1;
    }
  };

  for (var COLLECTION_NAME$1 in DOMIterables$1) {
    if (DOMIterables$1[COLLECTION_NAME$1]) {
      handlePrototype$1(global$M[COLLECTION_NAME$1] && global$M[COLLECTION_NAME$1].prototype);
    }
  }

  handlePrototype$1(DOMTokenListPrototype$1);

  var uncurryThis$y = functionUncurryThis;
  var toObject$d = toObject$g;

  var floor$7 = Math.floor;
  var charAt$4 = uncurryThis$y(''.charAt);
  var replace$7 = uncurryThis$y(''.replace);
  var stringSlice$6 = uncurryThis$y(''.slice);
  var SUBSTITUTION_SYMBOLS = /\$([$&'`]|\d{1,2}|<[^>]*>)/g;
  var SUBSTITUTION_SYMBOLS_NO_NAMED = /\$([$&'`]|\d{1,2})/g;

  // `GetSubstitution` abstract operation
  // https://tc39.es/ecma262/#sec-getsubstitution
  var getSubstitution$1 = function (matched, str, position, captures, namedCaptures, replacement) {
    var tailPos = position + matched.length;
    var m = captures.length;
    var symbols = SUBSTITUTION_SYMBOLS_NO_NAMED;
    if (namedCaptures !== undefined) {
      namedCaptures = toObject$d(namedCaptures);
      symbols = SUBSTITUTION_SYMBOLS;
    }
    return replace$7(replacement, symbols, function (match, ch) {
      var capture;
      switch (charAt$4(ch, 0)) {
        case '$': return '$';
        case '&': return matched;
        case '`': return stringSlice$6(str, 0, position);
        case "'": return stringSlice$6(str, tailPos);
        case '<':
          capture = namedCaptures[stringSlice$6(ch, 1, -1)];
          break;
        default: // \d\d?
          var n = +ch;
          if (n === 0) return match;
          if (n > m) {
            var f = floor$7(n / 10);
            if (f === 0) return match;
            if (f <= m) return captures[f - 1] === undefined ? charAt$4(ch, 1) : captures[f - 1] + charAt$4(ch, 1);
            return match;
          }
          capture = captures[n - 1];
      }
      return capture === undefined ? '' : capture;
    });
  };

  var apply$6 = functionApply;
  var call$i = functionCall;
  var uncurryThis$x = functionUncurryThis;
  var fixRegExpWellKnownSymbolLogic$2 = fixRegexpWellKnownSymbolLogic;
  var fails$w = fails$J;
  var anObject$h = anObject$q;
  var isCallable$d = isCallable$q;
  var toIntegerOrInfinity$8 = toIntegerOrInfinity$c;
  var toLength$7 = toLength$a;
  var toString$b = toString$g;
  var requireObjectCoercible$8 = requireObjectCoercible$d;
  var advanceStringIndex$1 = advanceStringIndex$3;
  var getMethod$4 = getMethod$7;
  var getSubstitution = getSubstitution$1;
  var regExpExec$3 = regexpExecAbstract;
  var wellKnownSymbol$k = wellKnownSymbol$s;

  var REPLACE = wellKnownSymbol$k('replace');
  var max$2 = Math.max;
  var min$5 = Math.min;
  var concat$1 = uncurryThis$x([].concat);
  var push$6 = uncurryThis$x([].push);
  var stringIndexOf$1 = uncurryThis$x(''.indexOf);
  var stringSlice$5 = uncurryThis$x(''.slice);

  var maybeToString = function (it) {
    return it === undefined ? it : String(it);
  };

  // IE <= 11 replaces $0 with the whole match, as if it was $&
  // https://stackoverflow.com/questions/6024666/getting-ie-to-replace-a-regex-with-the-literal-string-0
  var REPLACE_KEEPS_$0 = (function () {
    // eslint-disable-next-line regexp/prefer-escape-replacement-dollar-char -- required for testing
    return 'a'.replace(/./, '$0') === '$0';
  })();

  // Safari <= 13.0.3(?) substitutes nth capture where n>m with an empty string
  var REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE = (function () {
    if (/./[REPLACE]) {
      return /./[REPLACE]('a', '$0') === '';
    }
    return false;
  })();

  var REPLACE_SUPPORTS_NAMED_GROUPS = !fails$w(function () {
    var re = /./;
    re.exec = function () {
      var result = [];
      result.groups = { a: '7' };
      return result;
    };
    // eslint-disable-next-line regexp/no-useless-dollar-replacements -- false positive
    return ''.replace(re, '$<a>') !== '7';
  });

  // @@replace logic
  fixRegExpWellKnownSymbolLogic$2('replace', function (_, nativeReplace, maybeCallNative) {
    var UNSAFE_SUBSTITUTE = REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE ? '$' : '$0';

    return [
      // `String.prototype.replace` method
      // https://tc39.es/ecma262/#sec-string.prototype.replace
      function replace(searchValue, replaceValue) {
        var O = requireObjectCoercible$8(this);
        var replacer = searchValue == undefined ? undefined : getMethod$4(searchValue, REPLACE);
        return replacer
          ? call$i(replacer, searchValue, O, replaceValue)
          : call$i(nativeReplace, toString$b(O), searchValue, replaceValue);
      },
      // `RegExp.prototype[@@replace]` method
      // https://tc39.es/ecma262/#sec-regexp.prototype-@@replace
      function (string, replaceValue) {
        var rx = anObject$h(this);
        var S = toString$b(string);

        if (
          typeof replaceValue == 'string' &&
          stringIndexOf$1(replaceValue, UNSAFE_SUBSTITUTE) === -1 &&
          stringIndexOf$1(replaceValue, '$<') === -1
        ) {
          var res = maybeCallNative(nativeReplace, rx, S, replaceValue);
          if (res.done) return res.value;
        }

        var functionalReplace = isCallable$d(replaceValue);
        if (!functionalReplace) replaceValue = toString$b(replaceValue);

        var global = rx.global;
        if (global) {
          var fullUnicode = rx.unicode;
          rx.lastIndex = 0;
        }
        var results = [];
        while (true) {
          var result = regExpExec$3(rx, S);
          if (result === null) break;

          push$6(results, result);
          if (!global) break;

          var matchStr = toString$b(result[0]);
          if (matchStr === '') rx.lastIndex = advanceStringIndex$1(S, toLength$7(rx.lastIndex), fullUnicode);
        }

        var accumulatedResult = '';
        var nextSourcePosition = 0;
        for (var i = 0; i < results.length; i++) {
          result = results[i];

          var matched = toString$b(result[0]);
          var position = max$2(min$5(toIntegerOrInfinity$8(result.index), S.length), 0);
          var captures = [];
          // NOTE: This is equivalent to
          //   captures = result.slice(1).map(maybeToString)
          // but for some reason `nativeSlice.call(result, 1, result.length)` (called in
          // the slice polyfill when slicing native arrays) "doesn't work" in safari 9 and
          // causes a crash (https://pastebin.com/N21QzeQA) when trying to debug it.
          for (var j = 1; j < result.length; j++) push$6(captures, maybeToString(result[j]));
          var namedCaptures = result.groups;
          if (functionalReplace) {
            var replacerArgs = concat$1([matched], captures, position, S);
            if (namedCaptures !== undefined) push$6(replacerArgs, namedCaptures);
            var replacement = toString$b(apply$6(replaceValue, undefined, replacerArgs));
          } else {
            replacement = getSubstitution(matched, S, position, captures, namedCaptures, replaceValue);
          }
          if (position >= nextSourcePosition) {
            accumulatedResult += stringSlice$5(S, nextSourcePosition, position) + replacement;
            nextSourcePosition = position + matched.length;
          }
        }
        return accumulatedResult + stringSlice$5(S, nextSourcePosition);
      }
    ];
  }, !REPLACE_SUPPORTS_NAMED_GROUPS || !REPLACE_KEEPS_$0 || REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE);

  // a string of all valid unicode whitespaces
  var whitespaces$2 = '\u0009\u000A\u000B\u000C\u000D\u0020\u00A0\u1680\u2000\u2001\u2002' +
    '\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200A\u202F\u205F\u3000\u2028\u2029\uFEFF';

  var uncurryThis$w = functionUncurryThis;
  var requireObjectCoercible$7 = requireObjectCoercible$d;
  var toString$a = toString$g;
  var whitespaces$1 = whitespaces$2;

  var replace$6 = uncurryThis$w(''.replace);
  var whitespace = '[' + whitespaces$1 + ']';
  var ltrim = RegExp('^' + whitespace + whitespace + '*');
  var rtrim = RegExp(whitespace + whitespace + '*$');

  // `String.prototype.{ trim, trimStart, trimEnd, trimLeft, trimRight }` methods implementation
  var createMethod$2 = function (TYPE) {
    return function ($this) {
      var string = toString$a(requireObjectCoercible$7($this));
      if (TYPE & 1) string = replace$6(string, ltrim, '');
      if (TYPE & 2) string = replace$6(string, rtrim, '');
      return string;
    };
  };

  var stringTrim = {
    // `String.prototype.{ trimLeft, trimStart }` methods
    // https://tc39.es/ecma262/#sec-string.prototype.trimstart
    start: createMethod$2(1),
    // `String.prototype.{ trimRight, trimEnd }` methods
    // https://tc39.es/ecma262/#sec-string.prototype.trimend
    end: createMethod$2(2),
    // `String.prototype.trim` method
    // https://tc39.es/ecma262/#sec-string.prototype.trim
    trim: createMethod$2(3)
  };

  var PROPER_FUNCTION_NAME$3 = functionName.PROPER;
  var fails$v = fails$J;
  var whitespaces = whitespaces$2;

  var non = '\u200B\u0085\u180E';

  // check that a method works with the correct list
  // of whitespaces and has a correct name
  var stringTrimForced = function (METHOD_NAME) {
    return fails$v(function () {
      return !!whitespaces[METHOD_NAME]()
        || non[METHOD_NAME]() !== non
        || (PROPER_FUNCTION_NAME$3 && whitespaces[METHOD_NAME].name !== METHOD_NAME);
    });
  };

  var $$J = _export;
  var $trim = stringTrim.trim;
  var forcedStringTrimMethod = stringTrimForced;

  // `String.prototype.trim` method
  // https://tc39.es/ecma262/#sec-string.prototype.trim
  $$J({ target: 'String', proto: true, forced: forcedStringTrimMethod('trim') }, {
    trim: function trim() {
      return $trim(this);
    }
  });

  var uncurryThis$v = functionUncurryThis;
  var PROPER_FUNCTION_NAME$2 = functionName.PROPER;
  var redefine$a = redefine$e.exports;
  var anObject$g = anObject$q;
  var isPrototypeOf$7 = objectIsPrototypeOf;
  var $toString$3 = toString$g;
  var fails$u = fails$J;
  var regExpFlags = regexpFlags$1;

  var TO_STRING = 'toString';
  var RegExpPrototype = RegExp.prototype;
  var n$ToString = RegExpPrototype[TO_STRING];
  var getFlags = uncurryThis$v(regExpFlags);

  var NOT_GENERIC = fails$u(function () { return n$ToString.call({ source: 'a', flags: 'b' }) != '/a/b'; });
  // FF44- RegExp#toString has a wrong name
  var INCORRECT_NAME = PROPER_FUNCTION_NAME$2 && n$ToString.name != TO_STRING;

  // `RegExp.prototype.toString` method
  // https://tc39.es/ecma262/#sec-regexp.prototype.tostring
  if (NOT_GENERIC || INCORRECT_NAME) {
    redefine$a(RegExp.prototype, TO_STRING, function toString() {
      var R = anObject$g(this);
      var p = $toString$3(R.source);
      var rf = R.flags;
      var f = $toString$3(rf === undefined && isPrototypeOf$7(RegExpPrototype, R) && !('flags' in RegExpPrototype) ? getFlags(R) : rf);
      return '/' + p + '/' + f;
    }, { unsafe: true });
  }

  var $$I = _export;
  var global$L = global$1e;
  var getBuiltIn$4 = getBuiltIn$a;
  var apply$5 = functionApply;
  var uncurryThis$u = functionUncurryThis;
  var fails$t = fails$J;

  var Array$6 = global$L.Array;
  var $stringify$2 = getBuiltIn$4('JSON', 'stringify');
  var exec$2 = uncurryThis$u(/./.exec);
  var charAt$3 = uncurryThis$u(''.charAt);
  var charCodeAt$2 = uncurryThis$u(''.charCodeAt);
  var replace$5 = uncurryThis$u(''.replace);
  var numberToString$1 = uncurryThis$u(1.0.toString);

  var tester = /[\uD800-\uDFFF]/g;
  var low = /^[\uD800-\uDBFF]$/;
  var hi = /^[\uDC00-\uDFFF]$/;

  var fix = function (match, offset, string) {
    var prev = charAt$3(string, offset - 1);
    var next = charAt$3(string, offset + 1);
    if ((exec$2(low, match) && !exec$2(hi, next)) || (exec$2(hi, match) && !exec$2(low, prev))) {
      return '\\u' + numberToString$1(charCodeAt$2(match, 0), 16);
    } return match;
  };

  var FORCED$8 = fails$t(function () {
    return $stringify$2('\uDF06\uD834') !== '"\\udf06\\ud834"'
      || $stringify$2('\uDEAD') !== '"\\udead"';
  });

  if ($stringify$2) {
    // `JSON.stringify` method
    // https://tc39.es/ecma262/#sec-json.stringify
    // https://github.com/tc39/proposal-well-formed-stringify
    $$I({ target: 'JSON', stat: true, forced: FORCED$8 }, {
      // eslint-disable-next-line no-unused-vars -- required for `.length`
      stringify: function stringify(it, replacer, space) {
        for (var i = 0, l = arguments.length, args = Array$6(l); i < l; i++) args[i] = arguments[i];
        var result = apply$5($stringify$2, null, args);
        return typeof result == 'string' ? replace$5(result, tester, fix) : result;
      }
    });
  }

  var fails$s = fails$J;
  var wellKnownSymbol$j = wellKnownSymbol$s;
  var V8_VERSION$2 = engineV8Version;

  var SPECIES$3 = wellKnownSymbol$j('species');

  var arrayMethodHasSpeciesSupport$5 = function (METHOD_NAME) {
    // We can't use this feature detection in V8 since it causes
    // deoptimization and serious performance degradation
    // https://github.com/zloirock/core-js/issues/677
    return V8_VERSION$2 >= 51 || !fails$s(function () {
      var array = [];
      var constructor = array.constructor = {};
      constructor[SPECIES$3] = function () {
        return { foo: 1 };
      };
      return array[METHOD_NAME](Boolean).foo !== 1;
    });
  };

  var $$H = _export;
  var global$K = global$1e;
  var fails$r = fails$J;
  var isArray$3 = isArray$5;
  var isObject$k = isObject$s;
  var toObject$c = toObject$g;
  var lengthOfArrayLike$d = lengthOfArrayLike$h;
  var createProperty$3 = createProperty$5;
  var arraySpeciesCreate$1 = arraySpeciesCreate$3;
  var arrayMethodHasSpeciesSupport$4 = arrayMethodHasSpeciesSupport$5;
  var wellKnownSymbol$i = wellKnownSymbol$s;
  var V8_VERSION$1 = engineV8Version;

  var IS_CONCAT_SPREADABLE = wellKnownSymbol$i('isConcatSpreadable');
  var MAX_SAFE_INTEGER$1 = 0x1FFFFFFFFFFFFF;
  var MAXIMUM_ALLOWED_INDEX_EXCEEDED = 'Maximum allowed index exceeded';
  var TypeError$e = global$K.TypeError;

  // We can't use this feature detection in V8 since it causes
  // deoptimization and serious performance degradation
  // https://github.com/zloirock/core-js/issues/679
  var IS_CONCAT_SPREADABLE_SUPPORT = V8_VERSION$1 >= 51 || !fails$r(function () {
    var array = [];
    array[IS_CONCAT_SPREADABLE] = false;
    return array.concat()[0] !== array;
  });

  var SPECIES_SUPPORT = arrayMethodHasSpeciesSupport$4('concat');

  var isConcatSpreadable = function (O) {
    if (!isObject$k(O)) return false;
    var spreadable = O[IS_CONCAT_SPREADABLE];
    return spreadable !== undefined ? !!spreadable : isArray$3(O);
  };

  var FORCED$7 = !IS_CONCAT_SPREADABLE_SUPPORT || !SPECIES_SUPPORT;

  // `Array.prototype.concat` method
  // https://tc39.es/ecma262/#sec-array.prototype.concat
  // with adding support of @@isConcatSpreadable and @@species
  $$H({ target: 'Array', proto: true, forced: FORCED$7 }, {
    // eslint-disable-next-line no-unused-vars -- required for `.length`
    concat: function concat(arg) {
      var O = toObject$c(this);
      var A = arraySpeciesCreate$1(O, 0);
      var n = 0;
      var i, k, length, len, E;
      for (i = -1, length = arguments.length; i < length; i++) {
        E = i === -1 ? O : arguments[i];
        if (isConcatSpreadable(E)) {
          len = lengthOfArrayLike$d(E);
          if (n + len > MAX_SAFE_INTEGER$1) throw TypeError$e(MAXIMUM_ALLOWED_INDEX_EXCEEDED);
          for (k = 0; k < len; k++, n++) if (k in E) createProperty$3(A, n, E[k]);
        } else {
          if (n >= MAX_SAFE_INTEGER$1) throw TypeError$e(MAXIMUM_ALLOWED_INDEX_EXCEEDED);
          createProperty$3(A, n++, E);
        }
      }
      A.length = n;
      return A;
    }
  });

  var wellKnownSymbol$h = wellKnownSymbol$s;
  var create$4 = objectCreate;
  var definePropertyModule$5 = objectDefineProperty;

  var UNSCOPABLES = wellKnownSymbol$h('unscopables');
  var ArrayPrototype$1 = Array.prototype;

  // Array.prototype[@@unscopables]
  // https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
  if (ArrayPrototype$1[UNSCOPABLES] == undefined) {
    definePropertyModule$5.f(ArrayPrototype$1, UNSCOPABLES, {
      configurable: true,
      value: create$4(null)
    });
  }

  // add a key to Array.prototype[@@unscopables]
  var addToUnscopables$5 = function (key) {
    ArrayPrototype$1[UNSCOPABLES][key] = true;
  };

  var iterators = {};

  var fails$q = fails$J;

  var correctPrototypeGetter = !fails$q(function () {
    function F() { /* empty */ }
    F.prototype.constructor = null;
    // eslint-disable-next-line es/no-object-getprototypeof -- required for testing
    return Object.getPrototypeOf(new F()) !== F.prototype;
  });

  var global$J = global$1e;
  var hasOwn$e = hasOwnProperty_1;
  var isCallable$c = isCallable$q;
  var toObject$b = toObject$g;
  var sharedKey$1 = sharedKey$4;
  var CORRECT_PROTOTYPE_GETTER$1 = correctPrototypeGetter;

  var IE_PROTO = sharedKey$1('IE_PROTO');
  var Object$1 = global$J.Object;
  var ObjectPrototype$3 = Object$1.prototype;

  // `Object.getPrototypeOf` method
  // https://tc39.es/ecma262/#sec-object.getprototypeof
  var objectGetPrototypeOf$1 = CORRECT_PROTOTYPE_GETTER$1 ? Object$1.getPrototypeOf : function (O) {
    var object = toObject$b(O);
    if (hasOwn$e(object, IE_PROTO)) return object[IE_PROTO];
    var constructor = object.constructor;
    if (isCallable$c(constructor) && object instanceof constructor) {
      return constructor.prototype;
    } return object instanceof Object$1 ? ObjectPrototype$3 : null;
  };

  var fails$p = fails$J;
  var isCallable$b = isCallable$q;
  var getPrototypeOf$5 = objectGetPrototypeOf$1;
  var redefine$9 = redefine$e.exports;
  var wellKnownSymbol$g = wellKnownSymbol$s;

  var ITERATOR$8 = wellKnownSymbol$g('iterator');
  var BUGGY_SAFARI_ITERATORS$1 = false;

  // `%IteratorPrototype%` object
  // https://tc39.es/ecma262/#sec-%iteratorprototype%-object
  var IteratorPrototype$2, PrototypeOfArrayIteratorPrototype, arrayIterator;

  /* eslint-disable es/no-array-prototype-keys -- safe */
  if ([].keys) {
    arrayIterator = [].keys();
    // Safari 8 has buggy iterators w/o `next`
    if (!('next' in arrayIterator)) BUGGY_SAFARI_ITERATORS$1 = true;
    else {
      PrototypeOfArrayIteratorPrototype = getPrototypeOf$5(getPrototypeOf$5(arrayIterator));
      if (PrototypeOfArrayIteratorPrototype !== Object.prototype) IteratorPrototype$2 = PrototypeOfArrayIteratorPrototype;
    }
  }

  var NEW_ITERATOR_PROTOTYPE = IteratorPrototype$2 == undefined || fails$p(function () {
    var test = {};
    // FF44- legacy iterators case
    return IteratorPrototype$2[ITERATOR$8].call(test) !== test;
  });

  if (NEW_ITERATOR_PROTOTYPE) IteratorPrototype$2 = {};

  // `%IteratorPrototype%[@@iterator]()` method
  // https://tc39.es/ecma262/#sec-%iteratorprototype%-@@iterator
  if (!isCallable$b(IteratorPrototype$2[ITERATOR$8])) {
    redefine$9(IteratorPrototype$2, ITERATOR$8, function () {
      return this;
    });
  }

  var iteratorsCore = {
    IteratorPrototype: IteratorPrototype$2,
    BUGGY_SAFARI_ITERATORS: BUGGY_SAFARI_ITERATORS$1
  };

  var defineProperty$a = objectDefineProperty.f;
  var hasOwn$d = hasOwnProperty_1;
  var wellKnownSymbol$f = wellKnownSymbol$s;

  var TO_STRING_TAG$2 = wellKnownSymbol$f('toStringTag');

  var setToStringTag$9 = function (target, TAG, STATIC) {
    if (target && !STATIC) target = target.prototype;
    if (target && !hasOwn$d(target, TO_STRING_TAG$2)) {
      defineProperty$a(target, TO_STRING_TAG$2, { configurable: true, value: TAG });
    }
  };

  var IteratorPrototype$1 = iteratorsCore.IteratorPrototype;
  var create$3 = objectCreate;
  var createPropertyDescriptor$4 = createPropertyDescriptor$8;
  var setToStringTag$8 = setToStringTag$9;
  var Iterators$4 = iterators;

  var returnThis$1 = function () { return this; };

  var createIteratorConstructor$2 = function (IteratorConstructor, NAME, next, ENUMERABLE_NEXT) {
    var TO_STRING_TAG = NAME + ' Iterator';
    IteratorConstructor.prototype = create$3(IteratorPrototype$1, { next: createPropertyDescriptor$4(+!ENUMERABLE_NEXT, next) });
    setToStringTag$8(IteratorConstructor, TO_STRING_TAG, false);
    Iterators$4[TO_STRING_TAG] = returnThis$1;
    return IteratorConstructor;
  };

  var global$I = global$1e;
  var isCallable$a = isCallable$q;

  var String$3 = global$I.String;
  var TypeError$d = global$I.TypeError;

  var aPossiblePrototype$1 = function (argument) {
    if (typeof argument == 'object' || isCallable$a(argument)) return argument;
    throw TypeError$d("Can't set " + String$3(argument) + ' as a prototype');
  };

  /* eslint-disable no-proto -- safe */

  var uncurryThis$t = functionUncurryThis;
  var anObject$f = anObject$q;
  var aPossiblePrototype = aPossiblePrototype$1;

  // `Object.setPrototypeOf` method
  // https://tc39.es/ecma262/#sec-object.setprototypeof
  // Works with __proto__ only. Old v8 can't work with null proto objects.
  // eslint-disable-next-line es/no-object-setprototypeof -- safe
  var objectSetPrototypeOf = Object.setPrototypeOf || ('__proto__' in {} ? function () {
    var CORRECT_SETTER = false;
    var test = {};
    var setter;
    try {
      // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
      setter = uncurryThis$t(Object.getOwnPropertyDescriptor(Object.prototype, '__proto__').set);
      setter(test, []);
      CORRECT_SETTER = test instanceof Array;
    } catch (error) { /* empty */ }
    return function setPrototypeOf(O, proto) {
      anObject$f(O);
      aPossiblePrototype(proto);
      if (CORRECT_SETTER) setter(O, proto);
      else O.__proto__ = proto;
      return O;
    };
  }() : undefined);

  var $$G = _export;
  var call$h = functionCall;
  var FunctionName$1 = functionName;
  var isCallable$9 = isCallable$q;
  var createIteratorConstructor$1 = createIteratorConstructor$2;
  var getPrototypeOf$4 = objectGetPrototypeOf$1;
  var setPrototypeOf$5 = objectSetPrototypeOf;
  var setToStringTag$7 = setToStringTag$9;
  var createNonEnumerableProperty$4 = createNonEnumerableProperty$a;
  var redefine$8 = redefine$e.exports;
  var wellKnownSymbol$e = wellKnownSymbol$s;
  var Iterators$3 = iterators;
  var IteratorsCore = iteratorsCore;

  var PROPER_FUNCTION_NAME$1 = FunctionName$1.PROPER;
  var CONFIGURABLE_FUNCTION_NAME$1 = FunctionName$1.CONFIGURABLE;
  var IteratorPrototype = IteratorsCore.IteratorPrototype;
  var BUGGY_SAFARI_ITERATORS = IteratorsCore.BUGGY_SAFARI_ITERATORS;
  var ITERATOR$7 = wellKnownSymbol$e('iterator');
  var KEYS = 'keys';
  var VALUES = 'values';
  var ENTRIES = 'entries';

  var returnThis = function () { return this; };

  var defineIterator$3 = function (Iterable, NAME, IteratorConstructor, next, DEFAULT, IS_SET, FORCED) {
    createIteratorConstructor$1(IteratorConstructor, NAME, next);

    var getIterationMethod = function (KIND) {
      if (KIND === DEFAULT && defaultIterator) return defaultIterator;
      if (!BUGGY_SAFARI_ITERATORS && KIND in IterablePrototype) return IterablePrototype[KIND];
      switch (KIND) {
        case KEYS: return function keys() { return new IteratorConstructor(this, KIND); };
        case VALUES: return function values() { return new IteratorConstructor(this, KIND); };
        case ENTRIES: return function entries() { return new IteratorConstructor(this, KIND); };
      } return function () { return new IteratorConstructor(this); };
    };

    var TO_STRING_TAG = NAME + ' Iterator';
    var INCORRECT_VALUES_NAME = false;
    var IterablePrototype = Iterable.prototype;
    var nativeIterator = IterablePrototype[ITERATOR$7]
      || IterablePrototype['@@iterator']
      || DEFAULT && IterablePrototype[DEFAULT];
    var defaultIterator = !BUGGY_SAFARI_ITERATORS && nativeIterator || getIterationMethod(DEFAULT);
    var anyNativeIterator = NAME == 'Array' ? IterablePrototype.entries || nativeIterator : nativeIterator;
    var CurrentIteratorPrototype, methods, KEY;

    // fix native
    if (anyNativeIterator) {
      CurrentIteratorPrototype = getPrototypeOf$4(anyNativeIterator.call(new Iterable()));
      if (CurrentIteratorPrototype !== Object.prototype && CurrentIteratorPrototype.next) {
        if (getPrototypeOf$4(CurrentIteratorPrototype) !== IteratorPrototype) {
          if (setPrototypeOf$5) {
            setPrototypeOf$5(CurrentIteratorPrototype, IteratorPrototype);
          } else if (!isCallable$9(CurrentIteratorPrototype[ITERATOR$7])) {
            redefine$8(CurrentIteratorPrototype, ITERATOR$7, returnThis);
          }
        }
        // Set @@toStringTag to native iterators
        setToStringTag$7(CurrentIteratorPrototype, TO_STRING_TAG, true);
      }
    }

    // fix Array.prototype.{ values, @@iterator }.name in V8 / FF
    if (PROPER_FUNCTION_NAME$1 && DEFAULT == VALUES && nativeIterator && nativeIterator.name !== VALUES) {
      if (CONFIGURABLE_FUNCTION_NAME$1) {
        createNonEnumerableProperty$4(IterablePrototype, 'name', VALUES);
      } else {
        INCORRECT_VALUES_NAME = true;
        defaultIterator = function values() { return call$h(nativeIterator, this); };
      }
    }

    // export additional methods
    if (DEFAULT) {
      methods = {
        values: getIterationMethod(VALUES),
        keys: IS_SET ? defaultIterator : getIterationMethod(KEYS),
        entries: getIterationMethod(ENTRIES)
      };
      if (FORCED) for (KEY in methods) {
        if (BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME || !(KEY in IterablePrototype)) {
          redefine$8(IterablePrototype, KEY, methods[KEY]);
        }
      } else $$G({ target: NAME, proto: true, forced: BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME }, methods);
    }

    // define iterator
    if (IterablePrototype[ITERATOR$7] !== defaultIterator) {
      redefine$8(IterablePrototype, ITERATOR$7, defaultIterator, { name: DEFAULT });
    }
    Iterators$3[NAME] = defaultIterator;

    return methods;
  };

  var toIndexedObject$6 = toIndexedObject$b;
  var addToUnscopables$4 = addToUnscopables$5;
  var Iterators$2 = iterators;
  var InternalStateModule$9 = internalState;
  var defineProperty$9 = objectDefineProperty.f;
  var defineIterator$2 = defineIterator$3;
  var DESCRIPTORS$d = descriptors;

  var ARRAY_ITERATOR = 'Array Iterator';
  var setInternalState$9 = InternalStateModule$9.set;
  var getInternalState$5 = InternalStateModule$9.getterFor(ARRAY_ITERATOR);

  // `Array.prototype.entries` method
  // https://tc39.es/ecma262/#sec-array.prototype.entries
  // `Array.prototype.keys` method
  // https://tc39.es/ecma262/#sec-array.prototype.keys
  // `Array.prototype.values` method
  // https://tc39.es/ecma262/#sec-array.prototype.values
  // `Array.prototype[@@iterator]` method
  // https://tc39.es/ecma262/#sec-array.prototype-@@iterator
  // `CreateArrayIterator` internal method
  // https://tc39.es/ecma262/#sec-createarrayiterator
  var es_array_iterator = defineIterator$2(Array, 'Array', function (iterated, kind) {
    setInternalState$9(this, {
      type: ARRAY_ITERATOR,
      target: toIndexedObject$6(iterated), // target
      index: 0,                          // next index
      kind: kind                         // kind
    });
  // `%ArrayIteratorPrototype%.next` method
  // https://tc39.es/ecma262/#sec-%arrayiteratorprototype%.next
  }, function () {
    var state = getInternalState$5(this);
    var target = state.target;
    var kind = state.kind;
    var index = state.index++;
    if (!target || index >= target.length) {
      state.target = undefined;
      return { value: undefined, done: true };
    }
    if (kind == 'keys') return { value: index, done: false };
    if (kind == 'values') return { value: target[index], done: false };
    return { value: [index, target[index]], done: false };
  }, 'values');

  // argumentsList[@@iterator] is %ArrayProto_values%
  // https://tc39.es/ecma262/#sec-createunmappedargumentsobject
  // https://tc39.es/ecma262/#sec-createmappedargumentsobject
  var values = Iterators$2.Arguments = Iterators$2.Array;

  // https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
  addToUnscopables$4('keys');
  addToUnscopables$4('values');
  addToUnscopables$4('entries');

  // V8 ~ Chrome 45- bug
  if (DESCRIPTORS$d && values.name !== 'values') try {
    defineProperty$9(values, 'name', { value: 'values' });
  } catch (error) { /* empty */ }

  var global$H = global$1e;
  var DOMIterables = domIterables;
  var DOMTokenListPrototype = domTokenListPrototype;
  var ArrayIteratorMethods = es_array_iterator;
  var createNonEnumerableProperty$3 = createNonEnumerableProperty$a;
  var wellKnownSymbol$d = wellKnownSymbol$s;

  var ITERATOR$6 = wellKnownSymbol$d('iterator');
  var TO_STRING_TAG$1 = wellKnownSymbol$d('toStringTag');
  var ArrayValues = ArrayIteratorMethods.values;

  var handlePrototype = function (CollectionPrototype, COLLECTION_NAME) {
    if (CollectionPrototype) {
      // some Chrome versions have non-configurable methods on DOMTokenList
      if (CollectionPrototype[ITERATOR$6] !== ArrayValues) try {
        createNonEnumerableProperty$3(CollectionPrototype, ITERATOR$6, ArrayValues);
      } catch (error) {
        CollectionPrototype[ITERATOR$6] = ArrayValues;
      }
      if (!CollectionPrototype[TO_STRING_TAG$1]) {
        createNonEnumerableProperty$3(CollectionPrototype, TO_STRING_TAG$1, COLLECTION_NAME);
      }
      if (DOMIterables[COLLECTION_NAME]) for (var METHOD_NAME in ArrayIteratorMethods) {
        // some Chrome versions have non-configurable methods on DOMTokenList
        if (CollectionPrototype[METHOD_NAME] !== ArrayIteratorMethods[METHOD_NAME]) try {
          createNonEnumerableProperty$3(CollectionPrototype, METHOD_NAME, ArrayIteratorMethods[METHOD_NAME]);
        } catch (error) {
          CollectionPrototype[METHOD_NAME] = ArrayIteratorMethods[METHOD_NAME];
        }
      }
    }
  };

  for (var COLLECTION_NAME in DOMIterables) {
    handlePrototype(global$H[COLLECTION_NAME] && global$H[COLLECTION_NAME].prototype, COLLECTION_NAME);
  }

  handlePrototype(DOMTokenListPrototype, 'DOMTokenList');

  // TODO: Remove from `core-js@4` since it's moved to entry points

  var $$F = _export;
  var global$G = global$1e;
  var call$g = functionCall;
  var uncurryThis$s = functionUncurryThis;
  var isCallable$8 = isCallable$q;
  var isObject$j = isObject$s;

  var DELEGATES_TO_EXEC = function () {
    var execCalled = false;
    var re = /[ac]/;
    re.exec = function () {
      execCalled = true;
      return /./.exec.apply(this, arguments);
    };
    return re.test('abc') === true && execCalled;
  }();

  var Error$1 = global$G.Error;
  var un$Test = uncurryThis$s(/./.test);

  // `RegExp.prototype.test` method
  // https://tc39.es/ecma262/#sec-regexp.prototype.test
  $$F({ target: 'RegExp', proto: true, forced: !DELEGATES_TO_EXEC }, {
    test: function (str) {
      var exec = this.exec;
      if (!isCallable$8(exec)) return un$Test(this, str);
      var result = call$g(exec, this, str);
      if (result !== null && !isObject$j(result)) {
        throw new Error$1('RegExp exec method returned something other than an Object or null');
      }
      return !!result;
    }
  });

  var global$F = global$1e;
  var isRegExp = isRegexp;

  var TypeError$c = global$F.TypeError;

  var notARegexp = function (it) {
    if (isRegExp(it)) {
      throw TypeError$c("The method doesn't accept regular expressions");
    } return it;
  };

  var wellKnownSymbol$c = wellKnownSymbol$s;

  var MATCH = wellKnownSymbol$c('match');

  var correctIsRegexpLogic = function (METHOD_NAME) {
    var regexp = /./;
    try {
      '/./'[METHOD_NAME](regexp);
    } catch (error1) {
      try {
        regexp[MATCH] = false;
        return '/./'[METHOD_NAME](regexp);
      } catch (error2) { /* empty */ }
    } return false;
  };

  var $$E = _export;
  var uncurryThis$r = functionUncurryThis;
  var getOwnPropertyDescriptor$4 = objectGetOwnPropertyDescriptor.f;
  var toLength$6 = toLength$a;
  var toString$9 = toString$g;
  var notARegExp$2 = notARegexp;
  var requireObjectCoercible$6 = requireObjectCoercible$d;
  var correctIsRegExpLogic$2 = correctIsRegexpLogic;

  // eslint-disable-next-line es/no-string-prototype-startswith -- safe
  var un$StartsWith = uncurryThis$r(''.startsWith);
  var stringSlice$4 = uncurryThis$r(''.slice);
  var min$4 = Math.min;

  var CORRECT_IS_REGEXP_LOGIC$1 = correctIsRegExpLogic$2('startsWith');
  // https://github.com/zloirock/core-js/pull/702
  var MDN_POLYFILL_BUG$1 = !CORRECT_IS_REGEXP_LOGIC$1 && !!function () {
    var descriptor = getOwnPropertyDescriptor$4(String.prototype, 'startsWith');
    return descriptor && !descriptor.writable;
  }();

  // `String.prototype.startsWith` method
  // https://tc39.es/ecma262/#sec-string.prototype.startswith
  $$E({ target: 'String', proto: true, forced: !MDN_POLYFILL_BUG$1 && !CORRECT_IS_REGEXP_LOGIC$1 }, {
    startsWith: function startsWith(searchString /* , position = 0 */) {
      var that = toString$9(requireObjectCoercible$6(this));
      notARegExp$2(searchString);
      var index = toLength$6(min$4(arguments.length > 1 ? arguments[1] : undefined, that.length));
      var search = toString$9(searchString);
      return un$StartsWith
        ? un$StartsWith(that, search, index)
        : stringSlice$4(that, index, index + search.length) === search;
    }
  });

  var DESCRIPTORS$c = descriptors;
  var uncurryThis$q = functionUncurryThis;
  var call$f = functionCall;
  var fails$o = fails$J;
  var objectKeys$2 = objectKeys$4;
  var getOwnPropertySymbolsModule$1 = objectGetOwnPropertySymbols;
  var propertyIsEnumerableModule$1 = objectPropertyIsEnumerable;
  var toObject$a = toObject$g;
  var IndexedObject$2 = indexedObject;

  // eslint-disable-next-line es/no-object-assign -- safe
  var $assign = Object.assign;
  // eslint-disable-next-line es/no-object-defineproperty -- required for testing
  var defineProperty$8 = Object.defineProperty;
  var concat = uncurryThis$q([].concat);

  // `Object.assign` method
  // https://tc39.es/ecma262/#sec-object.assign
  var objectAssign = !$assign || fails$o(function () {
    // should have correct order of operations (Edge bug)
    if (DESCRIPTORS$c && $assign({ b: 1 }, $assign(defineProperty$8({}, 'a', {
      enumerable: true,
      get: function () {
        defineProperty$8(this, 'b', {
          value: 3,
          enumerable: false
        });
      }
    }), { b: 2 })).b !== 1) return true;
    // should work with symbols and should have deterministic property order (V8 bug)
    var A = {};
    var B = {};
    // eslint-disable-next-line es/no-symbol -- safe
    var symbol = Symbol();
    var alphabet = 'abcdefghijklmnopqrst';
    A[symbol] = 7;
    alphabet.split('').forEach(function (chr) { B[chr] = chr; });
    return $assign({}, A)[symbol] != 7 || objectKeys$2($assign({}, B)).join('') != alphabet;
  }) ? function assign(target, source) { // eslint-disable-line no-unused-vars -- required for `.length`
    var T = toObject$a(target);
    var argumentsLength = arguments.length;
    var index = 1;
    var getOwnPropertySymbols = getOwnPropertySymbolsModule$1.f;
    var propertyIsEnumerable = propertyIsEnumerableModule$1.f;
    while (argumentsLength > index) {
      var S = IndexedObject$2(arguments[index++]);
      var keys = getOwnPropertySymbols ? concat(objectKeys$2(S), getOwnPropertySymbols(S)) : objectKeys$2(S);
      var length = keys.length;
      var j = 0;
      var key;
      while (length > j) {
        key = keys[j++];
        if (!DESCRIPTORS$c || call$f(propertyIsEnumerable, S, key)) T[key] = S[key];
      }
    } return T;
  } : $assign;

  var $$D = _export;
  var assign$1 = objectAssign;

  // `Object.assign` method
  // https://tc39.es/ecma262/#sec-object.assign
  // eslint-disable-next-line es/no-object-assign -- required for testing
  $$D({ target: 'Object', stat: true, forced: Object.assign !== assign$1 }, {
    assign: assign$1
  });

  var $$C = _export;
  var global$E = global$1e;
  var toAbsoluteIndex$4 = toAbsoluteIndex$7;
  var toIntegerOrInfinity$7 = toIntegerOrInfinity$c;
  var lengthOfArrayLike$c = lengthOfArrayLike$h;
  var toObject$9 = toObject$g;
  var arraySpeciesCreate = arraySpeciesCreate$3;
  var createProperty$2 = createProperty$5;
  var arrayMethodHasSpeciesSupport$3 = arrayMethodHasSpeciesSupport$5;

  var HAS_SPECIES_SUPPORT$3 = arrayMethodHasSpeciesSupport$3('splice');

  var TypeError$b = global$E.TypeError;
  var max$1 = Math.max;
  var min$3 = Math.min;
  var MAX_SAFE_INTEGER = 0x1FFFFFFFFFFFFF;
  var MAXIMUM_ALLOWED_LENGTH_EXCEEDED = 'Maximum allowed length exceeded';

  // `Array.prototype.splice` method
  // https://tc39.es/ecma262/#sec-array.prototype.splice
  // with adding support of @@species
  $$C({ target: 'Array', proto: true, forced: !HAS_SPECIES_SUPPORT$3 }, {
    splice: function splice(start, deleteCount /* , ...items */) {
      var O = toObject$9(this);
      var len = lengthOfArrayLike$c(O);
      var actualStart = toAbsoluteIndex$4(start, len);
      var argumentsLength = arguments.length;
      var insertCount, actualDeleteCount, A, k, from, to;
      if (argumentsLength === 0) {
        insertCount = actualDeleteCount = 0;
      } else if (argumentsLength === 1) {
        insertCount = 0;
        actualDeleteCount = len - actualStart;
      } else {
        insertCount = argumentsLength - 2;
        actualDeleteCount = min$3(max$1(toIntegerOrInfinity$7(deleteCount), 0), len - actualStart);
      }
      if (len + insertCount - actualDeleteCount > MAX_SAFE_INTEGER) {
        throw TypeError$b(MAXIMUM_ALLOWED_LENGTH_EXCEEDED);
      }
      A = arraySpeciesCreate(O, actualDeleteCount);
      for (k = 0; k < actualDeleteCount; k++) {
        from = actualStart + k;
        if (from in O) createProperty$2(A, k, O[from]);
      }
      A.length = actualDeleteCount;
      if (insertCount < actualDeleteCount) {
        for (k = actualStart; k < len - actualDeleteCount; k++) {
          from = k + actualDeleteCount;
          to = k + insertCount;
          if (from in O) O[to] = O[from];
          else delete O[to];
        }
        for (k = len; k > len - actualDeleteCount + insertCount; k--) delete O[k - 1];
      } else if (insertCount > actualDeleteCount) {
        for (k = len - actualDeleteCount; k > actualStart; k--) {
          from = k + actualDeleteCount - 1;
          to = k + insertCount - 1;
          if (from in O) O[to] = O[from];
          else delete O[to];
        }
      }
      for (k = 0; k < insertCount; k++) {
        O[k + actualStart] = arguments[k + 2];
      }
      O.length = len - actualDeleteCount + insertCount;
      return A;
    }
  });

  var uncurryThis$p = functionUncurryThis;

  var arraySlice$9 = uncurryThis$p([].slice);

  var $$B = _export;
  var global$D = global$1e;
  var isArray$2 = isArray$5;
  var isConstructor$1 = isConstructor$4;
  var isObject$i = isObject$s;
  var toAbsoluteIndex$3 = toAbsoluteIndex$7;
  var lengthOfArrayLike$b = lengthOfArrayLike$h;
  var toIndexedObject$5 = toIndexedObject$b;
  var createProperty$1 = createProperty$5;
  var wellKnownSymbol$b = wellKnownSymbol$s;
  var arrayMethodHasSpeciesSupport$2 = arrayMethodHasSpeciesSupport$5;
  var un$Slice = arraySlice$9;

  var HAS_SPECIES_SUPPORT$2 = arrayMethodHasSpeciesSupport$2('slice');

  var SPECIES$2 = wellKnownSymbol$b('species');
  var Array$5 = global$D.Array;
  var max = Math.max;

  // `Array.prototype.slice` method
  // https://tc39.es/ecma262/#sec-array.prototype.slice
  // fallback for not array-like ES3 strings and DOM objects
  $$B({ target: 'Array', proto: true, forced: !HAS_SPECIES_SUPPORT$2 }, {
    slice: function slice(start, end) {
      var O = toIndexedObject$5(this);
      var length = lengthOfArrayLike$b(O);
      var k = toAbsoluteIndex$3(start, length);
      var fin = toAbsoluteIndex$3(end === undefined ? length : end, length);
      // inline `ArraySpeciesCreate` for usage native `Array#slice` where it's possible
      var Constructor, result, n;
      if (isArray$2(O)) {
        Constructor = O.constructor;
        // cross-realm fallback
        if (isConstructor$1(Constructor) && (Constructor === Array$5 || isArray$2(Constructor.prototype))) {
          Constructor = undefined;
        } else if (isObject$i(Constructor)) {
          Constructor = Constructor[SPECIES$2];
          if (Constructor === null) Constructor = undefined;
        }
        if (Constructor === Array$5 || Constructor === undefined) {
          return un$Slice(O, k, fin);
        }
      }
      result = new (Constructor === undefined ? Array$5 : Constructor)(max(fin - k, 0));
      for (n = 0; k < fin; k++, n++) if (k in O) createProperty$1(result, n, O[k]);
      result.length = n;
      return result;
    }
  });

  // `SameValue` abstract operation
  // https://tc39.es/ecma262/#sec-samevalue
  // eslint-disable-next-line es/no-object-is -- safe
  var sameValue$1 = Object.is || function is(x, y) {
    // eslint-disable-next-line no-self-compare -- NaN check
    return x === y ? x !== 0 || 1 / x === 1 / y : x != x && y != y;
  };

  var $$A = _export;
  var is = sameValue$1;

  // `Object.is` method
  // https://tc39.es/ecma262/#sec-object.is
  $$A({ target: 'Object', stat: true }, {
    is: is
  });

  var $$z = _export;
  var global$C = global$1e;

  // `globalThis` object
  // https://tc39.es/ecma262/#sec-globalthis
  $$z({ global: true }, {
    globalThis: global$C
  });

  var internalMetadata = {exports: {}};

  var objectGetOwnPropertyNamesExternal = {};

  /* eslint-disable es/no-object-getownpropertynames -- safe */

  var classof$6 = classofRaw$1;
  var toIndexedObject$4 = toIndexedObject$b;
  var $getOwnPropertyNames$1 = objectGetOwnPropertyNames.f;
  var arraySlice$8 = arraySliceSimple;

  var windowNames = typeof window == 'object' && window && Object.getOwnPropertyNames
    ? Object.getOwnPropertyNames(window) : [];

  var getWindowNames = function (it) {
    try {
      return $getOwnPropertyNames$1(it);
    } catch (error) {
      return arraySlice$8(windowNames);
    }
  };

  // fallback for IE11 buggy Object.getOwnPropertyNames with iframe and window
  objectGetOwnPropertyNamesExternal.f = function getOwnPropertyNames(it) {
    return windowNames && classof$6(it) == 'Window'
      ? getWindowNames(it)
      : $getOwnPropertyNames$1(toIndexedObject$4(it));
  };

  // FF26- bug: ArrayBuffers are non-extensible, but Object.isExtensible does not report it
  var fails$n = fails$J;

  var arrayBufferNonExtensible = fails$n(function () {
    if (typeof ArrayBuffer == 'function') {
      var buffer = new ArrayBuffer(8);
      // eslint-disable-next-line es/no-object-isextensible, es/no-object-defineproperty -- safe
      if (Object.isExtensible(buffer)) Object.defineProperty(buffer, 'a', { value: 8 });
    }
  });

  var fails$m = fails$J;
  var isObject$h = isObject$s;
  var classof$5 = classofRaw$1;
  var ARRAY_BUFFER_NON_EXTENSIBLE = arrayBufferNonExtensible;

  // eslint-disable-next-line es/no-object-isextensible -- safe
  var $isExtensible$1 = Object.isExtensible;
  var FAILS_ON_PRIMITIVES$3 = fails$m(function () { $isExtensible$1(1); });

  // `Object.isExtensible` method
  // https://tc39.es/ecma262/#sec-object.isextensible
  var objectIsExtensible = (FAILS_ON_PRIMITIVES$3 || ARRAY_BUFFER_NON_EXTENSIBLE) ? function isExtensible(it) {
    if (!isObject$h(it)) return false;
    if (ARRAY_BUFFER_NON_EXTENSIBLE && classof$5(it) == 'ArrayBuffer') return false;
    return $isExtensible$1 ? $isExtensible$1(it) : true;
  } : $isExtensible$1;

  var fails$l = fails$J;

  var freezing = !fails$l(function () {
    // eslint-disable-next-line es/no-object-isextensible, es/no-object-preventextensions -- required for testing
    return Object.isExtensible(Object.preventExtensions({}));
  });

  var $$y = _export;
  var uncurryThis$o = functionUncurryThis;
  var hiddenKeys$1 = hiddenKeys$6;
  var isObject$g = isObject$s;
  var hasOwn$c = hasOwnProperty_1;
  var defineProperty$7 = objectDefineProperty.f;
  var getOwnPropertyNamesModule$1 = objectGetOwnPropertyNames;
  var getOwnPropertyNamesExternalModule = objectGetOwnPropertyNamesExternal;
  var isExtensible$1 = objectIsExtensible;
  var uid$4 = uid$7;
  var FREEZING$1 = freezing;

  var REQUIRED = false;
  var METADATA = uid$4('meta');
  var id$1 = 0;

  var setMetadata = function (it) {
    defineProperty$7(it, METADATA, { value: {
      objectID: 'O' + id$1++, // object ID
      weakData: {}          // weak collections IDs
    } });
  };

  var fastKey$1 = function (it, create) {
    // return a primitive with prefix
    if (!isObject$g(it)) return typeof it == 'symbol' ? it : (typeof it == 'string' ? 'S' : 'P') + it;
    if (!hasOwn$c(it, METADATA)) {
      // can't set metadata to uncaught frozen object
      if (!isExtensible$1(it)) return 'F';
      // not necessary to add metadata
      if (!create) return 'E';
      // add missing metadata
      setMetadata(it);
    // return object ID
    } return it[METADATA].objectID;
  };

  var getWeakData$1 = function (it, create) {
    if (!hasOwn$c(it, METADATA)) {
      // can't set metadata to uncaught frozen object
      if (!isExtensible$1(it)) return true;
      // not necessary to add metadata
      if (!create) return false;
      // add missing metadata
      setMetadata(it);
    // return the store of weak collections IDs
    } return it[METADATA].weakData;
  };

  // add metadata on freeze-family methods calling
  var onFreeze$1 = function (it) {
    if (FREEZING$1 && REQUIRED && isExtensible$1(it) && !hasOwn$c(it, METADATA)) setMetadata(it);
    return it;
  };

  var enable = function () {
    meta.enable = function () { /* empty */ };
    REQUIRED = true;
    var getOwnPropertyNames = getOwnPropertyNamesModule$1.f;
    var splice = uncurryThis$o([].splice);
    var test = {};
    test[METADATA] = 1;

    // prevent exposing of metadata key
    if (getOwnPropertyNames(test).length) {
      getOwnPropertyNamesModule$1.f = function (it) {
        var result = getOwnPropertyNames(it);
        for (var i = 0, length = result.length; i < length; i++) {
          if (result[i] === METADATA) {
            splice(result, i, 1);
            break;
          }
        } return result;
      };

      $$y({ target: 'Object', stat: true, forced: true }, {
        getOwnPropertyNames: getOwnPropertyNamesExternalModule.f
      });
    }
  };

  var meta = internalMetadata.exports = {
    enable: enable,
    fastKey: fastKey$1,
    getWeakData: getWeakData$1,
    onFreeze: onFreeze$1
  };

  hiddenKeys$1[METADATA] = true;

  var wellKnownSymbol$a = wellKnownSymbol$s;
  var Iterators$1 = iterators;

  var ITERATOR$5 = wellKnownSymbol$a('iterator');
  var ArrayPrototype = Array.prototype;

  // check on default Array iterator
  var isArrayIteratorMethod$3 = function (it) {
    return it !== undefined && (Iterators$1.Array === it || ArrayPrototype[ITERATOR$5] === it);
  };

  var classof$4 = classof$d;
  var getMethod$3 = getMethod$7;
  var Iterators = iterators;
  var wellKnownSymbol$9 = wellKnownSymbol$s;

  var ITERATOR$4 = wellKnownSymbol$9('iterator');

  var getIteratorMethod$5 = function (it) {
    if (it != undefined) return getMethod$3(it, ITERATOR$4)
      || getMethod$3(it, '@@iterator')
      || Iterators[classof$4(it)];
  };

  var global$B = global$1e;
  var call$e = functionCall;
  var aCallable$5 = aCallable$8;
  var anObject$e = anObject$q;
  var tryToString$2 = tryToString$5;
  var getIteratorMethod$4 = getIteratorMethod$5;

  var TypeError$a = global$B.TypeError;

  var getIterator$4 = function (argument, usingIterator) {
    var iteratorMethod = arguments.length < 2 ? getIteratorMethod$4(argument) : usingIterator;
    if (aCallable$5(iteratorMethod)) return anObject$e(call$e(iteratorMethod, argument));
    throw TypeError$a(tryToString$2(argument) + ' is not iterable');
  };

  var call$d = functionCall;
  var anObject$d = anObject$q;
  var getMethod$2 = getMethod$7;

  var iteratorClose$2 = function (iterator, kind, value) {
    var innerResult, innerError;
    anObject$d(iterator);
    try {
      innerResult = getMethod$2(iterator, 'return');
      if (!innerResult) {
        if (kind === 'throw') throw value;
        return value;
      }
      innerResult = call$d(innerResult, iterator);
    } catch (error) {
      innerError = true;
      innerResult = error;
    }
    if (kind === 'throw') throw value;
    if (innerError) throw innerResult;
    anObject$d(innerResult);
    return value;
  };

  var global$A = global$1e;
  var bind$8 = functionBindContext;
  var call$c = functionCall;
  var anObject$c = anObject$q;
  var tryToString$1 = tryToString$5;
  var isArrayIteratorMethod$2 = isArrayIteratorMethod$3;
  var lengthOfArrayLike$a = lengthOfArrayLike$h;
  var isPrototypeOf$6 = objectIsPrototypeOf;
  var getIterator$3 = getIterator$4;
  var getIteratorMethod$3 = getIteratorMethod$5;
  var iteratorClose$1 = iteratorClose$2;

  var TypeError$9 = global$A.TypeError;

  var Result = function (stopped, result) {
    this.stopped = stopped;
    this.result = result;
  };

  var ResultPrototype = Result.prototype;

  var iterate$4 = function (iterable, unboundFunction, options) {
    var that = options && options.that;
    var AS_ENTRIES = !!(options && options.AS_ENTRIES);
    var IS_ITERATOR = !!(options && options.IS_ITERATOR);
    var INTERRUPTED = !!(options && options.INTERRUPTED);
    var fn = bind$8(unboundFunction, that);
    var iterator, iterFn, index, length, result, next, step;

    var stop = function (condition) {
      if (iterator) iteratorClose$1(iterator, 'normal', condition);
      return new Result(true, condition);
    };

    var callFn = function (value) {
      if (AS_ENTRIES) {
        anObject$c(value);
        return INTERRUPTED ? fn(value[0], value[1], stop) : fn(value[0], value[1]);
      } return INTERRUPTED ? fn(value, stop) : fn(value);
    };

    if (IS_ITERATOR) {
      iterator = iterable;
    } else {
      iterFn = getIteratorMethod$3(iterable);
      if (!iterFn) throw TypeError$9(tryToString$1(iterable) + ' is not iterable');
      // optimisation for array iterators
      if (isArrayIteratorMethod$2(iterFn)) {
        for (index = 0, length = lengthOfArrayLike$a(iterable); length > index; index++) {
          result = callFn(iterable[index]);
          if (result && isPrototypeOf$6(ResultPrototype, result)) return result;
        } return new Result(false);
      }
      iterator = getIterator$3(iterable, iterFn);
    }

    next = iterator.next;
    while (!(step = call$c(next, iterator)).done) {
      try {
        result = callFn(step.value);
      } catch (error) {
        iteratorClose$1(iterator, 'throw', error);
      }
      if (typeof result == 'object' && result && isPrototypeOf$6(ResultPrototype, result)) return result;
    } return new Result(false);
  };

  var global$z = global$1e;
  var isPrototypeOf$5 = objectIsPrototypeOf;

  var TypeError$8 = global$z.TypeError;

  var anInstance$8 = function (it, Prototype) {
    if (isPrototypeOf$5(Prototype, it)) return it;
    throw TypeError$8('Incorrect invocation');
  };

  var wellKnownSymbol$8 = wellKnownSymbol$s;

  var ITERATOR$3 = wellKnownSymbol$8('iterator');
  var SAFE_CLOSING = false;

  try {
    var called = 0;
    var iteratorWithReturn = {
      next: function () {
        return { done: !!called++ };
      },
      'return': function () {
        SAFE_CLOSING = true;
      }
    };
    iteratorWithReturn[ITERATOR$3] = function () {
      return this;
    };
    // eslint-disable-next-line es/no-array-from, no-throw-literal -- required for testing
    Array.from(iteratorWithReturn, function () { throw 2; });
  } catch (error) { /* empty */ }

  var checkCorrectnessOfIteration$4 = function (exec, SKIP_CLOSING) {
    if (!SKIP_CLOSING && !SAFE_CLOSING) return false;
    var ITERATION_SUPPORT = false;
    try {
      var object = {};
      object[ITERATOR$3] = function () {
        return {
          next: function () {
            return { done: ITERATION_SUPPORT = true };
          }
        };
      };
      exec(object);
    } catch (error) { /* empty */ }
    return ITERATION_SUPPORT;
  };

  var isCallable$7 = isCallable$q;
  var isObject$f = isObject$s;
  var setPrototypeOf$4 = objectSetPrototypeOf;

  // makes subclassing work correct for wrapped built-ins
  var inheritIfRequired$3 = function ($this, dummy, Wrapper) {
    var NewTarget, NewTargetPrototype;
    if (
      // it can work only with native `setPrototypeOf`
      setPrototypeOf$4 &&
      // we haven't completely correct pre-ES6 way for getting `new.target`, so use this
      isCallable$7(NewTarget = dummy.constructor) &&
      NewTarget !== Wrapper &&
      isObject$f(NewTargetPrototype = NewTarget.prototype) &&
      NewTargetPrototype !== Wrapper.prototype
    ) setPrototypeOf$4($this, NewTargetPrototype);
    return $this;
  };

  var $$x = _export;
  var global$y = global$1e;
  var uncurryThis$n = functionUncurryThis;
  var isForced$2 = isForced_1;
  var redefine$7 = redefine$e.exports;
  var InternalMetadataModule$1 = internalMetadata.exports;
  var iterate$3 = iterate$4;
  var anInstance$7 = anInstance$8;
  var isCallable$6 = isCallable$q;
  var isObject$e = isObject$s;
  var fails$k = fails$J;
  var checkCorrectnessOfIteration$3 = checkCorrectnessOfIteration$4;
  var setToStringTag$6 = setToStringTag$9;
  var inheritIfRequired$2 = inheritIfRequired$3;

  var collection$3 = function (CONSTRUCTOR_NAME, wrapper, common) {
    var IS_MAP = CONSTRUCTOR_NAME.indexOf('Map') !== -1;
    var IS_WEAK = CONSTRUCTOR_NAME.indexOf('Weak') !== -1;
    var ADDER = IS_MAP ? 'set' : 'add';
    var NativeConstructor = global$y[CONSTRUCTOR_NAME];
    var NativePrototype = NativeConstructor && NativeConstructor.prototype;
    var Constructor = NativeConstructor;
    var exported = {};

    var fixMethod = function (KEY) {
      var uncurriedNativeMethod = uncurryThis$n(NativePrototype[KEY]);
      redefine$7(NativePrototype, KEY,
        KEY == 'add' ? function add(value) {
          uncurriedNativeMethod(this, value === 0 ? 0 : value);
          return this;
        } : KEY == 'delete' ? function (key) {
          return IS_WEAK && !isObject$e(key) ? false : uncurriedNativeMethod(this, key === 0 ? 0 : key);
        } : KEY == 'get' ? function get(key) {
          return IS_WEAK && !isObject$e(key) ? undefined : uncurriedNativeMethod(this, key === 0 ? 0 : key);
        } : KEY == 'has' ? function has(key) {
          return IS_WEAK && !isObject$e(key) ? false : uncurriedNativeMethod(this, key === 0 ? 0 : key);
        } : function set(key, value) {
          uncurriedNativeMethod(this, key === 0 ? 0 : key, value);
          return this;
        }
      );
    };

    var REPLACE = isForced$2(
      CONSTRUCTOR_NAME,
      !isCallable$6(NativeConstructor) || !(IS_WEAK || NativePrototype.forEach && !fails$k(function () {
        new NativeConstructor().entries().next();
      }))
    );

    if (REPLACE) {
      // create collection constructor
      Constructor = common.getConstructor(wrapper, CONSTRUCTOR_NAME, IS_MAP, ADDER);
      InternalMetadataModule$1.enable();
    } else if (isForced$2(CONSTRUCTOR_NAME, true)) {
      var instance = new Constructor();
      // early implementations not supports chaining
      var HASNT_CHAINING = instance[ADDER](IS_WEAK ? {} : -0, 1) != instance;
      // V8 ~ Chromium 40- weak-collections throws on primitives, but should return false
      var THROWS_ON_PRIMITIVES = fails$k(function () { instance.has(1); });
      // most early implementations doesn't supports iterables, most modern - not close it correctly
      // eslint-disable-next-line no-new -- required for testing
      var ACCEPT_ITERABLES = checkCorrectnessOfIteration$3(function (iterable) { new NativeConstructor(iterable); });
      // for early implementations -0 and +0 not the same
      var BUGGY_ZERO = !IS_WEAK && fails$k(function () {
        // V8 ~ Chromium 42- fails only with 5+ elements
        var $instance = new NativeConstructor();
        var index = 5;
        while (index--) $instance[ADDER](index, index);
        return !$instance.has(-0);
      });

      if (!ACCEPT_ITERABLES) {
        Constructor = wrapper(function (dummy, iterable) {
          anInstance$7(dummy, NativePrototype);
          var that = inheritIfRequired$2(new NativeConstructor(), dummy, Constructor);
          if (iterable != undefined) iterate$3(iterable, that[ADDER], { that: that, AS_ENTRIES: IS_MAP });
          return that;
        });
        Constructor.prototype = NativePrototype;
        NativePrototype.constructor = Constructor;
      }

      if (THROWS_ON_PRIMITIVES || BUGGY_ZERO) {
        fixMethod('delete');
        fixMethod('has');
        IS_MAP && fixMethod('get');
      }

      if (BUGGY_ZERO || HASNT_CHAINING) fixMethod(ADDER);

      // weak collections should not contains .clear method
      if (IS_WEAK && NativePrototype.clear) delete NativePrototype.clear;
    }

    exported[CONSTRUCTOR_NAME] = Constructor;
    $$x({ global: true, forced: Constructor != NativeConstructor }, exported);

    setToStringTag$6(Constructor, CONSTRUCTOR_NAME);

    if (!IS_WEAK) common.setStrong(Constructor, CONSTRUCTOR_NAME, IS_MAP);

    return Constructor;
  };

  var redefine$6 = redefine$e.exports;

  var redefineAll$6 = function (target, src, options) {
    for (var key in src) redefine$6(target, key, src[key], options);
    return target;
  };

  var getBuiltIn$3 = getBuiltIn$a;
  var definePropertyModule$4 = objectDefineProperty;
  var wellKnownSymbol$7 = wellKnownSymbol$s;
  var DESCRIPTORS$b = descriptors;

  var SPECIES$1 = wellKnownSymbol$7('species');

  var setSpecies$3 = function (CONSTRUCTOR_NAME) {
    var Constructor = getBuiltIn$3(CONSTRUCTOR_NAME);
    var defineProperty = definePropertyModule$4.f;

    if (DESCRIPTORS$b && Constructor && !Constructor[SPECIES$1]) {
      defineProperty(Constructor, SPECIES$1, {
        configurable: true,
        get: function () { return this; }
      });
    }
  };

  var defineProperty$6 = objectDefineProperty.f;
  var create$2 = objectCreate;
  var redefineAll$5 = redefineAll$6;
  var bind$7 = functionBindContext;
  var anInstance$6 = anInstance$8;
  var iterate$2 = iterate$4;
  var defineIterator$1 = defineIterator$3;
  var setSpecies$2 = setSpecies$3;
  var DESCRIPTORS$a = descriptors;
  var fastKey = internalMetadata.exports.fastKey;
  var InternalStateModule$8 = internalState;

  var setInternalState$8 = InternalStateModule$8.set;
  var internalStateGetterFor$1 = InternalStateModule$8.getterFor;

  var collectionStrong$2 = {
    getConstructor: function (wrapper, CONSTRUCTOR_NAME, IS_MAP, ADDER) {
      var Constructor = wrapper(function (that, iterable) {
        anInstance$6(that, Prototype);
        setInternalState$8(that, {
          type: CONSTRUCTOR_NAME,
          index: create$2(null),
          first: undefined,
          last: undefined,
          size: 0
        });
        if (!DESCRIPTORS$a) that.size = 0;
        if (iterable != undefined) iterate$2(iterable, that[ADDER], { that: that, AS_ENTRIES: IS_MAP });
      });

      var Prototype = Constructor.prototype;

      var getInternalState = internalStateGetterFor$1(CONSTRUCTOR_NAME);

      var define = function (that, key, value) {
        var state = getInternalState(that);
        var entry = getEntry(that, key);
        var previous, index;
        // change existing entry
        if (entry) {
          entry.value = value;
        // create new entry
        } else {
          state.last = entry = {
            index: index = fastKey(key, true),
            key: key,
            value: value,
            previous: previous = state.last,
            next: undefined,
            removed: false
          };
          if (!state.first) state.first = entry;
          if (previous) previous.next = entry;
          if (DESCRIPTORS$a) state.size++;
          else that.size++;
          // add to index
          if (index !== 'F') state.index[index] = entry;
        } return that;
      };

      var getEntry = function (that, key) {
        var state = getInternalState(that);
        // fast case
        var index = fastKey(key);
        var entry;
        if (index !== 'F') return state.index[index];
        // frozen object case
        for (entry = state.first; entry; entry = entry.next) {
          if (entry.key == key) return entry;
        }
      };

      redefineAll$5(Prototype, {
        // `{ Map, Set }.prototype.clear()` methods
        // https://tc39.es/ecma262/#sec-map.prototype.clear
        // https://tc39.es/ecma262/#sec-set.prototype.clear
        clear: function clear() {
          var that = this;
          var state = getInternalState(that);
          var data = state.index;
          var entry = state.first;
          while (entry) {
            entry.removed = true;
            if (entry.previous) entry.previous = entry.previous.next = undefined;
            delete data[entry.index];
            entry = entry.next;
          }
          state.first = state.last = undefined;
          if (DESCRIPTORS$a) state.size = 0;
          else that.size = 0;
        },
        // `{ Map, Set }.prototype.delete(key)` methods
        // https://tc39.es/ecma262/#sec-map.prototype.delete
        // https://tc39.es/ecma262/#sec-set.prototype.delete
        'delete': function (key) {
          var that = this;
          var state = getInternalState(that);
          var entry = getEntry(that, key);
          if (entry) {
            var next = entry.next;
            var prev = entry.previous;
            delete state.index[entry.index];
            entry.removed = true;
            if (prev) prev.next = next;
            if (next) next.previous = prev;
            if (state.first == entry) state.first = next;
            if (state.last == entry) state.last = prev;
            if (DESCRIPTORS$a) state.size--;
            else that.size--;
          } return !!entry;
        },
        // `{ Map, Set }.prototype.forEach(callbackfn, thisArg = undefined)` methods
        // https://tc39.es/ecma262/#sec-map.prototype.foreach
        // https://tc39.es/ecma262/#sec-set.prototype.foreach
        forEach: function forEach(callbackfn /* , that = undefined */) {
          var state = getInternalState(this);
          var boundFunction = bind$7(callbackfn, arguments.length > 1 ? arguments[1] : undefined);
          var entry;
          while (entry = entry ? entry.next : state.first) {
            boundFunction(entry.value, entry.key, this);
            // revert to the last existing entry
            while (entry && entry.removed) entry = entry.previous;
          }
        },
        // `{ Map, Set}.prototype.has(key)` methods
        // https://tc39.es/ecma262/#sec-map.prototype.has
        // https://tc39.es/ecma262/#sec-set.prototype.has
        has: function has(key) {
          return !!getEntry(this, key);
        }
      });

      redefineAll$5(Prototype, IS_MAP ? {
        // `Map.prototype.get(key)` method
        // https://tc39.es/ecma262/#sec-map.prototype.get
        get: function get(key) {
          var entry = getEntry(this, key);
          return entry && entry.value;
        },
        // `Map.prototype.set(key, value)` method
        // https://tc39.es/ecma262/#sec-map.prototype.set
        set: function set(key, value) {
          return define(this, key === 0 ? 0 : key, value);
        }
      } : {
        // `Set.prototype.add(value)` method
        // https://tc39.es/ecma262/#sec-set.prototype.add
        add: function add(value) {
          return define(this, value = value === 0 ? 0 : value, value);
        }
      });
      if (DESCRIPTORS$a) defineProperty$6(Prototype, 'size', {
        get: function () {
          return getInternalState(this).size;
        }
      });
      return Constructor;
    },
    setStrong: function (Constructor, CONSTRUCTOR_NAME, IS_MAP) {
      var ITERATOR_NAME = CONSTRUCTOR_NAME + ' Iterator';
      var getInternalCollectionState = internalStateGetterFor$1(CONSTRUCTOR_NAME);
      var getInternalIteratorState = internalStateGetterFor$1(ITERATOR_NAME);
      // `{ Map, Set }.prototype.{ keys, values, entries, @@iterator }()` methods
      // https://tc39.es/ecma262/#sec-map.prototype.entries
      // https://tc39.es/ecma262/#sec-map.prototype.keys
      // https://tc39.es/ecma262/#sec-map.prototype.values
      // https://tc39.es/ecma262/#sec-map.prototype-@@iterator
      // https://tc39.es/ecma262/#sec-set.prototype.entries
      // https://tc39.es/ecma262/#sec-set.prototype.keys
      // https://tc39.es/ecma262/#sec-set.prototype.values
      // https://tc39.es/ecma262/#sec-set.prototype-@@iterator
      defineIterator$1(Constructor, CONSTRUCTOR_NAME, function (iterated, kind) {
        setInternalState$8(this, {
          type: ITERATOR_NAME,
          target: iterated,
          state: getInternalCollectionState(iterated),
          kind: kind,
          last: undefined
        });
      }, function () {
        var state = getInternalIteratorState(this);
        var kind = state.kind;
        var entry = state.last;
        // revert to the last existing entry
        while (entry && entry.removed) entry = entry.previous;
        // get next entry
        if (!state.target || !(state.last = entry = entry ? entry.next : state.state.first)) {
          // or finish the iteration
          state.target = undefined;
          return { value: undefined, done: true };
        }
        // return step by kind
        if (kind == 'keys') return { value: entry.key, done: false };
        if (kind == 'values') return { value: entry.value, done: false };
        return { value: [entry.key, entry.value], done: false };
      }, IS_MAP ? 'entries' : 'values', !IS_MAP, true);

      // `{ Map, Set }.prototype[@@species]` accessors
      // https://tc39.es/ecma262/#sec-get-map-@@species
      // https://tc39.es/ecma262/#sec-get-set-@@species
      setSpecies$2(CONSTRUCTOR_NAME);
    }
  };

  var collection$2 = collection$3;
  var collectionStrong$1 = collectionStrong$2;

  // `Set` constructor
  // https://tc39.es/ecma262/#sec-set-objects
  collection$2('Set', function (init) {
    return function Set() { return init(this, arguments.length ? arguments[0] : undefined); };
  }, collectionStrong$1);

  var charAt$2 = stringMultibyte.charAt;
  var toString$8 = toString$g;
  var InternalStateModule$7 = internalState;
  var defineIterator = defineIterator$3;

  var STRING_ITERATOR = 'String Iterator';
  var setInternalState$7 = InternalStateModule$7.set;
  var getInternalState$4 = InternalStateModule$7.getterFor(STRING_ITERATOR);

  // `String.prototype[@@iterator]` method
  // https://tc39.es/ecma262/#sec-string.prototype-@@iterator
  defineIterator(String, 'String', function (iterated) {
    setInternalState$7(this, {
      type: STRING_ITERATOR,
      string: toString$8(iterated),
      index: 0
    });
  // `%StringIteratorPrototype%.next` method
  // https://tc39.es/ecma262/#sec-%stringiteratorprototype%.next
  }, function next() {
    var state = getInternalState$4(this);
    var string = state.string;
    var index = state.index;
    var point;
    if (index >= string.length) return { value: undefined, done: true };
    point = charAt$2(string, index);
    state.index += point.length;
    return { value: point, done: false };
  });

  var uncurryThis$m = functionUncurryThis;
  var redefineAll$4 = redefineAll$6;
  var getWeakData = internalMetadata.exports.getWeakData;
  var anObject$b = anObject$q;
  var isObject$d = isObject$s;
  var anInstance$5 = anInstance$8;
  var iterate$1 = iterate$4;
  var ArrayIterationModule = arrayIteration;
  var hasOwn$b = hasOwnProperty_1;
  var InternalStateModule$6 = internalState;

  var setInternalState$6 = InternalStateModule$6.set;
  var internalStateGetterFor = InternalStateModule$6.getterFor;
  var find$1 = ArrayIterationModule.find;
  var findIndex = ArrayIterationModule.findIndex;
  var splice$1 = uncurryThis$m([].splice);
  var id = 0;

  // fallback for uncaught frozen keys
  var uncaughtFrozenStore = function (store) {
    return store.frozen || (store.frozen = new UncaughtFrozenStore());
  };

  var UncaughtFrozenStore = function () {
    this.entries = [];
  };

  var findUncaughtFrozen = function (store, key) {
    return find$1(store.entries, function (it) {
      return it[0] === key;
    });
  };

  UncaughtFrozenStore.prototype = {
    get: function (key) {
      var entry = findUncaughtFrozen(this, key);
      if (entry) return entry[1];
    },
    has: function (key) {
      return !!findUncaughtFrozen(this, key);
    },
    set: function (key, value) {
      var entry = findUncaughtFrozen(this, key);
      if (entry) entry[1] = value;
      else this.entries.push([key, value]);
    },
    'delete': function (key) {
      var index = findIndex(this.entries, function (it) {
        return it[0] === key;
      });
      if (~index) splice$1(this.entries, index, 1);
      return !!~index;
    }
  };

  var collectionWeak$1 = {
    getConstructor: function (wrapper, CONSTRUCTOR_NAME, IS_MAP, ADDER) {
      var Constructor = wrapper(function (that, iterable) {
        anInstance$5(that, Prototype);
        setInternalState$6(that, {
          type: CONSTRUCTOR_NAME,
          id: id++,
          frozen: undefined
        });
        if (iterable != undefined) iterate$1(iterable, that[ADDER], { that: that, AS_ENTRIES: IS_MAP });
      });

      var Prototype = Constructor.prototype;

      var getInternalState = internalStateGetterFor(CONSTRUCTOR_NAME);

      var define = function (that, key, value) {
        var state = getInternalState(that);
        var data = getWeakData(anObject$b(key), true);
        if (data === true) uncaughtFrozenStore(state).set(key, value);
        else data[state.id] = value;
        return that;
      };

      redefineAll$4(Prototype, {
        // `{ WeakMap, WeakSet }.prototype.delete(key)` methods
        // https://tc39.es/ecma262/#sec-weakmap.prototype.delete
        // https://tc39.es/ecma262/#sec-weakset.prototype.delete
        'delete': function (key) {
          var state = getInternalState(this);
          if (!isObject$d(key)) return false;
          var data = getWeakData(key);
          if (data === true) return uncaughtFrozenStore(state)['delete'](key);
          return data && hasOwn$b(data, state.id) && delete data[state.id];
        },
        // `{ WeakMap, WeakSet }.prototype.has(key)` methods
        // https://tc39.es/ecma262/#sec-weakmap.prototype.has
        // https://tc39.es/ecma262/#sec-weakset.prototype.has
        has: function has(key) {
          var state = getInternalState(this);
          if (!isObject$d(key)) return false;
          var data = getWeakData(key);
          if (data === true) return uncaughtFrozenStore(state).has(key);
          return data && hasOwn$b(data, state.id);
        }
      });

      redefineAll$4(Prototype, IS_MAP ? {
        // `WeakMap.prototype.get(key)` method
        // https://tc39.es/ecma262/#sec-weakmap.prototype.get
        get: function get(key) {
          var state = getInternalState(this);
          if (isObject$d(key)) {
            var data = getWeakData(key);
            if (data === true) return uncaughtFrozenStore(state).get(key);
            return data ? data[state.id] : undefined;
          }
        },
        // `WeakMap.prototype.set(key, value)` method
        // https://tc39.es/ecma262/#sec-weakmap.prototype.set
        set: function set(key, value) {
          return define(this, key, value);
        }
      } : {
        // `WeakSet.prototype.add(value)` method
        // https://tc39.es/ecma262/#sec-weakset.prototype.add
        add: function add(value) {
          return define(this, value, true);
        }
      });

      return Constructor;
    }
  };

  var global$x = global$1e;
  var uncurryThis$l = functionUncurryThis;
  var redefineAll$3 = redefineAll$6;
  var InternalMetadataModule = internalMetadata.exports;
  var collection$1 = collection$3;
  var collectionWeak = collectionWeak$1;
  var isObject$c = isObject$s;
  var isExtensible = objectIsExtensible;
  var enforceInternalState = internalState.enforce;
  var NATIVE_WEAK_MAP = nativeWeakMap;

  var IS_IE11 = !global$x.ActiveXObject && 'ActiveXObject' in global$x;
  var InternalWeakMap;

  var wrapper = function (init) {
    return function WeakMap() {
      return init(this, arguments.length ? arguments[0] : undefined);
    };
  };

  // `WeakMap` constructor
  // https://tc39.es/ecma262/#sec-weakmap-constructor
  var $WeakMap = collection$1('WeakMap', wrapper, collectionWeak);

  // IE11 WeakMap frozen keys fix
  // We can't use feature detection because it crash some old IE builds
  // https://github.com/zloirock/core-js/issues/485
  if (NATIVE_WEAK_MAP && IS_IE11) {
    InternalWeakMap = collectionWeak.getConstructor(wrapper, 'WeakMap', true);
    InternalMetadataModule.enable();
    var WeakMapPrototype = $WeakMap.prototype;
    var nativeDelete = uncurryThis$l(WeakMapPrototype['delete']);
    var nativeHas = uncurryThis$l(WeakMapPrototype.has);
    var nativeGet = uncurryThis$l(WeakMapPrototype.get);
    var nativeSet = uncurryThis$l(WeakMapPrototype.set);
    redefineAll$3(WeakMapPrototype, {
      'delete': function (key) {
        if (isObject$c(key) && !isExtensible(key)) {
          var state = enforceInternalState(this);
          if (!state.frozen) state.frozen = new InternalWeakMap();
          return nativeDelete(this, key) || state.frozen['delete'](key);
        } return nativeDelete(this, key);
      },
      has: function has(key) {
        if (isObject$c(key) && !isExtensible(key)) {
          var state = enforceInternalState(this);
          if (!state.frozen) state.frozen = new InternalWeakMap();
          return nativeHas(this, key) || state.frozen.has(key);
        } return nativeHas(this, key);
      },
      get: function get(key) {
        if (isObject$c(key) && !isExtensible(key)) {
          var state = enforceInternalState(this);
          if (!state.frozen) state.frozen = new InternalWeakMap();
          return nativeHas(this, key) ? nativeGet(this, key) : state.frozen.get(key);
        } return nativeGet(this, key);
      },
      set: function set(key, value) {
        if (isObject$c(key) && !isExtensible(key)) {
          var state = enforceInternalState(this);
          if (!state.frozen) state.frozen = new InternalWeakMap();
          nativeHas(this, key) ? nativeSet(this, key, value) : state.frozen.set(key, value);
        } else nativeSet(this, key, value);
        return this;
      }
    });
  }

  var wellKnownSymbolWrapped = {};

  var wellKnownSymbol$6 = wellKnownSymbol$s;

  wellKnownSymbolWrapped.f = wellKnownSymbol$6;

  var global$w = global$1e;

  var path$1 = global$w;

  var path = path$1;
  var hasOwn$a = hasOwnProperty_1;
  var wrappedWellKnownSymbolModule$1 = wellKnownSymbolWrapped;
  var defineProperty$5 = objectDefineProperty.f;

  var defineWellKnownSymbol$2 = function (NAME) {
    var Symbol = path.Symbol || (path.Symbol = {});
    if (!hasOwn$a(Symbol, NAME)) defineProperty$5(Symbol, NAME, {
      value: wrappedWellKnownSymbolModule$1.f(NAME)
    });
  };

  var $$w = _export;
  var global$v = global$1e;
  var getBuiltIn$2 = getBuiltIn$a;
  var apply$4 = functionApply;
  var call$b = functionCall;
  var uncurryThis$k = functionUncurryThis;
  var DESCRIPTORS$9 = descriptors;
  var NATIVE_SYMBOL$1 = nativeSymbol;
  var fails$j = fails$J;
  var hasOwn$9 = hasOwnProperty_1;
  var isArray$1 = isArray$5;
  var isCallable$5 = isCallable$q;
  var isObject$b = isObject$s;
  var isPrototypeOf$4 = objectIsPrototypeOf;
  var isSymbol$3 = isSymbol$6;
  var anObject$a = anObject$q;
  var toObject$8 = toObject$g;
  var toIndexedObject$3 = toIndexedObject$b;
  var toPropertyKey$2 = toPropertyKey$6;
  var $toString$2 = toString$g;
  var createPropertyDescriptor$3 = createPropertyDescriptor$8;
  var nativeObjectCreate = objectCreate;
  var objectKeys$1 = objectKeys$4;
  var getOwnPropertyNamesModule = objectGetOwnPropertyNames;
  var getOwnPropertyNamesExternal = objectGetOwnPropertyNamesExternal;
  var getOwnPropertySymbolsModule = objectGetOwnPropertySymbols;
  var getOwnPropertyDescriptorModule$3 = objectGetOwnPropertyDescriptor;
  var definePropertyModule$3 = objectDefineProperty;
  var propertyIsEnumerableModule = objectPropertyIsEnumerable;
  var arraySlice$7 = arraySlice$9;
  var redefine$5 = redefine$e.exports;
  var shared = shared$5.exports;
  var sharedKey = sharedKey$4;
  var hiddenKeys = hiddenKeys$6;
  var uid$3 = uid$7;
  var wellKnownSymbol$5 = wellKnownSymbol$s;
  var wrappedWellKnownSymbolModule = wellKnownSymbolWrapped;
  var defineWellKnownSymbol$1 = defineWellKnownSymbol$2;
  var setToStringTag$5 = setToStringTag$9;
  var InternalStateModule$5 = internalState;
  var $forEach$1 = arrayIteration.forEach;

  var HIDDEN = sharedKey('hidden');
  var SYMBOL = 'Symbol';
  var PROTOTYPE$1 = 'prototype';
  var TO_PRIMITIVE = wellKnownSymbol$5('toPrimitive');

  var setInternalState$5 = InternalStateModule$5.set;
  var getInternalState$3 = InternalStateModule$5.getterFor(SYMBOL);

  var ObjectPrototype$2 = Object[PROTOTYPE$1];
  var $Symbol = global$v.Symbol;
  var SymbolPrototype$1 = $Symbol && $Symbol[PROTOTYPE$1];
  var TypeError$7 = global$v.TypeError;
  var QObject = global$v.QObject;
  var $stringify$1 = getBuiltIn$2('JSON', 'stringify');
  var nativeGetOwnPropertyDescriptor$1 = getOwnPropertyDescriptorModule$3.f;
  var nativeDefineProperty$1 = definePropertyModule$3.f;
  var nativeGetOwnPropertyNames = getOwnPropertyNamesExternal.f;
  var nativePropertyIsEnumerable = propertyIsEnumerableModule.f;
  var push$5 = uncurryThis$k([].push);

  var AllSymbols = shared('symbols');
  var ObjectPrototypeSymbols = shared('op-symbols');
  var StringToSymbolRegistry = shared('string-to-symbol-registry');
  var SymbolToStringRegistry = shared('symbol-to-string-registry');
  var WellKnownSymbolsStore = shared('wks');

  // Don't use setters in Qt Script, https://github.com/zloirock/core-js/issues/173
  var USE_SETTER = !QObject || !QObject[PROTOTYPE$1] || !QObject[PROTOTYPE$1].findChild;

  // fallback for old Android, https://code.google.com/p/v8/issues/detail?id=687
  var setSymbolDescriptor = DESCRIPTORS$9 && fails$j(function () {
    return nativeObjectCreate(nativeDefineProperty$1({}, 'a', {
      get: function () { return nativeDefineProperty$1(this, 'a', { value: 7 }).a; }
    })).a != 7;
  }) ? function (O, P, Attributes) {
    var ObjectPrototypeDescriptor = nativeGetOwnPropertyDescriptor$1(ObjectPrototype$2, P);
    if (ObjectPrototypeDescriptor) delete ObjectPrototype$2[P];
    nativeDefineProperty$1(O, P, Attributes);
    if (ObjectPrototypeDescriptor && O !== ObjectPrototype$2) {
      nativeDefineProperty$1(ObjectPrototype$2, P, ObjectPrototypeDescriptor);
    }
  } : nativeDefineProperty$1;

  var wrap = function (tag, description) {
    var symbol = AllSymbols[tag] = nativeObjectCreate(SymbolPrototype$1);
    setInternalState$5(symbol, {
      type: SYMBOL,
      tag: tag,
      description: description
    });
    if (!DESCRIPTORS$9) symbol.description = description;
    return symbol;
  };

  var $defineProperty = function defineProperty(O, P, Attributes) {
    if (O === ObjectPrototype$2) $defineProperty(ObjectPrototypeSymbols, P, Attributes);
    anObject$a(O);
    var key = toPropertyKey$2(P);
    anObject$a(Attributes);
    if (hasOwn$9(AllSymbols, key)) {
      if (!Attributes.enumerable) {
        if (!hasOwn$9(O, HIDDEN)) nativeDefineProperty$1(O, HIDDEN, createPropertyDescriptor$3(1, {}));
        O[HIDDEN][key] = true;
      } else {
        if (hasOwn$9(O, HIDDEN) && O[HIDDEN][key]) O[HIDDEN][key] = false;
        Attributes = nativeObjectCreate(Attributes, { enumerable: createPropertyDescriptor$3(0, false) });
      } return setSymbolDescriptor(O, key, Attributes);
    } return nativeDefineProperty$1(O, key, Attributes);
  };

  var $defineProperties = function defineProperties(O, Properties) {
    anObject$a(O);
    var properties = toIndexedObject$3(Properties);
    var keys = objectKeys$1(properties).concat($getOwnPropertySymbols(properties));
    $forEach$1(keys, function (key) {
      if (!DESCRIPTORS$9 || call$b($propertyIsEnumerable$1, properties, key)) $defineProperty(O, key, properties[key]);
    });
    return O;
  };

  var $create = function create(O, Properties) {
    return Properties === undefined ? nativeObjectCreate(O) : $defineProperties(nativeObjectCreate(O), Properties);
  };

  var $propertyIsEnumerable$1 = function propertyIsEnumerable(V) {
    var P = toPropertyKey$2(V);
    var enumerable = call$b(nativePropertyIsEnumerable, this, P);
    if (this === ObjectPrototype$2 && hasOwn$9(AllSymbols, P) && !hasOwn$9(ObjectPrototypeSymbols, P)) return false;
    return enumerable || !hasOwn$9(this, P) || !hasOwn$9(AllSymbols, P) || hasOwn$9(this, HIDDEN) && this[HIDDEN][P]
      ? enumerable : true;
  };

  var $getOwnPropertyDescriptor = function getOwnPropertyDescriptor(O, P) {
    var it = toIndexedObject$3(O);
    var key = toPropertyKey$2(P);
    if (it === ObjectPrototype$2 && hasOwn$9(AllSymbols, key) && !hasOwn$9(ObjectPrototypeSymbols, key)) return;
    var descriptor = nativeGetOwnPropertyDescriptor$1(it, key);
    if (descriptor && hasOwn$9(AllSymbols, key) && !(hasOwn$9(it, HIDDEN) && it[HIDDEN][key])) {
      descriptor.enumerable = true;
    }
    return descriptor;
  };

  var $getOwnPropertyNames = function getOwnPropertyNames(O) {
    var names = nativeGetOwnPropertyNames(toIndexedObject$3(O));
    var result = [];
    $forEach$1(names, function (key) {
      if (!hasOwn$9(AllSymbols, key) && !hasOwn$9(hiddenKeys, key)) push$5(result, key);
    });
    return result;
  };

  var $getOwnPropertySymbols = function getOwnPropertySymbols(O) {
    var IS_OBJECT_PROTOTYPE = O === ObjectPrototype$2;
    var names = nativeGetOwnPropertyNames(IS_OBJECT_PROTOTYPE ? ObjectPrototypeSymbols : toIndexedObject$3(O));
    var result = [];
    $forEach$1(names, function (key) {
      if (hasOwn$9(AllSymbols, key) && (!IS_OBJECT_PROTOTYPE || hasOwn$9(ObjectPrototype$2, key))) {
        push$5(result, AllSymbols[key]);
      }
    });
    return result;
  };

  // `Symbol` constructor
  // https://tc39.es/ecma262/#sec-symbol-constructor
  if (!NATIVE_SYMBOL$1) {
    $Symbol = function Symbol() {
      if (isPrototypeOf$4(SymbolPrototype$1, this)) throw TypeError$7('Symbol is not a constructor');
      var description = !arguments.length || arguments[0] === undefined ? undefined : $toString$2(arguments[0]);
      var tag = uid$3(description);
      var setter = function (value) {
        if (this === ObjectPrototype$2) call$b(setter, ObjectPrototypeSymbols, value);
        if (hasOwn$9(this, HIDDEN) && hasOwn$9(this[HIDDEN], tag)) this[HIDDEN][tag] = false;
        setSymbolDescriptor(this, tag, createPropertyDescriptor$3(1, value));
      };
      if (DESCRIPTORS$9 && USE_SETTER) setSymbolDescriptor(ObjectPrototype$2, tag, { configurable: true, set: setter });
      return wrap(tag, description);
    };

    SymbolPrototype$1 = $Symbol[PROTOTYPE$1];

    redefine$5(SymbolPrototype$1, 'toString', function toString() {
      return getInternalState$3(this).tag;
    });

    redefine$5($Symbol, 'withoutSetter', function (description) {
      return wrap(uid$3(description), description);
    });

    propertyIsEnumerableModule.f = $propertyIsEnumerable$1;
    definePropertyModule$3.f = $defineProperty;
    getOwnPropertyDescriptorModule$3.f = $getOwnPropertyDescriptor;
    getOwnPropertyNamesModule.f = getOwnPropertyNamesExternal.f = $getOwnPropertyNames;
    getOwnPropertySymbolsModule.f = $getOwnPropertySymbols;

    wrappedWellKnownSymbolModule.f = function (name) {
      return wrap(wellKnownSymbol$5(name), name);
    };

    if (DESCRIPTORS$9) {
      // https://github.com/tc39/proposal-Symbol-description
      nativeDefineProperty$1(SymbolPrototype$1, 'description', {
        configurable: true,
        get: function description() {
          return getInternalState$3(this).description;
        }
      });
      {
        redefine$5(ObjectPrototype$2, 'propertyIsEnumerable', $propertyIsEnumerable$1, { unsafe: true });
      }
    }
  }

  $$w({ global: true, wrap: true, forced: !NATIVE_SYMBOL$1, sham: !NATIVE_SYMBOL$1 }, {
    Symbol: $Symbol
  });

  $forEach$1(objectKeys$1(WellKnownSymbolsStore), function (name) {
    defineWellKnownSymbol$1(name);
  });

  $$w({ target: SYMBOL, stat: true, forced: !NATIVE_SYMBOL$1 }, {
    // `Symbol.for` method
    // https://tc39.es/ecma262/#sec-symbol.for
    'for': function (key) {
      var string = $toString$2(key);
      if (hasOwn$9(StringToSymbolRegistry, string)) return StringToSymbolRegistry[string];
      var symbol = $Symbol(string);
      StringToSymbolRegistry[string] = symbol;
      SymbolToStringRegistry[symbol] = string;
      return symbol;
    },
    // `Symbol.keyFor` method
    // https://tc39.es/ecma262/#sec-symbol.keyfor
    keyFor: function keyFor(sym) {
      if (!isSymbol$3(sym)) throw TypeError$7(sym + ' is not a symbol');
      if (hasOwn$9(SymbolToStringRegistry, sym)) return SymbolToStringRegistry[sym];
    },
    useSetter: function () { USE_SETTER = true; },
    useSimple: function () { USE_SETTER = false; }
  });

  $$w({ target: 'Object', stat: true, forced: !NATIVE_SYMBOL$1, sham: !DESCRIPTORS$9 }, {
    // `Object.create` method
    // https://tc39.es/ecma262/#sec-object.create
    create: $create,
    // `Object.defineProperty` method
    // https://tc39.es/ecma262/#sec-object.defineproperty
    defineProperty: $defineProperty,
    // `Object.defineProperties` method
    // https://tc39.es/ecma262/#sec-object.defineproperties
    defineProperties: $defineProperties,
    // `Object.getOwnPropertyDescriptor` method
    // https://tc39.es/ecma262/#sec-object.getownpropertydescriptors
    getOwnPropertyDescriptor: $getOwnPropertyDescriptor
  });

  $$w({ target: 'Object', stat: true, forced: !NATIVE_SYMBOL$1 }, {
    // `Object.getOwnPropertyNames` method
    // https://tc39.es/ecma262/#sec-object.getownpropertynames
    getOwnPropertyNames: $getOwnPropertyNames,
    // `Object.getOwnPropertySymbols` method
    // https://tc39.es/ecma262/#sec-object.getownpropertysymbols
    getOwnPropertySymbols: $getOwnPropertySymbols
  });

  // Chrome 38 and 39 `Object.getOwnPropertySymbols` fails on primitives
  // https://bugs.chromium.org/p/v8/issues/detail?id=3443
  $$w({ target: 'Object', stat: true, forced: fails$j(function () { getOwnPropertySymbolsModule.f(1); }) }, {
    getOwnPropertySymbols: function getOwnPropertySymbols(it) {
      return getOwnPropertySymbolsModule.f(toObject$8(it));
    }
  });

  // `JSON.stringify` method behavior with symbols
  // https://tc39.es/ecma262/#sec-json.stringify
  if ($stringify$1) {
    var FORCED_JSON_STRINGIFY = !NATIVE_SYMBOL$1 || fails$j(function () {
      var symbol = $Symbol();
      // MS Edge converts symbol values to JSON as {}
      return $stringify$1([symbol]) != '[null]'
        // WebKit converts symbol values to JSON as null
        || $stringify$1({ a: symbol }) != '{}'
        // V8 throws on boxed symbols
        || $stringify$1(Object(symbol)) != '{}';
    });

    $$w({ target: 'JSON', stat: true, forced: FORCED_JSON_STRINGIFY }, {
      // eslint-disable-next-line no-unused-vars -- required for `.length`
      stringify: function stringify(it, replacer, space) {
        var args = arraySlice$7(arguments);
        var $replacer = replacer;
        if (!isObject$b(replacer) && it === undefined || isSymbol$3(it)) return; // IE8 returns string on undefined
        if (!isArray$1(replacer)) replacer = function (key, value) {
          if (isCallable$5($replacer)) value = call$b($replacer, this, key, value);
          if (!isSymbol$3(value)) return value;
        };
        args[1] = replacer;
        return apply$4($stringify$1, null, args);
      }
    });
  }

  // `Symbol.prototype[@@toPrimitive]` method
  // https://tc39.es/ecma262/#sec-symbol.prototype-@@toprimitive
  if (!SymbolPrototype$1[TO_PRIMITIVE]) {
    var valueOf = SymbolPrototype$1.valueOf;
    // eslint-disable-next-line no-unused-vars -- required for .length
    redefine$5(SymbolPrototype$1, TO_PRIMITIVE, function (hint) {
      // TODO: improve hint logic
      return call$b(valueOf, this);
    });
  }
  // `Symbol.prototype[@@toStringTag]` property
  // https://tc39.es/ecma262/#sec-symbol.prototype-@@tostringtag
  setToStringTag$5($Symbol, SYMBOL);

  hiddenKeys[HIDDEN] = true;

  var $$v = _export;
  var DESCRIPTORS$8 = descriptors;
  var global$u = global$1e;
  var uncurryThis$j = functionUncurryThis;
  var hasOwn$8 = hasOwnProperty_1;
  var isCallable$4 = isCallable$q;
  var isPrototypeOf$3 = objectIsPrototypeOf;
  var toString$7 = toString$g;
  var defineProperty$4 = objectDefineProperty.f;
  var copyConstructorProperties = copyConstructorProperties$2;

  var NativeSymbol = global$u.Symbol;
  var SymbolPrototype = NativeSymbol && NativeSymbol.prototype;

  if (DESCRIPTORS$8 && isCallable$4(NativeSymbol) && (!('description' in SymbolPrototype) ||
    // Safari 12 bug
    NativeSymbol().description !== undefined
  )) {
    var EmptyStringDescriptionStore = {};
    // wrap Symbol constructor for correct work with undefined description
    var SymbolWrapper = function Symbol() {
      var description = arguments.length < 1 || arguments[0] === undefined ? undefined : toString$7(arguments[0]);
      var result = isPrototypeOf$3(SymbolPrototype, this)
        ? new NativeSymbol(description)
        // in Edge 13, String(Symbol(undefined)) === 'Symbol(undefined)'
        : description === undefined ? NativeSymbol() : NativeSymbol(description);
      if (description === '') EmptyStringDescriptionStore[result] = true;
      return result;
    };

    copyConstructorProperties(SymbolWrapper, NativeSymbol);
    SymbolWrapper.prototype = SymbolPrototype;
    SymbolPrototype.constructor = SymbolWrapper;

    var NATIVE_SYMBOL = String(NativeSymbol('test')) == 'Symbol(test)';
    var symbolToString = uncurryThis$j(SymbolPrototype.toString);
    var symbolValueOf = uncurryThis$j(SymbolPrototype.valueOf);
    var regexp = /^Symbol\((.*)\)[^)]+$/;
    var replace$4 = uncurryThis$j(''.replace);
    var stringSlice$3 = uncurryThis$j(''.slice);

    defineProperty$4(SymbolPrototype, 'description', {
      configurable: true,
      get: function description() {
        var symbol = symbolValueOf(this);
        var string = symbolToString(symbol);
        if (hasOwn$8(EmptyStringDescriptionStore, symbol)) return '';
        var desc = NATIVE_SYMBOL ? stringSlice$3(string, 7, -1) : replace$4(string, regexp, '$1');
        return desc === '' ? undefined : desc;
      }
    });

    $$v({ global: true, forced: true }, {
      Symbol: SymbolWrapper
    });
  }

  var collection = collection$3;
  var collectionStrong = collectionStrong$2;

  // `Map` constructor
  // https://tc39.es/ecma262/#sec-map-objects
  collection('Map', function (init) {
    return function Map() { return init(this, arguments.length ? arguments[0] : undefined); };
  }, collectionStrong);

  var $$u = _export;
  var $filter$1 = arrayIteration.filter;
  var arrayMethodHasSpeciesSupport$1 = arrayMethodHasSpeciesSupport$5;

  var HAS_SPECIES_SUPPORT$1 = arrayMethodHasSpeciesSupport$1('filter');

  // `Array.prototype.filter` method
  // https://tc39.es/ecma262/#sec-array.prototype.filter
  // with adding support of @@species
  $$u({ target: 'Array', proto: true, forced: !HAS_SPECIES_SUPPORT$1 }, {
    filter: function filter(callbackfn /* , thisArg */) {
      return $filter$1(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
    }
  });

  var $$t = _export;
  var $map$1 = arrayIteration.map;
  var arrayMethodHasSpeciesSupport = arrayMethodHasSpeciesSupport$5;

  var HAS_SPECIES_SUPPORT = arrayMethodHasSpeciesSupport('map');

  // `Array.prototype.map` method
  // https://tc39.es/ecma262/#sec-array.prototype.map
  // with adding support of @@species
  $$t({ target: 'Array', proto: true, forced: !HAS_SPECIES_SUPPORT }, {
    map: function map(callbackfn /* , thisArg */) {
      return $map$1(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
    }
  });

  var $$s = _export;
  var fails$i = fails$J;
  var getOwnPropertyNames$3 = objectGetOwnPropertyNamesExternal.f;

  // eslint-disable-next-line es/no-object-getownpropertynames -- required for testing
  var FAILS_ON_PRIMITIVES$2 = fails$i(function () { return !Object.getOwnPropertyNames(1); });

  // `Object.getOwnPropertyNames` method
  // https://tc39.es/ecma262/#sec-object.getownpropertynames
  $$s({ target: 'Object', stat: true, forced: FAILS_ON_PRIMITIVES$2 }, {
    getOwnPropertyNames: getOwnPropertyNames$3
  });

  var hasOwn$7 = hasOwnProperty_1;

  var isDataDescriptor$2 = function (descriptor) {
    return descriptor !== undefined && (hasOwn$7(descriptor, 'value') || hasOwn$7(descriptor, 'writable'));
  };

  var $$r = _export;
  var call$a = functionCall;
  var isObject$a = isObject$s;
  var anObject$9 = anObject$q;
  var isDataDescriptor$1 = isDataDescriptor$2;
  var getOwnPropertyDescriptorModule$2 = objectGetOwnPropertyDescriptor;
  var getPrototypeOf$3 = objectGetPrototypeOf$1;

  // `Reflect.get` method
  // https://tc39.es/ecma262/#sec-reflect.get
  function get$3(target, propertyKey /* , receiver */) {
    var receiver = arguments.length < 3 ? target : arguments[2];
    var descriptor, prototype;
    if (anObject$9(target) === receiver) return target[propertyKey];
    descriptor = getOwnPropertyDescriptorModule$2.f(target, propertyKey);
    if (descriptor) return isDataDescriptor$1(descriptor)
      ? descriptor.value
      : descriptor.get === undefined ? undefined : call$a(descriptor.get, receiver);
    if (isObject$a(prototype = getPrototypeOf$3(target))) return get$3(prototype, propertyKey, receiver);
  }

  $$r({ target: 'Reflect', stat: true }, {
    get: get$3
  });

  var $$q = _export;
  var global$t = global$1e;
  var setToStringTag$4 = setToStringTag$9;

  $$q({ global: true }, { Reflect: {} });

  // Reflect[@@toStringTag] property
  // https://tc39.es/ecma262/#sec-reflect-@@tostringtag
  setToStringTag$4(global$t.Reflect, 'Reflect', true);

  var uncurryThis$i = functionUncurryThis;

  // `thisNumberValue` abstract operation
  // https://tc39.es/ecma262/#sec-thisnumbervalue
  var thisNumberValue$2 = uncurryThis$i(1.0.valueOf);

  var DESCRIPTORS$7 = descriptors;
  var global$s = global$1e;
  var uncurryThis$h = functionUncurryThis;
  var isForced$1 = isForced_1;
  var redefine$4 = redefine$e.exports;
  var hasOwn$6 = hasOwnProperty_1;
  var inheritIfRequired$1 = inheritIfRequired$3;
  var isPrototypeOf$2 = objectIsPrototypeOf;
  var isSymbol$2 = isSymbol$6;
  var toPrimitive = toPrimitive$2;
  var fails$h = fails$J;
  var getOwnPropertyNames$2 = objectGetOwnPropertyNames.f;
  var getOwnPropertyDescriptor$3 = objectGetOwnPropertyDescriptor.f;
  var defineProperty$3 = objectDefineProperty.f;
  var thisNumberValue$1 = thisNumberValue$2;
  var trim = stringTrim.trim;

  var NUMBER = 'Number';
  var NativeNumber = global$s[NUMBER];
  var NumberPrototype = NativeNumber.prototype;
  var TypeError$6 = global$s.TypeError;
  var arraySlice$6 = uncurryThis$h(''.slice);
  var charCodeAt$1 = uncurryThis$h(''.charCodeAt);

  // `ToNumeric` abstract operation
  // https://tc39.es/ecma262/#sec-tonumeric
  var toNumeric = function (value) {
    var primValue = toPrimitive(value, 'number');
    return typeof primValue == 'bigint' ? primValue : toNumber$1(primValue);
  };

  // `ToNumber` abstract operation
  // https://tc39.es/ecma262/#sec-tonumber
  var toNumber$1 = function (argument) {
    var it = toPrimitive(argument, 'number');
    var first, third, radix, maxCode, digits, length, index, code;
    if (isSymbol$2(it)) throw TypeError$6('Cannot convert a Symbol value to a number');
    if (typeof it == 'string' && it.length > 2) {
      it = trim(it);
      first = charCodeAt$1(it, 0);
      if (first === 43 || first === 45) {
        third = charCodeAt$1(it, 2);
        if (third === 88 || third === 120) return NaN; // Number('+0x1') should be NaN, old V8 fix
      } else if (first === 48) {
        switch (charCodeAt$1(it, 1)) {
          case 66: case 98: radix = 2; maxCode = 49; break; // fast equal of /^0b[01]+$/i
          case 79: case 111: radix = 8; maxCode = 55; break; // fast equal of /^0o[0-7]+$/i
          default: return +it;
        }
        digits = arraySlice$6(it, 2);
        length = digits.length;
        for (index = 0; index < length; index++) {
          code = charCodeAt$1(digits, index);
          // parseInt parses a string to a first unavailable symbol
          // but ToNumber should return NaN if a string contains unavailable symbols
          if (code < 48 || code > maxCode) return NaN;
        } return parseInt(digits, radix);
      }
    } return +it;
  };

  // `Number` constructor
  // https://tc39.es/ecma262/#sec-number-constructor
  if (isForced$1(NUMBER, !NativeNumber(' 0o1') || !NativeNumber('0b1') || NativeNumber('+0x1'))) {
    var NumberWrapper = function Number(value) {
      var n = arguments.length < 1 ? 0 : NativeNumber(toNumeric(value));
      var dummy = this;
      // check on 1..constructor(foo) case
      return isPrototypeOf$2(NumberPrototype, dummy) && fails$h(function () { thisNumberValue$1(dummy); })
        ? inheritIfRequired$1(Object(n), dummy, NumberWrapper) : n;
    };
    for (var keys$2 = DESCRIPTORS$7 ? getOwnPropertyNames$2(NativeNumber) : (
      // ES3:
      'MAX_VALUE,MIN_VALUE,NaN,NEGATIVE_INFINITY,POSITIVE_INFINITY,' +
      // ES2015 (in case, if modules with ES2015 Number statics required before):
      'EPSILON,MAX_SAFE_INTEGER,MIN_SAFE_INTEGER,isFinite,isInteger,isNaN,isSafeInteger,parseFloat,parseInt,' +
      // ESNext
      'fromString,range'
    ).split(','), j$1 = 0, key$1; keys$2.length > j$1; j$1++) {
      if (hasOwn$6(NativeNumber, key$1 = keys$2[j$1]) && !hasOwn$6(NumberWrapper, key$1)) {
        defineProperty$3(NumberWrapper, key$1, getOwnPropertyDescriptor$3(NativeNumber, key$1));
      }
    }
    NumberWrapper.prototype = NumberPrototype;
    NumberPrototype.constructor = NumberWrapper;
    redefine$4(global$s, NUMBER, NumberWrapper);
  }

  var $$p = _export;
  var call$9 = functionCall;
  var anObject$8 = anObject$q;
  var isObject$9 = isObject$s;
  var isDataDescriptor = isDataDescriptor$2;
  var fails$g = fails$J;
  var definePropertyModule$2 = objectDefineProperty;
  var getOwnPropertyDescriptorModule$1 = objectGetOwnPropertyDescriptor;
  var getPrototypeOf$2 = objectGetPrototypeOf$1;
  var createPropertyDescriptor$2 = createPropertyDescriptor$8;

  // `Reflect.set` method
  // https://tc39.es/ecma262/#sec-reflect.set
  function set$5(target, propertyKey, V /* , receiver */) {
    var receiver = arguments.length < 4 ? target : arguments[3];
    var ownDescriptor = getOwnPropertyDescriptorModule$1.f(anObject$8(target), propertyKey);
    var existingDescriptor, prototype, setter;
    if (!ownDescriptor) {
      if (isObject$9(prototype = getPrototypeOf$2(target))) {
        return set$5(prototype, propertyKey, V, receiver);
      }
      ownDescriptor = createPropertyDescriptor$2(0);
    }
    if (isDataDescriptor(ownDescriptor)) {
      if (ownDescriptor.writable === false || !isObject$9(receiver)) return false;
      if (existingDescriptor = getOwnPropertyDescriptorModule$1.f(receiver, propertyKey)) {
        if (existingDescriptor.get || existingDescriptor.set || existingDescriptor.writable === false) return false;
        existingDescriptor.value = V;
        definePropertyModule$2.f(receiver, propertyKey, existingDescriptor);
      } else definePropertyModule$2.f(receiver, propertyKey, createPropertyDescriptor$2(0, V));
    } else {
      setter = ownDescriptor.set;
      if (setter === undefined) return false;
      call$9(setter, receiver, V);
    } return true;
  }

  // MS Edge 17-18 Reflect.set allows setting the property to object
  // with non-writable property on the prototype
  var MS_EDGE_BUG = fails$g(function () {
    var Constructor = function () { /* empty */ };
    var object = definePropertyModule$2.f(new Constructor(), 'a', { configurable: true });
    // eslint-disable-next-line es/no-reflect -- required for testing
    return Reflect.set(Constructor.prototype, 'a', 1, object) !== false;
  });

  $$p({ target: 'Reflect', stat: true, forced: MS_EDGE_BUG }, {
    set: set$5
  });

  var $$o = _export;
  var anObject$7 = anObject$q;
  var getOwnPropertyDescriptor$2 = objectGetOwnPropertyDescriptor.f;

  // `Reflect.deleteProperty` method
  // https://tc39.es/ecma262/#sec-reflect.deleteproperty
  $$o({ target: 'Reflect', stat: true }, {
    deleteProperty: function deleteProperty(target, propertyKey) {
      var descriptor = getOwnPropertyDescriptor$2(anObject$7(target), propertyKey);
      return descriptor && !descriptor.configurable ? false : delete target[propertyKey];
    }
  });

  var $$n = _export;

  // `Reflect.has` method
  // https://tc39.es/ecma262/#sec-reflect.has
  $$n({ target: 'Reflect', stat: true }, {
    has: function has(target, propertyKey) {
      return propertyKey in target;
    }
  });

  var $$m = _export;
  var ownKeys$1 = ownKeys$3;

  // `Reflect.ownKeys` method
  // https://tc39.es/ecma262/#sec-reflect.ownkeys
  $$m({ target: 'Reflect', stat: true }, {
    ownKeys: ownKeys$1
  });

  var $$l = _export;
  var anObject$6 = anObject$q;
  var objectGetPrototypeOf = objectGetPrototypeOf$1;
  var CORRECT_PROTOTYPE_GETTER = correctPrototypeGetter;

  // `Reflect.getPrototypeOf` method
  // https://tc39.es/ecma262/#sec-reflect.getprototypeof
  $$l({ target: 'Reflect', stat: true, sham: !CORRECT_PROTOTYPE_GETTER }, {
    getPrototypeOf: function getPrototypeOf(target) {
      return objectGetPrototypeOf(anObject$6(target));
    }
  });

  var defineWellKnownSymbol = defineWellKnownSymbol$2;

  // `Symbol.iterator` well-known symbol
  // https://tc39.es/ecma262/#sec-symbol.iterator
  defineWellKnownSymbol('iterator');

  var $$k = _export;
  var $isExtensible = objectIsExtensible;

  // `Object.isExtensible` method
  // https://tc39.es/ecma262/#sec-object.isextensible
  // eslint-disable-next-line es/no-object-isextensible -- safe
  $$k({ target: 'Object', stat: true, forced: Object.isExtensible !== $isExtensible }, {
    isExtensible: $isExtensible
  });

  var global$r = global$1e;

  var nativePromiseConstructor = global$r.Promise;

  var userAgent$4 = engineUserAgent;

  var engineIsIos = /(?:ipad|iphone|ipod).*applewebkit/i.test(userAgent$4);

  var classof$3 = classofRaw$1;
  var global$q = global$1e;

  var engineIsNode = classof$3(global$q.process) == 'process';

  var global$p = global$1e;
  var apply$3 = functionApply;
  var bind$6 = functionBindContext;
  var isCallable$3 = isCallable$q;
  var hasOwn$5 = hasOwnProperty_1;
  var fails$f = fails$J;
  var html = html$2;
  var arraySlice$5 = arraySlice$9;
  var createElement = documentCreateElement$2;
  var IS_IOS$1 = engineIsIos;
  var IS_NODE$2 = engineIsNode;

  var set$4 = global$p.setImmediate;
  var clear$1 = global$p.clearImmediate;
  var process$2 = global$p.process;
  var Dispatch = global$p.Dispatch;
  var Function$1 = global$p.Function;
  var MessageChannel = global$p.MessageChannel;
  var String$2 = global$p.String;
  var counter = 0;
  var queue$2 = {};
  var ONREADYSTATECHANGE = 'onreadystatechange';
  var location, defer, channel, port;

  try {
    // Deno throws a ReferenceError on `location` access without `--location` flag
    location = global$p.location;
  } catch (error) { /* empty */ }

  var run = function (id) {
    if (hasOwn$5(queue$2, id)) {
      var fn = queue$2[id];
      delete queue$2[id];
      fn();
    }
  };

  var runner = function (id) {
    return function () {
      run(id);
    };
  };

  var listener = function (event) {
    run(event.data);
  };

  var post = function (id) {
    // old engines have not location.origin
    global$p.postMessage(String$2(id), location.protocol + '//' + location.host);
  };

  // Node.js 0.9+ & IE10+ has setImmediate, otherwise:
  if (!set$4 || !clear$1) {
    set$4 = function setImmediate(fn) {
      var args = arraySlice$5(arguments, 1);
      queue$2[++counter] = function () {
        apply$3(isCallable$3(fn) ? fn : Function$1(fn), undefined, args);
      };
      defer(counter);
      return counter;
    };
    clear$1 = function clearImmediate(id) {
      delete queue$2[id];
    };
    // Node.js 0.8-
    if (IS_NODE$2) {
      defer = function (id) {
        process$2.nextTick(runner(id));
      };
    // Sphere (JS game engine) Dispatch API
    } else if (Dispatch && Dispatch.now) {
      defer = function (id) {
        Dispatch.now(runner(id));
      };
    // Browsers with MessageChannel, includes WebWorkers
    // except iOS - https://github.com/zloirock/core-js/issues/624
    } else if (MessageChannel && !IS_IOS$1) {
      channel = new MessageChannel();
      port = channel.port2;
      channel.port1.onmessage = listener;
      defer = bind$6(port.postMessage, port);
    // Browsers with postMessage, skip WebWorkers
    // IE8 has postMessage, but it's sync & typeof its postMessage is 'object'
    } else if (
      global$p.addEventListener &&
      isCallable$3(global$p.postMessage) &&
      !global$p.importScripts &&
      location && location.protocol !== 'file:' &&
      !fails$f(post)
    ) {
      defer = post;
      global$p.addEventListener('message', listener, false);
    // IE8-
    } else if (ONREADYSTATECHANGE in createElement('script')) {
      defer = function (id) {
        html.appendChild(createElement('script'))[ONREADYSTATECHANGE] = function () {
          html.removeChild(this);
          run(id);
        };
      };
    // Rest old browsers
    } else {
      defer = function (id) {
        setTimeout(runner(id), 0);
      };
    }
  }

  var task$1 = {
    set: set$4,
    clear: clear$1
  };

  var userAgent$3 = engineUserAgent;
  var global$o = global$1e;

  var engineIsIosPebble = /ipad|iphone|ipod/i.test(userAgent$3) && global$o.Pebble !== undefined;

  var userAgent$2 = engineUserAgent;

  var engineIsWebosWebkit = /web0s(?!.*chrome)/i.test(userAgent$2);

  var global$n = global$1e;
  var bind$5 = functionBindContext;
  var getOwnPropertyDescriptor$1 = objectGetOwnPropertyDescriptor.f;
  var macrotask = task$1.set;
  var IS_IOS = engineIsIos;
  var IS_IOS_PEBBLE = engineIsIosPebble;
  var IS_WEBOS_WEBKIT = engineIsWebosWebkit;
  var IS_NODE$1 = engineIsNode;

  var MutationObserver = global$n.MutationObserver || global$n.WebKitMutationObserver;
  var document$2 = global$n.document;
  var process$1 = global$n.process;
  var Promise$1 = global$n.Promise;
  // Node.js 11 shows ExperimentalWarning on getting `queueMicrotask`
  var queueMicrotaskDescriptor = getOwnPropertyDescriptor$1(global$n, 'queueMicrotask');
  var queueMicrotask = queueMicrotaskDescriptor && queueMicrotaskDescriptor.value;

  var flush, head, last, notify$1, toggle, node, promise, then;

  // modern engines have queueMicrotask method
  if (!queueMicrotask) {
    flush = function () {
      var parent, fn;
      if (IS_NODE$1 && (parent = process$1.domain)) parent.exit();
      while (head) {
        fn = head.fn;
        head = head.next;
        try {
          fn();
        } catch (error) {
          if (head) notify$1();
          else last = undefined;
          throw error;
        }
      } last = undefined;
      if (parent) parent.enter();
    };

    // browsers with MutationObserver, except iOS - https://github.com/zloirock/core-js/issues/339
    // also except WebOS Webkit https://github.com/zloirock/core-js/issues/898
    if (!IS_IOS && !IS_NODE$1 && !IS_WEBOS_WEBKIT && MutationObserver && document$2) {
      toggle = true;
      node = document$2.createTextNode('');
      new MutationObserver(flush).observe(node, { characterData: true });
      notify$1 = function () {
        node.data = toggle = !toggle;
      };
    // environments with maybe non-completely correct, but existent Promise
    } else if (!IS_IOS_PEBBLE && Promise$1 && Promise$1.resolve) {
      // Promise.resolve without an argument throws an error in LG WebOS 2
      promise = Promise$1.resolve(undefined);
      // workaround of WebKit ~ iOS Safari 10.1 bug
      promise.constructor = Promise$1;
      then = bind$5(promise.then, promise);
      notify$1 = function () {
        then(flush);
      };
    // Node.js without promises
    } else if (IS_NODE$1) {
      notify$1 = function () {
        process$1.nextTick(flush);
      };
    // for other environments - macrotask based on:
    // - setImmediate
    // - MessageChannel
    // - window.postMessag
    // - onreadystatechange
    // - setTimeout
    } else {
      // strange IE + webpack dev server bug - use .bind(global)
      macrotask = bind$5(macrotask, global$n);
      notify$1 = function () {
        macrotask(flush);
      };
    }
  }

  var microtask$1 = queueMicrotask || function (fn) {
    var task = { fn: fn, next: undefined };
    if (last) last.next = task;
    if (!head) {
      head = task;
      notify$1();
    } last = task;
  };

  var newPromiseCapability$2 = {};

  var aCallable$4 = aCallable$8;

  var PromiseCapability = function (C) {
    var resolve, reject;
    this.promise = new C(function ($$resolve, $$reject) {
      if (resolve !== undefined || reject !== undefined) throw TypeError('Bad Promise constructor');
      resolve = $$resolve;
      reject = $$reject;
    });
    this.resolve = aCallable$4(resolve);
    this.reject = aCallable$4(reject);
  };

  // `NewPromiseCapability` abstract operation
  // https://tc39.es/ecma262/#sec-newpromisecapability
  newPromiseCapability$2.f = function (C) {
    return new PromiseCapability(C);
  };

  var anObject$5 = anObject$q;
  var isObject$8 = isObject$s;
  var newPromiseCapability$1 = newPromiseCapability$2;

  var promiseResolve$1 = function (C, x) {
    anObject$5(C);
    if (isObject$8(x) && x.constructor === C) return x;
    var promiseCapability = newPromiseCapability$1.f(C);
    var resolve = promiseCapability.resolve;
    resolve(x);
    return promiseCapability.promise;
  };

  var global$m = global$1e;

  var hostReportErrors$1 = function (a, b) {
    var console = global$m.console;
    if (console && console.error) {
      arguments.length == 1 ? console.error(a) : console.error(a, b);
    }
  };

  var perform$1 = function (exec) {
    try {
      return { error: false, value: exec() };
    } catch (error) {
      return { error: true, value: error };
    }
  };

  var Queue$1 = function () {
    this.head = null;
    this.tail = null;
  };

  Queue$1.prototype = {
    add: function (item) {
      var entry = { item: item, next: null };
      if (this.head) this.tail.next = entry;
      else this.head = entry;
      this.tail = entry;
    },
    get: function () {
      var entry = this.head;
      if (entry) {
        this.head = entry.next;
        if (this.tail === entry) this.tail = null;
        return entry.item;
      }
    }
  };

  var queue$1 = Queue$1;

  var engineIsBrowser = typeof window == 'object';

  var $$j = _export;
  var global$l = global$1e;
  var getBuiltIn$1 = getBuiltIn$a;
  var call$8 = functionCall;
  var NativePromise = nativePromiseConstructor;
  var redefine$3 = redefine$e.exports;
  var redefineAll$2 = redefineAll$6;
  var setPrototypeOf$3 = objectSetPrototypeOf;
  var setToStringTag$3 = setToStringTag$9;
  var setSpecies$1 = setSpecies$3;
  var aCallable$3 = aCallable$8;
  var isCallable$2 = isCallable$q;
  var isObject$7 = isObject$s;
  var anInstance$4 = anInstance$8;
  var inspectSource = inspectSource$4;
  var iterate = iterate$4;
  var checkCorrectnessOfIteration$2 = checkCorrectnessOfIteration$4;
  var speciesConstructor$1 = speciesConstructor$3;
  var task = task$1.set;
  var microtask = microtask$1;
  var promiseResolve = promiseResolve$1;
  var hostReportErrors = hostReportErrors$1;
  var newPromiseCapabilityModule = newPromiseCapability$2;
  var perform = perform$1;
  var Queue = queue$1;
  var InternalStateModule$4 = internalState;
  var isForced = isForced_1;
  var wellKnownSymbol$4 = wellKnownSymbol$s;
  var IS_BROWSER = engineIsBrowser;
  var IS_NODE = engineIsNode;
  var V8_VERSION = engineV8Version;

  var SPECIES = wellKnownSymbol$4('species');
  var PROMISE = 'Promise';

  var getInternalState$2 = InternalStateModule$4.getterFor(PROMISE);
  var setInternalState$4 = InternalStateModule$4.set;
  var getInternalPromiseState = InternalStateModule$4.getterFor(PROMISE);
  var NativePromisePrototype = NativePromise && NativePromise.prototype;
  var PromiseConstructor = NativePromise;
  var PromisePrototype = NativePromisePrototype;
  var TypeError$5 = global$l.TypeError;
  var document$1 = global$l.document;
  var process = global$l.process;
  var newPromiseCapability = newPromiseCapabilityModule.f;
  var newGenericPromiseCapability = newPromiseCapability;

  var DISPATCH_EVENT = !!(document$1 && document$1.createEvent && global$l.dispatchEvent);
  var NATIVE_REJECTION_EVENT = isCallable$2(global$l.PromiseRejectionEvent);
  var UNHANDLED_REJECTION = 'unhandledrejection';
  var REJECTION_HANDLED = 'rejectionhandled';
  var PENDING = 0;
  var FULFILLED = 1;
  var REJECTED = 2;
  var HANDLED = 1;
  var UNHANDLED = 2;
  var SUBCLASSING = false;

  var Internal, OwnPromiseCapability, PromiseWrapper, nativeThen;

  var FORCED$6 = isForced(PROMISE, function () {
    var PROMISE_CONSTRUCTOR_SOURCE = inspectSource(PromiseConstructor);
    var GLOBAL_CORE_JS_PROMISE = PROMISE_CONSTRUCTOR_SOURCE !== String(PromiseConstructor);
    // V8 6.6 (Node 10 and Chrome 66) have a bug with resolving custom thenables
    // https://bugs.chromium.org/p/chromium/issues/detail?id=830565
    // We can't detect it synchronously, so just check versions
    if (!GLOBAL_CORE_JS_PROMISE && V8_VERSION === 66) return true;
    // We can't use @@species feature detection in V8 since it causes
    // deoptimization and performance degradation
    // https://github.com/zloirock/core-js/issues/679
    if (V8_VERSION >= 51 && /native code/.test(PROMISE_CONSTRUCTOR_SOURCE)) return false;
    // Detect correctness of subclassing with @@species support
    var promise = new PromiseConstructor(function (resolve) { resolve(1); });
    var FakePromise = function (exec) {
      exec(function () { /* empty */ }, function () { /* empty */ });
    };
    var constructor = promise.constructor = {};
    constructor[SPECIES] = FakePromise;
    SUBCLASSING = promise.then(function () { /* empty */ }) instanceof FakePromise;
    if (!SUBCLASSING) return true;
    // Unhandled rejections tracking support, NodeJS Promise without it fails @@species test
    return !GLOBAL_CORE_JS_PROMISE && IS_BROWSER && !NATIVE_REJECTION_EVENT;
  });

  var INCORRECT_ITERATION$1 = FORCED$6 || !checkCorrectnessOfIteration$2(function (iterable) {
    PromiseConstructor.all(iterable)['catch'](function () { /* empty */ });
  });

  // helpers
  var isThenable = function (it) {
    var then;
    return isObject$7(it) && isCallable$2(then = it.then) ? then : false;
  };

  var callReaction = function (reaction, state) {
    var value = state.value;
    var ok = state.state == FULFILLED;
    var handler = ok ? reaction.ok : reaction.fail;
    var resolve = reaction.resolve;
    var reject = reaction.reject;
    var domain = reaction.domain;
    var result, then, exited;
    try {
      if (handler) {
        if (!ok) {
          if (state.rejection === UNHANDLED) onHandleUnhandled(state);
          state.rejection = HANDLED;
        }
        if (handler === true) result = value;
        else {
          if (domain) domain.enter();
          result = handler(value); // can throw
          if (domain) {
            domain.exit();
            exited = true;
          }
        }
        if (result === reaction.promise) {
          reject(TypeError$5('Promise-chain cycle'));
        } else if (then = isThenable(result)) {
          call$8(then, result, resolve, reject);
        } else resolve(result);
      } else reject(value);
    } catch (error) {
      if (domain && !exited) domain.exit();
      reject(error);
    }
  };

  var notify = function (state, isReject) {
    if (state.notified) return;
    state.notified = true;
    microtask(function () {
      var reactions = state.reactions;
      var reaction;
      while (reaction = reactions.get()) {
        callReaction(reaction, state);
      }
      state.notified = false;
      if (isReject && !state.rejection) onUnhandled(state);
    });
  };

  var dispatchEvent = function (name, promise, reason) {
    var event, handler;
    if (DISPATCH_EVENT) {
      event = document$1.createEvent('Event');
      event.promise = promise;
      event.reason = reason;
      event.initEvent(name, false, true);
      global$l.dispatchEvent(event);
    } else event = { promise: promise, reason: reason };
    if (!NATIVE_REJECTION_EVENT && (handler = global$l['on' + name])) handler(event);
    else if (name === UNHANDLED_REJECTION) hostReportErrors('Unhandled promise rejection', reason);
  };

  var onUnhandled = function (state) {
    call$8(task, global$l, function () {
      var promise = state.facade;
      var value = state.value;
      var IS_UNHANDLED = isUnhandled(state);
      var result;
      if (IS_UNHANDLED) {
        result = perform(function () {
          if (IS_NODE) {
            process.emit('unhandledRejection', value, promise);
          } else dispatchEvent(UNHANDLED_REJECTION, promise, value);
        });
        // Browsers should not trigger `rejectionHandled` event if it was handled here, NodeJS - should
        state.rejection = IS_NODE || isUnhandled(state) ? UNHANDLED : HANDLED;
        if (result.error) throw result.value;
      }
    });
  };

  var isUnhandled = function (state) {
    return state.rejection !== HANDLED && !state.parent;
  };

  var onHandleUnhandled = function (state) {
    call$8(task, global$l, function () {
      var promise = state.facade;
      if (IS_NODE) {
        process.emit('rejectionHandled', promise);
      } else dispatchEvent(REJECTION_HANDLED, promise, state.value);
    });
  };

  var bind$4 = function (fn, state, unwrap) {
    return function (value) {
      fn(state, value, unwrap);
    };
  };

  var internalReject = function (state, value, unwrap) {
    if (state.done) return;
    state.done = true;
    if (unwrap) state = unwrap;
    state.value = value;
    state.state = REJECTED;
    notify(state, true);
  };

  var internalResolve = function (state, value, unwrap) {
    if (state.done) return;
    state.done = true;
    if (unwrap) state = unwrap;
    try {
      if (state.facade === value) throw TypeError$5("Promise can't be resolved itself");
      var then = isThenable(value);
      if (then) {
        microtask(function () {
          var wrapper = { done: false };
          try {
            call$8(then, value,
              bind$4(internalResolve, wrapper, state),
              bind$4(internalReject, wrapper, state)
            );
          } catch (error) {
            internalReject(wrapper, error, state);
          }
        });
      } else {
        state.value = value;
        state.state = FULFILLED;
        notify(state, false);
      }
    } catch (error) {
      internalReject({ done: false }, error, state);
    }
  };

  // constructor polyfill
  if (FORCED$6) {
    // 25.4.3.1 Promise(executor)
    PromiseConstructor = function Promise(executor) {
      anInstance$4(this, PromisePrototype);
      aCallable$3(executor);
      call$8(Internal, this);
      var state = getInternalState$2(this);
      try {
        executor(bind$4(internalResolve, state), bind$4(internalReject, state));
      } catch (error) {
        internalReject(state, error);
      }
    };
    PromisePrototype = PromiseConstructor.prototype;
    // eslint-disable-next-line no-unused-vars -- required for `.length`
    Internal = function Promise(executor) {
      setInternalState$4(this, {
        type: PROMISE,
        done: false,
        notified: false,
        parent: false,
        reactions: new Queue(),
        rejection: false,
        state: PENDING,
        value: undefined
      });
    };
    Internal.prototype = redefineAll$2(PromisePrototype, {
      // `Promise.prototype.then` method
      // https://tc39.es/ecma262/#sec-promise.prototype.then
      then: function then(onFulfilled, onRejected) {
        var state = getInternalPromiseState(this);
        var reaction = newPromiseCapability(speciesConstructor$1(this, PromiseConstructor));
        state.parent = true;
        reaction.ok = isCallable$2(onFulfilled) ? onFulfilled : true;
        reaction.fail = isCallable$2(onRejected) && onRejected;
        reaction.domain = IS_NODE ? process.domain : undefined;
        if (state.state == PENDING) state.reactions.add(reaction);
        else microtask(function () {
          callReaction(reaction, state);
        });
        return reaction.promise;
      },
      // `Promise.prototype.catch` method
      // https://tc39.es/ecma262/#sec-promise.prototype.catch
      'catch': function (onRejected) {
        return this.then(undefined, onRejected);
      }
    });
    OwnPromiseCapability = function () {
      var promise = new Internal();
      var state = getInternalState$2(promise);
      this.promise = promise;
      this.resolve = bind$4(internalResolve, state);
      this.reject = bind$4(internalReject, state);
    };
    newPromiseCapabilityModule.f = newPromiseCapability = function (C) {
      return C === PromiseConstructor || C === PromiseWrapper
        ? new OwnPromiseCapability(C)
        : newGenericPromiseCapability(C);
    };

    if (isCallable$2(NativePromise) && NativePromisePrototype !== Object.prototype) {
      nativeThen = NativePromisePrototype.then;

      if (!SUBCLASSING) {
        // make `Promise#then` return a polyfilled `Promise` for native promise-based APIs
        redefine$3(NativePromisePrototype, 'then', function then(onFulfilled, onRejected) {
          var that = this;
          return new PromiseConstructor(function (resolve, reject) {
            call$8(nativeThen, that, resolve, reject);
          }).then(onFulfilled, onRejected);
        // https://github.com/zloirock/core-js/issues/640
        }, { unsafe: true });

        // makes sure that native promise-based APIs `Promise#catch` properly works with patched `Promise#then`
        redefine$3(NativePromisePrototype, 'catch', PromisePrototype['catch'], { unsafe: true });
      }

      // make `.constructor === Promise` work for native promise-based APIs
      try {
        delete NativePromisePrototype.constructor;
      } catch (error) { /* empty */ }

      // make `instanceof Promise` work for native promise-based APIs
      if (setPrototypeOf$3) {
        setPrototypeOf$3(NativePromisePrototype, PromisePrototype);
      }
    }
  }

  $$j({ global: true, wrap: true, forced: FORCED$6 }, {
    Promise: PromiseConstructor
  });

  setToStringTag$3(PromiseConstructor, PROMISE, false);
  setSpecies$1(PROMISE);

  PromiseWrapper = getBuiltIn$1(PROMISE);

  // statics
  $$j({ target: PROMISE, stat: true, forced: FORCED$6 }, {
    // `Promise.reject` method
    // https://tc39.es/ecma262/#sec-promise.reject
    reject: function reject(r) {
      var capability = newPromiseCapability(this);
      call$8(capability.reject, undefined, r);
      return capability.promise;
    }
  });

  $$j({ target: PROMISE, stat: true, forced: FORCED$6 }, {
    // `Promise.resolve` method
    // https://tc39.es/ecma262/#sec-promise.resolve
    resolve: function resolve(x) {
      return promiseResolve(this, x);
    }
  });

  $$j({ target: PROMISE, stat: true, forced: INCORRECT_ITERATION$1 }, {
    // `Promise.all` method
    // https://tc39.es/ecma262/#sec-promise.all
    all: function all(iterable) {
      var C = this;
      var capability = newPromiseCapability(C);
      var resolve = capability.resolve;
      var reject = capability.reject;
      var result = perform(function () {
        var $promiseResolve = aCallable$3(C.resolve);
        var values = [];
        var counter = 0;
        var remaining = 1;
        iterate(iterable, function (promise) {
          var index = counter++;
          var alreadyCalled = false;
          remaining++;
          call$8($promiseResolve, C, promise).then(function (value) {
            if (alreadyCalled) return;
            alreadyCalled = true;
            values[index] = value;
            --remaining || resolve(values);
          }, reject);
        });
        --remaining || resolve(values);
      });
      if (result.error) reject(result.value);
      return capability.promise;
    },
    // `Promise.race` method
    // https://tc39.es/ecma262/#sec-promise.race
    race: function race(iterable) {
      var C = this;
      var capability = newPromiseCapability(C);
      var reject = capability.reject;
      var result = perform(function () {
        var $promiseResolve = aCallable$3(C.resolve);
        iterate(iterable, function (promise) {
          call$8($promiseResolve, C, promise).then(capability.resolve, reject);
        });
      });
      if (result.error) reject(result.value);
      return capability.promise;
    }
  });

  var $$i = _export;
  var $includes$1 = arrayIncludes.includes;
  var addToUnscopables$3 = addToUnscopables$5;

  // `Array.prototype.includes` method
  // https://tc39.es/ecma262/#sec-array.prototype.includes
  $$i({ target: 'Array', proto: true }, {
    includes: function includes(el /* , fromIndex = 0 */) {
      return $includes$1(this, el, arguments.length > 1 ? arguments[1] : undefined);
    }
  });

  // https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
  addToUnscopables$3('includes');

  var $$h = _export;
  var uncurryThis$g = functionUncurryThis;
  var notARegExp$1 = notARegexp;
  var requireObjectCoercible$5 = requireObjectCoercible$d;
  var toString$6 = toString$g;
  var correctIsRegExpLogic$1 = correctIsRegexpLogic;

  var stringIndexOf = uncurryThis$g(''.indexOf);

  // `String.prototype.includes` method
  // https://tc39.es/ecma262/#sec-string.prototype.includes
  $$h({ target: 'String', proto: true, forced: !correctIsRegExpLogic$1('includes') }, {
    includes: function includes(searchString /* , position = 0 */) {
      return !!~stringIndexOf(
        toString$6(requireObjectCoercible$5(this)),
        toString$6(notARegExp$1(searchString)),
        arguments.length > 1 ? arguments[1] : undefined
      );
    }
  });

  var arraySlice$4 = arraySliceSimple;

  var floor$6 = Math.floor;

  var mergeSort = function (array, comparefn) {
    var length = array.length;
    var middle = floor$6(length / 2);
    return length < 8 ? insertionSort(array, comparefn) : merge$1(
      array,
      mergeSort(arraySlice$4(array, 0, middle), comparefn),
      mergeSort(arraySlice$4(array, middle), comparefn),
      comparefn
    );
  };

  var insertionSort = function (array, comparefn) {
    var length = array.length;
    var i = 1;
    var element, j;

    while (i < length) {
      j = i;
      element = array[i];
      while (j && comparefn(array[j - 1], element) > 0) {
        array[j] = array[--j];
      }
      if (j !== i++) array[j] = element;
    } return array;
  };

  var merge$1 = function (array, left, right, comparefn) {
    var llength = left.length;
    var rlength = right.length;
    var lindex = 0;
    var rindex = 0;

    while (lindex < llength || rindex < rlength) {
      array[lindex + rindex] = (lindex < llength && rindex < rlength)
        ? comparefn(left[lindex], right[rindex]) <= 0 ? left[lindex++] : right[rindex++]
        : lindex < llength ? left[lindex++] : right[rindex++];
    } return array;
  };

  var arraySort$1 = mergeSort;

  var userAgent$1 = engineUserAgent;

  var firefox = userAgent$1.match(/firefox\/(\d+)/i);

  var engineFfVersion = !!firefox && +firefox[1];

  var UA = engineUserAgent;

  var engineIsIeOrEdge = /MSIE|Trident/.test(UA);

  var userAgent = engineUserAgent;

  var webkit = userAgent.match(/AppleWebKit\/(\d+)\./);

  var engineWebkitVersion = !!webkit && +webkit[1];

  var $$g = _export;
  var uncurryThis$f = functionUncurryThis;
  var aCallable$2 = aCallable$8;
  var toObject$7 = toObject$g;
  var lengthOfArrayLike$9 = lengthOfArrayLike$h;
  var toString$5 = toString$g;
  var fails$e = fails$J;
  var internalSort$1 = arraySort$1;
  var arrayMethodIsStrict$2 = arrayMethodIsStrict$4;
  var FF$1 = engineFfVersion;
  var IE_OR_EDGE$1 = engineIsIeOrEdge;
  var V8$1 = engineV8Version;
  var WEBKIT$1 = engineWebkitVersion;

  var test = [];
  var un$Sort$1 = uncurryThis$f(test.sort);
  var push$4 = uncurryThis$f(test.push);

  // IE8-
  var FAILS_ON_UNDEFINED = fails$e(function () {
    test.sort(undefined);
  });
  // V8 bug
  var FAILS_ON_NULL = fails$e(function () {
    test.sort(null);
  });
  // Old WebKit
  var STRICT_METHOD$2 = arrayMethodIsStrict$2('sort');

  var STABLE_SORT$1 = !fails$e(function () {
    // feature detection can be too slow, so check engines versions
    if (V8$1) return V8$1 < 70;
    if (FF$1 && FF$1 > 3) return;
    if (IE_OR_EDGE$1) return true;
    if (WEBKIT$1) return WEBKIT$1 < 603;

    var result = '';
    var code, chr, value, index;

    // generate an array with more 512 elements (Chakra and old V8 fails only in this case)
    for (code = 65; code < 76; code++) {
      chr = String.fromCharCode(code);

      switch (code) {
        case 66: case 69: case 70: case 72: value = 3; break;
        case 68: case 71: value = 4; break;
        default: value = 2;
      }

      for (index = 0; index < 47; index++) {
        test.push({ k: chr + index, v: value });
      }
    }

    test.sort(function (a, b) { return b.v - a.v; });

    for (index = 0; index < test.length; index++) {
      chr = test[index].k.charAt(0);
      if (result.charAt(result.length - 1) !== chr) result += chr;
    }

    return result !== 'DGBEFHACIJK';
  });

  var FORCED$5 = FAILS_ON_UNDEFINED || !FAILS_ON_NULL || !STRICT_METHOD$2 || !STABLE_SORT$1;

  var getSortCompare$1 = function (comparefn) {
    return function (x, y) {
      if (y === undefined) return -1;
      if (x === undefined) return 1;
      if (comparefn !== undefined) return +comparefn(x, y) || 0;
      return toString$5(x) > toString$5(y) ? 1 : -1;
    };
  };

  // `Array.prototype.sort` method
  // https://tc39.es/ecma262/#sec-array.prototype.sort
  $$g({ target: 'Array', proto: true, forced: FORCED$5 }, {
    sort: function sort(comparefn) {
      if (comparefn !== undefined) aCallable$2(comparefn);

      var array = toObject$7(this);

      if (STABLE_SORT$1) return comparefn === undefined ? un$Sort$1(array) : un$Sort$1(array, comparefn);

      var items = [];
      var arrayLength = lengthOfArrayLike$9(array);
      var itemsLength, index;

      for (index = 0; index < arrayLength; index++) {
        if (index in array) push$4(items, array[index]);
      }

      internalSort$1(items, getSortCompare$1(comparefn));

      itemsLength = items.length;
      index = 0;

      while (index < itemsLength) array[index] = items[index++];
      while (index < arrayLength) delete array[index++];

      return array;
    }
  });

  var $$f = _export;
  var toObject$6 = toObject$g;
  var nativeKeys = objectKeys$4;
  var fails$d = fails$J;

  var FAILS_ON_PRIMITIVES$1 = fails$d(function () { nativeKeys(1); });

  // `Object.keys` method
  // https://tc39.es/ecma262/#sec-object.keys
  $$f({ target: 'Object', stat: true, forced: FAILS_ON_PRIMITIVES$1 }, {
    keys: function keys(it) {
      return nativeKeys(toObject$6(it));
    }
  });

  var toObject$5 = toObject$g;
  var toAbsoluteIndex$2 = toAbsoluteIndex$7;
  var lengthOfArrayLike$8 = lengthOfArrayLike$h;

  // `Array.prototype.fill` method implementation
  // https://tc39.es/ecma262/#sec-array.prototype.fill
  var arrayFill$1 = function fill(value /* , start = 0, end = @length */) {
    var O = toObject$5(this);
    var length = lengthOfArrayLike$8(O);
    var argumentsLength = arguments.length;
    var index = toAbsoluteIndex$2(argumentsLength > 1 ? arguments[1] : undefined, length);
    var end = argumentsLength > 2 ? arguments[2] : undefined;
    var endPos = end === undefined ? length : toAbsoluteIndex$2(end, length);
    while (endPos > index) O[index++] = value;
    return O;
  };

  var $$e = _export;
  var fill$1 = arrayFill$1;
  var addToUnscopables$2 = addToUnscopables$5;

  // `Array.prototype.fill` method
  // https://tc39.es/ecma262/#sec-array.prototype.fill
  $$e({ target: 'Array', proto: true }, {
    fill: fill$1
  });

  // https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
  addToUnscopables$2('fill');

  var anObject$4 = anObject$q;
  var iteratorClose = iteratorClose$2;

  // call something on iterator step with safe closing on error
  var callWithSafeIterationClosing$1 = function (iterator, fn, value, ENTRIES) {
    try {
      return ENTRIES ? fn(anObject$4(value)[0], value[1]) : fn(value);
    } catch (error) {
      iteratorClose(iterator, 'throw', error);
    }
  };

  var global$k = global$1e;
  var bind$3 = functionBindContext;
  var call$7 = functionCall;
  var toObject$4 = toObject$g;
  var callWithSafeIterationClosing = callWithSafeIterationClosing$1;
  var isArrayIteratorMethod$1 = isArrayIteratorMethod$3;
  var isConstructor = isConstructor$4;
  var lengthOfArrayLike$7 = lengthOfArrayLike$h;
  var createProperty = createProperty$5;
  var getIterator$2 = getIterator$4;
  var getIteratorMethod$2 = getIteratorMethod$5;

  var Array$4 = global$k.Array;

  // `Array.from` method implementation
  // https://tc39.es/ecma262/#sec-array.from
  var arrayFrom$1 = function from(arrayLike /* , mapfn = undefined, thisArg = undefined */) {
    var O = toObject$4(arrayLike);
    var IS_CONSTRUCTOR = isConstructor(this);
    var argumentsLength = arguments.length;
    var mapfn = argumentsLength > 1 ? arguments[1] : undefined;
    var mapping = mapfn !== undefined;
    if (mapping) mapfn = bind$3(mapfn, argumentsLength > 2 ? arguments[2] : undefined);
    var iteratorMethod = getIteratorMethod$2(O);
    var index = 0;
    var length, result, step, iterator, next, value;
    // if the target is not iterable or it's an array with the default iterator - use a simple case
    if (iteratorMethod && !(this == Array$4 && isArrayIteratorMethod$1(iteratorMethod))) {
      iterator = getIterator$2(O, iteratorMethod);
      next = iterator.next;
      result = IS_CONSTRUCTOR ? new this() : [];
      for (;!(step = call$7(next, iterator)).done; index++) {
        value = mapping ? callWithSafeIterationClosing(iterator, mapfn, [step.value, index], true) : step.value;
        createProperty(result, index, value);
      }
    } else {
      length = lengthOfArrayLike$7(O);
      result = IS_CONSTRUCTOR ? new this(length) : Array$4(length);
      for (;length > index; index++) {
        value = mapping ? mapfn(O[index], index) : O[index];
        createProperty(result, index, value);
      }
    }
    result.length = index;
    return result;
  };

  var $$d = _export;
  var from = arrayFrom$1;
  var checkCorrectnessOfIteration$1 = checkCorrectnessOfIteration$4;

  var INCORRECT_ITERATION = !checkCorrectnessOfIteration$1(function (iterable) {
    // eslint-disable-next-line es/no-array-from -- required for testing
    Array.from(iterable);
  });

  // `Array.from` method
  // https://tc39.es/ecma262/#sec-array.from
  $$d({ target: 'Array', stat: true, forced: INCORRECT_ITERATION }, {
    from: from
  });

  var DESCRIPTORS$6 = descriptors;
  var FUNCTION_NAME_EXISTS = functionName.EXISTS;
  var uncurryThis$e = functionUncurryThis;
  var defineProperty$2 = objectDefineProperty.f;

  var FunctionPrototype = Function.prototype;
  var functionToString = uncurryThis$e(FunctionPrototype.toString);
  var nameRE = /function\b(?:\s|\/\*[\S\s]*?\*\/|\/\/[^\n\r]*[\n\r]+)*([^\s(/]*)/;
  var regExpExec$2 = uncurryThis$e(nameRE.exec);
  var NAME$1 = 'name';

  // Function instances `.name` property
  // https://tc39.es/ecma262/#sec-function-instances-name
  if (DESCRIPTORS$6 && !FUNCTION_NAME_EXISTS) {
    defineProperty$2(FunctionPrototype, NAME$1, {
      configurable: true,
      get: function () {
        try {
          return regExpExec$2(nameRE, functionToString(this))[1];
        } catch (error) {
          return '';
        }
      }
    });
  }

  var $$c = _export;
  var DESCRIPTORS$5 = descriptors;
  var anObject$3 = anObject$q;
  var toPropertyKey$1 = toPropertyKey$6;
  var definePropertyModule$1 = objectDefineProperty;
  var fails$c = fails$J;

  // MS Edge has broken Reflect.defineProperty - throwing instead of returning false
  var ERROR_INSTEAD_OF_FALSE = fails$c(function () {
    // eslint-disable-next-line es/no-reflect -- required for testing
    Reflect.defineProperty(definePropertyModule$1.f({}, 1, { value: 1 }), 1, { value: 2 });
  });

  // `Reflect.defineProperty` method
  // https://tc39.es/ecma262/#sec-reflect.defineproperty
  $$c({ target: 'Reflect', stat: true, forced: ERROR_INSTEAD_OF_FALSE, sham: !DESCRIPTORS$5 }, {
    defineProperty: function defineProperty(target, propertyKey, attributes) {
      anObject$3(target);
      var key = toPropertyKey$1(propertyKey);
      anObject$3(attributes);
      try {
        definePropertyModule$1.f(target, key, attributes);
        return true;
      } catch (error) {
        return false;
      }
    }
  });

  var call$6 = functionCall;
  var fixRegExpWellKnownSymbolLogic$1 = fixRegexpWellKnownSymbolLogic;
  var anObject$2 = anObject$q;
  var toLength$5 = toLength$a;
  var toString$4 = toString$g;
  var requireObjectCoercible$4 = requireObjectCoercible$d;
  var getMethod$1 = getMethod$7;
  var advanceStringIndex = advanceStringIndex$3;
  var regExpExec$1 = regexpExecAbstract;

  // @@match logic
  fixRegExpWellKnownSymbolLogic$1('match', function (MATCH, nativeMatch, maybeCallNative) {
    return [
      // `String.prototype.match` method
      // https://tc39.es/ecma262/#sec-string.prototype.match
      function match(regexp) {
        var O = requireObjectCoercible$4(this);
        var matcher = regexp == undefined ? undefined : getMethod$1(regexp, MATCH);
        return matcher ? call$6(matcher, regexp, O) : new RegExp(regexp)[MATCH](toString$4(O));
      },
      // `RegExp.prototype[@@match]` method
      // https://tc39.es/ecma262/#sec-regexp.prototype-@@match
      function (string) {
        var rx = anObject$2(this);
        var S = toString$4(string);
        var res = maybeCallNative(nativeMatch, rx, S);

        if (res.done) return res.value;

        if (!rx.global) return regExpExec$1(rx, S);

        var fullUnicode = rx.unicode;
        rx.lastIndex = 0;
        var A = [];
        var n = 0;
        var result;
        while ((result = regExpExec$1(rx, S)) !== null) {
          var matchStr = toString$4(result[0]);
          A[n] = matchStr;
          if (matchStr === '') rx.lastIndex = advanceStringIndex(S, toLength$5(rx.lastIndex), fullUnicode);
          n++;
        }
        return n === 0 ? null : A;
      }
    ];
  });

  var $$b = _export;
  var $findIndex$1 = arrayIteration.findIndex;
  var addToUnscopables$1 = addToUnscopables$5;

  var FIND_INDEX = 'findIndex';
  var SKIPS_HOLES$1 = true;

  // Shouldn't skip holes
  if (FIND_INDEX in []) Array(1)[FIND_INDEX](function () { SKIPS_HOLES$1 = false; });

  // `Array.prototype.findIndex` method
  // https://tc39.es/ecma262/#sec-array.prototype.findindex
  $$b({ target: 'Array', proto: true, forced: SKIPS_HOLES$1 }, {
    findIndex: function findIndex(callbackfn /* , that = undefined */) {
      return $findIndex$1(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
    }
  });

  // https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
  addToUnscopables$1(FIND_INDEX);

  var uncurryThis$d = functionUncurryThis;
  var requireObjectCoercible$3 = requireObjectCoercible$d;
  var toString$3 = toString$g;

  var quot = /"/g;
  var replace$3 = uncurryThis$d(''.replace);

  // `CreateHTML` abstract operation
  // https://tc39.es/ecma262/#sec-createhtml
  var createHtml = function (string, tag, attribute, value) {
    var S = toString$3(requireObjectCoercible$3(string));
    var p1 = '<' + tag;
    if (attribute !== '') p1 += ' ' + attribute + '="' + replace$3(toString$3(value), quot, '&quot;') + '"';
    return p1 + '>' + S + '</' + tag + '>';
  };

  var fails$b = fails$J;

  // check the existence of a method, lowercase
  // of a tag and escaping quotes in arguments
  var stringHtmlForced = function (METHOD_NAME) {
    return fails$b(function () {
      var test = ''[METHOD_NAME]('"');
      return test !== test.toLowerCase() || test.split('"').length > 3;
    });
  };

  var $$a = _export;
  var createHTML = createHtml;
  var forcedStringHTMLMethod = stringHtmlForced;

  // `String.prototype.anchor` method
  // https://tc39.es/ecma262/#sec-string.prototype.anchor
  $$a({ target: 'String', proto: true, forced: forcedStringHTMLMethod('anchor') }, {
    anchor: function anchor(name) {
      return createHTML(this, 'a', 'name', name);
    }
  });

  var $$9 = _export;
  var uncurryThis$c = functionUncurryThis;
  var IndexedObject$1 = indexedObject;
  var toIndexedObject$2 = toIndexedObject$b;
  var arrayMethodIsStrict$1 = arrayMethodIsStrict$4;

  var un$Join = uncurryThis$c([].join);

  var ES3_STRINGS = IndexedObject$1 != Object;
  var STRICT_METHOD$1 = arrayMethodIsStrict$1('join', ',');

  // `Array.prototype.join` method
  // https://tc39.es/ecma262/#sec-array.prototype.join
  $$9({ target: 'Array', proto: true, forced: ES3_STRINGS || !STRICT_METHOD$1 }, {
    join: function join(separator) {
      return un$Join(toIndexedObject$2(this), separator === undefined ? ',' : separator);
    }
  });

  var call$5 = functionCall;
  var fixRegExpWellKnownSymbolLogic = fixRegexpWellKnownSymbolLogic;
  var anObject$1 = anObject$q;
  var requireObjectCoercible$2 = requireObjectCoercible$d;
  var sameValue = sameValue$1;
  var toString$2 = toString$g;
  var getMethod = getMethod$7;
  var regExpExec = regexpExecAbstract;

  // @@search logic
  fixRegExpWellKnownSymbolLogic('search', function (SEARCH, nativeSearch, maybeCallNative) {
    return [
      // `String.prototype.search` method
      // https://tc39.es/ecma262/#sec-string.prototype.search
      function search(regexp) {
        var O = requireObjectCoercible$2(this);
        var searcher = regexp == undefined ? undefined : getMethod(regexp, SEARCH);
        return searcher ? call$5(searcher, regexp, O) : new RegExp(regexp)[SEARCH](toString$2(O));
      },
      // `RegExp.prototype[@@search]` method
      // https://tc39.es/ecma262/#sec-regexp.prototype-@@search
      function (string) {
        var rx = anObject$1(this);
        var S = toString$2(string);
        var res = maybeCallNative(nativeSearch, rx, S);

        if (res.done) return res.value;

        var previousLastIndex = rx.lastIndex;
        if (!sameValue(previousLastIndex, 0)) rx.lastIndex = 0;
        var result = regExpExec(rx, S);
        if (!sameValue(rx.lastIndex, previousLastIndex)) rx.lastIndex = previousLastIndex;
        return result === null ? -1 : result.index;
      }
    ];
  });

  var global$j = global$1e;
  var toIntegerOrInfinity$6 = toIntegerOrInfinity$c;
  var toString$1 = toString$g;
  var requireObjectCoercible$1 = requireObjectCoercible$d;

  var RangeError$8 = global$j.RangeError;

  // `String.prototype.repeat` method implementation
  // https://tc39.es/ecma262/#sec-string.prototype.repeat
  var stringRepeat = function repeat(count) {
    var str = toString$1(requireObjectCoercible$1(this));
    var result = '';
    var n = toIntegerOrInfinity$6(count);
    if (n < 0 || n == Infinity) throw RangeError$8('Wrong number of repetitions');
    for (;n > 0; (n >>>= 1) && (str += str)) if (n & 1) result += str;
    return result;
  };

  var $$8 = _export;
  var global$i = global$1e;
  var uncurryThis$b = functionUncurryThis;
  var toIntegerOrInfinity$5 = toIntegerOrInfinity$c;
  var thisNumberValue = thisNumberValue$2;
  var $repeat = stringRepeat;
  var fails$a = fails$J;

  var RangeError$7 = global$i.RangeError;
  var String$1 = global$i.String;
  var floor$5 = Math.floor;
  var repeat = uncurryThis$b($repeat);
  var stringSlice$2 = uncurryThis$b(''.slice);
  var un$ToFixed = uncurryThis$b(1.0.toFixed);

  var pow$2 = function (x, n, acc) {
    return n === 0 ? acc : n % 2 === 1 ? pow$2(x, n - 1, acc * x) : pow$2(x * x, n / 2, acc);
  };

  var log$1 = function (x) {
    var n = 0;
    var x2 = x;
    while (x2 >= 4096) {
      n += 12;
      x2 /= 4096;
    }
    while (x2 >= 2) {
      n += 1;
      x2 /= 2;
    } return n;
  };

  var multiply = function (data, n, c) {
    var index = -1;
    var c2 = c;
    while (++index < 6) {
      c2 += n * data[index];
      data[index] = c2 % 1e7;
      c2 = floor$5(c2 / 1e7);
    }
  };

  var divide = function (data, n) {
    var index = 6;
    var c = 0;
    while (--index >= 0) {
      c += data[index];
      data[index] = floor$5(c / n);
      c = (c % n) * 1e7;
    }
  };

  var dataToString = function (data) {
    var index = 6;
    var s = '';
    while (--index >= 0) {
      if (s !== '' || index === 0 || data[index] !== 0) {
        var t = String$1(data[index]);
        s = s === '' ? t : s + repeat('0', 7 - t.length) + t;
      }
    } return s;
  };

  var FORCED$4 = fails$a(function () {
    return un$ToFixed(0.00008, 3) !== '0.000' ||
      un$ToFixed(0.9, 0) !== '1' ||
      un$ToFixed(1.255, 2) !== '1.25' ||
      un$ToFixed(1000000000000000128.0, 0) !== '1000000000000000128';
  }) || !fails$a(function () {
    // V8 ~ Android 4.3-
    un$ToFixed({});
  });

  // `Number.prototype.toFixed` method
  // https://tc39.es/ecma262/#sec-number.prototype.tofixed
  $$8({ target: 'Number', proto: true, forced: FORCED$4 }, {
    toFixed: function toFixed(fractionDigits) {
      var number = thisNumberValue(this);
      var fractDigits = toIntegerOrInfinity$5(fractionDigits);
      var data = [0, 0, 0, 0, 0, 0];
      var sign = '';
      var result = '0';
      var e, z, j, k;

      // TODO: ES2018 increased the maximum number of fraction digits to 100, need to improve the implementation
      if (fractDigits < 0 || fractDigits > 20) throw RangeError$7('Incorrect fraction digits');
      // eslint-disable-next-line no-self-compare -- NaN check
      if (number != number) return 'NaN';
      if (number <= -1e21 || number >= 1e21) return String$1(number);
      if (number < 0) {
        sign = '-';
        number = -number;
      }
      if (number > 1e-21) {
        e = log$1(number * pow$2(2, 69, 1)) - 69;
        z = e < 0 ? number * pow$2(2, -e, 1) : number / pow$2(2, e, 1);
        z *= 0x10000000000000;
        e = 52 - e;
        if (e > 0) {
          multiply(data, 0, z);
          j = fractDigits;
          while (j >= 7) {
            multiply(data, 1e7, 0);
            j -= 7;
          }
          multiply(data, pow$2(10, j, 1), 0);
          j = e - 1;
          while (j >= 23) {
            divide(data, 1 << 23);
            j -= 23;
          }
          divide(data, 1 << j);
          multiply(data, 1, 1);
          divide(data, 2);
          result = dataToString(data);
        } else {
          multiply(data, 0, z);
          multiply(data, 1 << -e, 0);
          result = dataToString(data) + repeat('0', fractDigits);
        }
      }
      if (fractDigits > 0) {
        k = result.length;
        result = sign + (k <= fractDigits
          ? '0.' + repeat('0', fractDigits - k) + result
          : stringSlice$2(result, 0, k - fractDigits) + '.' + stringSlice$2(result, k - fractDigits));
      } else {
        result = sign + result;
      } return result;
    }
  });

  var $$7 = _export;
  var uncurryThis$a = functionUncurryThis;
  var getOwnPropertyDescriptor = objectGetOwnPropertyDescriptor.f;
  var toLength$4 = toLength$a;
  var toString = toString$g;
  var notARegExp = notARegexp;
  var requireObjectCoercible = requireObjectCoercible$d;
  var correctIsRegExpLogic = correctIsRegexpLogic;

  // eslint-disable-next-line es/no-string-prototype-endswith -- safe
  var un$EndsWith = uncurryThis$a(''.endsWith);
  var slice = uncurryThis$a(''.slice);
  var min$2 = Math.min;

  var CORRECT_IS_REGEXP_LOGIC = correctIsRegExpLogic('endsWith');
  // https://github.com/zloirock/core-js/pull/702
  var MDN_POLYFILL_BUG = !CORRECT_IS_REGEXP_LOGIC && !!function () {
    var descriptor = getOwnPropertyDescriptor(String.prototype, 'endsWith');
    return descriptor && !descriptor.writable;
  }();

  // `String.prototype.endsWith` method
  // https://tc39.es/ecma262/#sec-string.prototype.endswith
  $$7({ target: 'String', proto: true, forced: !MDN_POLYFILL_BUG && !CORRECT_IS_REGEXP_LOGIC }, {
    endsWith: function endsWith(searchString /* , endPosition = @length */) {
      var that = toString(requireObjectCoercible(this));
      notARegExp(searchString);
      var endPosition = arguments.length > 1 ? arguments[1] : undefined;
      var len = that.length;
      var end = endPosition === undefined ? len : min$2(toLength$4(endPosition), len);
      var search = toString(searchString);
      return un$EndsWith
        ? un$EndsWith(that, search, end)
        : slice(that, end - search.length, end) === search;
    }
  });

  var $$6 = _export;
  var $find$1 = arrayIteration.find;
  var addToUnscopables = addToUnscopables$5;

  var FIND = 'find';
  var SKIPS_HOLES = true;

  // Shouldn't skip holes
  if (FIND in []) Array(1)[FIND](function () { SKIPS_HOLES = false; });

  // `Array.prototype.find` method
  // https://tc39.es/ecma262/#sec-array.prototype.find
  $$6({ target: 'Array', proto: true, forced: SKIPS_HOLES }, {
    find: function find(callbackfn /* , that = undefined */) {
      return $find$1(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
    }
  });

  // https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
  addToUnscopables(FIND);

  var DESCRIPTORS$4 = descriptors;
  var uncurryThis$9 = functionUncurryThis;
  var objectKeys = objectKeys$4;
  var toIndexedObject$1 = toIndexedObject$b;
  var $propertyIsEnumerable = objectPropertyIsEnumerable.f;

  var propertyIsEnumerable = uncurryThis$9($propertyIsEnumerable);
  var push$3 = uncurryThis$9([].push);

  // `Object.{ entries, values }` methods implementation
  var createMethod$1 = function (TO_ENTRIES) {
    return function (it) {
      var O = toIndexedObject$1(it);
      var keys = objectKeys(O);
      var length = keys.length;
      var i = 0;
      var result = [];
      var key;
      while (length > i) {
        key = keys[i++];
        if (!DESCRIPTORS$4 || propertyIsEnumerable(O, key)) {
          push$3(result, TO_ENTRIES ? [key, O[key]] : O[key]);
        }
      }
      return result;
    };
  };

  var objectToArray = {
    // `Object.entries` method
    // https://tc39.es/ecma262/#sec-object.entries
    entries: createMethod$1(true),
    // `Object.values` method
    // https://tc39.es/ecma262/#sec-object.values
    values: createMethod$1(false)
  };

  var $$5 = _export;
  var $values = objectToArray.values;

  // `Object.values` method
  // https://tc39.es/ecma262/#sec-object.values
  $$5({ target: 'Object', stat: true }, {
    values: function values(O) {
      return $values(O);
    }
  });

  var $$4 = _export;
  var FREEZING = freezing;
  var fails$9 = fails$J;
  var isObject$6 = isObject$s;
  var onFreeze = internalMetadata.exports.onFreeze;

  // eslint-disable-next-line es/no-object-freeze -- safe
  var $freeze = Object.freeze;
  var FAILS_ON_PRIMITIVES = fails$9(function () { $freeze(1); });

  // `Object.freeze` method
  // https://tc39.es/ecma262/#sec-object.freeze
  $$4({ target: 'Object', stat: true, forced: FAILS_ON_PRIMITIVES, sham: !FREEZING }, {
    freeze: function freeze(it) {
      return $freeze && isObject$6(it) ? $freeze(onFreeze(it)) : it;
    }
  });

  var fails$8 = fails$J;
  var wellKnownSymbol$3 = wellKnownSymbol$s;
  var IS_PURE = isPure;

  var ITERATOR$2 = wellKnownSymbol$3('iterator');

  var nativeUrl = !fails$8(function () {
    var url = new URL('b?a=1&b=2&c=3', 'http://a');
    var searchParams = url.searchParams;
    var result = '';
    url.pathname = 'c%20d';
    searchParams.forEach(function (value, key) {
      searchParams['delete']('b');
      result += key + value;
    });
    return (IS_PURE && !url.toJSON)
      || !searchParams.sort
      || url.href !== 'http://a/c%20d?a=1&c=3'
      || searchParams.get('c') !== '3'
      || String(new URLSearchParams('?a=1')) !== 'a=1'
      || !searchParams[ITERATOR$2]
      // throws in Edge
      || new URL('https://a@b').username !== 'a'
      || new URLSearchParams(new URLSearchParams('a=b')).get('a') !== 'b'
      // not punycoded in Edge
      || new URL('http://тест').host !== 'xn--e1aybc'
      // not escaped in Chrome 62-
      || new URL('http://a#б').hash !== '#%D0%B1'
      // fails in Chrome 66-
      || result !== 'a1c3'
      // throws in Safari
      || new URL('http://x', undefined).host !== 'x';
  });

  // based on https://github.com/bestiejs/punycode.js/blob/master/punycode.js
  var global$h = global$1e;
  var uncurryThis$8 = functionUncurryThis;

  var maxInt = 2147483647; // aka. 0x7FFFFFFF or 2^31-1
  var base = 36;
  var tMin = 1;
  var tMax = 26;
  var skew = 38;
  var damp = 700;
  var initialBias = 72;
  var initialN = 128; // 0x80
  var delimiter = '-'; // '\x2D'
  var regexNonASCII = /[^\0-\u007E]/; // non-ASCII chars
  var regexSeparators = /[.\u3002\uFF0E\uFF61]/g; // RFC 3490 separators
  var OVERFLOW_ERROR = 'Overflow: input needs wider integers to process';
  var baseMinusTMin = base - tMin;

  var RangeError$6 = global$h.RangeError;
  var exec$1 = uncurryThis$8(regexSeparators.exec);
  var floor$4 = Math.floor;
  var fromCharCode = String.fromCharCode;
  var charCodeAt = uncurryThis$8(''.charCodeAt);
  var join$3 = uncurryThis$8([].join);
  var push$2 = uncurryThis$8([].push);
  var replace$2 = uncurryThis$8(''.replace);
  var split$2 = uncurryThis$8(''.split);
  var toLowerCase$1 = uncurryThis$8(''.toLowerCase);

  /**
   * Creates an array containing the numeric code points of each Unicode
   * character in the string. While JavaScript uses UCS-2 internally,
   * this function will convert a pair of surrogate halves (each of which
   * UCS-2 exposes as separate characters) into a single code point,
   * matching UTF-16.
   */
  var ucs2decode = function (string) {
    var output = [];
    var counter = 0;
    var length = string.length;
    while (counter < length) {
      var value = charCodeAt(string, counter++);
      if (value >= 0xD800 && value <= 0xDBFF && counter < length) {
        // It's a high surrogate, and there is a next character.
        var extra = charCodeAt(string, counter++);
        if ((extra & 0xFC00) == 0xDC00) { // Low surrogate.
          push$2(output, ((value & 0x3FF) << 10) + (extra & 0x3FF) + 0x10000);
        } else {
          // It's an unmatched surrogate; only append this code unit, in case the
          // next code unit is the high surrogate of a surrogate pair.
          push$2(output, value);
          counter--;
        }
      } else {
        push$2(output, value);
      }
    }
    return output;
  };

  /**
   * Converts a digit/integer into a basic code point.
   */
  var digitToBasic = function (digit) {
    //  0..25 map to ASCII a..z or A..Z
    // 26..35 map to ASCII 0..9
    return digit + 22 + 75 * (digit < 26);
  };

  /**
   * Bias adaptation function as per section 3.4 of RFC 3492.
   * https://tools.ietf.org/html/rfc3492#section-3.4
   */
  var adapt = function (delta, numPoints, firstTime) {
    var k = 0;
    delta = firstTime ? floor$4(delta / damp) : delta >> 1;
    delta += floor$4(delta / numPoints);
    while (delta > baseMinusTMin * tMax >> 1) {
      delta = floor$4(delta / baseMinusTMin);
      k += base;
    }
    return floor$4(k + (baseMinusTMin + 1) * delta / (delta + skew));
  };

  /**
   * Converts a string of Unicode symbols (e.g. a domain name label) to a
   * Punycode string of ASCII-only symbols.
   */
  var encode = function (input) {
    var output = [];

    // Convert the input in UCS-2 to an array of Unicode code points.
    input = ucs2decode(input);

    // Cache the length.
    var inputLength = input.length;

    // Initialize the state.
    var n = initialN;
    var delta = 0;
    var bias = initialBias;
    var i, currentValue;

    // Handle the basic code points.
    for (i = 0; i < input.length; i++) {
      currentValue = input[i];
      if (currentValue < 0x80) {
        push$2(output, fromCharCode(currentValue));
      }
    }

    var basicLength = output.length; // number of basic code points.
    var handledCPCount = basicLength; // number of code points that have been handled;

    // Finish the basic string with a delimiter unless it's empty.
    if (basicLength) {
      push$2(output, delimiter);
    }

    // Main encoding loop:
    while (handledCPCount < inputLength) {
      // All non-basic code points < n have been handled already. Find the next larger one:
      var m = maxInt;
      for (i = 0; i < input.length; i++) {
        currentValue = input[i];
        if (currentValue >= n && currentValue < m) {
          m = currentValue;
        }
      }

      // Increase `delta` enough to advance the decoder's <n,i> state to <m,0>, but guard against overflow.
      var handledCPCountPlusOne = handledCPCount + 1;
      if (m - n > floor$4((maxInt - delta) / handledCPCountPlusOne)) {
        throw RangeError$6(OVERFLOW_ERROR);
      }

      delta += (m - n) * handledCPCountPlusOne;
      n = m;

      for (i = 0; i < input.length; i++) {
        currentValue = input[i];
        if (currentValue < n && ++delta > maxInt) {
          throw RangeError$6(OVERFLOW_ERROR);
        }
        if (currentValue == n) {
          // Represent delta as a generalized variable-length integer.
          var q = delta;
          var k = base;
          while (true) {
            var t = k <= bias ? tMin : (k >= bias + tMax ? tMax : k - bias);
            if (q < t) break;
            var qMinusT = q - t;
            var baseMinusT = base - t;
            push$2(output, fromCharCode(digitToBasic(t + qMinusT % baseMinusT)));
            q = floor$4(qMinusT / baseMinusT);
            k += base;
          }

          push$2(output, fromCharCode(digitToBasic(q)));
          bias = adapt(delta, handledCPCountPlusOne, handledCPCount == basicLength);
          delta = 0;
          handledCPCount++;
        }
      }

      delta++;
      n++;
    }
    return join$3(output, '');
  };

  var stringPunycodeToAscii = function (input) {
    var encoded = [];
    var labels = split$2(replace$2(toLowerCase$1(input), regexSeparators, '\u002E'), '.');
    var i, label;
    for (i = 0; i < labels.length; i++) {
      label = labels[i];
      push$2(encoded, exec$1(regexNonASCII, label) ? 'xn--' + encode(label) : label);
    }
    return join$3(encoded, '.');
  };

  // TODO: in core-js@4, move /modules/ dependencies to public entries for better optimization by tools like `preset-env`

  var $$3 = _export;
  var global$g = global$1e;
  var getBuiltIn = getBuiltIn$a;
  var call$4 = functionCall;
  var uncurryThis$7 = functionUncurryThis;
  var USE_NATIVE_URL$1 = nativeUrl;
  var redefine$2 = redefine$e.exports;
  var redefineAll$1 = redefineAll$6;
  var setToStringTag$2 = setToStringTag$9;
  var createIteratorConstructor = createIteratorConstructor$2;
  var InternalStateModule$3 = internalState;
  var anInstance$3 = anInstance$8;
  var isCallable$1 = isCallable$q;
  var hasOwn$4 = hasOwnProperty_1;
  var bind$2 = functionBindContext;
  var classof$2 = classof$d;
  var anObject = anObject$q;
  var isObject$5 = isObject$s;
  var $toString$1 = toString$g;
  var create$1 = objectCreate;
  var createPropertyDescriptor$1 = createPropertyDescriptor$8;
  var getIterator$1 = getIterator$4;
  var getIteratorMethod$1 = getIteratorMethod$5;
  var wellKnownSymbol$2 = wellKnownSymbol$s;
  var arraySort = arraySort$1;

  var ITERATOR$1 = wellKnownSymbol$2('iterator');
  var URL_SEARCH_PARAMS = 'URLSearchParams';
  var URL_SEARCH_PARAMS_ITERATOR = URL_SEARCH_PARAMS + 'Iterator';
  var setInternalState$3 = InternalStateModule$3.set;
  var getInternalParamsState = InternalStateModule$3.getterFor(URL_SEARCH_PARAMS);
  var getInternalIteratorState = InternalStateModule$3.getterFor(URL_SEARCH_PARAMS_ITERATOR);

  var n$Fetch = getBuiltIn('fetch');
  var N$Request = getBuiltIn('Request');
  var Headers = getBuiltIn('Headers');
  var RequestPrototype = N$Request && N$Request.prototype;
  var HeadersPrototype = Headers && Headers.prototype;
  var RegExp$1 = global$g.RegExp;
  var TypeError$4 = global$g.TypeError;
  var decodeURIComponent = global$g.decodeURIComponent;
  var encodeURIComponent$1 = global$g.encodeURIComponent;
  var charAt$1 = uncurryThis$7(''.charAt);
  var join$2 = uncurryThis$7([].join);
  var push$1 = uncurryThis$7([].push);
  var replace$1 = uncurryThis$7(''.replace);
  var shift$1 = uncurryThis$7([].shift);
  var splice = uncurryThis$7([].splice);
  var split$1 = uncurryThis$7(''.split);
  var stringSlice$1 = uncurryThis$7(''.slice);

  var plus = /\+/g;
  var sequences = Array(4);

  var percentSequence = function (bytes) {
    return sequences[bytes - 1] || (sequences[bytes - 1] = RegExp$1('((?:%[\\da-f]{2}){' + bytes + '})', 'gi'));
  };

  var percentDecode = function (sequence) {
    try {
      return decodeURIComponent(sequence);
    } catch (error) {
      return sequence;
    }
  };

  var deserialize = function (it) {
    var result = replace$1(it, plus, ' ');
    var bytes = 4;
    try {
      return decodeURIComponent(result);
    } catch (error) {
      while (bytes) {
        result = replace$1(result, percentSequence(bytes--), percentDecode);
      }
      return result;
    }
  };

  var find = /[!'()~]|%20/g;

  var replacements = {
    '!': '%21',
    "'": '%27',
    '(': '%28',
    ')': '%29',
    '~': '%7E',
    '%20': '+'
  };

  var replacer$1 = function (match) {
    return replacements[match];
  };

  var serialize = function (it) {
    return replace$1(encodeURIComponent$1(it), find, replacer$1);
  };

  var validateArgumentsLength = function (passed, required) {
    if (passed < required) throw TypeError$4('Not enough arguments');
  };

  var URLSearchParamsIterator = createIteratorConstructor(function Iterator(params, kind) {
    setInternalState$3(this, {
      type: URL_SEARCH_PARAMS_ITERATOR,
      iterator: getIterator$1(getInternalParamsState(params).entries),
      kind: kind
    });
  }, 'Iterator', function next() {
    var state = getInternalIteratorState(this);
    var kind = state.kind;
    var step = state.iterator.next();
    var entry = step.value;
    if (!step.done) {
      step.value = kind === 'keys' ? entry.key : kind === 'values' ? entry.value : [entry.key, entry.value];
    } return step;
  }, true);

  var URLSearchParamsState = function (init) {
    this.entries = [];
    this.url = null;

    if (init !== undefined) {
      if (isObject$5(init)) this.parseObject(init);
      else this.parseQuery(typeof init == 'string' ? charAt$1(init, 0) === '?' ? stringSlice$1(init, 1) : init : $toString$1(init));
    }
  };

  URLSearchParamsState.prototype = {
    type: URL_SEARCH_PARAMS,
    bindURL: function (url) {
      this.url = url;
      this.update();
    },
    parseObject: function (object) {
      var iteratorMethod = getIteratorMethod$1(object);
      var iterator, next, step, entryIterator, entryNext, first, second;

      if (iteratorMethod) {
        iterator = getIterator$1(object, iteratorMethod);
        next = iterator.next;
        while (!(step = call$4(next, iterator)).done) {
          entryIterator = getIterator$1(anObject(step.value));
          entryNext = entryIterator.next;
          if (
            (first = call$4(entryNext, entryIterator)).done ||
            (second = call$4(entryNext, entryIterator)).done ||
            !call$4(entryNext, entryIterator).done
          ) throw TypeError$4('Expected sequence with length 2');
          push$1(this.entries, { key: $toString$1(first.value), value: $toString$1(second.value) });
        }
      } else for (var key in object) if (hasOwn$4(object, key)) {
        push$1(this.entries, { key: key, value: $toString$1(object[key]) });
      }
    },
    parseQuery: function (query) {
      if (query) {
        var attributes = split$1(query, '&');
        var index = 0;
        var attribute, entry;
        while (index < attributes.length) {
          attribute = attributes[index++];
          if (attribute.length) {
            entry = split$1(attribute, '=');
            push$1(this.entries, {
              key: deserialize(shift$1(entry)),
              value: deserialize(join$2(entry, '='))
            });
          }
        }
      }
    },
    serialize: function () {
      var entries = this.entries;
      var result = [];
      var index = 0;
      var entry;
      while (index < entries.length) {
        entry = entries[index++];
        push$1(result, serialize(entry.key) + '=' + serialize(entry.value));
      } return join$2(result, '&');
    },
    update: function () {
      this.entries.length = 0;
      this.parseQuery(this.url.query);
    },
    updateURL: function () {
      if (this.url) this.url.update();
    }
  };

  // `URLSearchParams` constructor
  // https://url.spec.whatwg.org/#interface-urlsearchparams
  var URLSearchParamsConstructor = function URLSearchParams(/* init */) {
    anInstance$3(this, URLSearchParamsPrototype);
    var init = arguments.length > 0 ? arguments[0] : undefined;
    setInternalState$3(this, new URLSearchParamsState(init));
  };

  var URLSearchParamsPrototype = URLSearchParamsConstructor.prototype;

  redefineAll$1(URLSearchParamsPrototype, {
    // `URLSearchParams.prototype.append` method
    // https://url.spec.whatwg.org/#dom-urlsearchparams-append
    append: function append(name, value) {
      validateArgumentsLength(arguments.length, 2);
      var state = getInternalParamsState(this);
      push$1(state.entries, { key: $toString$1(name), value: $toString$1(value) });
      state.updateURL();
    },
    // `URLSearchParams.prototype.delete` method
    // https://url.spec.whatwg.org/#dom-urlsearchparams-delete
    'delete': function (name) {
      validateArgumentsLength(arguments.length, 1);
      var state = getInternalParamsState(this);
      var entries = state.entries;
      var key = $toString$1(name);
      var index = 0;
      while (index < entries.length) {
        if (entries[index].key === key) splice(entries, index, 1);
        else index++;
      }
      state.updateURL();
    },
    // `URLSearchParams.prototype.get` method
    // https://url.spec.whatwg.org/#dom-urlsearchparams-get
    get: function get(name) {
      validateArgumentsLength(arguments.length, 1);
      var entries = getInternalParamsState(this).entries;
      var key = $toString$1(name);
      var index = 0;
      for (; index < entries.length; index++) {
        if (entries[index].key === key) return entries[index].value;
      }
      return null;
    },
    // `URLSearchParams.prototype.getAll` method
    // https://url.spec.whatwg.org/#dom-urlsearchparams-getall
    getAll: function getAll(name) {
      validateArgumentsLength(arguments.length, 1);
      var entries = getInternalParamsState(this).entries;
      var key = $toString$1(name);
      var result = [];
      var index = 0;
      for (; index < entries.length; index++) {
        if (entries[index].key === key) push$1(result, entries[index].value);
      }
      return result;
    },
    // `URLSearchParams.prototype.has` method
    // https://url.spec.whatwg.org/#dom-urlsearchparams-has
    has: function has(name) {
      validateArgumentsLength(arguments.length, 1);
      var entries = getInternalParamsState(this).entries;
      var key = $toString$1(name);
      var index = 0;
      while (index < entries.length) {
        if (entries[index++].key === key) return true;
      }
      return false;
    },
    // `URLSearchParams.prototype.set` method
    // https://url.spec.whatwg.org/#dom-urlsearchparams-set
    set: function set(name, value) {
      validateArgumentsLength(arguments.length, 1);
      var state = getInternalParamsState(this);
      var entries = state.entries;
      var found = false;
      var key = $toString$1(name);
      var val = $toString$1(value);
      var index = 0;
      var entry;
      for (; index < entries.length; index++) {
        entry = entries[index];
        if (entry.key === key) {
          if (found) splice(entries, index--, 1);
          else {
            found = true;
            entry.value = val;
          }
        }
      }
      if (!found) push$1(entries, { key: key, value: val });
      state.updateURL();
    },
    // `URLSearchParams.prototype.sort` method
    // https://url.spec.whatwg.org/#dom-urlsearchparams-sort
    sort: function sort() {
      var state = getInternalParamsState(this);
      arraySort(state.entries, function (a, b) {
        return a.key > b.key ? 1 : -1;
      });
      state.updateURL();
    },
    // `URLSearchParams.prototype.forEach` method
    forEach: function forEach(callback /* , thisArg */) {
      var entries = getInternalParamsState(this).entries;
      var boundFunction = bind$2(callback, arguments.length > 1 ? arguments[1] : undefined);
      var index = 0;
      var entry;
      while (index < entries.length) {
        entry = entries[index++];
        boundFunction(entry.value, entry.key, this);
      }
    },
    // `URLSearchParams.prototype.keys` method
    keys: function keys() {
      return new URLSearchParamsIterator(this, 'keys');
    },
    // `URLSearchParams.prototype.values` method
    values: function values() {
      return new URLSearchParamsIterator(this, 'values');
    },
    // `URLSearchParams.prototype.entries` method
    entries: function entries() {
      return new URLSearchParamsIterator(this, 'entries');
    }
  }, { enumerable: true });

  // `URLSearchParams.prototype[@@iterator]` method
  redefine$2(URLSearchParamsPrototype, ITERATOR$1, URLSearchParamsPrototype.entries, { name: 'entries' });

  // `URLSearchParams.prototype.toString` method
  // https://url.spec.whatwg.org/#urlsearchparams-stringification-behavior
  redefine$2(URLSearchParamsPrototype, 'toString', function toString() {
    return getInternalParamsState(this).serialize();
  }, { enumerable: true });

  setToStringTag$2(URLSearchParamsConstructor, URL_SEARCH_PARAMS);

  $$3({ global: true, forced: !USE_NATIVE_URL$1 }, {
    URLSearchParams: URLSearchParamsConstructor
  });

  // Wrap `fetch` and `Request` for correct work with polyfilled `URLSearchParams`
  if (!USE_NATIVE_URL$1 && isCallable$1(Headers)) {
    var headersHas = uncurryThis$7(HeadersPrototype.has);
    var headersSet = uncurryThis$7(HeadersPrototype.set);

    var wrapRequestOptions = function (init) {
      if (isObject$5(init)) {
        var body = init.body;
        var headers;
        if (classof$2(body) === URL_SEARCH_PARAMS) {
          headers = init.headers ? new Headers(init.headers) : new Headers();
          if (!headersHas(headers, 'content-type')) {
            headersSet(headers, 'content-type', 'application/x-www-form-urlencoded;charset=UTF-8');
          }
          return create$1(init, {
            body: createPropertyDescriptor$1(0, $toString$1(body)),
            headers: createPropertyDescriptor$1(0, headers)
          });
        }
      } return init;
    };

    if (isCallable$1(n$Fetch)) {
      $$3({ global: true, enumerable: true, forced: true }, {
        fetch: function fetch(input /* , init */) {
          return n$Fetch(input, arguments.length > 1 ? wrapRequestOptions(arguments[1]) : {});
        }
      });
    }

    if (isCallable$1(N$Request)) {
      var RequestConstructor = function Request(input /* , init */) {
        anInstance$3(this, RequestPrototype);
        return new N$Request(input, arguments.length > 1 ? wrapRequestOptions(arguments[1]) : {});
      };

      RequestPrototype.constructor = RequestConstructor;
      RequestConstructor.prototype = RequestPrototype;

      $$3({ global: true, forced: true }, {
        Request: RequestConstructor
      });
    }
  }

  var web_urlSearchParams = {
    URLSearchParams: URLSearchParamsConstructor,
    getState: getInternalParamsState
  };

  // TODO: in core-js@4, move /modules/ dependencies to public entries for better optimization by tools like `preset-env`

  var $$2 = _export;
  var DESCRIPTORS$3 = descriptors;
  var USE_NATIVE_URL = nativeUrl;
  var global$f = global$1e;
  var bind$1 = functionBindContext;
  var uncurryThis$6 = functionUncurryThis;
  var defineProperties = objectDefineProperties;
  var redefine$1 = redefine$e.exports;
  var anInstance$2 = anInstance$8;
  var hasOwn$3 = hasOwnProperty_1;
  var assign = objectAssign;
  var arrayFrom = arrayFrom$1;
  var arraySlice$3 = arraySliceSimple;
  var codeAt = stringMultibyte.codeAt;
  var toASCII = stringPunycodeToAscii;
  var $toString = toString$g;
  var setToStringTag$1 = setToStringTag$9;
  var URLSearchParamsModule = web_urlSearchParams;
  var InternalStateModule$2 = internalState;

  var setInternalState$2 = InternalStateModule$2.set;
  var getInternalURLState = InternalStateModule$2.getterFor('URL');
  var URLSearchParams$1 = URLSearchParamsModule.URLSearchParams;
  var getInternalSearchParamsState = URLSearchParamsModule.getState;

  var NativeURL = global$f.URL;
  var TypeError$3 = global$f.TypeError;
  var parseInt$1 = global$f.parseInt;
  var floor$3 = Math.floor;
  var pow$1 = Math.pow;
  var charAt = uncurryThis$6(''.charAt);
  var exec = uncurryThis$6(/./.exec);
  var join$1 = uncurryThis$6([].join);
  var numberToString = uncurryThis$6(1.0.toString);
  var pop = uncurryThis$6([].pop);
  var push = uncurryThis$6([].push);
  var replace = uncurryThis$6(''.replace);
  var shift = uncurryThis$6([].shift);
  var split = uncurryThis$6(''.split);
  var stringSlice = uncurryThis$6(''.slice);
  var toLowerCase = uncurryThis$6(''.toLowerCase);
  var unshift = uncurryThis$6([].unshift);

  var INVALID_AUTHORITY = 'Invalid authority';
  var INVALID_SCHEME = 'Invalid scheme';
  var INVALID_HOST = 'Invalid host';
  var INVALID_PORT = 'Invalid port';

  var ALPHA = /[a-z]/i;
  // eslint-disable-next-line regexp/no-obscure-range -- safe
  var ALPHANUMERIC = /[\d+-.a-z]/i;
  var DIGIT = /\d/;
  var HEX_START = /^0x/i;
  var OCT = /^[0-7]+$/;
  var DEC = /^\d+$/;
  var HEX = /^[\da-f]+$/i;
  /* eslint-disable regexp/no-control-character -- safe */
  var FORBIDDEN_HOST_CODE_POINT = /[\0\t\n\r #%/:<>?@[\\\]^|]/;
  var FORBIDDEN_HOST_CODE_POINT_EXCLUDING_PERCENT = /[\0\t\n\r #/:<>?@[\\\]^|]/;
  var LEADING_AND_TRAILING_C0_CONTROL_OR_SPACE = /^[\u0000-\u0020]+|[\u0000-\u0020]+$/g;
  var TAB_AND_NEW_LINE = /[\t\n\r]/g;
  /* eslint-enable regexp/no-control-character -- safe */
  var EOF;

  // https://url.spec.whatwg.org/#ipv4-number-parser
  var parseIPv4 = function (input) {
    var parts = split(input, '.');
    var partsLength, numbers, index, part, radix, number, ipv4;
    if (parts.length && parts[parts.length - 1] == '') {
      parts.length--;
    }
    partsLength = parts.length;
    if (partsLength > 4) return input;
    numbers = [];
    for (index = 0; index < partsLength; index++) {
      part = parts[index];
      if (part == '') return input;
      radix = 10;
      if (part.length > 1 && charAt(part, 0) == '0') {
        radix = exec(HEX_START, part) ? 16 : 8;
        part = stringSlice(part, radix == 8 ? 1 : 2);
      }
      if (part === '') {
        number = 0;
      } else {
        if (!exec(radix == 10 ? DEC : radix == 8 ? OCT : HEX, part)) return input;
        number = parseInt$1(part, radix);
      }
      push(numbers, number);
    }
    for (index = 0; index < partsLength; index++) {
      number = numbers[index];
      if (index == partsLength - 1) {
        if (number >= pow$1(256, 5 - partsLength)) return null;
      } else if (number > 255) return null;
    }
    ipv4 = pop(numbers);
    for (index = 0; index < numbers.length; index++) {
      ipv4 += numbers[index] * pow$1(256, 3 - index);
    }
    return ipv4;
  };

  // https://url.spec.whatwg.org/#concept-ipv6-parser
  // eslint-disable-next-line max-statements -- TODO
  var parseIPv6 = function (input) {
    var address = [0, 0, 0, 0, 0, 0, 0, 0];
    var pieceIndex = 0;
    var compress = null;
    var pointer = 0;
    var value, length, numbersSeen, ipv4Piece, number, swaps, swap;

    var chr = function () {
      return charAt(input, pointer);
    };

    if (chr() == ':') {
      if (charAt(input, 1) != ':') return;
      pointer += 2;
      pieceIndex++;
      compress = pieceIndex;
    }
    while (chr()) {
      if (pieceIndex == 8) return;
      if (chr() == ':') {
        if (compress !== null) return;
        pointer++;
        pieceIndex++;
        compress = pieceIndex;
        continue;
      }
      value = length = 0;
      while (length < 4 && exec(HEX, chr())) {
        value = value * 16 + parseInt$1(chr(), 16);
        pointer++;
        length++;
      }
      if (chr() == '.') {
        if (length == 0) return;
        pointer -= length;
        if (pieceIndex > 6) return;
        numbersSeen = 0;
        while (chr()) {
          ipv4Piece = null;
          if (numbersSeen > 0) {
            if (chr() == '.' && numbersSeen < 4) pointer++;
            else return;
          }
          if (!exec(DIGIT, chr())) return;
          while (exec(DIGIT, chr())) {
            number = parseInt$1(chr(), 10);
            if (ipv4Piece === null) ipv4Piece = number;
            else if (ipv4Piece == 0) return;
            else ipv4Piece = ipv4Piece * 10 + number;
            if (ipv4Piece > 255) return;
            pointer++;
          }
          address[pieceIndex] = address[pieceIndex] * 256 + ipv4Piece;
          numbersSeen++;
          if (numbersSeen == 2 || numbersSeen == 4) pieceIndex++;
        }
        if (numbersSeen != 4) return;
        break;
      } else if (chr() == ':') {
        pointer++;
        if (!chr()) return;
      } else if (chr()) return;
      address[pieceIndex++] = value;
    }
    if (compress !== null) {
      swaps = pieceIndex - compress;
      pieceIndex = 7;
      while (pieceIndex != 0 && swaps > 0) {
        swap = address[pieceIndex];
        address[pieceIndex--] = address[compress + swaps - 1];
        address[compress + --swaps] = swap;
      }
    } else if (pieceIndex != 8) return;
    return address;
  };

  var findLongestZeroSequence = function (ipv6) {
    var maxIndex = null;
    var maxLength = 1;
    var currStart = null;
    var currLength = 0;
    var index = 0;
    for (; index < 8; index++) {
      if (ipv6[index] !== 0) {
        if (currLength > maxLength) {
          maxIndex = currStart;
          maxLength = currLength;
        }
        currStart = null;
        currLength = 0;
      } else {
        if (currStart === null) currStart = index;
        ++currLength;
      }
    }
    if (currLength > maxLength) {
      maxIndex = currStart;
      maxLength = currLength;
    }
    return maxIndex;
  };

  // https://url.spec.whatwg.org/#host-serializing
  var serializeHost = function (host) {
    var result, index, compress, ignore0;
    // ipv4
    if (typeof host == 'number') {
      result = [];
      for (index = 0; index < 4; index++) {
        unshift(result, host % 256);
        host = floor$3(host / 256);
      } return join$1(result, '.');
    // ipv6
    } else if (typeof host == 'object') {
      result = '';
      compress = findLongestZeroSequence(host);
      for (index = 0; index < 8; index++) {
        if (ignore0 && host[index] === 0) continue;
        if (ignore0) ignore0 = false;
        if (compress === index) {
          result += index ? ':' : '::';
          ignore0 = true;
        } else {
          result += numberToString(host[index], 16);
          if (index < 7) result += ':';
        }
      }
      return '[' + result + ']';
    } return host;
  };

  var C0ControlPercentEncodeSet = {};
  var fragmentPercentEncodeSet = assign({}, C0ControlPercentEncodeSet, {
    ' ': 1, '"': 1, '<': 1, '>': 1, '`': 1
  });
  var pathPercentEncodeSet = assign({}, fragmentPercentEncodeSet, {
    '#': 1, '?': 1, '{': 1, '}': 1
  });
  var userinfoPercentEncodeSet = assign({}, pathPercentEncodeSet, {
    '/': 1, ':': 1, ';': 1, '=': 1, '@': 1, '[': 1, '\\': 1, ']': 1, '^': 1, '|': 1
  });

  var percentEncode = function (chr, set) {
    var code = codeAt(chr, 0);
    return code > 0x20 && code < 0x7F && !hasOwn$3(set, chr) ? chr : encodeURIComponent(chr);
  };

  // https://url.spec.whatwg.org/#special-scheme
  var specialSchemes = {
    ftp: 21,
    file: null,
    http: 80,
    https: 443,
    ws: 80,
    wss: 443
  };

  // https://url.spec.whatwg.org/#windows-drive-letter
  var isWindowsDriveLetter = function (string, normalized) {
    var second;
    return string.length == 2 && exec(ALPHA, charAt(string, 0))
      && ((second = charAt(string, 1)) == ':' || (!normalized && second == '|'));
  };

  // https://url.spec.whatwg.org/#start-with-a-windows-drive-letter
  var startsWithWindowsDriveLetter = function (string) {
    var third;
    return string.length > 1 && isWindowsDriveLetter(stringSlice(string, 0, 2)) && (
      string.length == 2 ||
      ((third = charAt(string, 2)) === '/' || third === '\\' || third === '?' || third === '#')
    );
  };

  // https://url.spec.whatwg.org/#single-dot-path-segment
  var isSingleDot = function (segment) {
    return segment === '.' || toLowerCase(segment) === '%2e';
  };

  // https://url.spec.whatwg.org/#double-dot-path-segment
  var isDoubleDot = function (segment) {
    segment = toLowerCase(segment);
    return segment === '..' || segment === '%2e.' || segment === '.%2e' || segment === '%2e%2e';
  };

  // States:
  var SCHEME_START = {};
  var SCHEME = {};
  var NO_SCHEME = {};
  var SPECIAL_RELATIVE_OR_AUTHORITY = {};
  var PATH_OR_AUTHORITY = {};
  var RELATIVE = {};
  var RELATIVE_SLASH = {};
  var SPECIAL_AUTHORITY_SLASHES = {};
  var SPECIAL_AUTHORITY_IGNORE_SLASHES = {};
  var AUTHORITY = {};
  var HOST = {};
  var HOSTNAME = {};
  var PORT = {};
  var FILE = {};
  var FILE_SLASH = {};
  var FILE_HOST = {};
  var PATH_START = {};
  var PATH = {};
  var CANNOT_BE_A_BASE_URL_PATH = {};
  var QUERY = {};
  var FRAGMENT = {};

  var URLState = function (url, isBase, base) {
    var urlString = $toString(url);
    var baseState, failure, searchParams;
    if (isBase) {
      failure = this.parse(urlString);
      if (failure) throw TypeError$3(failure);
      this.searchParams = null;
    } else {
      if (base !== undefined) baseState = new URLState(base, true);
      failure = this.parse(urlString, null, baseState);
      if (failure) throw TypeError$3(failure);
      searchParams = getInternalSearchParamsState(new URLSearchParams$1());
      searchParams.bindURL(this);
      this.searchParams = searchParams;
    }
  };

  URLState.prototype = {
    type: 'URL',
    // https://url.spec.whatwg.org/#url-parsing
    // eslint-disable-next-line max-statements -- TODO
    parse: function (input, stateOverride, base) {
      var url = this;
      var state = stateOverride || SCHEME_START;
      var pointer = 0;
      var buffer = '';
      var seenAt = false;
      var seenBracket = false;
      var seenPasswordToken = false;
      var codePoints, chr, bufferCodePoints, failure;

      input = $toString(input);

      if (!stateOverride) {
        url.scheme = '';
        url.username = '';
        url.password = '';
        url.host = null;
        url.port = null;
        url.path = [];
        url.query = null;
        url.fragment = null;
        url.cannotBeABaseURL = false;
        input = replace(input, LEADING_AND_TRAILING_C0_CONTROL_OR_SPACE, '');
      }

      input = replace(input, TAB_AND_NEW_LINE, '');

      codePoints = arrayFrom(input);

      while (pointer <= codePoints.length) {
        chr = codePoints[pointer];
        switch (state) {
          case SCHEME_START:
            if (chr && exec(ALPHA, chr)) {
              buffer += toLowerCase(chr);
              state = SCHEME;
            } else if (!stateOverride) {
              state = NO_SCHEME;
              continue;
            } else return INVALID_SCHEME;
            break;

          case SCHEME:
            if (chr && (exec(ALPHANUMERIC, chr) || chr == '+' || chr == '-' || chr == '.')) {
              buffer += toLowerCase(chr);
            } else if (chr == ':') {
              if (stateOverride && (
                (url.isSpecial() != hasOwn$3(specialSchemes, buffer)) ||
                (buffer == 'file' && (url.includesCredentials() || url.port !== null)) ||
                (url.scheme == 'file' && !url.host)
              )) return;
              url.scheme = buffer;
              if (stateOverride) {
                if (url.isSpecial() && specialSchemes[url.scheme] == url.port) url.port = null;
                return;
              }
              buffer = '';
              if (url.scheme == 'file') {
                state = FILE;
              } else if (url.isSpecial() && base && base.scheme == url.scheme) {
                state = SPECIAL_RELATIVE_OR_AUTHORITY;
              } else if (url.isSpecial()) {
                state = SPECIAL_AUTHORITY_SLASHES;
              } else if (codePoints[pointer + 1] == '/') {
                state = PATH_OR_AUTHORITY;
                pointer++;
              } else {
                url.cannotBeABaseURL = true;
                push(url.path, '');
                state = CANNOT_BE_A_BASE_URL_PATH;
              }
            } else if (!stateOverride) {
              buffer = '';
              state = NO_SCHEME;
              pointer = 0;
              continue;
            } else return INVALID_SCHEME;
            break;

          case NO_SCHEME:
            if (!base || (base.cannotBeABaseURL && chr != '#')) return INVALID_SCHEME;
            if (base.cannotBeABaseURL && chr == '#') {
              url.scheme = base.scheme;
              url.path = arraySlice$3(base.path);
              url.query = base.query;
              url.fragment = '';
              url.cannotBeABaseURL = true;
              state = FRAGMENT;
              break;
            }
            state = base.scheme == 'file' ? FILE : RELATIVE;
            continue;

          case SPECIAL_RELATIVE_OR_AUTHORITY:
            if (chr == '/' && codePoints[pointer + 1] == '/') {
              state = SPECIAL_AUTHORITY_IGNORE_SLASHES;
              pointer++;
            } else {
              state = RELATIVE;
              continue;
            } break;

          case PATH_OR_AUTHORITY:
            if (chr == '/') {
              state = AUTHORITY;
              break;
            } else {
              state = PATH;
              continue;
            }

          case RELATIVE:
            url.scheme = base.scheme;
            if (chr == EOF) {
              url.username = base.username;
              url.password = base.password;
              url.host = base.host;
              url.port = base.port;
              url.path = arraySlice$3(base.path);
              url.query = base.query;
            } else if (chr == '/' || (chr == '\\' && url.isSpecial())) {
              state = RELATIVE_SLASH;
            } else if (chr == '?') {
              url.username = base.username;
              url.password = base.password;
              url.host = base.host;
              url.port = base.port;
              url.path = arraySlice$3(base.path);
              url.query = '';
              state = QUERY;
            } else if (chr == '#') {
              url.username = base.username;
              url.password = base.password;
              url.host = base.host;
              url.port = base.port;
              url.path = arraySlice$3(base.path);
              url.query = base.query;
              url.fragment = '';
              state = FRAGMENT;
            } else {
              url.username = base.username;
              url.password = base.password;
              url.host = base.host;
              url.port = base.port;
              url.path = arraySlice$3(base.path);
              url.path.length--;
              state = PATH;
              continue;
            } break;

          case RELATIVE_SLASH:
            if (url.isSpecial() && (chr == '/' || chr == '\\')) {
              state = SPECIAL_AUTHORITY_IGNORE_SLASHES;
            } else if (chr == '/') {
              state = AUTHORITY;
            } else {
              url.username = base.username;
              url.password = base.password;
              url.host = base.host;
              url.port = base.port;
              state = PATH;
              continue;
            } break;

          case SPECIAL_AUTHORITY_SLASHES:
            state = SPECIAL_AUTHORITY_IGNORE_SLASHES;
            if (chr != '/' || charAt(buffer, pointer + 1) != '/') continue;
            pointer++;
            break;

          case SPECIAL_AUTHORITY_IGNORE_SLASHES:
            if (chr != '/' && chr != '\\') {
              state = AUTHORITY;
              continue;
            } break;

          case AUTHORITY:
            if (chr == '@') {
              if (seenAt) buffer = '%40' + buffer;
              seenAt = true;
              bufferCodePoints = arrayFrom(buffer);
              for (var i = 0; i < bufferCodePoints.length; i++) {
                var codePoint = bufferCodePoints[i];
                if (codePoint == ':' && !seenPasswordToken) {
                  seenPasswordToken = true;
                  continue;
                }
                var encodedCodePoints = percentEncode(codePoint, userinfoPercentEncodeSet);
                if (seenPasswordToken) url.password += encodedCodePoints;
                else url.username += encodedCodePoints;
              }
              buffer = '';
            } else if (
              chr == EOF || chr == '/' || chr == '?' || chr == '#' ||
              (chr == '\\' && url.isSpecial())
            ) {
              if (seenAt && buffer == '') return INVALID_AUTHORITY;
              pointer -= arrayFrom(buffer).length + 1;
              buffer = '';
              state = HOST;
            } else buffer += chr;
            break;

          case HOST:
          case HOSTNAME:
            if (stateOverride && url.scheme == 'file') {
              state = FILE_HOST;
              continue;
            } else if (chr == ':' && !seenBracket) {
              if (buffer == '') return INVALID_HOST;
              failure = url.parseHost(buffer);
              if (failure) return failure;
              buffer = '';
              state = PORT;
              if (stateOverride == HOSTNAME) return;
            } else if (
              chr == EOF || chr == '/' || chr == '?' || chr == '#' ||
              (chr == '\\' && url.isSpecial())
            ) {
              if (url.isSpecial() && buffer == '') return INVALID_HOST;
              if (stateOverride && buffer == '' && (url.includesCredentials() || url.port !== null)) return;
              failure = url.parseHost(buffer);
              if (failure) return failure;
              buffer = '';
              state = PATH_START;
              if (stateOverride) return;
              continue;
            } else {
              if (chr == '[') seenBracket = true;
              else if (chr == ']') seenBracket = false;
              buffer += chr;
            } break;

          case PORT:
            if (exec(DIGIT, chr)) {
              buffer += chr;
            } else if (
              chr == EOF || chr == '/' || chr == '?' || chr == '#' ||
              (chr == '\\' && url.isSpecial()) ||
              stateOverride
            ) {
              if (buffer != '') {
                var port = parseInt$1(buffer, 10);
                if (port > 0xFFFF) return INVALID_PORT;
                url.port = (url.isSpecial() && port === specialSchemes[url.scheme]) ? null : port;
                buffer = '';
              }
              if (stateOverride) return;
              state = PATH_START;
              continue;
            } else return INVALID_PORT;
            break;

          case FILE:
            url.scheme = 'file';
            if (chr == '/' || chr == '\\') state = FILE_SLASH;
            else if (base && base.scheme == 'file') {
              if (chr == EOF) {
                url.host = base.host;
                url.path = arraySlice$3(base.path);
                url.query = base.query;
              } else if (chr == '?') {
                url.host = base.host;
                url.path = arraySlice$3(base.path);
                url.query = '';
                state = QUERY;
              } else if (chr == '#') {
                url.host = base.host;
                url.path = arraySlice$3(base.path);
                url.query = base.query;
                url.fragment = '';
                state = FRAGMENT;
              } else {
                if (!startsWithWindowsDriveLetter(join$1(arraySlice$3(codePoints, pointer), ''))) {
                  url.host = base.host;
                  url.path = arraySlice$3(base.path);
                  url.shortenPath();
                }
                state = PATH;
                continue;
              }
            } else {
              state = PATH;
              continue;
            } break;

          case FILE_SLASH:
            if (chr == '/' || chr == '\\') {
              state = FILE_HOST;
              break;
            }
            if (base && base.scheme == 'file' && !startsWithWindowsDriveLetter(join$1(arraySlice$3(codePoints, pointer), ''))) {
              if (isWindowsDriveLetter(base.path[0], true)) push(url.path, base.path[0]);
              else url.host = base.host;
            }
            state = PATH;
            continue;

          case FILE_HOST:
            if (chr == EOF || chr == '/' || chr == '\\' || chr == '?' || chr == '#') {
              if (!stateOverride && isWindowsDriveLetter(buffer)) {
                state = PATH;
              } else if (buffer == '') {
                url.host = '';
                if (stateOverride) return;
                state = PATH_START;
              } else {
                failure = url.parseHost(buffer);
                if (failure) return failure;
                if (url.host == 'localhost') url.host = '';
                if (stateOverride) return;
                buffer = '';
                state = PATH_START;
              } continue;
            } else buffer += chr;
            break;

          case PATH_START:
            if (url.isSpecial()) {
              state = PATH;
              if (chr != '/' && chr != '\\') continue;
            } else if (!stateOverride && chr == '?') {
              url.query = '';
              state = QUERY;
            } else if (!stateOverride && chr == '#') {
              url.fragment = '';
              state = FRAGMENT;
            } else if (chr != EOF) {
              state = PATH;
              if (chr != '/') continue;
            } break;

          case PATH:
            if (
              chr == EOF || chr == '/' ||
              (chr == '\\' && url.isSpecial()) ||
              (!stateOverride && (chr == '?' || chr == '#'))
            ) {
              if (isDoubleDot(buffer)) {
                url.shortenPath();
                if (chr != '/' && !(chr == '\\' && url.isSpecial())) {
                  push(url.path, '');
                }
              } else if (isSingleDot(buffer)) {
                if (chr != '/' && !(chr == '\\' && url.isSpecial())) {
                  push(url.path, '');
                }
              } else {
                if (url.scheme == 'file' && !url.path.length && isWindowsDriveLetter(buffer)) {
                  if (url.host) url.host = '';
                  buffer = charAt(buffer, 0) + ':'; // normalize windows drive letter
                }
                push(url.path, buffer);
              }
              buffer = '';
              if (url.scheme == 'file' && (chr == EOF || chr == '?' || chr == '#')) {
                while (url.path.length > 1 && url.path[0] === '') {
                  shift(url.path);
                }
              }
              if (chr == '?') {
                url.query = '';
                state = QUERY;
              } else if (chr == '#') {
                url.fragment = '';
                state = FRAGMENT;
              }
            } else {
              buffer += percentEncode(chr, pathPercentEncodeSet);
            } break;

          case CANNOT_BE_A_BASE_URL_PATH:
            if (chr == '?') {
              url.query = '';
              state = QUERY;
            } else if (chr == '#') {
              url.fragment = '';
              state = FRAGMENT;
            } else if (chr != EOF) {
              url.path[0] += percentEncode(chr, C0ControlPercentEncodeSet);
            } break;

          case QUERY:
            if (!stateOverride && chr == '#') {
              url.fragment = '';
              state = FRAGMENT;
            } else if (chr != EOF) {
              if (chr == "'" && url.isSpecial()) url.query += '%27';
              else if (chr == '#') url.query += '%23';
              else url.query += percentEncode(chr, C0ControlPercentEncodeSet);
            } break;

          case FRAGMENT:
            if (chr != EOF) url.fragment += percentEncode(chr, fragmentPercentEncodeSet);
            break;
        }

        pointer++;
      }
    },
    // https://url.spec.whatwg.org/#host-parsing
    parseHost: function (input) {
      var result, codePoints, index;
      if (charAt(input, 0) == '[') {
        if (charAt(input, input.length - 1) != ']') return INVALID_HOST;
        result = parseIPv6(stringSlice(input, 1, -1));
        if (!result) return INVALID_HOST;
        this.host = result;
      // opaque host
      } else if (!this.isSpecial()) {
        if (exec(FORBIDDEN_HOST_CODE_POINT_EXCLUDING_PERCENT, input)) return INVALID_HOST;
        result = '';
        codePoints = arrayFrom(input);
        for (index = 0; index < codePoints.length; index++) {
          result += percentEncode(codePoints[index], C0ControlPercentEncodeSet);
        }
        this.host = result;
      } else {
        input = toASCII(input);
        if (exec(FORBIDDEN_HOST_CODE_POINT, input)) return INVALID_HOST;
        result = parseIPv4(input);
        if (result === null) return INVALID_HOST;
        this.host = result;
      }
    },
    // https://url.spec.whatwg.org/#cannot-have-a-username-password-port
    cannotHaveUsernamePasswordPort: function () {
      return !this.host || this.cannotBeABaseURL || this.scheme == 'file';
    },
    // https://url.spec.whatwg.org/#include-credentials
    includesCredentials: function () {
      return this.username != '' || this.password != '';
    },
    // https://url.spec.whatwg.org/#is-special
    isSpecial: function () {
      return hasOwn$3(specialSchemes, this.scheme);
    },
    // https://url.spec.whatwg.org/#shorten-a-urls-path
    shortenPath: function () {
      var path = this.path;
      var pathSize = path.length;
      if (pathSize && (this.scheme != 'file' || pathSize != 1 || !isWindowsDriveLetter(path[0], true))) {
        path.length--;
      }
    },
    // https://url.spec.whatwg.org/#concept-url-serializer
    serialize: function () {
      var url = this;
      var scheme = url.scheme;
      var username = url.username;
      var password = url.password;
      var host = url.host;
      var port = url.port;
      var path = url.path;
      var query = url.query;
      var fragment = url.fragment;
      var output = scheme + ':';
      if (host !== null) {
        output += '//';
        if (url.includesCredentials()) {
          output += username + (password ? ':' + password : '') + '@';
        }
        output += serializeHost(host);
        if (port !== null) output += ':' + port;
      } else if (scheme == 'file') output += '//';
      output += url.cannotBeABaseURL ? path[0] : path.length ? '/' + join$1(path, '/') : '';
      if (query !== null) output += '?' + query;
      if (fragment !== null) output += '#' + fragment;
      return output;
    },
    // https://url.spec.whatwg.org/#dom-url-href
    setHref: function (href) {
      var failure = this.parse(href);
      if (failure) throw TypeError$3(failure);
      this.searchParams.update();
    },
    // https://url.spec.whatwg.org/#dom-url-origin
    getOrigin: function () {
      var scheme = this.scheme;
      var port = this.port;
      if (scheme == 'blob') try {
        return new URLConstructor(scheme.path[0]).origin;
      } catch (error) {
        return 'null';
      }
      if (scheme == 'file' || !this.isSpecial()) return 'null';
      return scheme + '://' + serializeHost(this.host) + (port !== null ? ':' + port : '');
    },
    // https://url.spec.whatwg.org/#dom-url-protocol
    getProtocol: function () {
      return this.scheme + ':';
    },
    setProtocol: function (protocol) {
      this.parse($toString(protocol) + ':', SCHEME_START);
    },
    // https://url.spec.whatwg.org/#dom-url-username
    getUsername: function () {
      return this.username;
    },
    setUsername: function (username) {
      var codePoints = arrayFrom($toString(username));
      if (this.cannotHaveUsernamePasswordPort()) return;
      this.username = '';
      for (var i = 0; i < codePoints.length; i++) {
        this.username += percentEncode(codePoints[i], userinfoPercentEncodeSet);
      }
    },
    // https://url.spec.whatwg.org/#dom-url-password
    getPassword: function () {
      return this.password;
    },
    setPassword: function (password) {
      var codePoints = arrayFrom($toString(password));
      if (this.cannotHaveUsernamePasswordPort()) return;
      this.password = '';
      for (var i = 0; i < codePoints.length; i++) {
        this.password += percentEncode(codePoints[i], userinfoPercentEncodeSet);
      }
    },
    // https://url.spec.whatwg.org/#dom-url-host
    getHost: function () {
      var host = this.host;
      var port = this.port;
      return host === null ? ''
        : port === null ? serializeHost(host)
        : serializeHost(host) + ':' + port;
    },
    setHost: function (host) {
      if (this.cannotBeABaseURL) return;
      this.parse(host, HOST);
    },
    // https://url.spec.whatwg.org/#dom-url-hostname
    getHostname: function () {
      var host = this.host;
      return host === null ? '' : serializeHost(host);
    },
    setHostname: function (hostname) {
      if (this.cannotBeABaseURL) return;
      this.parse(hostname, HOSTNAME);
    },
    // https://url.spec.whatwg.org/#dom-url-port
    getPort: function () {
      var port = this.port;
      return port === null ? '' : $toString(port);
    },
    setPort: function (port) {
      if (this.cannotHaveUsernamePasswordPort()) return;
      port = $toString(port);
      if (port == '') this.port = null;
      else this.parse(port, PORT);
    },
    // https://url.spec.whatwg.org/#dom-url-pathname
    getPathname: function () {
      var path = this.path;
      return this.cannotBeABaseURL ? path[0] : path.length ? '/' + join$1(path, '/') : '';
    },
    setPathname: function (pathname) {
      if (this.cannotBeABaseURL) return;
      this.path = [];
      this.parse(pathname, PATH_START);
    },
    // https://url.spec.whatwg.org/#dom-url-search
    getSearch: function () {
      var query = this.query;
      return query ? '?' + query : '';
    },
    setSearch: function (search) {
      search = $toString(search);
      if (search == '') {
        this.query = null;
      } else {
        if ('?' == charAt(search, 0)) search = stringSlice(search, 1);
        this.query = '';
        this.parse(search, QUERY);
      }
      this.searchParams.update();
    },
    // https://url.spec.whatwg.org/#dom-url-searchparams
    getSearchParams: function () {
      return this.searchParams.facade;
    },
    // https://url.spec.whatwg.org/#dom-url-hash
    getHash: function () {
      var fragment = this.fragment;
      return fragment ? '#' + fragment : '';
    },
    setHash: function (hash) {
      hash = $toString(hash);
      if (hash == '') {
        this.fragment = null;
        return;
      }
      if ('#' == charAt(hash, 0)) hash = stringSlice(hash, 1);
      this.fragment = '';
      this.parse(hash, FRAGMENT);
    },
    update: function () {
      this.query = this.searchParams.serialize() || null;
    }
  };

  // `URL` constructor
  // https://url.spec.whatwg.org/#url-class
  var URLConstructor = function URL(url /* , base */) {
    var that = anInstance$2(this, URLPrototype);
    var base = arguments.length > 1 ? arguments[1] : undefined;
    var state = setInternalState$2(that, new URLState(url, false, base));
    if (!DESCRIPTORS$3) {
      that.href = state.serialize();
      that.origin = state.getOrigin();
      that.protocol = state.getProtocol();
      that.username = state.getUsername();
      that.password = state.getPassword();
      that.host = state.getHost();
      that.hostname = state.getHostname();
      that.port = state.getPort();
      that.pathname = state.getPathname();
      that.search = state.getSearch();
      that.searchParams = state.getSearchParams();
      that.hash = state.getHash();
    }
  };

  var URLPrototype = URLConstructor.prototype;

  var accessorDescriptor = function (getter, setter) {
    return {
      get: function () {
        return getInternalURLState(this)[getter]();
      },
      set: setter && function (value) {
        return getInternalURLState(this)[setter](value);
      },
      configurable: true,
      enumerable: true
    };
  };

  if (DESCRIPTORS$3) {
    defineProperties(URLPrototype, {
      // `URL.prototype.href` accessors pair
      // https://url.spec.whatwg.org/#dom-url-href
      href: accessorDescriptor('serialize', 'setHref'),
      // `URL.prototype.origin` getter
      // https://url.spec.whatwg.org/#dom-url-origin
      origin: accessorDescriptor('getOrigin'),
      // `URL.prototype.protocol` accessors pair
      // https://url.spec.whatwg.org/#dom-url-protocol
      protocol: accessorDescriptor('getProtocol', 'setProtocol'),
      // `URL.prototype.username` accessors pair
      // https://url.spec.whatwg.org/#dom-url-username
      username: accessorDescriptor('getUsername', 'setUsername'),
      // `URL.prototype.password` accessors pair
      // https://url.spec.whatwg.org/#dom-url-password
      password: accessorDescriptor('getPassword', 'setPassword'),
      // `URL.prototype.host` accessors pair
      // https://url.spec.whatwg.org/#dom-url-host
      host: accessorDescriptor('getHost', 'setHost'),
      // `URL.prototype.hostname` accessors pair
      // https://url.spec.whatwg.org/#dom-url-hostname
      hostname: accessorDescriptor('getHostname', 'setHostname'),
      // `URL.prototype.port` accessors pair
      // https://url.spec.whatwg.org/#dom-url-port
      port: accessorDescriptor('getPort', 'setPort'),
      // `URL.prototype.pathname` accessors pair
      // https://url.spec.whatwg.org/#dom-url-pathname
      pathname: accessorDescriptor('getPathname', 'setPathname'),
      // `URL.prototype.search` accessors pair
      // https://url.spec.whatwg.org/#dom-url-search
      search: accessorDescriptor('getSearch', 'setSearch'),
      // `URL.prototype.searchParams` getter
      // https://url.spec.whatwg.org/#dom-url-searchparams
      searchParams: accessorDescriptor('getSearchParams'),
      // `URL.prototype.hash` accessors pair
      // https://url.spec.whatwg.org/#dom-url-hash
      hash: accessorDescriptor('getHash', 'setHash')
    });
  }

  // `URL.prototype.toJSON` method
  // https://url.spec.whatwg.org/#dom-url-tojson
  redefine$1(URLPrototype, 'toJSON', function toJSON() {
    return getInternalURLState(this).serialize();
  }, { enumerable: true });

  // `URL.prototype.toString` method
  // https://url.spec.whatwg.org/#URL-stringification-behavior
  redefine$1(URLPrototype, 'toString', function toString() {
    return getInternalURLState(this).serialize();
  }, { enumerable: true });

  if (NativeURL) {
    var nativeCreateObjectURL = NativeURL.createObjectURL;
    var nativeRevokeObjectURL = NativeURL.revokeObjectURL;
    // `URL.createObjectURL` method
    // https://developer.mozilla.org/en-US/docs/Web/API/URL/createObjectURL
    if (nativeCreateObjectURL) redefine$1(URLConstructor, 'createObjectURL', bind$1(nativeCreateObjectURL, NativeURL));
    // `URL.revokeObjectURL` method
    // https://developer.mozilla.org/en-US/docs/Web/API/URL/revokeObjectURL
    if (nativeRevokeObjectURL) redefine$1(URLConstructor, 'revokeObjectURL', bind$1(nativeRevokeObjectURL, NativeURL));
  }

  setToStringTag$1(URLConstructor, 'URL');

  $$2({ global: true, forced: !USE_NATIVE_URL, sham: !DESCRIPTORS$3 }, {
    URL: URLConstructor
  });

  var typedArrayConstructor = {exports: {}};

  // eslint-disable-next-line es/no-typed-arrays -- safe
  var arrayBufferNative = typeof ArrayBuffer != 'undefined' && typeof DataView != 'undefined';

  var NATIVE_ARRAY_BUFFER$1 = arrayBufferNative;
  var DESCRIPTORS$2 = descriptors;
  var global$e = global$1e;
  var isCallable = isCallable$q;
  var isObject$4 = isObject$s;
  var hasOwn$2 = hasOwnProperty_1;
  var classof$1 = classof$d;
  var tryToString = tryToString$5;
  var createNonEnumerableProperty$2 = createNonEnumerableProperty$a;
  var redefine = redefine$e.exports;
  var defineProperty$1 = objectDefineProperty.f;
  var isPrototypeOf$1 = objectIsPrototypeOf;
  var getPrototypeOf$1 = objectGetPrototypeOf$1;
  var setPrototypeOf$2 = objectSetPrototypeOf;
  var wellKnownSymbol$1 = wellKnownSymbol$s;
  var uid$2 = uid$7;

  var Int8Array$3 = global$e.Int8Array;
  var Int8ArrayPrototype = Int8Array$3 && Int8Array$3.prototype;
  var Uint8ClampedArray = global$e.Uint8ClampedArray;
  var Uint8ClampedArrayPrototype = Uint8ClampedArray && Uint8ClampedArray.prototype;
  var TypedArray$1 = Int8Array$3 && getPrototypeOf$1(Int8Array$3);
  var TypedArrayPrototype$2 = Int8ArrayPrototype && getPrototypeOf$1(Int8ArrayPrototype);
  var ObjectPrototype$1 = Object.prototype;
  var TypeError$2 = global$e.TypeError;

  var TO_STRING_TAG = wellKnownSymbol$1('toStringTag');
  var TYPED_ARRAY_TAG$1 = uid$2('TYPED_ARRAY_TAG');
  var TYPED_ARRAY_CONSTRUCTOR$2 = uid$2('TYPED_ARRAY_CONSTRUCTOR');
  // Fixing native typed arrays in Opera Presto crashes the browser, see #595
  var NATIVE_ARRAY_BUFFER_VIEWS$2 = NATIVE_ARRAY_BUFFER$1 && !!setPrototypeOf$2 && classof$1(global$e.opera) !== 'Opera';
  var TYPED_ARRAY_TAG_REQUIRED = false;
  var NAME, Constructor, Prototype;

  var TypedArrayConstructorsList = {
    Int8Array: 1,
    Uint8Array: 1,
    Uint8ClampedArray: 1,
    Int16Array: 2,
    Uint16Array: 2,
    Int32Array: 4,
    Uint32Array: 4,
    Float32Array: 4,
    Float64Array: 8
  };

  var BigIntArrayConstructorsList = {
    BigInt64Array: 8,
    BigUint64Array: 8
  };

  var isView = function isView(it) {
    if (!isObject$4(it)) return false;
    var klass = classof$1(it);
    return klass === 'DataView'
      || hasOwn$2(TypedArrayConstructorsList, klass)
      || hasOwn$2(BigIntArrayConstructorsList, klass);
  };

  var isTypedArray$1 = function (it) {
    if (!isObject$4(it)) return false;
    var klass = classof$1(it);
    return hasOwn$2(TypedArrayConstructorsList, klass)
      || hasOwn$2(BigIntArrayConstructorsList, klass);
  };

  var aTypedArray$n = function (it) {
    if (isTypedArray$1(it)) return it;
    throw TypeError$2('Target is not a typed array');
  };

  var aTypedArrayConstructor$3 = function (C) {
    if (isCallable(C) && (!setPrototypeOf$2 || isPrototypeOf$1(TypedArray$1, C))) return C;
    throw TypeError$2(tryToString(C) + ' is not a typed array constructor');
  };

  var exportTypedArrayMethod$o = function (KEY, property, forced, options) {
    if (!DESCRIPTORS$2) return;
    if (forced) for (var ARRAY in TypedArrayConstructorsList) {
      var TypedArrayConstructor = global$e[ARRAY];
      if (TypedArrayConstructor && hasOwn$2(TypedArrayConstructor.prototype, KEY)) try {
        delete TypedArrayConstructor.prototype[KEY];
      } catch (error) { /* empty */ }
    }
    if (!TypedArrayPrototype$2[KEY] || forced) {
      redefine(TypedArrayPrototype$2, KEY, forced ? property
        : NATIVE_ARRAY_BUFFER_VIEWS$2 && Int8ArrayPrototype[KEY] || property, options);
    }
  };

  var exportTypedArrayStaticMethod = function (KEY, property, forced) {
    var ARRAY, TypedArrayConstructor;
    if (!DESCRIPTORS$2) return;
    if (setPrototypeOf$2) {
      if (forced) for (ARRAY in TypedArrayConstructorsList) {
        TypedArrayConstructor = global$e[ARRAY];
        if (TypedArrayConstructor && hasOwn$2(TypedArrayConstructor, KEY)) try {
          delete TypedArrayConstructor[KEY];
        } catch (error) { /* empty */ }
      }
      if (!TypedArray$1[KEY] || forced) {
        // V8 ~ Chrome 49-50 `%TypedArray%` methods are non-writable non-configurable
        try {
          return redefine(TypedArray$1, KEY, forced ? property : NATIVE_ARRAY_BUFFER_VIEWS$2 && TypedArray$1[KEY] || property);
        } catch (error) { /* empty */ }
      } else return;
    }
    for (ARRAY in TypedArrayConstructorsList) {
      TypedArrayConstructor = global$e[ARRAY];
      if (TypedArrayConstructor && (!TypedArrayConstructor[KEY] || forced)) {
        redefine(TypedArrayConstructor, KEY, property);
      }
    }
  };

  for (NAME in TypedArrayConstructorsList) {
    Constructor = global$e[NAME];
    Prototype = Constructor && Constructor.prototype;
    if (Prototype) createNonEnumerableProperty$2(Prototype, TYPED_ARRAY_CONSTRUCTOR$2, Constructor);
    else NATIVE_ARRAY_BUFFER_VIEWS$2 = false;
  }

  for (NAME in BigIntArrayConstructorsList) {
    Constructor = global$e[NAME];
    Prototype = Constructor && Constructor.prototype;
    if (Prototype) createNonEnumerableProperty$2(Prototype, TYPED_ARRAY_CONSTRUCTOR$2, Constructor);
  }

  // WebKit bug - typed arrays constructors prototype is Object.prototype
  if (!NATIVE_ARRAY_BUFFER_VIEWS$2 || !isCallable(TypedArray$1) || TypedArray$1 === Function.prototype) {
    // eslint-disable-next-line no-shadow -- safe
    TypedArray$1 = function TypedArray() {
      throw TypeError$2('Incorrect invocation');
    };
    if (NATIVE_ARRAY_BUFFER_VIEWS$2) for (NAME in TypedArrayConstructorsList) {
      if (global$e[NAME]) setPrototypeOf$2(global$e[NAME], TypedArray$1);
    }
  }

  if (!NATIVE_ARRAY_BUFFER_VIEWS$2 || !TypedArrayPrototype$2 || TypedArrayPrototype$2 === ObjectPrototype$1) {
    TypedArrayPrototype$2 = TypedArray$1.prototype;
    if (NATIVE_ARRAY_BUFFER_VIEWS$2) for (NAME in TypedArrayConstructorsList) {
      if (global$e[NAME]) setPrototypeOf$2(global$e[NAME].prototype, TypedArrayPrototype$2);
    }
  }

  // WebKit bug - one more object in Uint8ClampedArray prototype chain
  if (NATIVE_ARRAY_BUFFER_VIEWS$2 && getPrototypeOf$1(Uint8ClampedArrayPrototype) !== TypedArrayPrototype$2) {
    setPrototypeOf$2(Uint8ClampedArrayPrototype, TypedArrayPrototype$2);
  }

  if (DESCRIPTORS$2 && !hasOwn$2(TypedArrayPrototype$2, TO_STRING_TAG)) {
    TYPED_ARRAY_TAG_REQUIRED = true;
    defineProperty$1(TypedArrayPrototype$2, TO_STRING_TAG, { get: function () {
      return isObject$4(this) ? this[TYPED_ARRAY_TAG$1] : undefined;
    } });
    for (NAME in TypedArrayConstructorsList) if (global$e[NAME]) {
      createNonEnumerableProperty$2(global$e[NAME], TYPED_ARRAY_TAG$1, NAME);
    }
  }

  var arrayBufferViewCore = {
    NATIVE_ARRAY_BUFFER_VIEWS: NATIVE_ARRAY_BUFFER_VIEWS$2,
    TYPED_ARRAY_CONSTRUCTOR: TYPED_ARRAY_CONSTRUCTOR$2,
    TYPED_ARRAY_TAG: TYPED_ARRAY_TAG_REQUIRED && TYPED_ARRAY_TAG$1,
    aTypedArray: aTypedArray$n,
    aTypedArrayConstructor: aTypedArrayConstructor$3,
    exportTypedArrayMethod: exportTypedArrayMethod$o,
    exportTypedArrayStaticMethod: exportTypedArrayStaticMethod,
    isView: isView,
    isTypedArray: isTypedArray$1,
    TypedArray: TypedArray$1,
    TypedArrayPrototype: TypedArrayPrototype$2
  };

  /* eslint-disable no-new -- required for testing */

  var global$d = global$1e;
  var fails$7 = fails$J;
  var checkCorrectnessOfIteration = checkCorrectnessOfIteration$4;
  var NATIVE_ARRAY_BUFFER_VIEWS$1 = arrayBufferViewCore.NATIVE_ARRAY_BUFFER_VIEWS;

  var ArrayBuffer$2 = global$d.ArrayBuffer;
  var Int8Array$2 = global$d.Int8Array;

  var typedArrayConstructorsRequireWrappers = !NATIVE_ARRAY_BUFFER_VIEWS$1 || !fails$7(function () {
    Int8Array$2(1);
  }) || !fails$7(function () {
    new Int8Array$2(-1);
  }) || !checkCorrectnessOfIteration(function (iterable) {
    new Int8Array$2();
    new Int8Array$2(null);
    new Int8Array$2(1.5);
    new Int8Array$2(iterable);
  }, true) || fails$7(function () {
    // Safari (11+) bug - a reason why even Safari 13 should load a typed array polyfill
    return new Int8Array$2(new ArrayBuffer$2(2), 1, undefined).length !== 1;
  });

  var global$c = global$1e;
  var toIntegerOrInfinity$4 = toIntegerOrInfinity$c;
  var toLength$3 = toLength$a;

  var RangeError$5 = global$c.RangeError;

  // `ToIndex` abstract operation
  // https://tc39.es/ecma262/#sec-toindex
  var toIndex$2 = function (it) {
    if (it === undefined) return 0;
    var number = toIntegerOrInfinity$4(it);
    var length = toLength$3(number);
    if (number !== length) throw RangeError$5('Wrong length or index');
    return length;
  };

  // IEEE754 conversions based on https://github.com/feross/ieee754
  var global$b = global$1e;

  var Array$3 = global$b.Array;
  var abs = Math.abs;
  var pow = Math.pow;
  var floor$2 = Math.floor;
  var log = Math.log;
  var LN2 = Math.LN2;

  var pack = function (number, mantissaLength, bytes) {
    var buffer = Array$3(bytes);
    var exponentLength = bytes * 8 - mantissaLength - 1;
    var eMax = (1 << exponentLength) - 1;
    var eBias = eMax >> 1;
    var rt = mantissaLength === 23 ? pow(2, -24) - pow(2, -77) : 0;
    var sign = number < 0 || number === 0 && 1 / number < 0 ? 1 : 0;
    var index = 0;
    var exponent, mantissa, c;
    number = abs(number);
    // eslint-disable-next-line no-self-compare -- NaN check
    if (number != number || number === Infinity) {
      // eslint-disable-next-line no-self-compare -- NaN check
      mantissa = number != number ? 1 : 0;
      exponent = eMax;
    } else {
      exponent = floor$2(log(number) / LN2);
      c = pow(2, -exponent);
      if (number * c < 1) {
        exponent--;
        c *= 2;
      }
      if (exponent + eBias >= 1) {
        number += rt / c;
      } else {
        number += rt * pow(2, 1 - eBias);
      }
      if (number * c >= 2) {
        exponent++;
        c /= 2;
      }
      if (exponent + eBias >= eMax) {
        mantissa = 0;
        exponent = eMax;
      } else if (exponent + eBias >= 1) {
        mantissa = (number * c - 1) * pow(2, mantissaLength);
        exponent = exponent + eBias;
      } else {
        mantissa = number * pow(2, eBias - 1) * pow(2, mantissaLength);
        exponent = 0;
      }
    }
    while (mantissaLength >= 8) {
      buffer[index++] = mantissa & 255;
      mantissa /= 256;
      mantissaLength -= 8;
    }
    exponent = exponent << mantissaLength | mantissa;
    exponentLength += mantissaLength;
    while (exponentLength > 0) {
      buffer[index++] = exponent & 255;
      exponent /= 256;
      exponentLength -= 8;
    }
    buffer[--index] |= sign * 128;
    return buffer;
  };

  var unpack = function (buffer, mantissaLength) {
    var bytes = buffer.length;
    var exponentLength = bytes * 8 - mantissaLength - 1;
    var eMax = (1 << exponentLength) - 1;
    var eBias = eMax >> 1;
    var nBits = exponentLength - 7;
    var index = bytes - 1;
    var sign = buffer[index--];
    var exponent = sign & 127;
    var mantissa;
    sign >>= 7;
    while (nBits > 0) {
      exponent = exponent * 256 + buffer[index--];
      nBits -= 8;
    }
    mantissa = exponent & (1 << -nBits) - 1;
    exponent >>= -nBits;
    nBits += mantissaLength;
    while (nBits > 0) {
      mantissa = mantissa * 256 + buffer[index--];
      nBits -= 8;
    }
    if (exponent === 0) {
      exponent = 1 - eBias;
    } else if (exponent === eMax) {
      return mantissa ? NaN : sign ? -Infinity : Infinity;
    } else {
      mantissa = mantissa + pow(2, mantissaLength);
      exponent = exponent - eBias;
    } return (sign ? -1 : 1) * mantissa * pow(2, exponent - mantissaLength);
  };

  var ieee754 = {
    pack: pack,
    unpack: unpack
  };

  var global$a = global$1e;
  var uncurryThis$5 = functionUncurryThis;
  var DESCRIPTORS$1 = descriptors;
  var NATIVE_ARRAY_BUFFER = arrayBufferNative;
  var FunctionName = functionName;
  var createNonEnumerableProperty$1 = createNonEnumerableProperty$a;
  var redefineAll = redefineAll$6;
  var fails$6 = fails$J;
  var anInstance$1 = anInstance$8;
  var toIntegerOrInfinity$3 = toIntegerOrInfinity$c;
  var toLength$2 = toLength$a;
  var toIndex$1 = toIndex$2;
  var IEEE754 = ieee754;
  var getPrototypeOf = objectGetPrototypeOf$1;
  var setPrototypeOf$1 = objectSetPrototypeOf;
  var getOwnPropertyNames$1 = objectGetOwnPropertyNames.f;
  var defineProperty = objectDefineProperty.f;
  var arrayFill = arrayFill$1;
  var arraySlice$2 = arraySliceSimple;
  var setToStringTag = setToStringTag$9;
  var InternalStateModule$1 = internalState;

  var PROPER_FUNCTION_NAME = FunctionName.PROPER;
  var CONFIGURABLE_FUNCTION_NAME = FunctionName.CONFIGURABLE;
  var getInternalState$1 = InternalStateModule$1.get;
  var setInternalState$1 = InternalStateModule$1.set;
  var ARRAY_BUFFER = 'ArrayBuffer';
  var DATA_VIEW = 'DataView';
  var PROTOTYPE = 'prototype';
  var WRONG_LENGTH$1 = 'Wrong length';
  var WRONG_INDEX = 'Wrong index';
  var NativeArrayBuffer = global$a[ARRAY_BUFFER];
  var $ArrayBuffer = NativeArrayBuffer;
  var ArrayBufferPrototype$1 = $ArrayBuffer && $ArrayBuffer[PROTOTYPE];
  var $DataView = global$a[DATA_VIEW];
  var DataViewPrototype = $DataView && $DataView[PROTOTYPE];
  var ObjectPrototype = Object.prototype;
  var Array$2 = global$a.Array;
  var RangeError$4 = global$a.RangeError;
  var fill = uncurryThis$5(arrayFill);
  var reverse = uncurryThis$5([].reverse);

  var packIEEE754 = IEEE754.pack;
  var unpackIEEE754 = IEEE754.unpack;

  var packInt8 = function (number) {
    return [number & 0xFF];
  };

  var packInt16 = function (number) {
    return [number & 0xFF, number >> 8 & 0xFF];
  };

  var packInt32 = function (number) {
    return [number & 0xFF, number >> 8 & 0xFF, number >> 16 & 0xFF, number >> 24 & 0xFF];
  };

  var unpackInt32 = function (buffer) {
    return buffer[3] << 24 | buffer[2] << 16 | buffer[1] << 8 | buffer[0];
  };

  var packFloat32 = function (number) {
    return packIEEE754(number, 23, 4);
  };

  var packFloat64 = function (number) {
    return packIEEE754(number, 52, 8);
  };

  var addGetter$1 = function (Constructor, key) {
    defineProperty(Constructor[PROTOTYPE], key, { get: function () { return getInternalState$1(this)[key]; } });
  };

  var get$2 = function (view, count, index, isLittleEndian) {
    var intIndex = toIndex$1(index);
    var store = getInternalState$1(view);
    if (intIndex + count > store.byteLength) throw RangeError$4(WRONG_INDEX);
    var bytes = getInternalState$1(store.buffer).bytes;
    var start = intIndex + store.byteOffset;
    var pack = arraySlice$2(bytes, start, start + count);
    return isLittleEndian ? pack : reverse(pack);
  };

  var set$3 = function (view, count, index, conversion, value, isLittleEndian) {
    var intIndex = toIndex$1(index);
    var store = getInternalState$1(view);
    if (intIndex + count > store.byteLength) throw RangeError$4(WRONG_INDEX);
    var bytes = getInternalState$1(store.buffer).bytes;
    var start = intIndex + store.byteOffset;
    var pack = conversion(+value);
    for (var i = 0; i < count; i++) bytes[start + i] = pack[isLittleEndian ? i : count - i - 1];
  };

  if (!NATIVE_ARRAY_BUFFER) {
    $ArrayBuffer = function ArrayBuffer(length) {
      anInstance$1(this, ArrayBufferPrototype$1);
      var byteLength = toIndex$1(length);
      setInternalState$1(this, {
        bytes: fill(Array$2(byteLength), 0),
        byteLength: byteLength
      });
      if (!DESCRIPTORS$1) this.byteLength = byteLength;
    };

    ArrayBufferPrototype$1 = $ArrayBuffer[PROTOTYPE];

    $DataView = function DataView(buffer, byteOffset, byteLength) {
      anInstance$1(this, DataViewPrototype);
      anInstance$1(buffer, ArrayBufferPrototype$1);
      var bufferLength = getInternalState$1(buffer).byteLength;
      var offset = toIntegerOrInfinity$3(byteOffset);
      if (offset < 0 || offset > bufferLength) throw RangeError$4('Wrong offset');
      byteLength = byteLength === undefined ? bufferLength - offset : toLength$2(byteLength);
      if (offset + byteLength > bufferLength) throw RangeError$4(WRONG_LENGTH$1);
      setInternalState$1(this, {
        buffer: buffer,
        byteLength: byteLength,
        byteOffset: offset
      });
      if (!DESCRIPTORS$1) {
        this.buffer = buffer;
        this.byteLength = byteLength;
        this.byteOffset = offset;
      }
    };

    DataViewPrototype = $DataView[PROTOTYPE];

    if (DESCRIPTORS$1) {
      addGetter$1($ArrayBuffer, 'byteLength');
      addGetter$1($DataView, 'buffer');
      addGetter$1($DataView, 'byteLength');
      addGetter$1($DataView, 'byteOffset');
    }

    redefineAll(DataViewPrototype, {
      getInt8: function getInt8(byteOffset) {
        return get$2(this, 1, byteOffset)[0] << 24 >> 24;
      },
      getUint8: function getUint8(byteOffset) {
        return get$2(this, 1, byteOffset)[0];
      },
      getInt16: function getInt16(byteOffset /* , littleEndian */) {
        var bytes = get$2(this, 2, byteOffset, arguments.length > 1 ? arguments[1] : undefined);
        return (bytes[1] << 8 | bytes[0]) << 16 >> 16;
      },
      getUint16: function getUint16(byteOffset /* , littleEndian */) {
        var bytes = get$2(this, 2, byteOffset, arguments.length > 1 ? arguments[1] : undefined);
        return bytes[1] << 8 | bytes[0];
      },
      getInt32: function getInt32(byteOffset /* , littleEndian */) {
        return unpackInt32(get$2(this, 4, byteOffset, arguments.length > 1 ? arguments[1] : undefined));
      },
      getUint32: function getUint32(byteOffset /* , littleEndian */) {
        return unpackInt32(get$2(this, 4, byteOffset, arguments.length > 1 ? arguments[1] : undefined)) >>> 0;
      },
      getFloat32: function getFloat32(byteOffset /* , littleEndian */) {
        return unpackIEEE754(get$2(this, 4, byteOffset, arguments.length > 1 ? arguments[1] : undefined), 23);
      },
      getFloat64: function getFloat64(byteOffset /* , littleEndian */) {
        return unpackIEEE754(get$2(this, 8, byteOffset, arguments.length > 1 ? arguments[1] : undefined), 52);
      },
      setInt8: function setInt8(byteOffset, value) {
        set$3(this, 1, byteOffset, packInt8, value);
      },
      setUint8: function setUint8(byteOffset, value) {
        set$3(this, 1, byteOffset, packInt8, value);
      },
      setInt16: function setInt16(byteOffset, value /* , littleEndian */) {
        set$3(this, 2, byteOffset, packInt16, value, arguments.length > 2 ? arguments[2] : undefined);
      },
      setUint16: function setUint16(byteOffset, value /* , littleEndian */) {
        set$3(this, 2, byteOffset, packInt16, value, arguments.length > 2 ? arguments[2] : undefined);
      },
      setInt32: function setInt32(byteOffset, value /* , littleEndian */) {
        set$3(this, 4, byteOffset, packInt32, value, arguments.length > 2 ? arguments[2] : undefined);
      },
      setUint32: function setUint32(byteOffset, value /* , littleEndian */) {
        set$3(this, 4, byteOffset, packInt32, value, arguments.length > 2 ? arguments[2] : undefined);
      },
      setFloat32: function setFloat32(byteOffset, value /* , littleEndian */) {
        set$3(this, 4, byteOffset, packFloat32, value, arguments.length > 2 ? arguments[2] : undefined);
      },
      setFloat64: function setFloat64(byteOffset, value /* , littleEndian */) {
        set$3(this, 8, byteOffset, packFloat64, value, arguments.length > 2 ? arguments[2] : undefined);
      }
    });
  } else {
    var INCORRECT_ARRAY_BUFFER_NAME = PROPER_FUNCTION_NAME && NativeArrayBuffer.name !== ARRAY_BUFFER;
    /* eslint-disable no-new -- required for testing */
    if (!fails$6(function () {
      NativeArrayBuffer(1);
    }) || !fails$6(function () {
      new NativeArrayBuffer(-1);
    }) || fails$6(function () {
      new NativeArrayBuffer();
      new NativeArrayBuffer(1.5);
      new NativeArrayBuffer(NaN);
      return INCORRECT_ARRAY_BUFFER_NAME && !CONFIGURABLE_FUNCTION_NAME;
    })) {
    /* eslint-enable no-new -- required for testing */
      $ArrayBuffer = function ArrayBuffer(length) {
        anInstance$1(this, ArrayBufferPrototype$1);
        return new NativeArrayBuffer(toIndex$1(length));
      };

      $ArrayBuffer[PROTOTYPE] = ArrayBufferPrototype$1;

      for (var keys$1 = getOwnPropertyNames$1(NativeArrayBuffer), j = 0, key; keys$1.length > j;) {
        if (!((key = keys$1[j++]) in $ArrayBuffer)) {
          createNonEnumerableProperty$1($ArrayBuffer, key, NativeArrayBuffer[key]);
        }
      }

      ArrayBufferPrototype$1.constructor = $ArrayBuffer;
    } else if (INCORRECT_ARRAY_BUFFER_NAME && CONFIGURABLE_FUNCTION_NAME) {
      createNonEnumerableProperty$1(NativeArrayBuffer, 'name', ARRAY_BUFFER);
    }

    // WebKit bug - the same parent prototype for typed arrays and data view
    if (setPrototypeOf$1 && getPrototypeOf(DataViewPrototype) !== ObjectPrototype) {
      setPrototypeOf$1(DataViewPrototype, ObjectPrototype);
    }

    // iOS Safari 7.x bug
    var testView = new $DataView(new $ArrayBuffer(2));
    var $setInt8 = uncurryThis$5(DataViewPrototype.setInt8);
    testView.setInt8(0, 2147483648);
    testView.setInt8(1, 2147483649);
    if (testView.getInt8(0) || !testView.getInt8(1)) redefineAll(DataViewPrototype, {
      setInt8: function setInt8(byteOffset, value) {
        $setInt8(this, byteOffset, value << 24 >> 24);
      },
      setUint8: function setUint8(byteOffset, value) {
        $setInt8(this, byteOffset, value << 24 >> 24);
      }
    }, { unsafe: true });
  }

  setToStringTag($ArrayBuffer, ARRAY_BUFFER);
  setToStringTag($DataView, DATA_VIEW);

  var arrayBuffer = {
    ArrayBuffer: $ArrayBuffer,
    DataView: $DataView
  };

  var isObject$3 = isObject$s;

  var floor$1 = Math.floor;

  // `IsIntegralNumber` abstract operation
  // https://tc39.es/ecma262/#sec-isintegralnumber
  // eslint-disable-next-line es/no-number-isinteger -- safe
  var isIntegralNumber$1 = Number.isInteger || function isInteger(it) {
    return !isObject$3(it) && isFinite(it) && floor$1(it) === it;
  };

  var global$9 = global$1e;
  var toIntegerOrInfinity$2 = toIntegerOrInfinity$c;

  var RangeError$3 = global$9.RangeError;

  var toPositiveInteger$1 = function (it) {
    var result = toIntegerOrInfinity$2(it);
    if (result < 0) throw RangeError$3("The argument can't be less than 0");
    return result;
  };

  var global$8 = global$1e;
  var toPositiveInteger = toPositiveInteger$1;

  var RangeError$2 = global$8.RangeError;

  var toOffset$2 = function (it, BYTES) {
    var offset = toPositiveInteger(it);
    if (offset % BYTES) throw RangeError$2('Wrong offset');
    return offset;
  };

  var bind = functionBindContext;
  var call$3 = functionCall;
  var aConstructor = aConstructor$2;
  var toObject$3 = toObject$g;
  var lengthOfArrayLike$6 = lengthOfArrayLike$h;
  var getIterator = getIterator$4;
  var getIteratorMethod = getIteratorMethod$5;
  var isArrayIteratorMethod = isArrayIteratorMethod$3;
  var aTypedArrayConstructor$2 = arrayBufferViewCore.aTypedArrayConstructor;

  var typedArrayFrom$1 = function from(source /* , mapfn, thisArg */) {
    var C = aConstructor(this);
    var O = toObject$3(source);
    var argumentsLength = arguments.length;
    var mapfn = argumentsLength > 1 ? arguments[1] : undefined;
    var mapping = mapfn !== undefined;
    var iteratorMethod = getIteratorMethod(O);
    var i, length, result, step, iterator, next;
    if (iteratorMethod && !isArrayIteratorMethod(iteratorMethod)) {
      iterator = getIterator(O, iteratorMethod);
      next = iterator.next;
      O = [];
      while (!(step = call$3(next, iterator)).done) {
        O.push(step.value);
      }
    }
    if (mapping && argumentsLength > 2) {
      mapfn = bind(mapfn, arguments[2]);
    }
    length = lengthOfArrayLike$6(O);
    result = new (aTypedArrayConstructor$2(C))(length);
    for (i = 0; length > i; i++) {
      result[i] = mapping ? mapfn(O[i], i) : O[i];
    }
    return result;
  };

  var $$1 = _export;
  var global$7 = global$1e;
  var call$2 = functionCall;
  var DESCRIPTORS = descriptors;
  var TYPED_ARRAYS_CONSTRUCTORS_REQUIRES_WRAPPERS = typedArrayConstructorsRequireWrappers;
  var ArrayBufferViewCore$o = arrayBufferViewCore;
  var ArrayBufferModule = arrayBuffer;
  var anInstance = anInstance$8;
  var createPropertyDescriptor = createPropertyDescriptor$8;
  var createNonEnumerableProperty = createNonEnumerableProperty$a;
  var isIntegralNumber = isIntegralNumber$1;
  var toLength$1 = toLength$a;
  var toIndex = toIndex$2;
  var toOffset$1 = toOffset$2;
  var toPropertyKey = toPropertyKey$6;
  var hasOwn$1 = hasOwnProperty_1;
  var classof = classof$d;
  var isObject$2 = isObject$s;
  var isSymbol$1 = isSymbol$6;
  var create = objectCreate;
  var isPrototypeOf = objectIsPrototypeOf;
  var setPrototypeOf = objectSetPrototypeOf;
  var getOwnPropertyNames = objectGetOwnPropertyNames.f;
  var typedArrayFrom = typedArrayFrom$1;
  var forEach = arrayIteration.forEach;
  var setSpecies = setSpecies$3;
  var definePropertyModule = objectDefineProperty;
  var getOwnPropertyDescriptorModule = objectGetOwnPropertyDescriptor;
  var InternalStateModule = internalState;
  var inheritIfRequired = inheritIfRequired$3;

  var getInternalState = InternalStateModule.get;
  var setInternalState = InternalStateModule.set;
  var nativeDefineProperty = definePropertyModule.f;
  var nativeGetOwnPropertyDescriptor = getOwnPropertyDescriptorModule.f;
  var round = Math.round;
  var RangeError$1 = global$7.RangeError;
  var ArrayBuffer$1 = ArrayBufferModule.ArrayBuffer;
  var ArrayBufferPrototype = ArrayBuffer$1.prototype;
  var DataView$1 = ArrayBufferModule.DataView;
  var NATIVE_ARRAY_BUFFER_VIEWS = ArrayBufferViewCore$o.NATIVE_ARRAY_BUFFER_VIEWS;
  var TYPED_ARRAY_CONSTRUCTOR$1 = ArrayBufferViewCore$o.TYPED_ARRAY_CONSTRUCTOR;
  var TYPED_ARRAY_TAG = ArrayBufferViewCore$o.TYPED_ARRAY_TAG;
  var TypedArray = ArrayBufferViewCore$o.TypedArray;
  var TypedArrayPrototype$1 = ArrayBufferViewCore$o.TypedArrayPrototype;
  var aTypedArrayConstructor$1 = ArrayBufferViewCore$o.aTypedArrayConstructor;
  var isTypedArray = ArrayBufferViewCore$o.isTypedArray;
  var BYTES_PER_ELEMENT = 'BYTES_PER_ELEMENT';
  var WRONG_LENGTH = 'Wrong length';

  var fromList = function (C, list) {
    aTypedArrayConstructor$1(C);
    var index = 0;
    var length = list.length;
    var result = new C(length);
    while (length > index) result[index] = list[index++];
    return result;
  };

  var addGetter = function (it, key) {
    nativeDefineProperty(it, key, { get: function () {
      return getInternalState(this)[key];
    } });
  };

  var isArrayBuffer = function (it) {
    var klass;
    return isPrototypeOf(ArrayBufferPrototype, it) || (klass = classof(it)) == 'ArrayBuffer' || klass == 'SharedArrayBuffer';
  };

  var isTypedArrayIndex = function (target, key) {
    return isTypedArray(target)
      && !isSymbol$1(key)
      && key in target
      && isIntegralNumber(+key)
      && key >= 0;
  };

  var wrappedGetOwnPropertyDescriptor = function getOwnPropertyDescriptor(target, key) {
    key = toPropertyKey(key);
    return isTypedArrayIndex(target, key)
      ? createPropertyDescriptor(2, target[key])
      : nativeGetOwnPropertyDescriptor(target, key);
  };

  var wrappedDefineProperty = function defineProperty(target, key, descriptor) {
    key = toPropertyKey(key);
    if (isTypedArrayIndex(target, key)
      && isObject$2(descriptor)
      && hasOwn$1(descriptor, 'value')
      && !hasOwn$1(descriptor, 'get')
      && !hasOwn$1(descriptor, 'set')
      // TODO: add validation descriptor w/o calling accessors
      && !descriptor.configurable
      && (!hasOwn$1(descriptor, 'writable') || descriptor.writable)
      && (!hasOwn$1(descriptor, 'enumerable') || descriptor.enumerable)
    ) {
      target[key] = descriptor.value;
      return target;
    } return nativeDefineProperty(target, key, descriptor);
  };

  if (DESCRIPTORS) {
    if (!NATIVE_ARRAY_BUFFER_VIEWS) {
      getOwnPropertyDescriptorModule.f = wrappedGetOwnPropertyDescriptor;
      definePropertyModule.f = wrappedDefineProperty;
      addGetter(TypedArrayPrototype$1, 'buffer');
      addGetter(TypedArrayPrototype$1, 'byteOffset');
      addGetter(TypedArrayPrototype$1, 'byteLength');
      addGetter(TypedArrayPrototype$1, 'length');
    }

    $$1({ target: 'Object', stat: true, forced: !NATIVE_ARRAY_BUFFER_VIEWS }, {
      getOwnPropertyDescriptor: wrappedGetOwnPropertyDescriptor,
      defineProperty: wrappedDefineProperty
    });

    typedArrayConstructor.exports = function (TYPE, wrapper, CLAMPED) {
      var BYTES = TYPE.match(/\d+$/)[0] / 8;
      var CONSTRUCTOR_NAME = TYPE + (CLAMPED ? 'Clamped' : '') + 'Array';
      var GETTER = 'get' + TYPE;
      var SETTER = 'set' + TYPE;
      var NativeTypedArrayConstructor = global$7[CONSTRUCTOR_NAME];
      var TypedArrayConstructor = NativeTypedArrayConstructor;
      var TypedArrayConstructorPrototype = TypedArrayConstructor && TypedArrayConstructor.prototype;
      var exported = {};

      var getter = function (that, index) {
        var data = getInternalState(that);
        return data.view[GETTER](index * BYTES + data.byteOffset, true);
      };

      var setter = function (that, index, value) {
        var data = getInternalState(that);
        if (CLAMPED) value = (value = round(value)) < 0 ? 0 : value > 0xFF ? 0xFF : value & 0xFF;
        data.view[SETTER](index * BYTES + data.byteOffset, value, true);
      };

      var addElement = function (that, index) {
        nativeDefineProperty(that, index, {
          get: function () {
            return getter(this, index);
          },
          set: function (value) {
            return setter(this, index, value);
          },
          enumerable: true
        });
      };

      if (!NATIVE_ARRAY_BUFFER_VIEWS) {
        TypedArrayConstructor = wrapper(function (that, data, offset, $length) {
          anInstance(that, TypedArrayConstructorPrototype);
          var index = 0;
          var byteOffset = 0;
          var buffer, byteLength, length;
          if (!isObject$2(data)) {
            length = toIndex(data);
            byteLength = length * BYTES;
            buffer = new ArrayBuffer$1(byteLength);
          } else if (isArrayBuffer(data)) {
            buffer = data;
            byteOffset = toOffset$1(offset, BYTES);
            var $len = data.byteLength;
            if ($length === undefined) {
              if ($len % BYTES) throw RangeError$1(WRONG_LENGTH);
              byteLength = $len - byteOffset;
              if (byteLength < 0) throw RangeError$1(WRONG_LENGTH);
            } else {
              byteLength = toLength$1($length) * BYTES;
              if (byteLength + byteOffset > $len) throw RangeError$1(WRONG_LENGTH);
            }
            length = byteLength / BYTES;
          } else if (isTypedArray(data)) {
            return fromList(TypedArrayConstructor, data);
          } else {
            return call$2(typedArrayFrom, TypedArrayConstructor, data);
          }
          setInternalState(that, {
            buffer: buffer,
            byteOffset: byteOffset,
            byteLength: byteLength,
            length: length,
            view: new DataView$1(buffer)
          });
          while (index < length) addElement(that, index++);
        });

        if (setPrototypeOf) setPrototypeOf(TypedArrayConstructor, TypedArray);
        TypedArrayConstructorPrototype = TypedArrayConstructor.prototype = create(TypedArrayPrototype$1);
      } else if (TYPED_ARRAYS_CONSTRUCTORS_REQUIRES_WRAPPERS) {
        TypedArrayConstructor = wrapper(function (dummy, data, typedArrayOffset, $length) {
          anInstance(dummy, TypedArrayConstructorPrototype);
          return inheritIfRequired(function () {
            if (!isObject$2(data)) return new NativeTypedArrayConstructor(toIndex(data));
            if (isArrayBuffer(data)) return $length !== undefined
              ? new NativeTypedArrayConstructor(data, toOffset$1(typedArrayOffset, BYTES), $length)
              : typedArrayOffset !== undefined
                ? new NativeTypedArrayConstructor(data, toOffset$1(typedArrayOffset, BYTES))
                : new NativeTypedArrayConstructor(data);
            if (isTypedArray(data)) return fromList(TypedArrayConstructor, data);
            return call$2(typedArrayFrom, TypedArrayConstructor, data);
          }(), dummy, TypedArrayConstructor);
        });

        if (setPrototypeOf) setPrototypeOf(TypedArrayConstructor, TypedArray);
        forEach(getOwnPropertyNames(NativeTypedArrayConstructor), function (key) {
          if (!(key in TypedArrayConstructor)) {
            createNonEnumerableProperty(TypedArrayConstructor, key, NativeTypedArrayConstructor[key]);
          }
        });
        TypedArrayConstructor.prototype = TypedArrayConstructorPrototype;
      }

      if (TypedArrayConstructorPrototype.constructor !== TypedArrayConstructor) {
        createNonEnumerableProperty(TypedArrayConstructorPrototype, 'constructor', TypedArrayConstructor);
      }

      createNonEnumerableProperty(TypedArrayConstructorPrototype, TYPED_ARRAY_CONSTRUCTOR$1, TypedArrayConstructor);

      if (TYPED_ARRAY_TAG) {
        createNonEnumerableProperty(TypedArrayConstructorPrototype, TYPED_ARRAY_TAG, CONSTRUCTOR_NAME);
      }

      exported[CONSTRUCTOR_NAME] = TypedArrayConstructor;

      $$1({
        global: true, forced: TypedArrayConstructor != NativeTypedArrayConstructor, sham: !NATIVE_ARRAY_BUFFER_VIEWS
      }, exported);

      if (!(BYTES_PER_ELEMENT in TypedArrayConstructor)) {
        createNonEnumerableProperty(TypedArrayConstructor, BYTES_PER_ELEMENT, BYTES);
      }

      if (!(BYTES_PER_ELEMENT in TypedArrayConstructorPrototype)) {
        createNonEnumerableProperty(TypedArrayConstructorPrototype, BYTES_PER_ELEMENT, BYTES);
      }

      setSpecies(CONSTRUCTOR_NAME);
    };
  } else typedArrayConstructor.exports = function () { /* empty */ };

  var createTypedArrayConstructor = typedArrayConstructor.exports;

  // `Uint8Array` constructor
  // https://tc39.es/ecma262/#sec-typedarray-objects
  createTypedArrayConstructor('Uint8', function (init) {
    return function Uint8Array(data, byteOffset, length) {
      return init(this, data, byteOffset, length);
    };
  });

  var ArrayBufferViewCore$n = arrayBufferViewCore;
  var lengthOfArrayLike$5 = lengthOfArrayLike$h;
  var toIntegerOrInfinity$1 = toIntegerOrInfinity$c;

  var aTypedArray$m = ArrayBufferViewCore$n.aTypedArray;
  var exportTypedArrayMethod$n = ArrayBufferViewCore$n.exportTypedArrayMethod;

  // `%TypedArray%.prototype.at` method
  // https://github.com/tc39/proposal-relative-indexing-method
  exportTypedArrayMethod$n('at', function at(index) {
    var O = aTypedArray$m(this);
    var len = lengthOfArrayLike$5(O);
    var relativeIndex = toIntegerOrInfinity$1(index);
    var k = relativeIndex >= 0 ? relativeIndex : len + relativeIndex;
    return (k < 0 || k >= len) ? undefined : O[k];
  });

  var toObject$2 = toObject$g;
  var toAbsoluteIndex$1 = toAbsoluteIndex$7;
  var lengthOfArrayLike$4 = lengthOfArrayLike$h;

  var min$1 = Math.min;

  // `Array.prototype.copyWithin` method implementation
  // https://tc39.es/ecma262/#sec-array.prototype.copywithin
  // eslint-disable-next-line es/no-array-prototype-copywithin -- safe
  var arrayCopyWithin = [].copyWithin || function copyWithin(target /* = 0 */, start /* = 0, end = @length */) {
    var O = toObject$2(this);
    var len = lengthOfArrayLike$4(O);
    var to = toAbsoluteIndex$1(target, len);
    var from = toAbsoluteIndex$1(start, len);
    var end = arguments.length > 2 ? arguments[2] : undefined;
    var count = min$1((end === undefined ? len : toAbsoluteIndex$1(end, len)) - from, len - to);
    var inc = 1;
    if (from < to && to < from + count) {
      inc = -1;
      from += count - 1;
      to += count - 1;
    }
    while (count-- > 0) {
      if (from in O) O[to] = O[from];
      else delete O[to];
      to += inc;
      from += inc;
    } return O;
  };

  var uncurryThis$4 = functionUncurryThis;
  var ArrayBufferViewCore$m = arrayBufferViewCore;
  var $ArrayCopyWithin = arrayCopyWithin;

  var u$ArrayCopyWithin = uncurryThis$4($ArrayCopyWithin);
  var aTypedArray$l = ArrayBufferViewCore$m.aTypedArray;
  var exportTypedArrayMethod$m = ArrayBufferViewCore$m.exportTypedArrayMethod;

  // `%TypedArray%.prototype.copyWithin` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.copywithin
  exportTypedArrayMethod$m('copyWithin', function copyWithin(target, start /* , end */) {
    return u$ArrayCopyWithin(aTypedArray$l(this), target, start, arguments.length > 2 ? arguments[2] : undefined);
  });

  var ArrayBufferViewCore$l = arrayBufferViewCore;
  var $every = arrayIteration.every;

  var aTypedArray$k = ArrayBufferViewCore$l.aTypedArray;
  var exportTypedArrayMethod$l = ArrayBufferViewCore$l.exportTypedArrayMethod;

  // `%TypedArray%.prototype.every` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.every
  exportTypedArrayMethod$l('every', function every(callbackfn /* , thisArg */) {
    return $every(aTypedArray$k(this), callbackfn, arguments.length > 1 ? arguments[1] : undefined);
  });

  var ArrayBufferViewCore$k = arrayBufferViewCore;
  var call$1 = functionCall;
  var $fill = arrayFill$1;

  var aTypedArray$j = ArrayBufferViewCore$k.aTypedArray;
  var exportTypedArrayMethod$k = ArrayBufferViewCore$k.exportTypedArrayMethod;

  // `%TypedArray%.prototype.fill` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.fill
  exportTypedArrayMethod$k('fill', function fill(value /* , start, end */) {
    var length = arguments.length;
    return call$1(
      $fill,
      aTypedArray$j(this),
      value,
      length > 1 ? arguments[1] : undefined,
      length > 2 ? arguments[2] : undefined
    );
  });

  var lengthOfArrayLike$3 = lengthOfArrayLike$h;

  var arrayFromConstructorAndList$1 = function (Constructor, list) {
    var index = 0;
    var length = lengthOfArrayLike$3(list);
    var result = new Constructor(length);
    while (length > index) result[index] = list[index++];
    return result;
  };

  var ArrayBufferViewCore$j = arrayBufferViewCore;
  var speciesConstructor = speciesConstructor$3;

  var TYPED_ARRAY_CONSTRUCTOR = ArrayBufferViewCore$j.TYPED_ARRAY_CONSTRUCTOR;
  var aTypedArrayConstructor = ArrayBufferViewCore$j.aTypedArrayConstructor;

  // a part of `TypedArraySpeciesCreate` abstract operation
  // https://tc39.es/ecma262/#typedarray-species-create
  var typedArraySpeciesConstructor$4 = function (originalArray) {
    return aTypedArrayConstructor(speciesConstructor(originalArray, originalArray[TYPED_ARRAY_CONSTRUCTOR]));
  };

  var arrayFromConstructorAndList = arrayFromConstructorAndList$1;
  var typedArraySpeciesConstructor$3 = typedArraySpeciesConstructor$4;

  var typedArrayFromSpeciesAndList = function (instance, list) {
    return arrayFromConstructorAndList(typedArraySpeciesConstructor$3(instance), list);
  };

  var ArrayBufferViewCore$i = arrayBufferViewCore;
  var $filter = arrayIteration.filter;
  var fromSpeciesAndList = typedArrayFromSpeciesAndList;

  var aTypedArray$i = ArrayBufferViewCore$i.aTypedArray;
  var exportTypedArrayMethod$j = ArrayBufferViewCore$i.exportTypedArrayMethod;

  // `%TypedArray%.prototype.filter` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.filter
  exportTypedArrayMethod$j('filter', function filter(callbackfn /* , thisArg */) {
    var list = $filter(aTypedArray$i(this), callbackfn, arguments.length > 1 ? arguments[1] : undefined);
    return fromSpeciesAndList(this, list);
  });

  var ArrayBufferViewCore$h = arrayBufferViewCore;
  var $find = arrayIteration.find;

  var aTypedArray$h = ArrayBufferViewCore$h.aTypedArray;
  var exportTypedArrayMethod$i = ArrayBufferViewCore$h.exportTypedArrayMethod;

  // `%TypedArray%.prototype.find` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.find
  exportTypedArrayMethod$i('find', function find(predicate /* , thisArg */) {
    return $find(aTypedArray$h(this), predicate, arguments.length > 1 ? arguments[1] : undefined);
  });

  var ArrayBufferViewCore$g = arrayBufferViewCore;
  var $findIndex = arrayIteration.findIndex;

  var aTypedArray$g = ArrayBufferViewCore$g.aTypedArray;
  var exportTypedArrayMethod$h = ArrayBufferViewCore$g.exportTypedArrayMethod;

  // `%TypedArray%.prototype.findIndex` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.findindex
  exportTypedArrayMethod$h('findIndex', function findIndex(predicate /* , thisArg */) {
    return $findIndex(aTypedArray$g(this), predicate, arguments.length > 1 ? arguments[1] : undefined);
  });

  var ArrayBufferViewCore$f = arrayBufferViewCore;
  var $forEach = arrayIteration.forEach;

  var aTypedArray$f = ArrayBufferViewCore$f.aTypedArray;
  var exportTypedArrayMethod$g = ArrayBufferViewCore$f.exportTypedArrayMethod;

  // `%TypedArray%.prototype.forEach` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.foreach
  exportTypedArrayMethod$g('forEach', function forEach(callbackfn /* , thisArg */) {
    $forEach(aTypedArray$f(this), callbackfn, arguments.length > 1 ? arguments[1] : undefined);
  });

  var ArrayBufferViewCore$e = arrayBufferViewCore;
  var $includes = arrayIncludes.includes;

  var aTypedArray$e = ArrayBufferViewCore$e.aTypedArray;
  var exportTypedArrayMethod$f = ArrayBufferViewCore$e.exportTypedArrayMethod;

  // `%TypedArray%.prototype.includes` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.includes
  exportTypedArrayMethod$f('includes', function includes(searchElement /* , fromIndex */) {
    return $includes(aTypedArray$e(this), searchElement, arguments.length > 1 ? arguments[1] : undefined);
  });

  var ArrayBufferViewCore$d = arrayBufferViewCore;
  var $indexOf = arrayIncludes.indexOf;

  var aTypedArray$d = ArrayBufferViewCore$d.aTypedArray;
  var exportTypedArrayMethod$e = ArrayBufferViewCore$d.exportTypedArrayMethod;

  // `%TypedArray%.prototype.indexOf` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.indexof
  exportTypedArrayMethod$e('indexOf', function indexOf(searchElement /* , fromIndex */) {
    return $indexOf(aTypedArray$d(this), searchElement, arguments.length > 1 ? arguments[1] : undefined);
  });

  var global$6 = global$1e;
  var fails$5 = fails$J;
  var uncurryThis$3 = functionUncurryThis;
  var ArrayBufferViewCore$c = arrayBufferViewCore;
  var ArrayIterators = es_array_iterator;
  var wellKnownSymbol = wellKnownSymbol$s;

  var ITERATOR = wellKnownSymbol('iterator');
  var Uint8Array$2 = global$6.Uint8Array;
  var arrayValues = uncurryThis$3(ArrayIterators.values);
  var arrayKeys = uncurryThis$3(ArrayIterators.keys);
  var arrayEntries = uncurryThis$3(ArrayIterators.entries);
  var aTypedArray$c = ArrayBufferViewCore$c.aTypedArray;
  var exportTypedArrayMethod$d = ArrayBufferViewCore$c.exportTypedArrayMethod;
  var TypedArrayPrototype = Uint8Array$2 && Uint8Array$2.prototype;

  var GENERIC = !fails$5(function () {
    TypedArrayPrototype[ITERATOR].call([1]);
  });

  var ITERATOR_IS_VALUES = !!TypedArrayPrototype
    && TypedArrayPrototype.values
    && TypedArrayPrototype[ITERATOR] === TypedArrayPrototype.values
    && TypedArrayPrototype.values.name === 'values';

  var typedArrayValues = function values() {
    return arrayValues(aTypedArray$c(this));
  };

  // `%TypedArray%.prototype.entries` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.entries
  exportTypedArrayMethod$d('entries', function entries() {
    return arrayEntries(aTypedArray$c(this));
  }, GENERIC);
  // `%TypedArray%.prototype.keys` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.keys
  exportTypedArrayMethod$d('keys', function keys() {
    return arrayKeys(aTypedArray$c(this));
  }, GENERIC);
  // `%TypedArray%.prototype.values` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.values
  exportTypedArrayMethod$d('values', typedArrayValues, GENERIC || !ITERATOR_IS_VALUES, { name: 'values' });
  // `%TypedArray%.prototype[@@iterator]` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype-@@iterator
  exportTypedArrayMethod$d(ITERATOR, typedArrayValues, GENERIC || !ITERATOR_IS_VALUES, { name: 'values' });

  var ArrayBufferViewCore$b = arrayBufferViewCore;
  var uncurryThis$2 = functionUncurryThis;

  var aTypedArray$b = ArrayBufferViewCore$b.aTypedArray;
  var exportTypedArrayMethod$c = ArrayBufferViewCore$b.exportTypedArrayMethod;
  var $join = uncurryThis$2([].join);

  // `%TypedArray%.prototype.join` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.join
  exportTypedArrayMethod$c('join', function join(separator) {
    return $join(aTypedArray$b(this), separator);
  });

  /* eslint-disable es/no-array-prototype-lastindexof -- safe */
  var apply$2 = functionApply;
  var toIndexedObject = toIndexedObject$b;
  var toIntegerOrInfinity = toIntegerOrInfinity$c;
  var lengthOfArrayLike$2 = lengthOfArrayLike$h;
  var arrayMethodIsStrict = arrayMethodIsStrict$4;

  var min = Math.min;
  var $lastIndexOf$1 = [].lastIndexOf;
  var NEGATIVE_ZERO = !!$lastIndexOf$1 && 1 / [1].lastIndexOf(1, -0) < 0;
  var STRICT_METHOD = arrayMethodIsStrict('lastIndexOf');
  var FORCED$3 = NEGATIVE_ZERO || !STRICT_METHOD;

  // `Array.prototype.lastIndexOf` method implementation
  // https://tc39.es/ecma262/#sec-array.prototype.lastindexof
  var arrayLastIndexOf = FORCED$3 ? function lastIndexOf(searchElement /* , fromIndex = @[*-1] */) {
    // convert -0 to +0
    if (NEGATIVE_ZERO) return apply$2($lastIndexOf$1, this, arguments) || 0;
    var O = toIndexedObject(this);
    var length = lengthOfArrayLike$2(O);
    var index = length - 1;
    if (arguments.length > 1) index = min(index, toIntegerOrInfinity(arguments[1]));
    if (index < 0) index = length + index;
    for (;index >= 0; index--) if (index in O && O[index] === searchElement) return index || 0;
    return -1;
  } : $lastIndexOf$1;

  var ArrayBufferViewCore$a = arrayBufferViewCore;
  var apply$1 = functionApply;
  var $lastIndexOf = arrayLastIndexOf;

  var aTypedArray$a = ArrayBufferViewCore$a.aTypedArray;
  var exportTypedArrayMethod$b = ArrayBufferViewCore$a.exportTypedArrayMethod;

  // `%TypedArray%.prototype.lastIndexOf` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.lastindexof
  exportTypedArrayMethod$b('lastIndexOf', function lastIndexOf(searchElement /* , fromIndex */) {
    var length = arguments.length;
    return apply$1($lastIndexOf, aTypedArray$a(this), length > 1 ? [searchElement, arguments[1]] : [searchElement]);
  });

  var ArrayBufferViewCore$9 = arrayBufferViewCore;
  var $map = arrayIteration.map;
  var typedArraySpeciesConstructor$2 = typedArraySpeciesConstructor$4;

  var aTypedArray$9 = ArrayBufferViewCore$9.aTypedArray;
  var exportTypedArrayMethod$a = ArrayBufferViewCore$9.exportTypedArrayMethod;

  // `%TypedArray%.prototype.map` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.map
  exportTypedArrayMethod$a('map', function map(mapfn /* , thisArg */) {
    return $map(aTypedArray$9(this), mapfn, arguments.length > 1 ? arguments[1] : undefined, function (O, length) {
      return new (typedArraySpeciesConstructor$2(O))(length);
    });
  });

  var global$5 = global$1e;
  var aCallable$1 = aCallable$8;
  var toObject$1 = toObject$g;
  var IndexedObject = indexedObject;
  var lengthOfArrayLike$1 = lengthOfArrayLike$h;

  var TypeError$1 = global$5.TypeError;

  // `Array.prototype.{ reduce, reduceRight }` methods implementation
  var createMethod = function (IS_RIGHT) {
    return function (that, callbackfn, argumentsLength, memo) {
      aCallable$1(callbackfn);
      var O = toObject$1(that);
      var self = IndexedObject(O);
      var length = lengthOfArrayLike$1(O);
      var index = IS_RIGHT ? length - 1 : 0;
      var i = IS_RIGHT ? -1 : 1;
      if (argumentsLength < 2) while (true) {
        if (index in self) {
          memo = self[index];
          index += i;
          break;
        }
        index += i;
        if (IS_RIGHT ? index < 0 : length <= index) {
          throw TypeError$1('Reduce of empty array with no initial value');
        }
      }
      for (;IS_RIGHT ? index >= 0 : length > index; index += i) if (index in self) {
        memo = callbackfn(memo, self[index], index, O);
      }
      return memo;
    };
  };

  var arrayReduce = {
    // `Array.prototype.reduce` method
    // https://tc39.es/ecma262/#sec-array.prototype.reduce
    left: createMethod(false),
    // `Array.prototype.reduceRight` method
    // https://tc39.es/ecma262/#sec-array.prototype.reduceright
    right: createMethod(true)
  };

  var ArrayBufferViewCore$8 = arrayBufferViewCore;
  var $reduce = arrayReduce.left;

  var aTypedArray$8 = ArrayBufferViewCore$8.aTypedArray;
  var exportTypedArrayMethod$9 = ArrayBufferViewCore$8.exportTypedArrayMethod;

  // `%TypedArray%.prototype.reduce` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.reduce
  exportTypedArrayMethod$9('reduce', function reduce(callbackfn /* , initialValue */) {
    var length = arguments.length;
    return $reduce(aTypedArray$8(this), callbackfn, length, length > 1 ? arguments[1] : undefined);
  });

  var ArrayBufferViewCore$7 = arrayBufferViewCore;
  var $reduceRight = arrayReduce.right;

  var aTypedArray$7 = ArrayBufferViewCore$7.aTypedArray;
  var exportTypedArrayMethod$8 = ArrayBufferViewCore$7.exportTypedArrayMethod;

  // `%TypedArray%.prototype.reduceRicht` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.reduceright
  exportTypedArrayMethod$8('reduceRight', function reduceRight(callbackfn /* , initialValue */) {
    var length = arguments.length;
    return $reduceRight(aTypedArray$7(this), callbackfn, length, length > 1 ? arguments[1] : undefined);
  });

  var ArrayBufferViewCore$6 = arrayBufferViewCore;

  var aTypedArray$6 = ArrayBufferViewCore$6.aTypedArray;
  var exportTypedArrayMethod$7 = ArrayBufferViewCore$6.exportTypedArrayMethod;
  var floor = Math.floor;

  // `%TypedArray%.prototype.reverse` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.reverse
  exportTypedArrayMethod$7('reverse', function reverse() {
    var that = this;
    var length = aTypedArray$6(that).length;
    var middle = floor(length / 2);
    var index = 0;
    var value;
    while (index < middle) {
      value = that[index];
      that[index++] = that[--length];
      that[length] = value;
    } return that;
  });

  var global$4 = global$1e;
  var ArrayBufferViewCore$5 = arrayBufferViewCore;
  var lengthOfArrayLike = lengthOfArrayLike$h;
  var toOffset = toOffset$2;
  var toObject = toObject$g;
  var fails$4 = fails$J;

  var RangeError = global$4.RangeError;
  var aTypedArray$5 = ArrayBufferViewCore$5.aTypedArray;
  var exportTypedArrayMethod$6 = ArrayBufferViewCore$5.exportTypedArrayMethod;

  var FORCED$2 = fails$4(function () {
    // eslint-disable-next-line es/no-typed-arrays -- required for testing
    new Int8Array(1).set({});
  });

  // `%TypedArray%.prototype.set` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.set
  exportTypedArrayMethod$6('set', function set(arrayLike /* , offset */) {
    aTypedArray$5(this);
    var offset = toOffset(arguments.length > 1 ? arguments[1] : undefined, 1);
    var length = this.length;
    var src = toObject(arrayLike);
    var len = lengthOfArrayLike(src);
    var index = 0;
    if (len + offset > length) throw RangeError('Wrong length');
    while (index < len) this[offset + index] = src[index++];
  }, FORCED$2);

  var ArrayBufferViewCore$4 = arrayBufferViewCore;
  var typedArraySpeciesConstructor$1 = typedArraySpeciesConstructor$4;
  var fails$3 = fails$J;
  var arraySlice$1 = arraySlice$9;

  var aTypedArray$4 = ArrayBufferViewCore$4.aTypedArray;
  var exportTypedArrayMethod$5 = ArrayBufferViewCore$4.exportTypedArrayMethod;

  var FORCED$1 = fails$3(function () {
    // eslint-disable-next-line es/no-typed-arrays -- required for testing
    new Int8Array(1).slice();
  });

  // `%TypedArray%.prototype.slice` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.slice
  exportTypedArrayMethod$5('slice', function slice(start, end) {
    var list = arraySlice$1(aTypedArray$4(this), start, end);
    var C = typedArraySpeciesConstructor$1(this);
    var index = 0;
    var length = list.length;
    var result = new C(length);
    while (length > index) result[index] = list[index++];
    return result;
  }, FORCED$1);

  var ArrayBufferViewCore$3 = arrayBufferViewCore;
  var $some = arrayIteration.some;

  var aTypedArray$3 = ArrayBufferViewCore$3.aTypedArray;
  var exportTypedArrayMethod$4 = ArrayBufferViewCore$3.exportTypedArrayMethod;

  // `%TypedArray%.prototype.some` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.some
  exportTypedArrayMethod$4('some', function some(callbackfn /* , thisArg */) {
    return $some(aTypedArray$3(this), callbackfn, arguments.length > 1 ? arguments[1] : undefined);
  });

  var global$3 = global$1e;
  var uncurryThis$1 = functionUncurryThis;
  var fails$2 = fails$J;
  var aCallable = aCallable$8;
  var internalSort = arraySort$1;
  var ArrayBufferViewCore$2 = arrayBufferViewCore;
  var FF = engineFfVersion;
  var IE_OR_EDGE = engineIsIeOrEdge;
  var V8 = engineV8Version;
  var WEBKIT = engineWebkitVersion;

  var Array$1 = global$3.Array;
  var aTypedArray$2 = ArrayBufferViewCore$2.aTypedArray;
  var exportTypedArrayMethod$3 = ArrayBufferViewCore$2.exportTypedArrayMethod;
  var Uint16Array = global$3.Uint16Array;
  var un$Sort = Uint16Array && uncurryThis$1(Uint16Array.prototype.sort);

  // WebKit
  var ACCEPT_INCORRECT_ARGUMENTS = !!un$Sort && !(fails$2(function () {
    un$Sort(new Uint16Array(2), null);
  }) && fails$2(function () {
    un$Sort(new Uint16Array(2), {});
  }));

  var STABLE_SORT = !!un$Sort && !fails$2(function () {
    // feature detection can be too slow, so check engines versions
    if (V8) return V8 < 74;
    if (FF) return FF < 67;
    if (IE_OR_EDGE) return true;
    if (WEBKIT) return WEBKIT < 602;

    var array = new Uint16Array(516);
    var expected = Array$1(516);
    var index, mod;

    for (index = 0; index < 516; index++) {
      mod = index % 4;
      array[index] = 515 - index;
      expected[index] = index - 2 * mod + 3;
    }

    un$Sort(array, function (a, b) {
      return (a / 4 | 0) - (b / 4 | 0);
    });

    for (index = 0; index < 516; index++) {
      if (array[index] !== expected[index]) return true;
    }
  });

  var getSortCompare = function (comparefn) {
    return function (x, y) {
      if (comparefn !== undefined) return +comparefn(x, y) || 0;
      // eslint-disable-next-line no-self-compare -- NaN check
      if (y !== y) return -1;
      // eslint-disable-next-line no-self-compare -- NaN check
      if (x !== x) return 1;
      if (x === 0 && y === 0) return 1 / x > 0 && 1 / y < 0 ? 1 : -1;
      return x > y;
    };
  };

  // `%TypedArray%.prototype.sort` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.sort
  exportTypedArrayMethod$3('sort', function sort(comparefn) {
    if (comparefn !== undefined) aCallable(comparefn);
    if (STABLE_SORT) return un$Sort(this, comparefn);

    return internalSort(aTypedArray$2(this), getSortCompare(comparefn));
  }, !STABLE_SORT || ACCEPT_INCORRECT_ARGUMENTS);

  var ArrayBufferViewCore$1 = arrayBufferViewCore;
  var toLength = toLength$a;
  var toAbsoluteIndex = toAbsoluteIndex$7;
  var typedArraySpeciesConstructor = typedArraySpeciesConstructor$4;

  var aTypedArray$1 = ArrayBufferViewCore$1.aTypedArray;
  var exportTypedArrayMethod$2 = ArrayBufferViewCore$1.exportTypedArrayMethod;

  // `%TypedArray%.prototype.subarray` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.subarray
  exportTypedArrayMethod$2('subarray', function subarray(begin, end) {
    var O = aTypedArray$1(this);
    var length = O.length;
    var beginIndex = toAbsoluteIndex(begin, length);
    var C = typedArraySpeciesConstructor(O);
    return new C(
      O.buffer,
      O.byteOffset + beginIndex * O.BYTES_PER_ELEMENT,
      toLength((end === undefined ? length : toAbsoluteIndex(end, length)) - beginIndex)
    );
  });

  var global$2 = global$1e;
  var apply = functionApply;
  var ArrayBufferViewCore = arrayBufferViewCore;
  var fails$1 = fails$J;
  var arraySlice = arraySlice$9;

  var Int8Array$1 = global$2.Int8Array;
  var aTypedArray = ArrayBufferViewCore.aTypedArray;
  var exportTypedArrayMethod$1 = ArrayBufferViewCore.exportTypedArrayMethod;
  var $toLocaleString = [].toLocaleString;

  // iOS Safari 6.x fails here
  var TO_LOCALE_STRING_BUG = !!Int8Array$1 && fails$1(function () {
    $toLocaleString.call(new Int8Array$1(1));
  });

  var FORCED = fails$1(function () {
    return [1, 2].toLocaleString() != new Int8Array$1([1, 2]).toLocaleString();
  }) || !fails$1(function () {
    Int8Array$1.prototype.toLocaleString.call([1, 2]);
  });

  // `%TypedArray%.prototype.toLocaleString` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.tolocalestring
  exportTypedArrayMethod$1('toLocaleString', function toLocaleString() {
    return apply(
      $toLocaleString,
      TO_LOCALE_STRING_BUG ? arraySlice(aTypedArray(this)) : aTypedArray(this),
      arraySlice(arguments)
    );
  }, FORCED);

  var exportTypedArrayMethod = arrayBufferViewCore.exportTypedArrayMethod;
  var fails = fails$J;
  var global$1 = global$1e;
  var uncurryThis = functionUncurryThis;

  var Uint8Array$1 = global$1.Uint8Array;
  var Uint8ArrayPrototype = Uint8Array$1 && Uint8Array$1.prototype || {};
  var arrayToString = [].toString;
  var join = uncurryThis([].join);

  if (fails(function () { arrayToString.call({}); })) {
    arrayToString = function toString() {
      return join(this);
    };
  }

  var IS_NOT_ARRAY_METHOD = Uint8ArrayPrototype.toString != arrayToString;

  // `%TypedArray%.prototype.toString` method
  // https://tc39.es/ecma262/#sec-%typedarray%.prototype.tostring
  exportTypedArrayMethod('toString', arrayToString, IS_NOT_ARRAY_METHOD);

  var mediaManager = {};

  var $ = _export;
  var call = functionCall;

  // `URL.prototype.toJSON` method
  // https://url.spec.whatwg.org/#dom-url-tojson
  $({ target: 'URL', proto: true, enumerable: true }, {
    toJSON: function toJSON() {
      return call(URL.prototype.toString, this);
    }
  });

  var cjs$1 = {};

  /*! (c) 2020 Andrea Giammarchi */


  var $parse = JSON.parse,
      $stringify = JSON.stringify;
  var keys = Object.keys;
  var Primitive = String; // it could be Number

  var primitive = 'string'; // it could be 'number'

  var ignore = {};
  var object = 'object';

  var noop = function noop(_, value) {
    return value;
  };

  var primitives = function primitives(value) {
    return value instanceof Primitive ? Primitive(value) : value;
  };

  var Primitives = function Primitives(_, value) {
    return typeof value === primitive ? new Primitive(value) : value;
  };

  var revive = function revive(input, parsed, output, $) {
    var lazy = [];

    for (var ke = keys(output), length = ke.length, y = 0; y < length; y++) {
      var k = ke[y];
      var value = output[k];

      if (value instanceof Primitive) {
        var tmp = input[value];

        if (typeof tmp === object && !parsed.has(tmp)) {
          parsed.add(tmp);
          output[k] = ignore;
          lazy.push({
            k: k,
            a: [input, parsed, tmp, $]
          });
        } else output[k] = $.call(output, k, tmp);
      } else if (output[k] !== ignore) output[k] = $.call(output, k, value);
    }

    for (var _length = lazy.length, i = 0; i < _length; i++) {
      var _lazy$i = lazy[i],
          _k = _lazy$i.k,
          a = _lazy$i.a;
      output[_k] = $.call(output, _k, revive.apply(null, a));
    }

    return output;
  };

  var set$2 = function set(known, input, value) {
    var index = Primitive(input.push(value) - 1);
    known.set(value, index);
    return index;
  };

  var parse = function parse(text, reviver) {
    var input = $parse(text, Primitives).map(primitives);
    var value = input[0];
    var $ = reviver || noop;
    var tmp = typeof value === object && value ? revive(input, new Set(), value, $) : value;
    return $.call({
      '': tmp
    }, '', tmp);
  };

  cjs$1.parse = parse;

  var stringify = function stringify(value, replacer, space) {
    var $ = replacer && typeof replacer === object ? function (k, v) {
      return k === '' || -1 < replacer.indexOf(k) ? v : void 0;
    } : replacer || noop;
    var known = new Map();
    var input = [];
    var output = [];
    var i = +set$2(known, input, $.call({
      '': value
    }, '', value));
    var firstRun = !i;

    while (i < input.length) {
      firstRun = true;
      output[i] = $stringify(input[i++], replace, space);
    }

    return '[' + output.join(',') + ']';

    function replace(key, value) {
      if (firstRun) {
        firstRun = !firstRun;
        return value;
      }

      var after = $.call(this, key, value);

      switch (typeof after) {
        case object:
          if (after === null) return after;

        case primitive:
          return known.get(after) || set$2(known, input, after);
      }

      return after;
    }
  };

  cjs$1.stringify = stringify;

  var toJSON = function toJSON(any) {
    return $parse(stringify(any));
  };

  cjs$1.toJSON = toJSON;

  var fromJSON = function fromJSON(any) {
    return parse($stringify(any));
  };

  cjs$1.fromJSON = fromJSON;

  var _mutations;

  function makeMap(str, expectsLowerCase) {
    var map = Object.create(null);
    var list = str.split(',');

    for (var i = 0; i < list.length; i++) {
      map[list[i]] = true;
    }

    return expectsLowerCase ? function (val) {
      return !!map[val.toLowerCase()];
    } : function (val) {
      return !!map[val];
    };
  }

  function normalizeStyle(value) {
    if (isArray(value)) {
      var res = {};

      for (var i = 0; i < value.length; i++) {
        var item = value[i];
        var normalized = isString(item) ? parseStringStyle(item) : normalizeStyle(item);

        if (normalized) {
          for (var key in normalized) {
            res[key] = normalized[key];
          }
        }
      }

      return res;
    } else if (isString(value)) {
      return value;
    } else if (isObject$1(value)) {
      return value;
    }
  }

  var listDelimiterRE = /;(?![^(]*\))/g;
  var propertyDelimiterRE = /:([^]+)/;
  var styleCommentRE = /\/\*[\s\S]*?\*\//g;

  function parseStringStyle(cssText) {
    var ret = {};
    cssText.replace(styleCommentRE, '').split(listDelimiterRE).forEach(function (item) {
      if (item) {
        var tmp = item.split(propertyDelimiterRE);
        tmp.length > 1 && (ret[tmp[0].trim()] = tmp[1].trim());
      }
    });
    return ret;
  }

  function normalizeClass(value) {
    var res = '';

    if (isString(value)) {
      res = value;
    } else if (isArray(value)) {
      for (var i = 0; i < value.length; i++) {
        var normalized = normalizeClass(value[i]);

        if (normalized) {
          res += normalized + ' ';
        }
      }
    } else if (isObject$1(value)) {
      for (var name in value) {
        if (value[name]) {
          res += name + ' ';
        }
      }
    }

    return res.trim();
  }
  /**
   * On the client we only need to offer special cases for boolean attributes that
   * have different names from their corresponding dom properties:
   * - itemscope -> N/A
   * - allowfullscreen -> allowFullscreen
   * - formnovalidate -> formNoValidate
   * - ismap -> isMap
   * - nomodule -> noModule
   * - novalidate -> noValidate
   * - readonly -> readOnly
   */


  var specialBooleanAttrs = "itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly";
  var isSpecialBooleanAttr = /*#__PURE__*/makeMap(specialBooleanAttrs);
  /**
   * Boolean attributes should be included if the value is truthy or ''.
   * e.g. `<select multiple>` compiles to `{ multiple: '' }`
   */

  function includeBooleanAttr(value) {
    return !!value || value === '';
  }
  /**
   * For converting {{ interpolation }} values to displayed strings.
   * @private
   */


  var toDisplayString = function toDisplayString(val) {
    return isString(val) ? val : val == null ? '' : isArray(val) || isObject$1(val) && (val.toString === objectToString || !isFunction(val.toString)) ? JSON.stringify(val, replacer, 2) : String(val);
  };

  var replacer = function replacer(_key, val) {
    // can't use isRef here since @vue/shared has no deps
    if (val && val.__v_isRef) {
      return replacer(_key, val.value);
    } else if (isMap(val)) {
      var _ref2;

      return _ref2 = {}, _ref2["Map(" + val.size + ")"] = [].concat(val.entries()).reduce(function (entries, _ref) {
        var key = _ref[0],
            val = _ref[1];
        entries[key + " =>"] = val;
        return entries;
      }, {}), _ref2;
    } else if (isSet(val)) {
      var _ref7;

      return _ref7 = {}, _ref7["Set(" + val.size + ")"] = [].concat(val.values()), _ref7;
    } else if (isObject$1(val) && !isArray(val) && !isPlainObject(val)) {
      return String(val);
    }

    return val;
  };

  var EMPTY_OBJ = {};
  var EMPTY_ARR = [];

  var NOOP = function NOOP() {};
  /**
   * Always return false.
   */


  var NO = function NO() {
    return false;
  };

  var onRE = /^on[^a-z]/;

  var isOn = function isOn(key) {
    return onRE.test(key);
  };

  var isModelListener = function isModelListener(key) {
    return key.startsWith('onUpdate:');
  };

  var extend = Object.assign;

  var remove = function remove(arr, el) {
    var i = arr.indexOf(el);

    if (i > -1) {
      arr.splice(i, 1);
    }
  };

  var hasOwnProperty = Object.prototype.hasOwnProperty;

  var hasOwn = function hasOwn(val, key) {
    return hasOwnProperty.call(val, key);
  };

  var isArray = Array.isArray;

  var isMap = function isMap(val) {
    return toTypeString(val) === '[object Map]';
  };

  var isSet = function isSet(val) {
    return toTypeString(val) === '[object Set]';
  };

  var isFunction = function isFunction(val) {
    return typeof val === 'function';
  };

  var isString = function isString(val) {
    return typeof val === 'string';
  };

  var isSymbol = function isSymbol(val) {
    return typeof val === 'symbol';
  };

  var isObject$1 = function isObject$1(val) {
    return val !== null && typeof val === 'object';
  };

  var isPromise$1 = function isPromise$1(val) {
    return isObject$1(val) && isFunction(val.then) && isFunction(val.catch);
  };

  var objectToString = Object.prototype.toString;

  var toTypeString = function toTypeString(value) {
    return objectToString.call(value);
  };

  var toRawType = function toRawType(value) {
    // extract "RawType" from strings like "[object RawType]"
    return toTypeString(value).slice(8, -1);
  };

  var isPlainObject = function isPlainObject(val) {
    return toTypeString(val) === '[object Object]';
  };

  var isIntegerKey = function isIntegerKey(key) {
    return isString(key) && key !== 'NaN' && key[0] !== '-' && '' + parseInt(key, 10) === key;
  };

  var isReservedProp = /*#__PURE__*/makeMap( // the leading comma is intentional so empty string "" is also included
  ',key,ref,ref_for,ref_key,' + 'onVnodeBeforeMount,onVnodeMounted,' + 'onVnodeBeforeUpdate,onVnodeUpdated,' + 'onVnodeBeforeUnmount,onVnodeUnmounted');

  var cacheStringFunction = function cacheStringFunction(fn) {
    var cache = Object.create(null);
    return function (str) {
      var hit = cache[str];
      return hit || (cache[str] = fn(str));
    };
  };

  var camelizeRE = /-(\w)/g;
  /**
   * @private
   */

  var camelize = cacheStringFunction(function (str) {
    return str.replace(camelizeRE, function (_, c) {
      return c ? c.toUpperCase() : '';
    });
  });
  var hyphenateRE = /\B([A-Z])/g;
  /**
   * @private
   */

  var hyphenate = cacheStringFunction(function (str) {
    return str.replace(hyphenateRE, '-$1').toLowerCase();
  });
  /**
   * @private
   */

  var capitalize = cacheStringFunction(function (str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  });
  /**
   * @private
   */

  var toHandlerKey = cacheStringFunction(function (str) {
    return str ? "on" + capitalize(str) : "";
  }); // compare whether a value has changed, accounting for NaN.

  var hasChanged = function hasChanged(value, oldValue) {
    return !Object.is(value, oldValue);
  };

  var invokeArrayFns = function invokeArrayFns(fns, arg) {
    for (var i = 0; i < fns.length; i++) {
      fns[i](arg);
    }
  };

  var def = function def(obj, key, value) {
    Object.defineProperty(obj, key, {
      configurable: true,
      enumerable: false,
      value: value
    });
  };

  var toNumber = function toNumber(val) {
    var n = parseFloat(val);
    return isNaN(n) ? val : n;
  };

  var _globalThis;

  var getGlobalThis = function getGlobalThis() {
    return _globalThis || (_globalThis = typeof globalThis !== 'undefined' ? globalThis : typeof self !== 'undefined' ? self : typeof window !== 'undefined' ? window : typeof commonjsGlobal !== 'undefined' ? commonjsGlobal : {});
  };

  var activeEffectScope;

  var EffectScope = /*#__PURE__*/function () {
    function EffectScope(detached) {
      if (detached === void 0) {
        detached = false;
      }

      this.detached = detached;
      /**
       * @internal
       */

      this.active = true;
      /**
       * @internal
       */

      this.effects = [];
      /**
       * @internal
       */

      this.cleanups = [];
      this.parent = activeEffectScope;

      if (!detached && activeEffectScope) {
        this.index = (activeEffectScope.scopes || (activeEffectScope.scopes = [])).push(this) - 1;
      }
    }

    var _proto = EffectScope.prototype;

    _proto.run = function run(fn) {
      if (this.active) {
        var currentEffectScope = activeEffectScope;

        try {
          activeEffectScope = this;
          return fn();
        } finally {
          activeEffectScope = currentEffectScope;
        }
      }
    }
    /**
     * This should only be called on non-detached scopes
     * @internal
     */
    ;

    _proto.on = function on() {
      activeEffectScope = this;
    }
    /**
     * This should only be called on non-detached scopes
     * @internal
     */
    ;

    _proto.off = function off() {
      activeEffectScope = this.parent;
    };

    _proto.stop = function stop(fromParent) {
      if (this.active) {
        var i, l;

        for (i = 0, l = this.effects.length; i < l; i++) {
          this.effects[i].stop();
        }

        for (i = 0, l = this.cleanups.length; i < l; i++) {
          this.cleanups[i]();
        }

        if (this.scopes) {
          for (i = 0, l = this.scopes.length; i < l; i++) {
            this.scopes[i].stop(true);
          }
        } // nested scope, dereference from parent to avoid memory leaks


        if (!this.detached && this.parent && !fromParent) {
          // optimized O(1) removal
          var last = this.parent.scopes.pop();

          if (last && last !== this) {
            this.parent.scopes[this.index] = last;
            last.index = this.index;
          }
        }

        this.parent = undefined;
        this.active = false;
      }
    };

    return EffectScope;
  }();

  function recordEffectScope(effect, scope) {
    if (scope === void 0) {
      scope = activeEffectScope;
    }

    if (scope && scope.active) {
      scope.effects.push(effect);
    }
  }

  var createDep = function createDep(effects) {
    var dep = new Set(effects);
    dep.w = 0;
    dep.n = 0;
    return dep;
  };

  var wasTracked = function wasTracked(dep) {
    return (dep.w & trackOpBit) > 0;
  };

  var newTracked = function newTracked(dep) {
    return (dep.n & trackOpBit) > 0;
  };

  var initDepMarkers = function initDepMarkers(_ref) {
    var deps = _ref.deps;

    if (deps.length) {
      for (var i = 0; i < deps.length; i++) {
        deps[i].w |= trackOpBit; // set was tracked
      }
    }
  };

  var finalizeDepMarkers = function finalizeDepMarkers(effect) {
    var deps = effect.deps;

    if (deps.length) {
      var ptr = 0;

      for (var i = 0; i < deps.length; i++) {
        var dep = deps[i];

        if (wasTracked(dep) && !newTracked(dep)) {
          dep.delete(effect);
        } else {
          deps[ptr++] = dep;
        } // clear bits


        dep.w &= ~trackOpBit;
        dep.n &= ~trackOpBit;
      }

      deps.length = ptr;
    }
  };

  var targetMap = new WeakMap(); // The number of effects currently being tracked recursively.

  var effectTrackDepth = 0;
  var trackOpBit = 1;
  /**
   * The bitwise track markers support at most 30 levels of recursion.
   * This value is chosen to enable modern JS engines to use a SMI on all platforms.
   * When recursion depth is greater, fall back to using a full cleanup.
   */

  var maxMarkerBits = 30;
  var activeEffect;
  var ITERATE_KEY = Symbol('');
  var MAP_KEY_ITERATE_KEY = Symbol('');

  var ReactiveEffect = /*#__PURE__*/function () {
    function ReactiveEffect(fn, scheduler, scope) {
      if (scheduler === void 0) {
        scheduler = null;
      }

      this.fn = fn;
      this.scheduler = scheduler;
      this.active = true;
      this.deps = [];
      this.parent = undefined;
      recordEffectScope(this, scope);
    }

    var _proto2 = ReactiveEffect.prototype;

    _proto2.run = function run() {
      if (!this.active) {
        return this.fn();
      }

      var parent = activeEffect;
      var lastShouldTrack = shouldTrack;

      while (parent) {
        if (parent === this) {
          return;
        }

        parent = parent.parent;
      }

      try {
        this.parent = activeEffect;
        activeEffect = this;
        shouldTrack = true;
        trackOpBit = 1 << ++effectTrackDepth;

        if (effectTrackDepth <= maxMarkerBits) {
          initDepMarkers(this);
        } else {
          cleanupEffect(this);
        }

        return this.fn();
      } finally {
        if (effectTrackDepth <= maxMarkerBits) {
          finalizeDepMarkers(this);
        }

        trackOpBit = 1 << --effectTrackDepth;
        activeEffect = this.parent;
        shouldTrack = lastShouldTrack;
        this.parent = undefined;

        if (this.deferStop) {
          this.stop();
        }
      }
    };

    _proto2.stop = function stop() {
      // stopped while running itself - defer the cleanup
      if (activeEffect === this) {
        this.deferStop = true;
      } else if (this.active) {
        cleanupEffect(this);

        if (this.onStop) {
          this.onStop();
        }

        this.active = false;
      }
    };

    return ReactiveEffect;
  }();

  function cleanupEffect(effect) {
    var deps = effect.deps;

    if (deps.length) {
      for (var i = 0; i < deps.length; i++) {
        deps[i].delete(effect);
      }

      deps.length = 0;
    }
  }

  var shouldTrack = true;
  var trackStack = [];

  function pauseTracking() {
    trackStack.push(shouldTrack);
    shouldTrack = false;
  }

  function resetTracking() {
    var last = trackStack.pop();
    shouldTrack = last === undefined ? true : last;
  }

  function track(target, type, key) {
    if (shouldTrack && activeEffect) {
      var depsMap = targetMap.get(target);

      if (!depsMap) {
        targetMap.set(target, depsMap = new Map());
      }

      var dep = depsMap.get(key);

      if (!dep) {
        depsMap.set(key, dep = createDep());
      }

      trackEffects(dep);
    }
  }

  function trackEffects(dep, debuggerEventExtraInfo) {
    var shouldTrack = false;

    if (effectTrackDepth <= maxMarkerBits) {
      if (!newTracked(dep)) {
        dep.n |= trackOpBit; // set newly tracked

        shouldTrack = !wasTracked(dep);
      }
    } else {
      // Full cleanup mode.
      shouldTrack = !dep.has(activeEffect);
    }

    if (shouldTrack) {
      dep.add(activeEffect);
      activeEffect.deps.push(dep);
    }
  }

  function trigger(target, type, key, newValue, oldValue, oldTarget) {
    var depsMap = targetMap.get(target);

    if (!depsMap) {
      // never been tracked
      return;
    }

    var deps = [];

    if (type === "clear"
    /* TriggerOpTypes.CLEAR */
    ) {
      // collection being cleared
      // trigger all effects for target
      deps = [].concat(depsMap.values());
    } else if (key === 'length' && isArray(target)) {
      var newLength = toNumber(newValue);
      depsMap.forEach(function (dep, key) {
        if (key === 'length' || key >= newLength) {
          deps.push(dep);
        }
      });
    } else {
      // schedule runs for SET | ADD | DELETE
      if (key !== void 0) {
        deps.push(depsMap.get(key));
      } // also run for iteration key on ADD | DELETE | Map.SET


      switch (type) {
        case "add"
        /* TriggerOpTypes.ADD */
        :
          if (!isArray(target)) {
            deps.push(depsMap.get(ITERATE_KEY));

            if (isMap(target)) {
              deps.push(depsMap.get(MAP_KEY_ITERATE_KEY));
            }
          } else if (isIntegerKey(key)) {
            // new index added to array -> length changes
            deps.push(depsMap.get('length'));
          }

          break;

        case "delete"
        /* TriggerOpTypes.DELETE */
        :
          if (!isArray(target)) {
            deps.push(depsMap.get(ITERATE_KEY));

            if (isMap(target)) {
              deps.push(depsMap.get(MAP_KEY_ITERATE_KEY));
            }
          }

          break;

        case "set"
        /* TriggerOpTypes.SET */
        :
          if (isMap(target)) {
            deps.push(depsMap.get(ITERATE_KEY));
          }

          break;
      }
    }

    if (deps.length === 1) {
      if (deps[0]) {
        {
          triggerEffects(deps[0]);
        }
      }
    } else {
      var effects = [];

      for (var _iterator = _createForOfIteratorHelperLoose(deps), _step; !(_step = _iterator()).done;) {
        var dep = _step.value;

        if (dep) {
          effects.push.apply(effects, dep);
        }
      }

      {
        triggerEffects(createDep(effects));
      }
    }
  }

  function triggerEffects(dep, debuggerEventExtraInfo) {
    // spread into array for stabilization
    var effects = isArray(dep) ? dep : [].concat(dep);

    for (var _iterator2 = _createForOfIteratorHelperLoose(effects), _step2; !(_step2 = _iterator2()).done;) {
      var effect = _step2.value;

      if (effect.computed) {
        triggerEffect(effect);
      }
    }

    for (var _iterator3 = _createForOfIteratorHelperLoose(effects), _step3; !(_step3 = _iterator3()).done;) {
      var _effect = _step3.value;

      if (!_effect.computed) {
        triggerEffect(_effect);
      }
    }
  }

  function triggerEffect(effect, debuggerEventExtraInfo) {
    if (effect !== activeEffect || effect.allowRecurse) {
      if (effect.scheduler) {
        effect.scheduler();
      } else {
        effect.run();
      }
    }
  }

  var isNonTrackableKeys = /*#__PURE__*/makeMap("__proto__,__v_isRef,__isVue");
  var builtInSymbols = new Set( /*#__PURE__*/Object.getOwnPropertyNames(Symbol) // ios10.x Object.getOwnPropertyNames(Symbol) can enumerate 'arguments' and 'caller'
  // but accessing them on Symbol leads to TypeError because Symbol is a strict mode
  // function
  .filter(function (key) {
    return key !== 'arguments' && key !== 'caller';
  }).map(function (key) {
    return Symbol[key];
  }).filter(isSymbol));
  var get = /*#__PURE__*/createGetter();
  var shallowGet = /*#__PURE__*/createGetter(false, true);
  var readonlyGet = /*#__PURE__*/createGetter(true);
  var arrayInstrumentations = /*#__PURE__*/createArrayInstrumentations();

  function createArrayInstrumentations() {
    var instrumentations = {};
    ['includes', 'indexOf', 'lastIndexOf'].forEach(function (key) {
      instrumentations[key] = function () {
        var arr = toRaw(this);

        for (var i = 0, l = this.length; i < l; i++) {
          track(arr, "get"
          /* TrackOpTypes.GET */
          , i + '');
        } // we run the method using the original args first (which may be reactive)


        for (var _len2 = arguments.length, args = new Array(_len2), _key3 = 0; _key3 < _len2; _key3++) {
          args[_key3] = arguments[_key3];
        }

        var res = arr[key].apply(arr, args);

        if (res === -1 || res === false) {
          // if that didn't work, run it again using raw values.
          return arr[key].apply(arr, args.map(toRaw));
        } else {
          return res;
        }
      };
    });
    ['push', 'pop', 'shift', 'unshift', 'splice'].forEach(function (key) {
      instrumentations[key] = function () {
        pauseTracking();

        for (var _len3 = arguments.length, args = new Array(_len3), _key4 = 0; _key4 < _len3; _key4++) {
          args[_key4] = arguments[_key4];
        }

        var res = toRaw(this)[key].apply(this, args);
        resetTracking();
        return res;
      };
    });
    return instrumentations;
  }

  function createGetter(isReadonly, shallow) {
    if (isReadonly === void 0) {
      isReadonly = false;
    }

    if (shallow === void 0) {
      shallow = false;
    }

    return function get(target, key, receiver) {
      if (key === "__v_isReactive"
      /* ReactiveFlags.IS_REACTIVE */
      ) {
        return !isReadonly;
      } else if (key === "__v_isReadonly"
      /* ReactiveFlags.IS_READONLY */
      ) {
        return isReadonly;
      } else if (key === "__v_isShallow"
      /* ReactiveFlags.IS_SHALLOW */
      ) {
        return shallow;
      } else if (key === "__v_raw"
      /* ReactiveFlags.RAW */
      && receiver === (isReadonly ? shallow ? shallowReadonlyMap : readonlyMap : shallow ? shallowReactiveMap : reactiveMap).get(target)) {
        return target;
      }

      var targetIsArray = isArray(target);

      if (!isReadonly && targetIsArray && hasOwn(arrayInstrumentations, key)) {
        return Reflect.get(arrayInstrumentations, key, receiver);
      }

      var res = Reflect.get(target, key, receiver);

      if (isSymbol(key) ? builtInSymbols.has(key) : isNonTrackableKeys(key)) {
        return res;
      }

      if (!isReadonly) {
        track(target, "get"
        /* TrackOpTypes.GET */
        , key);
      }

      if (shallow) {
        return res;
      }

      if (isRef(res)) {
        // ref unwrapping - skip unwrap for Array + integer key.
        return targetIsArray && isIntegerKey(key) ? res : res.value;
      }

      if (isObject$1(res)) {
        // Convert returned value into a proxy as well. we do the isObject check
        // here to avoid invalid value warning. Also need to lazy access readonly
        // and reactive here to avoid circular dependency.
        return isReadonly ? readonly(res) : reactive(res);
      }

      return res;
    };
  }

  var set = /*#__PURE__*/createSetter();
  var shallowSet = /*#__PURE__*/createSetter(true);

  function createSetter(shallow) {
    if (shallow === void 0) {
      shallow = false;
    }

    return function set(target, key, value, receiver) {
      var oldValue = target[key];

      if (isReadonly(oldValue) && isRef(oldValue) && !isRef(value)) {
        return false;
      }

      if (!shallow) {
        if (!isShallow(value) && !isReadonly(value)) {
          oldValue = toRaw(oldValue);
          value = toRaw(value);
        }

        if (!isArray(target) && isRef(oldValue) && !isRef(value)) {
          oldValue.value = value;
          return true;
        }
      }

      var hadKey = isArray(target) && isIntegerKey(key) ? Number(key) < target.length : hasOwn(target, key);
      var result = Reflect.set(target, key, value, receiver); // don't trigger if target is something up in the prototype chain of original

      if (target === toRaw(receiver)) {
        if (!hadKey) {
          trigger(target, "add"
          /* TriggerOpTypes.ADD */
          , key, value);
        } else if (hasChanged(value, oldValue)) {
          trigger(target, "set"
          /* TriggerOpTypes.SET */
          , key, value);
        }
      }

      return result;
    };
  }

  function deleteProperty(target, key) {
    var hadKey = hasOwn(target, key);
    target[key];
    var result = Reflect.deleteProperty(target, key);

    if (result && hadKey) {
      trigger(target, "delete"
      /* TriggerOpTypes.DELETE */
      , key, undefined);
    }

    return result;
  }

  function has(target, key) {
    var result = Reflect.has(target, key);

    if (!isSymbol(key) || !builtInSymbols.has(key)) {
      track(target, "has"
      /* TrackOpTypes.HAS */
      , key);
    }

    return result;
  }

  function ownKeys(target) {
    track(target, "iterate"
    /* TrackOpTypes.ITERATE */
    , isArray(target) ? 'length' : ITERATE_KEY);
    return Reflect.ownKeys(target);
  }

  var mutableHandlers = {
    get: get,
    set: set,
    deleteProperty: deleteProperty,
    has: has,
    ownKeys: ownKeys
  };
  var readonlyHandlers = {
    get: readonlyGet,
    set: function set(target, key) {
      return true;
    },
    deleteProperty: function deleteProperty(target, key) {
      return true;
    }
  };
  var shallowReactiveHandlers = /*#__PURE__*/extend({}, mutableHandlers, {
    get: shallowGet,
    set: shallowSet
  }); // Props handlers are special in the sense that it should not unwrap top-level

  var toShallow = function toShallow(value) {
    return value;
  };

  var getProto = function getProto(v) {
    return Reflect.getPrototypeOf(v);
  };

  function get$1(target, key, isReadonly, isShallow) {
    if (isReadonly === void 0) {
      isReadonly = false;
    }

    if (isShallow === void 0) {
      isShallow = false;
    } // #1772: readonly(reactive(Map)) should return readonly + reactive version
    // of the value


    target = target["__v_raw"
    /* ReactiveFlags.RAW */
    ];
    var rawTarget = toRaw(target);
    var rawKey = toRaw(key);

    if (!isReadonly) {
      if (key !== rawKey) {
        track(rawTarget, "get"
        /* TrackOpTypes.GET */
        , key);
      }

      track(rawTarget, "get"
      /* TrackOpTypes.GET */
      , rawKey);
    }

    var _getProto = getProto(rawTarget),
        has = _getProto.has;

    var wrap = isShallow ? toShallow : isReadonly ? toReadonly : toReactive;

    if (has.call(rawTarget, key)) {
      return wrap(target.get(key));
    } else if (has.call(rawTarget, rawKey)) {
      return wrap(target.get(rawKey));
    } else if (target !== rawTarget) {
      // #3602 readonly(reactive(Map))
      // ensure that the nested reactive `Map` can do tracking for itself
      target.get(key);
    }
  }

  function has$1(key, isReadonly) {
    if (isReadonly === void 0) {
      isReadonly = false;
    }

    var target = this["__v_raw"
    /* ReactiveFlags.RAW */
    ];
    var rawTarget = toRaw(target);
    var rawKey = toRaw(key);

    if (!isReadonly) {
      if (key !== rawKey) {
        track(rawTarget, "has"
        /* TrackOpTypes.HAS */
        , key);
      }

      track(rawTarget, "has"
      /* TrackOpTypes.HAS */
      , rawKey);
    }

    return key === rawKey ? target.has(key) : target.has(key) || target.has(rawKey);
  }

  function size(target, isReadonly) {
    if (isReadonly === void 0) {
      isReadonly = false;
    }

    target = target["__v_raw"
    /* ReactiveFlags.RAW */
    ];
    !isReadonly && track(toRaw(target), "iterate"
    /* TrackOpTypes.ITERATE */
    , ITERATE_KEY);
    return Reflect.get(target, 'size', target);
  }

  function add(value) {
    value = toRaw(value);
    var target = toRaw(this);
    var proto = getProto(target);
    var hadKey = proto.has.call(target, value);

    if (!hadKey) {
      target.add(value);
      trigger(target, "add"
      /* TriggerOpTypes.ADD */
      , value, value);
    }

    return this;
  }

  function set$1(key, value) {
    value = toRaw(value);
    var target = toRaw(this);

    var _getProto2 = getProto(target),
        has = _getProto2.has,
        get = _getProto2.get;

    var hadKey = has.call(target, key);

    if (!hadKey) {
      key = toRaw(key);
      hadKey = has.call(target, key);
    }

    var oldValue = get.call(target, key);
    target.set(key, value);

    if (!hadKey) {
      trigger(target, "add"
      /* TriggerOpTypes.ADD */
      , key, value);
    } else if (hasChanged(value, oldValue)) {
      trigger(target, "set"
      /* TriggerOpTypes.SET */
      , key, value);
    }

    return this;
  }

  function deleteEntry(key) {
    var target = toRaw(this);

    var _getProto3 = getProto(target),
        has = _getProto3.has,
        get = _getProto3.get;

    var hadKey = has.call(target, key);

    if (!hadKey) {
      key = toRaw(key);
      hadKey = has.call(target, key);
    }

    get ? get.call(target, key) : undefined; // forward the operation before queueing reactions

    var result = target.delete(key);

    if (hadKey) {
      trigger(target, "delete"
      /* TriggerOpTypes.DELETE */
      , key, undefined);
    }

    return result;
  }

  function clear() {
    var target = toRaw(this);
    var hadItems = target.size !== 0;
    var result = target.clear();

    if (hadItems) {
      trigger(target, "clear"
      /* TriggerOpTypes.CLEAR */
      , undefined, undefined);
    }

    return result;
  }

  function createForEach(isReadonly, isShallow) {
    return function forEach(callback, thisArg) {
      var observed = this;
      var target = observed["__v_raw"
      /* ReactiveFlags.RAW */
      ];
      var rawTarget = toRaw(target);
      var wrap = isShallow ? toShallow : isReadonly ? toReadonly : toReactive;
      !isReadonly && track(rawTarget, "iterate"
      /* TrackOpTypes.ITERATE */
      , ITERATE_KEY);
      return target.forEach(function (value, key) {
        // important: make sure the callback is
        // 1. invoked with the reactive map as `this` and 3rd arg
        // 2. the value received should be a corresponding reactive/readonly.
        return callback.call(thisArg, wrap(value), wrap(key), observed);
      });
    };
  }

  function createIterableMethod(method, isReadonly, isShallow) {
    return function () {
      var _ref8;

      var target = this["__v_raw"
      /* ReactiveFlags.RAW */
      ];
      var rawTarget = toRaw(target);
      var targetIsMap = isMap(rawTarget);
      var isPair = method === 'entries' || method === Symbol.iterator && targetIsMap;
      var isKeyOnly = method === 'keys' && targetIsMap;
      var innerIterator = target[method].apply(target, arguments);
      var wrap = isShallow ? toShallow : isReadonly ? toReadonly : toReactive;
      !isReadonly && track(rawTarget, "iterate"
      /* TrackOpTypes.ITERATE */
      , isKeyOnly ? MAP_KEY_ITERATE_KEY : ITERATE_KEY); // return a wrapped iterator which returns observed versions of the
      // values emitted from the real iterator

      return _ref8 = {
        // iterator protocol
        next: function next() {
          var _innerIterator$next = innerIterator.next(),
              value = _innerIterator$next.value,
              done = _innerIterator$next.done;

          return done ? {
            value: value,
            done: done
          } : {
            value: isPair ? [wrap(value[0]), wrap(value[1])] : wrap(value),
            done: done
          };
        }
      }, _ref8[Symbol.iterator] = function () {
        return this;
      }, _ref8;
    };
  }

  function createReadonlyMethod(type) {
    return function () {
      return type === "delete"
      /* TriggerOpTypes.DELETE */
      ? false : this;
    };
  }

  function createInstrumentations() {
    var mutableInstrumentations = {
      get: function get(key) {
        return get$1(this, key);
      },

      get size() {
        return size(this);
      },

      has: has$1,
      add: add,
      set: set$1,
      delete: deleteEntry,
      clear: clear,
      forEach: createForEach(false, false)
    };
    var shallowInstrumentations = {
      get: function get(key) {
        return get$1(this, key, false, true);
      },

      get size() {
        return size(this);
      },

      has: has$1,
      add: add,
      set: set$1,
      delete: deleteEntry,
      clear: clear,
      forEach: createForEach(false, true)
    };
    var readonlyInstrumentations = {
      get: function get(key) {
        return get$1(this, key, true);
      },

      get size() {
        return size(this, true);
      },

      has: function has(key) {
        return has$1.call(this, key, true);
      },
      add: createReadonlyMethod("add"
      /* TriggerOpTypes.ADD */
      ),
      set: createReadonlyMethod("set"
      /* TriggerOpTypes.SET */
      ),
      delete: createReadonlyMethod("delete"
      /* TriggerOpTypes.DELETE */
      ),
      clear: createReadonlyMethod("clear"
      /* TriggerOpTypes.CLEAR */
      ),
      forEach: createForEach(true, false)
    };
    var shallowReadonlyInstrumentations = {
      get: function get(key) {
        return get$1(this, key, true, true);
      },

      get size() {
        return size(this, true);
      },

      has: function has(key) {
        return has$1.call(this, key, true);
      },
      add: createReadonlyMethod("add"
      /* TriggerOpTypes.ADD */
      ),
      set: createReadonlyMethod("set"
      /* TriggerOpTypes.SET */
      ),
      delete: createReadonlyMethod("delete"
      /* TriggerOpTypes.DELETE */
      ),
      clear: createReadonlyMethod("clear"
      /* TriggerOpTypes.CLEAR */
      ),
      forEach: createForEach(true, true)
    };
    var iteratorMethods = ['keys', 'values', 'entries', Symbol.iterator];
    iteratorMethods.forEach(function (method) {
      mutableInstrumentations[method] = createIterableMethod(method, false, false);
      readonlyInstrumentations[method] = createIterableMethod(method, true, false);
      shallowInstrumentations[method] = createIterableMethod(method, false, true);
      shallowReadonlyInstrumentations[method] = createIterableMethod(method, true, true);
    });
    return [mutableInstrumentations, readonlyInstrumentations, shallowInstrumentations, shallowReadonlyInstrumentations];
  }

  var _createInstrumentatio = /* #__PURE__*/createInstrumentations(),
      mutableInstrumentations = _createInstrumentatio[0],
      readonlyInstrumentations = _createInstrumentatio[1],
      shallowInstrumentations = _createInstrumentatio[2],
      shallowReadonlyInstrumentations = _createInstrumentatio[3];

  function createInstrumentationGetter(isReadonly, shallow) {
    var instrumentations = shallow ? isReadonly ? shallowReadonlyInstrumentations : shallowInstrumentations : isReadonly ? readonlyInstrumentations : mutableInstrumentations;
    return function (target, key, receiver) {
      if (key === "__v_isReactive"
      /* ReactiveFlags.IS_REACTIVE */
      ) {
        return !isReadonly;
      } else if (key === "__v_isReadonly"
      /* ReactiveFlags.IS_READONLY */
      ) {
        return isReadonly;
      } else if (key === "__v_raw"
      /* ReactiveFlags.RAW */
      ) {
        return target;
      }

      return Reflect.get(hasOwn(instrumentations, key) && key in target ? instrumentations : target, key, receiver);
    };
  }

  var mutableCollectionHandlers = {
    get: /*#__PURE__*/createInstrumentationGetter(false, false)
  };
  var shallowCollectionHandlers = {
    get: /*#__PURE__*/createInstrumentationGetter(false, true)
  };
  var readonlyCollectionHandlers = {
    get: /*#__PURE__*/createInstrumentationGetter(true, false)
  };
  var reactiveMap = new WeakMap();
  var shallowReactiveMap = new WeakMap();
  var readonlyMap = new WeakMap();
  var shallowReadonlyMap = new WeakMap();

  function targetTypeMap(rawType) {
    switch (rawType) {
      case 'Object':
      case 'Array':
        return 1
        /* TargetType.COMMON */
        ;

      case 'Map':
      case 'Set':
      case 'WeakMap':
      case 'WeakSet':
        return 2
        /* TargetType.COLLECTION */
        ;

      default:
        return 0
        /* TargetType.INVALID */
        ;
    }
  }

  function getTargetType(value) {
    return value["__v_skip"
    /* ReactiveFlags.SKIP */
    ] || !Object.isExtensible(value) ? 0
    /* TargetType.INVALID */
    : targetTypeMap(toRawType(value));
  }

  function reactive(target) {
    // if trying to observe a readonly proxy, return the readonly version.
    if (isReadonly(target)) {
      return target;
    }

    return createReactiveObject(target, false, mutableHandlers, mutableCollectionHandlers, reactiveMap);
  }
  /**
   * Return a shallowly-reactive copy of the original object, where only the root
   * level properties are reactive. It also does not auto-unwrap refs (even at the
   * root level).
   */


  function shallowReactive(target) {
    return createReactiveObject(target, false, shallowReactiveHandlers, shallowCollectionHandlers, shallowReactiveMap);
  }
  /**
   * Creates a readonly copy of the original object. Note the returned copy is not
   * made reactive, but `readonly` can be called on an already reactive object.
   */


  function readonly(target) {
    return createReactiveObject(target, true, readonlyHandlers, readonlyCollectionHandlers, readonlyMap);
  }

  function createReactiveObject(target, isReadonly, baseHandlers, collectionHandlers, proxyMap) {
    if (!isObject$1(target)) {
      return target;
    } // target is already a Proxy, return it.
    // exception: calling readonly() on a reactive object


    if (target["__v_raw"
    /* ReactiveFlags.RAW */
    ] && !(isReadonly && target["__v_isReactive"
    /* ReactiveFlags.IS_REACTIVE */
    ])) {
      return target;
    } // target already has corresponding Proxy


    var existingProxy = proxyMap.get(target);

    if (existingProxy) {
      return existingProxy;
    } // only specific value types can be observed.


    var targetType = getTargetType(target);

    if (targetType === 0
    /* TargetType.INVALID */
    ) {
      return target;
    }

    var proxy = new Proxy(target, targetType === 2
    /* TargetType.COLLECTION */
    ? collectionHandlers : baseHandlers);
    proxyMap.set(target, proxy);
    return proxy;
  }

  function isReactive(value) {
    if (isReadonly(value)) {
      return isReactive(value["__v_raw"
      /* ReactiveFlags.RAW */
      ]);
    }

    return !!(value && value["__v_isReactive"
    /* ReactiveFlags.IS_REACTIVE */
    ]);
  }

  function isReadonly(value) {
    return !!(value && value["__v_isReadonly"
    /* ReactiveFlags.IS_READONLY */
    ]);
  }

  function isShallow(value) {
    return !!(value && value["__v_isShallow"
    /* ReactiveFlags.IS_SHALLOW */
    ]);
  }

  function isProxy(value) {
    return isReactive(value) || isReadonly(value);
  }

  function toRaw(observed) {
    var raw = observed && observed["__v_raw"
    /* ReactiveFlags.RAW */
    ];
    return raw ? toRaw(raw) : observed;
  }

  function markRaw(value) {
    def(value, "__v_skip"
    /* ReactiveFlags.SKIP */
    , true);
    return value;
  }

  var toReactive = function toReactive(value) {
    return isObject$1(value) ? reactive(value) : value;
  };

  var toReadonly = function toReadonly(value) {
    return isObject$1(value) ? readonly(value) : value;
  };

  function trackRefValue(ref) {
    if (shouldTrack && activeEffect) {
      ref = toRaw(ref);
      {
        trackEffects(ref.dep || (ref.dep = createDep()));
      }
    }
  }

  function triggerRefValue(ref, newVal) {
    ref = toRaw(ref);

    if (ref.dep) {
      {
        triggerEffects(ref.dep);
      }
    }
  }

  function isRef(r) {
    return !!(r && r.__v_isRef === true);
  }

  function unref(ref) {
    return isRef(ref) ? ref.value : ref;
  }

  var shallowUnwrapHandlers = {
    get: function get(target, key, receiver) {
      return unref(Reflect.get(target, key, receiver));
    },
    set: function set(target, key, value, receiver) {
      var oldValue = target[key];

      if (isRef(oldValue) && !isRef(value)) {
        oldValue.value = value;
        return true;
      } else {
        return Reflect.set(target, key, value, receiver);
      }
    }
  };

  function proxyRefs(objectWithRefs) {
    return isReactive(objectWithRefs) ? objectWithRefs : new Proxy(objectWithRefs, shallowUnwrapHandlers);
  }

  var _a;

  var ComputedRefImpl = /*#__PURE__*/function () {
    function ComputedRefImpl(getter, _setter, isReadonly, isSSR) {
      var _this = this;

      this._setter = _setter;
      this.dep = undefined;
      this.__v_isRef = true;
      this[_a] = false;
      this._dirty = true;
      this.effect = new ReactiveEffect(getter, function () {
        if (!_this._dirty) {
          _this._dirty = true;
          triggerRefValue(_this);
        }
      });
      this.effect.computed = this;
      this.effect.active = this._cacheable = !isSSR;
      this["__v_isReadonly"
      /* ReactiveFlags.IS_READONLY */
      ] = isReadonly;
    }

    _createClass(ComputedRefImpl, [{
      key: "value",
      get: function get() {
        // the computed ref may get wrapped by other proxies e.g. readonly() #3376
        var self = toRaw(this);
        trackRefValue(self);

        if (self._dirty || !self._cacheable) {
          self._dirty = false;
          self._value = self.effect.run();
        }

        return self._value;
      },
      set: function set(newValue) {
        this._setter(newValue);
      }
    }]);

    return ComputedRefImpl;
  }();

  _a = "__v_isReadonly"
  /* ReactiveFlags.IS_READONLY */
  ;

  function computed$1(getterOrOptions, debugOptions, isSSR) {
    if (isSSR === void 0) {
      isSSR = false;
    }

    var getter;
    var setter;
    var onlyGetter = isFunction(getterOrOptions);

    if (onlyGetter) {
      getter = getterOrOptions;
      setter = NOOP;
    } else {
      getter = getterOrOptions.get;
      setter = getterOrOptions.set;
    }

    var cRef = new ComputedRefImpl(getter, setter, onlyGetter || !setter, isSSR);
    return cRef;
  }

  function callWithErrorHandling(fn, instance, type, args) {
    var res;

    try {
      res = args ? fn.apply(void 0, args) : fn();
    } catch (err) {
      handleError(err, instance, type);
    }

    return res;
  }

  function callWithAsyncErrorHandling(fn, instance, type, args) {
    if (isFunction(fn)) {
      var res = callWithErrorHandling(fn, instance, type, args);

      if (res && isPromise$1(res)) {
        res.catch(function (err) {
          handleError(err, instance, type);
        });
      }

      return res;
    }

    var values = [];

    for (var i = 0; i < fn.length; i++) {
      values.push(callWithAsyncErrorHandling(fn[i], instance, type, args));
    }

    return values;
  }

  function handleError(err, instance, type, throwInDev) {
    instance ? instance.vnode : null;

    if (instance) {
      var cur = instance.parent; // the exposed instance is the render proxy to keep it consistent with 2.x

      var exposedInstance = instance.proxy; // in production the hook receives only the error code

      var errorInfo = type;

      while (cur) {
        var errorCapturedHooks = cur.ec;

        if (errorCapturedHooks) {
          for (var i = 0; i < errorCapturedHooks.length; i++) {
            if (errorCapturedHooks[i](err, exposedInstance, errorInfo) === false) {
              return;
            }
          }
        }

        cur = cur.parent;
      } // app-level handling


      var appErrorHandler = instance.appContext.config.errorHandler;

      if (appErrorHandler) {
        callWithErrorHandling(appErrorHandler, null, 10
        /* ErrorCodes.APP_ERROR_HANDLER */
        , [err, exposedInstance, errorInfo]);
        return;
      }
    }

    logError(err);
  }

  function logError(err, type, contextVNode, throwInDev) {
    {
      // recover in prod to reduce the impact on end-user
      console.error(err);
    }
  }

  var isFlushing = false;
  var isFlushPending = false;
  var queue = [];
  var flushIndex = 0;
  var pendingPostFlushCbs = [];
  var activePostFlushCbs = null;
  var postFlushIndex = 0;
  var resolvedPromise = /*#__PURE__*/Promise.resolve();
  var currentFlushPromise = null;

  function nextTick(fn) {
    var p = currentFlushPromise || resolvedPromise;
    return fn ? p.then(this ? fn.bind(this) : fn) : p;
  } // #2768
  // Use binary-search to find a suitable position in the queue,
  // so that the queue maintains the increasing order of job's id,
  // which can prevent the job from being skipped and also can avoid repeated patching.


  function findInsertionIndex(id) {
    // the start index should be `flushIndex + 1`
    var start = flushIndex + 1;
    var end = queue.length;

    while (start < end) {
      var middle = start + end >>> 1;
      var middleJobId = getId(queue[middle]);
      middleJobId < id ? start = middle + 1 : end = middle;
    }

    return start;
  }

  function queueJob(job) {
    // the dedupe search uses the startIndex argument of Array.includes()
    // by default the search index includes the current job that is being run
    // so it cannot recursively trigger itself again.
    // if the job is a watch() callback, the search will start with a +1 index to
    // allow it recursively trigger itself - it is the user's responsibility to
    // ensure it doesn't end up in an infinite loop.
    if (!queue.length || !queue.includes(job, isFlushing && job.allowRecurse ? flushIndex + 1 : flushIndex)) {
      if (job.id == null) {
        queue.push(job);
      } else {
        queue.splice(findInsertionIndex(job.id), 0, job);
      }

      queueFlush();
    }
  }

  function queueFlush() {
    if (!isFlushing && !isFlushPending) {
      isFlushPending = true;
      currentFlushPromise = resolvedPromise.then(flushJobs);
    }
  }

  function invalidateJob(job) {
    var i = queue.indexOf(job);

    if (i > flushIndex) {
      queue.splice(i, 1);
    }
  }

  function queuePostFlushCb(cb) {
    if (!isArray(cb)) {
      if (!activePostFlushCbs || !activePostFlushCbs.includes(cb, cb.allowRecurse ? postFlushIndex + 1 : postFlushIndex)) {
        pendingPostFlushCbs.push(cb);
      }
    } else {
      // if cb is an array, it is a component lifecycle hook which can only be
      // triggered by a job, which is already deduped in the main queue, so
      // we can skip duplicate check here to improve perf
      pendingPostFlushCbs.push.apply(pendingPostFlushCbs, cb);
    }

    queueFlush();
  }

  function flushPreFlushCbs(seen, // if currently flushing, skip the current job itself
  i) {
    if (i === void 0) {
      i = isFlushing ? flushIndex + 1 : 0;
    }

    for (; i < queue.length; i++) {
      var cb = queue[i];

      if (cb && cb.pre) {
        queue.splice(i, 1);
        i--;
        cb();
      }
    }
  }

  function flushPostFlushCbs(seen) {
    if (pendingPostFlushCbs.length) {
      var deduped = [].concat(new Set(pendingPostFlushCbs));
      pendingPostFlushCbs.length = 0; // #1947 already has active queue, nested flushPostFlushCbs call

      if (activePostFlushCbs) {
        var _activePostFlushCbs;

        (_activePostFlushCbs = activePostFlushCbs).push.apply(_activePostFlushCbs, deduped);

        return;
      }

      activePostFlushCbs = deduped;
      activePostFlushCbs.sort(function (a, b) {
        return getId(a) - getId(b);
      });

      for (postFlushIndex = 0; postFlushIndex < activePostFlushCbs.length; postFlushIndex++) {
        activePostFlushCbs[postFlushIndex]();
      }

      activePostFlushCbs = null;
      postFlushIndex = 0;
    }
  }

  var getId = function getId(job) {
    return job.id == null ? Infinity : job.id;
  };

  var comparator = function comparator(a, b) {
    var diff = getId(a) - getId(b);

    if (diff === 0) {
      if (a.pre && !b.pre) return -1;
      if (b.pre && !a.pre) return 1;
    }

    return diff;
  };

  function flushJobs(seen) {
    isFlushPending = false;
    isFlushing = true; // This ensures that:
    // 1. Components are updated from parent to child. (because parent is always
    //    created before the child so its render effect will have smaller
    //    priority number)
    // 2. If a component is unmounted during a parent component's update,
    //    its update can be skipped.

    queue.sort(comparator); // conditional usage of checkRecursiveUpdate must be determined out of
    // try ... catch block since Rollup by default de-optimizes treeshaking
    // inside try-catch. This can leave all warning code unshaked. Although
    // they would get eventually shaken by a minifier like terser, some minifiers
    // would fail to do that (e.g. https://github.com/evanw/esbuild/issues/1610)

    var check = NOOP;

    try {
      for (flushIndex = 0; flushIndex < queue.length; flushIndex++) {
        var job = queue[flushIndex];

        if (job && job.active !== false) {
          if ("production" !== 'production' && check(job)) ; // console.log(`running:`, job.id)

          callWithErrorHandling(job, null, 14
          /* ErrorCodes.SCHEDULER */
          );
        }
      }
    } finally {
      flushIndex = 0;
      queue.length = 0;
      flushPostFlushCbs();
      isFlushing = false;
      currentFlushPromise = null; // some postFlushCb queued jobs!
      // keep flushing until it drains.

      if (queue.length || pendingPostFlushCbs.length) {
        flushJobs();
      }
    }
  }

  var devtools;
  var buffer = [];
  var devtoolsNotInstalled = false;

  function emit(event) {
    for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
      args[_key2 - 1] = arguments[_key2];
    }

    if (devtools) {
      var _devtools;

      (_devtools = devtools).emit.apply(_devtools, [event].concat(args));
    } else if (!devtoolsNotInstalled) {
      buffer.push({
        event: event,
        args: args
      });
    }
  }

  function setDevtoolsHook(hook, target) {
    var _a, _b;

    devtools = hook;

    if (devtools) {
      devtools.enabled = true;
      buffer.forEach(function (_ref3) {
        var _devtools2;

        var event = _ref3.event,
            args = _ref3.args;
        return (_devtools2 = devtools).emit.apply(_devtools2, [event].concat(args));
      });
      buffer = [];
    } else if ( // handle late devtools injection - only do this if we are in an actual
    // browser environment to avoid the timer handle stalling test runner exit
    // (#4815)
    typeof window !== 'undefined' && // some envs mock window but not fully
    window.HTMLElement && // also exclude jsdom
    !((_b = (_a = window.navigator) === null || _a === void 0 ? void 0 : _a.userAgent) === null || _b === void 0 ? void 0 : _b.includes('jsdom'))) {
      var replay = target.__VUE_DEVTOOLS_HOOK_REPLAY__ = target.__VUE_DEVTOOLS_HOOK_REPLAY__ || [];
      replay.push(function (newHook) {
        setDevtoolsHook(newHook, target);
      }); // clear buffer after 3s - the user probably doesn't have devtools installed
      // at all, and keeping the buffer will cause memory leaks (#4738)

      setTimeout(function () {
        if (!devtools) {
          target.__VUE_DEVTOOLS_HOOK_REPLAY__ = null;
          devtoolsNotInstalled = true;
          buffer = [];
        }
      }, 3000);
    } else {
      // non-browser env, assume not installed
      devtoolsNotInstalled = true;
      buffer = [];
    }
  }

  function devtoolsInitApp(app, version) {
    emit("app:init"
    /* DevtoolsHooks.APP_INIT */
    , app, version, {
      Fragment: Fragment,
      Text: Text,
      Comment: Comment,
      Static: Static
    });
  }

  function devtoolsUnmountApp(app) {
    emit("app:unmount"
    /* DevtoolsHooks.APP_UNMOUNT */
    , app);
  }

  var devtoolsComponentAdded = /*#__PURE__*/createDevtoolsComponentHook("component:added"
  /* DevtoolsHooks.COMPONENT_ADDED */
  );
  var devtoolsComponentUpdated = /*#__PURE__*/createDevtoolsComponentHook("component:updated"
  /* DevtoolsHooks.COMPONENT_UPDATED */
  );

  var _devtoolsComponentRemoved = /*#__PURE__*/createDevtoolsComponentHook("component:removed"
  /* DevtoolsHooks.COMPONENT_REMOVED */
  );

  var devtoolsComponentRemoved = function devtoolsComponentRemoved(component) {
    if (devtools && typeof devtools.cleanupBuffer === 'function' && // remove the component if it wasn't buffered
    !devtools.cleanupBuffer(component)) {
      _devtoolsComponentRemoved(component);
    }
  };

  function createDevtoolsComponentHook(hook) {
    return function (component) {
      emit(hook, component.appContext.app, component.uid, component.parent ? component.parent.uid : undefined, component);
    };
  }

  function devtoolsComponentEmit(component, event, params) {
    emit("component:emit"
    /* DevtoolsHooks.COMPONENT_EMIT */
    , component.appContext.app, component, event, params);
  }

  function emit$1(instance, event) {
    if (instance.isUnmounted) return;
    var props = instance.vnode.props || EMPTY_OBJ;

    for (var _len3 = arguments.length, rawArgs = new Array(_len3 > 2 ? _len3 - 2 : 0), _key3 = 2; _key3 < _len3; _key3++) {
      rawArgs[_key3 - 2] = arguments[_key3];
    }

    var args = rawArgs;
    var isModelListener = event.startsWith('update:'); // for v-model update:xxx events, apply modifiers on args

    var modelArg = isModelListener && event.slice(7);

    if (modelArg && modelArg in props) {
      var modifiersKey = (modelArg === 'modelValue' ? 'model' : modelArg) + "Modifiers";

      var _ref22 = props[modifiersKey] || EMPTY_OBJ,
          number = _ref22.number,
          trim = _ref22.trim;

      if (trim) {
        args = rawArgs.map(function (a) {
          return isString(a) ? a.trim() : a;
        });
      }

      if (number) {
        args = rawArgs.map(toNumber);
      }
    }

    {
      devtoolsComponentEmit(instance, event, args);
    }
    var handlerName;
    var handler = props[handlerName = toHandlerKey(event)] || // also try camelCase event handler (#2249)
    props[handlerName = toHandlerKey(camelize(event))]; // for v-model update:xxx events, also trigger kebab-case equivalent
    // for props passed via kebab-case

    if (!handler && isModelListener) {
      handler = props[handlerName = toHandlerKey(hyphenate(event))];
    }

    if (handler) {
      callWithAsyncErrorHandling(handler, instance, 6
      /* ErrorCodes.COMPONENT_EVENT_HANDLER */
      , args);
    }

    var onceHandler = props[handlerName + "Once"];

    if (onceHandler) {
      if (!instance.emitted) {
        instance.emitted = {};
      } else if (instance.emitted[handlerName]) {
        return;
      }

      instance.emitted[handlerName] = true;
      callWithAsyncErrorHandling(onceHandler, instance, 6
      /* ErrorCodes.COMPONENT_EVENT_HANDLER */
      , args);
    }
  }

  function normalizeEmitsOptions(comp, appContext, asMixin) {
    if (asMixin === void 0) {
      asMixin = false;
    }

    var cache = appContext.emitsCache;
    var cached = cache.get(comp);

    if (cached !== undefined) {
      return cached;
    }

    var raw = comp.emits;
    var normalized = {}; // apply mixin/extends props

    var hasExtends = false;

    if (!isFunction(comp)) {
      var extendEmits = function extendEmits(raw) {
        var normalizedFromExtend = normalizeEmitsOptions(raw, appContext, true);

        if (normalizedFromExtend) {
          hasExtends = true;
          extend(normalized, normalizedFromExtend);
        }
      };

      if (!asMixin && appContext.mixins.length) {
        appContext.mixins.forEach(extendEmits);
      }

      if (comp.extends) {
        extendEmits(comp.extends);
      }

      if (comp.mixins) {
        comp.mixins.forEach(extendEmits);
      }
    }

    if (!raw && !hasExtends) {
      if (isObject$1(comp)) {
        cache.set(comp, null);
      }

      return null;
    }

    if (isArray(raw)) {
      raw.forEach(function (key) {
        return normalized[key] = null;
      });
    } else {
      extend(normalized, raw);
    }

    if (isObject$1(comp)) {
      cache.set(comp, normalized);
    }

    return normalized;
  } // Check if an incoming prop key is a declared emit event listener.
  // e.g. With `emits: { click: null }`, props named `onClick` and `onclick` are
  // both considered matched listeners.


  function isEmitListener(options, key) {
    if (!options || !isOn(key)) {
      return false;
    }

    key = key.slice(2).replace(/Once$/, '');
    return hasOwn(options, key[0].toLowerCase() + key.slice(1)) || hasOwn(options, hyphenate(key)) || hasOwn(options, key);
  }
  /**
   * mark the current rendering instance for asset resolution (e.g.
   * resolveComponent, resolveDirective) during render
   */


  var currentRenderingInstance = null;
  var currentScopeId = null;
  /**
   * Note: rendering calls maybe nested. The function returns the parent rendering
   * instance if present, which should be restored after the render is done:
   *
   * ```js
   * const prev = setCurrentRenderingInstance(i)
   * // ...render
   * setCurrentRenderingInstance(prev)
   * ```
   */

  function setCurrentRenderingInstance(instance) {
    var prev = currentRenderingInstance;
    currentRenderingInstance = instance;
    currentScopeId = instance && instance.type.__scopeId || null;
    return prev;
  }
  /**
   * Wrap a slot function to memoize current rendering instance
   * @private compiler helper
   */


  function withCtx(fn, ctx, isNonScopedSlot // false only
  ) {
    if (ctx === void 0) {
      ctx = currentRenderingInstance;
    }

    if (!ctx) return fn; // already normalized

    if (fn._n) {
      return fn;
    }

    var renderFnWithContext = function renderFnWithContext() {
      // If a user calls a compiled slot inside a template expression (#1745), it
      // can mess up block tracking, so by default we disable block tracking and
      // force bail out when invoking a compiled slot (indicated by the ._d flag).
      // This isn't necessary if rendering a compiled `<slot>`, so we flip the
      // ._d flag off when invoking the wrapped fn inside `renderSlot`.
      if (renderFnWithContext._d) {
        setBlockTracking(-1);
      }

      var prevInstance = setCurrentRenderingInstance(ctx);
      var res;

      try {
        res = fn.apply(void 0, arguments);
      } finally {
        setCurrentRenderingInstance(prevInstance);

        if (renderFnWithContext._d) {
          setBlockTracking(1);
        }
      }

      {
        devtoolsComponentUpdated(ctx);
      }
      return res;
    }; // mark normalized to avoid duplicated wrapping


    renderFnWithContext._n = true; // mark this as compiled by default
    // this is used in vnode.ts -> normalizeChildren() to set the slot
    // rendering flag.

    renderFnWithContext._c = true; // disable block tracking by default

    renderFnWithContext._d = true;
    return renderFnWithContext;
  }

  function markAttrsAccessed() {}

  function renderComponentRoot(instance) {
    var Component = instance.type,
        vnode = instance.vnode,
        proxy = instance.proxy,
        withProxy = instance.withProxy,
        props = instance.props,
        _instance$propsOption = instance.propsOptions,
        propsOptions = _instance$propsOption[0],
        slots = instance.slots,
        attrs = instance.attrs,
        emit = instance.emit,
        render = instance.render,
        renderCache = instance.renderCache,
        data = instance.data,
        setupState = instance.setupState,
        ctx = instance.ctx,
        inheritAttrs = instance.inheritAttrs;
    var result;
    var fallthroughAttrs;
    var prev = setCurrentRenderingInstance(instance);

    try {
      if (vnode.shapeFlag & 4
      /* ShapeFlags.STATEFUL_COMPONENT */
      ) {
        // withProxy is a proxy with a different `has` trap only for
        // runtime-compiled render functions using `with` block.
        var proxyToUse = withProxy || proxy;
        result = normalizeVNode(render.call(proxyToUse, proxyToUse, renderCache, props, setupState, data, ctx));
        fallthroughAttrs = attrs;
      } else {
        // functional
        var _render = Component; // in dev, mark attrs accessed if optional props (attrs === props)

        if ("production" !== 'production' && attrs === props) ;
        result = normalizeVNode(_render.length > 1 ? _render(props, "production" !== 'production' ? {
          get attrs() {
            markAttrsAccessed();
            return attrs;
          },

          slots: slots,
          emit: emit
        } : {
          attrs: attrs,
          slots: slots,
          emit: emit
        }) : _render(props, null
        /* we know it doesn't need it */
        ));
        fallthroughAttrs = Component.props ? attrs : getFunctionalFallthrough(attrs);
      }
    } catch (err) {
      blockStack.length = 0;
      handleError(err, instance, 1
      /* ErrorCodes.RENDER_FUNCTION */
      );
      result = createVNode(Comment);
    } // attr merging
    // in dev mode, comments are preserved, and it's possible for a template
    // to have comments along side the root element which makes it a fragment


    var root = result;

    if (fallthroughAttrs && inheritAttrs !== false) {
      var keys = Object.keys(fallthroughAttrs);
      var _root = root,
          shapeFlag = _root.shapeFlag;

      if (keys.length) {
        if (shapeFlag & (1
        /* ShapeFlags.ELEMENT */
        | 6
        /* ShapeFlags.COMPONENT */
        )) {
          if (propsOptions && keys.some(isModelListener)) {
            // If a v-model listener (onUpdate:xxx) has a corresponding declared
            // prop, it indicates this component expects to handle v-model and
            // it should not fallthrough.
            // related: #1543, #1643, #1989
            fallthroughAttrs = filterModelListeners(fallthroughAttrs, propsOptions);
          }

          root = cloneVNode(root, fallthroughAttrs);
        }
      }
    } // inherit directives


    if (vnode.dirs) {
      root = cloneVNode(root);
      root.dirs = root.dirs ? root.dirs.concat(vnode.dirs) : vnode.dirs;
    } // inherit transition data


    if (vnode.transition) {
      root.transition = vnode.transition;
    }

    {
      result = root;
    }
    setCurrentRenderingInstance(prev);
    return result;
  }

  var getFunctionalFallthrough = function getFunctionalFallthrough(attrs) {
    var res;

    for (var key in attrs) {
      if (key === 'class' || key === 'style' || isOn(key)) {
        (res || (res = {}))[key] = attrs[key];
      }
    }

    return res;
  };

  var filterModelListeners = function filterModelListeners(attrs, props) {
    var res = {};

    for (var key in attrs) {
      if (!isModelListener(key) || !(key.slice(9) in props)) {
        res[key] = attrs[key];
      }
    }

    return res;
  };

  function shouldUpdateComponent(prevVNode, nextVNode, optimized) {
    var prevProps = prevVNode.props,
        prevChildren = prevVNode.children,
        component = prevVNode.component;
    var nextProps = nextVNode.props,
        nextChildren = nextVNode.children,
        patchFlag = nextVNode.patchFlag;
    var emits = component.emitsOptions; // Parent component's render function was hot-updated. Since this may have

    if (nextVNode.dirs || nextVNode.transition) {
      return true;
    }

    if (optimized && patchFlag >= 0) {
      if (patchFlag & 1024
      /* PatchFlags.DYNAMIC_SLOTS */
      ) {
        // slot content that references values that might have changed,
        // e.g. in a v-for
        return true;
      }

      if (patchFlag & 16
      /* PatchFlags.FULL_PROPS */
      ) {
        if (!prevProps) {
          return !!nextProps;
        } // presence of this flag indicates props are always non-null


        return hasPropsChanged(prevProps, nextProps, emits);
      } else if (patchFlag & 8
      /* PatchFlags.PROPS */
      ) {
        var dynamicProps = nextVNode.dynamicProps;

        for (var i = 0; i < dynamicProps.length; i++) {
          var key = dynamicProps[i];

          if (nextProps[key] !== prevProps[key] && !isEmitListener(emits, key)) {
            return true;
          }
        }
      }
    } else {
      // this path is only taken by manually written render functions
      // so presence of any children leads to a forced update
      if (prevChildren || nextChildren) {
        if (!nextChildren || !nextChildren.$stable) {
          return true;
        }
      }

      if (prevProps === nextProps) {
        return false;
      }

      if (!prevProps) {
        return !!nextProps;
      }

      if (!nextProps) {
        return true;
      }

      return hasPropsChanged(prevProps, nextProps, emits);
    }

    return false;
  }

  function hasPropsChanged(prevProps, nextProps, emitsOptions) {
    var nextKeys = Object.keys(nextProps);

    if (nextKeys.length !== Object.keys(prevProps).length) {
      return true;
    }

    for (var i = 0; i < nextKeys.length; i++) {
      var key = nextKeys[i];

      if (nextProps[key] !== prevProps[key] && !isEmitListener(emitsOptions, key)) {
        return true;
      }
    }

    return false;
  }

  function updateHOCHostEl(_ref4, el // HostNode
  ) {
    var vnode = _ref4.vnode,
        parent = _ref4.parent;

    while (parent && parent.subTree === vnode) {
      (vnode = parent.vnode).el = el;
      parent = parent.parent;
    }
  }

  var isSuspense = function isSuspense(type) {
    return type.__isSuspense;
  }; // Suspense exposes a component-like API, and is treated like a component


  function queueEffectWithSuspense(fn, suspense) {
    if (suspense && suspense.pendingBranch) {
      if (isArray(fn)) {
        var _suspense$effects;

        (_suspense$effects = suspense.effects).push.apply(_suspense$effects, fn);
      } else {
        suspense.effects.push(fn);
      }
    } else {
      queuePostFlushCb(fn);
    }
  }

  function provide(key, value) {
    if (!currentInstance) ;else {
      var provides = currentInstance.provides; // by default an instance inherits its parent's provides object
      // but when it needs to provide values of its own, it creates its
      // own provides object using parent provides object as prototype.
      // this way in `inject` we can simply look up injections from direct
      // parent and let the prototype chain do the work.

      var parentProvides = currentInstance.parent && currentInstance.parent.provides;

      if (parentProvides === provides) {
        provides = currentInstance.provides = Object.create(parentProvides);
      } // TS doesn't allow symbol as index type


      provides[key] = value;
    }
  }

  function inject(key, defaultValue, treatDefaultAsFactory) {
    if (treatDefaultAsFactory === void 0) {
      treatDefaultAsFactory = false;
    } // fallback to `currentRenderingInstance` so that this can be called in
    // a functional component


    var instance = currentInstance || currentRenderingInstance;

    if (instance) {
      // #2400
      // to support `app.use` plugins,
      // fallback to appContext's `provides` if the instance is at root
      var provides = instance.parent == null ? instance.vnode.appContext && instance.vnode.appContext.provides : instance.parent.provides;

      if (provides && key in provides) {
        // TS doesn't allow symbol as index type
        return provides[key];
      } else if (arguments.length > 1) {
        return treatDefaultAsFactory && isFunction(defaultValue) ? defaultValue.call(instance.proxy) : defaultValue;
      } else ;
    }
  } // Simple effect.


  var INITIAL_WATCHER_VALUE = {}; // implementation

  function watch(source, cb, options) {
    return doWatch(source, cb, options);
  }

  function doWatch(source, cb, _temp) {
    var _ref23 = _temp === void 0 ? EMPTY_OBJ : _temp,
        immediate = _ref23.immediate,
        deep = _ref23.deep,
        flush = _ref23.flush;
        _ref23.onTrack;
        _ref23.onTrigger;

    var instance = currentInstance;
    var getter;
    var forceTrigger = false;
    var isMultiSource = false;

    if (isRef(source)) {
      getter = function getter() {
        return source.value;
      };

      forceTrigger = isShallow(source);
    } else if (isReactive(source)) {
      getter = function getter() {
        return source;
      };

      deep = true;
    } else if (isArray(source)) {
      isMultiSource = true;
      forceTrigger = source.some(function (s) {
        return isReactive(s) || isShallow(s);
      });

      getter = function getter() {
        return source.map(function (s) {
          if (isRef(s)) {
            return s.value;
          } else if (isReactive(s)) {
            return traverse(s);
          } else if (isFunction(s)) {
            return callWithErrorHandling(s, instance, 2
            /* ErrorCodes.WATCH_GETTER */
            );
          } else ;
        });
      };
    } else if (isFunction(source)) {
      if (cb) {
        // getter with cb
        getter = function getter() {
          return callWithErrorHandling(source, instance, 2
          /* ErrorCodes.WATCH_GETTER */
          );
        };
      } else {
        // no cb -> simple effect
        getter = function getter() {
          if (instance && instance.isUnmounted) {
            return;
          }

          if (cleanup) {
            cleanup();
          }

          return callWithAsyncErrorHandling(source, instance, 3
          /* ErrorCodes.WATCH_CALLBACK */
          , [onCleanup]);
        };
      }
    } else {
      getter = NOOP;
    }

    if (cb && deep) {
      var baseGetter = getter;

      getter = function getter() {
        return traverse(baseGetter());
      };
    }

    var cleanup;

    var onCleanup = function onCleanup(fn) {
      cleanup = effect.onStop = function () {
        callWithErrorHandling(fn, instance, 4
        /* ErrorCodes.WATCH_CLEANUP */
        );
      };
    }; // in SSR there is no need to setup an actual effect, and it should be noop
    // unless it's eager or sync flush


    var ssrCleanup;

    if (isInSSRComponentSetup) {
      // we will also not call the invalidate callback (+ runner is not set up)
      onCleanup = NOOP;

      if (!cb) {
        getter();
      } else if (immediate) {
        callWithAsyncErrorHandling(cb, instance, 3
        /* ErrorCodes.WATCH_CALLBACK */
        , [getter(), isMultiSource ? [] : undefined, onCleanup]);
      }

      if (flush === 'sync') {
        var ctx = useSSRContext();
        ssrCleanup = ctx.__watcherHandles || (ctx.__watcherHandles = []);
      } else {
        return NOOP;
      }
    }

    var oldValue = isMultiSource ? new Array(source.length).fill(INITIAL_WATCHER_VALUE) : INITIAL_WATCHER_VALUE;

    var job = function job() {
      if (!effect.active) {
        return;
      }

      if (cb) {
        // watch(source, cb)
        var newValue = effect.run();

        if (deep || forceTrigger || (isMultiSource ? newValue.some(function (v, i) {
          return hasChanged(v, oldValue[i]);
        }) : hasChanged(newValue, oldValue)) || false) {
          // cleanup before running cb again
          if (cleanup) {
            cleanup();
          }

          callWithAsyncErrorHandling(cb, instance, 3
          /* ErrorCodes.WATCH_CALLBACK */
          , [newValue, // pass undefined as the old value when it's changed for the first time
          oldValue === INITIAL_WATCHER_VALUE ? undefined : isMultiSource && oldValue[0] === INITIAL_WATCHER_VALUE ? [] : oldValue, onCleanup]);
          oldValue = newValue;
        }
      } else {
        // watchEffect
        effect.run();
      }
    }; // important: mark the job as a watcher callback so that scheduler knows
    // it is allowed to self-trigger (#1727)


    job.allowRecurse = !!cb;
    var scheduler;

    if (flush === 'sync') {
      scheduler = job; // the scheduler function gets called directly
    } else if (flush === 'post') {
      scheduler = function scheduler() {
        return queuePostRenderEffect(job, instance && instance.suspense);
      };
    } else {
      // default: 'pre'
      job.pre = true;
      if (instance) job.id = instance.uid;

      scheduler = function scheduler() {
        return queueJob(job);
      };
    }

    var effect = new ReactiveEffect(getter, scheduler);

    if (cb) {
      if (immediate) {
        job();
      } else {
        oldValue = effect.run();
      }
    } else if (flush === 'post') {
      queuePostRenderEffect(effect.run.bind(effect), instance && instance.suspense);
    } else {
      effect.run();
    }

    var unwatch = function unwatch() {
      effect.stop();

      if (instance && instance.scope) {
        remove(instance.scope.effects, effect);
      }
    };

    if (ssrCleanup) ssrCleanup.push(unwatch);
    return unwatch;
  } // this.$watch


  function instanceWatch(source, value, options) {
    var publicThis = this.proxy;
    var getter = isString(source) ? source.includes('.') ? createPathGetter(publicThis, source) : function () {
      return publicThis[source];
    } : source.bind(publicThis, publicThis);
    var cb;

    if (isFunction(value)) {
      cb = value;
    } else {
      cb = value.handler;
      options = value;
    }

    var cur = currentInstance;
    setCurrentInstance(this);
    var res = doWatch(getter, cb.bind(publicThis), options);

    if (cur) {
      setCurrentInstance(cur);
    } else {
      unsetCurrentInstance();
    }

    return res;
  }

  function createPathGetter(ctx, path) {
    var segments = path.split('.');
    return function () {
      var cur = ctx;

      for (var i = 0; i < segments.length && cur; i++) {
        cur = cur[segments[i]];
      }

      return cur;
    };
  }

  function traverse(value, seen) {
    if (!isObject$1(value) || value["__v_skip"
    /* ReactiveFlags.SKIP */
    ]) {
      return value;
    }

    seen = seen || new Set();

    if (seen.has(value)) {
      return value;
    }

    seen.add(value);

    if (isRef(value)) {
      traverse(value.value, seen);
    } else if (isArray(value)) {
      for (var i = 0; i < value.length; i++) {
        traverse(value[i], seen);
      }
    } else if (isSet(value) || isMap(value)) {
      value.forEach(function (v) {
        traverse(v, seen);
      });
    } else if (isPlainObject(value)) {
      for (var key in value) {
        traverse(value[key], seen);
      }
    }

    return value;
  }

  function useTransitionState() {
    var state = {
      isMounted: false,
      isLeaving: false,
      isUnmounting: false,
      leavingVNodes: new Map()
    };
    onMounted(function () {
      state.isMounted = true;
    });
    onBeforeUnmount(function () {
      state.isUnmounting = true;
    });
    return state;
  }

  var TransitionHookValidator = [Function, Array];
  var BaseTransitionImpl = {
    name: "BaseTransition",
    props: {
      mode: String,
      appear: Boolean,
      persisted: Boolean,
      // enter
      onBeforeEnter: TransitionHookValidator,
      onEnter: TransitionHookValidator,
      onAfterEnter: TransitionHookValidator,
      onEnterCancelled: TransitionHookValidator,
      // leave
      onBeforeLeave: TransitionHookValidator,
      onLeave: TransitionHookValidator,
      onAfterLeave: TransitionHookValidator,
      onLeaveCancelled: TransitionHookValidator,
      // appear
      onBeforeAppear: TransitionHookValidator,
      onAppear: TransitionHookValidator,
      onAfterAppear: TransitionHookValidator,
      onAppearCancelled: TransitionHookValidator
    },
    setup: function setup(props, _ref6) {
      var slots = _ref6.slots;
      var instance = getCurrentInstance();
      var state = useTransitionState();
      var prevTransitionKey;
      return function () {
        var children = slots.default && getTransitionRawChildren(slots.default(), true);

        if (!children || !children.length) {
          return;
        }

        var child = children[0];

        if (children.length > 1) {
          for (var _iterator4 = _createForOfIteratorHelperLoose(children), _step4; !(_step4 = _iterator4()).done;) {
            var c = _step4.value;

            if (c.type !== Comment) {
              child = c;
              break;
            }
          }
        } // there's no need to track reactivity for these props so use the raw
        // props for a bit better perf


        var rawProps = toRaw(props);
        var mode = rawProps.mode; // check mode

        if (state.isLeaving) {
          return emptyPlaceholder(child);
        } // in the case of <transition><keep-alive/></transition>, we need to
        // compare the type of the kept-alive children.


        var innerChild = getKeepAliveChild(child);

        if (!innerChild) {
          return emptyPlaceholder(child);
        }

        var enterHooks = resolveTransitionHooks(innerChild, rawProps, state, instance);
        setTransitionHooks(innerChild, enterHooks);
        var oldChild = instance.subTree;
        var oldInnerChild = oldChild && getKeepAliveChild(oldChild);
        var transitionKeyChanged = false;
        var getTransitionKey = innerChild.type.getTransitionKey;

        if (getTransitionKey) {
          var key = getTransitionKey();

          if (prevTransitionKey === undefined) {
            prevTransitionKey = key;
          } else if (key !== prevTransitionKey) {
            prevTransitionKey = key;
            transitionKeyChanged = true;
          }
        } // handle mode


        if (oldInnerChild && oldInnerChild.type !== Comment && (!isSameVNodeType(innerChild, oldInnerChild) || transitionKeyChanged)) {
          var leavingHooks = resolveTransitionHooks(oldInnerChild, rawProps, state, instance); // update old tree's hooks in case of dynamic transition

          setTransitionHooks(oldInnerChild, leavingHooks); // switching between different views

          if (mode === 'out-in') {
            state.isLeaving = true; // return placeholder node and queue update when leave finishes

            leavingHooks.afterLeave = function () {
              state.isLeaving = false; // #6835
              // it also needs to be updated when active is undefined

              if (instance.update.active !== false) {
                instance.update();
              }
            };

            return emptyPlaceholder(child);
          } else if (mode === 'in-out' && innerChild.type !== Comment) {
            leavingHooks.delayLeave = function (el, earlyRemove, delayedLeave) {
              var leavingVNodesCache = getLeavingNodesForType(state, oldInnerChild);
              leavingVNodesCache[String(oldInnerChild.key)] = oldInnerChild; // early removal callback

              el._leaveCb = function () {
                earlyRemove();
                el._leaveCb = undefined;
                delete enterHooks.delayedLeave;
              };

              enterHooks.delayedLeave = delayedLeave;
            };
          }
        }

        return child;
      };
    }
  }; // export the public type for h/tsx inference
  // also to avoid inline import() in generated d.ts files

  var BaseTransition = BaseTransitionImpl;

  function getLeavingNodesForType(state, vnode) {
    var leavingVNodes = state.leavingVNodes;
    var leavingVNodesCache = leavingVNodes.get(vnode.type);

    if (!leavingVNodesCache) {
      leavingVNodesCache = Object.create(null);
      leavingVNodes.set(vnode.type, leavingVNodesCache);
    }

    return leavingVNodesCache;
  } // The transition hooks are attached to the vnode as vnode.transition
  // and will be called at appropriate timing in the renderer.


  function resolveTransitionHooks(vnode, props, state, instance) {
    var appear = props.appear,
        mode = props.mode,
        _props$persisted = props.persisted,
        persisted = _props$persisted === void 0 ? false : _props$persisted,
        onBeforeEnter = props.onBeforeEnter,
        onEnter = props.onEnter,
        onAfterEnter = props.onAfterEnter,
        onEnterCancelled = props.onEnterCancelled,
        onBeforeLeave = props.onBeforeLeave,
        onLeave = props.onLeave,
        onAfterLeave = props.onAfterLeave,
        onLeaveCancelled = props.onLeaveCancelled,
        onBeforeAppear = props.onBeforeAppear,
        onAppear = props.onAppear,
        onAfterAppear = props.onAfterAppear,
        onAppearCancelled = props.onAppearCancelled;
    var key = String(vnode.key);
    var leavingVNodesCache = getLeavingNodesForType(state, vnode);

    var callHook = function callHook(hook, args) {
      hook && callWithAsyncErrorHandling(hook, instance, 9
      /* ErrorCodes.TRANSITION_HOOK */
      , args);
    };

    var callAsyncHook = function callAsyncHook(hook, args) {
      var done = args[1];
      callHook(hook, args);

      if (isArray(hook)) {
        if (hook.every(function (hook) {
          return hook.length <= 1;
        })) done();
      } else if (hook.length <= 1) {
        done();
      }
    };

    var hooks = {
      mode: mode,
      persisted: persisted,
      beforeEnter: function beforeEnter(el) {
        var hook = onBeforeEnter;

        if (!state.isMounted) {
          if (appear) {
            hook = onBeforeAppear || onBeforeEnter;
          } else {
            return;
          }
        } // for same element (v-show)


        if (el._leaveCb) {
          el._leaveCb(true
          /* cancelled */
          );
        } // for toggled element with same key (v-if)


        var leavingVNode = leavingVNodesCache[key];

        if (leavingVNode && isSameVNodeType(vnode, leavingVNode) && leavingVNode.el._leaveCb) {
          // force early removal (not cancelled)
          leavingVNode.el._leaveCb();
        }

        callHook(hook, [el]);
      },
      enter: function enter(el) {
        var hook = onEnter;
        var afterHook = onAfterEnter;
        var cancelHook = onEnterCancelled;

        if (!state.isMounted) {
          if (appear) {
            hook = onAppear || onEnter;
            afterHook = onAfterAppear || onAfterEnter;
            cancelHook = onAppearCancelled || onEnterCancelled;
          } else {
            return;
          }
        }

        var called = false;

        var done = el._enterCb = function (cancelled) {
          if (called) return;
          called = true;

          if (cancelled) {
            callHook(cancelHook, [el]);
          } else {
            callHook(afterHook, [el]);
          }

          if (hooks.delayedLeave) {
            hooks.delayedLeave();
          }

          el._enterCb = undefined;
        };

        if (hook) {
          callAsyncHook(hook, [el, done]);
        } else {
          done();
        }
      },
      leave: function leave(el, remove) {
        var key = String(vnode.key);

        if (el._enterCb) {
          el._enterCb(true
          /* cancelled */
          );
        }

        if (state.isUnmounting) {
          return remove();
        }

        callHook(onBeforeLeave, [el]);
        var called = false;

        var done = el._leaveCb = function (cancelled) {
          if (called) return;
          called = true;
          remove();

          if (cancelled) {
            callHook(onLeaveCancelled, [el]);
          } else {
            callHook(onAfterLeave, [el]);
          }

          el._leaveCb = undefined;

          if (leavingVNodesCache[key] === vnode) {
            delete leavingVNodesCache[key];
          }
        };

        leavingVNodesCache[key] = vnode;

        if (onLeave) {
          callAsyncHook(onLeave, [el, done]);
        } else {
          done();
        }
      },
      clone: function clone(vnode) {
        return resolveTransitionHooks(vnode, props, state, instance);
      }
    };
    return hooks;
  } // the placeholder really only handles one special case: KeepAlive
  // in the case of a KeepAlive in a leave phase we need to return a KeepAlive
  // placeholder with empty content to avoid the KeepAlive instance from being
  // unmounted.


  function emptyPlaceholder(vnode) {
    if (isKeepAlive(vnode)) {
      vnode = cloneVNode(vnode);
      vnode.children = null;
      return vnode;
    }
  }

  function getKeepAliveChild(vnode) {
    return isKeepAlive(vnode) ? vnode.children ? vnode.children[0] : undefined : vnode;
  }

  function setTransitionHooks(vnode, hooks) {
    if (vnode.shapeFlag & 6
    /* ShapeFlags.COMPONENT */
    && vnode.component) {
      setTransitionHooks(vnode.component.subTree, hooks);
    } else if (vnode.shapeFlag & 128
    /* ShapeFlags.SUSPENSE */
    ) {
      vnode.ssContent.transition = hooks.clone(vnode.ssContent);
      vnode.ssFallback.transition = hooks.clone(vnode.ssFallback);
    } else {
      vnode.transition = hooks;
    }
  }

  function getTransitionRawChildren(children, keepComment, parentKey) {
    if (keepComment === void 0) {
      keepComment = false;
    }

    var ret = [];
    var keyedFragmentCount = 0;

    for (var i = 0; i < children.length; i++) {
      var child = children[i]; // #5360 inherit parent key in case of <template v-for>

      var key = parentKey == null ? child.key : String(parentKey) + String(child.key != null ? child.key : i); // handle fragment children case, e.g. v-for

      if (child.type === Fragment) {
        if (child.patchFlag & 128
        /* PatchFlags.KEYED_FRAGMENT */
        ) keyedFragmentCount++;
        ret = ret.concat(getTransitionRawChildren(child.children, keepComment, key));
      } // comment placeholders should be skipped, e.g. v-if
      else if (keepComment || child.type !== Comment) {
        ret.push(key != null ? cloneVNode(child, {
          key: key
        }) : child);
      }
    } // #1126 if a transition children list contains multiple sub fragments, these
    // fragments will be merged into a flat children array. Since each v-for
    // fragment may contain different static bindings inside, we need to de-op
    // these children to force full diffs to ensure correct behavior.


    if (keyedFragmentCount > 1) {
      for (var _i = 0; _i < ret.length; _i++) {
        ret[_i].patchFlag = -2
        /* PatchFlags.BAIL */
        ;
      }
    }

    return ret;
  } // implementation, close to no-op


  var isAsyncWrapper = function isAsyncWrapper(i) {
    return !!i.type.__asyncLoader;
  };

  var isKeepAlive = function isKeepAlive(vnode) {
    return vnode.type.__isKeepAlive;
  };

  function onActivated(hook, target) {
    registerKeepAliveHook(hook, "a"
    /* LifecycleHooks.ACTIVATED */
    , target);
  }

  function onDeactivated(hook, target) {
    registerKeepAliveHook(hook, "da"
    /* LifecycleHooks.DEACTIVATED */
    , target);
  }

  function registerKeepAliveHook(hook, type, target) {
    if (target === void 0) {
      target = currentInstance;
    } // cache the deactivate branch check wrapper for injected hooks so the same
    // hook can be properly deduped by the scheduler. "__wdc" stands for "with
    // deactivation check".


    var wrappedHook = hook.__wdc || (hook.__wdc = function () {
      // only fire the hook if the target instance is NOT in a deactivated branch.
      var current = target;

      while (current) {
        if (current.isDeactivated) {
          return;
        }

        current = current.parent;
      }

      return hook();
    });

    injectHook(type, wrappedHook, target); // In addition to registering it on the target instance, we walk up the parent
    // chain and register it on all ancestor instances that are keep-alive roots.
    // This avoids the need to walk the entire component tree when invoking these
    // hooks, and more importantly, avoids the need to track child components in
    // arrays.

    if (target) {
      var current = target.parent;

      while (current && current.parent) {
        if (isKeepAlive(current.parent.vnode)) {
          injectToKeepAliveRoot(wrappedHook, type, target, current);
        }

        current = current.parent;
      }
    }
  }

  function injectToKeepAliveRoot(hook, type, target, keepAliveRoot) {
    // injectHook wraps the original for error handling, so make sure to remove
    // the wrapped version.
    var injected = injectHook(type, hook, keepAliveRoot, true
    /* prepend */
    );
    onUnmounted(function () {
      remove(keepAliveRoot[type], injected);
    }, target);
  }

  function injectHook(type, hook, target, prepend) {
    if (target === void 0) {
      target = currentInstance;
    }

    if (prepend === void 0) {
      prepend = false;
    }

    if (target) {
      var hooks = target[type] || (target[type] = []); // cache the error handling wrapper for injected hooks so the same hook
      // can be properly deduped by the scheduler. "__weh" stands for "with error
      // handling".

      var wrappedHook = hook.__weh || (hook.__weh = function () {
        if (target.isUnmounted) {
          return;
        } // disable tracking inside all lifecycle hooks
        // since they can potentially be called inside effects.


        pauseTracking(); // Set currentInstance during hook invocation.
        // This assumes the hook does not synchronously trigger other hooks, which
        // can only be false when the user does something really funky.

        setCurrentInstance(target);

        for (var _len4 = arguments.length, args = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
          args[_key4] = arguments[_key4];
        }

        var res = callWithAsyncErrorHandling(hook, target, type, args);
        unsetCurrentInstance();
        resetTracking();
        return res;
      });

      if (prepend) {
        hooks.unshift(wrappedHook);
      } else {
        hooks.push(wrappedHook);
      }

      return wrappedHook;
    }
  }

  var createHook = function createHook(lifecycle) {
    return function (hook, target) {
      if (target === void 0) {
        target = currentInstance;
      }

      return (// post-create lifecycle registrations are noops during SSR (except for serverPrefetch)
        (!isInSSRComponentSetup || lifecycle === "sp"
        /* LifecycleHooks.SERVER_PREFETCH */
        ) && injectHook(lifecycle, function () {
          return hook.apply(void 0, arguments);
        }, target)
      );
    };
  };

  var onBeforeMount = createHook("bm"
  /* LifecycleHooks.BEFORE_MOUNT */
  );
  var onMounted = createHook("m"
  /* LifecycleHooks.MOUNTED */
  );
  var onBeforeUpdate = createHook("bu"
  /* LifecycleHooks.BEFORE_UPDATE */
  );
  var onUpdated = createHook("u"
  /* LifecycleHooks.UPDATED */
  );
  var onBeforeUnmount = createHook("bum"
  /* LifecycleHooks.BEFORE_UNMOUNT */
  );
  var onUnmounted = createHook("um"
  /* LifecycleHooks.UNMOUNTED */
  );
  var onServerPrefetch = createHook("sp"
  /* LifecycleHooks.SERVER_PREFETCH */
  );
  var onRenderTriggered = createHook("rtg"
  /* LifecycleHooks.RENDER_TRIGGERED */
  );
  var onRenderTracked = createHook("rtc"
  /* LifecycleHooks.RENDER_TRACKED */
  );

  function onErrorCaptured(hook, target) {
    if (target === void 0) {
      target = currentInstance;
    }

    injectHook("ec"
    /* LifecycleHooks.ERROR_CAPTURED */
    , hook, target);
  }
  /**
   * Adds directives to a VNode.
   */


  function withDirectives(vnode, directives) {
    var internalInstance = currentRenderingInstance;

    if (internalInstance === null) {
      return vnode;
    }

    var instance = getExposeProxy(internalInstance) || internalInstance.proxy;
    var bindings = vnode.dirs || (vnode.dirs = []);

    for (var i = 0; i < directives.length; i++) {
      var _directives$i = directives[i],
          dir = _directives$i[0],
          value = _directives$i[1],
          arg = _directives$i[2],
          _directives$i$ = _directives$i[3],
          modifiers = _directives$i$ === void 0 ? EMPTY_OBJ : _directives$i$;

      if (dir) {
        if (isFunction(dir)) {
          dir = {
            mounted: dir,
            updated: dir
          };
        }

        if (dir.deep) {
          traverse(value);
        }

        bindings.push({
          dir: dir,
          instance: instance,
          value: value,
          oldValue: void 0,
          arg: arg,
          modifiers: modifiers
        });
      }
    }

    return vnode;
  }

  function invokeDirectiveHook(vnode, prevVNode, instance, name) {
    var bindings = vnode.dirs;
    var oldBindings = prevVNode && prevVNode.dirs;

    for (var i = 0; i < bindings.length; i++) {
      var binding = bindings[i];

      if (oldBindings) {
        binding.oldValue = oldBindings[i].value;
      }

      var hook = binding.dir[name];

      if (hook) {
        // disable tracking inside all lifecycle hooks
        // since they can potentially be called inside effects.
        pauseTracking();
        callWithAsyncErrorHandling(hook, instance, 8
        /* ErrorCodes.DIRECTIVE_HOOK */
        , [vnode.el, binding, vnode, prevVNode]);
        resetTracking();
      }
    }
  }

  var COMPONENTS = 'components';
  /**
   * @private
   */

  function resolveComponent(name, maybeSelfReference) {
    return resolveAsset(COMPONENTS, name, true, maybeSelfReference) || name;
  }

  var NULL_DYNAMIC_COMPONENT = Symbol();

  function resolveAsset(type, name, warnMissing, maybeSelfReference) {
    if (maybeSelfReference === void 0) {
      maybeSelfReference = false;
    }

    var instance = currentRenderingInstance || currentInstance;

    if (instance) {
      var Component = instance.type; // explicit self name has highest priority

      if (type === COMPONENTS) {
        var selfName = getComponentName(Component, false
        /* do not include inferred name to avoid breaking existing code */
        );

        if (selfName && (selfName === name || selfName === camelize(name) || selfName === capitalize(camelize(name)))) {
          return Component;
        }
      }

      var res = // local registration
      // check instance[type] first which is resolved for options API
      resolve(instance[type] || Component[type], name) || // global registration
      resolve(instance.appContext[type], name);

      if (!res && maybeSelfReference) {
        // fallback to implicit self-reference
        return Component;
      }

      return res;
    }
  }

  function resolve(registry, name) {
    return registry && (registry[name] || registry[camelize(name)] || registry[capitalize(camelize(name))]);
  }
  /**
   * Actual implementation
   */


  function renderList(source, renderItem, cache, index) {
    var ret;
    var cached = cache && cache[index];

    if (isArray(source) || isString(source)) {
      ret = new Array(source.length);

      for (var i = 0, l = source.length; i < l; i++) {
        ret[i] = renderItem(source[i], i, undefined, cached && cached[i]);
      }
    } else if (typeof source === 'number') {
      ret = new Array(source);

      for (var _i2 = 0; _i2 < source; _i2++) {
        ret[_i2] = renderItem(_i2 + 1, _i2, undefined, cached && cached[_i2]);
      }
    } else if (isObject$1(source)) {
      if (source[Symbol.iterator]) {
        ret = Array.from(source, function (item, i) {
          return renderItem(item, i, undefined, cached && cached[i]);
        });
      } else {
        var keys = Object.keys(source);
        ret = new Array(keys.length);

        for (var _i3 = 0, _l = keys.length; _i3 < _l; _i3++) {
          var key = keys[_i3];
          ret[_i3] = renderItem(source[key], key, _i3, cached && cached[_i3]);
        }
      }
    } else {
      ret = [];
    }

    if (cache) {
      cache[index] = ret;
    }

    return ret;
  }
  /**
   * Compiler runtime helper for rendering `<slot/>`
   * @private
   */


  function renderSlot(slots, name, props, // this is not a user-facing function, so the fallback is always generated by
  // the compiler and guaranteed to be a function returning an array
  fallback, noSlotted) {
    if (props === void 0) {
      props = {};
    }

    if (currentRenderingInstance.isCE || currentRenderingInstance.parent && isAsyncWrapper(currentRenderingInstance.parent) && currentRenderingInstance.parent.isCE) {
      if (name !== 'default') props.name = name;
      return createVNode('slot', props, fallback && fallback());
    }

    var slot = slots[name]; // invocation interfering with template-based block tracking, but in
    // `renderSlot` we can be sure that it's template-based so we can force
    // enable it.

    if (slot && slot._c) {
      slot._d = false;
    }

    openBlock();
    var validSlotContent = slot && ensureValidVNode(slot(props));
    var rendered = createBlock(Fragment, {
      key: props.key || // slot content array of a dynamic conditional slot may have a branch
      // key attached in the `createSlots` helper, respect that
      validSlotContent && validSlotContent.key || "_" + name
    }, validSlotContent || (fallback ? fallback() : []), validSlotContent && slots._ === 1
    /* SlotFlags.STABLE */
    ? 64
    /* PatchFlags.STABLE_FRAGMENT */
    : -2
    /* PatchFlags.BAIL */
    );

    if (!noSlotted && rendered.scopeId) {
      rendered.slotScopeIds = [rendered.scopeId + '-s'];
    }

    if (slot && slot._c) {
      slot._d = true;
    }

    return rendered;
  }

  function ensureValidVNode(vnodes) {
    return vnodes.some(function (child) {
      if (!isVNode(child)) return true;
      if (child.type === Comment) return false;
      if (child.type === Fragment && !ensureValidVNode(child.children)) return false;
      return true;
    }) ? vnodes : null;
  }
  /**
   * #2437 In Vue 3, functional components do not have a public instance proxy but
   * they exist in the internal parent chain. For code that relies on traversing
   * public $parent chains, skip functional ones and go to the parent instead.
   */


  var getPublicInstance = function getPublicInstance(i) {
    if (!i) return null;
    if (isStatefulComponent(i)) return getExposeProxy(i) || i.proxy;
    return getPublicInstance(i.parent);
  };

  var publicPropertiesMap = // Move PURE marker to new line to workaround compiler discarding it
  // due to type annotation

  /*#__PURE__*/
  extend(Object.create(null), {
    $: function $(i) {
      return i;
    },
    $el: function $el(i) {
      return i.vnode.el;
    },
    $data: function $data(i) {
      return i.data;
    },
    $props: function $props(i) {
      return i.props;
    },
    $attrs: function $attrs(i) {
      return i.attrs;
    },
    $slots: function $slots(i) {
      return i.slots;
    },
    $refs: function $refs(i) {
      return i.refs;
    },
    $parent: function $parent(i) {
      return getPublicInstance(i.parent);
    },
    $root: function $root(i) {
      return getPublicInstance(i.root);
    },
    $emit: function $emit(i) {
      return i.emit;
    },
    $options: function $options(i) {
      return resolveMergedOptions(i);
    },
    $forceUpdate: function $forceUpdate(i) {
      return i.f || (i.f = function () {
        return queueJob(i.update);
      });
    },
    $nextTick: function $nextTick(i) {
      return i.n || (i.n = nextTick.bind(i.proxy));
    },
    $watch: function $watch(i) {
      return instanceWatch.bind(i);
    }
  });

  var hasSetupBinding = function hasSetupBinding(state, key) {
    return state !== EMPTY_OBJ && !state.__isScriptSetup && hasOwn(state, key);
  };

  var PublicInstanceProxyHandlers = {
    get: function get(_ref9, key) {
      var instance = _ref9._;
      var ctx = instance.ctx,
          setupState = instance.setupState,
          data = instance.data,
          props = instance.props,
          accessCache = instance.accessCache,
          type = instance.type,
          appContext = instance.appContext; // for internal formatters to know that this is a Vue instance
      // This getter gets called for every property access on the render context
      // during render and is a major hotspot. The most expensive part of this
      // is the multiple hasOwn() calls. It's much faster to do a simple property
      // access on a plain object, so we use an accessCache object (with null
      // prototype) to memoize what access type a key corresponds to.

      var normalizedProps;

      if (key[0] !== '$') {
        var n = accessCache[key];

        if (n !== undefined) {
          switch (n) {
            case 1
            /* AccessTypes.SETUP */
            :
              return setupState[key];

            case 2
            /* AccessTypes.DATA */
            :
              return data[key];

            case 4
            /* AccessTypes.CONTEXT */
            :
              return ctx[key];

            case 3
            /* AccessTypes.PROPS */
            :
              return props[key];
            // default: just fallthrough
          }
        } else if (hasSetupBinding(setupState, key)) {
          accessCache[key] = 1
          /* AccessTypes.SETUP */
          ;
          return setupState[key];
        } else if (data !== EMPTY_OBJ && hasOwn(data, key)) {
          accessCache[key] = 2
          /* AccessTypes.DATA */
          ;
          return data[key];
        } else if ( // only cache other properties when instance has declared (thus stable)
        // props
        (normalizedProps = instance.propsOptions[0]) && hasOwn(normalizedProps, key)) {
          accessCache[key] = 3
          /* AccessTypes.PROPS */
          ;
          return props[key];
        } else if (ctx !== EMPTY_OBJ && hasOwn(ctx, key)) {
          accessCache[key] = 4
          /* AccessTypes.CONTEXT */
          ;
          return ctx[key];
        } else if (shouldCacheAccess) {
          accessCache[key] = 0
          /* AccessTypes.OTHER */
          ;
        }
      }

      var publicGetter = publicPropertiesMap[key];
      var cssModule, globalProperties; // public $xxx properties

      if (publicGetter) {
        if (key === '$attrs') {
          track(instance, "get"
          /* TrackOpTypes.GET */
          , key);
        }

        return publicGetter(instance);
      } else if ( // css module (injected by vue-loader)
      (cssModule = type.__cssModules) && (cssModule = cssModule[key])) {
        return cssModule;
      } else if (ctx !== EMPTY_OBJ && hasOwn(ctx, key)) {
        // user may set custom properties to `this` that start with `$`
        accessCache[key] = 4
        /* AccessTypes.CONTEXT */
        ;
        return ctx[key];
      } else if ( // global properties
      globalProperties = appContext.config.globalProperties, hasOwn(globalProperties, key)) {
        {
          return globalProperties[key];
        }
      } else ;
    },
    set: function set(_ref10, key, value) {
      var instance = _ref10._;
      var data = instance.data,
          setupState = instance.setupState,
          ctx = instance.ctx;

      if (hasSetupBinding(setupState, key)) {
        setupState[key] = value;
        return true;
      } else if (data !== EMPTY_OBJ && hasOwn(data, key)) {
        data[key] = value;
        return true;
      } else if (hasOwn(instance.props, key)) {
        return false;
      }

      if (key[0] === '$' && key.slice(1) in instance) {
        return false;
      } else {
        {
          ctx[key] = value;
        }
      }

      return true;
    },
    has: function has(_ref11, key) {
      var _ref11$_ = _ref11._,
          data = _ref11$_.data,
          setupState = _ref11$_.setupState,
          accessCache = _ref11$_.accessCache,
          ctx = _ref11$_.ctx,
          appContext = _ref11$_.appContext,
          propsOptions = _ref11$_.propsOptions;
      var normalizedProps;
      return !!accessCache[key] || data !== EMPTY_OBJ && hasOwn(data, key) || hasSetupBinding(setupState, key) || (normalizedProps = propsOptions[0]) && hasOwn(normalizedProps, key) || hasOwn(ctx, key) || hasOwn(publicPropertiesMap, key) || hasOwn(appContext.config.globalProperties, key);
    },
    defineProperty: function defineProperty(target, key, descriptor) {
      if (descriptor.get != null) {
        // invalidate key cache of a getter based property #5417
        target._.accessCache[key] = 0;
      } else if (hasOwn(descriptor, 'value')) {
        this.set(target, key, descriptor.value, null);
      }

      return Reflect.defineProperty(target, key, descriptor);
    }
  };
  var shouldCacheAccess = true;

  function applyOptions(instance) {
    var options = resolveMergedOptions(instance);
    var publicThis = instance.proxy;
    var ctx = instance.ctx; // do not cache property access on public proxy during state initialization

    shouldCacheAccess = false; // call beforeCreate first before accessing other options since
    // the hook may mutate resolved options (#2791)

    if (options.beforeCreate) {
      callHook$1(options.beforeCreate, instance, "bc"
      /* LifecycleHooks.BEFORE_CREATE */
      );
    }

    var dataOptions = options.data,
        computedOptions = options.computed,
        methods = options.methods,
        watchOptions = options.watch,
        provideOptions = options.provide,
        injectOptions = options.inject,
        created = options.created,
        beforeMount = options.beforeMount,
        mounted = options.mounted,
        beforeUpdate = options.beforeUpdate,
        updated = options.updated,
        activated = options.activated,
        deactivated = options.deactivated;
        options.beforeDestroy;
        var beforeUnmount = options.beforeUnmount;
        options.destroyed;
        var unmounted = options.unmounted,
        render = options.render,
        renderTracked = options.renderTracked,
        renderTriggered = options.renderTriggered,
        errorCaptured = options.errorCaptured,
        serverPrefetch = options.serverPrefetch,
        expose = options.expose,
        inheritAttrs = options.inheritAttrs,
        components = options.components,
        directives = options.directives;
        options.filters;
    var checkDuplicateProperties = null; // - props (already done outside of this function)
    // - inject
    // - methods
    // - data (deferred since it relies on `this` access)
    // - computed
    // - watch (deferred since it relies on `this` access)

    if (injectOptions) {
      resolveInjections(injectOptions, ctx, checkDuplicateProperties, instance.appContext.config.unwrapInjectedRef);
    }

    if (methods) {
      for (var key in methods) {
        var methodHandler = methods[key];

        if (isFunction(methodHandler)) {
          // In dev mode, we use the `createRenderContext` function to define
          // methods to the proxy target, and those are read-only but
          // reconfigurable, so it needs to be redefined here
          {
            ctx[key] = methodHandler.bind(publicThis);
          }
        }
      }
    }

    if (dataOptions) {
      var data = dataOptions.call(publicThis, publicThis);
      if (!isObject$1(data)) ;else {
        instance.data = reactive(data);
      }
    } // state initialization complete at this point - start caching access


    shouldCacheAccess = true;

    if (computedOptions) {
      var _loop = function _loop(_key5) {
        var opt = computedOptions[_key5];
        var get = isFunction(opt) ? opt.bind(publicThis, publicThis) : isFunction(opt.get) ? opt.get.bind(publicThis, publicThis) : NOOP;
        var set = !isFunction(opt) && isFunction(opt.set) ? opt.set.bind(publicThis) : NOOP;
        var c = computed({
          get: get,
          set: set
        });
        Object.defineProperty(ctx, _key5, {
          enumerable: true,
          configurable: true,
          get: function get() {
            return c.value;
          },
          set: function set(v) {
            return c.value = v;
          }
        });
      };

      for (var _key5 in computedOptions) {
        _loop(_key5);
      }
    }

    if (watchOptions) {
      for (var _key7 in watchOptions) {
        createWatcher(watchOptions[_key7], ctx, publicThis, _key7);
      }
    }

    if (provideOptions) {
      var provides = isFunction(provideOptions) ? provideOptions.call(publicThis) : provideOptions;
      Reflect.ownKeys(provides).forEach(function (key) {
        provide(key, provides[key]);
      });
    }

    if (created) {
      callHook$1(created, instance, "c"
      /* LifecycleHooks.CREATED */
      );
    }

    function registerLifecycleHook(register, hook) {
      if (isArray(hook)) {
        hook.forEach(function (_hook) {
          return register(_hook.bind(publicThis));
        });
      } else if (hook) {
        register(hook.bind(publicThis));
      }
    }

    registerLifecycleHook(onBeforeMount, beforeMount);
    registerLifecycleHook(onMounted, mounted);
    registerLifecycleHook(onBeforeUpdate, beforeUpdate);
    registerLifecycleHook(onUpdated, updated);
    registerLifecycleHook(onActivated, activated);
    registerLifecycleHook(onDeactivated, deactivated);
    registerLifecycleHook(onErrorCaptured, errorCaptured);
    registerLifecycleHook(onRenderTracked, renderTracked);
    registerLifecycleHook(onRenderTriggered, renderTriggered);
    registerLifecycleHook(onBeforeUnmount, beforeUnmount);
    registerLifecycleHook(onUnmounted, unmounted);
    registerLifecycleHook(onServerPrefetch, serverPrefetch);

    if (isArray(expose)) {
      if (expose.length) {
        var exposed = instance.exposed || (instance.exposed = {});
        expose.forEach(function (key) {
          Object.defineProperty(exposed, key, {
            get: function get() {
              return publicThis[key];
            },
            set: function set(val) {
              return publicThis[key] = val;
            }
          });
        });
      } else if (!instance.exposed) {
        instance.exposed = {};
      }
    } // options that are handled when creating the instance but also need to be
    // applied from mixins


    if (render && instance.render === NOOP) {
      instance.render = render;
    }

    if (inheritAttrs != null) {
      instance.inheritAttrs = inheritAttrs;
    } // asset options.


    if (components) instance.components = components;
    if (directives) instance.directives = directives;
  }

  function resolveInjections(injectOptions, ctx, checkDuplicateProperties, unwrapRef) {
    if (unwrapRef === void 0) {
      unwrapRef = false;
    }

    if (isArray(injectOptions)) {
      injectOptions = normalizeInject(injectOptions);
    }

    var _loop2 = function _loop2(key) {
      var opt = injectOptions[key];
      var injected = void 0;

      if (isObject$1(opt)) {
        if ('default' in opt) {
          injected = inject(opt.from || key, opt.default, true
          /* treat default function as factory */
          );
        } else {
          injected = inject(opt.from || key);
        }
      } else {
        injected = inject(opt);
      }

      if (isRef(injected)) {
        // TODO remove the check in 3.3
        if (unwrapRef) {
          Object.defineProperty(ctx, key, {
            enumerable: true,
            configurable: true,
            get: function get() {
              return injected.value;
            },
            set: function set(v) {
              return injected.value = v;
            }
          });
        } else {
          ctx[key] = injected;
        }
      } else {
        ctx[key] = injected;
      }
    };

    for (var key in injectOptions) {
      _loop2(key);
    }
  }

  function callHook$1(hook, instance, type) {
    callWithAsyncErrorHandling(isArray(hook) ? hook.map(function (h) {
      return h.bind(instance.proxy);
    }) : hook.bind(instance.proxy), instance, type);
  }

  function createWatcher(raw, ctx, publicThis, key) {
    var getter = key.includes('.') ? createPathGetter(publicThis, key) : function () {
      return publicThis[key];
    };

    if (isString(raw)) {
      var handler = ctx[raw];

      if (isFunction(handler)) {
        watch(getter, handler);
      }
    } else if (isFunction(raw)) {
      watch(getter, raw.bind(publicThis));
    } else if (isObject$1(raw)) {
      if (isArray(raw)) {
        raw.forEach(function (r) {
          return createWatcher(r, ctx, publicThis, key);
        });
      } else {
        var _handler = isFunction(raw.handler) ? raw.handler.bind(publicThis) : ctx[raw.handler];

        if (isFunction(_handler)) {
          watch(getter, _handler, raw);
        }
      }
    } else ;
  }
  /**
   * Resolve merged options and cache it on the component.
   * This is done only once per-component since the merging does not involve
   * instances.
   */


  function resolveMergedOptions(instance) {
    var base = instance.type;
    var mixins = base.mixins,
        extendsOptions = base.extends;
    var _instance$appContext = instance.appContext,
        globalMixins = _instance$appContext.mixins,
        cache = _instance$appContext.optionsCache,
        optionMergeStrategies = _instance$appContext.config.optionMergeStrategies;
    var cached = cache.get(base);
    var resolved;

    if (cached) {
      resolved = cached;
    } else if (!globalMixins.length && !mixins && !extendsOptions) {
      {
        resolved = base;
      }
    } else {
      resolved = {};

      if (globalMixins.length) {
        globalMixins.forEach(function (m) {
          return mergeOptions(resolved, m, optionMergeStrategies, true);
        });
      }

      mergeOptions(resolved, base, optionMergeStrategies);
    }

    if (isObject$1(base)) {
      cache.set(base, resolved);
    }

    return resolved;
  }

  function mergeOptions(to, from, strats, asMixin) {
    if (asMixin === void 0) {
      asMixin = false;
    }

    var mixins = from.mixins,
        extendsOptions = from.extends;

    if (extendsOptions) {
      mergeOptions(to, extendsOptions, strats, true);
    }

    if (mixins) {
      mixins.forEach(function (m) {
        return mergeOptions(to, m, strats, true);
      });
    }

    for (var key in from) {
      if (asMixin && key === 'expose') ;else {
        var strat = internalOptionMergeStrats[key] || strats && strats[key];
        to[key] = strat ? strat(to[key], from[key]) : from[key];
      }
    }

    return to;
  }

  var internalOptionMergeStrats = {
    data: mergeDataFn,
    props: mergeObjectOptions,
    emits: mergeObjectOptions,
    // objects
    methods: mergeObjectOptions,
    computed: mergeObjectOptions,
    // lifecycle
    beforeCreate: mergeAsArray,
    created: mergeAsArray,
    beforeMount: mergeAsArray,
    mounted: mergeAsArray,
    beforeUpdate: mergeAsArray,
    updated: mergeAsArray,
    beforeDestroy: mergeAsArray,
    beforeUnmount: mergeAsArray,
    destroyed: mergeAsArray,
    unmounted: mergeAsArray,
    activated: mergeAsArray,
    deactivated: mergeAsArray,
    errorCaptured: mergeAsArray,
    serverPrefetch: mergeAsArray,
    // assets
    components: mergeObjectOptions,
    directives: mergeObjectOptions,
    // watch
    watch: mergeWatchOptions,
    // provide / inject
    provide: mergeDataFn,
    inject: mergeInject
  };

  function mergeDataFn(to, from) {
    if (!from) {
      return to;
    }

    if (!to) {
      return from;
    }

    return function mergedDataFn() {
      return extend(isFunction(to) ? to.call(this, this) : to, isFunction(from) ? from.call(this, this) : from);
    };
  }

  function mergeInject(to, from) {
    return mergeObjectOptions(normalizeInject(to), normalizeInject(from));
  }

  function normalizeInject(raw) {
    if (isArray(raw)) {
      var res = {};

      for (var i = 0; i < raw.length; i++) {
        res[raw[i]] = raw[i];
      }

      return res;
    }

    return raw;
  }

  function mergeAsArray(to, from) {
    return to ? [].concat(new Set([].concat(to, from))) : from;
  }

  function mergeObjectOptions(to, from) {
    return to ? extend(extend(Object.create(null), to), from) : from;
  }

  function mergeWatchOptions(to, from) {
    if (!to) return from;
    if (!from) return to;
    var merged = extend(Object.create(null), to);

    for (var key in from) {
      merged[key] = mergeAsArray(to[key], from[key]);
    }

    return merged;
  }

  function initProps(instance, rawProps, isStateful, // result of bitwise flag comparison
  isSSR) {
    if (isSSR === void 0) {
      isSSR = false;
    }

    var props = {};
    var attrs = {};
    def(attrs, InternalObjectKey, 1);
    instance.propsDefaults = Object.create(null);
    setFullProps(instance, rawProps, props, attrs); // ensure all declared prop keys are present

    for (var key in instance.propsOptions[0]) {
      if (!(key in props)) {
        props[key] = undefined;
      }
    } // validation


    if (isStateful) {
      // stateful
      instance.props = isSSR ? props : shallowReactive(props);
    } else {
      if (!instance.type.props) {
        // functional w/ optional props, props === attrs
        instance.props = attrs;
      } else {
        // functional w/ declared props
        instance.props = props;
      }
    }

    instance.attrs = attrs;
  }

  function updateProps(instance, rawProps, rawPrevProps, optimized) {
    var props = instance.props,
        attrs = instance.attrs,
        patchFlag = instance.vnode.patchFlag;
    var rawCurrentProps = toRaw(props);
    var _instance$propsOption2 = instance.propsOptions,
        options = _instance$propsOption2[0];
    var hasAttrsChanged = false;

    if ( // always force full diff in dev
    // - #1942 if hmr is enabled with sfc component
    // - vite#872 non-sfc component used by sfc component
    (optimized || patchFlag > 0) && !(patchFlag & 16
    /* PatchFlags.FULL_PROPS */
    )) {
      if (patchFlag & 8
      /* PatchFlags.PROPS */
      ) {
        // Compiler-generated props & no keys change, just set the updated
        // the props.
        var propsToUpdate = instance.vnode.dynamicProps;

        for (var i = 0; i < propsToUpdate.length; i++) {
          var key = propsToUpdate[i]; // skip if the prop key is a declared emit event listener

          if (isEmitListener(instance.emitsOptions, key)) {
            continue;
          } // PROPS flag guarantees rawProps to be non-null


          var value = rawProps[key];

          if (options) {
            // attr / props separation was done on init and will be consistent
            // in this code path, so just check if attrs have it.
            if (hasOwn(attrs, key)) {
              if (value !== attrs[key]) {
                attrs[key] = value;
                hasAttrsChanged = true;
              }
            } else {
              var camelizedKey = camelize(key);
              props[camelizedKey] = resolvePropValue(options, rawCurrentProps, camelizedKey, value, instance, false
              /* isAbsent */
              );
            }
          } else {
            if (value !== attrs[key]) {
              attrs[key] = value;
              hasAttrsChanged = true;
            }
          }
        }
      }
    } else {
      // full props update.
      if (setFullProps(instance, rawProps, props, attrs)) {
        hasAttrsChanged = true;
      } // in case of dynamic props, check if we need to delete keys from
      // the props object


      var kebabKey;

      for (var _key8 in rawCurrentProps) {
        if (!rawProps || // for camelCase
        !hasOwn(rawProps, _key8) && ( // it's possible the original props was passed in as kebab-case
        // and converted to camelCase (#955)
        (kebabKey = hyphenate(_key8)) === _key8 || !hasOwn(rawProps, kebabKey))) {
          if (options) {
            if (rawPrevProps && ( // for camelCase
            rawPrevProps[_key8] !== undefined || // for kebab-case
            rawPrevProps[kebabKey] !== undefined)) {
              props[_key8] = resolvePropValue(options, rawCurrentProps, _key8, undefined, instance, true
              /* isAbsent */
              );
            }
          } else {
            delete props[_key8];
          }
        }
      } // in the case of functional component w/o props declaration, props and
      // attrs point to the same object so it should already have been updated.


      if (attrs !== rawCurrentProps) {
        for (var _key9 in attrs) {
          if (!rawProps || !hasOwn(rawProps, _key9) && !false) {
            delete attrs[_key9];
            hasAttrsChanged = true;
          }
        }
      }
    } // trigger updates for $attrs in case it's used in component slots


    if (hasAttrsChanged) {
      trigger(instance, "set"
      /* TriggerOpTypes.SET */
      , '$attrs');
    }
  }

  function setFullProps(instance, rawProps, props, attrs) {
    var _instance$propsOption3 = instance.propsOptions,
        options = _instance$propsOption3[0],
        needCastKeys = _instance$propsOption3[1];
    var hasAttrsChanged = false;
    var rawCastValues;

    if (rawProps) {
      for (var key in rawProps) {
        // key, ref are reserved and never passed down
        if (isReservedProp(key)) {
          continue;
        }

        var value = rawProps[key]; // prop option names are camelized during normalization, so to support
        // kebab -> camel conversion here we need to camelize the key.

        var camelKey = void 0;

        if (options && hasOwn(options, camelKey = camelize(key))) {
          if (!needCastKeys || !needCastKeys.includes(camelKey)) {
            props[camelKey] = value;
          } else {
            (rawCastValues || (rawCastValues = {}))[camelKey] = value;
          }
        } else if (!isEmitListener(instance.emitsOptions, key)) {
          if (!(key in attrs) || value !== attrs[key]) {
            attrs[key] = value;
            hasAttrsChanged = true;
          }
        }
      }
    }

    if (needCastKeys) {
      var rawCurrentProps = toRaw(props);
      var castValues = rawCastValues || EMPTY_OBJ;

      for (var i = 0; i < needCastKeys.length; i++) {
        var _key10 = needCastKeys[i];
        props[_key10] = resolvePropValue(options, rawCurrentProps, _key10, castValues[_key10], instance, !hasOwn(castValues, _key10));
      }
    }

    return hasAttrsChanged;
  }

  function resolvePropValue(options, props, key, value, instance, isAbsent) {
    var opt = options[key];

    if (opt != null) {
      var hasDefault = hasOwn(opt, 'default'); // default values

      if (hasDefault && value === undefined) {
        var defaultValue = opt.default;

        if (opt.type !== Function && isFunction(defaultValue)) {
          var propsDefaults = instance.propsDefaults;

          if (key in propsDefaults) {
            value = propsDefaults[key];
          } else {
            setCurrentInstance(instance);
            value = propsDefaults[key] = defaultValue.call(null, props);
            unsetCurrentInstance();
          }
        } else {
          value = defaultValue;
        }
      } // boolean casting


      if (opt[0
      /* BooleanFlags.shouldCast */
      ]) {
        if (isAbsent && !hasDefault) {
          value = false;
        } else if (opt[1
        /* BooleanFlags.shouldCastTrue */
        ] && (value === '' || value === hyphenate(key))) {
          value = true;
        }
      }
    }

    return value;
  }

  function normalizePropsOptions(comp, appContext, asMixin) {
    if (asMixin === void 0) {
      asMixin = false;
    }

    var cache = appContext.propsCache;
    var cached = cache.get(comp);

    if (cached) {
      return cached;
    }

    var raw = comp.props;
    var normalized = {};
    var needCastKeys = []; // apply mixin/extends props

    var hasExtends = false;

    if (!isFunction(comp)) {
      var extendProps = function extendProps(raw) {
        hasExtends = true;

        var _normalizePropsOption = normalizePropsOptions(raw, appContext, true),
            props = _normalizePropsOption[0],
            keys = _normalizePropsOption[1];

        extend(normalized, props);
        if (keys) needCastKeys.push.apply(needCastKeys, keys);
      };

      if (!asMixin && appContext.mixins.length) {
        appContext.mixins.forEach(extendProps);
      }

      if (comp.extends) {
        extendProps(comp.extends);
      }

      if (comp.mixins) {
        comp.mixins.forEach(extendProps);
      }
    }

    if (!raw && !hasExtends) {
      if (isObject$1(comp)) {
        cache.set(comp, EMPTY_ARR);
      }

      return EMPTY_ARR;
    }

    if (isArray(raw)) {
      for (var i = 0; i < raw.length; i++) {
        var normalizedKey = camelize(raw[i]);

        if (validatePropName(normalizedKey)) {
          normalized[normalizedKey] = EMPTY_OBJ;
        }
      }
    } else if (raw) {
      for (var key in raw) {
        var _normalizedKey = camelize(key);

        if (validatePropName(_normalizedKey)) {
          var opt = raw[key];
          var prop = normalized[_normalizedKey] = isArray(opt) || isFunction(opt) ? {
            type: opt
          } : Object.assign({}, opt);

          if (prop) {
            var booleanIndex = getTypeIndex(Boolean, prop.type);
            var stringIndex = getTypeIndex(String, prop.type);
            prop[0
            /* BooleanFlags.shouldCast */
            ] = booleanIndex > -1;
            prop[1
            /* BooleanFlags.shouldCastTrue */
            ] = stringIndex < 0 || booleanIndex < stringIndex; // if the prop needs boolean casting or default value

            if (booleanIndex > -1 || hasOwn(prop, 'default')) {
              needCastKeys.push(_normalizedKey);
            }
          }
        }
      }
    }

    var res = [normalized, needCastKeys];

    if (isObject$1(comp)) {
      cache.set(comp, res);
    }

    return res;
  }

  function validatePropName(key) {
    if (key[0] !== '$') {
      return true;
    }

    return false;
  } // use function string name to check type constructors
  // so that it works across vms / iframes.


  function getType(ctor) {
    var match = ctor && ctor.toString().match(/^\s*function (\w+)/);
    return match ? match[1] : ctor === null ? 'null' : '';
  }

  function isSameType(a, b) {
    return getType(a) === getType(b);
  }

  function getTypeIndex(type, expectedTypes) {
    if (isArray(expectedTypes)) {
      return expectedTypes.findIndex(function (t) {
        return isSameType(t, type);
      });
    } else if (isFunction(expectedTypes)) {
      return isSameType(expectedTypes, type) ? 0 : -1;
    }

    return -1;
  }

  var isInternalKey = function isInternalKey(key) {
    return key[0] === '_' || key === '$stable';
  };

  var normalizeSlotValue = function normalizeSlotValue(value) {
    return isArray(value) ? value.map(normalizeVNode) : [normalizeVNode(value)];
  };

  var normalizeSlot = function normalizeSlot(key, rawSlot, ctx) {
    if (rawSlot._n) {
      // already normalized - #5353
      return rawSlot;
    }

    var normalized = withCtx(function () {
      return normalizeSlotValue(rawSlot.apply(void 0, arguments));
    }, ctx);
    normalized._c = false;
    return normalized;
  };

  var normalizeObjectSlots = function normalizeObjectSlots(rawSlots, slots, instance) {
    var ctx = rawSlots._ctx;

    for (var key in rawSlots) {
      if (isInternalKey(key)) continue;
      var value = rawSlots[key];

      if (isFunction(value)) {
        slots[key] = normalizeSlot(key, value, ctx);
      } else if (value != null) {
        (function () {
          var normalized = normalizeSlotValue(value);

          slots[key] = function () {
            return normalized;
          };
        })();
      }
    }
  };

  var normalizeVNodeSlots = function normalizeVNodeSlots(instance, children) {
    var normalized = normalizeSlotValue(children);

    instance.slots.default = function () {
      return normalized;
    };
  };

  var initSlots = function initSlots(instance, children) {
    if (instance.vnode.shapeFlag & 32
    /* ShapeFlags.SLOTS_CHILDREN */
    ) {
      var type = children._;

      if (type) {
        // users can get the shallow readonly version of the slots object through `this.$slots`,
        // we should avoid the proxy object polluting the slots of the internal instance
        instance.slots = toRaw(children); // make compiler marker non-enumerable

        def(children, '_', type);
      } else {
        normalizeObjectSlots(children, instance.slots = {});
      }
    } else {
      instance.slots = {};

      if (children) {
        normalizeVNodeSlots(instance, children);
      }
    }

    def(instance.slots, InternalObjectKey, 1);
  };

  var updateSlots = function updateSlots(instance, children, optimized) {
    var vnode = instance.vnode,
        slots = instance.slots;
    var needDeletionCheck = true;
    var deletionComparisonTarget = EMPTY_OBJ;

    if (vnode.shapeFlag & 32
    /* ShapeFlags.SLOTS_CHILDREN */
    ) {
      var type = children._;

      if (type) {
        // compiled slots.
        if (optimized && type === 1
        /* SlotFlags.STABLE */
        ) {
          // compiled AND stable.
          // no need to update, and skip stale slots removal.
          needDeletionCheck = false;
        } else {
          // compiled but dynamic (v-if/v-for on slots) - update slots, but skip
          // normalization.
          extend(slots, children); // #2893
          // when rendering the optimized slots by manually written render function,
          // we need to delete the `slots._` flag if necessary to make subsequent updates reliable,
          // i.e. let the `renderSlot` create the bailed Fragment

          if (!optimized && type === 1
          /* SlotFlags.STABLE */
          ) {
            delete slots._;
          }
        }
      } else {
        needDeletionCheck = !children.$stable;
        normalizeObjectSlots(children, slots);
      }

      deletionComparisonTarget = children;
    } else if (children) {
      // non slot object children (direct value) passed to a component
      normalizeVNodeSlots(instance, children);
      deletionComparisonTarget = {
        default: 1
      };
    } // delete stale slots


    if (needDeletionCheck) {
      for (var key in slots) {
        if (!isInternalKey(key) && !(key in deletionComparisonTarget)) {
          delete slots[key];
        }
      }
    }
  };

  function createAppContext() {
    return {
      app: null,
      config: {
        isNativeTag: NO,
        performance: false,
        globalProperties: {},
        optionMergeStrategies: {},
        errorHandler: undefined,
        warnHandler: undefined,
        compilerOptions: {}
      },
      mixins: [],
      components: {},
      directives: {},
      provides: Object.create(null),
      optionsCache: new WeakMap(),
      propsCache: new WeakMap(),
      emitsCache: new WeakMap()
    };
  }

  var uid = 0;

  function createAppAPI(render, hydrate) {
    return function createApp(rootComponent, rootProps) {
      if (rootProps === void 0) {
        rootProps = null;
      }

      if (!isFunction(rootComponent)) {
        rootComponent = Object.assign({}, rootComponent);
      }

      if (rootProps != null && !isObject$1(rootProps)) {
        rootProps = null;
      }

      var context = createAppContext();
      var installedPlugins = new Set();
      var isMounted = false;
      var app = context.app = {
        _uid: uid++,
        _component: rootComponent,
        _props: rootProps,
        _container: null,
        _context: context,
        _instance: null,
        version: version,

        get config() {
          return context.config;
        },

        set config(v) {},

        use: function use(plugin) {
          for (var _len6 = arguments.length, options = new Array(_len6 > 1 ? _len6 - 1 : 0), _key6 = 1; _key6 < _len6; _key6++) {
            options[_key6 - 1] = arguments[_key6];
          }

          if (installedPlugins.has(plugin)) ;else if (plugin && isFunction(plugin.install)) {
            installedPlugins.add(plugin);
            plugin.install.apply(plugin, [app].concat(options));
          } else if (isFunction(plugin)) {
            installedPlugins.add(plugin);
            plugin.apply(void 0, [app].concat(options));
          } else ;
          return app;
        },
        mixin: function mixin(_mixin) {
          {
            if (!context.mixins.includes(_mixin)) {
              context.mixins.push(_mixin);
            }
          }
          return app;
        },
        component: function component(name, _component) {
          if (!_component) {
            return context.components[name];
          }

          context.components[name] = _component;
          return app;
        },
        directive: function directive(name, _directive) {
          if (!_directive) {
            return context.directives[name];
          }

          context.directives[name] = _directive;
          return app;
        },
        mount: function mount(rootContainer, isHydrate, isSVG) {
          if (!isMounted) {
            var vnode = createVNode(rootComponent, rootProps); // store app context on the root VNode.
            // this will be set on the root instance on initial mount.

            vnode.appContext = context; // HMR root reload

            if (isHydrate && hydrate) {
              hydrate(vnode, rootContainer);
            } else {
              render(vnode, rootContainer, isSVG);
            }

            isMounted = true;
            app._container = rootContainer;
            rootContainer.__vue_app__ = app;
            {
              app._instance = vnode.component;
              devtoolsInitApp(app, version);
            }
            return getExposeProxy(vnode.component) || vnode.component.proxy;
          }
        },
        unmount: function unmount() {
          if (isMounted) {
            render(null, app._container);
            {
              app._instance = null;
              devtoolsUnmountApp(app);
            }
            delete app._container.__vue_app__;
          }
        },
        provide: function provide(key, value) {
          context.provides[key] = value;
          return app;
        }
      };
      return app;
    };
  }
  /**
   * Function for handling a template ref
   */


  function setRef(rawRef, oldRawRef, parentSuspense, vnode, isUnmount) {
    if (isUnmount === void 0) {
      isUnmount = false;
    }

    if (isArray(rawRef)) {
      rawRef.forEach(function (r, i) {
        return setRef(r, oldRawRef && (isArray(oldRawRef) ? oldRawRef[i] : oldRawRef), parentSuspense, vnode, isUnmount);
      });
      return;
    }

    if (isAsyncWrapper(vnode) && !isUnmount) {
      // when mounting async components, nothing needs to be done,
      // because the template ref is forwarded to inner component
      return;
    }

    var refValue = vnode.shapeFlag & 4
    /* ShapeFlags.STATEFUL_COMPONENT */
    ? getExposeProxy(vnode.component) || vnode.component.proxy : vnode.el;
    var value = isUnmount ? null : refValue;
    var owner = rawRef.i,
        ref = rawRef.r;
    var oldRef = oldRawRef && oldRawRef.r;
    var refs = owner.refs === EMPTY_OBJ ? owner.refs = {} : owner.refs;
    var setupState = owner.setupState; // dynamic ref changed. unset old ref

    if (oldRef != null && oldRef !== ref) {
      if (isString(oldRef)) {
        refs[oldRef] = null;

        if (hasOwn(setupState, oldRef)) {
          setupState[oldRef] = null;
        }
      } else if (isRef(oldRef)) {
        oldRef.value = null;
      }
    }

    if (isFunction(ref)) {
      callWithErrorHandling(ref, owner, 12
      /* ErrorCodes.FUNCTION_REF */
      , [value, refs]);
    } else {
      var _isString = isString(ref);

      var _isRef = isRef(ref);

      if (_isString || _isRef) {
        var doSet = function doSet() {
          if (rawRef.f) {
            var existing = _isString ? hasOwn(setupState, ref) ? setupState[ref] : refs[ref] : ref.value;

            if (isUnmount) {
              isArray(existing) && remove(existing, refValue);
            } else {
              if (!isArray(existing)) {
                if (_isString) {
                  refs[ref] = [refValue];

                  if (hasOwn(setupState, ref)) {
                    setupState[ref] = refs[ref];
                  }
                } else {
                  ref.value = [refValue];
                  if (rawRef.k) refs[rawRef.k] = ref.value;
                }
              } else if (!existing.includes(refValue)) {
                existing.push(refValue);
              }
            }
          } else if (_isString) {
            refs[ref] = value;

            if (hasOwn(setupState, ref)) {
              setupState[ref] = value;
            }
          } else if (_isRef) {
            ref.value = value;
            if (rawRef.k) refs[rawRef.k] = value;
          } else ;
        };

        if (value) {
          doSet.id = -1;
          queuePostRenderEffect(doSet, parentSuspense);
        } else {
          doSet();
        }
      }
    }
  }

  var queuePostRenderEffect = queueEffectWithSuspense;
  /**
   * The createRenderer function accepts two generic arguments:
   * HostNode and HostElement, corresponding to Node and Element types in the
   * host environment. For example, for runtime-dom, HostNode would be the DOM
   * `Node` interface and HostElement would be the DOM `Element` interface.
   *
   * Custom renderers can pass in the platform specific types like this:
   *
   * ``` js
   * const { render, createApp } = createRenderer<Node, Element>({
   *   patchProp,
   *   ...nodeOps
   * })
   * ```
   */

  function createRenderer(options) {
    return baseCreateRenderer(options);
  } // Separate API for creating hydration-enabled renderer.


  function baseCreateRenderer(options, createHydrationFns) {
    var target = getGlobalThis();
    target.__VUE__ = true;
    {
      setDevtoolsHook(target.__VUE_DEVTOOLS_GLOBAL_HOOK__, target);
    }
    var hostInsert = options.insert,
        hostRemove = options.remove,
        hostPatchProp = options.patchProp,
        hostCreateElement = options.createElement,
        hostCreateText = options.createText,
        hostCreateComment = options.createComment,
        hostSetText = options.setText,
        hostSetElementText = options.setElementText,
        hostParentNode = options.parentNode,
        hostNextSibling = options.nextSibling,
        _options$setScopeId = options.setScopeId,
        hostSetScopeId = _options$setScopeId === void 0 ? NOOP : _options$setScopeId,
        hostInsertStaticContent = options.insertStaticContent; // Note: functions inside this closure should use `const xxx = () => {}`
    // style in order to prevent being inlined by minifiers.

    var patch = function patch(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
      if (anchor === void 0) {
        anchor = null;
      }

      if (parentComponent === void 0) {
        parentComponent = null;
      }

      if (parentSuspense === void 0) {
        parentSuspense = null;
      }

      if (isSVG === void 0) {
        isSVG = false;
      }

      if (slotScopeIds === void 0) {
        slotScopeIds = null;
      }

      if (optimized === void 0) {
        optimized = !!n2.dynamicChildren;
      }

      if (n1 === n2) {
        return;
      } // patching & not same type, unmount old tree


      if (n1 && !isSameVNodeType(n1, n2)) {
        anchor = getNextHostNode(n1);
        unmount(n1, parentComponent, parentSuspense, true);
        n1 = null;
      }

      if (n2.patchFlag === -2
      /* PatchFlags.BAIL */
      ) {
        optimized = false;
        n2.dynamicChildren = null;
      }

      var type = n2.type,
          ref = n2.ref,
          shapeFlag = n2.shapeFlag;

      switch (type) {
        case Text:
          processText(n1, n2, container, anchor);
          break;

        case Comment:
          processCommentNode(n1, n2, container, anchor);
          break;

        case Static:
          if (n1 == null) {
            mountStaticNode(n2, container, anchor, isSVG);
          }

          break;

        case Fragment:
          processFragment(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
          break;

        default:
          if (shapeFlag & 1
          /* ShapeFlags.ELEMENT */
          ) {
            processElement(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
          } else if (shapeFlag & 6
          /* ShapeFlags.COMPONENT */
          ) {
            processComponent(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
          } else if (shapeFlag & 64
          /* ShapeFlags.TELEPORT */
          ) {
            type.process(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized, internals);
          } else if (shapeFlag & 128
          /* ShapeFlags.SUSPENSE */
          ) {
            type.process(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized, internals);
          } else ;

      } // set ref


      if (ref != null && parentComponent) {
        setRef(ref, n1 && n1.ref, parentSuspense, n2 || n1, !n2);
      }
    };

    var processText = function processText(n1, n2, container, anchor) {
      if (n1 == null) {
        hostInsert(n2.el = hostCreateText(n2.children), container, anchor);
      } else {
        var el = n2.el = n1.el;

        if (n2.children !== n1.children) {
          hostSetText(el, n2.children);
        }
      }
    };

    var processCommentNode = function processCommentNode(n1, n2, container, anchor) {
      if (n1 == null) {
        hostInsert(n2.el = hostCreateComment(n2.children || ''), container, anchor);
      } else {
        // there's no support for dynamic comments
        n2.el = n1.el;
      }
    };

    var mountStaticNode = function mountStaticNode(n2, container, anchor, isSVG) {
      var _hostInsertStaticCont = hostInsertStaticContent(n2.children, container, anchor, isSVG, n2.el, n2.anchor);

      n2.el = _hostInsertStaticCont[0];
      n2.anchor = _hostInsertStaticCont[1];
    };

    var moveStaticNode = function moveStaticNode(_ref12, container, nextSibling) {
      var el = _ref12.el,
          anchor = _ref12.anchor;
      var next;

      while (el && el !== anchor) {
        next = hostNextSibling(el);
        hostInsert(el, container, nextSibling);
        el = next;
      }

      hostInsert(anchor, container, nextSibling);
    };

    var removeStaticNode = function removeStaticNode(_ref13) {
      var el = _ref13.el,
          anchor = _ref13.anchor;
      var next;

      while (el && el !== anchor) {
        next = hostNextSibling(el);
        hostRemove(el);
        el = next;
      }

      hostRemove(anchor);
    };

    var processElement = function processElement(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
      isSVG = isSVG || n2.type === 'svg';

      if (n1 == null) {
        mountElement(n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
      } else {
        patchElement(n1, n2, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
      }
    };

    var mountElement = function mountElement(vnode, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
      var el;
      var vnodeHook;
      var type = vnode.type,
          props = vnode.props,
          shapeFlag = vnode.shapeFlag,
          transition = vnode.transition,
          dirs = vnode.dirs;
      el = vnode.el = hostCreateElement(vnode.type, isSVG, props && props.is, props); // mount children first, since some props may rely on child content
      // being already rendered, e.g. `<select value>`

      if (shapeFlag & 8
      /* ShapeFlags.TEXT_CHILDREN */
      ) {
        hostSetElementText(el, vnode.children);
      } else if (shapeFlag & 16
      /* ShapeFlags.ARRAY_CHILDREN */
      ) {
        mountChildren(vnode.children, el, null, parentComponent, parentSuspense, isSVG && type !== 'foreignObject', slotScopeIds, optimized);
      }

      if (dirs) {
        invokeDirectiveHook(vnode, null, parentComponent, 'created');
      } // props


      if (props) {
        for (var key in props) {
          if (key !== 'value' && !isReservedProp(key)) {
            hostPatchProp(el, key, null, props[key], isSVG, vnode.children, parentComponent, parentSuspense, unmountChildren);
          }
        }
        /**
         * Special case for setting value on DOM elements:
         * - it can be order-sensitive (e.g. should be set *after* min/max, #2325, #4024)
         * - it needs to be forced (#1471)
         * #2353 proposes adding another renderer option to configure this, but
         * the properties affects are so finite it is worth special casing it
         * here to reduce the complexity. (Special casing it also should not
         * affect non-DOM renderers)
         */


        if ('value' in props) {
          hostPatchProp(el, 'value', null, props.value);
        }

        if (vnodeHook = props.onVnodeBeforeMount) {
          invokeVNodeHook(vnodeHook, parentComponent, vnode);
        }
      } // scopeId


      setScopeId(el, vnode, vnode.scopeId, slotScopeIds, parentComponent);
      {
        Object.defineProperty(el, '__vnode', {
          value: vnode,
          enumerable: false
        });
        Object.defineProperty(el, '__vueParentComponent', {
          value: parentComponent,
          enumerable: false
        });
      }

      if (dirs) {
        invokeDirectiveHook(vnode, null, parentComponent, 'beforeMount');
      } // #1583 For inside suspense + suspense not resolved case, enter hook should call when suspense resolved
      // #1689 For inside suspense + suspense resolved case, just call it


      var needCallTransitionHooks = (!parentSuspense || parentSuspense && !parentSuspense.pendingBranch) && transition && !transition.persisted;

      if (needCallTransitionHooks) {
        transition.beforeEnter(el);
      }

      hostInsert(el, container, anchor);

      if ((vnodeHook = props && props.onVnodeMounted) || needCallTransitionHooks || dirs) {
        queuePostRenderEffect(function () {
          vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, vnode);
          needCallTransitionHooks && transition.enter(el);
          dirs && invokeDirectiveHook(vnode, null, parentComponent, 'mounted');
        }, parentSuspense);
      }
    };

    var setScopeId = function setScopeId(el, vnode, scopeId, slotScopeIds, parentComponent) {
      if (scopeId) {
        hostSetScopeId(el, scopeId);
      }

      if (slotScopeIds) {
        for (var i = 0; i < slotScopeIds.length; i++) {
          hostSetScopeId(el, slotScopeIds[i]);
        }
      }

      if (parentComponent) {
        var subTree = parentComponent.subTree;

        if (vnode === subTree) {
          var parentVNode = parentComponent.vnode;
          setScopeId(el, parentVNode, parentVNode.scopeId, parentVNode.slotScopeIds, parentComponent.parent);
        }
      }
    };

    var mountChildren = function mountChildren(children, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized, start) {
      if (start === void 0) {
        start = 0;
      }

      for (var i = start; i < children.length; i++) {
        var child = children[i] = optimized ? cloneIfMounted(children[i]) : normalizeVNode(children[i]);
        patch(null, child, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
      }
    };

    var patchElement = function patchElement(n1, n2, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
      var el = n2.el = n1.el;
      var patchFlag = n2.patchFlag,
          dynamicChildren = n2.dynamicChildren,
          dirs = n2.dirs; // #1426 take the old vnode's patch flag into account since user may clone a
      // compiler-generated vnode, which de-opts to FULL_PROPS

      patchFlag |= n1.patchFlag & 16
      /* PatchFlags.FULL_PROPS */
      ;
      var oldProps = n1.props || EMPTY_OBJ;
      var newProps = n2.props || EMPTY_OBJ;
      var vnodeHook; // disable recurse in beforeUpdate hooks

      parentComponent && toggleRecurse(parentComponent, false);

      if (vnodeHook = newProps.onVnodeBeforeUpdate) {
        invokeVNodeHook(vnodeHook, parentComponent, n2, n1);
      }

      if (dirs) {
        invokeDirectiveHook(n2, n1, parentComponent, 'beforeUpdate');
      }

      parentComponent && toggleRecurse(parentComponent, true);
      var areChildrenSVG = isSVG && n2.type !== 'foreignObject';

      if (dynamicChildren) {
        patchBlockChildren(n1.dynamicChildren, dynamicChildren, el, parentComponent, parentSuspense, areChildrenSVG, slotScopeIds);
      } else if (!optimized) {
        // full diff
        patchChildren(n1, n2, el, null, parentComponent, parentSuspense, areChildrenSVG, slotScopeIds, false);
      }

      if (patchFlag > 0) {
        // the presence of a patchFlag means this element's render code was
        // generated by the compiler and can take the fast path.
        // in this path old node and new node are guaranteed to have the same shape
        // (i.e. at the exact same position in the source template)
        if (patchFlag & 16
        /* PatchFlags.FULL_PROPS */
        ) {
          // element props contain dynamic keys, full diff needed
          patchProps(el, n2, oldProps, newProps, parentComponent, parentSuspense, isSVG);
        } else {
          // class
          // this flag is matched when the element has dynamic class bindings.
          if (patchFlag & 2
          /* PatchFlags.CLASS */
          ) {
            if (oldProps.class !== newProps.class) {
              hostPatchProp(el, 'class', null, newProps.class, isSVG);
            }
          } // style
          // this flag is matched when the element has dynamic style bindings


          if (patchFlag & 4
          /* PatchFlags.STYLE */
          ) {
            hostPatchProp(el, 'style', oldProps.style, newProps.style, isSVG);
          } // props
          // This flag is matched when the element has dynamic prop/attr bindings
          // other than class and style. The keys of dynamic prop/attrs are saved for
          // faster iteration.
          // Note dynamic keys like :[foo]="bar" will cause this optimization to
          // bail out and go through a full diff because we need to unset the old key


          if (patchFlag & 8
          /* PatchFlags.PROPS */
          ) {
            // if the flag is present then dynamicProps must be non-null
            var propsToUpdate = n2.dynamicProps;

            for (var i = 0; i < propsToUpdate.length; i++) {
              var key = propsToUpdate[i];
              var prev = oldProps[key];
              var next = newProps[key]; // #1471 force patch value

              if (next !== prev || key === 'value') {
                hostPatchProp(el, key, prev, next, isSVG, n1.children, parentComponent, parentSuspense, unmountChildren);
              }
            }
          }
        } // text
        // This flag is matched when the element has only dynamic text children.


        if (patchFlag & 1
        /* PatchFlags.TEXT */
        ) {
          if (n1.children !== n2.children) {
            hostSetElementText(el, n2.children);
          }
        }
      } else if (!optimized && dynamicChildren == null) {
        // unoptimized, full diff
        patchProps(el, n2, oldProps, newProps, parentComponent, parentSuspense, isSVG);
      }

      if ((vnodeHook = newProps.onVnodeUpdated) || dirs) {
        queuePostRenderEffect(function () {
          vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, n2, n1);
          dirs && invokeDirectiveHook(n2, n1, parentComponent, 'updated');
        }, parentSuspense);
      }
    }; // The fast path for blocks.


    var patchBlockChildren = function patchBlockChildren(oldChildren, newChildren, fallbackContainer, parentComponent, parentSuspense, isSVG, slotScopeIds) {
      for (var i = 0; i < newChildren.length; i++) {
        var oldVNode = oldChildren[i];
        var newVNode = newChildren[i]; // Determine the container (parent element) for the patch.

        var container = // oldVNode may be an errored async setup() component inside Suspense
        // which will not have a mounted element
        oldVNode.el && ( // - In the case of a Fragment, we need to provide the actual parent
        // of the Fragment itself so it can move its children.
        oldVNode.type === Fragment || // - In the case of different nodes, there is going to be a replacement
        // which also requires the correct parent container
        !isSameVNodeType(oldVNode, newVNode) || // - In the case of a component, it could contain anything.
        oldVNode.shapeFlag & (6
        /* ShapeFlags.COMPONENT */
        | 64
        /* ShapeFlags.TELEPORT */
        )) ? hostParentNode(oldVNode.el) : // In other cases, the parent container is not actually used so we
        // just pass the block element here to avoid a DOM parentNode call.
        fallbackContainer;
        patch(oldVNode, newVNode, container, null, parentComponent, parentSuspense, isSVG, slotScopeIds, true);
      }
    };

    var patchProps = function patchProps(el, vnode, oldProps, newProps, parentComponent, parentSuspense, isSVG) {
      if (oldProps !== newProps) {
        if (oldProps !== EMPTY_OBJ) {
          for (var key in oldProps) {
            if (!isReservedProp(key) && !(key in newProps)) {
              hostPatchProp(el, key, oldProps[key], null, isSVG, vnode.children, parentComponent, parentSuspense, unmountChildren);
            }
          }
        }

        for (var _key11 in newProps) {
          // empty string is not valid prop
          if (isReservedProp(_key11)) continue;
          var next = newProps[_key11];
          var prev = oldProps[_key11]; // defer patching value

          if (next !== prev && _key11 !== 'value') {
            hostPatchProp(el, _key11, prev, next, isSVG, vnode.children, parentComponent, parentSuspense, unmountChildren);
          }
        }

        if ('value' in newProps) {
          hostPatchProp(el, 'value', oldProps.value, newProps.value);
        }
      }
    };

    var processFragment = function processFragment(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
      var fragmentStartAnchor = n2.el = n1 ? n1.el : hostCreateText('');
      var fragmentEndAnchor = n2.anchor = n1 ? n1.anchor : hostCreateText('');
      var patchFlag = n2.patchFlag,
          dynamicChildren = n2.dynamicChildren,
          fragmentSlotScopeIds = n2.slotScopeIds;

      if (fragmentSlotScopeIds) {
        slotScopeIds = slotScopeIds ? slotScopeIds.concat(fragmentSlotScopeIds) : fragmentSlotScopeIds;
      }

      if (n1 == null) {
        hostInsert(fragmentStartAnchor, container, anchor);
        hostInsert(fragmentEndAnchor, container, anchor); // a fragment can only have array children
        // since they are either generated by the compiler, or implicitly created
        // from arrays.

        mountChildren(n2.children, container, fragmentEndAnchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
      } else {
        if (patchFlag > 0 && patchFlag & 64
        /* PatchFlags.STABLE_FRAGMENT */
        && dynamicChildren && // #2715 the previous fragment could've been a BAILed one as a result
        // of renderSlot() with no valid children
        n1.dynamicChildren) {
          // a stable fragment (template root or <template v-for>) doesn't need to
          // patch children order, but it may contain dynamicChildren.
          patchBlockChildren(n1.dynamicChildren, dynamicChildren, container, parentComponent, parentSuspense, isSVG, slotScopeIds);

          if ( // #2080 if the stable fragment has a key, it's a <template v-for> that may
          //  get moved around. Make sure all root level vnodes inherit el.
          // #2134 or if it's a component root, it may also get moved around
          // as the component is being moved.
          n2.key != null || parentComponent && n2 === parentComponent.subTree) {
            traverseStaticChildren(n1, n2, true
            /* shallow */
            );
          }
        } else {
          // keyed / unkeyed, or manual fragments.
          // for keyed & unkeyed, since they are compiler generated from v-for,
          // each child is guaranteed to be a block so the fragment will never
          // have dynamicChildren.
          patchChildren(n1, n2, container, fragmentEndAnchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
        }
      }
    };

    var processComponent = function processComponent(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
      n2.slotScopeIds = slotScopeIds;

      if (n1 == null) {
        if (n2.shapeFlag & 512
        /* ShapeFlags.COMPONENT_KEPT_ALIVE */
        ) {
          parentComponent.ctx.activate(n2, container, anchor, isSVG, optimized);
        } else {
          mountComponent(n2, container, anchor, parentComponent, parentSuspense, isSVG, optimized);
        }
      } else {
        updateComponent(n1, n2, optimized);
      }
    };

    var mountComponent = function mountComponent(initialVNode, container, anchor, parentComponent, parentSuspense, isSVG, optimized) {
      var instance = initialVNode.component = createComponentInstance(initialVNode, parentComponent, parentSuspense);

      if (isKeepAlive(initialVNode)) {
        instance.ctx.renderer = internals;
      } // resolve props and slots for setup context


      {
        setupComponent(instance);
      } // setup() is async. This component relies on async logic to be resolved
      // before proceeding

      if (instance.asyncDep) {
        parentSuspense && parentSuspense.registerDep(instance, setupRenderEffect); // Give it a placeholder if this is not hydration
        // TODO handle self-defined fallback

        if (!initialVNode.el) {
          var placeholder = instance.subTree = createVNode(Comment);
          processCommentNode(null, placeholder, container, anchor);
        }

        return;
      }

      setupRenderEffect(instance, initialVNode, container, anchor, parentSuspense, isSVG, optimized);
    };

    var updateComponent = function updateComponent(n1, n2, optimized) {
      var instance = n2.component = n1.component;

      if (shouldUpdateComponent(n1, n2, optimized)) {
        if (instance.asyncDep && !instance.asyncResolved) {
          updateComponentPreRender(instance, n2, optimized);
          return;
        } else {
          // normal update
          instance.next = n2; // in case the child component is also queued, remove it to avoid
          // double updating the same child component in the same flush.

          invalidateJob(instance.update); // instance.update is the reactive effect.

          instance.update();
        }
      } else {
        // no update needed. just copy over properties
        n2.el = n1.el;
        instance.vnode = n2;
      }
    };

    var setupRenderEffect = function setupRenderEffect(instance, initialVNode, container, anchor, parentSuspense, isSVG, optimized) {
      var componentUpdateFn = function componentUpdateFn() {
        if (!instance.isMounted) {
          var vnodeHook;
          var _initialVNode = initialVNode,
              el = _initialVNode.el,
              props = _initialVNode.props;
          var bm = instance.bm,
              m = instance.m,
              parent = instance.parent;
          var isAsyncWrapperVNode = isAsyncWrapper(initialVNode);
          toggleRecurse(instance, false); // beforeMount hook

          if (bm) {
            invokeArrayFns(bm);
          } // onVnodeBeforeMount


          if (!isAsyncWrapperVNode && (vnodeHook = props && props.onVnodeBeforeMount)) {
            invokeVNodeHook(vnodeHook, parent, initialVNode);
          }

          toggleRecurse(instance, true);

          if (el && hydrateNode) {
            // vnode has adopted host node - perform hydration instead of mount.
            var hydrateSubTree = function hydrateSubTree() {
              instance.subTree = renderComponentRoot(instance);
              hydrateNode(el, instance.subTree, instance, parentSuspense, null);
            };

            if (isAsyncWrapperVNode) {
              initialVNode.type.__asyncLoader().then( // note: we are moving the render call into an async callback,
              // which means it won't track dependencies - but it's ok because
              // a server-rendered async wrapper is already in resolved state
              // and it will never need to change.
              function () {
                return !instance.isUnmounted && hydrateSubTree();
              });
            } else {
              hydrateSubTree();
            }
          } else {
            var subTree = instance.subTree = renderComponentRoot(instance);
            patch(null, subTree, container, anchor, instance, parentSuspense, isSVG);
            initialVNode.el = subTree.el;
          } // mounted hook


          if (m) {
            queuePostRenderEffect(m, parentSuspense);
          } // onVnodeMounted


          if (!isAsyncWrapperVNode && (vnodeHook = props && props.onVnodeMounted)) {
            var scopedInitialVNode = initialVNode;
            queuePostRenderEffect(function () {
              return invokeVNodeHook(vnodeHook, parent, scopedInitialVNode);
            }, parentSuspense);
          } // activated hook for keep-alive roots.
          // #1742 activated hook must be accessed after first render
          // since the hook may be injected by a child keep-alive


          if (initialVNode.shapeFlag & 256
          /* ShapeFlags.COMPONENT_SHOULD_KEEP_ALIVE */
          || parent && isAsyncWrapper(parent.vnode) && parent.vnode.shapeFlag & 256
          /* ShapeFlags.COMPONENT_SHOULD_KEEP_ALIVE */
          ) {
            instance.a && queuePostRenderEffect(instance.a, parentSuspense);
          }

          instance.isMounted = true;
          {
            devtoolsComponentAdded(instance);
          } // #2458: deference mount-only object parameters to prevent memleaks

          initialVNode = container = anchor = null;
        } else {
          // updateComponent
          // This is triggered by mutation of component's own state (next: null)
          // OR parent calling processComponent (next: VNode)
          var next = instance.next,
              bu = instance.bu,
              u = instance.u,
              _parent = instance.parent,
              vnode = instance.vnode;
          var originNext = next;

          var _vnodeHook;

          toggleRecurse(instance, false);

          if (next) {
            next.el = vnode.el;
            updateComponentPreRender(instance, next, optimized);
          } else {
            next = vnode;
          } // beforeUpdate hook


          if (bu) {
            invokeArrayFns(bu);
          } // onVnodeBeforeUpdate


          if (_vnodeHook = next.props && next.props.onVnodeBeforeUpdate) {
            invokeVNodeHook(_vnodeHook, _parent, next, vnode);
          }

          toggleRecurse(instance, true); // render

          var nextTree = renderComponentRoot(instance);
          var prevTree = instance.subTree;
          instance.subTree = nextTree;
          patch(prevTree, nextTree, // parent may have changed if it's in a teleport
          hostParentNode(prevTree.el), // anchor may have changed if it's in a fragment
          getNextHostNode(prevTree), instance, parentSuspense, isSVG);
          next.el = nextTree.el;

          if (originNext === null) {
            // self-triggered update. In case of HOC, update parent component
            // vnode el. HOC is indicated by parent instance's subTree pointing
            // to child component's vnode
            updateHOCHostEl(instance, nextTree.el);
          } // updated hook


          if (u) {
            queuePostRenderEffect(u, parentSuspense);
          } // onVnodeUpdated


          if (_vnodeHook = next.props && next.props.onVnodeUpdated) {
            queuePostRenderEffect(function () {
              return invokeVNodeHook(_vnodeHook, _parent, next, vnode);
            }, parentSuspense);
          }

          {
            devtoolsComponentUpdated(instance);
          }
        }
      }; // create reactive effect for rendering


      var effect = instance.effect = new ReactiveEffect(componentUpdateFn, function () {
        return queueJob(update);
      }, instance.scope // track it in component's effect scope
      );

      var update = instance.update = function () {
        return effect.run();
      };

      update.id = instance.uid; // allowRecurse
      // #1801, #2043 component render effects should allow recursive updates

      toggleRecurse(instance, true);
      update();
    };

    var updateComponentPreRender = function updateComponentPreRender(instance, nextVNode, optimized) {
      nextVNode.component = instance;
      var prevProps = instance.vnode.props;
      instance.vnode = nextVNode;
      instance.next = null;
      updateProps(instance, nextVNode.props, prevProps, optimized);
      updateSlots(instance, nextVNode.children, optimized);
      pauseTracking(); // props update may have triggered pre-flush watchers.
      // flush them before the render update.

      flushPreFlushCbs();
      resetTracking();
    };

    var patchChildren = function patchChildren(n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
      if (optimized === void 0) {
        optimized = false;
      }

      var c1 = n1 && n1.children;
      var prevShapeFlag = n1 ? n1.shapeFlag : 0;
      var c2 = n2.children;
      var patchFlag = n2.patchFlag,
          shapeFlag = n2.shapeFlag; // fast path

      if (patchFlag > 0) {
        if (patchFlag & 128
        /* PatchFlags.KEYED_FRAGMENT */
        ) {
          // this could be either fully-keyed or mixed (some keyed some not)
          // presence of patchFlag means children are guaranteed to be arrays
          patchKeyedChildren(c1, c2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
          return;
        } else if (patchFlag & 256
        /* PatchFlags.UNKEYED_FRAGMENT */
        ) {
          // unkeyed
          patchUnkeyedChildren(c1, c2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
          return;
        }
      } // children has 3 possibilities: text, array or no children.


      if (shapeFlag & 8
      /* ShapeFlags.TEXT_CHILDREN */
      ) {
        // text children fast path
        if (prevShapeFlag & 16
        /* ShapeFlags.ARRAY_CHILDREN */
        ) {
          unmountChildren(c1, parentComponent, parentSuspense);
        }

        if (c2 !== c1) {
          hostSetElementText(container, c2);
        }
      } else {
        if (prevShapeFlag & 16
        /* ShapeFlags.ARRAY_CHILDREN */
        ) {
          // prev children was array
          if (shapeFlag & 16
          /* ShapeFlags.ARRAY_CHILDREN */
          ) {
            // two arrays, cannot assume anything, do full diff
            patchKeyedChildren(c1, c2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
          } else {
            // no new children, just unmount old
            unmountChildren(c1, parentComponent, parentSuspense, true);
          }
        } else {
          // prev children was text OR null
          // new children is array OR null
          if (prevShapeFlag & 8
          /* ShapeFlags.TEXT_CHILDREN */
          ) {
            hostSetElementText(container, '');
          } // mount new if array


          if (shapeFlag & 16
          /* ShapeFlags.ARRAY_CHILDREN */
          ) {
            mountChildren(c2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
          }
        }
      }
    };

    var patchUnkeyedChildren = function patchUnkeyedChildren(c1, c2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
      c1 = c1 || EMPTY_ARR;
      c2 = c2 || EMPTY_ARR;
      var oldLength = c1.length;
      var newLength = c2.length;
      var commonLength = Math.min(oldLength, newLength);
      var i;

      for (i = 0; i < commonLength; i++) {
        var nextChild = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);
        patch(c1[i], nextChild, container, null, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
      }

      if (oldLength > newLength) {
        // remove old
        unmountChildren(c1, parentComponent, parentSuspense, true, false, commonLength);
      } else {
        // mount new
        mountChildren(c2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized, commonLength);
      }
    }; // can be all-keyed or mixed


    var patchKeyedChildren = function patchKeyedChildren(c1, c2, container, parentAnchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
      var i = 0;
      var l2 = c2.length;
      var e1 = c1.length - 1; // prev ending index

      var e2 = l2 - 1; // next ending index
      // 1. sync from start
      // (a b) c
      // (a b) d e

      while (i <= e1 && i <= e2) {
        var n1 = c1[i];
        var n2 = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);

        if (isSameVNodeType(n1, n2)) {
          patch(n1, n2, container, null, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
        } else {
          break;
        }

        i++;
      } // 2. sync from end
      // a (b c)
      // d e (b c)


      while (i <= e1 && i <= e2) {
        var _n = c1[e1];

        var _n2 = c2[e2] = optimized ? cloneIfMounted(c2[e2]) : normalizeVNode(c2[e2]);

        if (isSameVNodeType(_n, _n2)) {
          patch(_n, _n2, container, null, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
        } else {
          break;
        }

        e1--;
        e2--;
      } // 3. common sequence + mount
      // (a b)
      // (a b) c
      // i = 2, e1 = 1, e2 = 2
      // (a b)
      // c (a b)
      // i = 0, e1 = -1, e2 = 0


      if (i > e1) {
        if (i <= e2) {
          var nextPos = e2 + 1;
          var anchor = nextPos < l2 ? c2[nextPos].el : parentAnchor;

          while (i <= e2) {
            patch(null, c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]), container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
            i++;
          }
        }
      } // 4. common sequence + unmount
      // (a b) c
      // (a b)
      // i = 2, e1 = 2, e2 = 1
      // a (b c)
      // (b c)
      // i = 0, e1 = 0, e2 = -1
      else if (i > e2) {
        while (i <= e1) {
          unmount(c1[i], parentComponent, parentSuspense, true);
          i++;
        }
      } // 5. unknown sequence
      // [i ... e1 + 1]: a b [c d e] f g
      // [i ... e2 + 1]: a b [e d c h] f g
      // i = 2, e1 = 4, e2 = 5
      else {
        var s1 = i; // prev starting index

        var s2 = i; // next starting index
        // 5.1 build key:index map for newChildren

        var keyToNewIndexMap = new Map();

        for (i = s2; i <= e2; i++) {
          var nextChild = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);

          if (nextChild.key != null) {
            keyToNewIndexMap.set(nextChild.key, i);
          }
        } // 5.2 loop through old children left to be patched and try to patch
        // matching nodes & remove nodes that are no longer present


        var j;
        var patched = 0;
        var toBePatched = e2 - s2 + 1;
        var moved = false; // used to track whether any node has moved

        var maxNewIndexSoFar = 0; // works as Map<newIndex, oldIndex>
        // Note that oldIndex is offset by +1
        // and oldIndex = 0 is a special value indicating the new node has
        // no corresponding old node.
        // used for determining longest stable subsequence

        var newIndexToOldIndexMap = new Array(toBePatched);

        for (i = 0; i < toBePatched; i++) {
          newIndexToOldIndexMap[i] = 0;
        }

        for (i = s1; i <= e1; i++) {
          var prevChild = c1[i];

          if (patched >= toBePatched) {
            // all new children have been patched so this can only be a removal
            unmount(prevChild, parentComponent, parentSuspense, true);
            continue;
          }

          var newIndex = void 0;

          if (prevChild.key != null) {
            newIndex = keyToNewIndexMap.get(prevChild.key);
          } else {
            // key-less node, try to locate a key-less node of the same type
            for (j = s2; j <= e2; j++) {
              if (newIndexToOldIndexMap[j - s2] === 0 && isSameVNodeType(prevChild, c2[j])) {
                newIndex = j;
                break;
              }
            }
          }

          if (newIndex === undefined) {
            unmount(prevChild, parentComponent, parentSuspense, true);
          } else {
            newIndexToOldIndexMap[newIndex - s2] = i + 1;

            if (newIndex >= maxNewIndexSoFar) {
              maxNewIndexSoFar = newIndex;
            } else {
              moved = true;
            }

            patch(prevChild, c2[newIndex], container, null, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
            patched++;
          }
        } // 5.3 move and mount
        // generate longest stable subsequence only when nodes have moved


        var increasingNewIndexSequence = moved ? getSequence(newIndexToOldIndexMap) : EMPTY_ARR;
        j = increasingNewIndexSequence.length - 1; // looping backwards so that we can use last patched node as anchor

        for (i = toBePatched - 1; i >= 0; i--) {
          var nextIndex = s2 + i;
          var _nextChild = c2[nextIndex];

          var _anchor = nextIndex + 1 < l2 ? c2[nextIndex + 1].el : parentAnchor;

          if (newIndexToOldIndexMap[i] === 0) {
            // mount new
            patch(null, _nextChild, container, _anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
          } else if (moved) {
            // move if:
            // There is no stable subsequence (e.g. a reverse)
            // OR current node is not among the stable sequence
            if (j < 0 || i !== increasingNewIndexSequence[j]) {
              move(_nextChild, container, _anchor, 2
              /* MoveType.REORDER */
              );
            } else {
              j--;
            }
          }
        }
      }
    };

    var move = function move(vnode, container, anchor, moveType, parentSuspense) {
      if (parentSuspense === void 0) {
        parentSuspense = null;
      }

      var el = vnode.el,
          type = vnode.type,
          transition = vnode.transition,
          children = vnode.children,
          shapeFlag = vnode.shapeFlag;

      if (shapeFlag & 6
      /* ShapeFlags.COMPONENT */
      ) {
        move(vnode.component.subTree, container, anchor, moveType);
        return;
      }

      if (shapeFlag & 128
      /* ShapeFlags.SUSPENSE */
      ) {
        vnode.suspense.move(container, anchor, moveType);
        return;
      }

      if (shapeFlag & 64
      /* ShapeFlags.TELEPORT */
      ) {
        type.move(vnode, container, anchor, internals);
        return;
      }

      if (type === Fragment) {
        hostInsert(el, container, anchor);

        for (var i = 0; i < children.length; i++) {
          move(children[i], container, anchor, moveType);
        }

        hostInsert(vnode.anchor, container, anchor);
        return;
      }

      if (type === Static) {
        moveStaticNode(vnode, container, anchor);
        return;
      } // single nodes


      var needTransition = moveType !== 2
      /* MoveType.REORDER */
      && shapeFlag & 1
      /* ShapeFlags.ELEMENT */
      && transition;

      if (needTransition) {
        if (moveType === 0
        /* MoveType.ENTER */
        ) {
          transition.beforeEnter(el);
          hostInsert(el, container, anchor);
          queuePostRenderEffect(function () {
            return transition.enter(el);
          }, parentSuspense);
        } else {
          var leave = transition.leave,
              delayLeave = transition.delayLeave,
              afterLeave = transition.afterLeave;

          var _remove = function _remove() {
            return hostInsert(el, container, anchor);
          };

          var performLeave = function performLeave() {
            leave(el, function () {
              _remove();

              afterLeave && afterLeave();
            });
          };

          if (delayLeave) {
            delayLeave(el, _remove, performLeave);
          } else {
            performLeave();
          }
        }
      } else {
        hostInsert(el, container, anchor);
      }
    };

    var unmount = function unmount(vnode, parentComponent, parentSuspense, doRemove, optimized) {
      if (doRemove === void 0) {
        doRemove = false;
      }

      if (optimized === void 0) {
        optimized = false;
      }

      var type = vnode.type,
          props = vnode.props,
          ref = vnode.ref,
          children = vnode.children,
          dynamicChildren = vnode.dynamicChildren,
          shapeFlag = vnode.shapeFlag,
          patchFlag = vnode.patchFlag,
          dirs = vnode.dirs; // unset ref

      if (ref != null) {
        setRef(ref, null, parentSuspense, vnode, true);
      }

      if (shapeFlag & 256
      /* ShapeFlags.COMPONENT_SHOULD_KEEP_ALIVE */
      ) {
        parentComponent.ctx.deactivate(vnode);
        return;
      }

      var shouldInvokeDirs = shapeFlag & 1
      /* ShapeFlags.ELEMENT */
      && dirs;
      var shouldInvokeVnodeHook = !isAsyncWrapper(vnode);
      var vnodeHook;

      if (shouldInvokeVnodeHook && (vnodeHook = props && props.onVnodeBeforeUnmount)) {
        invokeVNodeHook(vnodeHook, parentComponent, vnode);
      }

      if (shapeFlag & 6
      /* ShapeFlags.COMPONENT */
      ) {
        unmountComponent(vnode.component, parentSuspense, doRemove);
      } else {
        if (shapeFlag & 128
        /* ShapeFlags.SUSPENSE */
        ) {
          vnode.suspense.unmount(parentSuspense, doRemove);
          return;
        }

        if (shouldInvokeDirs) {
          invokeDirectiveHook(vnode, null, parentComponent, 'beforeUnmount');
        }

        if (shapeFlag & 64
        /* ShapeFlags.TELEPORT */
        ) {
          vnode.type.remove(vnode, parentComponent, parentSuspense, optimized, internals, doRemove);
        } else if (dynamicChildren && ( // #1153: fast path should not be taken for non-stable (v-for) fragments
        type !== Fragment || patchFlag > 0 && patchFlag & 64
        /* PatchFlags.STABLE_FRAGMENT */
        )) {
          // fast path for block nodes: only need to unmount dynamic children.
          unmountChildren(dynamicChildren, parentComponent, parentSuspense, false, true);
        } else if (type === Fragment && patchFlag & (128
        /* PatchFlags.KEYED_FRAGMENT */
        | 256
        /* PatchFlags.UNKEYED_FRAGMENT */
        ) || !optimized && shapeFlag & 16
        /* ShapeFlags.ARRAY_CHILDREN */
        ) {
          unmountChildren(children, parentComponent, parentSuspense);
        }

        if (doRemove) {
          remove(vnode);
        }
      }

      if (shouldInvokeVnodeHook && (vnodeHook = props && props.onVnodeUnmounted) || shouldInvokeDirs) {
        queuePostRenderEffect(function () {
          vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, vnode);
          shouldInvokeDirs && invokeDirectiveHook(vnode, null, parentComponent, 'unmounted');
        }, parentSuspense);
      }
    };

    var remove = function remove(vnode) {
      var type = vnode.type,
          el = vnode.el,
          anchor = vnode.anchor,
          transition = vnode.transition;

      if (type === Fragment) {
        {
          removeFragment(el, anchor);
        }
        return;
      }

      if (type === Static) {
        removeStaticNode(vnode);
        return;
      }

      var performRemove = function performRemove() {
        hostRemove(el);

        if (transition && !transition.persisted && transition.afterLeave) {
          transition.afterLeave();
        }
      };

      if (vnode.shapeFlag & 1
      /* ShapeFlags.ELEMENT */
      && transition && !transition.persisted) {
        var leave = transition.leave,
            delayLeave = transition.delayLeave;

        var performLeave = function performLeave() {
          return leave(el, performRemove);
        };

        if (delayLeave) {
          delayLeave(vnode.el, performRemove, performLeave);
        } else {
          performLeave();
        }
      } else {
        performRemove();
      }
    };

    var removeFragment = function removeFragment(cur, end) {
      // For fragments, directly remove all contained DOM nodes.
      // (fragment child nodes cannot have transition)
      var next;

      while (cur !== end) {
        next = hostNextSibling(cur);
        hostRemove(cur);
        cur = next;
      }

      hostRemove(end);
    };

    var unmountComponent = function unmountComponent(instance, parentSuspense, doRemove) {
      var bum = instance.bum,
          scope = instance.scope,
          update = instance.update,
          subTree = instance.subTree,
          um = instance.um; // beforeUnmount hook

      if (bum) {
        invokeArrayFns(bum);
      } // stop effects in component scope


      scope.stop(); // update may be null if a component is unmounted before its async
      // setup has resolved.

      if (update) {
        // so that scheduler will no longer invoke it
        update.active = false;
        unmount(subTree, instance, parentSuspense, doRemove);
      } // unmounted hook


      if (um) {
        queuePostRenderEffect(um, parentSuspense);
      }

      queuePostRenderEffect(function () {
        instance.isUnmounted = true;
      }, parentSuspense); // A component with async dep inside a pending suspense is unmounted before
      // its async dep resolves. This should remove the dep from the suspense, and
      // cause the suspense to resolve immediately if that was the last dep.

      if (parentSuspense && parentSuspense.pendingBranch && !parentSuspense.isUnmounted && instance.asyncDep && !instance.asyncResolved && instance.suspenseId === parentSuspense.pendingId) {
        parentSuspense.deps--;

        if (parentSuspense.deps === 0) {
          parentSuspense.resolve();
        }
      }

      {
        devtoolsComponentRemoved(instance);
      }
    };

    var unmountChildren = function unmountChildren(children, parentComponent, parentSuspense, doRemove, optimized, start) {
      if (doRemove === void 0) {
        doRemove = false;
      }

      if (optimized === void 0) {
        optimized = false;
      }

      if (start === void 0) {
        start = 0;
      }

      for (var i = start; i < children.length; i++) {
        unmount(children[i], parentComponent, parentSuspense, doRemove, optimized);
      }
    };

    var getNextHostNode = function getNextHostNode(vnode) {
      if (vnode.shapeFlag & 6
      /* ShapeFlags.COMPONENT */
      ) {
        return getNextHostNode(vnode.component.subTree);
      }

      if (vnode.shapeFlag & 128
      /* ShapeFlags.SUSPENSE */
      ) {
        return vnode.suspense.next();
      }

      return hostNextSibling(vnode.anchor || vnode.el);
    };

    var render = function render(vnode, container, isSVG) {
      if (vnode == null) {
        if (container._vnode) {
          unmount(container._vnode, null, null, true);
        }
      } else {
        patch(container._vnode || null, vnode, container, null, null, null, isSVG);
      }

      flushPreFlushCbs();
      flushPostFlushCbs();
      container._vnode = vnode;
    };

    var internals = {
      p: patch,
      um: unmount,
      m: move,
      r: remove,
      mt: mountComponent,
      mc: mountChildren,
      pc: patchChildren,
      pbc: patchBlockChildren,
      n: getNextHostNode,
      o: options
    };
    var hydrate;
    var hydrateNode;

    if (createHydrationFns) {
      var _createHydrationFns = createHydrationFns(internals);

      hydrate = _createHydrationFns[0];
      hydrateNode = _createHydrationFns[1];
    }

    return {
      render: render,
      hydrate: hydrate,
      createApp: createAppAPI(render, hydrate)
    };
  }

  function toggleRecurse(_ref14, allowed) {
    var effect = _ref14.effect,
        update = _ref14.update;
    effect.allowRecurse = update.allowRecurse = allowed;
  }
  /**
   * #1156
   * When a component is HMR-enabled, we need to make sure that all static nodes
   * inside a block also inherit the DOM element from the previous tree so that
   * HMR updates (which are full updates) can retrieve the element for patching.
   *
   * #2080
   * Inside keyed `template` fragment static children, if a fragment is moved,
   * the children will always be moved. Therefore, in order to ensure correct move
   * position, el should be inherited from previous nodes.
   */


  function traverseStaticChildren(n1, n2, shallow) {
    if (shallow === void 0) {
      shallow = false;
    }

    var ch1 = n1.children;
    var ch2 = n2.children;

    if (isArray(ch1) && isArray(ch2)) {
      for (var i = 0; i < ch1.length; i++) {
        // this is only called in the optimized path so array children are
        // guaranteed to be vnodes
        var c1 = ch1[i];
        var c2 = ch2[i];

        if (c2.shapeFlag & 1
        /* ShapeFlags.ELEMENT */
        && !c2.dynamicChildren) {
          if (c2.patchFlag <= 0 || c2.patchFlag === 32
          /* PatchFlags.HYDRATE_EVENTS */
          ) {
            c2 = ch2[i] = cloneIfMounted(ch2[i]);
            c2.el = c1.el;
          }

          if (!shallow) traverseStaticChildren(c1, c2);
        } // #6852 also inherit for text nodes


        if (c2.type === Text) {
          c2.el = c1.el;
        } // also inherit for comment nodes, but not placeholders (e.g. v-if which

      }
    }
  } // https://en.wikipedia.org/wiki/Longest_increasing_subsequence


  function getSequence(arr) {
    var p = arr.slice();
    var result = [0];
    var i, j, u, v, c;
    var len = arr.length;

    for (i = 0; i < len; i++) {
      var arrI = arr[i];

      if (arrI !== 0) {
        j = result[result.length - 1];

        if (arr[j] < arrI) {
          p[i] = j;
          result.push(i);
          continue;
        }

        u = 0;
        v = result.length - 1;

        while (u < v) {
          c = u + v >> 1;

          if (arr[result[c]] < arrI) {
            u = c + 1;
          } else {
            v = c;
          }
        }

        if (arrI < arr[result[u]]) {
          if (u > 0) {
            p[i] = result[u - 1];
          }

          result[u] = i;
        }
      }
    }

    u = result.length;
    v = result[u - 1];

    while (u-- > 0) {
      result[u] = v;
      v = p[v];
    }

    return result;
  }

  var isTeleport = function isTeleport(type) {
    return type.__isTeleport;
  };

  var Fragment = Symbol(undefined);
  var Text = Symbol(undefined);
  var Comment = Symbol(undefined);
  var Static = Symbol(undefined); // Since v-if and v-for are the two possible ways node structure can dynamically
  // change, once we consider v-if branches and each v-for fragment a block, we
  // can divide a template into nested blocks, and within each block the node
  // structure would be stable. This allows us to skip most children diffing
  // and only worry about the dynamic nodes (indicated by patch flags).

  var blockStack = [];
  var currentBlock = null;
  /**
   * Open a block.
   * This must be called before `createBlock`. It cannot be part of `createBlock`
   * because the children of the block are evaluated before `createBlock` itself
   * is called. The generated code typically looks like this:
   *
   * ```js
   * function render() {
   *   return (openBlock(),createBlock('div', null, [...]))
   * }
   * ```
   * disableTracking is true when creating a v-for fragment block, since a v-for
   * fragment always diffs its children.
   *
   * @private
   */

  function openBlock(disableTracking) {
    if (disableTracking === void 0) {
      disableTracking = false;
    }

    blockStack.push(currentBlock = disableTracking ? null : []);
  }

  function closeBlock() {
    blockStack.pop();
    currentBlock = blockStack[blockStack.length - 1] || null;
  } // Whether we should be tracking dynamic child nodes inside a block.
  // Only tracks when this value is > 0
  // We are not using a simple boolean because this value may need to be
  // incremented/decremented by nested usage of v-once (see below)


  var isBlockTreeEnabled = 1;
  /**
   * Block tracking sometimes needs to be disabled, for example during the
   * creation of a tree that needs to be cached by v-once. The compiler generates
   * code like this:
   *
   * ``` js
   * _cache[1] || (
   *   setBlockTracking(-1),
   *   _cache[1] = createVNode(...),
   *   setBlockTracking(1),
   *   _cache[1]
   * )
   * ```
   *
   * @private
   */

  function setBlockTracking(value) {
    isBlockTreeEnabled += value;
  }

  function setupBlock(vnode) {
    // save current block children on the block vnode
    vnode.dynamicChildren = isBlockTreeEnabled > 0 ? currentBlock || EMPTY_ARR : null; // close block

    closeBlock(); // a block is always going to be patched, so track it as a child of its
    // parent block

    if (isBlockTreeEnabled > 0 && currentBlock) {
      currentBlock.push(vnode);
    }

    return vnode;
  }
  /**
   * @private
   */


  function createElementBlock(type, props, children, patchFlag, dynamicProps, shapeFlag) {
    return setupBlock(createBaseVNode(type, props, children, patchFlag, dynamicProps, shapeFlag, true
    /* isBlock */
    ));
  }
  /**
   * Create a block root vnode. Takes the same exact arguments as `createVNode`.
   * A block root keeps track of dynamic nodes within the block in the
   * `dynamicChildren` array.
   *
   * @private
   */


  function createBlock(type, props, children, patchFlag, dynamicProps) {
    return setupBlock(createVNode(type, props, children, patchFlag, dynamicProps, true
    /* isBlock: prevent a block from tracking itself */
    ));
  }

  function isVNode(value) {
    return value ? value.__v_isVNode === true : false;
  }

  function isSameVNodeType(n1, n2) {
    return n1.type === n2.type && n1.key === n2.key;
  }

  var InternalObjectKey = "__vInternal";

  var normalizeKey = function normalizeKey(_ref18) {
    var key = _ref18.key;
    return key != null ? key : null;
  };

  var normalizeRef = function normalizeRef(_ref19) {
    var ref = _ref19.ref,
        ref_key = _ref19.ref_key,
        ref_for = _ref19.ref_for;
    return ref != null ? isString(ref) || isRef(ref) || isFunction(ref) ? {
      i: currentRenderingInstance,
      r: ref,
      k: ref_key,
      f: !!ref_for
    } : ref : null;
  };

  function createBaseVNode(type, props, children, patchFlag, dynamicProps, shapeFlag
  /* ShapeFlags.ELEMENT */
  , isBlockNode, needFullChildrenNormalization) {
    if (props === void 0) {
      props = null;
    }

    if (children === void 0) {
      children = null;
    }

    if (patchFlag === void 0) {
      patchFlag = 0;
    }

    if (dynamicProps === void 0) {
      dynamicProps = null;
    }

    if (shapeFlag === void 0) {
      shapeFlag = type === Fragment ? 0 : 1;
    }

    if (isBlockNode === void 0) {
      isBlockNode = false;
    }

    if (needFullChildrenNormalization === void 0) {
      needFullChildrenNormalization = false;
    }

    var vnode = {
      __v_isVNode: true,
      __v_skip: true,
      type: type,
      props: props,
      key: props && normalizeKey(props),
      ref: props && normalizeRef(props),
      scopeId: currentScopeId,
      slotScopeIds: null,
      children: children,
      component: null,
      suspense: null,
      ssContent: null,
      ssFallback: null,
      dirs: null,
      transition: null,
      el: null,
      anchor: null,
      target: null,
      targetAnchor: null,
      staticCount: 0,
      shapeFlag: shapeFlag,
      patchFlag: patchFlag,
      dynamicProps: dynamicProps,
      dynamicChildren: null,
      appContext: null,
      ctx: currentRenderingInstance
    };

    if (needFullChildrenNormalization) {
      normalizeChildren(vnode, children); // normalize suspense children

      if (shapeFlag & 128
      /* ShapeFlags.SUSPENSE */
      ) {
        type.normalize(vnode);
      }
    } else if (children) {
      // compiled element vnode - if children is passed, only possible types are
      // string or Array.
      vnode.shapeFlag |= isString(children) ? 8
      /* ShapeFlags.TEXT_CHILDREN */
      : 16
      /* ShapeFlags.ARRAY_CHILDREN */
      ;
    } // validate key


    if (isBlockTreeEnabled > 0 && // avoid a block node from tracking itself
    !isBlockNode && // has current parent block
    currentBlock && ( // presence of a patch flag indicates this node needs patching on updates.
    // component nodes also should always be patched, because even if the
    // component doesn't need to update, it needs to persist the instance on to
    // the next vnode so that it can be properly unmounted later.
    vnode.patchFlag > 0 || shapeFlag & 6
    /* ShapeFlags.COMPONENT */
    ) && // the EVENTS flag is only for hydration and if it is the only flag, the
    // vnode should not be considered dynamic due to handler caching.
    vnode.patchFlag !== 32
    /* PatchFlags.HYDRATE_EVENTS */
    ) {
      currentBlock.push(vnode);
    }

    return vnode;
  }

  var createVNode = _createVNode;

  function _createVNode(type, props, children, patchFlag, dynamicProps, isBlockNode) {
    if (props === void 0) {
      props = null;
    }

    if (children === void 0) {
      children = null;
    }

    if (patchFlag === void 0) {
      patchFlag = 0;
    }

    if (dynamicProps === void 0) {
      dynamicProps = null;
    }

    if (isBlockNode === void 0) {
      isBlockNode = false;
    }

    if (!type || type === NULL_DYNAMIC_COMPONENT) {
      type = Comment;
    }

    if (isVNode(type)) {
      // createVNode receiving an existing vnode. This happens in cases like
      // <component :is="vnode"/>
      // #2078 make sure to merge refs during the clone instead of overwriting it
      var cloned = cloneVNode(type, props, true
      /* mergeRef: true */
      );

      if (children) {
        normalizeChildren(cloned, children);
      }

      if (isBlockTreeEnabled > 0 && !isBlockNode && currentBlock) {
        if (cloned.shapeFlag & 6
        /* ShapeFlags.COMPONENT */
        ) {
          currentBlock[currentBlock.indexOf(type)] = cloned;
        } else {
          currentBlock.push(cloned);
        }
      }

      cloned.patchFlag |= -2
      /* PatchFlags.BAIL */
      ;
      return cloned;
    } // class component normalization.


    if (isClassComponent(type)) {
      type = type.__vccOpts;
    } // class & style normalization.


    if (props) {
      // for reactive or proxy objects, we need to clone it to enable mutation.
      props = guardReactiveProps(props);
      var _props = props,
          klass = _props.class,
          style = _props.style;

      if (klass && !isString(klass)) {
        props.class = normalizeClass(klass);
      }

      if (isObject$1(style)) {
        // reactive state objects need to be cloned since they are likely to be
        // mutated
        if (isProxy(style) && !isArray(style)) {
          style = extend({}, style);
        }

        props.style = normalizeStyle(style);
      }
    } // encode the vnode type information into a bitmap


    var shapeFlag = isString(type) ? 1
    /* ShapeFlags.ELEMENT */
    : isSuspense(type) ? 128
    /* ShapeFlags.SUSPENSE */
    : isTeleport(type) ? 64
    /* ShapeFlags.TELEPORT */
    : isObject$1(type) ? 4
    /* ShapeFlags.STATEFUL_COMPONENT */
    : isFunction(type) ? 2
    /* ShapeFlags.FUNCTIONAL_COMPONENT */
    : 0;
    return createBaseVNode(type, props, children, patchFlag, dynamicProps, shapeFlag, isBlockNode, true);
  }

  function guardReactiveProps(props) {
    if (!props) return null;
    return isProxy(props) || InternalObjectKey in props ? extend({}, props) : props;
  }

  function cloneVNode(vnode, extraProps, mergeRef) {
    if (mergeRef === void 0) {
      mergeRef = false;
    } // This is intentionally NOT using spread or extend to avoid the runtime
    // key enumeration cost.


    var props = vnode.props,
        ref = vnode.ref,
        patchFlag = vnode.patchFlag,
        children = vnode.children;
    var mergedProps = extraProps ? mergeProps(props || {}, extraProps) : props;
    var cloned = {
      __v_isVNode: true,
      __v_skip: true,
      type: vnode.type,
      props: mergedProps,
      key: mergedProps && normalizeKey(mergedProps),
      ref: extraProps && extraProps.ref ? // #2078 in the case of <component :is="vnode" ref="extra"/>
      // if the vnode itself already has a ref, cloneVNode will need to merge
      // the refs so the single vnode can be set on multiple refs
      mergeRef && ref ? isArray(ref) ? ref.concat(normalizeRef(extraProps)) : [ref, normalizeRef(extraProps)] : normalizeRef(extraProps) : ref,
      scopeId: vnode.scopeId,
      slotScopeIds: vnode.slotScopeIds,
      children: children,
      target: vnode.target,
      targetAnchor: vnode.targetAnchor,
      staticCount: vnode.staticCount,
      shapeFlag: vnode.shapeFlag,
      // if the vnode is cloned with extra props, we can no longer assume its
      // existing patch flag to be reliable and need to add the FULL_PROPS flag.
      // note: preserve flag for fragments since they use the flag for children
      // fast paths only.
      patchFlag: extraProps && vnode.type !== Fragment ? patchFlag === -1 // hoisted node
      ? 16
      /* PatchFlags.FULL_PROPS */
      : patchFlag | 16
      /* PatchFlags.FULL_PROPS */
      : patchFlag,
      dynamicProps: vnode.dynamicProps,
      dynamicChildren: vnode.dynamicChildren,
      appContext: vnode.appContext,
      dirs: vnode.dirs,
      transition: vnode.transition,
      // These should technically only be non-null on mounted VNodes. However,
      // they *should* be copied for kept-alive vnodes. So we just always copy
      // them since them being non-null during a mount doesn't affect the logic as
      // they will simply be overwritten.
      component: vnode.component,
      suspense: vnode.suspense,
      ssContent: vnode.ssContent && cloneVNode(vnode.ssContent),
      ssFallback: vnode.ssFallback && cloneVNode(vnode.ssFallback),
      el: vnode.el,
      anchor: vnode.anchor,
      ctx: vnode.ctx
    };
    return cloned;
  }
  /**
   * @private
   */


  function createTextVNode(text, flag) {
    if (text === void 0) {
      text = ' ';
    }

    if (flag === void 0) {
      flag = 0;
    }

    return createVNode(Text, null, text, flag);
  }
  /**
   * @private
   */


  function createCommentVNode(text, // when used as the v-else branch, the comment node must be created as a
  // block to ensure correct updates.
  asBlock) {
    if (text === void 0) {
      text = '';
    }

    if (asBlock === void 0) {
      asBlock = false;
    }

    return asBlock ? (openBlock(), createBlock(Comment, null, text)) : createVNode(Comment, null, text);
  }

  function normalizeVNode(child) {
    if (child == null || typeof child === 'boolean') {
      // empty placeholder
      return createVNode(Comment);
    } else if (isArray(child)) {
      // fragment
      return createVNode(Fragment, null, // #3666, avoid reference pollution when reusing vnode
      child.slice());
    } else if (typeof child === 'object') {
      // already vnode, this should be the most common since compiled templates
      // always produce all-vnode children arrays
      return cloneIfMounted(child);
    } else {
      // strings and numbers
      return createVNode(Text, null, String(child));
    }
  } // optimized normalization for template-compiled render fns


  function cloneIfMounted(child) {
    return child.el === null && child.patchFlag !== -1
    /* PatchFlags.HOISTED */
    || child.memo ? child : cloneVNode(child);
  }

  function normalizeChildren(vnode, children) {
    var type = 0;
    var shapeFlag = vnode.shapeFlag;

    if (children == null) {
      children = null;
    } else if (isArray(children)) {
      type = 16
      /* ShapeFlags.ARRAY_CHILDREN */
      ;
    } else if (typeof children === 'object') {
      if (shapeFlag & (1
      /* ShapeFlags.ELEMENT */
      | 64
      /* ShapeFlags.TELEPORT */
      )) {
        // Normalize slot to plain children for plain element and Teleport
        var slot = children.default;

        if (slot) {
          // _c marker is added by withCtx() indicating this is a compiled slot
          slot._c && (slot._d = false);
          normalizeChildren(vnode, slot());
          slot._c && (slot._d = true);
        }

        return;
      } else {
        type = 32
        /* ShapeFlags.SLOTS_CHILDREN */
        ;
        var slotFlag = children._;

        if (!slotFlag && !(InternalObjectKey in children)) {
          children._ctx = currentRenderingInstance;
        } else if (slotFlag === 3
        /* SlotFlags.FORWARDED */
        && currentRenderingInstance) {
          // a child component receives forwarded slots from the parent.
          // its slot type is determined by its parent's slot type.
          if (currentRenderingInstance.slots._ === 1
          /* SlotFlags.STABLE */
          ) {
            children._ = 1
            /* SlotFlags.STABLE */
            ;
          } else {
            children._ = 2
            /* SlotFlags.DYNAMIC */
            ;
            vnode.patchFlag |= 1024
            /* PatchFlags.DYNAMIC_SLOTS */
            ;
          }
        }
      }
    } else if (isFunction(children)) {
      children = {
        default: children,
        _ctx: currentRenderingInstance
      };
      type = 32
      /* ShapeFlags.SLOTS_CHILDREN */
      ;
    } else {
      children = String(children); // force teleport children to array so it can be moved around

      if (shapeFlag & 64
      /* ShapeFlags.TELEPORT */
      ) {
        type = 16
        /* ShapeFlags.ARRAY_CHILDREN */
        ;
        children = [createTextVNode(children)];
      } else {
        type = 8
        /* ShapeFlags.TEXT_CHILDREN */
        ;
      }
    }

    vnode.children = children;
    vnode.shapeFlag |= type;
  }

  function mergeProps() {
    var ret = {};

    for (var i = 0; i < arguments.length; i++) {
      var toMerge = i < 0 || arguments.length <= i ? undefined : arguments[i];

      for (var key in toMerge) {
        if (key === 'class') {
          if (ret.class !== toMerge.class) {
            ret.class = normalizeClass([ret.class, toMerge.class]);
          }
        } else if (key === 'style') {
          ret.style = normalizeStyle([ret.style, toMerge.style]);
        } else if (isOn(key)) {
          var existing = ret[key];
          var incoming = toMerge[key];

          if (incoming && existing !== incoming && !(isArray(existing) && existing.includes(incoming))) {
            ret[key] = existing ? [].concat(existing, incoming) : incoming;
          }
        } else if (key !== '') {
          ret[key] = toMerge[key];
        }
      }
    }

    return ret;
  }

  function invokeVNodeHook(hook, instance, vnode, prevVNode) {
    if (prevVNode === void 0) {
      prevVNode = null;
    }

    callWithAsyncErrorHandling(hook, instance, 7
    /* ErrorCodes.VNODE_HOOK */
    , [vnode, prevVNode]);
  }

  var emptyAppContext = createAppContext();
  var uid$1 = 0;

  function createComponentInstance(vnode, parent, suspense) {
    var type = vnode.type; // inherit parent app context - or - if root, adopt from root vnode

    var appContext = (parent ? parent.appContext : vnode.appContext) || emptyAppContext;
    var instance = {
      uid: uid$1++,
      vnode: vnode,
      type: type,
      parent: parent,
      appContext: appContext,
      root: null,
      next: null,
      subTree: null,
      effect: null,
      update: null,
      scope: new EffectScope(true
      /* detached */
      ),
      render: null,
      proxy: null,
      exposed: null,
      exposeProxy: null,
      withProxy: null,
      provides: parent ? parent.provides : Object.create(appContext.provides),
      accessCache: null,
      renderCache: [],
      // local resolved assets
      components: null,
      directives: null,
      // resolved props and emits options
      propsOptions: normalizePropsOptions(type, appContext),
      emitsOptions: normalizeEmitsOptions(type, appContext),
      // emit
      emit: null,
      emitted: null,
      // props default value
      propsDefaults: EMPTY_OBJ,
      // inheritAttrs
      inheritAttrs: type.inheritAttrs,
      // state
      ctx: EMPTY_OBJ,
      data: EMPTY_OBJ,
      props: EMPTY_OBJ,
      attrs: EMPTY_OBJ,
      slots: EMPTY_OBJ,
      refs: EMPTY_OBJ,
      setupState: EMPTY_OBJ,
      setupContext: null,
      // suspense related
      suspense: suspense,
      suspenseId: suspense ? suspense.pendingId : 0,
      asyncDep: null,
      asyncResolved: false,
      // lifecycle hooks
      // not using enums here because it results in computed properties
      isMounted: false,
      isUnmounted: false,
      isDeactivated: false,
      bc: null,
      c: null,
      bm: null,
      m: null,
      bu: null,
      u: null,
      um: null,
      bum: null,
      da: null,
      a: null,
      rtg: null,
      rtc: null,
      ec: null,
      sp: null
    };
    {
      instance.ctx = {
        _: instance
      };
    }
    instance.root = parent ? parent.root : instance;
    instance.emit = emit$1.bind(null, instance); // apply custom element special handling

    if (vnode.ce) {
      vnode.ce(instance);
    }

    return instance;
  }

  var currentInstance = null;

  var getCurrentInstance = function getCurrentInstance() {
    return currentInstance || currentRenderingInstance;
  };

  var setCurrentInstance = function setCurrentInstance(instance) {
    currentInstance = instance;
    instance.scope.on();
  };

  var unsetCurrentInstance = function unsetCurrentInstance() {
    currentInstance && currentInstance.scope.off();
    currentInstance = null;
  };

  function isStatefulComponent(instance) {
    return instance.vnode.shapeFlag & 4
    /* ShapeFlags.STATEFUL_COMPONENT */
    ;
  }

  var isInSSRComponentSetup = false;

  function setupComponent(instance, isSSR) {
    if (isSSR === void 0) {
      isSSR = false;
    }

    isInSSRComponentSetup = isSSR;
    var _instance$vnode = instance.vnode,
        props = _instance$vnode.props,
        children = _instance$vnode.children;
    var isStateful = isStatefulComponent(instance);
    initProps(instance, props, isStateful, isSSR);
    initSlots(instance, children);
    var setupResult = isStateful ? setupStatefulComponent(instance, isSSR) : undefined;
    isInSSRComponentSetup = false;
    return setupResult;
  }

  function setupStatefulComponent(instance, isSSR) {
    var Component = instance.type;
    instance.accessCache = Object.create(null); // 1. create public instance / render proxy
    // also mark it raw so it's never observed

    instance.proxy = markRaw(new Proxy(instance.ctx, PublicInstanceProxyHandlers));
    var setup = Component.setup;

    if (setup) {
      var setupContext = instance.setupContext = setup.length > 1 ? createSetupContext(instance) : null;
      setCurrentInstance(instance);
      pauseTracking();
      var setupResult = callWithErrorHandling(setup, instance, 0
      /* ErrorCodes.SETUP_FUNCTION */
      , [instance.props, setupContext]);
      resetTracking();
      unsetCurrentInstance();

      if (isPromise$1(setupResult)) {
        setupResult.then(unsetCurrentInstance, unsetCurrentInstance);

        if (isSSR) {
          // return the promise so server-renderer can wait on it
          return setupResult.then(function (resolvedResult) {
            handleSetupResult(instance, resolvedResult, isSSR);
          }).catch(function (e) {
            handleError(e, instance, 0
            /* ErrorCodes.SETUP_FUNCTION */
            );
          });
        } else {
          // async setup returned Promise.
          // bail here and wait for re-entry.
          instance.asyncDep = setupResult;
        }
      } else {
        handleSetupResult(instance, setupResult, isSSR);
      }
    } else {
      finishComponentSetup(instance, isSSR);
    }
  }

  function handleSetupResult(instance, setupResult, isSSR) {
    if (isFunction(setupResult)) {
      // setup returned an inline render function
      if (instance.type.__ssrInlineRender) {
        // when the function's name is `ssrRender` (compiled by SFC inline mode),
        // set it as ssrRender instead.
        instance.ssrRender = setupResult;
      } else {
        instance.render = setupResult;
      }
    } else if (isObject$1(setupResult)) {
      // assuming a render function compiled from template is present.
      {
        instance.devtoolsRawSetupState = setupResult;
      }
      instance.setupState = proxyRefs(setupResult);
    } else ;

    finishComponentSetup(instance, isSSR);
  }

  var compile;

  function finishComponentSetup(instance, isSSR, skipOptions) {
    var Component = instance.type; // template / render function normalization
    // could be already set when returned from setup()

    if (!instance.render) {
      // only do on-the-fly compile if not in SSR - SSR on-the-fly compilation
      // is done by server-renderer
      if (!isSSR && compile && !Component.render) {
        var template = Component.template || resolveMergedOptions(instance).template;

        if (template) {
          var _instance$appContext$ = instance.appContext.config,
              isCustomElement = _instance$appContext$.isCustomElement,
              compilerOptions = _instance$appContext$.compilerOptions;
          var delimiters = Component.delimiters,
              componentCompilerOptions = Component.compilerOptions;
          var finalCompilerOptions = extend(extend({
            isCustomElement: isCustomElement,
            delimiters: delimiters
          }, compilerOptions), componentCompilerOptions);
          Component.render = compile(template, finalCompilerOptions);
        }
      }

      instance.render = Component.render || NOOP; // for runtime-compiled render functions using `with` blocks, the render
    } // support for 2.x options


    {
      setCurrentInstance(instance);
      pauseTracking();
      applyOptions(instance);
      resetTracking();
      unsetCurrentInstance();
    } // warn missing template/render
  }

  function createAttrsProxy(instance) {
    return new Proxy(instance.attrs, {
      get: function get(target, key) {
        track(instance, "get"
        /* TrackOpTypes.GET */
        , '$attrs');
        return target[key];
      }
    });
  }

  function createSetupContext(instance) {
    var expose = function expose(exposed) {
      instance.exposed = exposed || {};
    };

    var attrs;
    {
      return {
        get attrs() {
          return attrs || (attrs = createAttrsProxy(instance));
        },

        slots: instance.slots,
        emit: instance.emit,
        expose: expose
      };
    }
  }

  function getExposeProxy(instance) {
    if (instance.exposed) {
      return instance.exposeProxy || (instance.exposeProxy = new Proxy(proxyRefs(markRaw(instance.exposed)), {
        get: function get(target, key) {
          if (key in target) {
            return target[key];
          } else if (key in publicPropertiesMap) {
            return publicPropertiesMap[key](instance);
          }
        },
        has: function has(target, key) {
          return key in target || key in publicPropertiesMap;
        }
      }));
    }
  }

  function getComponentName(Component, includeInferred) {
    if (includeInferred === void 0) {
      includeInferred = true;
    }

    return isFunction(Component) ? Component.displayName || Component.name : Component.name || includeInferred && Component.__name;
  }

  function isClassComponent(value) {
    return isFunction(value) && '__vccOpts' in value;
  }

  var computed = function computed(getterOrOptions, debugOptions) {
    // @ts-ignore
    return computed$1(getterOrOptions, debugOptions, isInSSRComponentSetup);
  }; // dev only


  function h(type, propsOrChildren, children) {
    var l = arguments.length;

    if (l === 2) {
      if (isObject$1(propsOrChildren) && !isArray(propsOrChildren)) {
        // single vnode without props
        if (isVNode(propsOrChildren)) {
          return createVNode(type, null, [propsOrChildren]);
        } // props without children


        return createVNode(type, propsOrChildren);
      } else {
        // omit props
        return createVNode(type, null, propsOrChildren);
      }
    } else {
      if (l > 3) {
        children = Array.prototype.slice.call(arguments, 2);
      } else if (l === 3 && isVNode(children)) {
        children = [children];
      }

      return createVNode(type, propsOrChildren, children);
    }
  }

  var ssrContextKey = Symbol("");

  var useSSRContext = function useSSRContext() {
    {
      var ctx = inject(ssrContextKey);
      return ctx;
    }
  };

  var version = "3.2.45";
  var svgNS = 'http://www.w3.org/2000/svg';
  var doc = typeof document !== 'undefined' ? document : null;
  var templateContainer = doc && /*#__PURE__*/doc.createElement('template');
  var nodeOps = {
    insert: function insert(child, parent, anchor) {
      parent.insertBefore(child, anchor || null);
    },
    remove: function remove(child) {
      var parent = child.parentNode;

      if (parent) {
        parent.removeChild(child);
      }
    },
    createElement: function createElement(tag, isSVG, is, props) {
      var el = isSVG ? doc.createElementNS(svgNS, tag) : doc.createElement(tag, is ? {
        is: is
      } : undefined);

      if (tag === 'select' && props && props.multiple != null) {
        el.setAttribute('multiple', props.multiple);
      }

      return el;
    },
    createText: function createText(text) {
      return doc.createTextNode(text);
    },
    createComment: function createComment(text) {
      return doc.createComment(text);
    },
    setText: function setText(node, text) {
      node.nodeValue = text;
    },
    setElementText: function setElementText(el, text) {
      el.textContent = text;
    },
    parentNode: function parentNode(node) {
      return node.parentNode;
    },
    nextSibling: function nextSibling(node) {
      return node.nextSibling;
    },
    querySelector: function querySelector(selector) {
      return doc.querySelector(selector);
    },
    setScopeId: function setScopeId(el, id) {
      el.setAttribute(id, '');
    },
    // __UNSAFE__
    // Reason: innerHTML.
    // Static content here can only come from compiled templates.
    // As long as the user only uses trusted templates, this is safe.
    insertStaticContent: function insertStaticContent(content, parent, anchor, isSVG, start, end) {
      // <parent> before | first ... last | anchor </parent>
      var before = anchor ? anchor.previousSibling : parent.lastChild; // #5308 can only take cached path if:
      // - has a single root node
      // - nextSibling info is still available

      if (start && (start === end || start.nextSibling)) {
        // cached
        while (true) {
          parent.insertBefore(start.cloneNode(true), anchor);
          if (start === end || !(start = start.nextSibling)) break;
        }
      } else {
        // fresh insert
        templateContainer.innerHTML = isSVG ? "<svg>" + content + "</svg>" : content;
        var template = templateContainer.content;

        if (isSVG) {
          // remove outer svg wrapper
          var wrapper = template.firstChild;

          while (wrapper.firstChild) {
            template.appendChild(wrapper.firstChild);
          }

          template.removeChild(wrapper);
        }

        parent.insertBefore(template, anchor);
      }

      return [// first
      before ? before.nextSibling : parent.firstChild, // last
      anchor ? anchor.previousSibling : parent.lastChild];
    }
  }; // compiler should normalize class + :class bindings on the same element
  // into a single binding ['staticClass', dynamic]

  function patchClass(el, value, isSVG) {
    // directly setting className should be faster than setAttribute in theory
    // if this is an element during a transition, take the temporary transition
    // classes into account.
    var transitionClasses = el._vtc;

    if (transitionClasses) {
      value = (value ? [value].concat(transitionClasses) : [].concat(transitionClasses)).join(' ');
    }

    if (value == null) {
      el.removeAttribute('class');
    } else if (isSVG) {
      el.setAttribute('class', value);
    } else {
      el.className = value;
    }
  }

  function patchStyle(el, prev, next) {
    var style = el.style;
    var isCssString = isString(next);

    if (next && !isCssString) {
      for (var key in next) {
        setStyle(style, key, next[key]);
      }

      if (prev && !isString(prev)) {
        for (var _key12 in prev) {
          if (next[_key12] == null) {
            setStyle(style, _key12, '');
          }
        }
      }
    } else {
      var currentDisplay = style.display;

      if (isCssString) {
        if (prev !== next) {
          style.cssText = next;
        }
      } else if (prev) {
        el.removeAttribute('style');
      } // indicates that the `display` of the element is controlled by `v-show`,
      // so we always keep the current `display` value regardless of the `style`
      // value, thus handing over control to `v-show`.


      if ('_vod' in el) {
        style.display = currentDisplay;
      }
    }
  }

  var importantRE = /\s*!important$/;

  function setStyle(style, name, val) {
    if (isArray(val)) {
      val.forEach(function (v) {
        return setStyle(style, name, v);
      });
    } else {
      if (val == null) val = '';

      if (name.startsWith('--')) {
        // custom property definition
        style.setProperty(name, val);
      } else {
        var prefixed = autoPrefix(style, name);

        if (importantRE.test(val)) {
          // !important
          style.setProperty(hyphenate(prefixed), val.replace(importantRE, ''), 'important');
        } else {
          style[prefixed] = val;
        }
      }
    }
  }

  var prefixes = ['Webkit', 'Moz', 'ms'];
  var prefixCache = {};

  function autoPrefix(style, rawName) {
    var cached = prefixCache[rawName];

    if (cached) {
      return cached;
    }

    var name = camelize(rawName);

    if (name !== 'filter' && name in style) {
      return prefixCache[rawName] = name;
    }

    name = capitalize(name);

    for (var i = 0; i < prefixes.length; i++) {
      var prefixed = prefixes[i] + name;

      if (prefixed in style) {
        return prefixCache[rawName] = prefixed;
      }
    }

    return rawName;
  }

  var xlinkNS = 'http://www.w3.org/1999/xlink';

  function patchAttr(el, key, value, isSVG, instance) {
    if (isSVG && key.startsWith('xlink:')) {
      if (value == null) {
        el.removeAttributeNS(xlinkNS, key.slice(6, key.length));
      } else {
        el.setAttributeNS(xlinkNS, key, value);
      }
    } else {
      // note we are only checking boolean attributes that don't have a
      // corresponding dom prop of the same name here.
      var isBoolean = isSpecialBooleanAttr(key);

      if (value == null || isBoolean && !includeBooleanAttr(value)) {
        el.removeAttribute(key);
      } else {
        el.setAttribute(key, isBoolean ? '' : value);
      }
    }
  } // __UNSAFE__
  // functions. The user is responsible for using them with only trusted content.


  function patchDOMProp(el, key, value, // the following args are passed only due to potential innerHTML/textContent
  // overriding existing VNodes, in which case the old tree must be properly
  // unmounted.
  prevChildren, parentComponent, parentSuspense, unmountChildren) {
    if (key === 'innerHTML' || key === 'textContent') {
      if (prevChildren) {
        unmountChildren(prevChildren, parentComponent, parentSuspense);
      }

      el[key] = value == null ? '' : value;
      return;
    }

    if (key === 'value' && el.tagName !== 'PROGRESS' && // custom elements may use _value internally
    !el.tagName.includes('-')) {
      // store value as _value as well since
      // non-string values will be stringified.
      el._value = value;
      var newValue = value == null ? '' : value;

      if (el.value !== newValue || // #4956: always set for OPTION elements because its value falls back to
      // textContent if no value attribute is present. And setting .value for
      // OPTION has no side effect
      el.tagName === 'OPTION') {
        el.value = newValue;
      }

      if (value == null) {
        el.removeAttribute(key);
      }

      return;
    }

    var needRemove = false;

    if (value === '' || value == null) {
      var type = typeof el[key];

      if (type === 'boolean') {
        // e.g. <select multiple> compiles to { multiple: '' }
        value = includeBooleanAttr(value);
      } else if (value == null && type === 'string') {
        // e.g. <div :id="null">
        value = '';
        needRemove = true;
      } else if (type === 'number') {
        // e.g. <img :width="null">
        value = 0;
        needRemove = true;
      }
    } // some properties perform value validation and throw,
    // some properties has getter, no setter, will error in 'use strict'
    // eg. <select :type="null"></select> <select :willValidate="null"></select>


    try {
      el[key] = value;
    } catch (e) {}

    needRemove && el.removeAttribute(key);
  }

  function addEventListener(el, event, handler, options) {
    el.addEventListener(event, handler, options);
  }

  function removeEventListener(el, event, handler, options) {
    el.removeEventListener(event, handler, options);
  }

  function patchEvent(el, rawName, prevValue, nextValue, instance) {
    if (instance === void 0) {
      instance = null;
    } // vei = vue event invokers


    var invokers = el._vei || (el._vei = {});
    var existingInvoker = invokers[rawName];

    if (nextValue && existingInvoker) {
      // patch
      existingInvoker.value = nextValue;
    } else {
      var _parseName = parseName(rawName),
          name = _parseName[0],
          _options2 = _parseName[1];

      if (nextValue) {
        // add
        var invoker = invokers[rawName] = createInvoker(nextValue, instance);
        addEventListener(el, name, invoker, _options2);
      } else if (existingInvoker) {
        // remove
        removeEventListener(el, name, existingInvoker, _options2);
        invokers[rawName] = undefined;
      }
    }
  }

  var optionsModifierRE = /(?:Once|Passive|Capture)$/;

  function parseName(name) {
    var options;

    if (optionsModifierRE.test(name)) {
      options = {};
      var m;

      while (m = name.match(optionsModifierRE)) {
        name = name.slice(0, name.length - m[0].length);
        options[m[0].toLowerCase()] = true;
      }
    }

    var event = name[2] === ':' ? name.slice(3) : hyphenate(name.slice(2));
    return [event, options];
  } // To avoid the overhead of repeatedly calling Date.now(), we cache
  // and use the same timestamp for all event listeners attached in the same tick.


  var cachedNow = 0;
  var p = /*#__PURE__*/Promise.resolve();

  var getNow = function getNow() {
    return cachedNow || (p.then(function () {
      return cachedNow = 0;
    }), cachedNow = Date.now());
  };

  function createInvoker(initialValue, instance) {
    var invoker = function invoker(e) {
      // async edge case vuejs/vue#6566
      // inner click event triggers patch, event handler
      // attached to outer element during patch, and triggered again. This
      // happens because browsers fire microtask ticks between event propagation.
      // this no longer happens for templates in Vue 3, but could still be
      // theoretically possible for hand-written render functions.
      // the solution: we save the timestamp when a handler is attached,
      // and also attach the timestamp to any event that was handled by vue
      // for the first time (to avoid inconsistent event timestamp implementations
      // or events fired from iframes, e.g. #2513)
      // The handler would only fire if the event passed to it was fired
      // AFTER it was attached.
      if (!e._vts) {
        e._vts = Date.now();
      } else if (e._vts <= invoker.attached) {
        return;
      }

      callWithAsyncErrorHandling(patchStopImmediatePropagation(e, invoker.value), instance, 5
      /* ErrorCodes.NATIVE_EVENT_HANDLER */
      , [e]);
    };

    invoker.value = initialValue;
    invoker.attached = getNow();
    return invoker;
  }

  function patchStopImmediatePropagation(e, value) {
    if (isArray(value)) {
      var originalStop = e.stopImmediatePropagation;

      e.stopImmediatePropagation = function () {
        originalStop.call(e);
        e._stopped = true;
      };

      return value.map(function (fn) {
        return function (e) {
          return !e._stopped && fn && fn(e);
        };
      });
    } else {
      return value;
    }
  }

  var nativeOnRE = /^on[a-z]/;

  var patchProp = function patchProp(el, key, prevValue, nextValue, isSVG, prevChildren, parentComponent, parentSuspense, unmountChildren) {
    if (isSVG === void 0) {
      isSVG = false;
    }

    if (key === 'class') {
      patchClass(el, nextValue, isSVG);
    } else if (key === 'style') {
      patchStyle(el, prevValue, nextValue);
    } else if (isOn(key)) {
      // ignore v-model listeners
      if (!isModelListener(key)) {
        patchEvent(el, key, prevValue, nextValue, parentComponent);
      }
    } else if (key[0] === '.' ? (key = key.slice(1), true) : key[0] === '^' ? (key = key.slice(1), false) : shouldSetAsProp(el, key, nextValue, isSVG)) {
      patchDOMProp(el, key, nextValue, prevChildren, parentComponent, parentSuspense, unmountChildren);
    } else {
      // special case for <input v-model type="checkbox"> with
      // :true-value & :false-value
      // store value as dom properties since non-string values will be
      // stringified.
      if (key === 'true-value') {
        el._trueValue = nextValue;
      } else if (key === 'false-value') {
        el._falseValue = nextValue;
      }

      patchAttr(el, key, nextValue, isSVG);
    }
  };

  function shouldSetAsProp(el, key, value, isSVG) {
    if (isSVG) {
      // most keys must be set as attribute on svg elements to work
      // ...except innerHTML & textContent
      if (key === 'innerHTML' || key === 'textContent') {
        return true;
      } // or native onclick with function values


      if (key in el && nativeOnRE.test(key) && isFunction(value)) {
        return true;
      }

      return false;
    } // these are enumerated attrs, however their corresponding DOM properties
    // are actually booleans - this leads to setting it with a string "false"
    // value leading it to be coerced to `true`, so we need to always treat
    // them as attributes.
    // Note that `contentEditable` doesn't have this problem: its DOM
    // property is also enumerated string values.


    if (key === 'spellcheck' || key === 'draggable' || key === 'translate') {
      return false;
    } // #1787, #2840 form property on form elements is readonly and must be set as
    // attribute.


    if (key === 'form') {
      return false;
    } // #1526 <input list> must be set as attribute


    if (key === 'list' && el.tagName === 'INPUT') {
      return false;
    } // #2766 <textarea type> must be set as attribute


    if (key === 'type' && el.tagName === 'TEXTAREA') {
      return false;
    } // native onclick with string value, must be set as attribute


    if (nativeOnRE.test(key) && isString(value)) {
      return false;
    }

    return key in el;
  }

  var TRANSITION = 'transition';
  var ANIMATION = 'animation'; // DOM Transition is a higher-order-component based on the platform-agnostic
  // base Transition component, with DOM-specific logic.

  var Transition = function Transition(props, _ref) {
    var slots = _ref.slots;
    return h(BaseTransition, resolveTransitionProps(props), slots);
  };

  Transition.displayName = 'Transition';
  var DOMTransitionPropsValidators = {
    name: String,
    type: String,
    css: {
      type: Boolean,
      default: true
    },
    duration: [String, Number, Object],
    enterFromClass: String,
    enterActiveClass: String,
    enterToClass: String,
    appearFromClass: String,
    appearActiveClass: String,
    appearToClass: String,
    leaveFromClass: String,
    leaveActiveClass: String,
    leaveToClass: String
  };
  Transition.props = /*#__PURE__*/extend({}, BaseTransition.props, DOMTransitionPropsValidators);
  /**
   * #3227 Incoming hooks may be merged into arrays when wrapping Transition
   * with custom HOCs.
   */

  var callHook = function callHook(hook, args) {
    if (args === void 0) {
      args = [];
    }

    if (isArray(hook)) {
      hook.forEach(function (h) {
        return h.apply(void 0, args);
      });
    } else if (hook) {
      hook.apply(void 0, args);
    }
  };
  /**
   * Check if a hook expects a callback (2nd arg), which means the user
   * intends to explicitly control the end of the transition.
   */


  var hasExplicitCallback = function hasExplicitCallback(hook) {
    return hook ? isArray(hook) ? hook.some(function (h) {
      return h.length > 1;
    }) : hook.length > 1 : false;
  };

  function resolveTransitionProps(rawProps) {
    var baseProps = {};

    for (var key in rawProps) {
      if (!(key in DOMTransitionPropsValidators)) {
        baseProps[key] = rawProps[key];
      }
    }

    if (rawProps.css === false) {
      return baseProps;
    }

    var _rawProps$name = rawProps.name,
        name = _rawProps$name === void 0 ? 'v' : _rawProps$name,
        type = rawProps.type,
        duration = rawProps.duration,
        _rawProps$enterFromCl = rawProps.enterFromClass,
        enterFromClass = _rawProps$enterFromCl === void 0 ? name + "-enter-from" : _rawProps$enterFromCl,
        _rawProps$enterActive = rawProps.enterActiveClass,
        enterActiveClass = _rawProps$enterActive === void 0 ? name + "-enter-active" : _rawProps$enterActive,
        _rawProps$enterToClas = rawProps.enterToClass,
        enterToClass = _rawProps$enterToClas === void 0 ? name + "-enter-to" : _rawProps$enterToClas,
        _rawProps$appearFromC = rawProps.appearFromClass,
        appearFromClass = _rawProps$appearFromC === void 0 ? enterFromClass : _rawProps$appearFromC,
        _rawProps$appearActiv = rawProps.appearActiveClass,
        appearActiveClass = _rawProps$appearActiv === void 0 ? enterActiveClass : _rawProps$appearActiv,
        _rawProps$appearToCla = rawProps.appearToClass,
        appearToClass = _rawProps$appearToCla === void 0 ? enterToClass : _rawProps$appearToCla,
        _rawProps$leaveFromCl = rawProps.leaveFromClass,
        leaveFromClass = _rawProps$leaveFromCl === void 0 ? name + "-leave-from" : _rawProps$leaveFromCl,
        _rawProps$leaveActive = rawProps.leaveActiveClass,
        leaveActiveClass = _rawProps$leaveActive === void 0 ? name + "-leave-active" : _rawProps$leaveActive,
        _rawProps$leaveToClas = rawProps.leaveToClass,
        leaveToClass = _rawProps$leaveToClas === void 0 ? name + "-leave-to" : _rawProps$leaveToClas;
    var durations = normalizeDuration(duration);
    var enterDuration = durations && durations[0];
    var leaveDuration = durations && durations[1];

    var _onBeforeEnter = baseProps.onBeforeEnter,
        onEnter = baseProps.onEnter,
        _onEnterCancelled = baseProps.onEnterCancelled,
        _onLeave = baseProps.onLeave,
        _onLeaveCancelled = baseProps.onLeaveCancelled,
        _baseProps$onBeforeAp = baseProps.onBeforeAppear,
        _onBeforeAppear = _baseProps$onBeforeAp === void 0 ? _onBeforeEnter : _baseProps$onBeforeAp,
        _baseProps$onAppear = baseProps.onAppear,
        onAppear = _baseProps$onAppear === void 0 ? onEnter : _baseProps$onAppear,
        _baseProps$onAppearCa = baseProps.onAppearCancelled,
        _onAppearCancelled = _baseProps$onAppearCa === void 0 ? _onEnterCancelled : _baseProps$onAppearCa;

    var finishEnter = function finishEnter(el, isAppear, done) {
      removeTransitionClass(el, isAppear ? appearToClass : enterToClass);
      removeTransitionClass(el, isAppear ? appearActiveClass : enterActiveClass);
      done && done();
    };

    var finishLeave = function finishLeave(el, done) {
      el._isLeaving = false;
      removeTransitionClass(el, leaveFromClass);
      removeTransitionClass(el, leaveToClass);
      removeTransitionClass(el, leaveActiveClass);
      done && done();
    };

    var makeEnterHook = function makeEnterHook(isAppear) {
      return function (el, done) {
        var hook = isAppear ? onAppear : onEnter;

        var resolve = function resolve() {
          return finishEnter(el, isAppear, done);
        };

        callHook(hook, [el, resolve]);
        nextFrame(function () {
          removeTransitionClass(el, isAppear ? appearFromClass : enterFromClass);
          addTransitionClass(el, isAppear ? appearToClass : enterToClass);

          if (!hasExplicitCallback(hook)) {
            whenTransitionEnds(el, type, enterDuration, resolve);
          }
        });
      };
    };

    return extend(baseProps, {
      onBeforeEnter: function onBeforeEnter(el) {
        callHook(_onBeforeEnter, [el]);
        addTransitionClass(el, enterFromClass);
        addTransitionClass(el, enterActiveClass);
      },
      onBeforeAppear: function onBeforeAppear(el) {
        callHook(_onBeforeAppear, [el]);
        addTransitionClass(el, appearFromClass);
        addTransitionClass(el, appearActiveClass);
      },
      onEnter: makeEnterHook(false),
      onAppear: makeEnterHook(true),
      onLeave: function onLeave(el, done) {
        el._isLeaving = true;

        var resolve = function resolve() {
          return finishLeave(el, done);
        };

        addTransitionClass(el, leaveFromClass); // force reflow so *-leave-from classes immediately take effect (#2593)

        forceReflow();
        addTransitionClass(el, leaveActiveClass);
        nextFrame(function () {
          if (!el._isLeaving) {
            // cancelled
            return;
          }

          removeTransitionClass(el, leaveFromClass);
          addTransitionClass(el, leaveToClass);

          if (!hasExplicitCallback(_onLeave)) {
            whenTransitionEnds(el, type, leaveDuration, resolve);
          }
        });
        callHook(_onLeave, [el, resolve]);
      },
      onEnterCancelled: function onEnterCancelled(el) {
        finishEnter(el, false);
        callHook(_onEnterCancelled, [el]);
      },
      onAppearCancelled: function onAppearCancelled(el) {
        finishEnter(el, true);
        callHook(_onAppearCancelled, [el]);
      },
      onLeaveCancelled: function onLeaveCancelled(el) {
        finishLeave(el);
        callHook(_onLeaveCancelled, [el]);
      }
    });
  }

  function normalizeDuration(duration) {
    if (duration == null) {
      return null;
    } else if (isObject$1(duration)) {
      return [NumberOf(duration.enter), NumberOf(duration.leave)];
    } else {
      var n = NumberOf(duration);
      return [n, n];
    }
  }

  function NumberOf(val) {
    var res = toNumber(val);
    return res;
  }

  function addTransitionClass(el, cls) {
    cls.split(/\s+/).forEach(function (c) {
      return c && el.classList.add(c);
    });
    (el._vtc || (el._vtc = new Set())).add(cls);
  }

  function removeTransitionClass(el, cls) {
    cls.split(/\s+/).forEach(function (c) {
      return c && el.classList.remove(c);
    });
    var _vtc = el._vtc;

    if (_vtc) {
      _vtc.delete(cls);

      if (!_vtc.size) {
        el._vtc = undefined;
      }
    }
  }

  function nextFrame(cb) {
    requestAnimationFrame(function () {
      requestAnimationFrame(cb);
    });
  }

  var endId = 0;

  function whenTransitionEnds(el, expectedType, explicitTimeout, resolve) {
    var id = el._endId = ++endId;

    var resolveIfNotStale = function resolveIfNotStale() {
      if (id === el._endId) {
        resolve();
      }
    };

    if (explicitTimeout) {
      return setTimeout(resolveIfNotStale, explicitTimeout);
    }

    var _getTransitionInfo = getTransitionInfo(el, expectedType),
        type = _getTransitionInfo.type,
        timeout = _getTransitionInfo.timeout,
        propCount = _getTransitionInfo.propCount;

    if (!type) {
      return resolve();
    }

    var endEvent = type + 'end';
    var ended = 0;

    var end = function end() {
      el.removeEventListener(endEvent, onEnd);
      resolveIfNotStale();
    };

    var onEnd = function onEnd(e) {
      if (e.target === el && ++ended >= propCount) {
        end();
      }
    };

    setTimeout(function () {
      if (ended < propCount) {
        end();
      }
    }, timeout + 1);
    el.addEventListener(endEvent, onEnd);
  }

  function getTransitionInfo(el, expectedType) {
    var styles = window.getComputedStyle(el); // JSDOM may return undefined for transition properties

    var getStyleProperties = function getStyleProperties(key) {
      return (styles[key] || '').split(', ');
    };

    var transitionDelays = getStyleProperties(TRANSITION + "Delay");
    var transitionDurations = getStyleProperties(TRANSITION + "Duration");
    var transitionTimeout = getTimeout(transitionDelays, transitionDurations);
    var animationDelays = getStyleProperties(ANIMATION + "Delay");
    var animationDurations = getStyleProperties(ANIMATION + "Duration");
    var animationTimeout = getTimeout(animationDelays, animationDurations);
    var type = null;
    var timeout = 0;
    var propCount = 0;
    /* istanbul ignore if */

    if (expectedType === TRANSITION) {
      if (transitionTimeout > 0) {
        type = TRANSITION;
        timeout = transitionTimeout;
        propCount = transitionDurations.length;
      }
    } else if (expectedType === ANIMATION) {
      if (animationTimeout > 0) {
        type = ANIMATION;
        timeout = animationTimeout;
        propCount = animationDurations.length;
      }
    } else {
      timeout = Math.max(transitionTimeout, animationTimeout);
      type = timeout > 0 ? transitionTimeout > animationTimeout ? TRANSITION : ANIMATION : null;
      propCount = type ? type === TRANSITION ? transitionDurations.length : animationDurations.length : 0;
    }

    var hasTransform = type === TRANSITION && /\b(transform|all)(,|$)/.test(getStyleProperties(TRANSITION + "Property").toString());
    return {
      type: type,
      timeout: timeout,
      propCount: propCount,
      hasTransform: hasTransform
    };
  }

  function getTimeout(delays, durations) {
    while (delays.length < durations.length) {
      delays = delays.concat(delays);
    }

    return Math.max.apply(Math, durations.map(function (d, i) {
      return toMs(d) + toMs(delays[i]);
    }));
  } // Old versions of Chromium (below 61.0.3163.100) formats floating pointer
  // numbers in a locale-dependent way, using a comma instead of a dot.
  // If comma is not replaced with a dot, the input will be rounded down
  // (i.e. acting as a floor function) causing unexpected behaviors


  function toMs(s) {
    return Number(s.slice(0, -1).replace(',', '.')) * 1000;
  } // synchronously force layout to put elements into a certain state


  function forceReflow() {
    return document.body.offsetHeight;
  }

  var getModelAssigner = function getModelAssigner(vnode) {
    var fn = vnode.props['onUpdate:modelValue'] || false;
    return isArray(fn) ? function (value) {
      return invokeArrayFns(fn, value);
    } : fn;
  };

  function onCompositionStart(e) {
    e.target.composing = true;
  }

  function onCompositionEnd(e) {
    var target = e.target;

    if (target.composing) {
      target.composing = false;
      target.dispatchEvent(new Event('input'));
    }
  } // We are exporting the v-model runtime directly as vnode hooks so that it can
  // be tree-shaken in case v-model is never used.


  var vModelText = {
    created: function created(el, _ref3, vnode) {
      var _ref3$modifiers = _ref3.modifiers,
          lazy = _ref3$modifiers.lazy,
          trim = _ref3$modifiers.trim,
          number = _ref3$modifiers.number;
      el._assign = getModelAssigner(vnode);
      var castToNumber = number || vnode.props && vnode.props.type === 'number';
      addEventListener(el, lazy ? 'change' : 'input', function (e) {
        if (e.target.composing) return;
        var domValue = el.value;

        if (trim) {
          domValue = domValue.trim();
        }

        if (castToNumber) {
          domValue = toNumber(domValue);
        }

        el._assign(domValue);
      });

      if (trim) {
        addEventListener(el, 'change', function () {
          el.value = el.value.trim();
        });
      }

      if (!lazy) {
        addEventListener(el, 'compositionstart', onCompositionStart);
        addEventListener(el, 'compositionend', onCompositionEnd); // Safari < 10.2 & UIWebView doesn't fire compositionend when
        // switching focus before confirming composition choice
        // this also fixes the issue where some browsers e.g. iOS Chrome
        // fires "change" instead of "input" on autocomplete.

        addEventListener(el, 'change', onCompositionEnd);
      }
    },
    // set value on mounted so it's after min/max for type="range"
    mounted: function mounted(el, _ref4) {
      var value = _ref4.value;
      el.value = value == null ? '' : value;
    },
    beforeUpdate: function beforeUpdate(el, _ref5, vnode) {
      var value = _ref5.value,
          _ref5$modifiers = _ref5.modifiers,
          lazy = _ref5$modifiers.lazy,
          trim = _ref5$modifiers.trim,
          number = _ref5$modifiers.number;
      el._assign = getModelAssigner(vnode); // avoid clearing unresolved text. #2302

      if (el.composing) return;

      if (document.activeElement === el && el.type !== 'range') {
        if (lazy) {
          return;
        }

        if (trim && el.value.trim() === value) {
          return;
        }

        if ((number || el.type === 'number') && toNumber(el.value) === value) {
          return;
        }
      }

      var newValue = value == null ? '' : value;

      if (el.value !== newValue) {
        el.value = newValue;
      }
    }
  };
  var systemModifiers = ['ctrl', 'shift', 'alt', 'meta'];
  var modifierGuards = {
    stop: function stop(e) {
      return e.stopPropagation();
    },
    prevent: function prevent(e) {
      return e.preventDefault();
    },
    self: function self(e) {
      return e.target !== e.currentTarget;
    },
    ctrl: function ctrl(e) {
      return !e.ctrlKey;
    },
    shift: function shift(e) {
      return !e.shiftKey;
    },
    alt: function alt(e) {
      return !e.altKey;
    },
    meta: function meta(e) {
      return !e.metaKey;
    },
    left: function left(e) {
      return 'button' in e && e.button !== 0;
    },
    middle: function middle(e) {
      return 'button' in e && e.button !== 1;
    },
    right: function right(e) {
      return 'button' in e && e.button !== 2;
    },
    exact: function exact(e, modifiers) {
      return systemModifiers.some(function (m) {
        return e[m + "Key"] && !modifiers.includes(m);
      });
    }
  };
  /**
   * @private
   */

  var withModifiers = function withModifiers(fn, modifiers) {
    return function (event) {
      for (var i = 0; i < modifiers.length; i++) {
        var guard = modifierGuards[modifiers[i]];
        if (guard && guard(event, modifiers)) return;
      }

      for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
        args[_key2 - 1] = arguments[_key2];
      }

      return fn.apply(void 0, [event].concat(args));
    };
  }; // Kept for 2.x compat.
  // Note: IE11 compat for `spacebar` and `del` is removed for now.


  var keyNames = {
    esc: 'escape',
    space: ' ',
    up: 'arrow-up',
    left: 'arrow-left',
    right: 'arrow-right',
    down: 'arrow-down',
    delete: 'backspace'
  };
  /**
   * @private
   */

  var withKeys = function withKeys(fn, modifiers) {
    return function (event) {
      if (!('key' in event)) {
        return;
      }

      var eventKey = hyphenate(event.key);

      if (modifiers.some(function (k) {
        return k === eventKey || keyNames[k] === eventKey;
      })) {
        return fn(event);
      }
    };
  };

  var vShow = {
    beforeMount: function beforeMount(el, _ref15, _ref16) {
      var value = _ref15.value;
      var transition = _ref16.transition;
      el._vod = el.style.display === 'none' ? '' : el.style.display;

      if (transition && value) {
        transition.beforeEnter(el);
      } else {
        setDisplay(el, value);
      }
    },
    mounted: function mounted(el, _ref17, _ref18) {
      var value = _ref17.value;
      var transition = _ref18.transition;

      if (transition && value) {
        transition.enter(el);
      }
    },
    updated: function updated(el, _ref19, _ref20) {
      var value = _ref19.value,
          oldValue = _ref19.oldValue;
      var transition = _ref20.transition;
      if (!value === !oldValue) return;

      if (transition) {
        if (value) {
          transition.beforeEnter(el);
          setDisplay(el, true);
          transition.enter(el);
        } else {
          transition.leave(el, function () {
            setDisplay(el, false);
          });
        }
      } else {
        setDisplay(el, value);
      }
    },
    beforeUnmount: function beforeUnmount(el, _ref21) {
      var value = _ref21.value;
      setDisplay(el, value);
    }
  };

  function setDisplay(el, value) {
    el.style.display = value ? el._vod : 'none';
  } // SSR vnode transforms, only used when user includes client-oriented render


  var rendererOptions = /*#__PURE__*/extend({
    patchProp: patchProp
  }, nodeOps); // lazy create the renderer - this makes core renderer logic tree-shakable
  // in case the user only imports reactivity utilities from Vue.

  var renderer;

  function ensureRenderer() {
    return renderer || (renderer = createRenderer(rendererOptions));
  }

  var createApp = function createApp() {
    var _ensureRenderer;

    var app = (_ensureRenderer = ensureRenderer()).createApp.apply(_ensureRenderer, arguments);

    var mount = app.mount;

    app.mount = function (containerOrSelector) {
      var container = normalizeContainer(containerOrSelector);
      if (!container) return;
      var component = app._component;

      if (!isFunction(component) && !component.render && !component.template) {
        // __UNSAFE__
        // Reason: potential execution of JS expressions in in-DOM template.
        // The user must make sure the in-DOM template is trusted. If it's
        // rendered by the server, the template should not contain any user data.
        component.template = container.innerHTML;
      } // clear content before mounting


      container.innerHTML = '';
      var proxy = mount(container, false, container instanceof SVGElement);

      if (container instanceof Element) {
        container.removeAttribute('v-cloak');
        container.setAttribute('data-v-app', '');
      }

      return proxy;
    };

    return app;
  };

  function normalizeContainer(container) {
    if (isString(container)) {
      var res = document.querySelector(container);
      return res;
    }

    return container;
  }
  /**
   * Media Event bus - used for communication between joomla and vue
   */


  var Event$1 = /*#__PURE__*/function () {
    /**
       * Media Event constructor
       */
    function Event$1() {
      this.events = {};
    }
    /**
       * Fire an event
       * @param event
       * @param data
       */


    var _proto3 = Event$1.prototype;

    _proto3.fire = function fire(event, data) {
      if (data === void 0) {
        data = null;
      }

      if (this.events[event]) {
        this.events[event].forEach(function (fn) {
          return fn(data);
        });
      }
    }
    /**
       * Listen to events
       * @param event
       * @param callback
       */
    ;

    _proto3.listen = function listen(event, callback) {
      this.events[event] = this.events[event] || [];
      this.events[event].push(callback);
    };

    return Event$1;
  }(); // Loading state


  var SET_IS_LOADING = 'SET_IS_LOADING'; // Selecting media items

  var SELECT_DIRECTORY = 'SELECT_DIRECTORY';
  var SELECT_BROWSER_ITEM = 'SELECT_BROWSER_ITEM';
  var SELECT_BROWSER_ITEMS = 'SELECT_BROWSER_ITEMS';
  var UNSELECT_BROWSER_ITEM = 'UNSELECT_BROWSER_ITEM';
  var UNSELECT_ALL_BROWSER_ITEMS = 'UNSELECT_ALL_BROWSER_ITEMS'; // In/Decrease grid item size

  var INCREASE_GRID_SIZE = 'INCREASE_GRID_SIZE';
  var DECREASE_GRID_SIZE = 'DECREASE_GRID_SIZE'; // Api handlers

  var LOAD_CONTENTS_SUCCESS = 'LOAD_CONTENTS_SUCCESS';
  var LOAD_FULL_CONTENTS_SUCCESS = 'LOAD_FULL_CONTENTS_SUCCESS';
  var CREATE_DIRECTORY_SUCCESS = 'CREATE_DIRECTORY_SUCCESS';
  var UPLOAD_SUCCESS = 'UPLOAD_SUCCESS'; // Create folder modal

  var SHOW_CREATE_FOLDER_MODAL = 'SHOW_CREATE_FOLDER_MODAL';
  var HIDE_CREATE_FOLDER_MODAL = 'HIDE_CREATE_FOLDER_MODAL'; // Confirm Delete Modal

  var SHOW_CONFIRM_DELETE_MODAL = 'SHOW_CONFIRM_DELETE_MODAL';
  var HIDE_CONFIRM_DELETE_MODAL = 'HIDE_CONFIRM_DELETE_MODAL'; // Infobar

  var SHOW_INFOBAR = 'SHOW_INFOBAR';
  var HIDE_INFOBAR = 'HIDE_INFOBAR'; // Delete items

  var DELETE_SUCCESS = 'DELETE_SUCCESS'; // List view

  var CHANGE_LIST_VIEW = 'CHANGE_LIST_VIEW'; // Preview modal

  var SHOW_PREVIEW_MODAL = 'SHOW_PREVIEW_MODAL';
  var HIDE_PREVIEW_MODAL = 'HIDE_PREVIEW_MODAL'; // Rename modal

  var SHOW_RENAME_MODAL = 'SHOW_RENAME_MODAL';
  var HIDE_RENAME_MODAL = 'HIDE_RENAME_MODAL';
  var RENAME_SUCCESS = 'RENAME_SUCCESS'; // Share model

  var SHOW_SHARE_MODAL = 'SHOW_SHARE_MODAL';
  var HIDE_SHARE_MODAL = 'HIDE_SHARE_MODAL'; // Search Query

  var SET_SEARCH_QUERY = 'SET_SEARCH_QUERY';

  var Notifications = /*#__PURE__*/function () {
    function Notifications() {}

    var _proto4 = Notifications.prototype;

    /* Send and success notification */
    // eslint-disable-next-line class-methods-use-this
    _proto4.success = function success(message, options) {
      // eslint-disable-next-line no-use-before-define
      notifications.notify(message, Object.assign({
        type: 'message',
        // @todo rename it to success
        dismiss: true
      }, options));
    }
    /* Send an error notification */
    // eslint-disable-next-line class-methods-use-this
    ;

    _proto4.error = function error(message, options) {
      // eslint-disable-next-line no-use-before-define
      notifications.notify(message, Object.assign({
        type: 'error',
        // @todo rename it to danger
        dismiss: true
      }, options));
    }
    /* Ask the user a question */
    // eslint-disable-next-line class-methods-use-this
    ;

    _proto4.ask = function ask(message) {
      return window.confirm(message);
    }
    /* Send a notification */
    // eslint-disable-next-line class-methods-use-this
    ;

    _proto4.notify = function notify(message, options) {
      var _Joomla$renderMessage;

      var timer;

      if (options.type === 'message') {
        timer = 3000;
      }

      Joomla.renderMessages((_Joomla$renderMessage = {}, _Joomla$renderMessage[options.type] = [Joomla.Text._(message)], _Joomla$renderMessage), undefined, true, timer);
    };

    return Notifications;
  }(); // eslint-disable-next-line import/no-mutable-exports,import/prefer-default-export


  var notifications = new Notifications();
  var script$t = {
    name: 'MediaApp',
    data: function data() {
      return {
        // The full height of the app in px
        fullHeight: ''
      };
    },
    computed: {
      disks: function disks() {
        return this.$store.state.disks;
      }
    },
    created: function created() {
      var _this2 = this;

      // Listen to the toolbar events
      MediaManager.Event.listen('onClickCreateFolder', function () {
        return _this2.$store.commit(SHOW_CREATE_FOLDER_MODAL);
      });
      MediaManager.Event.listen('onClickDelete', function () {
        if (_this2.$store.state.selectedItems.length > 0) {
          _this2.$store.commit(SHOW_CONFIRM_DELETE_MODAL);
        } else {
          notifications.error('COM_MEDIA_PLEASE_SELECT_ITEM');
        }
      });
    },
    mounted: function mounted() {
      var _this3 = this;

      // Set the full height and add event listener when dom is updated
      this.$nextTick(function () {
        _this3.setFullHeight(); // Add the global resize event listener


        window.addEventListener('resize', _this3.setFullHeight);
      }); // Initial load the data

      this.$store.dispatch('getContents', this.$store.state.selectedDirectory);
    },
    beforeUnmount: function beforeUnmount() {
      // Remove the global resize event listener
      window.removeEventListener('resize', this.setFullHeight);
    },
    methods: {
      /* Set the full height on the app container */
      setFullHeight: function setFullHeight() {
        this.fullHeight = window.innerHeight - this.$el.getBoundingClientRect().top + "px";
      }
    }
  };
  var _hoisted_1$t = {
    class: "media-container"
  };
  var _hoisted_2$r = {
    class: "media-sidebar"
  };
  var _hoisted_3$h = {
    class: "media-main"
  };

  function render$t(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_disk = resolveComponent("media-disk");

    var _component_media_toolbar = resolveComponent("media-toolbar");

    var _component_media_browser = resolveComponent("media-browser");

    var _component_media_upload = resolveComponent("media-upload");

    var _component_media_create_folder_modal = resolveComponent("media-create-folder-modal");

    var _component_media_preview_modal = resolveComponent("media-preview-modal");

    var _component_media_rename_modal = resolveComponent("media-rename-modal");

    var _component_media_share_modal = resolveComponent("media-share-modal");

    var _component_media_confirm_delete_modal = resolveComponent("media-confirm-delete-modal");

    return openBlock(), createElementBlock("div", _hoisted_1$t, [createBaseVNode("div", _hoisted_2$r, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.disks, function (disk, index) {
      return openBlock(), createBlock(_component_media_disk, {
        key: index,
        uid: index,
        disk: disk
      }, null, 8
      /* PROPS */
      , ["uid", "disk"]);
    }), 128
    /* KEYED_FRAGMENT */
    ))]), createBaseVNode("div", _hoisted_3$h, [createVNode(_component_media_toolbar), createVNode(_component_media_browser)]), createVNode(_component_media_upload), createVNode(_component_media_create_folder_modal), createVNode(_component_media_preview_modal), createVNode(_component_media_rename_modal), createVNode(_component_media_share_modal), createVNode(_component_media_confirm_delete_modal)]);
  }

  script$t.render = render$t;
  script$t.__file = "administrator/components/com_media/resources/scripts/components/app.vue";
  var script$s = {
    name: 'MediaDisk',
    // eslint-disable-next-line vue/require-prop-types
    props: ['disk', 'uid'],
    computed: {
      diskId: function diskId() {
        return "disk-" + (this.uid + 1);
      }
    }
  };
  var _hoisted_1$s = {
    class: "media-disk"
  };
  var _hoisted_2$q = ["id"];

  function render$s(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_drive = resolveComponent("media-drive");

    return openBlock(), createElementBlock("div", _hoisted_1$s, [createBaseVNode("h2", {
      id: $options.diskId,
      class: "media-disk-name"
    }, toDisplayString($props.disk.displayName), 9
    /* TEXT, PROPS */
    , _hoisted_2$q), (openBlock(true), createElementBlock(Fragment, null, renderList($props.disk.drives, function (drive, index) {
      return openBlock(), createBlock(_component_media_drive, {
        key: index,
        "disk-id": $options.diskId,
        counter: index,
        drive: drive,
        total: $props.disk.drives.length
      }, null, 8
      /* PROPS */
      , ["disk-id", "counter", "drive", "total"]);
    }), 128
    /* KEYED_FRAGMENT */
    ))]);
  }

  script$s.render = render$s;
  script$s.__file = "administrator/components/com_media/resources/scripts/components/tree/disk.vue";
  var navigable = {
    methods: {
      navigateTo: function navigateTo(path) {
        this.$store.dispatch('getContents', path);
      }
    }
  };
  var script$r = {
    name: 'MediaDrive',
    mixins: [navigable],
    // eslint-disable-next-line vue/require-prop-types
    props: ['drive', 'total', 'diskId', 'counter'],
    computed: {
      /* Whether or not the item is active */
      isActive: function isActive() {
        return this.$store.state.selectedDirectory === this.drive.root;
      },
      getTabindex: function getTabindex() {
        return this.isActive ? 0 : -1;
      }
    },
    methods: {
      /* Handle the on drive click event */
      onDriveClick: function onDriveClick() {
        this.navigateTo(this.drive.root);
      },
      moveFocusToChildElement: function moveFocusToChildElement(nextRoot) {
        this.$refs[nextRoot].setFocusToFirstChild();
      },
      restoreFocus: function restoreFocus() {
        this.$refs['drive-root'].focus();
      }
    }
  };
  var _hoisted_1$r = ["aria-labelledby"];
  var _hoisted_2$p = ["aria-setsize", "tabindex"];
  var _hoisted_3$g = {
    class: "item-name"
  };

  function render$r(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_tree = resolveComponent("media-tree");

    return openBlock(), createElementBlock("div", {
      class: "media-drive",
      onClick: _cache[2] || (_cache[2] = withModifiers(function ($event) {
        return $options.onDriveClick();
      }, ["stop", "prevent"]))
    }, [createBaseVNode("ul", {
      class: "media-tree",
      role: "tree",
      "aria-labelledby": $props.diskId
    }, [createBaseVNode("li", {
      class: normalizeClass({
        active: $options.isActive,
        'media-tree-item': true,
        'media-drive-name': true
      }),
      role: "none"
    }, [createBaseVNode("a", {
      ref: "drive-root",
      role: "treeitem",
      "aria-level": "1",
      "aria-setsize": $props.counter,
      "aria-posinset": 1,
      tabindex: $options.getTabindex,
      onKeyup: [_cache[0] || (_cache[0] = withKeys(function ($event) {
        return $options.moveFocusToChildElement($props.drive.root);
      }, ["right"])), _cache[1] || (_cache[1] = withKeys(function () {
        return $options.onDriveClick && $options.onDriveClick.apply($options, arguments);
      }, ["enter"]))]
    }, [createBaseVNode("span", _hoisted_3$g, toDisplayString($props.drive.displayName), 1
    /* TEXT */
    )], 40
    /* PROPS, HYDRATE_EVENTS */
    , _hoisted_2$p), createVNode(_component_media_tree, {
      ref: $props.drive.root,
      root: $props.drive.root,
      level: 2,
      "parent-index": 0,
      onMoveFocusToParent: $options.restoreFocus
    }, null, 8
    /* PROPS */
    , ["root", "onMoveFocusToParent"])], 2
    /* CLASS */
    )], 8
    /* PROPS */
    , _hoisted_1$r)]);
  }

  script$r.render = render$r;
  script$r.__file = "administrator/components/com_media/resources/scripts/components/tree/drive.vue";
  var script$q = {
    name: 'MediaTree',
    mixins: [navigable],
    props: {
      root: {
        type: String,
        required: true
      },
      level: {
        type: Number,
        required: true
      },
      parentIndex: {
        type: Number,
        required: true
      }
    },
    emits: ['move-focus-to-parent'],
    computed: {
      /* Get the directories */
      directories: function directories() {
        var _this4 = this;

        return this.$store.state.directories.filter(function (directory) {
          return directory.directory === _this4.root;
        }) // Sort alphabetically
        .sort(function (a, b) {
          return a.name.toUpperCase() < b.name.toUpperCase() ? -1 : 1;
        });
      }
    },
    methods: {
      isActive: function isActive(item) {
        return item.path === this.$store.state.selectedDirectory;
      },
      getTabindex: function getTabindex(item) {
        return this.isActive(item) ? 0 : -1;
      },
      onItemClick: function onItemClick(item) {
        this.navigateTo(item.path);
        window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
          bubbles: true,
          cancelable: false,
          detail: {}
        }));
      },
      hasChildren: function hasChildren(item) {
        return item.directories.length > 0;
      },
      isOpen: function isOpen(item) {
        return this.$store.state.selectedDirectory.includes(item.path);
      },
      iconClass: function iconClass(item) {
        return {
          fas: false,
          'icon-folder': !this.isOpen(item),
          'icon-folder-open': this.isOpen(item)
        };
      },
      setFocusToFirstChild: function setFocusToFirstChild() {
        this.$refs[this.root + "0"][0].focus();
      },
      moveFocusToNextElement: function moveFocusToNextElement(currentIndex) {
        if (currentIndex + 1 === this.directories.length) {
          return;
        }

        this.$refs[this.root + (currentIndex + 1)][0].focus();
      },
      moveFocusToPreviousElement: function moveFocusToPreviousElement(currentIndex) {
        if (currentIndex === 0) {
          return;
        }

        this.$refs[this.root + (currentIndex - 1)][0].focus();
      },
      moveFocusToChildElement: function moveFocusToChildElement(item) {
        if (!this.hasChildren(item)) {
          return;
        }

        this.$refs[item.path][0].setFocusToFirstChild();
      },
      moveFocusToParentElement: function moveFocusToParentElement() {
        this.$emit('move-focus-to-parent', this.parentIndex);
      },
      restoreFocus: function restoreFocus(parentIndex) {
        this.$refs[this.root + parentIndex][0].focus();
      }
    }
  };
  var _hoisted_1$q = {
    class: "media-tree",
    role: "group"
  };
  var _hoisted_2$o = ["aria-level", "aria-setsize", "aria-posinset", "tabindex", "onClick", "onKeyup"];
  var _hoisted_3$f = {
    class: "item-icon"
  };
  var _hoisted_4$a = {
    class: "item-name"
  };

  function render$q(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_tree = resolveComponent("media-tree");

    return openBlock(), createElementBlock("ul", _hoisted_1$q, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.directories, function (item, index) {
      return openBlock(), createElementBlock("li", {
        key: item.path,
        class: normalizeClass(["media-tree-item", {
          active: $options.isActive(item)
        }]),
        role: "none"
      }, [createBaseVNode("a", {
        ref_for: true,
        ref: $props.root + index,
        role: "treeitem",
        "aria-level": $props.level,
        "aria-setsize": $options.directories.length,
        "aria-posinset": index,
        tabindex: $options.getTabindex(item),
        onClick: withModifiers(function ($event) {
          return $options.onItemClick(item);
        }, ["stop", "prevent"]),
        onKeyup: [withKeys(function ($event) {
          return $options.moveFocusToPreviousElement(index);
        }, ["up"]), withKeys(function ($event) {
          return $options.moveFocusToNextElement(index);
        }, ["down"]), withKeys(function ($event) {
          return $options.onItemClick(item);
        }, ["enter"]), withKeys(function ($event) {
          return $options.moveFocusToChildElement(item);
        }, ["right"]), _cache[0] || (_cache[0] = withKeys(function ($event) {
          return $options.moveFocusToParentElement();
        }, ["left"]))]
      }, [createBaseVNode("span", _hoisted_3$f, [createBaseVNode("span", {
        class: normalizeClass($options.iconClass(item))
      }, null, 2
      /* CLASS */
      )]), createBaseVNode("span", _hoisted_4$a, toDisplayString(item.name), 1
      /* TEXT */
      )], 40
      /* PROPS, HYDRATE_EVENTS */
      , _hoisted_2$o), createVNode(Transition, {
        name: "slide-fade"
      }, {
        default: withCtx(function () {
          return [$options.hasChildren(item) ? withDirectives((openBlock(), createBlock(_component_media_tree, {
            key: 0,
            ref_for: true,
            ref: item.path,
            "aria-expanded": $options.isOpen(item) ? 'true' : 'false',
            root: item.path,
            level: $props.level + 1,
            "parent-index": index,
            onMoveFocusToParent: $options.restoreFocus
          }, null, 8
          /* PROPS */
          , ["aria-expanded", "root", "level", "parent-index", "onMoveFocusToParent"])), [[vShow, $options.isOpen(item)]]) : createCommentVNode("v-if", true)];
        }),
        _: 2
        /* DYNAMIC */

      }, 1024
      /* DYNAMIC_SLOTS */
      )], 2
      /* CLASS */
      );
    }), 128
    /* KEYED_FRAGMENT */
    ))]);
  }

  script$q.render = render$q;
  script$q.__file = "administrator/components/com_media/resources/scripts/components/tree/tree.vue";
  var script$p = {
    name: 'MediaToolbar',
    computed: {
      toggleListViewBtnIcon: function toggleListViewBtnIcon() {
        return this.isGridView ? 'icon-list' : 'icon-th';
      },
      isLoading: function isLoading() {
        return this.$store.state.isLoading;
      },
      atLeastOneItemSelected: function atLeastOneItemSelected() {
        return this.$store.state.selectedItems.length > 0;
      },
      isGridView: function isGridView() {
        return this.$store.state.listView === 'grid';
      },
      allItemsSelected: function allItemsSelected() {
        // eslint-disable-next-line max-len
        return this.$store.getters.getSelectedDirectoryContents.length === this.$store.state.selectedItems.length;
      },
      search: function search() {
        return this.$store.state.search;
      }
    },
    watch: {
      // eslint-disable-next-line
      '$store.state.selectedItems': function $storeStateSelectedItems() {
        if (!this.allItemsSelected) {
          this.$refs.mediaToolbarSelectAll.checked = false;
        }
      }
    },
    methods: {
      toggleInfoBar: function toggleInfoBar() {
        if (this.$store.state.showInfoBar) {
          this.$store.commit(HIDE_INFOBAR);
        } else {
          this.$store.commit(SHOW_INFOBAR);
        }
      },
      decreaseGridSize: function decreaseGridSize() {
        if (!this.isGridSize('sm')) {
          this.$store.commit(DECREASE_GRID_SIZE);
        }
      },
      increaseGridSize: function increaseGridSize() {
        if (!this.isGridSize('xl')) {
          this.$store.commit(INCREASE_GRID_SIZE);
        }
      },
      changeListView: function changeListView() {
        if (this.$store.state.listView === 'grid') {
          this.$store.commit(CHANGE_LIST_VIEW, 'table');
        } else {
          this.$store.commit(CHANGE_LIST_VIEW, 'grid');
        }
      },
      toggleSelectAll: function toggleSelectAll() {
        if (this.allItemsSelected) {
          this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
        } else {
          // eslint-disable-next-line max-len
          this.$store.commit(SELECT_BROWSER_ITEMS, this.$store.getters.getSelectedDirectoryContents);
          window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
            bubbles: true,
            cancelable: false,
            detail: {}
          }));
        }
      },
      isGridSize: function isGridSize(size) {
        return this.$store.state.gridSize === size;
      },
      changeSearch: function changeSearch(query) {
        this.$store.commit(SET_SEARCH_QUERY, query.target.value);
      }
    }
  };
  var _hoisted_1$p = ["aria-label"];
  var _hoisted_2$n = {
    key: 0,
    class: "media-loader"
  };
  var _hoisted_3$e = {
    class: "media-view-icons"
  };
  var _hoisted_4$9 = ["aria-label"];
  var _hoisted_5$9 = {
    class: "media-view-search-input",
    role: "search"
  };
  var _hoisted_6$7 = {
    for: "media_search",
    class: "visually-hidden"
  };
  var _hoisted_7$4 = ["placeholder", "value"];
  var _hoisted_8$4 = {
    class: "media-view-icons"
  };
  var _hoisted_9$4 = ["aria-label"];

  var _hoisted_10$2 = /*#__PURE__*/createBaseVNode("span", {
    class: "icon-search-minus",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_11$2 = [_hoisted_10$2];
  var _hoisted_12$1 = ["aria-label"];

  var _hoisted_13 = /*#__PURE__*/createBaseVNode("span", {
    class: "icon-search-plus",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_14 = [_hoisted_13];
  var _hoisted_15 = ["aria-label"];
  var _hoisted_16 = ["aria-label"];

  var _hoisted_17 = /*#__PURE__*/createBaseVNode("span", {
    class: "icon-info",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_18 = [_hoisted_17];

  function render$p(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_breadcrumb = resolveComponent("media-breadcrumb");

    return openBlock(), createElementBlock("div", {
      class: "media-toolbar",
      role: "toolbar",
      "aria-label": _ctx.translate('COM_MEDIA_TOOLBAR_LABEL')
    }, [$options.isLoading ? (openBlock(), createElementBlock("div", _hoisted_2$n)) : createCommentVNode("v-if", true), createBaseVNode("div", _hoisted_3$e, [createBaseVNode("input", {
      ref: "mediaToolbarSelectAll",
      type: "checkbox",
      class: "media-toolbar-icon media-toolbar-select-all",
      "aria-label": _ctx.translate('COM_MEDIA_SELECT_ALL'),
      onClick: _cache[0] || (_cache[0] = withModifiers(function () {
        return $options.toggleSelectAll && $options.toggleSelectAll.apply($options, arguments);
      }, ["stop"]))
    }, null, 8
    /* PROPS */
    , _hoisted_4$9)]), createVNode(_component_media_breadcrumb), createBaseVNode("div", _hoisted_5$9, [createBaseVNode("label", _hoisted_6$7, toDisplayString(_ctx.translate('COM_MEDIA_SEARCH')), 1
    /* TEXT */
    ), createBaseVNode("input", {
      id: "media_search",
      class: "form-control",
      type: "text",
      placeholder: _ctx.translate('COM_MEDIA_SEARCH'),
      value: $options.search,
      onInput: _cache[1] || (_cache[1] = function () {
        return $options.changeSearch && $options.changeSearch.apply($options, arguments);
      })
    }, null, 40
    /* PROPS, HYDRATE_EVENTS */
    , _hoisted_7$4)]), createBaseVNode("div", _hoisted_8$4, [$options.isGridView ? (openBlock(), createElementBlock("button", {
      key: 0,
      type: "button",
      class: normalizeClass(["media-toolbar-icon media-toolbar-decrease-grid-size", {
        disabled: $options.isGridSize('sm')
      }]),
      "aria-label": _ctx.translate('COM_MEDIA_DECREASE_GRID'),
      onClick: _cache[2] || (_cache[2] = withModifiers(function ($event) {
        return $options.decreaseGridSize();
      }, ["stop", "prevent"]))
    }, _hoisted_11$2, 10
    /* CLASS, PROPS */
    , _hoisted_9$4)) : createCommentVNode("v-if", true), $options.isGridView ? (openBlock(), createElementBlock("button", {
      key: 1,
      type: "button",
      class: normalizeClass(["media-toolbar-icon media-toolbar-increase-grid-size", {
        disabled: $options.isGridSize('xl')
      }]),
      "aria-label": _ctx.translate('COM_MEDIA_INCREASE_GRID'),
      onClick: _cache[3] || (_cache[3] = withModifiers(function ($event) {
        return $options.increaseGridSize();
      }, ["stop", "prevent"]))
    }, _hoisted_14, 10
    /* CLASS, PROPS */
    , _hoisted_12$1)) : createCommentVNode("v-if", true), createBaseVNode("button", {
      type: "button",
      href: "#",
      class: "media-toolbar-icon media-toolbar-list-view",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_LIST_VIEW'),
      onClick: _cache[4] || (_cache[4] = withModifiers(function ($event) {
        return $options.changeListView();
      }, ["stop", "prevent"]))
    }, [createBaseVNode("span", {
      class: normalizeClass($options.toggleListViewBtnIcon),
      "aria-hidden": "true"
    }, null, 2
    /* CLASS */
    )], 8
    /* PROPS */
    , _hoisted_15), createBaseVNode("button", {
      type: "button",
      href: "#",
      class: "media-toolbar-icon media-toolbar-info",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_INFO'),
      onClick: _cache[5] || (_cache[5] = withModifiers(function () {
        return $options.toggleInfoBar && $options.toggleInfoBar.apply($options, arguments);
      }, ["stop", "prevent"]))
    }, _hoisted_18, 8
    /* PROPS */
    , _hoisted_16)])], 8
    /* PROPS */
    , _hoisted_1$p);
  }

  script$p.render = render$p;
  script$p.__file = "administrator/components/com_media/resources/scripts/components/toolbar/toolbar.vue";
  var script$o = {
    name: 'MediaBreadcrumb',
    mixins: [navigable],
    computed: {
      /* Get the crumbs from the current directory path */
      crumbs: function crumbs() {
        var _this5 = this;

        var items = [];
        var parts = this.$store.state.selectedDirectory.split('/'); // Add the drive as first element

        if (parts) {
          var drive = this.findDrive(parts[0]);

          if (drive) {
            items.push(drive);
            parts.shift();
          }
        }

        parts.filter(function (crumb) {
          return crumb.length !== 0;
        }).forEach(function (crumb) {
          items.push({
            name: crumb,
            path: _this5.$store.state.selectedDirectory.split(crumb)[0] + crumb
          });
        });
        return items;
      },

      /* Whether or not the crumb is the last element in the list */
      isLast: function isLast(item) {
        return this.crumbs.indexOf(item) === this.crumbs.length - 1;
      }
    },
    methods: {
      /* Handle the on crumb click event */
      onCrumbClick: function onCrumbClick(crumb) {
        this.navigateTo(crumb.path);
        window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
          bubbles: true,
          cancelable: false,
          detail: {}
        }));
      },
      findDrive: function findDrive(adapter) {
        var driveObject = null;
        this.$store.state.disks.forEach(function (disk) {
          disk.drives.forEach(function (drive) {
            if (drive.root.startsWith(adapter)) {
              driveObject = {
                name: drive.displayName,
                path: drive.root
              };
            }
          });
        });
        return driveObject;
      }
    }
  };
  var _hoisted_1$o = ["aria-label"];
  var _hoisted_2$m = ["aria-current", "onClick"];

  function render$o(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("nav", {
      class: "media-breadcrumb",
      "aria-label": _ctx.translate('COM_MEDIA_BREADCRUMB_LABEL')
    }, [createBaseVNode("ol", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.crumbs, function (val, index) {
      return openBlock(), createElementBlock("li", {
        key: index,
        class: "media-breadcrumb-item"
      }, [createBaseVNode("a", {
        href: "#",
        "aria-current": index === Object.keys($options.crumbs).length - 1 ? 'page' : undefined,
        onClick: withModifiers(function ($event) {
          return $options.onCrumbClick(val);
        }, ["stop", "prevent"])
      }, toDisplayString(val.name), 9
      /* TEXT, PROPS */
      , _hoisted_2$m)]);
    }), 128
    /* KEYED_FRAGMENT */
    ))])], 8
    /* PROPS */
    , _hoisted_1$o);
  }

  script$o.render = render$o;
  script$o.__file = "administrator/components/com_media/resources/scripts/components/breadcrumb/breadcrumb.vue";
  var script$n = {
    name: 'MediaBrowser',
    computed: {
      /* Get the contents of the currently selected directory */
      items: function items() {
        var _this6 = this;

        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        var directories = this.$store.getters.getSelectedDirectoryDirectories // Sort by type and alphabetically
        .sort(function (a, b) {
          return a.name.toUpperCase() < b.name.toUpperCase() ? -1 : 1;
        }).filter(function (dir) {
          return dir.name.toLowerCase().includes(_this6.$store.state.search.toLowerCase());
        }); // eslint-disable-next-line vue/no-side-effects-in-computed-properties

        var files = this.$store.getters.getSelectedDirectoryFiles // Sort by type and alphabetically
        .sort(function (a, b) {
          return a.name.toUpperCase() < b.name.toUpperCase() ? -1 : 1;
        }).filter(function (file) {
          return file.name.toLowerCase().includes(_this6.$store.state.search.toLowerCase());
        });
        return [].concat(directories, files);
      },

      /* The styles for the media-browser element */
      mediaBrowserStyles: function mediaBrowserStyles() {
        return {
          width: this.$store.state.showInfoBar ? '75%' : '100%'
        };
      },

      /* The styles for the media-browser element */
      listView: function listView() {
        return this.$store.state.listView;
      },
      mediaBrowserGridItemsClass: function mediaBrowserGridItemsClass() {
        var _ref24;

        return _ref24 = {}, _ref24["media-browser-items-" + this.$store.state.gridSize] = true, _ref24;
      },
      isModal: function isModal() {
        return Joomla.getOptions('com_media', {}).isModal;
      },
      currentDirectory: function currentDirectory() {
        var parts = this.$store.state.selectedDirectory.split('/').filter(function (crumb) {
          return crumb.length !== 0;
        }); // The first part is the name of the drive, so if we have a folder name display it. Else
        // find the filename

        if (parts.length !== 1) {
          return parts[parts.length - 1];
        }

        var diskName = '';
        this.$store.state.disks.forEach(function (disk) {
          disk.drives.forEach(function (drive) {
            if (drive.root === parts[0] + "/") {
              diskName = drive.displayName;
            }
          });
        });
        return diskName;
      }
    },
    created: function created() {
      document.body.addEventListener('click', this.unselectAllBrowserItems, false);
    },
    beforeUnmount: function beforeUnmount() {
      document.body.removeEventListener('click', this.unselectAllBrowserItems, false);
    },
    methods: {
      /* Unselect all browser items */
      unselectAllBrowserItems: function unselectAllBrowserItems(event) {
        var clickedDelete = !!(event.target.id !== undefined && event.target.id === 'mediaDelete');
        var notClickedBrowserItems = this.$refs.browserItems && !this.$refs.browserItems.contains(event.target) || event.target === this.$refs.browserItems;
        var notClickedInfobar = this.$refs.infobar !== undefined && !this.$refs.infobar.$el.contains(event.target);
        var clickedOutside = notClickedBrowserItems && notClickedInfobar && !clickedDelete;

        if (clickedOutside) {
          this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
          window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
            bubbles: true,
            cancelable: false,
            detail: {
              path: '',
              thumb: false,
              fileType: false,
              extension: false
            }
          }));
        }
      },
      // Listeners for drag and drop
      // Fix for Chrome
      onDragEnter: function onDragEnter(e) {
        e.stopPropagation();
        return false;
      },
      // Notify user when file is over the drop area
      onDragOver: function onDragOver(e) {
        e.preventDefault();
        document.querySelector('.media-dragoutline').classList.add('active');
        return false;
      },

      /* Upload files */
      upload: function upload(file) {
        var _this7 = this;

        // Create a new file reader instance
        var reader = new FileReader(); // Add the on load callback

        reader.onload = function (progressEvent) {
          var result = progressEvent.target.result;
          var splitIndex = result.indexOf('base64') + 7;
          var content = result.slice(splitIndex, result.length); // Upload the file

          _this7.$store.dispatch('uploadFile', {
            name: file.name,
            parent: _this7.$store.state.selectedDirectory,
            content: content
          });
        };

        reader.readAsDataURL(file);
      },
      // Logic for the dropped file
      onDrop: function onDrop(e) {
        e.preventDefault(); // Loop through array of files and upload each file

        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
          // eslint-disable-next-line no-plusplus,no-cond-assign
          for (var i = 0, f; f = e.dataTransfer.files[i]; i++) {
            document.querySelector('.media-dragoutline').classList.remove('active');
            this.upload(f);
          }
        }

        document.querySelector('.media-dragoutline').classList.remove('active');
      },
      // Reset the drop area border
      onDragLeave: function onDragLeave(e) {
        e.stopPropagation();
        e.preventDefault();
        document.querySelector('.media-dragoutline').classList.remove('active');
        return false;
      }
    }
  };
  var _hoisted_1$n = {
    class: "media-dragoutline"
  };

  var _hoisted_2$l = /*#__PURE__*/createBaseVNode("span", {
    class: "icon-cloud-upload upload-icon",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_3$d = {
    key: 0,
    class: "table media-browser-table"
  };
  var _hoisted_4$8 = {
    class: "visually-hidden"
  };
  var _hoisted_5$8 = {
    class: "media-browser-table-head"
  };

  var _hoisted_6$6 = /*#__PURE__*/createBaseVNode("th", {
    class: "type",
    scope: "col"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_7$3 = {
    class: "name",
    scope: "col"
  };
  var _hoisted_8$3 = {
    class: "size",
    scope: "col"
  };
  var _hoisted_9$3 = {
    class: "dimension",
    scope: "col"
  };
  var _hoisted_10$1 = {
    class: "created",
    scope: "col"
  };
  var _hoisted_11$1 = {
    class: "modified",
    scope: "col"
  };
  var _hoisted_12 = {
    key: 1,
    class: "media-browser-grid"
  };

  function render$n(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_browser_item_row = resolveComponent("media-browser-item-row");

    var _component_media_browser_item = resolveComponent("media-browser-item");

    var _component_media_infobar = resolveComponent("media-infobar");

    return openBlock(), createElementBlock("div", null, [createBaseVNode("div", {
      ref: "browserItems",
      class: "media-browser",
      style: normalizeStyle($options.mediaBrowserStyles),
      onDragenter: _cache[0] || (_cache[0] = function () {
        return $options.onDragEnter && $options.onDragEnter.apply($options, arguments);
      }),
      onDrop: _cache[1] || (_cache[1] = function () {
        return $options.onDrop && $options.onDrop.apply($options, arguments);
      }),
      onDragover: _cache[2] || (_cache[2] = function () {
        return $options.onDragOver && $options.onDragOver.apply($options, arguments);
      }),
      onDragleave: _cache[3] || (_cache[3] = function () {
        return $options.onDragLeave && $options.onDragLeave.apply($options, arguments);
      })
    }, [createBaseVNode("div", _hoisted_1$n, [_hoisted_2$l, createBaseVNode("p", null, toDisplayString(_ctx.translate('COM_MEDIA_DROP_FILE')), 1
    /* TEXT */
    )]), $options.listView === 'table' ? (openBlock(), createElementBlock("table", _hoisted_3$d, [createBaseVNode("caption", _hoisted_4$8, toDisplayString(_ctx.sprintf('COM_MEDIA_BROWSER_TABLE_CAPTION', $options.currentDirectory)), 1
    /* TEXT */
    ), createBaseVNode("thead", _hoisted_5$8, [createBaseVNode("tr", null, [_hoisted_6$6, createBaseVNode("th", _hoisted_7$3, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_NAME')), 1
    /* TEXT */
    ), createBaseVNode("th", _hoisted_8$3, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_SIZE')), 1
    /* TEXT */
    ), createBaseVNode("th", _hoisted_9$3, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DIMENSION')), 1
    /* TEXT */
    ), createBaseVNode("th", _hoisted_10$1, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_CREATED')), 1
    /* TEXT */
    ), createBaseVNode("th", _hoisted_11$1, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_MODIFIED')), 1
    /* TEXT */
    )])]), createBaseVNode("tbody", null, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.items, function (item) {
      return openBlock(), createBlock(_component_media_browser_item_row, {
        key: item.path,
        item: item
      }, null, 8
      /* PROPS */
      , ["item"]);
    }), 128
    /* KEYED_FRAGMENT */
    ))])])) : $options.listView === 'grid' ? (openBlock(), createElementBlock("div", _hoisted_12, [createBaseVNode("div", {
      class: normalizeClass(["media-browser-items", $options.mediaBrowserGridItemsClass])
    }, [(openBlock(true), createElementBlock(Fragment, null, renderList($options.items, function (item) {
      return openBlock(), createBlock(_component_media_browser_item, {
        key: item.path,
        item: item
      }, null, 8
      /* PROPS */
      , ["item"]);
    }), 128
    /* KEYED_FRAGMENT */
    ))], 2
    /* CLASS */
    )])) : createCommentVNode("v-if", true)], 36
    /* STYLE, HYDRATE_EVENTS */
    ), createVNode(_component_media_infobar, {
      ref: "infobar"
    }, null, 512
    /* NEED_PATCH */
    )]);
  }

  script$n.render = render$n;
  script$n.__file = "administrator/components/com_media/resources/scripts/components/browser/browser.vue";
  var script$m = {
    name: 'MediaBrowserItemDirectory',
    mixins: [navigable],
    // eslint-disable-next-line vue/require-prop-types
    props: ['item'],
    emits: ['toggle-settings'],
    data: function data() {
      return {
        showActions: false
      };
    },
    methods: {
      /* Handle the on preview double click event */
      onPreviewDblClick: function onPreviewDblClick() {
        this.navigateTo(this.item.path);
      },

      /* Hide actions dropdown */
      hideActions: function hideActions() {
        this.$refs.container.hideActions();
      },
      toggleSettings: function toggleSettings(bool) {
        this.$emit('toggle-settings', bool);
      }
    }
  };

  var _hoisted_1$m = /*#__PURE__*/createBaseVNode("div", {
    class: "file-background"
  }, [/*#__PURE__*/createBaseVNode("div", {
    class: "folder-icon"
  }, [/*#__PURE__*/createBaseVNode("span", {
    class: "icon-folder"
  })])], -1
  /* HOISTED */
  );

  var _hoisted_2$k = [_hoisted_1$m];
  var _hoisted_3$c = {
    class: "media-browser-item-info"
  };

  function render$m(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

    return openBlock(), createElementBlock("div", {
      class: "media-browser-item-directory",
      onMouseleave: _cache[2] || (_cache[2] = function ($event) {
        return $options.hideActions();
      })
    }, [createBaseVNode("div", {
      class: "media-browser-item-preview",
      tabindex: "0",
      onDblclick: _cache[0] || (_cache[0] = withModifiers(function ($event) {
        return $options.onPreviewDblClick();
      }, ["stop", "prevent"])),
      onKeyup: _cache[1] || (_cache[1] = withKeys(function ($event) {
        return $options.onPreviewDblClick();
      }, ["enter"]))
    }, _hoisted_2$k, 32
    /* HYDRATE_EVENTS */
    ), createBaseVNode("div", _hoisted_3$c, toDisplayString($props.item.name), 1
    /* TEXT */
    ), createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      onToggleSettings: $options.toggleSettings
    }, null, 8
    /* PROPS */
    , ["item", "onToggleSettings"])], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$m.render = render$m;
  script$m.__file = "administrator/components/com_media/resources/scripts/components/browser/items/directory.vue";
  var script$l = {
    name: 'MediaBrowserItemFile',
    // eslint-disable-next-line vue/require-prop-types
    props: ['item', 'focused'],
    emits: ['toggle-settings'],
    data: function data() {
      return {
        showActions: false
      };
    },
    methods: {
      /* Hide actions dropdown */
      hideActions: function hideActions() {
        this.$refs.container.hideActions();
      },

      /* Preview an item */
      openPreview: function openPreview() {
        this.$refs.container.openPreview();
      },
      toggleSettings: function toggleSettings(bool) {
        this.$emit('toggle-settings', bool);
      }
    }
  };

  var _hoisted_1$l = /*#__PURE__*/createBaseVNode("div", {
    class: "media-browser-item-preview"
  }, [/*#__PURE__*/createBaseVNode("div", {
    class: "file-background"
  }, [/*#__PURE__*/createBaseVNode("div", {
    class: "file-icon"
  }, [/*#__PURE__*/createBaseVNode("span", {
    class: "icon-file-alt"
  })])])], -1
  /* HOISTED */
  );

  var _hoisted_2$j = {
    class: "media-browser-item-info"
  };
  var _hoisted_3$b = ["aria-label", "title"];

  function render$l(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

    return openBlock(), createElementBlock("div", {
      class: "media-browser-item-file",
      onMouseleave: _cache[0] || (_cache[0] = function ($event) {
        return $options.hideActions();
      })
    }, [_hoisted_1$l, createBaseVNode("div", _hoisted_2$j, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1
    /* TEXT */
    ), createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM')
    }, null, 8
    /* PROPS */
    , _hoisted_3$b), createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8
    /* PROPS */
    , ["item", "onToggleSettings"])], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$l.render = render$l;
  script$l.__file = "administrator/components/com_media/resources/scripts/components/browser/items/file.vue";

  var dirname = function dirname(path) {
    if (typeof path !== 'string') {
      throw new TypeError('Path must be a string. Received ' + JSON.stringify(path));
    }

    if (path.length === 0) return '.';
    var code = path.charCodeAt(0);
    var hasRoot = code === 47;
    var end = -1;
    var matchedSlash = true;

    for (var i = path.length - 1; i >= 1; --i) {
      code = path.charCodeAt(i);

      if (code === 47) {
        if (!matchedSlash) {
          end = i;
          break;
        }
      } else {
        // We saw the first non-path separator
        matchedSlash = false;
      }
    }

    if (end === -1) return hasRoot ? '/' : '.';
    if (hasRoot && end === 1) return '//';
    return path.slice(0, end);
  };
  /**
   * Api class for communication with the server
   */


  var Api = /*#__PURE__*/function () {
    /**
       * Store constructor
       */
    function Api() {
      var options = Joomla.getOptions('com_media', {});

      if (options.apiBaseUrl === undefined) {
        throw new TypeError('Media api baseUrl is not defined');
      }

      if (options.csrfToken === undefined) {
        throw new TypeError('Media api csrf token is not defined');
      } // eslint-disable-next-line no-underscore-dangle


      this._baseUrl = options.apiBaseUrl; // eslint-disable-next-line no-underscore-dangle

      this._csrfToken = Joomla.getOptions('csrf.token');
      this.imagesExtensions = options.imagesExtensions;
      this.audioExtensions = options.audioExtensions;
      this.videoExtensions = options.videoExtensions;
      this.documentExtensions = options.documentExtensions;
      this.mediaVersion = new Date().getTime().toString();
      this.canCreate = options.canCreate || false;
      this.canEdit = options.canEdit || false;
      this.canDelete = options.canDelete || false;
    }
    /**
       * Get the contents of a directory from the server
       * @param {string}  dir  The directory path
       * @param {number}  full whether or not the persistent url should be returned
       * @param {number}  content whether or not the content should be returned
       * @returns {Promise}
       */


    var _proto5 = Api.prototype;

    _proto5.getContents = function getContents(dir, full, content) {
      var _this8 = this;

      // Wrap the ajax call into a real promise
      return new Promise(function (resolve, reject) {
        // Do a check on full
        if (['0', '1'].indexOf(full) !== -1) {
          throw Error('Invalid parameter: full');
        } // Do a check on download


        if (['0', '1'].indexOf(content) !== -1) {
          throw Error('Invalid parameter: content');
        } // eslint-disable-next-line no-underscore-dangle


        var url = _this8._baseUrl + "&task=api.files&path=" + encodeURIComponent(dir);

        if (full) {
          url += "&url=" + full;
        }

        if (content) {
          url += "&content=" + content;
        }

        Joomla.request({
          url: url,
          method: 'GET',
          headers: {
            'Content-Type': 'application/json'
          },
          onSuccess: function onSuccess(response) {
            // eslint-disable-next-line no-underscore-dangle
            resolve(_this8._normalizeArray(JSON.parse(response).data));
          },
          onError: function onError(xhr) {
            reject(xhr);
          }
        }); // eslint-disable-next-line no-underscore-dangle
      }).catch(this._handleError);
    }
    /**
       * Create a directory
       * @param name
       * @param parent
       * @returns {Promise.<T>}
       */
    ;

    _proto5.createDirectory = function createDirectory(name, parent) {
      var _this9 = this;

      // Wrap the ajax call into a real promise
      return new Promise(function (resolve, reject) {
        var _data;

        // eslint-disable-next-line no-underscore-dangle
        var url = _this9._baseUrl + "&task=api.files&path=" + encodeURIComponent(parent); // eslint-disable-next-line no-underscore-dangle

        var data = (_data = {}, _data[_this9._csrfToken] = '1', _data.name = name, _data);
        Joomla.request({
          url: url,
          method: 'POST',
          data: JSON.stringify(data),
          headers: {
            'Content-Type': 'application/json'
          },
          onSuccess: function onSuccess(response) {
            notifications.success('COM_MEDIA_CREATE_NEW_FOLDER_SUCCESS'); // eslint-disable-next-line no-underscore-dangle

            resolve(_this9._normalizeItem(JSON.parse(response).data));
          },
          onError: function onError(xhr) {
            notifications.error('COM_MEDIA_CREATE_NEW_FOLDER_ERROR');
            reject(xhr);
          }
        }); // eslint-disable-next-line no-underscore-dangle
      }).catch(this._handleError);
    }
    /**
       * Upload a file
       * @param name
       * @param parent
       * @param content base64 encoded string
       * @param override boolean whether or not we should override existing files
       * @return {Promise.<T>}
       */
    ;

    _proto5.upload = function upload(name, parent, content, override) {
      var _this10 = this;

      // Wrap the ajax call into a real promise
      return new Promise(function (resolve, reject) {
        var _data2;

        // eslint-disable-next-line no-underscore-dangle
        var url = _this10._baseUrl + "&task=api.files&path=" + encodeURIComponent(parent);
        var data = (_data2 = {}, _data2[_this10._csrfToken] = '1', _data2.name = name, _data2.content = content, _data2); // Append override

        if (override === true) {
          data.override = true;
        }

        Joomla.request({
          url: url,
          method: 'POST',
          data: JSON.stringify(data),
          headers: {
            'Content-Type': 'application/json'
          },
          onSuccess: function onSuccess(response) {
            notifications.success('COM_MEDIA_UPLOAD_SUCCESS'); // eslint-disable-next-line no-underscore-dangle

            resolve(_this10._normalizeItem(JSON.parse(response).data));
          },
          onError: function onError(xhr) {
            reject(xhr);
          }
        }); // eslint-disable-next-line no-underscore-dangle
      }).catch(this._handleError);
    }
    /**
       * Rename an item
       * @param path
       * @param newPath
       * @return {Promise.<T>}
       */
    // eslint-disable-next-line no-shadow
    ;

    _proto5.rename = function rename(path, newPath) {
      var _this11 = this;

      // Wrap the ajax call into a real promise
      return new Promise(function (resolve, reject) {
        var _data3;

        // eslint-disable-next-line no-underscore-dangle
        var url = _this11._baseUrl + "&task=api.files&path=" + encodeURIComponent(path);
        var data = (_data3 = {}, _data3[_this11._csrfToken] = '1', _data3.newPath = newPath, _data3);
        Joomla.request({
          url: url,
          method: 'PUT',
          data: JSON.stringify(data),
          headers: {
            'Content-Type': 'application/json'
          },
          onSuccess: function onSuccess(response) {
            notifications.success('COM_MEDIA_RENAME_SUCCESS'); // eslint-disable-next-line no-underscore-dangle

            resolve(_this11._normalizeItem(JSON.parse(response).data));
          },
          onError: function onError(xhr) {
            notifications.error('COM_MEDIA_RENAME_ERROR');
            reject(xhr);
          }
        }); // eslint-disable-next-line no-underscore-dangle
      }).catch(this._handleError);
    }
    /**
       * Delete a file
       * @param path
       * @return {Promise.<T>}
       */
    // eslint-disable-next-line no-shadow
    ;

    _proto5.delete = function _delete(path) {
      var _this12 = this;

      // Wrap the ajax call into a real promise
      return new Promise(function (resolve, reject) {
        var _data4;

        // eslint-disable-next-line no-underscore-dangle
        var url = _this12._baseUrl + "&task=api.files&path=" + encodeURIComponent(path); // eslint-disable-next-line no-underscore-dangle

        var data = (_data4 = {}, _data4[_this12._csrfToken] = '1', _data4);
        Joomla.request({
          url: url,
          method: 'DELETE',
          data: JSON.stringify(data),
          headers: {
            'Content-Type': 'application/json'
          },
          onSuccess: function onSuccess() {
            notifications.success('COM_MEDIA_DELETE_SUCCESS');
            resolve();
          },
          onError: function onError(xhr) {
            notifications.error('COM_MEDIA_DELETE_ERROR');
            reject(xhr);
          }
        }); // eslint-disable-next-line no-underscore-dangle
      }).catch(this._handleError);
    }
    /**
       * Normalize a single item
       * @param item
       * @returns {*}
       * @private
       */
    // eslint-disable-next-line no-underscore-dangle,class-methods-use-this
    ;

    _proto5._normalizeItem = function _normalizeItem(item) {
      if (item.type === 'dir') {
        item.directories = [];
        item.files = [];
      }

      item.directory = dirname(item.path);

      if (item.directory.indexOf(':', item.directory.length - 1) !== -1) {
        item.directory += '/';
      }

      return item;
    }
    /**
       * Normalize array data
       * @param data
       * @returns {{directories, files}}
       * @private
       */
    // eslint-disable-next-line no-underscore-dangle
    ;

    _proto5._normalizeArray = function _normalizeArray(data) {
      var _this13 = this;

      var directories = data.filter(function (item) {
        return item.type === 'dir';
      }) // eslint-disable-next-line no-underscore-dangle
      .map(function (directory) {
        return _this13._normalizeItem(directory);
      });
      var files = data.filter(function (item) {
        return item.type === 'file';
      }) // eslint-disable-next-line no-underscore-dangle
      .map(function (file) {
        return _this13._normalizeItem(file);
      });
      return {
        directories: directories,
        files: files
      };
    }
    /**
       * Handle errors
       * @param error
       * @private
       *
       * @TODO DN improve error handling
       */
    // eslint-disable-next-line no-underscore-dangle,class-methods-use-this
    ;

    _proto5._handleError = function _handleError(error) {
      var response = JSON.parse(error.response);

      if (response.message) {
        notifications.error(response.message);
      } else {
        switch (error.status) {
          case 409:
            // Handled in consumer
            break;

          case 404:
            notifications.error('COM_MEDIA_ERROR_NOT_FOUND');
            break;

          case 401:
            notifications.error('COM_MEDIA_ERROR_NOT_AUTHENTICATED');
            break;

          case 403:
            notifications.error('COM_MEDIA_ERROR_NOT_AUTHORIZED');
            break;

          case 500:
            notifications.error('COM_MEDIA_SERVER_ERROR');
            break;

          default:
            notifications.error('COM_MEDIA_ERROR');
        }
      }

      throw error;
    };

    return Api;
  }(); // eslint-disable-next-line import/prefer-default-export


  var api = new Api();
  var script$k = {
    name: 'MediaBrowserItemImage',
    props: {
      item: {
        type: Object,
        required: true
      },
      focused: {
        type: Boolean,
        required: true,
        default: false
      }
    },
    emits: ['toggle-settings'],
    data: function data() {
      return {
        showActions: {
          type: Boolean,
          default: false
        }
      };
    },
    computed: {
      getURL: function getURL() {
        if (!this.item.thumb_path) {
          return '';
        }

        return this.item.thumb_path.split(Joomla.getOptions('system.paths').rootFull).length > 1 ? this.item.thumb_path + "?" + api.mediaVersion : "" + this.item.thumb_path;
      },
      width: function width() {
        return this.item.width > 0 ? this.item.width : null;
      },
      height: function height() {
        return this.item.height > 0 ? this.item.height : null;
      },
      loading: function loading() {
        return this.item.width > 0 ? 'lazy' : null;
      },
      altTag: function altTag() {
        return this.item.name;
      }
    },
    methods: {
      /* Check if the item is an image to edit */
      canEdit: function canEdit() {
        return ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
      },

      /* Hide actions dropdown */
      hideActions: function hideActions() {
        this.$refs.container.hideActions();
      },

      /* Preview an item */
      openPreview: function openPreview() {
        this.$refs.container.openPreview();
      },

      /* Edit an item */
      editItem: function editItem() {
        // @todo should we use relative urls here?
        var fileBaseUrl = Joomla.getOptions('com_media').editViewUrl + "&path=";
        window.location.href = fileBaseUrl + this.item.path;
      },
      toggleSettings: function toggleSettings(bool) {
        this.$emit('toggle-settings', bool);
      }
    }
  };
  var _hoisted_1$k = ["title"];
  var _hoisted_2$i = {
    class: "image-background"
  };
  var _hoisted_3$a = ["src", "alt", "loading", "width", "height"];
  var _hoisted_4$7 = {
    key: 1,
    class: "icon-eye-slash image-placeholder",
    "aria-hidden": "true"
  };
  var _hoisted_5$7 = ["title"];
  var _hoisted_6$5 = ["aria-label", "title"];

  function render$k(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

    return openBlock(), createElementBlock("div", {
      class: "media-browser-image",
      tabindex: "0",
      onDblclick: _cache[0] || (_cache[0] = function ($event) {
        return $options.openPreview();
      }),
      onMouseleave: _cache[1] || (_cache[1] = function ($event) {
        return $options.hideActions();
      }),
      onKeyup: _cache[2] || (_cache[2] = withKeys(function ($event) {
        return $options.openPreview();
      }, ["enter"]))
    }, [createBaseVNode("div", {
      class: "media-browser-item-preview",
      title: $props.item.name
    }, [createBaseVNode("div", _hoisted_2$i, [$options.getURL ? (openBlock(), createElementBlock("img", {
      key: 0,
      class: "image-cropped",
      src: $options.getURL,
      alt: $options.altTag,
      loading: $options.loading,
      width: $options.width,
      height: $options.height
    }, null, 8
    /* PROPS */
    , _hoisted_3$a)) : createCommentVNode("v-if", true), !$options.getURL ? (openBlock(), createElementBlock("span", _hoisted_4$7)) : createCommentVNode("v-if", true)])], 8
    /* PROPS */
    , _hoisted_1$k), createBaseVNode("div", {
      class: "media-browser-item-info",
      title: $props.item.name
    }, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 9
    /* TEXT, PROPS */
    , _hoisted_5$7), createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM')
    }, null, 8
    /* PROPS */
    , _hoisted_6$5), createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      edit: $options.editItem,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8
    /* PROPS */
    , ["item", "edit", "onToggleSettings"])], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$k.render = render$k;
  script$k.__file = "administrator/components/com_media/resources/scripts/components/browser/items/image.vue";
  var script$j = {
    name: 'MediaBrowserItemVideo',
    // eslint-disable-next-line vue/require-prop-types
    props: ['item', 'focused'],
    emits: ['toggle-settings'],
    data: function data() {
      return {
        showActions: false
      };
    },
    methods: {
      /* Hide actions dropdown */
      hideActions: function hideActions() {
        this.$refs.container.hideActions();
      },

      /* Preview an item */
      openPreview: function openPreview() {
        this.$refs.container.openPreview();
      },
      toggleSettings: function toggleSettings(bool) {
        this.$emit('toggle-settings', bool);
      }
    }
  };

  var _hoisted_1$j = /*#__PURE__*/createBaseVNode("div", {
    class: "media-browser-item-preview"
  }, [/*#__PURE__*/createBaseVNode("div", {
    class: "file-background"
  }, [/*#__PURE__*/createBaseVNode("div", {
    class: "file-icon"
  }, [/*#__PURE__*/createBaseVNode("span", {
    class: "fas fa-file-video"
  })])])], -1
  /* HOISTED */
  );

  var _hoisted_2$h = {
    class: "media-browser-item-info"
  };

  function render$j(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

    return openBlock(), createElementBlock("div", {
      class: "media-browser-image",
      onDblclick: _cache[0] || (_cache[0] = function ($event) {
        return $options.openPreview();
      }),
      onMouseleave: _cache[1] || (_cache[1] = function ($event) {
        return $options.hideActions();
      })
    }, [_hoisted_1$j, createBaseVNode("div", _hoisted_2$h, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1
    /* TEXT */
    ), createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8
    /* PROPS */
    , ["item", "onToggleSettings"])], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$j.render = render$j;
  script$j.__file = "administrator/components/com_media/resources/scripts/components/browser/items/video.vue";
  var script$i = {
    name: 'MediaBrowserItemAudio',
    // eslint-disable-next-line vue/require-prop-types
    props: ['item', 'focused'],
    emits: ['toggle-settings'],
    data: function data() {
      return {
        showActions: false
      };
    },
    methods: {
      /* Hide actions dropdown */
      hideActions: function hideActions() {
        this.$refs.container.hideActions();
      },

      /* Preview an item */
      openPreview: function openPreview() {
        this.$refs.container.openPreview();
      },
      toggleSettings: function toggleSettings(bool) {
        this.$emit('toggle-settings', bool);
      }
    }
  };

  var _hoisted_1$i = /*#__PURE__*/createBaseVNode("div", {
    class: "media-browser-item-preview"
  }, [/*#__PURE__*/createBaseVNode("div", {
    class: "file-background"
  }, [/*#__PURE__*/createBaseVNode("div", {
    class: "file-icon"
  }, [/*#__PURE__*/createBaseVNode("span", {
    class: "fas fa-file-audio"
  })])])], -1
  /* HOISTED */
  );

  var _hoisted_2$g = {
    class: "media-browser-item-info"
  };

  function render$i(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

    return openBlock(), createElementBlock("div", {
      class: "media-browser-audio",
      tabindex: "0",
      onDblclick: _cache[0] || (_cache[0] = function ($event) {
        return $options.openPreview();
      }),
      onMouseleave: _cache[1] || (_cache[1] = function ($event) {
        return $options.hideActions();
      }),
      onKeyup: _cache[2] || (_cache[2] = withKeys(function ($event) {
        return $options.openPreview();
      }, ["enter"]))
    }, [_hoisted_1$i, createBaseVNode("div", _hoisted_2$g, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1
    /* TEXT */
    ), createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8
    /* PROPS */
    , ["item", "onToggleSettings"])], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$i.render = render$i;
  script$i.__file = "administrator/components/com_media/resources/scripts/components/browser/items/audio.vue";
  var script$h = {
    name: 'MediaBrowserItemDocument',
    // eslint-disable-next-line vue/require-prop-types
    props: ['item', 'focused'],
    emits: ['toggle-settings'],
    data: function data() {
      return {
        showActions: false
      };
    },
    methods: {
      /* Hide actions dropdown */
      hideActions: function hideActions() {
        this.$refs.container.hideActions();
      },

      /* Preview an item */
      openPreview: function openPreview() {
        this.$refs.container.openPreview();
      },
      toggleSettings: function toggleSettings(bool) {
        this.$emit('toggle-settings', bool);
      }
    }
  };

  var _hoisted_1$h = /*#__PURE__*/createBaseVNode("div", {
    class: "media-browser-item-preview"
  }, [/*#__PURE__*/createBaseVNode("div", {
    class: "file-background"
  }, [/*#__PURE__*/createBaseVNode("div", {
    class: "file-icon"
  }, [/*#__PURE__*/createBaseVNode("span", {
    class: "fas fa-file-pdf"
  })])])], -1
  /* HOISTED */
  );

  var _hoisted_2$f = {
    class: "media-browser-item-info"
  };
  var _hoisted_3$9 = ["aria-label", "title"];

  function render$h(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

    return openBlock(), createElementBlock("div", {
      class: "media-browser-doc",
      onDblclick: _cache[0] || (_cache[0] = function ($event) {
        return $options.openPreview();
      }),
      onMouseleave: _cache[1] || (_cache[1] = function ($event) {
        return $options.hideActions();
      })
    }, [_hoisted_1$h, createBaseVNode("div", _hoisted_2$f, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1
    /* TEXT */
    ), createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM')
    }, null, 8
    /* PROPS */
    , _hoisted_3$9), createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8
    /* PROPS */
    , ["item", "onToggleSettings"])], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$h.render = render$h;
  script$h.__file = "administrator/components/com_media/resources/scripts/components/browser/items/document.vue";
  var BrowserItem = {
    props: ['item'],
    data: function data() {
      return {
        hoverActive: false,
        actionsActive: false
      };
    },
    methods: {
      /**
       * Return the correct item type component
       */
      itemType: function itemType() {
        // Render directory items
        if (this.item.type === 'dir') return script$m; // Render image items

        if (this.item.extension && api.imagesExtensions.includes(this.item.extension.toLowerCase())) {
          return script$k;
        } // Render video items


        if (this.item.extension && api.videoExtensions.includes(this.item.extension.toLowerCase())) {
          return script$j;
        } // Render audio items


        if (this.item.extension && api.audioExtensions.includes(this.item.extension.toLowerCase())) {
          return script$i;
        } // Render document items


        if (this.item.extension && api.documentExtensions.includes(this.item.extension.toLowerCase())) {
          return script$h;
        } // Default to file type


        return script$l;
      },

      /**
       * Get the styles for the media browser item
       * @returns {{}}
       */
      styles: function styles() {
        return {
          width: "calc(" + this.$store.state.gridSize + "% - 20px)"
        };
      },

      /**
       * Whether or not the item is currently selected
       * @returns {boolean}
       */
      isSelected: function isSelected() {
        var _this14 = this;

        return this.$store.state.selectedItems.some(function (selected) {
          return selected.path === _this14.item.path;
        });
      },

      /**
       * Whether or not the item is currently active (on hover or via tab)
       * @returns {boolean}
       */
      isHoverActive: function isHoverActive() {
        return this.hoverActive;
      },

      /**
       * Whether or not the item is currently active (on hover or via tab)
       * @returns {boolean}
       */
      hasActions: function hasActions() {
        return this.actionsActive;
      },

      /**
       * Turns on the hover class
       */
      mouseover: function mouseover() {
        this.hoverActive = true;
      },

      /**
       * Turns off the hover class
       */
      mouseleave: function mouseleave() {
        this.hoverActive = false;
      },

      /**
       * Handle the click event
       * @param event
       */
      handleClick: function handleClick(event) {
        if (this.item.path && this.item.type === 'file') {
          window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
            bubbles: true,
            cancelable: false,
            detail: {
              path: this.item.path,
              thumb: this.item.thumb,
              fileType: this.item.mime_type ? this.item.mime_type : false,
              extension: this.item.extension ? this.item.extension : false,
              width: this.item.width ? this.item.width : 0,
              height: this.item.height ? this.item.height : 0
            }
          }));
        }

        if (this.item.type === 'dir') {
          window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
            bubbles: true,
            cancelable: false,
            detail: {}
          }));
        } // Handle clicks when the item was not selected


        if (!this.isSelected()) {
          // Unselect all other selected items,
          // if the shift key was not pressed during the click event
          if (!(event.shiftKey || event.keyCode === 13)) {
            this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
          }

          this.$store.commit(SELECT_BROWSER_ITEM, this.item);
          return;
        }

        this.$store.dispatch('toggleBrowserItemSelect', this.item);
        window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
          bubbles: true,
          cancelable: false,
          detail: {}
        })); // If more than one item was selected and the user clicks again on the selected item,
        // he most probably wants to unselect all other items.

        if (this.$store.state.selectedItems.length > 1) {
          this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
          this.$store.commit(SELECT_BROWSER_ITEM, this.item);
        }
      },

      /**
       * Handle the when an element is focused in the child to display the layover for a11y
       * @param active
       */
      toggleSettings: function toggleSettings(active) {
        // eslint-disable-next-line no-unused-expressions
        active ? this.mouseover() : this.mouseleave();
      }
    },
    render: function render() {
      return h('div', {
        class: {
          'media-browser-item': true,
          selected: this.isSelected(),
          active: this.isHoverActive(),
          actions: this.hasActions()
        },
        onClick: this.handleClick,
        onMouseover: this.mouseover,
        onMouseleave: this.mouseleave
      }, [h(this.itemType(), {
        item: this.item,
        onToggleSettings: this.toggleSettings
      })]);
    }
  };
  var script$g = {
    name: 'MediaBrowserItemRow',
    mixins: [navigable],
    // eslint-disable-next-line vue/require-prop-types
    props: ['item'],
    computed: {
      /* The dimension of a file */
      dimension: function dimension() {
        if (!this.item.width) {
          return '';
        }

        return this.item.width + "px * " + this.item.height + "px";
      },
      isDir: function isDir() {
        return this.item.type === 'dir';
      },

      /* The size of a file in KB */
      size: function size() {
        if (!this.item.size) {
          return '';
        }

        return (this.item.size / 1024).toFixed(2) + " KB";
      },
      selected: function selected() {
        return !!this.isSelected();
      }
    },
    methods: {
      /* Handle the on row double click event */
      onDblClick: function onDblClick() {
        if (this.isDir) {
          this.navigateTo(this.item.path);
          return;
        } // @todo remove the hardcoded extensions here


        var extensionWithPreview = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mp3', 'pdf']; // Show preview

        if (this.item.extension && extensionWithPreview.includes(this.item.extension.toLowerCase())) {
          this.$store.commit(SHOW_PREVIEW_MODAL);
          this.$store.dispatch('getFullContents', this.item);
        }
      },

      /**
       * Whether or not the item is currently selected
       * @returns {boolean}
       */
      isSelected: function isSelected() {
        var _this15 = this;

        return this.$store.state.selectedItems.some(function (selected) {
          return selected.path === _this15.item.path;
        });
      },

      /**
       * Handle the click event
       * @param event
       */
      onClick: function onClick(event) {
        var path = false;
        var data = {
          path: path,
          thumb: false,
          fileType: this.item.mime_type ? this.item.mime_type : false,
          extension: this.item.extension ? this.item.extension : false
        };

        if (this.item.type === 'file') {
          data.path = this.item.path;
          data.thumb = this.item.thumb ? this.item.thumb : false;
          data.width = this.item.width ? this.item.width : 0;
          data.height = this.item.height ? this.item.height : 0;
          window.parent.document.dispatchEvent(new CustomEvent('onMediaFileSelected', {
            bubbles: true,
            cancelable: false,
            detail: data
          }));
        } // Handle clicks when the item was not selected


        if (!this.isSelected()) {
          // Unselect all other selected items,
          // if the shift key was not pressed during the click event
          if (!(event.shiftKey || event.keyCode === 13)) {
            this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
          }

          this.$store.commit(SELECT_BROWSER_ITEM, this.item);
          return;
        } // If more than one item was selected and the user clicks again on the selected item,
        // he most probably wants to unselect all other items.


        if (this.$store.state.selectedItems.length > 1) {
          this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
          this.$store.commit(SELECT_BROWSER_ITEM, this.item);
        }
      }
    }
  };
  var _hoisted_1$g = ["data-type"];
  var _hoisted_2$e = {
    scope: "row",
    class: "name"
  };
  var _hoisted_3$8 = {
    class: "size"
  };
  var _hoisted_4$6 = {
    class: "dimension"
  };
  var _hoisted_5$6 = {
    class: "created"
  };
  var _hoisted_6$4 = {
    class: "modified"
  };

  function render$g(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("tr", {
      class: normalizeClass(["media-browser-item", {
        selected: $options.selected
      }]),
      onDblclick: _cache[0] || (_cache[0] = withModifiers(function ($event) {
        return $options.onDblClick();
      }, ["stop", "prevent"])),
      onClick: _cache[1] || (_cache[1] = function () {
        return $options.onClick && $options.onClick.apply($options, arguments);
      })
    }, [createBaseVNode("td", {
      class: "type",
      "data-type": $props.item.extension
    }, null, 8
    /* PROPS */
    , _hoisted_1$g), createBaseVNode("th", _hoisted_2$e, toDisplayString($props.item.name), 1
    /* TEXT */
    ), createBaseVNode("td", _hoisted_3$8, toDisplayString($options.size), 1
    /* TEXT */
    ), createBaseVNode("td", _hoisted_4$6, toDisplayString($options.dimension), 1
    /* TEXT */
    ), createBaseVNode("td", _hoisted_5$6, toDisplayString($props.item.create_date_formatted), 1
    /* TEXT */
    ), createBaseVNode("td", _hoisted_6$4, toDisplayString($props.item.modified_date_formatted), 1
    /* TEXT */
    )], 34
    /* CLASS, HYDRATE_EVENTS */
    );
  }

  script$g.render = render$g;
  script$g.__file = "administrator/components/com_media/resources/scripts/components/browser/items/row.vue";
  var script$f = {
    name: 'MediaModal',
    props: {
      /* Whether or not the close button in the header should be shown */
      showClose: {
        type: Boolean,
        default: true
      },

      /* The size of the modal */
      // eslint-disable-next-line vue/require-default-prop
      size: {
        type: String
      },
      labelElement: {
        type: String,
        required: true
      }
    },
    emits: ['close'],
    computed: {
      /* Get the modal css class */
      modalClass: function modalClass() {
        return {
          'modal-sm': this.size === 'sm'
        };
      }
    },
    mounted: function mounted() {
      // Listen to keydown events on the document
      document.addEventListener('keydown', this.onKeyDown);
    },
    beforeUnmount: function beforeUnmount() {
      // Remove the keydown event listener
      document.removeEventListener('keydown', this.onKeyDown);
    },
    methods: {
      /* Close the modal instance */
      close: function close() {
        this.$emit('close');
      },

      /* Handle keydown events */
      onKeyDown: function onKeyDown(event) {
        if (event.keyCode === 27) {
          this.close();
        }
      }
    }
  };
  var _hoisted_1$f = ["aria-labelledby"];
  var _hoisted_2$d = {
    class: "modal-content"
  };
  var _hoisted_3$7 = {
    class: "modal-header"
  };
  var _hoisted_4$5 = {
    class: "modal-body"
  };
  var _hoisted_5$5 = {
    class: "modal-footer"
  };

  function render$f(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_tab_lock = resolveComponent("tab-lock");

    return openBlock(), createElementBlock("div", {
      class: "media-modal-backdrop",
      onClick: _cache[2] || (_cache[2] = function ($event) {
        return $options.close();
      })
    }, [createBaseVNode("div", {
      class: "modal",
      style: {
        "display": "flex"
      },
      onClick: _cache[1] || (_cache[1] = withModifiers(function () {}, ["stop"]))
    }, [createVNode(_component_tab_lock, null, {
      default: withCtx(function () {
        return [createBaseVNode("div", {
          class: normalizeClass(["modal-dialog", $options.modalClass]),
          role: "dialog",
          "aria-labelledby": $props.labelElement
        }, [createBaseVNode("div", _hoisted_2$d, [createBaseVNode("div", _hoisted_3$7, [renderSlot(_ctx.$slots, "header"), renderSlot(_ctx.$slots, "backdrop-close"), $props.showClose ? (openBlock(), createElementBlock("button", {
          key: 0,
          type: "button",
          class: "btn-close",
          "aria-label": "Close",
          onClick: _cache[0] || (_cache[0] = function ($event) {
            return $options.close();
          })
        })) : createCommentVNode("v-if", true)]), createBaseVNode("div", _hoisted_4$5, [renderSlot(_ctx.$slots, "body")]), createBaseVNode("div", _hoisted_5$5, [renderSlot(_ctx.$slots, "footer")])])], 10
        /* CLASS, PROPS */
        , _hoisted_1$f)];
      }),
      _: 3
      /* FORWARDED */

    })])]);
  }

  script$f.render = render$f;
  script$f.__file = "administrator/components/com_media/resources/scripts/components/modals/modal.vue";
  var script$e = {
    name: 'MediaCreateFolderModal',
    data: function data() {
      return {
        folder: ''
      };
    },
    methods: {
      /* Check if the the form is valid */
      isValid: function isValid() {
        return this.folder;
      },

      /* Close the modal instance */
      close: function close() {
        this.reset();
        this.$store.commit(HIDE_CREATE_FOLDER_MODAL);
      },

      /* Save the form and create the folder */
      save: function save() {
        // Check if the form is valid
        if (!this.isValid()) {
          // @todo show an error message to user for insert a folder name
          // @todo mark the field as invalid
          return;
        } // Create the directory


        this.$store.dispatch('createDirectory', {
          name: this.folder,
          parent: this.$store.state.selectedDirectory
        });
        this.reset();
      },

      /* Reset the form */
      reset: function reset() {
        this.folder = '';
      }
    }
  };
  var _hoisted_1$e = {
    id: "createFolderTitle",
    class: "modal-title"
  };
  var _hoisted_2$c = {
    class: "p-3"
  };
  var _hoisted_3$6 = {
    class: "form-group"
  };
  var _hoisted_4$4 = {
    for: "folder"
  };
  var _hoisted_5$4 = ["disabled"];

  function render$e(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_modal = resolveComponent("media-modal");

    return _ctx.$store.state.showCreateFolderModal ? (openBlock(), createBlock(_component_media_modal, {
      key: 0,
      size: 'md',
      "label-element": "createFolderTitle",
      onClose: _cache[5] || (_cache[5] = function ($event) {
        return $options.close();
      })
    }, {
      header: withCtx(function () {
        return [createBaseVNode("h3", _hoisted_1$e, toDisplayString(_ctx.translate('COM_MEDIA_CREATE_NEW_FOLDER')), 1
        /* TEXT */
        )];
      }),
      body: withCtx(function () {
        return [createBaseVNode("div", _hoisted_2$c, [createBaseVNode("form", {
          class: "form",
          novalidate: "",
          onSubmit: _cache[2] || (_cache[2] = withModifiers(function () {
            return $options.save && $options.save.apply($options, arguments);
          }, ["prevent"]))
        }, [createBaseVNode("div", _hoisted_3$6, [createBaseVNode("label", _hoisted_4$4, toDisplayString(_ctx.translate('COM_MEDIA_FOLDER_NAME')), 1
        /* TEXT */
        ), withDirectives(createBaseVNode("input", {
          id: "folder",
          "onUpdate:modelValue": _cache[0] || (_cache[0] = function ($event) {
            return $data.folder = $event;
          }),
          class: "form-control",
          type: "text",
          required: "",
          autocomplete: "off",
          onInput: _cache[1] || (_cache[1] = function ($event) {
            return $data.folder = $event.target.value;
          })
        }, null, 544
        /* HYDRATE_EVENTS, NEED_PATCH */
        ), [[vModelText, $data.folder, void 0, {
          trim: true
        }]])])], 32
        /* HYDRATE_EVENTS */
        )])];
      }),
      footer: withCtx(function () {
        return [createBaseVNode("div", null, [createBaseVNode("button", {
          class: "btn btn-secondary",
          onClick: _cache[3] || (_cache[3] = function ($event) {
            return $options.close();
          })
        }, toDisplayString(_ctx.translate('JCANCEL')), 1
        /* TEXT */
        ), createBaseVNode("button", {
          class: "btn btn-success",
          disabled: !$options.isValid(),
          onClick: _cache[4] || (_cache[4] = function ($event) {
            return $options.save();
          })
        }, toDisplayString(_ctx.translate('JACTION_CREATE')), 9
        /* TEXT, PROPS */
        , _hoisted_5$4)])];
      }),
      _: 1
      /* STABLE */

    })) : createCommentVNode("v-if", true);
  }

  script$e.render = render$e;
  script$e.__file = "administrator/components/com_media/resources/scripts/components/modals/create-folder-modal.vue";
  var script$d = {
    name: 'MediaPreviewModal',
    computed: {
      /* Get the item to show in the modal */
      item: function item() {
        // Use the currently selected directory as a fallback
        return this.$store.state.previewItem;
      },

      /* Get the hashed URL */
      getHashedURL: function getHashedURL() {
        if (this.item.adapter.startsWith('local-')) {
          return this.item.url + "?" + api.mediaVersion;
        }

        return this.item.url;
      }
    },
    methods: {
      /* Close the modal */
      close: function close() {
        this.$store.commit(HIDE_PREVIEW_MODAL);
      },
      isImage: function isImage() {
        return this.item.mime_type.indexOf('image/') === 0;
      },
      isVideo: function isVideo() {
        return this.item.mime_type.indexOf('video/') === 0;
      },
      isAudio: function isAudio() {
        return this.item.mime_type.indexOf('audio/') === 0;
      },
      isDoc: function isDoc() {
        return this.item.mime_type.indexOf('application/') === 0;
      }
    }
  };
  var _hoisted_1$d = {
    id: "previewTitle",
    class: "modal-title text-light"
  };
  var _hoisted_2$b = {
    class: "image-background"
  };
  var _hoisted_3$5 = ["src"];
  var _hoisted_4$3 = {
    key: 1,
    controls: ""
  };
  var _hoisted_5$3 = ["src", "type"];
  var _hoisted_6$3 = ["type", "data"];
  var _hoisted_7$2 = ["src", "type"];

  var _hoisted_8$2 = /*#__PURE__*/createBaseVNode("span", {
    class: "icon-times"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_9$2 = [_hoisted_8$2];

  function render$d(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_modal = resolveComponent("media-modal");

    return _ctx.$store.state.showPreviewModal && $options.item ? (openBlock(), createBlock(_component_media_modal, {
      key: 0,
      size: 'md',
      class: "media-preview-modal",
      "label-element": "previewTitle",
      "show-close": false,
      onClose: _cache[1] || (_cache[1] = function ($event) {
        return $options.close();
      })
    }, {
      header: withCtx(function () {
        return [createBaseVNode("h3", _hoisted_1$d, toDisplayString($options.item.name), 1
        /* TEXT */
        )];
      }),
      body: withCtx(function () {
        return [createBaseVNode("div", _hoisted_2$b, [$options.isAudio() ? (openBlock(), createElementBlock("audio", {
          key: 0,
          controls: "",
          src: $options.item.url
        }, null, 8
        /* PROPS */
        , _hoisted_3$5)) : createCommentVNode("v-if", true), $options.isVideo() ? (openBlock(), createElementBlock("video", _hoisted_4$3, [createBaseVNode("source", {
          src: $options.item.url,
          type: $options.item.mime_type
        }, null, 8
        /* PROPS */
        , _hoisted_5$3)])) : createCommentVNode("v-if", true), $options.isDoc() ? (openBlock(), createElementBlock("object", {
          key: 2,
          type: $options.item.mime_type,
          data: $options.item.url,
          width: "800",
          height: "600"
        }, null, 8
        /* PROPS */
        , _hoisted_6$3)) : createCommentVNode("v-if", true), $options.isImage() ? (openBlock(), createElementBlock("img", {
          key: 3,
          src: $options.getHashedURL,
          type: $options.item.mime_type
        }, null, 8
        /* PROPS */
        , _hoisted_7$2)) : createCommentVNode("v-if", true)])];
      }),
      "backdrop-close": withCtx(function () {
        return [createBaseVNode("button", {
          type: "button",
          class: "media-preview-close",
          onClick: _cache[0] || (_cache[0] = function ($event) {
            return $options.close();
          })
        }, _hoisted_9$2)];
      }),
      _: 1
      /* STABLE */

    })) : createCommentVNode("v-if", true);
  }

  script$d.render = render$d;
  script$d.__file = "administrator/components/com_media/resources/scripts/components/modals/preview-modal.vue";
  var script$c = {
    name: 'MediaRenameModal',
    computed: {
      item: function item() {
        return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
      },
      name: function name() {
        return this.item.name.replace("." + this.item.extension, '');
      },
      extension: function extension() {
        return this.item.extension;
      }
    },
    updated: function updated() {
      var _this16 = this;

      this.$nextTick(function () {
        return _this16.$refs.nameField ? _this16.$refs.nameField.focus() : null;
      });
    },
    methods: {
      /* Check if the form is valid */
      isValid: function isValid() {
        return this.item.name.length > 0;
      },

      /* Close the modal instance */
      close: function close() {
        this.$store.commit(HIDE_RENAME_MODAL);
      },

      /* Save the form and create the folder */
      save: function save() {
        // Check if the form is valid
        if (!this.isValid()) {
          // @todo mark the field as invalid
          return;
        }

        var newName = this.$refs.nameField.value;

        if (this.extension.length) {
          newName += "." + this.item.extension;
        }

        var newPath = this.item.directory;

        if (newPath.substr(-1) !== '/') {
          newPath += '/';
        } // Rename the item


        this.$store.dispatch('renameItem', {
          item: this.item,
          newPath: newPath + newName,
          newName: newName
        });
      }
    }
  };
  var _hoisted_1$c = {
    id: "renameTitle",
    class: "modal-title"
  };
  var _hoisted_2$a = {
    class: "form-group p-3"
  };
  var _hoisted_3$4 = {
    for: "name"
  };
  var _hoisted_4$2 = ["placeholder", "value"];
  var _hoisted_5$2 = {
    key: 0,
    class: "input-group-text"
  };
  var _hoisted_6$2 = ["disabled"];

  function render$c(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_modal = resolveComponent("media-modal");

    return _ctx.$store.state.showRenameModal ? (openBlock(), createBlock(_component_media_modal, {
      key: 0,
      size: 'sm',
      "show-close": false,
      "label-element": "renameTitle",
      onClose: _cache[5] || (_cache[5] = function ($event) {
        return $options.close();
      })
    }, {
      header: withCtx(function () {
        return [createBaseVNode("h3", _hoisted_1$c, toDisplayString(_ctx.translate('COM_MEDIA_RENAME')), 1
        /* TEXT */
        )];
      }),
      body: withCtx(function () {
        return [createBaseVNode("div", null, [createBaseVNode("form", {
          class: "form",
          novalidate: "",
          onSubmit: _cache[0] || (_cache[0] = withModifiers(function () {
            return $options.save && $options.save.apply($options, arguments);
          }, ["prevent"]))
        }, [createBaseVNode("div", _hoisted_2$a, [createBaseVNode("label", _hoisted_3$4, toDisplayString(_ctx.translate('COM_MEDIA_NAME')), 1
        /* TEXT */
        ), createBaseVNode("div", {
          class: normalizeClass({
            'input-group': $options.extension.length
          })
        }, [createBaseVNode("input", {
          id: "name",
          ref: "nameField",
          class: "form-control",
          type: "text",
          placeholder: _ctx.translate('COM_MEDIA_NAME'),
          value: $options.name,
          required: "",
          autocomplete: "off"
        }, null, 8
        /* PROPS */
        , _hoisted_4$2), $options.extension.length ? (openBlock(), createElementBlock("span", _hoisted_5$2, toDisplayString($options.extension), 1
        /* TEXT */
        )) : createCommentVNode("v-if", true)], 2
        /* CLASS */
        )])], 32
        /* HYDRATE_EVENTS */
        )])];
      }),
      footer: withCtx(function () {
        return [createBaseVNode("div", null, [createBaseVNode("button", {
          type: "button",
          class: "btn btn-secondary",
          onClick: _cache[1] || (_cache[1] = function ($event) {
            return $options.close();
          }),
          onKeyup: _cache[2] || (_cache[2] = withKeys(function ($event) {
            return $options.close();
          }, ["enter"]))
        }, toDisplayString(_ctx.translate('JCANCEL')), 33
        /* TEXT, HYDRATE_EVENTS */
        ), createBaseVNode("button", {
          type: "button",
          class: "btn btn-success",
          disabled: !$options.isValid(),
          onClick: _cache[3] || (_cache[3] = function ($event) {
            return $options.save();
          }),
          onKeyup: _cache[4] || (_cache[4] = withKeys(function ($event) {
            return $options.save();
          }, ["enter"]))
        }, toDisplayString(_ctx.translate('JAPPLY')), 41
        /* TEXT, PROPS, HYDRATE_EVENTS */
        , _hoisted_6$2)])];
      }),
      _: 1
      /* STABLE */

    })) : createCommentVNode("v-if", true);
  }

  script$c.render = render$c;
  script$c.__file = "administrator/components/com_media/resources/scripts/components/modals/rename-modal.vue";
  var script$b = {
    name: 'MediaShareModal',
    computed: {
      item: function item() {
        return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
      },
      url: function url() {
        return this.$store.state.previewItem && Object.prototype.hasOwnProperty.call(this.$store.state.previewItem, 'url') ? this.$store.state.previewItem.url : null;
      }
    },
    methods: {
      /* Close the modal instance and reset the form */
      close: function close() {
        this.$store.commit(HIDE_SHARE_MODAL);
        this.$store.commit(LOAD_FULL_CONTENTS_SUCCESS, null);
      },
      // Generate the url from backend
      generateUrl: function generateUrl() {
        this.$store.dispatch('getFullContents', this.item);
      },
      // Copy to clipboard
      copyToClipboard: function copyToClipboard() {
        this.$refs.urlText.focus();
        this.$refs.urlText.select();

        try {
          document.execCommand('copy');
        } catch (err) {
          // @todo Error handling in joomla way
          // eslint-disable-next-line no-undef
          alert(translate('COM_MEDIA_SHARE_COPY_FAILED_ERROR'));
        }
      }
    }
  };
  var _hoisted_1$b = {
    id: "shareTitle",
    class: "modal-title"
  };
  var _hoisted_2$9 = {
    class: "p-3"
  };
  var _hoisted_3$3 = {
    class: "desc"
  };
  var _hoisted_4$1 = {
    key: 0,
    class: "control"
  };
  var _hoisted_5$1 = {
    key: 1,
    class: "control"
  };
  var _hoisted_6$1 = {
    class: "input-group"
  };
  var _hoisted_7$1 = ["title"];

  var _hoisted_8$1 = /*#__PURE__*/createBaseVNode("span", {
    class: "icon-clipboard",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_9$1 = [_hoisted_8$1];

  function render$b(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_modal = resolveComponent("media-modal");

    return _ctx.$store.state.showShareModal ? (openBlock(), createBlock(_component_media_modal, {
      key: 0,
      size: 'md',
      "show-close": false,
      "label-element": "shareTitle",
      onClose: _cache[4] || (_cache[4] = function ($event) {
        return $options.close();
      })
    }, {
      header: withCtx(function () {
        return [createBaseVNode("h3", _hoisted_1$b, toDisplayString(_ctx.translate('COM_MEDIA_SHARE')), 1
        /* TEXT */
        )];
      }),
      body: withCtx(function () {
        return [createBaseVNode("div", _hoisted_2$9, [createBaseVNode("div", _hoisted_3$3, [createTextVNode(toDisplayString(_ctx.translate('COM_MEDIA_SHARE_DESC')) + " ", 1
        /* TEXT */
        ), !$options.url ? (openBlock(), createElementBlock("div", _hoisted_4$1, [createBaseVNode("button", {
          class: "btn btn-success w-100",
          type: "button",
          onClick: _cache[0] || (_cache[0] = function () {
            return $options.generateUrl && $options.generateUrl.apply($options, arguments);
          })
        }, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_SHARE')), 1
        /* TEXT */
        )])) : (openBlock(), createElementBlock("div", _hoisted_5$1, [createBaseVNode("span", _hoisted_6$1, [withDirectives(createBaseVNode("input", {
          id: "url",
          ref: "urlText",
          "onUpdate:modelValue": _cache[1] || (_cache[1] = function ($event) {
            return $options.url = $event;
          }),
          readonly: "",
          type: "url",
          class: "form-control input-xxlarge",
          placeholder: "URL",
          autocomplete: "off"
        }, null, 512
        /* NEED_PATCH */
        ), [[vModelText, $options.url]]), createBaseVNode("button", {
          class: "btn btn-secondary",
          type: "button",
          title: _ctx.translate('COM_MEDIA_SHARE_COPY'),
          onClick: _cache[2] || (_cache[2] = function () {
            return $options.copyToClipboard && $options.copyToClipboard.apply($options, arguments);
          })
        }, _hoisted_9$1, 8
        /* PROPS */
        , _hoisted_7$1)])]))])])];
      }),
      footer: withCtx(function () {
        return [createBaseVNode("div", null, [createBaseVNode("button", {
          class: "btn btn-secondary",
          onClick: _cache[3] || (_cache[3] = function ($event) {
            return $options.close();
          })
        }, toDisplayString(_ctx.translate('JCANCEL')), 1
        /* TEXT */
        )])];
      }),
      _: 1
      /* STABLE */

    })) : createCommentVNode("v-if", true);
  }

  script$b.render = render$b;
  script$b.__file = "administrator/components/com_media/resources/scripts/components/modals/share-modal.vue";
  var script$a = {
    name: 'MediaShareModal',
    computed: {
      item: function item() {
        return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
      }
    },
    methods: {
      /* Delete Item */
      deleteItem: function deleteItem() {
        this.$store.dispatch('deleteSelectedItems');
        this.$store.commit(HIDE_CONFIRM_DELETE_MODAL);
      },

      /* Close the modal instance */
      close: function close() {
        this.$store.commit(HIDE_CONFIRM_DELETE_MODAL);
      }
    }
  };
  var _hoisted_1$a = {
    id: "confirmDeleteTitle",
    class: "modal-title"
  };
  var _hoisted_2$8 = {
    class: "p-3"
  };
  var _hoisted_3$2 = {
    class: "desc"
  };

  function render$a(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_modal = resolveComponent("media-modal");

    return _ctx.$store.state.showConfirmDeleteModal ? (openBlock(), createBlock(_component_media_modal, {
      key: 0,
      size: 'md',
      "show-close": false,
      "label-element": "confirmDeleteTitle",
      onClose: _cache[2] || (_cache[2] = function ($event) {
        return $options.close();
      })
    }, {
      header: withCtx(function () {
        return [createBaseVNode("h3", _hoisted_1$a, toDisplayString(_ctx.translate('COM_MEDIA_CONFIRM_DELETE_MODAL_HEADING')), 1
        /* TEXT */
        )];
      }),
      body: withCtx(function () {
        return [createBaseVNode("div", _hoisted_2$8, [createBaseVNode("div", _hoisted_3$2, toDisplayString(_ctx.translate('JGLOBAL_CONFIRM_DELETE')), 1
        /* TEXT */
        )])];
      }),
      footer: withCtx(function () {
        return [createBaseVNode("div", null, [createBaseVNode("button", {
          class: "btn btn-success",
          onClick: _cache[0] || (_cache[0] = function ($event) {
            return $options.close();
          })
        }, toDisplayString(_ctx.translate('JCANCEL')), 1
        /* TEXT */
        ), createBaseVNode("button", {
          id: "media-delete-item",
          class: "btn btn-danger",
          onClick: _cache[1] || (_cache[1] = function ($event) {
            return $options.deleteItem();
          })
        }, toDisplayString(_ctx.translate('COM_MEDIA_CONFIRM_DELETE_MODAL')), 1
        /* TEXT */
        )])];
      }),
      _: 1
      /* STABLE */

    })) : createCommentVNode("v-if", true);
  }

  script$a.render = render$a;
  script$a.__file = "administrator/components/com_media/resources/scripts/components/modals/confirm-delete-modal.vue";
  var script$9 = {
    name: 'MediaInfobar',
    computed: {
      /* Get the item to show in the infobar */
      item: function item() {
        // Check if there are selected items
        var selectedItems = this.$store.state.selectedItems; // If there is only one selected item, show that one.

        if (selectedItems.length === 1) {
          return selectedItems[0];
        } // If there are more selected items, use the last one


        if (selectedItems.length > 1) {
          return selectedItems.slice(-1)[0];
        } // Use the currently selected directory as a fallback


        return this.$store.getters.getSelectedDirectory;
      },

      /* Show/Hide the InfoBar */
      showInfoBar: function showInfoBar() {
        return this.$store.state.showInfoBar;
      }
    },
    methods: {
      hideInfoBar: function hideInfoBar() {
        this.$store.commit(HIDE_INFOBAR);
      }
    }
  };
  var _hoisted_1$9 = {
    key: 0,
    class: "media-infobar"
  };
  var _hoisted_2$7 = {
    key: 0,
    class: "text-center"
  };

  var _hoisted_3$1 = /*#__PURE__*/createBaseVNode("span", {
    class: "icon-file placeholder-icon"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_4 = {
    key: 1
  };
  var _hoisted_5 = {
    key: 0
  };
  var _hoisted_6 = {
    key: 1
  };
  var _hoisted_7 = {
    key: 2
  };
  var _hoisted_8 = {
    key: 3
  };
  var _hoisted_9 = {
    key: 4
  };
  var _hoisted_10 = {
    key: 5
  };
  var _hoisted_11 = {
    key: 6
  };

  function render$9(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createBlock(Transition, {
      name: "infobar"
    }, {
      default: withCtx(function () {
        return [$options.showInfoBar && $options.item ? (openBlock(), createElementBlock("div", _hoisted_1$9, [createBaseVNode("span", {
          class: "infobar-close",
          onClick: _cache[0] || (_cache[0] = function ($event) {
            return $options.hideInfoBar();
          })
        }, "×"), createBaseVNode("h2", null, toDisplayString($options.item.name), 1
        /* TEXT */
        ), $options.item.path === '/' ? (openBlock(), createElementBlock("div", _hoisted_2$7, [_hoisted_3$1, createTextVNode(" Select file or folder to view its details. ")])) : (openBlock(), createElementBlock("dl", _hoisted_4, [createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_FOLDER')), 1
        /* TEXT */
        ), createBaseVNode("dd", null, toDisplayString($options.item.directory), 1
        /* TEXT */
        ), createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_TYPE')), 1
        /* TEXT */
        ), $options.item.type === 'file' ? (openBlock(), createElementBlock("dd", _hoisted_5, toDisplayString(_ctx.translate('COM_MEDIA_FILE')), 1
        /* TEXT */
        )) : $options.item.type === 'dir' ? (openBlock(), createElementBlock("dd", _hoisted_6, toDisplayString(_ctx.translate('COM_MEDIA_FOLDER')), 1
        /* TEXT */
        )) : (openBlock(), createElementBlock("dd", _hoisted_7, " - ")), createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_CREATED')), 1
        /* TEXT */
        ), createBaseVNode("dd", null, toDisplayString($options.item.create_date_formatted), 1
        /* TEXT */
        ), createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_MODIFIED')), 1
        /* TEXT */
        ), createBaseVNode("dd", null, toDisplayString($options.item.modified_date_formatted), 1
        /* TEXT */
        ), createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DIMENSION')), 1
        /* TEXT */
        ), $options.item.width || $options.item.height ? (openBlock(), createElementBlock("dd", _hoisted_8, toDisplayString($options.item.width) + "px * " + toDisplayString($options.item.height) + "px ", 1
        /* TEXT */
        )) : (openBlock(), createElementBlock("dd", _hoisted_9, " - ")), createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_SIZE')), 1
        /* TEXT */
        ), $options.item.size ? (openBlock(), createElementBlock("dd", _hoisted_10, toDisplayString(($options.item.size / 1024).toFixed(2)) + " KB ", 1
        /* TEXT */
        )) : (openBlock(), createElementBlock("dd", _hoisted_11, " - ")), createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_MIME_TYPE')), 1
        /* TEXT */
        ), createBaseVNode("dd", null, toDisplayString($options.item.mime_type), 1
        /* TEXT */
        ), createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_EXTENSION')), 1
        /* TEXT */
        ), createBaseVNode("dd", null, toDisplayString($options.item.extension || '-'), 1
        /* TEXT */
        )]))])) : createCommentVNode("v-if", true)];
      }),
      _: 1
      /* STABLE */

    });
  }

  script$9.render = render$9;
  script$9.__file = "administrator/components/com_media/resources/scripts/components/infobar/infobar.vue";
  var script$8 = {
    name: 'MediaUpload',
    props: {
      // eslint-disable-next-line vue/require-default-prop
      accept: {
        type: String
      },
      // eslint-disable-next-line vue/require-prop-types
      extensions: {
        default: function _default() {
          return [];
        }
      },
      name: {
        type: String,
        default: 'file'
      },
      multiple: {
        type: Boolean,
        default: true
      }
    },
    created: function created() {
      var _this17 = this;

      // Listen to the toolbar upload click event
      MediaManager.Event.listen('onClickUpload', function () {
        return _this17.chooseFiles();
      });
    },
    methods: {
      /* Open the choose-file dialog */
      chooseFiles: function chooseFiles() {
        this.$refs.fileInput.click();
      },

      /* Upload files */
      upload: function upload(e) {
        var _this18 = this;

        e.preventDefault();
        var files = e.target.files; // Loop through array of files and upload each file

        Array.from(files).forEach(function (file) {
          // Create a new file reader instance
          var reader = new FileReader(); // Add the on load callback

          reader.onload = function (progressEvent) {
            var result = progressEvent.target.result;
            var splitIndex = result.indexOf('base64') + 7;
            var content = result.slice(splitIndex, result.length); // Upload the file

            _this18.$store.dispatch('uploadFile', {
              name: file.name,
              parent: _this18.$store.state.selectedDirectory,
              content: content
            });
          };

          reader.readAsDataURL(file);
        });
      }
    }
  };
  var _hoisted_1$8 = ["name", "multiple", "accept"];

  function render$8(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("input", {
      ref: "fileInput",
      type: "file",
      class: "hidden",
      name: $props.name,
      multiple: $props.multiple,
      accept: $props.accept,
      onChange: _cache[0] || (_cache[0] = function () {
        return $options.upload && $options.upload.apply($options, arguments);
      })
    }, null, 40
    /* PROPS, HYDRATE_EVENTS */
    , _hoisted_1$8);
  }

  script$8.render = render$8;
  script$8.__file = "administrator/components/com_media/resources/scripts/components/upload/upload.vue";
  /**
   * Translate plugin
   */

  var Translate = {
    // Translate from Joomla text
    translate: function translate(key) {
      return Joomla.Text._(key, key);
    },
    sprintf: function sprintf(string) {
      for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        args[_key - 1] = arguments[_key];
      } // eslint-disable-next-line no-param-reassign


      string = Translate.translate(string);
      var i = 0;
      return string.replace(/%((%)|s|d)/g, function (m) {
        var val = args[i];

        if (m === '%d') {
          val = parseFloat(val); // eslint-disable-next-line no-restricted-globals

          if (isNaN(val)) {
            val = 0;
          }
        } // eslint-disable-next-line no-plusplus


        i++;
        return val;
      });
    },
    install: function install(Vue) {
      return Vue.mixin({
        methods: {
          translate: function translate(key) {
            return Translate.translate(key);
          },
          sprintf: function sprintf(key) {
            for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
              args[_key2 - 1] = arguments[_key2];
            }

            return Translate.sprintf(key, args);
          }
        }
      });
    }
  };

  function getDevtoolsGlobalHook() {
    return getTarget().__VUE_DEVTOOLS_GLOBAL_HOOK__;
  }

  function getTarget() {
    // @ts-ignore
    return typeof navigator !== 'undefined' ? window : typeof commonjsGlobal !== 'undefined' ? commonjsGlobal : {};
  }

  var HOOK_SETUP = 'devtools-plugin:setup';

  function setupDevtoolsPlugin(pluginDescriptor, setupFn) {
    var hook = getDevtoolsGlobalHook();

    if (hook) {
      hook.emit(HOOK_SETUP, pluginDescriptor, setupFn);
    } else {
      var target = getTarget();
      var list = target.__VUE_DEVTOOLS_PLUGINS__ = target.__VUE_DEVTOOLS_PLUGINS__ || [];
      list.push({
        pluginDescriptor: pluginDescriptor,
        setupFn: setupFn
      });
    }
  }
  /*!
   * vuex v4.0.2
   * (c) 2021 Evan You
   * @license MIT
   */


  var storeKey = 'store';
  /**
   * forEach for object
   */

  function forEachValue(obj, fn) {
    Object.keys(obj).forEach(function (key) {
      return fn(obj[key], key);
    });
  }

  function isObject(obj) {
    return obj !== null && typeof obj === 'object';
  }

  function isPromise(val) {
    return val && typeof val.then === 'function';
  }

  function partial(fn, arg) {
    return function () {
      return fn(arg);
    };
  }

  function genericSubscribe(fn, subs, options) {
    if (subs.indexOf(fn) < 0) {
      options && options.prepend ? subs.unshift(fn) : subs.push(fn);
    }

    return function () {
      var i = subs.indexOf(fn);

      if (i > -1) {
        subs.splice(i, 1);
      }
    };
  }

  function resetStore(store, hot) {
    store._actions = Object.create(null);
    store._mutations = Object.create(null);
    store._wrappedGetters = Object.create(null);
    store._modulesNamespaceMap = Object.create(null);
    var state = store.state; // init all modules

    installModule(store, state, [], store._modules.root, true); // reset state

    resetStoreState(store, state, hot);
  }

  function resetStoreState(store, state, hot) {
    var oldState = store._state; // bind store public getters

    store.getters = {}; // reset local getters cache

    store._makeLocalGettersCache = Object.create(null);
    var wrappedGetters = store._wrappedGetters;
    var computedObj = {};
    forEachValue(wrappedGetters, function (fn, key) {
      // use computed to leverage its lazy-caching mechanism
      // direct inline function use will lead to closure preserving oldState.
      // using partial to return function with only arguments preserved in closure environment.
      computedObj[key] = partial(fn, store);
      Object.defineProperty(store.getters, key, {
        // TODO: use `computed` when it's possible. at the moment we can't due to
        // https://github.com/vuejs/vuex/pull/1883
        get: function get() {
          return computedObj[key]();
        },
        enumerable: true // for local getters

      });
    });
    store._state = reactive({
      data: state
    }); // enable strict mode for new state

    if (store.strict) {
      enableStrictMode(store);
    }

    if (oldState) {
      if (hot) {
        // dispatch changes in all subscribed watchers
        // to force getter re-evaluation for hot reloading.
        store._withCommit(function () {
          oldState.data = null;
        });
      }
    }
  }

  function installModule(store, rootState, path, module, hot) {
    var isRoot = !path.length;

    var namespace = store._modules.getNamespace(path); // register in namespace map


    if (module.namespaced) {
      if (store._modulesNamespaceMap[namespace] && "production" !== 'production') {
        console.error("[vuex] duplicate namespace " + namespace + " for the namespaced module " + path.join('/'));
      }

      store._modulesNamespaceMap[namespace] = module;
    } // set state


    if (!isRoot && !hot) {
      var parentState = getNestedState(rootState, path.slice(0, -1));
      var moduleName = path[path.length - 1];

      store._withCommit(function () {
        parentState[moduleName] = module.state;
      });
    }

    var local = module.context = makeLocalContext(store, namespace, path);
    module.forEachMutation(function (mutation, key) {
      var namespacedType = namespace + key;
      registerMutation(store, namespacedType, mutation, local);
    });
    module.forEachAction(function (action, key) {
      var type = action.root ? key : namespace + key;
      var handler = action.handler || action;
      registerAction(store, type, handler, local);
    });
    module.forEachGetter(function (getter, key) {
      var namespacedType = namespace + key;
      registerGetter(store, namespacedType, getter, local);
    });
    module.forEachChild(function (child, key) {
      installModule(store, rootState, path.concat(key), child, hot);
    });
  }
  /**
   * make localized dispatch, commit, getters and state
   * if there is no namespace, just use root ones
   */


  function makeLocalContext(store, namespace, path) {
    var noNamespace = namespace === '';
    var local = {
      dispatch: noNamespace ? store.dispatch : function (_type, _payload, _options) {
        var args = unifyObjectStyle(_type, _payload, _options);
        var payload = args.payload;
        var options = args.options;
        var type = args.type;

        if (!options || !options.root) {
          type = namespace + type;
        }

        return store.dispatch(type, payload);
      },
      commit: noNamespace ? store.commit : function (_type, _payload, _options) {
        var args = unifyObjectStyle(_type, _payload, _options);
        var payload = args.payload;
        var options = args.options;
        var type = args.type;

        if (!options || !options.root) {
          type = namespace + type;
        }

        store.commit(type, payload, options);
      }
    }; // getters and state object must be gotten lazily
    // because they will be changed by state update

    Object.defineProperties(local, {
      getters: {
        get: noNamespace ? function () {
          return store.getters;
        } : function () {
          return makeLocalGetters(store, namespace);
        }
      },
      state: {
        get: function get() {
          return getNestedState(store.state, path);
        }
      }
    });
    return local;
  }

  function makeLocalGetters(store, namespace) {
    if (!store._makeLocalGettersCache[namespace]) {
      var gettersProxy = {};
      var splitPos = namespace.length;
      Object.keys(store.getters).forEach(function (type) {
        // skip if the target getter is not match this namespace
        if (type.slice(0, splitPos) !== namespace) {
          return;
        } // extract local getter type


        var localType = type.slice(splitPos); // Add a port to the getters proxy.
        // Define as getter property because
        // we do not want to evaluate the getters in this time.

        Object.defineProperty(gettersProxy, localType, {
          get: function get() {
            return store.getters[type];
          },
          enumerable: true
        });
      });
      store._makeLocalGettersCache[namespace] = gettersProxy;
    }

    return store._makeLocalGettersCache[namespace];
  }

  function registerMutation(store, type, handler, local) {
    var entry = store._mutations[type] || (store._mutations[type] = []);
    entry.push(function wrappedMutationHandler(payload) {
      handler.call(store, local.state, payload);
    });
  }

  function registerAction(store, type, handler, local) {
    var entry = store._actions[type] || (store._actions[type] = []);
    entry.push(function wrappedActionHandler(payload) {
      var res = handler.call(store, {
        dispatch: local.dispatch,
        commit: local.commit,
        getters: local.getters,
        state: local.state,
        rootGetters: store.getters,
        rootState: store.state
      }, payload);

      if (!isPromise(res)) {
        res = Promise.resolve(res);
      }

      if (store._devtoolHook) {
        return res.catch(function (err) {
          store._devtoolHook.emit('vuex:error', err);

          throw err;
        });
      } else {
        return res;
      }
    });
  }

  function registerGetter(store, type, rawGetter, local) {
    if (store._wrappedGetters[type]) {
      return;
    }

    store._wrappedGetters[type] = function wrappedGetter(store) {
      return rawGetter(local.state, // local state
      local.getters, // local getters
      store.state, // root state
      store.getters // root getters
      );
    };
  }

  function enableStrictMode(store) {
    watch(function () {
      return store._state.data;
    }, function () {}, {
      deep: true,
      flush: 'sync'
    });
  }

  function getNestedState(state, path) {
    return path.reduce(function (state, key) {
      return state[key];
    }, state);
  }

  function unifyObjectStyle(type, payload, options) {
    if (isObject(type) && type.type) {
      options = payload;
      payload = type;
      type = type.type;
    }

    return {
      type: type,
      payload: payload,
      options: options
    };
  }

  var LABEL_VUEX_BINDINGS = 'vuex bindings';
  var MUTATIONS_LAYER_ID = 'vuex:mutations';
  var ACTIONS_LAYER_ID = 'vuex:actions';
  var INSPECTOR_ID = 'vuex';
  var actionId = 0;

  function addDevtools(app, store) {
    setupDevtoolsPlugin({
      id: 'org.vuejs.vuex',
      app: app,
      label: 'Vuex',
      homepage: 'https://next.vuex.vuejs.org/',
      logo: 'https://vuejs.org/images/icons/favicon-96x96.png',
      packageName: 'vuex',
      componentStateTypes: [LABEL_VUEX_BINDINGS]
    }, function (api) {
      api.addTimelineLayer({
        id: MUTATIONS_LAYER_ID,
        label: 'Vuex Mutations',
        color: COLOR_LIME_500
      });
      api.addTimelineLayer({
        id: ACTIONS_LAYER_ID,
        label: 'Vuex Actions',
        color: COLOR_LIME_500
      });
      api.addInspector({
        id: INSPECTOR_ID,
        label: 'Vuex',
        icon: 'storage',
        treeFilterPlaceholder: 'Filter stores...'
      });
      api.on.getInspectorTree(function (payload) {
        if (payload.app === app && payload.inspectorId === INSPECTOR_ID) {
          if (payload.filter) {
            var nodes = [];
            flattenStoreForInspectorTree(nodes, store._modules.root, payload.filter, '');
            payload.rootNodes = nodes;
          } else {
            payload.rootNodes = [formatStoreForInspectorTree(store._modules.root, '')];
          }
        }
      });
      api.on.getInspectorState(function (payload) {
        if (payload.app === app && payload.inspectorId === INSPECTOR_ID) {
          var modulePath = payload.nodeId;
          makeLocalGetters(store, modulePath);
          payload.state = formatStoreForInspectorState(getStoreModule(store._modules, modulePath), modulePath === 'root' ? store.getters : store._makeLocalGettersCache, modulePath);
        }
      });
      api.on.editInspectorState(function (payload) {
        if (payload.app === app && payload.inspectorId === INSPECTOR_ID) {
          var modulePath = payload.nodeId;
          var path = payload.path;

          if (modulePath !== 'root') {
            path = modulePath.split('/').filter(Boolean).concat(path);
          }

          store._withCommit(function () {
            payload.set(store._state.data, path, payload.state.value);
          });
        }
      });
      store.subscribe(function (mutation, state) {
        var data = {};

        if (mutation.payload) {
          data.payload = mutation.payload;
        }

        data.state = state;
        api.notifyComponentUpdate();
        api.sendInspectorTree(INSPECTOR_ID);
        api.sendInspectorState(INSPECTOR_ID);
        api.addTimelineEvent({
          layerId: MUTATIONS_LAYER_ID,
          event: {
            time: Date.now(),
            title: mutation.type,
            data: data
          }
        });
      });
      store.subscribeAction({
        before: function before(action, state) {
          var data = {};

          if (action.payload) {
            data.payload = action.payload;
          }

          action._id = actionId++;
          action._time = Date.now();
          data.state = state;
          api.addTimelineEvent({
            layerId: ACTIONS_LAYER_ID,
            event: {
              time: action._time,
              title: action.type,
              groupId: action._id,
              subtitle: 'start',
              data: data
            }
          });
        },
        after: function after(action, state) {
          var data = {};

          var duration = Date.now() - action._time;

          data.duration = {
            _custom: {
              type: 'duration',
              display: duration + "ms",
              tooltip: 'Action duration',
              value: duration
            }
          };

          if (action.payload) {
            data.payload = action.payload;
          }

          data.state = state;
          api.addTimelineEvent({
            layerId: ACTIONS_LAYER_ID,
            event: {
              time: Date.now(),
              title: action.type,
              groupId: action._id,
              subtitle: 'end',
              data: data
            }
          });
        }
      });
    });
  } // extracted from tailwind palette


  var COLOR_LIME_500 = 0x84cc16;
  var COLOR_DARK = 0x666666;
  var COLOR_WHITE = 0xffffff;
  var TAG_NAMESPACED = {
    label: 'namespaced',
    textColor: COLOR_WHITE,
    backgroundColor: COLOR_DARK
  };
  /**
   * @param {string} path
   */

  function extractNameFromPath(path) {
    return path && path !== 'root' ? path.split('/').slice(-2, -1)[0] : 'Root';
  }
  /**
   * @param {*} module
   * @return {import('@vue/devtools-api').CustomInspectorNode}
   */


  function formatStoreForInspectorTree(module, path) {
    return {
      id: path || 'root',
      // all modules end with a `/`, we want the last segment only
      // cart/ -> cart
      // nested/cart/ -> cart
      label: extractNameFromPath(path),
      tags: module.namespaced ? [TAG_NAMESPACED] : [],
      children: Object.keys(module._children).map(function (moduleName) {
        return formatStoreForInspectorTree(module._children[moduleName], path + moduleName + '/');
      })
    };
  }
  /**
   * @param {import('@vue/devtools-api').CustomInspectorNode[]} result
   * @param {*} module
   * @param {string} filter
   * @param {string} path
   */


  function flattenStoreForInspectorTree(result, module, filter, path) {
    if (path.includes(filter)) {
      result.push({
        id: path || 'root',
        label: path.endsWith('/') ? path.slice(0, path.length - 1) : path || 'Root',
        tags: module.namespaced ? [TAG_NAMESPACED] : []
      });
    }

    Object.keys(module._children).forEach(function (moduleName) {
      flattenStoreForInspectorTree(result, module._children[moduleName], filter, path + moduleName + '/');
    });
  }
  /**
   * @param {*} module
   * @return {import('@vue/devtools-api').CustomInspectorState}
   */


  function formatStoreForInspectorState(module, getters, path) {
    getters = path === 'root' ? getters : getters[path];
    var gettersKeys = Object.keys(getters);
    var storeState = {
      state: Object.keys(module.state).map(function (key) {
        return {
          key: key,
          editable: true,
          value: module.state[key]
        };
      })
    };

    if (gettersKeys.length) {
      var tree = transformPathsToObjectTree(getters);
      storeState.getters = Object.keys(tree).map(function (key) {
        return {
          key: key.endsWith('/') ? extractNameFromPath(key) : key,
          editable: false,
          value: canThrow(function () {
            return tree[key];
          })
        };
      });
    }

    return storeState;
  }

  function transformPathsToObjectTree(getters) {
    var result = {};
    Object.keys(getters).forEach(function (key) {
      var path = key.split('/');

      if (path.length > 1) {
        var target = result;
        var leafKey = path.pop();
        path.forEach(function (p) {
          if (!target[p]) {
            target[p] = {
              _custom: {
                value: {},
                display: p,
                tooltip: 'Module',
                abstract: true
              }
            };
          }

          target = target[p]._custom.value;
        });
        target[leafKey] = canThrow(function () {
          return getters[key];
        });
      } else {
        result[key] = canThrow(function () {
          return getters[key];
        });
      }
    });
    return result;
  }

  function getStoreModule(moduleMap, path) {
    var names = path.split('/').filter(function (n) {
      return n;
    });
    return names.reduce(function (module, moduleName, i) {
      var child = module[moduleName];

      if (!child) {
        throw new Error("Missing module \"" + moduleName + "\" for path \"" + path + "\".");
      }

      return i === names.length - 1 ? child : child._children;
    }, path === 'root' ? moduleMap : moduleMap.root._children);
  }

  function canThrow(cb) {
    try {
      return cb();
    } catch (e) {
      return e;
    }
  } // Base data struct for store's module, package with some attribute and method


  var Module = function Module(rawModule, runtime) {
    this.runtime = runtime; // Store some children item

    this._children = Object.create(null); // Store the origin module object which passed by programmer

    this._rawModule = rawModule;
    var rawState = rawModule.state; // Store the origin module's state

    this.state = (typeof rawState === 'function' ? rawState() : rawState) || {};
  };

  var prototypeAccessors$1 = {
    namespaced: {
      configurable: true
    }
  };

  prototypeAccessors$1.namespaced.get = function () {
    return !!this._rawModule.namespaced;
  };

  Module.prototype.addChild = function addChild(key, module) {
    this._children[key] = module;
  };

  Module.prototype.removeChild = function removeChild(key) {
    delete this._children[key];
  };

  Module.prototype.getChild = function getChild(key) {
    return this._children[key];
  };

  Module.prototype.hasChild = function hasChild(key) {
    return key in this._children;
  };

  Module.prototype.update = function update(rawModule) {
    this._rawModule.namespaced = rawModule.namespaced;

    if (rawModule.actions) {
      this._rawModule.actions = rawModule.actions;
    }

    if (rawModule.mutations) {
      this._rawModule.mutations = rawModule.mutations;
    }

    if (rawModule.getters) {
      this._rawModule.getters = rawModule.getters;
    }
  };

  Module.prototype.forEachChild = function forEachChild(fn) {
    forEachValue(this._children, fn);
  };

  Module.prototype.forEachGetter = function forEachGetter(fn) {
    if (this._rawModule.getters) {
      forEachValue(this._rawModule.getters, fn);
    }
  };

  Module.prototype.forEachAction = function forEachAction(fn) {
    if (this._rawModule.actions) {
      forEachValue(this._rawModule.actions, fn);
    }
  };

  Module.prototype.forEachMutation = function forEachMutation(fn) {
    if (this._rawModule.mutations) {
      forEachValue(this._rawModule.mutations, fn);
    }
  };

  Object.defineProperties(Module.prototype, prototypeAccessors$1);

  var ModuleCollection = function ModuleCollection(rawRootModule) {
    // register root module (Vuex.Store options)
    this.register([], rawRootModule, false);
  };

  ModuleCollection.prototype.get = function get(path) {
    return path.reduce(function (module, key) {
      return module.getChild(key);
    }, this.root);
  };

  ModuleCollection.prototype.getNamespace = function getNamespace(path) {
    var module = this.root;
    return path.reduce(function (namespace, key) {
      module = module.getChild(key);
      return namespace + (module.namespaced ? key + '/' : '');
    }, '');
  };

  ModuleCollection.prototype.update = function update$1(rawRootModule) {
    update([], this.root, rawRootModule);
  };

  ModuleCollection.prototype.register = function register(path, rawModule, runtime) {
    var this$1$1 = this;
    if (runtime === void 0) runtime = true;
    var newModule = new Module(rawModule, runtime);

    if (path.length === 0) {
      this.root = newModule;
    } else {
      var parent = this.get(path.slice(0, -1));
      parent.addChild(path[path.length - 1], newModule);
    } // register nested modules


    if (rawModule.modules) {
      forEachValue(rawModule.modules, function (rawChildModule, key) {
        this$1$1.register(path.concat(key), rawChildModule, runtime);
      });
    }
  };

  ModuleCollection.prototype.unregister = function unregister(path) {
    var parent = this.get(path.slice(0, -1));
    var key = path[path.length - 1];
    var child = parent.getChild(key);

    if (!child) {
      return;
    }

    if (!child.runtime) {
      return;
    }

    parent.removeChild(key);
  };

  ModuleCollection.prototype.isRegistered = function isRegistered(path) {
    var parent = this.get(path.slice(0, -1));
    var key = path[path.length - 1];

    if (parent) {
      return parent.hasChild(key);
    }

    return false;
  };

  function update(path, targetModule, newModule) {
    targetModule.update(newModule); // update nested modules

    if (newModule.modules) {
      for (var key in newModule.modules) {
        if (!targetModule.getChild(key)) {
          return;
        }

        update(path.concat(key), targetModule.getChild(key), newModule.modules[key]);
      }
    }
  }

  function createStore(options) {
    return new Store(options);
  }

  var Store = function Store(options) {
    var this$1$1 = this;
    if (options === void 0) options = {};
    var plugins = options.plugins;
    if (plugins === void 0) plugins = [];
    var strict = options.strict;
    if (strict === void 0) strict = false;
    var devtools = options.devtools; // store internal state

    this._committing = false;
    this._actions = Object.create(null);
    this._actionSubscribers = [];
    this._mutations = Object.create(null);
    this._wrappedGetters = Object.create(null);
    this._modules = new ModuleCollection(options);
    this._modulesNamespaceMap = Object.create(null);
    this._subscribers = [];
    this._makeLocalGettersCache = Object.create(null);
    this._devtools = devtools; // bind commit and dispatch to self

    var store = this;
    var ref = this;
    var dispatch = ref.dispatch;
    var commit = ref.commit;

    this.dispatch = function boundDispatch(type, payload) {
      return dispatch.call(store, type, payload);
    };

    this.commit = function boundCommit(type, payload, options) {
      return commit.call(store, type, payload, options);
    }; // strict mode


    this.strict = strict;
    var state = this._modules.root.state; // init root module.
    // this also recursively registers all sub-modules
    // and collects all module getters inside this._wrappedGetters

    installModule(this, state, [], this._modules.root); // initialize the store state, which is responsible for the reactivity
    // (also registers _wrappedGetters as computed properties)

    resetStoreState(this, state); // apply plugins

    plugins.forEach(function (plugin) {
      return plugin(this$1$1);
    });
  };

  var prototypeAccessors = {
    state: {
      configurable: true
    }
  };

  Store.prototype.install = function install(app, injectKey) {
    app.provide(injectKey || storeKey, this);
    app.config.globalProperties.$store = this;
    var useDevtools = this._devtools !== undefined ? this._devtools : true;

    if (useDevtools) {
      addDevtools(app, this);
    }
  };

  prototypeAccessors.state.get = function () {
    return this._state.data;
  };

  prototypeAccessors.state.set = function (v) {};

  Store.prototype.commit = function commit(_type, _payload, _options) {
    var this$1$1 = this; // check object-style commit

    var ref = unifyObjectStyle(_type, _payload, _options);
    var type = ref.type;
    var payload = ref.payload;
    var mutation = {
      type: type,
      payload: payload
    };
    var entry = this._mutations[type];

    if (!entry) {
      return;
    }

    this._withCommit(function () {
      entry.forEach(function commitIterator(handler) {
        handler(payload);
      });
    });

    this._subscribers.slice() // shallow copy to prevent iterator invalidation if subscriber synchronously calls unsubscribe
    .forEach(function (sub) {
      return sub(mutation, this$1$1.state);
    });
  };

  Store.prototype.dispatch = function dispatch(_type, _payload) {
    var this$1$1 = this; // check object-style dispatch

    var ref = unifyObjectStyle(_type, _payload);
    var type = ref.type;
    var payload = ref.payload;
    var action = {
      type: type,
      payload: payload
    };
    var entry = this._actions[type];

    if (!entry) {
      return;
    }

    try {
      this._actionSubscribers.slice() // shallow copy to prevent iterator invalidation if subscriber synchronously calls unsubscribe
      .filter(function (sub) {
        return sub.before;
      }).forEach(function (sub) {
        return sub.before(action, this$1$1.state);
      });
    } catch (e) {}

    var result = entry.length > 1 ? Promise.all(entry.map(function (handler) {
      return handler(payload);
    })) : entry[0](payload);
    return new Promise(function (resolve, reject) {
      result.then(function (res) {
        try {
          this$1$1._actionSubscribers.filter(function (sub) {
            return sub.after;
          }).forEach(function (sub) {
            return sub.after(action, this$1$1.state);
          });
        } catch (e) {}

        resolve(res);
      }, function (error) {
        try {
          this$1$1._actionSubscribers.filter(function (sub) {
            return sub.error;
          }).forEach(function (sub) {
            return sub.error(action, this$1$1.state, error);
          });
        } catch (e) {}

        reject(error);
      });
    });
  };

  Store.prototype.subscribe = function subscribe(fn, options) {
    return genericSubscribe(fn, this._subscribers, options);
  };

  Store.prototype.subscribeAction = function subscribeAction(fn, options) {
    var subs = typeof fn === 'function' ? {
      before: fn
    } : fn;
    return genericSubscribe(subs, this._actionSubscribers, options);
  };

  Store.prototype.watch = function watch$1(getter, cb, options) {
    var this$1$1 = this;
    return watch(function () {
      return getter(this$1$1.state, this$1$1.getters);
    }, cb, Object.assign({}, options));
  };

  Store.prototype.replaceState = function replaceState(state) {
    var this$1$1 = this;

    this._withCommit(function () {
      this$1$1._state.data = state;
    });
  };

  Store.prototype.registerModule = function registerModule(path, rawModule, options) {
    if (options === void 0) options = {};

    if (typeof path === 'string') {
      path = [path];
    }

    this._modules.register(path, rawModule);

    installModule(this, this.state, path, this._modules.get(path), options.preserveState); // reset store to update getters...

    resetStoreState(this, this.state);
  };

  Store.prototype.unregisterModule = function unregisterModule(path) {
    var this$1$1 = this;

    if (typeof path === 'string') {
      path = [path];
    }

    this._modules.unregister(path);

    this._withCommit(function () {
      var parentState = getNestedState(this$1$1.state, path.slice(0, -1));
      delete parentState[path[path.length - 1]];
    });

    resetStore(this);
  };

  Store.prototype.hasModule = function hasModule(path) {
    if (typeof path === 'string') {
      path = [path];
    }

    return this._modules.isRegistered(path);
  };

  Store.prototype.hotUpdate = function hotUpdate(newOptions) {
    this._modules.update(newOptions);

    resetStore(this, true);
  };

  Store.prototype._withCommit = function _withCommit(fn) {
    var committing = this._committing;
    this._committing = true;
    fn();
    this._committing = committing;
  };

  Object.defineProperties(Store.prototype, prototypeAccessors);

  var isMergeableObject = function isMergeableObject(value) {
    return isNonNullObject(value) && !isSpecial(value);
  };

  function isNonNullObject(value) {
    return !!value && typeof value === 'object';
  }

  function isSpecial(value) {
    var stringValue = Object.prototype.toString.call(value);
    return stringValue === '[object RegExp]' || stringValue === '[object Date]' || isReactElement(value);
  } // see https://github.com/facebook/react/blob/b5ac963fb791d1298e7f396236383bc955f916c1/src/isomorphic/classic/element/ReactElement.js#L21-L25


  var canUseSymbol = typeof Symbol === 'function' && Symbol.for;
  var REACT_ELEMENT_TYPE = canUseSymbol ? Symbol.for('react.element') : 0xeac7;

  function isReactElement(value) {
    return value.$$typeof === REACT_ELEMENT_TYPE;
  }

  function emptyTarget(val) {
    return Array.isArray(val) ? [] : {};
  }

  function cloneUnlessOtherwiseSpecified(value, options) {
    return options.clone !== false && options.isMergeableObject(value) ? deepmerge(emptyTarget(value), value, options) : value;
  }

  function defaultArrayMerge(target, source, options) {
    return target.concat(source).map(function (element) {
      return cloneUnlessOtherwiseSpecified(element, options);
    });
  }

  function getMergeFunction(key, options) {
    if (!options.customMerge) {
      return deepmerge;
    }

    var customMerge = options.customMerge(key);
    return typeof customMerge === 'function' ? customMerge : deepmerge;
  }

  function getEnumerableOwnPropertySymbols(target) {
    return Object.getOwnPropertySymbols ? Object.getOwnPropertySymbols(target).filter(function (symbol) {
      return target.propertyIsEnumerable(symbol);
    }) : [];
  }

  function getKeys(target) {
    return Object.keys(target).concat(getEnumerableOwnPropertySymbols(target));
  }

  function propertyIsOnObject(object, property) {
    try {
      return property in object;
    } catch (_) {
      return false;
    }
  } // Protects from prototype poisoning and unexpected merging up the prototype chain.


  function propertyIsUnsafe(target, key) {
    return propertyIsOnObject(target, key) // Properties are safe to merge if they don't exist in the target yet,
    && !(Object.hasOwnProperty.call(target, key) // unsafe if they exist up the prototype chain,
    && Object.propertyIsEnumerable.call(target, key)); // and also unsafe if they're nonenumerable.
  }

  function mergeObject(target, source, options) {
    var destination = {};

    if (options.isMergeableObject(target)) {
      getKeys(target).forEach(function (key) {
        destination[key] = cloneUnlessOtherwiseSpecified(target[key], options);
      });
    }

    getKeys(source).forEach(function (key) {
      if (propertyIsUnsafe(target, key)) {
        return;
      }

      if (propertyIsOnObject(target, key) && options.isMergeableObject(source[key])) {
        destination[key] = getMergeFunction(key, options)(target[key], source[key], options);
      } else {
        destination[key] = cloneUnlessOtherwiseSpecified(source[key], options);
      }
    });
    return destination;
  }

  function deepmerge(target, source, options) {
    options = options || {};
    options.arrayMerge = options.arrayMerge || defaultArrayMerge;
    options.isMergeableObject = options.isMergeableObject || isMergeableObject; // cloneUnlessOtherwiseSpecified is added to `options` so that custom arrayMerge()
    // implementations can use it. The caller may not replace it.

    options.cloneUnlessOtherwiseSpecified = cloneUnlessOtherwiseSpecified;
    var sourceIsArray = Array.isArray(source);
    var targetIsArray = Array.isArray(target);
    var sourceAndTargetTypesMatch = sourceIsArray === targetIsArray;

    if (!sourceAndTargetTypesMatch) {
      return cloneUnlessOtherwiseSpecified(source, options);
    } else if (sourceIsArray) {
      return options.arrayMerge(target, source, options);
    } else {
      return mergeObject(target, source, options);
    }
  }

  deepmerge.all = function deepmergeAll(array, options) {
    if (!Array.isArray(array)) {
      throw new Error('first argument should be an array');
    }

    return array.reduce(function (prev, next) {
      return deepmerge(prev, next, options);
    }, {});
  };

  var deepmerge_1 = deepmerge;
  var cjs = deepmerge_1;
  /**
   * Created by championswimmer on 22/07/17.
   */

  var MockStorage; // @ts-ignore

  {
    MockStorage = /*#__PURE__*/function () {
      function MockStorage() {}

      var _proto6 = MockStorage.prototype;

      _proto6.key = function key(index) {
        return Object.keys(this)[index];
      };

      _proto6.setItem = function setItem(key, data) {
        this[key] = data.toString();
      };

      _proto6.getItem = function getItem(key) {
        return this[key];
      };

      _proto6.removeItem = function removeItem(key) {
        delete this[key];
      };

      _proto6.clear = function clear() {
        for (var _i4 = 0, _Object$keys = Object.keys(this); _i4 < _Object$keys.length; _i4++) {
          var key = _Object$keys[_i4];
          delete this[key];
        }
      };

      _createClass(MockStorage, [{
        key: "length",
        get: function get() {
          return Object.keys(this).length;
        }
      }]);

      return MockStorage;
    }();
  } // tslint:disable: variable-name

  var SimplePromiseQueue = /*#__PURE__*/function () {
    function SimplePromiseQueue() {
      this._queue = [];
      this._flushing = false;
    }

    var _proto7 = SimplePromiseQueue.prototype;

    _proto7.enqueue = function enqueue(promise) {
      this._queue.push(promise);

      if (!this._flushing) {
        return this.flushQueue();
      }

      return Promise.resolve();
    };

    _proto7.flushQueue = function flushQueue() {
      var _this19 = this;

      this._flushing = true;

      var chain = function chain() {
        var nextTask = _this19._queue.shift();

        if (nextTask) {
          return nextTask.then(chain);
        } else {
          _this19._flushing = false;
        }
      };

      return Promise.resolve(chain());
    };

    return SimplePromiseQueue;
  }();

  var options$1 = {
    replaceArrays: {
      arrayMerge: function arrayMerge(destinationArray, sourceArray, options) {
        return sourceArray;
      }
    },
    concatArrays: {
      arrayMerge: function arrayMerge(target, source, options) {
        return target.concat.apply(target, source);
      }
    }
  };

  function merge(into, from, mergeOption) {
    return cjs(into, from, options$1[mergeOption]);
  }

  var FlattedJSON = JSON;
  /**
   * A class that implements the vuex persistence.
   * @type S type of the 'state' inside the store (default: any)
   */

  var VuexPersistence =
  /**
   * Create a {@link VuexPersistence} object.
   * Use the <code>plugin</code> function of this class as a
   * Vuex plugin.
   * @param {PersistOptions} options
   */
  function VuexPersistence(options) {
    var _this20 = this;

    // tslint:disable-next-line:variable-name
    this._mutex = new SimplePromiseQueue();
    /**
     * Creates a subscriber on the store. automatically is used
     * when this is used a vuex plugin. Not for manual usage.
     * @param store
     */

    this.subscriber = function (store) {
      return function (handler) {
        return store.subscribe(handler);
      };
    };

    if (typeof options === 'undefined') options = {};
    this.key = options.key != null ? options.key : 'vuex';
    this.subscribed = false;
    this.supportCircular = options.supportCircular || false;

    if (this.supportCircular) {
      FlattedJSON = cjs$1;
    }

    this.mergeOption = options.mergeOption || 'replaceArrays';
    var localStorageLitmus = true;

    try {
      window.localStorage.getItem('');
    } catch (err) {
      localStorageLitmus = false;
    }
    /**
     * 1. First, prefer storage sent in optinos
     * 2. Otherwise, use window.localStorage if available
     * 3. Finally, try to use MockStorage
     * 4. None of above? Well we gotta fail.
     */


    if (options.storage) {
      this.storage = options.storage;
    } else if (localStorageLitmus) {
      this.storage = window.localStorage;
    } else if (MockStorage) {
      this.storage = new MockStorage();
    } else {
      throw new Error("Neither 'window' is defined, nor 'MockStorage' is available");
    }
    /**
     * How this works is -
     *  1. If there is options.reducer function, we use that, if not;
     *  2. We check options.modules;
     *    1. If there is no options.modules array, we use entire state in reducer
     *    2. Otherwise, we create a reducer that merges all those state modules that are
     *        defined in the options.modules[] array
     * @type {((state: S) => {}) | ((state: S) => S) | ((state: any) => {})}
     */


    this.reducer = options.reducer != null ? options.reducer : options.modules == null ? function (state) {
      return state;
    } : function (state) {
      return options.modules.reduce(function (a, i) {
        var _merge;

        return merge(a, (_merge = {}, _merge[i] = state[i], _merge), _this20.mergeOption);
      }, {
        /* start empty accumulator*/
      });
    };

    this.filter = options.filter || function (mutation) {
      return true;
    };

    this.strictMode = options.strictMode || false;

    this.RESTORE_MUTATION = function RESTORE_MUTATION(state, savedState) {
      var mergedState = merge(state, savedState || {}, this.mergeOption);

      for (var _i5 = 0, _Object$keys2 = Object.keys(mergedState); _i5 < _Object$keys2.length; _i5++) {
        var propertyName = _Object$keys2[_i5];

        this._vm.$set(state, propertyName, mergedState[propertyName]);
      }
    };

    this.asyncStorage = options.asyncStorage || false;

    if (this.asyncStorage) {
      /**
       * Async {@link #VuexPersistence.restoreState} implementation
       * @type {((key: string, storage?: Storage) =>
       *      (Promise<S> | S)) | ((key: string, storage: AsyncStorage) => Promise<any>)}
       */
      this.restoreState = options.restoreState != null ? options.restoreState : function (key, storage) {
        return storage.getItem(key).then(function (value) {
          return typeof value === 'string' // If string, parse, or else, just return
          ? _this20.supportCircular ? FlattedJSON.parse(value || '{}') : JSON.parse(value || '{}') : value || {};
        });
      };
      /**
       * Async {@link #VuexPersistence.saveState} implementation
       * @type {((key: string, state: {}, storage?: Storage) =>
       *    (Promise<void> | void)) | ((key: string, state: {}, storage?: Storage) => Promise<void>)}
       */

      this.saveState = options.saveState != null ? options.saveState : function (key, state, storage) {
        return storage.setItem(key, // Second argument is state _object_ if asyc storage, stringified otherwise
        // do not stringify the state if the storage type is async
        _this20.asyncStorage ? merge({}, state || {}, _this20.mergeOption) : _this20.supportCircular ? FlattedJSON.stringify(state) : JSON.stringify(state));
      };
      /**
       * Async version of plugin
       * @param {Store<S>} store
       */

      this.plugin = function (store) {
        /**
         * For async stores, we're capturing the Promise returned
         * by the `restoreState()` function in a `restored` property
         * on the store itself. This would allow app developers to
         * determine when and if the store's state has indeed been
         * refreshed. This approach was suggested by GitHub user @hotdogee.
         * See https://github.com/championswimmer/vuex-persist/pull/118#issuecomment-500914963
         * @since 2.1.0
         */
        store.restored = _this20.restoreState(_this20.key, _this20.storage).then(function (savedState) {
          /**
           * If in strict mode, do only via mutation
           */
          if (_this20.strictMode) {
            store.commit('RESTORE_MUTATION', savedState);
          } else {
            store.replaceState(merge(store.state, savedState || {}, _this20.mergeOption));
          }

          _this20.subscriber(store)(function (mutation, state) {
            if (_this20.filter(mutation)) {
              _this20._mutex.enqueue(_this20.saveState(_this20.key, _this20.reducer(state), _this20.storage));
            }
          });

          _this20.subscribed = true;
        });
      };
    } else {
      /**
       * Sync {@link #VuexPersistence.restoreState} implementation
       * @type {((key: string, storage?: Storage) =>
       *    (Promise<S> | S)) | ((key: string, storage: Storage) => (any | string | {}))}
       */
      this.restoreState = options.restoreState != null ? options.restoreState : function (key, storage) {
        var value = storage.getItem(key);

        if (typeof value === 'string') {
          // If string, parse, or else, just return
          return _this20.supportCircular ? FlattedJSON.parse(value || '{}') : JSON.parse(value || '{}');
        } else {
          return value || {};
        }
      };
      /**
       * Sync {@link #VuexPersistence.saveState} implementation
       * @type {((key: string, state: {}, storage?: Storage) =>
       *     (Promise<void> | void)) | ((key: string, state: {}, storage?: Storage) => Promise<void>)}
       */

      this.saveState = options.saveState != null ? options.saveState : function (key, state, storage) {
        return storage.setItem(key, // Second argument is state _object_ if localforage, stringified otherwise
        _this20.supportCircular ? FlattedJSON.stringify(state) : JSON.stringify(state));
      };
      /**
       * Sync version of plugin
       * @param {Store<S>} store
       */

      this.plugin = function (store) {
        var savedState = _this20.restoreState(_this20.key, _this20.storage);

        if (_this20.strictMode) {
          store.commit('RESTORE_MUTATION', savedState);
        } else {
          store.replaceState(merge(store.state, savedState || {}, _this20.mergeOption));
        }

        _this20.subscriber(store)(function (mutation, state) {
          if (_this20.filter(mutation)) {
            _this20.saveState(_this20.key, _this20.reducer(state), _this20.storage);
          }
        });

        _this20.subscribed = true;
      };
    }
  };

  var VuexPersistence$1 = VuexPersistence; // The options for persisting state
  // eslint-disable-next-line import/prefer-default-export

  var persistedStateOptions = {
    storage: window.sessionStorage,
    key: 'joomla.mediamanager',
    reducer: function reducer(state) {
      return {
        selectedDirectory: state.selectedDirectory,
        showInfoBar: state.showInfoBar,
        listView: state.listView,
        gridSize: state.gridSize,
        search: state.search
      };
    }
  };
  var options = Joomla.getOptions('com_media', {});

  if (options.providers === undefined || options.providers.length === 0) {
    throw new TypeError('Media providers are not defined.');
  }
  /**
   * Get the drives
   *
   * @param  {Array}  adapterNames
   * @param  {String} provider
   *
   * @return {Array}
   */


  var getDrives = function getDrives(adapterNames, provider) {
    return adapterNames.map(function (name) {
      return {
        root: provider + "-" + name + ":/",
        displayName: name
      };
    });
  }; // Load disks from options


  var loadedDisks = options.providers.map(function (disk) {
    return {
      displayName: disk.displayName,
      drives: getDrives(disk.adapterNames, disk.name)
    };
  });
  var defaultDisk = loadedDisks.find(function (disk) {
    return disk.drives.length > 0 && disk.drives[0] !== undefined;
  });

  if (!defaultDisk) {
    throw new TypeError('No default media drive was found');
  }

  var currentPath;
  var storedState = JSON.parse(persistedStateOptions.storage.getItem(persistedStateOptions.key)); // Gracefully use the given path, the session storage state or fall back to sensible default

  if (options.currentPath) {
    var useDrive = false;
    Object.values(loadedDisks).forEach(function (drive) {
      return drive.drives.forEach(function (curDrive) {
        if (options.currentPath.indexOf(curDrive.root) === 0) {
          useDrive = true;
        }
      });
    });

    if (useDrive) {
      if (storedState && storedState.selectedDirectory && storedState.selectedDirectory !== options.currentPath) {
        storedState.selectedDirectory = options.currentPath;
        persistedStateOptions.storage.setItem(persistedStateOptions.key, JSON.stringify(storedState));
        currentPath = options.currentPath;
      } else {
        currentPath = options.currentPath;
      }
    } else {
      currentPath = defaultDisk.drives[0].root;
    }
  } else if (storedState && storedState.selectedDirectory) {
    currentPath = storedState.selectedDirectory;
  }

  if (!currentPath) {
    currentPath = defaultDisk.drives[0].root;
  } // The initial state


  var state = {
    // The general loading state
    isLoading: false,
    // Will hold the activated filesystem disks
    disks: loadedDisks,
    // The loaded directories
    directories: loadedDisks.map(function () {
      return {
        path: defaultDisk.drives[0].root,
        name: defaultDisk.displayName,
        directories: [],
        files: [],
        directory: null
      };
    }),
    // The loaded files
    files: [],
    // The selected disk. Providers are ordered by plugin ordering, so we set the first provider
    // in the list as the default provider and load first drive on it as default
    selectedDirectory: currentPath,
    // The currently selected items
    selectedItems: [],
    // The state of the infobar
    showInfoBar: false,
    // List view
    listView: 'grid',
    // The size of the grid items
    gridSize: 'md',
    // The state of confirm delete model
    showConfirmDeleteModal: false,
    // The state of create folder model
    showCreateFolderModal: false,
    // The state of preview model
    showPreviewModal: false,
    // The state of share model
    showShareModal: false,
    // The state of  model
    showRenameModal: false,
    // The preview item
    previewItem: null,
    // The Search Query
    search: ''
  }; // Sometimes we may need to compute derived state based on store state,
  // for example filtering through a list of items and counting them.

  /**
   * Get the currently selected directory
   * @param state
   * @returns {*}
   */

  var getSelectedDirectory = function getSelectedDirectory(state) {
    return state.directories.find(function (directory) {
      return directory.path === state.selectedDirectory;
    });
  };
  /**
   * Get the sudirectories of the currently selected directory
   * @param state
   *
   * @returns {Array|directories|{/}|computed.directories|*|Object}
   */


  var getSelectedDirectoryDirectories = function getSelectedDirectoryDirectories(state) {
    return state.directories.filter(function (directory) {
      return directory.directory === state.selectedDirectory;
    });
  };
  /**
   * Get the files of the currently selected directory
   * @param state
   *
   * @returns {Array|files|{}|FileList|*}
   */


  var getSelectedDirectoryFiles = function getSelectedDirectoryFiles(state) {
    return state.files.filter(function (file) {
      return file.directory === state.selectedDirectory;
    });
  };
  /**
   * Whether or not all items of the current directory are selected
   * @param state
   * @param getters
   * @returns Array
   */


  var getSelectedDirectoryContents = function getSelectedDirectoryContents(state, getters) {
    return [].concat(getters.getSelectedDirectoryDirectories, getters.getSelectedDirectoryFiles);
  };

  var getters = /*#__PURE__*/Object.freeze({
    __proto__: null,
    getSelectedDirectory: getSelectedDirectory,
    getSelectedDirectoryDirectories: getSelectedDirectoryDirectories,
    getSelectedDirectoryFiles: getSelectedDirectoryFiles,
    getSelectedDirectoryContents: getSelectedDirectoryContents
  });

  var updateUrlPath = function updateUrlPath(path) {
    var currentPath = path === null ? '' : path;
    var url = new URL(window.location.href);

    if (url.searchParams.has('path')) {
      window.history.pushState(null, '', url.href.replace(/\b(path=).*?(&|$)/, "$1" + currentPath + "$2"));
    } else {
      window.history.pushState(null, '', url.href + (url.href.indexOf('?') > 0 ? '&' : '?') + "path=" + currentPath);
    }
  };
  /**
   * Actions are similar to mutations, the difference being that:
   * Instead of mutating the state, actions commit mutations.
   * Actions can contain arbitrary asynchronous operations.
   */

  /**
   * Get contents of a directory from the api
   * @param context
   * @param payload
   */


  var getContents = function getContents(context, payload) {
    // Update the url
    updateUrlPath(payload);
    context.commit(SET_IS_LOADING, true);
    api.getContents(payload, 0).then(function (contents) {
      context.commit(LOAD_CONTENTS_SUCCESS, contents);
      context.commit(UNSELECT_ALL_BROWSER_ITEMS);
      context.commit(SELECT_DIRECTORY, payload);
      context.commit(SET_IS_LOADING, false);
    }).catch(function (error) {
      // @todo error handling
      context.commit(SET_IS_LOADING, false); // eslint-disable-next-line no-console

      console.log('error', error);
    });
  };
  /**
   * Get the full contents of a directory
   * @param context
   * @param payload
   */


  var getFullContents = function getFullContents(context, payload) {
    context.commit(SET_IS_LOADING, true);
    api.getContents(payload.path, 1).then(function (contents) {
      context.commit(LOAD_FULL_CONTENTS_SUCCESS, contents.files[0]);
      context.commit(SET_IS_LOADING, false);
    }).catch(function (error) {
      // @todo error handling
      context.commit(SET_IS_LOADING, false); // eslint-disable-next-line no-console

      console.log('error', error);
    });
  };
  /**
   * Download a file
   * @param context
   * @param payload
   */


  var download = function download(context, payload) {
    api.getContents(payload.path, 0, 1).then(function (contents) {
      var file = contents.files[0]; // Convert the base 64 encoded string to a blob

      var byteCharacters = atob(file.content);
      var byteArrays = [];

      for (var offset = 0; offset < byteCharacters.length; offset += 512) {
        var slice = byteCharacters.slice(offset, offset + 512);
        var byteNumbers = new Array(slice.length); // eslint-disable-next-line no-plusplus

        for (var i = 0; i < slice.length; i++) {
          byteNumbers[i] = slice.charCodeAt(i);
        }

        var byteArray = new Uint8Array(byteNumbers);
        byteArrays.push(byteArray);
      } // Download file


      var blobURL = URL.createObjectURL(new Blob(byteArrays, {
        type: file.mime_type
      }));
      var a = document.createElement('a');
      a.href = blobURL;
      a.download = file.name;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    }).catch(function (error) {
      // eslint-disable-next-line no-console
      console.log('error', error);
    });
  };
  /**
   * Toggle the selection state of an item
   * @param context
   * @param payload
   */


  var toggleBrowserItemSelect = function toggleBrowserItemSelect(context, payload) {
    var item = payload;
    var isSelected = context.state.selectedItems.some(function (selected) {
      return selected.path === item.path;
    });

    if (!isSelected) {
      context.commit(SELECT_BROWSER_ITEM, item);
    } else {
      context.commit(UNSELECT_BROWSER_ITEM, item);
    }
  };
  /**
   * Create a new folder
   * @param context
   * @param payload object with the new folder name and its parent directory
   */


  var createDirectory = function createDirectory(context, payload) {
    if (!api.canCreate) {
      return;
    }

    context.commit(SET_IS_LOADING, true);
    api.createDirectory(payload.name, payload.parent).then(function (folder) {
      context.commit(CREATE_DIRECTORY_SUCCESS, folder);
      context.commit(HIDE_CREATE_FOLDER_MODAL);
      context.commit(SET_IS_LOADING, false);
    }).catch(function (error) {
      // @todo error handling
      context.commit(SET_IS_LOADING, false); // eslint-disable-next-line no-console

      console.log('error', error);
    });
  };
  /**
   * Create a new folder
   * @param context
   * @param payload object with the new folder name and its parent directory
   */


  var uploadFile = function uploadFile(context, payload) {
    if (!api.canCreate) {
      return;
    }

    context.commit(SET_IS_LOADING, true);
    api.upload(payload.name, payload.parent, payload.content, payload.override || false).then(function (file) {
      context.commit(UPLOAD_SUCCESS, file);
      context.commit(SET_IS_LOADING, false);
    }).catch(function (error) {
      context.commit(SET_IS_LOADING, false); // Handle file exists

      if (error.status === 409) {
        if (notifications.ask(Translate.sprintf('COM_MEDIA_FILE_EXISTS_AND_OVERRIDE', payload.name), {})) {
          payload.override = true;
          uploadFile(context, payload);
        }
      }
    });
  };
  /**
   * Rename an item
   * @param context
   * @param payload object: the item and the new path
   */


  var renameItem = function renameItem(context, payload) {
    if (!api.canEdit) {
      return;
    }

    if (typeof payload.item.canEdit !== 'undefined' && payload.item.canEdit === false) {
      return;
    }

    context.commit(SET_IS_LOADING, true);
    api.rename(payload.item.path, payload.newPath).then(function (item) {
      context.commit(RENAME_SUCCESS, {
        item: item,
        oldPath: payload.item.path,
        newName: payload.newName
      });
      context.commit(HIDE_RENAME_MODAL);
      context.commit(SET_IS_LOADING, false);
    }).catch(function (error) {
      // @todo error handling
      context.commit(SET_IS_LOADING, false); // eslint-disable-next-line no-console

      console.log('error', error);
    });
  };
  /**
   * Delete the selected items
   * @param context
   */


  var deleteSelectedItems = function deleteSelectedItems(context) {
    if (!api.canDelete) {
      return;
    }

    context.commit(SET_IS_LOADING, true); // Get the selected items from the store

    var _context$state = context.state,
        selectedItems = _context$state.selectedItems,
        search = _context$state.search;

    if (selectedItems.length > 0) {
      selectedItems.forEach(function (item) {
        if (typeof item.canDelete !== 'undefined' && item.canDelete === false || search && !item.name.toLowerCase().includes(search.toLowerCase())) {
          return;
        }

        api.delete(item.path).then(function () {
          context.commit(DELETE_SUCCESS, item);
          context.commit(UNSELECT_ALL_BROWSER_ITEMS);
          context.commit(SET_IS_LOADING, false);
        }).catch(function (error) {
          // @todo error handling
          context.commit(SET_IS_LOADING, false); // eslint-disable-next-line no-console

          console.log('error', error);
        });
      });
    }
  };

  var actions = /*#__PURE__*/Object.freeze({
    __proto__: null,
    getContents: getContents,
    getFullContents: getFullContents,
    download: download,
    toggleBrowserItemSelect: toggleBrowserItemSelect,
    createDirectory: createDirectory,
    uploadFile: uploadFile,
    renameItem: renameItem,
    deleteSelectedItems: deleteSelectedItems
  }); // Mutations are very similar to events: each mutation has a string type and a handler.
  // The handler function is where we perform actual state modifications,
  // and it will receive the state as the first argument.
  // The grid item sizes

  var gridItemSizes = ['sm', 'md', 'lg', 'xl'];
  var mutations = (_mutations = {}, _mutations[SELECT_DIRECTORY] = function (state, payload) {
    state.selectedDirectory = payload;
    state.search = '';
  }, _mutations[LOAD_CONTENTS_SUCCESS] = function (state, payload) {
    /**
     * Create the directory structure
     * @param path
     */
    function createDirectoryStructureFromPath(path) {
      var exists = state.directories.some(function (existing) {
        return existing.path === path;
      });

      if (!exists) {
        // eslint-disable-next-line no-use-before-define
        var directory = directoryFromPath(path); // Add the sub directories and files

        directory.directories = state.directories.filter(function (existing) {
          return existing.directory === directory.path;
        }).map(function (existing) {
          return existing.path;
        }); // Add the directory

        state.directories.push(directory);

        if (directory.directory) {
          createDirectoryStructureFromPath(directory.directory);
        }
      }
    }
    /**
     * Create a directory from a path
     * @param path
     */


    function directoryFromPath(path) {
      var parts = path.split('/');
      var directory = dirname(path);

      if (directory.indexOf(':', directory.length - 1) !== -1) {
        directory += '/';
      }

      return {
        path: path,
        name: parts[parts.length - 1],
        directories: [],
        files: [],
        directory: directory !== '.' ? directory : null,
        type: 'dir',
        mime_type: 'directory'
      };
    }
    /**
     * Add a directory
     * @param state
     * @param directory
     */
    // eslint-disable-next-line no-shadow


    function addDirectory(state, directory) {
      var parentDirectory = state.directories.find(function (existing) {
        return existing.path === directory.directory;
      });
      var parentDirectoryIndex = state.directories.indexOf(parentDirectory);
      var index = state.directories.findIndex(function (existing) {
        return existing.path === directory.path;
      });

      if (index === -1) {
        index = state.directories.length;
      } // Add the directory


      state.directories.splice(index, 1, directory); // Update the relation to the parent directory

      if (parentDirectoryIndex !== -1) {
        state.directories.splice(parentDirectoryIndex, 1, Object.assign({}, parentDirectory, {
          directories: [].concat(parentDirectory.directories, [directory.path])
        }));
      }
    }
    /**
     * Add a file
     * @param state
     * @param directory
     */
    // eslint-disable-next-line no-shadow


    function addFile(state, file) {
      var parentDirectory = state.directories.find(function (directory) {
        return directory.path === file.directory;
      });
      var parentDirectoryIndex = state.directories.indexOf(parentDirectory);
      var index = state.files.findIndex(function (existing) {
        return existing.path === file.path;
      });

      if (index === -1) {
        index = state.files.length;
      } // Add the file


      state.files.splice(index, 1, file); // Update the relation to the parent directory

      if (parentDirectoryIndex !== -1) {
        state.directories.splice(parentDirectoryIndex, 1, Object.assign({}, parentDirectory, {
          files: [].concat(parentDirectory.files, [file.path])
        }));
      }
    } // Create the parent directory structure if it does not exist


    createDirectoryStructureFromPath(state.selectedDirectory); // Add directories

    payload.directories.forEach(function (directory) {
      addDirectory(state, directory);
    }); // Add files

    payload.files.forEach(function (file) {
      addFile(state, file);
    });
  }, _mutations[UPLOAD_SUCCESS] = function (state, payload) {
    var file = payload;
    var isNew = !state.files.some(function (existing) {
      return existing.path === file.path;
    }); // @todo handle file_exists

    if (isNew) {
      var parentDirectory = state.directories.find(function (existing) {
        return existing.path === file.directory;
      });
      var parentDirectoryIndex = state.directories.indexOf(parentDirectory); // Add the new file to the files array

      state.files.push(file); // Update the relation to the parent directory

      state.directories.splice(parentDirectoryIndex, 1, Object.assign({}, parentDirectory, {
        files: [].concat(parentDirectory.files, [file.path])
      }));
    }
  }, _mutations[CREATE_DIRECTORY_SUCCESS] = function (state, payload) {
    var directory = payload;
    var isNew = !state.directories.some(function (existing) {
      return existing.path === directory.path;
    });

    if (isNew) {
      var parentDirectory = state.directories.find(function (existing) {
        return existing.path === directory.directory;
      });
      var parentDirectoryIndex = state.directories.indexOf(parentDirectory); // Add the new directory to the directory

      state.directories.push(directory); // Update the relation to the parent directory

      state.directories.splice(parentDirectoryIndex, 1, Object.assign({}, parentDirectory, {
        directories: [].concat(parentDirectory.directories, [directory.path])
      }));
    }
  }, _mutations[RENAME_SUCCESS] = function (state, payload) {
    state.selectedItems[state.selectedItems.length - 1].name = payload.newName;
    var item = payload.item;
    var oldPath = payload.oldPath;

    if (item.type === 'file') {
      var index = state.files.findIndex(function (file) {
        return file.path === oldPath;
      });
      state.files.splice(index, 1, item);
    } else {
      var _index = state.directories.findIndex(function (directory) {
        return directory.path === oldPath;
      });

      state.directories.splice(_index, 1, item);
    }
  }, _mutations[DELETE_SUCCESS] = function (state, payload) {
    var item = payload; // Delete file

    if (item.type === 'file') {
      state.files.splice(state.files.findIndex(function (file) {
        return file.path === item.path;
      }), 1);
    } // Delete dir


    if (item.type === 'dir') {
      state.directories.splice(state.directories.findIndex(function (directory) {
        return directory.path === item.path;
      }), 1);
    }
  }, _mutations[SELECT_BROWSER_ITEM] = function (state, payload) {
    state.selectedItems.push(payload);
  }, _mutations[SELECT_BROWSER_ITEMS] = function (state, payload) {
    state.selectedItems = payload;
  }, _mutations[UNSELECT_BROWSER_ITEM] = function (state, payload) {
    var item = payload;
    state.selectedItems.splice(state.selectedItems.findIndex(function (selectedItem) {
      return selectedItem.path === item.path;
    }), 1);
  }, _mutations[UNSELECT_ALL_BROWSER_ITEMS] = function (state) {
    state.selectedItems = [];
  }, _mutations[SHOW_CREATE_FOLDER_MODAL] = function (state) {
    state.showCreateFolderModal = true;
  }, _mutations[HIDE_CREATE_FOLDER_MODAL] = function (state) {
    state.showCreateFolderModal = false;
  }, _mutations[SHOW_INFOBAR] = function (state) {
    state.showInfoBar = true;
  }, _mutations[HIDE_INFOBAR] = function (state) {
    state.showInfoBar = false;
  }, _mutations[CHANGE_LIST_VIEW] = function (state, view) {
    state.listView = view;
  }, _mutations[LOAD_FULL_CONTENTS_SUCCESS] = function (state, payload) {
    state.previewItem = payload;
  }, _mutations[SHOW_PREVIEW_MODAL] = function (state) {
    state.showPreviewModal = true;
  }, _mutations[HIDE_PREVIEW_MODAL] = function (state) {
    state.showPreviewModal = false;
  }, _mutations[SET_IS_LOADING] = function (state, payload) {
    state.isLoading = payload;
  }, _mutations[SHOW_RENAME_MODAL] = function (state) {
    state.showRenameModal = true;
  }, _mutations[HIDE_RENAME_MODAL] = function (state) {
    state.showRenameModal = false;
  }, _mutations[SHOW_SHARE_MODAL] = function (state) {
    state.showShareModal = true;
  }, _mutations[HIDE_SHARE_MODAL] = function (state) {
    state.showShareModal = false;
  }, _mutations[INCREASE_GRID_SIZE] = function (state) {
    var currentSizeIndex = gridItemSizes.indexOf(state.gridSize);

    if (currentSizeIndex >= 0 && currentSizeIndex < gridItemSizes.length - 1) {
      // eslint-disable-next-line no-plusplus
      state.gridSize = gridItemSizes[++currentSizeIndex];
    }
  }, _mutations[DECREASE_GRID_SIZE] = function (state) {
    var currentSizeIndex = gridItemSizes.indexOf(state.gridSize);

    if (currentSizeIndex > 0 && currentSizeIndex < gridItemSizes.length) {
      // eslint-disable-next-line no-plusplus
      state.gridSize = gridItemSizes[--currentSizeIndex];
    }
  }, _mutations[SET_SEARCH_QUERY] = function (state, query) {
    state.search = query;
  }, _mutations[SHOW_CONFIRM_DELETE_MODAL] = function (state) {
    state.showConfirmDeleteModal = true;
  }, _mutations[HIDE_CONFIRM_DELETE_MODAL] = function (state) {
    state.showConfirmDeleteModal = false;
  }, _mutations);
  var store = createStore({
    state: state,
    getters: getters,
    actions: actions,
    mutations: mutations,
    plugins: [new VuexPersistence$1(persistedStateOptions).plugin],
    strict: "production" !== 'production'
  });
  var script$7 = {
    name: 'MediaBrowserActionItemRename',
    props: {
      onFocused: {
        type: Function,
        default: function _default() {}
      },
      mainAction: {
        type: Function,
        default: function _default() {}
      },
      closingAction: {
        type: Function,
        default: function _default() {}
      }
    },
    methods: {
      openRenameModal: function openRenameModal() {
        this.mainAction();
      },
      hideActions: function hideActions() {
        this.closingAction();
      },
      focused: function focused(bool) {
        this.onFocused(bool);
      }
    }
  };

  var _hoisted_1$7 = /*#__PURE__*/createBaseVNode("span", {
    class: "image-browser-action fa fa-i-cursor",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_2$6 = {
    class: "action-text"
  };

  function render$7(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("button", {
      ref: "actionRenameButton",
      type: "button",
      class: "action-rename",
      onClick: _cache[0] || (_cache[0] = withModifiers(function ($event) {
        return $options.openRenameModal();
      }, ["stop"])),
      onKeyup: [_cache[1] || (_cache[1] = withKeys(function ($event) {
        return $options.openRenameModal();
      }, ["enter"])), _cache[2] || (_cache[2] = withKeys(function ($event) {
        return $options.openRenameModal();
      }, ["space"])), _cache[5] || (_cache[5] = withKeys(function ($event) {
        return $options.hideActions();
      }, ["esc"]))],
      onFocus: _cache[3] || (_cache[3] = function ($event) {
        return $options.focused(true);
      }),
      onBlur: _cache[4] || (_cache[4] = function ($event) {
        return $options.focused(false);
      })
    }, [_hoisted_1$7, createBaseVNode("span", _hoisted_2$6, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_RENAME')), 1
    /* TEXT */
    )], 544
    /* HYDRATE_EVENTS, NEED_PATCH */
    );
  }

  script$7.render = render$7;
  script$7.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/rename.vue";
  var script$6 = {
    name: 'MediaBrowserActionItemToggle',
    props: {
      mainAction: {
        type: Function,
        default: function _default() {}
      }
    },
    emits: ['on-focused'],
    methods: {
      openActions: function openActions() {
        this.mainAction();
      },
      focused: function focused(bool) {
        this.$emit('on-focused', bool);
      }
    }
  };
  var _hoisted_1$6 = ["aria-label", "title"];

  function render$6(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("button", {
      type: "button",
      class: "action-toggle",
      "aria-label": _ctx.sprintf('COM_MEDIA_MANAGE_ITEM', _ctx.$parent.$props.item.name),
      title: _ctx.sprintf('COM_MEDIA_MANAGE_ITEM', _ctx.$parent.$props.item.name),
      onKeyup: [_cache[1] || (_cache[1] = withKeys(function ($event) {
        return $options.openActions();
      }, ["enter"])), _cache[4] || (_cache[4] = withKeys(function ($event) {
        return $options.openActions();
      }, ["space"]))],
      onFocus: _cache[2] || (_cache[2] = function ($event) {
        return $options.focused(true);
      }),
      onBlur: _cache[3] || (_cache[3] = function ($event) {
        return $options.focused(false);
      })
    }, [createBaseVNode("span", {
      class: "image-browser-action icon-ellipsis-h",
      "aria-hidden": "true",
      onClick: _cache[0] || (_cache[0] = withModifiers(function ($event) {
        return $options.openActions();
      }, ["stop"]))
    })], 40
    /* PROPS, HYDRATE_EVENTS */
    , _hoisted_1$6);
  }

  script$6.render = render$6;
  script$6.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/toggle.vue";
  var script$5 = {
    name: 'MediaBrowserActionItemPreview',
    props: {
      onFocused: {
        type: Function,
        default: function _default() {}
      },
      mainAction: {
        type: Function,
        default: function _default() {}
      },
      closingAction: {
        type: Function,
        default: function _default() {}
      }
    },
    methods: {
      openPreview: function openPreview() {
        this.mainAction();
      },
      hideActions: function hideActions() {
        this.closingAction();
      },
      focused: function focused(bool) {
        this.onFocused(bool);
      }
    }
  };

  var _hoisted_1$5 = /*#__PURE__*/createBaseVNode("span", {
    class: "image-browser-action icon-search-plus",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_2$5 = {
    class: "action-text"
  };

  function render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("button", {
      type: "button",
      class: "action-preview",
      onClick: _cache[0] || (_cache[0] = withModifiers(function ($event) {
        return $options.openPreview();
      }, ["stop"])),
      onKeyup: [_cache[1] || (_cache[1] = withKeys(function ($event) {
        return $options.openPreview();
      }, ["enter"])), _cache[2] || (_cache[2] = withKeys(function ($event) {
        return $options.openPreview();
      }, ["space"])), _cache[5] || (_cache[5] = withKeys(function ($event) {
        return $options.hideActions();
      }, ["esc"]))],
      onFocus: _cache[3] || (_cache[3] = function ($event) {
        return $options.focused(true);
      }),
      onBlur: _cache[4] || (_cache[4] = function ($event) {
        return $options.focused(false);
      })
    }, [_hoisted_1$5, createBaseVNode("span", _hoisted_2$5, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_PREVIEW')), 1
    /* TEXT */
    )], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$5.render = render$5;
  script$5.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/preview.vue";
  var script$4 = {
    name: 'MediaBrowserActionItemDownload',
    props: {
      onFocused: {
        type: Function,
        default: function _default() {}
      },
      mainAction: {
        type: Function,
        default: function _default() {}
      },
      closingAction: {
        type: Function,
        default: function _default() {}
      }
    },
    methods: {
      download: function download() {
        this.mainAction();
      },
      hideActions: function hideActions() {
        this.closingAction();
      },
      focused: function focused(bool) {
        this.onFocused(bool);
      }
    }
  };

  var _hoisted_1$4 = /*#__PURE__*/createBaseVNode("span", {
    class: "image-browser-action icon-download",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_2$4 = {
    class: "action-text"
  };

  function render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("button", {
      type: "button",
      class: "action-download",
      onKeyup: [_cache[0] || (_cache[0] = withKeys(function ($event) {
        return $options.download();
      }, ["enter"])), _cache[1] || (_cache[1] = withKeys(function ($event) {
        return $options.download();
      }, ["space"])), _cache[5] || (_cache[5] = withKeys(function ($event) {
        return $options.hideActions();
      }, ["esc"]))],
      onClick: _cache[2] || (_cache[2] = withModifiers(function ($event) {
        return $options.download();
      }, ["stop"])),
      onFocus: _cache[3] || (_cache[3] = function ($event) {
        return $options.focused(true);
      }),
      onBlur: _cache[4] || (_cache[4] = function ($event) {
        return $options.focused(false);
      })
    }, [_hoisted_1$4, createBaseVNode("span", _hoisted_2$4, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_DOWNLOAD')), 1
    /* TEXT */
    )], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$4.render = render$4;
  script$4.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/download.vue";
  var script$3 = {
    name: 'MediaBrowserActionItemShare',
    props: {
      onFocused: {
        type: Function,
        default: function _default() {}
      },
      mainAction: {
        type: Function,
        default: function _default() {}
      },
      closingAction: {
        type: Function,
        default: function _default() {}
      }
    },
    methods: {
      openShareUrlModal: function openShareUrlModal() {
        this.mainAction();
      },
      hideActions: function hideActions() {
        this.closingAction();
      },
      focused: function focused(bool) {
        this.onFocused(bool);
      }
    }
  };

  var _hoisted_1$3 = /*#__PURE__*/createBaseVNode("span", {
    class: "image-browser-action icon-link",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_2$3 = {
    class: "action-text"
  };

  function render$3(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("button", {
      type: "button",
      class: "action-url",
      onClick: _cache[0] || (_cache[0] = withModifiers(function ($event) {
        return $options.openShareUrlModal();
      }, ["stop"])),
      onKeyup: [_cache[1] || (_cache[1] = withKeys(function ($event) {
        return $options.openShareUrlModal();
      }, ["enter"])), _cache[2] || (_cache[2] = withKeys(function ($event) {
        return $options.openShareUrlModal();
      }, ["space"])), _cache[5] || (_cache[5] = withKeys(function ($event) {
        return $options.hideActions();
      }, ["esc"]))],
      onFocus: _cache[3] || (_cache[3] = function ($event) {
        return $options.focused(true);
      }),
      onBlur: _cache[4] || (_cache[4] = function ($event) {
        return $options.focused(false);
      })
    }, [_hoisted_1$3, createBaseVNode("span", _hoisted_2$3, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_SHARE')), 1
    /* TEXT */
    )], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$3.render = render$3;
  script$3.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/share.vue";
  var script$2 = {
    name: 'MediaBrowserActionItemDelete',
    props: {
      onFocused: {
        type: Function,
        default: function _default() {}
      },
      mainAction: {
        type: Function,
        default: function _default() {}
      },
      closingAction: {
        type: Function,
        default: function _default() {}
      }
    },
    methods: {
      openConfirmDeleteModal: function openConfirmDeleteModal() {
        this.mainAction();
      },
      hideActions: function hideActions() {
        this.hideActions();
      },
      focused: function focused(bool) {
        this.onFocused(bool);
      }
    }
  };

  var _hoisted_1$2 = /*#__PURE__*/createBaseVNode("span", {
    class: "image-browser-action icon-trash",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_2$2 = {
    class: "action-text"
  };

  function render$2(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("button", {
      type: "button",
      class: "action-delete",
      onKeyup: [_cache[0] || (_cache[0] = withKeys(function ($event) {
        return $options.openConfirmDeleteModal();
      }, ["enter"])), _cache[1] || (_cache[1] = withKeys(function ($event) {
        return $options.openConfirmDeleteModal();
      }, ["space"])), _cache[4] || (_cache[4] = withKeys(function ($event) {
        return $options.hideActions();
      }, ["esc"]))],
      onFocus: _cache[2] || (_cache[2] = function ($event) {
        return $options.focused(true);
      }),
      onBlur: _cache[3] || (_cache[3] = function ($event) {
        return $options.focused(false);
      }),
      onClick: _cache[5] || (_cache[5] = withModifiers(function ($event) {
        return $options.openConfirmDeleteModal();
      }, ["stop"]))
    }, [_hoisted_1$2, createBaseVNode("span", _hoisted_2$2, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_DELETE')), 1
    /* TEXT */
    )], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$2.render = render$2;
  script$2.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/delete.vue";
  var script$1 = {
    name: 'MediaBrowserActionItemEdit',
    props: {
      onFocused: {
        type: Function,
        default: function _default() {}
      },
      mainAction: {
        type: Function,
        default: function _default() {}
      },
      closingAction: {
        type: Function,
        default: function _default() {}
      }
    },
    methods: {
      openRenameModal: function openRenameModal() {
        this.mainAction();
      },
      hideActions: function hideActions() {
        this.closingAction();
      },
      focused: function focused(bool) {
        this.onFocused(bool);
      },
      editItem: function editItem() {
        this.mainAction();
      }
    }
  };

  var _hoisted_1$1 = /*#__PURE__*/createBaseVNode("span", {
    class: "image-browser-action icon-pencil-alt",
    "aria-hidden": "true"
  }, null, -1
  /* HOISTED */
  );

  var _hoisted_2$1 = {
    class: "action-text"
  };

  function render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return openBlock(), createElementBlock("button", {
      type: "button",
      class: "action-edit",
      onKeyup: [_cache[0] || (_cache[0] = withKeys(function ($event) {
        return $options.editItem();
      }, ["enter"])), _cache[1] || (_cache[1] = withKeys(function ($event) {
        return $options.editItem();
      }, ["space"])), _cache[5] || (_cache[5] = withKeys(function ($event) {
        return $options.hideActions();
      }, ["esc"]))],
      onClick: _cache[2] || (_cache[2] = withModifiers(function ($event) {
        return $options.editItem();
      }, ["stop"])),
      onFocus: _cache[3] || (_cache[3] = function ($event) {
        return $options.focused(true);
      }),
      onBlur: _cache[4] || (_cache[4] = function ($event) {
        return $options.focused(false);
      })
    }, [_hoisted_1$1, createBaseVNode("span", _hoisted_2$1, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_EDIT')), 1
    /* TEXT */
    )], 32
    /* HYDRATE_EVENTS */
    );
  }

  script$1.render = render$1;
  script$1.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/edit.vue";
  var script = {
    name: 'MediaBrowserActionItemsContainer',
    props: {
      item: {
        type: Object,
        default: function _default() {}
      },
      edit: {
        type: Function,
        default: function _default() {}
      },
      previewable: {
        type: Boolean,
        default: false
      },
      downloadable: {
        type: Boolean,
        default: false
      },
      shareable: {
        type: Boolean,
        default: false
      }
    },
    emits: ['toggle-settings'],
    data: function data() {
      return {
        showActions: false
      };
    },
    computed: {
      canEdit: function canEdit() {
        return api.canEdit && (typeof this.item.canEdit !== 'undefined' ? this.item.canEdit : true);
      },
      canDelete: function canDelete() {
        return api.canDelete && (typeof this.item.canDelete !== 'undefined' ? this.item.canDelete : true);
      },
      canOpenEditView: function canOpenEditView() {
        return ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
      }
    },
    watch: {
      // eslint-disable-next-line
      "$store.state.showRenameModal": function $storeStateShowRenameModal(show) {
        var _this21 = this;

        if (!show && this.$refs.actionToggle && this.$store.state.selectedItems.find(function (item) {
          return item.name === _this21.item.name;
        }) !== undefined) {
          this.$refs.actionToggle.$el.focus();
        }
      }
    },
    methods: {
      /* Hide actions dropdown */
      hideActions: function hideActions() {
        this.showActions = false;
        this.$parent.$parent.$data.actionsActive = false;
      },

      /* Preview an item */
      openPreview: function openPreview() {
        this.$store.commit(SHOW_PREVIEW_MODAL);
        this.$store.dispatch('getFullContents', this.item);
      },

      /* Download an item */
      download: function download() {
        this.$store.dispatch('download', this.item);
      },

      /* Opening confirm delete modal */
      openConfirmDeleteModal: function openConfirmDeleteModal() {
        this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
        this.$store.commit(SELECT_BROWSER_ITEM, this.item);
        this.$store.commit(SHOW_CONFIRM_DELETE_MODAL);
      },

      /* Rename an item */
      openRenameModal: function openRenameModal() {
        this.hideActions();
        this.$store.commit(SELECT_BROWSER_ITEM, this.item);
        this.$store.commit(SHOW_RENAME_MODAL);
      },

      /* Open modal for share url */
      openShareUrlModal: function openShareUrlModal() {
        this.$store.commit(SELECT_BROWSER_ITEM, this.item);
        this.$store.commit(SHOW_SHARE_MODAL);
      },

      /* Open actions dropdown */
      openActions: function openActions() {
        this.showActions = true;
        this.$parent.$parent.$data.actionsActive = true;
        var buttons = [].concat(this.$el.parentElement.querySelectorAll('.media-browser-actions-list button'));

        if (buttons.length) {
          buttons.forEach(function (button, i) {
            if (i === 0) {
              button.tabIndex = 0;
            } else {
              button.tabIndex = -1;
            }
          });
          buttons[0].focus();
        }
      },

      /* Open actions dropdown and focus on last element */
      openLastActions: function openLastActions() {
        this.showActions = true;
        this.$parent.$parent.$data.actionsActive = true;
        var buttons = [].concat(this.$el.parentElement.querySelectorAll('.media-browser-actions-list button'));

        if (buttons.length) {
          buttons.forEach(function (button, i) {
            if (i === buttons.length) {
              button.tabIndex = 0;
            } else {
              button.tabIndex = -1;
            }
          });
          this.$nextTick(function () {
            return buttons[buttons.length - 1].focus();
          });
        }
      },

      /* Focus on the next item or go to the beginning again */
      focusNext: function focusNext(event) {
        var active = event.target;
        var buttons = [].concat(active.parentElement.querySelectorAll('button'));
        var lastchild = buttons[buttons.length - 1];
        active.tabIndex = -1;

        if (active === lastchild) {
          buttons[0].focus();
          buttons[0].tabIndex = 0;
        } else {
          active.nextElementSibling.focus();
          active.nextElementSibling.tabIndex = 0;
        }
      },

      /* Focus on the previous item or go to the end again */
      focusPrev: function focusPrev(event) {
        var active = event.target;
        var buttons = [].concat(active.parentElement.querySelectorAll('button'));
        var firstchild = buttons[0];
        active.tabIndex = -1;

        if (active === firstchild) {
          buttons[buttons.length - 1].focus();
          buttons[buttons.length - 1].tabIndex = 0;
        } else {
          active.previousElementSibling.focus();
          active.previousElementSibling.tabIndex = 0;
        }
      },

      /* Focus on the first item */
      focusFirst: function focusFirst(event) {
        var active = event.target;
        var buttons = [].concat(active.parentElement.querySelectorAll('button'));
        buttons[0].focus();
        buttons.forEach(function (button, i) {
          if (i === 0) {
            button.tabIndex = 0;
          } else {
            button.tabIndex = -1;
          }
        });
      },

      /* Focus on the last item */
      focusLast: function focusLast(event) {
        var active = event.target;
        var buttons = [].concat(active.parentElement.querySelectorAll('button'));
        buttons[buttons.length - 1].focus();
        buttons.forEach(function (button, i) {
          if (i === buttons.length) {
            button.tabIndex = 0;
          } else {
            button.tabIndex = -1;
          }
        });
      },
      editItem: function editItem() {
        this.edit();
      },
      focused: function focused(bool) {
        this.$emit('toggle-settings', bool);
      }
    }
  };
  var _hoisted_1 = ["aria-label", "title"];
  var _hoisted_2 = ["aria-label"];
  var _hoisted_3 = {
    "aria-hidden": "true",
    class: "media-browser-actions-item-name"
  };

  function render(_ctx, _cache, $props, $setup, $data, $options) {
    var _component_media_browser_action_item_toggle = resolveComponent("media-browser-action-item-toggle");

    var _component_media_browser_action_item_preview = resolveComponent("media-browser-action-item-preview");

    var _component_media_browser_action_item_download = resolveComponent("media-browser-action-item-download");

    var _component_media_browser_action_item_rename = resolveComponent("media-browser-action-item-rename");

    var _component_media_browser_action_item_edit = resolveComponent("media-browser-action-item-edit");

    var _component_media_browser_action_item_share = resolveComponent("media-browser-action-item-share");

    var _component_media_browser_action_item_delete = resolveComponent("media-browser-action-item-delete");

    return openBlock(), createElementBlock(Fragment, null, [createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      tabindex: "0",
      onFocusin: _cache[0] || (_cache[0] = function ($event) {
        return $options.focused(true);
      }),
      onFocusout: _cache[1] || (_cache[1] = function ($event) {
        return $options.focused(false);
      })
    }, null, 40
    /* PROPS, HYDRATE_EVENTS */
    , _hoisted_1), createBaseVNode("div", {
      class: normalizeClass(["media-browser-actions", {
        active: $data.showActions
      }])
    }, [createVNode(_component_media_browser_action_item_toggle, {
      ref: "actionToggle",
      "main-action": $options.openActions,
      onOnFocused: $options.focused,
      onKeyup: [_cache[2] || (_cache[2] = withKeys(function ($event) {
        return $options.openLastActions();
      }, ["up"])), _cache[3] || (_cache[3] = withKeys(function ($event) {
        return $options.openActions();
      }, ["down"])), _cache[4] || (_cache[4] = withKeys(function ($event) {
        return $options.openLastActions();
      }, ["end"])), _cache[5] || (_cache[5] = withKeys(function ($event) {
        return $options.openActions();
      }, ["home"]))],
      onKeydown: [_cache[6] || (_cache[6] = withKeys(withModifiers(function () {}, ["prevent"]), ["up"])), _cache[7] || (_cache[7] = withKeys(withModifiers(function () {}, ["prevent"]), ["down"])), _cache[8] || (_cache[8] = withKeys(withModifiers(function () {}, ["prevent"]), ["home"])), _cache[9] || (_cache[9] = withKeys(withModifiers(function () {}, ["prevent"]), ["end"]))]
    }, null, 8
    /* PROPS */
    , ["main-action", "onOnFocused"]), $data.showActions ? (openBlock(), createElementBlock("div", {
      key: 0,
      ref: "actionList",
      class: "media-browser-actions-list",
      role: "toolbar",
      "aria-orientation": "vertical",
      "aria-label": _ctx.sprintf('COM_MEDIA_ACTIONS_TOOLBAR_LABEL', _ctx.$parent.$props.item.name)
    }, [createBaseVNode("span", _hoisted_3, [createBaseVNode("strong", null, toDisplayString(_ctx.$parent.$props.item.name), 1
    /* TEXT */
    )]), $props.previewable ? (openBlock(), createBlock(_component_media_browser_action_item_preview, {
      key: 0,
      ref: "actionPreview",
      "on-focused": $options.focused,
      "main-action": $options.openPreview,
      "closing-action": $options.hideActions,
      onKeydown: [_cache[10] || (_cache[10] = withKeys(withModifiers(function () {}, ["prevent"]), ["up"])), _cache[11] || (_cache[11] = withKeys(withModifiers(function () {}, ["prevent"]), ["down"])), _cache[12] || (_cache[12] = withKeys(withModifiers(function () {}, ["prevent"]), ["home"])), _cache[13] || (_cache[13] = withKeys(withModifiers(function () {}, ["prevent"]), ["end"])), withKeys($options.hideActions, ["tab"])],
      onKeyup: [withKeys($options.focusPrev, ["up"]), withKeys($options.focusNext, ["down"]), withKeys($options.focusLast, ["end"]), withKeys($options.focusFirst, ["home"]), withKeys($options.hideActions, ["esc"])]
    }, null, 8
    /* PROPS */
    , ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"])) : createCommentVNode("v-if", true), $props.downloadable ? (openBlock(), createBlock(_component_media_browser_action_item_download, {
      key: 1,
      ref: "actionDownload",
      "on-focused": $options.focused,
      "main-action": $options.download,
      "closing-action": $options.hideActions,
      onKeydown: [_cache[14] || (_cache[14] = withKeys(withModifiers(function () {}, ["prevent"]), ["up"])), _cache[15] || (_cache[15] = withKeys(withModifiers(function () {}, ["prevent"]), ["down"])), withKeys($options.hideActions, ["tab"]), _cache[16] || (_cache[16] = withKeys(withModifiers(function () {}, ["prevent"]), ["home"])), _cache[17] || (_cache[17] = withKeys(withModifiers(function () {}, ["prevent"]), ["end"]))],
      onKeyup: [withKeys($options.focusPrev, ["up"]), withKeys($options.focusNext, ["down"]), withKeys($options.hideActions, ["esc"]), withKeys($options.focusLast, ["end"]), withKeys($options.focusFirst, ["home"])]
    }, null, 8
    /* PROPS */
    , ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"])) : createCommentVNode("v-if", true), $options.canEdit ? (openBlock(), createBlock(_component_media_browser_action_item_rename, {
      key: 2,
      ref: "actionRename",
      "on-focused": $options.focused,
      "main-action": $options.openRenameModal,
      "closing-action": $options.hideActions,
      onKeydown: [_cache[18] || (_cache[18] = withKeys(withModifiers(function () {}, ["prevent"]), ["up"])), _cache[19] || (_cache[19] = withKeys(withModifiers(function () {}, ["prevent"]), ["down"])), withKeys($options.hideActions, ["tab"]), _cache[20] || (_cache[20] = withKeys(withModifiers(function () {}, ["prevent"]), ["home"])), _cache[21] || (_cache[21] = withKeys(withModifiers(function () {}, ["prevent"]), ["end"]))],
      onKeyup: [withKeys($options.focusPrev, ["up"]), withKeys($options.focusNext, ["down"]), withKeys($options.hideActions, ["esc"]), withKeys($options.focusLast, ["end"]), withKeys($options.focusFirst, ["home"])]
    }, null, 8
    /* PROPS */
    , ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"])) : createCommentVNode("v-if", true), $options.canEdit && $options.canOpenEditView ? (openBlock(), createBlock(_component_media_browser_action_item_edit, {
      key: 3,
      ref: "actionEdit",
      "on-focused": $options.focused,
      "main-action": $options.editItem,
      "closing-action": $options.hideActions,
      onKeydown: [_cache[22] || (_cache[22] = withKeys(withModifiers(function () {}, ["prevent"]), ["up"])), _cache[23] || (_cache[23] = withKeys(withModifiers(function () {}, ["prevent"]), ["down"])), withKeys($options.hideActions, ["tab"]), _cache[24] || (_cache[24] = withKeys(withModifiers(function () {}, ["prevent"]), ["home"])), _cache[25] || (_cache[25] = withKeys(withModifiers(function () {}, ["prevent"]), ["end"]))],
      onKeyup: [withKeys($options.focusPrev, ["up"]), withKeys($options.focusNext, ["down"]), withKeys($options.hideActions, ["esc"]), withKeys($options.focusLast, ["end"]), withKeys($options.focusFirst, ["home"])]
    }, null, 8
    /* PROPS */
    , ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"])) : createCommentVNode("v-if", true), $props.shareable ? (openBlock(), createBlock(_component_media_browser_action_item_share, {
      key: 4,
      ref: "actionShare",
      "on-focused": $options.focused,
      "main-action": $options.openShareUrlModal,
      "closing-action": $options.hideActions,
      onKeydown: [_cache[26] || (_cache[26] = withKeys(withModifiers(function () {}, ["prevent"]), ["up"])), _cache[27] || (_cache[27] = withKeys(withModifiers(function () {}, ["prevent"]), ["down"])), withKeys($options.hideActions, ["tab"]), _cache[28] || (_cache[28] = withKeys(withModifiers(function () {}, ["prevent"]), ["home"])), _cache[29] || (_cache[29] = withKeys(withModifiers(function () {}, ["prevent"]), ["end"]))],
      onKeyup: [withKeys($options.focusPrev, ["up"]), withKeys($options.focusNext, ["down"]), withKeys($options.hideActions, ["esc"]), withKeys($options.focusLast, ["end"]), withKeys($options.focusFirst, ["home"])]
    }, null, 8
    /* PROPS */
    , ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"])) : createCommentVNode("v-if", true), $options.canDelete ? (openBlock(), createBlock(_component_media_browser_action_item_delete, {
      key: 5,
      ref: "actionDelete",
      "on-focused": $options.focused,
      "main-action": $options.openConfirmDeleteModal,
      "hide-actions": $options.hideActions,
      onKeydown: [_cache[30] || (_cache[30] = withKeys(withModifiers(function () {}, ["prevent"]), ["up"])), _cache[31] || (_cache[31] = withKeys(withModifiers(function () {}, ["prevent"]), ["down"])), withKeys($options.hideActions, ["tab"]), _cache[32] || (_cache[32] = withKeys(withModifiers(function () {}, ["prevent"]), ["home"])), _cache[33] || (_cache[33] = withKeys(withModifiers(function () {}, ["prevent"]), ["end"]))],
      onKeyup: [withKeys($options.focusPrev, ["up"]), withKeys($options.focusNext, ["down"]), withKeys($options.hideActions, ["esc"]), withKeys($options.focusLast, ["end"]), withKeys($options.focusFirst, ["home"])]
    }, null, 8
    /* PROPS */
    , ["on-focused", "main-action", "hide-actions", "onKeyup", "onKeydown"])) : createCommentVNode("v-if", true)], 8
    /* PROPS */
    , _hoisted_2)) : createCommentVNode("v-if", true)], 2
    /* CLASS */
    )], 64
    /* STABLE_FRAGMENT */
    );
  }

  script.render = render;
  script.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/actionItemsContainer.vue";
  window.MediaManager = window.MediaManager || {}; // Register the media manager event bus

  window.MediaManager.Event = new Event$1(); // Create the Vue app instance

  createApp(script$t).use(store).use(Translate) // Register the vue components
  .component('MediaDrive', script$r).component('MediaDisk', script$s).component('MediaTree', script$q).component('MediaToolbar', script$p).component('MediaBreadcrumb', script$o).component('MediaBrowser', script$n).component('MediaBrowserItem', BrowserItem).component('MediaBrowserItemRow', script$g).component('MediaModal', script$f).component('MediaCreateFolderModal', script$e).component('MediaPreviewModal', script$d).component('MediaRenameModal', script$c).component('MediaShareModal', script$b).component('MediaConfirmDeleteModal', script$a).component('MediaInfobar', script$9).component('MediaUpload', script$8).component('MediaBrowserActionItemToggle', script$6).component('MediaBrowserActionItemPreview', script$5).component('MediaBrowserActionItemDownload', script$4).component('MediaBrowserActionItemRename', script$7).component('MediaBrowserActionItemShare', script$3).component('MediaBrowserActionItemDelete', script$2).component('MediaBrowserActionItemEdit', script$1).component('MediaBrowserActionItemsContainer', script).mount('#com-media');

  return mediaManager;

})();
