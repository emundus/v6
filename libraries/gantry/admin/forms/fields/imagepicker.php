<?php
/**
 * @version   $Id: imagepicker.php 8140 2013-03-08 17:02:46Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();
/**
 * @package     gantry
 * @subpackage  admin.elements
 */

gantry_import('core.config.gantryformfield');
class GantryFormFieldImagePicker extends GantryFormField
{

	protected $type = 'imagepicker';
	protected $basetype = 'imagepicker';

	protected static $rokgallery_loaded;

	function getInput()
	{
		JHTML::_('behavior.modal');
		/** @var $gantry Gantry */
		global $gantry;

		$layout         = $link = $dropdown = "";
		$options        = $choices = array();
		$nomargin       = false;
		$rokgallery     = self::checkForRokGallery();
		//$rokgallery = false; // debug

		$value = str_replace("'", '"', $this->value);
		$data  = json_decode($value);
		if (!$data && strlen($value)) {
			$nomargin = true;
			$data     = json_decode('{"path":"' . $value . '"}');
		}
		$preview        = "";
		$preview_width  = 'width="100"';
		$preview_height = 'height="70"';

		if (!$data && (!isset($data->preview) || !isset($data->path))) $preview = $gantry->gantryUrl . '/admin/widgets/imagepicker/images/no-image.png'; else if (isset($data->preview)) $preview = $data->preview; else {
			$preview        = JURI::root(true) . '/' . $data->path;
			$preview_height = "";
		}

		if (!defined('ELEMENT_RTIMAGEPICKER')) {
			$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/imagepicker/css/imagepicker.css');

			gantry_addInlineScript("
			if (typeof jInsertEditorText == 'undefined'){
				function jInsertEditorText(text, editor) {
					var source = text.match(/(src)=(\"[^\"]*\")/i), img;
					text = source[2].replace(/\\\"/g, '');
					img = '" . JURI::root(true) . "/' + text;

					document.getElementById(editor + '-img').src = img;
					document.getElementById(editor + '-img').removeProperty('height');
					document.getElementById(editor).value = JSON.encode({path: text});
				};
			};
			");

			gantry_addInlineScript("
				var AdminURI = '" . JURI::base(true) . "/';
				var GalleryPickerInsertText = function(input, string, size, minithumb){
					var data = {
						path: string,
						width: size.width,
						height: size.height,
						preview: minithumb
					};

					document.getElementById(input + '-img').src = minithumb;
					document.getElementById(input + '-infos').innerHTML = data.width + ' x ' + data.height;
					document.getElementById(input).value = JSON.encode(data);

				};

				var empty_background_img = '" . $gantry->gantryUrl . "/admin/widgets/imagepicker/images/no-image.png';

			");

			define('ELEMENT_RTIMAGEPICKER', true);
		}

		gantry_addInlineScript("
			window.addEvent('domready', function(){
				document.id('" . $this->id . "').addEvent('keyup', function(value){
					document.id('" . $this->id . "-infos').innerHTML = '';
					if (!value || !value.length) document.id('" . $this->id . "-img').set('src', empty_background_img);
					else {
						var data = JSON.decode(value);
						document.id('" . $this->id . "-img').set('src', (data.preview ? data.preview : '" . JURI::root(true) . "/' + data.path));
						if (!data.preview){
							document.id('" . $this->id . "-img').removeProperty('height');
						} else {
							document.id('" . $this->id . "-img').set('height', '50');
							if (data.width && data.height) document.id('" . $this->id . "-infos').innerHTML = data.width + ' x ' + data.height;
						}
					}

					this.setProperty('value', value);
				});

				document.id('" . $this->id . "-clear').addEvent('click', function(e){
					e.stop();
					document.id('" . $this->id . "').set('value', '').fireEvent('set', '');
					document.id('" . $this->id . "-img').src = empty_background_img;
					document.id('" . $this->id . "-infos').innerHTML = '';
				});

				var dropdown = document.id('" . $this->id . "mediatype');
				if (dropdown){
					dropdown.addEvent('change', function(){
						document.id('" . $this->id . "-link').set('href', this.value);
					});
				}
			});
		");

		if ($rokgallery) $link = 'index.php?option=com_rokgallery&view=gallerypicker&tmpl=component&show_menuitems=0&inputfield=' . $this->id; else $link = "index.php?option=com_media&view=images&layout=default&tmpl=component&e_name=" . $this->id;

		if ($rokgallery) {
			$choices = array(
				array(
					'RokGallery',
					'index.php?option=com_rokgallery&view=gallerypicker&tmpl=component&show_menuitems=0&inputfield=' . $this->id
				),
				array(
					'MediaManager',
					'index.php?option=com_media&view=images&layout=default&tmpl=component&e_name=' . $this->id
				)
			);

			foreach ($choices as $option) {
				$options[] = GantryHtmlSelect::option($option[1], $option[0], 'value', 'text');
			}

			include_once($gantry->gantryPath . '/' . 'admin' . '/' . 'forms' . '/' . 'fields' . '/' . 'selectbox.php');
			$selectbox        = new GantryFormFieldSelectBox;
			$selectbox->id    = $this->id . 'mediatype';
			$selectbox->value = $link;
			$selectbox->addOptions($options);
			$dropdown = '<div id="' . $this->id . '-mediadropdown" class="mediadropdown">' . $selectbox->getInput() . "</div>";
		}

		$value = str_replace('"', "'", $value);
		$layout .= '
			<div class="wrapper">' . "\n" . '
				<div id="' . $this->id . '-wrapper" class="backgroundpicker">' . "\n" . '
					<img id="' . $this->id . '-img" class="backgroundpicker-img" ' . $preview_width . ' ' . $preview_height . ' alt="" src="' . $preview . '" />

					<div id="' . $this->id . '-infos" class="backgroundpicker-infos" ' . ($rokgallery && !$nomargin ? 'style="display:inline-block;"' : 'style="display:none;"') . ' >' . ((isset($data->width) && (isset($data->height))) ? $data->width . ' x ' . $data->height : '') . '</div>


					<a id="' . $this->id . '-link" href="' . $link . '" rel="{handler: \'iframe\', size: {x: 675, y: 450}}" class="rok-button modal">' . "\n" . '
						Select
					</a>' . "\n" . '
					<a id="' . $this->id . '-clear" href="#" class="rok-button bg-button-clear">' . "\n" . '
						Reset
					</a>' . "\n" . '

					' . $dropdown . '

					<input class="background-picker" type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . $value . '" />' . "\n" . '
					<div class="clr"></div>
				</div>' . "\n" . '
			</div>' . "\n" . '
		';

		return $layout;
	}

	protected static function checkForRokGallery()
	{
		if (!isset(self::$rokgallery_loaded)) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('extension_id AS id, element AS "option", params, enabled');
			$query->from('#__extensions');
			$query->where($query->qn('type') . ' = ' . $db->quote('component'));
			$query->where($query->qn('element') . ' = ' . $db->quote('com_rokgallery'));
			$db->setQuery($query);
			try {
				$component = $db->loadObject();
				if (!is_null($component) && isset($component->option) && $component->option !== null) {
					self::$rokgallery_loaded = true;
				}
				else{
					self::$rokgallery_loaded = false;
				}
			} catch (RuntimeException $e) {
				self::$rokgallery_loaded = false;
			}
		}
		return self::$rokgallery_loaded;
	}
}

?>
