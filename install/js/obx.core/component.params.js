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

	_fixBitrixBug: function(arParams) {
		// [fix bitrix bug]
		if( typeof(arParams.propertyID) == 'undefined' && typeof(arParams.popertyID) != 'undefined' ) {
			arParams.propertyID = arParams.popertyID;
		}
		// [/fix bitrix bug]
	}

	/**
	 * @deprecated - есть штатный тип FILE
	 * @param arParams
	 */
	,showTextArea: function(arParams) {
		this._fixBitrixBug(arParams);
		var options = arParams.data.split('||');
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

	,showRadioList: function(arParams) {
		this._fixBitrixBug(arParams);
		var data = null;
		eval('data = ' + arParams.data + ';');
		var inputsHtml = '';
		inputsHtml = ''
			+'<style type="text/css">'
				+ 'ul.bx-cmp-param-chooser {'
					+'list-style: none;'
					+'margin: 0;'
					+'padding: 0;'
				+'}'
				+'ul.bx-cmp-param-chooser li {'
					+'padding-bottom: 5px;'
				+'} '
				+'ul.bx-cmp-param-chooser li label.adm-designed-checkbox-label {'
					+'margin-right: 5px;'
				+'}'
			+'</style>'
			+'<ul class="bx-cmp-param-chooser">';
		var iChkbx = 0;
		if(data.MULTIPLE == 'Y') {
			for(var valueID in data.VALUES) {
				if( !data.VALUES.hasOwnProperty(valueID) ) continue;
				iChkbx++;
				inputsHtml += '<li>'
					+'<input'
						+' type="checkbox"'
						+' id="'+arParams.propertyID+'_chkbx_'+iChkbx+'"'
						+' name="'+arParams.propertyID+'[]"'
						+' value="'+valueID+'"'
						+' class="adm-designed-checkbox"'
						+' />'
					+'<label'
						+' for="'+arParams.propertyID+'_chkbx_'+iChkbx+'"'
						+' class="adm-designed-checkbox-label"'
						+'></label>'
					+'<label'
						+' for="'+arParams.propertyID+'_chkbx_'+iChkbx+'"'
						+'>'+data.VALUES[valueID]+'</label>'
				+'</li>';
			}
		}
		else {
			for(var valueID in data.VALUES) {
				if( !data.VALUES.hasOwnProperty(valueID) ) continue;
				iChkbx++;
				inputsHtml += '<li>'
					+'<input'
						+' type="radio"'
						+' id="'+arParams.propertyID+'_rdlist_'+iChkbx+'"'
						+' name="'+arParams.propertyID+'"'
						+' value="'+valueID+'"'
						+' />'
					+'<label for="'+arParams.propertyID+'_rdlist_'+iChkbx+'">'+data.VALUES[valueID]+'</label>'
				+'</li>';
			}
		}
		inputsHtml += '</ul>';
		arParams.oCont.innerHTML = inputsHtml;
	}
};
