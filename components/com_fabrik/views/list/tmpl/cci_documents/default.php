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

echo '<h1>' . $this->params->get('page_heading') . '</h1>';

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');

$user = JFactory::getSession()->get('emundusUser');

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


		if (!empty($this->rows)) {
			foreach ($this->rows as $row) {
				foreach ($row as $k => $v) {
					foreach ($this->headings as $key => $val) {
						$raw = $key.'_raw';
						if (property_exists($v->data, $raw)) {
							$data[$i][$val] = $v->data->$raw;
						}
					}
					if (property_exists($v->data, 'fabrik_edit') && !empty($v->data->fabrik_edit)) {
						$data[$i]['fabrik_edit_url'] = $v->data->fabrik_edit_url;
					}
					if (property_exists($v->data, 'id')) {
						$data[$i]['row_id'] = $v->id;
					}

					if (property_exists($v->data, 'fabrik_view_url') && !empty($v->data->fabrik_view_url)) {
						$data[$i]['fabrik_view_url'] = $v->data->fabrik_view_url;
					}
					$i = $i + 1;
				}
			}
			$h_files = new EmundusHelperFiles;
			$m_users = new EmundusModelUsers;
			$attachments = $h_files->getAttachmentsTypesByProfileID($m_users->getCurrentUserProfile($user->id));
		}
		
		?>

        <div class="g-block size-100">
			<?php if ($this->navigation->total < 1) :?>
				<?php echo JText::_("COM_EMUNDUS_NO_DOCUMENTS");?>
			<?php else: ?>
				<?php
				$gCounter = 0;
				foreach ($this->rows as $key => $row) :?>
                    <div class="accordion-container accordion-container-<?php echo $this->table->renderid; ?>">
                        <div class="article-title article-title-<?php echo $this->table->renderid; ?>">
                            <div class="article-name">
                                <i class="fas fa-caret-right"></i>
                                <h2><?php echo $key;?></h2>
                            </div>
                        </div>
                        <div class="accordion-content">
							<?php $_tmp = array(); foreach ($row as $company) :?>
	                            <?php if(!array_key_exists($company->data->jos_emundus_campaign_candidature___fnum_raw,$_tmp)) :?>
                                    <?php  $_tmp[$company->data->jos_emundus_campaign_candidature___fnum_raw] = $company->data->jos_emundus_campaign_candidature___fnum_raw;?>
                                        <div class="inner-accordion-container inner-accordion-container-<?php echo $this->table->renderid; ?>">
                                            <div class="inner-article-title inner-article-title-<?php echo $this->table->renderid; ?>">
                                                <div class="article-name inner-article-name">
                                                    <i class="fas fa-caret-right"></i>
                                                    <h3><?php echo $company->data->jos_emundus_setup_campaigns___label_raw."        ".$company->data->jos_emundus_setup_campaigns___start_date_raw;?></h3>
                                                </div>
                                                <p>
                                                    <span class="formation-date"><?php echo date('d/m/Y',strtotime($company->data->jos_emundus_setup_campaigns___start_date_raw)) ?></span>
                                                    <span class="user_name"><?php echo $company->data->jos_emundus_users___full_name_raw; ?></span>
                                                </p>
                                            </div>
                                            <div class="inner-accordion-content">
                                                <?php foreach ($attachments as $attachment) :?>

                                                    <?php if ($attachment->id == 102) :?>
                                                        <div class="em-attachement fiche-pedago em-have-file" onclick="getProductPDF('<?php echo $company->data->jos_emundus_setup_campaigns___training_raw; ?>')">
                                                            <i class="far fa-file-pdf"></i>
                                                            <p class="em-attachement-name"><?= $attachment->lbl; ?></p>
                                                        </div>
                                                    <?php else:?>
                                                        <div class="em-attachement">
                                                            <i class="far fa-file-pdf"></i>
                                                            <p class="em-attachement-name"><?= $attachment->lbl; ?></p>
                                                        </div>
                                                    <?php endif; ?>

                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                <?php endif; ?>
							<?php endforeach; ?>
                        </div>
                    </div>
				<?php endforeach; ?>

			<?php endif; ?>
        </div>
    </div>
</form>

<script>

    //Accorion
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


    //inner Accordion
    jQuery(function() {
        var Accordion = function(el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;

            var links = this.el.find('.inner-article-title-<?php echo $this->table->renderid; ?>');
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
                $el.find('.inner-accordion-content').not($next).slideUp().parent().removeClass('open');

                $el.find('.inner-accordion-content').not($next).parent().find('.fa-caret-right').removeClass("down");
            }
        };
        var accordion = new Accordion(jQuery('.inner-accordion-container-<?php echo $this->table->renderid; ?>'), false);
    });


    jQuery(document).ready(function(){
        //var inner = document.querySelectorAll('.inner-accordion-container');
        jQuery('.inner-accordion-content').slideToggle();
        jQuery('.accordion-content').slideToggle();
    });

    function getProductPDF(code) {
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=files&task=getproductpdf',
            async: false,
            data: {
                product_code: code
            },
            success: function (result) {
                result = JSON.parse(result);
                if (result.status) {
                    var win = window.open(result.filename, '_blank');
                    win.focus();
                } else {
                    alert(result.msg);
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }

</script>



