(function (doc, win, udef) {

	var
		firstEl = function (el) {
			return doc[el] || doc.getElementsByTagName(el)[0];
		},
		maybeCall = function(thing, ctx, args) {
			return typeof thing == 'function' ? thing.apply(ctx, args) : thing;
		},
		transitionEndEventName = null,

		stylesAreInjected = false,
		injectStyleSheet = function() {
			if (!stylesAreInjected) {

				stylesAreInjected = true;

				var stylesText =
					'.twipsy { display: block; position: absolute; visibility: visible; padding: 5px; font-size: 12px; z-index: 11000;}\
					.twipsy.above .twipsy-arrow { bottom: 0; left: 50%; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 5px solid #000000;}\
					.twipsy.above-left .twipsy-arrow { bottom: 0; left: 18px; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 5px solid #000000;}\
					.twipsy.above-right .twipsy-arrow { bottom: 0; right: 18px; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 5px solid #000000;}\
					.twipsy.left .twipsy-arrow { top: 50%; right: 0; margin-top: -5px; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-left: 5px solid #000000;}\
					.twipsy.below .twipsy-arrow { top: 0; left: 50%; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-bottom: 5px solid #000000;}\
					.twipsy.below-left .twipsy-arrow { top: 0; left: 18px; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-bottom: 5px solid #000000;}\
					.twipsy.below-right .twipsy-arrow { top: 0; right: 18px; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-bottom: 5px solid #000000;}\
					.twipsy.right .twipsy-arrow { top: 50%; left: 0; margin-top: -5px; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-right: 5px solid #000000;}\
					.twipsy-inner { padding: 3px 8px; background-color: #000000; color: white; text-align: center; max-width: 200px; text-decoration: none; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px;}\
					.twipsy-arrow { position: absolute; width: 0; height: 0;}',
					stylesContainer = new Element("style", {"type":"text/css"}).inject(firstEl("head"), "bottom");

				stylesContainer.styleSheet
					? stylesContainer.styleSheet.cssText = stylesText
					: stylesContainer.innerHTML = stylesText;
			}
		};

	// Determine browser support for CSS transitions
	if (typeOf(Browser.Features.transition) != "boolean") {
		Browser.Features.transition = (function () {
			var styles = (doc.body || doc.documentElement).style;

			if (styles.transition !== udef || styles.MsTransition !== udef) {
				transitionEndEventName = "TransitionEnd";
			}
			else if (styles.WebkitTransition !== udef) {
				transitionEndEventName = "webkitTransitionEnd";
			}
			else if (styles.MozTransition !== udef) {
				transitionEndEventName = "transitionend";
			}
			else if (styles.OTransition !== udef) {
				transitionEndEventName = "oTransitionEnd";
			}

			return transitionEndEventName != null;
		})();
	}



	var Twipsy = new Class({

		/**
		* Construct the twipsy
		*
		* @param element Element
		* @param options object
		*/
		initialize:function (element, options) {
			this.options = Object.merge({}, Twipsy.defaults, options);
			this.element = doc.id(element);
			this.enabled = true;
			if (options.injectStyles) {
				injectStyleSheet();
			}
			this.fixTitle();
		},

		/**
		* Display the twipsy
		*
		* @return Twipsy
		*/
		show: function() {
			var pos, actualWidth, actualHeight, placement, twipsyElement, position,
				offset, size, twipsySize, leftPosition;
			if (this.hasContent() && this.enabled) {
				twipsyElement = this.setContent().getTip();

				if (this.options.animate) {
					moofx(twipsyElement).animate({'opacity': 0.8}, {
						duration: '150ms',
						equation: 'ease-in',
						callback: function(){
							this.isShown = true;
						}.bind(this)
					});//.addClass('twipsy-fade');
				}

				twipsyElement
					.setStyles({top: 0, left: 0, display: 'block'})
					.inject(document.body, 'top');

				offset = this.element.getPosition();
				size   = this.element.getSize();
				pos    = {
					left:   offset.x,
					top:    offset.y,
					width:  size.x,
					height: size.y
				};

				twipsySize = twipsyElement.getSize();
				actualWidth = twipsySize.x;
				actualHeight = twipsySize.y;

				placement = maybeCall(this.options.placement, this, [twipsyElement, this.element]);
				leftPosition = pos.left - actualWidth - this.options.offset;

				if (leftPosition < 0 && placement == 'left') placement = 'right';

				var offsetOpt = {
					x: this.options.offset.x || this.options.offset,
					y: this.options.offset.y || this.options.offset
				}

				switch (placement) {
					case 'below':
						position = {top: pos.top + pos.height + this.options.offset.y, left: pos.left + pos.width / 2 - actualWidth / 2};
						break;

					case 'below-left':
						position = {top: pos.top + pos.height + this.options.offset.y, left: pos.left - this.options.offset.x};
						break;

					case 'below-right':
						position = {top: pos.top + pos.height + this.options.offset.y, left: pos.left + pos.width - actualWidth + this.options.offset.x};
						break;

					case 'above':
						position = {top: pos.top - actualHeight - this.options.offset.y, left: pos.left + pos.width / 2 - actualWidth / 2};
						break;

					case 'above-left':
						position = {top: pos.top - actualHeight - this.options.offset.y, left: pos.left - this.options.offset.x};
						break;

					case 'above-right':
						position = {top: pos.top - actualHeight - this.options.offset, left: pos.left + pos.width - actualWidth + this.options.offset};
						break;

					case 'left':
						position = {top: pos.top + pos.height / 2 - actualHeight / 2, left: leftPosition};
						break;

					case 'right':
						position = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width + this.options.offset};
						break;
				}

				twipsyElement
					.setStyles(position)
					.addClass(placement);
			}
			return this;
		},

		/**
		* Remove the twipsy from screen
		*
		* @return Twipsy
		*/
		hide: function() {
			var twipsyElement = this.getTip(),
				removeTwipsy = function(){
					this.isShown = false;
					twipsyElement.dispose();
				}.bind(this);

			if (!this.hasContent()){
				removeTwipsy();
				return this;
			}

			moofx(twipsyElement).animate({'opacity': 0}, {
				duration: '150ms',
				equation: 'ease-in',
				callback: removeTwipsy
			});

			return this;
		},

		/**
		* Set the readable content of the twipsy
		*
		* @return Twipsy
		*/
		setContent: function () {
			this.getTip().getElement('.twipsy-inner').set(this.options.html ? 'html' : 'text', this.getTitle());
			return this;
		},

		/**
		* Test if we have a content to put in the twipsy
		*
		@return boolean
		*/
		hasContent: function() {
			return this.getTitle().replace(/\s+/g, "") !== "";
		},

		/**
		* Get the title string
		*
		* @return String
		*/
		getTitle: function() {
			var title,
				e = this.element,
				o = this.options;

			this.fixTitle();

			if (typeof o.title == 'string') {
				title = e.getProperty(o.title == 'title' ? 'data-original-title' : o.title);
			}
			else if (typeof o.title == 'function') {
				title = o.title.call(e);
			}

			title = ('' + title).clean();
			return title || o.fallback;
		},

		/**
		* Get the twipsy HTML Element, construct it if not yet available
		*
		* @return Element
		*/
		getTip: function() {
			if (!this.tip) {
				this.tip = new Element("div.twipsy", {html: this.options.template});
			}
			return this.tip;
		},

		/**
		* Check if the given element is on screen
		*
		* @return boolean
		*/
		validate:function () {
			if (!this.element.parentNode) {
				this.hide();
				this.element = null;
				this.options = null;
				return false;
			}
			return true;
		},

		/**
		* Set enabled status to true
		*
		* @return Twipsy
		*/
		enable: function() {
			this.enabled = true;
			return this;
		},

		/**
		* Set enabled status to false
		*
		* @return twipsy
		*/
		disable: function() {
			this.enabled = false;
			return this;
		},

		/**
		* Toggle the enabled status
		*
		* @return Twipsy
		*/
		toggleEnabled: function() {
			this.enabled = !this.enabled;
			return this;
		},

		/**
		* Toggle the twipsy
		*
		* @return Twipsy
		*/
		toggle: function() {
			this[this.getTip().hasClass('in') ? 'hide' : 'show']();
			return this;
		},

		/**
		* Fix the title attribute of the trigger element, if not done yet
		*
		* @return Twipsy
		*/
		fixTitle:function () {
			var el = this.element;
			if (el.getProperty("title") || !el.getProperty("data-original-title")) {
				el.setProperty('data-original-title', el.getProperty("title") || '').removeProperty('title');
			}
			return this;
		}
	});

	Twipsy.defaults = {
		placement:    "above",
		animate:      true,
		delayIn:      0,
		delayOut:     0,
		html:         false,
		live:         false,
		offset:       0,
		title:        'title',
		trigger:      'hover',
		injectStyles: true,
		fallback:     "",
		template:     '<div class="twipsy-inner"></div><div class="twipsy-arrow"></div>'
	};

	Twipsy.rejectAttrOptions = ['title'];

	Twipsy.elementOptions = function (el, options) {
		var data = {},
			rejects = Twipsy.rejectAttrOptions,
			i = rejects.length;

		[
			"placement", "animate", "delay-in", "delay-out", "html",
			"offset", "title", "trigger", "template", "inject-styles"
		].each(function(item) {
			var res = null,lower;
			if (el.dataset) {
				res = el.dataset[item.camelCase()];
			}
			else {
				res = el.getProperty("data-" + item);
			}
			if (res) {
				lower = res.toLowerCase().clean();
				if (lower === "true") res = true;
				else if (lower === "false") res = false;
				else if (/^[0-9]+$/.test(lower)) lower = parseInt(lower, 10);
				data[item.camelCase()] = res;
			}
		});

		while (i--) {
			delete data[rejects[i]];
		}

		return Object.merge({}, options, data);
	};

	Element.implement({
		twipsy:function (options) {
			var twipsy, binder, eventIn, eventOut, name = 'twipsy';

			if (options === true) {
				return this.retrieve(name);
			}
			else if (typeof options == 'string') {
				twipsy = this.retrieve(name);
				if (twipsy) {
					twipsy[options]();
				}
				return this;
			}

			options = Object.merge({}, Twipsy.defaults, options);

			function get(ele) {
				var twipsy = ele.retrieve(name);

				if (!twipsy) {
					twipsy = new Twipsy(ele, Twipsy.elementOptions(ele, options));
					ele.store(name, twipsy);
				}

				return twipsy;
			}

			function enter() {
				var twipsy = get(this);
				twipsy.hoverState = 'in';

				if (options.delayIn == 0) {
					twipsy.show();
				} else {
					twipsy.fixTitle();
					setTimeout(function () {
						if (twipsy.hoverState == 'in') {
							twipsy.show();
						}
					}, options.delayIn);
				}
			}

			function leave() {
				var twipsy = get(this);
				twipsy.hoverState = 'out';
				if (options.delayOut == 0) {
					twipsy.hide();
				} else {
					setTimeout(function () {
						if (twipsy.hoverState == 'out') {
							twipsy.hide();
						}
					}, options.delayOut);
				}
			}

			if (options.trigger != 'manual') {
				eventIn = options.trigger == 'hover' ? 'mouseenter' : 'focus';
				eventOut = options.trigger == 'hover' ? 'mouseleave' : 'blur';
				get(this);

				document.id(this).addEvent(eventIn, enter).addEvent(eventOut, leave);
			}
			return this;
		}
	});

	Elements.implement({
		twipsy:function (options) {
			this.each(function(el) {
				el.twipsy(options);
			});
			return this;
		}
	});

	win.Twipsy = Twipsy;

})(document, self, undefined);
