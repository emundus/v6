/**
 * @package    HikaShop for Joomla!
 * @version    3.0.1
 * @author     hikashop.com
 * @copyright  (C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
!function ($) {
	"use strict"; // jshint ;_;

	/* DROPDOWN CLASS DEFINITION
	 * ========================= */
	var toggle = '[data-toggle=hkdropdown]',
		HKDropdown = function (element) {
			var $el = $(element).on('click.dropdown.data-api', this.toggle);
			$('html').on('click.dropdown.data-api', function () {
				$el.parent().removeClass('open');
			});
		};

	HKDropdown.prototype = {
		constructor: HKDropdown,
		toggle: function (e) {
			var $this = $(this), $parent, isActive;

			if ($this.is('.disabled, :disabled')) return;
			$parent = getParent($this);
			isActive = $parent.hasClass('open');
			clearMenus();
			if (!isActive) {
				if ('ontouchstart' in document.documentElement) {
					// if mobile we we use a backdrop because click events don't delegate
					$('<div class="hk-dropdown-backdrop"/>').insertBefore($(this)).on('click', clearMenus);
				}
				$parent.toggleClass('open');
			}
			$this.focus();
			return false;
		},
		keydown: function (e) {
			var $this, $items, $active, $parent, isActive, index;

			if (!/(38|40|27)/.test(e.keyCode)) return;

			$this = $(this);

			e.preventDefault();
			e.stopPropagation();

			if ($this.is('.disabled, :disabled')) return;

			$parent = getParent($this);
			isActive = $parent.hasClass('open');

			if (!isActive || (isActive && e.keyCode == 27)) {
				if (e.which == 27) $parent.find(toggle).focus();
				return $this.click();
			}

			$items = $('[role=menu] li:not(.divider):visible a', $parent);

			if (!$items.length) return;

			index = $items.index($items.filter(':focus'));

			if (e.keyCode == 38 && index > 0) index--; // up
			if (e.keyCode == 40 && index < $items.length - 1) index++; // down
			if (!~index) index = 0;

			$items
				.eq(index)
				.focus();
		}
	};

	function clearMenus() {
		$('.hk-dropdown-backdrop').remove();
		$(toggle).each(function () {
			getParent($(this)).removeClass('open');
		});
	}

	function getParent($this) {
		var selector = $this.attr('data-target'), $parent;

		if (!selector) {
			selector = $this.attr('href');
			selector = selector && /#/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, ''); //strip for ie7;
		}
		$parent = selector && $(selector);
		if (!$parent || !$parent.length) $parent = $this.parent();
		return $parent;
	}


	/* DROPDOWN PLUGIN DEFINITION
	 * ========================== */
	var old = $.fn.hkdropdown;
	$.fn.hkdropdown = function (option) {
		return this.each(function () {
			var $this = $(this)
				, data = $this.data('hkdropdown');
			if (!data) $this.data('hkdropdown', (data = new HKDropdown(this)));
			if (typeof option == 'string') data[option].call($this);
		});
	};

	$.fn.hkdropdown.Constructor = HKDropdown;

	/* DROPDOWN NO CONFLICT
	 * ==================== */
	$.fn.hkdropdown.noConflict = function () {
		$.fn.hkdropdown = old;
		return this;
	};

	/* APPLY TO STANDARD DROPDOWN ELEMENTS
	 * =================================== */
	$(document)
		.on('click.hkdropdown.data-api', clearMenus)
		.on('click.hkdropdown.data-api', '.hkdropdown form', function (e) { e.stopPropagation(); })
		.on('click.hkdropdown.data-api', toggle, HKDropdown.prototype.toggle)
		.on('keydown.hkdropdown.data-api', toggle + ', [role=menu]' , HKDropdown.prototype.keydown);
}(window.jQuery);
