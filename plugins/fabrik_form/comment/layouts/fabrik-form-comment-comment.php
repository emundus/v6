<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$d = $displayData;
?>

<div class="metadata muted">
	<small><?php echo FabrikHelperHTML::icon('icon-user'); ?>
		<?php echo $d->name; ?>, <?php echo Text::_('PLG_FORM_COMMENT_WROTE_ON'); ?> 
	</small>
	<?php echo FabrikHelperHTML::icon('icon-calendar'); ?>
	<small><?php echo HTMLHelper::date($d->comment->time_date, $d->dateFormat, 'UTC'); ?></small>
	<?php
	if ($d->internalRating) :
	?>
	<div class="rating">
	<?php 
	$r = (int) $d->comment->rating;
	for ($i = 0; $i < $r; $i++) :
		echo FabrikHelperHTML::icon('icon-star');
	endfor;
	?>
	</div>
	<?php 
	endif;
?>
</div>

<div class="comment" id="comment-<?php echo $d->comment->id; ?>">
	<div class="comment-content"><?php echo $d->comment->comment; ?></div>
	<div class="reply">
		<?php
		if ($d->canAdd) :
			?>
				<a href="#" class="replybutton btn btn-sm btn-outline-secondary"><?php echo Text::_('PLG_FORM_COMMENT_REPLY'); ?></a>
			<?php endif;

			if ($d->canDelete) :
				?>
				<a href="#" class="del-comment btn btn-danger btn-sm"><?php echo Text::_('PLG_FORM_COMMENT_DELETE');?></a>
			<?php
				endif;
			if ($d->useThumbsPlugin) :
				echo $d->thumbs;
			endif;
			?>
	</div>
</div>

<?php
if (!$d->commentsLocked) :
	echo $d->form;
endif;

