(function ($) {
    var SessionKeeper = function (options) {
        var root = this;
        this.vars = {
            messagetype: 'js',
            timeout: 0,
            warning: 0,
            redirect: false,
            strings: null
        };
        this.events = {
            warning: false,
            expired: false
        };
        this.warningoutput = {
            remaining: 0,
            percent: 0
        };
        this.warning = false;
        this.timeout = false;
        this.warningtimer = false;
        this.expiredtimer = false;
        this.countdown = false;
        var construct = function (options) {
            if (Joomla.getOptions('system.keepalive') !== undefined) {
                console.log(options.strings.CONSOLE_INACTIVE);
                return;
            }
            Object.assign(root.vars, options);
            if (root.vars.messagetype === 'modal') {
                $('.plg_system_sessionkeeper_rescue').click(function () {
                    root.keepaliveRequest();
                });
                if (root.vars.redirect) {
                    $('#sessionKeeperExpired').on('hidden', function () {
                        window.location.href = root.vars.redirect;
                    }).on('show', function () {
                        $('#sessionKeeperWarning').modal('hide').css('display', 'none');
                    });
                }
            }
            root.setTimers();
        };
        this.setTimers = function () {
            var now = new Date();
            root.warning = now.getTime() + ((root.vars.timeout - root.vars.warning) * 60000);
            root.timeout = now.getTime() + (root.vars.timeout * 60000);
            if (root.warningtimer !== false) {
                clearTimeout(root.warningtimer);
            }
            root.warningtimer = setTimeout(function () {
                root.displayWarning();
            }, (root.warning - now.getTime()));
            if (root.expiredtimer !== false) {
                clearTimeout(root.expiredtimer);
            }
            root.expiredtimer = setTimeout(function () {
                root.displayExpired();
            }, (root.timeout - now.getTime()));
        };
        this.displayWarning = function () {
            switch (root.vars.messagetype) {
                case 'js':
                    var message = root.vars.strings.JSWARNINGMESSAGE;
                    if (confirm(message)) {
                        root.keepaliveRequest();
                    }
                    break;
                case 'modal':
                    root.modalWarning();
                    break;
                case 'event':
                    root.eventWarning();
                    break;
            }
        };
        this.eventWarning = function () {
            var date = new Date();
            var timeout = new Date(date.getTime() + (root.vars.warning * 60000));
            root.countdown = setInterval(function () {
                root.countDown(timeout);
            }, 500);
            if (!root.events.warning)
                root.events.warning = new CustomEvent('SessionKeeperWarning');
            document.dispatchEvent(root.events.warning);
        };
        this.eventExpired = function () {
            if (!root.events.expired)
                root.events.expired = new CustomEvent('SessionKeeperExpired');
            document.dispatchEvent(root.events.expired);
        };
        this.modalWarning = function () {
            $('#sessionKeeperWarning').modal('show');
            var date = new Date();
            var timeout = new Date(date.getTime() + (root.vars.warning * 60000));
            root.countdown = setInterval(function () {
                root.countDown(timeout);
                $('#sessionKeeperWarning .hms').html(root.s2hms(root.warningoutput.remaining / 1000));
                $('#sessionKeeperWarning .progress .bar').css('width', root.warningoutput.percent + '%');
            }, 100);
        };
        this.modalExpired = function () {
            $('#sessionKeeperWarning').modal('hide').css('display', 'none');
            $('#sessionKeeperExpired').modal('show');
        };
        this.countDown = function (timeout) {
            var now = new Date();
            root.warningoutput.remaining = timeout - now.getTime();
            root.warningoutput.percent = (root.warningoutput.remaining / (root.vars.warning * 60000)) * 100;
            if (root.warningoutput.remaining <= 0) {
                clearInterval(root.countdown);
                root.coundown = false;
            }
        };
        this.s2hms = function (s) {
            var date = new Date(null);
            date.setSeconds(s);
            return date.toISOString().substr(11, 8);
        };
        this.keepaliveRequest = function () {
            var promise = root.getJSON(Joomla.getOptions('system.paths').base, {option: 'com_ajax', format: 'json'})
                    .then(function (response) {
                        $('#sessionKeeperWarning').modal('hide').css('display', 'none');
                        root.setTimers();
                    })
                    .catch(function (error) {
                        $('#sessionKeeperWarning').modal('hide').css('display', 'none');
                        alert(error);
                    });
        };
        this.displayExpired = function () {
            switch (root.vars.messagetype) {
                case 'js':
                    var message = root.vars.strings.EXPIREDMESSAGE;
                    alert(message);
                    if (root.vars.redirect) {
                        window.location.href = root.vars.redirect;
                    }
                    break;
                case 'modal':
                    clearInterval(root.countdown);
                    root.coundown = false;
                    root.modalExpired();
                    break;
                case 'event':
                    clearInterval(root.countdown);
                    root.coundown = false;
                    root.eventExpired();
                    break;
            }
            clearTimeout(root.warningtimer);
            clearTimeout(root.expiredtimer);
        };
        this.getJSON = function (url, args) {
            var parameters = [(new Date).getTime()];
            return new Promise(function (resolve, reject) {
                Object.keys(args).forEach(function (v, i) {
                    parameters.push(encodeURIComponent(v) + "=" + encodeURIComponent(args[v]));
                });
                var req = new XMLHttpRequest();
                req.open('GET', url + '?' + parameters.join('&'));
                req.withCredentials = true;
                req.onload = function () {
                    if (req.status === 200) {
                        resolve(JSON.parse(req.response));
                    } else {
                        reject(Error(req.statusText));
                    }
                };
                req.onerror = function () {
                    reject(Error('Network Error'));
                };
                req.send(null);
            });
        };
        construct(options);
    };
    $(document).ready(function ($) {
        var options;
        if (typeof Joomla === 'undefined') {
            var optext = $('.joomla-script-options').text();
            var site = JSON.parse(optext);
            var options = site.plg_system_sessionkeeper_config;
        } else {
            var options = Joomla.getOptions('plg_system_sessionkeeper_config');
        }
        window.plg_system_sessionkeeper = new SessionKeeper(options);
    });
})(jQuery);
// Polyfill for Object.assign in IE
if (typeof Object.assign !== 'function') {
    // Must be writable: true, enumerable: false, configurable: true
    Object.defineProperty(Object, "assign", {
        value: function assign(target, varArgs) { // .length of function is 2
            'use strict';
            if (target === null) { // TypeError if undefined or null
                throw new TypeError('Cannot convert undefined or null to object');
            }

            var to = Object(target);

            for (var index = 1; index < arguments.length; index++) {
                var nextSource = arguments[index];

                if (nextSource !== null) { // Skip over if undefined or null
                    for (var nextKey in nextSource) {
                        // Avoid bugs when hasOwnProperty is shadowed
                        if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                            to[nextKey] = nextSource[nextKey];
                        }
                    }
                }
            }
            return to;
        },
        writable: true,
        configurable: true
    });
}
;
// From https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/keys
if (!Object.keys) {
    Object.keys = (function () {
        'use strict';
        var hasOwnProperty = Object.prototype.hasOwnProperty,
                hasDontEnumBug = !({toString: null}).propertyIsEnumerable('toString'),
                dontEnums = [
                    'toString',
                    'toLocaleString',
                    'valueOf',
                    'hasOwnProperty',
                    'isPrototypeOf',
                    'propertyIsEnumerable',
                    'constructor'
                ],
                dontEnumsLength = dontEnums.length;

        return function (obj) {
            if (typeof obj !== 'function' && (typeof obj !== 'object' || obj === null)) {
                throw new TypeError('Object.keys called on non-object');
            }

            var result = [], prop, i;

            for (prop in obj) {
                if (hasOwnProperty.call(obj, prop)) {
                    result.push(prop);
                }
            }

            if (hasDontEnumBug) {
                for (i = 0; i < dontEnumsLength; i++) {
                    if (hasOwnProperty.call(obj, dontEnums[i])) {
                        result.push(dontEnums[i]);
                    }
                }
            }
            return result;
        };
    }());
}
;
// IE11 polyfill for date.toISOString
if (!Date.prototype.toISOString) {
    (function () {

        function pad(number) {
            if (number < 10) {
                return '0' + number;
            }
            return number;
        }

        Date.prototype.toISOString = function () {
            return this.getUTCFullYear() +
                    '-' + pad(this.getUTCMonth() + 1) +
                    '-' + pad(this.getUTCDate()) +
                    'T' + pad(this.getUTCHours()) +
                    ':' + pad(this.getUTCMinutes()) +
                    ':' + pad(this.getUTCSeconds()) +
                    '.' + (this.getUTCMilliseconds() / 1000).toFixed(3).slice(2, 5) +
                    'Z';
        };

    }());
}
//Production steps of ECMA-262, Edition 5, 15.4.4.18
//Reference: http://es5.github.io/#x15.4.4.18
if (!Array.prototype.forEach) {

    Array.prototype.forEach = function (callback/*, thisArg*/) {

        var T, k;

        if (this == null) {
            throw new TypeError('this is null or not defined');
        }

// 1. Let O be the result of calling toObject() passing the
// |this| value as the argument.
        var O = Object(this);

// 2. Let lenValue be the result of calling the Get() internal
// method of O with the argument "length".
// 3. Let len be toUint32(lenValue).
        var len = O.length >>> 0;

// 4. If isCallable(callback) is false, throw a TypeError exception.
// See: http://es5.github.com/#x9.11
        if (typeof callback !== 'function') {
            throw new TypeError(callback + ' is not a function');
        }

// 5. If thisArg was supplied, let T be thisArg; else let
// T be undefined.
        if (arguments.length > 1) {
            T = arguments[1];
        }

// 6. Let k be 0.
        k = 0;

// 7. Repeat while k < len.
        while (k < len) {

            var kValue;

            // a. Let Pk be ToString(k).
            //    This is implicit for LHS operands of the in operator.
            // b. Let kPresent be the result of calling the HasProperty
            //    internal method of O with argument Pk.
            //    This step can be combined with c.
            // c. If kPresent is true, then
            if (k in O) {

                // i. Let kValue be the result of calling the Get internal
                // method of O with argument Pk.
                kValue = O[k];

                // ii. Call the Call internal method of callback with T as
                // the this value and argument list containing kValue, k, and O.
                callback.call(T, kValue, k, O);
            }
            // d. Increase k by 1.
            k++;
        }
// 8. return undefined.
    };
}
if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = function (callback, thisArg) {
        thisArg = thisArg || window;
        for (var i = 0; i < this.length; i++) {
            callback.call(thisArg, this[i], i, this);
        }
    };
}
(function () {

    if (typeof window.CustomEvent === "function")
        return false;

    function CustomEvent(event, params) {
        params = params || {bubbles: false, cancelable: false, detail: undefined};
        var evt = document.createEvent('CustomEvent');
        evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
        return evt;
    }

    CustomEvent.prototype = window.Event.prototype;

    window.CustomEvent = CustomEvent;
})();