<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_cifre_suggestions
 * @copyright	Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<div class="profil-container" id="em-suggestions-module">

    <?= $intro; ?>

    <?php foreach ($offers as $key => $offer) :?>

        <?php if ($key%2 === 0) :?>
            <div class="column-card-container w-row">
        <?php endif; ?>

                <div class="w-col w-col-6">
                    <div>
                        <div class="card-offre" id="<?= $offer->fnum; ?>">
                            <div class="text-block-2"><?= (!empty($offer->titre))?$offer->titre:JText::_('NO_TITLE'); ?></div>
                            <div class="div-block margin">
                                <img src="https://assets.website-files.com/5e9eea59278d0a02df79f6bd/5e9ef4873152d535b204da4b_Twiice%20-%20Plan%20de%20travail%201.svg" alt="" class="image">
                                <div class="name">
                                    <?= $offer->profile; ?>
                                </div>
                            </div>
                            <div class="div-block">
                                <img src="https://assets.website-files.com/5e9eea59278d0a02df79f6bd/5e9f6bfa9fb16576de7aa78d_5e9ef4871565a65129befc4c_Twiice2-%20Plan%20de%20travail%201.svg" alt="" class="image">
                                <div class="name">
                                    <?= $offer->search; ?>
                                </div>
                            </div>
                            <div class="div-block-copy">
                                <div class="text-block-2-copy">Thématiques</div>
                                <div class="name">
                                    <?= $offer->themes; ?>
                                </div>
                            </div>
                            <div class="div-block-copy">
                                <div class="text-block-2-copy">Département</div>
                                <div class="name">
                                    <?= (empty($offer->department))?JText::_('COM_EMUNDUS_FABRIK_ALL_DEPARTMANTS'):$offer->department; ?>
                                </div>
                            </div>
                        </div>
                        <a href="<?= JRoute::_(JURI::base()."consultez-les-offres/details/299/".$offer->search_engine_page); ?>" class="cta-offre w-inline-block">
                            <div class="text-block-2"><?= JText::_('MOD_EMUNDUS_CIFRE_SUGGESTIONS_VIEW'); ?></div>
                        </a>
                    </div>
                </div>

            <?php if ($key === (count($offers) - 1)) :?>
                <!-- Final card offering to search for more. -->
                <?php if ($key%2 !== 0) :?>
                    </div>
                    <div class="column-card-container w-row">
                <?php endif; ?>

                    <div class="w-col w-col-6">
                        <div>
                            <a href="consultez-les-offres" class="voir-toutes-les-offres w-inline-block">
                                <div class="voir-offres">Voir toutes les offres</div>
                            </a>
                        </div>
                    </div>

                <?php if ($key%2 === 0) :?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        <?php if ($key%2 !== 0) :?>
            </div>
        <?php endif; ?>

    <?php endforeach; ?>
</div>
