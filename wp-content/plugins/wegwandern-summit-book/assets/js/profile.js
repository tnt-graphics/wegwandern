var $ = jQuery;
$('.cancel-summit-book-user-profile').on('click', function () {
    window.location.href = profileObj.dashboardUrl;
    return false;
});
$('.delete-summit-book-user-profile').on('click', function () {
    var confirmationPopup = $('.user-confirmation-popup');
    $(confirmationPopup).find('.summit-delete-title').html('Konto löschen?');
    $(confirmationPopup).find('.summit-delete-content').html('Sind Sie sicher, dass Sie dieses Konto löschen möchten?');
    $(confirmationPopup).find('.summit-delete-edit-submit').attr('data-id', '');
    $(confirmationPopup).find('.summit-delete-type').val('user');
    jQuery(".overlay").removeClass('hide');
    jQuery(".user-confirmation").removeClass("hide");
    $('.summit-delete-edit-submit').on('click', function () {
        $.ajax({
            url: loginObj.ajaxUrl,
            type: 'post',
            data: { 'action': 'wegwandern_summit_book_delete_user_action' },
            dataType: 'json',
            success: function (resp) {
                if (resp.result === 'success') {
                    jQuery(".overlay").addClass('hide');
                    jQuery(".user-confirmation").addClass("hide");
                    window.location.href = resp.redirect;
                }
            }
        });
        return false;
    });
    return false;
});
$(document).ready(function () {
    $('#form_edit-user-profile-summit-book .frm_final_submit').on('click', function () {
        if($('#form_edit-user-profile-summit-book .frm_error').length <= 0) {
            $('#form_edit-user-profile-summit-book .delete-summit-book-user-profile').addClass('disabled');
        }
    });
   
});