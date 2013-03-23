/**
 * jQuery.crSpline v0.0.1
 * http://github.com/MmmCurry/jquery.crSpline
 *
 * Supports animation along Catmull-Rom splines based on a series of waypoints.
 * Usage: See demo.js, demo.html
 *
 * Copyright 2010, M. Ian Graham
 * MIT License
 *
 */

(function ($) {
    $.crSpline = {};
    // Catmull-Rom interpolation between p0 and p1 for previous point p_1 and later point p2
    // http://en.wikipedia.org/wiki/Cubic_Hermite_spline#Catmull.E2.80.93Rom_spline
    var interpolate = function (t, p_1, p0, p1, p2) {
        return Math.floor((t * ((2 - t) * t - 1) * p_1 + (t * t * (3 * t - 5) + 2) * p0 + t * ((4 - 3 * t) * t + 1) * p1 + (t - 1) * t * t * p2) / 2);
    };

    // Extend this p1,p2 sequence linearly to a new p3
    var generateExtension = function (p1, p2) {
        return [
            p2[0] + (p2[0] - p1[0]), p2[1] + (p2[1] - p1[1])];
    };

    // Return an animation object based on a sequence of points
    // pointList must be an array of [x,y] pairs
    $.crSpline.buildSequence = function (pointList) {
        var res = {},
            seq = [],
            numSegments;
        if (pointList.length < 2) {
            throw "crSpline.buildSequence requires at least two points";
        }
        // Generate the first p_1 so the caller doesn't need to provide it
        seq.push(generateExtension(pointList[1], pointList[0]));

        // Throw provided points on the list
        for (var i = 0; i < pointList.length; i++) {
            seq.push(pointList[i]);
        }
        // Generate the last p2 so the caller doesn't need to provide it
        seq.push(generateExtension(seq[seq.length - 2], seq[seq.length - 1]));
        numSegments = seq.length - 3;
        res.getPos = function (t) {
            // XXX For now, assume all segments take equal time
            var segNum = Math.floor(t * numSegments);
            if (segNum === numSegments) {
                return {
                    left:seq[seq.length - 2][0],
                    top:seq[seq.length - 2][1]
                };
            }
            var microT = (t - segNum / numSegments) * numSegments;
            return {
                left:interpolate(microT, seq[segNum][0], seq[segNum + 1][0], seq[segNum + 2][0], seq[segNum + 3][0]) + "px",
                top:interpolate(microT, seq[segNum][1], seq[segNum + 1][1], seq[segNum + 2][1], seq[segNum + 3][1]) + "px"
            };
        };
        return res;
    };
    $.fx.step.crSpline = function (fx) {
        var css = fx.end.getPos(fx.pos);
        for (var i in css) {
            fx.elem.style[i] = css[i];
        }
    };
})(jQuery);

/*
 * jQuery throttle / debounce - v1.1 - 3/7/2010
 * http://benalman.com/projects/jquery-throttle-debounce-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function(b,c){var $=b.jQuery||b.Cowboy||(b.Cowboy={}),a;$.throttle=a=function(e,f,j,i){var h,d=0;if(typeof f!=="boolean"){i=j;j=f;f=c}function g(){var o=this,m=+new Date()-d,n=arguments;function l(){d=+new Date();j.apply(o,n)}function k(){h=c}if(i&&!h){l()}h&&clearTimeout(h);if(i===c&&m>e){l()}else{if(f!==true){h=setTimeout(i?k:l,i===c?e-m:e)}}}if($.guid){g.guid=j.guid=j.guid||$.guid++}return g};$.debounce=function(d,e,f){return f===c?a(d,e,false):a(d,f,e!==false)}})(this);

var isScrolledIntoView =  function (elem)
{
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    var elemTop = $(elem).offset().top;
    var elemBottom = elemTop + $(elem).height();

    return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom)
      && (elemBottom <= docViewBottom) &&  (elemTop >= docViewTop) );
}

$(function () {
    $(document).on('click', '#preview-topic', function (e) {
        e.preventDefault();
        $form = $('#topic-add-form');
        var oriAction = $form.attr('action'),
            preAction = '/api/post/0/preview';
        $form.attr('action', preAction).attr('target', '_blank');
        $form.submit();
        $form.attr('action', oriAction).removeAttr('target');
    });


    $(document).on('click', '#do-fav', function (e) {
        if (NPINFO.isMobile) {
            return;
        }
        var $this = $(this),
            clickCount = $this.data('clickCount');
        if(clickCount===undefined) clickCount = 0;
        $this.data('clickCount',++clickCount);
        if(++clickCount>6){
            $.dialog({id: 'clickTip', title: false, content: '不要玩啦', cancel: false, time: 1});
            return;
        }
        var $doFav = $('#do-fav'),
            $count = $('#JS_fav_topic').find('.count-no').eq(0),
            oCount = $count.text();
        e.preventDefault();
        if ($(this).attr('class') !== 'unfav-link') {
            $count.text(parseInt(oCount) - 1);
            return;
        }
        var position = $doFav.offset(),
            desPositon = $count.offset(),
            $fav = $('<a class="fav-link" style="position:absolute;left:' + position.left + 'px;top:' + position.top + 'px;"/>').appendTo($('body'));
        $fav.show();
        $fav.animate({
            crSpline:$.crSpline.buildSequence([
                [position.left, position.top],
                [desPositon.left - 50, desPositon.top - 20],
                [desPositon.left + 34, desPositon.top + 8]
            ]),
            duration:20000
        }, 1000, function () {
            $fav.fadeOut().remove();
            $count.text(parseInt(oCount) + 1);
        });
    });
});

$(function() {

    // handle resize event
    $(window).resize($.throttle(1000, function() {
        if (document.location.hash === '#reply-area') {
            $('html, body').animate({
                scrollTop: $('#reply-area').offset().top
            }, 800);
        }
    }));

    // handle scroll event
    if (/\/t\/\d/.test(document.location.href)) {
        $(window).scroll($.throttle(500, function() {
            var isInView = isScrolledIntoView('#reply-area'),
                oriHref = document.location.href;
            href = oriHref.indexOf('#') > 0 ? oriHref.slice(0, oriHref.indexOf('#')) : oriHref;
            if (isInView) {
                history.pushState(null, null, href + '#reply-area');
            } else {
                if (document.location.hash === '#reply-area') {
                    history.pushState(null, null, href);
                }
            }
        }));
    }
});
	