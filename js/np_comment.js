/**
 * NodePrint Comment 
 * @author airyland <i@mao.li>
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

        $(document).on('click','#cm-button',function (e) {
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
        var content=$(this.commentBox).val();
        var that = this;
        if (content=== ''||$.trim(content)==='') {
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
        if (e.data.cm_reply_name != '') html += '<a href="t/' + e.data.post_id + '#comment-' + e.data.cm_reply_to + '" class="s-avatar"><img src="avatar/' + e.data.cm_reply_id + '/20' + '" '+'width="20" height="20" alt=""></a>';
        html += '</li>';
        if ($('#no-reply').length > 0) $('#no-reply').remove();
        var floor = this.getMaxFloor();
        this.update_time_diff();
        $(html).appendTo('.cm-list').find('.floor').text('' + floor).end().css("backgroundColor", '#FFFF00').slideDown('slow').css("-moz-transition", "background-color 2.0s ease-in").css("-o-transition", "background-color 2.0s ease-in").css("-ms-transition", "background-color 2.0s ease-in").css("-webkit-transition", "background-color 2.0s ease-in").css("backgroundColor", "white")
        //.queue(function(){
        //$(this)
        //});
        $(this.commentBox).val('');
        $('#cm-box').val('');
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
        if(NPINFO.isMobile){
            return;
        }
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

$(function(){
    comment.init();
})

$(function() {
    jQuery.hotkeys.add('ctrl+return', function(e) {
        //alert('haha');
        comment.add();
    });
});