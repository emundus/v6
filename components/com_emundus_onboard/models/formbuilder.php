<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\CMS\Date\Date;

class EmundusonboardModelformbuilder extends JModelList {

     function updateOrder($elements, $group_id, $user) {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }


        $date = new Date();
        
            for($i = 0; $i < count($elements); $i++){
                
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $fields = array(
                    $db->quoteName('ordering'). ' = '.  $db->quote($elements[$i]['order']),
                    $db->quoteName('modified_by'). ' = '. $db->quote($user),
                   $db->quoteName('modified'). ' = '. $db->quote($date),
                );

                $query->update($db->quoteName('#__fabrik_elements'))
                    ->set($fields)
                    ->where($db->quoteName('id'). '  ='. $db->quote($elements[$i]['id']))
                    ->where($db->quoteName('group_id'). '  = ' . $db->quote($group_id));
                try {
                    $db->setQuery($query);
                    $db->execute();
                }
                catch(Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                    return $e->getMessage();
                }
            } 
                    return;
        
    }

    function ChangeRequire($element, $user) {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }           

        $date = new Date();

        if($element['FRequire'] === 'true'){
                $element['params']['notempty-message'] = array("");
                $element['params']['notempty-validation_condition'] = array("");
                $element['params']['validations']= array("plugin"=>"notempty","plugin_published"=>"1","validate_in"=>"both","validation_on"=>"both","must_validate"=>"1","show_icon"=>"1");
        }else {
                unset($element['params']['notempty-message']);
                unset($element['params']['notempty-validation_condition']);
                $element['params']['validations']= array();     
        }
        
       $db = $this->getDbo();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('params'). ' = '.  $db->quote(json_encode($element['params'])),
            $db->quoteName('modified_by'). ' = '. $db->quote($user),
            $db->quoteName('modified'). ' = '. $db->quote($date),
        );
        $query->update($db->quoteName('#__fabrik_elements'))
            ->set($fields)
            ->where($db->quoteName('id'). '  ='. $element['id']);

        try {
            $db->setQuery($query);
            return $db->execute();
            
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }
        
    }

        
    function changeTradLabel($element, $locallang , $newLabel, $user) {

         if (empty($user)) {
            $user = JFactory::getUser()->id;
        }  
                 
        $path_to_file = 'language\overrides\\';
        $path_to_file .= $locallang . '.override.ini' ;
        $Content_Folder = file_get_contents($path_to_file);
        $labelTofind= $element['labelToFind'] . '=';
        $labelToset= $labelTofind . "\"" .$newLabel."\"" ;


        if( strpos($Content_Folder,$labelTofind) !== false) {
            $labelTofind = "/^".$labelTofind.".*/mi";
            preg_match_all($labelTofind, $Content_Folder, $matches, PREG_SET_ORDER, 0);
            $Content_Folder = str_replace($matches[0], $labelToset,$Content_Folder);
            $Content_Folder = file_put_contents($path_to_file, $Content_Folder);
        }else{
            if(strpos($labelTofind,"LABEL_". $element['id']) !== false){
                $labelToset= "\n". $labelToset ;
                $Content_Folder = file_put_contents($path_to_file, $labelToset , FILE_APPEND);
            }else{
                
                $labelToAdd= "LABEL_" . $element['id'] ."_". strtoupper($element['labelToFind']);
                $labelToset= "\n".$labelToAdd . "=\"" .$newLabel."\"" ;
                $Content_Folder = file_put_contents($path_to_file, $labelToset , FILE_APPEND);
                
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $fields = array(
                    $db->quoteName('label'). ' = '.  $db->quote($labelToAdd),
                    $db->quoteName('modified_by'). ' = '. $db->quote($user),
                    $db->quoteName('modified'). ' = '. $db->quote($date),
                );
                $query->update($db->quoteName('#__fabrik_elements'))
                    ->set($fields)
                    ->where($db->quoteName('id'). '  ='. $element['id']);
                try {
                    $db->setQuery($query);
                    $db->execute();                    
                }
                catch(Exception $e) {
                    JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                    return $e->getMessage();
                }
            }

        }                     
        

            return;
        
    }


    function UpdateParams($element, $user, $locallang, $newLabel) {
        if (empty($user)) {
            $user = JFactory::getUser()->id;
        }           
        $date = new Date();
       $this->changeTradLabel($element, $locallang , $newLabel, $user);

        if($element['FRequire'] === 'true'){
                $element['params']['notempty-message'] = array("");
                $element['params']['notempty-validation_condition'] = array("");
                $element['params']['validations']= array("plugin"=>"notempty","plugin_published"=>"1","validate_in"=>"both","validation_on"=>"both","must_validate"=>"1","show_icon"=>"1");
        }else {
                unset($element['params']['notempty-message']);
                unset($element['params']['notempty-validation_condition']);
                $element['params']['validations']= array();     
        }
        $element['params']['filter_class'] = $element['params']['bootstrap_class'];        
       $db = $this->getDbo();
        $query = $db->getQuery(true);
        $fields = array(
            $db->quoteName('params'). ' = '.  $db->quote(json_encode($element['params'])),
            $db->quoteName('modified_by'). ' = '. $db->quote($user),
            $db->quoteName('modified'). ' = '. $db->quote($date),
        );
        $query->update($db->quoteName('#__fabrik_elements'))
            ->set($fields)
            ->where($db->quoteName('id'). '  ='. $element['id']);
        try {
            $db->setQuery($query);
            return $db->execute();            
        }
        catch(Exception $e) {
            JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
            return $e->getMessage();
        }        
    }
    
    
    function SubLabelsxValues($element, $locallang, $NewSubLabel, $user){
         if (empty($user)) {
            $user = JFactory::getUser()->id;
        }     
        $path_to_file = 'language\overrides\\';
        $path_to_file .= $locallang . '.override.ini' ;
        $Content_Folder = file_get_contents($path_to_file);

        if(count($NewSubLabel) < count($element['params']['sub_options']['sub_labels'])){
            $dif = count($element['params']['sub_options']['sub_labels']) - count($NewSubLabel);
            for($d = 0; $d < $dif; $d++){
             array_pop($element['params']['sub_options']['sub_labels']);
             array_pop($element['params']['sub_options']['sub_values']);
            }
            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $fields = array(
                $db->quoteName('params'). ' = '.  $db->quote(json_encode($element['params'])),
            );
            $query->update($db->quoteName('#__fabrik_elements'))
                ->set($fields)
                ->where($db->quoteName('id'). '  ='. $element['id']);
            try {
                $db->setQuery($query);
                $db->execute();                    
            }
            catch(Exception $e) {
                JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                return $e->getMessage();
            }
        }

        for($i = 0; $i < count($NewSubLabel); $i++){
            $labelTofind= $element['params']['sub_options']['sub_labels'][$i] . '=';
            $trad = $NewSubLabel[$i];
            $re1 = '/["]+/';
            preg_match_all($re1, $trad, $matches1, PREG_SET_ORDER, 0);
            for($tr = 0; $tr<count($matches1);$tr++){
                    $trad = str_replace($matches1[$tr], "''", $trad);
            }
            $re = '/[\x00-\x1F\x7F-\xFF\W+]/    ';
            preg_match_all($re, $NewSubLabel[$i], $matches, PREG_SET_ORDER, 0);
            for($m = 0; $m < count($matches);$m++){
                $NewSubLabel[$i] = str_replace($matches[$m], "", $NewSubLabel[$i]);
            }
            $NewSubLabel[$i] = strtoupper($NewSubLabel[$i]);
           
            if (strpos($Content_Folder,$labelTofind) === false || $labelTofind === "="){

                $sublabel = 'SL_' . $NewSubLabel[$i] . $element['id'] .$i;
                $element['params']['sub_options']['sub_labels'][$i] = $sublabel;
                $element['params']['sub_options']['sub_values'][$i] = $sublabel;
            
                if(strpos($labelTofind,$sublabel) !== false){
                    $labelToset= "\n".$sublabel. "=\"" . $trad . "\"";
                    file_put_contents($path_to_file, $labelToset , FILE_APPEND);
                }else{                
                    $labelToset= "\n".$sublabel . "=\"" .$trad."\"" ;
                    file_put_contents($path_to_file, $labelToset , FILE_APPEND);

                    $db = $this->getDbo();
                    $query = $db->getQuery(true);
                    $fields = array(
                        $db->quoteName('params'). ' = '.  $db->quote(json_encode($element['params'])),
                    );
                    $query->update($db->quoteName('#__fabrik_elements'))
                        ->set($fields)
                        ->where($db->quoteName('id'). '  ='. $element['id']);
                    try {
                        $db->setQuery($query);
                        $db->execute();                    
                    }
                    catch(Exception $e) {
                        JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus_onboard');
                        return $e->getMessage();
                    }
                }
            }else{
                $labelToset= $labelTofind . "\"" .$trad."\"" ;
                $labelTofind = "/^".$labelTofind.".*/mi";
                preg_match_all($labelTofind, $Content_Folder, $matches, PREG_SET_ORDER, 0);
                $Content_Folder = str_replace($matches[0], $labelToset,$Content_Folder);
                file_put_contents($path_to_file, $Content_Folder);                
            }

        }
        return $element['params'];

    }
    function getJTEXT($toJTEXT){
        for ($i = 0 ; $i < count($toJTEXT); $i++){
            $toJTEXT[$i] =  JText::_($toJTEXT[$i])  ;
        }
        return $toJTEXT;
    }


}