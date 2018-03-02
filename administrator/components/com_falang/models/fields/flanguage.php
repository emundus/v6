<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldFlanguage extends JFormFieldList
{
    public $type = 'flanguage';

    protected function getOptions()
    {

        $client = 'administrator';

        // Make sure the languages are sorted base on locale instead of random sorting
        $languages = JLanguageHelper::createLanguageList($this->value, constant('JPATH_' . strtoupper($client)), true, false);
        if (count($languages) > 1)
        {
            usort(
                $languages,
                function ($a, $b)
                {
                    return strcmp($a["value"], $b["value"]);
                }
            );
        }

        //remove default language
        $defaultLanguage = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

        foreach ($languages as $key=>$language){
            if ($language['value'] == $defaultLanguage ){
                unset($languages[$key]);
            }
        }
        // Merge any additional options in the XML definition.
        $options = array_merge(
            parent::getOptions(),
            $languages
        );

        return $options;
    }

}