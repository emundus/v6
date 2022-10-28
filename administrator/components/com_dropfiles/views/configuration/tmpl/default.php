<?php
defined('_JEXEC') || die;
$settings = array(
    'main' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_MAIN_ADMIN_LABEL'),
        'icon' => 'icon-main-settings.svg'
    ),
    'main_frontend' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_FRONTEND_LABEL'),
        'icon' => ''
    ),
    'main_advanced' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_ADVANCED_NAME'),
        'icon' => ''
    ),
    'search' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIG_SEARCH_LABEL'),
        'icon' => 'icon-search-upload.svg'
    ),
    'default_theme' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_THEME_DEFAULT_LABEL'),
        'icon' => 'icon-themes.svg'
    ),
    'ggd_theme' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_THEME_GGD_LABEL'),
        'icon' => 'icon-themes.svg'
    ),
    'theme_table' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_THEME_TABLE_LABEL'),
        'icon' => 'icon-themes.svg'
    ),
    'tree_theme' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_THEME_TREE_LABEL'),
        'icon' => 'tree-theme.svg'
    ),
    'clonetheme' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIG_CLONE_THEME_LABEL'),
        'icon' => 'icon-clone-theme.svg'
    ),
    'single_file' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIG_SINGLE_FILE_LABEL'),
        'icon' => 'icon-single-file.svg'
    ),
    'cloud_connection' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_GOOGLE_DRIVE'),
        'icon' => 'icon-cloud-config.svg'
    ),
    'cloud_dropbox' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_DROPBOX_DRIVE'),
        'icon' => ''
    ),
    'cloud_onedrive' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_ONE_DRIVE'),
        'icon' => ''
    ),
    'cloud_onedrive_business' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_ONE_DRIVE_BUSINESS'),
        'icon' => ''
    ),
    'docmanimport' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIG_IMPORT_TAB_LABEL'),
        'icon' => 'icon-import-export.svg'
    ),
    'notification' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIG_NOTIFICATION'),
        'icon' => 'icon-email-notification.svg'
    ),
    'permissions' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_PERMISSIONS_NAME'),
        'icon' => 'icon-user-roles.svg'
    ),
    'jutranslation' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_NAV_NAME_TRANSLATIONS'),
        'icon' => 'icon-translate.svg'
    ),
    'liveupdate' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_NAV_NAME_LIVE_UPDATES'),
        'icon' => 'icon-update.svg'
    ),
    'support' => array(
        'nav_name' => JText::sprintf('COM_DROPFILES_CONFIGURATION_SUPPORT_NAME'),
        'icon' => 'icons-help-outline.svg'
    )
);

JHtml::_('behavior.formvalidator');
JFactory::getDocument()->addScriptDeclaration("
    Joomla.submitbutton = function(task)
    {
        if (task == 'wizard.cancel' || document.formvalidator.isValid(document.getElementById('configuration-form')))
        {
            Joomla.submitform(task, document.getElementById('configuration-form'));
        }
    };
");
?>

<form id="configuration-form" method="POST" action="<?php echo JRoute::_('index.php?option=com_dropfiles&view=configuration'); ?>">
    <div class="ju-main-wrapper">
        <div class="ju-left-panel-toggle">
            <i class="material-icons ju-left-panel-toggle-icon" style="font-size: 20px">code</i>
        </div>
        <div class="ju-left-panel">
            <div class="ju-logo">
                <a href="https://www.joomunited.com/products/dropfiles" target="_blank">
                    <img src="<?php echo JUri::root(true) . '/administrator/components/com_dropfiles/assets/joomla-css-framework/images/logo-joomUnited-white.png' ?>"
                         srcset="<?php echo JUri::root(true) . '/administrator/components/com_dropfiles/assets/joomla-css-framework/images/logo-joomUnited-white.png' ?>"
                         alt="<?php echo JText::sprintf('COM_DROPFILES_CONFIGURATION_JU_LOGO') ?>">
                </a>
            </div>
            <div class="ju-menu-search">
                <i class="material-icons mi mi-search ju-menu-search-icon">search</i>
                <input type="text" class="ju-menu-search-input" size="16" placeholder="Search settings">
            </div>
            <ul class="tabs ju-menu-tabs">
                <li class="tab main-settings-list-tab parent-tabs">
                    <a href="#main" class="link-tab white-text waves-effect waves-light" id="mainparentlink">
                        <img class="menu-icons" src="<?php echo JURI::root() . 'administrator/components/com_dropfiles/assets/images/configuration/icon-main-settings.svg'?>"  title="" />
                        <span class="name tab-title"><?php echo JText::sprintf('COM_DROPFILES_CONFIG_MAIN_LABEL') ?></span>
                    </a>
                    <ul class="theme-list">
                        <?php foreach ($settings as $k => $v) : ?>
                            <?php if (in_array($k, array('main', 'main_frontend', 'main_advanced'))) : ?>
                                <li class="tab main-tab" data-tab-title="<?php echo $v['nav_name'] ?>">
                                    <a href="#<?php echo $k; ?>" class="link-tab white-text waves-effect waves-light" id="<?php echo $k; ?>linktab">
                                        <span class="name tab-title"><?php echo $v['nav_name']; ?></span>
                                    </a>
                                </li>
                            <?php endif;?>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <?php foreach ($settings as $k => $v) : ?>
                    <?php if (in_array($k, array('search'))) : ?>
                        <li class="tab main-tab" data-tab-title="<?php echo $v['nav_name'] ?>">
                            <a href="#<?php echo $k; ?>" class="link-tab white-text waves-effect waves-light" id="<?php echo $k; ?>linktab">
                                <img class="menu-icons" src="<?php echo JURI::root() . 'administrator/components/com_dropfiles/assets/images/configuration/'. $v['icon']?>"  title="" />
                                <span class="name tab-title"><?php echo $v['nav_name']; ?></span>
                            </a>
                        </li>
                    <?php endif;?>
                <?php endforeach; ?>

                <li class="tab theme-list-tab parent-tabs">
                    <a href="#default_theme" class="link-tab white-text waves-effect waves-light" id="themeparentlink">
                        <img class="menu-icons" src="<?php echo JURI::root() . 'administrator/components/com_dropfiles/assets/images/configuration/icon-themes.svg'?>"  title="" />
                        <span class="name tab-title"><?php echo JText::sprintf('COM_DROPFILES_CONFIGURATION_THEMES_GROUP_NAME') ?></span>
                    </a>
                    <ul class="theme-list">
                    <?php foreach ($settings as $k => $v) : ?>
                        <?php if (in_array($k, array('default_theme', 'ggd_theme', 'theme_table', 'tree_theme'))) : ?>
                                    <li class="tab main-tab theme-item" data-tab-title="<?php echo $v['nav_name'] ?>">
                                        <a href="#<?php echo $k; ?>" class="link-tab white-text waves-effect waves-light" id="<?php echo $k; ?>linktab">
                                            <span class="name tab-title"><?php echo $v['nav_name']; ?></span>
                                        </a>
                                    </li>
                        <?php endif;?>
                    <?php endforeach; ?>
                    </ul>
                </li>

                <?php foreach ($settings as $k => $v) : ?>
                    <?php if (in_array($k, array('clonetheme', 'single_file'))) : ?>
                        <li class="tab main-tab" data-tab-title="<?php echo $v['nav_name'] ?>">
                            <a href="#<?php echo $k; ?>" class="link-tab white-text waves-effect waves-light" id="<?php echo $k; ?>linktab">
                                <img class="menu-icons" src="<?php echo JURI::root() . 'administrator/components/com_dropfiles/assets/images/configuration/'. $v['icon']?>"  title="" />
                                <span class="name tab-title"><?php echo $v['nav_name']; ?></span>
                            </a>
                        </li>
                    <?php endif;?>
                <?php endforeach; ?>

                <li class="tab cloud-connection-tab parent-tabs">
                    <a href="#cloud_connection" class="link-tab white-text waves-effect waves-light" id="cloudparentlink">
                        <img class="menu-icons" src="<?php echo JURI::root() . 'administrator/components/com_dropfiles/assets/images/configuration/icon-cloud-config.svg'?>"  title="" />
                        <span class="name tab-title"><?php echo JText::sprintf('COM_DROPFILES_CONFIG_CLOUD_CONNECTION_LABEL') ?></span>
                    </a>
                    <ul class="theme-list">
                        <?php foreach ($settings as $k => $v) : ?>
                            <?php if (in_array($k, array('cloud_connection','cloud_dropbox', 'cloud_onedrive', 'cloud_onedrive_business'))) : ?>
                                <li class="tab main-tab theme-item" data-tab-title="<?php echo $v['nav_name'] ?>">
                                    <a href="#<?php echo $k; ?>" class="link-tab white-text waves-effect waves-light" id="<?php echo $k; ?>linktab">
                                        <span class="name tab-title"><?php echo $v['nav_name']; ?></span>
                                    </a>
                                </li>
                            <?php endif;?>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <?php foreach ($settings as $k => $v) : ?>
                    <?php if (!in_array($k, array('main', 'main_frontend', 'main_advanced', 'default_theme', 'ggd_theme', 'theme_table', 'tree_theme', 'search', 'clonetheme', 'single_file', 'cloud_connection', 'cloud_dropbox', 'cloud_onedrive', 'cloud_onedrive_business'))) : ?>
                        <li class="tab main-tab" data-tab-title="<?php echo $v['nav_name'] ?>">
                            <a href="#<?php echo $k; ?>" class="link-tab white-text waves-effect waves-light" id="<?php echo $k; ?>linktab">
                                <img class="menu-icons" src="<?php echo JURI::root() . 'administrator/components/com_dropfiles/assets/images/configuration/'. $v['icon']?>"  title="" />
                                <span class="name tab-title"><?php echo $v['nav_name']; ?></span>
                            </a>
                        </li>
                    <?php endif;?>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="ju-right-panel">
            <div id="main-settings-top-tabs" class="ju-top-tabs-wrapper" style="display: none" >
                <ul class="tabs ju-top-tabs" style="width: 100%;">
                    <?php foreach ($settings as $k => $v) : ?>
                        <?php if (in_array($k, array('main', 'main_frontend', 'main_advanced'))) :?>
                                    <li class="tab" data-tab-title="<?php echo $v['nav_name'] ?>">
                                        <a href="#<?php echo $k; ?>" class="link-tab" id="<?php echo $k; ?>jutoplink"><?php echo $v['nav_name']; ?></a>
                                    </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="indicator" style="right: 400px; left: 0;"></div>
                </ul>
            </div>

            <div id="theme-settings-top-tabs" class="ju-top-tabs-wrapper" style="display: none">
                <ul class="tabs ju-top-tabs" style="width: 100%;">
                    <?php foreach ($settings as $k => $v) : ?>
                        <?php if (in_array($k, array('default_theme', 'ggd_theme', 'theme_table', 'tree_theme'))) :?>
                                    <li class="tab" data-tab-title="<?php echo $v['nav_name'] ?>">
                                        <a href="#<?php echo $k; ?>" class="link-tab" id="<?php echo $k; ?>jutoplink"><?php echo $v['nav_name']; ?></a>
                                    </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="indicator" style="right: 400px; left: 0;"></div>
                </ul>
            </div>

            <div id="cloud-settings-top-tabs" class="ju-top-tabs-wrapper" style="display: none">
                <ul class="tabs ju-top-tabs" style="width: 100%;">
                    <?php foreach ($settings as $k => $v) : ?>
                        <?php if (in_array($k, array('cloud_connection', 'cloud_dropbox', 'cloud_onedrive', 'cloud_onedrive_business'))) :?>
                            <li class="tab" data-tab-title="<?php echo $v['nav_name'] ?>">
                                <a href="#<?php echo $k; ?>" class="link-tab" id="<?php echo $k; ?>jutoplink"><?php echo $v['nav_name']; ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <div class="indicator" style="right: 400px; left: 0;"></div>
                </ul>
            </div>

            <?php
            $message = JFactory::getSession()->get('sc_configuration_message');
            if ($message) :
                JFactory::getSession()->clear('sc_configuration_message');
                ?>
                <div id="ju-message-container">
                    <div class="alert alert-<?php echo $message['type'] ?>">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <h4 class="alert-heading">Message</h4>
                        <div class="alert-message"><?php echo JText::sprintf($message['text']); ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($settings as $k => $v) : ?>
                <div class="ju-content-wrapper" id="<?php echo ($k); ?>" style="display: none">
                    <?php echo $this->loadTemplate($k); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="clear"></div>
    </div>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="jform[component]" value="com_dropfiles" />
    <?php echo JHtml::_('form.token'); ?>
</form>

