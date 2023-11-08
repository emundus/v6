<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
?>
<div class="container-intro-map em-mb-32">
    <h2>Catalogue des activités</h2>
    <div class="em-flex-row eb-icon-links">
        <a class="eb-list-icon" href="activites-etudiantes-listes" target="_self">
            <span class="material-icons">list</span>
        </a>
        <a class="eb-list-icon em-ml-8" href="accueil-activites" target="_self">
            <span class="material-icons">apps</span>
        </a>
        <a class="eb-map-icon em-ml-8" href="chercher-par-localisation" target="_self">
            <span class="material-icons">map</span>
        </a>
    </div>
</div>
<div id="map<?php echo $module->id; ?>"
     style="position:relative; z-index:0; border-radius:7px; width: <?php echo $width; ?>%; height: <?php echo $height ?>px"></div>

<script type="text/javascript">
    jQuery(document).ready(function () {
        console.log('here');
        if (typeof document.getElementsByClassName('em-map')[0] != 'undefined') {
            document.getElementById('eb-category-page-columns').append(document.getElementsByClassName('em-map')[0]);
        }
    });
</script>


