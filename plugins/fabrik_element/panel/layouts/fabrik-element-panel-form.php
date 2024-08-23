<?php
$d = $displayData;
?>

<div id="<?php echo $d->id; ?>" class="fabrikinput fabrikElementReadOnly" style="background-color: <?php echo $d->backgroundColor ?>;<?php if ($d->type == 4): ?>padding: 0;<?php endif; ?>" name="<?php echo $d->name; ?>">
    <span class="material-icons<?php echo $d->iconType ?>" style="color: <?php echo $d->iconColor ?>"><?php echo $d->icon ?></span>

    <div class="fabrikElementContent w-full" <?php if ($d->type == 4): ?>style="margin-left: 0;"<?php endif; ?>>
	    <?php if ($d->accordion == 1) : ?>
            <div class="flex items-center justify-between cursor-pointer"
                 href="#<?php echo $d->id; ?>-content" data-te-collapse-init data-toggle="collapse" aria-expanded="false" aria-controls="<?php echo $d->id; ?>-content" id="<?php echo $d->id; ?>-heading">
                <h3>
                    <?php echo $d->title ?>
                </h3>
                <span class="material-icons-outlined transition-transform duration-300" id="<?php echo $d->id; ?>-icon">expand_more</span>
            </div>
	    <?php endif; ?>

        <div <?php if ($d->accordion == 1) : ?>class="h-0 collapse"<?php endif ?>
             id="<?php echo $d->id; ?>-content"
             data-te-collapse-item>
		    <?php if (!empty($d->title) && $d->accordion == 0) : ?>
                <h3><?php echo $d->title ?></h3>
		    <?php endif; ?>
            <p class="<?php if (!empty($d->title)) : ?>mt-2<?php endif; ?>">
                <span class="whitespace-pre-line" id="<?php echo $d->id; ?>-value"><?php echo $d->value;?></span>
            </p>
        </div>
    </div>
</div>

<?php if ($d->accordion == 1) : ?>
<script>
    jQuery('#<?php echo $d->id; ?>-heading').on('click', function () {
        let icon = jQuery('#<?php echo $d->id; ?>-icon');
        if(icon.css('transform') == 'none')
            icon.css('transform', 'rotate(180deg)');
        else
            icon.css('transform', '');
    });
</script>
<?php endif; ?>
