/**
 * NodePrint Comment
 * @author airyland <i@mao.li>
 **/


var $commentBox = $('#cm-box'),
    $commentBtn = $('#cm-button'),
    href = href || document.location.href,
    comment = {
        onSending: false,
        button: $('#cm-button'),
        storeId: '',
        // listening 
        listen: function() {
            var that = this,
                unCommit;

            that.storeId = 'reply:' + href.slice(href.lastIndexOf('/') + 1);


            //恢复未提交回复
            if (unCommit = store.get(that.storeId)) {
                $commentBox.val(unCommit);
            }

            $doc.on('click', '#cm-button', function(e) {
                e.preventDefault();
                that.add();
            });

            $($commentBox).bind('keyup', function() {
                $commentBox.css('borderColor', '#ccc');
                //未提交的回复保存在本地
                store.set(that.storeId, $commentBox.val());
                if (!$commentBox.val()) {
                    $commentBox.css('borderColor', 'red');
                }
            });

            //有表单示完成时提示离开
            $(window).on('beforeunload', function() {
                //删除本地保存回复
                store.remove(that.storeId);
                if ($('#cm-box').length > 0 && $('#cm-box').val() !== '') {
                    return '您确认要放弃已经输入的回复吗？';
                }
            });


            $('.s-avatar').tipsy({
                live: true,
                html: true,
                gravity: 'w',
                fade: true,
                title: function() {
                    var id = $(this).parent('li').data('replyto');
                    var content = $('li[data-id="' + id + '"]').find('p.comment-content').html();
                    return content;
                }
            });
        },
        // processing show
        processing: function() {
            if ($('#processing-tip').length == 0) {
                var html = '<div id="processing-tip" style="display:none;position:fixed;top:0;width:100%;height:40px;background:#ed4e8b;color:#fff;"><div style="width:120px;height:40px;margin:0 auto;line-height:40px;">Still working</div></div>';
                $(html).appendTo('body');
            }

            $('body,#cm-button,#cm-box').css('cursor', 'wait');
            $commentBox.attr('disabled', 'disabled');
            $(this.button).val('sending……').attr('disabled', 'disabled');
            this.t = setTimeout("comment.stillWorking()", 3000);
        },

        tooFastCheck: function() {
            var npCommentLastReply = store.get('np_comment_last_reply'),
                now = +new Date();
            if (npCommentLastReply) {
                if (now - npCommentLastReply < 20000) {
                    this.showTip('您回复太快啦,请过会再试咯~');
                    return true;
                }
                return false;
            }
        },

        // working tip after 3s of request
        stillWorking: function() {
            $('#processing-tip').slideDown();
        },
        // auto write @username 
        smartAt: function() {
            var that = this;

        },
        //get order of the comment list
        getOrder: function() {
            return ($.queryString['order']) ? $.queryString['order'] : 'asc';
        },
        //get the max floor
        getMaxFloor: function() {
            var el = (this.getOrder() == 'asc') ? ':last' : ':first';
            if ($('.floor:' + el).length > 0) return parseInt($('.floor:' + el).text()) + 1;
            return 1;
        },
        showTip: function(msg) {
            var _this = this;
            clearTimeout(this.tipTimeout);
            $('#reply-tip').text(msg);
            this.tipTimeout = setTimeout(function() {
                _this.clearTip();
            }, 3000);
        },
        clearTip: function() {
            $('#reply-tip').text('');
        },
        // make request 
        add: function() {
            var _this = this;

            // check if the reply is on sending 
            if(_this.onSending === true){
                return;
            }
            _this.onSending === true;

            NP.track('event', 'Reply send ' + ($doc.data('replyBy') === 'hotkeys' ? 'ctrlEnter' : 'button'));
            $doc.data('replyBy', '');
            if (!this.filter()) {
                return;
            }
            if (this.tooFastCheck()) {
                return;
            }
            var data = $('#cm-form').serialize();
            this.processing();
            var that = this;
            $.post('api/comment/add', data, function(e) {
                that.success(e);
            }, 'json');
        },
        //check if content is empty
        filter: function() {
            var content = $commentBox.val();
            if (content === '') {
                this.showTip('亲，内容不能为空哦~');
                this.showErrorStyle();
                return false;
            }
            if ($.trim(content) === '') {
                this.showTip('亲，纯空格也不行哦~');
                this.showErrorStyle();
                return false;
            }
            return true;
        },
        showErrorStyle: function() {
            $commentBox.css('borderColor', 'red');
            $commentBox.focus();
        },
        // ajax retrieve 
        retrieve: function() {


        },
        // do sth, when successfully adding comment
        success: function(e) {
            this.onSending === false;
            NP.track('event', 'Reply create');
            //提交成功删除本地保存回复
            store.remove(this.storeId);
            var html = Mustache.render($('#comment-template').html(), e);
            if (e.data.cm_reply_name != '') html += '<a href="t/' + e.data.post_id + '#comment-' + e.data.cm_reply_to + '" class="s-avatar"><img src="avatar/' + e.data.cm_reply_id + '/20' + '" ' + 'width="20" height="20" alt=""></a>';
            html += '</li>';
            if ($('#no-reply').length > 0) $('#no-reply').remove();
            var floor = this.getMaxFloor();
            this.update_time_diff();
            $(html).appendTo('.cm-list').find('.floor').text('' + floor).end().css("backgroundColor", '#FFFF00').slideDown('slow').css("-moz-transition", "background-color 2.0s ease-in").css("-o-transition", "background-color 2.0s ease-in").css("-ms-transition", "background-color 2.0s ease-in").css("-webkit-transition", "background-color 2.0s ease-in").css("backgroundColor", "white");
            $commentBox.val('');
            $('body,#cm-box').css('cursor', 'auto');
            $('#cm-button').css('cursor', 'pointer');
            $commentBox.css('borderColor', '#ccc');
            var commentNo = parseInt($('#post-comment-no').text()) + 1;
            $('#post-comment-no').text(commentNo);
            $commentBox.removeAttr('disabled');
            $(this.button).val('^_^ success!').removeAttr('disabled');
            setTimeout('comment.removeSuccessNotice()', 2000);
            $('#cm-reply-to').val('');
            $('#cm-reply-name').val('');
            clearTimeout(this.t);
            $('#processing-tip').fadeOut();
            $('#cm-reply-to').val('0');
            $('#cm-reply-name').val('');
            $('#preview').hide().find('ul').empty();
            store.set('np_comment_last_reply', +new Date());
        },
        removeSuccessNotice: function() {
            $(this.button).val('send');
        },
        update_time_diff: function() {
            if (NPINFO.isMobile) {
                return;
            }
            $('.time-ago').each(function() {
                var time = $(this).data('time');
                $(this).text(time_ago(time) + NP.lang.ago);
            });
        },
        getCommentInfo: function(id) {
            var info = $('li[data-id="' + id + '"]').find('p.comment-info').text();
            alert(info);
        },
        timeout: function() {

        },
        init: function() {
            this.smartAt();
            this.listen();
            setInterval("comment.update_time_diff()", 300000);
        }
    }

$(function() {
    comment.init();
});

$(function() {
    var sendingReply = function(e) {
        e.preventDefault();
        $doc.data('replyBy', 'hotkeys');
        comment.add();
        return false;
    };
    // sending reply shortcut
    $doc.bind('keyup', 'ctrl+return', sendingReply);

    // reply shortcut
    $doc.bind('keydown', {
        combi: 'r',
        disableInInput: true
    }, function(e) {
        e.preventDefault();
        $('#cm-box').focus();
    });
});


$doc.on('click', '.reply', function(e) {
    e.preventDefault();
    var content = $commentBox.val(),
        username = $(this).data('username'),
        replyTo = $(this).data('id');
    $('#cm-reply-to').val(replyTo);
    $('#cm-reply-name').val(username);
    if (content.indexOf(username) != -1) {
        return;
    }
    $commentBox.focus().val(content + ' @' + $(this).data('username') + ' ');
    NP.track('event', 'Reply click@');
});