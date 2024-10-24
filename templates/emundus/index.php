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
    <?php
    JHtml::_('jquery.framework');
    JHtml::_('bootstrap.framework');

    $doc = JFactory::getDocument();
    $doc->addStyleSheet('templates/'.$this->template.'/css/bootstrap.css');
    $doc->addStyleSheet('templates/'.$this->template.'/css/normalize.css');
    $doc->addStyleSheet('templates/'.$this->template.'/css/webflow.css');
    $doc->addStyleSheet('templates/'.$this->template.'/css/template.css');
    $style = $this->params->get('custom_css');
    if (($style || $style == Null) && !empty($style)) {
        $doc->addStyleDeclaration($style);
    }
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
            <div id="g-grid" class="g-grid size-20">
                <div id="g-block-size-100" class="g-block-size-20">
                    <div id="g-content" class="g-content">
                        <div id="platform-content" class="platform-content">
                            <div id="moduletable" class="moduletable">
                                <?php
                                $showcolumn = $this->countModules('header-a-saas');
                                ?>
                                <?php if($showcolumn): ?>
                                    <jdoc:include type="modules" name="header-a-saas" style="<?php if(($this->params->get('header-a-saas') == 'block') || ($this->params->get('header-a-saas') == Null)): echo "block"; else: echo "xhtml"; endif;?>"/>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="moduletable-b" class="moduletable-b size-50">
                <?php
                $showcolumn = $this->countModules('header-onboarding');
                ?>
                <?php if($showcolumn): ?>
                <nav id="siteNav">
                    <a href="#" id="menuToggler" class="show-on-small">&#9776;</a>
                    <jdoc:include type="modules" name="header-onboarding"/>
                </nav>
                <?php endif; ?>
            </div>
            <div id="moduletable-d" class="moduletable-d size-30">
                <?php
                $showcolumn = $this->countModules('header-c');
                ?>
                <?php if($showcolumn): ?>
                    <jdoc:include type="modules" name="header-c"/>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <section id="g-feature" class="g-feature">
        <div id="g-container" class="g-container">
            <div id="moduletable-f" class="moduletable-f size-9">
                <?php
                $showcolumn = $this->countModules('content-tutorial-a');
                ?>
                <?php if($showcolumn): ?>
                    <jdoc:include type="modules" name="content-tutorial-a"/>
                <?php endif; ?>
            </div>
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
            <div class="col-md-12" style="width: 100%">
                <jdoc:include type="component" />
            </div>
        </div>
    </section>
</div>
<div id="debug" class="debug">
    <?php if ($this->countModules('debug')){ ?>
        <jdoc:include type="modules" name="debug" style="<?php if(($this->params->get('debug') == 'block') || ($this->params->get('debug') == Null)): echo "block"; else: echo "xhtml"; endif;?>"/>
    <?php } ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script type="text/javascript">
    (function($){
        $(document).ready(function(){
            $('#menuToggler').on('click', function(e){
                e.preventDefault;
                $('#siteNav ul').toggleClass('menuIsActive');
            });
        });

        let found = false;

        // Get route
        const path = window.location.pathname.split('/');
        const route = path[path.length - 1];
        const menu = document.getElementById('moduletable-b');
        //

        // Get view params
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const view = urlParams.get('view')
        //

        menu.childNodes[1].childNodes.forEach((element) => {
            if(element.tagName == 'UL') {
                element.childNodes.forEach((list) => {
                    if (list.tagName == 'LI') {
                        list.childNodes.forEach((link) => {
                            if (link.tagName == 'A' && !found) {
                                let find;
                                if (view != null) {
                                    find = link.attributes.href.nodeValue.search(view);
                                } else {
                                    find = link.attributes.href.nodeValue.search(route);
                                }
                                if (find !== -1) {
                                    link.className = 'menu-current-link';
                                    found = true;
                                }
                            }
                        });
                    }
                });
            }
        });
    })(jQuery);
</script>
</body>
</html>
