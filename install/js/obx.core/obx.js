/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 ** @license Affero GPLv3           **
 ** @mailto rootfavell@gmail.com    **
 *************************************/

if( typeof(obx) == 'undefined' ) obx = {};
(function(obx){
	obx.parseUrlParams = function(url, bParseHash) {
		bParseHash = !!bParseHash;
		var oTmpUrlHash = url.split('#');
		var paramsString = '';
		if(bParseHash) {
			paramsString = oTmpUrlHash[1];
		}
		else {
			paramsString = oTmpUrlHash[0];
		}
		paramsString = paramsString.replace(/^\??/, '&');
		var parser = /(?:^|&|\?)([^&=]+)=?([^&]*)/g;
		var rawUrlParams = [];
		paramsString.replace(parser, function($0, $1, $2, $3, $4) {
			console.log($1, $2);
			if($1) {
				rawUrlParams.push({name: $1, value: $2});
			}
		});
		return obx.getSerializedFormAsObject(rawUrlParams);
	};

	obx.getSerializedFormAsObject = function(arFormFields) {
		var getParentNodeByNameChain = function(obFieldsTree, arNameChain, depth) {
			var parentNode = obFieldsTree;
			for(var index=1; index<=depth; index++) {
				//var dumpCurParent = parentNode;
				var typeOfParentNode = typeof(parentNode[arNameChain[index-1]]);
				switch(typeOfParentNode) {
					// undefined по идее недостижимый случай, но на всякий вожарный
					case 'undefined':
						parentNode[arNameChain[index-1]] = {__itemsCount__: 0};
						break;
					case 'string':
						//сохраняем уже существующее значение ноды, если передано значение дочернего элемента
						// ситуация вида key1=val1&key1[]=val11
						//Хотя тот же php не разруливает такое, а сразу заменяет на массив
						// и на выходе остается только key1 = array(0 => val11) // val1 - будет уничтожен
						//var stringParentNodeBackup = parentNode[arNameChain[index-1]];
						//parentNode[arNameChain[index-1]] = {__itemsCount__: 1, __backup__: stringParentNodeBackup};
						parentNode[arNameChain[index-1]] = {__itemsCount__: 0};
						break;
				}
				parentNode = parentNode[arNameChain[index-1]];
			}
			return parentNode;
		};

		var obFieldsTree = {};
		for(var key in arFormFields) {
			if(!arFormFields.hasOwnProperty(key)) continue;
			var formParamName = arFormFields[key].name;
			var formParamValue = arFormFields[key].value;

			var arFormParamNameChain = formParamName.split('[');
			for(var depth=0; depth<arFormParamNameChain.length; depth++) {
				// removing last "]" from names
				if( arFormParamNameChain[depth][arFormParamNameChain[depth].length-1] == ']' ) {
					arFormParamNameChain[depth] = arFormParamNameChain[depth].substr(0, arFormParamNameChain[depth].length-1);
				}
				var curIndexName = arFormParamNameChain[depth];

				// makeing tree from name-chain
				if(arFormParamNameChain.length - depth == 1) {
					var parentNode = null;
					if(curIndexName == '') {
						parentNode = getParentNodeByNameChain(obFieldsTree, arFormParamNameChain, depth);
						parentNode.__itemsCount__++;
						parentNode[parentNode.__itemsCount__] = formParamValue;
					}
					else {
						parentNode = getParentNodeByNameChain(obFieldsTree, arFormParamNameChain, depth);
						parentNode[curIndexName] = formParamValue;
					}
					break;
				}
				else {
					parentNode = getParentNodeByNameChain(obFieldsTree, arFormParamNameChain, depth);
					if( parentNode[curIndexName] == undefined ) {
						parentNode[curIndexName] = {__itemsCount__: 0};
					}

				}
			}
		}
		var removeItemsCount = function(obTree) {
			for(var key in obTree) {
				if(!obTree.hasOwnProperty(key)) continue;
				if(key == '__itemsCount__') {
					delete obTree[key];
				}
				else if(obTree[key].constructor == Object) {
					removeItemsCount(obTree[key]);
				}
			}
			return obTree;
		};
		removeItemsCount(obFieldsTree);
		return obFieldsTree;
	};
})(obx);

if(typeof(jQuery) == 'undefined') jQuery = false;
(function($) {
	if(!$) return;

	/*** NAMESPACE ***/
	$.obx = $.obx || {};

	/*** TOOLS ***/
	$.obx.tools = {
		isNumber : function (n){
			return typeof n === 'number' && isFinite(n);
		}
		,isInteger : function(n){
			return typeof n === 'number' && n % 1 == 0;
		}
		,isFloat : function (n){
			return typeof n === 'number' && Math.abs(n) % 1 > 0;
		}
		,isNull : function(n){
			return n === null;
		}
		,isObject : function(o){
			return o!==null && typeof(o) === 'object';
		}
		,isJQset : function(o){
			if(o && o!==null && typeof(o) === 'object' && o.jquery && o.length) return true;
			else return false;
		}
		,isJQtmpl : function(o){
			if(o && o!==null && typeof(o) === 'object' && o.key && o.data) return true;
			else return false;
		}
		,isBoolean: function(b){
			return typeof b === 'boolean';
		}
		,isString: function(str){
			return typeof(str)=='string';
		}
		,isUndefined: function(v){
			return typeof(v) == 'undefined';
		}
		,isEmpty: function(str){
			return (!str || 0 === str.length);
		}
		,isArray : function (arr){
			return typeof(arr)=='object'&&(arr instanceof Array);
		}
		,arraySortAsint : function(a, b){
			return a - b
		}
		,arraySortAsIntStr : function(a, b){
			if(a === b) return 0;
			if(typeof a === typeof b) return a < b ? -1 : 1;
			return typeof a < typeof b ? -1 : 1;
		}
		,jqIsGeatThan : function(major, minor){
			var explode = $.fn.jquery.split('.');
			var access = false;
			if(major<=(explode[0]-0)) access = true;
			if(access && minor<=(explode[1]-0)) access = true;
			else access = false;
			return access;
		}
	};



	/*** AGENT ***/
	$.obx.ieVersion = false;
	if($.browser.msie) $.obx.ieVersion = parseInt($.browser.version, 10);


	/*** HTML5 ***/
	if(typeof(Modernizr) != 'undefined'){

		// Placeholder analog
		if(!Modernizr.input.placeholder){
			var focusHandler = function(){
				var $this = $(this);
				var value = $this.val();
				var placeholder = $this.attr('placeholder');
				if(value==placeholder) $this.val('');
			};
			var focusoutHandler = function(){
				var $this = $(this);
				var value = $this.val();
				var placeholder = $this.attr('placeholder');
				if(!value || value=='') $this.val(placeholder);
			};
			var $placeholder = $('input[placeholder], textarea[placeholder]');
			$placeholder.on('focus', focusHandler);
			$placeholder.on('focusout', focusoutHandler);
			$placeholder.each(focusoutHandler);
		}

	}


})(jQuery);