function translateService(sourceText){
	var translatedText;

    var data = {
            key: YandexKey,
            lang: translator.from + '-' + translator.to,
            format: 'html',
            text: sourceText
        };

	jQuery.ajax({
        url: "https://translate.yandex.net/api/v1.5/tr.json/translate",
        dataType: 'json',
        data: data,
		success: function (result) {
			translatedText = result.text[0];
		},
		error: function (xhr) {
			translatedText = "ERROR "+xhr.responseJSON["code"]+": "+xhr.responseJSON["message"];
		},
		async:false
	});
      
	return translatedText;
}