<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 17/06/14
 * Time: 16:14
 */
?>
<div class="ui fluid accordion">
    <div class="title" id="em_application_connexion"><i
                class="dropdown icon"></i> <?php echo JText::_('COM_EMUNDUS_USERS_ACCOUNT'); ?> </div>
    <div class="content">
        <table>
            <thead>
            <tr>
                <th><strong><?php echo JText::_('COM_EMUNDUS_USERNAME'); ?></strong></th>
                <th><strong><?php echo JText::_('COM_EMUNDUS_APPLICATION_ACCOUNT_CREATED_ON'); ?></strong></th>
                <th><strong><?php echo JText::_('COM_EMUNDUS_USERS_LAST_VISIT'); ?></strong></th>
                <th><strong><?php echo JText::_('COM_EMUNDUS_STATUS'); ?></strong></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php
					if ($this->current_user->authorise('core.manage', 'com_users'))
						echo '<a class="modal" target="_self" rel="{handler:\'iframe\',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}" href="' . JRoute::_('index.php?option=com_emundus&view=users&edit=1&rowid=' . $this->student->id . '&tmpl=component') . '">' . $this->student->username . '</a>';
					else
						echo $this->student->username;
					?></td>
                <td><?php echo JHtml::_('date', $this->student->registerDate, JText::_('DATE_FORMAT_LC2')); ?></td>
                <td><?php echo JHtml::_('date', $this->student->lastvisitDate, JText::_('DATE_FORMAT_LC2')); ?></td>
                <td><?php
					if (isset($this->logged[0]->logoutLink))
						echo '<img style="border:0;" src="' . JURI::base() . 'media/com_emundus/images/icones/green.png" alt="' . JText::_('COM_EMUNDUS_USERS_ONLINE') . '" title="' . JText::_('COM_EMUNDUS_USERS_ONLINE') . '" />';
					else
						echo '<img style="border:0;" src="' . JURI::base() . 'media/com_emundus/images/icones/red.png" alt="' . JText::_('COM_EMUNDUS_USERS_OFFLINE') . '" title="' . JText::_('COM_EMUNDUS_USERS_OFFLINE') . '" />';
					?></td>
            </tr>
            </tbody>
        </table>
    </div>


    <div class="title" id="em_application_emails"><i
                class="dropdown icon"></i> <?php echo JText::_('COM_EMUNDUS_MAILS_EMAIL_HISTORY'); ?> </div>
    <div class="content">
        <div class="actions"><a class="modal clean" target="_self"
                                rel="{handler:'iframe',size:{x:window.getWidth()*0.8,y: window.getHeight()*0.8}}"
                                href="<?php echo JURI::base(); ?>index.php?option=com_emundus&amp;view=email&amp;tmpl=component&amp;sid=<?php echo $this->student->id; ?>&amp;Itemid=<?php echo $itemid; ?>">
                <button class="ui button teal submit labeled icon"
                        data-title="<?php echo JText::_('COM_EMUNDUS_MAILS_SEND_EMAIL'); ?>"><i
                            class="icon mail"></i><?php echo JText::_('COM_EMUNDUS_MAILS_SEND_EMAIL'); ?> </button>
            </a></div>
		<?php
		if (!empty($this->email['from'])) {
			echo '
			<div id="email_sent">
				<div class="email_title">' . JText::_('COM_EMUNDUS_MAILS_EMAIL_SENT') . '</div>
			';
			foreach ($this->email['from'] as $email) {
				echo '
					<div class="email">
						<div class="email_subject">
							' . $email->subject . '
						</div>
						<div class="email_details">
							<div class="email_to">
								<div class="email_legend">' . JText::_('COM_EMUNDUS_TO') . ' : </div>
								<div class="email_to_text">' . strtoupper($email->lastname) . ' ' . strtolower($email->firstname) . ' (' . $email->email . ') </div>
							</div>
							<div class="email_date">
								' . JHtml::_('date', $email->date_time, JText::_('DATE_FORMAT_LC2')) . '
							</div>
						</div>
						<div class="email_message">
							<div class="email_legend">' . JText::_('MESSAGE') . ' : </div>
							' . $email->message . '
						</div>
					</div>
					';
			}
			echo '
			</div>
			';
		}
		if (!empty($this->email['to'])) {
			echo '
			<div id="email_received">
				<div class="email_title">' . JText::_('COM_EMUNDUS_MAILS_EMAIL_RECEIVED') . '</div>
			';
			foreach ($this->email['to'] as $email) {
				echo '
					<div class="email">
						<div class="email_subject">
							' . $email->subject . '
						</div>
						<div class="email_details">
							<div class="email_from">
								' . strtoupper($email->lastname) . ' ' . strtolower($email->firstname) . ' (' . $email->email . ')
							</div>
							<div class="email_date">
								' . JHtml::_('date', $email->date_time, JText::_('DATE_FORMAT_LC2')) . '
							</div>
						</div>
						<div class="email_message">
							<div class="email_legend">' . JText::_('MESSAGE') . ' : </div>
							' . $email->message . '
						</div>
					</div>
					';
			}
			echo '
			</div>
			';
		}
		?>
    </div>
</div>
