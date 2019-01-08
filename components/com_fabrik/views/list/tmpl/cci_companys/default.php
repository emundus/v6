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

                    if (array_key_exists($raw, $v->data)) {
                        $data[$i][$val] = $v->data->$raw;
                    }

                }
                if (array_key_exists('__pk_val', $v->data)) {
                    $data[$i]['id'] = $v->data->__pk_val;
                }
                if (array_key_exists('fabrik_edit_url', $v->data)) {
                    $data[$i]['fabrik_edit_url'] = $v->data->fabrik_edit_url;
                }
                if (array_key_exists('id', $v)) {
                    $data[$i]['row_id'] = $v->id;
                }
                if (array_key_exists('jos_emundus_users___user_id_raw', $v->data)) {
                    
                    $data[$i]['id'] = $v->data->jos_emundus_users___user_id_raw;
                }

                $i = $i + 1;
            }
        }

        ?>


        <div class="g-block size-100">
            <?php if ($this->navigation->total < 1) :?>
                <h2>Vous n'avez pas d'entreprises</h2>
            <?php else: ?>
                <?php
                    $gCounter = 0;
                    foreach ($data as $d) { ?>
                        <div class="accordion-container accordion-container-<?php echo $this->table->renderid; ?>">
                            <div class="article-title article-title-<?php echo $this->table->renderid; ?>">
                                <?php if($this->table->db_table_name == 'jos_emundus_entreprise') :?>
                                    <?php if(!empty($d["Raison sociale"])) :?>
                                        <h4><?php echo $d["Raison sociale"]; ?></h4>
                                    <?php endif; ?>
                                <?php elseif ($this->table->db_table_name == 'jos_emundus_users') :?>
                                    <?php if ((!empty($d["lastname"]) && !empty($d["firstname"]))) :?>
                                        <h4><?php echo $d["lastname"]. " " .$d["firstname"]; ?></h4>
                                        <?php if(!empty($d['user_id'])) :?>
                                            <div class="em-inscrire-col"><a href="/mon-espace-decideur-rh/inscrire-des-collaborateurs?user=<?php echo $d['user_id']; ?>">inscrire à une formation</a></div>
                                        <?php endif; ?>
                                    <?php elseif (!empty($d["Nom"]) && !empty($d["Prénom"])) :?>
                                        <h4><?php echo $d["Nom"]. " " .$d["Prénom"]; ?></h4>
                                        <?php if(!empty($d['user_id'])) :?>
                                            <div class="em-inscrire-col"><a href="/mon-espace-decideur-rh/inscrire-des-collaborateurs?user=<?php echo $d['user_id']; ?>">inscrire à une formation</a></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <div class="accordion-icons">
                                    <a href="<?php echo $d['fabrik_edit_url']; ?>"><i class="far fa-eye"></i></a>
                                    <div style="display: inline" id="delete-row-<?php echo  $d['row_id']; ?>" class="delete-row-<?php echo $this->table->db_table_name; ?>" data-id="<?php echo  $d['id']; ?>"><i class="fas fa-times"></i></div>
                                </div>
                            </div>

                            <div class="accordion-content">
                                <?php foreach ($d as $k => $v) { ?>
                                    <?php if($k != 'fabrik_edit_url' && $k != 'id' && $k != 'row_id' && $k != '__pk_val' && $k != 'user_id') :?>
                                        <?php if(strpos($k, 'Title') == true) :?>
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
                                <?php } ?>
                            </div>
                        </div>
                <?php } ?>
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

            if (!e.data.multiple) {
                $el.find('.accordion-content').not($next).slideUp().parent().removeClass('open');
            }
        };
        var accordion = new Accordion(jQuery('.accordion-container-<?php echo $this->table->renderid; ?>'), false);
    });

    jQuery(".delete-row-<?php echo $this->table->db_table_name; ?>").on('click', function (e) {
        e.stopPropagation();
        jQuery.ajax({
            type: "post",
            url: "<?php echo $rows[0]->data->fabrik_view_url; ?>",
            dataType: 'json',
            data : ({id: jQuery(this).data("id")}),
            success: function(result) {
                if(result.status) {
                    location.reload();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    });

</script>


<style>
    .accordion-container {
        position: relative;
        width: 100%;
        border-top: none;
        outline: 0;
        cursor: pointer
    }

    .accordion-container .article-title {
        display: block;
        position: relative;
        margin: 0;
        padding: 0.625em 0.625em 0.625em 2em;
        font-size: 1.25em;
        font-weight: normal;
        cursor: pointer;
    }

    .accordion-container .article-title h4:hover,
    .accordion-container .article-title h4:active,
    .accordion-container.open .article-title {
        color: white;
    }

    .accordion-container .article-title h4:hover i:before,
    .accordion-container .article-title h4:hover i:active {
        color: white;
    }

    .article-title {
        height: 50px;
    }

    .article-title h4 {
        float: left;
    }

    .accordion-icons {
        float: right;
    }

    .accordion-icons i:before {
        font-size: 30px;
    }

    .article-icons a {
        background-image: none;
        background-color: transparent;
    }

    .accordion-content {
        display: none;
        padding-left: 2.3125em;
    }


    .accordion-container {
        width: 100%;
        margin-bottom: 5px;
    }


    .list-pagin {
        display: block;
        float: right;
    }

    .list-pagin li {
        width: 30px;
        cursor: pointer;
        display: inline-block;
    }

    .list-pagin p {
        font-size: 18px;
        text-align: center;
    }

    p.current {
        border: 1px solid;
        padding: 0px 8px;
    }

</style>