if (typeof(jQuery) == 'undefined') jQuery = false;
(function ($) {
	if (!$) return false;

	// default conf
	var defaults = {
		api: true, hashvar: 'scroll' // is a var name in a location.hash. For example: www.someurl.com/#scroll=footer
		, stopBefore: 80 // not reach this distance
		, duration: 500 // scrolling`s duration
		, watch: true, watchClass: 'on-display', watchFrom: 50 // start watch after Xpx from top
		, watchTo: false // end watch after Xpx from top, may be =false - it meen Infinity
		, preWatchClass: 300 // добавлять класс watchClass заренее (недоходя) Xpx
	};

	// public functions
	// public vars

	// constructor
	function DVT_Scrolling(root, conf) {
		// current instance
		var self = this;
		self.root = root;
		self.conf = conf;
		// private functions
		// private vars
		var W = $(window);
		self.setup = function () {
			//self.limits = [];
			self.jq = {};
			self.targets = [];
			self.target_ids = [];
			// jq sets
			//self.jq.links = self.root.find('a[href*="#' + self.conf.hashvar + '"]');
			self.jq.links = self.root.find('a[href*="#"]');
			if (!self.jq.links.length) return false;
			// targets jq sets
			self.jq.links.each(function (i) {
				var $this = $(this);
				self.targets[i] = false;
				var href = $this.attr('href');
				var hashParams = obx.parseUrlParams(href, true);
				var typeOfHashParam = typeof(hashParams[self.conf.hashvar]);
				if( typeOfHashParam != 'string' ) {
					return false;
				}
				self.target_ids[i] = hashParams[self.conf.hashvar];
				var target = $('#' + hashParams[self.conf.hashvar]);
				if (target.length) self.targets[i] = target;
				//if (self.targets[i] != false) {
					// Незьзя заранее расчитывать координаты, они могу измениться
					//self.limits.push(parseInt((self.targets[i].offset()).top, 10) - parseInt(self.conf.preWatchClass, 10));
				//}
			});
			//self.limits.push(Infinity);
			return true;
		};
		self.setup();

		// api
		$.extend(self, {
			getTargetTop: function (index) {
				if (self.targets[index]) {
					return parseInt((self.targets[index].offset()).top);
				} else return false;
			}, scrollTo: function (index) {
				var top = self.getTargetTop(index);
				if (!top) return false;
				var scroll = (top - (conf.stopBefore));
				if (scroll == (W.scrollTop() - 0)) return true;
				// animate
				$("html,body").animate({"scrollTop": scroll}, self.conf.duration);
			},
			getTargetPositions: function() {
				var positions = [];
				var offsetTop = 0;
				for(var k in self.targets) {
					if(!self.targets.hasOwnProperty(k)
						|| false === self.targets[k]
						|| self.targets[k].length < 1
						) continue;
					offsetTop = parseInt((self.targets[k].offset()).top, 10);
					positions.push(offsetTop - parseInt(self.conf.preWatchClass, 10));
				}
				positions.push(Infinity);
				return positions;
			}
		});

		// events handlers
		var ehandlers = {
			hashchange: function () {
				var hashParamValue = obx.parseUrlParams(location.hash, true)[self.conf.hashvar];
				if (hashParamValue)
					for (var i in self.target_ids) {
						if (hashParamValue == self.target_ids[i]) self.scrollTo(i);
					}
				;
			}, watcher: function () {
				var top = W.scrollTop() - 0;
				if (self.conf.watchFrom && top < (self.conf.watchFrom - 0)) {
					self.jq.links.removeClass(self.conf.watchClass);
					return true;
				}
				self.jq.links.removeClass(self.conf.watchClass);
				var positions = self.getTargetPositions();
				for (var k in positions) {
					if (positions[k] >= top) {
						k = k | 0;
						k > 0 ? k-- : k = 0; // index control
						self.jq.links.eq(k).addClass(self.conf.watchClass);
						return true;
					}
				}
				;
				return true;
			}
		};

		// events implementation
		W.on('hashchange', ehandlers.hashchange);
		ehandlers.hashchange(); // onload start
		if (self.conf.watch && self.conf.watchClass) {
			W.on('scroll', ehandlers.watcher);
			ehandlers.watcher(); // onload start
		}

		self.jq.links.each(function (index, value) {
			$(this).on('click', function () { // if link`s hash already set
				var hashParamValue = obx.parseUrlParams(location.hash, true)[self.conf.hashvar];
				if (hashParamValue == self.target_ids[index]) ehandlers.hashchange();
			});
		});

		// complete object
		return self;
	}


	// jQuery prototype implementation
	$.fn.DVT_Scrolling = function (conf) {
		// jq namespace
		if (!$.hasOwnProperty('obx')) {
			console.log('Needs main script obx!');
			return false;
		}
		// jq version
		if (!$.obx.tools.jqIsGreatThan(1, 7)) {
			console.log('JQuery version is not enough (need > 1.7)!');
			return false;
		}
		// jq set
		if (!this.length) return false;
		// if already constructed --> return API
		var el = this.data("DVT_Scrolling");
		if (el) {
			return el;
		}
		conf = $.extend(true, {}, defaults, conf);
		// each
		this.each(function () {
			var $this = $(this);
			el = new DVT_Scrolling($this, conf);
			$this.data("DVT_Scrolling", el);
		});
		return conf.api ? el : this;
	};

})(jQuery);