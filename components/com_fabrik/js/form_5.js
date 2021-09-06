requirejs(['fab/fabrik'], function () {
 
/*
  // The block you want to use
  var blockRef = 'form_5';
 
  // Should we use an exact match for the blockRef?
  var exact = false;
 
  var form = Fabrik.getBlock(blockRef, exact, function (block) {
 
    // This callback function is run once the block has been loaded.
    // The variable 'block' refers to Fabirk.blocks object that controls the form.
	var el = block.elements.get('fab_cdd_test___city');
	el.spinner = new Spinner(block.form);
  });
  
  Fabrik.addEvent('fabrik.cdd.update', function (cdd) {
	  if (cdd.element.id === 'fab_cdd_test___city_auto') {
		  alert('CDD Changed!');
		  jQuery('input[name*=fab_cdd_test___city_auto]').each(function (x, i) { i.checked = false; })
	  }
  });
  */
	Fabrik.addEvent('fabrik.form.elements.added', function (form) {
		fconsole('form elements added for: ' + form.block);
	});
});