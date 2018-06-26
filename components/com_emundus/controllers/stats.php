<?php

/**
 * @package     Joomla
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2015 eMundus. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 * @author James Dean
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');


class EmundusControllerStats extends JControllerLegacy {

    public function __construct($config = array())
    {
        require_once (JPATH_COMPONENT.DS.'models'.DS.'stats.php');
        parent::__construct($config);
    }

    public function getprofiletype() {
        $dateArray = [];
        $countArray = [];
        $jinput     = JFactory::getApplication()->input;
        $val = $jinput->getVar('chosenvalue', null);
        $periode =  $jinput->getVar('periode', null);
        $getAccountType  = EmundusModelStats::getAccountType($val, $periode);
         foreach($getAccountType as $users) {
                
            if($users[profile_id] == $val) {
                $dateArray[]=$users[_day];
                $countArray[] = $users[nombre];
            }
            
        }
       
     
      echo json_encode((object)(array('status' => true,'datearray'=>$dateArray, 'countarray'=>$countArray, 'val' => $val)));
      exit;
    }

    public function getconsultations() {
        $dateArray = [];
        $countArray = [];
        $jinput     = JFactory::getApplication()->input;
        $val = $jinput->getVar('chosenvalue', null);
        $periode =  $jinput->getVar('periode', null);

        
        $getConsultations  = EmundusModelStats::consultationOffres($val, $periode);
        
         foreach($getConsultations as $bars) {
                
            if($bars[num_offre] == $val) {
                $dateArray[]=$bars[_day];
                $countArray[] = $bars[nombre];
            }
            
        }
       
     
      echo json_encode((object)(array('status' => true,'datearray'=>$dateArray, 'countarray'=>$countArray, 'val' => $val)));
      exit;
    }

    public function getconnections() {
        $dateArray = [];
        $countArray = [];
        $jinput     = JFactory::getApplication()->input;
        $periode =  $jinput->getVar('periode', null);

        $getConnections  = EmundusModelStats::getConnections($periode);
        
        foreach($getConnections as $cog) {
            $dateArray[]=$cog[_day];
            $countArray[] = $cog[nombre_connexions];
        }
       
     
      echo json_encode((object)(array('status' => true,'datearray'=>$dateArray, 'countarray'=>$countArray)));
      exit;
    }

    public function getcandidatures() {
        $dateArray = [];
        $countArray = [];
        $jinput     = JFactory::getApplication()->input;
        $val = $jinput->getVar('chosenvalue', null);
        $periode =  $jinput->getVar('periode', null);
        $getCandidatures  = EmundusModelStats::candidatureOffres($val, $periode);
        
        foreach($getCandidatures as $cand) {
            if($cand[num_offre] == $val) {
                $dateArray[]=$cand[_day];
                $countArray[] = $cand[nombre];
            }
        }

        echo json_encode((object)(array('status' => true,'datearray'=>$dateArray, 'countarray'=>$countArray, 'val' => $val)));
        exit;
    }

    public function getrelations() {
        $dateArray = [];
        $countArray = [];
        $jinput     = JFactory::getApplication()->input;
        $periode =  $jinput->getVar('periode', null);

        $getNbRelations  = EmundusModelStats::getNbRelations($periode);
        
        foreach($getNbRelations as $rel) {
            $dateArray[]=$rel[_day];
            $countArray[] = $rel[nombre_rel_etablies];
        }
       
     
      echo json_encode((object)(array('status' => true,'datearray'=>$dateArray, 'countarray'=>$countArray)));
      exit;
    }
}
