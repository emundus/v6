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

$document->addScript('/projet/templates/emundus_vanilla/js/vyv-project.js');

$app = JFactory::getApplication();

$menu = $app->getMenu();
$menu_id = $menu->getActive();


$current_user = JFactory::getUser();


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
//echo $this->table->intro;

$form = $this->form;
$form_id = $form->id;

?>
<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList" data-id="<?= $form_id; ?>">

    <?php
    if ($this->hasButtons):
        echo $this->loadTemplate('buttons');
    endif;



    //for some really ODD reason loading the headings template inside the group
    //template causes an error as $this->_path['template'] doesn't contain the correct
    // path to this template - go figure!

    echo $this->loadTemplate('tabs');
    ?>
    <input type="hidden" id="menu_id" value="<?= $menu_id->id; ?>">
    <div data-w-id="3c3b9df0-751a-cd4b-5fc9-0b3c7fa86d77" class="em-wrappernavbar">
        <div class="em-navbar w-container">
            <div class="em-wrappermenu"><a href="../index.html" class="em-logonavbar w-inline-block w--current"><img src="../projet/images/custom/Groupe_VYV_Q.png" alt="Logo Groupe VYV"></a>
                <div class="em-miniwrappermenu">
                    <div class="em-wrapperitemmenu"><a href="/index.html" class="em-itemmenu w--current">LE challenge</a><a href="../projet/index.php?option=com_fabrik&view=list&listid=359" class="em-itemmenu">Projets</a><a href="/reglement.html" class="em-itemmenu">règlement</a><a href="/a-propos.html" class="em-itemmenu">à propos</a></div>
                </div>
                <a href="../projet/index.php?option=com_fabrik&view=list&listid=359" class="em-button-nav w-inline-block" data-ix="arrowcta-menu">
                    <div class="em-containerarrow"><img src="/projet/images/custom/5e049464ed2a2711565ccae1_arrow.svg" alt="" class="em-arrowcta-purple"><img src="/projet/images/custom/arrow.svg" alt="" class="em-arrowcta-white"></div>
                    <div class="em-textcta2">VOTER</div>
                    <div class="em-overlay"></div>
                </a>
            </div>
        </div>
    </div>
    <div class="em-project-section">
        <div class="navbar">
            <div class="navbar-content">
                <div class="wrapper-menu-item" data-ix="menu-item-wrapper"><a href="/index.html" class="nav-link w--current" data-ix="ia-navlink">LE CHALLENGE</a><a href="../projet/index.php?option=com_fabrik&view=list&listid=349" class="em-itemmenu">Projets</a><a href="/reglement.html" class="nav-link" data-ix="ia-navlink">RÈGLEMENT</a><a href="/a-propos.html" class="nav-link margin" data-ix="ia-navlink">À PROPOS</a></div>
                <a href="#" class="burger w-inline-block" data-ix="burger">
                    <div data-ix="center" class="line1 orange"></div>
                    <div data-ix="center" class="line2 orange"></div>
                    <div data-ix="center" class="line3 orange"></div>
                </a>
            </div>
        </div>

        <?php
        $thematiques_name = $m_award->GetThematique($current_user->id);
        $project_name = $m_award->GetProjet($current_user->id);

        $countByThematique1 = $m_award->CountByThematique(1);
        $countByThematique2 = $m_award->CountByThematique(2);
        $countByThematique3 = $m_award->CountByThematique(3);
        $countByThematique4 = $m_award->CountByThematique(4);
        $countByThematique5 = $m_award->CountByThematique(5);
        $countByThematique6 = $m_award->CountByThematique(6);
        $countByThematique7 = $m_award->CountByThematique(7);

        $theme = '';
        $nbVote = $m_award->CountVotes($current_user->id);
        $totalVote = $m_award->TotalVotes(); ?>
        <p class="em-paragrapheprojet-explain">Un total de <span style="font-weight:600;color:#82358b;"><?= $totalVote ?></span> projets a été déposé sur la plateforme de la manière suivante :</p>
        <ul class="em-ulprojet-explain">
            <li>Bien-être (sport, alimentation & santé) : <span style="font-weight:600;color:#82358b;"><?= $countByThematique1 ?></span></li>
            <li>Education et accès à la culture pour tous : <span style="font-weight:600;color:#82358b;"><?= $countByThematique2 ?></span></li>
            <li>Amélioration du cadre de vie et de l’habitat : <span style="font-weight:600;color:#82358b;"><?= $countByThematique3 ?></span></li>
            <li>Maitrise des avancées technologiques : <span style="font-weight:600;color:#82358b;"><?= $countByThematique4 ?></span></li>
            <li>Eco responsabilité individuelle et collective : <span style="font-weight:600;color:#82358b;"><?= $countByThematique5 ?></span></li>
            <li>Respect des diversités et de l’inclusion : <span style="font-weight:600;color:#82358b;"><?= $countByThematique6 ?></span></li>
            <li>Autres : <span style="font-weight:600;color:#82358b;"><?= $countByThematique7 ?></span></li>
        </ul>
        <?php
        if ($this->showFilters && $this->bootShowFilters) :
            echo $this->loadTemplate('filter');
        endif; ?>

        <?php if($current_user->guest == 1 && $nbVote == 0 || $current_user->guest == 0 && $nbVote == 0){ ?>
            <p class="em-divprojet-explain">Pour pouvoir voter, vous devez cliquer sur le bouton "> VOIR CE PROJET ET VOTER" puis vous connecter à votre compte. Attention, vous ne pourrez voter qu'une seule fois pour un projet, dans une limite de 6 projets.</p>
        <?php } ?>
        <?php if($nbVote == 1 && $current_user->guest == 0){ ?>
            <p class="em-paragrapheprojet-explain">Vous ne pouvez voter qu'une seule fois dans chaque thématique, dans une limite de six projets.
                Vous avez déjà voté pour un projet <span class="em-thematique-deja-votee"><?= $project_name[0] ?></span>. Donc vous ne pourrez plus voter pour ce projet, c'est pourquoi ce projet est désormais grisé.</p>
        <?php } ?>
        <?php if($nbVote > 1 && $current_user->guest == 0){ ?>
            <div class="em-divprojet-explain">Vous ne pouvez voter qu'une seule fois dans chaque thématique, dans une limite de 6 projets.
                Vous avez déjà voté pour plusieurs projets : <?php for($i=0; $i < $nbVote; $i++){ echo '<p class="em-thematique-deja-votee">'.  $project_name[$i]. '</p>'; }?><p class="em-paragrapheprojet-explain"> Donc vous ne pourrez plus voter pour un autre projet appartenant à l'une de ces thématiques, c'est pourquoi tous les autres projets rattachés à ces thématiques seront désormais grisés.</p></div>
        <?php } ?>

        <?php foreach ($this->pluginBeforeList as $c) :
            echo $c;
        endforeach;


        ?>

        <?php
        $gCounter = 0;
        $id_projet = $this->table->db_table_name.'___id_raw';
        $fnum_element = $this->table->db_table_name.'___fnum';
        $user = $this->table->db_table_name.'___user_raw';
        $nameThematique = $m_award->GetThematique($current_user->id);


        $i = 0; ?>

        <table class="<?php echo $this->list->class;?>" id="list_<?php echo $this->table->renderid;?>" >
            <tr><?php echo $headingsHtml?></tr>
        </table>
        <?php
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
                    if (end($element) == 'em-thematique') {
                        $thematique = $element[0];
                        $thematique_raw = $element[0].'_raw';
                    }
                    if (end($element) == 'em-visuel') {
                        $visuel = $element[0];
                    }
                }
            }
            $elementVisuel = explode('___',$visuel);
            $attachment_id = $m_award->getFabrikElement($elementVisuel[1]);


            for($i=1; $i <= count($group); $i++) {

                $countThematique = $m_award->CountThematique($current_user->id, $group[$i]->data->$thematique_raw);
                $fnum = $group[$i]->data->$fnum_element;
                $countVote = $m_award->CountVote($fnum,$current_user->id);
                $id = $group[$i]->data->$id_projet;

                $cid = $m_award->getCampaignId($fnum);


                //récupération du nom de l'image
                $filename = $m_award->getUpload($fnum,$cid,$attachment_id);
                $current_user = JFactory::getUSer();

                $url_detail = 'index.php?option=com_fabrik&view=details&formid='.$form_id.'&Itemid='.$menu_id->id.'&usekey=id&rowid='.$id;

                //Pair
                if (($i % 2) == 0 ) {
                    ?>
                    <div class="em-wrapper-project-row">
                        <?php if ($countVote == 1 || $nbVote == 1) { ?>
                            <div class="overlay"></div>
                        <?php } ?>
                        <div class="em-rowproject rowinvert w-row">
                            <div class="w-col w-col-6">
                                <div class="em-wrappercontainerimage imageinvert w-clearfix">
                                    <?php if (($i % 3) == 0 ) { ?>
                                        <div data-w-id="afa6a3c8-1634-0848-b10c-b657a0400b11" class="<?= $countVote == 0 ? "em-containerimage" : "em-containerimage-bloque"; ?>"><?= !empty($filename) ? '<img src="'.JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename.'" alt="Challenge solidaire" sizes="(max-width: 479px) 86vw, (max-width: 767px) 87vw, (max-width: 991px) 43vw, 36vw" class="em-imageproject">' :'' ?></div>
                                    <?php } ?>
                                    <?php if (($i % 3) == 1 ) { ?>
                                        <div data-w-id="afa6a3c8-1634-0848-b10c-b657a0400b11" class="<?= $countVote == 0 ? "em-containerimage2" : "em-containerimage2-bloque"; ?>"><?= !empty($filename) ? '<img src="'.JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename.'" alt="Challenge solidaire" sizes="(max-width: 479px) 86vw, (max-width: 767px) 87vw, (max-width: 991px) 43vw, 36vw" class="em-imageproject">' :'' ?></div>
                                    <?php } ?>
                                    <?php if (($i % 3) == 2 ) { ?>
                                        <div data-w-id="afa6a3c8-1634-0848-b10c-b657a0400b11" class="<?= $countVote == 0 ? "em-containerimage3" : "em-containerimage3-bloque"; ?>"><?= !empty($filename) ? '<img src="'.JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename.'" alt="Challenge solidaire" sizes="(max-width: 479px) 86vw, (max-width: 767px) 87vw, (max-width: 991px) 43vw, 36vw" class="em-imageproject">' :'' ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="w-col w-col-6">
                                <div class="em-wrappertextproject textinvert">
                                    <h2 class="em-titleproject"><?= $group[$i]->data->$titre; ?></h2>
                                    <h3 class="em-thematiqueproject"><?= $group[$i]->data->$thematique; ?></h3>
                                    <p class="em-paragrapheprojet"><?= $group[$i]->data->$description; ?></p>


                                    <?php
                                    if($current_user->guest){

                                        $url = JRoute::_('index.php?option=com_users&view=login&Itemid=1135&return=' . base64_encode($url_detail));
                                    }
                                    else{

                                        $url = JRoute::_($url_detail);
                                    }

                                    ?>

                                    <a href="<?= $url; ?>" class="em-button-vyv-projet w-inline-block" data-ix="arrowcta">
                                        <img src="/projet/images/custom/arrow.svg" alt="" class="em-arrowcta">
                                        <div class="em-textcta"><?= JText::_('VOIR CE PROJET ET VOTER'); ?></div>
                                    </a>
                                    <!--<div class="em-partage">
                                        <?php if($current_user->id != 0){ ?>
                                            <a id="favoris<?=$i?>" data-fnum="<?=$fnum?>" onclick="favoris(<?=$i?>,<?=$current_user->id?>)"><i class="fas fa-star"></i></a>
                                        <?php } ?>
                                        <a id="share<?=$i;?>" data-fnum="<?= $fnum; ?>" data-url="<?= JRoute::_($url);?>" onclick="share(<?=$i;?>)"><i class="fas fa-share-square"></i></a>
                                    </div>-->
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
                        <?php if ($countVote == 1 || $nbVote == 1) { ?>
                            <div class="overlay"></div>
                        <?php } ?>
                        <div class="em-rowproject w-row">
                            <div class="w-col w-col-6">
                                <div class="em-wrappercontainerimage w-clearfix">
                                    <?php if (($i % 3) == 0 ) { ?>
                                        <div data-w-id="afa6a3c8-1634-0848-b10c-b657a0400b11" class="<?= $countVote == 0 ? "em-containerimage" : "em-containerimage-bloque"; ?>"><?= !empty($filename) ? '<img src="'.JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename.'" alt="Challenge solidaire" sizes="(max-width: 479px) 86vw, (max-width: 767px) 87vw, (max-width: 991px) 43vw, 36vw" class="em-imageproject">' :'' ?></div>
                                    <?php } ?>
                                    <?php if (($i % 3) == 1 ) { ?>
                                        <div data-w-id="afa6a3c8-1634-0848-b10c-b657a0400b11" class="<?= $countVote == 0 ? "em-containerimage2" : "em-containerimage2-bloque"; ?>"><?= !empty($filename) ? '<img src="'.JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename.'" alt="Challenge solidaire" sizes="(max-width: 479px) 86vw, (max-width: 767px) 87vw, (max-width: 991px) 43vw, 36vw" class="em-imageproject">' :'' ?></div>
                                    <?php } ?>
                                    <?php if (($i % 3) == 2 ) { ?>
                                        <div data-w-id="afa6a3c8-1634-0848-b10c-b657a0400b11" class="<?= $countVote == 0 ? "em-containerimage3" : "em-containerimage3-bloque"; ?>"><?= !empty($filename) ? '<img src="'.JUri::base() .'images'.DS.'emundus'.DS.'files'.DS.$group[$i]->data->$user.DS.$filename.'" alt="Challenge solidaire" sizes="(max-width: 479px) 86vw, (max-width: 767px) 87vw, (max-width: 991px) 43vw, 36vw" class="em-imageproject">' :'' ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="w-col w-col-6">
                                <div class="em-wrappertextproject">
                                    <h2 class="em-titleproject"><?= $group[$i]->data->$titre; ?></h2>
                                    <h3 class="em-thematiqueproject"><?= $group[$i]->data->$thematique; ?></h3>
                                    <p class="em-paragrapheprojet"><?= $group[$i]->data->$description; ?></p>


                                    <?php
                                    if($current_user->guest){
                                        $url = JRoute::_('index.php?option=com_users&view=login&Itemid=1135&return=' . base64_encode($url_detail));
                                    }
                                    else{
                                        $url = JRoute::_($url_detail);
                                    } ?>

                                    <a href="<?= $url; ?>" class="em-button-vyv-projet w-inline-block" data-ix="arrowcta">
                                        <img src="/projet/images/custom/arrow.svg" alt="" class="em-arrowcta">
                                        <div class="em-textcta"><?= JText::_('VOIR CE PROJET ET VOTER'); ?></div>
                                    </a>
                                    <!--<div class="em-partage">
                                        <?php if($current_user->id != 0){ ?>
                                            <a id="favoris<?=$i?>" data-fnum="<?=$fnum?>" onclick="favoris(<?=$i?>,<?=$current_user->id?>)"><i class="fas fa-star"></i></a>
                                        <?php } ?>
                                        <a id="share<?=$i;?>" data-fnum="<?= $fnum; ?>" data-url="<?= JRoute::_($url);?>" onclick="share(<?=$i;?>)"><i class="fas fa-share-square"></i></a>
                                    </div>-->
                                </div>
                            </div>
                        </div>
                    </div>
                <?php }
            }
        }  ?>
        <?= $this->nav; ?>
        <div class="em-sectionfooter">
            <div class="em-divcontainerfooter">
                <div class="em-rowfooter"><a href="/protections-des-donnees.html" class="em-protections-des-donnees">Protection des données</a><a href="/mentions-legales.html" class="em-mentionslegales">Mentions légales</a>
                    <div class="div-block-2">
                        <div class="em-wrappermenufooter"><a href="/index.html" class="em-menufooter">Le challenge</a><a href="/projet/index.php?option=com_fabrik&view=list&listid=359" class="em-menufooter">PROJETS</a><a href="/reglement.html" class="em-menufooter">règlement</a><a href="/a-propos.html" class="em-menufooter">à propos</a></div>
                    </div>
                    <div><img src="/projet/images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF.jpg" alt="VYV groupe logo" srcset="/projet/images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF-p-500.jpeg 500w, /projet/images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF-p-800.jpeg 800w, /projet/images/custom/Composite_Grpe-VYVEMV_9entites_Q-VF.jpg 1000w" sizes="(max-width: 479px) 94vw, (max-width: 767px) 81vw, (max-width: 991px) 58vw, 63vw" class="em-logofooter"></div>
                </div>
            </div>
        </div>



        <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.4.1.min.220afd743d.js" type="text/javascript" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>

        <!-- [if lte IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script><![endif] -->
        <?php print_r($this->hiddenFields);?>
    </div>

</form>

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
    function favoris(index,user) {
        var favoris = document.getElementById('favoris' + index);
        var star = document.querySelector('#favoris' + index + ' i');
        var fnum_favoris = favoris.getAttribute('data-fnum');
        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=award&task=favoris',
            data: ({
                fnum: fnum_favoris,
                user: user
            }),
            success: function (result) {

                if (result.status == 'add') {
                    star.addClass('starActive');
                }
                if (result.status == 'delete') {
                    star.removeClass('starActive');
                }
            }
        });
    }
</script>