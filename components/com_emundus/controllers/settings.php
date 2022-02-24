<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Settings Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllersettings extends JControllerLegacy {

    var $m_settings = null;
    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->m_settings = $this->getModel('settings');
    }

    public function getstatus() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $status = $this->m_settings->getStatus();

            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('STATUS_RETRIEVED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_STATUS'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function gettags() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $status = $this->m_settings->getTags();

            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('STATUS_RETRIEVED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_STATUS'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createtag() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $this->m_settings->createTag();
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function createstatus() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $changeresponse = $this->m_settings->createStatus();
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function deletetag() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $id = $jinput->getInt('id');

            $changeresponse = $this->m_settings->deleteTag($id);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function deletestatus() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $id = $jinput->getInt('id');
            $step = $jinput->getInt('step');

            $changeresponse = $this->m_settings->deleteStatus($id,$step);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatestatus() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $status = $jinput->getInt('status');
	        $label = $jinput->getString('label');
	        $color = $jinput->getString('color');

            $changeresponse = $this->m_settings->updateStatus($status,$label,$color);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatestatusorder() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $status = $jinput->getString('status');

            $changeresponse = $this->m_settings->updateStatusOrder(explode(',',$status));
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatetags() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

            $tag = $jinput->getInt('tag');
            $label = $jinput->getString('label');
            $color = $jinput->getString('color');

            $changeresponse = $this->m_settings->updateTags($tag,$label,$color);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getarticle() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $article_id = $jinput->getString('article_id',0);
            $article_alias = $jinput->getString('article_alias','');
            $lang = $jinput->getString('lang');
            $field = $jinput->getString('field');

            $content = $this->m_settings->getArticle($lang,$article_id,$article_alias,$field);

            if (!empty($content)) {
                $tab = array('status' => 1, 'msg' => JText::_('ARTICLE_FIND'), 'data' => $content);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_ARTICLE') . $article_id, 'data' => $content);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatearticle() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $content = $jinput->getRaw('content');
            $article_id = $jinput->getString('article_id',0);
            $article_alias = $jinput->getString('article_alias','');
            $lang = $jinput->getString('lang');
            $field = $jinput->getString('field');

            $changeresponse = $this->m_settings->updateArticle($content,$lang,$article_id,$article_alias,$field);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getfooterarticles() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $content = $this->m_settings->getFooterArticles();

            if (!empty($content)) {
                $tab = array('status' => 1, 'msg' => JText::_('FOOTER_RETRIEVED'), 'data' => $content);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FOOTER'), 'data' => $content);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatefooter() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $col1 = $jinput->getRaw('col1');
            $col2 = $jinput->getRaw('col2');

            $changeresponse = $this->m_settings->updateFooter($col1,$col2);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function updatelogo() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $image = $jinput->files->get('file');

            if(isset($image)) {
                $target_dir = "images/custom/";
                unlink($target_dir . 'logo_custom.png');

                $target_file = $target_dir . basename('logo_custom.png');

                $logo_module = JModuleHelper::getModuleById('90');

                if (move_uploaded_file($image["tmp_name"], $target_file)) {
                    $new_content = str_replace('logo.png','logo_custom.png',$logo_module->content);
                    $this->model->updateLogo($new_content);
                    $tab = array('status' => 1, 'msg' => JText::_('LOGO_UPDATED'));
                } else {
                    $tab = array('status' => 0, 'msg' => JText::_('LOGO_NOT_UPDATED'));
                }
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('LOGO_NOT_UPDATED'));
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    public function updateicon() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $image = $jinput->files->get('file');

            if(isset($image)) {
                $target_dir = "images/custom/";
                unlink($target_dir . 'favicon.png');

                $target_file = $target_dir . basename('favicon.png');

                if (move_uploaded_file($image["tmp_name"], $target_file)) {
                    $tab = array('status' => 1, 'msg' => JText::_('ICON_UPDATED'));
                } else {
                    $tab = array('status' => 0, 'msg' => JText::_('ICON_NOT_UPDATED'));
                }
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ICON_NOT_UPDATED'));
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    public function removeicon(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $target_dir = "images/custom/";
            unlink($target_dir . 'favicon.png');

            $tab = array('status' => 1, 'msg' => JText::_('ICON_REMOVED'));

            echo json_encode((object)$tab);
            exit;
        }
    }

    public function updatehomebackground() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $image = $jinput->files->get('file');

            if(isset($image)) {
                $target_dir = "images/custom/";
                unlink($target_dir . 'home_background.png');

                $target_file = $target_dir . basename('home_background.png');

                if (move_uploaded_file($image["tmp_name"], $target_file)) {
                    $tab = array('status' => 1, 'msg' => JText::_('BACKGROUND_UPDATED'));
                } else {
                    $tab = array('status' => 0, 'msg' => JText::_('BACKGROUND_NOT_UPDATED'));
                }
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('BACKGROUND_NOT_UPDATED'));
            }
            echo json_encode((object)$tab);
            exit;
        }
    }

    public function getbackgroundoption(){
        $user = JFactory::getUser();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            try {
                $query->select('published,content')
                    ->from($db->quoteName('#__modules'))
                    ->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_custom'))
                    ->andWhere($db->quoteName('title') . ' LIKE ' . $db->quote('Homepage background'));

                $db->setQuery($query);
                $module = $db->loadObject();
                $published = $module->published;
                $content = $module->content;

                $tab = array('status' => 0, 'msg' => 'success', 'data' => $published, 'content' => $content);
            } catch (Exception $e) {
                $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatebackgroundmodule() {
        $user = JFactory::getUser();
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $published = $jinput->getInt('published');

            try {
                $query->update($db->quoteName('#__modules'))
                    ->set($db->quoteName('published') . ' = ' . $db->quote($published))
                    ->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_custom'))
                    ->andWhere($db->quoteName('title') . ' LIKE ' . $db->quote('Homepage background'));

                $db->setQuery($query);
                $state = $db->execute();

                $tab = array('status' => 0, 'msg' => 'success', 'data' => $state);
            } catch (Exception $e) {
                $tab = array('status' => 0, 'msg' => $e->getMessage(), 'data' => null);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getappcolors(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents('templates/g5_helium/custom/config/default/styles.yaml'));

            $primary = $yaml['base']['primary-color'];
            $secondary = $yaml['base']['secondary-color'];
            $tab = array('status' => '1', 'msg' => JText::_("SUCCESS"), 'primary' => $primary, 'secondary' => $secondary);
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatecolor(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => '0', 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $preset = $jinput->post->getRaw('preset');

            $yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents('templates/g5_helium/custom/config/default/styles.yaml'));

            $yaml['base']['primary-color'] = $preset['primary'];
            $yaml['accent']['color-1'] = $preset['primary'];
            $yaml['base']['secondary-color'] = $preset['secondary'];
            $yaml['accent']['color-2'] = $preset['secondary'];
            $yaml['link']['regular'] = $preset['secondary'];
            $yaml['link']['hover'] = $preset['secondary'];

            $new_yaml = \Symfony\Component\Yaml\Yaml::dump($yaml, 5);

            file_put_contents('templates/g5_helium/custom/config/default/styles.yaml', $new_yaml);

            $tab = array('status' => '1', 'msg' => JText::_("SUCCESS"));
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getdatasfromtable() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {


            $jinput = JFactory::getApplication()->input;
            $dbtable = $jinput->getString('db');

            $datas = $this->m_settings->getDatasFromTable($dbtable);
            $response = array('status' => '1', 'msg' => 'SUCCESS', 'data' => $datas);
        }
        echo json_encode((object)$response);
        exit;
    }

    public function savedatas() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $form = $jinput->getRaw('form');

            $state = $this->m_settings->saveDatas($form);
            $response = array('status' => $state, 'msg' => 'SUCCESS');
        }
        echo json_encode((object)$response);
        exit;
    }

    public function saveimporteddatas() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $form = $jinput->getRaw('form');
            $datas = $jinput->getRaw('datas');

            $state = $this->m_settings->saveImportedDatas($form,$datas);
            $response = array('status' => $state, 'msg' => 'SUCCESS');
        }
        echo json_encode((object)$response);
        exit;
    }

    public function unlockuser() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $user_id = $jinput->getInt('user');

            $state = $this->m_settings->unlockUser($user_id);
            $response = array('status' => $state, 'msg' => 'SUCCESS');
        }
        echo json_encode((object)$response);
        exit;
    }

    public function lockuser() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $user_id = $jinput->getInt('user');

            $state = $this->m_settings->lockUser($user_id);
            $response = array('status' => $state, 'msg' => 'SUCCESS');
        }
        echo json_encode((object)$response);
        exit;
    }

    public function checkfirstdatabasejoin() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {


            $state = $this->m_settings->checkFirstDatabaseJoin($user->id);
            $response = array('status' => $state, 'msg' => 'SUCCESS');
        }
        echo json_encode((object)$response);
        exit;
    }

    public function removeparam() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

            $jinput = JFactory::getApplication()->input;
            $param = $jinput->getString('param');

            $state = $this->m_settings->removeParam($param, $user->id);
            $response = array('status' => $state, 'msg' => 'SUCCESS');
        }
        echo json_encode((object)$response);
        exit;
    }

    public function redirectjroute() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $link = $jinput->getString('link');

            $response = array('status' => true, 'msg' => 'SUCCESS', 'data' => JRoute::_($link, false));
        }
        echo json_encode((object)$response);
        exit;
    }

    public function geteditorvariables() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {


            $datas = $this->m_settings->getEditorVariables();
            $response = array('status' => '1', 'msg' => 'SUCCESS', 'data' => $datas);
        }
        echo json_encode((object)$response);
        exit;
    }

    public function getactivelanguages() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $datas = JLanguageHelper::getLanguages();
            usort($datas, function($a, $b) {
                return (int)$a->lang_id > (int)$b->lang_id ? 1 : -1;
            });
            $response = array('status' => '1', 'msg' => 'SUCCESS', 'data' => $datas);
        }
        echo json_encode((object)$response);
        exit;
    }

    public function uploadimages() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $image = $jinput->files->get('file');

            if(isset($image)) {
                $config = JFactory::getConfig();
                $sitename = strtolower(str_replace(array('\\','=','&',',','#','_','*',';','!','?',':','+','$','\'',' ','£',')','(','@','%'),'_',$config->get('sitename')));

                $path = $image["name"];
                $ext = pathinfo($path, PATHINFO_EXTENSION);

                $target_dir = "images/custom/" . $sitename . "/";
                if(!file_exists($target_dir)){
                    mkdir($target_dir);
                }

                do{
                    $target_file = $target_dir . rand(1000,90000) . '.' . $ext;
                } while (file_exists($target_file));

                if (move_uploaded_file($image["tmp_name"], $target_file)) {
                    echo json_encode(array('location' => $target_file));
                } else {
                    echo json_encode(array('msg' => 'ERROR WHILE UPLOADING YOUR IMAGE'));
                }
            } else {
                echo json_encode(array('msg' => 'ERROR WHILE UPLOADING YOUR IMAGE'));
            }
            exit;
        }
    }

    public function gettasks(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            echo json_encode(array('status' => $result, 'msg' => JText::_("ACCESS_DENIED")));
        } else {
            $table = JTable::getInstance('user', 'JTable');
            $table->load($user->id);

            // Check if the param exists but is false, this avoids accidetally resetting a param.
            $params = $user->getParameters();
            echo json_encode(array('params' => $params));
        }
        exit;
    }

    public function uploaddropfiledoc() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            echo json_encode(array('status' => $result, 'msg' => JText::_("ACCESS_DENIED")));
        } else {

            $m_campaign = $this->getModel('campaign');

            $jinput = JFactory::getApplication()->input;
            $file = $jinput->files->get('file');
            $cid = $jinput->get('cid');

            if(isset($file)) {
                $campaign_category = $m_campaign->getCampaignCategory($cid);

                $path = $file["name"];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $filename = pathinfo($path, PATHINFO_FILENAME);

                $target_dir = "media/com_dropfiles/" . $campaign_category . "/";
                if(!file_exists($target_dir)){
                    mkdir($target_dir);
                }

                do{
                    $target_file = $target_dir . rand(1000,90000) . '.' . $ext;
                } while (file_exists($target_file));

                if (move_uploaded_file($file["tmp_name"], $target_file)) {
                    $did = $this->m_settings->moveUploadedFileToDropbox(pathinfo($target_file,PATHINFO_BASENAME),$filename,$ext,$campaign_category,filesize($target_file));
                    echo json_encode($m_campaign->getDropfileDocument($did));
                } else {
                    echo json_encode(array('msg' => 'ERROR WHILE UPLOADING YOUR DOCUMENT'));
                }
            } else {
                echo json_encode(array('msg' => 'ERROR WHILE UPLOADING YOUR DOCUMENT'));
            }
            exit;
        }
    }

    public function uploadformdoc() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            echo json_encode(array('status' => $result, 'msg' => JText::_("ACCESS_DENIED")));
        } else {

            $jinput = JFactory::getApplication()->input;
            $file = $jinput->files->get('file');
            $pid = $jinput->get('pid');

            if(isset($file)) {
                $config = JFactory::getConfig();

                /* Clean sitename for folder */
                $m_formbuilder = $this->getModel('formbuilder');

                $sitename = strtolower(str_replace(array('\\','=','&',',','#','_','*',';','!','?',':','+','$','\'',' ','£',')','(','@','%'),'_',$config->get('sitename')));
                $sitename = $m_formbuilder->replaceAccents($sitename);


                $path = $file["name"];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $filename = pathinfo($path, PATHINFO_FILENAME);


                $target_root = "images/custom/" . $sitename . "/";
                $target_dir = $target_root . "form_documents/";
                if(!file_exists($target_root)){
                    mkdir($target_root);
                }
                if(!file_exists($target_dir)){
                    mkdir($target_dir);
                }

                do{
                    $target_file = $target_dir . rand(1000,90000) . '.' . $ext;
                } while (file_exists($target_file));

                if (move_uploaded_file($file["tmp_name"], $target_file)) {
                    $this->m_settings->addDocumentToForm(pathinfo($target_file,PATHINFO_BASENAME),$filename,$target_dir,$pid);
                    $doc = new stdClass;
                    $doc->name = $filename;
                    $doc->link = $target_file;
                    $doc->id = explode('.',pathinfo($target_file,PATHINFO_BASENAME))[0];
                    echo json_encode($doc);
                } else {
                    echo json_encode(array('msg' => 'ERROR WHILE UPLOADING YOUR DOCUMENT'));
                }
            } else {
                echo json_encode(array('msg' => 'ERROR WHILE UPLOADING YOUR DOCUMENT'));
            }
            exit;
        }
    }

    public function rewindtutorial() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            echo json_encode(array('status' => $result, 'msg' => JText::_("ACCESS_DENIED")));
        } else {
            $table = JTable::getInstance('user', 'JTable');
            $table->load($user->id);

            $user->setParam('first_login', true);
            $user->setParam('first_campaign', true);
            $user->setParam('first_form', true);
            $user->setParam('first_formbuilder', true);
            $user->setParam('first_documents', true);
            $user->setParam('first_databasejoin', true);
            $user->setParam('first_program', true);

            // Get the raw User Parameters
            $params = $user->getParameters();

            // Set the user table instance to include the new token.
            $table->params = $params->toString();

            // Save user data
            if (!$table->store()) {
                JLog::add('Error saving params : '.$table->getError(), JLog::ERROR, 'com_emundus');
                echo json_encode(array('status' => true));
            }

            echo json_encode(array('status' => true));
        }
        exit;
    }

    public function getemundusparams(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            echo json_encode(array('status' => $result, 'msg' => JText::_("ACCESS_DENIED")));
        } else {
            $eMConfig = JComponentHelper::getParams('com_emundus');

            echo json_encode(array('config' => $eMConfig));
        }
        exit;
    }

    public function updateemundusparam(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            echo json_encode(array('status' => $result, 'msg' => JText::_("ACCESS_DENIED")));
        } else {
            $jinput = JFactory::getApplication()->input;
            $param = $jinput->getString('param');
            $value = $jinput->getInt('value');

            $eMConfig = JComponentHelper::getParams('com_emundus');
            $eMConfig->set($param, $value);

            $componentid = JComponentHelper::getComponent('com_emundus')->id;
            $db = JFactory::getDBO();

            $query = "UPDATE #__extensions SET params = ".$db->Quote($eMConfig->toString())." WHERE extension_id = ".$componentid;

            try {
                $db->setQuery($query);
                $status = $db->execute();
            } catch (Exception $e) {
                JLog::add('Error set param '.$param, JLog::ERROR, 'com_emundus');
            }
            echo json_encode(array('status' => $status));
        }
        exit;
    }

    /// get all users
    public function getallusers() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            echo json_encode(array('status' => $result, 'msg' => JText::_("ACCESS_DENIED")));
        } else {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            try {
                $query->clear()
                    ->select('#__users.*')
                    ->from($db->quoteName('#__users'));

                $db->setQuery($query);
                $users = $db->loadObjectList();
                echo json_encode(array('status' => true, 'users' => $users));
            } catch(Exception $e) {
                JLog::add('Cannot get all users '.$e->getMessage(), JLog::ERROR, 'com_emundus');
                echo json_encode(array('status' => false));
            }
        }
        exit;
    }
}

