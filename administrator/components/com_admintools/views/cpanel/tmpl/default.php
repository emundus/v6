<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

/** @var  AdmintoolsViewCpanel $this For type hinting in the IDE */

// Protect from unauthorized access
defined('_JEXEC') or die;

JHtml::_('behavior.modal');

F0FTemplateUtils::addCSS('media://com_admintools/css/jquery.jqplot.min.css');

AkeebaStrapper::addJSfile('media://com_admintools/js/excanvas.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jquery.jqplot.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.highlighter.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.dateAxisRenderer.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.barRenderer.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.pieRenderer.min.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/jqplot.hermite.js?' . ADMINTOOLS_VERSION);
AkeebaStrapper::addJSfile('media://com_admintools/js/cpanelgraphs.js?' . ADMINTOOLS_VERSION);

$lang = JFactory::getLanguage();
$option = 'com_admintools';
$isPro = $this->isPro;

$root = @realpath(JPATH_ROOT);
$root = trim($root);
$emptyRoot = empty($root);

$confirm = JText::_('ATOOLS_LBL_PURGESESSIONS_WARN', true);
$script = <<<JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
(function($){
	$(document).ready(function(){
		$('#optimize').click(warnBeforeOptimize);
		$('#btnchangelog').click(showChangelog);
	});

	function warnBeforeOptimize(e)
	{
		if(!confirm('$confirm'))
		{
			e.preventDefault();
			return false;
		}
	}

	function showChangelog()
	{
		var akeebaChangelogElement = $('#akeeba-changelog').clone().appendTo('body').attr('id', 'akeeba-changelog-clone');

		SqueezeBox.fromElement(
			document.getElementById('akeeba-changelog-clone'), {
				handler: 'adopt',
				size: {
					x: 550,
					y: 500
				}
			}
		);
	}
})(akeeba.jQuery);

JS;
$document = JFactory::getDocument();
$document->addScriptDeclaration($script, 'text/javascript');

$db = JFactory::getDBO();

?>
<?php
	// Obsolete PHP version check
	if (version_compare(PHP_VERSION, '5.4.0', 'lt')):
	JLoader::import('joomla.utilities.date');
	$akeebaCommonDatePHP = new JDate('2014-08-14 00:00:00', 'GMT');
	$akeebaCommonDateObsolescence = new JDate('2015-05-14 00:00:00', 'GMT');
?>
<div id="phpVersionCheck" class="alert alert-warning">
	<h3><?php echo JText::_('AKEEBA_COMMON_PHPVERSIONTOOOLD_WARNING_TITLE'); ?></h3>
	<p>
		<?php echo JText::sprintf(
			'AKEEBA_COMMON_PHPVERSIONTOOOLD_WARNING_BODY',
			PHP_VERSION,
			$akeebaCommonDatePHP->format(JText::_('DATE_FORMAT_LC1')),
			$akeebaCommonDateObsolescence->format(JText::_('DATE_FORMAT_LC1')),
			'5.5'
			);
		?>
	</p>
</div>
<?php endif; ?>


<div id="fastcheckNotice" class="alert alert-danger" style="display: none">
	<h3><?php echo JText::_('COM_ADMINTOOLS_CPANEL_ERR_CORRUPT_HEAD') ?></h3>
	<p>
		<?php echo JText::_('COM_ADMINTOOLS_CPANEL_ERR_CORRUPT_INFO') ?>
	</p>
	<p>
		<?php echo JText::_('COM_ADMINTOOLS_CPANEL_ERR_CORRUPT_DONTPANIC') ?>
	</p>
	<p>
		<?php echo JText::_('COM_ADMINTOOLS_CPANEL_ERR_CORRUPT_MOREINFO') ?>
	</p>
	<p>
		<a href="index.php?option=com_admintools&view=checkfiles" class="btn btn-large btn-primary">
			<?php echo JText::_('COM_ADMINTOOLS_CPANEL_CORRUPT_RUNFILES') ?>
		</a>
	</p>
</div>

<div id="restOfCPanel">
<?php if ($this->oldVersion): ?>
	<div class="alert alert-warning">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<strong><?php echo JText::_('COM_ADMINTOOLS_CPANEL_ERR_OLDVERSION') ?></strong>
	</div>
<?php endif; ?>

<?php if ($emptyRoot): ?>
	<div class="alert alert-error">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo JText::_('ATOOLS_LBL_CP_EMPTYROOT'); ?>
	</div>
<?php endif; ?>

<?php if ($this->needsdlid && $this->isPro): ?>
	<div class="alert alert-error">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo JText::sprintf('ATOOLS_LBL_CP_NEEDSDLID', 'https://www.akeebabackup.com/instructions/1436-admin-tools-download-id.html'); ?>
	</div>
<?php endif; ?>

<div id="updateNotice"></div>

<?php if (!$this->hasplugin && $isPro): ?>
	<div class="well">
		<h3><?php echo JText::_('ATOOLS_GEOBLOCK_LBL_GEOIPPLUGINSTATUS') ?></h3>

		<p><?php echo JText::_('ATOOLS_GEOBLOCK_LBL_GEOIPPLUGINMISSING') ?></p>

		<a class="btn btn-primary" href="https://www.akeebabackup.com/download/akgeoip.html" target="_blank">
			<span class="icon icon-white icon-download-alt"></span>
			<?php echo JText::_('ATOOLS_GEOBLOCK_LBL_DOWNLOADGEOIPPLUGIN') ?>
		</a>
	</div>
<?php elseif ($this->pluginNeedsUpdate && $isPro): ?>
	<div class="well well-small">
		<h3><?php echo JText::_('ATOOLS_GEOBLOCK_LBL_GEOIPPLUGINEXISTS') ?></h3>

		<p><?php echo JText::_('ATOOLS_GEOBLOCK_LBL_GEOIPPLUGINCANUPDATE') ?></p>

		<a class="btn btn-small"
		   href="index.php?option=com_admintools&view=cpanel&task=updategeoip&<?php echo JFactory::getSession()->getFormToken(); ?>=1">
			<span class="icon icon-retweet"></span>
			<?php echo JText::_('ATOOLS_GEOBLOCK_LBL_UPDATEGEOIPDATABASE') ?>
		</a>
	</div>
<?php endif; ?>

<?php if($this->hasPostInstallationMessages): ?>
	<div class="alert alert-info">
		<h3>
			<?php echo JText::_('COM_ADMINTOOLS_CPANEL_PIM_TITLE'); ?>
		</h3>
		<p>
			<?php echo JText::_('COM_ADMINTOOLS_CPANEL_PIM_DESC'); ?>
		</p>
		<a href="index.php?option=com_postinstall&eid=<?php echo $this->extension_id?>"
		   class="btn btn-primary btn-large">
			<?php echo JText::_('AKEEBA_CPANEL_PIM_BUTTON'); ?>
		</a>
	</div>
<?php elseif(is_null($this->hasPostInstallationMessages)): ?>
	<div class="alert alert-error">
		<h3>
			<?php echo JText::_('COM_ADMINTOOLS_CPANEL_PIM_ERROR_TITLE'); ?>
		</h3>
		<p>
			<?php echo JText::_('COM_ADMINTOOLS_CPANEL_PIM_ERROR_DESC'); ?>
		</p>
		<a href="https://www.akeebabackup.com/documentation/troubleshooter/abpimerror.html"
		   class="btn btn-primary btn-large">
			<?php echo JText::_('COM_ADMINTOOLS_CPANEL_PIM_ERROR_BUTTON'); ?>
		</a>
	</div>
<?php endif; ?>

<div class="row-fluid">

<div id="cpanel" class="span6">

<?php if (!$this->hasValidPassword): ?>
	<form action="index.php" method="post" name="adminForm" id="adminForm" class="well">
		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="cpanel"/>
		<input type="hidden" name="task" value="login"/>

		<h3><?php echo JText::_('ATOOLS_LBL_CP_MASTERPWHEAD') ?></h3>

		<p class="help-block"><?php echo JText::_('ATOOLS_LBL_CP_MASTERPWINTRO') ?></p>
		<label for="userpw"><?php echo JText::_('ATOOLS_LBL_CP_MASTERPW') ?></label>
		<input type="password" name="userpw" id="userpw" value=""/>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary"/>
		</div>
	</form>
<?php endif; ?>

<h2><?php echo JText::_('ATOOLS_LBL_CP_SECURITY') ?></h2>

<?php if ($this->htMakerSupported): ?>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=eom">
				<img
					src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/eom-32.png"
					border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_EOM') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_EOM') ?><br/>
					</span>
			</a>
		</div>
	</div>
<?php endif; ?>

<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
	<div class="icon">
		<a href="index.php?option=<?php echo $option ?>&view=masterpw">
			<img
				src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/wafconfig-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_MASTERPW') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_MASTERPW') ?><br/>
					</span>
		</a>
	</div>
</div>

<?php if ($this->htMakerSupported): ?>
	<?php $icon = $this->adminLocked ? 'locked' : 'unlocked'; ?>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=adminpw">
				<img
					src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/adminpw-<?php echo $icon ?>-32.png"
					border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_ADMINPW') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_ADMINPW') ?><br/>
					</span>
			</a>
		</div>
	</div>
<?php endif; ?>

<?php if ($isPro): ?>
	<?php if ($this->htMakerSupported): ?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="index.php?option=<?php echo $option ?>&view=htmaker">
					<img
						src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/htmaker-32.png"
						border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_HTMAKER') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_HTMAKER') ?><br/>
					</span>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<?php if ($this->nginxMakerSupported): ?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="index.php?option=<?php echo $option ?>&view=nginxmaker">
					<img
						src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/htmaker-32.png"
						border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_NGINXMAKER') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_NGINXMAKER') ?><br/>
					</span>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=waf">
				<img
					src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/waf-32.png"
					border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_WAF') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_WAF') ?><br/>
					</span>
			</a>
		</div>
	</div>
<?php endif; ?>

<?php if ($isPro): ?>
	<div class="icon">
		<a href="index.php?option=<?php echo $option ?>&view=scans">
			<img
				src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/scans-32.png"
				border="0" alt="<?php echo JText::_('COM_ADMINTOOLS_TITLE_SCANS') ?>"/>
				<span>
					<?php echo JText::_('COM_ADMINTOOLS_TITLE_SCANS') ?><br/>
				</span>
		</a>
	</div>
<?php endif; ?>

<div style="clear: both;"></div>

<h2><?php echo JText::_('ATOOLS_LBL_CP_TOOLS') ?></h2>

<?php if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'): ?>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=fixpermsconfig">
				<img
					src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/fixpermsconfig-32.png"
					border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_FIXPERMSCONFIG') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_FIXPERMSCONFIG') ?><br/>
					</span>
			</a>
		</div>
	</div>

	<?php if ($this->enable_fixperms): ?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="index.php?option=<?php echo $option ?>&view=fixperms&tmpl=component" class="modal"
				   rel="{handler: 'iframe', size: {x: 600, y: 250}}">
					<img
						src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/fixperms-32.png"
						border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_FIXPERMS') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_FIXPERMS') ?><br/>
					</span>
				</a>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>

<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
	<div class="icon">
		<a href="index.php?option=<?php echo $option ?>&view=seoandlink">
			<img
				src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/seoandlink-32.png"
				border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_SEOANDLINK') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_SEOANDLINK') ?><br/>
					</span>
		</a>
	</div>
</div>

<?php if ($this->enable_cleantmp): ?>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=cleantmp&tmpl=component" class="modal"
			   rel="{handler: 'iframe', size: {x: 600, y: 250}}">
				<img
					src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/cleantmp-32.png"
					border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_CLEANTMP') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_CLEANTMP') ?><br/>
					</span>
			</a>
		</div>
	</div>
<?php endif; ?>

<?php if ($this->enable_dbtools && $this->isMySQL): ?>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=dbtools&task=optimize&tmpl=component" class="modal"
			   rel="{handler: 'iframe', size: {x: 600, y: 250}}">
				<img
					src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/dbtools-optimize-32.png"
					border="0" alt="<?php echo JText::_('ATOOLS_LBL_OPTIMIZEDB') ?>"/>
					<span>
						<?php echo JText::_('ATOOLS_LBL_OPTIMIZEDB') ?><br/>
					</span>
			</a>
		</div>
	</div>
<?php endif; ?>

<?php if ($this->enable_cleantmp && $this->isMySQL): ?>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=dbtools&task=purgesessions" id="optimize">
				<img
					src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/dbtools-32.png"
					border="0" alt="<?php echo JText::_('ATOOLS_LBL_PURGESESSIONS') ?>"/>
					<span>
						<?php echo JText::_('ATOOLS_LBL_PURGESESSIONS') ?><br/>
					</span>
			</a>
		</div>
	</div>
<?php endif; ?>

<?php if ($isPro): ?>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="index.php?option=<?php echo $option ?>&view=redirs">
				<img
					src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/redirs-32.png"
					border="0" alt="<?php echo JText::_('ADMINTOOLS_TITLE_REDIRS') ?>"/>
					<span>
						<?php echo JText::_('ADMINTOOLS_TITLE_REDIRS') ?><br/>
					</span>
			</a>
		</div>
	</div>
<?php endif; ?>

<?php if ($isPro): ?>
	<?php $url = 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $this->pluginid; ?>
	<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
		<div class="icon">
			<a href="<?php echo $url ?>" target="_blank">
				<img
					src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/scheduling-32.png"
					border="0" alt="<?php echo JText::_('ATOOLS_TITLE_SCHEDULING') ?>"/>
					<span>
						<?php echo JText::_('ATOOLS_TITLE_SCHEDULING') ?><br/>
					</span>
			</a>
		</div>
	</div>
<?php endif; ?>

<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
	<div class="icon">
		<a href="index.php?option=com_admintools&view=importexport&task=export">
			<img
				src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/export-32.png"
				border="0" alt="<?php echo JText::_('ATOOLS_TITLE_EXPORT_SETTINGS') ?>"/>
                        <span>
                            <?php echo JText::_('ATOOLS_TITLE_EXPORT_SETTINGS') ?><br/>
                        </span>
		</a>
	</div>
</div>

<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
	<div class="icon">
		<a href="index.php?option=com_admintools&view=importexport&task=import">
			<img
				src="<?php echo rtrim(JURI::base(), '/'); ?>/../media/com_admintools/images/import-32.png"
				border="0" alt="<?php echo JText::_('ATOOLS_TITLE_IMPORT_SETTINGS') ?>"/>
                            <span>
                                <?php echo JText::_('ATOOLS_TITLE_IMPORT_SETTINGS') ?><br/>
                            </span>
		</a>
	</div>
</div>

</div>

<div id="sidepanes" class="span6">
	<div class="well">
		<h3><?php echo JText::_('ATOOLS_LBL_CP_UPDATES'); ?></h3>

		<?php
		$copyright = date('Y');
		if ($copyright != '2010')
		{
			$copyright = '2010 - ' . $copyright;
		}
		?>

		<div>
			<!-- CHANGELOG :: BEGIN -->
			<p>
				Admin Tools version <?php echo ADMINTOOLS_VERSION ?> &bull;
				<a href="#" id="btnchangelog" class="btn btn-mini">CHANGELOG</a>
				<a href="index.php?option=com_admintools&view=update&task=force" class="btn btn-inverse btn-small">
					<?php echo JText::_('COM_ADMINTOOLS_CPANEL_MSG_RELOADUPDATE'); ?>
				</a>

			</p>

			<div style="display:none;">
				<div id="akeeba-changelog">
					<?php
					require_once dirname(__FILE__) . '/coloriser.php';
					echo AkeebaChangelogColoriser::colorise(JPATH_COMPONENT_ADMINISTRATOR . '/CHANGELOG.php');
					?>
				</div>
			</div>
			<!-- CHANGELOG :: END -->

			<p>Copyright &copy; <?php echo $copyright ?> Nicholas K. Dionysopoulos / <a
					href="http://www.akeebabackup.com"><b><span style="color: #000">Akeeba</span><span
							style="color: #666666">Backup</span></b>.com</a></p>
			<?php $jedLink = ADMINTOOLS_PRO ? '16363' : '14087' ?>
			<p>If you use Admin Tools <?php echo ADMINTOOLS_PRO ? 'Professional' : 'Core' ?>, please post a rating and a
				review at the <a
					href="http://extensions.joomla.org/extensions/access-a-security/site-security/site-protection/<?php echo $jedLink ?>">Joomla!
					Extensions Directory</a>.</p>
		</div>

		<?php if (!$isPro): ?>
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

	<?php if ($this->isPro && $this->showstats):
		echo $this->loadTemplate('graphs');
		echo $this->loadTemplate('stats');
	endif; ?>

	<div id="disclaimer" class="alert alert-info" style="margin-top: 2em;">
		<a class="close" data-dismiss="alert" href="#">×</a>

		<h3><?php echo JText::_('ATOOLS_LBL_CP_DISCLAIMER') ?></h3>

		<p><?php echo JText::_('ATOOLS_LBL_CP_DISTEXT'); ?></p>
	</div>
</div>
</div>
</div>

<?php
if($this->statsIframe)
{
    echo $this->statsIframe;
}
?>

<div style="clear: both;"></div>

<script type="text/javascript">
	(function ($)
	{
		$(document).ready(function ()
		{
			$.ajax('index.php?option=com_admintools&view=cpanel&task=updateinfo&tmpl=component', {
				success: function (msg, textStatus, jqXHR)
				{
					// Get rid of junk before and after data
					var match = msg.match(/###([\s\S]*?)###/);
					data = match[1];

					if (data.length)
					{
						$('#updateNotice').html(data);
					}
				}
			});

			$.ajax('index.php?option=com_admintools&view=cpanel&task=fastcheck&tmpl=component', {
				success: function (msg, textStatus, jqXHR)
				{
					// Get rid of junk before and after data
					var match = msg.match(/###([\s\S]*?)###/);
					data = match[1];

					if (data == 'false')
					{
						$('#fastcheckNotice').show('fast');
					}
				}
			});
		});
	})(akeeba.jQuery);
</script>
