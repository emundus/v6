function translateService(sourceText){
	var translatedText;

    var data = {
            appId: 'Bearer ' + AzureToken,
            from: translator.from,
            to: translator.to,
            contentType: 'text/html',
            text: sourceText
        };

	jQuery.ajax({
        url: "https://api.microsofttranslator.com/V2/Ajax.svc/Translate",
        dataType: 'json',
        data: data,
		success: function (result) {
			translatedText = result;
		},
		error: function (xhr, textStatus, errorThrown) {
			translatedText = "ERROR : "+errorThrown;
		},
		async:false
	});
      
	return translatedText;
}


