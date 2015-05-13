<?php
/**
* @version   $Id: layout.php 26100 2015-01-27 14:16:12Z james $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
*
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class GantryDropdownLayout extends AbstractRokMenuLayout
{
    protected $theme_path;
    protected $params;
	static $jsLoaded = false;

    private $activeid;

    public function __construct(&$args)
    {
        parent::__construct($args);

		global $gantry;
        $theme_rel_path = "/html/mod_roknavmenu/themes/gantry-dropdown";
        $this->theme_path = $gantry->templatePath . $theme_rel_path;
        $this->args['theme_path'] = $this->theme_path;
        $this->args['theme_rel_path'] = $gantry->templateUrl. $theme_rel_path;
        $this->args['theme_url'] = $this->args['theme_rel_path'];
        $this->args['responsive-menu'] = $args['responsive-menu'];
    }

    public function stageHeader()
    {
		global $gantry;


		JHtml::_('behavior.framework', true);
        if (!self::$jsLoaded && $gantry->get('layout-mode', 'responsive') == 'responsive'){
            if (!($gantry->browser->name == 'ie' && $gantry->browser->shortver < 9)){
                $gantry->addScript($gantry->baseUrl . 'modules/mod_roknavmenu/themes/default/js/rokmediaqueries.js');
                $gantry->addScript($gantry->baseUrl . 'modules/mod_roknavmenu/themes/default/js/responsive.js');
                if ($this->args['responsive-menu'] == 'selectbox') $gantry->addScript($gantry->baseUrl . 'modules/mod_roknavmenu/themes/default/js/responsive-selectbox.js');
            }
            self::$jsLoaded = true;
        }
		$gantry->addLess('menu.less', 'menu.css', 1, array('menustyle'=>$gantry->get('menustyle','light'), 'menuHoverColor'=>$gantry->get('linkcolor'), 'menuDropBack'=>$gantry->get('accentcolor')));

         if ($gantry->get('layout-mode', 'responsive') == 'responsive'){
            $gantry->addLess('menu-responsive.less', 'menu-responsive.css', 1, array('menustyle'=>$gantry->get('menustyle','light'), 'menuHoverColor'=>$gantry->get('linkcolor'), 'menuDropBack'=>$gantry->get('accentcolor')));
        }

        // no media queries for IE8 so we compile and load the hovers
        if ($gantry->browser->name == 'ie' && $gantry->browser->shortver < 9){
            $gantry->addLess('menu-hovers.less', 'menu-hovers.css', 1, array('menustyle'=>$gantry->get('menustyle','light'), 'menuHoverColor'=>$gantry->get('linkcolor'), 'menuDropBack'=>$gantry->get('accentcolor')));
        }
    }

    protected function renderItem(JoomlaRokMenuNode &$item, RokMenuNodeTree &$menu)
    {

		global $gantry;

        $wrapper_css = '';

        $item_params = $item->getParams();

        //add default link class
        $item->addLinkClass('item');

        $dropdown_width = intval(trim($item_params->get('dropdown_dropdown_width')));

        if ($dropdown_width == 0) $dropdown_width = 180;

        $wrapper_css = ' style="width:'.$dropdown_width.'px;"';

        if ($item->getType() != 'menuitem') {
            $item->setLink('javascript:void(0);');
        }

        ?>
        <li <?php if($item->hasListItemClasses()) : ?>class="<?php echo $item->getListItemClasses()?>"<?php endif;?> <?php if($item->hasCssId() && $this->activeid):?>id="<?php echo $item->getCssId();?>"<?php endif;?>>

            <a <?php if($item->hasLinkClasses()):?>class="<?php echo $item->getLinkClasses();?>"<?php endif;?> <?php if($item->hasLink()):?>href="<?php echo $item->getLink();?>"<?php endif;?> <?php if($item->hasTarget()):?>target="<?php echo $item->getTarget();?>"<?php endif;?> <?php if ($item->hasAttribute('onclick')): ?>onclick="<?php echo $item->getAttribute('onclick'); ?>"<?php endif; ?><?php if ($item->hasLinkAttribs()): ?> <?php echo $item->getLinkAttribs(); ?><?php endif; ?>>
                <?php echo $item->getTitle(); ?>
            </a>

            <?php if ($item->hasChildren()): ?>
			<div class="dropdown <?php if ($item->getLevel() > 0) echo 'flyout '; ?>" <?php echo $wrapper_css; ?>>
				<div class="column">
					<ul class="level<?php echo intval($item->getLevel())+2; ?>">
					<?php foreach ($item->getChildren() as $child) : ?>
						<?php $this->renderItem($child, $menu); ?>
					<?php endforeach; ?>
					</ul>
				</div>
			</div>
            <?php endif; ?> 
        </li>
        <?php
    }

    public function renderMenu(&$menu) {
        ob_start();
?>
<div class="gf-menu-device-container"></div>
<ul class="gf-menu l1 " <?php if (array_key_exists('tag_id',$this->args)): ?>id="<?php echo $this->args['tag_id'];?>"<?php endif;?>>
    <?php foreach ($menu->getChildren() as $item) : ?>
        <?php $this->renderItem($item, $menu); ?>
    <?php endforeach; ?>
</ul>
<?php
        return ob_get_clean();
    }
}
