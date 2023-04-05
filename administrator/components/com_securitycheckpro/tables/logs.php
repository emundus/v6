<?php
/**
 * Securitycheck Pro table class
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Logs Table class
 */
class TableLogs extends JTable
{
    /**
     * Primary Key
     *
     * @var int
     */
    var $id = null;

    /**
     * @var string
     */
    var $ip = null;
    var $time = null;
    var $tag_description = null;
    var $description = null;
    var $type = null;
    var $uri = null;
    var $marked = 0;

    /**
     * Constructor
     *
     * @param object Database connector object
     */
    function TableLogs(&$db)
    {
        parent::__construct('#__securitycheckpro_logs', 'id', $db);
    }
}
