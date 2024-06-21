var $ = jQuery;
let singleHeartOnclickAttr = $('.single-wander-heart').attr('onclick');
if(typeof singleHeartOnclickAttr === 'undefined' || singleHeartOnclickAttr === false) {
    $('.single-wander-heart').on('click', function () {
        var heartIcon = $(this);
        if ($(this).hasClass('detail-single-wander-heart')) {
            let wanderungDiv = $('.wanderung').attr('id');
            let wanderungDivIdArray = wanderungDiv.split('-');
            var hikeId = wanderungDivIdArray[1];
        } else {
            var hikeId = $(this).parent().find('.single-wander-map').data('hikeid');
        }
        if (typeof hikeId === 'undefined' || hikeId === '') {
            return false;
        }
        if ($(this).hasClass('watchlisted') && $('body').hasClass('page-template-user-dashboard')) {
            processDeleteSummitBook('watchlist_' + hikeId, 'confirm', 'watchlist');
            return false;
        }
        if ($(this).hasClass('watchlisted')) {
            return false;
        }
        if ($('.summitLoginMenu').length > 0) {
            openSummitBookLoginMenu();
            let loginForm = $('.frm_login_form');
            let redirectField = loginForm.find('input[name="redirect_to"]');
            redirectField.val(window.location.href + '?watchlist-hikeId=' + hikeId);
            return false;
        } else {
            addToWatchlist(heartIcon, hikeId);
        }
    
    });
}
function addToWatchlist(iconLocation, hikeId) {
    if ($(iconLocation).hasClass('watchlisted')) {
        return false;
    }
    if ($('.summitLoginMenu').length > 0) {
        openSummitBookLoginMenu();
        let loginForm = $('.frm_login_form');
        let redirectField = loginForm.find('input[name="redirect_to"]');
        redirectField.val(window.location.href + '?watchlist-hikeId=' + hikeId);
        return false;
    }
    if ($('.userRolePopup').length > 0) {
        openUserRolePopup();
        return false;
    }
    $.ajax({
        url: loginObj.ajaxUrl,
        type: 'post',
        data: { 'action': 'wegwandern_summit_book_watchlist_hike', 'hikeId': hikeId },
        dataType: 'json',
        success: function (resp) {
            if (resp.result === 'success') {
                if (!$(iconLocation).hasClass('watchlisted')) {
                    $(iconLocation).addClass('watchlisted');
                }
                let onclickAttr = $(iconLocation).attr('onclick');
                if (typeof onclickAttr !== 'undefined' && onclickAttr !== false) {
                    $(iconLocation).removeAttr('onclick');
                }
            } else {
                console.log('error in watchlisting');
            }
        }
    });
}
$(document).ready(function () {
    if ($('.single-wander-heart').length > 0) {
        $.ajax({
            url: loginObj.ajaxUrl,
            type: 'get',
            data: { 'action': 'wegwandern_summit_book_user_watchlists' },
            dataType: 'json',
            success: function (resp) {
                if (typeof resp.watchlists !== 'undefined') {
                    let watchlists = resp.watchlists;
                    if (watchlists.length > 0) {
                        for (let i = 0; i < watchlists.length; i++) {
                            let element = watchlists[i];
                            if ($('*[data-hikeid="' + element + '"]').parent().hasClass('single-wander-img')) {
                                $('*[data-hikeid="' + element + '"]').parent().find('.single-wander-heart').addClass('watchlisted');
                            } else if ($('*[data-hikeid="' + element + '"]').parent().parent().parent().hasClass('wanderung-template-default')) {
                                $('.demo-gallery .single-wander-heart').addClass('watchlisted');
                            }
                        }
                    }
                }
            }
        });
    }
});