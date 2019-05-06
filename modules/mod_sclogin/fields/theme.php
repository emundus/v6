<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2019 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v8.0.5
 * @build-date      2019/01/14
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.helper');
jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

class JFormFieldTheme extends JFormFieldList
{
    public $type = 'Theme';

    protected function getOptions()
    {
        $mediaCssFiles = $this->getCssFiles('/media/sourcecoast/themes/sclogin/');

        $db = JFactory::getDBO();
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
            $options[] = JHtml::_('select.option', $cssPath, ucfirst($cssName));
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
        if(JFolder::exists($mediaFolder))
        {
            $cssFiles = JFolder::files($mediaFolder, '.css');
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
