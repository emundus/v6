var base_url = '<?php echo JURI::root();?>';
var tourot = new Tour({

    name: "tourot",
    steps: [
        {
            element: "#sp_support_saml",
            title: "Contact Us",
            content: "Feel free to contact us for any queries or issues regarding plugin. We will help you with configuration too.",
            backdrop: 'body',
            backdropPadding: '6',
        }, {
            element: "#idptab",
            title: "IDP Configuration",
            content: "Configure this tab using IDP information which you get from IDP-Metadata XML",
            backdrop: 'body',
            backdropPadding: '6',
            onNext: function () {
                jQuery('a[href="#description"]').click();
            },
        },
        {
            element: "#descriptiontab",
            title: "Service Provider Info",
            content: "This tab provides details to configure your IDP.",
            backdrop: 'body',
            backdropPadding: '6',
            onPrev: function () {
                jQuery('a[href="#identity-provider"]').click();
            },
            onNext: function () {
                jQuery('a[href="#service-provider"]').click();
            }


        }, {
            element: "#ssotab",
            title: "Single Sign on Settings",
            content: "You will get the information like SSO link, auto redirect option and more",
            backdrop: 'body',
            backdropPadding: '6',
            onNext: function () {
                jQuery('a[href="#licensing-plans"]').click();
            },
            onPrev: function () {
                jQuery('a[href="#description"]').click();
            }


        }, {
            element: "#licensingtab",
            title: "Licensing.",
            content: " You can find premium features and could upgrade to our premium plans.",
            backdrop: 'body',
            backdropPadding: '6',
            onNext: function () {
                jQuery('a[href="#identity-provider"]').click();
            },
            onPrev: function () {
                jQuery('a[href="#service-provider"]').click();
            }

        },
        {
            element: "#sp_ot_tourend",
            title: "Overall Tour Button",
            content: "Click here to know what each tab does.",
            backdrop: 'body',
            backdropPadding: '6',
            onPrev: function () {
                jQuery('a[href="#licensing-plans"]').click();
            }
        },
    ]

});

var touridp = new Tour({
    name: "tour",
    steps: [
        {
            element: "#sp_upload_metadata",
            title: "Upload Metadata",
            content: "If you have a metadata URL or file provided by your IDP, click on the button or you can configure the plugin manually",
            backdrop: 'body',
            backdropPadding: '6'
        },
        {
            element: "#sp_entity_id_idp",
            title: "Entity ID",
            content: "Enter your Identity Provider Entity Id / Issuer.",
            backdrop: 'body',
            backdropPadding: '6'
        },
        {
            element: "#sp_sso_url_idp",
            title: "Single Sign-On Service URL",
            content: "Enter your SAML Login URL from Identity Provider",
            backdrop: 'body',
            backdropPadding: '6'
        },
        {
            element: "#sp_certificate_idp",
            title: "X.509 Certificate",
            content: "Public key of your IDP to read the signed SAML Assertion/Response",
            backdrop: 'body',
            backdropPadding: '6'
        },
        {
            element: "#test-config",
            title: "Test Configuration",
            content: "It helps you to test the SSO and know what attributes are getting from IDP and configure them in attribute-mapping and Group-mapping Tab",
            backdrop: 'body',
            backdropPadding: '6'
        },
        {
            element: "#idp_end_tour",
            title: "Tour ends",
            content: "Click here to restart tour",
            backdrop: 'body',
            backdropPadding: '6'
        }

    ]
});


var tourds = new Tour({
    name: "tour",
    steps: [
        {
            element: "#idp_metadata_url",
            title: "Metadata Link",
            content: "You can use this metadata URL/link to configure your IDP",
            backdrop: 'body',
            backdropPadding: '6'

        }, {
            element: "#mo_download_metadata",
            title: "Download Metadata",
            content: "Download the metadata and provide this metadata to configure your IDP",
            backdrop: 'body',
            backdropPadding: '6'

        },
        {
            element: "#mo_other_idp",
            title: "Configuration Endpoints",
            content: "You can use this metadata to configure your IDP",
            backdrop: 'body',
            backdropPadding: '6'
        },
        {
            element: "#sp_ds_tourend",
            title: "Tour End",
            content: "Please click here to restart the tour",
            backdrop: 'body',
            backdropPadding: '6'
        },

    ]
});

var toursso = new Tour({
    name: "tour4",
    steps: [
        {
            element: "#mo_sp_sso_link_button",
            title: "SSO login link",
            content: "This link is used for Single Sign-On by end users. Add a button on your site login page with the following URL",
            backdrop: 'body',
            backdropPadding: '6'
        },
        {
            element: "#mo_sp_sso_end_tour",
            title: "Tour ends",
            content: "Click here to restart tour",
            backdrop: 'body',
            backdropPadding: '6'
        }

    ]
});


var touratt = new Tour({
    name: "tour5",
    steps: [
        {
            element: "#mo_saml_uname",
            title: "Username in Joomla Account",
            content: "NameID attribute is used for storing Username and Email. Make sure IDP send email in NameID.",
            backdrop: 'body',
            backdropPadding: '6',

        }, {
            element: "#mo_sp_attr_end_tour",
            title: "Tour ends",
            content: "Click here to restart tour",
            backdrop: 'body',
            backdropPadding: '6'
        }

    ]
});

var tourgrp = new Tour({
    name: "tour6",
    steps: [
        {
            element: "#mo_sp_grp_enable",
            title: "Enable Group Mapping",
            content: "Enable this option to assign the group for both new user and login user(including admin account)",
            backdrop: 'body',
            backdropPadding: '6'
        }, {
            element: "#mo_sp_grp_defaultgrp",
            title: "Groups",
            content: "Select the group to assign a default group while user creation.",
            backdrop: 'body',
            backdropPadding: '6'
        }, {
            element: "#mo_sp_grp_end_tour",
            title: "Tour ends",
            content: "Click here to restart tour",
            backdrop: 'body',
            backdropPadding: '6'
        }

    ]
});

var tourexp = new Tour({
    name: "tour7",
    steps: [
        {
            element: "#mo_sp_exp_exportconfig",
            title: "Export configuration",
            content: "Click here to download the configuration file.",
            backdrop: 'body',
            backdropPadding: '6'
        },
        {
            element: "#mo_sp_exp_end_tour",
            title: "Tour ends",
            content: "Click here to restart tour",
            backdrop: 'body',
            backdropPadding: '6'
        }

    ]
});


function restart_tourrg() {
    tourrg.restart();
}

var tourrg = new Tour({
    name: "tour",
    steps: [
        {
            element: "#spregister",
            title: "Account Setup",
            content: "Please enter required data to create/login into the account.",
            backdrop: 'body',
            backdropPadding: '6'
        },
        {
            element: "#sprg_end_tour",
            title: "Tour ends",
            content: "Click here to restart tour",
            backdrop: 'body',
            backdropPadding: '6'
        }

    ]
});


var base_url = '<?php echo JURI::root(); ?>';
var tabtour = new Tour({
    name: "tabtour",
    steps: [
        {
            element: "#idptab",
            title: "IDP Configuration",
            content: "Configure this tab using IDP information which you get from IDP-Metadata XML",
            backdrop: 'body',
            backdropPadding: '6',
        },
        {
            element: "#sp_support",
            title: "Contact Us",
            content: "Feel free to contact us for any queries or issues regarding the plugin. We will help you with configuration too.",
            backdrop: 'body',
            backdropPadding: '6',
            onNext: function () {
                jQuery('a[href="#description"]').click();
            }
        },
        {
            element: "#descriptiontab",
            title: "Service Provider Info",
            content: "This tab provides details to configure your IDP.",
            backdrop: 'body',
            backdropPadding: '6',
            onPrev: function () {
                jQuery('a[href="#identity-provider"]').click();
            },
            onNext: function () {
                jQuery('a[href="#sso_settings"]').click();
            }
        },
        {
            element: "#ssotab",
            title: "Single Sign on Settings",
            content: "You will get the information like SSO link, auto redirect option and more",
            backdrop: 'body',
            backdropPadding: '6',
            onNext: function () {
                jQuery('a[href="#licensing-plans"]').click();
            },
            onPrev: function () {
                jQuery('a[href="#description"]').click();
            }
        },
        {
            element: "#licensingtab",
            title: "Licensing.",
            content: " You can find premium features and could upgrade to our premium plans.",
            backdrop: 'body',
            backdropPadding: '6',
            onPrev: function () {
                jQuery('a[href="#sso_settings"]').click();
            }
        },
        {
            element: "#sprg_end_tour",
            title: "Tab Tour",
            content: "You could find the start tour button on each tab which will help you to configure the tab /get the inforamtion from that tab.",
            backdrop: 'body',
            backdropPadding: '6',
        },
        {
            element: "#sp_ot_tourend",
            title: "Overall Tour Button",
            content: "Click on this button to know what each tab does.",
            backdrop: 'body',
            backdropPadding: '6',
        },
    ]
});

tabtour.init();
tabtour.start();

function restart_tourds() {
    tourds.restart();
}

function restart_tourgrp() {
    tourgrp.restart();
}

function restart_toursso() {
    toursso.restart();
}

function restart_touratt() {
    touratt.restart();
}

function restart_tourot() {
    jQuery('a[href="#identity-provider"]').click();
    tourot.restart();
}

function restart_touridp() {
    touridp.restart();
}

			


        


        