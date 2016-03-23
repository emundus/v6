<?php
/**
 * @version   $Id: gantrytemplate.class.php 7050 2013-01-31 21:56:01Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

gantry_import("core.utilities.gantrysimplexmlelement");
gantry_import("core.utilities.gantryregistry");

/**
 * Populates the parameters and template configuration form the templateDetails.xml and params.ini
 *
 * @package    gantry
 * @subpackage core
 */
class GantryTemplate
{


	/**
	 *
	 */
	const GRID_MODE_FIXED = 'fixed';

	/**
	 *
	 */
	const GRID_MODE_RESPONSIVE = 'responsive';

	/**
	 * Template Author
	 * @access private
	 * @var string
	 */
	protected $author;
	/**
	 * Template Version
	 * @access private
	 * @var string
	 */
	protected $version;

	/**
	 * Template Short Name
	 * @access private
	 * @var string
	 */
	protected $name;

	/**
	 * Template license
	 * @access private
	 * @var string
	 */
	protected $license;

	/**
	 * Template Full Name
	 * @access private
	 * @var string
	 */
	protected $fullname;

	/**
	 * Creation Date
	 * @access private
	 * @var string
	 */
	protected $creationDate;

	/**
	 * Template Author Email
	 * @access private
	 * @var string
	 */
	protected $authorEmail;


	/**
	 * Template Author Url
	 * @access private
	 * @var string
	 */
	protected $authorUrl;

	/**
	 * Template Description
	 * @access private
	 * @var string
	 */
	protected $description;

	/**
	 * Template Copyright
	 * @access private
	 * @var string
	 */
	protected $copyright;


	/**
	 * @var GantrySimpleXMLElement
	 */
	protected $xml;

	/**
	 * @var GantrySimpleXMLElement
	 */
	protected $options_xml;

	/**
	 * @var array
	 */
	protected $positions = array();

	/**
	 * @var array
	 */
	protected $positionInfo = array();

	/**
	 * @var array
	 */
	protected $params = array();

	/**
	 * @var GantryRegistry
	 */
	protected $_params_reg;

	/**
	 * @var array
	 */
	protected $_processors = array();


	/**
	 * @var bool
	 */
	protected $legacycss = true;

	/**
	 * @var bool
	 */
	protected $gridcss = true;

	/**
	 * @var array
	 */
	protected $cached_possitions = array();

	/**
	 * @var null
	 */
	protected $_params_content;

	/**
	 * @var
	 */
	protected $masterParams;

	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array(
			'author',
			'version',
			'name',
			'license',
			'fullname',
			'creationDate',
			'authorEmail',
			'authorUrl',
			'description',
			'copyright',
			'positions',
			'params',
			'positionInfo',
			'legacycss',
			'gridcss',
			'_params_content',
			'cached_possitions'
		);
	}

	/**
	 * @param $gantry
	 */
	public function init(Gantry &$gantry)
	{
		$this->xml         = new GantrySimpleXMLElement($gantry->templatePath . '/templateDetails.xml', null, true);
		$this->options_xml = new GantrySimpleXMLElement($gantry->templatePath . '/template-options.xml', null, true);
		$this->loadPositions();
		$this->_getTemplateInfo();
		$this->loadProcessors($gantry);
		$this->params = $this->getXMLParams($gantry);
	}

	/**
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}


	/**
	 * @return array
	 */
	protected function & loadPositions()
	{
		$positions     = array();
		$xml_positions = $this->xml->xpath('//positions/position');
		foreach ($xml_positions as $position) {
			//$position_name = $position->data();
			array_push($this->positions, $position->data());
			$shortposition = preg_replace("/(\-[a-f])$/i", "", $position->data());
			if (!array_key_exists($shortposition, $this->positionInfo)) {
				$positionObject                     = new stdClass();
				$attrs                              = $position->attributes();
				$positionObject->name               = $shortposition;
				$positionObject->id                 = $shortposition;
				$positionObject->max_positions      = 1;
				$positionObject->mobile             = ((string)$attrs['mobile'] == 'true') ? true : false;
				$this->positionInfo[$shortposition] = $positionObject;
			} else {
				$this->positionInfo[$shortposition]->max_positions++;
			}
		}
		return $positions;
	}

	/**
	 * @param $gantry
	 */
	protected function loadProcessors(&$gantry)
	{
		$processor_path = $gantry->gantryPath . '/core/params/processors';

		if (file_exists($processor_path) && is_dir($processor_path)) {
			$d = dir($gantry->gantryPath . '/core/params/processors');
			while (false !== ($entry = $d->read())) {
				if ($entry != '.' && $entry != '..') {
					$processor_name = basename($entry, ".php");
					$path           = $processor_path . '/' . $processor_name . '.php';
					$className      = 'GantryParamProcessor' . ucfirst($processor_name);
					if (!class_exists($className)) {
						if (file_exists($path)) {
							require_once($path);
							if (class_exists($className)) {
								$this->_processors[$className] = new $className();
							}
						}
					}
				}
			}
			$d->close();
		}
	}

	/**
	 * @param $gantry
	 * @param $param_name
	 * @param $param_element
	 * @param $data
	 */
	protected function runProcessorPreLoad(&$gantry, $param_name, &$param_element, &$data)
	{
		foreach ($this->_processors as $processor) {
			$processor->preLoad($gantry, $param_name, $param_element, $data);
		}
	}

	/**
	 * @param $gantry
	 * @param $param_name
	 * @param $param_element
	 * @param $data
	 */
	protected function runProcessorPostLoad(&$gantry, $param_name, &$param_element, &$data)
	{
		foreach ($this->_processors as $processor) {
			$processor->postLoad($gantry, $param_name, $param_element, $data);
		}
	}

	/**
	 * @return array
	 */
	public function getUniquePositions()
	{
		return array_keys($this->positionInfo);
	}

	/**
	 * @param $position_name
	 *
	 * @return mixed
	 */
	public function getPositionInfo($position_name)
	{
		$shortposition = preg_replace("/(\-[a-f])$/i", "", $position_name);
		if (array_key_exists($shortposition, $this->positionInfo)) {
			return $this->positionInfo[$shortposition];
		}
		return false;
	}

	/**
	 * @return array
	 */
	public function getPositions()
	{
		return $this->positions;
	}

	/**
	 * @param $position
	 * @param $pattern
	 *
	 * @return array
	 */
	public function parsePosition($position, $pattern)
	{

		$filtered_positions = array();
		if (count($this->positions) > 0) {
			if (!array_key_exists($position, $this->cached_possitions)) {
				if (null == $pattern) {
					$pattern = "(-)?";
				}
				$regpat = "/^" . $position . $pattern . "/";
				foreach ($this->positions as $key => $value) {
					if (preg_match($regpat, $value) == 1) {
						$filtered_positions[] = $value;
					}
				}
				$this->cached_possitions[$position] = $filtered_positions;
			}
		} else {
			return $filtered_positions;
		}
		return $this->cached_possitions[$position];
	}

	/**
	 * @param $gantry
	 *
	 * @return array
	 */
	protected function getXMLParams(Gantry &$gantry)
	{
		$this->_params_content = "";

		$this->loadParamsContent($gantry);

		$data   = array();
		$params = $this->options_xml->xpath('//form//field|//form//fields[@default]|//form//fields[@value]');

		foreach ($params as $param) {
			$attrs  = $param->xpath('ancestor::fields[@name][not(@ignore-group=\'true\')]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$groups = array_flip($groups);
			if (array_key_exists('template-options', $groups)) unset($groups['template-options']);
			$groups = array_flip($groups);
			$prefix = '';
			foreach ($groups as $parent) {
				$prefix .= $parent . "-";
			}
			$param_name = $prefix . $param['name'];
			$this->_getParamInfo($gantry, $param_name, $param, $data);
		}
		$this->params = $data;
		return $data;
	}

	/**
	 * Loads the params.ini content
	 *
	 * @param  $gantry
	 *
	 * @return bool
	 */
	protected function loadParamsContent(Gantry &$gantry)
	{
		if ($gantry->isAdmin()) {
			$styleId = JFactory::getApplication()->input->getInt('id', 0);
			if ($styleId == 0) {
				$template = self::getMasterTemplateStyleByName($gantry->templateName);
				$styleId  = $template->id;
			}
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');
			$table = JTable::getInstance('Style', 'TemplatesTable', array());
			// Attempt to load the row.
			$return   = $table->load($styleId);
			$registry = new GantryRegistry;
			$registry->loadString($table->params);
			if ($registry->get('master') != 'true') {
				$return   = $table->load($registry->get('master'));
				$registry = new GantryRegistry;
				$registry->loadString($table->params);
			}
			// Check for a table object error.
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return false;
			}
			$this->_params_content = $table->params;
		} else {
			$template = self::getTemplateById(JFactory::getApplication()->getTemplate(true)->id);
			if (($style_id = $template->params->get('master', 'true')) !== 'true') {
				$master_id = $style_id;
			} else {
				$master_id = $template->id;
			}
			$registry              = self::getTemplateParams((int)$master_id);

			$this->masterParams    = $registry->toString();
			$this->_params_content = $this->masterParams;
		}
		$this->_params_reg = $registry;

		return true;
	}

	/**
	 * @return string
	 */
	public function getParamsHash()
	{
		return md5($this->_params_content);
	}

	/**
	 * @return string
	 */
	public function getMasterParamsHash()
	{
		return md5($this->masterParams);
	}


	/**
	 * @param        $gantry
	 * @param        $param_name
	 * @param        $param
	 * @param        $data
	 * @param string $prefix
	 */
	protected function _getParamInfo(Gantry &$gantry, $param_name, &$param, &$data, $prefix = "")
	{


		$attributes = array();
		foreach ($param->attributes() as $key => $val) {
			$attributes[$key] = (string)$val;
		}

		$full_param_name = $prefix . $param_name;

		$default = (array_key_exists('default', $attributes)) ? $attributes['default'] : false;
		$value   = $this->_getParamValue($full_param_name, $default);

		// run the preload of the processors
		$this->runProcessorPreLoad($gantry, $full_param_name, $param, $data);

		$data[$full_param_name] = array(
			'name'          => $prefix . $full_param_name,
			'type'          => $attributes['type'],
			'default'       => $default,
			'value'         => $value,
			'sitebase'      => $value,
			'setbyurl'      => (array_key_exists('setbyurl', $attributes)) ? ($attributes['setbyurl'] == 'true') ? true : false : false,
			'setbycookie'   => (array_key_exists('setbycookie', $attributes)) ? ($attributes['setbycookie'] == 'true') ? true : false : false,
			'setbysession'  => (array_key_exists('setbysession', $attributes)) ? ($attributes['setbysession'] == 'true') ? true : false : false,
			'setincookie'   => (array_key_exists('setbycookie', $attributes)) ? ($attributes['setbycookie'] == 'true') ? true : false : false,
			'setinsession'  => (array_key_exists('setinsession', $attributes)) ? ($attributes['setinsession'] == 'true') ? true : false : false,
			'setinoverride' => (array_key_exists('setinoverride', $attributes)) ? ($attributes['setinoverride'] == 'true') ? true : false : true,
			'setbyoverride' => (array_key_exists('setbyoverride', $attributes)) ? ($attributes['setbyoverride'] == 'true') ? true : false : true,
			'isbodyclass'   => (array_key_exists('isbodyclass', $attributes)) ? ($attributes['isbodyclass'] == 'true') ? true : false : false,
			'setclassbytag' => (array_key_exists('setclassbytag', $attributes)) ? $attributes['setclassbytag'] : false,
			'setby'         => 'default',
			'attributes'    => $attributes
		);

		if ($data[$full_param_name]['setbyurl']) $gantry->_setbyurl[] = $full_param_name;
		if ($data[$full_param_name]['setbysession']) $gantry->_setbysession[] = $full_param_name;
		if ($data[$full_param_name]['setbycookie']) $gantry->_setbycookie[] = $full_param_name;
		if ($data[$full_param_name]['setinsession']) $gantry->_setinsession[] = $full_param_name;
		if ($data[$full_param_name]['setincookie']) $gantry->_setincookie[] = $full_param_name;
		if ($data[$full_param_name]['setinoverride']) {
			$gantry->setinoverride[] = $full_param_name;
		} else {
			$gantry->dontsetinoverride[] = $full_param_name;
		}
		if ($data[$full_param_name]['setbyoverride']) $gantry->_setbyoverride[] = $full_param_name;
		if ($data[$full_param_name]['isbodyclass']) $gantry->_bodyclasses[] = $full_param_name;
		if ($data[$full_param_name]['setclassbytag']) $gantry->_classesbytag[$data[$full_param_name]['setclassbytag']][] = $full_param_name;

		$this->runProcessorPostLoad($gantry, $full_param_name, $param, $data);

	}

	/**
	 * @param $param_name
	 * @param $default
	 *
	 * @return mixed
	 */
	protected function _getParamValue($param_name, $default)
	{
		$original_param_name = $param_name;
		$value               = $default;
		$exists              = false;
		if ($this->_params_reg->exists($param_name)) $exists = true;


		// try forward dashes
		if (!$exists && strstr($param_name, '-') !== false) {
			$param_name_parts = explode('-', $param_name);
			$parts_count      = count($param_name_parts);
			$dots             = array();
			for ($i = 0; $i < $parts_count; $i++) {
				array_unshift($dots, array_pop($param_name_parts));
				$param_name = implode('-', $param_name_parts) . '.' . implode('.', $dots);
				if ($this->_params_reg->exists($param_name)) {
					$exists = true;
					break;
				}
			}

			// try backwards dashes
			if (!$exists) {
				$param_name       = $original_param_name;
				$param_name_parts = explode('-', $param_name);
				$parts_count      = count($param_name_parts);
				$dots             = array();
				for ($i = 0; $i < $parts_count; $i++) {
					array_unshift($dots, array_pop($param_name_parts));
					$param_name = implode('.', $param_name_parts) . '.' . implode('-', $dots);
					if ($this->_params_reg->exists($param_name)) {
						$exists = true;
						break;
					}
				}
			}
		}
		if ($exists) $value = $this->_params_reg->get($param_name, $default);
		return $value;
	}

	/**
	 * load the basic template info
	 * @return void
	 */
	protected function _getTemplateInfo()
	{
		if ($this->xml->name) $this->setName((string)$this->xml->name);
		if ($this->xml->version) $this->setVersion((string)$this->xml->version);
		if ($this->xml->creationDate) $this->setCreationDate((string)$this->xml->creationDate);
		if ($this->xml->author) $this->setAuthor((string)$this->xml->author);
		if ($this->xml->authorUrl) $this->setAuthorUrl((string)$this->xml->authorUrl);
		if ($this->xml->authorEmail) $this->setAuthorEmail((string)$this->xml->authorEmail);
		if ($this->xml->copyright) $this->setCopyright((string)$this->xml->copyright);
		if ($this->xml->license) $this->setLicense((string)$this->xml->license);
		if ($this->xml->description) $this->setDescription((string)$this->xml->description);
		if ($this->xml->legacycss) $this->setLegacycss((string)$this->xml->legacycss);
		if ($this->xml->gridcss) $this->setGridcss((string)$this->xml->gridcss);
	}

	/**
	 * @static
	 *
	 * @param $id
	 *
	 * @return GantryRegistry
	 */
	public static function getTemplateParams($id)
	{
		$templates = self::getAllTemplates();
		if (array_key_exists($id, $templates)) return $templates[$id]->params; else
			return new GantryRegistry;
	}

	/**
	 * @static
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public static function getTemplateById($id = 0)
	{
		$templates = self::getAllTemplates();
		if ($id == 0) {
			foreach ($templates as $lookingtemp) {
				if ($lookingtemp->home == 1) {
					$template = $lookingtemp;
					break;
				}
			}
		} else {
			$template = $templates[$id];
		}
		return $template;
	}

	/**
	 * @static
	 * @return mixed
	 */
	public static function getAllTemplates()
	{
		$cache     = JFactory::getCache('com_templates', '');
		$tag       = JFactory::getLanguage()->getTag();
		$templates = $cache->get('gantrytemplates0' . $tag);
		if ($templates === false) {
			// Load styles
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, home, template, s.params');
			$query->from('#__template_styles as s');
			$query->where('s.client_id = 0');
			$query->where('e.enabled = 1');
			$query->leftJoin('#__extensions as e ON e.element=s.template AND e.type=' . $db->quote('template') . ' AND e.client_id=s.client_id');

			$db->setQuery($query);
			$templates = $db->loadObjectList('id');
			foreach ($templates as &$template) {
				$registry = new GantryRegistry;
				$registry->loadString($template->params);
				$template->params = $registry;
			}
			$cache->store($templates, 'gantrytemplates0' . $tag);
		}

		return $templates;
	}

	/**
	 * @static
	 *
	 * @param $template_name
	 *
	 * @return bool
	 */
	public static function getMasterTemplateStyleByName($template_name)
	{
		$templates = self::getAllTemplates();
		foreach ($templates as $template) {
			if ($template->template == $template_name && $template->params->get('master') == 'true') {
				return $template;
			}
		}
		return false;
	}


	/**
	 * Gets the version for gantry
	 * @access public
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Sets the version for gantry
	 * @access public
	 *
	 * @param string $version
	 */
	protected function setVersion($version)
	{
		$this->version = $version;
	}


	/**
	 * Gets the name for gantry
	 * @access public
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets the name for gantry
	 * @access public
	 *
	 * @param string $name
	 */
	protected function setName($name)
	{
		$this->name = $name;
	}


	/**
	 * Gets the fullname for gantry
	 * @access public
	 * @return string
	 */
	public function getFullname()
	{
		return $this->fullname;
	}

	/**
	 * Sets the fullname for gantry
	 * @access public
	 *
	 * @param string $fullname
	 */
	protected function setFullname($fullname)
	{
		$this->fullname = $fullname;
	}


	/**
	 * Gets the creationDate for gantry
	 * @access public
	 * @return string
	 */
	public function getCreationDate()
	{
		return $this->creationDate;
	}

	/**
	 * Sets the creationDate for gantry
	 * @access public
	 *
	 * @param string $creationDate
	 */
	protected function setCreationDate($creationDate)
	{
		$this->creationDate = $creationDate;
	}


	/**
	 * Gets the authorEmail for gantry
	 * @access public
	 * @return string
	 */
	public function getAuthorEmail()
	{
		return $this->authorEmail;
	}

	/**
	 * Sets the authorEmail for gantry
	 * @access public
	 *
	 * @param string $authorEmail
	 */
	protected function setAuthorEmail($authorEmail)
	{
		$this->authorEmail = $authorEmail;
	}


	/**
	 * Gets the authorUrl for gantry
	 * @access public
	 * @return string
	 */
	public function getAuthorUrl()
	{
		return $this->authorUrl;
	}

	/**
	 * Sets the authorUrl for gantry
	 * @access public
	 *
	 * @param string $authorUrl
	 */
	protected function setAuthorUrl($authorUrl)
	{
		$this->authorUrl = $authorUrl;
	}


	/**
	 * Gets the description for gantry
	 * @access public
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Sets the description for gantry
	 * @access public
	 *
	 * @param string $description
	 */
	protected function setDescription($description)
	{
		$this->description = $description;
	}


	/**
	 * Gets the copyright for gantry
	 * @access public
	 * @return string
	 */
	public function getCopyright()
	{
		return $this->copyright;
	}

	/**
	 * Sets the copyright for gantry
	 * @access public
	 *
	 * @param string $copyright
	 */
	protected function setCopyright($copyright)
	{
		$this->copyright = $copyright;
	}


	/**
	 * Gets the license for gantry
	 * @access public
	 * @return string
	 */
	public function getLicense()
	{
		return $this->license;
	}

	/**
	 * Sets the license for gantry
	 * @access public
	 *
	 * @param string $license
	 */
	protected function setLicense($license)
	{
		$this->license = $license;
	}


	/**
	 * Gets the author for gantry
	 * @access public
	 * @return string
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * Sets the author for gantry
	 * @access public
	 *
	 * @param string $author
	 */
	protected function setAuthor($author)
	{
		$this->author = $author;
	}

	/**
	 * @param $legacycss
	 */
	public function setLegacycss($legacycss)
	{
		$set = true;
		if ($legacycss == 'false') {
			$this->legacycss = false;
		}
	}

	/**
	 * @return bool
	 */
	public function getLegacycss()
	{
		return $this->legacycss;
	}

	/**
	 * @param boolean $gridcss
	 */
	public function setGridcss($gridcss)
	{
		$set = true;
		if ($gridcss == 'false') {
			$this->gridcss = false;
		}
	}

	/**
	 * @return boolean
	 */
	public function getGridcss()
	{
		return $this->gridcss;
	}
}
