<?php
/**
 * @version        4.1.31 April 11, 2016
 * @author         RocketTheme http://www.rockettheme.com
 * @copyright      Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 */
class plgSystemGantry extends JPlugin
{
	/**
	 * @var bool
	 */
	protected static $prettyprint = false;
	/**
	 * @var array
	 */
	protected $bootstrapTriggers = array(
		'data-toggle="tab"',
		'data-toggle="pill"',
		'data-dismiss="alert"',
		'data-toggle="collapse"'
	);
	/**
	 * @var array
	 */
	protected $_cleanCacheAfterTasks = array(
		'com_modules'   => array(
			'module.apply',
			'module.save',
			'module.save2copy',
			'modules.unpublish',
			'modules.publish',
			'modules.saveorder',
			'modules.trash',
			'modules.duplicate'
		),
		'com_templates' => array(
			'publish',
			'save',
			'save_positions',
			'default',
			'apply',
			'save_source',
			'apply_source'
		),
		'com_config'    => array(
			'save'
		)
	);

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$app  = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_gantry', JPATH_ADMINISTRATOR);
		JLog::addLogger(array('text_file' => 'gantry.php'), $this->params->get('debugloglevel', 63), array('gantry'));
	}

	/**
	 *
	 */
	public function onAfterInitialise()
	{
		$app = JFactory::getApplication();
		if ($app->isSite()) {
			if (!defined('GANTRY_OVERRIDES_PATH')) {
				define('GANTRY_OVERRIDES_PATH', dirname(__FILE__) . '/overrides');
				JLog::add(sprintf('Setting override path to %s', GANTRY_OVERRIDES_PATH), JLog::DEBUG, 'rokoverrides');
			}
			require_once dirname(__FILE__) . '/functions.php';

		}
	}

	public function onGantryTemplateInit($filename)
	{
		JLog::add(JText::sprintf('GANTRY_INITIALIZED_FROM', $filename), JLog::DEBUG, 'gantry');
	}

	/**
	 * Catch the routed functions for
	 */
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();
		if ($app->isSite()) {
//			$template_info = $app->getTemplate(true);
//			if ($this->isGantryTemplate($template_info->id)) {
//				include(JPATH_LIBRARIES . '/gantry/gantry.php');
//			}
		} else {
			if (array_key_exists('option', $_REQUEST) && array_key_exists('task', $_REQUEST)) {
				$option = JFactory::getApplication()->input->getCmd('option');
				$task   = JFactory::getApplication()->input->getCmd('task');

				// Redirect styles.duplicate to template.duplicate to handle gantry template styles
				if ($option == 'com_templates' && $task == 'styles.duplicate') {
					$this->setRequestOption('option', 'com_gantry');
					$this->setRequestOption('task', 'template.duplicate');
				}

				// Redirect styles.delete to not let a gantry master template style be deleted
				if ($option == 'com_templates' && $task == 'styles.delete') {
					$this->setRequestOption('option', 'com_gantry');
					$this->setRequestOption('task', 'template.delete');
				}

				// redirect styles.edit if the template style is a gantry one
				if ($option == 'com_templates' && $task == 'style.edit') {
					$id = JFactory::getApplication()->input->getInt('id', 0);
					if ($id == 0) {
						// Initialise variables.
						$pks = JFactory::getApplication()->input->post->get('cid', array(), 'array');
						if (is_array($pks) && array_key_exists(0, $pks)) {
							$id = $pks[0];
						}
					}

					//redirect to gantry admin
					if ($this->isGantryTemplate($id)) {
						$this->setRequestOption('option', 'com_gantry');
						$this->setRequestOption('task', 'template.edit');
						$this->setRequestOption('id', $id);
					}
				}
			}
		}

	}

	/* temporary solution to add Google Prettify stuff */

	/**
	 * @param $key
	 * @param $value
	 */
	private function setRequestOption($key, $value)
	{
		if (class_exists('JRequest')) {
			JRequest::set(array($key => $value), 'GET');
			JRequest::set(array($key => $value), 'POST');
		}
	}

	/**
	 * Check if template is based on gantry
	 *
	 * @param string $id
	 *
	 * @return boolean
	 */
	private function isGantryTemplate($id)
	{
		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($id);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return false;

		}
		$template = $table->template;

		return file_exists(JPATH_SITE . '/' . 'templates' . '/' . $template . '/' . 'lib' . '/' . 'gantry' . '/' . 'gantry.php');

	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param    type      The table type to instantiate
	 * @param    string    A prefix for the table class name. Optional.
	 * @param    array     Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 */
	public function getTable($type = 'Style', $prefix = 'TemplatesTable', $config = array())
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * @param     $context
	 * @param     $article
	 * @param     $params
	 * @param int $page
	 */
	function onContentBeforeDisplay($context, &$article, &$params, $page = 0)
	{

		if (!self::$prettyprint && isset($article->text) && (strpos($article->text, '<code class="prettyprint') !== false || strpos($article->text, '<pre class="prettyprint') !== false)) {

			$doc = JFactory::getDocument();
			$app = JFactory::getApplication();
			if (!file_exists(JPATH_THEMES . '/' . $app->getTemplate() . '/less/prettify.less')) {
				if (file_exists(JPATH_THEMES . '/' . $app->getTemplate() . '/css/prettify.css')) {
					$doc->addStyleSheet(JURI::root(true) . '/' . $app->getTemplate() . '/css/prettify.css');
				} else {
					$doc->addStyleSheet(JURI::root(true) . '/libraries/gantry/libs/google-code-prettify/prettify.css');
				}
			}
			$doc->addScript(JURI::root(true) . '/libraries/gantry/libs/google-code-prettify/prettify.js');
			$doc->addScriptDeclaration("\nwindow.addEvent('domready', function() { prettyPrint();});\n");
			self::$prettyprint = true;
		}

	}

	/**
	 *
	 */
	public function onBeforeCompileHead()
	{
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
		if (!$app->isAdmin()) {
			$template_info = $app->getTemplate(true);
			// If its a gantry template dont load up
			if ($this->isGantryTemplate($template_info->id) && isset($doc->_styleSheets[JURI::root(true) . '/templates/' . $app->getTemplate() . '/css-compiled/bootstrap.css'])) {
				unset($doc->_styleSheets[JUri::base(true) . '/media/jui/css/bootstrap.css']);
				unset($doc->_styleSheets[JUri::base(true) . '/media/jui/css/bootstrap.min.css']);
				unset($doc->_styleSheets[JUri::base(true) . '/media/jui/css/bootstrap-responsive.css']);
				unset($doc->_styleSheets[JUri::base(true) . '/media/jui/css/bootstrap-responsive.min.css']);
				unset($doc->_styleSheets[JUri::base(true) . '/media/jui/css/bootstrap-extended.css']);
				unset($doc->_styleSheets[JUri::base(true) . '/media/jui/css/bootstrap-rtl.css']);
			}
		}
	}

	/**
	 *
	 */
	public function onAfterRender()
	{
		$app = JFactory::getApplication();

		if (!$app->isAdmin()) return;

		$option = $app->input->getString('option', '');
		$view   = $app->input->getString('view', '');
		$task   = $app->input->getString('task', '');

		if ($option == 'com_templates' && (($view == 'styles') || (empty($view) && empty($task)))) {
			$master_templates = $this->getMasters();
			$gantry_templates = $this->getGantryTemplates();
			if (!class_exists('phpQuery')) {
				require_once(JPATH_LIBRARIES . "/gantry/libs/phpQuery.php");
			}
			$document = JFactory::getDocument();
			$doctype  = $document->getType();
			if ($doctype == 'html') {
				$body = JResponse::getBody();
				$pq   = phpQuery::newDocument($body);

				foreach ($gantry_templates as $gantry) {
					if (in_array($gantry['id'], $master_templates)) {
						pq('td > input[value=' . $gantry['id'] . ']')->parent()->next()->append('<span style="white-space:nowrap;margin:0 10px;background:#d63c1f;color:#fff;padding:2px 4px;font-family:Helvetica,Arial,sans-serif;border-radius:3px;">&#10029; Master</span>');
					} else {
						pq('td > input[value=' . $gantry['id'] . ']')->parent()->next()->append('<span style="white-space:nowrap;margin:0 10px;background:#999;color:#fff;padding:2px 4px;font-family:Helvetica,Arial,sans-serif;border-radius:3px;">Override</span>');
					}

					$link  = pq('td > input[value=' . $gantry['id'] . ']')->parent()->next()->find('a[href*="com_templates"');
					$value = str_replace('style.edit', 'template.edit', str_replace('com_templates', 'com_gantry', $link->attr('href')));
					$link->attr('href', $value);
				}


				$body = $pq->getDocument()->htmlOuter();
				JResponse::setBody($body);
			}
		}

		if ($option == 'com_gantry') {


			if (!class_exists('phpQuery')) {
				require_once(JPATH_LIBRARIES . "/gantry/libs/phpQuery.php");
			}

			$body = JResponse::getBody();
			$pq   = phpQuery::newDocument($body);

			// default system message
			pq('div#toolbar-box')->after('<div class="clr"></div><dl id="system-message"><dt class="message"></dt><dd class="message message fade"><ul><li></li></ul></dd><span class="close"><span>x</span></span></dl>');
			// adminpraise3
			pq('#system-message-container')->append('<dl id="system-message"><dt class="message"></dt><dd class="message message fade"><ul><li></li></ul></dd><span class="close"><span>x</span></span></dl></div><div class="clear">');

			pq('#mc-title')->before('<div class="clr"></div><dl id="system-message"><dt class="message"></dt><dd class="message message fade"><ul><li></li></ul></dd><span class="close"><span>x</span></span></dl>');
			pq('div#content > .pagetitle')->after('<div class="clr"></div><dl id="system-message"><dt class="message"></dt><dd class="message message fade"><ul><li></li></ul></dd><span class="close"><span>x</span></span></dl>');


			$body = $pq->getDocument()->htmlOuter();
			JResponse::setBody($body);
		}
	}

	/**
	 * @return array
	 */
	private function getMasters()
	{
		$templates = $this->getTemplates();
		$masters   = array();
		foreach ($templates as $template) {
			if ($template->params->get('master') == 'true') {
				$masters[] = $template->id;
			}
		}
		return $masters;
	}

	/**
	 * @return mixed
	 */
	private function getTemplates()
	{
		$cache = JFactory::getCache('com_templates', '');
		$tag   = JFactory::getLanguage()->getTag();

		$templates = $cache->get('templates0' . $tag);

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
				$registry = new JRegistry;
				$registry->loadString($template->params);
				$template->params = $registry;

				// Create home element
				if ($template->home == '1' && !isset($templates[0]) && $template->home == $tag) {
					$templates[0] = clone $template;
				}
			}
			$cache->store($templates, 'templates0' . $tag);
		}
		return $templates;
	}

	/**
	 * @return array
	 */
	private function getGantryTemplates()
	{
		$templates = $this->getTemplates();
		$gantry    = array();
		foreach ($templates as $template) {
			if ($template->params->get('master') != null) {
				$gantry[] = array('id' => $template->id, 'name' => ucfirst($template->template));
			}
		}

		return $gantry;
	}

	/**
	 *
	 */
	public function onAfterDispatch()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin()) return;

		$document = JFactory::getDocument();
		$doctype  = $document->getType();
		$messages = JFactory::getSession()->get('application.queue');

		if ($doctype == 'html') {
			$buffer      = "";
			$tmp_buffers = $document->getBuffer();
			if (is_array($tmp_buffers)) {
				foreach ($document->getBuffer() as $key => $value) {
					$buffer .= $document->getBuffer($key);
				}
			}

			if (empty($buffer) && !count($messages)) return;

			// wether to load bootstrap jui or not
			if (($this->_contains($buffer, $this->bootstrapTriggers) || count($messages)) && version_compare(JVERSION, '3.0.0') >= 0) {
				JHtml::_('bootstrap.framework');
			}
		}

	}

	/**
	 * @param       $string
	 * @param array $search
	 * @param bool  $caseInsensitive
	 *
	 * @return bool
	 */
	private function _contains($string, array $search, $caseInsensitive = false)
	{
		$exp = '/' . implode('|', array_map('preg_quote', $search)) . ($caseInsensitive ? '/i' : '/');
		return preg_match($exp, $string) ? true : false;
	}

	/**
	 *
	 */
	public function onSearch()
	{

	}

}
