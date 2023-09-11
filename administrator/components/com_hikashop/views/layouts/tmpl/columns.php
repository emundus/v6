<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$button_style = 'style="height: 30px;width: 130px;"';
$config = hikashop_config();
$delay = (int)$config->get('switcher_cookie_retaining_period', 31557600);
$notice = JText::_('HIKA_NOTICE_COLUMNS', true);
?>
<!-- Dropdown columns selector -->
<div class="hkdropdown column_container">	
	<button type="button" data-toggle="hkdropdown" class="btn btn-primary button_column" aria-haspopup="true" aria-expanded="false" <?php echo $button_style; ?>>
		<span class="column_number"></span>
		<span class="columns_separator"> / </span>
		<span class="column_number_total"></span>
		<span class="hkdropdown-text column_text" style="margin: 0 5px;">
			<?php echo JText::_( 'HIKA_SELECT_COLUMNS', true ); ?>
		</span>
		<span class="caret"></span>
	</button>
	<!-- Dropdown columns settings [fill by JS] -->
	<ul class="hika_columns_select hkdropdown-menu"></ul>
</div>

<script type="text/javascript">
if(!window.localPage) window.localPage = {};

function dropdownFill () {
	var elems = document.querySelectorAll('table.adminlist .title');
	var drop_html ='';
	var ref = 1;
	var def_nb = 0;
	var cf_nb = 0;
	var unDisplayed = 0

	const header_name = [];
	for (i = 0; i < elems.length; i++) {
		var status = 'checked';
		if (elems[i].classList.contains('default')) {
			cssProcess(ref,'hide');
			status = '';
			def_nb++;
		}
		header_name[i] = elems[i].textContent.trim();
		if(header_name[i] == '') {
			ref++;
			unDisplayed++;
			continue;
		}
		if (elems[i].classList.contains('custom_field'))
			cf_nb++;

		drop_html += '<li>'+
			'<label href="#" onclick="window.localPage.actionColumns(event, ' + ref + ', \'click\'); return false;">' +
				'<input class="form-check-input me-1" id="columnSelect_' + ref + '" type="checkbox" name="columnSelect" value="' + header_name[i] + '" '+status+'>' +
				'<span class="hika_columns_name">' + header_name[i] + '</span>' +
			'</label>'+
		'</li>';

		ref++;
	}

	var container = document.querySelector('.hika_columns_select');
	container.innerHTML = drop_html;

	if (ref > 30) {
		container.style.columns = "50px 2";
      	container.style.width = "30%";
	}      
	var cookies = cookiesCheck();

	if (cookies != '') {
		var tot_cookies = get_total(cookies,cf_nb);

		if (tot_cookies != elems.length - cf_nb) {
			order_columns_notice('');
			resetCookies(cookies);
		}
	}
	elems_nb = elems.length - unDisplayed;
	updateNumbers(elems_nb - def_nb,elems_nb,'');
}

window.localPage.actionColumns = function (event, rank, action) {
	if (event != '') {
		event = event || window.event;
		event.preventDefault();
		event.stopPropagation();
	}
	cssProcess(rank,action);

	checkboxOperation(rank,action);

	var operator = classOperation(rank,action);

	var elems = document.querySelectorAll('table.adminlist .title');

	var unDisplayed = 0;
	for (i = 0; i < elems.length; i++) {
		if(elems[i].textContent.trim() == '')
			unDisplayed++;
	}

	updateNumbers('','',operator);

	if (action == 'click')
		cookiesSave(rank);
}
function cookiesCheck() {
	var list_code = getListKey();

	let name = "cookie_" + list_code + "=";
	let decodedCookie = decodeURIComponent(document.cookie);
	let cookiesAll = decodedCookie.split(';');

	for(let i = 0; i <cookiesAll.length; i++) {
		let cookie = cookiesAll[i];
		while (cookie.charAt(0) == ' ') {
		  cookie = cookie.substring(1);
		}
		if (cookie.indexOf(name) == 0) {
			return cookie.substring(name.length, cookie.length);
		}
	}

	return "";
}
function order_columns_notice() {
	var errormsg = '<?php echo $notice; ?>';

	Joomla.renderMessages({"warning":[errormsg]});
}
function getListKey() {
	var elems = document.querySelector('#hikashop_main_content form table.adminlist');
	var table_id = elems.id;

	var list_code = table_id.substring(9, 12);

	let name_array = table_id. split("_");
	var list_code = name_array[1];

	if (list_code == 'plugins') {
		var form_elem = document.querySelector('#hikashop_main_content form');
		var form_action = form_elem.action;
		let plg_type_array = form_action. split("=");
		list_code = plg_type_array[3];
	}
	return list_code;
}
function cssProcess (rank, action) {
	var css_value_hide = 'display:none;';
	var css_value_hidden = 'display:table-cell;';

	var css_hide = 'table.adminlist tbody td:nth-child(' + rank + '), ' +
	'table.adminlist thead th:nth-child(' + rank + ') {' + css_value_hide + '}';
	var css_unhidden = 'table.adminlist tbody td:nth-child(' + rank + '), ' +
	'table.adminlist thead th:nth-child(' + rank + ') {' + css_value_hidden + '}';

    head = document.head || document.getElementsByTagName('head')[0],
    style = document.createElement('style');
	head.appendChild(style);

	var css = '';
	var elems = document.querySelectorAll('table.adminlist .title');
	var elems_targeted = elems[parseInt(rank) - 1];

	if (action == 'hide')
		css = css_hide;

	if (action == 'display')
		css = css_unhidden;

	if (action == 'click') {
		if(elems_targeted.classList.contains('columns_hide')) {
			css = css_unhidden;
		}
		else {
			css = css_hide;
		}
	}

	style.type = 'text/css';
	if (style.styleSheet) {
		style.styleSheet.cssText = css;
	} else {
		style.appendChild(document.createTextNode(css));
	}
}
function checkboxOperation(rank,action) {
	var string = '';
	var elems = document.querySelectorAll('table.adminlist .title');
	var checkbox = document.getElementById('columnSelect_' + parseInt(rank));
	var elems_targeted = elems[parseInt(rank) - 1];
	var status = '';
	if(action == 'hide')
		status = false;
	if(action == 'display') {
		status = true;
	}
	if(action == 'click') {
		if (elems_targeted.classList.contains('columns_hide'))
			status = true;
		else 
			status = false;
	}
	if (checkbox !== null)
		checkbox.checked = status;
}
function classOperation(rank,action) {
	if(!isNaN(rank)) {
		var operator = '';
		var elems = document.querySelectorAll('table.adminlist .title');
		var elems_targeted = elems[parseInt(rank) - 1];

 		if (elems_targeted !== undefined) {
			if(action == 'hide') {
				if(!elems_targeted.classList.contains('columns_hide'))
					elems_targeted.classList.add('columns_hide');
				operator = '-';
			}
			if(action == 'display') {
				if(elems_targeted.classList.contains('columns_hide'))
					elems_targeted.classList.remove('columns_hide');

				operator = '+';
			}
			if(action == 'click') {
				if(elems_targeted.classList.contains('columns_hide')) {
					elems_targeted.classList.remove('columns_hide');
					operator = '+';
				}
				else {
					elems_targeted.classList.add('columns_hide');
					operator = '-';
				}
			}
		}
		return operator;
	}
}
function updateNumbers (nb,nb_tot,operation) {
	var number_tot = document.querySelector('.column_number_total');
	var number = document.querySelector('.column_number');

	if (nb == '' || nb_tot == '') {
		var nb_str_tot = number_tot.textContent;
		var nb_str = number.textContent;

		nb_tot = parseInt(nb_str_tot);
		nb = parseInt(nb_str);
	}
	if (operation == '-') {nb = nb - 1;}
	if (operation == '+') {nb = nb + 1;}

	number_tot.innerHTML = nb_tot;
	number.innerHTML = nb;
}
function cookiesSave(rank) {
	var rank_ref = parseInt(rank);

	var cookies = cookiesCheck();
	if (cookies == '' && cookies.includes('tot_') || !cookies.includes('obj1')) {
		cookies = resetCookies(cookies);
	}
	var cookiesObj = JSON.parse(cookies);

	if (cookiesObj.hasOwnProperty('obj' + rank_ref)) {
		if (cookiesObj['obj' + rank_ref].d == 1) {
			cookiesObj['obj' + rank_ref].d = 0;
		} else {
			cookiesObj['obj' + rank_ref].d = 1;
		}

		cookies = JSON.stringify(cookiesObj);
	}
	var list_code = getListKey();
	window.hikashop.setCookie("cookie_" + list_code,cookies,<?php echo $delay; ?>);
}

function resetCookies(old_columnRanks) {
	var elems = document.querySelectorAll('table.adminlist .title');
	let cookies_objet = {};

	if (old_columnRanks != '' && !old_columnRanks.includes('obj1')) {
		let cookiesRef = old_columnRanks.split('/');
		for(i = 0; i <cookiesRef.length; i++) {
			if (cookiesRef[i] == '' || cookiesRef[i].includes('tot_'))
				continue;

			if (cookiesRef[i].includes(':')) {
				let cookies_def_array = cookiesRef[i].split(':');
				var cookies_rank = parseInt(cookies_def_array[1]);

				if (cookiesRef[i].includes('=')) {
					let cookies_statDisp = cookies_def_array[0].split('=');
					var cookies_status = cookies_statDisp[1];
					if(cookies_statDisp[0] == 'd')
						var cookies_display = 1;
					else
						var cookies_display = 0;
				}
				else {
					var cookies_status = '';
					if(cookies_def_array[0] == 'd')
						var cookies_display = 1;
					else
						var cookies_display = 0;
				}
			}
			else {
				var cookies_rank = cookiesRef[i];
				var cookies_status = '';
				var cookies_display = 0;
			}

			var elem_ref = parseInt(cookies_rank) - 1;
			if(elems[elem_ref].classList.contains('default')) 
				cookies_status = elems[elem_ref].getAttribute('data-alias');

			if(elems[elem_ref].classList.contains('custom_field')) {
				cookies_status = 'C_' + elems[elem_ref].getAttribute('data-alias');
			}

			let rankObjet = {
			d: cookies_display, 
			s: cookies_status, 
			r: cookies_rank 
			};
			cookies_objet["obj" + cookies_rank] = rankObjet;
		}
	}
	var ref = 0;
	for (let i = 0; i < elems.length; i++) {
		ref++;
		if (cookies_objet.hasOwnProperty("obj" + ref) && elems[i].classList.contains('custom_field')) {
			if(cookies_objet["obj" + ref].s === '') {
				var rank = String(ref);
				var status = 'C_'+ elems[i].getAttribute('data-alias');
				var display = 1;

				cookies_objet["obj" + ref] = rankObjet;
				continue;
			}
		}

		if (!cookies_objet.hasOwnProperty("obj" + ref)) {
			var rank = String(ref);
			var display = 1;
			var status = '';

			if (elems[i].classList.contains('default')) {
				var status = elems[i].getAttribute('data-alias');
				var display = 0;
			}
			if (elems[i].classList.contains('custom_field')) {
				var status = 'C_'+ elems[i].getAttribute('data-alias');
				var display = 1;
			}
			let rankObjet = {
			d: display, 
			s: status, 
			r: rank 
			};

			cookies_objet["obj" + ref] = rankObjet;
		}
	}
	cookies_objet = sortedObj(cookies_objet);

	let cookiesString = JSON.stringify(cookies_objet);

	var list_code = getListKey();
	window.hikashop.setCookie("cookie_" + list_code,cookiesString,<?php echo $delay; ?>);

	return cookiesString;
}
function sortedObj(cookies_objet) {
	var entries = Object.entries(cookies_objet);

	entries.sort((a, b) => a[1].r - b[1].r);

	var sortedCookiesObj = {};
	for (const [key, value] of entries) {
		sortedCookiesObj[key] = value;
	}
	return sortedCookiesObj;
}
function get_total(cookies,cf_nb) {
	var tot_number = 0;
	if (cookies.includes('obj1')) {
		let objetCookies = JSON.parse(cookies);
		var cfCount = 0;

		for (let key in objetCookies) {
			if(objetCookies[key].s.includes('C_'))
				cfCount++;
		}
		if (cfCount > cf_nb)
			cf_nb = cfCount;

		var tot_number = parseInt(Object.keys(objetCookies).length) - cf_nb
	}
	else {
		let cookies_array = cookies.split('/');
		if (cookies.includes('tot_')) {
			for(let i = 0; i < cookies_array.length; i++) {
				if(cookies_array[i].includes('tot_'))
					tot_number = parseInt(cookies_array[i].slice(4,6)) - cf_nb;
			}
		}
		else {
			var elems = document.querySelectorAll('table.adminlist .title');
			var unDisplayed = '';
			for (i = 0; i < elems.length; i++) {
				if(elems[i].textContent.trim() == '')
					unDisplayed++;
			}

			var tot_number = cookies_array.length - (cf_nb + unDisplayed);
		}
		resetCookies(cookies);
	}
	return tot_number;
}

function afterDisplay() {
	var elems = document.querySelectorAll('table.adminlist .title');
	var unDisplayed = 0;
	var uncheckedNb = 0;

	for (i = 0; i < elems.length; i++) {
		if(elems[i].textContent.trim() == '')
			unDisplayed++;
	}
	var elems_nb = elems.length - unDisplayed;

	var fromCookie =  cookiesCheck();
	if (fromCookie != '') {
		if(fromCookie.includes('tot_') || !fromCookie.includes('obj1')) {
			fromCookie = resetCookies(fromCookie);
		}
		let objetCookies = JSON.parse(fromCookie);

		var elem_treat = 0;
		for (let key in objetCookies) {
			var flag = 1;
			var cookies_ref = objetCookies[key].r;

			var elem_ref = parseInt(cookies_ref) - 1;

			if (elem_treat < elems.length) {
				if (objetCookies[key].s.includes('C_')) {
					var curr_alias = 'C_' + elems[elem_ref].getAttribute('data-alias');
					if (curr_alias != objetCookies[key].s || curr_alias == null) {
						flag = 0;
					}
				}
				if (flag) {
					var action = 'display';
					if(objetCookies[key].d == 0) {
						action = 'hide';
						uncheckedNb++;
					}
					window.localPage.actionColumns('',cookies_ref,action);
				}
				elem_treat++;
			}
			updateNumbers(elems_nb - uncheckedNb, elems_nb, '');
		}
	}
}
window.hikashop.ready(function(){
	dropdownFill();
	setTimeout(function(){afterDisplay();}, 100);
});
</script>
