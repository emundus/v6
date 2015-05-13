<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsViewImportexports extends F0FViewHtml
{
    protected function onDisplay($tpl = null)
    {
        // I won't interact with the database
        return true;
    }
}