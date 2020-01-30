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
require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'award.php');
$m_award = new EmundusModelAward();
$pageClass = $this->params->get('pageclass_sfx', '');
$document = JFactory::getDocument();
$app = JFactory::getApplication();

$menu = $app->getMenu();
$menu_id = $menu->getActive();


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

if ($this->showTitle == 1) : ?>
	<div class="page-header">
		<h1><?php echo $this->table->label;?></h1>
	</div>
<?php
endif;

// Intro outside of form to allow for other lists/forms to be injected.
echo $this->table->intro;

$form = $this->form;
$form_id = $form->id;

?>
<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList" data-id="<?= $form_id; ?>">

<?php
if ($this->hasButtons):
	echo $this->loadTemplate('buttons');
endif;

if ($this->showFilters && $this->bootShowFilters) :
	echo $this->layoutFilters();
endif;
//for some really ODD reason loading the headings template inside the group
//template causes an error as $this->_path['template'] doesn't contain the correct
// path to this template - go figure!
$headingsHtml = $this->loadTemplate('headings');
echo $this->loadTemplate('tabs');
?>
    <input type="hidden" id="menu_id" value="<?= $menu_id->id; ?>">
    <div data-w-id="3c3b9df0-751a-cd4b-5fc9-0b3c7fa86d77" class="em-wrappernavbar">
        <div class="em-navbar w-container">
            <div class="em-wrappermenu"><a href="index.html" class="em-logonavbar w-inline-block w--current"><img src="images/custom/5e0f4260452ae730c86dde94_5e0101728d0e1e94a13f80de_Groupe-VYV_Q.svg" alt="Logo Groupe VYV"></a>
                <div class="em-miniwrappermenu">
                    <div class="em-wrapperitemmenu"><a href="index.html" class="em-itemmenu w--current">LE challenge</a><a href="reglement.html" class="em-itemmenu">règlement</a><a href="a-propos.html" class="em-itemmenu">à propos</a></div>
                </div>
            </div>
        </div>
    </div>
    <div class="em-project-section">
        <div class="navbar">
            <div class="navbar-content">
                <div class="wrapper-menu-item" data-ix="menu-item-wrapper"><a href="index.html" class="nav-link w--current" data-ix="ia-navlink">LE CHALLENGE</a><a href="#" class="nav-link" data-ix="ia-navlink">RÈGLEMENT</a><a href="#" class="nav-link margin" data-ix="ia-navlink">À PROPOS</a></div>
                <a href="#" class="burger w-inline-block" data-ix="burger">
                    <div data-ix="center" class="line1 orange"></div>
                    <div data-ix="center" class="line2 orange"></div>
                    <div data-ix="center" class="line3 orange"></div>
                </a>
            </div>
        </div>

<?php foreach ($this->pluginBeforeList as $c) :
	echo $c;
endforeach;
?>
        <?php
        $gCounter = 0;
        $id_projet = $this->table->db_table_name.'___id_raw';
        $fnum_element = $this->table->db_table_name.'___fnum';
        $user = $this->table->db_table_name.'___user_raw';

        $i = 0;

        foreach ($this->rows as $groupedby => $group) {

        //Création d'un tableau pour récupérer les classes des éléments
        foreach($this->cellClass as $key => $val){

            $keys = array($i);
            $classArray[] = array_fill_keys($keys,$val);

            $i = $i +1;

        }
        // Décalage des indexes du tableau de 1 pour les modulos
        array_unshift($group,"");
        unset($group[0]);
        //var_dump($group).die();
        //Boucle de création des éléments spécifique à afficher dans la carte en fonction de la classe de l'élément
        for($j=0; $j < count($classArray); $j++) {
            for ($h = 0; $h < count($classArray); $h++) {

                $element = explode(' ', $classArray[$j][$h]['class']);

                //ajouter une classe sur l'élément fabrik souhaité
                if (end($element) == 'em-title') {
                    $titre = $element[0];
                }
                if (end($element) == 'em-description') {
                    $description = $element[0];
                }
            }
        }

        for($i=1; $i <= count($group); $i++) {
            $fnum = $group[$i]->data->$fnum_element;
            $id = $group[$i]->data->$id_projet;

            $cid = $m_award->getCampaignId($fnum);


        //récupération du nom de l'image
        $filename = $m_award->getUpload($fnum,$cid);
        $current_user = JFactory::getUSer();

        $url_detail = 'index.php?option=com_fabrik&view=details&formid='.$form_id.'&Itemid=2820&usekey=fnum&rowid='.$fnum;

        //Pair
        if (($i % 2) == 0 ) {
        ?>
            <div class="em-wrapper-project-row">
                <div class="em-rowproject rowinvert w-row">
                    <div class="w-col w-col-6">
                        <div class="em-wrappercontainerimage imageinvert w-clearfix">
                            <div data-w-id="afa6a3c8-1634-0848-b10c-b657a0400b11" class="em-containerimage"><img src="<?= JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename; ?>" alt="Challenge solidaire" sizes="(max-width: 479px) 86vw, (max-width: 767px) 87vw, (max-width: 991px) 43vw, 36vw" class="em-imageproject"></div>
                        </div>
                    </div>
                    <div class="w-col w-col-6">
                        <div class="em-wrappertextproject textinvert">
                            <h2 class="em-titleproject"><?= $group[$i]->data->$titre; ?></h2>
                            <h3><?= $group[$i]->data->$titre; ?></h3>
                            <p class="em-paragrapheprojet"><?= $group[$i]->data->$description; ?><br></p>

                            <?php
                            if($current_user->id == 0){

                                $url = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url_detail));
                            }
                            else{

                                $url = $url_detail;
                            }

                            ?>

                            <a href="<?= $url; ?>" class="em-button-project w-inline-block" data-ix="arrowcta-menu-2">
                                <div class="em-containerarrow2"><img src="images/custom/5e049464ed2a2711565ccae1_arrow.svg" alt="" class="em-arrowcta-purple2"><img src="images/custom/arrow.svg" alt="" class="em-arrowcta-white2"></div>
                                <div class="em-textcta">VOIR LE PROJET</div>
                                <div class="em-overlay"></div>

                            </a>
                            <div class="em-partage">
                                <a><i class="fas fa-star"></i></a>
                                <a id="share<?=$i;?>" data-fnum="<?= $fnum; ?>" data-url="<?= JRoute::_($url);?>" onclick="share(<?=$i;?>)"><i class="fas fa-share-square"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
 <?php
        }
        //Impair
        if (($i % 2) == 1) {
        ?>
            <div class="em-wrapper-project-row">
                <div class="em-rowproject w-row">
                    <div class="w-col w-col-6">
                        <div class="em-wrappercontainerimage w-clearfix">
                            <div data-w-id="af322628-4776-a3e3-4b5a-818fd075779b" class="em-containerimage"><img src="<?= JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename; ?>" alt="Challenge solidaire" sizes="(max-width: 479px) 86vw, (max-width: 767px) 87vw, (max-width: 991px) 43vw, 36vw" class="em-imageproject"></div>
                        </div>
                    </div>
                    <div class="w-col w-col-6">
                        <div class="em-wrappertextproject">
                            <h2 class="em-titleproject"><?= $group[$i]->data->$titre; ?></h2>
                            <h3></h3>
                            <p class="em-paragrapheprojet"><?= $group[$i]->data->$description; ?><br></p>
                            <?php
                            if($current_user->id == 0){
                                $url = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($url_detail));
                            }
                            else{
                                $url = $url_detail;
                            } ?>

                            <a href="<?= $url;?>" class="em-button-project w-inline-block" data-ix="arrowcta-menu-2">
                                <div class="em-containerarrow2"><img src="images/custom/5e049464ed2a2711565ccae1_arrow.svg" alt="" class="em-arrowcta-purple2"><img src="images/custom/arrow.svg" alt="" class="em-arrowcta-white2"></div>
                                <div class="em-textcta"><?= JText::_('SEE_MORE'); ?></div>
                                <div class="em-overlay"></div>
                            </a>
                            <div class="em-partage">
                                <a><i class="fas fa-star"></i></a>
                                <a id="share<?=$i;?>" data-fnum="<?= $fnum; ?>" data-url="<?= JRoute::_($url);?>" onclick="share(<?=$i;?>)"><i class="fas fa-share-square"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          <?php }
            }
        } ?>

    <div class="em-sectionfooter">
        <div class="em-divcontainerfooter">
            <div class="em-rowfooter"><a href="protections-des-donnees.html" class="em-protections-des-donnees">Protection des données</a><a href="mentions-legales.html" class="em-mentionslegales-2">Mentions légales</a>
                <div class="div-block-2">
                    <div class="em-wrappermenufooter"><a href="index.html" class="em-menufooter-2 w--current">Le challenge</a><a href="reglement.html" class="em-menufooter-2">règlement</a><a href="a-propos.html" class="em-menufooter-2">à propos</a></div>
                </div>
                <div><img src="images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF.jpg" alt="VYV groupe logo" srcset="images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF-p-500.jpeg 500w, images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF-p-800.jpeg 800w, images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF.jpg 1000w" sizes="(max-width: 479px) 94vw, (max-width: 767px) 81vw, (max-width: 991px) 58vw, 63vw" class="em-logofooter"></div>
            </div>
        </div>
    </div>

    <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.4.1.min.220afd743d.js" type="text/javascript" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="/templates/emundus_vanilla/js/vyv-project.js" type="text/javascript"></script>
    <!-- [if lte IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script><![endif] -->
	<?php print_r($this->hiddenFields);?>
</div>
</form>
<?php
echo $this->table->outro;
if ($pageClass !== '') :
	echo '</div>';
endif;
?>
<script>
    function share(index) {

        var share = document.getElementById('share'+index);
        var form = document.getElementsByClassName('fabrikForm')[0];
        var form_id = form.getAttribute('data-id');
        var fnum_projet = share.getAttribute('data-fnum');
        var url = share.getAttribute('data-url');

        var menu_id = document.getElementById('menu_id').value;

        Swal.fire({
            title: '<?= JText::_('SHARE'); ?>',
            html: '<div class="em-share">' +
                '<a href="https://www.facebook.com/sharer/sharer.php?u=index.php?option=com_fabrik%26view=details%26formid=' + form_id + '%26Itemid='+menu_id+'%26usekey=fnum%26rowid=' + fnum_projet + '"><i class="fab fa-facebook-square"></i></a>' +
                '<a href="https://twitter.com/intent/tweet?text=index.php?option=com_fabrik%26view=details%26formid=' + form_id + '%26Itemid='+menu_id+'%26usekey=fnum%26rowid=' + fnum_projet + '"><i class="fab fa-twitter-square"></i></a>' +
                '<a href="https://www.linkedin.com/shareArticle?mini=true&url=index.php?option=com_fabrik%26view=details%26formid=' + form_id + '%26Itemid='+menu_id+'%26usekey=fnum%26rowid=' + fnum_projet + '&title=&summary=&source="><i class="fab fa-linkedin"></i></a>' +
                '</div>' +
                '<input type="text" value="'+url+'">'
        });
    }
</script>
