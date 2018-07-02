<?php
defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');
$document = JFactory::getDocument();
$document->addScript('media'.DS.'com_emundus'.DS.'lib'.DS.'Chart.min.js');
$document->addScript('media'.DS.'com_emundus'.DS.'lib'.DS.'moment.min.js');
$document->addStyleSheet('media'.DS.'com_emundus'.DS.'lib'.DS.'bootstrap-336'.DS.'css'.DS.'bootstrap.min.css');
?>
<div class="container">
    <!-- Shows user info  -->
    <div class="row">

        <div class="col-md-12">
           
            <canvas id="users" ></canvas>

        </div>
    
        <div class="col-md-6" style="padding-left: 10%;" >
            <table id="userTable">
                <tr><td>Type de compte:</td>
                    <td>
                        
                        <select class="compte" id="compte">
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
                            <option value='2'selected>Dernier mois</option>
                            <option value='3'>Trois derniers mois</option>
                            <option value='4'>Six derniers mois</option>
                            <option value='5'>Dernière année</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <div class="col-md-6" id="userSummary"  >
            <?php 

                $count = 0;

                foreach($usersGraph as $ug) {
                    $count += $ug[nombre];
                }
                
                echo "<p><i>Nombre d'inscriptions : </i>$count </p>" ;
                
            ?>
        </div>
        
    </div>

    <hr style='width: 100%; border-top: 5px solid #fff;'>

    <!-- Shows offer info  -->   
    <div class="row">
        <div class="col-md-12" id="offresDiv">
            <canvas id="candLigne"></canvas>
        </div>

        <div class="col-md-6" style="padding-left: 10%;">
            <table>
            <tr><td>Période:</td>
                <td>
                    <select class="periodeCand" >
                        <option value='0'>Dernière semaine</option>
                        <option value='1'>Deux dernières semaines</option>
                        <option value='2'selected>Dernier mois</option>
                        <option value='3'>Trois derniers mois</option>
                        <option value='4'>Six derniers mois</option>
                        <option value='5'>Dernière année</option>
                    </select>
                </td>
            </tr>
            </table>
        </div>
        <div class="col-md-3" >
            <div id="summaryCandidature" >
                <?php 
                    $countCandidature = 0;
                    foreach($candidature as $candidatures) {
                        $countCandidature += $candidatures['nombre'];
                    }
                    echo "<p><i>Nombre de candidature des offres: </i>$countCandidature </p>"; 
                ?>
            </div>
        </div>
            <div class="col-md-3">
                <div id="summaryOffres">
                    <?php 

                        $countConsultation = 0;

                        foreach ($consultationBar as $cb) {
                            $countConsultation += $cb['nombre'];
                        }

                        echo "<p><i>Nombre de consultation des offres: </i>$countConsultation " ;

                    ?>
                </div>
            </div>
        </div>
    </div>

    <hr style='width: 100%; border-top: 5px solid #fff;'>

    <!-- Shows connexion info  -->
    <div class="row">
        <div class="col-md-12">
            <canvas id="co" ></canvas>
        </div>
        
        <div class="col-md-6" style="padding-left: 10%;">
            <table>
            <tr><td> Période: </td>
                <td>
                    <select class="periodeCo" >
                        <option value='0'>Dernière semaine</option>
                        <option value='1'>Deux dernières semaines</option>
                        <option value='2'selected>Dernier mois</option>
                        <option value='3'>Trois derniers mois</option>
                        <option value='4'>Six derniers mois</option>
                        <option value='5'>Dernière année</option>
                    </select>
                </td>
            </tr>
            </table>
        </div>
        <div class="col-md-6" style="padding-left: 10%;">
            <div id='summaryConnexion'>
                <?php 
                    $countConnexion = 0;
                    foreach($connections as $co) {
                        $countConnexion += $co['nombre_connexions'];
                    }             
                    echo "<p><i>Nombre de connexions: </i>$countConnexion " ;
                ?>
            </div>
        </div>
    </div>        

    <hr style='width: 100%; border-top: 21px solid #fff;'>

    <!-- Shows relation info  -->
    <div class="row">

        <div class="col-md-12">
            <canvas id="rel" ></canvas>
        </div>

        <div class="col-md-6" style="padding-left: 10%;">            <table>
            <tr><td> Période: </td>
                <td>
                    <select class="periodeRel" >
                        <option value='0'>Dernière semaine</option>
                        <option value='1'>Deux dernières semaines</option>
                        <option value='2' selected>Dernier mois</option>
                        <option value='3'>Trois derniers mois</option>
                        <option value='4'>Six derniers mois</option>
                        <option value='5'>Dernière année</option>
                    </select>
                </td>
            </tr>
            </table>
        </div>

        <div class="col-md-6" style="padding-left: 10%;">
            <div id='summaryRelation'>
                <?php 
                    $countRelations = 0;
                    foreach ($relations as $rel) {
                        $countRelations += $rel['nombre_rel_etablies'];
                    }
                    echo "<p><i>Nombre de relations etablies: </i>$countRelations " ;
                ?>
            </div>

        </div>

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

    // global options for graphs so it doesn't show decimals
    var options = 
        {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    userCallback: function(label, index, labels) {
                        // when the floored value is the same as the value we have a whole number
                        if (Math.floor(label) === label) {
                            return label;
                        }

                    },
                }
            }],
        };

    // Account function
    function afficheComptes(value,periode) {
        
        var sel = document.getElementById('compte');
        
        var opt = sel.options[sel.selectedIndex];
        
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getprofiletype",
            dataType: 'json',
            data:({
                chosenvalue: value,
                periode: periode
           }),
            success: function (result) {
                if (result.status) {
                    // Loop to get missing dates and create new value (0) for those dates
                    for (var i = 0; i < result.datearray.length; i++) {
                        //make sure we are not checking the last date in the labels array
                        if (i + 1 < result.datearray.length) {
                            var date1 = moment(result.datearray[i], "YYYY-MM-DD");
                            var date2 = moment(result.datearray[i + 1], "YYYY-MM-DD");

                            
                            //if the current date +1 is not the same as it's next neighbor we have to add in a new one
                            if (!date1.add(1, "days").isSame(date2)) {
                                
                                //add the label
                                result.datearray.splice(i + 1, 0, date1.format("YYYY-MM-DD"));
                                //add the data
                                result.countarray.splice(i + 1, 0, 0);
                            }
                        }
                    }

                    var elem = document.getElementById('users');
                    
                    var myLineChart = new Chart(elem, {
                        
                        type: 'line',
                        data: {
                            labels: result.datearray,
                            datasets: [{
                                label: "Nombre de comptes "+opt.text+" créés",
                                data: result.countarray,
                                borderColor: 'rgba(0, 99, 132, 0.6)'
                            }]
                        },
                        options: {
                            title:{
                                display: true,
                                text: "Création de comptes",
                                fontSize: 20
                            },
                            scales: options
                        }
                        
                    });
                   
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        }); 
    }

    // Account function uses 2 ajax functions, consultation and candidate
    function afficheOffres(periode) {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getcandidatures",
            dataType: 'json',
            data:({
                periode: periode
            }),
            success: function (resultCand) {
                
                if(resultCand.status) {
                    jQuery.ajax({
                        type: "post",
                        url: "index.php?option=com_emundus&controller=stats&task=getconsultation",
                        dataType: 'json',
                        data:({
                            periode: periode
                        }),
                        success: function (resultCon) {
                            var ctxLine = document.getElementById('candLigne').getContext('2d');
                            // destroy old canvas causing hover problems
                            if(window.bar != undefined)
                                window.bar.destroy();
                            window.bar = new Chart(ctxLine, {
                                type: 'horizontalBar',
                                data: {
                                    labels: resultCon.titre,
                                    datasets: [
                                        {
                                            label: "Consultations",
                                            data: resultCon.countarray,
                                            backgroundColor: "#3e95cd",
                                        },
                                        {
                                            label: "Mise en relation",
                                            data: resultCand.nbarray,
                                            backgroundColor: "#8e5ea2",
                                        },

                                    ]
                                },
                                options: {
                                    title:{
                                        display: true,
                                        text: "Offres",
                                        fontSize: 20
                                    },
                                    scales: {
                                        xAxes: [{
                                            ticks: {
                                                min: 0,
                                                beginAtZero: true,
                                                userCallback: function(label, index, labels) {
                                                    // when the floored value is the same as the value we have a whole number
                                                    if (Math.floor(label) === label) {
                                                        return label;
                                                    }
                                                },    
                                            }
                                        }]
                                    }
                                } 
                            });
                            
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                          console.log(jqXHR.responseText);
                        }
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        }); 
    }

    // Connection Function
    function afficheConnections(periode) {
        
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getconnections",
            dataType: 'json',
            data:({periode: periode}),
            success: function (result) {
                if (result.status) {
                    // Loop to get missing dates and create new value (0) for those dates
                    for (var i = 0; i < result.datearray.length; i++) {
                        //make sure we are not checking the last date in the labels array
                        if (i + 1 < result.datearray.length) {
                            var date1 = moment(result.datearray[i], "YYYY-MM-DD");
                            var date2 = moment(result.datearray[i + 1], "YYYY-MM-DD");

                            
                            //if the current date +1 is not the same as it's next neighbor we have to add in a new one
                            if (!date1.add(1, "days").isSame(date2)) {
                                
                                //add the label
                                result.datearray.splice(i + 1, 0, date1.format("YYYY-MM-DD"));
                                //add the data
                                result.countarray.splice(i + 1, 0, 0);
                            }
                        }
                    }
                    var elem = document.getElementById('co');
                    var myLineChart = new Chart(elem, {
                        type: 'line',
                        data: {
                            labels: result.datearray,
                            datasets: [{
                                label: "Nombre de connections effectuées",
                                data: result.countarray,
                                borderColor: 'rgb(89, 90, 109)'
                            }]
                        },
                        options: {
                            title:{
                                display: true,
                                text: "Nombre de connexions",
                                fontSize: 20
                            },
                            scales: options
                        } 
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    // Relation function 
    function afficherelations(periode) {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getrelations",
            dataType: 'json',
            data:({periode: periode}),
            success: function (result) {
                if (result.status) {
                    // Loop to get missing dates and create new value (0) for those dates
                    for (var i = 0; i < result.datearray.length; i++) {
                        //make sure we are not checking the last date in the labels array
                        if (i + 1 < result.datearray.length) {
                            var date1 = moment(result.datearray[i], "YYYY-MM-DD");
                            var date2 = moment(result.datearray[i + 1], "YYYY-MM-DD");

                            
                            //if the current date +1 is not the same as it's next neighbor we have to add in a new one
                            if (!date1.add(1, "days").isSame(date2)) {
                                
                                //add the label
                                result.datearray.splice(i + 1, 0, date1.format("YYYY-MM-DD"));
                                //add the data
                                result.countarray.splice(i + 1, 0, 0);
                            }
                        }
                    }
                    var elem = document.getElementById('rel');
                    var myLineChart = new Chart(elem, {
                        type: 'line',
                        data: {
                            labels: result.datearray,
                            datasets: [{
                                label: "Nombre de relations établies",
                                data: result.countarray,
                                borderColor: 'rgb(232, 128, 32)'
                            }]
                        },
                        options: {
                            title:{
                                display: true,
                                text: "Relations établies",
                                fontSize: 20
                            },
                            scales: options
                        }   
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        }); 
    }
    
    jQuery(document).ready(function () {
        var OffreClick = document.createElement("a");
        var text = document.createTextNode("Exporter les données");
        OffreClick.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id1');?>&Itemid=0' );
        OffreClick.append(text);
        document.getElementById("userSummary").append(OffreClick);
        document.getElementById("userSummary").append(document.createElement("br"));

        var contacts = document.createElement("a");
        text = document.createTextNode("Exporter les contacts");
        contacts.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id2');?>&Itemid=0' );
        contacts.append(text);
        document.getElementById("userSummary").append(contacts);


        var exportDonnees1 = document.createElement("a");
        text = document.createTextNode("Exporter les données");
        exportDonnees1.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id3');?>&Itemid=0' );
        exportDonnees1.append(text);
        document.getElementById("summaryOffres").append(exportDonnees1);


        var exportConnexion = document.createElement("a");
        text = document.createTextNode("Exporter les données");
        exportConnexion.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id4');?>&Itemid=0' );
        exportConnexion.append(text);
        document.getElementById("summaryConnexion").append(exportConnexion);

        var exportCand = document.createElement("a");
        text = document.createTextNode("Exporter les données");
        exportCand.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id5');?>&Itemid=0' );
        exportCand.append(text);
        document.getElementById("summaryCandidature").append(exportCand);

        exportCand = document.createElement("a");
        text = document.createTextNode("Exporter les données");
        exportCand.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id6');?>&Itemid=0' );
        exportCand.append(text);
        document.getElementById("summaryRelation").append(exportCand);

        var valuePeriodecompte = jQuery('.periodeCompte').val();
        var valueTimeLine = jQuery('.compte').val();
        afficheComptes(valueTimeLine, valuePeriodecompte);
        
        var valuePeriodeCo = jQuery('.periodeCo').val();
        afficheConnections(valuePeriodeCo);

        var valuePeriodeCand = jQuery('.periodeCand').val();
        
        //var valueCand = jQuery('.candidature').val();
        afficheOffres(valuePeriodeCand);

        var valuePeriodeRel = jQuery('.periodeRel').val();
        afficherelations(valuePeriodeRel);
    });

    jQuery('.compte').on('change', function () {
        var value = jQuery(this).val();
        var valuePeriodecompte = jQuery('.periodeCompte').val();
        
        afficheComptes(value, valuePeriodecompte);
    });

    jQuery('.periodeCompte').on('change', function () {
        
        var value = jQuery('.compte').val();
        var valuePeriodecompte = jQuery(this).val();
        afficheComptes(value, valuePeriodecompte);
    });

    jQuery('.periodeCand').on('change', function () {
        var valuePeriodeCand = jQuery(this).val();
        afficheOffres(valuePeriodeCand);
    });

    jQuery('.periodeCo').on('change',function () {
        var valuePeriodeCand = jQuery(this).val();
        afficheConnections(valuePeriodeCand);
    });

    jQuery('.periodeRel').on('change',function () {
        var valuePeriodeRel = jQuery(this).val();
        afficherelations(valuePeriodeRel);
    });
    
</script>

<style type='text/css'>

    .span12 {
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

    #summaryOffres p {
        margin-bottom: -2px;
    }

    #summaryCandidature p {
        margin-bottom: -2px;
    }

    #summaryConnexion p {
        margin-bottom: -2px;
    }

    #summaryRelation p {
        margin-bottom: -2px;
    }

</style>

