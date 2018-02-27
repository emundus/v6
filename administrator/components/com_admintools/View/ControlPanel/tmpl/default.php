<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

// Protect from unauthorized access
defined('_JEXEC') or die;

?>
<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/warnings'); ?>

<div>
	<div class="akeeba-container--50-50">
		<div>
			<?php if ($this->isRescueMode): ?>
				<div class="akeeba-block--failure">
					<div>
						<h3><?php echo JText::_('COM_ADMINTOOLS_CONTROLPANEL_RESCUEMODE_HEAD') ?></h3>
						<p>
							<?php echo JText::_('COM_ADMINTOOLS_CONTROLPANEL_RESCUEMODE_MESSAGE'); ?>
						</p>
						<p>
							<a class="akeeba-btn--primary"
							   href="https://www.akeebabackup.com/documentation/troubleshooter/atwafissues.html"
							   target="_blank"
							>
								<span class="akion-information-circled"></span>
								<?php echo JText::_('COM_ADMINTOOLS_CONTROLPANEL_RESCUEMODE_BTN_HOWTOUNBLOCK'); ?>
							</a>
							<a class="akeeba-btn--red"
							   href="index.php?option=com_admintools&view=ControlPanel&task=endRescue"
							>
								<span class="akion-power"></span>
								<?php echo JText::_('COM_ADMINTOOLS_CONTROLPANEL_RESCUEMODE_BTN_ENDRESCUE'); ?>
							</a>
						</p>
					</div>
				</div>
			<?php else: ?>
				<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/plugin_warning') ?>
				<?php endif; ?>

			<div id="selfBlocked" class="text-center" style="display: none;">
				<a class="akeeba-btn--red--big" href="<?php echo \JRoute::_('index.php?option=com_admintools&view=ControlPanel&task=unblockme'); ?>">
					<span class="akion-unlocked"></span>
					<?php echo \JText::_('COM_ADMINTOOLS_CONTROLPANEL_UNBLOCK_ME'); ?>
				</a>
			</div>

			<?php if (!$this->hasValidPassword): ?>
			<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/masterpassword'); ?>
			<?php endif; ?>

			<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/security'); ?>
			<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/tools'); ?>

			<?php if (ADMINTOOLS_PRO && !$this->needsQuickSetup): ?>
			<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/quicksetup'); ?>
			<?php endif; ?>
		</div>

		<div>
            <div class="akeeba-panel--default">
                <header class="akeeba-block-header">
				    <h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_UPDATES'); ?></h3>
                </header>

				<div>
					<p>
						Admin Tools version <?php echo ADMINTOOLS_VERSION; ?> &bull;
						<a href="#" id="btnAdminToolsChangelog" class="akeeba-btn--primary--small">CHANGELOG</a>
						<a href="index.php?option=com_admintools&view=ControlPanel&task=reloadUpdateInformation" class="akeeba-btn--dark--small">
							<?php echo \JText::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_RELOADUPDATE'); ?>
						</a>
					</p>

					<p>Copyright &copy; 2010&ndash;<?php echo date('Y'); ?> Nicholas K. Dionysopoulos / <a
								href="https://www.akeebabackup.com">Akeeba Ltd</a></p>
					<p>
						If you use Admin Tools <?php echo ADMINTOOLS_PRO ? 'Professional' : 'Core'; ?>, please post a
						rating and a review at the <a
								href="http://extensions.joomla.org/extensions/extension/access-a-security/site-security/admin-tools<?php echo ADMINTOOLS_PRO ? '-professional' : ''; ?>">Joomla!
							Extensions Directory</a>.
					</p>
				</div>

                <div id="akeeba-changelog" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
                    <div class="akeeba-renderer-fof">
                        <div class="akeeba-panel--info">
                            <header class="akeeba-block-header">
                                <h3>
						            <?php echo \JText::_('CHANGELOG'); ?>
                                </h3>
                            </header>
                            <div id="DialogBody">
					            <?php echo $this->formattedChangelog; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!$this->isPro): ?>
                <div style="text-align: center;">
                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                        <input type="hidden" name="cmd" value="_s-xclick">
                        <input type="hidden" name="hosted_button_id" value="6ZLKK32UVEPWA">

                        <p>
                            <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif" border="0"
                                   name="submit" alt="PayPal - The safer, easier way to pay online." style="width: 73px;">
                            <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                        </p>
                    </form>
                </div>
            <?php endif; ?>

			<?php if ($this->isPro && $this->showstats): ?>
				<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/graphs'); ?>
				<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/stats'); ?>
			<?php endif; ?>

			<div id="disclaimer" class="akeeba-block--info">
				<h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_DISCLAIMER'); ?></h3>

				<p>
					<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_DISTEXT'); ?>
				</p>
			</div>
		</div>
	</div>
</div>

<?php echo !empty($this->statsIframe) ? $this->statsIframe : ''; ?>
