<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2014 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access.
defined('_JEXEC') || die;
?>
<h4 class="support-title">You need support?</h4>
<div class="support-container">
    <p>
        Please read first the documentation available in the
        <a href="https://www.joomunited.com/support/joomla-documentation/?extension=dropfiles" target="_blank">
            online documentation
        </a>
        <br/>
        Then you can read the <a href="https://www.joomunited.com/support/joomla-documentation/?extension=dropfiles#faq" target="_blank">FAQ</a>
    </p>

    <h4>You still have a problem?</h4>
    <p>
        You can contact our support team here :
        <a href="http://www.joomunited.com/support/ticket-support" target="_blank">Support</a>
    </p>

    <h4>Please give use this informations when you open a new ticket :</h4>


    <p><i>Joomla version : </i><?php echo DropfilesBase::getJoomlaVersion(); ?></p>

    <p>
        <i>Dropfiles version : </i><?php echo DropfilesBase::getExtensionVersion('com_dropfiles'); ?><br/>
    </p>

    <p>
        <i>Php version : </i><?php echo phpversion(); ?><br/>
    </p>

    <h4>Save time in your ticket resolution</h4>
    <p>
        We may need an admin access to your website, in order to save time you can create an admin access an give us the
        <i>username</i>,
        the <i>password</i> and the <i>site url</i><br/>
        Be as precise as possible in your explanations.<br/>
        You can add screenshots to your ticket, it always helps to understand your problem.
    </p>
</div>
