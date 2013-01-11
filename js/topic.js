$(function(){
	var href = document.location.href,
	isTopicAdd = /node\/(.*?)\/add/.test(href),
	isTopicEdit=/t\/\d+\/edit/.test(href);
	console.log(isTopicEdit);

if(isTopicAdd||isTopicEdit){
	var $previewBtn=$('#preview-topic'),
		$form =$('#topic-add-form');
	$previewBtn.on('click',function(e){
		e.preventDefault();
		var oriAction= $form.attr('action'),
			preAction= '/api/post/0/preview';
		$form.attr('action',preAction).attr('target','_blank');
		$form.submit();
		$form.attr('action',oriAction).removeAttr('target');
	});
}
});