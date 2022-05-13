/**
 * @package         Sourcerer
 * @version         9.2.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var RegularLabsSourcererPopup = null;

(function($) {
	var $editor = null;
	var $form   = null;

	RegularLabsSourcererPopup = {
		init: function() {
			$editor = Joomla.editors.instances['code'];
			$form   = document.getElementById('sourcererForm');

			try {
				var test = $editor.getValue();
			} catch (err) {
				setTimeout('RegularLabsSourcererPopup.init();', 100);
				return;
			}

			var string = '';

			var editor_textarea = window.parent.document.getElementById(sourcerer_editorname);

			if (editor_textarea) {
				var iframes = editor_textarea.parentNode.getElementsByTagName('iframe');

				if ( ! iframes.length) {
					$('.reglab-overlay').css('cursor', '').fadeOut();
					return;
				}

				var editor_frame  = iframes[0];
				var contentWindow = editor_frame.contentWindow;
				var selection     = '';

				if (typeof contentWindow.getSelection !== 'undefined') {
					var sel = contentWindow.getSelection();
					if (sel.rangeCount) {
						var container = contentWindow.document.createElement("div");
						for (var i = 0, len = sel.rangeCount; i < len; ++i) {
							container.appendChild(sel.getRangeAt(i).cloneContents());
						}
						selection = container.innerHTML;
					}
				} else if (typeof contentWindow.document.selection !== 'undefined') {
					if (contentWindow.document.selection.type == "Text") {
						selection = contentWindow.document.selection.createRange().htmlText;
					}
				}

				string = this.cleanRange(selection);
			}

			if ( ! string) {
				$('.reglab-overlay').css('cursor', '').fadeOut();
				return;
			}

			// Handle indentation
			string = string.replace(/^\t/gm, '    ');

			this.setAttributes(string);
			var code = this.removeSourceTags(string);

			$editor.setValue(code);

			$('.reglab-overlay').css('cursor', '').fadeOut();
		},

		insertText: function() {
			var t_word  = sourcerer_syntax_word;
			var t_start = sourcerer_tag_characters[0];
			var t_end   = sourcerer_tag_characters[1];

			var code = $editor.getValue();
			code     = this.removeSourceTags(code);

			var pre_php = [];
			var codes   = [];


			if (code) {
				codes.push(code);
			}

			string = codes.join('\n');


			// convert to html entities
			string = this.htmlentities(string, 'ENT_NOQUOTES');

			// replace indentation with tab images
			string = string.indent2Images();

			// replace linebreaks with br tags
			string = string.nl2br();

			var attributes = [];

			if ($form['raw'].value == '1') {
				attributes.push('raw="true"');
			}

			if ($form['trim'].value == '1') {
				attributes.push('trim="true"');
			}


			if (string) {
				string = '<span style="font-family: courier new, courier, monospace;">'
					+ string
					+ '</span>';
			}

			string = t_start + (t_word + ' ' + attributes.join(' ')).trim() + t_end
				+ string
				+ t_start + '/' + t_word + t_end;

			window.parent.jInsertEditorText(string, sourcerer_editorname);

			return true;
		},

		setAttributes: function(string) {
			var t_word  = sourcerer_syntax_word;
			var t_start = sourcerer_tag_characters[0];
			var t_end   = sourcerer_tag_characters[1];

			var start_tag = this.preg_quote(t_start + t_word) + '( .*?)' + this.preg_quote(t_end);
			var regex     = new RegExp(start_tag, 'gim');

			if ( ! string.match(regex)) {
				return;
			}

			var attributes = this.getAttributes(regex.exec(string)[1].trim());

			if ('raw' in attributes) {
				this.setRadioOption('raw', attributes.raw.toString().toBoolean());
			}
			if ('trim' in attributes) {
				this.setRadioOption('trim', attributes.trim.toString().toBoolean());
			}
		},

		setPhpField: function(value, method) {
			this.setField('php_file', value);
			this.setSelectOption('php_include_method', method);
		},

		getAttributes: function(string) {
			var attributes = {};

			var t_word  = sourcerer_syntax_word;
			var t_start = sourcerer_tag_characters[0];
			var t_end   = sourcerer_tag_characters[1];

			var regex = new RegExp('^0 ?');
			if (string.match(regex)) {
				attributes.raw = true;
				string         = string.replace(/^0/, '').trim();
			}

			var start_tag = this.preg_quote(t_start + t_word) + '( .*?)' + this.preg_quote(t_end);
			var regex     = new RegExp('([a-z_-]+)="([^"]*)"', 'gim');

			if ( ! string.match(regex)) {
				return attributes;
			}

			while (match = regex.exec(string)) {
				attributes[match[1]] = match[2];
			}

			return attributes;
		},

		removeSourceTags: function(string) {
			var t_word  = sourcerer_syntax_word;
			var t_start = sourcerer_tag_characters[0];
			var t_end   = sourcerer_tag_characters[1];

			var start_tag = this.preg_quote(t_start + t_word) + '.*?' + this.preg_quote(t_end);
			var end_tag   = this.preg_quote(t_start + '/' + t_word + t_end);

			var regex = new RegExp('(' + start_tag + ')\\s*', 'gim');
			start_tag = t_start + t_word + t_end;
			if (string.match(regex)) {
				start_tag = regex.exec(string)[1];
				string    = string.replace(regex, '');
			}

			regex   = new RegExp('\\s*' + end_tag, 'gim');
			end_tag = t_start + '/' + t_word + t_end;
			string  = string.replace(regex, '');

			return string.trim();
		},

		cleanRange: function(string) {
			var regex = new RegExp('[\n\r]', 'gim');
			string    = string.replace(regex, '');
			regex     = new RegExp('(</p><p>|<p>|</p>|<br>|<br>)', 'gim');
			string    = string.replace(regex, '\n');
			string    = string.replace(/^\s+/, '').replace(/\s+$/, '');
			regex     = new RegExp('<img[^>]*src="[^"]*/tab.png"[^>]*>', 'gim');
			string    = string.replace(regex, '\t');
			regex     = new RegExp('</?[^>]*>', 'gim');
			string    = string.replace(regex, '');
			regex     = new RegExp('(&nbsp;|&#160;)', 'gim');
			string    = string.replace(regex, ' ');
			regex     = new RegExp('&lt;', 'gim');
			string    = string.replace(regex, '<');
			regex     = new RegExp('&gt;', 'gim');
			string    = string.replace(regex, '>');
			regex     = new RegExp('&amp;', 'gim');
			string    = string.replace(regex, '&');
			return string;
		},

		htmlentities: function(string, quote_style) {
			tmp_str = string.toString();

			if (false === (histogram = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
				return false;
			}

			for (symbol in histogram) {
				entity  = histogram[symbol];
				tmp_str = tmp_str.split(symbol).join(entity);
			}

			return tmp_str;
		},

		get_html_translation_table: function(table, quote_style) {
			var entities          = {}, histogram = {}, decimal = 0, symbol = '';
			var constMappingTable = {}, constMappingQuoteStyle = {};
			var useTable          = {}, useQuoteStyle = {};

			// Translate arguments
			constMappingTable[0]      = 'HTML_SPECIALCHARS';
			constMappingTable[1]      = 'HTML_ENTITIES';
			constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
			constMappingQuoteStyle[2] = 'ENT_COMPAT';
			constMappingQuoteStyle[3] = 'ENT_QUOTES';

			useTable      = ! isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
			useQuoteStyle = ! isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';

			if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
				throw Error('Table: ' + useTable + ' not supported');
				// return false;
			}

			// ascii decimals for better compatibility
			entities['38'] = '&amp;';
			if (useQuoteStyle !== 'ENT_NOQUOTES') {
				entities['34'] = '&quot;';
			}
			if (useQuoteStyle === 'ENT_QUOTES') {
				entities['39'] = '&#039;';
			}
			entities['60'] = '&lt;';
			entities['62'] = '&gt;';

			if (useTable === 'HTML_ENTITIES') {
				entities['160'] = '&nbsp;';
				entities['161'] = '&iexcl;';
				entities['162'] = '&cent;';
				entities['163'] = '&pound;';
				entities['164'] = '&curren;';
				entities['165'] = '&yen;';
				entities['166'] = '&brvbar;';
				entities['167'] = '&sect;';
				entities['168'] = '&uml;';
				entities['169'] = '&copy;';
				entities['170'] = '&ordf;';
				entities['171'] = '&laquo;';
				entities['172'] = '&not;';
				entities['173'] = '&shy;';
				entities['174'] = '&reg;';
				entities['175'] = '&macr;';
				entities['176'] = '&deg;';
				entities['177'] = '&plusmn;';
				entities['178'] = '&sup2;';
				entities['179'] = '&sup3;';
				entities['180'] = '&acute;';
				entities['181'] = '&micro;';
				entities['182'] = '&para;';
				entities['183'] = '&middot;';
				entities['184'] = '&cedil;';
				entities['185'] = '&sup1;';
				entities['186'] = '&ordm;';
				entities['187'] = '&raquo;';
				entities['188'] = '&frac14;';
				entities['189'] = '&frac12;';
				entities['190'] = '&frac34;';
				entities['191'] = '&iquest;';
				entities['192'] = '&Agrave;';
				entities['193'] = '&Aacute;';
				entities['194'] = '&Acirc;';
				entities['195'] = '&Atilde;';
				entities['196'] = '&Auml;';
				entities['197'] = '&Aring;';
				entities['198'] = '&AElig;';
				entities['199'] = '&Ccedil;';
				entities['200'] = '&Egrave;';
				entities['201'] = '&Eacute;';
				entities['202'] = '&Ecirc;';
				entities['203'] = '&Euml;';
				entities['204'] = '&Igrave;';
				entities['205'] = '&Iacute;';
				entities['206'] = '&Icirc;';
				entities['207'] = '&Iuml;';
				entities['208'] = '&ETH;';
				entities['209'] = '&Ntilde;';
				entities['210'] = '&Ograve;';
				entities['211'] = '&Oacute;';
				entities['212'] = '&Ocirc;';
				entities['213'] = '&Otilde;';
				entities['214'] = '&Ouml;';
				entities['215'] = '&times;';
				entities['216'] = '&Oslash;';
				entities['217'] = '&Ugrave;';
				entities['218'] = '&Uacute;';
				entities['219'] = '&Ucirc;';
				entities['220'] = '&Uuml;';
				entities['221'] = '&Yacute;';
				entities['222'] = '&THORN;';
				entities['223'] = '&szlig;';
				entities['224'] = '&agrave;';
				entities['225'] = '&aacute;';
				entities['226'] = '&acirc;';
				entities['227'] = '&atilde;';
				entities['228'] = '&auml;';
				entities['229'] = '&aring;';
				entities['230'] = '&aelig;';
				entities['231'] = '&ccedil;';
				entities['232'] = '&egrave;';
				entities['233'] = '&eacute;';
				entities['234'] = '&ecirc;';
				entities['235'] = '&euml;';
				entities['236'] = '&igrave;';
				entities['237'] = '&iacute;';
				entities['238'] = '&icirc;';
				entities['239'] = '&iuml;';
				entities['240'] = '&eth;';
				entities['241'] = '&ntilde;';
				entities['242'] = '&ograve;';
				entities['243'] = '&oacute;';
				entities['244'] = '&ocirc;';
				entities['245'] = '&otilde;';
				entities['246'] = '&ouml;';
				entities['247'] = '&divide;';
				entities['248'] = '&oslash;';
				entities['249'] = '&ugrave;';
				entities['250'] = '&uacute;';
				entities['251'] = '&ucirc;';
				entities['252'] = '&uuml;';
				entities['253'] = '&yacute;';
				entities['254'] = '&thorn;';
				entities['255'] = '&yuml;';
			}

			// ascii decimals to real symbols
			for (decimal in entities) {
				symbol            = String.fromCharCode(decimal);
				histogram[symbol] = entities[decimal];
			}

			return histogram;
		},

		preg_quote: function(str) {
			return (str + '').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!<>\|\:])/g, '\\$1');
		},

		setField: function(name, value) {
			$('input[name="' + name + '"]').val(value);
		},

		setRadioOption: function(name, value) {
			var inputs = $('input[name="' + name + '"]');
			var input  = $('input[name="' + name + '"][value="' + value + '"]');

			$('label[for="' + input.attr('id') + '"]').click();
			inputs.attr('checked', false);
			input.attr('checked', true).click();
		},

		setSelectOption: function(name, value) {
			var self = this;

			var select = $('select[name="' + name + '"]');
			var option = $('select[name="' + name + '"] option[value="' + value + '"]');

			if ( ! option.length) {
				return;
			}

			select.find('option').attr('selected', false);
			option.attr('selected', 'selected');
			select.trigger('liszt:updated');
		}
	};

	String.prototype.ltrim         = function() {
		return this.fixLineBreaks().replace(/^[\n ]*/, "");
	};
	String.prototype.rtrim         = function() {
		return this.fixLineBreaks().replace(/[\n ]*$/, "");
	};
	String.prototype.trim          = function() {
		return this.fixLineBreaks().ltrim().rtrim();
	};
	String.prototype.toBoolean     = function() {
		return this == 1 || this == '1' || this == 'true' ? 1 : 0;
	};
	String.prototype.fixLineBreaks = function() {
		return this.replace(/\r/, "");
	};
	String.prototype.escapeQuotes  = function() {
		return this.replace(/'/g, '\\\'');
	};
	String.prototype.hasLineBreaks = function() {
		var regex = new RegExp('\n', 'gm');
		return regex.test(this);
	};
	String.prototype.indent        = function() {
		var regex = new RegExp('\n', 'gm');

		return '\n    ' + this.replace(regex, '\n    ') + '\n';
	};
	String.prototype.nl2br         = function() {
		var regex = new RegExp('\n', 'gm');

		return this.replace(regex, '<br>');
	};

	String.prototype.indent2Images = function() {
		var string = this;
		var regex  = new RegExp('((^|\n)(    |\t)*)(   ? ?|\t)', 'gm');

		while (regex.test(string)) {
			string = string.replace(regex, '$1<img src="' + sourcerer_root + '/media/sourcerer/images/tab.png">');
		}

		return string.replace(regex, '<br>');
	};

})(jQuery);
