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

    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'models'.DS.'stats.php');
        parent::__construct($config);
    }

    public function getprofiletype() {
    	$dateArray = [];
        $countArray = [];

        $jinput = JFactory::getApplication()->input;
        $val = $jinput->post->get('chosenvalue', null);
        $periode = $jinput->post->get('periode', null);

        $m_stats = new EmundusModelStats();
        $getAccountType = $m_stats->getAccountType($val, $periode);

        foreach ($getAccountType as $users) {
        	if ($users['profile_id'] == $val) {
                $dateArray[] = $users['_day'];
                $countArray[] = $users['nombre'];
            }
        }

        echo json_encode((object)[
        	'status' => true,
	        'datearray' => $dateArray,
	        'countarray' => $countArray
        ]);
        exit;
    }

    public function getconsultations() {
        $dateArray = [];
        $countArray = [];

        $jinput = JFactory::getApplication()->input;
        $val = $jinput->post->get('chosenvalue', null);
        $periode = $jinput->post->get('periode', null);

	    $m_stats = new EmundusModelStats();
        $getConsultations = $m_stats->consultationOffres($val, $periode);
        
        foreach ($getConsultations as $bars) {
            if ($bars['num_offre'] == $val) {
                $dateArray[] = $bars['_day'];
                $countArray[] = $bars['nombre'];
            }
        }
     
        echo json_encode((object)[
        	'status' => true,
	        'datearray' => $dateArray,
	        'countarray' => $countArray
        ]);
        exit;
    }

    public function getconsultation() {
        $numOffre = [];
        $countArray = [];

        $jinput = JFactory::getApplication()->input;
        $periode = $jinput->post->get('periode', null);

        $m_stats = new EmundusModelStats();
        $getConsultations = $m_stats->consultationOffre($periode);
        
        foreach ($getConsultations as $bars) {
                $titre[] = $bars['titre'];
                $countArray[] = $bars['nb'];
        }
 
	    echo json_encode((object)[
	        'status' => true,
	        'titre' => $titre,
	        'countarray' => $countArray
	    ]);
	    exit;
	}

    public function getconnections() {
        $dateArray = [];
        $countArray = [];
        $jinput = JFactory::getApplication()->input;
        $periode = $jinput->post->get('periode', null);

	    $m_stats = new EmundusModelStats();
        $getConnections = $m_stats->getConnections($periode);
        
        
        foreach ($getConnections as $cog) {
            $dateArray[] = $cog['_day'];
            $countArray[] = $cog['nombre_connexions'];
        }
     
        echo json_encode((object)[
        	'status' => true,
	        'datearray' => $dateArray,
	        'countarray' => $countArray
        ]);
        exit;
    }

    public function getcandidatures() {
        $candArray = [];
        $nbArray = [];

        $jinput = JFactory::getApplication()->input;
        //$val = $jinput->post->get('chosenvalue', null);
        $periode = $jinput->post->get('periode', null);

	    $m_stats = new EmundusModelStats();
        $getCandidatures = $m_stats->candidatureOffres($periode);
        foreach ($getCandidatures as $cand) {
                $candArray[] = $cand['titre'];
                $nbArray[] = $cand['nb'];
        }

        echo json_encode((object)[
        	'status' => true,
	        'candarray' => $candArray,
	        'nbarray' => $nbArray
        ]);
        exit;
    }

    public function getrelations() {
        $dateArray = [];
        $countArray = [];
        $jinput = JFactory::getApplication()->input;
        $periode = $jinput->post->get('periode', null);

	    $m_stats = new EmundusModelStats();
        $getNbRelations = $m_stats->getNbRelations($periode);
        
        foreach ($getNbRelations as $rel) {
            $dateArray[] = $rel['_day'];
            $countArray[] = $rel['nombre_rel_etablies'];
        }

        echo json_encode((object)[
        	'status' => true,
	        'datearray' => $dateArray,
	        'countarray' => $countArray
        ]);
        exit;
    }

    public function addview() {
        $m_stats = new EmundusModelStats();
        $jinput = JFactory::getApplication()->input;
        $view = $jinput->post->get('view', null);

	    echo json_encode((object) [
		    'status' => $m_stats->addView($view)
	    ]);
	    exit;
    }

    public function linktofabrik() {
        $m_stats = new EmundusModelStats();
        $jinput = JFactory::getApplication()->input;
        $view = $jinput->post->get('view', null);
        $fabrikId = $m_stats->linkToFabrik($view);

        $id = $fabrikId[0];

        echo json_encode((object)[
        	'status' => true,
            'fabrikId' => $id
        ]);
        exit;
    }
}
