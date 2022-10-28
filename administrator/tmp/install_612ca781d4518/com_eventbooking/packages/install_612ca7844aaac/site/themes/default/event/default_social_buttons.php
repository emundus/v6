<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (empty($this->config->social_sharing_buttons))
{
	$shareOptions = array(
		'Facebook',
		'Twitter',
		'LinkedIn',
		'Delicious',
		'Digg',
        'Pinterest',
	);
}
else
{
	$shareOptions = explode(',', $this->config->social_sharing_buttons);
}

$title = $this->item->title;
?>
<div class="eb-social-sharing-buttons" class="row-fluid">
    <?php
    if (in_array('Facebook', $shareOptions))
    {
	    $alt = Text::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'FaceBook');

	    echo '<a href="https://www.facebook.com/sharer.php?u=' . rawurlencode($socialUrl) . '&amp;t=' . rawurlencode($title) . '" title="' . $alt . '" class="btn eb-btn-facebook" target="blank" >
	                <i class="fa fa-facebook-square"></i>                        
             </a>';
    }

    if (in_array('Twitter', $shareOptions))
    {
	    $alt     = Text::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Twitter');

	    echo '<a href="https://twitter.com/?status=' . rawurlencode($title . " " . $socialUrl) . '" title="' . $alt . '" class="btn eb-btn-twitter" target="blank" >
                  <i class="fa fa-twitter-square"></i>  
              </a>';
    }
    
    if (in_array('LinkedIn', $shareOptions))
    {
	    $alt     = Text::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'LinkedIn');

	    echo '<a href="https://www.linkedin.com/shareArticle?mini=true&amp;url=' . rawurlencode($socialUrl) . '&amp;title=' . $title . '" title="' . $alt . '" target="blank" class="btn eb-btn-linkedin">
                   <i class="fa fa-linkedin-square"></i>       
              </a>';
    }

    if (in_array('Delicious', $shareOptions))
    {
	    $alt     = Text::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Delicious');

	    echo '<a href="https://del.icio.us/post?url=' . rawurlencode($socialUrl) . '&amp;title=' . $title . '" title="' . $alt . '" target="blank" class="btn eb-btn-delicious">
                   <i class="fa fa-delicious"></i>       
              </a>';
    }


    if (in_array('Digg', $shareOptions))
    {
	    $alt     = Text::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Digg');

	    echo '<a href="https://digg.com/submit?url=' . rawurlencode($socialUrl) . '&amp;title=' . $title . '" title="' . $alt . '" target="blank" class="btn eb-btn-digg">
                   <i class="fa fa-digg"></i>       
              </a>';
    }

    if (in_array('Pinterest', $shareOptions))
    {
	    $alt     = Text::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Pinterest');

	    echo '<a href="https://www.pinterest.com/pin/create/button/?url=' . rawurlencode($socialUrl) . '" title="' . $alt . '" target="blank" class="btn eb-btn-pinterest">
                   <i class="fa fa-pinterest-square"></i>       
              </a>';
    }
    ?>
</div>