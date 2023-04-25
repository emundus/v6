<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.html.pane');

JLoader::import( 'views.default.view',FALANG_ADMINPATH);

/**
 * HTML View class for the Falang component
 *
 * @static
 * @package		Falang
 * @subpackage	Views
 * @since 1.0
 */
class CPanelViewCpanel extends FalangViewDefault
{
    protected $pluginsInfos;
	/**
	 * Control Panel display function
	 *
	 * @param template $tpl
	 */
	public function display($tpl = null)
	{

		//update downloadid
		$this->getModel()->updateDownloadId();
        $document = Factory::getDocument();
		$document->setTitle(JText::_('COM_FALANG_TITLE') . ' :: ' .JText::_('COM_FALANG_CONTROL_PANEL'));
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_('COM_FALANG_TITLE') .' :: '. JText::_( 'COM_FALANG_HEADER' ), 'falang' );
		JToolBarHelper::preferences('com_falang', '580', '750');
		JToolBarHelper::help( 'screen.cpanel', true);

//		JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTROL_PANEL'), 'index.php?option=com_falang', true);
//		JHtmlSidebar::addEntry(JText::_('COM_FALANG_TRANSLATION'), 'index.php?option=com_falang&amp;task=translate.overview');
//		JHtmlSidebar::addEntry(JText::_('COM_FALANG_ORPHANS'), 'index.php?option=com_falang&amp;task=translate.orphans');
//		JHtmlSidebar::addEntry(JText::_('COM_FALANG_CONTENT_ELEMENTS'), 'index.php?option=com_falang&amp;task=elements.show', false);
//		JHtmlSidebar::addEntry(JText::_('COM_FALANG_HELP_AND_HOWTO'), 'index.php?option=com_falang&amp;task=help.show', false);
//
//		$this->sidebar = JHtmlSidebar::render();

        $params = JComponentHelper::getParams('com_falang');
		$updateCaching = $params->get('update_caching',false);
        //if caching enalbed we don't force the update
        $updateInfo = LiveUpdate::getUpdateInformation(!$updateCaching);

        // Get current version available
        $this->currentVersion = $updateInfo->extInfo->version;

        //get latest version
        $this->latestVersion = $updateInfo->version;

		$version = new FalangVersion();
		$this->versionType = $version->getVersionType();

        $this->updateInfo = $updateInfo;

        $js = "var progress_msg = '".JText::_('COM_FALANG_CPANEL_CHECK_PROGRESS')."';";
        $document->addScriptDeclaration($js);
        $document->addScript('components/com_falang/assets/js/cpanel.js');

		//load font-awesome
		$document->addStyleSheet('https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');


		$this->showVersion = false;

		//check site default language in content langauge liste
		$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		$falangManager = FalangManager::getInstance();

		$languages = $falangManager->getLanguagesIndexedByCode(false);

		if (!array_key_exists($default_lang,$languages)){
			JFactory::getApplication()->enqueueMessage(JText::_('COM_FALANG_CPANEL_CONFIGURATION_DEFAULT_LANGUAGE_MISSING'),'error');
		}

		$this->pluginsInfos = $this->getPluginsInfos();

		parent::display($tpl);
	}


    protected function getPluginsInfos(){
	    $plugins = array(
		    //key use in the display table row and ajax
		    'joomlacontentsearch'     => array(
			    'name' => 'falangContent', //name is used to look in extension filter with folter too
			    'folder' => 'search',
			    'title'  => 'Joomla Content search',
			    'ars_id' => 41,
			    'type'   => 'free',
		    ),
		    'k2contentsearch'          => array(
			    'name' => 'falangK2',
			    'folder' => 'search',
			    'title'  => 'K2 Content search',
			    'ars_id' => 42,
			    'type'   => 'free',
		    ),
		    'falangmissingtranslation'     => array(
			    'name' => 'falangmissing',
			    'folder' => 'system',
			    'title'  => 'Missing Translation',
			    'ars_id' => 46,
			    'type'   => 'free',
		    ),
		    'falangextraparams' => array(
			    'name' => 'falangextraparams',
			    'folder' => 'system',
			    'title'  => 'Falang Extra Params',
			    'ars_id' => 47,
			    'type'   => 'paid',
		    ),
		    'k2extrafield'          => array(
			    'name' => 'falangk2',
			    'folder' => 'system',
			    'title'  => 'K2 extra field',
			    'ars_id' => 43,
			    'type'   => 'paid',
		    ),
		    'smartsearchcontent'     => array(
			    'name' => 'falangcontent',
			    'folder' => 'finder',
			    'title'  => 'Smart Search - Joomla Content',
			    'ars_id' => 109,
			    'type'   => 'paid',
		    ),
	    );

	    $db = JFactory::getDbo();
        foreach ($plugins as $plugin => $values){
            //search if installed
	        $query = $db->getQuery(true)
                    ->select('enabled,manifest_cache')
                    ->from($db->qn('#__extensions'))
                    ->where($db->qn('element').' = '.$db->q($plugin))
                    ->where($db->qn('folder').' = '.$db->q($values['folder']));
            $db->setQuery($query);
            $row = $db->loadObject();
            if (!isset($row)){
                $plugins[$plugin]['installed']= 0;
                continue;
            } else {
                $plugins[$plugin]['installed']= 1;
            }
            //plugin installed //get other info
            if ($row->enabled == 0) {
	            $plugins[$plugin]['enabled'] = 0;
            } else {
	            $plugins[$plugin]['enabled'] = 1;
            }
	        //get version
	        $json = json_decode($row->manifest_cache);
	        $plugins[$plugin]['version_local'] = $json->version;

        }
        return $plugins;
    }
    /**
	  * render News feed from Faboba-Falang portal
	  */
	 protected function renderJFNews() {
	 	
	 	$output = '';

		//  get RSS parsed object
		$options = array();
		$options['rssUrl']		= '';
		$options['cache_time']	= 86400;

		$rssDoc = JFactory::getXMLparser('RSS', $options);

		if ( $rssDoc == false ) {
			$output = JText::_('Error: Feed not retrieved');
		} else {	
			// channel header and link
			$title 	= $rssDoc->get_title();
			$link	= $rssDoc->get_link();
			
			$output = '<table class="adminlist">';
			$output .= '<tr><th colspan="3"><a href="'.$link.'" target="_blank">'.JText::_($title) .'</th></tr>';
			$output .= '<tr><td colspan="3">'.JText::_('NEWS_INTRODUCTION').'</td></tr>';
			
			$items = array_slice($rssDoc->get_items(), 0, 3);
			$numItems = count($items);
            if($numItems == 0) {
            	$output .= '<tr><th>' .JText::_('No news items found'). '</th></tr>';
            } else {
            	$k = 0;
                for( $j = 0; $j < $numItems; $j++ ) {
                    $item = $items[$j];
                	$output .= '<tr><td class="row' .$k. '">';
                	$output .= '<a href="' .$item->get_link(). '" target="_blank">' .$item->get_title(). '</a>';
					if($item->get_description()) {
	                	$description = $this->limitText($item->get_description(), 50);
						$output .= '<br />' .$description;
					}
                	$output .= '</td></tr>';
                }
            }
			$k = 1 - $k;
						
			$output .= '</table>';
		}	 	
	 	return $output;
	 }
	 
	 /**
	  * render content state information
	  */
	 function renderContentState() {
	 	$falangManager =  FalangManager::getInstance();
	 	$output = '';
		$alertContent = false;
		if( array_key_exists('unpublished', $this->contentInfo) && is_array($this->contentInfo['unpublished']) ) {
			$alertContent = true;
		}		
		ob_start();
		?>
		<table class="adminlist">
			<tr>
				<th><?php echo JText::_("UNPUBLISHED CONTENT ELEMENTS");?></th>
				<th style="text-align: center;"><?php echo JText::_("Language");?></th>
				<th style="text-align: center;"><?php echo JText::_("Publish");?></th>
			</tr>
			<?php
			$k=0;
			if( $alertContent ) {
				$curReftable = '';
				foreach ($this->contentInfo['unpublished'] as $ceInfo ) {
					$contentElement = $falangManager->getContentElement( $ceInfo['catid'] );

					// Trap for content elements that may have been removed
					if (is_null($contentElement)){
						$name = "<span style='font-style:italic'>".JText::sprintf("CONTENT_ELEMENT_MISSING",$ceInfo["reference_table"])."</span>";
					}
					else {
						$name = $contentElement->Name;
					}
					if ($ceInfo["reference_table"] != $curReftable){
						$curReftable = $ceInfo["reference_table"];
						$k=0;
						?>
			<tr><td colspan="3"><strong><?php echo $name;?></strong></td></tr>
						<?php
					}

					JLoader::import( 'models.ContentObject',FALANG_ADMINPATH);
					$contentObject = new ContentObject( $ceInfo['language_id'], $contentElement );
					$contentObject->loadFromContentID($ceInfo['reference_id']);
					$link = 'index.php?option=com_falang&amp;task=translate.edit&amp;&amp;catid=' .$ceInfo['catid']. '&cid[]=0|' .$ceInfo['reference_id'].'|'.$ceInfo['language_id'];
					$hrefEdit = "<a href='".$link."'>".$contentObject->title. "</a>";

					$link = 'index.php?option=com_falang&amp;task=translate.publish&amp;catid=' .$ceInfo['catid']. '&cid[]=0|' .$ceInfo['reference_id'].'|'.$ceInfo['language_id'];
					$hrefPublish = '<a href="'.$link.'"><img src="images/publish_x.png" width="12" height="12" border="0" alt="" /></a>';
					?>
			<tr class="row<?php echo $k;?>">
				<td align="left"><?php echo $hrefEdit;?></td>
				<td style="text-align: center;"><?php echo $ceInfo['language'];?></td>
					<td style="text-align: center;"><?php echo $hrefPublish;?></td>
			</tr>
					<?php
					$k = 1 - $k;
				}
			} else {
					?>
			<tr class="row0">
				<td colspan="3"><?php echo JText::_("No unpublished translations found");?></td>
			</tr>
					<?php
			}
			?>
		</table>
		<?php
		$output .= ob_get_clean();
	 	return $output;
	 }
	 
	 /**
	  * render content state information
	  */
	 function renderPerformanceInfo() {
	 	$output = '';
		ob_start();
		?>
		<table class="adminlist">
			<tr>
				<th />
				<th ><?php echo JText::_("Current");?></th>
				<th ><?php echo JText::_("Best Available");?></th>
			</tr>
			<tr class="row0">
				<?php
				if ($this->performanceInfo["driver"]["optimal"]){
					$color="green";
				}
				else {
					$color="red";
				}
				echo "<td>".JText::_("mySQL Driver")."</td>\n";
				echo "<td>".$this->performanceInfo["driver"]["current"]."</td>\n";
				echo "<td style='color:$color;font-weight:bold'>".$this->performanceInfo["driver"]["best"]."</td>\n";
				?>
			</tr>
			<tr class="row1">
				<?php
				if ($this->performanceInfo["cache"]["optimal"]){
					$color="green";
				}
				else {
					$color="red";
				}
				echo "<td>".JText::_("Translation Caching")."</td>\n";
				echo "<td>".$this->performanceInfo["cache"]["current"]."</td>\n";
				echo "<td style='color:$color;font-weight:bold'>".$this->performanceInfo["cache"]["best"]."</td>\n";
				?>
			</tr>
			</table>
		<?php
		$output .= ob_get_clean();
	 	return $output;
	 }
	 
	 /**
	  * render system state information
	  */
	 function renderSystemState() {
	 	$output = '';
		$stateGroups =  $this->panelStates;
		ob_start();
		?>
		<table class="adminlist">
			<?php
			foreach ($stateGroups as $key=>$stateRow) {
				if (!is_array($stateRow) || count($stateRow)==0){
					continue;
				}
				?>
			<tr>
				<th colspan="3"><?php echo JText::_($key. ' state');?></th>
			</tr>
				<?php
				$k=0;
				foreach ($stateRow as $row) {
					if (!isset($row->link ) ) continue;
					?>
			<tr class="row<?php echo $k;?>">
				<td><?php
					if( $row->link != '' ) {
						$row->description = '<a href="' .$row->link. '">' .$row->description. '</a>';
					}
					echo $row->description;
				?></td>
				<td colspan="2"><?php echo $row->resultText;?></td>
			</tr>
					<?php
					$k = 1 - $k;
				}
			}
			?>
			</table>
		<?php
		$output .= ob_get_clean();
	 	return $output;
	 }

	function limitText($text, $wordcount)
	{
		if(!$wordcount) {
			return $text;
		}

		$texts = explode( ' ', $text );
		$count = count( $texts );

		if ( $count > $wordcount )
		{
			$text = '';
			for( $i=0; $i < $wordcount; $i++ ) {
				$text .= ' '. $texts[$i];
			}
			$text .= '...';
		}

		return $text;
	}

	/**
	function getPluginVersion($folder,$extension_name){
		$extension = JTable::getInstance('extension');
		$id = $extension->find(array('element' => $extension_name, 'type' => 'plugin','folder' => $folder));
		$extension->load($id);
		$componentInfo = json_decode($extension->manifest_cache, true);
		return $componentInfo['version'];
    }
     */

		 
}
