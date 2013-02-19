/**
 * Router core
 */

var doc = doc || document,
    win = win || window,
    $doc = $doc || $(doc),
    host = doc.location.host,
    optionalParam = /\((.*?)\)/g,
    namedParam = /(\(\?)?:\w+/g,
    splatParam = /\*\w+/g,
    escapeRegExp = /[\-{}\[\]+?.,\\\^$|#\s]/g,
    NPRouter = function(options) {
        options || (options = {});
        if(options.routes) this.routes = options.routes;
        this.options = options;
        this.options['bootstrap'] && this.options['bootstrap']();
        this.loading = $(this.options['loading']);
        this.initialize.apply(this, arguments);
    };


NPRouter.prototype = {
    initialize: function(options) {
        var _this = this;
        this.history = new NPHistory();
        $doc.on('click', 'a[data-router!=false]', function(e) {
            var href = this.href,
                $this = $(this),
                use_router = !$this.data('router') || $this.data('router') !== 'false';

            if(host !== this.host) {
                this.target = '_blank';
                return;
            }

            // handle excluded page
            if(_this.options.exclude) {
                if(_.find(_this.options.exclude, function(one) {
                    return doc.location.href.indexOf(one) !== -1;
                })) {
                    NP.log('route::page ' + href + 'excluded');
                    return;
                } else {
                    e.preventDefault();
                }
            }

            // handle blocked urls
            if(_this.options.block) {
                if(_.find(_this.options.block, function(one) {
                    return href.indexOf(one) !== -1;
                })) {
                    NP.log('route::url ' + href + 'blocked');
                    return;
                } else {
                    e.preventDefault();
                }
            }

            // ini action 
            _this.options['init'] && _this.options['init']();

            //match
            if(use_router) {
                var href = $(this).attr('href'),
                    title = $this.data('title') ? $this.data('title') : $this.attr('title') ? $this.attr('title') : $this.text();
                _this.match(href, title);
            }
        });

        // get reglist
        this.regList = this._getRegList();

        win.addEventListener('popstate', function(e) {
            if($('.content').length > 0 && e.state && e.state.data) {
                $('.content').empty().append(e.state.data);
            }
        });
    },
    _routeToRegExp: function(route) {
        route = route.replace(escapeRegExp, '\\$&').replace(optionalParam, '(?:$1)?').replace(namedParam, function(match, optional) {
            return optional ? match : '([^\/]+)';
        }).replace(splatParam, '(.*?)');
        return new RegExp(route + '$');
    },

    _extractParameters: function(route, fragment) {
        return route.exec(fragment).slice(1);
    },

    _getRouteList: function() {
        return _.keys(this.routes);
    },

    _getRegList: function() {
        var _this = this;
        return _.map(this._getRouteList(), function(route) {
            return _this._routeToRegExp(route);
        });
    },
    setLeaveCallback: function(url, func, args) {
        this.leaveCallbackFunc = {
            url: url,
            func: func,
            args: args
        }
    },
    emptyLeaveCallback: function() {
        this.leaveCallbackFunc = null;
    },
    triggerLeaveCallback: function() {
        var callback = this.leaveCallbackFunc;
        if(callback) {
            var returnValue = callback['func'].apply(this.args);
        }

        return returnValue;
    },
    match: function(url, title) {
        var _this = this;
        var startTime = +new Date();
        var match = _.find(this.regList, function(reg, index) {
            return reg.test(url);
        });

        if(match) {
            this.showLoading();
            var index = _.indexOf(this.regList, match),
                key = this._getRouteList()[index],
                args = [url].concat(this._extractParameters(match, url)),
                matchCallback = this.options[this.routes[key]];

            if(_this.leaveCallbackFunc && _this.history.pathname === _this.leaveCallbackFunc.url) {
                if(_this.triggerLeaveCallback() === false) {
                    return;
                } else {
                    this.emptyLeaveCallback();
                }
            }

            this.getPage(url, title, true, matchCallback, args);
        } else {
            NP.log('route::no match');
            doc.location.href = url;
        }
    },
    getPage: function(url, title, useLoading, matchCallback, args) {
        var _this = this,
            useLoading = typeof useLoading !== 'undefined' && useLoading === true ? true : false;
        useLoading && _this.showLoading();


        if(matchCallback) {
            if('leave' in matchCallback) {
                _this.setLeaveCallback(url, matchCallback['leave'], args);
            }
        }

        this.fetch(url, function(data) {
            $('.content').empty().prepend(data);
            if(matchCallback) {
                if('enter' in matchCallback) {
                    matchCallback['enter'].apply(this, args);
                }
            }
            _this.hideLoading();
            _this.history.pushState({
                data: data
            }, title + '-' + _this.options['siteName'], url);
            _this.history._saveState();
            useLoading && _this.hideLoading();
            _this.options['finish'] && _this.options['finish'](url);
        }, matchCallback);


    },
    showLoading: function() {
        this.loading.slideDown();
    },
    hideLoading: function(callback) {
        this.loading.slideUp('fast', function() {
            callback && callback.call(this);
        });
    },
    showError: function(error) {
        track('/view/404');
        var _this = this,
            $span = _this.loading.find('span').eq(0),
            text = $span.text();
        $span.text(error);
        setTimeout(function() {
            _this.hideLoading(function() {
                $span.text(text);
            });

        }, 3000);
    },
    fetch: function(url, callback, matchCallback) {
        var _this = this,
            useCache = false;

        if(matchCallback) {
            useCache = matchCallback['cache'];
            if(useCache) {
                var data = NPCache.get('page::' + url, useCache);
                if(data) {
                    callback.call(this, data);
                    return;
                }
            }
        }

        $.ajax({
            url: url,
            data: {
                format: 'html'
            },
            cache: false,
            success: function(data) {
                useCache && NPCache.set('page::' + url, data);
                callback && callback.call(this, data);
            },
            error: function() {
                _this.showError('页面不存在哦');
            }
        });
    }
};



/**
 * History manager
 */

var NPHistory = function() {
        this._saveState();
    }

NPHistory.prototype = {
    pushState: function(state, title, href) {
        doc.title = title;
        history.pushState(state, title, href);
    },
    _saveState: function() {
        this.title = doc.title;
        this.url = doc.location.href;
        this.pathname = doc.location.pathname;
    },
    restoreState: function() {
        this.pushState(null, this.title, this.url);
    }
}