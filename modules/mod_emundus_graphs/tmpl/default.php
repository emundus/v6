<?php
defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');
$document = JFactory::getDocument();
$document->addScript('media'.DS.'com_emundus'.DS.'lib'.DS.'plotly'.DS.'plotly.min.js');
?>

<div style="display: inline-block; width: 100%">
    <div id ="statsCompte"style="float: left; width: 50%;">
    <table>
    <tr><td>Type de compte:</td>
        <td><select class="compte" >
            <?php
                echo $distinctProfile;
            ?>
            </select>
        </td>
    </tr>

    <tr><td> Période: </td>
        <td>
            <select class="periodeCompte" >
                <option value='0'>Dernière semaine</option>
                <option value='1'>Deux dernières semaines</option>
                <option value='2'>Dernier mois</option>
                <option value='3'>Trois derniers mois</option>
                <option value='4'>Six derniers mois</option>
            </select>
        </td>
    </tr>
    </table>
        
        <div id="users" ></div>
        <div id="userSummary" style="float: left; padding-left: 30%">
        <?php 
            $count = 0;
            foreach($usersGraph as $ug) {
                $count += $ug[nombre];
            }
            
            echo "<p><i>Nombre d'inscriptions : </i>$count </p>" ;
            
            ?>
        </div>
    </div>

    <div style="float: right;  width: 50%;">
        <table>
        <tr><td>Offres:</td>
            <td><select class="offres" >
                    <?php
                        echo $distinctOffres;
                    ?>
                </select>
            </td>
        </tr>

        <tr><td> Période: </td>
            <td>
                <select class="periodeConsult" >
                    <option value='0'>Dernière semaine</option>
                    <option value='1'>Deux dernières semaines</option>
                    <option value='2'>Dernier mois</option>
                    <option value='3'>Trois derniers mois</option>
                    <option value='4'>Six derniers mois</option>
                </select>
            </td>
        </tr>
        </table>

        <div id="offreBar" style="float: left;"></div>
        <div id="summeryOffres" style="float: left; padding-left: 30%">
        <?php 
            $countConsultation = 0;
            foreach($consultationBar as $cb) {
                $countConsultation += $cb[nombre];
            }
            echo "<p><i>Nombre de consultation des offres: </i>$countConsultation " ;
            ?>
        </div>
        <br>
    </div>
    
    <hr style='width: 100%; border-top: 21px solid #fff;'>
    <br>
    <div style="float: left;  width: 50%;">
    <table>
        <tr><td>Offres:</td>
            <td><select class="candidature" >
                    <?php
                        echo $distinctCandidatures;
                    ?>
                </select>
            </td>
        </tr>

        <tr><td> Période: </td>
            <td>
                <select class="periodeCand" >
                    <option value='0'>Dernière semaine</option>
                    <option value='1'>Deux dernières semaines</option>
                    <option value='2'>Dernier mois</option>
                    <option value='3'>Trois derniers mois</option>
                    <option value='4'>Six derniers mois</option>
                </select>
            </td>
        </tr>
        </table>
        <div id="candLigne" style="float: left;"></div>
        <div id="summeryCandidature" style="float: left; padding-left: 30%">
        <?php 
            $countCandidature = 0;
            foreach($candidature as $candidatures) {
                $countCandidature += $candidatures[nombre];
            }
            echo "<p><i>Nombre de candidature des offres: </i>$countCandidature </p>"; 
            ?>
        </div>
    </div>

    
    
    <div id ="connectionGraph"style="float: left; width: 50%;">
        <table>
        <tr><td> Période: </td>
            <td>
                <select class="periodeCo" >
                    <option value='0'>Dernière semaine</option>
                    <option value='1'>Deux dernières semaines</option>
                    <option value='2'>Dernier mois</option>
                    <option value='3'>Trois derniers mois</option>
                    <option value='4'>Six derniers mois</option>
                </select>
            </td>
        </tr>
        </table>
        <div id="co" ></div>
        <div id='summaryConnexion' style="float: left; padding-left: 30%">
            <?php 
                $countConnexion = 0;
                foreach($connections as $co) {
                    $countConnexion += $co[nombre_connexions];
                }             
                echo "<p><i>Nombre de connexions: </i>$countConnexion " ;
            ?>
        </div>
    </div>

    <hr style='width: 100%; border-top: 21px solid #fff;'>

    <div id ="relationGraph"style="float: left; width: 50%;">
        <table>
        <tr><td> Période: </td>
            <td>
                <select class="periodeRel" >
                    <option value='0'>Dernière semaine</option>
                    <option value='1'>Deux dernières semaines</option>
                    <option value='2'>Dernier mois</option>
                    <option value='3'>Trois derniers mois</option>
                    <option value='4'>Six derniers mois</option>
                </select>
            </td>
        </tr>
        </table>
        <div id="rel" ></div>
        <div id='summaryRelation' style="float: left; padding-left: 30%">
            <?php 
                $countRelations = 0;
                foreach($relations as $rel) {
                    $countRelations += $rel[nombre_rel_etablies];
                }             
                echo "<p><i>Nombre de relations etablies: </i>$countRelations " ;
            ?>
        </div>
    </div>
  <!--  <hr style='width: 100%; border-top: 21px solid #fff;'>
     <div id="avancement" style="float: left; width: 50%;">
        <h3>Avancement des dossiers</h3>
        <table>
            <tr><td><div style="float: left;border-style: groove;">
            <p id="attenteActeur">Nombre de dossiers en attente d'un acteur: </p>
        </div>
        Exporter les données</td></tr>
        <tr><td><div style="border-style: groove; float: left;">
            <p id="finalise">Nombre de dossiers finalisés: </p>
        </div>
        Exporter les données</td></tr>
        <tr><td><div style="float: left;border-style: groove;">
            <p id="validation">Nombre de dossiers en attente de validation: </p>
        </div>
        Exporter les données</td></tr>
        </table>
        
        
        
        
    </div>
    -->
</div>

            



<script type="text/javascript">

// CREATE ARRAY OF USERS
var profileArray = [];
    var dateArray = [];
    var countArray = [];

    
    function afficheGraphe(value,periode) {
       
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getprofiletype",
            dataType: 'json',
            data:({chosenvalue: value,
                    periode: periode
                   }),
            success: function (result) {
                if(result.status) {    
                    var data = [{
                        x: result.datearray,
                        y: result.countarray,
                        type: 'bar',
                        marker: {
                            color: 'rgb(142,124,195)'
                        }
                    }];
                    var layout = {
                        autosize: true,
                        title: 'Nombre de Comptes créés',
                        xaxis: {
                            title: 'Date'
                        },
                        yaxis: {
                            title: 'Nombre'
                        }
                    };
                    var options = {displayModeBar: false};
                    var elem = document.getElementById('users');
                    Plotly.newPlot(elem, data, layout,{displayModeBar: false});
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        }); 
    }


    function afficheLigne(value, periode) {
       
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getcandidatures",
            dataType: 'json',
            data:({chosenvalue: value,
                    periode: periode}),
            success: function (result) {

                //alert(result.result);
                if(result.status) {                
                    var data = [{
                        x: result.datearray,
                        y: result.countarray,
                        mode: 'lines+markers',
                        marker: {
                            color: 'rgb(142,124,195)'
                        }
                    }];
                    var layout = {
                        autosize: true,
                        title: 'Nombre de Candidatures',
                        xaxis: {
                            title: 'Date'
                        },
                        yaxis: {
                            title: 'Nombre'
                        }
                    };
                    var options = {displayModeBar: false};
                    var elem = document.getElementById('candLigne');
                    Plotly.newPlot(elem, data, layout,{displayModeBar: false});
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        }); 
    }


    function afficheBar(value, periode) {
        
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getconsultations",
            dataType: 'json',
            data:({chosenvalue: value,
                    periode: periode}),
            success: function (result) {
                
                //alert(result.result);
                if(result.status) {    
                    var data = [{
                        x: result.datearray,
                        y: result.countarray,
                        type: 'bar',
                        marker: {
                            color: 'rgb(198, 21, 21)'
                        }
                    }];
                    var layout = {
                        autosize: true,
                        title: 'Nombre de Consultations d\'offres',
                        xaxis: {
                            title: 'Date'
                            
                        },
                        yaxis: {
                            title: 'Nombre'
                        }
                    };
                    var options = {displayModeBar: false};
                    var elem = document.getElementById('offreBar');
                    Plotly.newPlot(elem, data, layout,{displayModeBar: false});
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        }); 
    }

    function connectionGraph(periode) {
        
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getconnections",
            dataType: 'json',
            data:({periode: periode}),
            success: function (result) {

                //alert(result.result);
                if(result.status) {    
                    var data = [{
                        x: result.datearray,
                        y: result.countarray,
                        mode: 'lines+markers',
                        marker: {
                            color: 'rgb(198, 21, 21)'
                        }
                    }];
                    var layout = {
                        autosize: true,
                        title: 'Connexion au Site',
                        xaxis: {
                            title: 'Date'
                            
                        },
                        yaxis: {
                            title: 'Nombre'
                        }
                    };
                    var options = {displayModeBar: false};
                    var elem = document.getElementById('co');
                    Plotly.newPlot(elem, data, layout,{displayModeBar: false});
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        }); 
    }

    function relationGraph(periode) {
        
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getrelations",
            dataType: 'json',
            data:({periode: periode}),
            success: function (result) {

                //alert(result.result);
                if(result.status) {    
                    var data = [{
                        x: result.datearray,
                        y: result.countarray,
                        type: 'scatter',
                         mode: 'lines',
                         line: {
                            color: 'rgba(67,67,67,1)',
                            width: '2'
                        }
                    }];
                    var layout = {
                        autosize: true,
                        title: 'Relations établies',
                        xaxis: {
                            title: 'Date'
                            
                        },
                        yaxis: {
                            title: 'Nombre'
                        }
                    };
                    var options = {displayModeBar: false};
                    var elem = document.getElementById('rel');
                    Plotly.newPlot(elem, data, layout,{displayModeBar: false});
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        }); 
    }
    
    jQuery(document).ready(function () {

        valuePeriodecompte = jQuery('.periodeCompte').val();
        valueTimeLine = jQuery('.compte').val();
        afficheGraphe(valueTimeLine, valuePeriodecompte);

        
        valuePeriodeConsult = jQuery('.periodeConsult').val();
        valueBar = jQuery('.offres').val();
        afficheBar(valueBar, valuePeriodeConsult);
        
        valuePeriodeCo = jQuery('.periodeCo').val();
        connectionGraph(valuePeriodeCo);

        valuePeriodeCand = jQuery('.periodeCand').val();
        valueCand = jQuery('.candidature').val();
        afficheLigne(valueCand, valuePeriodeCand);

        valuePeriodeRel = jQuery('.periodeRel').val();
        relationGraph(valuePeriodeRel);

    });

    jQuery('.compte').on('change', function () {
        value = jQuery(this).val();
        valuePeriodecompte = jQuery('.periodeCompte').val();
        
        afficheGraphe(value, valuePeriodecompte);
    });

    jQuery('.periodeCompte').on('change', function () {
        value = jQuery('.compte').val();
        valuePeriodecompte = jQuery(this).val();
        afficheGraphe(value, valuePeriodecompte);
    });

    jQuery('.offres').on('change', function () {
        value = jQuery(this).val();
        valuePeriodeConsult = jQuery('.periodeConsult').val();
        afficheBar(value, valuePeriodeConsult);
    });

    jQuery('.periodeConsult').on('change', function () {
        value = jQuery('.offres').val();
        valuePeriodeConsult = jQuery(this).val();
        afficheBar(value, valuePeriodeConsult);
    });

    jQuery('.candidature').on('change', function () {
        value = jQuery(this).val();
        valuePeriodeCand = jQuery('.periodeCand').val();
        afficheLigne(value, valuePeriodeCand);
    });

    jQuery('.periodeCand').on('change', function () {
        value = jQuery('.candidature').val();
        valuePeriodeCand = jQuery(this).val();
        afficheLigne(value, valuePeriodeCand);
    });

    jQuery('.periodeCo').on('change',function () {
        valuePeriodeCand = jQuery(this).val();
        connectionGraph(valuePeriodeCand);
    });

    jQuery('.periodeRel').on('change',function () {
        valuePeriodeRel = jQuery(this).val();
        relationGraph(valuePeriodeRel);
    });
    
OffreClick = document.createElement("a");
var text = document.createTextNode("Exporter les données");
OffreClick.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id1');?>&Itemid=0' );
OffreClick.append(text);
document.getElementById("userSummary").append(OffreClick);
document.getElementById("userSummary").append(document.createElement("br"));

contacts = document.createElement("a");
var text = document.createTextNode("Exporter les contacts");
contacts.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id2');?>&Itemid=0' );
contacts.append(text);
document.getElementById("userSummary").append(contacts);


var exportDonnees1 = document.createElement("a");
var text = document.createTextNode("Exporter les données");
exportDonnees1.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id3');?>&Itemid=0' );
exportDonnees1.append(text);
document.getElementById("summeryOffres").append(exportDonnees1);


var exportConnexion = document.createElement("a");
var text = document.createTextNode("Exporter les données");
exportConnexion.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id4');?>&Itemid=0' );
exportConnexion.append(text);
document.getElementById("summaryConnexion").append(exportConnexion);

var exportCand = document.createElement("a");
var text = document.createTextNode("Exporter les données");
exportCand.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id5');?>&Itemid=0' );
exportCand.append(text);
document.getElementById("summeryCandidature").append(exportCand);

var exportCand = document.createElement("a");
var text = document.createTextNode("Exporter les données");
exportCand.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id6');?>&Itemid=0' );
exportCand.append(text);
document.getElementById("summaryRelation").append(exportCand);
    
</script>

<style type='text/css'>

    .span12{
        display: none;
    }

    table {
        border: none;
    }
    table tr {
    }
    table td {
        border: none;
        c
    }

    #selectPeriode {
        margin-left: 33%;
        padding-bottom: 50px; 
    }

    #userSummary p {
        margin-bottom: -2px;
    }

    #summeryOffres p {
        margin-bottom: -2px;
    }

    #summeryCandidature p {
        margin-bottom: -2px;
    }

    #summaryConnexion p {
        margin-bottom: -2px;
    }

    #summaryRelation p {
        margin-bottom: -2px;
    }

</style>

