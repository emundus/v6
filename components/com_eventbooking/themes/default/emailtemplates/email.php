<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
?>
<!doctype html>
<html>
<head>
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title><?php echo $subject; ?></title>
	<style>
		/* -------------------------------------
			GLOBAL RESETS
		------------------------------------- */
		img {
			border: none;
			-ms-interpolation-mode: bicubic;
			max-width: 100%;
		}

		body {
			background-color: #f6f6f6;
			font-family: sans-serif;
			-webkit-font-smoothing: antialiased;
			font-size: 14px;
			line-height: 1.4;
			margin: 0;
			padding: 0;
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: 100%;
		}

		table {
			border-collapse: separate;
			mso-table-lspace: 0pt;
			mso-table-rspace: 0pt;
			width: 100%;
		}

		table td {
			font-family: sans-serif;
			font-size: 14px;
			vertical-align: top;
		}

		/* -------------------------------------
			BODY & CONTAINER
		------------------------------------- */

		.body {
			background-color: #f6f6f6;
			width: 100%;
		}

		/* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
		.container {
			display: block;
			margin: 0 auto !important;
			/* makes it centered */
			max-width: 800px;
			padding: 10px;
			width: 800px;
		}

		/* This should also be a block element, so that it will fill 100% of the .container */
		.content {
			box-sizing: border-box;
			display: block;
			Margin: 0 auto;
			max-width: 880px;
			padding: 10px;
		}

		/* -------------------------------------
			HEADER, FOOTER, MAIN
		------------------------------------- */
		.main {
			background: #fff;
			border-radius: 3px;
			width: 100%;
		}

		.wrapper {
			box-sizing: border-box;
			padding: 20px;
		}

		/* -------------------------------------
			TYPOGRAPHY
		------------------------------------- */
		h1,
		h2,
		h3,
		h4 {
			color: #000000;
			font-family: sans-serif;
			font-weight: 400;
			line-height: 1.4;
			margin: 0;
			margin-bottom: 30px;
		}

		h1 {
			font-size: 35px;
			font-weight: 300;
			text-align: center;
			text-transform: capitalize;
		}
		.eb-heading, .heading {
			font-size: 20px;
			font-weight: bold;
			text-align: left;
			text-transform: capitalize;
		}
		.os_row_heading {
			font-size: 16px;
			font-weight: bold;
			text-align: left;
			text-transform: capitalize;
		}

		p,
		ul,
		ol {
			font-family: sans-serif;
			font-size: 14px;
			font-weight: normal;
			margin: 0;
			Margin-bottom: 15px;
		}

		p li,
		ul li,
		ol li {
			list-style-position: inside;
			margin-left: 5px;
		}

		a {
			color: #3498db;
			text-decoration: underline;
		}

		/* -------------------------------------
			OTHER STYLES THAT MIGHT BE USEFUL
		------------------------------------- */

		.text-center {
			text-align: center;
		}

		.text-right {
			text-align: right;
		}

		.text-left {
			text-align: left;
		}

		.clear {
			clear: both;
		}

		hr {
			border: 0;
			border-bottom: 1px solid #f6f6f6;
			Margin: 20px 0;
		}

		/* -------------------------------------
			Twitter bootstrap table style
		------------------------------------- */
		.table > thead > tr > th,
		.table > tbody > tr > th,
		.table > tfoot > tr > th,
		.table > thead > tr > td,
		.table > tbody > tr > td,
		.table > tfoot > tr > td {
			padding: 8px;
			line-height: 1.42857143;
			vertical-align: top;
			border-top: 1px solid #dddddd;
		}

		.table > thead > tr > th {
			vertical-align: bottom;
			border-bottom: 2px solid #dddddd;
		}

		.table > caption + thead > tr:first-child > th,
		.table > colgroup + thead > tr:first-child > th,
		.table > thead:first-child > tr:first-child > th,
		.table > caption + thead > tr:first-child > td,
		.table > colgroup + thead > tr:first-child > td,
		.table > thead:first-child > tr:first-child > td {
			border-top: 0;
		}

		.table > tbody + tbody {
			border-top: 2px solid #dddddd;
		}

		.table-condensed > thead > tr > th,
		.table-condensed > tbody > tr > th,
		.table-condensed > tfoot > tr > th,
		.table-condensed > thead > tr > td,
		.table-condensed > tbody > tr > td,
		.table-condensed > tfoot > tr > td {
			padding: 5px;
		}

		.table-bordered {
			border: 1px solid #ddd;
			border-collapse: separate;
			*border-collapse: collapse;
			border-left: 0;
			-webkit-border-radius: 4px;
			-moz-border-radius: 4px;
			border-radius: 4px
		}

		.table-bordered > thead > tr > th,
		.table-bordered > tbody > tr > th,
		.table-bordered > tfoot > tr > th,
		.table-bordered > thead > tr > td,
		.table-bordered > tbody > tr > td,
		.table-bordered > tfoot > tr > td {
			border-left: 1px solid #dddddd;
			border-bottom: none;
		}

		.table-bordered > thead > tr > th,
		.table-bordered > thead > tr > td {
			border-bottom-width: 2px;
		}

		.table-striped > tbody > tr:nth-child(odd) > td,
		.table-striped > tbody > tr:nth-child(odd) > th {
			background-color: #f9f9f9;
		}
        .os_table > thead > tr > th,
		.os_table > tbody > tr > th,
		.os_table > tfoot > tr > th,
		.os_table > thead > tr > td,
		.os_table > tbody > tr > td,
		.os_table > tfoot > tr > td {
			padding: 6px 0;
			line-height: 1.42857143;
			vertical-align: top;
		}

		/* -------------------------------------
			Some Events Booking specific style
		------------------------------------- */

		.col_quantity {
			width : 12% ;
			text-align: center ;
		}
		.col_price {
			text-align: right ;
			width: 10% ;
		}

		.col_event_date {
			width: 17% ;
			text-align: center ;
		}
		.col_event {
			text-align: left ;
		}

		.title_cell
		{
			font-weight: bold;
		}
	</style>
</head>
<body class="">
<table border="0" cellpadding="0" cellspacing="0" class="body">
	<tr>
		<td>&nbsp;</td>
		<td class="container">
			<div class="content">
				<table class="main">
					<tr>
						<td class="wrapper">
							<?php echo $body; ?>
						</td>
					</tr>
				</table>

			</div>
		</td>
		<td>&nbsp;</td>
	</tr>
</table>
</body>
</html>