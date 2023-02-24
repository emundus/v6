<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

if(!$helper->isJFBConnectInstalled)
    return;

$loginButtons = $helper->getLoginButtons($orientation, $alignment);

if ($loginButtons != '')
{
    $introText = Text::_('MOD_SCLOGIN_SOCIAL_INTRO_TEXT_LABEL');
    $postText = Text::_('MOD_SCLOGIN_SOCIAL_POST_TEXT_LABEL');

    echo '<div class="sclogin-social-login '.$socialSpan . ' ' . $layout . ' ' . $orientation.'">';
    if($introText)
        echo '<span class="sclogin-social-intro '.$socialSpan.'">'.$introText.'</span>';
    echo $loginButtons;
    if($postText)
        echo '<span class="sclogin-social-post-text '.$socialSpan.'">'.$postText.'</span>';
    echo '</div>';
}