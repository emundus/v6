<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
require_once "ZoomAPIWrapper.php";

/**
* Create a Joomla user from the forms data
*
* @package     Joomla.Plugin
* @subpackage  Fabrik.form.juseremundus
* @since       3.0
*/

class PlgFabrik_FormEmunduszoommeeting extends plgFabrik_Form {

    public function searchSubArray(Array $array, $key) {
        foreach ($array as $index => $subarray){
            if (isset($subarray[$key])) {
                return array('parent' => $index, 'status' => true);
            }
        }
    }

    public function onAfterProcess() {
        /* read json template file */
        $route = JPATH_BASE.'/plugins/fabrik_form/emunduszoommeeting/api_templates' . DS;
        $template = file_get_contents($route . __FUNCTION__ . '.json');
        $json = json_decode($template, true);

        /* create new zoom meeting room */
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        /* get JFactory application */
        $app = JFactory::getApplication();

        /* get api key */
        $eMConfig = JComponentHelper::getParams('com_emundus');
        $apiSecret = $eMConfig->get('zoom_jwt', '');

        /* call to api to create new zoom meeting */
        $zoom = new ZoomAPIWrapper($apiSecret);

        /* get info of host from $_POST */
        $host = current($_POST['jos_emundus_jury___president']);

        $offset = $app->get('offset', 'UTC');
        $startTime = date('Y-m-d\TH:i:s\Z', strtotime($_POST["jos_emundus_jury___start_time"]));
        $endTime = date('Y-m-d\TH:i:s\Z', strtotime($_POST["jos_emundus_jury___end_time"]));

        $hostQuery = "select * from data_referentiel_zoom_token as drzt where drzt.user = " . $host;
        $db->setQuery($hostQuery);
        $raw = $db->loadObject();

        /* handle start time, end time */

        foreach($_POST as $key => $post) {
            $suff = explode("jos_emundus_jury___", $key)[1];

            /* find key in JSON template */
            if(array_key_exists($suff, $json)) {
                if(is_array($post) and sizeof($post) == 1)
                    $post = current($post);
                $json[$suff] = $post;
            } else {
                if($this->searchSubArray($json, 'join_before_host')['status'] === true) {

                    $parentKey = $this->searchSubArray($json, $suff)['parent'];

                    if(is_array($post) and sizeof($post) == 1)
                        $post = current($post);
                    $json[$parentKey][$suff] = $post;

                }
            }
        }

        $response = $zoom->doRequest('POST', '/users/'. $raw->zoom_id .'/meetings', array(), array(), json_encode($json, JSON_PRETTY_PRINT));
        
        echo '<pre>'; var_dump($response); echo '</pre>'; die;
    }
}