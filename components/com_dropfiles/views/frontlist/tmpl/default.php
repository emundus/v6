<?php
defined('_JEXEC') || die;

if ($this->menuItemParams->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1>
            <?php echo $this->escape($this->menuItemParams->get('page_heading')); ?>
        </h1>
    </div>
<?php endif; ?>

<div class="dropfiles-page ">
    <?php echo $this->filesHtml; ?>

</div>    
