(function ($) {
    $.queryString = (function (a) {
        if (a === "") return {};
        var b = {};
        for (var i = 0; i < a.length; ++i) {
            var p = a[i].split('=');
            if (p.length !== 2) continue;
            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
        }
        return b;
    })(window.location.search.substr(1).split('&'));

    if (USER.userName) {
        store.set('lastLoginName', USER.userName);
    }

    if (store.get('lastLoginName') && $('#user-name').length > 0) {
        if (document.location.href.indexOf('signup') === -1) {
            $('#user-name').val(store.get('lastLoginName'));
            //延迟执行，否则无法获得焦点
            setTimeout(function () {
                $('#user-pwd')[0].focus();
            }, 500);
        }
    }

    //使用JS发送心跳包来检测并记录用户是否在线
    //并且定时更新不在线用户的信息
	$.get('/api/user?do=_get_online');
	$.get('/api/user?do=get_online_user',function(data){
		if(data.error===0){
		$('#online').text(data.no+'位用户在线');
		}
	},'json')

})(jQuery);


artDialog.fn.shake = function () {
    var style = this.DOM.wrap[0].style,
        p = [4, 8, 4, 0, -4, -8, -4, 0],
        fx = function () {
            style.marginLeft = p.shift() + 'px';
            if (p.length <= 0) {
                style.marginLeft = 0;
                clearInterval(timerId);
            }
            ;
        };
    p = p.concat(p.concat(p));
    timerId = setInterval(fx, 13);
    return this;
};


!function () {
    var href = document.location.href;
    //不得已的解决方法，使用base后，锚点基本地址是网站首页。
    document.getElementById('home').href = document.location.href + '#body';
    //帖子发表时间过久提示
    if (/\/t\/\d/.test(href)) {
        var time = $('.time-txt').eq(0).text(),
            diff = (+new Date()) - (+new Date(time)),
            diffMonth = Math.floor(diff / 1000 / 60 / 60 / 24 / 30);
        if (diffMonth >= 2) {
            setTimeout(function () {
                $('<p class="long-ago-topic-tip">这是一个创建于<b>' + diffMonth + '</b>个月前的主题，其中的信息可能已经有所发展或是发生改变。</p>').insertBefore('.post-content').show();
            }, 1000)
        }
    }


}()


/**
 * comment
 **/
var comment = {
    commentBox:$('#cm-box'),
    button:$('#cm-button'),
    storeId:'',
    // listening 
    listen:function () {
        var that = this,
            href = document.location.href,
            unCommit;

        that.storeId = 'reply:' + href.slice(href.lastIndexOf('/') + 1);


        //恢复未提交回复
        if (unCommit = store.get(that.storeId)) {
            $('#cm-box').val(unCommit);
        }

        $(that.button).click(function (e) {
            e.preventDefault();
            that.add();
        });

        jQuery.hotkeys.add('ctrl+return', function (e) {
            that.add();
        });

        $(that.commentBox).bind('keyup', function () {
            $(that.commentBox).css('borderColor', '#ccc');
            //未提交的回复保存在本地
            store.set(that.storeId, $(that.commentBox).val());
            if (!$(that.commentBox).val()) {
                $(that.commentBox).css('borderColor', 'red');
            }
        });

        //有表单示完成时提示离开
        $(window).on('beforeunload', function () {
            //删除本地保存回复
            store.remove(that.storeId);
            if ($('#cm-box').length > 0 && $('#cm-box').val() !== '') {
                return '您确认要放弃已经输入的回复吗？';
            }
        });


        $('.s-avatar').tipsy({
            live:true,
            html:true,
            gravity:'w',
            fade:true,
            title:function () {
                var id = $(this).parent('li').data('replyto');
                var content = $('li[data-id="' + id + '"]').find('p.comment-content').html();
                return content;
            }
        });
    },
    // processing show
    processing:function () {
        if ($('#processing-tip').length == 0) {
            var html = '<div id="processing-tip" style="display:none;position:fixed;top:0;width:100%;height:40px;background:#ed4e8b;color:#fff;"><div style="width:120px;height:40px;margin:0 auto;line-height:40px;">Still working</div></div>';
            $(html).appendTo('body');
        }

        $('body,#cm-button,#cm-box').css('cursor', 'wait');
        $(this.commentBox).attr('disabled', 'disabled');
        $(this.button).val('sending……').attr('disabled', 'disabled');
        this.t = setTimeout("comment.stillWorking()", 3000);
    },

    // working tip after 3s of request
    stillWorking:function () {
        $('#processing-tip').slideDown();
    },
    // auto write @username 
    smartAt:function () {
        var that = this;
        $('body').on('click', '.reply', function (e) {
            e.preventDefault();
            var content = $('#cm-box').val();
            var username = $(this).data('username');
            var replyTo = $(this).data('id');

            $('#cm-reply-to').val(replyTo);
            $('#cm-reply-name').val(username);
            if (content.indexOf(username) != -1) {
                //alert('重复at不行哦');
                return;
            }
            $(that.commentBox).focus().val(content + ' @' + $(this).data('username') + ' ');
        });

    },
    //get order of the comment list
    getOrder:function () {
        return($.queryString['order']) ? $.queryString['order'] : 'asc';
    },
    //get the max floor
    getMaxFloor:function () {
        var el = (this.getOrder() == 'asc') ? ':last' : ':first';
        if ($('.floor:' + el).length > 0) return parseInt($('.floor:' + el).text()) + 1;
        return 1;
    },
    // make request 
    add:function () {
        if (!this.filter()) return;
        var data = $('#cm-form').serialize();
        this.processing();
        var that = this;
        $.post('api/comment/add', data, function (e) {
            that.success(e);
        }, 'json');
    },
    //check if content is empty
    filter:function () {
        var that = this;
        if ($(that.commentBox).val() == '') {
            $(that.commentBox).css('borderColor', 'red');
            $(that.commentBox).focus();
            return false;
        }
        return true;
    },
    // ajax retrieve 
    retrieve:function () {


    },
    // do sth, when successfully adding comment
    success:function (e) {
        //提交成功删除本地保存回复
        store.remove(this.storeId);
        var html = Mustache.render($('#comment-template').html(), e);
        if (e.data.cm_reply_name != '') html += '<a href="t/' + e.data.post_id + '#comment-' + e.data.cm_reply_to + '" class="s-avatar"><img src="avatar/' + e.data.cm_reply_id + '/20' + e.data.cm_reply_name + '" width="20" height="20" alt=""></a>';
        html += '</li>';
        if ($('#no-reply').length > 0) $('#no-reply').remove();
        var floor = this.getMaxFloor();
        this.update_time_diff();
        $(html).appendTo('.cm-list').find('.floor').text('' + floor).end().css("backgroundColor", '#FFFF00').slideDown('slow').css("-moz-transition", "background-color 2.0s ease-in").css("-o-transition", "background-color 2.0s ease-in").css("-ms-transition", "background-color 2.0s ease-in").css("-webkit-transition", "background-color 2.0s ease-in").css("backgroundColor", "white")
        //.queue(function(){
        //$(this)
        //});
        $(this.commentBox).val('');

        $('body,#cm-box').css('cursor', 'auto');
        $('#cm-button').css('cursor', 'pointer');
        $('#cm-box').css('borderColor', '#ccc');
        var commentNo = parseInt($('#post-comment-no').text()) + 1;
        $('#post-comment-no').text(commentNo);
        $(this.commentBox).removeAttr('disabled');
        $(this.button).val('^_^ success!').removeAttr('disabled');
        setTimeout('comment.removeSuccessNotice()', 2000);
        $('#cm-reply-to').val('');
        $('#cm-reply-name').val('');
        clearTimeout(this.t)
        $('#processing-tip').fadeOut();
        $('#cm-reply-to').val('0');
        $('#cm-reply-name').val('');
        $('#preview').hide().find('ul').empty();

    },
    removeSuccessNotice:function () {

        $(this.button).val('send');
        //console.log('trigger');
    },
    update_time_diff:function () {
        $('.time-ago').each(function () {
            var time = $(this).data('time');
            $(this).text(time_ago(time) + ' ago');
        });
    },
    getCommentInfo:function (id) {
        var info = $('li[data-id="' + id + '"]').find('p.comment-info').text();
        alert(info);
    },
    timeout:function () {

    },
    init:function () {
        this.smartAt();
        this.listen();
        setInterval("comment.update_time_diff()", 300000);
    }
}

var topic = {
    id:0,
    target:$('#do-fav'),
    listen:function () {
        $('#do-fav').click(function (e) {
            e.preventDefault();
            topic.fav($(this).attr('href'));

        });
    },

    fav:function (url) {
        $.getJSON(url, '', function (res) {
            if (res.error == 0) {
                var unfav = url.indexOf('unfav') == -1,
                    add = unfav > 0 ? 1 : -1;
                $('#topic-fav-count').text(parseInt($('#topic-fav-count').text()) + add);
                (url.indexOf('unfav') == -1) ? $('#do-fav').attr('href', url.replace('fav', 'unfav')).attr('title', 'click to unfav').removeClass().addClass('fav-link') : $('#do-fav').attr('title', 'click to fav').attr('href', url.replace('unfav', 'fav')).removeClass().addClass('unfav-link');

            }
        });

    },
    init:function () {
        this.listen();
    }
}

var message = {

    checkTag:function () {
        return $('#unread-count').length > 0 ? true : false;
    },
    /**
     * @todo 使用pagevisibility来检测，当不在当面页面上提示
     */
    fetch:function () {
        if (this.checkTag()) {
            $.get('api/user/0/message', {
                type:1,
                count:1
            }, function (res) {
                var no = res.count;
                $('#unread-count').addClass('vivid-notice').hide().text(no).fadeIn();
                var title = document.title;
                if (parseInt(no) > 0) {
                    if (window.localStorage && window.webkitNotifications) {
                        if (localStorage.getItem('enableNotification') === '1') {
                            var notice = window.webkitNotifications.createNotification('', '提醒', '您收到新的站内消息');
                            notice.show();
                            setTimeout(function () {
                                notice.close();
                            }, 2000);
                        }
                    }
                    setInterval(function () {
                        document.title = '(' + no + ')条新消息-' + title;
                        setTimeout(function () {
                            document.title = '(  )条新消息-' + title;
                        }, 1000);
                    }, 2000);
                }
            }, 'json');

        }

    },
    setRead:function () {
    }
}

var post = {
    tipsy:function () {
        $('.fav-link,.unfav-link,.share').tipsy({
            live:true,
            title:'title',
            gravity:'s',
            fade:true
        });
    },
    share:function () {
        $('.share').click(function (e) {
            e.preventDefault();
            window.open($(this).attr('href'), '_blank', 'width=550,height=370');
        });
    },
    init:function () {
        this.tipsy();
        this.share();
    }

}


//dirty but quick way

function get_time_difference(earlierDate, laterDate) {
    var nTotalDiff = laterDate.getTime() - earlierDate.getTime();
    var oDiff = new Object();
    oDiff.days = Math.floor(nTotalDiff / 1000 / 60 / 60 / 24);
    nTotalDiff -= oDiff.days * 1000 * 60 * 60 * 24;
    oDiff.hours = Math.floor(nTotalDiff / 1000 / 60 / 60);
    nTotalDiff -= oDiff.hours * 1000 * 60 * 60;
    oDiff.minutes = Math.floor(nTotalDiff / 1000 / 60);
    nTotalDiff -= oDiff.minutes * 1000 * 60;
    oDiff.seconds = Math.floor(nTotalDiff / 1000);
    return oDiff;
}
// Function Usage

function time_ago(time) {
    dateCurrent = new Date();
    oldDate = new Date(time);
    oDiff = get_time_difference(oldDate, dateCurrent);
    days = (oDiff.days != 0) ? oDiff.days + ' days ' : '';
    hours = (oDiff.hours != 0) ? oDiff.hours + ' hours ' : '';
    minutes = (oDiff.minutes != 0) ? oDiff.minutes + ' minutes ' : '';
    seconds = (oDiff.seconds != 0) ? oDiff.seconds + ' seconds ' : '';
    //如果是天数级别，返回天数
    if (oDiff.days > 0) return oDiff.days + ' days';
    //如果是小时级别显示小时和分钟
    if (oDiff.days == 0 && oDiff.hours > 0) return oDiff.hours + ' hours ' + ((oDiff.minutes > 0) ? oDiff.minutes + ' minutes' : '');
    //如果是分钟的显示分钟
    if (oDiff.days == 0 && oDiff.hours == 0 && oDiff.minutes > 0) return oDiff.minutes + ' minutes';
    //如果只有秒，显示秒
    if (oDiff.days == 0 && oDiff.hours == 0 && oDiff.minutes == 0 && oDiff.seconds > 0) return oDiff.seconds + ' seconds';
}

$(function () {
    comment.init();
    message.fetch();
    topic.init();
    //setTimeout('message.fetch()',2000);
    post.init();
    $("a[rel='external']").attr('target', '_blank');
    //alert($.queryString['order']);
    //$("img").lazyload({event:"sporty",effect:"fadeIn"});
    //$(window).bind("load",function(){var timeout=setTimeout(function(){$("img").trigger("sporty")},500);});
    $('.unfav-node').tipsy({
        gravity:'s',
        fade:true
    });
    //backbone mode
    //if(window.location.href.indexOf('#!')!=-1) {
    // load js sources
    //}
    //
    $('#register-form').validate({

        rules:{
            "user-name":{
                minlength:3,
                maxlength:10,
                remote:"api/user/0/check_username"
            },
            "user-email":{
                remote:"api/user/0/check_email"
            },
            "user-pwd":{
                minlength:6,
                maxlength:16
            }
        },
        messages:{
            "user-name":{
                remote:'Sorry, the user name has been taken'
            },
            "user-email":{
                remote:'Sorry, the email has been taken'
            }

        }
    });


});

$(function () {

    var $body = $('body'),
        signinDialog;
    $('#signin-btn,a[href="/signin"]').click(function (e) {
        e.preventDefault();
        signinDialog = $.dialog({
            title:'Sign In',
            content:$('#signin-template').html(),
            lock:true,
            fixed:true,
            opacity:0.1,
            init:function () {
                $body.toggleClass('modal');
                $('#user-name').focus();
                if (store.get('lastLoginName')) {
                    $('#user-name').val(store.get('lastLoginName'));
                    $('#user-pwd').focus();
                }
            },
            close:function () {
                $body.toggleClass('modal');
            }

        });
    });

    $(document).on('click', '#do-signin-btn', function (e) {
        e.preventDefault();
        var $tip = $('#signin-tip');
        if ($('#user-name').val() === '' || $('#user-pwd').val() === '') {
            $tip.text('请填写用户名和密码');
            signinDialog.shake();
            return;
        }
        var data = $('#js-signin-form').serialize();
        $.post('/api/user/signin', data, function (e) {
            if (e.error !== 0) {
                $tip.text(e.data.message);
            } else {
                document.location.reload();
            }
        }, 'json');
    });


    /**

     $('a').click(function(e) {
        e.preventDefault();
    });

     var pjax = true,
     api = {
        't': '/api/post/',
        'node': '/api/node/',
        'member': '/api/member/',
        'fav': '/api/fav/'
    },
     setTitle = function(title) {
        document.title = title;
    }
     $(document).on('click', 'a', function(e) {
        e.preventDefault();
        var href = this.href,
        title = $(this).text(),
        data = $('.topic-list').html(),
        $loader = $('#loading');
        $('.topic-list').addClass('blur');
        $loader.show();
        $.ajax({
            url: '/api/post/0/list',
            cache: true,
            success: function(data) {
                var html = Mustache.render($('#post-list-template').html(), JSON.parse(data));
                $('.topic-list').html(html);
                $loader.hide();
                $('.topic-list').removeClass('blur');
            },
            fail: function() {

            }
        });

        window.history.pushState({
            data: {
                data:data,
                view:true
            }
        }, title, href);
        store.set(href,data);
        setTitle(title);
    //$('.topic-list').html('');

    });


     window.onpopstate = function(event) {
        console.log("location: " + document.location + ", state: " + JSON.stringify(event.state));
    };


     **/


    /*
     $('#add-node-btn').click(function(e){
     e.preventDefault();
     var tip = $.dialog({title:'操作提示',content:'添加节点中'});
     var data=$('#node-add-form').serialize();
     $.post('/api/nodes/add',data,function(data){
     if(data.error===0){
     tip.content('添加成功,2秒后自动关闭');
     setTimeout(function(){tip.close();},2000);
     //$.dialog({content:'更新中',time:2});
     }
     },'json');
     });

     */

    $('#do-delete,.do-topic-delete').click(function (e) {
        e.preventDefault();
        var href = this.href;
        $.dialog({
            title:'提示',
            content:'确定删除吗，删除时相关评论也会被删除',
            ok:function () {
                location.href = href;
            },
            cancel:function () {
            },
            okVal:'删除',
            cancelVal:'取消'
        });
    });


    if (document.location.hash === '#no-admin-rights') {
        $.dialog({
            title:'抱歉',
            content:'抱歉，您没有管理员权限，请以管理员身份重新登录',
            lock:true
        });
    }

    if (document.location.hash === '#email-confirm-done') {
        $.dialog({
            title:'恭喜',
            content:'恭喜，您已经完成邮箱验证',
            time:2
        });
    }

    if (document.location.hash === '#email-confirm-fail') {
        $.dialog({
            title:'抱歉',
            content:'抱歉，邮箱验证失败',
            lock:true
        });
    }
    var apiRequest = function (url, target, form, sendingTitle, doneTitle) {
        $(target).click(function (e) {
            e.preventDefault();
            var isDelete = $(this).attr('class').indexOf('delete') !== -1 || $(this).attr('id').indexOf('delete') !== -1;
            //针对删除操提示
            if (isDelete) {
                if (!confirm('确定删除吗')) {
                    return;
                }
            }
            var $this = $(this);
            var tip = $.dialog({
                title:'操作提示',
                content:sendingTitle
            });
            if (form !== '') {
                var data = $(form).serialize();
            } else {
                var data = $this.parent('form').serialize();
            }

            $.post('api/' + url, data, function (data) {
                if (data.error === 0) {
                    tip.content(doneTitle + ',2秒后自动关闭');

                    //$.dialog({content:'更新中',time:2});
                } else {
                    tip.content('操作失败：' + data.msg);
                }

                setTimeout(function () {
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
    //删除节点
    $('.admin-nav a').each(function () {
        if (document.location.href.indexOf(this.href) !== -1) {
            $(this).addClass('current');
        }
    });

    /*notification*/
    if (window.webkitNotifications) {
        var enableNotification = localStorage.getItem('enableNotification') === '1';
        if (enableNotification) {
            $('#enable-notification').attr('checked', 'checked');
        } else {
            localStorage.setItem('enableNotification', 0);
        }

        $('#enable-notification').length > 0 && document.querySelector('#enable-notification').addEventListener('click', function () {
            if (window.webkitNotifications.checkPermission() == 0) { // 0 is PERMISSION_ALLOWED
                if ($(this).prop('checked')) {
                    var text = '您已启用桌面消息提醒';
                    localStorage.setItem('enableNotification', 1);
                } else {
                    var text = '您已取消桌面消息提醒';
                    localStorage.setItem('enableNotification', 0);
                }
                var notification = window.webkitNotifications.createNotification('', '提示', text);
                notification.show();
                setTimeout(function () {
                    notification.close();
                }, 3000)
            } else {
                window.webkitNotifications.requestPermission();
            }
        }, false);
    }

    //tips
    //@todo 在窗口缩小时应检测右边是否有可用宽度智能定位
    var $doc = $(document),
        temp = '<p class="node-name">{{node_name}}[<b>{{node_post_no}}</b>]</p><p class="node-intro">{{node_intro}}</p>',
        parseTemp = function (data) {
            return temp.replace('{{node_name}}', data.node_name).replace('{{node_intro}}', (data.node_intro === "0" || data.node_intro === null) ? '该节点暂时没有介绍哦~' : data.node_intro).replace('{{node_post_no}}', data.node_post_no);
        },
        showTip = function (offset, data, height) {
            var nodeTip = $('#node-tip');
            height = height + 5;
            if (nodeTip.length > 0) {
                nodeTip.hide().css({
                    left:offset.left,
                    top:offset.top + height
                }).find('#inner-content').html(parseTemp(data)).end().show();
            } else {
                $('<div class="node-tip" id="node-tip"><div class="tip-content"><span class="arrow1">◆</span><span class="arrow2">◆</span><div id="inner-content"></div></div><div>').appendTo('body').css({
                    left:offset.left,
                    top:offset.top + height
                }).find('#inner-content').html(parseTemp(data)).end().show();
            }

        },
        hideTip = function () {
            $('#node-tip').hide();
        }


    $doc.on({
        'mouseenter':function () {
            var $this = $(this),
                height = $this.outerHeight();
            this.title = "";
            this.tip = setTimeout(function () {
                var offset = $this.offset(),
                    href = $this.attr('href'),
                    data;
                //在缓存中找
                if (store.get(href)) {
                    data = store.get(href);
                    showTip(offset, data, height);
                } else {
                    $.get('/api' + href, {}, function (e) {
                        if (e.error === 0) {
                            store.set(href, e.info);
                            showTip(offset, e.info, height);
                        }
                    }, 'json');
                }

            }, 200);
        },
        'mouseleave':function (e) {
            clearTimeout(this.tip);
            if (e.target.nodeName !== 'a') {
                hideTip();
            }
        }
    }, '.node-list a,.post-node,a[href^="/node/"]');


    $('.icon-list .icon,#file-upload,#preview li').tipsy({
        live:true,
        html:true,
        gravity:'n',
        fade:true
    });


    var $commentBox = $('#cm-box');
    //ArrayBuffer is deprecated in XMLHttpRequest.send(). Use ArrayBufferView instead.

    function FileUpload(img, file, li) {
        var reader = new FileReader(),
            $li = $(li);
        //this.ctrl = createThrobber(img); 这个只有firefox支持
        var xhr = new XMLHttpRequest();
        this.xhr = xhr;
        console.log(xhr);
        var self = this;
        this.xhr.upload.addEventListener("progress", function (e) {
            //维护进度条
            if (e.lengthComputable) {
                var percentage = Math.round((e.loaded * 100) / e.total);
                //self.ctrl.update(percentage);
                $li.find('.upload-progress').width(percentage + '%');
            }
        }, false);

        xhr.upload.addEventListener("load", function (e) {
            //self.ctrl.update(100);
            //var canvas = self.ctrl.ctx.canvas;
            //canvas.parentNode.removeChild(canvas);
        }, false);

        this.xhr.onreadystatechange = function () {
            if (self.xhr.readyState === 4) {
                if (self.xhr.status === 200) {
                    var data = JSON.parse(self.xhr.responseText),
                        commentText = $commentBox.val();
                    console.log(data);
                    if (data.error === 0) {
                        $li.find('#finish').css('opacity', 1);
                        //插入输入框
                        $commentBox.val(commentText + '\n' + 'http://' + document.location.host + '/np-content/upload/' + data.img['file_name']);
                    } else {
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
        reader.onload = function (evt) {
            // xhr.sendAsBinary(evt.target.result);
            xhr.send(formdata);
        };
        reader.readAsBinaryString(file);
    }

    var $upload = $('#file-upload');
    $upload.change(function (e) {
        var file = $upload.get(0).files[0],
            fileType = file.type,
            reader = new FileReader(),
            $preview = $('#preview');
        $preview.show();
        //console.log(file);
        //格式检测 
        var type = /image\/png/.test(fileType) || /image\/jpeg/.test(fileType);
        if (!type) {
            alert('请上传jpg或者png格式的图片');
            return;
        }
        //体积检测
        var limit = 2 * 1024 * 1024;
        if (file.size > limit) {
            alert('图片超过2M');
            return;
        }
        //尺寸检测 
        var img = new Image();
        var li = $('<li><span class="upload-progress-wrap"><span class="upload-progress"></span></span><span class="icon finish" id="finish">&#10003;</span></li>').prepend($(img));
        $('#preview>ul').append(li);

        reader.onload = (function (img) {
            return function (e) {
                img.src = e.target.result;
            }
        })(img);
        reader.readAsDataURL(file);
        new FileUpload(img, file, li);
    });


    $('#JS-add-pic,#JS-add-link').on('click', function () {
        var address = prompt('请输入完整地址', 'http://');
        if (address && address !== 'http://') {
            var $commentBox = $('#cm-box'),
                commentText = $commentBox.val();
            $commentBox.val(commentText + '\n' + address);
        }
    });

    $('.onindex').change(function () {
        var $form = $(this).parent('form'),
            data = $form.serialize();
            var $dialog=$.dialog({content:'更新中'});
        $.post($form.attr('action'), data, function (data) {
            if(data.error===0){
                $dialog.content('更新成功');
                setTimeout(function(){$dialog.close();},1000);
            }
        },'json');
    });

     //autoTextarea($('#cm-box')[0],20,300);

});