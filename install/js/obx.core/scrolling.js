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
			self.limits = [];
			self.jq = {};
			self.targets = [];
			self.hrefs = [];
			// jq sets
			self.jq.links = self.root.find('a[href*="#' + self.conf.hashvar + '"]');
			if (!self.jq.links.length) return false;
			// targets jq sets
			self.jq.links.each(function (i) {
				$this = $(this);
				self.targets[i] = false;
				var href = $this.attr('href');
				var explode = href.split('=');
				if (!explode[1]) return false;
				self.hrefs[i] = '#' + self.conf.hashvar + '=' + explode[1];
				var target = $('#' + (explode[1].split('&'))[0]);
				if (target.length) self.targets[i] = target;
				if (self.targets[i] != false) {
					self.limits.push(parseInt((self.targets[i].offset()).top, 10) - parseInt(self.conf.preWatchClass, 10));
				}
			});
			self.limits.push(Infinity);
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
			}
		});

		// events handlers
		var ehandlers = {
			hashchange: function () {
				if (location.hash)
					for (var i in self.hrefs) {
						if (location.hash == self.hrefs[i]) self.scrollTo(i);
					}
				;
			}, watcher: function () {
				var top = W.scrollTop() - 0;
				if (self.conf.watchFrom && top < (self.conf.watchFrom - 0)) {
					self.jq.links.removeClass(self.conf.watchClass);
					return true;
				}

				self.jq.links.removeClass(self.conf.watchClass);
				for (var k in self.limits) {
					if ((self.limits[k]) >= top) {
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
				if (location.hash == self.hrefs[index]) ehandlers.hashchange();
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
		if (!$.obx.tools.jqIsGeatThan(1, 7)) {
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