<?php
defined('_JEXEC') or die('Restricted access');
JFactory::getSession()->set('application_layout', 'mail');

?>

<div class='mail'>
    <div class="row">
        <div class="panel panel-default widget em-container-mail">
            <div class="panel-heading em-container-mail-heading">

                <h3 class="panel-title">
                	<span class="glyphicon glyphicon-comment"></span>
                	<?= JText::_('MESSAGES'); ?>
                	<span class="label label-info"><?= count($this->messages); ?></span>
                </h3>
                <div class="btn-group pull-right">
                    <button id="em-prev-file" class="btn btn-info btn-xxl"><i class="small arrow left icon"></i></button>
                    <button id="em-next-file" class="btn btn-info btn-xxl"><i class="small arrow right icon"></i></button>
                </div>

            </div>
            <div class="panel-body em-container-mail-body">
                <p class="text-center align-middle"> <?= JText::_('EMAIL_HISTORY_DESCRIPTION'); ?></p>
                <hr>
                <?php if ($this->messages === false) :?>
                    <h3 class="text-center align-middle"> <?= JText::_('ERROR_GETTING_MESSAGES'); ?> </h3>
                <?php elseif (count($this->messages) === 0) :?>
                    <h3 class="text-center align-middle"> <?= JText::_('NO_MESSAGES_FOUND'); ?> </h3>
                <?php else :?>
                    <?php foreach ($this->messages as $message) :?>
                        <div class='panel panel-default em-container-mail-content'>
                            <div class="panel-heading em-container-mail-content-heading"><h3><?= $message->subject; ?> <small> <?= JText::_('MESSAGE_FROM').': '.JFactory::getUser($message->user_id_from)->name.' '.date('d/m/Y H:i:s', strtotime($message->date_time)); ?> </small></h3></div>
                            <div class="panel-body em-container-mail-content-body">
                                <?= $message->message; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
