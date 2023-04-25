function translateService(sourceText){
	var translatedText;

    var data = [{
		    text:sourceText
     }];

    var endpoint = "https://api.cognitive.microsofttranslator.com/translate?api-version=3.0";
    endpoint = endpoint + '&from='+translator.from;
    endpoint = endpoint + '&to='+translator.to;
    endpoint = endpoint + '&textType=html';

	jQuery.ajax({
        url: endpoint,
        dataType: 'json',
        headers:  {
            'Ocp-Apim-Subscription-Key': azureKey,
            'Content-Type':'application/json; charset=UTF-8'
        },
        data: JSON.stringify(data),
        type: 'POST',
        success: function (result) {
            console.log(result);
			translatedText = result[0]['translations'][0].text;
		},
		error: function (xhr, textStatus, errorThrown) {
			translatedText = "ERROR : "+errorThrown;
		},
		async:false
	});
      
	return translatedText;
}



