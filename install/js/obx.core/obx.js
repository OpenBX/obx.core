/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 **         Morozov P. Artem        **
 ** @license Affero GPLv3           **
 ** @mailto rootfavell@gmail.com    **
 ** @mailto tashiro@yandex.ru       **
 *************************************/

if(typeof(jQuery) == 'undefined') jQuery = false;
(function($) {
	if(!$) return false;

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