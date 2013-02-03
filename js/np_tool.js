/**
 * 工具脚本
 * @author airyland <i@mao.li>
 */


// css auto reload
function reloadStylesheets() {
	var queryString = '?reload=' + new Date().getTime();
	$('link[rel="stylesheet"]').each(function() {
		this.href = this.href.replace(/\?.*|$/, queryString);
	});
}

setInterval(function() {
	reloadStylesheets();
}, 2000);