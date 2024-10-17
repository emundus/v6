<?php
defined('_JEXEC') or die;
?>

<?php if (!empty($events)) : ?>
    <div class="mod_emundus_events__list">
        <?php foreach ($events as $event) : ?>
            <div style="background-color: <?php echo $bg_color; ?>;border-color: <?php echo $border_color; ?>" class="mod_emundus_events__block">
                <!-- ICON -->
                <div class="mod_emundus_events__block_icon">
                    <!-- MONTH -->
                    <div style="background-color: <?php echo $text_color; ?>" class="mod_emundus_events__month">
                        <?php echo date('M', strtotime($event->start_date)); ?>
                    </div>
                    <!-- DAY -->
                    <div class="mod_emundus_events__day">
                        <?php echo date('d', strtotime($event->start_date)); ?>
                    </div>
                </div>
                <div style="text-align: center">
                    <!-- DATE -->
                    <p class="mod_emundus_events__date"><?php echo date('d.m.Y',strtotime($event->start_date)); ?></p>
                    <!-- TITLE -->
	                <?php if(!empty($event->link)) : ?>
                        <a style="text-decoration-color: <?php echo $text_color; ?>;cursor: pointer" class="mod_emundus_events__title mod_emundus_events__link" href="<?php echo $event->link; ?>">
                            <label style="color: <?php echo $text_color; ?>;font-weight: bold;cursor: pointer"><?php echo $event->title; ?></label>
                        </a>
                    <?php else : ?>
                        <label style="color: <?php echo $text_color; ?>;cursor: unset;font-weight: bold" class="mod_emundus_events__title"><?php echo $event->title; ?></label>
                    <?php endif; ?>
                    <!-- DESCRIPTION -->
                    <p class="mod_emundus_events__description"><?php echo $event->description; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

