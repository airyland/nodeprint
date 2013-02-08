/**
 * Router core
 */

var doc = document,
    host = document.location.host,
    optionalParam = /\((.*?)\)/g,
    namedParam = /(\(\?)?:\w+/g,
    splatParam = /\*\w+/g,
    escapeRegExp = /[\-{}\[\]+?.,\\\^$|#\s]/g;


var NPRouter = function(options) {
        options || (options = {});
        if(options.routes) this.routes = options.routes;
        this.options = options;
        this.options['bootstrap'] && this.options['bootstrap']();
        this.loading = $(this.options['loading']);
        this.initialize.apply(this, arguments);
    };



NPRouter.prototype = {
    initialize: function(options) {
        var linkList = $('a[data-router!=false]');
        var _this = this;
        this.history = new NPHistory();
        $(document).on('click', 'a[data-router!=false]', function(e) {

            var href = this.href;
            //console.log(href);
            if(host !== this.host) {
                this.target = '_blank';
                return;
            }
            if(_this.options.exclude) {
                if(_.find(_this.options.exclude, function(one) {
                    return doc.location.href.indexOf(one) !== -1;
                })) {
                    return;
                } else {
                    e.preventDefault();
                }
            }

            if(_this.options.block) {
                if(_.find(_this.options.block, function(one) {
                    return href.indexOf(one) !== -1;
                })) {
                    //console.log('block');
                    return;
                } else {
                    e.preventDefault();
                }
            }

            _this.options['init'] && _this.options['init']();
            var $this = $(this),
                use_router = !$this.data('router') || $this.data('router') !== 'false';
            if(use_router) {
                var href = $(this).attr('href'),
                    title = $this.data('title') ? $this.data('title') : $this.attr('title') ? $this.attr('title') : $this.text();
                _this.match(href, title);
            } else {
                //do nothing
            }
        });

        this.regList = this._getRegList();
        window.addEventListener('popstate', function(e) {
           // console.log(e);
            if($('.content').length > 0 && e.state && e.state.data) {
                $('.content').empty().append(e.state.data);
            }
        });
    },
    _routeToRegExp: function(route) {
        route = route.replace(escapeRegExp, '\\$&').replace(optionalParam, '(?:$1)?').replace(namedParam, function(match, optional) {
            return optional ? match : '([^\/]+)';
        }).replace(splatParam, '(.*?)');
        return new RegExp( /*'^' +*/ route + '$');
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
                //console.log('history: ' + _this.history.url + '和' + _this.leaveCallbackFunc.url + '不一样哦');
                if(_this.triggerLeaveCallback() === false) {
                    return;
                } else {
                    this.emptyLeaveCallback();
                }
                //_this.history._saveState();
            }

            this.getPage(url, title, true, matchCallback, args);

            var endTime = +new Date();
            //console.log('elapse time:' + (endTime - startTime));
        } else {
            //console.log('no match');
            //no match, redirect to the location
            document.location.href = url;
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
        //this.loading.slideDown();
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
                var data = store.get('page::' + url);
                if(data) {
                    callback.call(this, data);
                    return;
                }
            }
        }

        $.ajax({
            url: url,
            success: function(data) {
                if(useCache) {
                    store.set('page::' + url, data);
                }
                callback && callback.call(this, data);
            },
            error: function() {
                //console.log('页面不存在哦');
                _this.showError('页面不存在哦');
            }
        });
    }
};



var options = {
    loading: '#loading',
    siteName: 'NodePrint',
    bootstrap: function() {
        // if(!store.get('widgets')){
        // $.get('/api/site/widgets',function(data){
        //console.log(data)
        // store.set('widgets',data);
        //NPWidget.parseWidgets(data);
        //$(data).hide().appendTo('.sidebar');
        // })
        // }
    },
    init: function() {
        $('#node-tip').hide();
    },
    finish: function(url) {
        $('html, body').animate({
            scrollTop: 0
        }, 'fast');
        $('#node-tip').hide();
        track('/view/ajax');
        $('.mobile .top, #home').attr('href', url.replace(/#.*?$/,'') + '#body');
    },
    routes: {
        '/#home': 'getHomeTab',
        '/?tab=:tab': 'getHomeTab',
        '/t/:id': 'singleTopic',
        '/member/:name(/:category)(/?page=:page)': 'singleMember',
        '/page/:id/:page': 'singlePage',
        '/topic/:id': 'singleTopic',
        '/node(?for=:dofor)': 'chooseNode',
        '/node/:slug/add': 'addTopic',
        '/node/:slug': 'singleNode',
        '/t/search/:key(?page=:page)': 'search',
        '/messages(/?type=:type)':'messages'
    },
    exclude: [document.location.host + '/admin'],
    block: ['/messages/send','/signin'],
    getHomeTab: {
        enter: function() {
            //console.log('enter home');
            NPWidget.fetch('home');
            $('.post-guide').hide();
        },
        leave: function() {
            //console.log('leave home');
        }
    },
    messages:function(){

    },

    singleTopic: {
        enter: function() {
            NP.use(['js/np_comment.js', 'js/plugin/at.js'], function() {
           var data = ['admin'],
               $userNameNode = $('.cm-list>li>p>a.user-name');
           authorName = $('.post-info .post_author>img').attr('alt');
           data.push(authorName);
           $.unique($.merge(data, $.unique($.map($userNameNode, function(val, key) {
               return $(val).text();
           }))));
           $('#cm-box').atWho('@', {
               'data': data,
               'tpl': "<li data-value='${name}'><img src='/avatar/${name}/20'/> ${name}</li>"
           });
       }
            );
            NPWidget.fetch('topic');
            track('/view/ajax/topic');
        },
        leave: function() {
            if($.trim($('#cm-box').val()) !== '') {
                if(!confirm('您确认要放弃已经输入的回复吗？')) {
                    return false;
                }
                return true;
            }
        }
    },
    addTopic: {
        enter: function(url, slug) {
            //
            NPWidget.fetch('create_topic', true);
            $('#profile-box').hide();
        },
        leave: function() {
            $('#profile-box').show();
        },
        cache: true
    },
    singleMember: {
        enter: function() {
            NPWidget.fetch('member');
        }

    },
    singlePage: function(id, page) {
        cache: true
    },
    chooseNode: {
        cache: true
    },
    singleNode: {
        enter: function() {
            NPWidget.fetch('node');
            //track('/view/ajax/node');
            //console.log('enter node');
            NP.use(['/js/plugin/jquery.jeditable.mini.js', 'js/np_admin.js']);
        },
        cache: false
    }

}

//History manager
var NPHistory = function() {
        this._saveState();
    }

NPHistory.prototype = {
    pushState: function(state, title, href) {
        document.title = title;
        history.pushState(state, title, href);
    },
    _saveState: function() {
        this.title = document.title;
        this.url = document.location.href;
        this.pathname = document.location.pathname;
        //console.log('initialize history' + 'url:' + this.url);
    },
    restoreState: function() {
        this.pushState(null, this.title, this.url);
    }
}

var NPWidget = {
    use: function(widgets) {
        var selector = widgets.join(',');
        $(selector, '.sidebar').show().siblings().hide();
    },
    fetch: function(page, cache) {
        cache = cache ? cache : false;
        if(cache) {
            var data = store.get('widget:' + page);
            if(data) {
                NPWidget.parseWidgets(data);
                return;
            }
        }
        $.get('/api/site/widgets/' + page, function(data) {
            NPWidget.parseWidgets(data);
            cache && store.set('widget:' + page, data)
        });
    },
    parseWidgets: function(data) {
        if($('#profile-box').length > 0) {
            $('.sidebar').find('#profile-box').siblings().remove();
        }

        $('.sidebar').append(data);
        /*var $data=$(data),
            $widgets=$data.children();
            console.log($data)
            console.log($widgets.length)*/
    }
}



$(function() {
    var app = new NPRouter(options);
    //console.log(app);
    $('.search').submit(function(e) {
        e.preventDefault();
        var key = encodeURI($('#search').val().replace(/</g, '').replace(/>/g, '').replace(/\//g, '').replace(/\\/g, '')),
            url = '/t/search/' + key;
        app.getPage(url, key + '-search', true);
        track('topic search input', true);
    });
});