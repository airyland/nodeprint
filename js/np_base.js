/**
 * general functions
 * @author airyland <i@mao.li>
 */

var NP = NP || {};

/**
 * Google Analytics track function
 * @param string type either 'event' or 'page'
 * @param string key  event track key join by ' ' or url
 * @return void
 */
NP.track = function(type, key) {
    if (!NPINFO.ga) {
        return;
    }
    _gaq.push(['_setAccount', NPINFO.ga]);
    //event track
    if (type === 'event') {
        var track = key.split(' ');
        _gaq.push(['_trackEvent', track[0], track[1], track[2] ? track[2] : '']);
        NP.config.log && NP.log(track);
        //page track
    } else if (type === 'page') {
        key ? _gaq.push(['_trackPageview', key]) : _gaq.push(['_trackPageview']);
        NP.config.log && NP.log('track::page ' + key ? key : '');
    }
}

/**
 * Log function
 * @param string msg
 */

NP.log = function(msg) {
    NP.config.log && window.console && console.log(msg);
}

/**
 * dialog shake effect
 * @note require artdialog
 */
/**
var artDialog = artDialog || {};
artDialog.fn.shake = function() {
	var style = this.DOM.wrap[0].style,
		p = [4, 8, 4, 0, -4, -8, -4, 0],
		fx = function() {
			style.marginLeft = p.shift() + 'px';
			if(p.length <= 0) {
				style.marginLeft = 0;
				clearInterval(timerId);
			};
		};
	p = p.concat(p.concat(p));
	timerId = setInterval(fx, 13);
	return this;
};
**/


/**
 * get time diff
 * dirty but quick way
 */

function get_time_diff(earlierDate, laterDate) {
    var nTotalDiff = laterDate.getTime() - earlierDate.getTime(),
        oDiff = {};
    oDiff.days = Math.floor(nTotalDiff / 1000 / 60 / 60 / 24);
    oDiff.weeks = Math.floor(oDiff.days / 7);
    oDiff.months = Math.floor(oDiff.days / 31);
    nTotalDiff -= oDiff.days * 1000 * 60 * 60 * 24;
    oDiff.hours = Math.floor(nTotalDiff / 1000 / 60 / 60);
    nTotalDiff -= oDiff.hours * 1000 * 60 * 60;
    oDiff.minutes = Math.floor(nTotalDiff / 1000 / 60);
    nTotalDiff -= oDiff.minutes * 1000 * 60;
    oDiff.seconds = Math.floor(nTotalDiff / 1000);
    return oDiff;
}


function time_ago(time) {
    var dateCurrent = new Date(),
        oldDate = new Date(time),
        oDiff = get_time_diff(oldDate, dateCurrent);
    if (oDiff.days > 0) {
        if (oDiff.days >= 31) {
            return oDiff.months + ' ' + NP.lang.months;
        }
        if (oDiff.days >= 7 && oDiff.days <= 30) {
            return oDiff.weeks + ' ' + NP.lang.weeks;
        }
        return oDiff.days + ' ' + NP.lang.days + '';
    }

    if(oDiff.days === 0){
        
    }
    if (oDiff.days === 0 && oDiff.hours > 0) {
        return oDiff.hours + ' ' + NP.lang.hours + ' ' + ((oDiff.minutes > 0) ? oDiff.minutes + ' ' + NP.lang.minutes : '');
    }
    if (oDiff.days === 0 && oDiff.hours === 0 && oDiff.minutes > 0) return oDiff.minutes + ' ' + NP.lang.minutes;
    if (oDiff.days === 0 && oDiff.hours === 0 && oDiff.minutes === 0 && oDiff.seconds > 0) return oDiff.seconds + ' ' + NP.lang.seconds;
}


/**
 * get querystring
 */
$.queryString = (function(a) {
    if (a === "") return {};
    var b = {};
    for (var i = 0; i < a.length; ++i) {
        var p = a[i].split('=');
        if (p.length !== 2) continue;
        b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
    }
    return b;
})(window.location.search.substr(1).split('&'));

/**
 * NP Widget Management
 */

var NPWidget = {
    use: function(widgets) {
        var selector = widgets.join(',');
        $(selector, '.sidebar').show().siblings().hide();
    },
    fetch: function(page, cache, callback) {
        if (typeof cache === 'undefined') {
            if (NP.config.widgetCache) {
                cache = NP.config.widgetCache;
            } else {
                cache = false;
            }
        }
        if (cache) {
            var data = NPCache.get('widget:' + page, cache);
            if (data) {
                NPWidget.parseWidgets(data);
                return;
            }
        }
        $.get('/api/site/widgets/' + page, function(data) {
            NPWidget.parseWidgets(data);
            cache && NPCache.set('widget:' + page, data);
            callback && callback.call(this, data);
        });
    },
    parseWidgets: function(data) {
        if ($('#profile-box').length > 0) {
            $('.sidebar').find('#profile-box').siblings().remove();
        } else if ($('.login-box').length > 0) {
            $('.sidebar').find('.login-box').siblings().remove();
        } else {
            $('.sidebar').empty();
        }
        $('.sidebar').append(data);
    }
}

/**
 * NP Cache management
 */
var NPCache = {
    set: function(key, value) {
        store.set(key, value);
        store.set(this._getTimeKey(key), +new Date());
    },
    get: function(key, time) {
        var setTime = store.get(this._getTimeKey(key)),
            diff = (+new Date()) - setTime;
        console.log(setTime);
        console.log(diff);
        if (diff > time) {
            return false;
        } else {
            return store.get(key);
        }
    },
    clear: function() {
        store.clear();
    },
    cron: function() {

    },
    _getTimeKey: function(key) {
        return '_' + key + 'time';
    }
}

var NPTip = {
    hashMap: {
        'topic.deleted.success': '恭喜，帖子删除成功'
    },
    run: function() {
        var hash = document.location.hash.replace('#', '');
        if (hash && hash in this.hashMap) {
            $.dialog({
                title: false,
                cancel: false,
                time: 2,
                content: this.hashMap[hash]
            });
        }
    }
}