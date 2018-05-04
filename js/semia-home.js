
/*
var scroller = new slimScroll(document.getElementById('news-container-id'), { 'wrapperClass': 'scroll-wrapper unselectable mac',
                'scrollBarContainerClass': 'scrollBarContainer',
                'scrollBarContainerSpecialClass': 'animate',
                'scrollBarClass': 'scroll',
                'keepFocus': true});
window.onresize = function(){
    scroller.resetValues();  
}
*/

$(document).ready(function() {
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById("date").innerHTML = new Date().toLocaleDateString("it-IT", options);
    $('.expand-content').magnificPopup({
        type: 'inline',
        closeOnBgClick: true,
        closeBtnInside: true,
        showCloseBtn: true,
        midClick: true
    });
});

$(document).on('click', '.close-button', function(e) {
    e.preventDefault();
    $.magnificPopup.close();
});


