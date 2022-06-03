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
        <div class="homepage-container__banner"
             <?php if (!empty($mod_em_homepage_banner_image)) : ?>
             style="background-image:url('<?= $mod_em_homepage_banner_image; ?>');"
             <?php else : ?>
             style="background:black;"
             <?php endif ?>
        >
            <h1><?= $mod_em_homepage_banner_text; ?></h1>
        </div>

        <!-- Barre de recherche-->
        <div class="homepage-container__filter">
            <form method="post" style="margin: 0">
                <div>
                    <?php if (in_array('keywords',$mod_em_homepage_filters_display)) : ?>
                        <input type="text" name="search" placeholder="Rechercher..." />
                    <?php endif; ?>
                    <?php if (in_array('category',$mod_em_homepage_filters_display)) : ?>
                        <select name="category">
                            <option>Veuillez sélectionner</option>
                        </select>
                    <?php endif; ?>
                    <?php if (in_array('program',$mod_em_homepage_filters_display)) : ?>
                        <select name="program">
                            <option>Veuillez sélectionner</option>
                        </select>
                    <?php endif; ?>
                    <?php if (in_array('state',$mod_em_homepage_filters_display)) : ?>
                        <select name="state">
                            <option>Veuillez sélectionner</option>
                        </select>
                    <?php endif; ?>
                    <button class="btn btn-primary homepage-search_button">
                        <span class="material-icons">search</span>
                    </button>
                </div>
            </form>
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

    <div id="homepage-container__campaign" class="homepage-container__campaign"></div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function(event) {
        getCampaigns()
    });

    function getCampaigns(){
        xhrRequest('index.php?option=com_emundus&controller=guest&task=getallcampaign').then((response) => {
            console.log(response.data);
            response.data.forEach((campaign) => {
                let text = '<div class="homepage-container__campaign-block">' +
                    '<p class="homepage-container__campaign-prog"></p>' +
                    '<h3 class="homepage-container__campaign-title">'+campaign.label+'</h3>' +
                    '<h4 class="homepage-container__campaign-prog-cat"></h4>' +
                    '<p class="homepage-container__campaign-text"></p>' +
                    '<p class="homepage-container__campaign-text"></p>' +
                    '<p class="homepage-container__campaign-text"></p>' +
                    '</div>';
                document.getElementById('homepage-container__campaign').insertAdjacentHTML('afterend',text);
            });
        })
    }

    function xhrRequest(url, body = null, method = 'GET') {
        const acceptedStatus = [200, 201, 202, 203, 204, 205, 206];

        return new Promise(function(resolve, reject) {
            const xhr = new XMLHttpRequest();

            xhr.open(method, url);

            xhr.onload = function() {
                if (acceptedStatus.indexOf(xhr.status) !== -1) {
                    const result = JSON.parse(xhr.responseText);

                    resolve(result);
                } else {
                    reject(this.statusText);
                }
            }

            xhr.onerror = function() {
                reject(this.statusText);
            }

            xhr.send(body);
        });
    }
</script>
