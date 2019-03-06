<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundus_category_search
 * @copyright	Copyright (C) 2005 - 2018 eMundus. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>

<div class="em-category-search-module em-category-wide">
    <?php echo $heading; ?>
    <div class="em-themes">
        <?php foreach ($categories as $category) :?>
            <a href="<?php echo $search_page; ?>?category=<?php echo str_replace(['é','è','ê'],'e', html_entity_decode(mb_strtolower(str_replace(' ','-', $category->title)))); ?>&clearfilters=1" class="em-theme-<?php echo $category->color ?>" title="<?php echo $category->label; ?>"><?php echo $category->label; ?></a>
        <?php endforeach; ?>
    </div>
</div>
