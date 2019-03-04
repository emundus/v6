<?php
/**
 * Bootstrap List Template - Default
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$pageClass = $this->params->get('pageclass_sfx', '');

if ($pageClass !== '') :
    echo '<div class="' . $pageClass . '">';
endif;

if ($this->tablePicker != '') : ?>
    <div style="text-align:right"><?php echo FText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
<?php
endif;

if ($this->params->get('show_page_heading')) :
    echo '<h1>' . $this->params->get('page_heading') . '</h1>';
endif;




// Intro outside of form to allow for other lists/forms to be injected.
echo $this->table->intro;
?>
<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">


    <div class="fabrikDataContainer">

        <?php foreach ($this->pluginBeforeList as $c) :
            echo $c;

        endforeach;

        $data = array();
        $i = 0;
        $rows = $this->rows[0];
        if (!empty($rows)) {

            foreach ($rows as $k => $v) {
                foreach ($this->headings as $key => $val) {
                    $raw = $key.'_raw';
                    if (property_exists($v->data, $raw)) {
                        if ($raw =="jos_emundus_users___birthday_raw") {
                            $v->data->$raw = date('d/m/Y', strtotime($v->data->$raw));
                        }
                        $data[$i][$val] = $v->data->$raw;
                    }
                }

                if (property_exists($v->data, '__pk_val')) {
                    $data[$i]['id'] = $v->data->__pk_val;
                }
                if (property_exists($v->data, 'fabrik_edit') && !empty($v->data->fabrik_edit)) {
                    $data[$i]['fabrik_edit_url'] = $v->data->fabrik_edit_url;
                }
                if (property_exists($v->data, 'id')) {
                    $data[$i]['row_id'] = $v->id;
                }
                if (property_exists($v->data, 'jos_emundus_users___user_id_raw')) {
                    $data[$i]['id'] = $v->data->jos_emundus_users___user_id_raw;
                }

                $i = $i + 1;
            }
        }
        

        ?>


        <div class="g-block size-100">
            <?php if ($this->navigation->total < 1) :?>
                <?php if($this->table->db_table_name == 'jos_emundus_entreprise') :?>
                    <h2><?php echo JText::_("COM_EMUNDUS_NO_COMPANIES");?></h2>
                <?php elseif ($this->table->db_table_name == 'jos_emundus_users') :?>
                    <h2><?php echo JText::_("COM_EMUNDUS_NO_ASSOCIATES");?></h2>
                <?php endif; ?>
            <?php else: ?>
                <?php
                    $gCounter = 0;
                    foreach ($data as $d) :?>
                        <div class="accordion-container accordion-container-<?php echo $this->table->renderid; ?>">
                            <div class="article-title article-title-<?php echo $this->table->renderid; ?>">
                                <i class="fas fa-caret-right"></i>
                                <?php if ($this->table->db_table_name == 'jos_emundus_entreprise') :?>
                                    <?php if (!empty($d["Raison sociale"])) :?>
                                        <h4><?php echo $d["Raison sociale"]; ?></h4>
                                    <?php endif; ?>
                                <?php elseif ($this->table->db_table_name == 'jos_emundus_users') :?>

                                    <?php if (!empty($d["lastname"]) && !empty($d["firstname"])) :?>
                                        <h4><?php echo $d["lastname"]. " " .$d["firstname"]; ?></h4>
                                    <?php elseif (!empty($d["Nom"]) && !empty($d["Prénom"])) :?>
                                        <h4><?php echo $d["Nom"]. " " .$d["Prénom"]; ?></h4>
                                    <?php endif; ?>

	                                <?php if (!empty($d['user_id'])) :?>
                                        <div class="em-inscrire-col"><a href="/inscription?user=<?php echo $d['user_id']; ?>"><?php echo JText::_("COM_EMUNDUS_SIGNUP_FORMATION");?></a></div>
	                                <?php endif; ?>
                                <?php endif; ?>
                                <div class="accordion-icons">
                                    <?php if ($d['fabrik_edit_url']) :?>
                                        <a href="<?php echo $d['fabrik_edit_url']; ?>"><i class="fa fa-pen"></i></a>
                                    <?php endif; ?>
                                    <div style="display: inline" id="delete-row-<?php echo $d['row_id']; ?>" class="delete-row-<?php echo $this->table->db_table_name; ?>" data-id="<?php echo $d['id']; ?>" <?php if (!empty($d['user_id'])) { echo 'data-cid= "'.$d['cid'].'"'; } ?>>
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-content">
                                <?php foreach ($d as $k => $v) :?>
                                    <?php if ($k != 'fabrik_edit_url' && $k != 'id' && $k != 'row_id' && $k != '__pk_val' && $k != 'user_id' && $k != 'cid') :?>
                                        <?php if (strpos($k, 'Title')) :?>
                                            <div class="em-group-title">
                                                <span><?php echo str_replace('Title-', '',$k); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="em-element <?php echo str_replace(' ','-', $k);?>">
                                                <div class="em-element-label"><?php echo $k; ?></div>
                                            <div class="em-element-value"><?php echo $v; ?></div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif;?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
    <ul id="list-pagin-<?php echo $this->table->renderid; ?>" class="list-pagin"></ul>
</form>

<?php
if ($this->hasButtons)
    echo $this->loadTemplate('buttons');
echo $this->table->outro;
if ($pageClass !== '') :
    echo '</div>';
endif;
?>


<script>

    var data = <?php echo sizeof($data); ?>;

    //Pagination
    pageSize = 3;

    var pageCount =  data / pageSize;

    if (pageCount > 1) {
        for (var i = 0 ; i<pageCount;i++) {
            jQuery("#list-pagin-<?php echo $this->table->renderid; ?>").append('<li><p>'+(i+1)+'</p></li> ');
        }
    }

    jQuery("#list-pagin-<?php echo $this->table->renderid; ?> li").first().find("p").addClass("current");
    showPage<?php echo $this->table->renderid; ?>  = function(page) {
        jQuery(".accordion-container-<?php echo $this->table->renderid; ?>").hide();
        jQuery(".accordion-container-<?php echo $this->table->renderid; ?>").each(function(n) {
            if (n >= pageSize * (page - 1) && n < pageSize * page)
                jQuery(this).show();
        });
    };

    showPage<?php echo $this->table->renderid; ?> (1);

    jQuery("#list-pagin-<?php echo $this->table->renderid; ?> li p").click(function() {
        jQuery("#list-pagin-<?php echo $this->table->renderid; ?> li p").removeClass("current");
        jQuery(this).addClass("current");
        showPage<?php echo $this->table->renderid; ?> (parseInt(jQuery(this).text()))
    });


// accordion
    jQuery(function() {
        var Accordion = function(el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;

            var links = this.el.find('.article-title-<?php echo $this->table->renderid; ?>');
            links.on('click', {
                el: this.el,
                multiple: this.multiple
            }, this.dropdown)
        };

        Accordion.prototype.dropdown = function(e) {
            var $el = e.data.el;

            $this = jQuery(this),
            $next = $this.next();

            $next.slideToggle();
            $this.parent().toggleClass('open');


            $this.find('.fa-caret-right').toggleClass("down");

            if (!e.data.multiple) {
                $el.find('.accordion-content').not($next).slideUp().parent().removeClass('open');

                $el.find('.accordion-content').not($next).parent().find('.fa-caret-right').removeClass("down");
            }
        };
        var accordion = new Accordion(jQuery('.accordion-container-<?php echo $this->table->renderid; ?>'), false);
    });

    jQuery(document).ready(function(){
        if(jQuery(this).find('.accordion-container-<?php echo $this->table->renderid; ?>').size() > 0 ) {
            var first = document.querySelectorAll('.accordion-container-<?php echo $this->table->renderid; ?>')[0];
            jQuery(first.getElementsByClassName('accordion-content')[0]).slideToggle();
            first.classList.add('open');
            jQuery(first).find('.fa-caret-right').addClass("down");
        }
    });

    jQuery(".delete-row-<?php echo $this->table->db_table_name; ?>").on('click', function (e) {
        var row = jQuery(this).closest('.accordion-container')[0];

        e.stopPropagation();

        Swal.fire({
                title: "<?php echo ($this->table->db_table_name == 'jos_emundus_users') ? JText::_('REMOVE_ASSOCIATE_CONFIRM') : JText::_('REMOVE_COMPANY_CONFIRM'); ?>",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#dc3545",
                confirmButtonText: "<?php echo JText::_('JYES');?>",
                cancelButtonText: "<?php echo JText::_('JNO');?>"
            }
        ).then(
            function (isConfirm) {
                if (isConfirm.value == true) {
                    jQuery.ajax({
                        type: "post",
                        url: "<?php echo $rows[0]->data->fabrik_view_url; ?>",
                        dataType: 'json',
                        data : ({
                            id: jQuery(".delete-row-<?php echo $this->table->db_table_name; ?>").data("id"),
                            <?php if (!empty($d['user_id'])) :?>
                                cid: jQuery(".delete-row-<?php echo $this->table->db_table_name; ?>").data("cid"),
                            <?php endif; ?>
                        }),
                        success: function(result) {
                            if (result.status) {
                                jQuery(row).hide();
                                Swal.fire({
                                    type: 'success',
                                    title: "<?php echo ($this->table->db_table_name == 'jos_emundus_users') ? JText::_('REMOVE_ASSOCIATE_REMOVED') : JText::_('REMOVE_COMPANY_REMOVED'); ?>"
                                });
                            }
                            else {
                                Swal.fire({
                                    type: 'error',
                                    text: "<?php echo ($this->table->db_table_name == 'jos_emundus_users') ? JText::_('REMOVE_ASSOCIATE__NOT_REMOVED') : JText::_('REMOVE_COMPANY_NOT_REMOVED'); ?>"
                                });
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                            Swal.fire({
                                type: 'error',
                                text: "<?php echo ($this->table->db_table_name == 'jos_emundus_users') ? JText::_('REMOVE_ASSOCIATE_NOT_REMOVED') : JText::_('REMOVE_COMPANY_NOT_REMOVED'); ?>"
                            });
                        }
                    });
                }
            }
        );
    });

</script>

