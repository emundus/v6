/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
jQuery(function(a){var b=a("div#treeselectmenu").html();a(".treeselect li").each(function(){$li=a(this);$div=$li.find("div.treeselect-item:first");$li.prepend('<i class="pull-left icon-"></i>');
$div.after('<div class="clearfix"></div>');if($li.find("ul.treeselect-sub").length){$li.find("i").addClass("treeselect-toggle icon-minus");$div.find("label:first").after(b);
if(!$li.find("ul.treeselect-sub ul.treeselect-sub").length){$li.find("div.treeselect-menu-expand").remove();}}});a("i.treeselect-toggle").click(function(){$i=a(this);
if($i.parent().find("ul.treeselect-sub").is(":visible")){$i.removeClass("icon-minus").addClass("icon-plus");$i.parent().find("ul.treeselect-sub").hide();
$i.parent().find("ul.treeselect-sub i.treeselect-toggle").removeClass("icon-minus").addClass("icon-plus");}else{$i.removeClass("icon-plus").addClass("icon-minus");
$i.parent().find("ul.treeselect-sub").show();$i.parent().find("ul.treeselect-sub i.treeselect-toggle").removeClass("icon-plus").addClass("icon-minus");
}});a("#treeselectfilter").keyup(function(){var c=a(this).val().toLowerCase();a(".treeselect li").each(function(){if(a(this).text().toLowerCase().indexOf(c)==-1){a(this).hide();
}else{a(this).show();}});});a("#treeCheckAll").click(function(){a(".treeselect input").attr("checked","checked");});a("#treeUncheckAll").click(function(){a(".treeselect input").attr("checked",false);
});a("#treeExpandAll").click(function(){a("ul.treeselect ul.treeselect-sub").show();a("ul.treeselect i.treeselect-toggle").removeClass("icon-plus").addClass("icon-minus");
});a("#treeCollapseAll").click(function(){a("ul.treeselect ul.treeselect-sub").hide();a("ul.treeselect i.treeselect-toggle").removeClass("icon-minus").addClass("icon-plus");
});a("a.checkall").click(function(){a(this).parent().parent().parent().parent().parent().parent().find("ul.treeselect-sub input").attr("checked","checked");
});a("a.uncheckall").click(function(){a(this).parent().parent().parent().parent().parent().parent().find("ul.treeselect-sub input").attr("checked",false);
});a("a.expandall").click(function(){$parent=a(this).parent().parent().parent().parent().parent().parent().parent();$parent.find("ul.treeselect-sub").show();
$parent.find("ul.treeselect-sub i.treeselect-toggle").removeClass("icon-plus").addClass("icon-minus");});a("a.collapseall").click(function(){$parent=a(this).parent().parent().parent().parent().parent().parent().parent();
$parent.find("li ul.treeselect-sub").hide();$parent.find("li i.treeselect-toggle").removeClass("icon-minus").addClass("icon-plus");});});