if(typeof(jQuery) == 'undefined') jQuery = false;
(function($){
    if(!$) return false;

	// default conf
	var defaults = {
		api: 			true
		,hashvar: 		'scroll' // is a var name in a location.hash. For example: www.someurl.com/#scroll=footer
		,stopBefore:	80 // not reach this distance
		,duration: 		500 // scrolling`s duration
        ,watch:			true
		,watchClass:	'on-display'
		,watchFrom:		50 // start watch after Xpx from top
		,watchTo:		false // end watch after Xpx from top, may be =false - it meen Infinity
		,preWatchClass:	300 // добавлять класс watchClass заренее (недоходя) Xpx
	};

	// public functions
	// public vars

	// constructor
	function OBX_Scrolling(root, conf){
		// current instance
		var self = this;

		// private functions
		// private vars
		var jq={}, targets=[], hrefs=[], W=$(window), limits=[];

		// jq sets
		jq.links=root.find('a[href*="#'+conf.hashvar+'"]');
			if(!jq.links.length) return false;
		// targets jq sets
		jq.links.each(function(i){
			$this = $(this);
			targets[i]=false;
			var href = $this.attr('href');
			var explode = href.split('=');
			if(!explode[1]) return false;
			hrefs[i] = '#'+conf.hashvar+'='+explode[1];
			var target = $('#'+(explode[1].split('&'))[0]);
			if(target.length) targets[i] = target;
			limits.push( parseInt((targets[i].offset()).top, 10)-parseInt(conf.preWatchClass, 10) );
		});
		limits.push(Infinity);

		// api
		$.extend(self, {
			getTargetTop : function(index){
				if(targets[index]){
					return parseInt((targets[index].offset()).top);
				} else return false;
			}
			,scrollTo : function(index){
				var top = self.getTargetTop(index);
				if(!top) return false;
				var scroll = (top-(conf.stopBefore));
				if(scroll==(W.scrollTop()-0)) return true;
				// animate
				$("html,body").animate({"scrollTop": scroll}, conf.duration);
			}
		});

		// events handlers
		var ehandlers = {
			hashchange : function(){
				if(location.hash)
				for(var i in hrefs){
					if(location.hash==hrefs[i]) self.scrollTo(i);
				};
			}
			,watcher : function(){
				var top = W.scrollTop()-0;
				if(conf.watchFrom && top < (conf.watchFrom-0) ){
					jq.links.removeClass(conf.watchClass);
					return true;
				}
				for(var k in limits){
					if((limits[k]-0)>=top){
						k = k-0;
						jq.links.removeClass(conf.watchClass);
						k>0 ? k-- : k=0; // index control
						jq.links.eq(k).addClass(conf.watchClass);
						return true;
					}
				};
				return true;
			}
		};

		// events implementation
		W.on('hashchange', ehandlers.hashchange);
			ehandlers.hashchange(); // onload start
		if(conf.watch && conf.watchClass){
			W.on('scroll', ehandlers.watcher);
			ehandlers.watcher(); // onload start
		}

		jq.links.on('click', function(){ // if link`s hash already set
			if(location.hash==$(this).attr('href')) ehandlers.hashchange();
		});

		// complete object
		return self;
	}



	// jQuery prototype implementation
	$.fn.OBX_Scrolling = function(conf){
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
		// jq set
		if(!this.length) return false;
		// if already constructed --> return API
		var el = this.data("OBX_Scrolling");
		if (el) { return el; }
		conf = $.extend(true, {}, defaults, conf);
		// each
		this.each(function() {
			var $this = $(this);
			el = new OBX_Scrolling($this, conf);
			$this.data("OBX_Scrolling", el);
		});
		return conf.api ? el: this;
	};

})(jQuery);