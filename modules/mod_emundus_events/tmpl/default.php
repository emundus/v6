<?php
defined('_JEXEC') or die;
?>

<?php if (!empty($events)) : ?>
    <div class="mod_emundus_events__list grid grid-cols-3 gap-3">
        <?php foreach ($events as $event) : ?>
            <div style="background-color: <?php echo $bg_color; ?>;border-color: <?php echo $border_color; ?>" class="mod_emundus_events__block gap-2 p-4 border border-neutral-600 rounded flex flex-col items-center shadow-sm">
                <!-- ICON -->
                <div class="bg-white rounded-2xl border border-neutral-300">
                    <!-- MONTH -->
                    <div style="background-color: <?php echo $text_color; ?>" class="mod_emundus_events__month rounded-t-2xl px-5">
                        <?php echo date('M', strtotime($event->start_date)); ?>
                    </div>
                    <!-- DAY -->
                    <div class="mod_emundus_events__day rounded-b-2xl">
                        <?php echo date('d', strtotime($event->start_date)); ?>
                    </div>
                </div>
                <div class="text-center">
                    <!-- DATE -->
                    <p class="mod_emundus_events__date mb-2"><?php echo date('d.m.Y',strtotime($event->start_date)); ?></p>
                    <!-- TITLE -->
	                <?php if(!empty($event->link)) : ?>
                        <a class="mod_emundus_events__title mod_emundus_events__link" href="<?php echo $event->link; ?>">
                            <label style="color: <?php echo $text_color; ?>" class="font-bold"><?php echo $event->title; ?></label>
                        </a>
                    <?php else : ?>
                        <label style="color: <?php echo $text_color; ?>" class="mod_emundus_events__title font-bold"><?php echo $event->title; ?></label>
                    <?php endif; ?>
                    <!-- DESCRIPTION -->
                    <p class="mod_emundus_events__description"><?php echo $event->description; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

