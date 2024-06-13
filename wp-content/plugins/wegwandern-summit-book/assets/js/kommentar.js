var $ = jQuery;
$(document).ready(function () {
    if ($('#form_commentsform').length > 0) {
        $(document).on('frmFormComplete', function (event, form, response) {
            var tourIdDiv = $('#field_fso1z');
            if (tourIdDiv.val() === '') {
                let wanderungDiv = $('.wanderung').attr('id');
                let wanderungDivIdArray = wanderungDiv.split('-');
                var hikeId = wanderungDivIdArray[1];
                tourIdDiv.val(hikeId);
            }
        });
    }
});
$('.show-full-comment').on('click', function(){
    $(this).parent().parent().find('.long-comment-version').removeClass('hide');
    $(this).parent().addClass('hide');
});
$('.delete-comment').on('click', function (e) {
    let commentIdText = e.target.id;
    processDeleteSummitBook(commentIdText, 'confirm', 'comment');
    return false;
});