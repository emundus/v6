<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') || die;

/**
 * Class DropfilesViewFrontViewer
 */
class DropfilesViewFrontViewer extends JViewLegacy
{
    /**
     * Display the view
     *
     * @param null|string $tpl Template
     *
     * @return void
     */
    public function display($tpl = null)
    {
        JHtml::_('jquery.framework');
        $doc = JFactory::getDocument();
        $doc->addScript(JURI::base('true') . '/components/com_dropfiles/assets/js/mediaelement-and-player.js');
        $doc->addStyleSheet(JURI::base('true') . '/components/com_dropfiles/assets/css/mediaelementplayer.min.css');
        $app = JFactory::getApplication();
        $id = $app->input->getString('id');
        $catid = $app->input->getInt('catid');
        $ext = $app->input->getString('ext');
        $this->downloadLink = JUri::root() . 'index.php?option=com_dropfiles&task=frontfile.download&&id=';
        $this->downloadLink .= $id . '&catid=' . $catid . '&preview=1';
        $this->mediaType = $app->input->getString('type');
        JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');
        $this->mineType = DropfilesFilesHelper::mimeType($ext);
        parent::display($tpl);
    }
}
