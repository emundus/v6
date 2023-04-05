<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\View\Plugin;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a plugin.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The item object for the newsfeed
     *
     * @var   CMSObject
     */
    protected $item;

    /**
     * The form object for the newsfeed
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The model state of the newsfeed
     *
     * @var   CMSObject
     */
    protected $state;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item  = $this->get('Item');
        $this->form  = $this->get('Form');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $canDo = ContentHelper::getActions('com_plugins');

        ToolbarHelper::title(Text::sprintf('COM_PLUGINS_MANAGER_PLUGIN', Text::_($this->item->name)), 'plug plugin');

        // If not checked out, can save the item.
        if ($canDo->get('core.edit')) {
            ToolbarHelper::apply('plugin.apply');

            ToolbarHelper::save('plugin.save');
        }

        ToolbarHelper::cancel('plugin.cancel', 'JTOOLBAR_CLOSE');
        ToolbarHelper::divider();

        // Get the help information for the plugin item.
        $lang = Factory::getLanguage();

        $help = $this->get('Help');

        if ($help->url && $lang->hasKey($help->url)) {
            $debug = $lang->setDebug(false);
            $url = Text::_($help->url);
            $lang->setDebug($debug);
        } else {
            $url = null;
        }

        ToolbarHelper::inlinehelp();
        ToolbarHelper::help($help->key, false, $url);
    }
}
