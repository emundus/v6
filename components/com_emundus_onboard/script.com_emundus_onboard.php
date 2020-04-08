<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// include('../../administrator/comp');

/**
 * Script file of emundus_onboard component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_emundus_onboard
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
class com_emundus_onboardInstallerScript
{
  /**
   * This method is called after a component is installed.
   *
   * @param  \stdClass $parent - Parent object calling this method.
   *
   * @return void
   */
  public function install($parent)
  {
    $parent->getParent();
  }

  /**
   * This method is called after a component is uninstalled.
   *
   * @param  \stdClass $parent - Parent object calling this method.
   *
   * @return void
   */
  public function uninstall($parent)
  {
    // echo '<p>' . JText::_('COM_EMUNDUS_ONBOARD_UNINSTALL_TEXT') . '</p>';
  }

  /**
   * This method is called after a component is updated.
   *
   * @param  \stdClass $parent - Parent object calling object.
   *
   * @return void
   */
  public function update($parent)
  {
    // echo '<p>' .
    //   JText::sprintf(
    //     'COM_EMUNDUS_ONBOARD_UPDATE_TEXT',
    //     $parent->get('manifest')->version
    //   ) .
    //   '</p>';
  }

  /**
   * Runs just before any installation action is performed on the component.
   * Verifications and pre-requisites should run in this function.
   *
   * @param  string    $type   - Type of PreFlight action. Possible values are:
   *                           - * install
   *                           - * update
   *                           - * discover_install
   * @param  \stdClass $parent - Parent object calling object.
   *
   * @return void
   */
  public function preflight($type, $parent)
  {
    $user = JFactory::getUser();
    $app = JFactory::getApplication();

    $token = JSession::getFormToken();

    echo '<p>Preflight</p>';
  }

  /**
   * Runs right after any installation action is performed on the component.
   *
   * @param  string    $type   - Type of PostFlight action. Possible values are:
   *                           - * install
   *                           - * update
   *                           - * discover_install
   * @param  \stdClass $parent - Parent object calling object.
   *
   * @return void
   */
  function postflight($type, $parent)
  {
    $user = JFactory::getUser();
    $app = JFactory::getApplication();
    $token = JSession::getFormToken();

    echo '<p>Installation terminée avec succès</p>';
    
    $db = JFactory::getDbo();

    $query =
      'SELECT id FROM jos_menu WHERE alias = "onboarding"';

    $db->setQuery($query);
    $parentId = $db->loadObject()->id;

    $db = JFactory::getDbo();

    $query =
      'SELECT extension_id FROM jos_extensions WHERE element = "com_emundus_onboard"';

    $db->setQuery($query);
    $componentId = $db->loadObject()->extension_id;

    $db = JFactory::getDbo();

    $query =
      'SELECT id FROM jos_template_styles WHERE template = "emundus"';
    
    $db->setQuery($query);
    $templateId = $db->loadObject()->id;

    echo '<p>Création des items</p>';

    // Création des items
    ?>
    <script type="text/javascript">
    // création de l'objet XMLHttRequest
    var ajax = null;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer")
        ajax = new ActiveXObject("Microsoft.XMLHTTP");
    else
        ajax = new XMLHttpRequest();

    // envoie de requête HTTP et reception de la réponse
    function requete(methode, chemin, parametre, callback){
      ajax.open(methode, chemin, true);
      ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
      ajax.onreadystatechange = function() {
          if(ajax.readyState == 4) {
              callback(ajax.responseText);
          }
      }
      ajax.send(parametre);
    }

    // traitement avant envoie
    function createItem(datas){
      var methode = "post";
      var data = datas;
      var chemin = "./index.php?option=com_menus&view=item&client_id=0&layout=edit&id=0";
      requete(methode, chemin, data, callbackMethod);
    }

    function callbackMethod(response) {
      if (i != 3) {
        i++;
        var link = "index.php?option=com_emundus_onboard&view=" + links[i];
        var datas = datass[i] + "jform[menutype]=coordinatormenu&jform[path]=" + paths[i] + "&jform[type]=component&jform[link]=" + encodeURIComponent(link) + "&jform[parent_id]=" + parentId + "&jform[level]=2&jform[published]=1&jform[home]=0&jform[access]=7&jform[language]=*&jform[checked_out]=0&jform[checked_out_time]=2020-04-01 16:30:00&jform[img]=&jform[template_style_id]=" + templateId + "&jform[params][menu-anchor_title]=&jform[params][menu-anchor_css]=&jform[params][menu_image]=&jform[params][menu_image_css]=&jform[params][menu_text]=1&jform[params][menu_show]=1&jform[params][page_title]=&jform[params][show_page_heading]=&jform[params][page_heading]=&jform[params][pageclass_sfx]=&jform[params][menu-meta_description]=&jform[params][menu-meta_keywords]=&jform[params][robots]=&jform[params][secure]=0&jform[component_id]=" + componentId + "&task=item.apply&" + [tokenMenu] + "=1";
        createItem(datas);
      }
    }

    var i = 0;
    var tokenMenu = '<?= $token ?>';
    var componentId = '<?= $componentId ?>';
    var templateId = '<?= $templateId ?>';
    var parentId = '<?= $parentId ?>';
    var links = ['campaign', 'program', 'email', 'formulaire'];
    var paths = ['onboarding/campaigns', 'onboarding/programs', 'onboarding/emails', 'onboarding/forms'];
    var datass = ['jform[title]=Campagne d\'appel&jform[alias]=campaigns&', 'jform[title]=Programme&jform[alias]=programs&', 'jform[title]=Email&jform[alias]=emails&', 'jform[title]=Formulaire&jform[alias]=forms&'];
    var link = "index.php?option=com_emundus_onboard&view=" + links[i];
    var datas = datass[i] + "jform[menutype]=coordinatormenu&jform[path]=" + paths[i] + "&jform[type]=component&jform[link]=" + encodeURIComponent(link) + "&jform[parent_id]=" + parentId + "&jform[level]=2&jform[published]=1&jform[home]=0&jform[access]=7&jform[language]=*&jform[checked_out]=0&jform[checked_out_time]=2020-04-01 16:30:00&jform[img]=&jform[template_style_id]=" + templateId + "&jform[params][menu-anchor_title]=&jform[params][menu-anchor_css]=&jform[params][menu_image]=&jform[params][menu_image_css]=&jform[params][menu_text]=1&jform[params][menu_show]=1&jform[params][page_title]=&jform[params][show_page_heading]=&jform[params][page_heading]=&jform[params][pageclass_sfx]=&jform[params][menu-meta_description]=&jform[params][menu-meta_keywords]=&jform[params][robots]=&jform[params][secure]=0&jform[component_id]=" + componentId + "&task=item.apply&" + [tokenMenu] + "=1";
    createItem(datas);
    </script>
    
    <?php echo 'Items créés avec succès';
  }
}
