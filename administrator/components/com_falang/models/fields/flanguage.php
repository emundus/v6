<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2021. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Component\ComponentHelper;


class JFormFieldFlanguage extends ListField
{

    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = 'flanguage';

    protected function getOptions()
    {

        $client = 'administrator';

        // Make sure the languages are sorted base on locale instead of random sorting
        $languages = LanguageHelper::createLanguageList($this->value, constant('JPATH_' . strtoupper($client)), true, false);
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
        $defaultLanguage = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

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