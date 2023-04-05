/**
 * Make a map and return a function for checking if a key
 * is in that map.
 * IMPORTANT: all calls of this function must be prefixed with
 * \/\*#\_\_PURE\_\_\*\/
 * So that rollup can tree-shake them if necessary.
 */
function makeMap(str, expectsLowerCase) {
  const map = Object.create(null);
  const list = str.split(',');

  for (let i = 0; i < list.length; i++) {
    map[list[i]] = true;
  }

  return expectsLowerCase ? val => !!map[val.toLowerCase()] : val => !!map[val];
}

function normalizeStyle(value) {
  if (isArray(value)) {
    const res = {};

    for (let i = 0; i < value.length; i++) {
      const item = value[i];
      const normalized = isString(item) ? parseStringStyle(item) : normalizeStyle(item);

      if (normalized) {
        for (const key in normalized) {
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

const listDelimiterRE = /;(?![^(]*\))/g;
const propertyDelimiterRE = /:([^]+)/;
const styleCommentRE = /\/\*.*?\*\//gs;

function parseStringStyle(cssText) {
  const ret = {};
  cssText.replace(styleCommentRE, '').split(listDelimiterRE).forEach(item => {
    if (item) {
      const tmp = item.split(propertyDelimiterRE);
      tmp.length > 1 && (ret[tmp[0].trim()] = tmp[1].trim());
    }
  });
  return ret;
}

function normalizeClass(value) {
  let res = '';

  if (isString(value)) {
    res = value;
  } else if (isArray(value)) {
    for (let i = 0; i < value.length; i++) {
      const normalized = normalizeClass(value[i]);

      if (normalized) {
        res += normalized + ' ';
      }
    }
  } else if (isObject$1(value)) {
    for (const name in value) {
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

const specialBooleanAttrs = `itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly`;
const isSpecialBooleanAttr = /*#__PURE__*/makeMap(specialBooleanAttrs);
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


const toDisplayString = val => {
  return isString(val) ? val : val == null ? '' : isArray(val) || isObject$1(val) && (val.toString === objectToString || !isFunction(val.toString)) ? JSON.stringify(val, replacer, 2) : String(val);
};

const replacer = (_key, val) => {
  // can't use isRef here since @vue/shared has no deps
  if (val && val.__v_isRef) {
    return replacer(_key, val.value);
  } else if (isMap(val)) {
    return {
      [`Map(${val.size})`]: [...val.entries()].reduce((entries, _ref) => {
        let [key, val] = _ref;
        entries[`${key} =>`] = val;
        return entries;
      }, {})
    };
  } else if (isSet(val)) {
    return {
      [`Set(${val.size})`]: [...val.values()]
    };
  } else if (isObject$1(val) && !isArray(val) && !isPlainObject(val)) {
    return String(val);
  }

  return val;
};

const EMPTY_OBJ = {};
const EMPTY_ARR = [];

const NOOP = () => {};
/**
 * Always return false.
 */


const NO = () => false;

const onRE = /^on[^a-z]/;

const isOn = key => onRE.test(key);

const isModelListener = key => key.startsWith('onUpdate:');

const extend = Object.assign;

const remove = (arr, el) => {
  const i = arr.indexOf(el);

  if (i > -1) {
    arr.splice(i, 1);
  }
};

const hasOwnProperty = Object.prototype.hasOwnProperty;

const hasOwn = (val, key) => hasOwnProperty.call(val, key);

const isArray = Array.isArray;

const isMap = val => toTypeString(val) === '[object Map]';

const isSet = val => toTypeString(val) === '[object Set]';

const isFunction = val => typeof val === 'function';

const isString = val => typeof val === 'string';

const isSymbol = val => typeof val === 'symbol';

const isObject$1 = val => val !== null && typeof val === 'object';

const isPromise$1 = val => {
  return isObject$1(val) && isFunction(val.then) && isFunction(val.catch);
};

const objectToString = Object.prototype.toString;

const toTypeString = value => objectToString.call(value);

const toRawType = value => {
  // extract "RawType" from strings like "[object RawType]"
  return toTypeString(value).slice(8, -1);
};

const isPlainObject = val => toTypeString(val) === '[object Object]';

const isIntegerKey = key => isString(key) && key !== 'NaN' && key[0] !== '-' && '' + parseInt(key, 10) === key;

const isReservedProp = /*#__PURE__*/makeMap( // the leading comma is intentional so empty string "" is also included
',key,ref,ref_for,ref_key,' + 'onVnodeBeforeMount,onVnodeMounted,' + 'onVnodeBeforeUpdate,onVnodeUpdated,' + 'onVnodeBeforeUnmount,onVnodeUnmounted');

const cacheStringFunction = fn => {
  const cache = Object.create(null);
  return str => {
    const hit = cache[str];
    return hit || (cache[str] = fn(str));
  };
};

const camelizeRE = /-(\w)/g;
/**
 * @private
 */

const camelize = cacheStringFunction(str => {
  return str.replace(camelizeRE, (_, c) => c ? c.toUpperCase() : '');
});
const hyphenateRE = /\B([A-Z])/g;
/**
 * @private
 */

const hyphenate = cacheStringFunction(str => str.replace(hyphenateRE, '-$1').toLowerCase());
/**
 * @private
 */

const capitalize = cacheStringFunction(str => str.charAt(0).toUpperCase() + str.slice(1));
/**
 * @private
 */

const toHandlerKey = cacheStringFunction(str => str ? `on${capitalize(str)}` : ``); // compare whether a value has changed, accounting for NaN.

const hasChanged = (value, oldValue) => !Object.is(value, oldValue);

const invokeArrayFns = (fns, arg) => {
  for (let i = 0; i < fns.length; i++) {
    fns[i](arg);
  }
};

const def = (obj, key, value) => {
  Object.defineProperty(obj, key, {
    configurable: true,
    enumerable: false,
    value
  });
};

const toNumber = val => {
  const n = parseFloat(val);
  return isNaN(n) ? val : n;
};

let _globalThis;

const getGlobalThis = () => {
  return _globalThis || (_globalThis = typeof globalThis !== 'undefined' ? globalThis : typeof self !== 'undefined' ? self : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : {});
};

let activeEffectScope;

class EffectScope {
  constructor(detached) {
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

  run(fn) {
    if (this.active) {
      const currentEffectScope = activeEffectScope;

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


  on() {
    activeEffectScope = this;
  }
  /**
   * This should only be called on non-detached scopes
   * @internal
   */


  off() {
    activeEffectScope = this.parent;
  }

  stop(fromParent) {
    if (this.active) {
      let i, l;

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
        const last = this.parent.scopes.pop();

        if (last && last !== this) {
          this.parent.scopes[this.index] = last;
          last.index = this.index;
        }
      }

      this.parent = undefined;
      this.active = false;
    }
  }

}

function recordEffectScope(effect, scope) {
  if (scope === void 0) {
    scope = activeEffectScope;
  }

  if (scope && scope.active) {
    scope.effects.push(effect);
  }
}

const createDep = effects => {
  const dep = new Set(effects);
  dep.w = 0;
  dep.n = 0;
  return dep;
};

const wasTracked = dep => (dep.w & trackOpBit) > 0;

const newTracked = dep => (dep.n & trackOpBit) > 0;

const initDepMarkers = _ref => {
  let {
    deps
  } = _ref;

  if (deps.length) {
    for (let i = 0; i < deps.length; i++) {
      deps[i].w |= trackOpBit; // set was tracked
    }
  }
};

const finalizeDepMarkers = effect => {
  const {
    deps
  } = effect;

  if (deps.length) {
    let ptr = 0;

    for (let i = 0; i < deps.length; i++) {
      const dep = deps[i];

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

const targetMap = new WeakMap(); // The number of effects currently being tracked recursively.

let effectTrackDepth = 0;
let trackOpBit = 1;
/**
 * The bitwise track markers support at most 30 levels of recursion.
 * This value is chosen to enable modern JS engines to use a SMI on all platforms.
 * When recursion depth is greater, fall back to using a full cleanup.
 */

const maxMarkerBits = 30;
let activeEffect;
const ITERATE_KEY = Symbol('');
const MAP_KEY_ITERATE_KEY = Symbol('');

class ReactiveEffect {
  constructor(fn, scheduler, scope) {
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

  run() {
    if (!this.active) {
      return this.fn();
    }

    let parent = activeEffect;
    let lastShouldTrack = shouldTrack;

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
  }

  stop() {
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
  }

}

function cleanupEffect(effect) {
  const {
    deps
  } = effect;

  if (deps.length) {
    for (let i = 0; i < deps.length; i++) {
      deps[i].delete(effect);
    }

    deps.length = 0;
  }
}

let shouldTrack = true;
const trackStack = [];

function pauseTracking() {
  trackStack.push(shouldTrack);
  shouldTrack = false;
}

function resetTracking() {
  const last = trackStack.pop();
  shouldTrack = last === undefined ? true : last;
}

function track(target, type, key) {
  if (shouldTrack && activeEffect) {
    let depsMap = targetMap.get(target);

    if (!depsMap) {
      targetMap.set(target, depsMap = new Map());
    }

    let dep = depsMap.get(key);

    if (!dep) {
      depsMap.set(key, dep = createDep());
    }
    trackEffects(dep);
  }
}

function trackEffects(dep, debuggerEventExtraInfo) {
  let shouldTrack = false;

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
  const depsMap = targetMap.get(target);

  if (!depsMap) {
    // never been tracked
    return;
  }

  let deps = [];

  if (type === "clear"
  /* TriggerOpTypes.CLEAR */
  ) {
    // collection being cleared
    // trigger all effects for target
    deps = [...depsMap.values()];
  } else if (key === 'length' && isArray(target)) {
    const newLength = toNumber(newValue);
    depsMap.forEach((dep, key) => {
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
    const effects = [];

    for (const dep of deps) {
      if (dep) {
        effects.push(...dep);
      }
    }

    {
      triggerEffects(createDep(effects));
    }
  }
}

function triggerEffects(dep, debuggerEventExtraInfo) {
  // spread into array for stabilization
  const effects = isArray(dep) ? dep : [...dep];

  for (const effect of effects) {
    if (effect.computed) {
      triggerEffect(effect);
    }
  }

  for (const effect of effects) {
    if (!effect.computed) {
      triggerEffect(effect);
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

const isNonTrackableKeys = /*#__PURE__*/makeMap(`__proto__,__v_isRef,__isVue`);
const builtInSymbols = new Set( /*#__PURE__*/Object.getOwnPropertyNames(Symbol) // ios10.x Object.getOwnPropertyNames(Symbol) can enumerate 'arguments' and 'caller'
// but accessing them on Symbol leads to TypeError because Symbol is a strict mode
// function
.filter(key => key !== 'arguments' && key !== 'caller').map(key => Symbol[key]).filter(isSymbol));
const get = /*#__PURE__*/createGetter();
const shallowGet = /*#__PURE__*/createGetter(false, true);
const readonlyGet = /*#__PURE__*/createGetter(true);
const arrayInstrumentations = /*#__PURE__*/createArrayInstrumentations();

function createArrayInstrumentations() {
  const instrumentations = {};
  ['includes', 'indexOf', 'lastIndexOf'].forEach(key => {
    instrumentations[key] = function () {
      const arr = toRaw(this);

      for (let i = 0, l = this.length; i < l; i++) {
        track(arr, "get"
        /* TrackOpTypes.GET */
        , i + '');
      } // we run the method using the original args first (which may be reactive)


      for (var _len2 = arguments.length, args = new Array(_len2), _key3 = 0; _key3 < _len2; _key3++) {
        args[_key3] = arguments[_key3];
      }

      const res = arr[key](...args);

      if (res === -1 || res === false) {
        // if that didn't work, run it again using raw values.
        return arr[key](...args.map(toRaw));
      } else {
        return res;
      }
    };
  });
  ['push', 'pop', 'shift', 'unshift', 'splice'].forEach(key => {
    instrumentations[key] = function () {
      pauseTracking();

      for (var _len3 = arguments.length, args = new Array(_len3), _key4 = 0; _key4 < _len3; _key4++) {
        args[_key4] = arguments[_key4];
      }

      const res = toRaw(this)[key].apply(this, args);
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

    const targetIsArray = isArray(target);

    if (!isReadonly && targetIsArray && hasOwn(arrayInstrumentations, key)) {
      return Reflect.get(arrayInstrumentations, key, receiver);
    }

    const res = Reflect.get(target, key, receiver);

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

const set = /*#__PURE__*/createSetter();
const shallowSet = /*#__PURE__*/createSetter(true);

function createSetter(shallow) {
  if (shallow === void 0) {
    shallow = false;
  }

  return function set(target, key, value, receiver) {
    let oldValue = target[key];

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

    const hadKey = isArray(target) && isIntegerKey(key) ? Number(key) < target.length : hasOwn(target, key);
    const result = Reflect.set(target, key, value, receiver); // don't trigger if target is something up in the prototype chain of original

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
  const hadKey = hasOwn(target, key);
  target[key];
  const result = Reflect.deleteProperty(target, key);

  if (result && hadKey) {
    trigger(target, "delete"
    /* TriggerOpTypes.DELETE */
    , key, undefined);
  }

  return result;
}

function has(target, key) {
  const result = Reflect.has(target, key);

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

const mutableHandlers = {
  get,
  set,
  deleteProperty,
  has,
  ownKeys
};
const readonlyHandlers = {
  get: readonlyGet,

  set(target, key) {

    return true;
  },

  deleteProperty(target, key) {

    return true;
  }

};
const shallowReactiveHandlers = /*#__PURE__*/extend({}, mutableHandlers, {
  get: shallowGet,
  set: shallowSet
}); // Props handlers are special in the sense that it should not unwrap top-level

const toShallow = value => value;

const getProto = v => Reflect.getPrototypeOf(v);

function get$1(target, key, isReadonly, isShallow) {
  if (isReadonly === void 0) {
    isReadonly = false;
  }

  if (isShallow === void 0) {
    isShallow = false;
  }

  // #1772: readonly(reactive(Map)) should return readonly + reactive version
  // of the value
  target = target["__v_raw"
  /* ReactiveFlags.RAW */
  ];
  const rawTarget = toRaw(target);
  const rawKey = toRaw(key);

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

  const {
    has
  } = getProto(rawTarget);
  const wrap = isShallow ? toShallow : isReadonly ? toReadonly : toReactive;

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

  const target = this["__v_raw"
  /* ReactiveFlags.RAW */
  ];
  const rawTarget = toRaw(target);
  const rawKey = toRaw(key);

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
  const target = toRaw(this);
  const proto = getProto(target);
  const hadKey = proto.has.call(target, value);

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
  const target = toRaw(this);
  const {
    has,
    get
  } = getProto(target);
  let hadKey = has.call(target, key);

  if (!hadKey) {
    key = toRaw(key);
    hadKey = has.call(target, key);
  }

  const oldValue = get.call(target, key);
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
  const target = toRaw(this);
  const {
    has,
    get
  } = getProto(target);
  let hadKey = has.call(target, key);

  if (!hadKey) {
    key = toRaw(key);
    hadKey = has.call(target, key);
  }

  get ? get.call(target, key) : undefined; // forward the operation before queueing reactions

  const result = target.delete(key);

  if (hadKey) {
    trigger(target, "delete"
    /* TriggerOpTypes.DELETE */
    , key, undefined);
  }

  return result;
}

function clear() {
  const target = toRaw(this);
  const hadItems = target.size !== 0;

  const result = target.clear();

  if (hadItems) {
    trigger(target, "clear"
    /* TriggerOpTypes.CLEAR */
    , undefined, undefined);
  }

  return result;
}

function createForEach(isReadonly, isShallow) {
  return function forEach(callback, thisArg) {
    const observed = this;
    const target = observed["__v_raw"
    /* ReactiveFlags.RAW */
    ];
    const rawTarget = toRaw(target);
    const wrap = isShallow ? toShallow : isReadonly ? toReadonly : toReactive;
    !isReadonly && track(rawTarget, "iterate"
    /* TrackOpTypes.ITERATE */
    , ITERATE_KEY);
    return target.forEach((value, key) => {
      // important: make sure the callback is
      // 1. invoked with the reactive map as `this` and 3rd arg
      // 2. the value received should be a corresponding reactive/readonly.
      return callback.call(thisArg, wrap(value), wrap(key), observed);
    });
  };
}

function createIterableMethod(method, isReadonly, isShallow) {
  return function () {
    const target = this["__v_raw"
    /* ReactiveFlags.RAW */
    ];
    const rawTarget = toRaw(target);
    const targetIsMap = isMap(rawTarget);
    const isPair = method === 'entries' || method === Symbol.iterator && targetIsMap;
    const isKeyOnly = method === 'keys' && targetIsMap;
    const innerIterator = target[method](...arguments);
    const wrap = isShallow ? toShallow : isReadonly ? toReadonly : toReactive;
    !isReadonly && track(rawTarget, "iterate"
    /* TrackOpTypes.ITERATE */
    , isKeyOnly ? MAP_KEY_ITERATE_KEY : ITERATE_KEY); // return a wrapped iterator which returns observed versions of the
    // values emitted from the real iterator

    return {
      // iterator protocol
      next() {
        const {
          value,
          done
        } = innerIterator.next();
        return done ? {
          value,
          done
        } : {
          value: isPair ? [wrap(value[0]), wrap(value[1])] : wrap(value),
          done
        };
      },

      // iterable protocol
      [Symbol.iterator]() {
        return this;
      }

    };
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
  const mutableInstrumentations = {
    get(key) {
      return get$1(this, key);
    },

    get size() {
      return size(this);
    },

    has: has$1,
    add,
    set: set$1,
    delete: deleteEntry,
    clear,
    forEach: createForEach(false, false)
  };
  const shallowInstrumentations = {
    get(key) {
      return get$1(this, key, false, true);
    },

    get size() {
      return size(this);
    },

    has: has$1,
    add,
    set: set$1,
    delete: deleteEntry,
    clear,
    forEach: createForEach(false, true)
  };
  const readonlyInstrumentations = {
    get(key) {
      return get$1(this, key, true);
    },

    get size() {
      return size(this, true);
    },

    has(key) {
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
  const shallowReadonlyInstrumentations = {
    get(key) {
      return get$1(this, key, true, true);
    },

    get size() {
      return size(this, true);
    },

    has(key) {
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
  const iteratorMethods = ['keys', 'values', 'entries', Symbol.iterator];
  iteratorMethods.forEach(method => {
    mutableInstrumentations[method] = createIterableMethod(method, false, false);
    readonlyInstrumentations[method] = createIterableMethod(method, true, false);
    shallowInstrumentations[method] = createIterableMethod(method, false, true);
    shallowReadonlyInstrumentations[method] = createIterableMethod(method, true, true);
  });
  return [mutableInstrumentations, readonlyInstrumentations, shallowInstrumentations, shallowReadonlyInstrumentations];
}

const [mutableInstrumentations, readonlyInstrumentations, shallowInstrumentations, shallowReadonlyInstrumentations] = /* #__PURE__*/createInstrumentations();

function createInstrumentationGetter(isReadonly, shallow) {
  const instrumentations = shallow ? isReadonly ? shallowReadonlyInstrumentations : shallowInstrumentations : isReadonly ? readonlyInstrumentations : mutableInstrumentations;
  return (target, key, receiver) => {
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

const mutableCollectionHandlers = {
  get: /*#__PURE__*/createInstrumentationGetter(false, false)
};
const shallowCollectionHandlers = {
  get: /*#__PURE__*/createInstrumentationGetter(false, true)
};
const readonlyCollectionHandlers = {
  get: /*#__PURE__*/createInstrumentationGetter(true, false)
};

const reactiveMap = new WeakMap();
const shallowReactiveMap = new WeakMap();
const readonlyMap = new WeakMap();
const shallowReadonlyMap = new WeakMap();

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


  const existingProxy = proxyMap.get(target);

  if (existingProxy) {
    return existingProxy;
  } // only specific value types can be observed.


  const targetType = getTargetType(target);

  if (targetType === 0
  /* TargetType.INVALID */
  ) {
    return target;
  }

  const proxy = new Proxy(target, targetType === 2
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
  const raw = observed && observed["__v_raw"
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

const toReactive = value => isObject$1(value) ? reactive(value) : value;

const toReadonly = value => isObject$1(value) ? readonly(value) : value;

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

const shallowUnwrapHandlers = {
  get: (target, key, receiver) => unref(Reflect.get(target, key, receiver)),
  set: (target, key, value, receiver) => {
    const oldValue = target[key];

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

class ComputedRefImpl {
  constructor(getter, _setter, isReadonly, isSSR) {
    this._setter = _setter;
    this.dep = undefined;
    this.__v_isRef = true;
    this[_a] = false;
    this._dirty = true;
    this.effect = new ReactiveEffect(getter, () => {
      if (!this._dirty) {
        this._dirty = true;
        triggerRefValue(this);
      }
    });
    this.effect.computed = this;
    this.effect.active = this._cacheable = !isSSR;
    this["__v_isReadonly"
    /* ReactiveFlags.IS_READONLY */
    ] = isReadonly;
  }

  get value() {
    // the computed ref may get wrapped by other proxies e.g. readonly() #3376
    const self = toRaw(this);
    trackRefValue(self);

    if (self._dirty || !self._cacheable) {
      self._dirty = false;
      self._value = self.effect.run();
    }

    return self._value;
  }

  set value(newValue) {
    this._setter(newValue);
  }

}

_a = "__v_isReadonly"
/* ReactiveFlags.IS_READONLY */
;

function computed$1(getterOrOptions, debugOptions, isSSR) {
  if (isSSR === void 0) {
    isSSR = false;
  }

  let getter;
  let setter;
  const onlyGetter = isFunction(getterOrOptions);

  if (onlyGetter) {
    getter = getterOrOptions;
    setter = NOOP;
  } else {
    getter = getterOrOptions.get;
    setter = getterOrOptions.set;
  }

  const cRef = new ComputedRefImpl(getter, setter, onlyGetter || !setter, isSSR);

  return cRef;
}

function warn(msg) {
  return; // avoid props formatting or warn handler tracking deps that might be mutated
}

function callWithErrorHandling(fn, instance, type, args) {
  let res;

  try {
    res = args ? fn(...args) : fn();
  } catch (err) {
    handleError(err, instance, type);
  }

  return res;
}

function callWithAsyncErrorHandling(fn, instance, type, args) {
  if (isFunction(fn)) {
    const res = callWithErrorHandling(fn, instance, type, args);

    if (res && isPromise$1(res)) {
      res.catch(err => {
        handleError(err, instance, type);
      });
    }

    return res;
  }

  const values = [];

  for (let i = 0; i < fn.length; i++) {
    values.push(callWithAsyncErrorHandling(fn[i], instance, type, args));
  }

  return values;
}

function handleError(err, instance, type, throwInDev) {

  instance ? instance.vnode : null;

  if (instance) {
    let cur = instance.parent; // the exposed instance is the render proxy to keep it consistent with 2.x

    const exposedInstance = instance.proxy; // in production the hook receives only the error code

    const errorInfo = type;

    while (cur) {
      const errorCapturedHooks = cur.ec;

      if (errorCapturedHooks) {
        for (let i = 0; i < errorCapturedHooks.length; i++) {
          if (errorCapturedHooks[i](err, exposedInstance, errorInfo) === false) {
            return;
          }
        }
      }

      cur = cur.parent;
    } // app-level handling


    const appErrorHandler = instance.appContext.config.errorHandler;

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

let isFlushing = false;
let isFlushPending = false;
const queue = [];
let flushIndex = 0;
const pendingPostFlushCbs = [];
let activePostFlushCbs = null;
let postFlushIndex = 0;
const resolvedPromise = /*#__PURE__*/Promise.resolve();
let currentFlushPromise = null;

function nextTick(fn) {
  const p = currentFlushPromise || resolvedPromise;
  return fn ? p.then(this ? fn.bind(this) : fn) : p;
} // #2768
// Use binary-search to find a suitable position in the queue,
// so that the queue maintains the increasing order of job's id,
// which can prevent the job from being skipped and also can avoid repeated patching.


function findInsertionIndex(id) {
  // the start index should be `flushIndex + 1`
  let start = flushIndex + 1;
  let end = queue.length;

  while (start < end) {
    const middle = start + end >>> 1;
    const middleJobId = getId(queue[middle]);
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
  const i = queue.indexOf(job);

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
    pendingPostFlushCbs.push(...cb);
  }

  queueFlush();
}

function flushPreFlushCbs(seen, // if currently flushing, skip the current job itself
i) {
  if (i === void 0) {
    i = isFlushing ? flushIndex + 1 : 0;
  }

  for (; i < queue.length; i++) {
    const cb = queue[i];

    if (cb && cb.pre) {

      queue.splice(i, 1);
      i--;
      cb();
    }
  }
}

function flushPostFlushCbs(seen) {
  if (pendingPostFlushCbs.length) {
    const deduped = [...new Set(pendingPostFlushCbs)];
    pendingPostFlushCbs.length = 0; // #1947 already has active queue, nested flushPostFlushCbs call

    if (activePostFlushCbs) {
      activePostFlushCbs.push(...deduped);
      return;
    }

    activePostFlushCbs = deduped;

    activePostFlushCbs.sort((a, b) => getId(a) - getId(b));

    for (postFlushIndex = 0; postFlushIndex < activePostFlushCbs.length; postFlushIndex++) {

      activePostFlushCbs[postFlushIndex]();
    }

    activePostFlushCbs = null;
    postFlushIndex = 0;
  }
}

const getId = job => job.id == null ? Infinity : job.id;

const comparator = (a, b) => {
  const diff = getId(a) - getId(b);

  if (diff === 0) {
    if (a.pre && !b.pre) return -1;
    if (b.pre && !a.pre) return 1;
  }

  return diff;
};

function flushJobs(seen) {
  isFlushPending = false;
  isFlushing = true;
  // This ensures that:
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

  const check = NOOP;

  try {
    for (flushIndex = 0; flushIndex < queue.length; flushIndex++) {
      const job = queue[flushIndex];

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

let devtools;
let buffer = [];
let devtoolsNotInstalled = false;

function emit(event) {
  for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
    args[_key2 - 1] = arguments[_key2];
  }

  if (devtools) {
    devtools.emit(event, ...args);
  } else if (!devtoolsNotInstalled) {
    buffer.push({
      event,
      args
    });
  }
}

function setDevtoolsHook(hook, target) {
  var _a, _b;

  devtools = hook;

  if (devtools) {
    devtools.enabled = true;
    buffer.forEach(_ref3 => {
      let {
        event,
        args
      } = _ref3;
      return devtools.emit(event, ...args);
    });
    buffer = [];
  } else if ( // handle late devtools injection - only do this if we are in an actual
  // browser environment to avoid the timer handle stalling test runner exit
  // (#4815)
  typeof window !== 'undefined' && // some envs mock window but not fully
  window.HTMLElement && // also exclude jsdom
  !((_b = (_a = window.navigator) === null || _a === void 0 ? void 0 : _a.userAgent) === null || _b === void 0 ? void 0 : _b.includes('jsdom'))) {
    const replay = target.__VUE_DEVTOOLS_HOOK_REPLAY__ = target.__VUE_DEVTOOLS_HOOK_REPLAY__ || [];
    replay.push(newHook => {
      setDevtoolsHook(newHook, target);
    }); // clear buffer after 3s - the user probably doesn't have devtools installed
    // at all, and keeping the buffer will cause memory leaks (#4738)

    setTimeout(() => {
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
    Fragment,
    Text,
    Comment,
    Static
  });
}

function devtoolsUnmountApp(app) {
  emit("app:unmount"
  /* DevtoolsHooks.APP_UNMOUNT */
  , app);
}

const devtoolsComponentAdded = /*#__PURE__*/createDevtoolsComponentHook("component:added"
/* DevtoolsHooks.COMPONENT_ADDED */
);
const devtoolsComponentUpdated = /*#__PURE__*/createDevtoolsComponentHook("component:updated"
/* DevtoolsHooks.COMPONENT_UPDATED */
);

const _devtoolsComponentRemoved = /*#__PURE__*/createDevtoolsComponentHook("component:removed"
/* DevtoolsHooks.COMPONENT_REMOVED */
);

const devtoolsComponentRemoved = component => {
  if (devtools && typeof devtools.cleanupBuffer === 'function' && // remove the component if it wasn't buffered
  !devtools.cleanupBuffer(component)) {
    _devtoolsComponentRemoved(component);
  }
};

function createDevtoolsComponentHook(hook) {
  return component => {
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
  const props = instance.vnode.props || EMPTY_OBJ;

  for (var _len3 = arguments.length, rawArgs = new Array(_len3 > 2 ? _len3 - 2 : 0), _key3 = 2; _key3 < _len3; _key3++) {
    rawArgs[_key3 - 2] = arguments[_key3];
  }

  let args = rawArgs;
  const isModelListener = event.startsWith('update:'); // for v-model update:xxx events, apply modifiers on args

  const modelArg = isModelListener && event.slice(7);

  if (modelArg && modelArg in props) {
    const modifiersKey = `${modelArg === 'modelValue' ? 'model' : modelArg}Modifiers`;
    const {
      number,
      trim
    } = props[modifiersKey] || EMPTY_OBJ;

    if (trim) {
      args = rawArgs.map(a => isString(a) ? a.trim() : a);
    }

    if (number) {
      args = rawArgs.map(toNumber);
    }
  }

  {
    devtoolsComponentEmit(instance, event, args);
  }

  let handlerName;
  let handler = props[handlerName = toHandlerKey(event)] || // also try camelCase event handler (#2249)
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

  const onceHandler = props[handlerName + `Once`];

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

  const cache = appContext.emitsCache;
  const cached = cache.get(comp);

  if (cached !== undefined) {
    return cached;
  }

  const raw = comp.emits;
  let normalized = {}; // apply mixin/extends props

  let hasExtends = false;

  if (!isFunction(comp)) {
    const extendEmits = raw => {
      const normalizedFromExtend = normalizeEmitsOptions(raw, appContext, true);

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
    raw.forEach(key => normalized[key] = null);
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


let currentRenderingInstance = null;
let currentScopeId = null;
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
  const prev = currentRenderingInstance;
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

  const renderFnWithContext = function () {
    // If a user calls a compiled slot inside a template expression (#1745), it
    // can mess up block tracking, so by default we disable block tracking and
    // force bail out when invoking a compiled slot (indicated by the ._d flag).
    // This isn't necessary if rendering a compiled `<slot>`, so we flip the
    // ._d flag off when invoking the wrapped fn inside `renderSlot`.
    if (renderFnWithContext._d) {
      setBlockTracking(-1);
    }

    const prevInstance = setCurrentRenderingInstance(ctx);
    let res;

    try {
      res = fn(...arguments);
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

function markAttrsAccessed() {
}

function renderComponentRoot(instance) {
  const {
    type: Component,
    vnode,
    proxy,
    withProxy,
    props,
    propsOptions: [propsOptions],
    slots,
    attrs,
    emit,
    render,
    renderCache,
    data,
    setupState,
    ctx,
    inheritAttrs
  } = instance;
  let result;
  let fallthroughAttrs;
  const prev = setCurrentRenderingInstance(instance);

  try {
    if (vnode.shapeFlag & 4
    /* ShapeFlags.STATEFUL_COMPONENT */
    ) {
      // withProxy is a proxy with a different `has` trap only for
      // runtime-compiled render functions using `with` block.
      const proxyToUse = withProxy || proxy;
      result = normalizeVNode(render.call(proxyToUse, proxyToUse, renderCache, props, setupState, data, ctx));
      fallthroughAttrs = attrs;
    } else {
      // functional
      const render = Component; // in dev, mark attrs accessed if optional props (attrs === props)

      if ("production" !== 'production' && attrs === props) ;

      result = normalizeVNode(render.length > 1 ? render(props, "production" !== 'production' ? {
        get attrs() {
          markAttrsAccessed();
          return attrs;
        },

        slots,
        emit
      } : {
        attrs,
        slots,
        emit
      }) : render(props, null
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


  let root = result;

  if (fallthroughAttrs && inheritAttrs !== false) {
    const keys = Object.keys(fallthroughAttrs);
    const {
      shapeFlag
    } = root;

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

const getFunctionalFallthrough = attrs => {
  let res;

  for (const key in attrs) {
    if (key === 'class' || key === 'style' || isOn(key)) {
      (res || (res = {}))[key] = attrs[key];
    }
  }

  return res;
};

const filterModelListeners = (attrs, props) => {
  const res = {};

  for (const key in attrs) {
    if (!isModelListener(key) || !(key.slice(9) in props)) {
      res[key] = attrs[key];
    }
  }

  return res;
};

function shouldUpdateComponent(prevVNode, nextVNode, optimized) {
  const {
    props: prevProps,
    children: prevChildren,
    component
  } = prevVNode;
  const {
    props: nextProps,
    children: nextChildren,
    patchFlag
  } = nextVNode;
  const emits = component.emitsOptions; // Parent component's render function was hot-updated. Since this may have


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
      const dynamicProps = nextVNode.dynamicProps;

      for (let i = 0; i < dynamicProps.length; i++) {
        const key = dynamicProps[i];

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
  const nextKeys = Object.keys(nextProps);

  if (nextKeys.length !== Object.keys(prevProps).length) {
    return true;
  }

  for (let i = 0; i < nextKeys.length; i++) {
    const key = nextKeys[i];

    if (nextProps[key] !== prevProps[key] && !isEmitListener(emitsOptions, key)) {
      return true;
    }
  }

  return false;
}

function updateHOCHostEl(_ref4, el // HostNode
) {
  let {
    vnode,
    parent
  } = _ref4;

  while (parent && parent.subTree === vnode) {
    (vnode = parent.vnode).el = el;
    parent = parent.parent;
  }
}

const isSuspense = type => type.__isSuspense; // Suspense exposes a component-like API, and is treated like a component

function queueEffectWithSuspense(fn, suspense) {
  if (suspense && suspense.pendingBranch) {
    if (isArray(fn)) {
      suspense.effects.push(...fn);
    } else {
      suspense.effects.push(fn);
    }
  } else {
    queuePostFlushCb(fn);
  }
}

function provide(key, value) {
  if (!currentInstance) ; else {
    let provides = currentInstance.provides; // by default an instance inherits its parent's provides object
    // but when it needs to provide values of its own, it creates its
    // own provides object using parent provides object as prototype.
    // this way in `inject` we can simply look up injections from direct
    // parent and let the prototype chain do the work.

    const parentProvides = currentInstance.parent && currentInstance.parent.provides;

    if (parentProvides === provides) {
      provides = currentInstance.provides = Object.create(parentProvides);
    } // TS doesn't allow symbol as index type


    provides[key] = value;
  }
}

function inject(key, defaultValue, treatDefaultAsFactory) {
  if (treatDefaultAsFactory === void 0) {
    treatDefaultAsFactory = false;
  }

  // fallback to `currentRenderingInstance` so that this can be called in
  // a functional component
  const instance = currentInstance || currentRenderingInstance;

  if (instance) {
    // #2400
    // to support `app.use` plugins,
    // fallback to appContext's `provides` if the instance is at root
    const provides = instance.parent == null ? instance.vnode.appContext && instance.vnode.appContext.provides : instance.parent.provides;

    if (provides && key in provides) {
      // TS doesn't allow symbol as index type
      return provides[key];
    } else if (arguments.length > 1) {
      return treatDefaultAsFactory && isFunction(defaultValue) ? defaultValue.call(instance.proxy) : defaultValue;
    } else ;
  }
} // Simple effect.


const INITIAL_WATCHER_VALUE = {}; // implementation

function watch(source, cb, options) {

  return doWatch(source, cb, options);
}

function doWatch(source, cb, _temp) {
  let {
    immediate,
    deep,
    flush,
    onTrack,
    onTrigger
  } = _temp === void 0 ? EMPTY_OBJ : _temp;

  const instance = currentInstance;
  let getter;
  let forceTrigger = false;
  let isMultiSource = false;

  if (isRef(source)) {
    getter = () => source.value;

    forceTrigger = isShallow(source);
  } else if (isReactive(source)) {
    getter = () => source;

    deep = true;
  } else if (isArray(source)) {
    isMultiSource = true;
    forceTrigger = source.some(s => isReactive(s) || isShallow(s));

    getter = () => source.map(s => {
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
  } else if (isFunction(source)) {
    if (cb) {
      // getter with cb
      getter = () => callWithErrorHandling(source, instance, 2
      /* ErrorCodes.WATCH_GETTER */
      );
    } else {
      // no cb -> simple effect
      getter = () => {
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
    const baseGetter = getter;

    getter = () => traverse(baseGetter());
  }

  let cleanup;

  let onCleanup = fn => {
    cleanup = effect.onStop = () => {
      callWithErrorHandling(fn, instance, 4
      /* ErrorCodes.WATCH_CLEANUP */
      );
    };
  }; // in SSR there is no need to setup an actual effect, and it should be noop
  // unless it's eager or sync flush


  let ssrCleanup;

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
      const ctx = useSSRContext();
      ssrCleanup = ctx.__watcherHandles || (ctx.__watcherHandles = []);
    } else {
      return NOOP;
    }
  }

  let oldValue = isMultiSource ? new Array(source.length).fill(INITIAL_WATCHER_VALUE) : INITIAL_WATCHER_VALUE;

  const job = () => {
    if (!effect.active) {
      return;
    }

    if (cb) {
      // watch(source, cb)
      const newValue = effect.run();

      if (deep || forceTrigger || (isMultiSource ? newValue.some((v, i) => hasChanged(v, oldValue[i])) : hasChanged(newValue, oldValue)) || false) {
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
  let scheduler;

  if (flush === 'sync') {
    scheduler = job; // the scheduler function gets called directly
  } else if (flush === 'post') {
    scheduler = () => queuePostRenderEffect(job, instance && instance.suspense);
  } else {
    // default: 'pre'
    job.pre = true;
    if (instance) job.id = instance.uid;

    scheduler = () => queueJob(job);
  }

  const effect = new ReactiveEffect(getter, scheduler);


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

  const unwatch = () => {
    effect.stop();

    if (instance && instance.scope) {
      remove(instance.scope.effects, effect);
    }
  };

  if (ssrCleanup) ssrCleanup.push(unwatch);
  return unwatch;
} // this.$watch


function instanceWatch(source, value, options) {
  const publicThis = this.proxy;
  const getter = isString(source) ? source.includes('.') ? createPathGetter(publicThis, source) : () => publicThis[source] : source.bind(publicThis, publicThis);
  let cb;

  if (isFunction(value)) {
    cb = value;
  } else {
    cb = value.handler;
    options = value;
  }

  const cur = currentInstance;
  setCurrentInstance(this);
  const res = doWatch(getter, cb.bind(publicThis), options);

  if (cur) {
    setCurrentInstance(cur);
  } else {
    unsetCurrentInstance();
  }

  return res;
}

function createPathGetter(ctx, path) {
  const segments = path.split('.');
  return () => {
    let cur = ctx;

    for (let i = 0; i < segments.length && cur; i++) {
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
    for (let i = 0; i < value.length; i++) {
      traverse(value[i], seen);
    }
  } else if (isSet(value) || isMap(value)) {
    value.forEach(v => {
      traverse(v, seen);
    });
  } else if (isPlainObject(value)) {
    for (const key in value) {
      traverse(value[key], seen);
    }
  }

  return value;
}

function useTransitionState() {
  const state = {
    isMounted: false,
    isLeaving: false,
    isUnmounting: false,
    leavingVNodes: new Map()
  };
  onMounted(() => {
    state.isMounted = true;
  });
  onBeforeUnmount(() => {
    state.isUnmounting = true;
  });
  return state;
}

const TransitionHookValidator = [Function, Array];
const BaseTransitionImpl = {
  name: `BaseTransition`,
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

  setup(props, _ref6) {
    let {
      slots
    } = _ref6;
    const instance = getCurrentInstance();
    const state = useTransitionState();
    let prevTransitionKey;
    return () => {
      const children = slots.default && getTransitionRawChildren(slots.default(), true);

      if (!children || !children.length) {
        return;
      }

      let child = children[0];

      if (children.length > 1) {

        for (const c of children) {
          if (c.type !== Comment) {

            child = c;
            break;
          }
        }
      } // there's no need to track reactivity for these props so use the raw
      // props for a bit better perf


      const rawProps = toRaw(props);
      const {
        mode
      } = rawProps; // check mode

      if (state.isLeaving) {
        return emptyPlaceholder(child);
      } // in the case of <transition><keep-alive/></transition>, we need to
      // compare the type of the kept-alive children.


      const innerChild = getKeepAliveChild(child);

      if (!innerChild) {
        return emptyPlaceholder(child);
      }

      const enterHooks = resolveTransitionHooks(innerChild, rawProps, state, instance);
      setTransitionHooks(innerChild, enterHooks);
      const oldChild = instance.subTree;
      const oldInnerChild = oldChild && getKeepAliveChild(oldChild);
      let transitionKeyChanged = false;
      const {
        getTransitionKey
      } = innerChild.type;

      if (getTransitionKey) {
        const key = getTransitionKey();

        if (prevTransitionKey === undefined) {
          prevTransitionKey = key;
        } else if (key !== prevTransitionKey) {
          prevTransitionKey = key;
          transitionKeyChanged = true;
        }
      } // handle mode


      if (oldInnerChild && oldInnerChild.type !== Comment && (!isSameVNodeType(innerChild, oldInnerChild) || transitionKeyChanged)) {
        const leavingHooks = resolveTransitionHooks(oldInnerChild, rawProps, state, instance); // update old tree's hooks in case of dynamic transition

        setTransitionHooks(oldInnerChild, leavingHooks); // switching between different views

        if (mode === 'out-in') {
          state.isLeaving = true; // return placeholder node and queue update when leave finishes

          leavingHooks.afterLeave = () => {
            state.isLeaving = false; // #6835
            // it also needs to be updated when active is undefined

            if (instance.update.active !== false) {
              instance.update();
            }
          };

          return emptyPlaceholder(child);
        } else if (mode === 'in-out' && innerChild.type !== Comment) {
          leavingHooks.delayLeave = (el, earlyRemove, delayedLeave) => {
            const leavingVNodesCache = getLeavingNodesForType(state, oldInnerChild);
            leavingVNodesCache[String(oldInnerChild.key)] = oldInnerChild; // early removal callback

            el._leaveCb = () => {
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

const BaseTransition = BaseTransitionImpl;

function getLeavingNodesForType(state, vnode) {
  const {
    leavingVNodes
  } = state;
  let leavingVNodesCache = leavingVNodes.get(vnode.type);

  if (!leavingVNodesCache) {
    leavingVNodesCache = Object.create(null);
    leavingVNodes.set(vnode.type, leavingVNodesCache);
  }

  return leavingVNodesCache;
} // The transition hooks are attached to the vnode as vnode.transition
// and will be called at appropriate timing in the renderer.


function resolveTransitionHooks(vnode, props, state, instance) {
  const {
    appear,
    mode,
    persisted = false,
    onBeforeEnter,
    onEnter,
    onAfterEnter,
    onEnterCancelled,
    onBeforeLeave,
    onLeave,
    onAfterLeave,
    onLeaveCancelled,
    onBeforeAppear,
    onAppear,
    onAfterAppear,
    onAppearCancelled
  } = props;
  const key = String(vnode.key);
  const leavingVNodesCache = getLeavingNodesForType(state, vnode);

  const callHook = (hook, args) => {
    hook && callWithAsyncErrorHandling(hook, instance, 9
    /* ErrorCodes.TRANSITION_HOOK */
    , args);
  };

  const callAsyncHook = (hook, args) => {
    const done = args[1];
    callHook(hook, args);

    if (isArray(hook)) {
      if (hook.every(hook => hook.length <= 1)) done();
    } else if (hook.length <= 1) {
      done();
    }
  };

  const hooks = {
    mode,
    persisted,

    beforeEnter(el) {
      let hook = onBeforeEnter;

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


      const leavingVNode = leavingVNodesCache[key];

      if (leavingVNode && isSameVNodeType(vnode, leavingVNode) && leavingVNode.el._leaveCb) {
        // force early removal (not cancelled)
        leavingVNode.el._leaveCb();
      }

      callHook(hook, [el]);
    },

    enter(el) {
      let hook = onEnter;
      let afterHook = onAfterEnter;
      let cancelHook = onEnterCancelled;

      if (!state.isMounted) {
        if (appear) {
          hook = onAppear || onEnter;
          afterHook = onAfterAppear || onAfterEnter;
          cancelHook = onAppearCancelled || onEnterCancelled;
        } else {
          return;
        }
      }

      let called = false;

      const done = el._enterCb = cancelled => {
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

    leave(el, remove) {
      const key = String(vnode.key);

      if (el._enterCb) {
        el._enterCb(true
        /* cancelled */
        );
      }

      if (state.isUnmounting) {
        return remove();
      }

      callHook(onBeforeLeave, [el]);
      let called = false;

      const done = el._leaveCb = cancelled => {
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

    clone(vnode) {
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

  let ret = [];
  let keyedFragmentCount = 0;

  for (let i = 0; i < children.length; i++) {
    let child = children[i]; // #5360 inherit parent key in case of <template v-for>

    const key = parentKey == null ? child.key : String(parentKey) + String(child.key != null ? child.key : i); // handle fragment children case, e.g. v-for

    if (child.type === Fragment) {
      if (child.patchFlag & 128
      /* PatchFlags.KEYED_FRAGMENT */
      ) keyedFragmentCount++;
      ret = ret.concat(getTransitionRawChildren(child.children, keepComment, key));
    } // comment placeholders should be skipped, e.g. v-if
    else if (keepComment || child.type !== Comment) {
      ret.push(key != null ? cloneVNode(child, {
        key
      }) : child);
    }
  } // #1126 if a transition children list contains multiple sub fragments, these
  // fragments will be merged into a flat children array. Since each v-for
  // fragment may contain different static bindings inside, we need to de-op
  // these children to force full diffs to ensure correct behavior.


  if (keyedFragmentCount > 1) {
    for (let i = 0; i < ret.length; i++) {
      ret[i].patchFlag = -2
      /* PatchFlags.BAIL */
      ;
    }
  }

  return ret;
} // implementation, close to no-op

const isAsyncWrapper = i => !!i.type.__asyncLoader;

const isKeepAlive = vnode => vnode.type.__isKeepAlive;

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
  }

  // cache the deactivate branch check wrapper for injected hooks so the same
  // hook can be properly deduped by the scheduler. "__wdc" stands for "with
  // deactivation check".
  const wrappedHook = hook.__wdc || (hook.__wdc = () => {
    // only fire the hook if the target instance is NOT in a deactivated branch.
    let current = target;

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
    let current = target.parent;

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
  const injected = injectHook(type, hook, keepAliveRoot, true
  /* prepend */
  );
  onUnmounted(() => {
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
    const hooks = target[type] || (target[type] = []); // cache the error handling wrapper for injected hooks so the same hook
    // can be properly deduped by the scheduler. "__weh" stands for "with error
    // handling".

    const wrappedHook = hook.__weh || (hook.__weh = function () {
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

      const res = callWithAsyncErrorHandling(hook, target, type, args);
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

const createHook = lifecycle => function (hook, target) {
  if (target === void 0) {
    target = currentInstance;
  }

  return (// post-create lifecycle registrations are noops during SSR (except for serverPrefetch)
    (!isInSSRComponentSetup || lifecycle === "sp"
    /* LifecycleHooks.SERVER_PREFETCH */
    ) && injectHook(lifecycle, function () {
      return hook(...arguments);
    }, target)
  );
};

const onBeforeMount = createHook("bm"
/* LifecycleHooks.BEFORE_MOUNT */
);
const onMounted = createHook("m"
/* LifecycleHooks.MOUNTED */
);
const onBeforeUpdate = createHook("bu"
/* LifecycleHooks.BEFORE_UPDATE */
);
const onUpdated = createHook("u"
/* LifecycleHooks.UPDATED */
);
const onBeforeUnmount = createHook("bum"
/* LifecycleHooks.BEFORE_UNMOUNT */
);
const onUnmounted = createHook("um"
/* LifecycleHooks.UNMOUNTED */
);
const onServerPrefetch = createHook("sp"
/* LifecycleHooks.SERVER_PREFETCH */
);
const onRenderTriggered = createHook("rtg"
/* LifecycleHooks.RENDER_TRIGGERED */
);
const onRenderTracked = createHook("rtc"
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
  const internalInstance = currentRenderingInstance;

  if (internalInstance === null) {
    return vnode;
  }

  const instance = getExposeProxy(internalInstance) || internalInstance.proxy;
  const bindings = vnode.dirs || (vnode.dirs = []);

  for (let i = 0; i < directives.length; i++) {
    let [dir, value, arg, modifiers = EMPTY_OBJ] = directives[i];

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
        dir,
        instance,
        value,
        oldValue: void 0,
        arg,
        modifiers
      });
    }
  }

  return vnode;
}

function invokeDirectiveHook(vnode, prevVNode, instance, name) {
  const bindings = vnode.dirs;
  const oldBindings = prevVNode && prevVNode.dirs;

  for (let i = 0; i < bindings.length; i++) {
    const binding = bindings[i];

    if (oldBindings) {
      binding.oldValue = oldBindings[i].value;
    }

    let hook = binding.dir[name];

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

const COMPONENTS = 'components';
/**
 * @private
 */

function resolveComponent(name, maybeSelfReference) {
  return resolveAsset(COMPONENTS, name, true, maybeSelfReference) || name;
}

const NULL_DYNAMIC_COMPONENT = Symbol();


function resolveAsset(type, name, warnMissing, maybeSelfReference) {

  if (maybeSelfReference === void 0) {
    maybeSelfReference = false;
  }

  const instance = currentRenderingInstance || currentInstance;

  if (instance) {
    const Component = instance.type; // explicit self name has highest priority

    if (type === COMPONENTS) {
      const selfName = getComponentName(Component, false
      /* do not include inferred name to avoid breaking existing code */
      );

      if (selfName && (selfName === name || selfName === camelize(name) || selfName === capitalize(camelize(name)))) {
        return Component;
      }
    }

    const res = // local registration
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
  let ret;
  const cached = cache && cache[index];

  if (isArray(source) || isString(source)) {
    ret = new Array(source.length);

    for (let i = 0, l = source.length; i < l; i++) {
      ret[i] = renderItem(source[i], i, undefined, cached && cached[i]);
    }
  } else if (typeof source === 'number') {

    ret = new Array(source);

    for (let i = 0; i < source; i++) {
      ret[i] = renderItem(i + 1, i, undefined, cached && cached[i]);
    }
  } else if (isObject$1(source)) {
    if (source[Symbol.iterator]) {
      ret = Array.from(source, (item, i) => renderItem(item, i, undefined, cached && cached[i]));
    } else {
      const keys = Object.keys(source);
      ret = new Array(keys.length);

      for (let i = 0, l = keys.length; i < l; i++) {
        const key = keys[i];
        ret[i] = renderItem(source[key], key, i, cached && cached[i]);
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

  let slot = slots[name];
  // invocation interfering with template-based block tracking, but in
  // `renderSlot` we can be sure that it's template-based so we can force
  // enable it.


  if (slot && slot._c) {
    slot._d = false;
  }

  openBlock();
  const validSlotContent = slot && ensureValidVNode(slot(props));
  const rendered = createBlock(Fragment, {
    key: props.key || // slot content array of a dynamic conditional slot may have a branch
    // key attached in the `createSlots` helper, respect that
    validSlotContent && validSlotContent.key || `_${name}`
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
  return vnodes.some(child => {
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


const getPublicInstance = i => {
  if (!i) return null;
  if (isStatefulComponent(i)) return getExposeProxy(i) || i.proxy;
  return getPublicInstance(i.parent);
};

const publicPropertiesMap = // Move PURE marker to new line to workaround compiler discarding it
// due to type annotation

/*#__PURE__*/
extend(Object.create(null), {
  $: i => i,
  $el: i => i.vnode.el,
  $data: i => i.data,
  $props: i => i.props,
  $attrs: i => i.attrs,
  $slots: i => i.slots,
  $refs: i => i.refs,
  $parent: i => getPublicInstance(i.parent),
  $root: i => getPublicInstance(i.root),
  $emit: i => i.emit,
  $options: i => resolveMergedOptions(i) ,
  $forceUpdate: i => i.f || (i.f = () => queueJob(i.update)),
  $nextTick: i => i.n || (i.n = nextTick.bind(i.proxy)),
  $watch: i => instanceWatch.bind(i) 
});

const hasSetupBinding = (state, key) => state !== EMPTY_OBJ && !state.__isScriptSetup && hasOwn(state, key);

const PublicInstanceProxyHandlers = {
  get(_ref9, key) {
    let {
      _: instance
    } = _ref9;
    const {
      ctx,
      setupState,
      data,
      props,
      accessCache,
      type,
      appContext
    } = instance; // for internal formatters to know that this is a Vue instance
    // This getter gets called for every property access on the render context
    // during render and is a major hotspot. The most expensive part of this
    // is the multiple hasOwn() calls. It's much faster to do a simple property
    // access on a plain object, so we use an accessCache object (with null
    // prototype) to memoize what access type a key corresponds to.


    let normalizedProps;

    if (key[0] !== '$') {
      const n = accessCache[key];

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

    const publicGetter = publicPropertiesMap[key];
    let cssModule, globalProperties; // public $xxx properties

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

  set(_ref10, key, value) {
    let {
      _: instance
    } = _ref10;
    const {
      data,
      setupState,
      ctx
    } = instance;

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

  has(_ref11, key) {
    let {
      _: {
        data,
        setupState,
        accessCache,
        ctx,
        appContext,
        propsOptions
      }
    } = _ref11;
    let normalizedProps;
    return !!accessCache[key] || data !== EMPTY_OBJ && hasOwn(data, key) || hasSetupBinding(setupState, key) || (normalizedProps = propsOptions[0]) && hasOwn(normalizedProps, key) || hasOwn(ctx, key) || hasOwn(publicPropertiesMap, key) || hasOwn(appContext.config.globalProperties, key);
  },

  defineProperty(target, key, descriptor) {
    if (descriptor.get != null) {
      // invalidate key cache of a getter based property #5417
      target._.accessCache[key] = 0;
    } else if (hasOwn(descriptor, 'value')) {
      this.set(target, key, descriptor.value, null);
    }

    return Reflect.defineProperty(target, key, descriptor);
  }

};

let shouldCacheAccess = true;

function applyOptions(instance) {
  const options = resolveMergedOptions(instance);
  const publicThis = instance.proxy;
  const ctx = instance.ctx; // do not cache property access on public proxy during state initialization

  shouldCacheAccess = false; // call beforeCreate first before accessing other options since
  // the hook may mutate resolved options (#2791)

  if (options.beforeCreate) {
    callHook$1(options.beforeCreate, instance, "bc"
    /* LifecycleHooks.BEFORE_CREATE */
    );
  }

  const {
    // state
    data: dataOptions,
    computed: computedOptions,
    methods,
    watch: watchOptions,
    provide: provideOptions,
    inject: injectOptions,
    // lifecycle
    created,
    beforeMount,
    mounted,
    beforeUpdate,
    updated,
    activated,
    deactivated,
    beforeDestroy,
    beforeUnmount,
    destroyed,
    unmounted,
    render,
    renderTracked,
    renderTriggered,
    errorCaptured,
    serverPrefetch,
    // public API
    expose,
    inheritAttrs,
    // assets
    components,
    directives,
    filters
  } = options;
  const checkDuplicateProperties = null;
  // - props (already done outside of this function)
  // - inject
  // - methods
  // - data (deferred since it relies on `this` access)
  // - computed
  // - watch (deferred since it relies on `this` access)


  if (injectOptions) {
    resolveInjections(injectOptions, ctx, checkDuplicateProperties, instance.appContext.config.unwrapInjectedRef);
  }

  if (methods) {
    for (const key in methods) {
      const methodHandler = methods[key];

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

    const data = dataOptions.call(publicThis, publicThis);

    if (!isObject$1(data)) ; else {
      instance.data = reactive(data);
    }
  } // state initialization complete at this point - start caching access


  shouldCacheAccess = true;

  if (computedOptions) {
    for (const key in computedOptions) {
      const opt = computedOptions[key];
      const get = isFunction(opt) ? opt.bind(publicThis, publicThis) : isFunction(opt.get) ? opt.get.bind(publicThis, publicThis) : NOOP;

      const set = !isFunction(opt) && isFunction(opt.set) ? opt.set.bind(publicThis) : NOOP;
      const c = computed({
        get,
        set
      });
      Object.defineProperty(ctx, key, {
        enumerable: true,
        configurable: true,
        get: () => c.value,
        set: v => c.value = v
      });
    }
  }

  if (watchOptions) {
    for (const key in watchOptions) {
      createWatcher(watchOptions[key], ctx, publicThis, key);
    }
  }

  if (provideOptions) {
    const provides = isFunction(provideOptions) ? provideOptions.call(publicThis) : provideOptions;
    Reflect.ownKeys(provides).forEach(key => {
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
      hook.forEach(_hook => register(_hook.bind(publicThis)));
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
      const exposed = instance.exposed || (instance.exposed = {});
      expose.forEach(key => {
        Object.defineProperty(exposed, key, {
          get: () => publicThis[key],
          set: val => publicThis[key] = val
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

  for (const key in injectOptions) {
    const opt = injectOptions[key];
    let injected;

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
          get: () => injected.value,
          set: v => injected.value = v
        });
      } else {

        ctx[key] = injected;
      }
    } else {
      ctx[key] = injected;
    }
  }
}

function callHook$1(hook, instance, type) {
  callWithAsyncErrorHandling(isArray(hook) ? hook.map(h => h.bind(instance.proxy)) : hook.bind(instance.proxy), instance, type);
}

function createWatcher(raw, ctx, publicThis, key) {
  const getter = key.includes('.') ? createPathGetter(publicThis, key) : () => publicThis[key];

  if (isString(raw)) {
    const handler = ctx[raw];

    if (isFunction(handler)) {
      watch(getter, handler);
    }
  } else if (isFunction(raw)) {
    watch(getter, raw.bind(publicThis));
  } else if (isObject$1(raw)) {
    if (isArray(raw)) {
      raw.forEach(r => createWatcher(r, ctx, publicThis, key));
    } else {
      const handler = isFunction(raw.handler) ? raw.handler.bind(publicThis) : ctx[raw.handler];

      if (isFunction(handler)) {
        watch(getter, handler, raw);
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
  const base = instance.type;
  const {
    mixins,
    extends: extendsOptions
  } = base;
  const {
    mixins: globalMixins,
    optionsCache: cache,
    config: {
      optionMergeStrategies
    }
  } = instance.appContext;
  const cached = cache.get(base);
  let resolved;

  if (cached) {
    resolved = cached;
  } else if (!globalMixins.length && !mixins && !extendsOptions) {
    {
      resolved = base;
    }
  } else {
    resolved = {};

    if (globalMixins.length) {
      globalMixins.forEach(m => mergeOptions(resolved, m, optionMergeStrategies, true));
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

  const {
    mixins,
    extends: extendsOptions
  } = from;

  if (extendsOptions) {
    mergeOptions(to, extendsOptions, strats, true);
  }

  if (mixins) {
    mixins.forEach(m => mergeOptions(to, m, strats, true));
  }

  for (const key in from) {
    if (asMixin && key === 'expose') ; else {
      const strat = internalOptionMergeStrats[key] || strats && strats[key];
      to[key] = strat ? strat(to[key], from[key]) : from[key];
    }
  }

  return to;
}

const internalOptionMergeStrats = {
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
    const res = {};

    for (let i = 0; i < raw.length; i++) {
      res[raw[i]] = raw[i];
    }

    return res;
  }

  return raw;
}

function mergeAsArray(to, from) {
  return to ? [...new Set([].concat(to, from))] : from;
}

function mergeObjectOptions(to, from) {
  return to ? extend(extend(Object.create(null), to), from) : from;
}

function mergeWatchOptions(to, from) {
  if (!to) return from;
  if (!from) return to;
  const merged = extend(Object.create(null), to);

  for (const key in from) {
    merged[key] = mergeAsArray(to[key], from[key]);
  }

  return merged;
}

function initProps(instance, rawProps, isStateful, // result of bitwise flag comparison
isSSR) {
  if (isSSR === void 0) {
    isSSR = false;
  }

  const props = {};
  const attrs = {};
  def(attrs, InternalObjectKey, 1);
  instance.propsDefaults = Object.create(null);
  setFullProps(instance, rawProps, props, attrs); // ensure all declared prop keys are present

  for (const key in instance.propsOptions[0]) {
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
  const {
    props,
    attrs,
    vnode: {
      patchFlag
    }
  } = instance;
  const rawCurrentProps = toRaw(props);
  const [options] = instance.propsOptions;
  let hasAttrsChanged = false;

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
      const propsToUpdate = instance.vnode.dynamicProps;

      for (let i = 0; i < propsToUpdate.length; i++) {
        let key = propsToUpdate[i]; // skip if the prop key is a declared emit event listener

        if (isEmitListener(instance.emitsOptions, key)) {
          continue;
        } // PROPS flag guarantees rawProps to be non-null


        const value = rawProps[key];

        if (options) {
          // attr / props separation was done on init and will be consistent
          // in this code path, so just check if attrs have it.
          if (hasOwn(attrs, key)) {
            if (value !== attrs[key]) {
              attrs[key] = value;
              hasAttrsChanged = true;
            }
          } else {
            const camelizedKey = camelize(key);
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


    let kebabKey;

    for (const key in rawCurrentProps) {
      if (!rawProps || // for camelCase
      !hasOwn(rawProps, key) && ( // it's possible the original props was passed in as kebab-case
      // and converted to camelCase (#955)
      (kebabKey = hyphenate(key)) === key || !hasOwn(rawProps, kebabKey))) {
        if (options) {
          if (rawPrevProps && ( // for camelCase
          rawPrevProps[key] !== undefined || // for kebab-case
          rawPrevProps[kebabKey] !== undefined)) {
            props[key] = resolvePropValue(options, rawCurrentProps, key, undefined, instance, true
            /* isAbsent */
            );
          }
        } else {
          delete props[key];
        }
      }
    } // in the case of functional component w/o props declaration, props and
    // attrs point to the same object so it should already have been updated.


    if (attrs !== rawCurrentProps) {
      for (const key in attrs) {
        if (!rawProps || !hasOwn(rawProps, key) && !false) {
          delete attrs[key];
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
  const [options, needCastKeys] = instance.propsOptions;
  let hasAttrsChanged = false;
  let rawCastValues;

  if (rawProps) {
    for (let key in rawProps) {
      // key, ref are reserved and never passed down
      if (isReservedProp(key)) {
        continue;
      }

      const value = rawProps[key]; // prop option names are camelized during normalization, so to support
      // kebab -> camel conversion here we need to camelize the key.

      let camelKey;

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
    const rawCurrentProps = toRaw(props);
    const castValues = rawCastValues || EMPTY_OBJ;

    for (let i = 0; i < needCastKeys.length; i++) {
      const key = needCastKeys[i];
      props[key] = resolvePropValue(options, rawCurrentProps, key, castValues[key], instance, !hasOwn(castValues, key));
    }
  }

  return hasAttrsChanged;
}

function resolvePropValue(options, props, key, value, instance, isAbsent) {
  const opt = options[key];

  if (opt != null) {
    const hasDefault = hasOwn(opt, 'default'); // default values

    if (hasDefault && value === undefined) {
      const defaultValue = opt.default;

      if (opt.type !== Function && isFunction(defaultValue)) {
        const {
          propsDefaults
        } = instance;

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

  const cache = appContext.propsCache;
  const cached = cache.get(comp);

  if (cached) {
    return cached;
  }

  const raw = comp.props;
  const normalized = {};
  const needCastKeys = []; // apply mixin/extends props

  let hasExtends = false;

  if (!isFunction(comp)) {
    const extendProps = raw => {
      hasExtends = true;
      const [props, keys] = normalizePropsOptions(raw, appContext, true);
      extend(normalized, props);
      if (keys) needCastKeys.push(...keys);
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
    for (let i = 0; i < raw.length; i++) {

      const normalizedKey = camelize(raw[i]);

      if (validatePropName(normalizedKey)) {
        normalized[normalizedKey] = EMPTY_OBJ;
      }
    }
  } else if (raw) {

    for (const key in raw) {
      const normalizedKey = camelize(key);

      if (validatePropName(normalizedKey)) {
        const opt = raw[key];
        const prop = normalized[normalizedKey] = isArray(opt) || isFunction(opt) ? {
          type: opt
        } : Object.assign({}, opt);

        if (prop) {
          const booleanIndex = getTypeIndex(Boolean, prop.type);
          const stringIndex = getTypeIndex(String, prop.type);
          prop[0
          /* BooleanFlags.shouldCast */
          ] = booleanIndex > -1;
          prop[1
          /* BooleanFlags.shouldCastTrue */
          ] = stringIndex < 0 || booleanIndex < stringIndex; // if the prop needs boolean casting or default value

          if (booleanIndex > -1 || hasOwn(prop, 'default')) {
            needCastKeys.push(normalizedKey);
          }
        }
      }
    }
  }

  const res = [normalized, needCastKeys];

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
  const match = ctor && ctor.toString().match(/^\s*function (\w+)/);
  return match ? match[1] : ctor === null ? 'null' : '';
}

function isSameType(a, b) {
  return getType(a) === getType(b);
}

function getTypeIndex(type, expectedTypes) {
  if (isArray(expectedTypes)) {
    return expectedTypes.findIndex(t => isSameType(t, type));
  } else if (isFunction(expectedTypes)) {
    return isSameType(expectedTypes, type) ? 0 : -1;
  }

  return -1;
}

const isInternalKey = key => key[0] === '_' || key === '$stable';

const normalizeSlotValue = value => isArray(value) ? value.map(normalizeVNode) : [normalizeVNode(value)];

const normalizeSlot = (key, rawSlot, ctx) => {
  if (rawSlot._n) {
    // already normalized - #5353
    return rawSlot;
  }

  const normalized = withCtx(function () {
    if ("production" !== 'production' && currentInstance) ;

    return normalizeSlotValue(rawSlot(...arguments));
  }, ctx);
  normalized._c = false;
  return normalized;
};

const normalizeObjectSlots = (rawSlots, slots, instance) => {
  const ctx = rawSlots._ctx;

  for (const key in rawSlots) {
    if (isInternalKey(key)) continue;
    const value = rawSlots[key];

    if (isFunction(value)) {
      slots[key] = normalizeSlot(key, value, ctx);
    } else if (value != null) {

      const normalized = normalizeSlotValue(value);

      slots[key] = () => normalized;
    }
  }
};

const normalizeVNodeSlots = (instance, children) => {

  const normalized = normalizeSlotValue(children);

  instance.slots.default = () => normalized;
};

const initSlots = (instance, children) => {
  if (instance.vnode.shapeFlag & 32
  /* ShapeFlags.SLOTS_CHILDREN */
  ) {
    const type = children._;

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

const updateSlots = (instance, children, optimized) => {
  const {
    vnode,
    slots
  } = instance;
  let needDeletionCheck = true;
  let deletionComparisonTarget = EMPTY_OBJ;

  if (vnode.shapeFlag & 32
  /* ShapeFlags.SLOTS_CHILDREN */
  ) {
    const type = children._;

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
    for (const key in slots) {
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

let uid = 0;

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

    const context = createAppContext();
    const installedPlugins = new Set();
    let isMounted = false;
    const app = context.app = {
      _uid: uid++,
      _component: rootComponent,
      _props: rootProps,
      _container: null,
      _context: context,
      _instance: null,
      version,

      get config() {
        return context.config;
      },

      set config(v) {
      },

      use(plugin) {
        for (var _len6 = arguments.length, options = new Array(_len6 > 1 ? _len6 - 1 : 0), _key6 = 1; _key6 < _len6; _key6++) {
          options[_key6 - 1] = arguments[_key6];
        }

        if (installedPlugins.has(plugin)) ; else if (plugin && isFunction(plugin.install)) {
          installedPlugins.add(plugin);
          plugin.install(app, ...options);
        } else if (isFunction(plugin)) {
          installedPlugins.add(plugin);
          plugin(app, ...options);
        } else ;

        return app;
      },

      mixin(mixin) {
        {
          if (!context.mixins.includes(mixin)) {
            context.mixins.push(mixin);
          }
        }

        return app;
      },

      component(name, component) {

        if (!component) {
          return context.components[name];
        }

        context.components[name] = component;
        return app;
      },

      directive(name, directive) {

        if (!directive) {
          return context.directives[name];
        }

        context.directives[name] = directive;
        return app;
      },

      mount(rootContainer, isHydrate, isSVG) {
        if (!isMounted) {

          const vnode = createVNode(rootComponent, rootProps); // store app context on the root VNode.
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

      unmount() {
        if (isMounted) {
          render(null, app._container);

          {
            app._instance = null;
            devtoolsUnmountApp(app);
          }

          delete app._container.__vue_app__;
        }
      },

      provide(key, value) {

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
    rawRef.forEach((r, i) => setRef(r, oldRawRef && (isArray(oldRawRef) ? oldRawRef[i] : oldRawRef), parentSuspense, vnode, isUnmount));
    return;
  }

  if (isAsyncWrapper(vnode) && !isUnmount) {
    // when mounting async components, nothing needs to be done,
    // because the template ref is forwarded to inner component
    return;
  }

  const refValue = vnode.shapeFlag & 4
  /* ShapeFlags.STATEFUL_COMPONENT */
  ? getExposeProxy(vnode.component) || vnode.component.proxy : vnode.el;
  const value = isUnmount ? null : refValue;
  const {
    i: owner,
    r: ref
  } = rawRef;

  const oldRef = oldRawRef && oldRawRef.r;
  const refs = owner.refs === EMPTY_OBJ ? owner.refs = {} : owner.refs;
  const setupState = owner.setupState; // dynamic ref changed. unset old ref

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
    const _isString = isString(ref);

    const _isRef = isRef(ref);

    if (_isString || _isRef) {
      const doSet = () => {
        if (rawRef.f) {
          const existing = _isString ? hasOwn(setupState, ref) ? setupState[ref] : refs[ref] : ref.value;

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

const queuePostRenderEffect = queueEffectWithSuspense;
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
  const target = getGlobalThis();
  target.__VUE__ = true;

  {
    setDevtoolsHook(target.__VUE_DEVTOOLS_GLOBAL_HOOK__, target);
  }

  const {
    insert: hostInsert,
    remove: hostRemove,
    patchProp: hostPatchProp,
    createElement: hostCreateElement,
    createText: hostCreateText,
    createComment: hostCreateComment,
    setText: hostSetText,
    setElementText: hostSetElementText,
    parentNode: hostParentNode,
    nextSibling: hostNextSibling,
    setScopeId: hostSetScopeId = NOOP,
    insertStaticContent: hostInsertStaticContent
  } = options; // Note: functions inside this closure should use `const xxx = () => {}`
  // style in order to prevent being inlined by minifiers.

  const patch = function (n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
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

    const {
      type,
      ref,
      shapeFlag
    } = n2;

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

  const processText = (n1, n2, container, anchor) => {
    if (n1 == null) {
      hostInsert(n2.el = hostCreateText(n2.children), container, anchor);
    } else {
      const el = n2.el = n1.el;

      if (n2.children !== n1.children) {
        hostSetText(el, n2.children);
      }
    }
  };

  const processCommentNode = (n1, n2, container, anchor) => {
    if (n1 == null) {
      hostInsert(n2.el = hostCreateComment(n2.children || ''), container, anchor);
    } else {
      // there's no support for dynamic comments
      n2.el = n1.el;
    }
  };

  const mountStaticNode = (n2, container, anchor, isSVG) => {
    [n2.el, n2.anchor] = hostInsertStaticContent(n2.children, container, anchor, isSVG, n2.el, n2.anchor);
  };

  const moveStaticNode = (_ref12, container, nextSibling) => {
    let {
      el,
      anchor
    } = _ref12;
    let next;

    while (el && el !== anchor) {
      next = hostNextSibling(el);
      hostInsert(el, container, nextSibling);
      el = next;
    }

    hostInsert(anchor, container, nextSibling);
  };

  const removeStaticNode = _ref13 => {
    let {
      el,
      anchor
    } = _ref13;
    let next;

    while (el && el !== anchor) {
      next = hostNextSibling(el);
      hostRemove(el);
      el = next;
    }

    hostRemove(anchor);
  };

  const processElement = (n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) => {
    isSVG = isSVG || n2.type === 'svg';

    if (n1 == null) {
      mountElement(n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
    } else {
      patchElement(n1, n2, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
    }
  };

  const mountElement = (vnode, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) => {
    let el;
    let vnodeHook;
    const {
      type,
      props,
      shapeFlag,
      transition,
      dirs
    } = vnode;
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
      for (const key in props) {
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


    const needCallTransitionHooks = (!parentSuspense || parentSuspense && !parentSuspense.pendingBranch) && transition && !transition.persisted;

    if (needCallTransitionHooks) {
      transition.beforeEnter(el);
    }

    hostInsert(el, container, anchor);

    if ((vnodeHook = props && props.onVnodeMounted) || needCallTransitionHooks || dirs) {
      queuePostRenderEffect(() => {
        vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, vnode);
        needCallTransitionHooks && transition.enter(el);
        dirs && invokeDirectiveHook(vnode, null, parentComponent, 'mounted');
      }, parentSuspense);
    }
  };

  const setScopeId = (el, vnode, scopeId, slotScopeIds, parentComponent) => {
    if (scopeId) {
      hostSetScopeId(el, scopeId);
    }

    if (slotScopeIds) {
      for (let i = 0; i < slotScopeIds.length; i++) {
        hostSetScopeId(el, slotScopeIds[i]);
      }
    }

    if (parentComponent) {
      let subTree = parentComponent.subTree;

      if (vnode === subTree) {
        const parentVNode = parentComponent.vnode;
        setScopeId(el, parentVNode, parentVNode.scopeId, parentVNode.slotScopeIds, parentComponent.parent);
      }
    }
  };

  const mountChildren = function (children, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized, start) {
    if (start === void 0) {
      start = 0;
    }

    for (let i = start; i < children.length; i++) {
      const child = children[i] = optimized ? cloneIfMounted(children[i]) : normalizeVNode(children[i]);
      patch(null, child, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
    }
  };

  const patchElement = (n1, n2, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) => {
    const el = n2.el = n1.el;
    let {
      patchFlag,
      dynamicChildren,
      dirs
    } = n2; // #1426 take the old vnode's patch flag into account since user may clone a
    // compiler-generated vnode, which de-opts to FULL_PROPS

    patchFlag |= n1.patchFlag & 16
    /* PatchFlags.FULL_PROPS */
    ;
    const oldProps = n1.props || EMPTY_OBJ;
    const newProps = n2.props || EMPTY_OBJ;
    let vnodeHook; // disable recurse in beforeUpdate hooks

    parentComponent && toggleRecurse(parentComponent, false);

    if (vnodeHook = newProps.onVnodeBeforeUpdate) {
      invokeVNodeHook(vnodeHook, parentComponent, n2, n1);
    }

    if (dirs) {
      invokeDirectiveHook(n2, n1, parentComponent, 'beforeUpdate');
    }

    parentComponent && toggleRecurse(parentComponent, true);

    const areChildrenSVG = isSVG && n2.type !== 'foreignObject';

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
          const propsToUpdate = n2.dynamicProps;

          for (let i = 0; i < propsToUpdate.length; i++) {
            const key = propsToUpdate[i];
            const prev = oldProps[key];
            const next = newProps[key]; // #1471 force patch value

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
      queuePostRenderEffect(() => {
        vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, n2, n1);
        dirs && invokeDirectiveHook(n2, n1, parentComponent, 'updated');
      }, parentSuspense);
    }
  }; // The fast path for blocks.


  const patchBlockChildren = (oldChildren, newChildren, fallbackContainer, parentComponent, parentSuspense, isSVG, slotScopeIds) => {
    for (let i = 0; i < newChildren.length; i++) {
      const oldVNode = oldChildren[i];
      const newVNode = newChildren[i]; // Determine the container (parent element) for the patch.

      const container = // oldVNode may be an errored async setup() component inside Suspense
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

  const patchProps = (el, vnode, oldProps, newProps, parentComponent, parentSuspense, isSVG) => {
    if (oldProps !== newProps) {
      if (oldProps !== EMPTY_OBJ) {
        for (const key in oldProps) {
          if (!isReservedProp(key) && !(key in newProps)) {
            hostPatchProp(el, key, oldProps[key], null, isSVG, vnode.children, parentComponent, parentSuspense, unmountChildren);
          }
        }
      }

      for (const key in newProps) {
        // empty string is not valid prop
        if (isReservedProp(key)) continue;
        const next = newProps[key];
        const prev = oldProps[key]; // defer patching value

        if (next !== prev && key !== 'value') {
          hostPatchProp(el, key, prev, next, isSVG, vnode.children, parentComponent, parentSuspense, unmountChildren);
        }
      }

      if ('value' in newProps) {
        hostPatchProp(el, 'value', oldProps.value, newProps.value);
      }
    }
  };

  const processFragment = (n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) => {
    const fragmentStartAnchor = n2.el = n1 ? n1.el : hostCreateText('');
    const fragmentEndAnchor = n2.anchor = n1 ? n1.anchor : hostCreateText('');
    let {
      patchFlag,
      dynamicChildren,
      slotScopeIds: fragmentSlotScopeIds
    } = n2;


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

  const processComponent = (n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) => {
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

  const mountComponent = (initialVNode, container, anchor, parentComponent, parentSuspense, isSVG, optimized) => {
    const instance = initialVNode.component = createComponentInstance(initialVNode, parentComponent, parentSuspense);


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
        const placeholder = instance.subTree = createVNode(Comment);
        processCommentNode(null, placeholder, container, anchor);
      }

      return;
    }

    setupRenderEffect(instance, initialVNode, container, anchor, parentSuspense, isSVG, optimized);
  };

  const updateComponent = (n1, n2, optimized) => {
    const instance = n2.component = n1.component;

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

  const setupRenderEffect = (instance, initialVNode, container, anchor, parentSuspense, isSVG, optimized) => {
    const componentUpdateFn = () => {
      if (!instance.isMounted) {
        let vnodeHook;
        const {
          el,
          props
        } = initialVNode;
        const {
          bm,
          m,
          parent
        } = instance;
        const isAsyncWrapperVNode = isAsyncWrapper(initialVNode);
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
          const hydrateSubTree = () => {

            instance.subTree = renderComponentRoot(instance);

            hydrateNode(el, instance.subTree, instance, parentSuspense, null);
          };

          if (isAsyncWrapperVNode) {
            initialVNode.type.__asyncLoader().then( // note: we are moving the render call into an async callback,
            // which means it won't track dependencies - but it's ok because
            // a server-rendered async wrapper is already in resolved state
            // and it will never need to change.
            () => !instance.isUnmounted && hydrateSubTree());
          } else {
            hydrateSubTree();
          }
        } else {

          const subTree = instance.subTree = renderComponentRoot(instance);

          patch(null, subTree, container, anchor, instance, parentSuspense, isSVG);

          initialVNode.el = subTree.el;
        } // mounted hook


        if (m) {
          queuePostRenderEffect(m, parentSuspense);
        } // onVnodeMounted


        if (!isAsyncWrapperVNode && (vnodeHook = props && props.onVnodeMounted)) {
          const scopedInitialVNode = initialVNode;
          queuePostRenderEffect(() => invokeVNodeHook(vnodeHook, parent, scopedInitialVNode), parentSuspense);
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
        let {
          next,
          bu,
          u,
          parent,
          vnode
        } = instance;
        let originNext = next;
        let vnodeHook;


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


        if (vnodeHook = next.props && next.props.onVnodeBeforeUpdate) {
          invokeVNodeHook(vnodeHook, parent, next, vnode);
        }

        toggleRecurse(instance, true); // render

        const nextTree = renderComponentRoot(instance);

        const prevTree = instance.subTree;
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


        if (vnodeHook = next.props && next.props.onVnodeUpdated) {
          queuePostRenderEffect(() => invokeVNodeHook(vnodeHook, parent, next, vnode), parentSuspense);
        }

        {
          devtoolsComponentUpdated(instance);
        }
      }
    }; // create reactive effect for rendering


    const effect = instance.effect = new ReactiveEffect(componentUpdateFn, () => queueJob(update), instance.scope // track it in component's effect scope
    );

    const update = instance.update = () => effect.run();

    update.id = instance.uid; // allowRecurse
    // #1801, #2043 component render effects should allow recursive updates

    toggleRecurse(instance, true);

    update();
  };

  const updateComponentPreRender = (instance, nextVNode, optimized) => {
    nextVNode.component = instance;
    const prevProps = instance.vnode.props;
    instance.vnode = nextVNode;
    instance.next = null;
    updateProps(instance, nextVNode.props, prevProps, optimized);
    updateSlots(instance, nextVNode.children, optimized);
    pauseTracking(); // props update may have triggered pre-flush watchers.
    // flush them before the render update.

    flushPreFlushCbs();
    resetTracking();
  };

  const patchChildren = function (n1, n2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) {
    if (optimized === void 0) {
      optimized = false;
    }

    const c1 = n1 && n1.children;
    const prevShapeFlag = n1 ? n1.shapeFlag : 0;
    const c2 = n2.children;
    const {
      patchFlag,
      shapeFlag
    } = n2; // fast path

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

  const patchUnkeyedChildren = (c1, c2, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) => {
    c1 = c1 || EMPTY_ARR;
    c2 = c2 || EMPTY_ARR;
    const oldLength = c1.length;
    const newLength = c2.length;
    const commonLength = Math.min(oldLength, newLength);
    let i;

    for (i = 0; i < commonLength; i++) {
      const nextChild = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);
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


  const patchKeyedChildren = (c1, c2, container, parentAnchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized) => {
    let i = 0;
    const l2 = c2.length;
    let e1 = c1.length - 1; // prev ending index

    let e2 = l2 - 1; // next ending index
    // 1. sync from start
    // (a b) c
    // (a b) d e

    while (i <= e1 && i <= e2) {
      const n1 = c1[i];
      const n2 = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);

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
      const n1 = c1[e1];
      const n2 = c2[e2] = optimized ? cloneIfMounted(c2[e2]) : normalizeVNode(c2[e2]);

      if (isSameVNodeType(n1, n2)) {
        patch(n1, n2, container, null, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
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
        const nextPos = e2 + 1;
        const anchor = nextPos < l2 ? c2[nextPos].el : parentAnchor;

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
      const s1 = i; // prev starting index

      const s2 = i; // next starting index
      // 5.1 build key:index map for newChildren

      const keyToNewIndexMap = new Map();

      for (i = s2; i <= e2; i++) {
        const nextChild = c2[i] = optimized ? cloneIfMounted(c2[i]) : normalizeVNode(c2[i]);

        if (nextChild.key != null) {

          keyToNewIndexMap.set(nextChild.key, i);
        }
      } // 5.2 loop through old children left to be patched and try to patch
      // matching nodes & remove nodes that are no longer present


      let j;
      let patched = 0;
      const toBePatched = e2 - s2 + 1;
      let moved = false; // used to track whether any node has moved

      let maxNewIndexSoFar = 0; // works as Map<newIndex, oldIndex>
      // Note that oldIndex is offset by +1
      // and oldIndex = 0 is a special value indicating the new node has
      // no corresponding old node.
      // used for determining longest stable subsequence

      const newIndexToOldIndexMap = new Array(toBePatched);

      for (i = 0; i < toBePatched; i++) newIndexToOldIndexMap[i] = 0;

      for (i = s1; i <= e1; i++) {
        const prevChild = c1[i];

        if (patched >= toBePatched) {
          // all new children have been patched so this can only be a removal
          unmount(prevChild, parentComponent, parentSuspense, true);
          continue;
        }

        let newIndex;

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


      const increasingNewIndexSequence = moved ? getSequence(newIndexToOldIndexMap) : EMPTY_ARR;
      j = increasingNewIndexSequence.length - 1; // looping backwards so that we can use last patched node as anchor

      for (i = toBePatched - 1; i >= 0; i--) {
        const nextIndex = s2 + i;
        const nextChild = c2[nextIndex];
        const anchor = nextIndex + 1 < l2 ? c2[nextIndex + 1].el : parentAnchor;

        if (newIndexToOldIndexMap[i] === 0) {
          // mount new
          patch(null, nextChild, container, anchor, parentComponent, parentSuspense, isSVG, slotScopeIds, optimized);
        } else if (moved) {
          // move if:
          // There is no stable subsequence (e.g. a reverse)
          // OR current node is not among the stable sequence
          if (j < 0 || i !== increasingNewIndexSequence[j]) {
            move(nextChild, container, anchor, 2
            /* MoveType.REORDER */
            );
          } else {
            j--;
          }
        }
      }
    }
  };

  const move = function (vnode, container, anchor, moveType, parentSuspense) {
    if (parentSuspense === void 0) {
      parentSuspense = null;
    }

    const {
      el,
      type,
      transition,
      children,
      shapeFlag
    } = vnode;

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

      for (let i = 0; i < children.length; i++) {
        move(children[i], container, anchor, moveType);
      }

      hostInsert(vnode.anchor, container, anchor);
      return;
    }

    if (type === Static) {
      moveStaticNode(vnode, container, anchor);
      return;
    } // single nodes


    const needTransition = moveType !== 2
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
        queuePostRenderEffect(() => transition.enter(el), parentSuspense);
      } else {
        const {
          leave,
          delayLeave,
          afterLeave
        } = transition;

        const remove = () => hostInsert(el, container, anchor);

        const performLeave = () => {
          leave(el, () => {
            remove();
            afterLeave && afterLeave();
          });
        };

        if (delayLeave) {
          delayLeave(el, remove, performLeave);
        } else {
          performLeave();
        }
      }
    } else {
      hostInsert(el, container, anchor);
    }
  };

  const unmount = function (vnode, parentComponent, parentSuspense, doRemove, optimized) {
    if (doRemove === void 0) {
      doRemove = false;
    }

    if (optimized === void 0) {
      optimized = false;
    }

    const {
      type,
      props,
      ref,
      children,
      dynamicChildren,
      shapeFlag,
      patchFlag,
      dirs
    } = vnode; // unset ref

    if (ref != null) {
      setRef(ref, null, parentSuspense, vnode, true);
    }

    if (shapeFlag & 256
    /* ShapeFlags.COMPONENT_SHOULD_KEEP_ALIVE */
    ) {
      parentComponent.ctx.deactivate(vnode);
      return;
    }

    const shouldInvokeDirs = shapeFlag & 1
    /* ShapeFlags.ELEMENT */
    && dirs;
    const shouldInvokeVnodeHook = !isAsyncWrapper(vnode);
    let vnodeHook;

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
      queuePostRenderEffect(() => {
        vnodeHook && invokeVNodeHook(vnodeHook, parentComponent, vnode);
        shouldInvokeDirs && invokeDirectiveHook(vnode, null, parentComponent, 'unmounted');
      }, parentSuspense);
    }
  };

  const remove = vnode => {
    const {
      type,
      el,
      anchor,
      transition
    } = vnode;

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

    const performRemove = () => {
      hostRemove(el);

      if (transition && !transition.persisted && transition.afterLeave) {
        transition.afterLeave();
      }
    };

    if (vnode.shapeFlag & 1
    /* ShapeFlags.ELEMENT */
    && transition && !transition.persisted) {
      const {
        leave,
        delayLeave
      } = transition;

      const performLeave = () => leave(el, performRemove);

      if (delayLeave) {
        delayLeave(vnode.el, performRemove, performLeave);
      } else {
        performLeave();
      }
    } else {
      performRemove();
    }
  };

  const removeFragment = (cur, end) => {
    // For fragments, directly remove all contained DOM nodes.
    // (fragment child nodes cannot have transition)
    let next;

    while (cur !== end) {
      next = hostNextSibling(cur);
      hostRemove(cur);
      cur = next;
    }

    hostRemove(end);
  };

  const unmountComponent = (instance, parentSuspense, doRemove) => {

    const {
      bum,
      scope,
      update,
      subTree,
      um
    } = instance; // beforeUnmount hook

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

    queuePostRenderEffect(() => {
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

  const unmountChildren = function (children, parentComponent, parentSuspense, doRemove, optimized, start) {
    if (doRemove === void 0) {
      doRemove = false;
    }

    if (optimized === void 0) {
      optimized = false;
    }

    if (start === void 0) {
      start = 0;
    }

    for (let i = start; i < children.length; i++) {
      unmount(children[i], parentComponent, parentSuspense, doRemove, optimized);
    }
  };

  const getNextHostNode = vnode => {
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

  const render = (vnode, container, isSVG) => {
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

  const internals = {
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
  let hydrate;
  let hydrateNode;

  if (createHydrationFns) {
    [hydrate, hydrateNode] = createHydrationFns(internals);
  }

  return {
    render,
    hydrate,
    createApp: createAppAPI(render, hydrate)
  };
}

function toggleRecurse(_ref14, allowed) {
  let {
    effect,
    update
  } = _ref14;
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

  const ch1 = n1.children;
  const ch2 = n2.children;

  if (isArray(ch1) && isArray(ch2)) {
    for (let i = 0; i < ch1.length; i++) {
      // this is only called in the optimized path so array children are
      // guaranteed to be vnodes
      const c1 = ch1[i];
      let c2 = ch2[i];

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
  const p = arr.slice();
  const result = [0];
  let i, j, u, v, c;
  const len = arr.length;

  for (i = 0; i < len; i++) {
    const arrI = arr[i];

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

const isTeleport = type => type.__isTeleport;

const Fragment = Symbol(undefined);
const Text = Symbol(undefined);
const Comment = Symbol(undefined);
const Static = Symbol(undefined); // Since v-if and v-for are the two possible ways node structure can dynamically
// change, once we consider v-if branches and each v-for fragment a block, we
// can divide a template into nested blocks, and within each block the node
// structure would be stable. This allows us to skip most children diffing
// and only worry about the dynamic nodes (indicated by patch flags).

const blockStack = [];
let currentBlock = null;
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


let isBlockTreeEnabled = 1;
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

const InternalObjectKey = `__vInternal`;

const normalizeKey = _ref18 => {
  let {
    key
  } = _ref18;
  return key != null ? key : null;
};

const normalizeRef = _ref19 => {
  let {
    ref,
    ref_key,
    ref_for
  } = _ref19;
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

  const vnode = {
    __v_isVNode: true,
    __v_skip: true,
    type,
    props,
    key: props && normalizeKey(props),
    ref: props && normalizeRef(props),
    scopeId: currentScopeId,
    slotScopeIds: null,
    children,
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
    shapeFlag,
    patchFlag,
    dynamicProps,
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

const createVNode = _createVNode;

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
    const cloned = cloneVNode(type, props, true
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
    let {
      class: klass,
      style
    } = props;

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


  const shapeFlag = isString(type) ? 1
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
  }

  // This is intentionally NOT using spread or extend to avoid the runtime
  // key enumeration cost.
  const {
    props,
    ref,
    patchFlag,
    children
  } = vnode;
  const mergedProps = extraProps ? mergeProps(props || {}, extraProps) : props;
  const cloned = {
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
  let type = 0;
  const {
    shapeFlag
  } = vnode;

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
      const slot = children.default;

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
      const slotFlag = children._;

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
  const ret = {};

  for (let i = 0; i < arguments.length; i++) {
    const toMerge = i < 0 || arguments.length <= i ? undefined : arguments[i];

    for (const key in toMerge) {
      if (key === 'class') {
        if (ret.class !== toMerge.class) {
          ret.class = normalizeClass([ret.class, toMerge.class]);
        }
      } else if (key === 'style') {
        ret.style = normalizeStyle([ret.style, toMerge.style]);
      } else if (isOn(key)) {
        const existing = ret[key];
        const incoming = toMerge[key];

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

const emptyAppContext = createAppContext();
let uid$1 = 0;

function createComponentInstance(vnode, parent, suspense) {
  const type = vnode.type; // inherit parent app context - or - if root, adopt from root vnode

  const appContext = (parent ? parent.appContext : vnode.appContext) || emptyAppContext;
  const instance = {
    uid: uid$1++,
    vnode,
    type,
    parent,
    appContext,
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
    suspense,
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

let currentInstance = null;

const getCurrentInstance = () => currentInstance || currentRenderingInstance;

const setCurrentInstance = instance => {
  currentInstance = instance;
  instance.scope.on();
};

const unsetCurrentInstance = () => {
  currentInstance && currentInstance.scope.off();
  currentInstance = null;
};

function isStatefulComponent(instance) {
  return instance.vnode.shapeFlag & 4
  /* ShapeFlags.STATEFUL_COMPONENT */
  ;
}

let isInSSRComponentSetup = false;

function setupComponent(instance, isSSR) {
  if (isSSR === void 0) {
    isSSR = false;
  }

  isInSSRComponentSetup = isSSR;
  const {
    props,
    children
  } = instance.vnode;
  const isStateful = isStatefulComponent(instance);
  initProps(instance, props, isStateful, isSSR);
  initSlots(instance, children);
  const setupResult = isStateful ? setupStatefulComponent(instance, isSSR) : undefined;
  isInSSRComponentSetup = false;
  return setupResult;
}

function setupStatefulComponent(instance, isSSR) {

  const Component = instance.type;


  instance.accessCache = Object.create(null); // 1. create public instance / render proxy
  // also mark it raw so it's never observed

  instance.proxy = markRaw(new Proxy(instance.ctx, PublicInstanceProxyHandlers));


  const {
    setup
  } = Component;

  if (setup) {
    const setupContext = instance.setupContext = setup.length > 1 ? createSetupContext(instance) : null;
    setCurrentInstance(instance);
    pauseTracking();
    const setupResult = callWithErrorHandling(setup, instance, 0
    /* ErrorCodes.SETUP_FUNCTION */
    , [instance.props, setupContext]);
    resetTracking();
    unsetCurrentInstance();

    if (isPromise$1(setupResult)) {
      setupResult.then(unsetCurrentInstance, unsetCurrentInstance);

      if (isSSR) {
        // return the promise so server-renderer can wait on it
        return setupResult.then(resolvedResult => {
          handleSetupResult(instance, resolvedResult, isSSR);
        }).catch(e => {
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

let compile;

function finishComponentSetup(instance, isSSR, skipOptions) {
  const Component = instance.type; // template / render function normalization
  // could be already set when returned from setup()

  if (!instance.render) {
    // only do on-the-fly compile if not in SSR - SSR on-the-fly compilation
    // is done by server-renderer
    if (!isSSR && compile && !Component.render) {
      const template = Component.template || resolveMergedOptions(instance).template;

      if (template) {

        const {
          isCustomElement,
          compilerOptions
        } = instance.appContext.config;
        const {
          delimiters,
          compilerOptions: componentCompilerOptions
        } = Component;
        const finalCompilerOptions = extend(extend({
          isCustomElement,
          delimiters
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
    get(target, key) {
      track(instance, "get"
      /* TrackOpTypes.GET */
      , '$attrs');
      return target[key];
    }

  });
}

function createSetupContext(instance) {
  const expose = exposed => {

    instance.exposed = exposed || {};
  };

  let attrs;

  {
    return {
      get attrs() {
        return attrs || (attrs = createAttrsProxy(instance));
      },

      slots: instance.slots,
      emit: instance.emit,
      expose
    };
  }
}

function getExposeProxy(instance) {
  if (instance.exposed) {
    return instance.exposeProxy || (instance.exposeProxy = new Proxy(proxyRefs(markRaw(instance.exposed)), {
      get(target, key) {
        if (key in target) {
          return target[key];
        } else if (key in publicPropertiesMap) {
          return publicPropertiesMap[key](instance);
        }
      },

      has(target, key) {
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

const computed = (getterOrOptions, debugOptions) => {
  // @ts-ignore
  return computed$1(getterOrOptions, debugOptions, isInSSRComponentSetup);
}; // dev only


function h(type, propsOrChildren, children) {
  const l = arguments.length;

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

const ssrContextKey = Symbol(``);

const useSSRContext = () => {
  {
    const ctx = inject(ssrContextKey);

    return ctx;
  }
};


const version = "3.2.45";

const svgNS = 'http://www.w3.org/2000/svg';
const doc = typeof document !== 'undefined' ? document : null;
const templateContainer = doc && /*#__PURE__*/doc.createElement('template');
const nodeOps = {
  insert: (child, parent, anchor) => {
    parent.insertBefore(child, anchor || null);
  },
  remove: child => {
    const parent = child.parentNode;

    if (parent) {
      parent.removeChild(child);
    }
  },
  createElement: (tag, isSVG, is, props) => {
    const el = isSVG ? doc.createElementNS(svgNS, tag) : doc.createElement(tag, is ? {
      is
    } : undefined);

    if (tag === 'select' && props && props.multiple != null) {
      el.setAttribute('multiple', props.multiple);
    }

    return el;
  },
  createText: text => doc.createTextNode(text),
  createComment: text => doc.createComment(text),
  setText: (node, text) => {
    node.nodeValue = text;
  },
  setElementText: (el, text) => {
    el.textContent = text;
  },
  parentNode: node => node.parentNode,
  nextSibling: node => node.nextSibling,
  querySelector: selector => doc.querySelector(selector),

  setScopeId(el, id) {
    el.setAttribute(id, '');
  },

  // __UNSAFE__
  // Reason: innerHTML.
  // Static content here can only come from compiled templates.
  // As long as the user only uses trusted templates, this is safe.
  insertStaticContent(content, parent, anchor, isSVG, start, end) {
    // <parent> before | first ... last | anchor </parent>
    const before = anchor ? anchor.previousSibling : parent.lastChild; // #5308 can only take cached path if:
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
      templateContainer.innerHTML = isSVG ? `<svg>${content}</svg>` : content;
      const template = templateContainer.content;

      if (isSVG) {
        // remove outer svg wrapper
        const wrapper = template.firstChild;

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
  const transitionClasses = el._vtc;

  if (transitionClasses) {
    value = (value ? [value, ...transitionClasses] : [...transitionClasses]).join(' ');
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
  const style = el.style;
  const isCssString = isString(next);

  if (next && !isCssString) {
    for (const key in next) {
      setStyle(style, key, next[key]);
    }

    if (prev && !isString(prev)) {
      for (const key in prev) {
        if (next[key] == null) {
          setStyle(style, key, '');
        }
      }
    }
  } else {
    const currentDisplay = style.display;

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
const importantRE = /\s*!important$/;

function setStyle(style, name, val) {
  if (isArray(val)) {
    val.forEach(v => setStyle(style, name, v));
  } else {
    if (val == null) val = '';

    if (name.startsWith('--')) {
      // custom property definition
      style.setProperty(name, val);
    } else {
      const prefixed = autoPrefix(style, name);

      if (importantRE.test(val)) {
        // !important
        style.setProperty(hyphenate(prefixed), val.replace(importantRE, ''), 'important');
      } else {
        style[prefixed] = val;
      }
    }
  }
}

const prefixes = ['Webkit', 'Moz', 'ms'];
const prefixCache = {};

function autoPrefix(style, rawName) {
  const cached = prefixCache[rawName];

  if (cached) {
    return cached;
  }

  let name = camelize(rawName);

  if (name !== 'filter' && name in style) {
    return prefixCache[rawName] = name;
  }

  name = capitalize(name);

  for (let i = 0; i < prefixes.length; i++) {
    const prefixed = prefixes[i] + name;

    if (prefixed in style) {
      return prefixCache[rawName] = prefixed;
    }
  }

  return rawName;
}

const xlinkNS = 'http://www.w3.org/1999/xlink';

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
    const isBoolean = isSpecialBooleanAttr(key);

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
    const newValue = value == null ? '' : value;

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

  let needRemove = false;

  if (value === '' || value == null) {
    const type = typeof el[key];

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
  } catch (e) {
  }

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
  }

  // vei = vue event invokers
  const invokers = el._vei || (el._vei = {});
  const existingInvoker = invokers[rawName];

  if (nextValue && existingInvoker) {
    // patch
    existingInvoker.value = nextValue;
  } else {
    const [name, options] = parseName(rawName);

    if (nextValue) {
      // add
      const invoker = invokers[rawName] = createInvoker(nextValue, instance);
      addEventListener(el, name, invoker, options);
    } else if (existingInvoker) {
      // remove
      removeEventListener(el, name, existingInvoker, options);
      invokers[rawName] = undefined;
    }
  }
}

const optionsModifierRE = /(?:Once|Passive|Capture)$/;

function parseName(name) {
  let options;

  if (optionsModifierRE.test(name)) {
    options = {};
    let m;

    while (m = name.match(optionsModifierRE)) {
      name = name.slice(0, name.length - m[0].length);
      options[m[0].toLowerCase()] = true;
    }
  }

  const event = name[2] === ':' ? name.slice(3) : hyphenate(name.slice(2));
  return [event, options];
} // To avoid the overhead of repeatedly calling Date.now(), we cache
// and use the same timestamp for all event listeners attached in the same tick.


let cachedNow = 0;
const p = /*#__PURE__*/Promise.resolve();

const getNow = () => cachedNow || (p.then(() => cachedNow = 0), cachedNow = Date.now());

function createInvoker(initialValue, instance) {
  const invoker = e => {
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
    const originalStop = e.stopImmediatePropagation;

    e.stopImmediatePropagation = () => {
      originalStop.call(e);
      e._stopped = true;
    };

    return value.map(fn => e => !e._stopped && fn && fn(e));
  } else {
    return value;
  }
}

const nativeOnRE = /^on[a-z]/;

const patchProp = function (el, key, prevValue, nextValue, isSVG, prevChildren, parentComponent, parentSuspense, unmountChildren) {
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

const TRANSITION = 'transition';
const ANIMATION = 'animation'; // DOM Transition is a higher-order-component based on the platform-agnostic
// base Transition component, with DOM-specific logic.

const Transition = (props, _ref) => {
  let {
    slots
  } = _ref;
  return h(BaseTransition, resolveTransitionProps(props), slots);
};

Transition.displayName = 'Transition';
const DOMTransitionPropsValidators = {
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

const callHook = function (hook, args) {
  if (args === void 0) {
    args = [];
  }

  if (isArray(hook)) {
    hook.forEach(h => h(...args));
  } else if (hook) {
    hook(...args);
  }
};
/**
 * Check if a hook expects a callback (2nd arg), which means the user
 * intends to explicitly control the end of the transition.
 */


const hasExplicitCallback = hook => {
  return hook ? isArray(hook) ? hook.some(h => h.length > 1) : hook.length > 1 : false;
};

function resolveTransitionProps(rawProps) {
  const baseProps = {};

  for (const key in rawProps) {
    if (!(key in DOMTransitionPropsValidators)) {
      baseProps[key] = rawProps[key];
    }
  }

  if (rawProps.css === false) {
    return baseProps;
  }

  const {
    name = 'v',
    type,
    duration,
    enterFromClass = `${name}-enter-from`,
    enterActiveClass = `${name}-enter-active`,
    enterToClass = `${name}-enter-to`,
    appearFromClass = enterFromClass,
    appearActiveClass = enterActiveClass,
    appearToClass = enterToClass,
    leaveFromClass = `${name}-leave-from`,
    leaveActiveClass = `${name}-leave-active`,
    leaveToClass = `${name}-leave-to`
  } = rawProps;
  const durations = normalizeDuration(duration);
  const enterDuration = durations && durations[0];
  const leaveDuration = durations && durations[1];
  const {
    onBeforeEnter,
    onEnter,
    onEnterCancelled,
    onLeave,
    onLeaveCancelled,
    onBeforeAppear = onBeforeEnter,
    onAppear = onEnter,
    onAppearCancelled = onEnterCancelled
  } = baseProps;

  const finishEnter = (el, isAppear, done) => {
    removeTransitionClass(el, isAppear ? appearToClass : enterToClass);
    removeTransitionClass(el, isAppear ? appearActiveClass : enterActiveClass);
    done && done();
  };

  const finishLeave = (el, done) => {
    el._isLeaving = false;
    removeTransitionClass(el, leaveFromClass);
    removeTransitionClass(el, leaveToClass);
    removeTransitionClass(el, leaveActiveClass);
    done && done();
  };

  const makeEnterHook = isAppear => {
    return (el, done) => {
      const hook = isAppear ? onAppear : onEnter;

      const resolve = () => finishEnter(el, isAppear, done);

      callHook(hook, [el, resolve]);
      nextFrame(() => {
        removeTransitionClass(el, isAppear ? appearFromClass : enterFromClass);
        addTransitionClass(el, isAppear ? appearToClass : enterToClass);

        if (!hasExplicitCallback(hook)) {
          whenTransitionEnds(el, type, enterDuration, resolve);
        }
      });
    };
  };

  return extend(baseProps, {
    onBeforeEnter(el) {
      callHook(onBeforeEnter, [el]);
      addTransitionClass(el, enterFromClass);
      addTransitionClass(el, enterActiveClass);
    },

    onBeforeAppear(el) {
      callHook(onBeforeAppear, [el]);
      addTransitionClass(el, appearFromClass);
      addTransitionClass(el, appearActiveClass);
    },

    onEnter: makeEnterHook(false),
    onAppear: makeEnterHook(true),

    onLeave(el, done) {
      el._isLeaving = true;

      const resolve = () => finishLeave(el, done);

      addTransitionClass(el, leaveFromClass); // force reflow so *-leave-from classes immediately take effect (#2593)

      forceReflow();
      addTransitionClass(el, leaveActiveClass);
      nextFrame(() => {
        if (!el._isLeaving) {
          // cancelled
          return;
        }

        removeTransitionClass(el, leaveFromClass);
        addTransitionClass(el, leaveToClass);

        if (!hasExplicitCallback(onLeave)) {
          whenTransitionEnds(el, type, leaveDuration, resolve);
        }
      });
      callHook(onLeave, [el, resolve]);
    },

    onEnterCancelled(el) {
      finishEnter(el, false);
      callHook(onEnterCancelled, [el]);
    },

    onAppearCancelled(el) {
      finishEnter(el, true);
      callHook(onAppearCancelled, [el]);
    },

    onLeaveCancelled(el) {
      finishLeave(el);
      callHook(onLeaveCancelled, [el]);
    }

  });
}

function normalizeDuration(duration) {
  if (duration == null) {
    return null;
  } else if (isObject$1(duration)) {
    return [NumberOf(duration.enter), NumberOf(duration.leave)];
  } else {
    const n = NumberOf(duration);
    return [n, n];
  }
}

function NumberOf(val) {
  const res = toNumber(val);
  return res;
}

function addTransitionClass(el, cls) {
  cls.split(/\s+/).forEach(c => c && el.classList.add(c));
  (el._vtc || (el._vtc = new Set())).add(cls);
}

function removeTransitionClass(el, cls) {
  cls.split(/\s+/).forEach(c => c && el.classList.remove(c));
  const {
    _vtc
  } = el;

  if (_vtc) {
    _vtc.delete(cls);

    if (!_vtc.size) {
      el._vtc = undefined;
    }
  }
}

function nextFrame(cb) {
  requestAnimationFrame(() => {
    requestAnimationFrame(cb);
  });
}

let endId = 0;

function whenTransitionEnds(el, expectedType, explicitTimeout, resolve) {
  const id = el._endId = ++endId;

  const resolveIfNotStale = () => {
    if (id === el._endId) {
      resolve();
    }
  };

  if (explicitTimeout) {
    return setTimeout(resolveIfNotStale, explicitTimeout);
  }

  const {
    type,
    timeout,
    propCount
  } = getTransitionInfo(el, expectedType);

  if (!type) {
    return resolve();
  }

  const endEvent = type + 'end';
  let ended = 0;

  const end = () => {
    el.removeEventListener(endEvent, onEnd);
    resolveIfNotStale();
  };

  const onEnd = e => {
    if (e.target === el && ++ended >= propCount) {
      end();
    }
  };

  setTimeout(() => {
    if (ended < propCount) {
      end();
    }
  }, timeout + 1);
  el.addEventListener(endEvent, onEnd);
}

function getTransitionInfo(el, expectedType) {
  const styles = window.getComputedStyle(el); // JSDOM may return undefined for transition properties

  const getStyleProperties = key => (styles[key] || '').split(', ');

  const transitionDelays = getStyleProperties(`${TRANSITION}Delay`);
  const transitionDurations = getStyleProperties(`${TRANSITION}Duration`);
  const transitionTimeout = getTimeout(transitionDelays, transitionDurations);
  const animationDelays = getStyleProperties(`${ANIMATION}Delay`);
  const animationDurations = getStyleProperties(`${ANIMATION}Duration`);
  const animationTimeout = getTimeout(animationDelays, animationDurations);
  let type = null;
  let timeout = 0;
  let propCount = 0;
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

  const hasTransform = type === TRANSITION && /\b(transform|all)(,|$)/.test(getStyleProperties(`${TRANSITION}Property`).toString());
  return {
    type,
    timeout,
    propCount,
    hasTransform
  };
}

function getTimeout(delays, durations) {
  while (delays.length < durations.length) {
    delays = delays.concat(delays);
  }

  return Math.max(...durations.map((d, i) => toMs(d) + toMs(delays[i])));
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

const getModelAssigner = vnode => {
  const fn = vnode.props['onUpdate:modelValue'] || false;
  return isArray(fn) ? value => invokeArrayFns(fn, value) : fn;
};

function onCompositionStart(e) {
  e.target.composing = true;
}

function onCompositionEnd(e) {
  const target = e.target;

  if (target.composing) {
    target.composing = false;
    target.dispatchEvent(new Event('input'));
  }
} // We are exporting the v-model runtime directly as vnode hooks so that it can
// be tree-shaken in case v-model is never used.


const vModelText = {
  created(el, _ref3, vnode) {
    let {
      modifiers: {
        lazy,
        trim,
        number
      }
    } = _ref3;
    el._assign = getModelAssigner(vnode);
    const castToNumber = number || vnode.props && vnode.props.type === 'number';
    addEventListener(el, lazy ? 'change' : 'input', e => {
      if (e.target.composing) return;
      let domValue = el.value;

      if (trim) {
        domValue = domValue.trim();
      }

      if (castToNumber) {
        domValue = toNumber(domValue);
      }

      el._assign(domValue);
    });

    if (trim) {
      addEventListener(el, 'change', () => {
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
  mounted(el, _ref4) {
    let {
      value
    } = _ref4;
    el.value = value == null ? '' : value;
  },

  beforeUpdate(el, _ref5, vnode) {
    let {
      value,
      modifiers: {
        lazy,
        trim,
        number
      }
    } = _ref5;
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

    const newValue = value == null ? '' : value;

    if (el.value !== newValue) {
      el.value = newValue;
    }
  }

};

const systemModifiers = ['ctrl', 'shift', 'alt', 'meta'];
const modifierGuards = {
  stop: e => e.stopPropagation(),
  prevent: e => e.preventDefault(),
  self: e => e.target !== e.currentTarget,
  ctrl: e => !e.ctrlKey,
  shift: e => !e.shiftKey,
  alt: e => !e.altKey,
  meta: e => !e.metaKey,
  left: e => 'button' in e && e.button !== 0,
  middle: e => 'button' in e && e.button !== 1,
  right: e => 'button' in e && e.button !== 2,
  exact: (e, modifiers) => systemModifiers.some(m => e[`${m}Key`] && !modifiers.includes(m))
};
/**
 * @private
 */

const withModifiers = (fn, modifiers) => {
  return function (event) {
    for (let i = 0; i < modifiers.length; i++) {
      const guard = modifierGuards[modifiers[i]];
      if (guard && guard(event, modifiers)) return;
    }

    for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
      args[_key2 - 1] = arguments[_key2];
    }

    return fn(event, ...args);
  };
}; // Kept for 2.x compat.
// Note: IE11 compat for `spacebar` and `del` is removed for now.


const keyNames = {
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

const withKeys = (fn, modifiers) => {
  return event => {
    if (!('key' in event)) {
      return;
    }

    const eventKey = hyphenate(event.key);

    if (modifiers.some(k => k === eventKey || keyNames[k] === eventKey)) {
      return fn(event);
    }
  };
};

const vShow = {
  beforeMount(el, _ref15, _ref16) {
    let {
      value
    } = _ref15;
    let {
      transition
    } = _ref16;
    el._vod = el.style.display === 'none' ? '' : el.style.display;

    if (transition && value) {
      transition.beforeEnter(el);
    } else {
      setDisplay(el, value);
    }
  },

  mounted(el, _ref17, _ref18) {
    let {
      value
    } = _ref17;
    let {
      transition
    } = _ref18;

    if (transition && value) {
      transition.enter(el);
    }
  },

  updated(el, _ref19, _ref20) {
    let {
      value,
      oldValue
    } = _ref19;
    let {
      transition
    } = _ref20;
    if (!value === !oldValue) return;

    if (transition) {
      if (value) {
        transition.beforeEnter(el);
        setDisplay(el, true);
        transition.enter(el);
      } else {
        transition.leave(el, () => {
          setDisplay(el, false);
        });
      }
    } else {
      setDisplay(el, value);
    }
  },

  beforeUnmount(el, _ref21) {
    let {
      value
    } = _ref21;
    setDisplay(el, value);
  }

};

function setDisplay(el, value) {
  el.style.display = value ? el._vod : 'none';
} // SSR vnode transforms, only used when user includes client-oriented render

const rendererOptions = /*#__PURE__*/extend({
  patchProp
}, nodeOps); // lazy create the renderer - this makes core renderer logic tree-shakable
// in case the user only imports reactivity utilities from Vue.

let renderer;

function ensureRenderer() {
  return renderer || (renderer = createRenderer(rendererOptions));
}

const createApp = function () {
  const app = ensureRenderer().createApp(...arguments);

  const {
    mount
  } = app;

  app.mount = containerOrSelector => {
    const container = normalizeContainer(containerOrSelector);
    if (!container) return;
    const component = app._component;

    if (!isFunction(component) && !component.render && !component.template) {
      // __UNSAFE__
      // Reason: potential execution of JS expressions in in-DOM template.
      // The user must make sure the in-DOM template is trusted. If it's
      // rendered by the server, the template should not contain any user data.
      component.template = container.innerHTML;
    } // clear content before mounting


    container.innerHTML = '';
    const proxy = mount(container, false, container instanceof SVGElement);

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
    const res = document.querySelector(container);

    return res;
  }

  return container;
}

/**
 * Media Event bus - used for communication between joomla and vue
 */
class Event$1 {
  /**
     * Media Event constructor
     */
  constructor() {
    this.events = {};
  }
  /**
     * Fire an event
     * @param event
     * @param data
     */


  fire(event, data) {
    if (data === void 0) {
      data = null;
    }

    if (this.events[event]) {
      this.events[event].forEach(fn => fn(data));
    }
  }
  /**
     * Listen to events
     * @param event
     * @param callback
     */


  listen(event, callback) {
    this.events[event] = this.events[event] || [];
    this.events[event].push(callback);
  }

}

// Loading state
const SET_IS_LOADING = 'SET_IS_LOADING'; // Selecting media items

const SELECT_DIRECTORY = 'SELECT_DIRECTORY';
const SELECT_BROWSER_ITEM = 'SELECT_BROWSER_ITEM';
const SELECT_BROWSER_ITEMS = 'SELECT_BROWSER_ITEMS';
const UNSELECT_BROWSER_ITEM = 'UNSELECT_BROWSER_ITEM';
const UNSELECT_ALL_BROWSER_ITEMS = 'UNSELECT_ALL_BROWSER_ITEMS'; // In/Decrease grid item size

const INCREASE_GRID_SIZE = 'INCREASE_GRID_SIZE';
const DECREASE_GRID_SIZE = 'DECREASE_GRID_SIZE'; // Api handlers

const LOAD_CONTENTS_SUCCESS = 'LOAD_CONTENTS_SUCCESS';
const LOAD_FULL_CONTENTS_SUCCESS = 'LOAD_FULL_CONTENTS_SUCCESS';
const CREATE_DIRECTORY_SUCCESS = 'CREATE_DIRECTORY_SUCCESS';
const UPLOAD_SUCCESS = 'UPLOAD_SUCCESS'; // Create folder modal

const SHOW_CREATE_FOLDER_MODAL = 'SHOW_CREATE_FOLDER_MODAL';
const HIDE_CREATE_FOLDER_MODAL = 'HIDE_CREATE_FOLDER_MODAL'; // Confirm Delete Modal

const SHOW_CONFIRM_DELETE_MODAL = 'SHOW_CONFIRM_DELETE_MODAL';
const HIDE_CONFIRM_DELETE_MODAL = 'HIDE_CONFIRM_DELETE_MODAL'; // Infobar

const SHOW_INFOBAR = 'SHOW_INFOBAR';
const HIDE_INFOBAR = 'HIDE_INFOBAR'; // Delete items

const DELETE_SUCCESS = 'DELETE_SUCCESS'; // List view

const CHANGE_LIST_VIEW = 'CHANGE_LIST_VIEW'; // Preview modal

const SHOW_PREVIEW_MODAL = 'SHOW_PREVIEW_MODAL';
const HIDE_PREVIEW_MODAL = 'HIDE_PREVIEW_MODAL'; // Rename modal

const SHOW_RENAME_MODAL = 'SHOW_RENAME_MODAL';
const HIDE_RENAME_MODAL = 'HIDE_RENAME_MODAL';
const RENAME_SUCCESS = 'RENAME_SUCCESS'; // Share model

const SHOW_SHARE_MODAL = 'SHOW_SHARE_MODAL';
const HIDE_SHARE_MODAL = 'HIDE_SHARE_MODAL'; // Search Query

const SET_SEARCH_QUERY = 'SET_SEARCH_QUERY';

class Notifications {
  /* Send and success notification */
  // eslint-disable-next-line class-methods-use-this
  success(message, options) {
    // eslint-disable-next-line no-use-before-define
    notifications.notify(message, {
      type: 'message',
      // @todo rename it to success
      dismiss: true,
      ...options
    });
  }
  /* Send an error notification */
  // eslint-disable-next-line class-methods-use-this


  error(message, options) {
    // eslint-disable-next-line no-use-before-define
    notifications.notify(message, {
      type: 'error',
      // @todo rename it to danger
      dismiss: true,
      ...options
    });
  }
  /* Ask the user a question */
  // eslint-disable-next-line class-methods-use-this


  ask(message) {
    return window.confirm(message);
  }
  /* Send a notification */
  // eslint-disable-next-line class-methods-use-this


  notify(message, options) {
    let timer;

    if (options.type === 'message') {
      timer = 3000;
    }

    Joomla.renderMessages({
      [options.type]: [Joomla.Text._(message)]
    }, undefined, true, timer);
  }

} // eslint-disable-next-line import/no-mutable-exports,import/prefer-default-export


let notifications = new Notifications();

var script$t = {
  name: 'MediaApp',
  data() {
    return {
      // The full height of the app in px
      fullHeight: '',
    };
  },
  computed: {
    disks() {
      return this.$store.state.disks;
    },
  },
  created() {
    // Listen to the toolbar events
    MediaManager.Event.listen('onClickCreateFolder', () => this.$store.commit(SHOW_CREATE_FOLDER_MODAL));
    MediaManager.Event.listen('onClickDelete', () => {
      if (this.$store.state.selectedItems.length > 0) {
        this.$store.commit(SHOW_CONFIRM_DELETE_MODAL);
      } else {
        notifications.error('COM_MEDIA_PLEASE_SELECT_ITEM');
      }
    });
  },
  mounted() {
    // Set the full height and add event listener when dom is updated
    this.$nextTick(() => {
      this.setFullHeight();
      // Add the global resize event listener
      window.addEventListener('resize', this.setFullHeight);
    });

    // Initial load the data
    this.$store.dispatch('getContents', this.$store.state.selectedDirectory);
  },
  beforeUnmount() {
    // Remove the global resize event listener
    window.removeEventListener('resize', this.setFullHeight);
  },
  methods: {
    /* Set the full height on the app container */
    setFullHeight() {
      this.fullHeight = `${window.innerHeight - this.$el.getBoundingClientRect().top}px`;
    },
  },
};

const _hoisted_1$t = { class: "media-container" };
const _hoisted_2$r = { class: "media-sidebar" };
const _hoisted_3$h = { class: "media-main" };

function render$t(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_disk = resolveComponent("media-disk");
  const _component_media_toolbar = resolveComponent("media-toolbar");
  const _component_media_browser = resolveComponent("media-browser");
  const _component_media_upload = resolveComponent("media-upload");
  const _component_media_create_folder_modal = resolveComponent("media-create-folder-modal");
  const _component_media_preview_modal = resolveComponent("media-preview-modal");
  const _component_media_rename_modal = resolveComponent("media-rename-modal");
  const _component_media_share_modal = resolveComponent("media-share-modal");
  const _component_media_confirm_delete_modal = resolveComponent("media-confirm-delete-modal");

  return (openBlock(), createElementBlock("div", _hoisted_1$t, [
    createBaseVNode("div", _hoisted_2$r, [
      (openBlock(true), createElementBlock(Fragment, null, renderList($options.disks, (disk, index) => {
        return (openBlock(), createBlock(_component_media_disk, {
          key: index,
          uid: index,
          disk: disk
        }, null, 8 /* PROPS */, ["uid", "disk"]))
      }), 128 /* KEYED_FRAGMENT */))
    ]),
    createBaseVNode("div", _hoisted_3$h, [
      createVNode(_component_media_toolbar),
      createVNode(_component_media_browser)
    ]),
    createVNode(_component_media_upload),
    createVNode(_component_media_create_folder_modal),
    createVNode(_component_media_preview_modal),
    createVNode(_component_media_rename_modal),
    createVNode(_component_media_share_modal),
    createVNode(_component_media_confirm_delete_modal)
  ]))
}

script$t.render = render$t;
script$t.__file = "administrator/components/com_media/resources/scripts/components/app.vue";

var script$s = {
  name: 'MediaDisk',
  // eslint-disable-next-line vue/require-prop-types
  props: ['disk', 'uid'],
  computed: {
    diskId() {
      return `disk-${this.uid + 1}`;
    },
  },
};

const _hoisted_1$s = { class: "media-disk" };
const _hoisted_2$q = ["id"];

function render$s(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_drive = resolveComponent("media-drive");

  return (openBlock(), createElementBlock("div", _hoisted_1$s, [
    createBaseVNode("h2", {
      id: $options.diskId,
      class: "media-disk-name"
    }, toDisplayString($props.disk.displayName), 9 /* TEXT, PROPS */, _hoisted_2$q),
    (openBlock(true), createElementBlock(Fragment, null, renderList($props.disk.drives, (drive, index) => {
      return (openBlock(), createBlock(_component_media_drive, {
        key: index,
        "disk-id": $options.diskId,
        counter: index,
        drive: drive,
        total: $props.disk.drives.length
      }, null, 8 /* PROPS */, ["disk-id", "counter", "drive", "total"]))
    }), 128 /* KEYED_FRAGMENT */))
  ]))
}

script$s.render = render$s;
script$s.__file = "administrator/components/com_media/resources/scripts/components/tree/disk.vue";

var navigable = {
  methods: {
    navigateTo(path) {
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
    isActive() {
      return (this.$store.state.selectedDirectory === this.drive.root);
    },
    getTabindex() {
      return this.isActive ? 0 : -1;
    },
  },
  methods: {
    /* Handle the on drive click event */
    onDriveClick() {
      this.navigateTo(this.drive.root);
    },
    moveFocusToChildElement(nextRoot) {
      this.$refs[nextRoot].setFocusToFirstChild();
    },
    restoreFocus() {
      this.$refs['drive-root'].focus();
    },
  },
};

const _hoisted_1$r = ["aria-labelledby"];
const _hoisted_2$p = ["aria-setsize", "tabindex"];
const _hoisted_3$g = { class: "item-name" };

function render$r(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_tree = resolveComponent("media-tree");

  return (openBlock(), createElementBlock("div", {
    class: "media-drive",
    onClick: _cache[2] || (_cache[2] = withModifiers($event => ($options.onDriveClick()), ["stop","prevent"]))
  }, [
    createBaseVNode("ul", {
      class: "media-tree",
      role: "tree",
      "aria-labelledby": $props.diskId
    }, [
      createBaseVNode("li", {
        class: normalizeClass({active: $options.isActive, 'media-tree-item': true, 'media-drive-name': true}),
        role: "none"
      }, [
        createBaseVNode("a", {
          ref: "drive-root",
          role: "treeitem",
          "aria-level": "1",
          "aria-setsize": $props.counter,
          "aria-posinset": 1,
          tabindex: $options.getTabindex,
          onKeyup: [
            _cache[0] || (_cache[0] = withKeys($event => ($options.moveFocusToChildElement($props.drive.root)), ["right"])),
            _cache[1] || (_cache[1] = withKeys((...args) => ($options.onDriveClick && $options.onDriveClick(...args)), ["enter"]))
          ]
        }, [
          createBaseVNode("span", _hoisted_3$g, toDisplayString($props.drive.displayName), 1 /* TEXT */)
        ], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_2$p),
        createVNode(_component_media_tree, {
          ref: $props.drive.root,
          root: $props.drive.root,
          level: 2,
          "parent-index": 0,
          onMoveFocusToParent: $options.restoreFocus
        }, null, 8 /* PROPS */, ["root", "onMoveFocusToParent"])
      ], 2 /* CLASS */)
    ], 8 /* PROPS */, _hoisted_1$r)
  ]))
}

script$r.render = render$r;
script$r.__file = "administrator/components/com_media/resources/scripts/components/tree/drive.vue";

var script$q = {
  name: 'MediaTree',
  mixins: [navigable],
  props: {
    root: {
      type: String,
      required: true,
    },
    level: {
      type: Number,
      required: true,
    },
    parentIndex: {
      type: Number,
      required: true,
    },
  },
  emits: ['move-focus-to-parent'],
  computed: {
    /* Get the directories */
    directories() {
      return this.$store.state.directories
        .filter((directory) => (directory.directory === this.root))
        // Sort alphabetically
        .sort((a, b) => ((a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1));
    },
  },
  methods: {
    isActive(item) {
      return (item.path === this.$store.state.selectedDirectory);
    },
    getTabindex(item) {
      return this.isActive(item) ? 0 : -1;
    },
    onItemClick(item) {
      this.navigateTo(item.path);
      window.parent.document.dispatchEvent(
        new CustomEvent(
          'onMediaFileSelected',
          {
            bubbles: true,
            cancelable: false,
            detail: {},
          },
        ),
      );
    },
    hasChildren(item) {
      return item.directories.length > 0;
    },
    isOpen(item) {
      return this.$store.state.selectedDirectory.includes(item.path);
    },
    iconClass(item) {
      return {
        fas: false,
        'icon-folder': !this.isOpen(item),
        'icon-folder-open': this.isOpen(item),
      };
    },
    setFocusToFirstChild() {
      this.$refs[`${this.root}0`][0].focus();
    },
    moveFocusToNextElement(currentIndex) {
      if ((currentIndex + 1) === this.directories.length) {
        return;
      }
      this.$refs[this.root + (currentIndex + 1)][0].focus();
    },
    moveFocusToPreviousElement(currentIndex) {
      if (currentIndex === 0) {
        return;
      }
      this.$refs[this.root + (currentIndex - 1)][0].focus();
    },
    moveFocusToChildElement(item) {
      if (!this.hasChildren(item)) {
        return;
      }
      this.$refs[item.path][0].setFocusToFirstChild();
    },
    moveFocusToParentElement() {
      this.$emit('move-focus-to-parent', this.parentIndex);
    },
    restoreFocus(parentIndex) {
      this.$refs[this.root + parentIndex][0].focus();
    },
  },
};

const _hoisted_1$q = {
  class: "media-tree",
  role: "group"
};
const _hoisted_2$o = ["aria-level", "aria-setsize", "aria-posinset", "tabindex", "onClick", "onKeyup"];
const _hoisted_3$f = { class: "item-icon" };
const _hoisted_4$a = { class: "item-name" };

function render$q(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_tree = resolveComponent("media-tree");

  return (openBlock(), createElementBlock("ul", _hoisted_1$q, [
    (openBlock(true), createElementBlock(Fragment, null, renderList($options.directories, (item, index) => {
      return (openBlock(), createElementBlock("li", {
        key: item.path,
        class: normalizeClass(["media-tree-item", {active: $options.isActive(item)}]),
        role: "none"
      }, [
        createBaseVNode("a", {
          ref_for: true,
          ref: $props.root + index,
          role: "treeitem",
          "aria-level": $props.level,
          "aria-setsize": $options.directories.length,
          "aria-posinset": index,
          tabindex: $options.getTabindex(item),
          onClick: withModifiers($event => ($options.onItemClick(item)), ["stop","prevent"]),
          onKeyup: [
            withKeys($event => ($options.moveFocusToPreviousElement(index)), ["up"]),
            withKeys($event => ($options.moveFocusToNextElement(index)), ["down"]),
            withKeys($event => ($options.onItemClick(item)), ["enter"]),
            withKeys($event => ($options.moveFocusToChildElement(item)), ["right"]),
            _cache[0] || (_cache[0] = withKeys($event => ($options.moveFocusToParentElement()), ["left"]))
          ]
        }, [
          createBaseVNode("span", _hoisted_3$f, [
            createBaseVNode("span", {
              class: normalizeClass($options.iconClass(item))
            }, null, 2 /* CLASS */)
          ]),
          createBaseVNode("span", _hoisted_4$a, toDisplayString(item.name), 1 /* TEXT */)
        ], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_2$o),
        createVNode(Transition, { name: "slide-fade" }, {
          default: withCtx(() => [
            ($options.hasChildren(item))
              ? withDirectives((openBlock(), createBlock(_component_media_tree, {
                  key: 0,
                  ref_for: true,
                  ref: item.path,
                  "aria-expanded": $options.isOpen(item) ? 'true' : 'false',
                  root: item.path,
                  level: ($props.level+1),
                  "parent-index": index,
                  onMoveFocusToParent: $options.restoreFocus
                }, null, 8 /* PROPS */, ["aria-expanded", "root", "level", "parent-index", "onMoveFocusToParent"])), [
                  [vShow, $options.isOpen(item)]
                ])
              : createCommentVNode("v-if", true)
          ]),
          _: 2 /* DYNAMIC */
        }, 1024 /* DYNAMIC_SLOTS */)
      ], 2 /* CLASS */))
    }), 128 /* KEYED_FRAGMENT */))
  ]))
}

script$q.render = render$q;
script$q.__file = "administrator/components/com_media/resources/scripts/components/tree/tree.vue";

var script$p = {
  name: 'MediaToolbar',
  computed: {
    toggleListViewBtnIcon() {
      return (this.isGridView) ? 'icon-list' : 'icon-th';
    },
    isLoading() {
      return this.$store.state.isLoading;
    },
    atLeastOneItemSelected() {
      return this.$store.state.selectedItems.length > 0;
    },
    isGridView() {
      return (this.$store.state.listView === 'grid');
    },
    allItemsSelected() {
      // eslint-disable-next-line max-len
      return (this.$store.getters.getSelectedDirectoryContents.length === this.$store.state.selectedItems.length);
    },
    search() {
      return this.$store.state.search;
    },
  },
  watch: {
    // eslint-disable-next-line
    '$store.state.selectedItems'() {
      if (!this.allItemsSelected) {
        this.$refs.mediaToolbarSelectAll.checked = false;
      }
    },
  },
  methods: {
    toggleInfoBar() {
      if (this.$store.state.showInfoBar) {
        this.$store.commit(HIDE_INFOBAR);
      } else {
        this.$store.commit(SHOW_INFOBAR);
      }
    },
    decreaseGridSize() {
      if (!this.isGridSize('sm')) {
        this.$store.commit(DECREASE_GRID_SIZE);
      }
    },
    increaseGridSize() {
      if (!this.isGridSize('xl')) {
        this.$store.commit(INCREASE_GRID_SIZE);
      }
    },
    changeListView() {
      if (this.$store.state.listView === 'grid') {
        this.$store.commit(CHANGE_LIST_VIEW, 'table');
      } else {
        this.$store.commit(CHANGE_LIST_VIEW, 'grid');
      }
    },
    toggleSelectAll() {
      if (this.allItemsSelected) {
        this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
      } else {
        // eslint-disable-next-line max-len
        this.$store.commit(SELECT_BROWSER_ITEMS, this.$store.getters.getSelectedDirectoryContents);
        window.parent.document.dispatchEvent(
          new CustomEvent(
            'onMediaFileSelected',
            {
              bubbles: true,
              cancelable: false,
              detail: {},
            },
          ),
        );
      }
    },
    isGridSize(size) {
      return (this.$store.state.gridSize === size);
    },
    changeSearch(query) {
      this.$store.commit(SET_SEARCH_QUERY, query.target.value);
    },
  },
};

const _hoisted_1$p = ["aria-label"];
const _hoisted_2$n = {
  key: 0,
  class: "media-loader"
};
const _hoisted_3$e = { class: "media-view-icons" };
const _hoisted_4$9 = ["aria-label"];
const _hoisted_5$9 = {
  class: "media-view-search-input",
  role: "search"
};
const _hoisted_6$7 = {
  for: "media_search",
  class: "visually-hidden"
};
const _hoisted_7$4 = ["placeholder", "value"];
const _hoisted_8$4 = { class: "media-view-icons" };
const _hoisted_9$4 = ["aria-label"];
const _hoisted_10$2 = /*#__PURE__*/createBaseVNode("span", {
  class: "icon-search-minus",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_11$2 = [
  _hoisted_10$2
];
const _hoisted_12$1 = ["aria-label"];
const _hoisted_13 = /*#__PURE__*/createBaseVNode("span", {
  class: "icon-search-plus",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_14 = [
  _hoisted_13
];
const _hoisted_15 = ["aria-label"];
const _hoisted_16 = ["aria-label"];
const _hoisted_17 = /*#__PURE__*/createBaseVNode("span", {
  class: "icon-info",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_18 = [
  _hoisted_17
];

function render$p(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_breadcrumb = resolveComponent("media-breadcrumb");

  return (openBlock(), createElementBlock("div", {
    class: "media-toolbar",
    role: "toolbar",
    "aria-label": _ctx.translate('COM_MEDIA_TOOLBAR_LABEL')
  }, [
    ($options.isLoading)
      ? (openBlock(), createElementBlock("div", _hoisted_2$n))
      : createCommentVNode("v-if", true),
    createBaseVNode("div", _hoisted_3$e, [
      createBaseVNode("input", {
        ref: "mediaToolbarSelectAll",
        type: "checkbox",
        class: "media-toolbar-icon media-toolbar-select-all",
        "aria-label": _ctx.translate('COM_MEDIA_SELECT_ALL'),
        onClick: _cache[0] || (_cache[0] = withModifiers((...args) => ($options.toggleSelectAll && $options.toggleSelectAll(...args)), ["stop"]))
      }, null, 8 /* PROPS */, _hoisted_4$9)
    ]),
    createVNode(_component_media_breadcrumb),
    createBaseVNode("div", _hoisted_5$9, [
      createBaseVNode("label", _hoisted_6$7, toDisplayString(_ctx.translate('COM_MEDIA_SEARCH')), 1 /* TEXT */),
      createBaseVNode("input", {
        id: "media_search",
        class: "form-control",
        type: "text",
        placeholder: _ctx.translate('COM_MEDIA_SEARCH'),
        value: $options.search,
        onInput: _cache[1] || (_cache[1] = (...args) => ($options.changeSearch && $options.changeSearch(...args)))
      }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_7$4)
    ]),
    createBaseVNode("div", _hoisted_8$4, [
      ($options.isGridView)
        ? (openBlock(), createElementBlock("button", {
            key: 0,
            type: "button",
            class: normalizeClass(["media-toolbar-icon media-toolbar-decrease-grid-size", {disabled: $options.isGridSize('sm')}]),
            "aria-label": _ctx.translate('COM_MEDIA_DECREASE_GRID'),
            onClick: _cache[2] || (_cache[2] = withModifiers($event => ($options.decreaseGridSize()), ["stop","prevent"]))
          }, _hoisted_11$2, 10 /* CLASS, PROPS */, _hoisted_9$4))
        : createCommentVNode("v-if", true),
      ($options.isGridView)
        ? (openBlock(), createElementBlock("button", {
            key: 1,
            type: "button",
            class: normalizeClass(["media-toolbar-icon media-toolbar-increase-grid-size", {disabled: $options.isGridSize('xl')}]),
            "aria-label": _ctx.translate('COM_MEDIA_INCREASE_GRID'),
            onClick: _cache[3] || (_cache[3] = withModifiers($event => ($options.increaseGridSize()), ["stop","prevent"]))
          }, _hoisted_14, 10 /* CLASS, PROPS */, _hoisted_12$1))
        : createCommentVNode("v-if", true),
      createBaseVNode("button", {
        type: "button",
        href: "#",
        class: "media-toolbar-icon media-toolbar-list-view",
        "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_LIST_VIEW'),
        onClick: _cache[4] || (_cache[4] = withModifiers($event => ($options.changeListView()), ["stop","prevent"]))
      }, [
        createBaseVNode("span", {
          class: normalizeClass($options.toggleListViewBtnIcon),
          "aria-hidden": "true"
        }, null, 2 /* CLASS */)
      ], 8 /* PROPS */, _hoisted_15),
      createBaseVNode("button", {
        type: "button",
        href: "#",
        class: "media-toolbar-icon media-toolbar-info",
        "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_INFO'),
        onClick: _cache[5] || (_cache[5] = withModifiers((...args) => ($options.toggleInfoBar && $options.toggleInfoBar(...args)), ["stop","prevent"]))
      }, _hoisted_18, 8 /* PROPS */, _hoisted_16)
    ])
  ], 8 /* PROPS */, _hoisted_1$p))
}

script$p.render = render$p;
script$p.__file = "administrator/components/com_media/resources/scripts/components/toolbar/toolbar.vue";

var script$o = {
  name: 'MediaBreadcrumb',
  mixins: [navigable],
  computed: {
    /* Get the crumbs from the current directory path */
    crumbs() {
      const items = [];

      const parts = this.$store.state.selectedDirectory.split('/');

      // Add the drive as first element
      if (parts) {
        const drive = this.findDrive(parts[0]);

        if (drive) {
          items.push(drive);
          parts.shift();
        }
      }

      parts
        .filter((crumb) => crumb.length !== 0)
        .forEach((crumb) => {
          items.push({
            name: crumb,
            path: this.$store.state.selectedDirectory.split(crumb)[0] + crumb,
          });
        });

      return items;
    },
    /* Whether or not the crumb is the last element in the list */
    isLast(item) {
      return this.crumbs.indexOf(item) === this.crumbs.length - 1;
    },
  },
  methods: {
    /* Handle the on crumb click event */
    onCrumbClick(crumb) {
      this.navigateTo(crumb.path);
      window.parent.document.dispatchEvent(
        new CustomEvent(
          'onMediaFileSelected',
          {
            bubbles: true,
            cancelable: false,
            detail: {},
          },
        ),
      );
    },
    findDrive(adapter) {
      let driveObject = null;

      this.$store.state.disks.forEach((disk) => {
        disk.drives.forEach((drive) => {
          if (drive.root.startsWith(adapter)) {
            driveObject = { name: drive.displayName, path: drive.root };
          }
        });
      });

      return driveObject;
    },
  },
};

const _hoisted_1$o = ["aria-label"];
const _hoisted_2$m = ["aria-current", "onClick"];

function render$o(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("nav", {
    class: "media-breadcrumb",
    "aria-label": _ctx.translate('COM_MEDIA_BREADCRUMB_LABEL')
  }, [
    createBaseVNode("ol", null, [
      (openBlock(true), createElementBlock(Fragment, null, renderList($options.crumbs, (val, index) => {
        return (openBlock(), createElementBlock("li", {
          key: index,
          class: "media-breadcrumb-item"
        }, [
          createBaseVNode("a", {
            href: "#",
            "aria-current": (index === Object.keys($options.crumbs).length - 1) ? 'page' : undefined,
            onClick: withModifiers($event => ($options.onCrumbClick(val)), ["stop","prevent"])
          }, toDisplayString(val.name), 9 /* TEXT, PROPS */, _hoisted_2$m)
        ]))
      }), 128 /* KEYED_FRAGMENT */))
    ])
  ], 8 /* PROPS */, _hoisted_1$o))
}

script$o.render = render$o;
script$o.__file = "administrator/components/com_media/resources/scripts/components/breadcrumb/breadcrumb.vue";

var script$n = {
  name: 'MediaBrowser',
  computed: {
    /* Get the contents of the currently selected directory */
    items() {
      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      const directories = this.$store.getters.getSelectedDirectoryDirectories
        // Sort by type and alphabetically
        .sort((a, b) => ((a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1))
        .filter((dir) => dir.name.toLowerCase().includes(this.$store.state.search.toLowerCase()));

      // eslint-disable-next-line vue/no-side-effects-in-computed-properties
      const files = this.$store.getters.getSelectedDirectoryFiles
        // Sort by type and alphabetically
        .sort((a, b) => ((a.name.toUpperCase() < b.name.toUpperCase()) ? -1 : 1))
        .filter((file) => file.name.toLowerCase().includes(this.$store.state.search.toLowerCase()));

      return [...directories, ...files];
    },
    /* The styles for the media-browser element */
    mediaBrowserStyles() {
      return {
        width: this.$store.state.showInfoBar ? '75%' : '100%',
      };
    },
    /* The styles for the media-browser element */
    listView() {
      return this.$store.state.listView;
    },
    mediaBrowserGridItemsClass() {
      return {
        [`media-browser-items-${this.$store.state.gridSize}`]: true,
      };
    },
    isModal() {
      return Joomla.getOptions('com_media', {}).isModal;
    },
    currentDirectory() {
      const parts = this.$store.state.selectedDirectory.split('/').filter((crumb) => crumb.length !== 0);

      // The first part is the name of the drive, so if we have a folder name display it. Else
      // find the filename
      if (parts.length !== 1) {
        return parts[parts.length - 1];
      }

      let diskName = '';

      this.$store.state.disks.forEach((disk) => {
        disk.drives.forEach((drive) => {
          if (drive.root === `${parts[0]}/`) {
            diskName = drive.displayName;
          }
        });
      });

      return diskName;
    },
  },
  created() {
    document.body.addEventListener('click', this.unselectAllBrowserItems, false);
  },
  beforeUnmount() {
    document.body.removeEventListener('click', this.unselectAllBrowserItems, false);
  },
  methods: {
    /* Unselect all browser items */
    unselectAllBrowserItems(event) {
      const clickedDelete = !!((event.target.id !== undefined && event.target.id === 'mediaDelete'));
      const notClickedBrowserItems = (this.$refs.browserItems
        && !this.$refs.browserItems.contains(event.target))
        || event.target === this.$refs.browserItems;

      const notClickedInfobar = this.$refs.infobar !== undefined
        && !this.$refs.infobar.$el.contains(event.target);

      const clickedOutside = notClickedBrowserItems && notClickedInfobar && !clickedDelete;
      if (clickedOutside) {
        this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);

        window.parent.document.dispatchEvent(
          new CustomEvent(
            'onMediaFileSelected',
            {
              bubbles: true,
              cancelable: false,
              detail: {
                path: '',
                thumb: false,
                fileType: false,
                extension: false,
              },
            },
          ),
        );
      }
    },

    // Listeners for drag and drop
    // Fix for Chrome
    onDragEnter(e) {
      e.stopPropagation();
      return false;
    },

    // Notify user when file is over the drop area
    onDragOver(e) {
      e.preventDefault();
      document.querySelector('.media-dragoutline').classList.add('active');
      return false;
    },

    /* Upload files */
    upload(file) {
      // Create a new file reader instance
      const reader = new FileReader();

      // Add the on load callback
      reader.onload = (progressEvent) => {
        const { result } = progressEvent.target;
        const splitIndex = result.indexOf('base64') + 7;
        const content = result.slice(splitIndex, result.length);

        // Upload the file
        this.$store.dispatch('uploadFile', {
          name: file.name,
          parent: this.$store.state.selectedDirectory,
          content,
        });
      };

      reader.readAsDataURL(file);
    },

    // Logic for the dropped file
    onDrop(e) {
      e.preventDefault();

      // Loop through array of files and upload each file
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        // eslint-disable-next-line no-plusplus,no-cond-assign
        for (let i = 0, f; f = e.dataTransfer.files[i]; i++) {
          document.querySelector('.media-dragoutline').classList.remove('active');
          this.upload(f);
        }
      }
      document.querySelector('.media-dragoutline').classList.remove('active');
    },

    // Reset the drop area border
    onDragLeave(e) {
      e.stopPropagation();
      e.preventDefault();
      document.querySelector('.media-dragoutline').classList.remove('active');
      return false;
    },
  },
};

const _hoisted_1$n = { class: "media-dragoutline" };
const _hoisted_2$l = /*#__PURE__*/createBaseVNode("span", {
  class: "icon-cloud-upload upload-icon",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_3$d = {
  key: 0,
  class: "table media-browser-table"
};
const _hoisted_4$8 = { class: "visually-hidden" };
const _hoisted_5$8 = { class: "media-browser-table-head" };
const _hoisted_6$6 = /*#__PURE__*/createBaseVNode("th", {
  class: "type",
  scope: "col"
}, null, -1 /* HOISTED */);
const _hoisted_7$3 = {
  class: "name",
  scope: "col"
};
const _hoisted_8$3 = {
  class: "size",
  scope: "col"
};
const _hoisted_9$3 = {
  class: "dimension",
  scope: "col"
};
const _hoisted_10$1 = {
  class: "created",
  scope: "col"
};
const _hoisted_11$1 = {
  class: "modified",
  scope: "col"
};
const _hoisted_12 = {
  key: 1,
  class: "media-browser-grid"
};

function render$n(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_browser_item_row = resolveComponent("media-browser-item-row");
  const _component_media_browser_item = resolveComponent("media-browser-item");
  const _component_media_infobar = resolveComponent("media-infobar");

  return (openBlock(), createElementBlock("div", null, [
    createBaseVNode("div", {
      ref: "browserItems",
      class: "media-browser",
      style: normalizeStyle($options.mediaBrowserStyles),
      onDragenter: _cache[0] || (_cache[0] = (...args) => ($options.onDragEnter && $options.onDragEnter(...args))),
      onDrop: _cache[1] || (_cache[1] = (...args) => ($options.onDrop && $options.onDrop(...args))),
      onDragover: _cache[2] || (_cache[2] = (...args) => ($options.onDragOver && $options.onDragOver(...args))),
      onDragleave: _cache[3] || (_cache[3] = (...args) => ($options.onDragLeave && $options.onDragLeave(...args)))
    }, [
      createBaseVNode("div", _hoisted_1$n, [
        _hoisted_2$l,
        createBaseVNode("p", null, toDisplayString(_ctx.translate('COM_MEDIA_DROP_FILE')), 1 /* TEXT */)
      ]),
      ($options.listView === 'table')
        ? (openBlock(), createElementBlock("table", _hoisted_3$d, [
            createBaseVNode("caption", _hoisted_4$8, toDisplayString(_ctx.sprintf('COM_MEDIA_BROWSER_TABLE_CAPTION', $options.currentDirectory)), 1 /* TEXT */),
            createBaseVNode("thead", _hoisted_5$8, [
              createBaseVNode("tr", null, [
                _hoisted_6$6,
                createBaseVNode("th", _hoisted_7$3, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_NAME')), 1 /* TEXT */),
                createBaseVNode("th", _hoisted_8$3, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_SIZE')), 1 /* TEXT */),
                createBaseVNode("th", _hoisted_9$3, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DIMENSION')), 1 /* TEXT */),
                createBaseVNode("th", _hoisted_10$1, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_CREATED')), 1 /* TEXT */),
                createBaseVNode("th", _hoisted_11$1, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_MODIFIED')), 1 /* TEXT */)
              ])
            ]),
            createBaseVNode("tbody", null, [
              (openBlock(true), createElementBlock(Fragment, null, renderList($options.items, (item) => {
                return (openBlock(), createBlock(_component_media_browser_item_row, {
                  key: item.path,
                  item: item
                }, null, 8 /* PROPS */, ["item"]))
              }), 128 /* KEYED_FRAGMENT */))
            ])
          ]))
        : ($options.listView === 'grid')
          ? (openBlock(), createElementBlock("div", _hoisted_12, [
              createBaseVNode("div", {
                class: normalizeClass(["media-browser-items", $options.mediaBrowserGridItemsClass])
              }, [
                (openBlock(true), createElementBlock(Fragment, null, renderList($options.items, (item) => {
                  return (openBlock(), createBlock(_component_media_browser_item, {
                    key: item.path,
                    item: item
                  }, null, 8 /* PROPS */, ["item"]))
                }), 128 /* KEYED_FRAGMENT */))
              ], 2 /* CLASS */)
            ]))
          : createCommentVNode("v-if", true)
    ], 36 /* STYLE, HYDRATE_EVENTS */),
    createVNode(_component_media_infobar, { ref: "infobar" }, null, 512 /* NEED_PATCH */)
  ]))
}

script$n.render = render$n;
script$n.__file = "administrator/components/com_media/resources/scripts/components/browser/browser.vue";

var script$m = {
  name: 'MediaBrowserItemDirectory',
  mixins: [navigable],
  // eslint-disable-next-line vue/require-prop-types
  props: ['item'],
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Handle the on preview double click event */
    onPreviewDblClick() {
      this.navigateTo(this.item.path);
    },
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$m = /*#__PURE__*/createBaseVNode("div", { class: "file-background" }, [
  /*#__PURE__*/createBaseVNode("div", { class: "folder-icon" }, [
    /*#__PURE__*/createBaseVNode("span", { class: "icon-folder" })
  ])
], -1 /* HOISTED */);
const _hoisted_2$k = [
  _hoisted_1$m
];
const _hoisted_3$c = { class: "media-browser-item-info" };

function render$m(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-item-directory",
    onMouseleave: _cache[2] || (_cache[2] = $event => ($options.hideActions()))
  }, [
    createBaseVNode("div", {
      class: "media-browser-item-preview",
      tabindex: "0",
      onDblclick: _cache[0] || (_cache[0] = withModifiers($event => ($options.onPreviewDblClick()), ["stop","prevent"])),
      onKeyup: _cache[1] || (_cache[1] = withKeys($event => ($options.onPreviewDblClick()), ["enter"]))
    }, _hoisted_2$k, 32 /* HYDRATE_EVENTS */),
    createBaseVNode("div", _hoisted_3$c, toDisplayString($props.item.name), 1 /* TEXT */),
    createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* HYDRATE_EVENTS */))
}

script$m.render = render$m;
script$m.__file = "administrator/components/com_media/resources/scripts/components/browser/items/directory.vue";

var script$l = {
  name: 'MediaBrowserItemFile',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$l = /*#__PURE__*/createBaseVNode("div", { class: "media-browser-item-preview" }, [
  /*#__PURE__*/createBaseVNode("div", { class: "file-background" }, [
    /*#__PURE__*/createBaseVNode("div", { class: "file-icon" }, [
      /*#__PURE__*/createBaseVNode("span", { class: "icon-file-alt" })
    ])
  ])
], -1 /* HOISTED */);
const _hoisted_2$j = { class: "media-browser-item-info" };
const _hoisted_3$b = ["aria-label", "title"];

function render$l(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-item-file",
    onMouseleave: _cache[0] || (_cache[0] = $event => ($options.hideActions()))
  }, [
    _hoisted_1$l,
    createBaseVNode("div", _hoisted_2$j, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1 /* TEXT */),
    createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM')
    }, null, 8 /* PROPS */, _hoisted_3$b),
    createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* HYDRATE_EVENTS */))
}

script$l.render = render$l;
script$l.__file = "administrator/components/com_media/resources/scripts/components/browser/items/file.vue";

const dirname = path => {
  if (typeof path !== 'string') {
    throw new TypeError('Path must be a string. Received ' + JSON.stringify(path));
  }

  if (path.length === 0) return '.';
  let code = path.charCodeAt(0);
  const hasRoot = code === 47;
  let end = -1;
  let matchedSlash = true;

  for (let i = path.length - 1; i >= 1; --i) {
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

class Api {
  /**
     * Store constructor
     */
  constructor() {
    const options = Joomla.getOptions('com_media', {});

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


  getContents(dir, full, content) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      // Do a check on full
      if (['0', '1'].indexOf(full) !== -1) {
        throw Error('Invalid parameter: full');
      } // Do a check on download


      if (['0', '1'].indexOf(content) !== -1) {
        throw Error('Invalid parameter: content');
      } // eslint-disable-next-line no-underscore-dangle


      let url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(dir)}`;

      if (full) {
        url += `&url=${full}`;
      }

      if (content) {
        url += `&content=${content}`;
      }

      Joomla.request({
        url,
        method: 'GET',
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: response => {
          // eslint-disable-next-line no-underscore-dangle
          resolve(this._normalizeArray(JSON.parse(response).data));
        },
        onError: xhr => {
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


  createDirectory(name, parent) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      // eslint-disable-next-line no-underscore-dangle
      const url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(parent)}`; // eslint-disable-next-line no-underscore-dangle

      const data = {
        [this._csrfToken]: '1',
        name
      };
      Joomla.request({
        url,
        method: 'POST',
        data: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: response => {
          notifications.success('COM_MEDIA_CREATE_NEW_FOLDER_SUCCESS'); // eslint-disable-next-line no-underscore-dangle

          resolve(this._normalizeItem(JSON.parse(response).data));
        },
        onError: xhr => {
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


  upload(name, parent, content, override) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      // eslint-disable-next-line no-underscore-dangle
      const url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(parent)}`;
      const data = {
        // eslint-disable-next-line no-underscore-dangle
        [this._csrfToken]: '1',
        name,
        content
      }; // Append override

      if (override === true) {
        data.override = true;
      }

      Joomla.request({
        url,
        method: 'POST',
        data: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: response => {
          notifications.success('COM_MEDIA_UPLOAD_SUCCESS'); // eslint-disable-next-line no-underscore-dangle

          resolve(this._normalizeItem(JSON.parse(response).data));
        },
        onError: xhr => {
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


  rename(path, newPath) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      // eslint-disable-next-line no-underscore-dangle
      const url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(path)}`;
      const data = {
        // eslint-disable-next-line no-underscore-dangle
        [this._csrfToken]: '1',
        newPath
      };
      Joomla.request({
        url,
        method: 'PUT',
        data: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: response => {
          notifications.success('COM_MEDIA_RENAME_SUCCESS'); // eslint-disable-next-line no-underscore-dangle

          resolve(this._normalizeItem(JSON.parse(response).data));
        },
        onError: xhr => {
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


  delete(path) {
    // Wrap the ajax call into a real promise
    return new Promise((resolve, reject) => {
      // eslint-disable-next-line no-underscore-dangle
      const url = `${this._baseUrl}&task=api.files&path=${encodeURIComponent(path)}`; // eslint-disable-next-line no-underscore-dangle

      const data = {
        [this._csrfToken]: '1'
      };
      Joomla.request({
        url,
        method: 'DELETE',
        data: JSON.stringify(data),
        headers: {
          'Content-Type': 'application/json'
        },
        onSuccess: () => {
          notifications.success('COM_MEDIA_DELETE_SUCCESS');
          resolve();
        },
        onError: xhr => {
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


  _normalizeItem(item) {
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


  _normalizeArray(data) {
    const directories = data.filter(item => item.type === 'dir') // eslint-disable-next-line no-underscore-dangle
    .map(directory => this._normalizeItem(directory));
    const files = data.filter(item => item.type === 'file') // eslint-disable-next-line no-underscore-dangle
    .map(file => this._normalizeItem(file));
    return {
      directories,
      files
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


  _handleError(error) {
    const response = JSON.parse(error.response);

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
  }

} // eslint-disable-next-line import/prefer-default-export


const api = new Api();

var script$k = {
  name: 'MediaBrowserItemImage',
  props: {
    item: { type: Object, required: true },
    focused: { type: Boolean, required: true, default: false },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: { type: Boolean, default: false },
    };
  },
  computed: {
    getURL() {
      if (!this.item.thumb_path) {
        return '';
      }

      return this.item.thumb_path.split(Joomla.getOptions('system.paths').rootFull).length > 1
        ? `${this.item.thumb_path}?${api.mediaVersion}`
        : `${this.item.thumb_path}`;
    },
    width() {
      return this.item.width > 0 ? this.item.width : null;
    },
    height() {
      return this.item.height > 0 ? this.item.height : null;
    },
    loading() {
      return this.item.width > 0 ? 'lazy' : null;
    },
    altTag() {
      return this.item.name;
    },
  },
  methods: {
    /* Check if the item is an image to edit */
    canEdit() {
      return ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
    },
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    /* Edit an item */
    editItem() {
      // @todo should we use relative urls here?
      const fileBaseUrl = `${Joomla.getOptions('com_media').editViewUrl}&path=`;

      window.location.href = fileBaseUrl + this.item.path;
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$k = ["title"];
const _hoisted_2$i = { class: "image-background" };
const _hoisted_3$a = ["src", "alt", "loading", "width", "height"];
const _hoisted_4$7 = {
  key: 1,
  class: "icon-eye-slash image-placeholder",
  "aria-hidden": "true"
};
const _hoisted_5$7 = ["title"];
const _hoisted_6$5 = ["aria-label", "title"];

function render$k(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-image",
    tabindex: "0",
    onDblclick: _cache[0] || (_cache[0] = $event => ($options.openPreview())),
    onMouseleave: _cache[1] || (_cache[1] = $event => ($options.hideActions())),
    onKeyup: _cache[2] || (_cache[2] = withKeys($event => ($options.openPreview()), ["enter"]))
  }, [
    createBaseVNode("div", {
      class: "media-browser-item-preview",
      title: $props.item.name
    }, [
      createBaseVNode("div", _hoisted_2$i, [
        ($options.getURL)
          ? (openBlock(), createElementBlock("img", {
              key: 0,
              class: "image-cropped",
              src: $options.getURL,
              alt: $options.altTag,
              loading: $options.loading,
              width: $options.width,
              height: $options.height
            }, null, 8 /* PROPS */, _hoisted_3$a))
          : createCommentVNode("v-if", true),
        (!$options.getURL)
          ? (openBlock(), createElementBlock("span", _hoisted_4$7))
          : createCommentVNode("v-if", true)
      ])
    ], 8 /* PROPS */, _hoisted_1$k),
    createBaseVNode("div", {
      class: "media-browser-item-info",
      title: $props.item.name
    }, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 9 /* TEXT, PROPS */, _hoisted_5$7),
    createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM')
    }, null, 8 /* PROPS */, _hoisted_6$5),
    createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      edit: $options.editItem,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "edit", "onToggleSettings"])
  ], 32 /* HYDRATE_EVENTS */))
}

script$k.render = render$k;
script$k.__file = "administrator/components/com_media/resources/scripts/components/browser/items/image.vue";

var script$j = {
  name: 'MediaBrowserItemVideo',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$j = /*#__PURE__*/createBaseVNode("div", { class: "media-browser-item-preview" }, [
  /*#__PURE__*/createBaseVNode("div", { class: "file-background" }, [
    /*#__PURE__*/createBaseVNode("div", { class: "file-icon" }, [
      /*#__PURE__*/createBaseVNode("span", { class: "fas fa-file-video" })
    ])
  ])
], -1 /* HOISTED */);
const _hoisted_2$h = { class: "media-browser-item-info" };

function render$j(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-image",
    onDblclick: _cache[0] || (_cache[0] = $event => ($options.openPreview())),
    onMouseleave: _cache[1] || (_cache[1] = $event => ($options.hideActions()))
  }, [
    _hoisted_1$j,
    createBaseVNode("div", _hoisted_2$h, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1 /* TEXT */),
    createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* HYDRATE_EVENTS */))
}

script$j.render = render$j;
script$j.__file = "administrator/components/com_media/resources/scripts/components/browser/items/video.vue";

var script$i = {
  name: 'MediaBrowserItemAudio',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$i = /*#__PURE__*/createBaseVNode("div", { class: "media-browser-item-preview" }, [
  /*#__PURE__*/createBaseVNode("div", { class: "file-background" }, [
    /*#__PURE__*/createBaseVNode("div", { class: "file-icon" }, [
      /*#__PURE__*/createBaseVNode("span", { class: "fas fa-file-audio" })
    ])
  ])
], -1 /* HOISTED */);
const _hoisted_2$g = { class: "media-browser-item-info" };

function render$i(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-audio",
    tabindex: "0",
    onDblclick: _cache[0] || (_cache[0] = $event => ($options.openPreview())),
    onMouseleave: _cache[1] || (_cache[1] = $event => ($options.hideActions())),
    onKeyup: _cache[2] || (_cache[2] = withKeys($event => ($options.openPreview()), ["enter"]))
  }, [
    _hoisted_1$i,
    createBaseVNode("div", _hoisted_2$g, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1 /* TEXT */),
    createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* HYDRATE_EVENTS */))
}

script$i.render = render$i;
script$i.__file = "administrator/components/com_media/resources/scripts/components/browser/items/audio.vue";

var script$h = {
  name: 'MediaBrowserItemDocument',
  // eslint-disable-next-line vue/require-prop-types
  props: ['item', 'focused'],
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.$refs.container.hideActions();
    },
    /* Preview an item */
    openPreview() {
      this.$refs.container.openPreview();
    },
    toggleSettings(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1$h = /*#__PURE__*/createBaseVNode("div", { class: "media-browser-item-preview" }, [
  /*#__PURE__*/createBaseVNode("div", { class: "file-background" }, [
    /*#__PURE__*/createBaseVNode("div", { class: "file-icon" }, [
      /*#__PURE__*/createBaseVNode("span", { class: "fas fa-file-pdf" })
    ])
  ])
], -1 /* HOISTED */);
const _hoisted_2$f = { class: "media-browser-item-info" };
const _hoisted_3$9 = ["aria-label", "title"];

function render$h(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_browser_action_items_container = resolveComponent("media-browser-action-items-container");

  return (openBlock(), createElementBlock("div", {
    class: "media-browser-doc",
    onDblclick: _cache[0] || (_cache[0] = $event => ($options.openPreview())),
    onMouseleave: _cache[1] || (_cache[1] = $event => ($options.hideActions()))
  }, [
    _hoisted_1$h,
    createBaseVNode("div", _hoisted_2$f, toDisplayString($props.item.name) + " " + toDisplayString($props.item.filetype), 1 /* TEXT */),
    createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM')
    }, null, 8 /* PROPS */, _hoisted_3$9),
    createVNode(_component_media_browser_action_items_container, {
      ref: "container",
      item: $props.item,
      previewable: true,
      downloadable: true,
      shareable: true,
      onToggleSettings: $options.toggleSettings
    }, null, 8 /* PROPS */, ["item", "onToggleSettings"])
  ], 32 /* HYDRATE_EVENTS */))
}

script$h.render = render$h;
script$h.__file = "administrator/components/com_media/resources/scripts/components/browser/items/document.vue";

var BrowserItem = {
  props: ['item'],

  data() {
    return {
      hoverActive: false,
      actionsActive: false
    };
  },

  methods: {
    /**
     * Return the correct item type component
     */
    itemType() {
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
    styles() {
      return {
        width: `calc(${this.$store.state.gridSize}% - 20px)`
      };
    },

    /**
     * Whether or not the item is currently selected
     * @returns {boolean}
     */
    isSelected() {
      return this.$store.state.selectedItems.some(selected => selected.path === this.item.path);
    },

    /**
     * Whether or not the item is currently active (on hover or via tab)
     * @returns {boolean}
     */
    isHoverActive() {
      return this.hoverActive;
    },

    /**
     * Whether or not the item is currently active (on hover or via tab)
     * @returns {boolean}
     */
    hasActions() {
      return this.actionsActive;
    },

    /**
     * Turns on the hover class
     */
    mouseover() {
      this.hoverActive = true;
    },

    /**
     * Turns off the hover class
     */
    mouseleave() {
      this.hoverActive = false;
    },

    /**
     * Handle the click event
     * @param event
     */
    handleClick(event) {
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
    toggleSettings(active) {
      // eslint-disable-next-line no-unused-expressions
      active ? this.mouseover() : this.mouseleave();
    }

  },

  render() {
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
    dimension() {
      if (!this.item.width) {
        return '';
      }
      return `${this.item.width}px * ${this.item.height}px`;
    },
    isDir() {
      return (this.item.type === 'dir');
    },
    /* The size of a file in KB */
    size() {
      if (!this.item.size) {
        return '';
      }
      return `${(this.item.size / 1024).toFixed(2)} KB`;
    },
    selected() {
      return !!this.isSelected();
    },
  },

  methods: {
    /* Handle the on row double click event */
    onDblClick() {
      if (this.isDir) {
        this.navigateTo(this.item.path);
        return;
      }

      // @todo remove the hardcoded extensions here
      const extensionWithPreview = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mp3', 'pdf'];

      // Show preview
      if (this.item.extension
        && extensionWithPreview.includes(this.item.extension.toLowerCase())) {
        this.$store.commit(SHOW_PREVIEW_MODAL);
        this.$store.dispatch('getFullContents', this.item);
      }
    },

    /**
     * Whether or not the item is currently selected
     * @returns {boolean}
     */
    isSelected() {
      return this.$store.state.selectedItems.some((selected) => selected.path === this.item.path);
    },

    /**
     * Handle the click event
     * @param event
     */
    onClick(event) {
      const path = false;
      const data = {
        path,
        thumb: false,
        fileType: this.item.mime_type ? this.item.mime_type : false,
        extension: this.item.extension ? this.item.extension : false,
      };

      if (this.item.type === 'file') {
        data.path = this.item.path;
        data.thumb = this.item.thumb ? this.item.thumb : false;
        data.width = this.item.width ? this.item.width : 0;
        data.height = this.item.height ? this.item.height : 0;

        window.parent.document.dispatchEvent(
          new CustomEvent(
            'onMediaFileSelected',
            {
              bubbles: true,
              cancelable: false,
              detail: data,
            },
          ),
        );
      }

      // Handle clicks when the item was not selected
      if (!this.isSelected()) {
        // Unselect all other selected items,
        // if the shift key was not pressed during the click event
        if (!(event.shiftKey || event.keyCode === 13)) {
          this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
        }
        this.$store.commit(SELECT_BROWSER_ITEM, this.item);
        return;
      }

      // If more than one item was selected and the user clicks again on the selected item,
      // he most probably wants to unselect all other items.
      if (this.$store.state.selectedItems.length > 1) {
        this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
        this.$store.commit(SELECT_BROWSER_ITEM, this.item);
      }
    },

  },
};

const _hoisted_1$g = ["data-type"];
const _hoisted_2$e = {
  scope: "row",
  class: "name"
};
const _hoisted_3$8 = { class: "size" };
const _hoisted_4$6 = { class: "dimension" };
const _hoisted_5$6 = { class: "created" };
const _hoisted_6$4 = { class: "modified" };

function render$g(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("tr", {
    class: normalizeClass(["media-browser-item", {selected: $options.selected}]),
    onDblclick: _cache[0] || (_cache[0] = withModifiers($event => ($options.onDblClick()), ["stop","prevent"])),
    onClick: _cache[1] || (_cache[1] = (...args) => ($options.onClick && $options.onClick(...args)))
  }, [
    createBaseVNode("td", {
      class: "type",
      "data-type": $props.item.extension
    }, null, 8 /* PROPS */, _hoisted_1$g),
    createBaseVNode("th", _hoisted_2$e, toDisplayString($props.item.name), 1 /* TEXT */),
    createBaseVNode("td", _hoisted_3$8, toDisplayString($options.size), 1 /* TEXT */),
    createBaseVNode("td", _hoisted_4$6, toDisplayString($options.dimension), 1 /* TEXT */),
    createBaseVNode("td", _hoisted_5$6, toDisplayString($props.item.create_date_formatted), 1 /* TEXT */),
    createBaseVNode("td", _hoisted_6$4, toDisplayString($props.item.modified_date_formatted), 1 /* TEXT */)
  ], 34 /* CLASS, HYDRATE_EVENTS */))
}

script$g.render = render$g;
script$g.__file = "administrator/components/com_media/resources/scripts/components/browser/items/row.vue";

var script$f = {
  name: 'MediaModal',
  props: {
    /* Whether or not the close button in the header should be shown */
    showClose: {
      type: Boolean,
      default: true,
    },
    /* The size of the modal */
    // eslint-disable-next-line vue/require-default-prop
    size: {
      type: String,
    },
    labelElement: {
      type: String,
      required: true,
    },
  },
  emits: ['close'],
  computed: {
    /* Get the modal css class */
    modalClass() {
      return {
        'modal-sm': this.size === 'sm',
      };
    },
  },
  mounted() {
    // Listen to keydown events on the document
    document.addEventListener('keydown', this.onKeyDown);
  },
  beforeUnmount() {
    // Remove the keydown event listener
    document.removeEventListener('keydown', this.onKeyDown);
  },
  methods: {
    /* Close the modal instance */
    close() {
      this.$emit('close');
    },
    /* Handle keydown events */
    onKeyDown(event) {
      if (event.keyCode === 27) {
        this.close();
      }
    },
  },
};

const _hoisted_1$f = ["aria-labelledby"];
const _hoisted_2$d = { class: "modal-content" };
const _hoisted_3$7 = { class: "modal-header" };
const _hoisted_4$5 = { class: "modal-body" };
const _hoisted_5$5 = { class: "modal-footer" };

function render$f(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_tab_lock = resolveComponent("tab-lock");

  return (openBlock(), createElementBlock("div", {
    class: "media-modal-backdrop",
    onClick: _cache[2] || (_cache[2] = $event => ($options.close()))
  }, [
    createBaseVNode("div", {
      class: "modal",
      style: {"display":"flex"},
      onClick: _cache[1] || (_cache[1] = withModifiers(() => {}, ["stop"]))
    }, [
      createVNode(_component_tab_lock, null, {
        default: withCtx(() => [
          createBaseVNode("div", {
            class: normalizeClass(["modal-dialog", $options.modalClass]),
            role: "dialog",
            "aria-labelledby": $props.labelElement
          }, [
            createBaseVNode("div", _hoisted_2$d, [
              createBaseVNode("div", _hoisted_3$7, [
                renderSlot(_ctx.$slots, "header"),
                renderSlot(_ctx.$slots, "backdrop-close"),
                ($props.showClose)
                  ? (openBlock(), createElementBlock("button", {
                      key: 0,
                      type: "button",
                      class: "btn-close",
                      "aria-label": "Close",
                      onClick: _cache[0] || (_cache[0] = $event => ($options.close()))
                    }))
                  : createCommentVNode("v-if", true)
              ]),
              createBaseVNode("div", _hoisted_4$5, [
                renderSlot(_ctx.$slots, "body")
              ]),
              createBaseVNode("div", _hoisted_5$5, [
                renderSlot(_ctx.$slots, "footer")
              ])
            ])
          ], 10 /* CLASS, PROPS */, _hoisted_1$f)
        ]),
        _: 3 /* FORWARDED */
      })
    ])
  ]))
}

script$f.render = render$f;
script$f.__file = "administrator/components/com_media/resources/scripts/components/modals/modal.vue";

var script$e = {
  name: 'MediaCreateFolderModal',
  data() {
    return {
      folder: '',
    };
  },
  methods: {
    /* Check if the the form is valid */
    isValid() {
      return (this.folder);
    },
    /* Close the modal instance */
    close() {
      this.reset();
      this.$store.commit(HIDE_CREATE_FOLDER_MODAL);
    },
    /* Save the form and create the folder */
    save() {
      // Check if the form is valid
      if (!this.isValid()) {
        // @todo show an error message to user for insert a folder name
        // @todo mark the field as invalid
        return;
      }

      // Create the directory
      this.$store.dispatch('createDirectory', {
        name: this.folder,
        parent: this.$store.state.selectedDirectory,
      });
      this.reset();
    },
    /* Reset the form */
    reset() {
      this.folder = '';
    },
  },
};

const _hoisted_1$e = {
  id: "createFolderTitle",
  class: "modal-title"
};
const _hoisted_2$c = { class: "p-3" };
const _hoisted_3$6 = { class: "form-group" };
const _hoisted_4$4 = { for: "folder" };
const _hoisted_5$4 = ["disabled"];

function render$e(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_modal = resolveComponent("media-modal");

  return (_ctx.$store.state.showCreateFolderModal)
    ? (openBlock(), createBlock(_component_media_modal, {
        key: 0,
        size: 'md',
        "label-element": "createFolderTitle",
        onClose: _cache[5] || (_cache[5] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$e, toDisplayString(_ctx.translate('COM_MEDIA_CREATE_NEW_FOLDER')), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", _hoisted_2$c, [
            createBaseVNode("form", {
              class: "form",
              novalidate: "",
              onSubmit: _cache[2] || (_cache[2] = withModifiers((...args) => ($options.save && $options.save(...args)), ["prevent"]))
            }, [
              createBaseVNode("div", _hoisted_3$6, [
                createBaseVNode("label", _hoisted_4$4, toDisplayString(_ctx.translate('COM_MEDIA_FOLDER_NAME')), 1 /* TEXT */),
                withDirectives(createBaseVNode("input", {
                  id: "folder",
                  "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => (($data.folder) = $event)),
                  class: "form-control",
                  type: "text",
                  required: "",
                  autocomplete: "off",
                  onInput: _cache[1] || (_cache[1] = $event => ($data.folder = $event.target.value))
                }, null, 544 /* HYDRATE_EVENTS, NEED_PATCH */), [
                  [
                    vModelText,
                    $data.folder,
                    void 0,
                    { trim: true }
                  ]
                ])
              ])
            ], 32 /* HYDRATE_EVENTS */)
          ])
        ]),
        footer: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("button", {
              class: "btn btn-secondary",
              onClick: _cache[3] || (_cache[3] = $event => ($options.close()))
            }, toDisplayString(_ctx.translate('JCANCEL')), 1 /* TEXT */),
            createBaseVNode("button", {
              class: "btn btn-success",
              disabled: !$options.isValid(),
              onClick: _cache[4] || (_cache[4] = $event => ($options.save()))
            }, toDisplayString(_ctx.translate('JACTION_CREATE')), 9 /* TEXT, PROPS */, _hoisted_5$4)
          ])
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$e.render = render$e;
script$e.__file = "administrator/components/com_media/resources/scripts/components/modals/create-folder-modal.vue";

var script$d = {
  name: 'MediaPreviewModal',
  computed: {
    /* Get the item to show in the modal */
    item() {
      // Use the currently selected directory as a fallback
      return this.$store.state.previewItem;
    },
    /* Get the hashed URL */
    getHashedURL() {
      if (this.item.adapter.startsWith('local-')) {
        return `${this.item.url}?${api.mediaVersion}`;
      }
      return this.item.url;
    },
  },
  methods: {
    /* Close the modal */
    close() {
      this.$store.commit(HIDE_PREVIEW_MODAL);
    },
    isImage() {
      return this.item.mime_type.indexOf('image/') === 0;
    },
    isVideo() {
      return this.item.mime_type.indexOf('video/') === 0;
    },
    isAudio() {
      return this.item.mime_type.indexOf('audio/') === 0;
    },
    isDoc() {
      return this.item.mime_type.indexOf('application/') === 0;
    },
  },
};

const _hoisted_1$d = {
  id: "previewTitle",
  class: "modal-title text-light"
};
const _hoisted_2$b = { class: "image-background" };
const _hoisted_3$5 = ["src"];
const _hoisted_4$3 = {
  key: 1,
  controls: ""
};
const _hoisted_5$3 = ["src", "type"];
const _hoisted_6$3 = ["type", "data"];
const _hoisted_7$2 = ["src", "type"];
const _hoisted_8$2 = /*#__PURE__*/createBaseVNode("span", { class: "icon-times" }, null, -1 /* HOISTED */);
const _hoisted_9$2 = [
  _hoisted_8$2
];

function render$d(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_modal = resolveComponent("media-modal");

  return (_ctx.$store.state.showPreviewModal && $options.item)
    ? (openBlock(), createBlock(_component_media_modal, {
        key: 0,
        size: 'md',
        class: "media-preview-modal",
        "label-element": "previewTitle",
        "show-close": false,
        onClose: _cache[1] || (_cache[1] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$d, toDisplayString($options.item.name), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", _hoisted_2$b, [
            ($options.isAudio())
              ? (openBlock(), createElementBlock("audio", {
                  key: 0,
                  controls: "",
                  src: $options.item.url
                }, null, 8 /* PROPS */, _hoisted_3$5))
              : createCommentVNode("v-if", true),
            ($options.isVideo())
              ? (openBlock(), createElementBlock("video", _hoisted_4$3, [
                  createBaseVNode("source", {
                    src: $options.item.url,
                    type: $options.item.mime_type
                  }, null, 8 /* PROPS */, _hoisted_5$3)
                ]))
              : createCommentVNode("v-if", true),
            ($options.isDoc())
              ? (openBlock(), createElementBlock("object", {
                  key: 2,
                  type: $options.item.mime_type,
                  data: $options.item.url,
                  width: "800",
                  height: "600"
                }, null, 8 /* PROPS */, _hoisted_6$3))
              : createCommentVNode("v-if", true),
            ($options.isImage())
              ? (openBlock(), createElementBlock("img", {
                  key: 3,
                  src: $options.getHashedURL,
                  type: $options.item.mime_type
                }, null, 8 /* PROPS */, _hoisted_7$2))
              : createCommentVNode("v-if", true)
          ])
        ]),
        "backdrop-close": withCtx(() => [
          createBaseVNode("button", {
            type: "button",
            class: "media-preview-close",
            onClick: _cache[0] || (_cache[0] = $event => ($options.close()))
          }, _hoisted_9$2)
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$d.render = render$d;
script$d.__file = "administrator/components/com_media/resources/scripts/components/modals/preview-modal.vue";

var script$c = {
  name: 'MediaRenameModal',
  computed: {
    item() {
      return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
    },
    name() {
      return this.item.name.replace(`.${this.item.extension}`, '');
    },
    extension() {
      return this.item.extension;
    },
  },
  updated() {
    this.$nextTick(() => (this.$refs.nameField ? this.$refs.nameField.focus() : null));
  },
  methods: {
    /* Check if the form is valid */
    isValid() {
      return this.item.name.length > 0;
    },
    /* Close the modal instance */
    close() {
      this.$store.commit(HIDE_RENAME_MODAL);
    },
    /* Save the form and create the folder */
    save() {
      // Check if the form is valid
      if (!this.isValid()) {
        // @todo mark the field as invalid
        return;
      }
      let newName = this.$refs.nameField.value;
      if (this.extension.length) {
        newName += `.${this.item.extension}`;
      }

      let newPath = this.item.directory;
      if (newPath.substr(-1) !== '/') {
        newPath += '/';
      }

      // Rename the item
      this.$store.dispatch('renameItem', {
        item: this.item,
        newPath: newPath + newName,
        newName,
      });
    },
  },
};

const _hoisted_1$c = {
  id: "renameTitle",
  class: "modal-title"
};
const _hoisted_2$a = { class: "form-group p-3" };
const _hoisted_3$4 = { for: "name" };
const _hoisted_4$2 = ["placeholder", "value"];
const _hoisted_5$2 = {
  key: 0,
  class: "input-group-text"
};
const _hoisted_6$2 = ["disabled"];

function render$c(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_modal = resolveComponent("media-modal");

  return (_ctx.$store.state.showRenameModal)
    ? (openBlock(), createBlock(_component_media_modal, {
        key: 0,
        size: 'sm',
        "show-close": false,
        "label-element": "renameTitle",
        onClose: _cache[5] || (_cache[5] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$c, toDisplayString(_ctx.translate('COM_MEDIA_RENAME')), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("form", {
              class: "form",
              novalidate: "",
              onSubmit: _cache[0] || (_cache[0] = withModifiers((...args) => ($options.save && $options.save(...args)), ["prevent"]))
            }, [
              createBaseVNode("div", _hoisted_2$a, [
                createBaseVNode("label", _hoisted_3$4, toDisplayString(_ctx.translate('COM_MEDIA_NAME')), 1 /* TEXT */),
                createBaseVNode("div", {
                  class: normalizeClass({'input-group': $options.extension.length})
                }, [
                  createBaseVNode("input", {
                    id: "name",
                    ref: "nameField",
                    class: "form-control",
                    type: "text",
                    placeholder: _ctx.translate('COM_MEDIA_NAME'),
                    value: $options.name,
                    required: "",
                    autocomplete: "off"
                  }, null, 8 /* PROPS */, _hoisted_4$2),
                  ($options.extension.length)
                    ? (openBlock(), createElementBlock("span", _hoisted_5$2, toDisplayString($options.extension), 1 /* TEXT */))
                    : createCommentVNode("v-if", true)
                ], 2 /* CLASS */)
              ])
            ], 32 /* HYDRATE_EVENTS */)
          ])
        ]),
        footer: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("button", {
              type: "button",
              class: "btn btn-secondary",
              onClick: _cache[1] || (_cache[1] = $event => ($options.close())),
              onKeyup: _cache[2] || (_cache[2] = withKeys($event => ($options.close()), ["enter"]))
            }, toDisplayString(_ctx.translate('JCANCEL')), 33 /* TEXT, HYDRATE_EVENTS */),
            createBaseVNode("button", {
              type: "button",
              class: "btn btn-success",
              disabled: !$options.isValid(),
              onClick: _cache[3] || (_cache[3] = $event => ($options.save())),
              onKeyup: _cache[4] || (_cache[4] = withKeys($event => ($options.save()), ["enter"]))
            }, toDisplayString(_ctx.translate('JAPPLY')), 41 /* TEXT, PROPS, HYDRATE_EVENTS */, _hoisted_6$2)
          ])
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$c.render = render$c;
script$c.__file = "administrator/components/com_media/resources/scripts/components/modals/rename-modal.vue";

var script$b = {
  name: 'MediaShareModal',
  computed: {
    item() {
      return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
    },

    url() {
      return (this.$store.state.previewItem && Object.prototype.hasOwnProperty.call(this.$store.state.previewItem, 'url') ? this.$store.state.previewItem.url : null);
    },
  },
  methods: {
    /* Close the modal instance and reset the form */
    close() {
      this.$store.commit(HIDE_SHARE_MODAL);
      this.$store.commit(LOAD_FULL_CONTENTS_SUCCESS, null);
    },

    // Generate the url from backend
    generateUrl() {
      this.$store.dispatch('getFullContents', this.item);
    },

    // Copy to clipboard
    copyToClipboard() {
      this.$refs.urlText.focus();
      this.$refs.urlText.select();

      try {
        document.execCommand('copy');
      } catch (err) {
        // @todo Error handling in joomla way
        // eslint-disable-next-line no-undef
        alert(translate('COM_MEDIA_SHARE_COPY_FAILED_ERROR'));
      }
    },
  },
};

const _hoisted_1$b = {
  id: "shareTitle",
  class: "modal-title"
};
const _hoisted_2$9 = { class: "p-3" };
const _hoisted_3$3 = { class: "desc" };
const _hoisted_4$1 = {
  key: 0,
  class: "control"
};
const _hoisted_5$1 = {
  key: 1,
  class: "control"
};
const _hoisted_6$1 = { class: "input-group" };
const _hoisted_7$1 = ["title"];
const _hoisted_8$1 = /*#__PURE__*/createBaseVNode("span", {
  class: "icon-clipboard",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_9$1 = [
  _hoisted_8$1
];

function render$b(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_modal = resolveComponent("media-modal");

  return (_ctx.$store.state.showShareModal)
    ? (openBlock(), createBlock(_component_media_modal, {
        key: 0,
        size: 'md',
        "show-close": false,
        "label-element": "shareTitle",
        onClose: _cache[4] || (_cache[4] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$b, toDisplayString(_ctx.translate('COM_MEDIA_SHARE')), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", _hoisted_2$9, [
            createBaseVNode("div", _hoisted_3$3, [
              createTextVNode(toDisplayString(_ctx.translate('COM_MEDIA_SHARE_DESC')) + " ", 1 /* TEXT */),
              (!$options.url)
                ? (openBlock(), createElementBlock("div", _hoisted_4$1, [
                    createBaseVNode("button", {
                      class: "btn btn-success w-100",
                      type: "button",
                      onClick: _cache[0] || (_cache[0] = (...args) => ($options.generateUrl && $options.generateUrl(...args)))
                    }, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_SHARE')), 1 /* TEXT */)
                  ]))
                : (openBlock(), createElementBlock("div", _hoisted_5$1, [
                    createBaseVNode("span", _hoisted_6$1, [
                      withDirectives(createBaseVNode("input", {
                        id: "url",
                        ref: "urlText",
                        "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => (($options.url) = $event)),
                        readonly: "",
                        type: "url",
                        class: "form-control input-xxlarge",
                        placeholder: "URL",
                        autocomplete: "off"
                      }, null, 512 /* NEED_PATCH */), [
                        [vModelText, $options.url]
                      ]),
                      createBaseVNode("button", {
                        class: "btn btn-secondary",
                        type: "button",
                        title: _ctx.translate('COM_MEDIA_SHARE_COPY'),
                        onClick: _cache[2] || (_cache[2] = (...args) => ($options.copyToClipboard && $options.copyToClipboard(...args)))
                      }, _hoisted_9$1, 8 /* PROPS */, _hoisted_7$1)
                    ])
                  ]))
            ])
          ])
        ]),
        footer: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("button", {
              class: "btn btn-secondary",
              onClick: _cache[3] || (_cache[3] = $event => ($options.close()))
            }, toDisplayString(_ctx.translate('JCANCEL')), 1 /* TEXT */)
          ])
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$b.render = render$b;
script$b.__file = "administrator/components/com_media/resources/scripts/components/modals/share-modal.vue";

var script$a = {
  name: 'MediaShareModal',
  computed: {
    item() {
      return this.$store.state.selectedItems[this.$store.state.selectedItems.length - 1];
    },
  },
  methods: {
    /* Delete Item */
    deleteItem() {
      this.$store.dispatch('deleteSelectedItems');
      this.$store.commit(HIDE_CONFIRM_DELETE_MODAL);
    },
    /* Close the modal instance */
    close() {
      this.$store.commit(HIDE_CONFIRM_DELETE_MODAL);
    },
  },
};

const _hoisted_1$a = {
  id: "confirmDeleteTitle",
  class: "modal-title"
};
const _hoisted_2$8 = { class: "p-3" };
const _hoisted_3$2 = { class: "desc" };

function render$a(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_modal = resolveComponent("media-modal");

  return (_ctx.$store.state.showConfirmDeleteModal)
    ? (openBlock(), createBlock(_component_media_modal, {
        key: 0,
        size: 'md',
        "show-close": false,
        "label-element": "confirmDeleteTitle",
        onClose: _cache[2] || (_cache[2] = $event => ($options.close()))
      }, {
        header: withCtx(() => [
          createBaseVNode("h3", _hoisted_1$a, toDisplayString(_ctx.translate('COM_MEDIA_CONFIRM_DELETE_MODAL_HEADING')), 1 /* TEXT */)
        ]),
        body: withCtx(() => [
          createBaseVNode("div", _hoisted_2$8, [
            createBaseVNode("div", _hoisted_3$2, toDisplayString(_ctx.translate('JGLOBAL_CONFIRM_DELETE')), 1 /* TEXT */)
          ])
        ]),
        footer: withCtx(() => [
          createBaseVNode("div", null, [
            createBaseVNode("button", {
              class: "btn btn-success",
              onClick: _cache[0] || (_cache[0] = $event => ($options.close()))
            }, toDisplayString(_ctx.translate('JCANCEL')), 1 /* TEXT */),
            createBaseVNode("button", {
              id: "media-delete-item",
              class: "btn btn-danger",
              onClick: _cache[1] || (_cache[1] = $event => ($options.deleteItem()))
            }, toDisplayString(_ctx.translate('COM_MEDIA_CONFIRM_DELETE_MODAL')), 1 /* TEXT */)
          ])
        ]),
        _: 1 /* STABLE */
      }))
    : createCommentVNode("v-if", true)
}

script$a.render = render$a;
script$a.__file = "administrator/components/com_media/resources/scripts/components/modals/confirm-delete-modal.vue";

var script$9 = {
  name: 'MediaInfobar',
  computed: {
    /* Get the item to show in the infobar */
    item() {
      // Check if there are selected items
      const { selectedItems } = this.$store.state;

      // If there is only one selected item, show that one.
      if (selectedItems.length === 1) {
        return selectedItems[0];
      }

      // If there are more selected items, use the last one
      if (selectedItems.length > 1) {
        return selectedItems.slice(-1)[0];
      }

      // Use the currently selected directory as a fallback
      return this.$store.getters.getSelectedDirectory;
    },
    /* Show/Hide the InfoBar */
    showInfoBar() {
      return this.$store.state.showInfoBar;
    },
  },
  methods: {
    hideInfoBar() {
      this.$store.commit(HIDE_INFOBAR);
    },
  },
};

const _hoisted_1$9 = {
  key: 0,
  class: "media-infobar"
};
const _hoisted_2$7 = {
  key: 0,
  class: "text-center"
};
const _hoisted_3$1 = /*#__PURE__*/createBaseVNode("span", { class: "icon-file placeholder-icon" }, null, -1 /* HOISTED */);
const _hoisted_4 = { key: 1 };
const _hoisted_5 = { key: 0 };
const _hoisted_6 = { key: 1 };
const _hoisted_7 = { key: 2 };
const _hoisted_8 = { key: 3 };
const _hoisted_9 = { key: 4 };
const _hoisted_10 = { key: 5 };
const _hoisted_11 = { key: 6 };

function render$9(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createBlock(Transition, { name: "infobar" }, {
    default: withCtx(() => [
      ($options.showInfoBar && $options.item)
        ? (openBlock(), createElementBlock("div", _hoisted_1$9, [
            createBaseVNode("span", {
              class: "infobar-close",
              onClick: _cache[0] || (_cache[0] = $event => ($options.hideInfoBar()))
            }, "×"),
            createBaseVNode("h2", null, toDisplayString($options.item.name), 1 /* TEXT */),
            ($options.item.path === '/')
              ? (openBlock(), createElementBlock("div", _hoisted_2$7, [
                  _hoisted_3$1,
                  createTextVNode(" Select file or folder to view its details. ")
                ]))
              : (openBlock(), createElementBlock("dl", _hoisted_4, [
                  createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_FOLDER')), 1 /* TEXT */),
                  createBaseVNode("dd", null, toDisplayString($options.item.directory), 1 /* TEXT */),
                  createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_TYPE')), 1 /* TEXT */),
                  ($options.item.type === 'file')
                    ? (openBlock(), createElementBlock("dd", _hoisted_5, toDisplayString(_ctx.translate('COM_MEDIA_FILE')), 1 /* TEXT */))
                    : ($options.item.type === 'dir')
                      ? (openBlock(), createElementBlock("dd", _hoisted_6, toDisplayString(_ctx.translate('COM_MEDIA_FOLDER')), 1 /* TEXT */))
                      : (openBlock(), createElementBlock("dd", _hoisted_7, " - ")),
                  createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_CREATED')), 1 /* TEXT */),
                  createBaseVNode("dd", null, toDisplayString($options.item.create_date_formatted), 1 /* TEXT */),
                  createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DATE_MODIFIED')), 1 /* TEXT */),
                  createBaseVNode("dd", null, toDisplayString($options.item.modified_date_formatted), 1 /* TEXT */),
                  createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_DIMENSION')), 1 /* TEXT */),
                  ($options.item.width || $options.item.height)
                    ? (openBlock(), createElementBlock("dd", _hoisted_8, toDisplayString($options.item.width) + "px * " + toDisplayString($options.item.height) + "px ", 1 /* TEXT */))
                    : (openBlock(), createElementBlock("dd", _hoisted_9, " - ")),
                  createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_SIZE')), 1 /* TEXT */),
                  ($options.item.size)
                    ? (openBlock(), createElementBlock("dd", _hoisted_10, toDisplayString(($options.item.size / 1024).toFixed(2)) + " KB ", 1 /* TEXT */))
                    : (openBlock(), createElementBlock("dd", _hoisted_11, " - ")),
                  createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_MIME_TYPE')), 1 /* TEXT */),
                  createBaseVNode("dd", null, toDisplayString($options.item.mime_type), 1 /* TEXT */),
                  createBaseVNode("dt", null, toDisplayString(_ctx.translate('COM_MEDIA_MEDIA_EXTENSION')), 1 /* TEXT */),
                  createBaseVNode("dd", null, toDisplayString($options.item.extension || '-'), 1 /* TEXT */)
                ]))
          ]))
        : createCommentVNode("v-if", true)
    ]),
    _: 1 /* STABLE */
  }))
}

script$9.render = render$9;
script$9.__file = "administrator/components/com_media/resources/scripts/components/infobar/infobar.vue";

var script$8 = {
  name: 'MediaUpload',
  props: {
    // eslint-disable-next-line vue/require-default-prop
    accept: {
      type: String,
    },
    // eslint-disable-next-line vue/require-prop-types
    extensions: {
      default: () => [],
    },
    name: {
      type: String,
      default: 'file',
    },
    multiple: {
      type: Boolean,
      default: true,
    },
  },
  created() {
    // Listen to the toolbar upload click event
    MediaManager.Event.listen('onClickUpload', () => this.chooseFiles());
  },
  methods: {
    /* Open the choose-file dialog */
    chooseFiles() {
      this.$refs.fileInput.click();
    },
    /* Upload files */
    upload(e) {
      e.preventDefault();
      const { files } = e.target;

      // Loop through array of files and upload each file
      Array.from(files).forEach((file) => {
        // Create a new file reader instance
        const reader = new FileReader();

        // Add the on load callback
        reader.onload = (progressEvent) => {
          const { result } = progressEvent.target;
          const splitIndex = result.indexOf('base64') + 7;
          const content = result.slice(splitIndex, result.length);

          // Upload the file
          this.$store.dispatch('uploadFile', {
            name: file.name,
            parent: this.$store.state.selectedDirectory,
            content,
          });
        };

        reader.readAsDataURL(file);
      });
    },
  },
};

const _hoisted_1$8 = ["name", "multiple", "accept"];

function render$8(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("input", {
    ref: "fileInput",
    type: "file",
    class: "hidden",
    name: $props.name,
    multiple: $props.multiple,
    accept: $props.accept,
    onChange: _cache[0] || (_cache[0] = (...args) => ($options.upload && $options.upload(...args)))
  }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_1$8))
}

script$8.render = render$8;
script$8.__file = "administrator/components/com_media/resources/scripts/components/upload/upload.vue";

/**
 * Translate plugin
 */
const Translate = {
  // Translate from Joomla text
  translate: key => Joomla.Text._(key, key),
  sprintf: function (string) {
    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }

    // eslint-disable-next-line no-param-reassign
    string = Translate.translate(string);
    let i = 0;
    return string.replace(/%((%)|s|d)/g, m => {
      let val = args[i];

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
  install: Vue => Vue.mixin({
    methods: {
      translate(key) {
        return Translate.translate(key);
      },

      sprintf(key) {
        for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
          args[_key2 - 1] = arguments[_key2];
        }

        return Translate.sprintf(key, args);
      }

    }
  })
};

function getDevtoolsGlobalHook() {
  return getTarget().__VUE_DEVTOOLS_GLOBAL_HOOK__;
}
function getTarget() {
  // @ts-ignore
  return typeof navigator !== 'undefined' ? window : typeof global !== 'undefined' ? global : {};
}

const HOOK_SETUP = 'devtools-plugin:setup';

function setupDevtoolsPlugin(pluginDescriptor, setupFn) {
  const hook = getDevtoolsGlobalHook();

  if (hook) {
    hook.emit(HOOK_SETUP, pluginDescriptor, setupFn);
  } else {
    const target = getTarget();
    const list = target.__VUE_DEVTOOLS_PLUGINS__ = target.__VUE_DEVTOOLS_PLUGINS__ || [];
    list.push({
      pluginDescriptor,
      setupFn
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
      get: function () {
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
      get: function () {
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
        get: function () {
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
  }, function () {
  }, {
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
      before: function (action, state) {
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
      after: function (action, state) {
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

prototypeAccessors.state.set = function (v) {
};

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
  } catch (e) {
  }

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
      } catch (e) {
      }

      resolve(res);
    }, function (error) {
      try {
        this$1$1._actionSubscribers.filter(function (sub) {
          return sub.error;
        }).forEach(function (sub) {
          return sub.error(action, this$1$1.state, error);
        });
      } catch (e) {
      }

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

let MockStorage; // @ts-ignore

{
  MockStorage = class {
    get length() {
      return Object.keys(this).length;
    }

    key(index) {
      return Object.keys(this)[index];
    }

    setItem(key, data) {
      this[key] = data.toString();
    }

    getItem(key) {
      return this[key];
    }

    removeItem(key) {
      delete this[key];
    }

    clear() {
      for (let key of Object.keys(this)) {
        delete this[key];
      }
    }

  };
} // tslint:disable: variable-name

class SimplePromiseQueue {
  constructor() {
    this._queue = [];
    this._flushing = false;
  }

  enqueue(promise) {
    this._queue.push(promise);

    if (!this._flushing) {
      return this.flushQueue();
    }

    return Promise.resolve();
  }

  flushQueue() {
    this._flushing = true;

    const chain = () => {
      const nextTask = this._queue.shift();

      if (nextTask) {
        return nextTask.then(chain);
      } else {
        this._flushing = false;
      }
    };

    return Promise.resolve(chain());
  }

}

const options$1 = {
  replaceArrays: {
    arrayMerge: (destinationArray, sourceArray, options) => sourceArray
  },
  concatArrays: {
    arrayMerge: (target, source, options) => target.concat(...source)
  }
};

function merge(into, from, mergeOption) {
  return cjs(into, from, options$1[mergeOption]);
}

let FlattedJSON = JSON;
/**
 * A class that implements the vuex persistence.
 * @type S type of the 'state' inside the store (default: any)
 */

class VuexPersistence {
  /**
   * Create a {@link VuexPersistence} object.
   * Use the <code>plugin</code> function of this class as a
   * Vuex plugin.
   * @param {PersistOptions} options
   */
  constructor(options) {
    // tslint:disable-next-line:variable-name
    this._mutex = new SimplePromiseQueue();
    /**
     * Creates a subscriber on the store. automatically is used
     * when this is used a vuex plugin. Not for manual usage.
     * @param store
     */

    this.subscriber = store => handler => store.subscribe(handler);

    if (typeof options === 'undefined') options = {};
    this.key = options.key != null ? options.key : 'vuex';
    this.subscribed = false;
    this.supportCircular = options.supportCircular || false;

    if (this.supportCircular) {
      FlattedJSON = require('flatted');
    }

    this.mergeOption = options.mergeOption || 'replaceArrays';
    let localStorageLitmus = true;

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


    this.reducer = options.reducer != null ? options.reducer : options.modules == null ? state => state : state => options.modules.reduce((a, i) => merge(a, {
      [i]: state[i]
    }, this.mergeOption), {
      /* start empty accumulator*/
    });

    this.filter = options.filter || (mutation => true);

    this.strictMode = options.strictMode || false;

    this.RESTORE_MUTATION = function RESTORE_MUTATION(state, savedState) {
      const mergedState = merge(state, savedState || {}, this.mergeOption);

      for (const propertyName of Object.keys(mergedState)) {
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
      this.restoreState = options.restoreState != null ? options.restoreState : (key, storage) => storage.getItem(key).then(value => typeof value === 'string' // If string, parse, or else, just return
      ? this.supportCircular ? FlattedJSON.parse(value || '{}') : JSON.parse(value || '{}') : value || {});
      /**
       * Async {@link #VuexPersistence.saveState} implementation
       * @type {((key: string, state: {}, storage?: Storage) =>
       *    (Promise<void> | void)) | ((key: string, state: {}, storage?: Storage) => Promise<void>)}
       */

      this.saveState = options.saveState != null ? options.saveState : (key, state, storage) => storage.setItem(key, // Second argument is state _object_ if asyc storage, stringified otherwise
      // do not stringify the state if the storage type is async
      this.asyncStorage ? merge({}, state || {}, this.mergeOption) : this.supportCircular ? FlattedJSON.stringify(state) : JSON.stringify(state));
      /**
       * Async version of plugin
       * @param {Store<S>} store
       */

      this.plugin = store => {
        /**
         * For async stores, we're capturing the Promise returned
         * by the `restoreState()` function in a `restored` property
         * on the store itself. This would allow app developers to
         * determine when and if the store's state has indeed been
         * refreshed. This approach was suggested by GitHub user @hotdogee.
         * See https://github.com/championswimmer/vuex-persist/pull/118#issuecomment-500914963
         * @since 2.1.0
         */
        store.restored = this.restoreState(this.key, this.storage).then(savedState => {
          /**
           * If in strict mode, do only via mutation
           */
          if (this.strictMode) {
            store.commit('RESTORE_MUTATION', savedState);
          } else {
            store.replaceState(merge(store.state, savedState || {}, this.mergeOption));
          }

          this.subscriber(store)((mutation, state) => {
            if (this.filter(mutation)) {
              this._mutex.enqueue(this.saveState(this.key, this.reducer(state), this.storage));
            }
          });
          this.subscribed = true;
        });
      };
    } else {
      /**
       * Sync {@link #VuexPersistence.restoreState} implementation
       * @type {((key: string, storage?: Storage) =>
       *    (Promise<S> | S)) | ((key: string, storage: Storage) => (any | string | {}))}
       */
      this.restoreState = options.restoreState != null ? options.restoreState : (key, storage) => {
        const value = storage.getItem(key);

        if (typeof value === 'string') {
          // If string, parse, or else, just return
          return this.supportCircular ? FlattedJSON.parse(value || '{}') : JSON.parse(value || '{}');
        } else {
          return value || {};
        }
      };
      /**
       * Sync {@link #VuexPersistence.saveState} implementation
       * @type {((key: string, state: {}, storage?: Storage) =>
       *     (Promise<void> | void)) | ((key: string, state: {}, storage?: Storage) => Promise<void>)}
       */

      this.saveState = options.saveState != null ? options.saveState : (key, state, storage) => storage.setItem(key, // Second argument is state _object_ if localforage, stringified otherwise
      this.supportCircular ? FlattedJSON.stringify(state) : JSON.stringify(state));
      /**
       * Sync version of plugin
       * @param {Store<S>} store
       */

      this.plugin = store => {
        const savedState = this.restoreState(this.key, this.storage);

        if (this.strictMode) {
          store.commit('RESTORE_MUTATION', savedState);
        } else {
          store.replaceState(merge(store.state, savedState || {}, this.mergeOption));
        }

        this.subscriber(store)((mutation, state) => {
          if (this.filter(mutation)) {
            this.saveState(this.key, this.reducer(state), this.storage);
          }
        });
        this.subscribed = true;
      };
    }
  }

}

var VuexPersistence$1 = VuexPersistence;

// The options for persisting state
// eslint-disable-next-line import/prefer-default-export
const persistedStateOptions = {
  storage: window.sessionStorage,
  key: 'joomla.mediamanager',
  reducer: state => ({
    selectedDirectory: state.selectedDirectory,
    showInfoBar: state.showInfoBar,
    listView: state.listView,
    gridSize: state.gridSize,
    search: state.search
  })
};

const options = Joomla.getOptions('com_media', {});

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


const getDrives = (adapterNames, provider) => adapterNames.map(name => ({
  root: `${provider}-${name}:/`,
  displayName: name
})); // Load disks from options


const loadedDisks = options.providers.map(disk => ({
  displayName: disk.displayName,
  drives: getDrives(disk.adapterNames, disk.name)
}));
const defaultDisk = loadedDisks.find(disk => disk.drives.length > 0 && disk.drives[0] !== undefined);

if (!defaultDisk) {
  throw new TypeError('No default media drive was found');
}

let currentPath;
const storedState = JSON.parse(persistedStateOptions.storage.getItem(persistedStateOptions.key)); // Gracefully use the given path, the session storage state or fall back to sensible default

if (options.currentPath) {
  let useDrive = false;
  Object.values(loadedDisks).forEach(drive => drive.drives.forEach(curDrive => {
    if (options.currentPath.indexOf(curDrive.root) === 0) {
      useDrive = true;
    }
  }));

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
  directories: loadedDisks.map(() => ({
    path: defaultDisk.drives[0].root,
    name: defaultDisk.displayName,
    directories: [],
    files: [],
    directory: null
  })),
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
};

// Sometimes we may need to compute derived state based on store state,
// for example filtering through a list of items and counting them.

/**
 * Get the currently selected directory
 * @param state
 * @returns {*}
 */
const getSelectedDirectory = state => state.directories.find(directory => directory.path === state.selectedDirectory);
/**
 * Get the sudirectories of the currently selected directory
 * @param state
 *
 * @returns {Array|directories|{/}|computed.directories|*|Object}
 */

const getSelectedDirectoryDirectories = state => state.directories.filter(directory => directory.directory === state.selectedDirectory);
/**
 * Get the files of the currently selected directory
 * @param state
 *
 * @returns {Array|files|{}|FileList|*}
 */

const getSelectedDirectoryFiles = state => state.files.filter(file => file.directory === state.selectedDirectory);
/**
 * Whether or not all items of the current directory are selected
 * @param state
 * @param getters
 * @returns Array
 */

const getSelectedDirectoryContents = (state, getters) => [...getters.getSelectedDirectoryDirectories, ...getters.getSelectedDirectoryFiles];

var getters = /*#__PURE__*/Object.freeze({
    __proto__: null,
    getSelectedDirectory: getSelectedDirectory,
    getSelectedDirectoryDirectories: getSelectedDirectoryDirectories,
    getSelectedDirectoryFiles: getSelectedDirectoryFiles,
    getSelectedDirectoryContents: getSelectedDirectoryContents
});

const updateUrlPath = path => {
  const currentPath = path === null ? '' : path;
  const url = new URL(window.location.href);

  if (url.searchParams.has('path')) {
    window.history.pushState(null, '', url.href.replace(/\b(path=).*?(&|$)/, `$1${currentPath}$2`));
  } else {
    window.history.pushState(null, '', `${url.href + (url.href.indexOf('?') > 0 ? '&' : '?')}path=${currentPath}`);
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


const getContents = (context, payload) => {
  // Update the url
  updateUrlPath(payload);
  context.commit(SET_IS_LOADING, true);
  api.getContents(payload, 0).then(contents => {
    context.commit(LOAD_CONTENTS_SUCCESS, contents);
    context.commit(UNSELECT_ALL_BROWSER_ITEMS);
    context.commit(SELECT_DIRECTORY, payload);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
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

const getFullContents = (context, payload) => {
  context.commit(SET_IS_LOADING, true);
  api.getContents(payload.path, 1).then(contents => {
    context.commit(LOAD_FULL_CONTENTS_SUCCESS, contents.files[0]);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
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

const download = (context, payload) => {
  api.getContents(payload.path, 0, 1).then(contents => {
    const file = contents.files[0]; // Convert the base 64 encoded string to a blob

    const byteCharacters = atob(file.content);
    const byteArrays = [];

    for (let offset = 0; offset < byteCharacters.length; offset += 512) {
      const slice = byteCharacters.slice(offset, offset + 512);
      const byteNumbers = new Array(slice.length); // eslint-disable-next-line no-plusplus

      for (let i = 0; i < slice.length; i++) {
        byteNumbers[i] = slice.charCodeAt(i);
      }

      const byteArray = new Uint8Array(byteNumbers);
      byteArrays.push(byteArray);
    } // Download file


    const blobURL = URL.createObjectURL(new Blob(byteArrays, {
      type: file.mime_type
    }));
    const a = document.createElement('a');
    a.href = blobURL;
    a.download = file.name;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  }).catch(error => {
    // eslint-disable-next-line no-console
    console.log('error', error);
  });
};
/**
 * Toggle the selection state of an item
 * @param context
 * @param payload
 */

const toggleBrowserItemSelect = (context, payload) => {
  const item = payload;
  const isSelected = context.state.selectedItems.some(selected => selected.path === item.path);

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

const createDirectory = (context, payload) => {
  if (!api.canCreate) {
    return;
  }

  context.commit(SET_IS_LOADING, true);
  api.createDirectory(payload.name, payload.parent).then(folder => {
    context.commit(CREATE_DIRECTORY_SUCCESS, folder);
    context.commit(HIDE_CREATE_FOLDER_MODAL);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
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

const uploadFile = (context, payload) => {
  if (!api.canCreate) {
    return;
  }

  context.commit(SET_IS_LOADING, true);
  api.upload(payload.name, payload.parent, payload.content, payload.override || false).then(file => {
    context.commit(UPLOAD_SUCCESS, file);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
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

const renameItem = (context, payload) => {
  if (!api.canEdit) {
    return;
  }

  if (typeof payload.item.canEdit !== 'undefined' && payload.item.canEdit === false) {
    return;
  }

  context.commit(SET_IS_LOADING, true);
  api.rename(payload.item.path, payload.newPath).then(item => {
    context.commit(RENAME_SUCCESS, {
      item,
      oldPath: payload.item.path,
      newName: payload.newName
    });
    context.commit(HIDE_RENAME_MODAL);
    context.commit(SET_IS_LOADING, false);
  }).catch(error => {
    // @todo error handling
    context.commit(SET_IS_LOADING, false); // eslint-disable-next-line no-console

    console.log('error', error);
  });
};
/**
 * Delete the selected items
 * @param context
 */

const deleteSelectedItems = context => {
  if (!api.canDelete) {
    return;
  }

  context.commit(SET_IS_LOADING, true); // Get the selected items from the store

  const {
    selectedItems,
    search
  } = context.state;

  if (selectedItems.length > 0) {
    selectedItems.forEach(item => {
      if (typeof item.canDelete !== 'undefined' && item.canDelete === false || search && !item.name.toLowerCase().includes(search.toLowerCase())) {
        return;
      }

      api.delete(item.path).then(() => {
        context.commit(DELETE_SUCCESS, item);
        context.commit(UNSELECT_ALL_BROWSER_ITEMS);
        context.commit(SET_IS_LOADING, false);
      }).catch(error => {
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
});

// Mutations are very similar to events: each mutation has a string type and a handler.
// The handler function is where we perform actual state modifications,
// and it will receive the state as the first argument.
// The grid item sizes

const gridItemSizes = ['sm', 'md', 'lg', 'xl'];
var mutations = {
  /**
   * Select a directory
   * @param state
   * @param payload
   */
  [SELECT_DIRECTORY]: (state, payload) => {
    state.selectedDirectory = payload;
    state.search = '';
  },

  /**
   * The load content success mutation
   * @param state
   * @param payload
   */
  [LOAD_CONTENTS_SUCCESS]: (state, payload) => {
    /**
     * Create the directory structure
     * @param path
     */
    function createDirectoryStructureFromPath(path) {
      const exists = state.directories.some(existing => existing.path === path);

      if (!exists) {
        // eslint-disable-next-line no-use-before-define
        const directory = directoryFromPath(path); // Add the sub directories and files

        directory.directories = state.directories.filter(existing => existing.directory === directory.path).map(existing => existing.path); // Add the directory

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
      const parts = path.split('/');
      let directory = dirname(path);

      if (directory.indexOf(':', directory.length - 1) !== -1) {
        directory += '/';
      }

      return {
        path,
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
      const parentDirectory = state.directories.find(existing => existing.path === directory.directory);
      const parentDirectoryIndex = state.directories.indexOf(parentDirectory);
      let index = state.directories.findIndex(existing => existing.path === directory.path);

      if (index === -1) {
        index = state.directories.length;
      } // Add the directory


      state.directories.splice(index, 1, directory); // Update the relation to the parent directory

      if (parentDirectoryIndex !== -1) {
        state.directories.splice(parentDirectoryIndex, 1, { ...parentDirectory,
          directories: [...parentDirectory.directories, directory.path]
        });
      }
    }
    /**
     * Add a file
     * @param state
     * @param directory
     */
    // eslint-disable-next-line no-shadow


    function addFile(state, file) {
      const parentDirectory = state.directories.find(directory => directory.path === file.directory);
      const parentDirectoryIndex = state.directories.indexOf(parentDirectory);
      let index = state.files.findIndex(existing => existing.path === file.path);

      if (index === -1) {
        index = state.files.length;
      } // Add the file


      state.files.splice(index, 1, file); // Update the relation to the parent directory

      if (parentDirectoryIndex !== -1) {
        state.directories.splice(parentDirectoryIndex, 1, { ...parentDirectory,
          files: [...parentDirectory.files, file.path]
        });
      }
    } // Create the parent directory structure if it does not exist


    createDirectoryStructureFromPath(state.selectedDirectory); // Add directories

    payload.directories.forEach(directory => {
      addDirectory(state, directory);
    }); // Add files

    payload.files.forEach(file => {
      addFile(state, file);
    });
  },

  /**
   * The upload success mutation
   * @param state
   * @param payload
   */
  [UPLOAD_SUCCESS]: (state, payload) => {
    const file = payload;
    const isNew = !state.files.some(existing => existing.path === file.path); // @todo handle file_exists

    if (isNew) {
      const parentDirectory = state.directories.find(existing => existing.path === file.directory);
      const parentDirectoryIndex = state.directories.indexOf(parentDirectory); // Add the new file to the files array

      state.files.push(file); // Update the relation to the parent directory

      state.directories.splice(parentDirectoryIndex, 1, { ...parentDirectory,
        files: [...parentDirectory.files, file.path]
      });
    }
  },

  /**
   * The create directory success mutation
   * @param state
   * @param payload
   */
  [CREATE_DIRECTORY_SUCCESS]: (state, payload) => {
    const directory = payload;
    const isNew = !state.directories.some(existing => existing.path === directory.path);

    if (isNew) {
      const parentDirectory = state.directories.find(existing => existing.path === directory.directory);
      const parentDirectoryIndex = state.directories.indexOf(parentDirectory); // Add the new directory to the directory

      state.directories.push(directory); // Update the relation to the parent directory

      state.directories.splice(parentDirectoryIndex, 1, { ...parentDirectory,
        directories: [...parentDirectory.directories, directory.path]
      });
    }
  },

  /**
   * The rename success handler
   * @param state
   * @param payload
   */
  [RENAME_SUCCESS]: (state, payload) => {
    state.selectedItems[state.selectedItems.length - 1].name = payload.newName;
    const {
      item
    } = payload;
    const {
      oldPath
    } = payload;

    if (item.type === 'file') {
      const index = state.files.findIndex(file => file.path === oldPath);
      state.files.splice(index, 1, item);
    } else {
      const index = state.directories.findIndex(directory => directory.path === oldPath);
      state.directories.splice(index, 1, item);
    }
  },

  /**
   * The delete success mutation
   * @param state
   * @param payload
   */
  [DELETE_SUCCESS]: (state, payload) => {
    const item = payload; // Delete file

    if (item.type === 'file') {
      state.files.splice(state.files.findIndex(file => file.path === item.path), 1);
    } // Delete dir


    if (item.type === 'dir') {
      state.directories.splice(state.directories.findIndex(directory => directory.path === item.path), 1);
    }
  },

  /**
   * Select a browser item
   * @param state
   * @param payload the item
   */
  [SELECT_BROWSER_ITEM]: (state, payload) => {
    state.selectedItems.push(payload);
  },

  /**
   * Select browser items
   * @param state
   * @param payload the items
   */
  [SELECT_BROWSER_ITEMS]: (state, payload) => {
    state.selectedItems = payload;
  },

  /**
   * Unselect a browser item
   * @param state
   * @param payload the item
   */
  [UNSELECT_BROWSER_ITEM]: (state, payload) => {
    const item = payload;
    state.selectedItems.splice(state.selectedItems.findIndex(selectedItem => selectedItem.path === item.path), 1);
  },

  /**
   * Unselect all browser items
   * @param state
   * @param payload the item
   */
  [UNSELECT_ALL_BROWSER_ITEMS]: state => {
    state.selectedItems = [];
  },

  /**
   * Show the create folder modal
   * @param state
   */
  [SHOW_CREATE_FOLDER_MODAL]: state => {
    state.showCreateFolderModal = true;
  },

  /**
   * Hide the create folder modal
   * @param state
   */
  [HIDE_CREATE_FOLDER_MODAL]: state => {
    state.showCreateFolderModal = false;
  },

  /**
   * Show the info bar
   * @param state
   */
  [SHOW_INFOBAR]: state => {
    state.showInfoBar = true;
  },

  /**
   * Show the info bar
   * @param state
   */
  [HIDE_INFOBAR]: state => {
    state.showInfoBar = false;
  },

  /**
   * Define the list grid view
   * @param state
   */
  [CHANGE_LIST_VIEW]: (state, view) => {
    state.listView = view;
  },

  /**
   * FUll content is loaded
   * @param state
   * @param payload
   */
  [LOAD_FULL_CONTENTS_SUCCESS]: (state, payload) => {
    state.previewItem = payload;
  },

  /**
   * Show the preview modal
   * @param state
   */
  [SHOW_PREVIEW_MODAL]: state => {
    state.showPreviewModal = true;
  },

  /**
   * Hide the preview modal
   * @param state
   */
  [HIDE_PREVIEW_MODAL]: state => {
    state.showPreviewModal = false;
  },

  /**
   * Set the is loading state
   * @param state
   */
  [SET_IS_LOADING]: (state, payload) => {
    state.isLoading = payload;
  },

  /**
   * Show the rename modal
   * @param state
   */
  [SHOW_RENAME_MODAL]: state => {
    state.showRenameModal = true;
  },

  /**
   * Hide the rename modal
   * @param state
   */
  [HIDE_RENAME_MODAL]: state => {
    state.showRenameModal = false;
  },

  /**
   * Show the share modal
   * @param state
   */
  [SHOW_SHARE_MODAL]: state => {
    state.showShareModal = true;
  },

  /**
   * Hide the share modal
   * @param state
   */
  [HIDE_SHARE_MODAL]: state => {
    state.showShareModal = false;
  },

  /**
   * Increase the size of the grid items
   * @param state
   */
  [INCREASE_GRID_SIZE]: state => {
    let currentSizeIndex = gridItemSizes.indexOf(state.gridSize);

    if (currentSizeIndex >= 0 && currentSizeIndex < gridItemSizes.length - 1) {
      // eslint-disable-next-line no-plusplus
      state.gridSize = gridItemSizes[++currentSizeIndex];
    }
  },

  /**
   * Increase the size of the grid items
   * @param state
   */
  [DECREASE_GRID_SIZE]: state => {
    let currentSizeIndex = gridItemSizes.indexOf(state.gridSize);

    if (currentSizeIndex > 0 && currentSizeIndex < gridItemSizes.length) {
      // eslint-disable-next-line no-plusplus
      state.gridSize = gridItemSizes[--currentSizeIndex];
    }
  },

  /**
   * Set search query
   * @param state
   * @param query
   */
  [SET_SEARCH_QUERY]: (state, query) => {
    state.search = query;
  },

  /**
   * Show the confirm modal
   * @param state
   */
  [SHOW_CONFIRM_DELETE_MODAL]: state => {
    state.showConfirmDeleteModal = true;
  },

  /**
   * Hide the confirm modal
   * @param state
   */
  [HIDE_CONFIRM_DELETE_MODAL]: state => {
    state.showConfirmDeleteModal = false;
  }
};

var store = createStore({
  state,
  getters,
  actions,
  mutations,
  plugins: [new VuexPersistence$1(persistedStateOptions).plugin],
  strict: "production" !== 'production'
});

var script$7 = {
  name: 'MediaBrowserActionItemRename',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openRenameModal() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$7 = /*#__PURE__*/createBaseVNode("span", {
  class: "image-browser-action fa fa-i-cursor",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_2$6 = { class: "action-text" };

function render$7(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    ref: "actionRenameButton",
    type: "button",
    class: "action-rename",
    onClick: _cache[0] || (_cache[0] = withModifiers($event => ($options.openRenameModal()), ["stop"])),
    onKeyup: [
      _cache[1] || (_cache[1] = withKeys($event => ($options.openRenameModal()), ["enter"])),
      _cache[2] || (_cache[2] = withKeys($event => ($options.openRenameModal()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _hoisted_1$7,
    createBaseVNode("span", _hoisted_2$6, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_RENAME')), 1 /* TEXT */)
  ], 544 /* HYDRATE_EVENTS, NEED_PATCH */))
}

script$7.render = render$7;
script$7.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/rename.vue";

var script$6 = {
  name: 'MediaBrowserActionItemToggle',
  props: {
    mainAction: { type: Function, default: () => {} },
  },
  emits: ['on-focused'],
  methods: {
    openActions() {
      this.mainAction();
    },
    focused(bool) {
      this.$emit('on-focused', bool);
    },
  },
};

const _hoisted_1$6 = ["aria-label", "title"];

function render$6(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-toggle",
    "aria-label": _ctx.sprintf('COM_MEDIA_MANAGE_ITEM', (_ctx.$parent.$props.item.name)),
    title: _ctx.sprintf('COM_MEDIA_MANAGE_ITEM', (_ctx.$parent.$props.item.name)),
    onKeyup: [
      _cache[1] || (_cache[1] = withKeys($event => ($options.openActions()), ["enter"])),
      _cache[4] || (_cache[4] = withKeys($event => ($options.openActions()), ["space"]))
    ],
    onFocus: _cache[2] || (_cache[2] = $event => ($options.focused(true))),
    onBlur: _cache[3] || (_cache[3] = $event => ($options.focused(false)))
  }, [
    createBaseVNode("span", {
      class: "image-browser-action icon-ellipsis-h",
      "aria-hidden": "true",
      onClick: _cache[0] || (_cache[0] = withModifiers($event => ($options.openActions()), ["stop"]))
    })
  ], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_1$6))
}

script$6.render = render$6;
script$6.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/toggle.vue";

var script$5 = {
  name: 'MediaBrowserActionItemPreview',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openPreview() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$5 = /*#__PURE__*/createBaseVNode("span", {
  class: "image-browser-action icon-search-plus",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_2$5 = { class: "action-text" };

function render$5(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-preview",
    onClick: _cache[0] || (_cache[0] = withModifiers($event => ($options.openPreview()), ["stop"])),
    onKeyup: [
      _cache[1] || (_cache[1] = withKeys($event => ($options.openPreview()), ["enter"])),
      _cache[2] || (_cache[2] = withKeys($event => ($options.openPreview()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _hoisted_1$5,
    createBaseVNode("span", _hoisted_2$5, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_PREVIEW')), 1 /* TEXT */)
  ], 32 /* HYDRATE_EVENTS */))
}

script$5.render = render$5;
script$5.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/preview.vue";

var script$4 = {
  name: 'MediaBrowserActionItemDownload',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    download() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$4 = /*#__PURE__*/createBaseVNode("span", {
  class: "image-browser-action icon-download",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_2$4 = { class: "action-text" };

function render$4(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-download",
    onKeyup: [
      _cache[0] || (_cache[0] = withKeys($event => ($options.download()), ["enter"])),
      _cache[1] || (_cache[1] = withKeys($event => ($options.download()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onClick: _cache[2] || (_cache[2] = withModifiers($event => ($options.download()), ["stop"])),
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _hoisted_1$4,
    createBaseVNode("span", _hoisted_2$4, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_DOWNLOAD')), 1 /* TEXT */)
  ], 32 /* HYDRATE_EVENTS */))
}

script$4.render = render$4;
script$4.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/download.vue";

var script$3 = {
  name: 'MediaBrowserActionItemShare',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openShareUrlModal() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$3 = /*#__PURE__*/createBaseVNode("span", {
  class: "image-browser-action icon-link",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_2$3 = { class: "action-text" };

function render$3(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-url",
    onClick: _cache[0] || (_cache[0] = withModifiers($event => ($options.openShareUrlModal()), ["stop"])),
    onKeyup: [
      _cache[1] || (_cache[1] = withKeys($event => ($options.openShareUrlModal()), ["enter"])),
      _cache[2] || (_cache[2] = withKeys($event => ($options.openShareUrlModal()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _hoisted_1$3,
    createBaseVNode("span", _hoisted_2$3, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_SHARE')), 1 /* TEXT */)
  ], 32 /* HYDRATE_EVENTS */))
}

script$3.render = render$3;
script$3.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/share.vue";

var script$2 = {
  name: 'MediaBrowserActionItemDelete',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openConfirmDeleteModal() {
      this.mainAction();
    },
    hideActions() {
      this.hideActions();
    },
    focused(bool) {
      this.onFocused(bool);
    },
  },
};

const _hoisted_1$2 = /*#__PURE__*/createBaseVNode("span", {
  class: "image-browser-action icon-trash",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_2$2 = { class: "action-text" };

function render$2(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-delete",
    onKeyup: [
      _cache[0] || (_cache[0] = withKeys($event => ($options.openConfirmDeleteModal()), ["enter"])),
      _cache[1] || (_cache[1] = withKeys($event => ($options.openConfirmDeleteModal()), ["space"])),
      _cache[4] || (_cache[4] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onFocus: _cache[2] || (_cache[2] = $event => ($options.focused(true))),
    onBlur: _cache[3] || (_cache[3] = $event => ($options.focused(false))),
    onClick: _cache[5] || (_cache[5] = withModifiers($event => ($options.openConfirmDeleteModal()), ["stop"]))
  }, [
    _hoisted_1$2,
    createBaseVNode("span", _hoisted_2$2, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_DELETE')), 1 /* TEXT */)
  ], 32 /* HYDRATE_EVENTS */))
}

script$2.render = render$2;
script$2.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/delete.vue";

var script$1 = {
  name: 'MediaBrowserActionItemEdit',
  props: {
    onFocused: { type: Function, default: () => {} },
    mainAction: { type: Function, default: () => {} },
    closingAction: { type: Function, default: () => {} },
  },
  methods: {
    openRenameModal() {
      this.mainAction();
    },
    hideActions() {
      this.closingAction();
    },
    focused(bool) {
      this.onFocused(bool);
    },
    editItem() {
      this.mainAction();
    },
  },
};

const _hoisted_1$1 = /*#__PURE__*/createBaseVNode("span", {
  class: "image-browser-action icon-pencil-alt",
  "aria-hidden": "true"
}, null, -1 /* HOISTED */);
const _hoisted_2$1 = { class: "action-text" };

function render$1(_ctx, _cache, $props, $setup, $data, $options) {
  return (openBlock(), createElementBlock("button", {
    type: "button",
    class: "action-edit",
    onKeyup: [
      _cache[0] || (_cache[0] = withKeys($event => ($options.editItem()), ["enter"])),
      _cache[1] || (_cache[1] = withKeys($event => ($options.editItem()), ["space"])),
      _cache[5] || (_cache[5] = withKeys($event => ($options.hideActions()), ["esc"]))
    ],
    onClick: _cache[2] || (_cache[2] = withModifiers($event => ($options.editItem()), ["stop"])),
    onFocus: _cache[3] || (_cache[3] = $event => ($options.focused(true))),
    onBlur: _cache[4] || (_cache[4] = $event => ($options.focused(false)))
  }, [
    _hoisted_1$1,
    createBaseVNode("span", _hoisted_2$1, toDisplayString(_ctx.translate('COM_MEDIA_ACTION_EDIT')), 1 /* TEXT */)
  ], 32 /* HYDRATE_EVENTS */))
}

script$1.render = render$1;
script$1.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/edit.vue";

var script = {
  name: 'MediaBrowserActionItemsContainer',
  props: {
    item: { type: Object, default: () => {} },
    edit: { type: Function, default: () => {} },
    previewable: { type: Boolean, default: false },
    downloadable: { type: Boolean, default: false },
    shareable: { type: Boolean, default: false },
  },
  emits: ['toggle-settings'],
  data() {
    return {
      showActions: false,
    };
  },
  computed: {
    canEdit() {
      return api.canEdit && (typeof this.item.canEdit !== 'undefined' ? this.item.canEdit : true);
    },
    canDelete() {
      return api.canDelete && (typeof this.item.canDelete !== 'undefined' ? this.item.canDelete : true);
    },
    canOpenEditView() {
      return ['jpg', 'jpeg', 'png'].includes(this.item.extension.toLowerCase());
    },
  },
  watch: {
    // eslint-disable-next-line
    "$store.state.showRenameModal"(show) {
      if (
        !show
        && this.$refs.actionToggle
        && this.$store.state.selectedItems.find(
          (item) => item.name === this.item.name,
        ) !== undefined
      ) {
        this.$refs.actionToggle.$el.focus();
      }
    },
  },
  methods: {
    /* Hide actions dropdown */
    hideActions() {
      this.showActions = false;
      this.$parent.$parent.$data.actionsActive = false;
    },
    /* Preview an item */
    openPreview() {
      this.$store.commit(SHOW_PREVIEW_MODAL);
      this.$store.dispatch('getFullContents', this.item);
    },
    /* Download an item */
    download() {
      this.$store.dispatch('download', this.item);
    },
    /* Opening confirm delete modal */
    openConfirmDeleteModal() {
      this.$store.commit(UNSELECT_ALL_BROWSER_ITEMS);
      this.$store.commit(SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(SHOW_CONFIRM_DELETE_MODAL);
    },
    /* Rename an item */
    openRenameModal() {
      this.hideActions();
      this.$store.commit(SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(SHOW_RENAME_MODAL);
    },
    /* Open modal for share url */
    openShareUrlModal() {
      this.$store.commit(SELECT_BROWSER_ITEM, this.item);
      this.$store.commit(SHOW_SHARE_MODAL);
    },
    /* Open actions dropdown */
    openActions() {
      this.showActions = true;
      this.$parent.$parent.$data.actionsActive = true;
      const buttons = [...this.$el.parentElement.querySelectorAll('.media-browser-actions-list button')];
      if (buttons.length) {
        buttons.forEach((button, i) => {
          if (i === (0)) {
            button.tabIndex = 0;
          } else {
            button.tabIndex = -1;
          }
        });
        buttons[0].focus();
      }
    },
    /* Open actions dropdown and focus on last element */
    openLastActions() {
      this.showActions = true;
      this.$parent.$parent.$data.actionsActive = true;
      const buttons = [...this.$el.parentElement.querySelectorAll('.media-browser-actions-list button')];
      if (buttons.length) {
        buttons.forEach((button, i) => {
          if (i === (buttons.length)) {
            button.tabIndex = 0;
          } else {
            button.tabIndex = -1;
          }
        });
        this.$nextTick(() => buttons[buttons.length - 1].focus());
      }
    },
    /* Focus on the next item or go to the beginning again */
    focusNext(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      const lastchild = buttons[buttons.length - 1];
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
    focusPrev(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      const firstchild = buttons[0];
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
    focusFirst(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      buttons[0].focus();
      buttons.forEach((button, i) => {
        if (i === 0) {
          button.tabIndex = 0;
        } else {
          button.tabIndex = -1;
        }
      });
    },
    /* Focus on the last item */
    focusLast(event) {
      const active = event.target;
      const buttons = [...active.parentElement.querySelectorAll('button')];
      buttons[buttons.length - 1].focus();
      buttons.forEach((button, i) => {
        if (i === (buttons.length)) {
          button.tabIndex = 0;
        } else {
          button.tabIndex = -1;
        }
      });
    },
    editItem() {
      this.edit();
    },
    focused(bool) {
      this.$emit('toggle-settings', bool);
    },
  },
};

const _hoisted_1 = ["aria-label", "title"];
const _hoisted_2 = ["aria-label"];
const _hoisted_3 = {
  "aria-hidden": "true",
  class: "media-browser-actions-item-name"
};

function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_media_browser_action_item_toggle = resolveComponent("media-browser-action-item-toggle");
  const _component_media_browser_action_item_preview = resolveComponent("media-browser-action-item-preview");
  const _component_media_browser_action_item_download = resolveComponent("media-browser-action-item-download");
  const _component_media_browser_action_item_rename = resolveComponent("media-browser-action-item-rename");
  const _component_media_browser_action_item_edit = resolveComponent("media-browser-action-item-edit");
  const _component_media_browser_action_item_share = resolveComponent("media-browser-action-item-share");
  const _component_media_browser_action_item_delete = resolveComponent("media-browser-action-item-delete");

  return (openBlock(), createElementBlock(Fragment, null, [
    createBaseVNode("span", {
      class: "media-browser-select",
      "aria-label": _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      title: _ctx.translate('COM_MEDIA_TOGGLE_SELECT_ITEM'),
      tabindex: "0",
      onFocusin: _cache[0] || (_cache[0] = $event => ($options.focused(true))),
      onFocusout: _cache[1] || (_cache[1] = $event => ($options.focused(false)))
    }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_1),
    createBaseVNode("div", {
      class: normalizeClass(["media-browser-actions", { active: $data.showActions }])
    }, [
      createVNode(_component_media_browser_action_item_toggle, {
        ref: "actionToggle",
        "main-action": $options.openActions,
        onOnFocused: $options.focused,
        onKeyup: [
          _cache[2] || (_cache[2] = withKeys($event => ($options.openLastActions()), ["up"])),
          _cache[3] || (_cache[3] = withKeys($event => ($options.openActions()), ["down"])),
          _cache[4] || (_cache[4] = withKeys($event => ($options.openLastActions()), ["end"])),
          _cache[5] || (_cache[5] = withKeys($event => ($options.openActions()), ["home"]))
        ],
        onKeydown: [
          _cache[6] || (_cache[6] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
          _cache[7] || (_cache[7] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
          _cache[8] || (_cache[8] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
          _cache[9] || (_cache[9] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
        ]
      }, null, 8 /* PROPS */, ["main-action", "onOnFocused"]),
      ($data.showActions)
        ? (openBlock(), createElementBlock("div", {
            key: 0,
            ref: "actionList",
            class: "media-browser-actions-list",
            role: "toolbar",
            "aria-orientation": "vertical",
            "aria-label": _ctx.sprintf('COM_MEDIA_ACTIONS_TOOLBAR_LABEL',(_ctx.$parent.$props.item.name))
          }, [
            createBaseVNode("span", _hoisted_3, [
              createBaseVNode("strong", null, toDisplayString(_ctx.$parent.$props.item.name), 1 /* TEXT */)
            ]),
            ($props.previewable)
              ? (openBlock(), createBlock(_component_media_browser_action_item_preview, {
                  key: 0,
                  ref: "actionPreview",
                  "on-focused": $options.focused,
                  "main-action": $options.openPreview,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[10] || (_cache[10] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[11] || (_cache[11] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    _cache[12] || (_cache[12] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[13] || (_cache[13] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"])),
                    withKeys($options.hideActions, ["tab"])
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"]),
                    withKeys($options.hideActions, ["esc"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($props.downloadable)
              ? (openBlock(), createBlock(_component_media_browser_action_item_download, {
                  key: 1,
                  ref: "actionDownload",
                  "on-focused": $options.focused,
                  "main-action": $options.download,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[14] || (_cache[14] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[15] || (_cache[15] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[16] || (_cache[16] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[17] || (_cache[17] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($options.canEdit)
              ? (openBlock(), createBlock(_component_media_browser_action_item_rename, {
                  key: 2,
                  ref: "actionRename",
                  "on-focused": $options.focused,
                  "main-action": $options.openRenameModal,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[18] || (_cache[18] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[19] || (_cache[19] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[20] || (_cache[20] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[21] || (_cache[21] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($options.canEdit && $options.canOpenEditView)
              ? (openBlock(), createBlock(_component_media_browser_action_item_edit, {
                  key: 3,
                  ref: "actionEdit",
                  "on-focused": $options.focused,
                  "main-action": $options.editItem,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[22] || (_cache[22] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[23] || (_cache[23] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[24] || (_cache[24] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[25] || (_cache[25] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($props.shareable)
              ? (openBlock(), createBlock(_component_media_browser_action_item_share, {
                  key: 4,
                  ref: "actionShare",
                  "on-focused": $options.focused,
                  "main-action": $options.openShareUrlModal,
                  "closing-action": $options.hideActions,
                  onKeydown: [
                    _cache[26] || (_cache[26] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[27] || (_cache[27] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[28] || (_cache[28] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[29] || (_cache[29] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "closing-action", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true),
            ($options.canDelete)
              ? (openBlock(), createBlock(_component_media_browser_action_item_delete, {
                  key: 5,
                  ref: "actionDelete",
                  "on-focused": $options.focused,
                  "main-action": $options.openConfirmDeleteModal,
                  "hide-actions": $options.hideActions,
                  onKeydown: [
                    _cache[30] || (_cache[30] = withKeys(withModifiers(() => {}, ["prevent"]), ["up"])),
                    _cache[31] || (_cache[31] = withKeys(withModifiers(() => {}, ["prevent"]), ["down"])),
                    withKeys($options.hideActions, ["tab"]),
                    _cache[32] || (_cache[32] = withKeys(withModifiers(() => {}, ["prevent"]), ["home"])),
                    _cache[33] || (_cache[33] = withKeys(withModifiers(() => {}, ["prevent"]), ["end"]))
                  ],
                  onKeyup: [
                    withKeys($options.focusPrev, ["up"]),
                    withKeys($options.focusNext, ["down"]),
                    withKeys($options.hideActions, ["esc"]),
                    withKeys($options.focusLast, ["end"]),
                    withKeys($options.focusFirst, ["home"])
                  ]
                }, null, 8 /* PROPS */, ["on-focused", "main-action", "hide-actions", "onKeyup", "onKeydown"]))
              : createCommentVNode("v-if", true)
          ], 8 /* PROPS */, _hoisted_2))
        : createCommentVNode("v-if", true)
    ], 2 /* CLASS */)
  ], 64 /* STABLE_FRAGMENT */))
}

script.render = render;
script.__file = "administrator/components/com_media/resources/scripts/components/browser/actionItems/actionItemsContainer.vue";

window.MediaManager = window.MediaManager || {}; // Register the media manager event bus

window.MediaManager.Event = new Event$1(); // Create the Vue app instance

createApp(script$t).use(store).use(Translate) // Register the vue components
.component('MediaDrive', script$r).component('MediaDisk', script$s).component('MediaTree', script$q).component('MediaToolbar', script$p).component('MediaBreadcrumb', script$o).component('MediaBrowser', script$n).component('MediaBrowserItem', BrowserItem).component('MediaBrowserItemRow', script$g).component('MediaModal', script$f).component('MediaCreateFolderModal', script$e).component('MediaPreviewModal', script$d).component('MediaRenameModal', script$c).component('MediaShareModal', script$b).component('MediaConfirmDeleteModal', script$a).component('MediaInfobar', script$9).component('MediaUpload', script$8).component('MediaBrowserActionItemToggle', script$6).component('MediaBrowserActionItemPreview', script$5).component('MediaBrowserActionItemDownload', script$4).component('MediaBrowserActionItemRename', script$7).component('MediaBrowserActionItemShare', script$3).component('MediaBrowserActionItemDelete', script$2).component('MediaBrowserActionItemEdit', script$1).component('MediaBrowserActionItemsContainer', script).mount('#com-media');
