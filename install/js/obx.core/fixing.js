if(typeof(jQuery) == 'undefined') jQuery = false;
(function($){
    if(!$) return false;

	// default conf
	var defaults = {
		api:true,
		fixClass:'fixed',
		fixCondition: function() { return true; }
	};

	// public functions
	// public vars
	var limits = {}; // limits



	// constructor
	function OBX_Fixing(target, top, conf){
		// current instance
		var self = this;

		// private functions
		// private vars
		var isfixed = false;

		// api
		$.extend(self, {
			addNewFixed : function(set){ // adds a new set to the current set for the limit
				if(!$.obx.tools.isJQset(set)) return false;
				set.each(function(){
					target.push($(this)[0]);
				});
				return self;
			},
			fix : function(){
				if( $.isFunction(conf.fixCondition) && conf.fixCondition() ) {
					target.addClass(conf.fixClass);
					isfixed = true;
				}
			},
			unfix : function(){
				target.removeClass(conf.fixClass);
				isfixed = false;
			}
		});

		// events handlers
		ehandlers = {
			scroll : function(){
				if($(window).scrollTop()>=top && !isfixed){
					self.fix();
				}else if($(window).scrollTop()<=top && isfixed){
					self.unfix();
				}
			}
		};

		$(window).on('scroll', ehandlers.scroll);
		ehandlers.scroll(); // onload start


		// complete object
		return self;
	};
	
	// jQuery prototype implementation
	$.fn.OBX_Fixing = function(top, conf, margin){
		top=parseInt(top);
		margin = parseInt(margin);
		if (top|0 == 0){
			top = $(this).offset().top;
		}
		if (margin|0 != 0) {
			top +=margin;
		}

		// jq namespace
		if(!$.hasOwnProperty('obx')){
			console.log('Needs main script obx!');
			return false;
		}
		// jq version
		if(!$.obx.tools.jqIsGeatThan(1, 7)){
			console.log('JQuery version is not enough (need > 1.7)!');
			return false;
		}
		// if already constructed --> return API and expand the set
		if(limits[top]) return limits[top].addNewFixed(this);
		// else:
		this.data("OBX_Fixing", true); // mark
		conf = $.extend(true, {}, defaults, conf);
		limits[top] = new OBX_Fixing(this, top, conf);
		return conf.api ? limits[top]: this;
	};
})(jQuery);