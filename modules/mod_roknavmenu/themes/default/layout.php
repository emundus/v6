<?php
/**
 * @version   $Id: layout.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
if(!class_exists('RokMavMenuDefaultLayout')){
    class RokMavMenuDefaultLayout extends AbstractRokMenuLayout
    {

        public function stageHeader()
        {

        }

        public function renderMenu(&$menu)
        {
            ob_start();
    ?>
    <ul class = "menu<?php echo $this->args['class_sfx']; ?>" <?php if ($this->args['tag_id'] != null): ?>id="<?php echo $this->args['tag_id']; ?>"<?php endif; ?>>
    <?php foreach ($menu->getChildren() as $item) : ?>
    <?php $this->renderItem($item, $menu); ?>
    <?php endforeach; ?>
    </ul>
    <?php
            return ob_get_clean();
        }

        protected function renderItem(JoomlaRokMenuNode &$item, &$menu)
        {
            ?>
            <li <?php if ($item->hasListItemClasses()) : ?>class="<?php echo $item->getListItemClasses(); ?>"<?php endif; ?> <?php if (null != $item->getCssId()): ?>id="<?php echo $item->getCssId(); ?>"<?php endif; ?>>
            <?php if ($item->getType() == 'menuitem') : ?>
            <a <?php if ($item->hasLinkClasses()): ?>class="<?php echo $item->getLinkClasses(); ?>"<?php endif; ?> <?php if ($item->hasLink()): ?>href="<?php echo $item->getLink(); ?>"<?php endif; ?> <?php if (null != $item->getTarget()): ?>target="<?php echo $item->getTarget(); ?>"<?php endif; ?> <?php if ($item->hasLinkAttribs()): ?> <?php echo $item->getLinkAttribs(); ?><?php endif; ?>>
        <?php if (null != $item->getImage()): ?>
                    <img alt="<?php echo $item->getAlias();?>" src="<?php echo $item->getImage();?>"/><?php endif; ?>
            <span <?php if ($item->hasSpanClasses()): ?>class="<?php echo $item->getSpanClasses(); ?>"<?php endif; ?>><?php echo $item->getTitle();?></span>
                </a>
        <?php elseif ($item->getType() == 'separator') : ?>
            <span <?php if ($item->hasSpanClasses()): ?>class="<?php echo $item->getSpanClasses(); ?>"<?php endif; ?>><?php echo $item->getTitle();?></span>
        <?php endif; ?>
            <?php if ($item->hasChildren()): ?>
            <ul>
                    <?php foreach ($item->getChildren() as $child) : ?>
        <?php $this->renderItem($child, $menu); ?>
        <?php endforeach; ?>
            </ul>
        <?php endif; ?>
            </li>
            <?php

        }
    }
}



