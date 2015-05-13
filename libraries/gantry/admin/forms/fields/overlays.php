<?php
/**
 * @package        gantry
 * @subpackage     admin.elements
 * @version        3.0.9 August 17, 2010
 * @author         RocketTheme http://www.rockettheme.com
 * @copyright      Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('GANTRY_VERSION') or die();
/**
 * @package     gantry
 * @subpackage  admin.elements
 */
gantry_import('core.config.gantryformfield');

class GantryFormFieldOverlays extends GantryFormField
{

	protected $type = 'overlays';
	protected $basetype = 'none';

	public function getInput()
	{
		/** @global $gantry Gantry */
		global $gantry;
		$output = '';

		$split_template_path = explode('/', $gantry->templatePath);
		$this->template = end($split_template_path);

		$class        = $this->element['class'] ? $this->element['class'] : '';
		$preview      = $this->element['preview'] ? $this->element['preview'] : "false";
		$path         = ($this->element['path']) ? $this->element['path'] : false;
		$node         = $this->element;
		$control_name = $this->name;

		$name = substr(str_replace($gantry->templateName . '-template-options', "", $this->element['name']), 1, -1);
		$name = str_replace("][", "-", $name);

		$name = $this->id;

		if (!$path) return "No path set in templateDetails.xml";

		if ($preview == 'true') $class .= " overlay-slider";

		if (!defined('GANTRY_CSS')) {
			$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/gantry.css');
			define('GANTRY_CSS', 1);
		}
		if (!defined('GANTRY_SLIDER')) {
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/slider/js/slider.js');
			if (!defined('GANTRY_SLIDER')) define('GANTRY_SLIDER', 1);
		}
		if (!defined('GANTRY_OVERLAYS')) {
			$gantry->addInlineScript('var GantryOverlays = {};');
			$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/overlays/css/overlays.css');
			define('GANTRY_OVERLAYS', 1);
		}

//        $this->value = $value;

		$rootPath = str_replace("__TEMPLATE__", $gantry->templatePath, $path);
		$urlPath  = str_replace("__TEMPLATE__", $gantry->templateUrl, $path);

		$this->_loadOverlays($name, $rootPath);

		$overlays = array();

		$__overlays = $gantry->retrieveTemp('overlays', 'overlays', array());
		$__paths    = $gantry->retrieveTemp('overlays', 'paths', array());

		$overlays[$name] = "'none': {'file': 'overlay-off.png', 'value': 'none', 'name': 'Off', 'path': '" . $gantry->gantryUrl . "/admin/widgets/overlays/images/overlay-off.png'}, ";
		foreach ($__overlays[$name] as $title => $file) {
			$overlays[$name] .= "'" . $file['name'] . "': {'file': '" . $file['file'] . "', 'value': '" . $file['name'] . "', 'name': '" . $title . "', 'path': '" . $urlPath . $file['file'] . "'}, ";
		}

		$overlays[$name] = substr($overlays[$name], 0, -2);

		$gantry->addInlineScript('GantryOverlays["' . $this->id . '"] = new Hash({' . $overlays[$name] . '});');

		$scriptinit = $this->sliderInit($this->id);
		$gantry->addDomReadyScript($scriptinit);

		$output = '
		<div class="wrapper">
		';


		$output .= '<div class="overlay-tip">
			<div class="overlay-tip-left"></div>
			<div class="overlay-tip-mid"><span>Example</span></div>
			<div class="overlay-tip-right"></div>
		</div>';

		if ($preview == 'true') {
			$output .= '<div class="overlay-preview"><div></div></div>';
		}

		$output .= '
		<div id="' . $this->id . '-wrapper" class="' . $class . '">
			<div class="slider">
			    <div class="slider2"></div>
				<div class="knob"></div>
			</div>
			<input type="hidden" id="' . $this->id . '" class="slider" name="' . $this->name . '" value="' . $this->value . '" />
		</div>
		</div>
		';

		$gantry->addTemp('overlays', 'overlays', $__overlays);
		$gantry->addTemp('overlays', 'paths', $__paths);

		return $output;
	}

	function _loadOverlays($elementName, $path)
	{
		/** @global $gantry Gantry */
		global $gantry;

		$overlays = $gantry->retrieveTemp('overlays', 'overlays', array());
		$__paths  = $gantry->retrieveTemp('overlays', 'paths', array());

		$limit = $gantry->get('overlays_list_limit');

		$counter = 0;
		if (is_dir($path) && !isset($__paths[$path])) {
			if ($dh = opendir($path)) {
				$overlays[$elementName] = array();
				while (($file = readdir($dh)) !== false) {
					if (filetype($path . $file) == 'file' && $this->_isImage($file)) {
						if ($counter >= $limit) continue;

						$ext  = substr($file, strrpos($file, '.') + 1);
						$name = substr($file, 0, strrpos($file, '.'));

						$title = str_replace("-", " ", $name);
						$title = ucwords($title);

						$overlays[$elementName][$title] = array(
							'name' => $name,
							'ext'  => $ext,
							'file' => $name . "." . $ext
						);

						$counter++;
					}
				}
				closedir($dh);
				$__paths[$path] = $overlays[$elementName];
			}
		} else {
			$overlays[$elementName] = $__paths[$path];
		}

		ksort($overlays[$elementName]);

		$gantry->addTemp('overlays', 'overlays', $overlays);
		$gantry->addTemp('overlays', 'paths', $__paths);

		return $overlays;
	}

	function _isImage($file)
	{
		$extension = strtolower(substr($file, -4));

		return ($extension == '.jpg' || $extension == '.bmp' || $extension == '.gif' || $extension == '.png');
	}

	function sliderInit($name)
	{
		/** @global $gantry Gantry */
		global $gantry;

		$name = $name;
		$name = str_replace("][", "-", $name);

		$name2 = str_replace("-", "_", $name);
		$id    = str_replace("-", "_", $this->id);

		$valueName = $this->value;

		$current = $this->value;
		if ($current === false) $current = "none";

		$slider   = "document.id('" . $this->id . "').getPrevious('.slider')";
		$knob     = "document.id('" . $this->id . "').getPrevious('.slider').getElement('.knob')";
		$hidden   = "document.id('" . $this->id . "')";
		$children = 'GantryOverlays["' . $name . '"].getKeys();';

		$__overlays = $__overlays = $gantry->retrieveTemp('overlays', 'overlays', array());

		$steps   = count($__overlays[$name]);
		$default = '"' . $this->default . '"';

		$js = "
			if (!window.sliders) window.sliders = {};
			var current = GantryOverlays['" . $name . "'].getKeys().indexOf('" . $valueName . "');
			$hidden.addEvents({
				'set': function(value) {
					var slider = window.sliders['" . $id . "'];
					var index = slider.list.indexOf(value);
					slider.set(index);
				}
			});
			window.sliders['" . $id . "'] = new RokSlider(" . $slider . ", " . $knob . ", {
				steps: GantryOverlays['" . $name . "'].getKeys().length - 1,
				snap: true,
				onComplete: function() {
					this.knob.removeClass('down');
				},
				onDrag: function(now) {
					this.element.getFirst().setStyle('width', now + 10);

					var data = GantryOverlays['" . $name . "'].get(this.list[this.step]), width = 0;
					if (this.preview && this.preview.hasClass('overlay-preview')) {
						this.preview.setStyle('background-image', 'url('+data['path']+')');
						width = this.preview.getSize().x / 2;
					} else {
						width = " . $slider . ".getSize().x / 2;
					}

					this.tiptitle.getElement('span').innerHTML = data['name'];
					var x = this.tiptitle.getSize().x;

					this.tiptitle.setStyle('left', width - x / 2);
				},
				onChange: function(step) {
					" . $hidden . ".setProperty('value', this.list[step]);
				},
				onTick: function(position) {
					if(this.options.snap) position = this.toPosition(this.step);
					this.knob.setStyle(this.property, position);
					this.fireEvent('onDrag', position);
				}
			});
			window.sliders['" . $id . "'].list = " . $children . ";
			window.sliders['" . $id . "'].preview = document.id('" . $this->id . "').getParent('.wrapper').getElement('.overlay-preview');
			window.sliders['" . $id . "'].hiddenEl = " . $hidden . ";

			if (window.sliders['" . $id . "'].preview && window.sliders['" . $id . "'].preview.hasClass('overlay-preview')) window.sliders['" . $id . "'].tiptitle = window.sliders['" . $id . "'].preview.getPrevious();
			else window.sliders['" . $id . "'].tiptitle = document.id('" . $this->id . "').getParent('.wrapper').getElement('.overlay-tip');

			window.sliders['" . $id . "'].set(current);

			if (window.sliders['" . $id . "'].preview && window.sliders['" . $id . "'].preview.hasClass('overlay-preview')) {
				var tmpColors = ['#fff', '#ddd', '#333', '#000'];
				var data = GantryOverlays['" . $name . "'].get(window.sliders['" . $id . "'].list[window.sliders['" . $id . "'].step]);

				window.sliders['" . $id . "'].preview.setStyle('background-image', 'url('+data['path']+')');

				window.sliders['" . $id . "'].preview.addEvent('click', function() {
					if (this.indexColor == null) this.indexColor = 0;
					else {
						this.indexColor += 1;
						if (this.indexColor > tmpColors.length - 1) this.indexColor = 0;
					}

					this.setStyle('background-color', tmpColors[this.indexColor]);

				});
			}

			if (window.sliders['" . $id . "'].tiptitle) {
				" . $slider . ".addEvents({
					'mouseenter': function() {
						var pattern = GantryOverlays['" . $name . "'].get(window.sliders['" . $id . "'].list[window.sliders['" . $id . "'].step]);
						var name = pattern['name'];

						window.sliders['" . $id . "'].tiptitle.getElement('span').innerHTML = name;
						var x = window.sliders['" . $id . "'].tiptitle.getSize().x;

						if (window.sliders['" . $id . "'].preview && window.sliders['" . $id . "'].preview.hasClass('overlay-preview')) {
							window.sliders['" . $id . "'].tiptitle.setStyles({
								'visibility': 'visible',
								'top': -30,
								'left': (window.sliders['" . $id . "'].preview.getSize().x / 2) - x / 2
							});
						} else {
							window.sliders['" . $id . "'].tiptitle.setStyles({
								'visibility': 'visible',
								'top': -35,
								'left': (" . $slider . ".getSize().x / 2) - x / 2
							});
						}
					},
					'mouseleave': function() {
						window.sliders['" . $id . "'].tiptitle.setStyle('visibility', 'hidden');
					}
				});
			}

			$knob.addEvents({
				'mousedown': function() {this.addClass('down');},
				'mouseup': function() {this.removeClass('down');}
			});
			";

		return $js;
	}
}

?>
