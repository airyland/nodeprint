/**
 * NodePrint loader
 * for better JS module management
 * @author airyland <i@mao.li>
 * @lastmodified 2013.01.23
 */
(function($) {
	$.browser = {};
	$.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());
	$.chainclude = function(urls, finaly) {
		var onload = function(callback, data) {
				if(typeof urls.length != 'undefined') {
					if(urls.length == 0) return $.isFunction(finaly) ? finaly(data) : null;
					urls.shift();
					return $.chainclude.load(urls, onload);
				}
				for(var item in urls) {
					urls[item](data);
					delete urls[item];
					var count = 0;
					for(var i in urls)
					count++;
					return(count == 0) ? $.isFunction(finaly) ? finaly(data) : null : $.chainclude.load(urls, onload);
				}
			}
		$.chainclude.load(urls, onload);
	};
	$.chainclude.load = function(urls, onload) {
		if(typeof urls == 'object' && typeof urls.length == 'undefined') for(var item in urls)
		return $.include.load(item, onload, urls[item].callback);
		urls = $.makeArray(urls);
		$.include.load(urls[0], onload, null);
	};
	$.include = function(urls, finaly) {
		var luid = $.include.luid++;
		var onload = function(callback, data) {
				if($.isFunction(callback)) callback(data);
				if(--$.include.counter[luid] == 0 && $.isFunction(finaly)) finaly();
			}
		if(typeof urls == 'object' && typeof urls.length == 'undefined') {
			$.include.counter[luid] = 0;
			for(var item in urls)
			$.include.counter[luid]++;
			return $.each(urls, function(url, callback) {
				$.include.load(url, onload, callback);
			});
		}
		urls = $.makeArray(urls);
		$.include.counter[luid] = urls.length;
		$.each(urls, function() {
			$.include.load(this, onload, null);
		});
	}
	$.extend(
	$.include, {
		luid: 0,
		counter: [],
		load: function(url, onload, callback) {
			if($.include.exist(url)) return onload(callback);
			if(/.css$/.test(url)) $.include.loadCSS(url, onload, callback);
			else if(/.js$/.test(url)) $.include.loadJS(url, onload, callback);
			else $.get(url, function(data) {
				onload(callback, data)
			});
		},
		loadCSS: function(url, onload, callback) {
			var css = document.createElement('link');
			css.setAttribute('type', 'text/css');
			css.setAttribute('rel', 'stylesheet');
			css.setAttribute('href', url);
			$('head').get(0).appendChild(css);
			$.browser.msie ? $.include.IEonload(css, onload, callback) : onload(callback); //other browsers do not support it
		},
		loadJS: function(url, onload, callback) {
			var js = document.createElement("script");
			js.setAttribute("defer", true);
			var sp = /charset=([^&]+)/ig.exec(url);
			if(sp) js.setAttribute("charset", sp[1]); //带参数charset=xxx 设置属性charset
			//js.setAttribute("src",url);
			js.src = url;
			$.browser.msie ? $.include.IEonload(js, onload, callback) : js.onload = function() {
				onload(callback);
			};
			$('head').get(0).appendChild(js);
		},
		IEonload: function(elm, onload, callback) {
			elm.onreadystatechange = function() {
				if(this.readyState == 'loaded' || this.readyState == 'complete') onload(callback);
			};
		},
		exist: function(url) {
			var fresh = false;
			$('head script').each(

			function() {
				if(/.css$/.test(url) && this.href == url) return fresh = true;
				else if(/.js$/.test(url) && this.src == url) return fresh = true;
			});
			return fresh;
		}
	});


})(jQuery);

if(typeof NP == "undefined" || !NP) {
	var NP = {};
}

NP.use = function(module, callBack) {
	$.include(module, callBack);
}

NP.chainUse = function(module,callBack) {
	$.chainclude(module, callBack);
}

NP.config={
	log:true,
	widgetCache:false
}

NP.run=function(){
	
}

NP.requireDestop=function(){
    if(NPINFO.isMobile){
        return;
    }
}