var $ = jQuery;

$(document).ready(function () {
    let titleField = $('#field_3ypso');
    let titleHiddenField = $('#field_ha1b1');
    if (typeof titleField !== undefined && typeof titleHiddenField !== undefined && titleField[0] !== undefined && titleHiddenField[0] !== undefined) {
        let titleFieldId = getFieldIdFromName(titleField[0].name);
        let titleHiddenFieldId = getFieldIdFromName(titleHiddenField[0].name);
        duplicateFormidableFieldContent(titleFieldId, titleHiddenFieldId);
    }
});

function getFieldIdFromName(titleFieldName) {
    const myArray = titleFieldName.split("[");
    const myArray2 = myArray[1].split("]");
    return myArray2[0];
}

/** make grey button of save and publish when clicked on pulish button */
$('.pinwand-ad-publish-button').on('click', function () {
    let savedStatus = $('#field_q2dk0');
    savedStatus.val('inVerification');
    $(savedStatus).change();
    $('.summit-book-create-pinwall-ad-form-section form#form_pinnwandeintragform').submit();
    if($('.summit-book-create-pinwall-ad-form-section .frm_error').length <= 0){
        $('.summit-book-create-pinwall-ad-form-section .frm_final_submit').addClass('disableButton');
        $(this).addClass('disableButton');
    }
    return false;
});

/**go to dashbord  when clicked on Schliessen*/
$('.pinwand-ad-cancel-button').on('click', function () {
    window.location.href = profileObj.dashboardUrl;
    return false;
});

/**show delete popup when clicked on delete icon in dashborad of pinwand section */
$('.delete-pinwand-ad').on('click', function (e) {
    let entryIdText = e.target.id;
    processDeleteSummitBook(entryIdText, 'confirm', 'pinwand-ad');
    return false;
});