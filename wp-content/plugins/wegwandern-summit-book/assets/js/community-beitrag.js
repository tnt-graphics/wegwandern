
function clearOptions(dropdown) {
    $(dropdown).html('<option>Select</option>').attr('disabled', 'disabled');
}

$(document).ready(function () {
    let titleField = $('#field_y6erv');
    let titleHiddenField = $('#field_ojs9m');
    if (typeof titleField !== undefined && typeof titleHiddenField !== undefined && titleField[0] !== undefined && titleHiddenField[0] !== undefined) {
        let titleFieldId = getFieldIdFromName(titleField[0].name);
        let titleHiddenFieldId = getFieldIdFromName(titleHiddenField[0].name);
        duplicateFormidableFieldContent(titleFieldId, titleHiddenFieldId);
    }
    
});
/** make grey button of save and publish when clicked on pulish button */
$('.publish-button').on('click', function () {
    let savedStatus = $('#field_tobcx');
    savedStatus.val('inVerification');
    $(savedStatus).change();
    $('.summit-book-create-article-form-section form#form_communitybeitragform').submit();
    if($('.summit-book-create-article-form-section .frm_error').length <= 0){
        $('.summit-book-create-article-form-section .frm_final_submit').addClass('disableButton');
        $(this).addClass('disableButton');
    }
    return false;
});
/**go to dashbord  when clicked on Schliessen*/
$('.cancel-button').on('click', function () {
    window.location.href = profileObj.dashboardUrl;
    return false;
});
/**show delete popup when clicked on delete icon in dashborad of article section */
$('.delete-hiking-article').on('click', function (e) {
    let entryIdText = e.target.id;
    processDeleteSummitBook(entryIdText, 'confirm', 'article');
    return false;
});

function activateDeleteSummitBook() {
    $('.summit-delete-edit-submit').on('click', function (e){
        let entryIdText = $('.summit-delete-edit-submit').attr('data-id');
        let type = $('.summit-delete-type').val();
        console.log(entryIdText);
        console.log(type);
        processDeleteSummitBook('delete_'+entryIdText, 'delete', type);
    });
}

