<?php 
defined('_JEXEC') or die ('Restricted access');
$app  = JFactory::getApplication();
$doc = JFactory::getDocument();
?>
<!DOCTYPE html>
<html xmlns="//www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
<jdoc:include type="head" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php global $template_path;
$template_path = JURI::base() . 'templates/' . $app->getTemplate(); ?>
<?php JLoader::import( 'joomla.version' );
$version = new JVersion();
if (version_compare( $version->RELEASE, "2.5", "<=")) {
  if(JFactory::getApplication()->get('jquery') !== true) {
    $document = JFactory::getDocument();
    $headData = $this->getHeadData();
    reset($headData['scripts']);
    $newHeadData = $headData['scripts'];
    $jquery = array(JURI::base() .'/templates/' . $this->template . '/js/jquery.js' => array('mime' => 'text/javascript', 'defer' => FALSE, 'async' => FALSE));
    $newHeadData = $jquery + $newHeadData;
    $headData['scripts'] = $newHeadData;
    $this->setHeadData($headData);
    $doc->addScript(JURI::base() .'/templates/' . $this->template . '/js/jui/bootstrap.min.js', 'text/javascript');
  }
} else {
  JHtml::_('jquery.framework');
  JHtml::_('bootstrap.framework');
} ?>
<?php
if (version_compare( $version->RELEASE, "2.5", "<")) {
  JHtml::_('jquery.ui');
}
$doc = JFactory::getDocument();
$doc->addStyleSheet('templates/'.$this->template.'/css/normalize.css');
$doc->addStyleSheet('templates/'.$this->template.'/css/webflow.css');
$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');
$style = $this->params->get('custom_css');
if (($style || $style == Null) && !empty($style)) {
 $doc->addStyleDeclaration($style);
}
$doc->addScript($template_path.'/js/webflow.js');
?>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="icon" type="image/x-icon" href="/" />
<meta charset="utf-8" />
<meta name="robots" content="noindex, nofollow" />
<title>Se connecter</title>
</head>
<body class="gantry-g-helium-style-site-com_users-view-login-no-layout-no-task-dir-ltr-login-em-formregistrationcenter-em-sectionlogin-itemid-1135-outline-25-g-joomla-gantry4-g-style-preset4">
<div id="g-page-surround" class="g-page-surround">
  <div id="g-menu-overlay" class="g-menu-overlay"></div>
  <section id="g-navigation" class="g-navigation">
    <div id="g-container" class="g-container">
      <div id="g-grid" class="g-grid">
        <div id="g-block-size-100" class="g-block-size-100">
          <div id="g-content" class="g-content">
            <div id="platform-content" class="platform-content">
              <div id="moduletable" class="moduletable">
<?php
$showcolumn= $this->countModules('header-a');
?>
<?php if($showcolumn): ?>
<jdoc:include type="modules" name="header-a" style="<?php if(($this->params->get('header-a') == 'block') || ($this->params->get('header-a') == Null)): echo "block"; else: echo "xhtml"; endif;?>"/>
<?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section id="g-feature" class="g-feature">
    <div id="g-container" class="g-container">
      <div id="g-grid" class="g-grid">
        <div id="g-block-size-100" class="g-block-size-100">
          <div id="g-system-messages" class="g-system-messages">
            <div id="system-message-container" class="system-message-container">
              <jdoc:include type="message" style="width:100%;"/>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section id="g-container-main" class="g-container-main">
    <div id="g-container" class="g-container">
      <div id="g-grid" class="g-grid">
        <div id="g-block-size-100" class="g-block-size-100">
          <main id="g-main-mainbody" class="g-main-mainbody">
            <div id="g-grid" class="g-grid">
              <div id="g-block-size-100" class="g-block-size-100">
                <div id="g-content" class="g-content">
                  <div id="platform-content-row-fluid" class="platform-content-row-fluid">
                    <div id="span12" class="span12">
                        <div class="w-form">
                        <jdoc:include type="component" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </main>
        </div>
      </div>
    </div>
  </section>
  <div id="g-container" class="g-container">
    <footer id="g-footer" class="g-footer">
      <div id="g-grid" class="g-grid">
        <div id="footer-a" class="g-block-size-100-footer-a">
          <div id="g-content" class="g-content">
            <div id="platform-content" class="platform-content">
              <div id="moduletable-footer-legal" class="moduletable-footer-legal">
<?php
$showcolumn= $this->countModules('footer-a');
?>
<?php if($showcolumn): ?>
<jdoc:include type="modules" name="footer-a" style="<?php if(($this->params->get('footer-a') == 'block') || ($this->params->get('footer-a') == Null)): echo "block"; else: echo "xhtml"; endif;?>"/>
<?php endif; ?>
                        
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
</div>
<div id="debug" class="debug">
  <?php if ($this->countModules('debug')){ ?>
  <jdoc:include type="modules" name="debug" style="<?php if(($this->params->get('debug') == 'block') || ($this->params->get('debug') == Null)): echo "block"; else: echo "xhtml"; endif;?>"/>
<?php } ?>
</div>
</body>
</html>