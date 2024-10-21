<?php
defined('_JEXEC') or die;
?>

<script language='JavaScript'
        src='https://d13qcyivyon4xf.cloudfront.net/assistant/js/LivingActorAssistant.min.js'></script>
<link rel='stylesheet' href='https://cisirh.livingactor.com/css/styles_cisirh.css'>
<script language='JavaScript' type='text/JavaScript'>

    var _laaO = _laaO || {};
    _laaO["ref"] = 'oTNwJzX4MzXl5Wan5WZ';
    _laaO["host"] = 'https://d13qcyivyon4xf.cloudfront.net/assistant/v1/{ref}/'
    _laaO["config"] = 'itf_Responsive.xml';
    _laaO["autoLaunch"] = false;
    _laaO['callBtnColors'] = ['<?php echo $callBtnColors; ?>'];
    _laaO["callBtnTxt"] = '<?php echo $callBtnText; ?>'


    //NAU 2020-10-02 - Chatbot RDP : filtre titulaire par défaut
    _laaO["flag"] = '<?php echo $flag; ?>'

    // Les balises suivantes servent à positionner le bouton sur votre page

    // Placement en haut a droite (dans la bannière):
    // > Positionnement et rétrécissement de l'image:
    _laaO["LA_AvatarPicture"] = {
        left: "-29px",
        bottom: "0px",
        width: "48px",
        height: "34px",
        backgroundSize: "100%"
    };

    // > Positionnement et de l'arriere plan:
    _laaO["LA_EmbedCallAreaA"] = {
        width: '<?php echo $width; ?>' + 'px',
        height: '<?php echo $height; ?>' + 'px',
        right: '<?php echo $right; ?>' + 'px',
        bottom: '<?php echo $bottom; ?>' + 'px'
    };
    // Positionnement et de l'arriere plan:
    _laaO["LA_EmbedCallContent"] = {
        width: "250px",
        height: "25px",
        marginTop: "5px"
    };
    // Positionnement et du texte defilant:
    _laaO["LA_EmbedCallTitle"] = {
        paddingTop: "5px",
        // Vitesse et nombre de défilement du texte sur le bouton :
        //choose a number between 10 and 20
        translationSpeed: 15,
        numberOfAppearance: 2
    }

    // Positionnement et de l'alerte:
    _laaO["LA_AlertPush"] = {
        timeout: 700
    }
    _laaO["onOpen"] = function () {
        LivingActor.CISIRH.InitFiltersList(['Titulaire', 'Stagiaire', 'Contractuel', 'Ouvrier']);
    };

    // Instanciation du chatbot avec les paramètres définis précédemment :
    var LivingActorAssistant = new LivingActor.Assistant(_laaO);

    // Séquence d’ouverture (obligation de saisie d’un filtre de population) [Ne pas modifier] :
    /**
     * User Input Listener
     */
    LivingActorAssistant.addEventListener(
        LivingActor.Assistant.EVENT_onUserInput,
        null,
        function (target, type, parameters) {
            if (LivingActorAssistant.options["flag"] == "") {
                if (typeof parameters == "object" && parameters.text != null) {
                    var iswelcome = parameters.text.match(/show:welcome\d*|dosave\:/);
                    if (!iswelcome && parameters.text != 'id:S1213') {
                        LA_UserInput('id:S1213');
                    }
                }
            }
        }
    );

    // Réglages des paramètres de contextualisation :
    var contextvars = LivingActor.Assistant.getInstance().options['contextvars'];
    contextvars.application = '<?php echo $application; ?>';
    contextvars.entite = '<?php echo $entite; ?>';
    contextvars.partenaire = '<?php echo $partenaire; ?>';
    contextvars.ministere = '<?php echo $ministere; ?>';
    contextvars.email = '<?php echo $contact_email; ?>';
</script>

