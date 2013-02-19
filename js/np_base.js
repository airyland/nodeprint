/**
 * general functions
 * @author airyland <i@mao.li>
 */

var NP = NP || {};

/**
 * Google Analytics track function
 */
NP.track = function(type, key) {
	if(!NPINFO.ga) {
		return;
	}
	_gaq.push(['_setAccount', NPINFO.ga]);
	//event track
	if(type === 'event') {
		var track = key.split('');
		track = track.split(' ');
		_gaq.push(['_trackEvent', track[0], track[1], track[2] ? track[2] : '']);
		//page track
	} else {
		key ? _gaq.push(['_trackPageview', key]) : _gaq.push(['_trackPageview']);
	}
}