
<?php
/**
 * @package     eMundus.mediaVersion
 *
 * @author      Benjamin Retord
 * @copyright   Copyright (C) 2019 eMundus All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Set mediaVersion to 0
 *
 * @package  eMundus.mediaVersion
 */
class PlgSystemMediaVersion extends JPlugin {

    public function onAfterRoute()
    {
        $document = JFactory::getDocument();
        $document->setMediaVersion(0);
    }
}