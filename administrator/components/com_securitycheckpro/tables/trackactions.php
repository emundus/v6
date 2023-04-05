<?php
/**
 * Track Actions
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

/**
 * Userlogs Table class
 *
 * @since __DEPLOY_VERSION__
 */
class JTableTrackActions extends JTable
{
    /**
     * Constructor
     *
     * @param JDatabaseDriver &$db A database connector object
     *
     * @since __DEPLOY_VERSION__
     */
    public function __construct(&$db)
    {
        parent::__construct('#__securitycheckpro_trackactions', 'id', $db);
    }
}
