<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

//namespace Joomla\Component\Falang\Administrator\View;

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;


jimport('joomla.html.pane');

/**
 * HTML Abstract View class for the Falang never used directly
 *
 * @since 2.0
 */

class FalangViewDefault extends BaseHtmlView {

    protected $state;

	public function display($tpl=null)
	{
	    $document = Factory::getDocument();
		$document->addStyleSheet(JURI::base().'components/com_falang/assets/css/falang.css');
		
		// Get data from the model
		$this->state		= $this->get('State');
		// Are there messages to display ?
		$showMessage	= false;
		$message = $this->get('message');

		if ( is_object($this->state) )
		{
			$message1		= $this->state->get('message') == null ? $message : $this->state->get('message');
            $this->state->set('message', $message1);
			$message2		= $this->state->get('extension.message');
			$showMessage	= ( $message1 || $message2 );
		}

		$this->showMessage = $showMessage;

		parent::display($tpl);

		$this->footer();
	}

	public function footer()
	{
		$version = new FalangVersion();
		?>
		<div class="falang_footer">
		<?php if ($version->_versiontype == 'free')
		{ ?>
			<div class="alert alert-warning" style="padding: 15px">
				<p>
					<?php echo JText::_('COM_FALANG_FREE_VERSION_FOOTER_MSG'); ?>
				</p>
				<a class="btn btn-danger" target="_blank"
				   href="https://www.faboba.com/composants/falang/donwload.html?utm_source=Joomla&utm_medium=upgradebutton&utm_campaign=freeversion">
					<span class="icon-heart"></span><?php echo JText::_('COM_FALANG_FREE_VERSION_FOOTER_BTN_LABEL'); ?>
				</a>
			</div>
		<?php } ?>
			Falang <?php echo $version->getVersionFull(); ?>
			<br />
			<div class="footer_review">
				<?php echo JText::_('COM_FALANG_FOOTER_LIKE_MSG'); ?><a href="https://extensions.joomla.org/extension/falang/" target="_blank"><?php echo JText::_('COM_FALANG_FOOTER_LEAVE_MSG'); ?></a>
				<a class="stars" href="https://extensions.joomla.org/extension/falang/" target="_blank">
					<span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span>
				</a>
			</div>
			<br />
			&copy; 2021 - faboba.com All right Reserved
			<p></p>
		</div>
		<?php
	}

	
	/** 
	 * Returns the path of the help file to be included as output for the page
	 * The path is used as include statement within the view template  
	 */
	protected function getHelpPathL($ref) {
		$lang = JFactory::getLanguage();
		if (!preg_match( '#\.html$#i', $ref )) {
			$ref = $ref . '.html';
		}

		$url = 'components/com_falang/help';
		$tag =  $lang->getTag();

		// Check if the file exists within a different language!
		if( $lang->getTag() != 'en-GB' ) {
			$localeURL = JPATH_BASE.DS.$url.DS.$tag.DS.$ref;
			jimport( 'joomla.filesystem.file' );
			if( !JFile::exists( $localeURL ) ) {
				$tag = 'en-GB';
			}
		}
		return $url.'/'.$tag.'/'.$ref;
		
	}
	
	/**
	 * Routine to hide submenu suing CSS since there are no paramaters for doing so without hiding the main menu
	 *
	 */
	protected function _hideSubmenu(){
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::base().'components/com_falang/assets/css/hidesubmenu.css');
	}

	 /**
	 * This method creates a standard cpanel button for joomla before 3.0
	 *
	 * @param string $link
	 * @param string $image
	 * @param string $text
	 * @param string $path
	 * @param string $target
	 * @param string $onclick
	 * @access protected
	 */
	 protected function _quickiconButton( $link, $image, $text, $path=null, $target='', $onclick='' ) {
	 	if( $target != '' ) {
	 		$target = 'target="' .$target. '"';
	 	}
	 	if( $onclick != '' ) {
	 		$onclick = 'onclick="' .$onclick. '"';
	 	}
	 	if( $path === null || $path === '' ) {
	 		$path = 'components/com_falang/assets/images/';
	 	}

         ?>
        <div class="icon">
            <a href="<?php echo $link; ?>" <?php echo $target;?>  <?php echo $onclick;?>>
                <?php echo JHTML::_('image.administrator', $image, $path, NULL, NULL, $text ); ?>
                <span><?php echo $text; ?></span>
            </a>
        </div>
		<?php
	 }
	 
	/**
	 * Method to use a tooltip independ from JElements
	 *
	 * @param string $label	title of the lable
	 * @param string $description	of the lable
	 * @param string $control_name	name of the control the lable is related to
	 * @param string $name	of the control
	 * @return string
	 */
	protected function fetchTooltip($label, $description, $control_name='', $name='')
	{
		$output = '<label id="'.$control_name.$name.'-lbl" for="'.$control_name.$name.'"';
		if ($description) {
			$output .= ' class="hasTip" title="'.JText::_($label).'::'.JText::_($description).'">';
		} else {
			$output .= '>';
		}
		$output .= JText::_( $label ).'</label>';

		return $output;
	}
	
	private function showSplashInfo() {
        HTMLHelper::_('bootstrap.renderModal');

/*		
		SqueezeBox.initialize({});
		SqueezeBox.setOptions(SqueezeBox.presets,{'handler': 'iframe','size': {'x': 1000, 'y': 600},'closeWithOverlay': 0});
		SqueezeBox.url = target;

		SqueezeBox.setContent('iframe', SqueezeBox.url );*/
	}
}

