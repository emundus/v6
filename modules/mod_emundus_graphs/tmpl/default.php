<?php
defined('_JEXEC') or die;
header('Content-Type: text/html; charset=utf-8');
$document = JFactory::getDocument();
//Chart.js is the libary used for this module's graphs
$document->addScript('media'.DS.'com_emundus'.DS.'lib'.DS.'Chart.min.js');
//moment.js is a Date libary, using to retrieve missing dates
$document->addScript('media'.DS.'com_emundus'.DS.'lib'.DS.'moment.min.js');
?>
<div class="container">

    <div class="explination">
        <?php echo JText::_('MOD_EMUNDUS_GRAPHS_INTRO'); ?>
    </div>


    <!-- Shows connexion info  -->
    <div class="row" id="connectionRow" style="display:none;">
        <div class="col-md-12">
            <canvas id="co" ></canvas>
        </div>

        <div class="col-md-6" style="padding-left: 10%;">
            <table>
                <tr><td><?php echo JText::_("PERIODE"); ?></td>
                    <td>
                        <select class="periodeCo" >
                            <option value='0'><?php echo JText::_("PERIODE_1_WEEK"); ?></option>
                            <option value='1'><?php echo JText::_("PERIODE_2_WEEK"); ?></option>
                            <option value='2' selected><?php echo JText::_("PERIODE_1_MONTH"); ?></option>
                            <option value='3'><?php echo JText::_("PERIODE_3_MONTH"); ?></option>
                            <option value='4'><?php echo JText::_("PERIODE_6_MONTH"); ?></option>
                            <option value='5'><?php echo JText::_("PERIODE_1_YEAR"); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6" style="padding-left: 10%;">
            <div id='summaryConnexion'>
                <p id='countConnexion'><i><?php echo JText::_("CONNEXION_TOTAL"); ?></i></p>
            </div>
        </div>

    </div>

    <!-- Shows user info  -->
    <div class="row" id="userRow" style="display:none;">

        <div class="col-md-12">
            <canvas id="users"></canvas>
        </div>

        <div class="col-md-6" style="padding-left: 10%;" >
            <table id="userTable">
                <tr><td><?php echo JText::_("USER_TYPE"); ?></td>
                    <td>

                        <select class="compte" id="compte">
                            <?php
                            echo $distinctProfile;
                            ?>
                        </select>

                    </td>
                </tr>

                <tr><td> <?php echo JText::_("PERIODE"); ?> </td>
                    <td>
                        <select class="periodeCompte" >
                            <option value='0'><?php echo JText::_("PERIODE_1_WEEK"); ?></option>
                            <option value='1'><?php echo JText::_("PERIODE_2_WEEK"); ?></option>
                            <option value='2' selected><?php echo JText::_("PERIODE_1_MONTH"); ?></option>
                            <option value='3'><?php echo JText::_("PERIODE_3_MONTH"); ?></option>
                            <option value='4'><?php echo JText::_("PERIODE_6_MONTH"); ?></option>
                            <option value='5'><?php echo JText::_("PERIODE_1_YEAR"); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <div class="col-md-6" id="userSummary"  >
            <p id='userCount'><i><?php echo JText::_("USER_GRAPH_TOTAL");?></i></p>
        </div>
        <div class="col-md-12">
            <hr style='width: 100%; border-top: 5px solid #fff;'>
        </div>

    </div>






    <!-- Shows offer info  -->
    <div class="row" id="offerRow" style="display:none;">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12" id="offresDiv">
                    <canvas id="candLigne"></canvas>
                </div>

            </div>

            <div class="row">
                <div class="col-md-12" style="padding-left: 10%;">
                    <table>
                        <tr><td><?php echo JText::_("PERIODE"); ?></td>
                            <td>
                                <select class="periodeCand" >
                                    <option value='0'><?php echo JText::_("PERIODE_1_WEEK"); ?></option>
                                    <option value='1'><?php echo JText::_("PERIODE_2_WEEK"); ?></option>
                                    <option value='2' selected><?php echo JText::_("PERIODE_1_MONTH"); ?></option>
                                    <option value='3'><?php echo JText::_("PERIODE_3_MONTH"); ?></option>
                                    <option value='4'><?php echo JText::_("PERIODE_6_MONTH"); ?></option>
                                    <option value='5'><?php echo JText::_("PERIODE_1_YEAR"); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div id="summaryOffres">
                        <p id='countConsultation'><i><?php echo JText::_("CONSULT_TOTAL"); ?></i></p>
                    </div>
                </div>

                <div class="col-md-6" >
                    <div id="summaryCandidature" >
                        <p id='countCandidature'><i><?php echo JText::_("CANDIDATE_TOTAL"); ?></i></p>
                    </div>
                </div>
            </div>



        </div>

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <canvas id="projects"  height="400"></canvas>
                </div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12" id="summaryProjects">
                </div>
            </div>

        </div>
    </div>




</div>

<!-- Shows gender info  -->
<div class="row" id="genderRow" style="display:none;">

    <div class="col-md-12">
        <canvas id="gender" ></canvas>
    </div>

    <div class="col-md-6" style="padding-left: 10%;">
        <div id='summaryGender'></div>
    </div>
    <div class="col-md-12">
        <hr style='width: 100%; border-top: 5px solid #fff;'>
    </div>
</div>

<!-- Shows nationality info  -->
<div class="row" id="nationRow" style="display:none;">

    <div class="col-md-12">
        <canvas id="nationality" ></canvas>
    </div>

    <div class="col-md-6" style="padding-left: 10%;">
        <div id='summaryNationality'></div>
    </div>
    <div class="col-md-12">
        <hr style='width: 100%; border-top: 5px solid #fff;'>
    </div>
</div>

<!-- Shows age info  -->
<div class="row" id="ageRow" style="display:none;">

    <div class="col-md-12">
        <canvas id="age" ></canvas>
    </div>

    <div class="col-md-6" style="padding-left: 10%;">
        <div id='summaryAge'></div>
    </div>
    <div class="col-md-12">
        <hr style='width: 100%; border-top: 5px solid #fff;'>
    </div>
</div>

<!-- Shows files info  -->
<div class="row" id="filesRow" style="display:none;">

    <div class="col-md-12">
        <canvas id="files" ></canvas>
    </div>

    <div class="col-md-6" style="padding-left: 10%;">
        <div id='summaryFiles'></div>
    </div>
    <div class="col-md-12">
        <hr style='width: 100%; border-top: 5px solid #fff;'>
    </div>
</div>

<!-- Additional views  -->
<div class="row">
    <div class="col-md-4 col-centered" >
        <table id="viewTable">
            <tr><th> Autres Statistiques Possibles</th><th></th></tr>
            <?php echo $tableField; ?>
        </table>
    </div>
</div>

</div>

<script type="text/javascript">
    var compteChart;
    var offreChart;
    var connexionChart;
    var relationChart;
    var genderChart;
    var nationChart;
    var ageChart;
    var filesChart;
    var projectChart;
    // global options for graphs so it doesn't show decimals
    var options = {
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
    // Get total accounts created by user
    /*
    function countType(value) {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=countuser",
            dataType: 'json',
            data:({
                value: value
           }),
           success: function(result) {
                if(document.getElementById("userCount").childNodes.length > 1)
                    document.getElementById("userCount").childNodes[1].remove();
                document.getElementById("userCount").append(result.count);
           },
           error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }
    */
    function setColorGradient(num_steps) {

        var colorArray = [];
        // colors needs to be in rgb(red, green, blue)
        var start_red = 185;
        var start_green = 43;
        var start_blue = 39;

        var end_red = 21;
        var end_green = 101;
        var end_blue = 192;

        var current_red = start_red;
        var current_green = start_green;
        var current_blue = start_blue;
        var red_diff = end_red - start_red;
        var green_diff = end_green - start_green;
        var blue_diff = end_blue - start_blue;

        var red_step = (red_diff/num_steps) ;
        var green_step = (green_diff/num_steps);
        var blue_step = (blue_diff/num_steps);
        while (current_red > end_red && current_green < end_green && current_blue < end_blue) {
            current_red += red_step;
            current_green += green_step;
            current_blue += blue_step;
            colorArray.push('rgb(' + parseInt(current_red) + ',' + parseInt(current_green) + ',' + parseInt(current_blue) + ')');
        }

        return colorArray;
    }

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
            success: function(result) {
                if (result.status) {
                    if (document.getElementById("userCount").childNodes.length > 1)
                        document.getElementById("userCount").childNodes[1].remove();
                    document.getElementById("userCount").append(result.count);
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
                    if (compteChart != undefined || compteChart != null)
                        compteChart.destroy();
                    var elem = document.getElementById('users');
                    elem.height = 400;

                    compteChart = new Chart(elem, {
                        type: 'line',
                        data: {
                            labels: result.datearray,
                            datasets: [{
                                label: "<?php echo JText::_("USER_GRAPH_LABEL"); ?>",
                                data: result.countarray,
                                borderColor: 'rgba(0, 99, 132, 0.6)'
                            }]
                        },
                        options: {
                            title:{
                                display: true,
                                text: "<?php echo JText::_("USER_GRAPH_TITLE"); ?>",
                                fontSize: 20
                            },
                            elements: { point: { radius: 1 } } ,
                            scales: options,
                            maintainAspectRatio: false
                        }
                    });
                }
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }

    <?php if (isset($projects)) :?>
    // Project function
    function  afficheProjets() {
        if (projectChart != undefined || projectChart != null)
            projectChart.destroy();
        var elem = document.getElementById('projects');
        projectChart = new Chart(elem, {
            type: 'bar',
            data: {
                labels: ['Future Doctorant', 'Chercheur', 'Acteur public ou associé'],
                datasets: [
                    // Future Doctorant
                    {
                        label: 'Mises en relation avec des chercheurs acceptées',
                        data: [<?php echo $projects["future_to_chercheur_accept"];?>,0,0],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        stack: 1
                    },
                    {
                        label: 'Totalité des mises en relation avec des chercheurs',
                        data: [<?php echo $projects["future_to_chercheur_total"];?>,0,0],
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        stack: 1
                    },
                    {
                        label: "Totalité des mises en relation avec des acteurs public",
                        backgroundColor: "rgba(66, 244, 235,0.2)",
                        data: [<?php echo $projects["future_to_public_total"];?>, 0, 0],
                        stack: 2
                    },
                    {
                        label: "Mises en relation avec des acteurs public acceptées",
                        backgroundColor: "rgba(65, 98, 244,0.2)",
                        data: [<?php echo $projects["future_to_public_accept"];?>, 0, 0],
                        stack: 2
                    },
                    // Chercheurs
                    {
                        label: 'Mises en relation avec des Futurs Doctorants acceptées',
                        data: [0,<?php echo $projects["chercheur_to_future_accept"];?>,0],
                        backgroundColor: 'rgba(226, 138, 138, 0.2)',
                        borderWidth: 1,
                        stack: 1
                    },
                    {
                        label: 'Totalité des mises en relation avec des Futurs Doctorants',
                        data: [0,<?php echo $projects["chercheur_to_future_total"];?>,0],
                        backgroundColor: 'rgba(255, 0, 0, 0.2)',
                        stack: 1
                    },
                    {
                        label: "Totalité des mises en relation avec des acteurs public",
                        backgroundColor: "rgba(99,255,132,0.2)",
                        data: [0, <?php echo $projects["chercheur_to_public_total"];?>, 0],
                        stack: 2
                    },
                    {
                        label: "Mise en relation avec des acteurs public acceptées",
                        backgroundColor: "rgba(201, 0, 232,0.2)",
                        data: [0, <?php echo $projects["chercheur_to_public_accept"];?>, 0],
                        stack: 2
                    },
                    // Acteurs public
                    {
                        label: 'Nombre de demande de mise en relation par des Future Doctorants acceptés',
                        data: [0,0,<?php echo $projects["public_to_future_accept"];?>],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 1,
                        stack: 1
                    },
                    {
                        label: 'Nombre de demande de mise en relation par des Future Doctorants',
                        data: [0,0,<?php echo $projects["public_to_future_total"];?>],
                        backgroundColor: "rgba(99,255,132,0.2)",
                        stack: 1
                    },
                    {
                        label: "Nombre de demande de mise en relation par des chercheurs",
                        backgroundColor: "rgba(99,255,132,0.2)",
                        data: [0, 0, <?php echo $projects["public_to_chercheur_total"];?>],
                        stack: 2
                    },
                    {
                        label: "Nombre de demande de mise en relation par des chercheurs acceptés",
                        backgroundColor: "rgba(252, 248, 0,0.2)",
                        data: [0, 0, <?php echo $projects["public_to_chercheur_accept"];?>],
                        stack: 2
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                title:{
                    display: true,
                    text: "<?php echo JText::_("PROJECT_TITLE"); ?>",
                    fontSize: 20
                },
                scales: {
                    xAxes: [{
                        stacked: true,
                    }],
                    yAxes: [{
                        id: "bar-y-axis1",
                        stacked: false,
                        ticks: {
                            beginAtZero: true,
                            userCallback: function(label, index, labels) {
                                // when the floored value is the same as the value we have a whole number
                                if (Math.floor(label) === label) {
                                    return label;
                                }
                            }
                        }
                    }]
                }
            }
        });
        elem.height = 400;
    }
    <?php endif; ?>


    // Account function uses 2 ajax functions, consultation and candidate
    function afficheOffres(periode) {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getcandidatures",
            dataType: 'json',
            data:({
                periode: periode
            }),
            success: function(resultCand) {
                if (resultCand.status) {
                    jQuery.ajax({
                        type: "post",
                        url: "index.php?option=com_emundus&controller=stats&task=getoffres",
                        dataType: 'json',
                        data:({
                            periode: periode
                        }),
                        success: function(resultOffre) {
                            if (document.getElementById("countCandidature").childNodes.length > 1)
                                document.getElementById("countCandidature").childNodes[1].remove();
                            document.getElementById("countCandidature").append(resultCand.count);
                            if (document.getElementById("countConsultation").childNodes.length > 1)
                                document.getElementById("countConsultation").childNodes[1].remove();
                            document.getElementById("countConsultation").append(resultOffre.countOffre);
                            var ctxLine = document.getElementById('candLigne');
                            ctxLine.height = 400;
                            // destroy old canvas causing hover problems
                            if (offreChart != undefined || offreChart != null)
                                offreChart.destroy();
                            offreChart = new Chart(ctxLine, {
                                type: 'horizontalBar',
                                data: {

                                    datasets: [
                                        {
                                            label: "Nombre d'offres",
                                            data: [resultOffre.countOffre],
                                            backgroundColor: "#3e95cd",
                                        },
                                        {
                                            label: "Mise en relation",
                                            data: [resultCand.count],
                                            backgroundColor: "#8e5ea2",
                                        },
                                    ]
                                },
                                options: {
                                    maintainAspectRatio: false,
                                    title:{
                                        display: true,
                                        text: "<?php echo JText::_('OFFER_TITLE'); ?>",
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
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
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
            data:({ periode: periode }),
            success: function(result) {
                if (result.status) {

                    if (document.getElementById("countConnexion").childNodes.length > 1)
                        document.getElementById("countConnexion").childNodes[1].remove();
                    document.getElementById("countConnexion").append(result.count);
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
                    if (connexionChart != undefined || connexionChart != null)
                        connexionChart.destroy();
                    var elem = document.getElementById('co');
                    elem.height = 400;
                    connexionChart = new Chart(elem, {
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
                            maintainAspectRatio: false,
                            title:{
                                display: true,
                                text: "<?php echo JText::_("CONNECTION_TITLE") ;?>",
                                fontSize: 20
                            },
                            elements: { point: { radius: 1 } } ,
                            scales: options
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }

    function afficheGenre() {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getgender",
            dataType: 'json',
            success: function(result) {
                if (result.status) {
                    if (genderChart != undefined || genderChart != null)
                        genderChart.destroy();
                    var elem = document.getElementById('gender');
                    elem.height = 400;
                    genderChart = new Chart(elem, {
                        type: 'pie',
                        data: {
                            datasets: [{
                                data: [result.male, result.female],
                                backgroundColor: ["#3e95cd", "#8e5ea2"]
                            }],
                            // These labels appear in the legend and in the tooltips when hovering different arcs
                            labels: [
                                '<?php echo JText::_('MOD_EMUNDUS_GRAPHS_MALE'); ?>',
                                '<?php echo JText::_('MOD_EMUNDUS_GRAPHS_FEMALE'); ?>'
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            title:{
                                display: true,
                                text: "<?php echo JText::_('MOD_EMUNDUS_GRAPHS_GENDERS'); ?>",
                                fontSize: 20
                            }
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }
    function afficheNationality() {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getnationality",
            dataType: 'json',
            success: function(result) {
                if (result.status) {
                    if (nationChart != undefined || nationChart != null)
                        nationChart.destroy();
                    var colorArray = setColorGradient(result.nationality.length);
                    var elem = document.getElementById('nationality');
                    elem.height = 400;
                    nationChart = new Chart(elem, {
                        type: 'bar',
                        data: {
                            labels: result.nationality,
                            datasets: [{
                                data: result.nb,
                                backgroundColor: colorArray
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            legend: {
                                display: false,
                            },
                            title:{
                                display: true,
                                text: "<?php echo JText::_('MOD_EMUNDUS_GRAPHS_NATIONALITIES'); ?>",
                                fontSize: 20
                            },
                            scales: options
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }
    function afficheAge() {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getage",
            dataType: 'json',
            success: function(result) {
                if (result.status) {
                    if (ageChart != undefined || ageChart != null)
                        ageChart.destroy();
                    var colorArray = setColorGradient(result.campaign.length);
                    var elem = document.getElementById('age');
                    elem.height = 400;
                    ageChart = new Chart(elem, {
                        type: 'bar',
                        data: {
                            labels: result.campaign,
                            datasets: [{
                                data: result.age,
                                backgroundColor: colorArray
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            legend: {
                                display: false,
                            },
                            title:{
                                display: true,
                                text: "<?php echo JText::_('MOD_EMUNDUS_GRAPHS_AGES'); ?>",
                                fontSize: 20
                            },
                            scales: options
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }
    function afficheFiles() {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=getfiles",
            dataType: 'json',
            success: function(result) {
                var nbArray = [];
                var valArray = [];
                if (filesChart != undefined || filesChart != null)
                    filesChart.destroy();
                for (var i in result.val) {
                    valArray.push(i);
                    nbArray.push(result.val[i]);
                }
                var colorArray = setColorGradient(nbArray.length);
                var elem = document.getElementById('files');
                elem.height = 400;
                filesChart = new Chart(elem, {
                    type: 'pie',
                    data: {
                        datasets: [{
                            data: nbArray,
                            backgroundColor: colorArray
                        }],
                        // These labels appear in the legend and in the tooltips when hovering different arcs
                        labels: valArray
                    },
                    options: {
                        maintainAspectRatio: false,
                        title: {
                            display: true,
                            text: "<?php echo JText::_('MOD_EMUNDUS_GRAPHS_FILES'); ?>",
                            fontSize: 20
                        }
                    }
                });
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
    }
    //// AddView Function uses 2 AJAXs
    // Fist AJAX Creates the view if possible
    function addView(view) {
        jQuery.ajax({
            type: "post",
            url: "index.php?option=com_emundus&controller=stats&task=addview&format=raw",
            dataType: 'json',
            data: ({ view: view }),
            success: function(result) {
                if (result.status) {
                    jQuery.ajax({
                        type: "post",
                        url: "index.php?option=com_emundus&controller=stats&task=linkfabrik&format=raw",
                        dataType: 'json',
                        data:({
                            view: view,
                            listid: result.listid
                        }),
                        success: function(res) {
                            if (res.status) {
                                location.reload();
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            alert("Impossible de relier à Fabrik");
                            console.log(jqXHR.responseText);
                        }
                    })
                } else {
                    alert("Vous n'avez pas la table ou les colonnes pour créer ce graphe.");
                    var nono = document.createElement("i");
                    nono.className = "fas fa-times";
                    document.getElementById(view).prepend(nono);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        })
    }
    jQuery(document).ready(function() {
        var projectButton = document.createElement("div");
        projectButton.className = "btn";
        var ProjectIcon = document.createElement("i");
        ProjectIcon.className ="search icon";
        projectButton.append(ProjectIcon);
        var ProjectClick = document.createElement("a");
        var text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID10"); ?>");
        ProjectClick.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id10');?>&Itemid=0' );
        ProjectClick.append(text);
        projectButton.append(ProjectClick);
        document.getElementById("summaryProjects").append(projectButton);
        document.getElementById("summaryProjects").append(document.createElement("br"));

        <?php if (isset($projects)) :?>
            afficheProjets();
        <?php endif; ?>

        jQuery('#viewTable').each(function() {
            if (jQuery(this).find('tr').children("td").length < 2) {
                jQuery(this).hide();
            }
        });

        if (<?php echo $nationality; ?>) {
            document.getElementById("nationRow").setAttribute("style", "display:block;");
            var button = document.createElement("div");
            button.className = "btn";
            var icon = document.createElement("i");
            icon.className = "search icon";
            button.append(icon);
            var OffreClick = document.createElement("a");
            var text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID7"); ?>");
            OffreClick.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id7');?>&Itemid=0' );
            OffreClick.append(text);
            button.append(OffreClick);
            document.getElementById("summaryNationality").append(button);
            document.getElementById("summaryNationality").append(document.createElement("br"));
            afficheNationality();
        }
        if (<?php echo $gender; ?>) {
            document.getElementById("genderRow").setAttribute("style", "display:block;");
            var button = document.createElement("div");
            button.className = "btn";
            var icon = document.createElement("i");
            icon.className = "search icon";
            button.append(icon);
            var OffreClick = document.createElement("a");
            var text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID9"); ?>");
            OffreClick.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id9');?>&Itemid=0' );
            OffreClick.append(text);
            button.append(OffreClick);
            document.getElementById("summaryGender").append(button);
            document.getElementById("summaryGender").append(document.createElement("br"));
            afficheGenre();
        }
        if (<?php echo $age; ?>) {
            document.getElementById("ageRow").setAttribute("style", "display:block;");
            var button = document.createElement("div");
            button.className = "btn";
            var icon = document.createElement("i");
            icon.className = "search icon";
            button.append(icon);
            var OffreClick = document.createElement("a");
            var text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID10"); ?>");
            OffreClick.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id10');?>&Itemid=0' );
            OffreClick.append(text);
            button.append(OffreClick);
            document.getElementById("summaryAge").append(button);
            document.getElementById("summaryAge").append(document.createElement("br"));
            afficheAge();
        }
        if (<?php echo $files; ?>) {
            document.getElementById("filesRow").setAttribute("style", "display:block;");
            var button = document.createElement("div");
            button.className = "btn";
            var icon = document.createElement("i");
            icon.className = "search icon";
            button.append(icon);
            var OffreClick = document.createElement("a");
            var text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID8"); ?>");
            OffreClick.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id8');?>&Itemid=0' );
            OffreClick.append(text);
            button.append(OffreClick);
            document.getElementById("summaryFiles").append(button);
            document.getElementById("summaryFiles").append(document.createElement("br"));
            afficheFiles();
        }
        if (<?php echo $comptes; ?>) {
            // create button to export user data
            document.getElementById("userRow").setAttribute("style", "display:block;");
            var button = document.createElement("div");
            button.className = "btn";
            var icon = document.createElement("i");
            icon.className = "search icon";
            button.append(icon);
            var OffreClick = document.createElement("a");
            var text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID1"); ?>");
            OffreClick.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id1');?>&Itemid=0' );
            OffreClick.append(text);
            button.append(OffreClick);
            document.getElementById("userSummary").append(button);
            document.getElementById("userSummary").append(document.createElement("br"));
            // create button to see contacts
            var buttonC = document.createElement("div");
            buttonC.className = "btn";
            var iconC = document.createElement("i");
            iconC.className = "search icon";
            buttonC.append(iconC);
            var contacts = document.createElement("a");
            text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID2"); ?>");
            contacts.setAttribute('href', '<?php echo $user_url; ?>' );
            contacts.append(text);
            buttonC.append(contacts);
            document.getElementById("userSummary").append(buttonC);
            var valuePeriodecompte = jQuery('.periodeCompte').val();
            var value = jQuery('.compte').val();
            afficheComptes(value, valuePeriodecompte);
        }
        if (<?php echo $consult; ?>  && <?php echo $cand; ?>) {
            document.getElementById("offerRow").setAttribute("style", "display:block;");
            var buttonCon = document.createElement("div");
            buttonCon.className = "btn";
            var buttonCand = document.createElement("div");
            buttonCand.className = "btn";
            var icon1 = document.createElement("i");
            icon1.className = "search icon";
            var icon2 = document.createElement("i");
            icon2.className = "search icon";
            buttonCand.append(icon1);
            buttonCon.append(icon2);
            var exportDonnees1 = document.createElement("a");
            text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID3"); ?>");
            exportDonnees1.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id3');?>&Itemid=0' );
            exportDonnees1.append(text);
            buttonCon.append(exportDonnees1);
            document.getElementById("summaryOffres").append(buttonCon);
            var exportCand = document.createElement("a");
            text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID5"); ?>");
            exportCand.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id5');?>&Itemid=0' );
            exportCand.append(text);
            buttonCand.append(exportCand);
            document.getElementById("summaryCandidature").append(buttonCand);
            var valuePeriodeCand = jQuery('.periodeCand').val();
            afficheOffres(valuePeriodeCand);
        }
        if (<?php echo $con; ?>) {
            document.getElementById("connectionRow").setAttribute("style", "display:block;");
            var button = document.createElement("div");
            button.className = "btn";
            var icon = document.createElement("i");
            icon.className ="search icon";
            button.append(icon);
            var exportConnexion = document.createElement("a");
            text = document.createTextNode("<?php echo JText::_("MOD_EM_LIST_ID4"); ?>");
            exportConnexion.setAttribute('href', 'index.php?option=com_fabrik&task=list.view&listid=<?php echo $params->get('mod_em_list_id4');?>&Itemid=0' );
            exportConnexion.append(text);
            button.append(exportConnexion);
            document.getElementById("summaryConnexion").append(button);
            var valuePeriodeCo = jQuery('.periodeCo').val();
            afficheConnections(valuePeriodeCo);
        }

    });

    jQuery('.compte').on('change', function() {
        var value = jQuery(this).val();
        var valuePeriodecompte = jQuery('.periodeCompte').val();
        afficheComptes(value, valuePeriodecompte);
    });
    jQuery('.periodeCompte').on('change', function() {
        var value = jQuery('.compte').val();
        var valuePeriodecompte = jQuery(this).val();
        afficheComptes(value, valuePeriodecompte);
    });
    jQuery('.periodeCand').on('change', function() {
        var valuePeriodeCand = jQuery(this).val();
        afficheOffres(valuePeriodeCand);
    });
    jQuery('.periodeCo').on('change',function() {
        var valuePeriodeCand = jQuery(this).val();
        afficheConnections(valuePeriodeCand);
    });

</script>

<style type='text/css'>
    .span12 {
        display: none;
    }
    .explination {
        text-align: center;
        background-color: rgb(233, 233, 233);
        border-radius: 15px 15px 15px 15px;
        -moz-border-radius: 15px 15px 15px 15px;
        -webkit-border-radius: 15px 15px 15px 15px;
        border: 0px solid #000000;
        padding-bottom: 10px;
        margin-bottom: 30px;
    }
    table {
        border: none;
    }

    table td {
        border: none;
    }
    #selectPeriode {
        margin-left: 33%;
        padding-bottom: 50px;
    }
    #userSummary p {
        margin-bottom: 0px;
    }
    #summaryOffres p {
        margin-bottom: 0px;
    }
    #summaryCandidature p {
        margin-bottom: 0px;
    }
    #summaryConnexion p {
        margin-bottom: 0px;
    }
    #summaryRelation p {
        margin-bottom: 0px;
    }
    #projects {
        height: 400px !important;
    }
    #connectionRow {
        margin-bottom: 35px;
    }
    #userRow {
        margin-bottom: 35px;
    }
    #summaryCandidature {
        margin-left: 10px;
    }
    #summaryProjects {
        margin-top: 20px;
        margin-left: 30%;
    }
</style>