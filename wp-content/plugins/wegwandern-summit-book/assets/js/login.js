var $ = jQuery;

function openSummitBookLoginMenu() {
    valLogin("summitLoginMenu");
    $(".summitLoginMenu ").toggleClass("summitLoginWindow");
    scrollHeight();
    loginWindowPosition('summitLoginMenu');
    if($('.home').length > 0){
        $('.login').toggleClass("loginHome");  
    }
}

function openSummitBookNavigation(){
    $(".userNavigationMenu ").toggleClass("userNavigationWindow");
    loginWindowPosition('userNavigationMenu');
    if($('.home').length > 0){
        $('.login').toggleClass("loginHome");  
    }
}

function openSummitBookLoginMenuInKomment(){
    $('.summitLoginMenu input[name="redirect_to"]')[0].value = window.location.href + '?kommentar-login=yes'
    openSummitBookLoginMenu();
}

$(document).ready(function () {
    var imgSrc = $('.user-avatar-shortcode img').attr('src');
    if (typeof imgSrc === 'undefined') {
        $('.usr-avatar').addClass('default-avatar');
        return false;
    }
    $('.usr-avatar').css({ "background-image": "url(" + imgSrc + ")", "border-radius": "50%", "background-size": "cover" });
    $('.usr-avatar').addClass('summit-book-user-logged-in');
    $('.usr-avatar').removeClass('default-avatar');
});