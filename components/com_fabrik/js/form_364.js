requirejs(['fab/fabrik'], function () {
	Fabrik.addEvent('fabrik.form.group.delete.end', function (form, event) {
		var montant_idex=0;

for(i=0;i<100;i++){
  if($('jos_emundus_inov_budget_n1_522_repeat___montant_idex_'+i) !== null){
    tmp = parseFloat($('jos_emundus_inov_budget_n1_522_repeat___montant_idex_'+i).value.replace(/[^\d\][^.]/gi, ''));
    if(tmp === null || tmp==="" || isNaN(tmp)) {tmp=0;}
    montant_idex = parseFloat(montant_idex) + parseFloat(tmp);
  }
}

if(montant_idex === null || montant_idex==="" || isNaN(montant_idex)) {montant_idex=0;}

$('jos_emundus_inov_budget_n1___total_montant_idex').value=parseFloat(montant_idex);
$('jos_emundus_inov_budget_n1___total').value=parseFloat($('jos_emundus_inov_budget_n1___total_montant_idex').value)+parseFloat($('jos_emundus_inov_budget_n1___total_montant_idex1').value)+parseFloat($('jos_emundus_inov_budget_n1___total_investissement').value)+parseFloat($('jos_emundus_inov_budget_n1___total_amortissement').value);
		
		var montant_idex=0;

for(i=0;i<100;i++){
  if($('jos_emundus_inov_budget_n1_524_repeat___montant_idex_'+i) !== null){
    tmp = parseFloat($('jos_emundus_inov_budget_n1_524_repeat___montant_idex_'+i).value.replace(/[^\d\][^.]/gi, ''));
    if(tmp === null || tmp==="" || isNaN(tmp)) {tmp=0;}
    montant_idex = parseFloat(montant_idex) + parseFloat(tmp);
  }
}

if(montant_idex === null || montant_idex==="" || isNaN(montant_idex)) {montant_idex=0;}

$('jos_emundus_inov_budget_n1___total_montant_idex1').value=parseFloat(montant_idex); 

$('jos_emundus_inov_budget_n1___total').value=parseFloat($('jos_emundus_inov_budget_n1___total_montant_idex').value)+parseFloat($('jos_emundus_inov_budget_n1___total_montant_idex1').value)+parseFloat($('jos_emundus_inov_budget_n1___total_investissement').value)+parseFloat($('jos_emundus_inov_budget_n1___total_amortissement').value);

		var montant_idex=0;
		var montant_investissement=0;
for(i=0;i<100;i++){
  if($('jos_emundus_inov_budget_n1_525_repeat___montant_amortissement_'+i) !== null){
    tmp = parseFloat($('jos_emundus_inov_budget_n1_525_repeat___montant_amortissement_'+i).value.replace(/[^\d\][^.]/gi, ''));
    if(tmp === null || tmp==="" || isNaN(tmp)) {tmp=0;}
    montant_idex = parseFloat(montant_idex) + parseFloat(tmp);
	
	investissement = parseFloat($('jos_emundus_inov_budget_n1_525_repeat___montant_investissement_'+i).value.replace(/[^\d\][^.]/gi, ''));
    if(investissement === null || investissement==="" || isNaN(investissement)) {investissement=0;}
    montant_investissement = parseFloat(montant_investissement) + parseFloat(investissement);
  }
}

if(montant_idex === null || montant_idex==="" || isNaN(montant_idex)) {montant_idex=0;}
if(montant_investissement === null || montant_investissement==="" || isNaN(montant_investissement)) {montant_investissement=0;}

$('jos_emundus_inov_budget_n1___total_investissement').value=parseFloat(montant_investissement);
$('jos_emundus_inov_budget_n1___total_amortissement').value=parseFloat(montant_idex);

$('jos_emundus_inov_budget_n1___total').value=parseFloat($('jos_emundus_inov_budget_n1___total_montant_idex').value)+parseFloat($('jos_emundus_inov_budget_n1___total_montant_idex1').value)+parseFloat($('jos_emundus_inov_budget_n1___total_investissement').value)+parseFloat($('jos_emundus_inov_budget_n1___total_amortissement').value);

	});
})