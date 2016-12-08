<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

// Protect from unauthorized access
defined('_JEXEC') or die;

JHtml::_('behavior.modal');
?>
<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/warnings'); ?>

<div id="restOfCPanel">
	<div class="row-fluid">
		<div class="akeeba-cpanel span6">
			<div id="selfBlocked" class="text-center" style="display: none;">
				<a class="btn btn-large btn-danger" href="<?php echo \JRoute::_('index.php?option=com_admintools&view=ControlPanel&task=unblockme'); ?>">
					<span class="icon icon-unlock"></span>
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

		<div id="sidepanes" class="span6">
			<div class="well">
				<h3>
					<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_UPDATES'); ?>
				</h3>

				<div>
					<!-- CHANGELOG :: BEGIN -->
					<p>
						Admin Tools version <?php echo ADMINTOOLS_VERSION; ?> &bull;
						<a href="#" id="btnchangelog" class="btn btn-mini">CHANGELOG</a>
						<a href="index.php?option=com_admintools&view=ControlPanel&task=reloadUpdateInformation" class="btn btn-inverse btn-small">
							<?php echo \JText::_('COM_ADMINTOOLS_MSG_CONTROLPANEL_RELOADUPDATE'); ?>
						</a>
					</p>

					<div class="modal fade" id="akeeba-changelog" tabindex="-1" role="dialog" aria-labelledby="changelogDialogLabel" aria-hidden="true" style="display:none;">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
									<h4 class="modal-title" id="changelogDialogLabel">
										<?php echo \JText::_('CHANGELOG'); ?>
									</h4>
								</div>
								<div class="modal-body" id="DialogBody">
									<?php echo $this->changeLog; ?>

								</div>
							</div>
						</div>
					</div>
					<!-- CHANGELOG :: END -->

					<p>Copyright &copy; 2010&ndash;<?php echo date('Y'); ?> Nicholas K. Dionysopoulos / <a
							href="https://www.akeebabackup.com">Akeeba Ltd</a></p>
					<p>
						If you use Admin Tools <?php echo ADMINTOOLS_PRO ? 'Professional' : 'Core'; ?>, please post a
						rating and a review at the <a
								href="http://extensions.joomla.org/extensions/extension/access-a-security/site-security/admin-tools<?php echo ADMINTOOLS_PRO ? '-professional' : ''; ?>">Joomla!
							Extensions Directory</a>.
					</p>
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
			</div>

			<?php if ($this->isPro && $this->showstats): ?>
				<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/graphs'); ?>
				<?php echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/stats'); ?>
			<?php endif; ?>

			<div id="disclaimer" class="alert alert-info" style="margin-top: 2em;">
				<a class="close" data-dismiss="alert" href="#">×</a>

				<h3>
					<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_DISCLAIMER'); ?>
				</h3>

				<p>
					<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_DISTEXT'); ?>
				</p>
			</div>
		</div>
	</div>
</div>

<?php echo !empty($this->statsIframe) ? $this->statsIframe : ''; ?>


<div class="clearfix"></div>
