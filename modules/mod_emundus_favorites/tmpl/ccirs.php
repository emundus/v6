<?php
/**
 * @package   Joomla.Site
 * @subpackage  eMundus
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
echo $description;
?>

<style>
    .unfave {
        cursor: pointer;
    }

    .unfave:hover,
    .unfave:active {
        color: #d91e18;
    }
</style>

<?php if (!empty($favorites)) :?>
    <div class="<?php echo $moduleclass_sfx ?>">
		<?php foreach ($favorites as $favorite) : ?>
            <div class="row favorite" id="row<?php echo $favorite->id; ?>">
                <div class="col-md-9 main-page-favorite-title">
                    <a href="<?php echo JRoute::_(JURI::base().'formation?rowid='. $favorite->id.'-'.str_replace('.html', '', $favorite->url)); ?>" ><?php echo $favorite->title; ?></a>
                </div>

                <div class="col-md-2 main-page-file-progress">
                    <div class="main-page-file-progress-label">
                        <a href="<?php echo JRoute::_(JURI::base().'formation?rowid='. $favorite->id.'-'.str_replace('.html', '', $favorite->url)); ?>" >s'inscrire</a>
                    </div>
                </div>
                <div class="col-md-1">
                    <i class="fas fa-times unfave" id="unf-<?php echo $favorite->id; ?>" onclick="unfavorite(<?php echo $favorite->id; ?>)"></i>
                </div>
            </div>
		<?php endforeach;  ?>
        <ul id="pagin"></ul>
    </div>
<?php else :
	echo JText::_('NO_FAVORITES');
endif; ?>


<?php echo $outro; ?>

<script type="text/javascript">


    var favorites = <?php echo json_encode($favorites); ?>;

    //Pagination
    pageSize = 3;

    var pageCount =  Object.keys(favorites).length / pageSize;

    if (pageCount > 1) {
        for (var i = 0 ; i<pageCount;i++) {
            jQuery("#pagin").append('<li><p>'+(i+1)+'</p></li> ');
        }
    }

    jQuery("#pagin li").first().find("p").addClass("current");
    showPage = function(page) {
        jQuery(".favorite").hide();
        jQuery(".favorite").each(function(n) {
            if (n >= pageSize * (page - 1) && n < pageSize * page)
                jQuery(this).show();
        });
    };

    showPage(1);

    jQuery("#pagin li p").click(function() {
        jQuery("#pagin li p").removeClass("current");
        jQuery(this).addClass("current");
        showPage(parseInt(jQuery(this).text()))
    });


    function unfavorite(programme_id) {
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=programme&task=unfavorite',
            data: {
                programme_id: programme_id,
                user_id: <?php echo $user->id; ?>
            },
            success: function(result) {
                result = JSON.parse(result);
                if (result.status) {
                    document.getElementById('row'+programme_id).remove();
                } else {
                    document.getElementById('unf-'+programme_id).style.color = '#d91e18';
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }
</script>
