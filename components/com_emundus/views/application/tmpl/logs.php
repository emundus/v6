<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 13/11/14
 * Time: 11:24
 */
JFactory::getSession()->set('application_layout', 'logs');

$offset = JFactory::getApplication()->get('offset', 'UTC');
$dateTime = new DateTime(gmdate('Y-m-d H:i:s'), new DateTimeZone($offset));
$now = $dateTime->format(JText::_('DATE_FORMAT_LC2'));

?>

<style type="text/css">
	.widget .panel-body { padding:0px; }
	.widget .list-group { margin-bottom: 0; }
	.widget .panel-title { display:inline }
	.widget .label-info { float: right; }
	.widget li.list-group-item {border-radius: 0;border: 0;border-top: 1px solid #ddd;}
	.widget li.list-group-item:hover { background-color: rgba(86,61,124,.1); }
	.widget .mic-info { color: #666666;font-size: 11px; }
	.widget .action { margin-top:5px; }
    .widget .log-message { font-size: 18px; font-weight:600;}
    .widget .log-user { font-size:16px;}
	.widget .btn-block { border-top-left-radius:0px;border-top-right-radius:0px; }
    .widget .filter-item { list-style:none; }
</style>

<div class="logs">
    <div class="row">
        <div class="panel panel-default widget em-container-comment">
            <div class="panel-heading em-container-comment-heading">

                <h3 class="panel-title">
                	<span class="glyphicon glyphicon-list"></span>
                	<?php echo JText::_('LOGS'); ?>
                    <span class="label label-info"><?php echo count($this->fileLogs); ?></span>
                </h3>

                <div class="btn-group pull-right">
                    <button id="em-prev-file" class="btn btn-info btn-xxl"><i class="small arrow left icon"></i></button>
                    <button id="em-next-file" class="btn btn-info btn-xxl"><i class="small arrow right icon"></i></button>
                </div>

            </div>
            <div class="panel-body em-container-comment-body">
                <ul class="list-group">
                <?php
				if (count($this->fileLogs) > 0) {
					$i = 0;
					foreach ($this->fileLogs as $log) { ?>
                    <li class="list-group-item" id="<?php echo $log->id; ?>">
                        <div class="row">
                            <div class="col-xs-10 col-md-11">
                                <div class="em-list-status">
                                    <div class="log-message"><?php echo $this->messages[$i]; ?></div>
                                    <div class="log-user"><?php echo $log->firstname . ' ' . $log->lastname; ?> - <?php echo $log->timestamp; ?></div>
                                </div>
                            </div>
                        </div>
                    </li>
                 <?php
						$i++;
					}
				} else echo JText::_('NO_LOGS');
				?>
                </ul>
			</div>
        </div>
    </div>
</div>
