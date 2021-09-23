<?php
/**
 * Created by PhpStorm.
 * User: DELL
 * Date: 4/27/2018
 * Time: 6:19 PM
 */
?>
<ul class="eb-validation_errors">
	<?php
		foreach ($errors as $error)
		{
		?>
			<li><?php echo $error; ?></li>
		<?php
		}
	?>
</ul>
