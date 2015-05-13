<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2014 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v4.3.0
 * @build-date      2015/03/19
 */

defined('_JEXEC') or die('Restricted access');

if (!class_exists('JFBCFactory'))
    return;

$loginButtons = $helper->getLoginButtons($orientation, $alignment);

if ($loginButtons != '')
{
    $introText = JText::_('MOD_SCLOGIN_SOCIAL_INTRO_TEXT_LABEL');

    echo '<div class="sclogin-social-login '.$socialSpan . ' ' . $layout . ' ' . $orientation.'">';
    if($introText)
        echo '<span class="sclogin-social-intro '.$socialSpan.'">'.$introText.'</span>';
    echo $loginButtons;
    echo '</div>';
}