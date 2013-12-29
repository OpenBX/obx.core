/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 ** @license Affero GPLv3           **
 ** @mailto rootfavell@gmail.com    **
 *************************************/

if( typeof(obx) == 'undefined' ) {
	var obx = {};
}
obx.componentParams = {
	showTextArea: function(arParams) {
		// [fix bitrix bug]
		if( typeof(arParams.propertyID) == 'undefined' && typeof(arParams.popertyID) != 'undefined' ) {
			arParams.propertyID = arParams.popertyID;
		}
		// [/fix bitrix bug]
		console.log(arParams);
		options = arParams.data.split('||');
		console.log(options);
		arParams.oCont.innerHTML =
			'<textarea'
				+' id="'+arParams.propertyID+'_text"'
				+' name="'+arParams.propertyID+'"'
				+' cols="'+options[0]+'"'
				+' rows="'+options[1]+'"'
			+'>'
				+arParams.oInput.value
			+'</textarea>'
		;
	}
};
