<?php
defined('_JEXEC') or die('Restricted access');
JFactory::getSession()->set('application_layout', 'mail');

?>

<div class='mail'>
    <div class="row">
        <div class="panel panel-default widget">
            <div class="panel-heading">

                <h3 class="panel-title">
                	<span class="glyphicon glyphicon-comment"></span>
                	<?php echo JText::_('MESSAGES'); ?>
                	<span class="label label-info"><?php echo count($this->messages); ?></span>
                </h3>

            </div>
            <div class="panel-body">

                <?php if (!$this->messages) :?>
                    <h3> <?php echo JText::_('ERROR_GETTING MESSAGES'); ?> </h3>
                <?php elseif (count($this->messages) === 0) :?>
                    <h3> <?php echo JText::_('NO_MESSAGES_FOUND'); ?> </h3>
                <?php else :?>
                    <?php foreach ($this->messages as $message) :?>
                        <div class='panel panel-default'>
                            <div class="panel-heading"><h3><?php echo $message->subject; ?> <small> <?php echo JText::_('MESSAGE_FROM').': '.JFactory::getUser($message->user_id_from)->name.' '.date('Y-m-d H:i:s', strtotime($message->date_time)); ?> </small></h3></div>
                            <div class="panel-body">
                                <?php echo $message->message; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>