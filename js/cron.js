/**
 * tool for cron job
 * @author airyland <i@mao.li>
 */

// update node info cache
$(function () {
    var lastNodeCacheSync = store.get('lastNodeCacheSync'),
        formatDate = function (date) {
            var year = date.getFullYear(),
                month = date.getMonth() + 1,
                day = date.getDate();
            return year + '-' + month + '-' + day;
        },
        today = formatDate(new Date),
        log = false;
    $.log = function ($string) {
        log && window.console && console.log($string);
    }
    if (today !== lastNodeCacheSync) {
        $.log('sync begin');
        $.get('/api/nodes/list', function (data) {
            for (var i = 0, len = data['nodes'].length; i < len; i++) {
                for (var j = 0, childLen = data['nodes'][i]['child_node'].length; j < childLen; j++) {
                    var node = data['nodes'][i]['child_node'][j];
                    store.set('/node/' + node['node_slug'], node);
                }
            }
            store.set('lastNodeCacheSync', today);
            $.log('sync end');
        }, 'json');
    } else {
        $.log('synced today');
    }


});