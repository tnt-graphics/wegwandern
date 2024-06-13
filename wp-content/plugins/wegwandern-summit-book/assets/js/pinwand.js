var $ = jQuery;
$('.pinwand-create-btn').on('click', function () {
    /**check if the user is logged in, if loggd in go to dashborad else login form opens up*/
    if($('.summitLoginMenu').length > 0) {
       openSummitBookLoginMenu()
    } else {
        window.location.href = pinwandObj.inseratUrl;
    }
});
