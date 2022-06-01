<?php
/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
?>
<div class="homepage-container">

        <!-- Bannière-->
        <div class="homepage-container__banner" style="background-image:url('<?= $mod_em_homepage_banner_image; ?>');">
            <h1><?= $mod_em_homepage_banner_text; ?></h1>
        </div>

        <!-- Barre de recherche-->
        <div class="homepage-container__filter">

        </div>

        <!-- Texte d'introduction -->
        <div class="homepage-container__intro">
            <?= $mod_em_homepage_introtext_text; ?>
        </div>

        <!-- Liste des campagnes-->
    <div class="homepage-container__campaigns">
     <div class="homepage-container__campaign--pinned">
            <!-- Campagne à la une-->
            <div class="homepage-container__campaign-block">

                <p class="homepage-container__campaign-prog">Admissions en 4ème  année</p>

                <h3 class="homepage-container__campaign-title">Cycle ingénieur – 2ème année</h3>

                <h4 class="homepage-container__campaign-prog-cat">Voie par alternance</h4>

                <p class="homepage-container__campaign-text">Clôture le 31/03/2022 à 13h59</p>
                <p class="homepage-container__campaign-text">Fuseau horaire Europe/Pari</p>
                <div>
                    <p>Candidatez en :</p>
                    <ul>
                        <li>4ème année du Diplôme d'ingénieurs BAC+5 (CTI). Campus de Laval et Paris/Ivry-sur-Seine,</li>
                        <li>4ème année en section internationale du Diplôme d'ingénieurs BAC+5 (CTI). Campus Paris/Ivry-sur-Seine uniquement.</li>
                    </ul>
                    <p>Vous pourrez classer par ordre de préférence les majeures suivantes : Cybersécurité, Systèmes Embarqués et Autonomes, Réalité Virtuelle et Systèmes Immersifs, Intelligence Artificielle & Data Science, Software Engineering.</p>
                </div>


            </div>
     </div>

    <div class="homepage-container__campaign">

            <!-- Campagnes -->

            <div class="homepage-container__campaign-block">
                <p class="homepage-container__campaign-prog"></p>

                <h3 class="homepage-container__campaign-title">Campagne 2</h3>

                <h4 class="homepage-container__campaign-prog-cat"></h4>

                <p class="homepage-container__campaign-text"></p>
                <p class="homepage-container__campaign-text"></p>
                <p class="homepage-container__campaign-text"></p>

            </div>

            <div class="homepage-container__campaign-block">

                <p class="homepage-container__campaign-prog"></p>

                <h3 class="homepage-container__campaign-title">Campagne 3</h3>

                <h4 class="homepage-container__campaign-prog-cat"></h4>

                <p class="homepage-container__campaign-text"></p>
                <p class="homepage-container__campaign-text"></p>
                <p class="homepage-container__campaign-text"></p>

            </div>

            <div class="homepage-container__campaign-block">

                <p class="homepage-container__campaign-prog"></p>

                <h3 class="homepage-container__campaign-title">Campagne 4</h3>

                <h4 class="homepage-container__campaign-prog-cat"></h4>

                <p class="homepage-container__campaign-text"></p>
                <p class="homepage-container__campaign-text"></p>
                <p class="homepage-container__campaign-text"></p>

            </div>

        </div>
    </div>

</div>
