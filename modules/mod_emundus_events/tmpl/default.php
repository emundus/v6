<?php
defined('_JEXEC') or die;
?>

<h1>Events</h1>

<?php if (empty($events)) : ?>
    <p>No events found</p>
<?php else : ?>
    <div class="grid grid-cols-3 gap-3">
        <?php foreach ($events as $event) : ?>
            <div class="gap-2 p-4 border border-neutral-600 rounded bg-neutral-200 flex flex-col items-center justify-center">
                <!-- ICON -->
                <div style="width: 64px;" class="bg-white rounded-2xl">
                    <!-- MONTH -->
                    <div class="mod_emundus_events__month rounded-t-2xl">
                        <?php echo date('M', strtotime($event->start_date)); ?>
                    </div>
                    <!-- DAY -->
                    <div class="mod_emundus_events__day rounded-b-2xl">
                            <?php echo date('d', strtotime($event->start_date)); ?>
                    </div>
                </div>
                <div class="text-center">
                    <!-- DATE -->
                    <p><?php echo date('d.m.Y',strtotime($event->start_date)); ?></p>
                    <!-- TITLE -->
                    <h2><?php echo $event->title; ?></h2>
                    <!-- DESCRIPTION -->
                    <p><?php echo $event->description; ?></p>
                    <!-- LINK -->
                    <?php if(!empty($event->link)) : ?>
                        <a href="<?php echo $event->link; ?>">
                            <?php echo JText::_('MOD_EMUNDUS_EVENTS_MORE'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

