<?php
defined('_JEXEC') or die('Access Deny');
require_once(dirname(__FILE__).DS.'helper.php');

JHtml::script('media/com_emundus/js/jquery.cookie.js');
JHtml::script('media/jui/js/bootstrap.min.js');

$helper = new modEmundusUpdateHelper;

if(EmundusHelperAccess::asCoordinatorAccessLevel(JFactory::getUser()->id)) {
    /*TODO: Add request to site where we're gonna get the version number, article and important
    {
        "version": "3.X.X",
        "article": "linkToArticle",
        "import" : 1 or 0
    }
    use json_decode.
    */
    $update = (object)[
        "version"=>"3.8",
        "article"=>"https://www.emundus.fr",
        "important"=>1
    ];

    // Get the version in their db 
    $siteVersion = $helper->checkVersion();

    if($update->version != $siteVersion->version && $update->version != $siteVersion->ignore){
        require(JModuleHelper::getLayoutPath('mod_emundus_update','default.php'));
    }

    
}


