<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="w600" border="0" cellspacing="0" cellpadding="0" width="600" style="margin:0px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
	<tr>
		<td class="w20" width="20"></td>
		<td class="w560 pict" style="text-align:left; color:#575757" width="560">
			<div id="title" style="font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">

<img src="{VAR:LIVE_SITE}/media/com_hikashop/images/icons/icon-48-product.png" border="0" alt="" style="float:left;margin-right:4px;"/>
<h1 style="color:#1c8faf !important;font-size:16px;font-weight:bold; border-bottom:1px solid #ddd; padding-bottom:10px">
	{TXT:MAIL_TITLE}
</h1>
			</div>
		</td>
		<td class="w20" width="20"></td>
	</tr>
	<tr>
		<td class="w20" width="20"></td>
		<td style="border:1px solid #adadad;background-color:#ffffff;">
			<div class="w550" width="550" id="content" style="font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;margin-left:5px;margin-right:5px;">
<p>
	<h3 style="color:#393939 !important; font-size:14px; font-weight:normal; font-weight:bold;margin-bottom:0px;padding:0px;">{TXT:HI_VENDOR}</h3>
	{TXT:PRODUCT_APPROVAL_BEGIN_MESSAGE}
</p>
<!--{IF:product}-->
<p>
	<strong>{TXT:PRODUCT}</strong>: <a href="{VAR:PRODUCT_URL}">{VAR:product.product_name}</a>
</p>
<!--{ENDIF:product}-->
<!--{IF:products}-->
<ul>
<!--{START:products}-->
	<li><a href="{LINEVAR:url}">{LINEVAR:product_name}</a></li>
<!--{END:products}-->
</ul>
<!--{ENDIF:products}-->
<!--{IF:message}-->
<h4>{TXT:APPROVAL_ADDITIONAL_MESSAGE}</h4>
<p>
{VAR:message}
</p>
<!--{ENDIF:message}-->
			</div>
		</td>
		<td class="w20" width="20"></td>
	</tr>
</table>
