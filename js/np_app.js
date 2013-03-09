/**
 * app core
 * @author airyland@qq.com <i@mao.li>
 * 1. dom reference
 * 2.
 * 3.
 * 4.
 * 5.
 */



/**
 * 1. dom reference
 */
var doc = document,
    $doc = $(doc),
    $body = $('body'),
    href = location.href,
    hash = location.hash,
    isTopicPage = href.indexOf('/t/') !== -1,
    isNodeinfoPage = href.indexOf('/node/') !== -1;

/**
 * 2.initialize spinner
 */
var opts = {
    lines: 12,
    length: 5,
    width: 3,
    radius: 8,
    corners: 1,
    rotate: 0,
    color: '#FFF',
    speed: 1,
    trail: 60,
    shadow: false,
    hwaccel: false,
    className: 'spinner',
    zIndex: 2e9,
    top: '5px',
    left: 'auto'
},
    target = document.getElementById('loading'),
    spinner = new Spinner(opts).spin(target);




/**
 * 3. route nodeprint!
 */
var options = {
    loading: '#loading',
    siteName: 'NodePrint',
    bootstrap: function() {},
    init: function() {
        $('#node-tip').hide();
    },
    finish: function(url) {
        $('html, body').animate({
            scrollTop: 0
        }, 'fast');
        $('#node-tip').hide();
        $('.mobile .top, #back-to-top').attr('href', url.replace(/#.*?$/, '') + '#body');
        //track
        NP.track('event', 'View ajax');
        NP.track('page', url);
    },
    fail: function(url){
        NP.track('event','View 404 ' + url);
        $('#loading').hide();
        $.dialog({title:false,content:'页面不存在哦~'});
    },
    routes: {
        '/#home': 'getHomeTab',
        '/?tab=:tab': 'getHomeTab',
        '/t/:id': 'singleTopic',
        '/t/:id/edit':'editTopic',
        '/member/:name(/:category)(/?page=:page)': 'singleMember',
        '/page/:id/:page': 'singlePage',
        '/topic/:id': 'singleTopic',
        '/node(?for=:dofor)': 'chooseNode',
        '/node/:slug/add': 'addTopic',
        '/node/:slug': 'singleNode',
        '/t/search/:key(?page=:page)': 'search',
        '/messages(/?type=:type)': 'messages',
        '/signin':'signin'
    },
    exclude: [document.location.host + '/admin'],
    block: [],
    getHomeTab: {
        enter: function() {
            NPWidget.fetch('home');
            $('.post-guide').hide();
			NP.track('event','Home view ajax');
        },
        cache:50000
    },
    messages: {
        enter: function() {
            NP.track('event', 'Message view ajax');
        }
    },

    singleTopic: {
        enter: function() {
            NP.use(['js/np_comment.js', 'js/np_topic.js', 'js/plugin/at.js'], function() {
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
            });
            NPWidget.fetch('topic');
            NP.track('event','Topic view ajax');
        },
        leave: function() {
            $(window).unbind('scroll').unbind('resize');
            $doc.off('click','#cm-button');
            if($.trim($('#cm-box').val()) !== '') {
                if(!confirm('您确认要放弃已经输入的回复吗？')) {
                    NP.track('event', 'Reply keep');
                    return false;
                }
                NP.track('event', 'Reply abandon');
                return true;
            }
        }
    },
    addTopic: {
        enter: function(url, slug) {
            NP.track('event', 'Topic enterAddPage ajax');
            NPWidget.fetch('create_topic', true);
            $('#profile-box').hide();
        },
        leave: function() {
            $('#profile-box').show();
        },
        cache: 30000
    },
    editTopic:{
        enter:function(){
            NP.track('event', 'Topic enterEdit ajax');
            NPWidget.fetch('create_topic', true);
            $('#profile-box').hide();
        },
        leave:function(){
            $('#profile-box').show();
        }
    },
    singleMember: {
        enter: function(url, name) {
            NP.track('event', 'Member view ajax/' + name);
            NPWidget.fetch('member');
        }

    },
    singlePage: function(id, page) {
        NP.track('event', 'Page view ajax');
        cache: 200
    },
    chooseNode: {
		enter:function(){
		NP.track('event', 'Node chooseNode ajax');
		},
        cache: 30000000
    },
    singleNode: {
        enter: function() {
            NP.track('event', 'Node view ajax');
            NPWidget.fetch('node');
            NP.chainUse(['/js/plugin/jquery.jeditable.mini.js', 'js/np_admin.js']);
        },
        cache: 30000
    },
    signin: {

    }

}



$(function() {

    /**
     * do the route job
     */
    var app = new NPRouter(options);

    /*
     * manually route the search action
     */
    $('.search').submit(function(e) {
        e.preventDefault();
        var key = encodeURI($('#search').val().replace(/</g, '').replace(/>/g, '').replace(/\//g, '').replace(/\\/g, '')),
            url = '/t/search/' + key;
        app.getPage(url, key + '-search', true);
        NP.track('event', 'Topic search ajax/'+key);
    });

    // click topic list item
    $(document).on('click', '.topic-list>li', function (e) {
        if (e.target.nodeName !== 'A') {
            $(this).find('.post-title').trigger('click');
        }
    });

    //save last login user name 
    if(NPUSER.userName) {
        store.set('lastLoginName', NPUSER.userName);
    }
    //resore last login user name
    if(store.get('lastLoginName') && $('#user-name').length > 0) {
        if(document.location.href.indexOf('signup') === -1) {
            $('#user-name').val(store.get('lastLoginName'));
            // use setTimeout trick
            setTimeout(function() {
                $('#user-pwd')[0].focus();
            }, 500);
        }
    }

    // heart beat~
    $.get('/api/user?do=_get_online');
    // get online user
    $.get('/api/user?do=get_online_user', function(data) {
        if(data.error === 0) {
            $('#online').text(data.no + '位用户在线');
        }
    }, 'json')


    //the topic created long ago~
    if(/\/t\/\d/.test(href)) {
        var time = $('.time-txt').eq(0).text(),
            diff = (+new Date()) - (+new Date(time)),
            diffMonth = Math.floor(diff / 1000 / 60 / 60 / 24 / 30);
        if(diffMonth >= 2) {
            setTimeout(function() {
                $('<p class="long-ago-topic-tip">这是一个创建于<b>' + diffMonth + '</b>个月前的主题，其中的信息可能已经有所发展或是发生改变。</p>').insertBefore('.post-content').show();
            }, 1000)
        }
    }

    // deal with a[rel='external']
    $("a[rel='external']").attr('target', '_blank');

    //fix back to top link address when use <base> tag
    $('.mobile .top, #back-to-top').attr('href', href + '#body');

    //tipsy
    $('.unfav-node').tipsy({
        gravity: 's',
        fade: true
    });

    NPTip.run();

});



var topic = {
    id: 0,
    target: $('#do-fav'),
    listen: function() {
        $(document).on('click', '#do-fav', function(e) {
            e.preventDefault();
            topic.fav($(this).attr('href'));
        });
    },

    fav: function(url) {
        $.getJSON(url, '', function(res) {
            if(res.error == 0) {
                var unfav = url.indexOf('unfav') == -1,
                    add = unfav > 0 ? 1 : -1;
                $('#topic-fav-count').text(parseInt($('#topic-fav-count').text()) + add);
                (url.indexOf('unfav') == -1) ? $('#do-fav').attr('href', url.replace('fav', 'unfav')).attr('title', 'click to unfav').removeClass().addClass('fav-link') : $('#do-fav').attr('title', 'click to fav').attr('href', url.replace('unfav', 'fav')).removeClass().addClass('unfav-link');

            }
        });

    },
    init: function() {
        this.listen();
    }
}



var post = {
    tipsy: function() {
        $('.fav-link,.unfav-link,.share').tipsy({
            live: true,
            title: 'title',
            gravity: 's',
            fade: true
        });
    },
    share: function() {
        $(document).on('click', '.share', function(e) {
            e.preventDefault();
            window.open($(this).attr('href'), '_blank', 'width=550,height=370');
        });
    },
    init: function() {
        this.tipsy();
        this.share();
    }

}


$(function() {
    topic.init();
    post.init();

    /**
     * signup form validation
     */
    $('#register-form').validate({

        rules: {
            "user-name": {
                minlength: 3,
                maxlength: 10,
                remote: "api/user/0/check_username"
            },
            "user-email": {
                remote: "api/user/0/check_email"
            },
            "user-pwd": {
                minlength: 6,
                maxlength: 16
            }
        },
        messages: {
            "user-name": {
                remote: 'Sorry, the user name has been taken'
            },
            "user-email": {
                remote: 'Sorry, the email has been taken'
            }

        }
    });


});

$(function() {

    var signinDialog;
    $('#signin-btn,a[href="/signin"]').click(function(e) {
        if(NPINFO.isMobile) return;
        var history = new NPHistory();
        e.preventDefault();
        var $content = $('#signin-template'),
            title = $content.data('title');
        signinDialog = $.dialog({
            title: title,
            content: $content.html(),
            lock: true,
            fixed: true,
            opacity: 0.1,
            init: function() {
                $body.toggleClass('modal');
                $('#user-name').focus();
                if(store.get('lastLoginName')) {
                    $('#user-name').val(store.get('lastLoginName'));
                    $('#user-pwd').focus();
                }
                history.pushState(null, title, '/signin');
            },
            close: function() {
                $body.toggleClass('modal');
                history.restoreState();
            }

        });
    });

    /**
     * signin form validation
     */
    $doc.on('click', '#do-signin-btn', function(e) {
        e.preventDefault();
        var $tip = $('#signin-tip');
        if($('#user-name').val() === '' || $('#user-pwd').val() === '') {
            $tip.text('请填写用户名和密码');
            signinDialog.shake();
            return;
        }
        var data = $('#js-signin-form').serialize();
        $.post('/api/user/signin', data, function(e) {
            if(e.error !== 0) {
                $tip.text(e.data.message);
            } else {
                document.location.reload();
            }
        }, 'json');
    });



    if(hash === '#no-admin-rights') {
        $.dialog({
            title: '抱歉',
            content: '抱歉，您没有管理员权限，请以管理员身份重新登录',
            lock: true
        });
    }

    if(hash === '#email-confirm-done') {
        $.dialog({
            title: '恭喜',
            content: '恭喜，您已经完成邮箱验证',
            time: 2
        });
    }

    if(hash === '#email-confirm-fail') {
        $.dialog({
            title: '抱歉',
            content: '抱歉，邮箱验证失败',
            lock: true
        });
    }
    var apiRequest = function(url, target, form, sendingTitle, doneTitle) {
            $(target).click(function(e) {
                e.preventDefault();
                var isDelete = $(this).attr('class').indexOf('delete') !== -1 || $(this).attr('id').indexOf('delete') !== -1;
                //针对删除操提示
                if(isDelete) {
                    if(!confirm('确定删除吗')) {
                        return;
                    }
                }
                var $this = $(this);
                var tip = $.dialog({
                    title: '操作提示',
                    content: sendingTitle
                });
                var data = '';
                if(form !== '') {
                    data = $(form).serialize();
                } else {
                    data = $this.parent('form').serialize();
                }

                $.post('api/' + url, data, function(data) {
                    if(data.error === 0) {
                        tip.content(doneTitle + ',2秒后自动关闭');

                        //$.dialog({content:'更新中',time:2});
                    } else {
                        tip.content('操作失败：' + data.msg);
                    }

                    setTimeout(function() {
                        tip.close();
                    }, 2000);
                    $this.parent('form').parent('td').parent('tr').remove();


                }, 'json');
            });
        }

    apiRequest('site/update_config', '#update-config-btn', '#config-form', '更新中', '更新成功');
    apiRequest('site/update_config', '#update-config2-btn', '#config2-form', '更新中', '更新成功');
    apiRequest('nodes/add', '#add-node-btn', '#node-add-form', '添加节点中', '添加成功');
    apiRequest('nodes/delete', '.delete-node', '', '删除节点中', '删除成功');
    apiRequest('admin/add_admin', '#add-admin-btn', '#add-admin-form', '添加中', '添加成功');



    //tips
    //@todo smart position
    if(!NPINFO.isMobile) {
        var temp = '<p class="node-name">{{node_name}}[<b>{{node_post_no}}</b>]</p><p class="node-intro">{{node_intro}}</p>',
            parseTemp = function(data) {
                return temp.replace('{{node_name}}', data.node_name).replace('{{node_intro}}', (data.node_intro === "0" || data.node_intro === null) ? '该节点暂时没有介绍哦~' : data.node_intro).replace('{{node_post_no}}', data.node_post_no);
            },
            showTip = function(offset, data, height) {
                var nodeTip = $('#node-tip');
                height = height + 5;
                if(nodeTip.length > 0) {
                    nodeTip.hide().css({
                        left: offset.left,
                        top: offset.top + height
                    }).find('#inner-content').html(parseTemp(data)).end().show();
                } else {
                    $('<div class="node-tip" id="node-tip"><div class="tip-content"><span class="arrow1">◆</span><span class="arrow2">◆</span><div id="inner-content"></div></div><div>').appendTo('body').css({
                        left: offset.left,
                        top: offset.top + height
                    }).find('#inner-content').html(parseTemp(data)).end().show();
                }

            },
            hideTip = function() {
                $('#node-tip').hide();
            };


        $doc.on({
            'mouseenter': function() {
                var $this = $(this),
                    height = $this.outerHeight();
                this.title = "";
                this.tip = setTimeout(function() {
                    var offset = $this.offset(),
                        href = $this.attr('href'),
                        data;
                    //在缓存中找
                    if(store.get(href)) {
                        data = store.get(href);
                        showTip(offset, data, height);
                    } else {
                        $.get('/api' + href, {}, function(e) {
                            if(e.error === 0) {
                                store.set(href, e.info);
                                showTip(offset, e.info, height);
                            }
                        }, 'json');
                    }

                }, 200);
            },
            'mouseleave': function(e) {
                clearTimeout(this.tip);
                if(e.target.nodeName !== 'a') {
                    hideTip();
                }
            }
        }, '.node-list a,.post-node,a[href^="/node/"]');
    }

    $('.icon-list .icon,#file-upload,#preview li').tipsy({
        live: true,
        html: true,
        gravity: 'n',
        fade: true
    });


    var $commentBox = $('#cm-box');
    //ArrayBuffer is deprecated in XMLHttpRequest.send(). Use ArrayBufferView instead.

    function FileUpload(img, file, li) {
        var reader = new FileReader(),
            $li = $(li);
        //this.ctrl = createThrobber(img); 这个只有firefox支持
        var xhr = new XMLHttpRequest();
        this.xhr = xhr;
        //console.log(xhr);
        var self = this;
        this.xhr.upload.addEventListener("progress", function(e) {
            //维护进度条
            if(e.lengthComputable) {
                var percentage = Math.round((e.loaded * 100) / e.total);
                //self.ctrl.update(percentage);
                $li.find('.upload-progress').width(percentage + '%');
            }
        }, false);

        xhr.upload.addEventListener("load", function(e) {
            //self.ctrl.update(100);
            //var canvas = self.ctrl.ctx.canvas;
            //canvas.parentNode.removeChild(canvas);
        }, false);

        this.xhr.onreadystatechange = function() {
            if(self.xhr.readyState === 4) {
                if(self.xhr.status === 200) {
                    var data = JSON.parse(self.xhr.responseText),
                        commentText = $commentBox.val();
                    //console.log(data);
                    if(data.error === 0) {
                        NP.track('event','Reply imageUpload success');
                        $li.find('#finish').css('opacity', 1);
                        $commentBox.val(commentText + '\n' + 'http://' + document.location.host + '/np-content/upload/' + data.img['file_name']);
                    } else {
                        NP.track('event','Reply imageUpload '+data.msg);
                        $li.attr('title', data.msg + '').find('#finish').html('&#10060;').css('color', 'red').css('opacity', 1);
                        $li.find('.upload-progress-wrap').find('.upload-progress').css('background', 'red');
                    }
                }
            }
        }

        var formdata = new FormData();
        formdata.append("file", file);

        xhr.open("POST", "/api/upload", true);

        //xhr.setRequestHeader("X_FILENAME", file.name);
        // xhr.send(file);
        // xhr.overrideMimeType('text/plain; charset=x-user-defined-binary');
        reader.onload = function(evt) {
            // xhr.sendAsBinary(evt.target.result);
            xhr.send(formdata);
        };
        reader.readAsBinaryString(file);
    }

    var $upload = $('#file-upload');
    $upload.change(function(e) {
        var file = $upload.get(0).files[0],
            fileType = file.type,
            reader = new FileReader(),
            $preview = $('#preview');
        $preview.show();
        //console.log(file);
        //格式检测 
        var type = /image\/png/.test(fileType) || /image\/jpeg/.test(fileType);
        if(!type) {
            alert('请上传jpg或者png格式的图片');
            return;
        }
        //体积检测
        var limit = 2 * 1024 * 1024;
        if(file.size > limit) {
            alert('图片超过2M');
            return;
        }
        //尺寸检测 
        var img = new Image();
        var li = $('<li><span class="upload-progress-wrap"><span class="upload-progress"></span></span><span class="icon finish" id="finish">&#10003;</span></li>').prepend($(img));
        $('#preview>ul').append(li);

        reader.onload = (function(img) {
            return function(e) {
                img.src = e.target.result;
            }
        })(img);
        reader.readAsDataURL(file);
        new FileUpload(img, file, li);
    });


    $('#JS-add-pic,#JS-add-link').on('click', function() {
        var address = prompt('请输入完整地址', 'http://');
        if(address && address !== 'http://') {
            var $commentBox = $('#cm-box'),
                commentText = $commentBox.val();
            $commentBox.val(commentText + '\n' + address);
        }
        NP.track('event','Reply clickBar');
    });

    $('.onindex').change(function() {
        var $form = $(this).parent('form'),
            data = $form.serialize();
        var $dialog = $.dialog({
            content: '更新中'
        });
        $.post($form.attr('action'), data, function(data) {
            if(data.error === 0) {
                $dialog.content('更新成功');
                setTimeout(function() {
                    $dialog.close();
                }, 1000);
            }
        }, 'json');
    });

    //autoTextarea($('#cm-box')[0],20,300);
    if(isTopicPage) {
        var data = ['admin'],
            $userNameNode = $('.cm-list>li>p>a.user-name');
        authorName = $('.post-info .post_author>img').attr('alt');
        data.push(authorName);
        $.unique($.merge(data, $.unique($.map($userNameNode, function(val, key) {
            return $(val).text();
        }))));
        $LAB.script("/js/plugin/at.js").wait(function() {
            $('#cm-box').atWho('@', {
                'data': data,
                'tpl': "<li data-value='${name}'><img src='/avatar/${name}/20'/> ${name}</li>"
            });
        });
        if(parseInt(hash)) {
            $(hash).css('background-color', 'yellow');
            setTimeout(function() {
                $(hash).css('background-color', '#fff');
            }, 3000);
        }
        /*if(hash){$('html, body').animate({
         scrollTop: $(hash).offset().top
         }, 2000);}*/
    }

    $('#fetch-gravatar').on('click', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        $.dialog({
            title: '获取Gravatar头像',
            content: '<img style="vertical-align: bottom;" src="' + url + '?s=73"/>&nbsp;<img style="vertical-align: bottom;" src="' + url + '?s=48"/>&nbsp;<img style="vertical-align: bottom;" src="' + url + '?s=20"/> <br/><br/><br/><a href="/api/user/0/use_gravatar" class="vivid-button">确认使用Gravatar头像</a> '
        });
    });

    $doc.on('click', '#JS_fav_action', function(e) {
        e.preventDefault();
        var $this = $(this),
            api = $this.attr('href'),
            isFav = api.indexOf('=fav') !== -1;
        $this.css('background', 'yellow');
        $.get(api, function(data) {
            if(data.error === 0) {
                $this.attr('href', isFav ? api.replace('fav', 'unfav') : api.replace('unfav', 'fav'));
                $this.text(isFav ? '取消收藏' : '加入收藏');
                $this.css('background', 'white');
                NP.track('event','Topic ' +isFav?'fav':'unfav'+ ' success');
            }else{
                NP.track('event','Topic ' +isFav?'fav':'unfav'+ ' fail');
            }
        }, 'json');
    });
    $doc.on('click', '#JS_follow_action', function(e) {
        e.preventDefault();
        var $this = $(this),
            api = $this.attr('href'),
            isFo = api.indexOf('=fo') !== -1;
        $.get(api, function(data) {
            if(data.error === 0) {
                $this.attr('href', isFo ? api.replace('=fo', '=unfo') : api.replace('=unfo', '=fo'));
                $this.text(isFo ? '取消关注' : '关注TA');
                NP.track('event','Member ' +isFo?'fo':'unfo'+ ' success');
            }else{
                NP.track('event','Member ' +isFo?'fo':'unfo'+ ' fail');
            }
        }, 'json');


    });


});



var trackMap = {
    '.track-sidebar-add-topic': 'Nav createTopic sidebar',
    '.track-home-add-topic': 'Nav createTopic content',
    '.home': 'Nav backHome nav',
    '#logo': 'Nav backHome Logo',
    '.home-ad a': 'Ad click index',
    '.node-ad a': 'Ad click node',
    '.topic-ad a': 'Ad click topic',
    '.user-ad a': 'Ad click member',
	'#JS_msg' :'Nav message',
	'#JS_send_message':'Nav clickPm'
};


$(function() {
    for(var i in trackMap) {
        (function() {
            var temp = i;
            $(document).on('click', temp, function(e) {
                $(this).attr('target', '_blank');
                NP.track('event',trackMap[temp]);
            })
        })()
    }
});

