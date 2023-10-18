<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class cron_reportPreviewMaker {
	public $displaySubmitButton = true;

	public function prepareMail($data = null) {
		if(empty($data))
			return $this->getDefaultData();

		$config =& hikashop_config();
		$subject = JText::_('CRON_REPORT_SUBJECT');

		$mailClass = hikashop_get('class.mail');
		$infos = new stdClass();
		$infos->report = $data['report'];
		$infos->detailreport = $data['detailreport'];
		$mail = $mailClass->get('cron_report', $infos);
		$mail->subject = $subject;
		$mail->from_email = $config->get('from_email');
		$mail->from_name = $config->get('from_name');
		if(empty($mail->dst_email))
			$mail->dst_email = array($config->get('from_email'));
		return $mail;
	}

	public function getDefaultData() {
	}

	public function getSelector($data) {
?>
<dl class="hika_options">
	<dt>
		<?php echo JText::_('CRON_REPORT'); ?>
	</dt>
	<dd>
		<input type="text" name="data[report]" size="40" value="<?php echo @$data['report']; ?>" />
	</dd>
	<dt>
		<?php echo JText::_('DETAILED_REPORT'); ?>
	</dt>
	<dd>
		<input type="text" name="data[detailreport]" size="40" value="<?php echo @$data['detailreport']; ?>" />
	</dd>
</dl>
<?php
	}
}
