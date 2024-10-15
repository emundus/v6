<?php
defined('_JEXEC') or die;
?>

<h1>Events</h1>

<?php if (empty($events)) : ?>
    <p>No events found</p>
<?php else : ?>
    <div class="grid-cols-3">
        <?php foreach ($events as $event) : ?>
            <div class="p-4 border border-neutral-600 rounded bg-neutral-300">
                <!-- ICON -->
                <div style="height: 64px;width: 64px;" class="bg-white rounded-2xl">
                    <!-- MONTH -->
                    <div class="mod_emundus_events__month rounded-t-2xl">
                        <?php echo date('M', strtotime($event->start_date)); ?>
                    </div>
                    <!-- DAY -->
                    <div class="mod_emundus_events__day">
                            <?php echo date('d', strtotime($event->start_date)); ?>
                    </div>
                </div>
                <!-- DATE -->
                <p><?php echo $event->start_date; ?></p>
                <!-- TITLE -->
                <h2><?php echo $event->title; ?></h2>
                <!-- DESCRIPTION -->
                <p><?php echo $event->description; ?></p>
                <!-- LINK -->
                <a href="<?php echo $event->link; ?>">More info</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

