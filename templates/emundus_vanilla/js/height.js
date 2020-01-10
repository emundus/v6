function setColumns() {
( function() {
if (jQuery(window).width() > 750) {
contentDiv = jQuery("#ttr_content");
contentMarginDiv = jQuery("#ttr_content_margin");
sidebar1Div = jQuery("#ttr_sidebar_left");
sidebar2Div = jQuery("#ttr_sidebar_right");
sidebar1MarginDiv = jQuery("#ttr_sidebar_left_margin");
sidebar2MarginDiv = jQuery("#ttr_sidebar_right_margin");
if (sidebar1Div != null)
{
if (contentDiv.height() > sidebar1Div.height())
{
if (sidebar2Div != null)
{
if (contentDiv.height() > sidebar2Div.height())
{
sidebar1MarginDiv.height(contentDiv.height() - 0 - 0 - 0 - 0);
sidebar2MarginDiv.height(contentDiv.height() - 0 - 0 - 0 - 0);
}
else
{
sidebar1MarginDiv.height(sidebar2Div.height() - 0 - 0 - 0 - 0);
var Height = sidebar2Div.height() - 20 - 70 - 0 - 0;
contentMarginDiv.css("min-height", Height+'px');
 }
}
else
{
sidebar1MarginDiv.height(contentDiv.height() - 0 - 0 - 0 - 0);
}
}
else
{
if (sidebar2Div != null)
{
if (sidebar1Div.height() > sidebar2Div.height())
{
sidebar2MarginDiv.height(sidebar1Div.height() - 0 - 0 - 0 - 0);
var Height = sidebar1Div.height() - 20 - 70 - 0 - 0;
contentMarginDiv.css("min-height", Height+'px');
}
else
{
sidebar1MarginDiv.height(sidebar2Div.height() - 0 - 0 - 0 - 0);
var Height = sidebar2Div.height() - 20 - 70 - 0 - 0;
contentMarginDiv.css("min-height", Height+'px');
}
}
else
{
var Height = sidebar1Div.height() - 20 - 70 - 0 - 0;
contentMarginDiv.css("min-height", Height+'px');
}
}
}
if (sidebar2Div != null)
{
if (contentDiv.height() > sidebar2Div.height())
{
if (sidebar1Div != null)
{
if (contentDiv.height() > sidebar1Div.height())
{
sidebar1MarginDiv.height(contentDiv.height() - 0 - 0 - 0 - 0);
sidebar2MarginDiv.height(contentDiv.height() - 0 - 0 - 0 - 0);
}
else
{
sidebar2MarginDiv.height(sidebar1Div.height() - 0 - 0 - 0 - 0);
var Height = sidebar1Div.height() - 20 - 70 - 0 - 0;
contentMarginDiv.css("min-height", Height+'px');
 }
}
else
{
sidebar2MarginDiv.height(contentDiv.height() - 0 - 0 - 0 - 0);
}
}
else
{
if (sidebar1Div != null)
{
if (sidebar2Div.height() > sidebar1Div.height())
{
sidebar1MarginDiv.height(sidebar1Div.height() - 0 - 0 - 0 - 0);
var Height = sidebar1Div.height() - 0 - 0 - 0 - 0;
contentMarginDiv.css("min-height", Height+'px');
}
else
{
sidebar2MarginDiv.height(sidebar1Div.height() - 0 - 0 - 0 - 0);
var Height = sidebar1Div.height() - 20 - 70 - 0 - 0;
contentMarginDiv.css("min-height", Height+'px');
}
}
else
{
var Height = sidebar2Div.height() - 20 - 70 - 0 - 0;
contentMarginDiv.css("min-height", Height+'px');
}
}
}
}
} )();
}
jQuery(window).on('load', function(){
setColumns();
});
