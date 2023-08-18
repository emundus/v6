<?php
defined('JPATH_BASE') or die;

$d   = $displayData;
$ext = JFile::getExt($d->filename);
?>

<style>
    .file-button {
        padding: 6px 10px;
        background: var(--blue-700);
        border-radius: var(--em-applicant-br);
        color: white;
        border: solid 1px transparent;
    }
    .file-button:hover {
        background-color: transparent;
        border-color: var(--blue-700);
        color: var(--blue-700) !important
    }
    .file-button:hover .file-button__filename {
        color: var(--blue-700) !important;
    }
    .file-button__filename {
        margin-top: 0 !important;
        color: white !important;
    }
</style>
<a class="download-archive fabrik-filetype-<?php echo $ext; ?>" title="<?php echo $d->filename; ?>"
	href="<?php echo $d->file; ?>">

	<?php
	if ($d->thumb) :
		?>
		<img src="<?php echo $d->thumb;?>" alt="<?php echo $d->filename;?>" />
	<?php
	else :
		?>
        <div class="em-flex-row em-gap-4 file-button">
            <span class="material-icons-outlined">file_download</span>
            <span class="file-button__filename"><?php echo $d->filename; ?></span>
        </div>
		<?php
	endif;
	?>
</a>