<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien BarrÃ¨re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;
jimport('joomla.application.component.modellist');

/**
 * Class DropfilesControllerFts
 */
class DropfilesControllerFts extends JControllerForm
{
    /**
     * Indexer progress id
     *
     * @var boolean
     */
    protected $pId = false;

    /**
     * Get progress id
     *
     * @return string
     * @since  5.1.2
     */
    public function getPId()
    {
        if (!$this->pId) {
            $this->pId = sha1(time() . uniqid());
        }

        return $this->pId;
    }

    /**
     * Get default option
     *
     * @return array
     * @since  5.1.2
     */
    protected function defaultOptions()
    {
        return array(
            'enabled'          => 1,
            'autoreindex'      => 1,
            'index_ready'      => 0,
            'deflogic'         => 0, // AND
            'minlen'           => 3,
            'maxrepeat'        => 80, // 80%
            'stopwords'        => '',
            'epostype'         => '',
            'cluster_weights'  => serialize(array(
                'post_title'   => 0.8,
                'post_content' => 0.5,
            )),
            'tq_disable'       => 0,
            'tq_nocache'       => 1,
            'tq_post_status'   => 'any',
            'tq_post_type'     => 'any',
            'rebuild_time'     => 0,
            'process_time'     => '0|',
            'ping_period'      => 30,
            'est_time'         => '00:00:00',
            'activation_error' => '',
            'admin_message'    => ''
        );
    }

    /**
     * Get fts option
     *
     * @param string $optName Option name
     *
     * @return array|mixed
     * @since  5.1.2
     */
    public function ftsGetOption($optName)
    {
        $defaultOptions = $this->defaultOptions();
        $value          = $this->getParam($optName, $defaultOptions[$optName]);
        if ($optName === 'cluster_weights') {
            $value = (strlen($value) > 0) ? unserialize($value) : array();
        }

        return $value;
    }

    /**
     * Save fts option
     *
     * @param string      $optName Option name
     * @param array|mixed $value   Option value
     *
     * @return boolean
     * @since  5.1.2
     */
    public function ftsSaveOption($optName, $value)
    {
        $defaultOptions = $this->defaultOptions();
        if (isset($defaultOptions[$optName])) {
            // Allowed
            switch ($optName) {
                case 'cluster_weights':
                    $value = serialize($value);
                    break;
                default:
                    break;
            }
            $this->saveParams($optName, $value);
        } else {
            // Not Allowed
            return false;
        }
    }

    /**
     * Check and sync files
     *
     * @return mixed
     * @since  5.1.2
     */
    public function checkAndSyncFiles()
    {
        $model = $this->getModel();

        return $model->checkAndSyncFiles($this->ftsGetOption('rebuild_time'));
    }

    /**
     * Get indexer status
     *
     * @return mixed
     * @since  5.1.2
     */
    public function getStatus()
    {
        $model                 = $this->getModel();
        $status                = $model->getStatus();
        $status['est_time']    = $this->ftsGetOption('est_time');
        $status['enabled']     = $this->ftsGetOption('enabled');
        $status['index_ready'] = $this->ftsGetOption('index_ready');
        $status['autoreindex'] = $this->ftsGetOption('autoreindex');

        return $status;
    }

    /**
     * Rebuild index
     *
     * @param boolean $time Time
     *
     * @return mixed
     * @since  5.1.2
     */
    public function rebuildindex($time = false)
    {
        if (!$time) {
            $time = time();
        }
        $this->ftsSaveOption('rebuild_time', $time);

        return $this->checkAndSyncFiles();
    }

    /**
     * Get progress state
     *
     * @param string $processId Indexer progress id
     *
     * @return integer
     * @since  5.1.2
     */
    public function indexerProcessState($processId)
    {
        $time        = time();
        $processTime = explode('|', $this->ftsGetOption('process_time'));
        $pingPrior   = 30;
        if ($processId !== $processTime[1]) {
            if ($processTime[0] + $pingPrior * 4 > $time) {
                return 2;    // Other pid indexing goes
            } else {
                return 0;    // Free
            }
        } else {
            if ($processTime[0] + $pingPrior * 2 > $time) {
                return 1;    // Our pid indexing goes
            } else {
                return 0;    // Free
            }
        }

        // Unreachable
        return 0;
    }

    /**
     * Submit rebuild
     *
     * @return void
     * @since  5.1.2
     * @throws \Exception Failed to start application
     */
    public function submitrebuild()
    {
        // phpcs:disable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, Generic.PHP.NoSilencedErrors.Discouraged -- Turn off error log
        @ini_set('error_reporting', 0);
        @error_reporting(0);
        // phpcs:enable
//        if (!$this->isFtsEnabled()) {
//            $this->ftsSaveOption('plain_text_search', 1);
//        }

        if (!class_exists('FtsHelperResponse')) {
            $pathResponseHelper = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components';
            $pathResponseHelper .= DIRECTORY_SEPARATOR . 'com_dropfiles' . DIRECTORY_SEPARATOR . 'helpers';
            $pathResponseHelper .= DIRECTORY_SEPARATOR . 'ftsresponse.php';
            require_once $pathResponseHelper;
        }

        $response = new FtsHelperResponse();
        $model    = $this->getModel();
        $data = $response->getData();
        if ($data !== false) {
            $this->ftsSaveOption('index_ready', 0);
            $model->ftsCreateTables();
            $this->rebuildindex(time());
            $response->reload();
        }
        echo $response->getJSON();
        JFactory::getApplication()->close();
    }

    /**
     * Ajax ping
     *
     * @return void
     * @since  5.1.2
     * @throws \Exception Failed to start application
     */
    public function ajaxping()
    {
        // phpcs:disable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, Generic.PHP.NoSilencedErrors.Discouraged -- Turn off error log
        @ini_set('error_reporting', 0);
        @error_reporting(0);
        // phpcs:enable
        $t0 = microtime(true);

        $pathResponseHelper = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components';
        $pathResponseHelper .= DIRECTORY_SEPARATOR . 'com_dropfiles' . DIRECTORY_SEPARATOR . 'helpers';
        $pathResponseHelper .= DIRECTORY_SEPARATOR . 'ftsresponse.php';
        require_once $pathResponseHelper;

        $response = new FtsHelperResponse();
        $data = $response->getData();
        if ($data !== false) {
            $process_id = $data['pid'];
            $status     = $this->getStatus();

            $state = $this->indexerProcessState($process_id);

            $response->variable('code', 0);

            // $view = $this->loadView();
            $response->variable('status', json_encode($status));
            switch ($state) {
                case 2:
                case 1:
                    // Other pid is indexing
                    $response->variable('result', 10);    // Just wait, ping
                    break;
                case 0:
                default:
                    // Indexer is free now, lets check what to do
                    if ($status['n_pending'] > 0) {
                        // There is something to index
                        $response->variable('result', 5);    // Start to index
                    } else {
                        // Nothing to index
                        $response->variable('result', 0);    // Indexing stopped, ping
                    }
            }

            $response->console('pong! ' . (microtime(true) - $t0) . ' s');
        }

        echo $response->getJSON();
        JFactory::getApplication()->close();
    }

    /**
     * Rebuild index step
     *
     * @return void
     * @since  5.1.2
     * @throws \Exception Failed to start application
     */
    public function rebuildstep()
    {
        if (!class_exists('FtsHelperResponse')) {
            $pathResponseHelper = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components';
            $pathResponseHelper .= DIRECTORY_SEPARATOR . 'com_dropfiles' . DIRECTORY_SEPARATOR . 'helpers';
            $pathResponseHelper .= DIRECTORY_SEPARATOR . 'ftsresponse.php';
            require_once $pathResponseHelper;
        }

        $response = new FtsHelperResponse();
        $model    = $this->getModel();
        $data = $response->getData();
        if ($data !== false) {
            $processId = $data['pid'];
            $st        = $this->indexerProcessState($processId);
            if ($st !== 2) {
                // Allow to start indexer session
                // Set up lock
                $this->ftsSaveOption('process_time', time() . '|' . $processId);

                $build_time = $this->ftsGetOption('rebuild_time');

                $maxtime  = 10;
                $start_ts = microtime(true);

                ignore_user_abort(true);

                $n = 0;
                while (microtime(true) - $start_ts < $maxtime) {
                    $ids = $model->getRecordsToRebuild(1);
                    foreach ($ids as $item) {
                        if (!(microtime(true) - $start_ts < $maxtime)) {
                            break;
                        }

                        // Rebuild this record
                        if ($item->tsrc === 'df_files') {
                            // Check if locked and lock if not locked
                            if ($model->lockUnlockedRecord($item->id)) {
                                // Record is locked, lets index it now
                                $db    = JFactory::getDbo();
                                $query = $db->getQuery(true);
                                $query->select($db->quoteName(array(
                                    'id',
                                    'title',
                                    'description',
                                    'ext',
                                    'catid',
                                    'file',
                                    'modified_time'
                                )))->from($db->quoteName('#__dropfiles_files'))
                                      ->where($db->quoteName('id') . ' = ' . $db->quote($item->tid));
                                $db->setQuery($query);
                                $file = $db->loadObject();

                                $modt   = $file->modified_time;
                                $chunks = array(
                                    'title'       => $file->title,
                                    'description' => $file->description
                                );

                                $chunks2 = $model->getChunksWithContent($chunks, $file);

                                $model->clearLog();
                                $res = $model->reIndex($item->id, $chunks2);
                                if (!$res) {
                                    $response->console('Indexing error: ' . $model->getLog());
                                }
                                // Store some statistic
                                $time = time();
                                $model->updateRecordData($item->id, array(
                                    'tdt'           => $modt,
                                    'build_time'    => $build_time,
                                    'update_dt'     => date('Y-m-d H:i:s', $time),
                                    'force_rebuild' => 0,
                                ));
                                $model->unlockRecord($item->id);
                            }
                        }
                        $n ++;
                    }
                    if ($n < 1) {
                        break;
                    }
                }

                $finish_ts = microtime(true);

                $response->variable('code', 0);

                $status = $this->getStatus();

                $est_seconds = $n > 0 ? intval((($finish_ts - $start_ts) * $status['n_pending']) / $n) : 0;

                $est_h   = intval($est_seconds / 3600);
                $est_m   = intval(($est_seconds - $est_h * 3600) / 60);
                $est_s   = ($est_seconds - $est_h * 3600) % 60;
                $est_str = sprintf('%02d:%02d:%02d', $est_h, $est_m, $est_s);

                $this->ftsSaveOption('est_time', $est_str);

                $status['est_time'] = $est_str;

                if ($status['n_pending'] > 0) {
                    if (($finish_ts - $start_ts) < ($maxtime / 2)) {
                        // Just a delay
                        $this->ftsSaveOption('process_time', '0|' . $processId);
                        $response->variable('result', 10);
                    } else {
                        // There is something to index
                        // Remove lock
                        $this->ftsSaveOption('process_time', '0|' . $processId);
                        $response->variable('result', 5);    // Continue indexing
                    }
                } else {
                    // Nothing to index
                    // Remove lock
                    $this->ftsSaveOption('process_time', '0|0');
                    $response->variable('result', 0);    // Indexing stopped, ping
                    $this->ftsSaveOption('index_ready', 1);
                    $response->variable('delay', 0);
                }
                $status = $this->getStatus();
                // $view = $this->loadView();
                $response->variable('status', json_encode($status));

                $response->console(sprintf('%s files has been rebuilt', $n));
            } else {
                // Unable to index
                $response->variable('code', 1);
            }
        }
        echo $response->getJSON();
        JFactory::getApplication()->close();
    }

    /**
     * Get param by key
     *
     * @param string $key     Key name
     * @param string $default Default value
     *
     * @return mixed
     *
     * @since 5.1.2
     */
    protected function getParam($key, $default)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('params')->from('#__extensions')->where('element = "com_dropfiles"');
        $db->setQuery((string) $query);
        $params = new JRegistry;
        $results = $db->loadObject();
        if ($results) {
            $params->loadString($results->params, 'JSON');
        }

        //$params = JComponentHelper::getParams('com_dropfiles');
        return $params->get($key, $default);
    }

    /**
     * Save full text search params
     *
     * @param string $key   Key name
     * @param object $value Value
     *
     * @return boolean
     *
     * @since 5.1.2
     */
    protected function saveParams($key, $value)
    {
        $datas     = array($key => $value);
        $component = JComponentHelper::getComponent('com_dropfiles');
        $table     = JTable::getInstance('extension');
        // Load the previous Data
        if (!$table->load($component->id, false)) {
            return false;
        }
        $d = json_decode($table->params, true);
        foreach ($datas as $key => $data) {
                $d[$key] = $data;
        }
        $table->params = json_encode($d);
        // Bind the data.
        if (!$table->bind($datas)) {
            return false;
        }
        // Check the data.
        if (!$table->check()) {
            return false;
        }
        // Store the data.
        if (!$table->store()) {
            return false;
        }
    }

    /**
     * Exit and print status
     *
     * @param string $status Status
     * @param array  $datas  Data
     *
     * @return void
     * @since  5.1.2
     * @throws \Exception Failed to start application
     */
    private function exitStatus($status, $datas = array())
    {
        $response = array('response' => $status, 'datas' => $datas);
        echo json_encode($response);
        JFactory::getApplication()->close();
    }

    /**
     * Check full text search is enabled
     *
     * @return boolean
     *
     * @since 5.1.2
     */
    private function isFtsEnabled()
    {
        $params = JComponentHelper::getParams('com_dropfiles');

        if ((int) $params->get('plain_text_search', 0) === 1) {
            return true;
        } else {
            return false;
        }
    }
}
