<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="w600" border="0" cellspacing="0" cellpadding="0" width="600" style="margin:0px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
	<tr>
		<td class="w20" width="20"></td>
		<td class="w560 pict" style="text-align:left; color:#575757" width="560">
			<div id="title" style="font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">

<img src="{VAR:LIVE_SITE}/media/com_hikashop/images/icons/icon-48-payment.png" border="0" alt="" style="float:left;margin-right:4px;"/>
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
	<h3 style="color:#393939 !important; font-size:14px; font-weight:normal; font-weight:bold;margin-bottom:0px;padding:0px;">{TXT:HI_ADMIN}</h3>
	{TXT:VENDOR_PAYMENT_REQUEST_BEGIN_MESSAGE}
</p>
<p>
	<strong>{TXT:HIKA_VENDOR}</strong>: <a href="{VAR:VENDOR_URL}">{VAR:vendor.vendor_name}</a>
</p>

<table class="w550" border="0" cellspacing="0" cellpadding="0" width="550" style="margin-top:10px;margin-bottom:10px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
	<tr>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:left;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:ORDER_STATUS}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:HIKAM_STATS_TOTAL_ORDERS}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:HIKASHOP_TOTAL}</td>
	</tr>
<!--{START:REQUEST_LINE}-->
	<tr>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;">{LINEVAR:NAME}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:COUNT}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:TOTAL}</td>
	</tr>
<!--{END:REQUEST_LINE}-->
<!--{START:REQUEST_FOOTER}-->
	<tr>
		<td colspan="2" style="text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{LINEVAR:COUNT}</td>
		<td style="text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{LINEVAR:TOTAL}</td>
	</tr>
<!--{END:REQUEST_FOOTER}-->
</table>
			</div>
		</td>
		<td class="w20" width="20"></td>
	</tr>
</table>
