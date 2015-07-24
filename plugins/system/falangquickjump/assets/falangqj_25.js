window.addEvent('domready', function() {
  var tbl = $$('table.adminlist');

  if (tbl.length) {
    tbl = tbl[0];
  } else {
    return;
  }
  var tblHead = tbl.tHead;
  var tblHeadSpan = 1;
  var tblFoot = tbl.tFoot;
  var tblBody = tbl.tBodies[0];
  var tblBodyOffset = 0;
  var position;
  var rowposition;
  var row;

  if (!tblHead) {
    tblHead = tblBody;
    tblBodyOffset = 1;
  } else {
    tblHeadSpan = tblHead.rows.length;
  }

  var header = $(tblHead).getElements('th a');
  if (header.length == 0) {
    return;
  }

  Array.each(header, function(o, i) {
    if (o.text == 'Status') {
      position = o.getParent();
      rowposition = position.cellIndex;
    }
  });
  if (!position) {
    position = header.getLast().getParent();
    rowposition = (tblBody.rows[0].cells.length) - 1;
  }

  var th = document.createElement('th');
  var th = new Element('th', {
    html: 'Falang',
    width: 100,
    rowspan: tblHeadSpan
  } );
  th.inject(position, 'after');

  if (tblFoot) {
    for (var h=0; h<tblFoot.rows.length; h++) {
      tblFoot.rows[h].cells[0].colSpan = tblFoot.rows[h].cells[0].colSpan + 1;
    }
  }

  for (var i=tblBodyOffset; i<tblBody.rows.length; i++) {
    row = falang[i-tblBodyOffset];
    if (!row) {
      continue;
    }
    var td = new Element('td', {
      'class': 'center'
    });

    var ele = td;


    Object.each(row['status'], function(status,lang) {

    //state|publish
    var res = status.split("|");
    var state = res[0];
    var publish = res[1];

    var statusCss = '';
    //status
    //-1 not exist, 0 old , 1 uptodate
    switch (state)
    {
        case "-1":statusCss = 'notexist';break;
        case "0":statusCss = 'old';break;
        case "1":statusCss = 'uptodate';break;

    }

      var statePublish = '';
      switch (publish)
      {
        case "":statePublish = 'lang-unpublished';break;
        case "0":statePublish = 'lang-unpublished';break;
        case "1":statePublish = 'lang-published';break;
      }


    if (row['link'] != '') {
        var link = new Element('a', {
            'href': row['link'],
            'style': 'width:20px;',
            'class': 'label quickjump '+statusCss
        });
        link.inject(ele);
//        ele = link;
    }


      var statustag = new Element('span', {
          'rel': 'tooltip',
          'data-original-title':lang
      });

      statustag.innerHTML = lang;
      statustag.inject(link);

      var statuspublish = new Element('span', {
        'class': statePublish
      });

      statuspublish.inject(link);


    });

    // ICON PER LANGUAGE
    var childs = $(tblBody.rows[i]).getChildren('td');
    td.inject(childs[rowposition], 'after');
  }

});
