/**
 * NodePrint Message Manager
 * @author airyland <i@mao.li>
 */

var NPMessage = {
    isSupportNotification :!! window.webkitNotifications,
    lastMessageFetch:function(){
        return store.get('lastMsgFetchTime');
    },
    fetch:function () {
        var _this=this,
              $msgCount=$('.msg-count').eq(0);
        $.get('api/user/0/message', {
            type:1,
            count:1,
            start_time:_this.lastMessageFetch?_this.lastMessageFetch:''
        }, function (res) {
            var allNo = res.count,
                   newNo= res.new_no;
            if (parseInt(allNo) > 0) {
                $msgCount.text(allNo).fadeIn();
                if(newNo){
                    _this.notify(newNo);
                }
            }else{
                $msgCount.text(0).hide();
            }
            _this.setLastMsgFetchTime();
        }, 'json');
    },
    notify:function(no){
        if (window.localStorage && window.webkitNotifications) {
            if (localStorage.getItem('enableNotification') === '1') {
                var notice = window.webkitNotifications.createNotification('', 'NodePrint站内消息提醒', '您收到'+no+'条新的站内消息');
                notice.show();
                setTimeout(function () {
                    notice.close();
                }, 10000);
            }
        }
    },
    setRead:function () {
    },
    setLastMsgFetchTime:function(){
      store.set('lastMsgFetchTime',+new Date());   
    }
}
    
    

$(function(){
    
    setTimeout(function(){
        NPMessage.fetch(); 
        setInterval(function(){
            NPMessage.fetch(); 
        },20000);
    },2000);

    
/*    $('#JS_msg').on('click',function(e){
        e.preventDefault();
        var history=new NPHistory();
        msgDialog=$.dialog({
            title:'我的未读消息',
            content:'努力获取中...',
            init:function(){
                history.pushState(null, '消息', '/messages');
                $('body').addClass('modal');
            },
            close:function(){
                history.restoreState();
                $('body').removeClass('modal');
            }
        });
        
        $.get('/messages/',function(data){
            msgDialog.content(data);                     
        });   
  
    });*/
	
    $(document).on('click','.aui_content .pagination>li>a',function(e){
        e.preventDefault();
        var href=$(this).attr('href'),
        width=$('.aui_content>.m-list').width(),
        height=$('.aui_content>.m-list').height()+35;
        msgDialog.content('<div class="msg-dialog-loading" style="width:'+width+'px;height:'+height+'px;text-align:center;vertical-align:center;"><span style="margin-top:'+height/2+'px;">获取中...</span></div>');
        $.get(href,function(data){
            msgDialog.content(data);                     
        });
    });
    
    $(document).on('click','.aui_content .m-list>li',function(){
        $(this).find('p').eq(1).slideDown();
    });
    
    $(document).on('click','#JS_send_message',function(e){
        e.preventDefault();
        var username=$(this).data('sendto'),
            dialog=$.dialog({
            title:'发送私信',
            content:$('#send-message-template').html(),
            init:function(){
                $('#send_to').val(username);
                var data=['a','b','c'];
                $LAB.script("/js/plugin/at.js")
                .wait(function(){
                    $('#send_to').atWho('@',{
                        'data':'/api/username',
                        'tpl':"<li data-value='${name}'><img src='/avatar/${name}/20'/> ${name}</li>"
                    });

                });

                $('#do-send').click(function(e){
                    e.preventDefault();
                    if(!$('#send_to').val()||!$('#pm_content').val()){
                        $('.msg-error').show();
                        dialog.shake();
                        return;
                    }
                    var $form=$(this).closest('form'),
                        data=$form.serialize(),
                        api=$form.attr('action');
                    $.post(api,data,function(data){
                        if(data.error===0){
                            dialog.content('发送成功');
                        }
                        setTimeout(function(){
                            dialog.close();
                        },1000)
                    },'json')
                });
            }
        });
    });

                   $(document).on('click','.cancel-send',function(e){
                    e.preventDefault();
                   var list = art.dialog.list;
for (var i in list) {
    list[i].close();
};
               })
    
     
    /*notification*/
    if (window.webkitNotifications) {
        $('#desktop-notification').show();
        var enableNotification = localStorage.getItem('enableNotification') === '1';
        if (enableNotification&&window.webkitNotifications.checkPermission() === 0) {
            $('#enable-notification').prop('checked',true);
        } else {
            localStorage.setItem('enableNotification', 0);
        }
    }

        $('#enable-notification').length>0 && ! $('#enable-notification').is(':hidden')&&document.querySelector('#enable-notification').addEventListener('click', function () {
           $this=$(this);
            if (window.webkitNotifications.checkPermission() === 0) { // 0 is PERMISSION_ALLOWED
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
                window.webkitNotifications.requestPermission(function(){
                   var isPermitted=window.webkitNotifications.checkPermission();
                   if(isPermitted===2){
                       $.dialog('您拒绝了桌面提醒通知,请进入浏览器设置移除nodeprint.com再重试');
                       $this.prop('checked',false);
                   }else if(isPermitted===0){
	                   localStorage.setItem('enableNotification', 1);
                       $this.prop('checked',true);
                   }
                });
            }
        }, false);
    

     
});
