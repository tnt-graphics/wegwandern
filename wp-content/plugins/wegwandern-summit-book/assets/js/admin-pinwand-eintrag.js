jQuery(document).ready(function ($) {
    $('.admin-pinwand-ad-action').on('click', function () {
        let process = '';

        if ($(this).hasClass('admin-publish')) {
            process = 'publish';
        } else if ($(this).hasClass('admin-reject')) {
            process = 'reject';
        }

        let parentPost = $(this).parent().parent();
        let parentPostIdText = $(parentPost).attr('id');
        let parentPostArray = parentPostIdText.split('-');
        let parentPostId = parentPostArray[1];

        console.log(process)
        console.log(parentPostId)
        let data = {
            'action': 'wegwandern_summit_book_pinnwand_eintrag_actions',
            'process': process,
            'post_id': parentPostId
        };
        $.post(beitragObj.ajaxUrl, data, function (resp) {
            location.reload(true);
        });
        return false;
    });
});
