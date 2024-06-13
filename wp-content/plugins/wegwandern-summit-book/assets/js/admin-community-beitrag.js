jQuery(document).ready(function ($) {
    $('.admin-article-action').on('click', function () {
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
        let data = {
            'action': 'wegwandern_summit_book_community_beitrag_actions',
            'process': process,
            'post_id': parentPostId
        };
        $.post(beitragObj.ajaxUrl, data, function (resp) {
            location.reload(true);
        });
        return false;
    });
});