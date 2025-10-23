(function ($) {
    if (typeof ajaxurl === "undefined") {
        ajaxurl = wpmfimport.vars.ajaxurl;
    }

    $(document).ready(function () {
        /**
         * Import size and filetype
         * @param lastId
         */
        var wpmfimport_meta_size = function (lastId) {
            var button_wmpfImportsize = document.getElementById('wmpfImportsize');
            var $this = jQuery('#wmpfImportsize');
            $this.find(".spinner").show().css({"visibility": "visible"});
            
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: "wpmf_import_size_filetype",
                    wpmf_last_id: lastId,
                    wpmf_nonce: wpmfimport.vars.wpmf_nonce
                },
                success: function (res) {
                    if (res.status) {
                        if (res.continue) {
                            button_wmpfImportsize.textContent = 'Synchronizing... (' + res.progress + ')';
                            wpmfimport_meta_size(parseInt(res.last_id));
                        } else {
                            $this.closest("div#wpmf_error").hide();
                        }
                    }
                }
            });
        };

        $('#wmpfImportsize').on('click', function () {
            wpmfimport_meta_size(0);
        });
    });
}(jQuery));
