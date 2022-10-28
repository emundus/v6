/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

"use strict";

if (typeof window.RegularLabsMultiSelect === 'undefined'
	|| typeof RegulaRegularLabsMultiSelectrLabsForm.version === 'undefined'
	|| RegularLabsMultiSelect.version < '22.4.18687') {

	(function($) {
		$(document).ready(function() {
			$('.rl_multiselect').each(function() {
				RegularLabsMultiSelect.init($(this));
			});
		});

		window.RegularLabsMultiSelect = {
			version: '22.4.18687',

			init: function(element) {
				const self      = this;
				const controls  = element.find('div.rl_multiselect-controls');
				const list      = element.find('ul.rl_multiselect-ul');
				const menu      = element.find('div.rl_multiselect-menu-block').html();
				const maxHeight = list.css('max-height');

				list.find('li').each(function() {
					const $li  = $(this);
					const $div = $li.find('div.rl_multiselect-item:first');

					// Add icons
					$li.prepend('<span class="pull-left icon-"></span>');

					// Append clearfix
					$div.after('<div class="clearfix"></div>');

					if ( ! $li.find('ul.rl_multiselect-sub').length) {
						return;
					}

					// Add classes to Expand/Collapse icons
					$li.find('span.icon-').addClass('rl_multiselect-toggle icon-minus');

					// Append drop down menu in nodes
					$div.find('label:first').after(menu);

					if ( ! $li.find('ul.rl_multiselect-sub ul.rl_multiselect-sub').length) {
						$li.find('div.rl_multiselect-menu-expand').remove();
					}
				});

				// Takes care of the Expand/Collapse of a node
				list.find('span.rl_multiselect-toggle').click(function() {
					const $icon = $(this);

					// Take care of parent UL
					if ($icon.parent().find('ul.rl_multiselect-sub').is(':visible')) {
						$icon.removeClass('icon-minus').addClass('icon-plus');
						$icon.parent().find('ul.rl_multiselect-sub').hide();
						$icon.parent().find('ul.rl_multiselect-sub span.rl_multiselect-toggle').removeClass('icon-minus').addClass('icon-plus');

						return;
					}

					$icon.removeClass('icon-plus').addClass('icon-minus');
					$icon.parent().find('ul.rl_multiselect-sub').show();
					$icon.parent().find('ul.rl_multiselect-sub span.rl_multiselect-toggle').removeClass('icon-plus').addClass('icon-minus');

				});

				// Takes care of the filtering
				controls.find('input.rl_multiselect-filter').keyup(function() {
					const $text = $(this).val().toLowerCase();

					list.find('li').each(function() {
						const $li = $(this);

						if ($li.text().toLowerCase().indexOf($text) < 0) {
							$li.hide();
							return;
						}

						$li.show();
					});
				});

				// Checks all checkboxes in the list
				list.find('input').on('change', function() {
					self.updateCounts(this.closest('.rl_multiselect'));
				});

				// Checks all checkboxes in the list
				controls.find('a.rl_multiselect-checkall').click(function() {
					list.find('input').prop('checked', true);
					self.updateCounts(this.closest('.rl_multiselect'));
				});

				// Unchecks all checkboxes in the list
				controls.find('a.rl_multiselect-uncheckall').click(function() {
					list.find('input').prop('checked', false);
					self.updateCounts(this.closest('.rl_multiselect'));
				});

				// Toggles all checkboxes in the list
				controls.find('a.rl_multiselect-toggleall').click(function() {
					list.find('input').each(function() {
						const $input = $(this);

						$input.prop('checked', ! $input.prop('checked'));
					});
					self.updateCounts(this.closest('.rl_multiselect'));
				});

				// Expands all sub-items in the list
				controls.find('a.rl_multiselect-expandall').click(function() {
					list.find('ul.rl_multiselect-sub').show();
					list.find('span.rl_multiselect-toggle').removeClass('icon-plus').addClass('icon-minus');
				});

				// Hides all sub-items in the list
				controls.find('a.rl_multiselect-collapseall').click(function() {
					list.find('ul.rl_multiselect-sub').hide();
					list.find('span.rl_multiselect-toggle').removeClass('icon-minus').addClass('icon-plus');
				});

				// Shows all selected items in the list
				controls.find('a.rl_multiselect-showall').click(function() {
					list.find('li').show();
				});

				// Shows all selected items in the list
				controls.find('a.rl_multiselect-showselected').click(function() {
					list.find('li').each(function() {
						const $li = $(this);
						let $hide = true;

						$li.find('input').each(function() {
							if ($(this).prop('checked')) {
								$hide = false;
								return false;
							}
						});

						if ($hide) {
							$li.hide();
							return;
						}

						$li.show();
					});
				});

				// Maximizes the list
				controls.find('a.rl_multiselect-maximize').click(function() {
					list.css('max-height', '');
					controls.find('a.rl_multiselect-maximize').hide();
					controls.find('a.rl_multiselect-minimize').show();
				});

				// Minimizes the list
				controls.find('a.rl_multiselect-minimize').click(function() {
					list.css('max-height', maxHeight);
					controls.find('a.rl_multiselect-minimize').hide();
					controls.find('a.rl_multiselect-maximize').show();
				});

				// Take care of children check/uncheck all
				element.find('a.checkall').click(function() {
					const $parent = $(this).parent().parent().parent().parent().parent().parent();

					$parent.find('ul.rl_multiselect-sub input').prop('checked', true);
				});

				element.find('a.uncheckall').click(function() {
					const $parent = $(this).parent().parent().parent().parent().parent().parent();

					$parent.find('ul.rl_multiselect-sub input').prop('checked', false);
				});

				// Take care of children toggle all
				element.find('a.expandall').click(function() {
					const $parent = $(this).parent().parent().parent().parent().parent().parent().parent();

					$parent.find('ul.rl_multiselect-sub').show();
					$parent.find('ul.rl_multiselect-sub span.rl_multiselect-toggle').removeClass('icon-plus').addClass('icon-minus');
				});

				element.find('a.collapseall').click(function() {
					const $parent = $(this).parent().parent().parent().parent().parent().parent().parent();

					$parent.find('li ul.rl_multiselect-sub').hide();
					$parent.find('li span.rl_multiselect-toggle').removeClass('icon-minus').addClass('icon-plus');
				});

				element.find('div.rl_multiselect-item.hidechildren').click(function() {
					const $parent = $(this).parent();

					$(this).find('input').each(function() {
						const $sub   = $parent.find('ul.rl_multiselect-sub').first();
						const $input = $(this);

						if ($input.prop('checked')) {
							$parent.find('span.rl_multiselect-toggle, div.rl_multiselect-menu').css('visibility', 'hidden');
							if ( ! $sub.parent().hasClass('hidelist')) {
								$sub.wrap('<div style="display:none;" class="hidelist"></div>');
							}

							return;
						}

						$parent.find('span.rl_multiselect-toggle, div.rl_multiselect-menu').css('visibility', 'visible');
						if ($sub.parent().hasClass('hidelist')) {
							$sub.unwrap();
						}
					});
				});
			},

			updateCounts: function(container) {
				$(container).find('.rl_multiselect-count-selected').html(
					$(container).find('ul.rl_multiselect-ul input:checked').length
				);
			}
		};
	})(jQuery);
}
