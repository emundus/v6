<?php
defined( '_JEXEC' ) or die();
/**
 * @version 1: emundus-qcm-setup.php 89 2018-03-01 Brice Hubinet
 * @package QCM
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Création du groupe et du module à la création d'un QCM
 */

jimport('joomla.log.log');
JLog::addLogger(['text_file' => 'com_emundus.qcm_setup.php'], JLog::ALL, ['com_emundus']);
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$user = JFactory::getUser();

try {
    $jinput = JFactory::getApplication()->input;
    $data = new stdClass;
    $data->campaign_id = $jinput->getInt('jos_emundus_setup_qcm_campaign___campaign')[0];
    $data->label = $jinput->getString('jos_emundus_setup_qcm_campaign___label');
    $data->status = $jinput->getInt('jos_emundus_setup_qcm_campaign___status')[0];
    $data->type = $jinput->get('jos_emundus_setup_qcm_campaign___template')[0];
    $data->profile = $jinput->getInt('jos_emundus_setup_qcm_campaign___profile')[0];
    $data->categories = $jinput->getInt('jos_emundus_setup_qcm_campaign_1052_repeat___category_raw');



    if((int)$data->type === 2){
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus_onboard'.DS.'models'.DS.'formbuilder.php');

        $m_formbuilder = new EmundusonboardModelformbuilder;

        foreach ($data->categories as $category){
            $query->clear()
                ->select('*')
                ->from($db->quoteName('#__emundus_qcm_section'))
                ->where($db->quoteName('id') . ' = ' . $category[0]);
            $db->setQuery($query);
            $section = $db->loadObject();

            $m_formbuilder->createMenu($section->name,'',$data->profile,false);
        }
        // Create a new profile
        /*require_once (JPATH_SITE.DS.'components'.DS.'com_emundus_onboard'.DS.'models'.DS.'form.php');

        $m_form = new EmundusonboardModelform;
        $data_profile = [
            'label' => $data->label,
            'description' => '',
            'published' => 1,
        ];
        $new_profile = $m_form->createProfile($data_profile,$user->id,$user->name,false);
        $data->profile = $new_profile;*/
    }
    
    // Création de la phase QCM
    $query->select('id')
        ->from($db->quoteName('#__emundus_campaign_workflow'))
        ->where($db->quoteName('status') . ' = ' . $data->status)
        ->andWhere($db->quoteName('campaign') . ' = ' . $data->campaign_id);
    $db->setQuery($query);
    $workflow = $db->loadResult();

    if(!empty($workflow)){
        $query->clear()
            ->update($db->quoteName('#__emundus_campaign_workflow'))
            ->set($db->quoteName('profile') . ' = ' . $db->quote($data->profile))
            ->where($db->quoteName('id') . ' = ' . $db->quote($workflow));
        $db->setQuery($query);
        $db->execute();
    } else {
        $query->clear()
            ->insert($db->quoteName('#__emundus_campaign_workflow'))
            ->set($db->quoteName('date_time') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
            ->set($db->quoteName('updated') . ' = ' . $db->quote(date('Y-m-d H:i:s')))
            ->set($db->quoteName('campaign') . ' = ' . $data->campaign_id)
            ->set($db->quoteName('profile') . ' = ' . $data->profile)
            ->set($db->quoteName('status') . ' = ' . $data->status)
            ->set($db->quoteName('step') . ' = 1');
        $db->setQuery($query);
        $db->execute();
    }

} catch(Exception $e) {
    echo '<pre>'; var_dump($e->getMessage()); echo '</pre>'; die;
    JLog::add('plugins/fabrik_form/php/scripts/emundus-qcm-setup-campaign.php | Error at init qcm module : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
}
?>
