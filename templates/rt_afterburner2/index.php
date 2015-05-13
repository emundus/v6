<?php
/**
* @version   $Id: index.php 26432 2015-02-05 21:39:35Z reggie $
* @author RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

/* No Direct Access */
defined( '_JEXEC' ) or die( 'Restricted index access' );
/* Load Mootools */
JHTML::_('behavior.framework', true);
// JQuery en mode "no conflict"
JHtml::_('jquery.framework');
/* Load and Inititialize Gantry Class */
require_once(dirname(__FILE__) . '/lib/gantry/gantry.php');
$gantry->init();
/* Get the Current Preset */
$gpreset = str_replace(' ','',strtolower($gantry->get('name')));
?>
<!doctype html>
<html xml:lang="<?php echo $gantry->language; ?>" lang="<?php echo $gantry->language;?>" >
<head>
	<?php if ($gantry->get('layout-mode') == '960fixed') : ?>
	<meta name="viewport" content="width=960px">
	<?php elseif ($gantry->get('layout-mode') == '1200fixed') : ?>
	<meta name="viewport" content="width=1200px">
	<?php else : ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php endif; ?>
    <?php
        $gantry->displayHead();
		/* Force IE to most recent version */
		if ($gantry->browser->name == 'ie') : 
			echo '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
		endif;        

		if ($gantry->get('layout-mode', 'responsive') == 'responsive') $gantry->addStyle('grid-responsive.css', 5);
		$gantry->addLess('bootstrap.less', 'bootstrap.css', 6);   

        if ($gantry->browser->name == 'ie'){
        	if ($gantry->browser->shortversion == 9){
        		$gantry->addInlineScript("if (typeof RokMediaQueries !== 'undefined') window.addEvent('domready', function(){ RokMediaQueries._fireEvent(RokMediaQueries.getQuery()); });");
        	}
			if ($gantry->browser->shortversion == 8){
				$gantry->addScript('html5shim.js');
			}
		}
		if ($gantry->get('layout-mode', 'responsive') == 'responsive') $gantry->addScript('rokmediaqueries.js');
        $gantry->addScript('tab.js');
        $gantry->addScript('jquery.cookie.js');
    ?>
    <script>
        jQuery(document).ready(function() {
            jQuery('.gf-menu.l1 ').parent().attr('id', 'topmenu');
            if (jQuery(window).width() <= 767) {
                jQuery("#topmenu").appendTo("#responsive_menu");
            }
        });
    </script>

    <noscript><style>.javascript_alert {
                background: #F8FAFC url(images/emundus/icones/agt_update_critical_32x32.png) center no-repeat;
                background-position: 15px 50%; /* x-pos y-pos */
                color: red;
                text-align: center;
                font-weight: bold;
                font-size: medium;
                padding: 5px 20px 5px 45px;
                border-top: 2px solid #B5D4FE;
                border-bottom: 2px solid #B5D4FE;
            }</style></noscript>

</head>
<body <?php echo $gantry->displayBodyTag(); ?>>
    <noscript>
        <p class="javascript_alert"><?php echo JText::_('JAVASCRIPT_REQUIRED'); ?> <a href="http://www.activatejavascript.org/" target="_blank"><?php echo JText::_('JAVASCRIPT_GET_HELP_ON'); ?> http://www.activatejavascript.org/</a></p>
    </noscript>

    <?php /** Begin Top Surround **/ if ($gantry->countModules('top') or $gantry->countModules('header')) : ?>
    <header id="rt-top-surround">
		<?php /** Begin Top **/ if ($gantry->countModules('top')) : ?>
		<div id="rt-top" <?php echo $gantry->displayClassesByTag('rt-top'); ?>>
			<div class="rt-containertop">
				<?php echo $gantry->displayModules('top','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
		<?php /** End Top **/ endif; ?>
		<?php /** Begin Header **/ if ($gantry->countModules('header')) : ?>
		<div id="rt-header">
			<div class="rt-container">
				<?php echo $gantry->displayModules('header','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
		<?php /** End Header **/ endif; ?>
        <div id="responsive_menu" class="rt-grid-12">
        </div>
		<?php /** Begin Second Header **/ if ($gantry->countModules('secondheader')) : ?>
		<div id="rt-secondheader">
			<div class="rt-container">
				<?php echo $gantry->displayModules('secondheader','standard','standard'); ?>
				<div class="clear"></div>
			</div>
		</div>
		<?php /** End Second Header **/ endif; ?>
		<?php /** Begin Second Menu**/ ?>
		<div id="rt-secondmenu">
			<nav class="nav-area">
      			<div class="rt-container">
        			<div id="nav">
        				<?php if ($gantry->countModules('secondmenu')) : ?>
        				<?php echo $gantry->displayModules('secondmenu','standard','standard'); ?>
        				<?php endif; ?>
        			</div>
      			</div>
    		</nav>	
			<div class="clear"></div>
		</div>
		<?php /** End Second Menu **/ ?>
	</header>
	<?php /** End Top Surround **/ endif; ?>

	<?php /** Begin Drawer **/ if ($gantry->countModules('drawer')) : ?>
 		<div id="rt-drawer">
			<div class="rt-container">
           		<?php echo $gantry->displayModules('drawer','standard','standard'); ?>
           		<div class="clear"></div>
			</div>
       </div>
   	<?php /** End Drawer **/ endif; ?>
	<?php /** Begin Breadcrumbs **/ if ($gantry->countModules('breadcrumb')) : ?>
	<div id="rt-breadcrumbs">
		<div class="rt-container">
			<?php echo $gantry->displayModules('breadcrumb','standard','standard'); ?>
	   		<div class="clear"></div>				   
		</div>
	</div>
	<?php /** End Breadcrumbs **/ endif; ?>

    <div id="rt-mainbody-surround">
        <div class="rt-container">
            <?php /** Begin Main Body **/ ?>
            <?php echo $gantry->displayMainbody('mainbody','sidebar','standard','standard','standard','standard','standard'); ?>
            <?php /** End Main Body **/ ?>
        </div>
    </div>

    <?php /** Begin Bottom **/ if ($gantry->countModules('bottom')) : ?>
	<div id="rt-bottom">
		<div class="rt-container">		
			<?php echo $gantry->displayModules('bottom','standard','standard'); ?>
			<div class="clear"></div>
		</div>
	</div>
	<?php /** End Bottom **/ endif; ?>
	
	<?php /** Begin Footer **/ if ($gantry->countModules('footer')) : ?>
	<div id="rt-footer"> 
		<br />    
        <footer id="footer">
            <div class="notes">
                <p>
                    <em>
                        Conformément à la loi « informatique et libertés » du 6 janvier 1978 modifiée en 2004,
                        vous bénéficiez d’un droit d’accès et de rectification aux informations qui vous concernent, <br>
                        que vous pouvez exercer en <a href="index.php?option=com_contact&amp;view=contact&amp;id=6:contact-cnil&amp;catid=47&amp;Itemid=139">nous adressant un message en cliquant ici</a>
                    </em>
                    <br>
                    Sorbonne Universités rassemble des universités et des établissements d’enseignement supérieur et de recherche, reconnus pour leur excellence scientifique et pédagogique.
                </p>
            </div>
          	<div class="footerSU blue">
          		<div class="rt-containerbottom">
          			<div class="blasonSU">
          				<a href="http://www.sorbonne-universites.fr/" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-sorbonne.png" width="24" height="26" alt=""></a>
          			</div>
          			<div class="sepSU">
          				<img class="light" src="//headerfooter.sorbonne-universites.fr/typo3conf/ext/su_headerfooter/Resources/Public/Images/footerSU-light-separator.png" width="1" height="57" alt="">
          			</div>
          			<ul class="partenairesSU">
          				<li>
							<a href="http://www.paris-sorbonne.fr/" title="Université Paris-Sorbonne" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-paris-sorbonne.png" width="55" height="41" alt=""></a>
						</li>
						<li>
							<a href="http://www.upmc.fr/" title="Université Pierre et Marie Curie" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-upmc.png" width="52" height="17" alt=""></a>
						</li>
						<li>
							<a href="http://www.utc.fr/" title="Université de Technologie de Compiègne" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-utc.png" width="71" height="24" alt=""></a>
						</li>
						<li>
							<a href="http://www.insead.edu/" title="Institut Européen d'Administration des Affaires" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-insead.png" width="57" height="19" alt=""></a>
						</li>
						<li>
							<a href="http://www.mnhn.fr/" title="Muséum National d'Histoire Naturelle" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-museum.png" width="83" height="25" alt=""></a>
						</li>
						<li>
							<a href="http://www.pspbb.fr/" title="Pôle Supérieur Paris Boulogne-Billancourt" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-pspbb.png" width="66" height="46" alt=""></a>
						</li>
						<li>
							<a href="http://www.cnrs.fr/" title="Centre national de la Recherche Scientifique" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-cnrs.png" width="30" height="29" alt=""></a>
						</li>
						<li>
							<a href="http://www.inserm.fr/" title="Institut National de la Santé et de la Recherche Médicale" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-inserm.png" width="89" height="23" alt=""></a>
						</li>
						<li>
							<a href="http://www.ird.fr/" title="Institut de Recherche pour le Développement" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-ird.png" width="48" height="25" alt=""></a>
						</li>
						<li>
							<a href="http://www.inria.fr/" title="Institut National de Recherche en Informatique et en Automatique" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-inria.png" width="65" height="22" alt=""></a>
						</li>
						<li>
							<a href="http://www.ciep.fr/" title="Centre International d'Etudes Pédagogiques" target="_blank"><img class="light" src="//headerfooter.sorbonne-universites.fr/fileadmin/user_upload/footerSU-light-logo-ciep.png" width="28" height="47" alt=""></a>
						</li>
					</ul>
				</div>	
			</div>
            <p style="color: #000000;float: right;">Logiciel <em><a target="_blank" title="Your open source candidatures management" href="http://www.emundus.fr">eMundus®</a></em></p>
        </footer>
  </div>
		<div class="rt-container">
			<?php //echo $gantry->displayModules('footer','standard','standard'); ?>
			<div class="clear"></div>
		</div>
	
	<?php /** End Footer **/ endif; ?>

	<?php /** Begin Debug **/ if ($gantry->countModules('debug')) : ?>
	<div id="rt-debug">
		<?php echo $gantry->displayModules('debug','standard','standard'); ?>
		<div class="clear"></div>
	</div>
	<?php /** End Debug **/ endif; ?>
	<?php /** Begin Analytics **/ if ($gantry->countModules('analytics')) : ?>
	<?php echo $gantry->displayModules('analytics','basic','basic'); ?>
	<?php /** End Analytics **/ endif; ?>

</body>
<script type="text/javascript">
    var body = document.body,
        html = document.documentElement;

    var height = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );
    var component = "<?php echo $_GET['option'];?>";
    if (body.offsetHeight < html.clientHeight && html.clientHeight>900 && component!="com_emundus") {
        document.getElementById('rt-footer').style.position="fixed";
        document.getElementById('rt-footer').style.bottom=0;
    }

</script>
</html>
<?php
$gantry->finalize();
?>
