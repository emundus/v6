<?php
/**
 * @package     FaLang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die;

jimport('joomla.html.pane');

/**
 * Method to translate with a pre_reg function call
 *
 * @param array $matches
 * @return string
 */
function jfTranslate($matches){
	$translation = '!!!' .JText::_($matches[1]);
	return $translation;
}


/**
 * HTML Abstract View class for the Falang never used directly
 *
 * @since 2.0
 */

require_once JPATH_ROOT.'/administrator/components/com_falang/legacy/view.php';


class FalangViewDefault extends LegacyView {

	public $showVersion = true;

    protected $state;

    public function __construct($config = null)
	{
		parent::__construct($config);
		$this->_addPath('template', $this->_basePath.DS.'views'.DS.'default'.DS.'tmpl');
	}

	public function display($tpl=null)
	{
		$document = JFactory::getDocument();
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

		$this->assign('showMessage',	$showMessage);
		$this->assignRef('state',		$this->state);

		JHTML::_('behavior.tooltip');
		parent::display($tpl);

		$this->versionInfo();
	}

	public function versionInfo(){
		if ($this->showVersion){
		$version = new FalangVersion();
		?><div align="center"><span class="smallgrey">Falang Version <?php echo $version->getVersionFull() .', '. $version->getCopyright();?> Copyright by <a href="http://www.faboba.com" target="_blank" class="smallgrey">Faboba</a> </span></div>
	<?php		
		}
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
		JHTML::stylesheet( 'hidesubmenu.css', 'administrator/components/com_falang/assets/css/' );
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
		JHTML::_('behavior.modal');
		
/*		
		SqueezeBox.initialize({});
		SqueezeBox.setOptions(SqueezeBox.presets,{'handler': 'iframe','size': {'x': 1000, 'y': 600},'closeWithOverlay': 0});
		SqueezeBox.url = target;

		SqueezeBox.setContent('iframe', SqueezeBox.url );*/
	}
}
?>
