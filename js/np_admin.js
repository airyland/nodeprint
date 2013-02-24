/**
 * admin js
 * @author airyland <i@mao.li>
 */

$(function() {
    //节点介绍信息编辑
    var $doc = $(document),
        $nodeEditable = $('.node-editable'),
        $topic = $('.post-content'),
        href = document.location.href,
        isTopicPage = /\/t\/\d$/.test(href),
        isNodePage = /\/node\//.test(href),
        isAdminPage = /\/admin\/.*?/.test(href);
    if(isNodePage && !NPINFO.isMobile) {
        nodeSlug = href.slice(href.lastIndexOf('/') + 1);
        $nodeEditable.editable("/api/node/" + nodeSlug + '?do=update', {
            indicator: "更新中...",
            type: 'textarea',
            name: 'node-intro',
            submitdata: {
                _method: "post",
                inline: 1
            },
            select: true,
            submit: '确定',
            cancel: '取消',
            cssclass: "editable",
            tooltip: '编辑'
        });
    }



    $doc.on('click', '#do-delete,.do-topic-delete', function(e) {
        e.preventDefault();
        var href = this.href;
        $.dialog({
            title: '提示',
            content: '确定删除吗，删除时相关评论也会被删除',
            ok: function() {
                location.href = href;
            },
            cancel: function() {},
            okVal: '删除',
            cancelVal: '取消'
        });
    });



    //帖子移动到其他节点
    $doc.on('click', '#do-transfer', function(e) {
        e.preventDefault();
        var id = $(this).data('id'),
            dialog = $.dialog({
                title: '移动帖子',
                content: '<label for="node-to-transfer">节点名字(注意不是别名)</label><br/><br/><input id="node-to-transfer" autofocus/>',
                okVal: '移动',
                cancelVal: '取消',
                ok: function() {
                    if($('#node-to-transfer').val() === '') {
                        alert('请输入节点名');
                        return false;
                    }
                    $.post('/api/post/' + id + '?do=transfer', {
                        "node-name": $('#node-to-transfer').val()
                    }, function(data) {
                        if(data.error === 0) {
                            alert('移动成功');
                            location.reload();
                        } else {
                            alert('转移失败，请确认节点名是否正确');
                        }
                    }, 'json');
                    return false;
                },
                cancel: function() {}
            });
    });

    $('.node-child').each(function() {
        var id = $(this).data('id'),
            name = $('.node-parent-' + id).data('name');
        if(name) {
            $(this).text(name);
        } else {
            $(this).text('');
        }
    });


    if(isAdminPage) {
        var messageMap = {
            'clearCompiledTemplate_success': '清除Smarty编译缓存成功!',
            'clearAllCache_success': '清除Smarty静态缓存成功!',
            'manualBackup_success': '手动备份数据库成功'
        },
            closeTip = '两秒后自动关闭';
        hash = document.location.hash.replace('#', '');
        if(messageMap[hash]) {
            $.dialog({
                'title': false,
                cancel: false,
                content: messageMap[hash],
                time: 2
            });
        }
    }

    $('.admin-nav a').each(function() {
        if(document.location.href.indexOf(this.href) !== -1) {
            $(this).addClass('current');
        }
    });



});