<?php 
defined('_JEXEC') or die('Restricted access');

$params = JComponentHelper::getParams( 'com_falang' );
$downloadid = $params->get('downloadid');
$version = new FalangVersion();

?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if (FALANG_J30) {  ?>
        <?php if (!empty( $this->sidebar)): ?>
            <div id="j-sidebar-container" class="span2">
                <?php echo $this->sidebar; ?>
            </div>
            <div id="j-main-container" class="span10">
            <?php else : ?>
                <div id="j-main-container">
            <?php endif;?>
                    <?php if (empty($downloadid)) { ?>
                    <div class="alert alert-info">
                        <h4 class="alert-heading"><?php echo JText::_('COM_FALANG_CPANEL_NEEDSAUTH_NOTICE'); ?></h4>
                        <p>
                            <?php echo JText::_('COM_FALANG_CPANEL_NEEDSAUTH'); ?>
                            <a target="_blank"  href="http://www.faboba.com/index.php?option=com_content&view=article&id=39"><?php echo JText::_('COM_FALANG_CPANEL_NEEDSAUTH_LINK'); ?></a>
                        </p>
                    </div>
                    <?php } ?>

                    <?php if($version->_versiontype == 'free') { ?>
                        <div class="alert alert-info">
                            <h4 class="alert-heading"><?php echo JText::_('COM_FALANG_CPANEL_FREE_MSG_TITLE');?></h4>
                            <p>
                                <?php echo JText::_('COM_FALANG_CPANEL_FREE_MSG'); ?>
                            </p>
                        </div>
                    <?php } ?>

                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#version" data-toggle="tab"><?php echo JText::_('COM_FALANG_CPANEL_VERSION');?></a></li>
                        <li><a href="#information" data-toggle="tab"><?php echo JText::_('COM_FALANG_CPANEL_INFORMATION');?></a></li>
                    </ul>
                    <div class="tab-content">
                        <!-- Begin Tabs -->
                        <div class="tab-pane active" id="version">
                            <?php echo $this->loadTemplate('version'); ?>
                        </div>
                        <div class="tab-pane " id="information">
                            <?php echo $this->loadTemplate('information'); ?>
                        </div>
                        <!-- End Tabs -->
                    </div>
            </div>

    <?php } else { ?>

        <!-- Message for joomla 2.5-->
        <dl id="system-message">
            <?php if (empty($downloadid)) { ?>
                <dd class="notice message">
                    <ul>
                        <li>
                            <?php echo JText::_('COM_FALANG_CPANEL_NEEDSAUTH'); ?>
                            <a target="_blank"  href="http://www.faboba.com/index.php?option=com_content&view=article&id=39"><?php echo JText::_('COM_FALANG_CPANEL_NEEDSAUTH_LINK'); ?></a>
                        </li>
                    </ul>
                </dd>
            <?php } ?>

            <?php if($version->_versiontype == 'free') { ?>
                <dd class="notice message">
                    <ul>
                        <li><?php echo JText::_('COM_FALANG_CPANEL_FREE_MSG'); ?></li>
                    </ul>
                </dd>
            <?php } ?>
        </dl>

        <div class="width-35 fltlft">
            <div id="cpanel">
                <?php

                $link = 'index.php?option=com_falang&amp;task=translate.overview';
                $this->_quickiconButton( $link, 'icon-48-translation.png', JText::_('COM_FALANG_CPANEL_QBT_TRANSLATION') );

                $link = 'index.php?option=com_falang&amp;task=translate.orphans';
                $this->_quickiconButton( $link, 'icon-48-orphan.png', JText::_('COM_FALANG_CPANEL_QBT_ORPHANS') );

                ?>
                <div style="clear: both;"></div>
                <?php
                $link = 'index.php?option=com_falang&amp;task=elements.show';
                $this->_quickiconButton( $link, 'icon-48-extension.png', JText::_('COM_FALANG_CPANEL_QBT_CONTENT_ELEMENTS') );

                $link = 'index.php?option=com_falang&amp;task=help.show';
                $this->_quickiconButton( $link, 'icon-48-help.png', JText::_('COM_FALANG_CPANEL_QBT_HELP_AND_HOWTO') );
                ?>
            </div>
        </div>
        <div class="width-65 fltrt">
            <?php
            echo JHtml::_('sliders.start', 'falang-slider');
            //version
            echo JHtml::_('sliders.panel', JText::_('COM_FALANG_CPANEL_VERSION'), 'version-panel');
            echo $this->loadTemplate('version');

            //information
            echo JHtml::_('sliders.panel', JText::_('COM_FALANG_CPANEL_INFORMATION'), 'information-panel');
            echo $this->loadTemplate('information');
            echo JHtml::_('sliders.end');
            ?>
        </div>
        <div style="clear: both;"></div>
    <?php } ?>


<input type="hidden" name="option" value="com_falang" />
<input type="hidden" name="task" value="cpanel.show" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" />
</form>
