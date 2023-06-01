<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="w600" border="0" cellspacing="0" cellpadding="0" width="600" style="margin:0px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
	<tr>
		<td class="w20" width="20"></td>
		<td class="w560 pict" style="text-align:left; color:#575757" width="560">
			<div id="title" style="font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">

<img src="{VAR:LIVE_SITE}media/com_hikashop/images/icons/icon-48-forum.png" border="0" alt="" style="float:left;margin-right:4px;"/>
<h1 style="font-size:16px;font-weight:bold; border-bottom:1px solid #ddd; padding-bottom:10px">
	{TXT:CONTACT_TITLE}
</h1>
<!--{IF:PRODUCT}-->
<h2 style="font-size:12px;font-weight:bold; padding-bottom:10px">
	{TXT:FOR_PRODUCT}
</h2>
<!--{ENDIF:PRODUCT}-->
			</div>
		</td>
		<td class="w20" width="20"></td>
	</tr>
	<tr>
		<td class="w20" width="20"></td>
		<td style="border:1px solid #adadad;background-color:#ffffff;">
			<div class="w550" width="550" id="content" style="font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;margin-left:5px;margin-right:5px;">
<p>
	<h3 style="color:#393939 !important; font-size:14px; font-weight:normal; font-weight:bold;margin-bottom:0px;padding:0px;">{TXT:HI_USER}</h3>
	{TXT:CONTACT_BEGIN_MESSAGE}
</p>

<table class="w550" border="0" cellspacing="0" cellpadding="0" width="550" style="margin-top:10px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
<!--{IF:USER}-->
	<tr>
		<td style="font-size:12px;font-weight:bold;">{TXT:USER}</td>
		<td>{VAR:USER_DETAILS}</td>
	</tr>
<!--{ENDIF:USER}-->	
<!--{IF:PRODUCT}-->
	<tr>
		<td style="font-size:12px;font-weight:bold;">{TXT:PRODUCT}</td>
		<td>{VAR:PRODUCT_DETAILS}</td>
	</tr>
<!--{ENDIF:PRODUCT}-->
</table>
<!--{IF:MESSAGE}-->
<h1 style="font-size:16px;font-weight:bold;border-top:1px solid #ddd;border-bottom:1px solid #ddd;padding-top:10px;padding-bottom:10px;">
	{TXT:USER_MESSAGE}
</h1>
<p>
	{VAR:USER_MESSAGE}
</p>
<!--{ENDIF:MESSAGE}-->	
			</div>
		</td>
		<td class="w20" width="20"></td>
	</tr>
</table>
