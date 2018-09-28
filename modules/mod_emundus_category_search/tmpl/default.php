<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_cifre_offers
 * @copyright	Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>

<div class="em-category-search-module">
    <h4>Les formations <br>
        par domaines de compétences
    </h4>
    <?php foreach ($categories as $category) :?>
        <div class="em-themes em-theme-<?php echo $category->color ?>"><a href="<?php echo $search_page; ?>?category=<?php echo str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace(' ','-', $category->title)))); ?>"><?php echo $category->label; ?></a></div>
    <?php endforeach; ?>

</div>
