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
obx.componentParams = obx.componentParams || {

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

	/**
	 *
	 * @param arParams
	 */
	,showListChooser: function(arParams) {
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
		var iCheckBox = 0;
		var chkBoxHandlers = null;
		if(data.MULTIPLE == 'Y') {
			chkBoxHandlers = {};
			for(var valueID in data.VALUES) {
				if( !data.VALUES.hasOwnProperty(valueID) ) continue;
				iCheckBox++;
				var checkBoxID = '__cmp_param_'+arParams.propertyID+'_chkbx_'+iCheckBox;
				var hiddenID = '__cmp_param_'+arParams.propertyID+'_data_'+iCheckBox;
				inputsHtml += '<li>'
					+'<input'
						+' type="hidden"'
						+' id="'+hiddenID+'"'
						+' name="'+arParams.propertyID+'[]"'
						+' />'
					+'<input'
						+' type="checkbox"'
						+' id="'+checkBoxID+'"'
						+' value="'+valueID+'"'
						+' class="adm-designed-checkbox"'
						+(data.IX_CUR_VALS.hasOwnProperty(valueID)?' checked="checked"':'')
						+' />'
					+'<label'
						+' for="'+checkBoxID+'"'
						+' class="adm-designed-checkbox-label"'
						+'></label>'
					+'<label'
						+' for="'+checkBoxID+'"'
						+'>'+data.VALUES[valueID]+'</label>'
				+'</li>';
				(function() {
					var chkbxID = checkBoxID;
					var hdnID = hiddenID;
					chkBoxHandlers[iCheckBox] = function() {
						var checkBox = document.getElementById(chkbxID);
						var hiddenInput = document.getElementById(hdnID);
						hiddenInput.value = checkBox.value;
						if(checkBox.checked) {
							hiddenInput.disabled = false;
						}
						else {
							hiddenInput.disabled = true;
						}
						checkBox.onchange = function() {
							hiddenInput.checked = this.checked;
							if(this.checked) {
								hiddenInput.disabled = false;
							}
							else {
								hiddenInput.disabled = true;
							}
							console.log(hiddenInput.value);
							console.log(hiddenInput.disabled);
						};
						console.log(hiddenInput.value);
						console.log(hiddenInput.disabled);
					};
				})();
			}

		}
		else {
			for(var valueID in data.VALUES) {
				if( !data.VALUES.hasOwnProperty(valueID) ) continue;
				iCheckBox++;
				inputsHtml += '<li>'
					+'<input'
						+' type="radio"'
						+' id="__cmp_param_'+arParams.propertyID+'_rdlist_'+iCheckBox+'"'
						+' name="'+arParams.propertyID+'"'
						+' value="'+valueID+'"'
						+((arParams.oInput.value == valueID)?' checked="checked"':'')
						+' />'
					+'<label for="__cmp_param_'+arParams.propertyID+'_rdlist_'+iCheckBox+'">'+data.VALUES[valueID]+'</label>'
				+'</li>';
			}
		}
		inputsHtml += '</ul>';
		arParams.oCont.innerHTML = inputsHtml;
		if(null !== chkBoxHandlers) {
			for(var i in chkBoxHandlers) {
				(chkBoxHandlers[i])();
			}
		}
	}
};
