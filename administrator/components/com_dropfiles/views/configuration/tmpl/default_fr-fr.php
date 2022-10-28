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
<h4 class="support-title">Vous avez besoin d'aide?</h4>
<div class="support-container">
    <p>
        Veuillez lire dans un premier temps la documentation disponible dans
        <a href="https://www.joomunited.com/support/joomla-documentation/?extension=dropfiles" target="_blank">la section detéléchargement</a>
        Vous pouvez aussi lire la
        <a href="https://www.joomunited.com/support/joomla-documentation/?extension=dropfiles#faq" target="_blank">FAQ</a>
    </p>

    <h4>Vous n'avez trouvé de réponse à votre problème?</h4>
    <p>
        Vous pouvez contacter notre support grâce à notre :
        <a href="http://www.joomunited.com/support/ticket-support" target="_blank">système de ticket</a>
    </p>

    <h4>Veuillez nous fournir les informations suivantes à l'ouverture du ticket :</h4>


    <p><i>Version de Joomla : </i><?php echo DropfilesBase::getJoomlaVersion(); ?></p>

    <p>
        <i>Version de Dropfiles : </i><?php echo DropfilesBase::getExtensionVersion('com_dropfiles'); ?><br/>
    </p>

    <p>
        <i>Version de Php : </i><?php echo phpversion(); ?><br/>
    </p>

    <h4>Gagnez du temps pour la résolution de votre problème</h4>
    <p>
        Nous aurons sûrement besoin d'un accès administrateur à votre site web, pouvez vous nous fournir le <i>nom
            d'utilisateur</i>, le <i>mot de passe</i> et <i>l'adresse de votre site</i><br/>
        Décrivez au maximum le problème que vous rencontrez.<br/>
        Vous pouvez attacher des copies d'écran à votre ticket, cela aide à comprendre au mieux votre problème.
    </p>
</div>
