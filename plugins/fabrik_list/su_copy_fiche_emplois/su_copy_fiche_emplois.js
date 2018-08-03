define(['jquery', 'fab/list-plugin'], function (jQuery, FbListPlugin) {
	var FbListSu_copy_fiche_emplois = new Class({
		Extends   : FbListPlugin,
		initialize: function (options) {
			this.parent(options);
		}
	});

	return FbListSu_copy_fiche_emplois;
});