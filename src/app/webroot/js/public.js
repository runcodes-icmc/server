$(function() {
//    updateServerDateTime();
    

    function scrollToAnchor(aid){
        var aTag = $(aid);
        $('html,body').animate({scrollTop: aTag.offset().top},'slow');
    }
    $(".easyscroll").click(function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        scrollToAnchor(href);
    });			


    var delay = 700;
    $(".btn-hide-panel").on('click', function() {
        var panel = $(this).attr('data-panel');
        $("#panel-" + panel).animate({
            height: 0,
            padding: 0
        }, delay, function() {
            $.getJSON("/cookies/hidePanel/" + panel);
            $("#btn-hide-" + panel).hide();
            $("#btn-show-" + panel).show();
        });
    });
    $(".btn-show-panel").on('click', function() {
        var panel = $(this).attr('data-panel');
        var curHeight = $("#panel-" + panel).height();
        var autoHeight = $("#panel-" + panel).css('height', 'auto').height();
        $("#panel-" + panel).height(curHeight).animate({
            height: autoHeight,
            padding: 10
        }, delay, function() {
            $.getJSON("/cookies/showPanel/" + panel);
            $("#btn-hide-" + panel).show();
            $("#btn-show-" + panel).hide();
        });
    });
    $(".btn-toggle-panel").on('click', function(e) {
        e.preventDefault();
        var panel = $(this).attr('data-panel');
        if ($("#panel-" + panel).hasClass("panel-hide")) {
            $("#panel-" + panel).removeClass("panel-hide");
            var curHeight = $("#panel-" + panel).height();
            var autoHeight = $("#panel-" + panel).css('height', 'auto').height();
            $("#panel-" + panel).height(curHeight).animate({
                height: autoHeight,
                padding: 10
            }, delay);
        } else {
            $("#panel-" + panel).animate({
                height: 0,
                padding: 0
            }, delay, function() {
                $("#panel-" + panel).addClass("panel-hide");
            });
        }
    });
});
function updateServerDateTimeClient(data) {
    data.seconds = parseInt(data.seconds) + 1;
    if (data.seconds < 10) {
        data.seconds = '0' + data.seconds;
    } else {
        data.seconds = '' + data.seconds;
    }
    $('#server-time').html(data.mday + "/" + data.mon + "/" + data.year + " " + data.hours + ":" + data.minutes + ":" + data.seconds);
    if (data.seconds < 59) {
        setTimeout(function () {
            updateServerDateTimeClient(data);
        }, 1000);
    } else {
        setTimeout(updateServerDateTime, 1000);
    }
}
function updateServerDateTime() {
    $.getJSON('/getServerDateTimeJson', function(data) {
        $('#server-time').html(data.mday + "/" + data.mon + "/" + data.year + " " + data.hours + ":" + data.minutes + ":" + data.seconds);
        if (data.seconds < 58) {
            setTimeout(function () {
                updateServerDateTimeClient(data);
            }, 1000);
        } else {
            setTimeout(updateServerDateTime, 1000);
        }
    });
}