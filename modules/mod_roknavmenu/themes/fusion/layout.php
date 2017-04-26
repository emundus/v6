<?php
/**
 * @version   $Id: layout.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!class_exists('RokMavMenuFusionLayout')){
    class RokMavMenuFusionLayout extends AbstractRokMenuLayout
    {
        protected $theme_path;
        protected $params;

        public function __construct(&$args)
        {
            parent::__construct($args);

            $this->theme_path = JPATH_SITE . "/modules/mod_roknavmenu/themes/fusion";
            $this->args['theme_path'] = $this->theme_path;
            $this->args['theme_rel_path'] = JURI::root(true) . str_replace(JPATH_SITE, '', $this->theme_path);
            $this->args['theme_url'] = $this->args['theme_rel_path'];
        }

        public function stageHeader()
        {
            if ($this->args['roknavmenu_fusion_effect'] == 'slidefade') $this->args['roknavmenu_fusion_effect'] = "slide and fade";
            $this->addScript('js/sfhover.js');

            if ($this->browser->name == "ie" && $this->args['roknavmenu_fusion_effect'] == 'slide and fade') $this->args['roknavmenu_fusion_effect'] = "slide";

            if ($this->args['roknavmenu_fusion_enable_js']) {
                JHtml::_('behavior.framework', true);
                $this->addScript('js/fusion.js');
                ob_start();
                ?>
                window.addEvent('domready', function() {
                new Fusion('ul.menutop', {
                pill: <?php echo $this->args['roknavmenu_fusion_pill']; ?>,
                effect: '<?php echo $this->args['roknavmenu_fusion_effect']; ?>',
                opacity:  <?php echo $this->args['roknavmenu_fusion_opacity']; ?>,
                hideDelay:  <?php echo $this->args['roknavmenu_fusion_hidedelay']; ?>,
                centered:  <?php echo $this->args['roknavmenu_fusion_centeredOffset']; ?>,
                tweakInitial: {'x': <?php echo $this->args['roknavmenu_fusion_tweakInitial_x']; ?>, 'y': <?php echo $this->args['roknavmenu_fusion_tweakInitial_y']; ?>},
                tweakSubsequent: {'x':  <?php echo $this->args['roknavmenu_fusion_tweakSubsequent_x']; ?>, 'y':  <?php echo $this->args['roknavmenu_fusion_tweakSubsequent_y']; ?>},
                tweakSizes: {'width': <?php echo $this->args['roknavmenu_fusion_tweak-width']; ?>, 'height': <?php echo $this->args['roknavmenu_fusion_tweak-height']; ?>},
                menuFx: {duration:  <?php echo $this->args['roknavmenu_fusion_menu_duration']; ?>, transition: Fx.Transitions.<?php echo $this->args['roknavmenu_fusion_menu_animation']; ?>},
                pillFx: {duration:  <?php echo $this->args['roknavmenu_fusion_pill_duration']; ?>, transition: Fx.Transitions.<?php echo $this->args['roknavmenu_fusion_pill_animation']; ?>}
                });
                });
                <?php
                $inline = ob_get_clean();
                $this->appendInlineScript($inline);
            }
            if ($this->args['roknavmenu_fusion_load_css']) {
                $this->addStyle("css/fusion.css");
            }
        }

        protected function renderItem(JoomlaRokMenuNode &$item, RokMenuNodeTree &$menu)
        {
            ?>
            <li <?php if ($item->hasListItemClasses()) : ?>class="<?php echo $item->getListItemClasses(); ?>"<?php endif; ?> <?php if ($item->hasCssId() && $this->args['roknavmenu_fusion_enable_current_id']): ?>id="<?php echo $item->css_id; ?>"<?php endif; ?>>
            <?php if ($item->getType() == 'menuitem') : ?>
            <a <?php if ($item->hasLinkClasses()): ?>class="<?php echo $item->getLinkClasses(); ?>"<?php endif; ?> <?php if ($item->hasLink()): ?>href="<?php echo $item->getLink(); ?>"<?php endif; ?> <?php if ($item->hasTarget()): ?>target="<?php echo $item->getTarget(); ?>"<?php endif; ?> <?php if ($item->hasAttribute('onclick')): ?>onclick="<?php echo $item->getAttribute('onclick'); ?>"<?php endif; ?><?php if ($item->hasLinkAttribs()): ?> <?php echo $item->getLinkAttribs(); ?><?php endif; ?>>
        <?php if ($item->hasImage()): ?>
                <img alt="<?php echo $item->getAlias; ?>" src="<?php echo $item->getImage(); ?>"/><?php endif; ?>
            <span><?php echo $item->getTitle();?></span>
                </a>
        <?php elseif ($item->getType() == 'separator') : ?>
            <span <?php if ($item->hasLinkClasses()): ?>class="<?php echo $item->getLinkClasses(); ?> nolink"<?php endif; ?>>
                    <span><?php echo $item->getTitle();?></span>
                </span>
        <?php endif; ?>
            <?php if ($item->hasChildren()): ?>
            <ul class="level<?php echo intval($item->getLevel()) + 2; ?>">
                <?php foreach ($item->getChildren() as $child) : ?>
        <?php $this->renderItem($child, $menu); ?>
        <?php endforeach; ?>
                </ul>
        <?php endif; ?>
            </li>
            <?php

        }


        public function renderMenu(&$menu)
        {
            ob_start();
       ?>
    <div id="horizmenu-surround">
        <ul class="menutop level1" <?php if ($this->args['tag_id'] != null): ?>id="<?php echo$this->args['tag_id']; ?>"<?php endif; ?>>
            <?php foreach ($menu->getChildren() as $item) : ?>
    <?php $this->renderItem($item, $menu); ?>
    <?php endforeach; ?>
        </ul>
    </div>
    <?php
            return ob_get_clean();
        }
    }
}