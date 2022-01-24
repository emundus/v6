<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/css/emundus_export_select_columns.css" );
$eMConfig = JComponentHelper::getParams('com_emundus');
$current_user = JFactory::getUser();
$view = JRequest::getVar('v', null, 'GET', 'none',0);
$comments = JRequest::getVar('comments', null, 'POST', 'none', 0);
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);
// Starting a session.
$session = JFactory::getSession();
$s_elements = $session->get('s_elements');
$comments = $session->get('comments');
?>

    <h1><?= JText::_('EM_TAGS_PAGE_TITLE'); ?></h1>

    <div id="em-select-program">
        <h2><?= JText::_('SELECT_PROG_DESC');?></h2>
        <select id="program" class="form-control" onchange="programSelect();">
            <option value=""><?= JText::_('PROGRAM_SELECT'); ?></option>
            <?php foreach ($this->programs as $program) :?>
                <option value="<?= $program["code"]; ?>"><?= $program["label"]; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="program-categories" class="hide">
        <hr>
        <div id="program-categories_desc">
            <h2><?= JText::_('SELECT_CAT_DESC'); ?></h2>
        </div>

        <div id="program-categories-group">
            <div id="em-select-campaign">
                <select id="campaign" class="form-control" onchange="showAll()">
                    <option value=""><?= JText::_('CAMPAIGN_SELECT'); ?></option>
                </select>
            </div>

            <div id="em-select-evaluation">
                <button class="btn btn-primary" onclick="showEval();"><?= JText::_('EVALUATION_SELECT'); ?></button>
            </div>

            <div id="em-select-decision">
                <button class="btn btn-primary" onclick="showDecision();"><?= JText::_('DECISION_SELECT'); ?></button>
            </div>

            <div id="em-select-admission">
                <button class="btn btn-primary" onclick="showAdmission();"><?= JText::_('ADMISSION_SELECT'); ?></button>
            </div>

            <div id="em-select-other">
                <button class="btn btn-primary" onclick="showOther();"><?= JText::_('OTHER_TAG_SELECT'); ?></button>
            </div>
        </div>

    </div>

    <div id="result"></div>

    <div id="other-result" class="hide">
        <hr>
        <div class="em-program-title">
            <h1><?= JText::_('TAG_TABLE_TITLE'); ?></h1>
            <div class="alert alert-warning em-alert warning">
                <?= JText::_('COM_EMUNDUS_TAG_TABLE_WARNING'); ?>
            </div>
        </div>
        <div id="emundus_elements">
            <div class="panel panel-primary excel" id="emundus_tag_table">
                <div class="panel-heading">
                    <legend>
                        <label><?= JText::_('OTHER_TAG_SELECT'); ?></label>
                    </legend>
                </div>

                <div class="panel-body">
                    <div class="panel panel-info excel" id="emundus_grp_<?= $t->group_id; ?>">
                        <div class="panel-heading">
                            <legend>
                                <label for="emundus_checkall_grp_'<?= $t->group_id; ?>"><?= JText::_($t->group_label); ?></label>
                            </legend>
                        </div>

                        <div class="panel-body" id="other-result-body">
                            <div class="em-element-title em-element-main-title">
                                <div class="em-element-title-id em-element-main-title-id col-md-3">
                                    <b><?= JText::_('ID'); ?></b>
                                </div>
                                <div class="em-element-title-label em-element-main-title-label col-md-9">
                                    <b><?= JText::_('LABEL'); ?></b>
                                </div>
                                <br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    function programSelect() {
        let course = document.getElementById('program').options[document.getElementById('program').selectedIndex].value;
        if (course !== '') {
            var httpRequest = new XMLHttpRequest();
            httpRequest.responseType = 'json';
            httpRequest.onreadystatechange = function(data) {

                if (httpRequest.status == 200 && httpRequest.readyState == 4 && httpRequest.response) {
                    var campaigns = httpRequest.response.campaigns;
                    campaigns.forEach(function(campaign) {
                        var opt = document.createElement('option');
                        opt.appendChild( document.createTextNode(campaign.label) );
                        opt.value = campaign.id;
                        document.getElementById('campaign').appendChild(opt);
                        document.getElementById('program-categories').classList.remove('hide');
                    });
                }
            };
            httpRequest.open("GET", '<?= JURI::base(); ?>index.php?option=com_emundus&controller=campaign&task=getcampaignsbyprogram&course='+course, true);
            httpRequest.send();
        }
        else {
            document.getElementById('program-categories').classList.add('hide');
        }

    }

    function showEval() {
        var course = document.getElementById('program').options[document.getElementById('program').selectedIndex].value;
        document.getElementById('campaign').value = '';
        document.getElementById('other-result').classList.add('hide');

        var httpRequest = new XMLHttpRequest();
        httpRequest.onreadystatechange = function(data) {
            if (httpRequest.status == 200 && httpRequest.readyState == 4) {
                document.getElementById('result').innerHTML = "";
                document.getElementById('result').innerHTML = "<hr>" + httpRequest.responseText;
                document.querySelector('#result .em-program-title h1').innerHTML = document.querySelector('#result .em-program-title h1').innerHTML + " - <?= JText::_('EXPORT_EVAL_TITLE');?>";
            }
        };
        httpRequest.open("GET", '<?= JURI::base(); ?>index.php?option=com_emundus&view=export_select_columns&format=raw&code='+course+'&layout=programme&form=evaluation&all=1', true);
        httpRequest.send();
    }

    function showAdmission() {
        var course = document.getElementById('program').options[document.getElementById('program').selectedIndex].value;
        document.getElementById('campaign').value = '';
        document.getElementById('other-result').classList.add('hide');

        var httpRequest = new XMLHttpRequest();
        httpRequest.onreadystatechange = function(data) {
            if (httpRequest.status == 200 && httpRequest.readyState == 4) {
                document.getElementById('result').innerHTML = "";
                document.getElementById('result').innerHTML = "<hr>" + httpRequest.responseText;
                document.querySelector('#result .em-program-title h1').innerHTML = document.querySelector('#result .em-program-title h1').innerHTML + " - <?= JText::_('EXPORT_ADMISSION_TITLE');?>";
            }
        };
        httpRequest.open("GET", '<?= JURI::base(); ?>index.php?option=com_emundus&view=export_select_columns&format=raw&code='+course+'&layout=programme&form=admission&all=1', true);
        httpRequest.send();
    }

    function showDecision() {
        var course = document.getElementById('program').options[document.getElementById('program').selectedIndex].value;
        document.getElementById('campaign').value = '';
        document.getElementById('other-result').classList.add('hide');

        var httpRequest = new XMLHttpRequest();
        httpRequest.onreadystatechange = function(data) {
            if(httpRequest.status == 200 && httpRequest.readyState == 4) {
                document.getElementById('result').innerHTML = "";
                document.getElementById('result').innerHTML = "<hr>" + httpRequest.responseText;
                document.querySelector('#result .em-program-title h1').innerHTML = document.querySelector('#result .em-program-title h1').innerHTML + " - <?= JText::_('EXPORT_DECISION_TITLE');?>";
            }
        };
        httpRequest.open("GET", '<?= JURI::base(); ?>index.php?option=com_emundus&view=export_select_columns&format=raw&code='+course+'&layout=programme&form=decision&all=1', true);
        httpRequest.send();
    }


    function showAll() {
        var course = document.getElementById('program').options[document.getElementById('program').selectedIndex].value;
        var camp = document.getElementById('campaign').options[document.getElementById('campaign').selectedIndex];

        document.getElementById('other-result').classList.add('hide');

        document.getElementById('campaign').selectedIndex = 0;

        if (camp !== '') {
            var httpRequest = new XMLHttpRequest();
            httpRequest.onreadystatechange = function(data) {
                if(httpRequest.status == 200 && httpRequest.readyState == 4) {
                    document.getElementById('result').innerHTML = "";
                    document.getElementById('result').innerHTML = "<hr>" + httpRequest.responseText;
                    document.querySelector('#result .em-program-title h1').innerHTML = document.querySelector('#result .em-program-title h1').innerHTML + " - "+camp.text;
                }
            };
            httpRequest.open("GET", '<?= JURI::base(); ?>index.php?option=com_emundus&view=export_select_columns&format=raw&code='+course+'&layout=programme&camp='+camp.value+'&all=1', true);
            httpRequest.send();
        }

    }

    function showOther() {
        document.getElementById('campaign').value = '';
        document.getElementById('result').value = '';
        var httpRequest = new XMLHttpRequest();
        httpRequest.responseType = 'json';
        httpRequest.onreadystatechange = function(data) {
            if (httpRequest.status == 200 && httpRequest.readyState == 4 && httpRequest.response) {
                document.getElementById('result').innerHTML = "";
                document.getElementById('other-result').classList.remove('hide');
                var tags = httpRequest.response.tags;
                var setWidth = 0;
                tags.forEach(function(tag) {
                    /* element ID */
                    var id = document.createElement('div');
                    id.setAttribute('class', 'em-element-id');
                    id.classList.add('col-md-3');
                    id.setAttribute('id', tag.tag);
                    id.onclick = function() {copyid('['+tag.tag+']')};
                    id.setAttribute('data-toggle', 'tooltip');
                    id.setAttribute('data-placement', 'left');
                    id.setAttribute('title', '<?=JText::_("SELECT_TO_COPY");?>');
                    id.innerText = '['+tag.tag+']';


                    var seperator = document.createElement('div');
                    seperator.setAttribute('class', 'col-md-2');


                    /* element Label */
                    var label = document.createElement('div');
                    label.setAttribute('class', 'em-element-label');
                    label.classList.add('col-md-9');
                    label.innerText = tag.description;

                    const container =  document.createElement('div');
                    container.setAttribute('class', 'em-element other-em-element');
                    container.append(id);

                    container.append(label);
                    document.getElementById('other-result-body').append(container);

                    if (setWidth < document.getElementById(tag.tag).clientWidth) {
                        setWidth = id.clientWidth;
                    }
                });
                console.log(setWidth);
                console.log(document.getElementsByClassName('em-element-id'));
                document.getElementsByClassName('em-element-id')[0].style["min-width"] = setWidth;
            }
        };
        httpRequest.open("GET", '<?= JURI::base(); ?>index.php?option=com_emundus&controller=export_select_columns&task=getalltags', true);
        httpRequest.send();
    }

    function copyid(t) {
        var text = document.createElement("textarea");
        document.body.appendChild(text);
        text.value = t;
        text.select();
        document.execCommand("copy");
        document.body.removeChild(text);

        Swal.fire({
            timer: 1500,
            showConfirmButton: false,
            type: 'success',
            title: "<?= JText::_('TAG_COPIED'); ?> :<br>" + t
        });
    }
</script>

<style>
    .em-element-id, .em-element-label, .em-element-title-id, .em-element-title-label {
        text-align: left;
    }
</style>
