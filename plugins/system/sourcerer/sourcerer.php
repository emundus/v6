<?php
/**
 * @package         Sourcerer
 * @version         9.0.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\Language as RL_Language;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\Sourcerer\Area;
use RegularLabs\Plugin\System\Sourcerer\Clean;
use RegularLabs\Plugin\System\Sourcerer\Params;
use RegularLabs\Plugin\System\Sourcerer\Protect;
use RegularLabs\Plugin\System\Sourcerer\Replace;
use RegularLabs\Plugin\System\Sourcerer\Security;

// Do not instantiate plugin on install pages
// to prevent installation/update breaking because of potential breaking changes
$input = JFactory::getApplication()->input;
if (in_array($input->get('option'), ['com_installer', 'com_regularlabsmanager']) && $input->get('action') != '')
{
	return;
}

if ( ! is_file(__DIR__ . '/vendor/autoload.php'))
{
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
	|| ! is_file(JPATH_LIBRARIES . '/regularlabs/src/SystemPlugin.php')
)
{
	JFactory::getLanguage()->load('plg_system_sourcerer', __DIR__);
	JFactory::getApplication()->enqueueMessage(
		JText::sprintf('SRC_EXTENSION_CAN_NOT_FUNCTION', JText::_('SOURCERER'))
		. ' ' . JText::_('SRC_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
		'error'
	);

	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'SOURCERER'))
{
	RL_Extension::disable('sourcerer', 'plugin');

	RL_Language::load('plg_system_regularlabs');

	JFactory::getApplication()->enqueueMessage(
		JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('SOURCERER')),
		'error'
	);

	return;
}

if (true)
{
	class PlgSystemSourcerer extends RL_SystemPlugin
	{
		public $_lang_prefix           = 'SRC';
		public $_can_disable_by_url    = false;
		public $_disable_on_components = true;
		public $_page_types            = ['html', 'feed', 'pdf', 'xml', 'ajax', 'json', 'raw'];
		public $_jversion              = 3;

		protected function handleOnContentPrepare($area, $context, &$article, &$params, $page = 0)
		{
			$src_params = Params::get();

			$area = isset($article->created_by) ? 'articles' : 'other';

			$remove = $src_params->remove_from_search
				&& in_array($context, ['com_search.search', 'com_search.search.article', 'com_finder.indexer']);


			if (isset($article->description))
			{
				Replace::replace($article->description, $area, $article, $remove);
			}

			if (isset($article->title))
			{
				Replace::replace($article->title, $area, $article, $remove);
			}

			// Don't handle article texts in category list view
			if (RL_Document::isCategoryList($context))
			{
				return false;
			}

			if (isset($article->text))
			{
				Replace::replace($article->text, $area, $article, $remove);

				// Don't also do stuff on introtext/fulltext if text is set
				return false;
			}

			if (isset($article->introtext))
			{
				Replace::replace($article->introtext, $area, $article, $remove);
			}

			if (isset($article->fulltext))
			{
				Replace::replace($article->fulltext, $area, $article, $remove);
			}

			return false;
		}

		protected function changeDocumentBuffer(&$buffer)
		{
			if ( ! RL_Document::isHtml())
			{
				return false;
			}

			return Area::tag($buffer, 'component');
		}

		protected function changeFinalHtmlOutput(&$html)
		{
			// only in html, pdfs, ajax/raw and feeds
			if ( ! in_array(JFactory::getDocument()->getType(), ['html', 'pdf', 'ajax', 'raw']) && ! RL_Document::isFeed())
			{
				return false;
			}

			$params = Params::get();

			[$pre, $body, $post] = RL_Html::getBody($html);

			Protect::_($body);
			Replace::replaceInTheRest($body);

			Clean::cleanFinalHtmlOutput($body);
			RL_Protect::unprotect($body);

			$params->enable_in_head
				? Replace::replace($pre, 'head')
				: Clean::cleanTagsFromHead($pre);

			$html = $pre . $body . $post;

			return true;
		}
	}
}
