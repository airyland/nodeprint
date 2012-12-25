/**
 * admin js
 * @author airyland <i@mao.li>
 */

$(function() {

	//节点介绍信息编辑
	var $doc = $(document),
		$nodeEditable = $('.node-editable'),
		href = document.location.href;
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
		tooltip: '点击编辑'
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

	$('.node-child').each(function(){
		var id=$(this).data('id'),
	        name=$('.node-parent-'+id).data('name');
		if(name){
			$(this).text(name);
		}else{
			$(this).text('');
		}
	});


});