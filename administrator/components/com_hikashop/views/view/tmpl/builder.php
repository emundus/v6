<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="shared-lists" class="">
    <div class="shared-lists-container">
    <h4><?php echo JText::_('VIEW_BUILDER'); ?></h4>
<?php
$grid = false;
$pos = '';
$forbidden_ids = array();
hikashop_loadJsLib('tooltip');
foreach($this->element->structure as $k => $structure) {
    foreach($structure->blocks as $block) {
        if(in_array($block->type, array('block', 'empty'))) {
            $this->element->structure[$k]->has_blocks = true;
            break;
        }
    }
    if(isset($structure->id))
        $forbidden_ids[] = $structure->id;
}
$count = 0;
foreach($this->element->structure as $k => $structure) {
    if(empty($structure->has_blocks))
        continue;
    if($count>=12) {
        $count = 0;
        $pos = 'left';
        $grid = false;
?>
        </div>
    </div>
<?php
    }
    if(is_numeric($structure->width)) {
        if($grid == false) {
            $grid = true;
?>
    <div class="hk-row-fluid">
<?php
        $count += $structure->width;
        }else {
?>
        </div>
<?php
}
?>
        <div class="hkc-sm-<?php echo $structure->width; ?>">
<?php
    }
    if($structure->width == 'left' && $grid == false) {
        $grid = true;
        $pos = 'left';
?>
    <div class="hk-row-fluid">
        <div class="hkc-sm-6 ">
<?php
    }
    if($structure->width == 'right' && $pos == 'left') {
        $pos = 'right';
?>
        </div>
        <div class="hkc-sm-6 ">
<?php
    }
    if($structure->width == 'full' && $grid) {
        $count = 0;
        $pos = 'left';
        $grid = false;
?>
        </div>
    </div>
<?php
    }
    if(isset($structure->id))
        $id = $structure->id;
    else {
        $id = $k;
        while(in_array($id, $forbidden_ids)) {
            $id++;
        }
        $forbidden_ids[] = $id;
    }
?>
    <div id="builder-group<?php echo $id; ?>" class="builder-list-group"><?php
    foreach($structure->blocks as $i => $block) {
        if($block->type == 'separator') {
            $keys = array_keys($structure->blocks);
            if($i != end($keys) && $i != reset($keys)) {
                $id = max($forbidden_ids)+1;
                $forbidden_ids[] = $id;
?>
    </div>
    <div id="builder-group<?php echo $id; ?>" class="builder-list-group">
<?php
            }
            continue;
        }
        if($block->type == 'normal')
            continue;
        if($block->type == 'empty')
            continue;

        $tooltip = JText::sprintf('VIEW_BUILDER_DELETE', $block->name); 
        ?><div class="list-group-item" data-id="<?php echo $block->name; ?>">
            <?php echo $block->name; ?>
            <a href="#" onclick="removeBlock(this);" data-toggle="hk-tooltip" data-title="<?php echo $tooltip ?>" data-original-title="">
                <i class="fas fa-times"></i>
            </a>
         </div><?php
    }
    ?></div>
<?php
}
if($grid) {
?>
        </div>
    </div>
<?php
}
$changes_text = " Please first save your modifications so that the drag & drop interface can be refreshed.";
?>
</div>
</div>
<?php hikashop_loadJslib('sortable'); ?>
<script>
var userModifications = false;
var sortableModifications = false;
window.hikashop.ready( function() {
    var textarea = document.getElementById('jform_articletext');

    textarea.addEventListener('input', function(evt) {
        if(!window.sortableModifications)
            window.userModifications = true;
    });

    var editor = document.querySelector('.CodeMirror');
    if(editor) {
        editor = editor.CodeMirror;
        editor.on('change', function(instance, changeObj) {
            if(!window.sortableModifications)
                window.userModifications = true;
        });
    }
});
function removeBlock(link) {
    if(confirm('Are you sure you want to delete this block ?')) {
        var evt = {};
        evt.item = link.parentNode;
        evt.from = link.parentNode.parentNode;
        if(moveBlockCode(evt))
            link.parentNode.remove();
    }
}
function checkCodeMove(evt) {
    if(window.userModifications) {
        alert("The move was not possible as the code has been manually changed in the code editor. <?php echo $changes_text; ?>");
        return false;
    }
    return true;
}
function moveBlockCode(evt) {
    window.sortableModifications = true;
    var textarea = document.getElementById('jform_articletext');
    var regex = new RegExp('([\t\t\n ]*)<!-- ' + evt.item.getAttribute('data-id') + ' -->(.*?)<!-- EO ' + evt.item.getAttribute('data-id') +' -->', 'gs');
    var code = regex.exec(textarea.value);
    var codeleft = '';
    var empty_pos_code = [];

    if(!code || !code.length) {
        alert("The move was not possible tags for the block being moved could not be found in the code.<?php echo $changes_text; ?>");
        window.sortableModifications = false;
        return false;
    }

    var blocks_left = evt.from.querySelectorAll('.list-group-item');
    if(!blocks_left || blocks_left.length == 0 || (blocks_left.length == 1 && blocks_left[0].getAttribute('data-id') == evt.item.getAttribute('data-id'))) {
        var id = evt.from.id.replace('builder-group','');
        codeleft = code[1] + '<!-- POSITION '+id+' -->';
        evt.from.innerHTML = '';
    }

    if(evt.to) {
        var blocks_on_arrival = evt.to.querySelectorAll('.list-group-item');
        if(blocks_on_arrival.length <= 1) {
            var id = evt.to.id.replace('builder-group','');
            var regex = new RegExp('([\t\t\n ]*)<!-- POSITION '+id+' -->', 'gs');
            var empty_pos_code = regex.exec(textarea.value);
            if(!empty_pos_code || !empty_pos_code.length) {
                alert('The move was not possible as the empty position tag could not be found in the destination position');
                window.sortableModifications = false;
                return false;
            }
        } else {
            var sibling = null;
            var after = false;
            if(blocks_on_arrival[evt.newIndex+1]) {
                sibling = blocks_on_arrival[evt.newIndex+1];
            } else {
                sibling = blocks_on_arrival[evt.newIndex-1];
                after = true;
            }
            var regex = new RegExp('([\t\t\n ]*)<!-- ' + sibling.getAttribute('data-id') + ' -->(.*?)<!-- EO ' + sibling.getAttribute('data-id') +' -->', 'gs');
            var sibling_code = regex.exec(textarea.value);
            if(!sibling_code || !sibling_code.length) {
                alert('The move was not possible as the ' + sibling.getAttribute('data-id') + ' tag could not be found in the destination position');
                window.sortableModifications = false;
                return false;
            }
            var id = evt.to.id.replace('builder-group','');
            empty_pos_code.push(sibling_code[1] + '<!-- POSITION '+id+' -->');
            var result = empty_pos_code[0] + sibling_code[0];
            if(after) {
                result = sibling_code[0] + empty_pos_code[0];
            }
            textarea.value = textarea.value.replace(sibling_code[0], result);

        }
    }
    textarea.value = textarea.value.replace(code[0], codeleft);

    if(evt.to) {
        textarea.value = textarea.value.replace(empty_pos_code[0], code[0]);
    }

    var editor = document.querySelector('.CodeMirror');
    if(editor) {
        editor = editor.CodeMirror;
        var scrollInfo = editor.getScrollInfo();

        editor.setValue(textarea.value);

        editor.scrollTo(scrollInfo.left, scrollInfo.top);
    }
    window.sortableModifications = false;
    return true;
}
var elements = document.querySelectorAll('.builder-list-group'), groups = [];
for(var i = 0; i < elements.length; i++) {
    groups.push(
        new Sortable(elements[i], {
            group: 'shared',
            animation: 150,
            dataIdAttr: 'data-id',
            onMove: function (evt) {
                return checkCodeMove(evt);
            },
            onEnd: function (evt) {
                return moveBlockCode(evt);
            }
        })
    );
}
</script>
<style>
.builder-list-group {
    display: -ms-flexbox;
    display: flex;
    -ms-flex-direction: column;
    flex-direction: column;
    padding-left: 0;
    margin-bottom: 0;
    min-width: 200px;
    margin-top: 5px;
}
.list-group-item {
    position: relative;
    display: block;
    background-color: #fff;
    border-bottom: 1px solid #dedede;
    padding: 0px;
    margin: 2px 3px;
    cursor: -webkit-grab;
    cursor: grab;
    text-align: center;
}
div#shared-lists .list-group-item:hover {
    box-shadow: 5px 5px 5px rgba(0,0,0,0.4);
    position: relative;
    top: -2px;
    left: -2px;
}
#shared-lists .builder-list-group .list-group-item:active {
    cursor: -webkit-grabbing;
    cursor: grabbing;
    border: 1px solid rgba(0,0,0,0.6);
    box-shadow: 7px 7px 7px rgba(0,0,0,0.4);
    position: relative;
    top: -4px;
    left: -4px;
}
#shared-lists .builder-list-group .list-group-item.sortable-ghost {
    opacity:1 !important;
    border:2px solid rgba(0,0,0,0.5);
    box-shadow: 10px 10px 10px rgba(0,0,0,0.8);
}
.list-group-item a {
    border: 1px solid #aeadad;
    border-width: 0px 0px 0px 1px;
    float: right;
    width: 30px;
}
.list-group-item a:hover {
    background-color: #e6e6e6;
}
div#shared-lists {
    background-color: #fff;
    padding: 10px 3px 3px 3px;
}
div#shared-lists .shared-lists-container {
    background-color: #fff;
    padding: 3px 6px 6px 3px;
    border: 2px solid #c0c0c0;
    border-radius: 3px;
}
div#shared-lists h4 {
    background-color: #fff;
    display: inline-block;
    padding: 0px 9px 0px 9px;
    margin: 0px;
    position: relative;
    top: -15px;
}
div#shared-lists .hk-row-fluid > div {
    padding:0px;
}
.builder-list-group{
    min-height:20px;
}
#shared-lists #builder-group1,
#shared-lists div#builder-group11 {
    background-color: #a9f9c9;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
#shared-lists #builder-group2,
#shared-lists div#builder-group12 {
    background-color: #d5b7f7;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
#shared-lists #builder-group3,
#shared-lists div#builder-group13 {
    background-color: #f6b7c2;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
#shared-lists #builder-group4,
#shared-lists div#builder-group14 {
    background-color: #f9f98f;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
#shared-lists #builder-group5,
#shared-lists div#builder-group15 {
    background-color: #a9a9f9;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
#shared-lists div#builder-group7,
#shared-lists div#builder-group17 {
    background-color: #7f7fd2;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
#shared-lists #builder-group6,
#shared-lists div#builder-group16 {
    background-color: #fad591;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
#shared-lists div#builder-group8,
#shared-lists div#builder-group18 {
    background-color: #fad591;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
#shared-lists div#builder-group0,
#shared-lists div#builder-group9,
#shared-lists div#builder-group19 {
    background-color: #f5afaf;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
#shared-lists div#builder-group10,
#shared-lists div#builder-group20 {
    background-color: #adf7ad;
    padding: 1px 0px 1px 0px;
    margin: 0px;
    border: 1px solid #fff;
}
.builder-list-group:empty:after {
    content: '<?php echo JText::_('EMPTY_POSITION'); ?>';
    text-align: center;
    padding: .75rem 1.25rem !important;
    margin-left: -30px;
    font-weight: bold;
}
</style>
