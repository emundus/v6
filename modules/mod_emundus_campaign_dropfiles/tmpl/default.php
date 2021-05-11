<?php
defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');
?>


<div class="em-infoComp">
	<h1 class="g-title"><?php echo $module->title; ?></h1>
	<ul>
	<?php foreach($files as $file) { ?>
		<a href="files/<?php echo $file->catid."/".$file->title_category."/".$file->id."/".$file->title_file.".".$file->ext; ?>" target="_blank" rel="noopener noreferrer" >
			<li class="em-infoComp__btn">
				<?php echo $file->title_file.".".$file->ext; ?><span><i class="fas fa-arrow-circle-down"></i></span>
			</li>
		</a>
	<?php } ?>
	</ul>
</div>
