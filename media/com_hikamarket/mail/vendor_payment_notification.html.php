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

<img src="{VAR:LIVE_SITE}media/com_hikashop/images/icons/icon-48-order.png" border="0" alt="" style="float:left;margin-right:4px;"/>
<h1 style="color:#1c8faf !important;font-size:16px;font-weight:bold; border-bottom:1px solid #ddd; padding-bottom:10px">
	{TXT:MAIL_TITLE}
</h1>

<h2 style="color:#1c8faf !important;font-size:12px;font-weight:bold; padding-bottom:10px">
	{TXT:YOU_HAVE_BEEN_PAID}
</h2>
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
	{TXT:SALE_BEGIN_MESSAGE}
</p>
<h1 style="color:#1c8faf !important;font-size:16px;font-weight:bold;border-bottom:1px solid #ddd;padding-top:10px;padding-bottom:10px;">
	{TXT:SALE_CUSTOMER}
</h1>

<table class="w550" border="0" cellspacing="0" cellpadding="0" width="550" style="margin-top:10px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
	<tr>
		<td style="color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:BILLING_ADDRESS}</td>
	</tr>
	<tr>
		<td>{VAR:BILLING_ADDRESS}</td>
	</tr>
</table>

<h1 style="color:#1c8faf !important;font-size:16px;font-weight:bold;border-bottom:1px solid #ddd;padding-top:10px;padding-bottom:10px;">
	{TXT:SUMMARY_OF_YOUR_SALE}
</h1>

<table class="w550" border="0" cellspacing="0" cellpadding="0" width="550" style="margin-top:10px;margin-bottom:10px;font-family: Arial, Helvetica, sans-serif;font-size:12px;line-height:18px;">
	<tr>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:left;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_NAME}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_PRICE}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_QUANTITY}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PRODUCT_TOTAL}</td>
	</tr>
<!--{START:PRODUCT_LINE}-->
	<tr>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;">
			{LINEVAR:PRODUCT_IMG}
			{LINEVAR:PRODUCT_NAME}<!--{IF:ORDER_PRODUCT_CODE}--> {LINEVAR:PRODUCT_CODE}<!--{ENDIF:ORDER_PRODUCT_CODE}-->
		</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:PRODUCT_PRICE}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:PRODUCT_QUANTIY}</td>
		<td style="border-bottom:1px solid #ddd;padding-bottom:3px;text-align:right">{LINEVAR:PRODUCT_TOTAL}</td>
	</tr>
<!--{END:PRODUCT_LINE}-->
<!--{START:ORDER_FOOTER}-->
	<tr>
		<td colspan="3" style="text-align:right;color:#1c8faf !important;font-size:12px;font-weight:bold;">{LINEVAR:NAME}</td>
		<td style="text-align:right">{LINEVAR:VALUE}</td>
	</tr>
<!--{END:ORDER_FOOTER}-->
</table>
<!--{IF:PAYMENT}-->
<p>
	<span style="color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:PAYMENT_METHOD} :</span> {VAR:PAYMENT}
</p>
<!--{ENDIF:PAYMENT}-->
<!--{IF:SHIPPING}-->
<p>
	<span style="color:#1c8faf !important;font-size:12px;font-weight:bold;">{TXT:HIKASHOP_SHIPPING_METHOD} :</span> {VAR:SHIPPING}
</p>
<!--{ENDIF:SHIPPING}-->
<!--{IF:ORDER_SUMMARY}-->
<h1 style="color:#1c8faf !important;font-size:16px;font-weight:bold;border-bottom:1px solid #ddd;padding-top:10px;padding-bottom:10px;">
	{TXT:ADDITIONAL_INFORMATION}
</h1>
<p style="border-bottom:1px solid #ddd;padding-bottom:10px;">
	{VAR:ORDER_SUMMARY}
</p>
<!--{ENDIF:ORDER_SUMMARY}-->
<p>
	{TXT:SALE_END_MESSAGE}
</p>
			</div>
		</td>
		<td class="w20" width="20"></td>
	</tr>
</table>
