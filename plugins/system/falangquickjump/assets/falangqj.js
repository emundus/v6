jQuery( document ).ready(function() {
    $('#quickModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);// Button that triggered the modal
        var dataUrl = button.data('url');// Extract info from data-* attributes
        jQuery('#quickModal').find('iframe').attr('src',dataUrl);
    });
    $('#quickModal').on('hidden.bs.modal', function (event) {
        jQuery('#quickModal').find('iframe').attr('src','about:blank');
    });
});

(jQuery)(function() {
  var tbl;
  if (typeof jFalangTable === 'undefined') {
    return;
  }

  tbl = jQuery(jFalangTable.tableselector);

  if (tbl.length > 0) {
    tbl = tbl[0];
  } else {
    //search for alternative tables but for now we quit here
    return;
  }

  var tblHead = jQuery(tbl).find('tHead');
  var tblHeadSpan = 1;
  var tblBody = jQuery(tbl).find('tBody');
  var tblBodyOffset = 0;
  var row;
  var tmp;
  var i;

  if (tblHead.length == 0) {
    tblHead = tblBody;
    tblBodyOffset = 1;
  } else {
    tblHeadSpan = tblHead.length;
  }

  var position = jQuery(tblHead[0].children[0]).find('th').length-1;

  if (jFalangTable.columnselector != '' && jFalangTable.columnselector > -1) {
    position = jFalangTable.columnselector;
  } else {
    for(i=0; i<position; i++) {
      tmp = jQuery(tblHead[0].children[0].children[i]).find('a');
      if (tmp.length > 0 && tmp.html().trim() == Joomla.Text._('JSTATUS')) {
        position = i;
        break;
      }
    }
    // If we didn't find the status we search for the title
    if (position == jQuery(tblHead[0].children[0]).find('th').length-1) {
      for(i=0; i<position; i++) {
        tmp = jQuery(tblHead[0].children[0].children[i]).find('a');
        if (tmp.length > 0 && tmp.html().trim() == Joomla.Text._('JGLOBAL_TITLE')) {
          position = i;
          break;
        }
      }
    }
  }

  //find col span later
  var rowposition = position;
  var th = jQuery('<th width="10%" rowspan="'+tblHeadSpan+'" class="nowrap center"><a href="#">'+Joomla.Text._('LIB_FALANG_TRANSLATION')+'</a></th>');
  th.insertAfter(tblHead[0].children[0].children[position]);

  jQuery.each(tblBody[0].children, function(line, item){
    if (line < tblBodyOffset) {
      return;
    }
    row = falang[line-tblBodyOffset];
    if (!row) {
      return;
    }

    var td = jQuery('<td class="center"></td>');

    var btngroup = jQuery('<div class="btn-group"/>');
    if (row['hide'] != 'true') {
      var i = 0;
      var perrow = 0;
      for (a in row['status']) {
        if (row['status'].hasOwnProperty(a)) {
          perrow++;
        }
      }
      if (perrow > 3) {
        perrow = Math.ceil(perrow/2);
      }

      jQuery.each(row['status'], function(lang, status) {
        if (++i > perrow) {
          i=0;
          btngroup.appendTo(td);
          btngroup = jQuery('<div class="btn-group" style="margin: 1px 0" />');
        }

          //state|publish
          var res = status.split("|");
          var state = res[0];
          var publish = res[1];

          var stateCss = '';
          //status
          //-1 not exist, 0 old , 1 uptodate
          switch (state)
          {
              case "-1":stateCss = 'notexist';break;
              case "0":stateCss = 'old';break;
              case "1":stateCss = 'uptodate';break;

          }
          var statePublish = '';
          switch (publish)
          {
            case "":statePublish = 'lang-unpublished';break;
            case "0":statePublish = 'lang-unpublished';break;
            case "1":statePublish = 'lang-published';break;
          }

          var height = jQuery(window).height() - 50;
          var width = jQuery(window).width() - 50;

          //var x = jQuery('<a rel="{size: {x: '+width+', y: '+height+'},handler:\'iframe\' ,closable: true}" href="'+row['link-'+lang]+'" data-toggle="modal" class="label quickjump quickmodal '+stateCss+'"><span rel="tooltip" data-original-title="'+lang+'">'+lang+'</span><span class="'+statePublish+'"></span></a>');
          var x = jQuery('<a data-bs-target="#quickModal" data-url="'+row['link-'+lang]+'" href="'+row['link-'+lang]+'" data-bs-toggle="modal" role="button" class="label quickjump quickmodal '+stateCss+'"><span rel="tooltip" data-original-title="'+lang+'">'+lang+'</span><span class="'+statePublish+'"></span></a>');

        x.appendTo(btngroup);
      });
    }

    btngroup.appendTo(td);


    td.insertAfter(item.children[rowposition]);
  });

});
