<?php
/**
* @version   $Id$
* @package   Jumi
* @copyright (C) 2008 - 2010 Martin Hajek, 2011 Edvard Ananyan
* @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('joomla.event.plugin');
require_once( dirname( __FILE__ ).DS.'jumi'.DS.'class.jumicoder.php' );

class plgSystemJumi extends JPlugin {
    function __construct( &$subject ) {
      parent::__construct( $subject );
      // load plugin parameters and language file
      $this->_plugin = JPluginHelper::getPlugin( 'system', 'jumi' );
      $this->_params = json_decode( $this->_plugin->params );
      JPlugin::loadLanguage('plg_system_jumi', JPATH_ADMINISTRATOR);
    }

    function onAfterRender() {
      $mainframe = JFactory::getApplication();
      if($mainframe->isAdmin())
        return;

      $plugin = JPluginHelper::getPlugin('system', 'jumi');
      $pluginParams = json_decode( $plugin->params );

      $content = JResponse::getBody();

      //print_r($pluginParams);exit;

      // expression to search for
      $regex1 = '%\{jumi\b[^}]?(\S*?)\}([\S\s]*?)\{/jumi\}%';
      $regex2 = '/{(jumi)\s*(.*?)}/i'; //BUG: des not work with written codes containing
        // if hide_code then replace jumi syntax codes with an empty string
        if ( $pluginParams->hide_code == 1 ) {
            $content = preg_replace( $regex1, '', $content );
            $content = preg_replace( $regex2, '', $content );
            return true;
        }
				
				//NEW JUMI SYNTAX
				if (preg_match($regex1, $content)) {
				    $continuesearching = true;
				    while ($continuesearching){  //Nesting loop
				        // find all instances of $regex1 (i.e. jumi) in an article and put them in $result
				        $result = array();
				        $matches_found = preg_match_all( $regex1, $content, $result, PREG_SET_ORDER );
				
							if ($matches_found) {
								for ($matchi = 0; $matchi < count($result); $matchi++) { //cycle through all jumi instancies.
									
							    //Sewing code written and code stored together to output
									$storage_source = $this->getStorageSource(trim($result[$matchi][1]), $pluginParams->default_absolute_path); //filepathname or record id or ""
									$code_written = $result[$matchi][2]; //raw code written or ""
								
									$output = $this->getOutput($code_written, $storage_source);
									//Final replacement of $regex1 (i.e. {jumi ...}...{/jumi}) in $article->text by eval $output
									ob_start();
									eval("?>".$output);
									$output = str_replace( '$' , '\$' , ob_get_contents()); //fixed joomla bug
									$output = str_replace( '\0' , '\\\\0' , ob_get_contents()); //fixed php bug. Not sure if there is no side effect of the fix.
									ob_end_clean();
									$content = preg_replace($regex1, $output, $content, 1);
								}
							/*
	if ($nested == 0) {
						  		$continuesearching = false;
						  	}
*/
							} else {
				   		  $continuesearching = false;
							}
				    }
				}
				//OLD JUMI SYNTAX
				if (preg_match($regex2, $content)) {
        $continuesearching = true;
        while ($continuesearching){  //Nesting loop
            // find all instances of $regex2 (i.e. jumi) in an article and put them in $matches
            $matches = array();
            $matches_found = preg_match_all( $regex2, $content, $matches, PREG_SET_ORDER );
            if ($matches_found) {
                // cycle through all jumi instancies. Put text into $dummy[2]
                foreach ($matches as $dummy) {
                    //read arguments contained in [] from $dummy[2] and put them into the array $jumi
                    $mms=array();
                    $jumi="";
                    preg_match_all('/\[.*?\]/', $dummy[2], $mms);
                    if ($mms) { //at the least one argument found
                        foreach ($mms as $i=>$mm) {
                            $jumi = preg_replace("/\[|]/", "", $mm);
                        }
                    }

                //Following syntax {jumi [storage_source][arg1]...[argN]}
                    $storage_source = $this->getStorageSource(trim(array_shift($jumi)), $pluginParams->default_absolute_path); //filepathname or record id or ""
                    $output = ''; // Jumi output

                    if($storage_source == '') { //if nothing to show
                        $output = '<div style="color:#FF0000;background:#FFFF00;">'.JText::_('ERROR_CONTENT').'</div>';
                    } else { // buffer output
                        ob_start();
                        if(is_int($storage_source)){ //it is record id
                      $code_stored = $this->getCodeStored($storage_source);
                    if($code_stored != null){ //include custom script written
                                eval ('?>'.$code_stored);
                    } else {
                                $output = '<div style="color:#FF0000;background:#FFFF00;">'.JText::sprintf('ERROR_RECORD', $storage_source).'</div>';
                    }
                } else { //it is file
                    if(is_readable($storage_source)) {
                                include($storage_source); //include file
                    } else {
                                $output = '<div style="color:#FF0000;background:#FFFF00;">'.JText::sprintf('ERROR_FILE', $storage_source).'</div>';
                    }
                        }
                if ($output == ''){ //if there are no errors
                    //$output = str_replace( '$' , '\$' , ob_get_contents()); fixed joomla bug
                    $output = ob_get_contents();
                }
                    ob_end_clean();
                }

                // final replacement of $regex2 (i.e. {jumi [][]}) in $article->text by $output
                    $content = preg_replace($regex2, $output, $content, 1);
                }
                if ($pluginParams->nested_replace == 0) {
                    $continuesearching = false;
                }
            } else {
                $continuesearching = false;
            }
        }
					
				}
        JResponse::setBody($content);
    }

    function getCodeStored($source) { //returns code stored in the database or null.
        $database  = JFactory::getDBO();
        //$user = &JFactory::getUser();
        //$database->setQuery("select custom_script from #__jumi where id = '{$source}' and access <= {$user->gid} and published = 1");
        $database->setQuery("select custom_script from #__jumi where id = $source");
        return $database->loadResult();
    }

    function getStorageSource($source, $abspath) { //returns filepathname or a record id or ""
        $storage=trim($source);
        if ($storage!=""){
            if ($id = substr(strchr($storage,"*"),1)) { //if record id return it
                return (int)$id;
            } else { // else return filepathname
                if($abspath == '')
                    return $storage;
                else
                    return $abspath.DS.$storage;
            }
        } else { // else return ""
            return '';
        }
    }

	function getOutput($code_written, $storage_source) 
	{ //returns Jumi $output
		$output = ''; // Jumi output
		if($code_written == '' && $storage_source == '') { //if nothing to show
		  $output = ($debug == 0) ? '' : '<div style="color:#FF0000;background:#FFFF00;">'.JText::_('ERROR_CONTENT').'</div>';
		} else { // buffer code to $output
			if($code_written != ''){ //if code written
				$code_written = JumiCoder::cleanRubbish($code_written);
				$code_written = JumiCoder::decode($code_written, 0);
    		$output .= $code_written; //include code written
			}
  		if($storage_source != ''){ //if record id or filepathname
				if(is_int($storage_source)){ //if record id
    		  $code_stored = $this->getCodeStored($storage_source);
      		if($code_stored != null){
						$output .= $code_stored; //include record
      		} else {
						$output = ($debug == 0) ? '' : '<div style="color:#FF0000;background:#FFFF00;">'.JText::sprintf('ERROR_RECORD', $storage_source).'</div>';
      		}
      	} else { //if file
      		if(is_readable($storage_source)) {
						$output .= file_get_contents($storage_source); //include file
      		} else {
						$output = ($debug == 0) ? '' : '<div style="color:#FF0000;background:#FFFF00;">'.JText::sprintf('ERROR_FILE', $storage_source).'</div>';
      		}
				}
  		}
  	}
		return $output;
	}


}
