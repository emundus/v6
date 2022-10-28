<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Plugin\CMSPlugin as JPlugin;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\Registry\Registry;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\Uri as RL_Uri;
use RegularLabs\Plugin\System\RegularLabs\AdminMenu;
use RegularLabs\Plugin\System\RegularLabs\DownloadKey;
use RegularLabs\Plugin\System\RegularLabs\QuickPage;
use RegularLabs\Plugin\System\RegularLabs\SearchHelper;

if ( ! is_file(__DIR__ . '/vendor/autoload.php'))
{
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/src/ParametersNew.php')
)
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3))
{
	RL_Extension::disable('regularlabs', 'plugin');

	return;
}

JFactory::getLanguage()->load('plg_system_regularlabs', __DIR__);

$config = new JConfig;

$input = JFactory::getApplication()->input;

// Deal with error reporting when loading pages we don't want to break due to php warnings
if ( ! in_array($config->error_reporting, ['none', '0'])
	&& (
		($input->get('option') == 'com_regularlabsmanager'
			&& ($input->get('task') == 'update' || $input->get('view') == 'process')
		)
		||
		($input->getInt('rl_qp') == 1 && $input->get('url') != '')
	)
)
{
	RL_Extension::orderPluginFirst('regularlabs');

	error_reporting(E_ERROR);
}

class PlgSystemRegularLabs extends JPlugin
{
	public function onAfterDispatch()
	{
		if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
		{
			return;
		}

		if ( ! RL_Document::isAdmin(true) || ! RL_Document::isHtml()
		)
		{
			return;
		}

		RL_Document::loadMainDependencies();
	}

	public function onAfterRender()
	{
		if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
		{
			return;
		}

		if ( ! RL_Document::isAdmin(true) || ! RL_Document::isHtml()
		)
		{
			return;
		}

		$this->fixQuotesInTooltips();

		AdminMenu::combine();

		AdminMenu::addHelpItem();

		DownloadKey::cloak();
	}

	private function fixQuotesInTooltips()
	{
		$html = JFactory::getApplication()->getBody();

		if ($html == '')
		{
			return;
		}

		if (strpos($html, '&amp;quot;rl-code&amp;quot;') === false
			&& strpos($html, '&amp;quot;rl_code&amp;quot;') === false)
		{
			return;
		}

		$html = str_replace(
			['&amp;quot;rl-code&amp;quot;', '&amp;quot;rl_code&amp;quot;'],
			'&quot;rl-code&quot;',
			$html
		);

		JFactory::getApplication()->setBody($html);
	}

	public function onAfterRoute()
	{
		if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
		{
			if (JFactory::getApplication()->isClient('administrator'))
			{
				JFactory::getApplication()->enqueueMessage('The Regular Labs Library folder is missing or incomplete: ' . JPATH_LIBRARIES . '/regularlabs', 'error');
			}

			return;
		}

		DownloadKey::update();

		SearchHelper::load();

		QuickPage::render();
	}

	public function onAjaxRegularLabs()
	{
		$input = JFactory::getApplication()->input;

		$format = $input->getString('format', 'json');

		$attributes = RL_Uri::getCompressedAttributes();
		$attributes = new Registry($attributes);

		$field      = $attributes->get('field');
		$field_type = $attributes->get('fieldtype');

		$class = $this->getAjaxClass($field, $field_type);

		if (empty($class) || ! class_exists($class))
		{
			return false;
		}

		$type = $attributes->type ?? '';

		$method = 'getAjax' . ucfirst($format) . ucfirst($type);

		$class = new $class;

		if ( ! method_exists($class, $method))
		{
			return false;
		}

		return $class->$method($attributes);
	}

	public function getAjaxClass($field, $field_type = '')
	{
		if (empty($field))
		{
			return false;
		}

		if ($field_type)
		{
			return $this->getFieldClass($field, $field_type);
		}

		$file = JPATH_LIBRARIES . '/regularlabs/fields/' . strtolower($field) . '.php';

		if ( ! file_exists($file))
		{
			return $this->getFieldClass($field, $field);
		}

		require_once $file;

		return 'JFormFieldRL_' . ucfirst($field);
	}

	public function getFieldClass($field, $field_type)
	{
		$file = JPATH_PLUGINS . '/fields/' . strtolower($field_type) . '/fields/' . strtolower($field) . '.php';

		if ( ! file_exists($file))
		{
			return false;
		}

		require_once $file;

		return 'JFormField' . ucfirst($field);
	}

	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		$uri  = JUri::getInstance($url);
		$host = $uri->getHost();

		if (
			strpos($host, 'regularlabs.com') === false
			&& strpos($host, 'nonumber.nl') === false
		)
		{
			return true;
		}

		$uri->setScheme('https');
		$uri->setHost('download.regularlabs.com');
		$uri->delVar('pro');
		$url = $uri->toString();

		$params = RL_Parameters::getComponent('regularlabsmanager');

		if (empty($params) || empty($params->key))
		{
			return true;
		}

		$uri->setVar('k', $params->key);
		$url = $uri->toString();

		return true;
	}
}
