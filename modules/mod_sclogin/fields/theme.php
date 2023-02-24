<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Filesystem\Folder;

jimport('joomla.form.helper');
jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');

class JFormFieldTheme extends JFormFieldList
{
    public $type = 'Theme';

    protected function getOptions()
    {
        $mediaCssFiles = $this->getCssFiles('/media/sourcecoast/themes/sclogin/');

        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        $query->select("template")
            ->from("#__template_styles")
            ->where("client_id = 0")
            ->where("home = 1");
        $db->setQuery($query);
        $templateFolder = $db->loadResult();
        $templateCssFiles = $this->getCssFiles('/templates/'.$templateFolder.'/html/mod_sclogin/themes/');

        $cssFiles = array_merge($mediaCssFiles, $templateCssFiles);

        $options = array();
        foreach ($cssFiles as $cssName=>$cssPath)
        {
            $options[] = HTMLHelper::_('select.option', $cssPath, ucfirst($cssName));
        }
        return $options;
    }

    protected function getLabel()
    {
        if (count($this->getOptions()) == 0)
            return "<label>There are no Themes available</label>";

        return parent::getLabel();
    }

    private function getCssFiles($folderPath)
    {
        $mediaCssFiles = array();
        $mediaFolder = JPATH_SITE . $folderPath;
        if(Folder::exists($mediaFolder))
        {
            $cssFiles = Folder::files($mediaFolder, '.css');
            if ($cssFiles && count($cssFiles) > 0)
            {
                foreach ($cssFiles as $file)
                {
                    $optionName = str_replace(".css", "", $file);

                    $mediaCssFiles[$optionName] = $file;
                }
            }
        }
        return $mediaCssFiles;
    }

}
