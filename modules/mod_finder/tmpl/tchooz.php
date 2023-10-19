<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_SITE . '/components/com_finder/helpers/html');

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

$route .= '&format=feed';

// Load the smart search component language file.
$lang = JFactory::getLanguage();
$lang->load('com_finder', JPATH_SITE);

$suffix = $params->get('moduleclass_sfx');
$output = '<input type="text" name="q" id="mod-finder-searchword' . $module->id . '" class="search-query input-medium" size="'
	. $params->get('field_size', 20) . '" value="' . htmlspecialchars(JFactory::getApplication()->input->get('q', '', 'string'), ENT_COMPAT, 'UTF-8') . '"'
	. ' placeholder="' . JText::_('MOD_FINDER_SEARCH_VALUE') . '"/>';

$showLabel  = $params->get('show_label', 1);
$labelClass = (!$showLabel ? 'element-invisible ' : '') . 'finder' . $suffix;
$label      = '<label for="mod-finder-searchword' . $module->id . '" class="' . $labelClass . '">' . $params->get('alt_label', JText::_('JSEARCH_FILTER_SUBMIT')) . '</label>';

switch ($params->get('label_pos', 'left'))
{
	case 'top' :
		$output = $label . '<br />' . $output;
		break;

	case 'bottom' :
		$output .= '<br />' . $label;
		break;

	case 'right' :
		$output .= $label;
		break;

	case 'left' :
	default :
		$output = $label . $output;
		break;
}

if ($params->get('show_button', 0))
{
	$button = '<button class="btn btn-primary hasTooltip ' . $suffix . ' finder' . $suffix . '" type="submit" title="' . JText::_('MOD_FINDER_SEARCH_BUTTON') . '"><span class="icon-search icon-white"></span>' . JText::_('JSEARCH_FILTER_SUBMIT') . '</button>';

	switch ($params->get('button_pos', 'left'))
	{
		case 'top' :
			$output = $button . '<br />' . $output;
			break;

		case 'bottom' :
			$output .= '<br />' . $button;
			break;

		case 'right' :
			$output .= $button;
			break;

		case 'left' :
		default :
			$output = $button . $output;
			break;
	}
}

JHtml::_('stylesheet', 'com_finder/finder.css', array('version' => 'auto', 'relative' => true));
/*
 * This segment of code sets up the autocompleter.
 */
if ($params->get('show_autosuggest', 1))
{
	JHtml::_('script', 'jui/jquery.autocomplete.min.js', array('version' => 'auto', 'relative' => true));
}
?>

<style>
    .mod-finder-modal{
        position: fixed;
        left: 50%;
        transform: translate(-50%, 0);
        top: 20%;
        width: 45%;
        box-shadow: rgba(0, 0, 0, 0.5) 0px 16px 70px;
        max-width: 640px;
        padding: 20px;
        border-radius: 8px;
        background: #f0f8ff;
        display: none;
        z-index: 100;
    }
    .mod-finder-modal input{
        width: 100%;
        border-radius: 0 !important;
        border: unset;
        background: #f0f8ff !important;
        margin-bottom: 0;
        border-bottom: solid 1px #363636 !important;
    }
    .mod-finder-modal input:hover,.mod-finder-modal input:focus{
        border: unset;
    }
    .autocomplete-suggestions{
        position: fixed !important;
        width: 100% !important;
        max-height: 640px !important;
        z-index: 9999;
        left: 0 !important;
        margin-top: 5px;
        background: #f0f8ff !important;
        border: unset;
        border-top: solid 1px;
        padding: 10px 4px;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        box-shadow: unset;
    }
    .autocomplete-suggestion{
        border-radius: 4px;
        padding: 8px;
    }
    .autocomplete-selected{
        background: #e1e1e1;
    }
    #search_results{
        padding: 8px;
        display: flex;
        flex-direction: column;
    }
    .em-tab{
        border-bottom: solid 2px transparent;
    }
    .em-tab-selected{
        border-bottom-color: #20835F;
    }
</style>

<?php if (JFactory::getUser()->id == [62]) : ?>
    <span class="material-icons em-pointer" id="mod_finder_icon_open" style="color: black;margin-top: 4px; font-size: 30px" onclick="openSearch()">search</span>
<?php endif; ?>
<div class="mod-finder-modal" id="mod_finder_modal">
    <h5 class="em-font-weight-200 em-mb-16"><?php echo  JText::_('MOD_FINDER_HOW_CAN_I_HELP_YOU') ?></h5>
    <nav class="em-flex-row em-mb-12" style="display: none">
        <span id="scope_all" class="em-tab em-pointer em-p-8-12 em-tab-selected" onclick="updateScope('all')">Tout</span>
        <span id="scope_files" class="em-tab em-pointer em-p-8-12" onclick="updateScope('files')">Dossiers</span>
        <span id="scope_filters" class="em-tab em-pointer em-p-8-12" onclick="updateScope('filters')">Filtres</span>
    </nav>
    <input type="search" placeholder="<?php echo JText::_('MOD_FINDER_SEARCH_VALUE') ?>" id="mod-finder-searchword<?php echo $module->id ?>" />
    <div class="em-mt-12 em-flex-column em-w-100" id="finder-loader" style="display: none">
        <div class="em-loader"/></div>
    </div>
    <div id="search_results" class="em-mt-12">
    </div>
</div>

<script>
    let keysPressed = [];
    let searchScope = 'all';

    function updateScope(scope){
        searchScope = scope;
        let all = document.getElementById('scope_all');
        let files = document.getElementById('scope_files');
        let filters = document.getElementById('scope_filters');

        all.classList.remove('em-tab-selected');
        files.classList.remove('em-tab-selected');
        filters.classList.remove('em-tab-selected');

        document.getElementById('scope_' + scope).classList.add('em-tab-selected');
    }

    function delay(callback, ms) {
        var timer = 0;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, ms || 0);
        };
    }

    function openSearch(){
        let spotlight = document.getElementById('mod_finder_modal');

        spotlight.style.display = 'block'
        document.getElementById('mod-finder-searchword<?php echo $module->id ?>').focus();
    }

    function closeSearch(){
        let spotlight = document.getElementById('mod_finder_modal');
        spotlight.style.display = 'none'
    }

    document.addEventListener('click', function (e) {
        let spotlight = document.getElementById('mod_finder_modal');
        let clickInsideModule = false;

        if(spotlight.style.display === 'block') {
            e.composedPath().forEach((pathElement) => {
                if (pathElement.id == "mod_finder_modal" || pathElement.id == "mod-finder-searchword<?php echo $module->id ?>" || pathElement.id == 'mod_finder_icon_open') {
                    clickInsideModule = true;
                }
            });

            if (!clickInsideModule) {
                spotlight.style.display = 'none';
            }
        }
    });

    window.addEventListener('keydown', (e) => {
        e.stopImmediatePropagation();
        keysPressed[e.key] = true;

        if ((keysPressed['Control'] || keysPressed['Meta']) && e.key === 'k') {
            e.preventDefault();
            openSearch();
            keysPressed = [];
        } else if(keysPressed['Escape']) {
            e.preventDefault();
            closeSearch()
        }
    });

    jQuery(document).ready(function() {
        var value, searchword = jQuery('#mod-finder-searchword<?php echo $module->id ?>');

        // Get the current value.
        value = searchword.val();

        // If the current value equals the default value, clear it.
        searchword.on('focus', function () {
            var el = jQuery(this);

            if (el.val() === '<?php echo JText::_('MOD_FINDER_SEARCH_VALUE', true) ?>')
            {
                el.val('');
            }
        });

        // If the current value is empty, set the previous value.
        searchword.on('blur', function () {
            var el = jQuery(this);

            if (!el.val()) {
                el.val(value);
            }
        });

        jQuery('#mod-finder-searchform<?php echo $module->id ?>').on('submit', function (e) {
            e.stopPropagation();
            var advanced = jQuery('#mod-finder-advanced<?php echo $module->id ?>');

            // Disable select boxes with no value selected.
            if (advanced.length) {
                advanced.find('select').each(function (index, el) {
                    var el = jQuery(el);

                    if (!el.val()) {
                        el.attr('disabled', 'disabled');
                    }
                });
            }
        });

        <?php if ($params->get('show_autosuggest', 1)) : ?>
        jQuery('#mod-finder-searchword<?php echo $module->id ?>').keyup(delay(function (e) {
            document.getElementById('search_results').innerHTML = '';
            if(e.target.value !== '') {
                document.getElementById('finder-loader').style.display = 'flex';
                fetch('<?php echo $route; ?>&q=' + e.target.value)
                    .then((response) => {
                        if (response.ok) {
                            return response.text();
                        }
                    }).then((res) => {
                    return new window.DOMParser().parseFromString(res, "text/xml")
                }).then((data) => {
                    document.getElementById('finder-loader').style.display = 'none';

                    let items = data.getElementsByTagName('item');

                    if (items.length == 0) {
                        document.getElementById('search_results').insertAdjacentHTML('beforeend', '<p class="em-mb-8"><?php echo JText::_('MOD_EM_FINDER_NO_RESULTS_FOUND') ?></p>');
                    }
                    for (item of items) {
                        let content = '<a class="em-mb-8" target="_blank" href="' + item.getElementsByTagName('link')[0].textContent + '">' + item.getElementsByTagName('title')[0].textContent;

                        let details = item.getElementsByTagName('description')[0];
                        if(typeof details !== 'undefined' && details !== null) {
                            details = JSON.parse(details.textContent);

                            content += '<p class="em-font-size-12 em-mt-4">'+details.fnum+'</p>'
                        }
                        content += '</a>';

                        document.getElementById('search_results').insertAdjacentHTML('beforeend',content);
                    }

                })
            }
        }, 500));
        // TODO : Create a tmpl to display results as suggestion
        /*var suggest = jQuery('#mod-finder-searchword<?php echo $module->id ?>').autocomplete({
            appendTo: '#mod_finder_modal',
            serviceUrl: '<?php echo JRoute::_($route); ?>',
            paramName: 'q',
            minChars: 1,
            maxHeight: 400,
            width: 300,
            zIndex: 9999,
            deferRequestBy: 500
        });*/
        <?php endif; ?>
    });
</script>
