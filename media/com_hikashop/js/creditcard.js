/**
 * @package    HikaShop for Joomla!
 * @version    4.7.3
 * @author     hikashop.com
 * @copyright  (C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
/**
 *
 * @author Obsidev
 * @baseOn John Gardner
 *
 * Documentation: http://en.wikipedia.org/wiki/Credit_card_number
 */
function hikashopValidateExpDate(month, year) {
	var cardexp = /^[0-9]{2}$/;
	if(!cardexp.exec(month) || !cardexp.exec(year))  {
		alert(ccHikaErrors[5]);
		return false;
	}

	month = parseInt(month);
	if(month > 12) {
		alert(ccHikaErrors[5]);
		return false;
	}

	var ccExpYear = 2000 + parseInt(year),
		ccExpMonth = month,
		today = new Date(),
		expDate = new Date(),
		ccExpDay = [31,((!(ccExpYear % 4 ) && ( (ccExpYear % 100 ) || !( ccExpYear % 400 ) ))?29:28),31,30,31,30,31,31,30,31,30,31][ccExpMonth-1];

	expDate.setFullYear(ccExpYear, ccExpMonth-1, ccExpDay);
	expDate.setMonth(ccExpMonth-1);
	expDate.setDate(ccExpDay);

	if(expDate < today) {
		alert(ccHikaErrors[5]);
		return false;
	}
	return true;
}

function hikashopCheckCreditCard(cardnumber) {
	var err = 0;
	for(z = 0; z < 16 ; z++) {
		var check = hikashopCheckOneTypeCreditCard(cardnumber, z);
		if(check === true)
			return true;
		if(check !== false)
			err = check;
	}
	alert(ccHikaErrors[err]);
	return false;
}

function hikashopCheckOneTypeCreditCard(cardnumber, cardType) {
	// Ensure that the user has provided a credit card number
	if(cardnumber.length == 0)
		return 3;

	// Now remove any spaces from the credit card number
	cardnumber = cardnumber.replace(/\s/g, "");

	// Check that the number is numeric
	var cardNo = cardnumber, cardexp = /^[0-9]{8,19}$/;
	if(!cardexp.exec(cardNo))
		return 3;

	// Define the cards we support. You may add addtional card types as follows.
	//  Name:         As in the selection box of the form - must be same as user's
	//  Length:       List of possible valid lengths of the card number for the card
	//  prefixes:     List of possible prefixes for the card
	//  checkdigit:   Boolean to say whether there is a check digit
	// see: https://en.wikipedia.org/wiki/Payment_card_number#Issuer_identification_number_.28IIN.29
	var cards = {
		0: {name: "Visa", length: "13,16,19", prefixes: "4", checkdigit: true},
		1: {name: "MasterCard", length: "16", prefixes: "51,52,53,54,55,2221,2222,2223,2224,2225,2226,2227,2228,2229,223,224,225,226,227,228,229,23,24,25,26,271,2720", checkdigit: true},
		2: {name: "DinersClub", length: "14,16", prefixes: "309,36,38,39,54,55", checkdigit: true},
		3: {name: "CarteBlanche",length: "14",prefixes: "300,301,302,303,304,305", checkdigit: true},
		4: {name: "AmEx", length: "15", prefixes: "34,37",checkdigit: true},
		5: {name: "Discover", length: "16", prefixes: "6011,622,64,65", checkdigit: true},
		6: {name: "JCB", length: "16,19", prefixes: "35", checkdigit: true},
		7: {name: "enRoute", length: "15", prefixes: "2014,2149", checkdigit: false},
		8: {name: "Solo", length: "16,18,19", prefixes: "6334,6767", checkdigit: true},
		9: {name: "Switch", length: "16,18,19", prefixes: "4903,4905,4911,4936,564182,633110,6333,6759", checkdigit: true},
		10: {name: "Maestro", length: "12,13,14,15,16,18,19", prefixes: "50,56,57,58,59,60,61,62,63,64,65,66,67,68,69", checkdigit: true},
		11: {name: "UATP", length: "15", prefixes: "1", checkdigit: true},
		12: {name: "LaserCard", length: "16,17,18,19", prefixes: "6304,6706,6771,6709", checkdigit: true},
		13: {name: "UnionPay", length: "16,17,18,19", prefixes: "62", checkdigit: true},
		14: {name: "Isracard", length: "8", prefixes: "0,1,2,3,4,5,6,7,8,9", checkdigit: false},
		15: {name: "Direct", length: "9", prefixes: "0,1,2,3,4,5,6,7,8,9", checkdigit: false},
		16: {name: "Bankcard", length: "16", prefixes: "5610,56022", checkdigit: true},
		17: {name: "China UnionPay", length: "16,17,18,19", prefixes: "62", checkdigit: true},
		18: {name: "InterPayment", length: "16,17,18,19", prefixes: "636", checkdigit: true},
		19: {name: "InstaPayment", length: "16", prefixes: "637,638,639", checkdigit: true},
		20: {name: "Laser", length: "16,17,18,19", prefixes: "6304,6706,6771,6709", checkdigit: true},
		21: {name: "Dankort", length: "16", prefixes: "5019", checkdigit: true},
		22: {name: "NSPK MIR", length: "16", prefixes: "2200,2201,2202,2203", checkdigit: true},
		23: {name: "Verve", length: "16,19", prefixes: "506,6500", checkdigit: true},
		24: {name: "CARDGUARD EAD BG ILS", length: "16", prefixes: "5392", checkdigit: true},
	};

	// Now check the modulus 10 check digit - if required
	if(cards[cardType].checkdigit) {
		// checksum: running checksum total
		// j: takes value of 1 or 2
		var checksum = 0, j = 1;
		for(i = cardNo.length - 1; i >= 0; i--) {
			// Extract the next digit and multiply by 1 or 2 on alternative digits.
			var calc = Number(cardNo.charAt(i)) * j;

			// If the result is in two digits add 1 to the checksum total
			if(calc > 9) {
				checksum++;
				calc -= 10;
			}

			// Add the units element to the checksum total
			checksum += calc;

			// Switch the value of j
			j = (j == 1) ? 2 : 1;
		}

		// All done - if checksum is divisible by 10, it is a valid modulus 10.
		// If not, report an error.
		if(checksum % 10 != 0)
			return 3;
	}

	// There is a specific algorithm to check on Isracard credit cards
	if(cards[cardType].name == "Isracard") {
		var sum = 0, iNum;
		for( var i in cardNo+='' ){
			iNum = parseInt(cardNo[i]);
			sum += i%2?iNum:iNum>4?iNum*2%10+1:iNum*2;
		}
		if(sum%10)
			return 3;
	}

	// The following are the card-specific checks we undertake.
	var LengthValid = false, PrefixValid = false;

	// We use these for holding the valid lengths and prefixes of a card type
	var prefix, lengths;

	// Load an array with the valid prefixes for this card
	prefix = cards[cardType].prefixes.split(",");

	// Now see if any of them match what we have in the card number
	for(i = 0; i < prefix.length; i++) {
		var exp = new RegExp("^" + prefix[i]);
		if(exp.test(cardNo))
			PrefixValid = true;
	}

	// If it isn't a valid prefix there's no point at looking at the length
	if(!PrefixValid)
		return 3;

	// See if the length is valid for this card
	lengths = cards[cardType].length.split(",");
	for(j = 0; j < lengths.length; j++) {
		if(cardNo.length == lengths[j])
			LengthValid = true;
	}

	// See if all is OK by seeing if the length was valid. We only check the length if all else was hunky dory.
	if(!LengthValid)
		return 3;

	// The credit card is in the required format.
	return true;
}
