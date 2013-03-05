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

$(function () {
    $(document).on('click', '.topic-list>li', function (e) {
        if (e.target.nodeName !== 'A') {
            $(this).find('.post-title').trigger('click');
        }
    });
});
	
	