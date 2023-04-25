<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2021. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Component\ComponentHelper;

class JFormFieldFtables extends ListField
{

    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = 'ftables';

    public function getOptions() {
        $options = array();

        $options = array();
	    $ie_list = ComponentHelper::getParams('com_falang')->get('ie_list', '');
	    $values = explode("\r\n",$ie_list);
	    foreach( $values as  $l ){
	    	$tables = explode(",",$l);
		    if (isset($tables[0]) && isset($tables[1])){
			    $options[] = array("value" => $tables[0], "text" => $tables[1] );
		    }
	    }

//        // Merge any additional options in the XML definition.
//        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }

}