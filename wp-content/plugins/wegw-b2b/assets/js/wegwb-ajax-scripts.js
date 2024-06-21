var preview = false;

jQuery(document).ready(function ($) {

    /**
     * Load more ads on angebote page
     */
    $('#angebote-loadmore').on('click', function() {
        var itemcount = jQuery(".angebote-wander").length;
        var loadmore  = jQuery(".LoadMore");
        var listDiv   = jQuery('.angebote_list');
        var page_type = jQuery(".page_type").val();
        jQuery('#loader-icon').removeClass("hide");
        var data = {
            'action' : 'wegw_angebote_loadmore',
            'nonce': ajax_object.ajax_nonce,
            'count'  : itemcount,
            'page_type' : page_type,
        };

        jQuery.ajax({
            url: ajax_object.ajax_url,
            type: "post",
            data: data,
            beforeSend: function () {
            loadmore.addClass("active");
            },
            complete: function () {
            loadmore.removeClass("active");
            },
            success: function (response) {
            var posts = JSON.parse(response);
            var countp = jQuery(posts[0]).filter(".angebote-wander").length;
            console.log(countp);
            if (posts == "" || countp < 1) {
                console.log("empty");
                jQuery(".LoadMore").hide();
            }
            jQuery('#loader-icon').addClass("hide");

            if (countp > 0) {
                console.log(posts[0]);
                listDiv.append(posts[0]);
                jQuery('.angebote_list .angebote-wander a[target="_blank"]').addClass('externalLink');
                jQuery(".noWanderung").remove();
            } else {

                if (jQuery(".noWanderung").length < 1) {
                jQuery("#angebote-loadmore").before(posts);
                }
            }
            },
            error: function () {
            jQuery('#loader-icon').addClass("hide");
            },
        });
    });

    /**
     * Onclick save ads as `Draft` in ads create page.
     */
    $('#proceed-credits-section').on('click', function () {
        var b2b_ad_edit_mode = $('#edit_mode').val();
        var b2b_ad_kategorie = [];
        $('.b2b-ad-kategorie:checked').each(function () {
            b2b_ad_kategorie.push($(this).val());
        });

        /* Check for validation , if val is present or not */
        if ($('#uploaded-image').attr('src') === "" || $('#uploaded-image').attr('src') === undefined && (b2b_ad_edit_mode != 1)) {
         //   if (b2b_ad_edit_mode != 1) {
                //  $('.upload_error .required').addClass('error-bold');
                $('.cat_error').addClass("hide");
                $('.upload_error_message').removeClass('hide');
                $('.upload_error_message').html('Upload image');
                $('.region_error_message').addClass("hide");
                window.scrollTo({
                    top: $('.upload_error_message')[0].offsetTop,
                    behavior: 'smooth'
                });
                return;
          //  }
        }
        else if ($('#b2b-ad-title').val() == "") {
            $('.cat_error').addClass("hide");
            $('.upload_error_message').addClass('hide');
            $('#b2b-ad-title').focus();
            $('.region_error_message').addClass("hide");
            return;
        }
        // else if ($('#b2b-ad-main-title').val() === "") {
        //     $('.cat_error').addClass("hide");
        //     $('.upload_error_message').addClass('hide');
        //     $('#b2b-ad-main-title').focus();
        //     $('.region_error_message').addClass("hide");
        //     return;
        // }
        else if ($('#b2b-ad-description').val() === "") {
            $('.cat_error').addClass("hide");
            $('.upload_error_message').addClass('hide');
            $('#b2b-ad-description').focus();
            $('.region_error_message').addClass("hide");
            return;
        }
        else if ($('#b2b-ad-bold-descp').val() === "") {
            $('.cat_error').addClass("hide");
            $('.upload_error_message').addClass('hide');
            $('#b2b-ad-bold-descp').focus();
            $('.region_error_message').addClass("hide");
            return;
        }
        else if ($('#b2b-ad-link').val() === "") {
            $('.cat_error').addClass("hide");
            $('.upload_error_message').addClass('hide');
            $('#b2b-ad-link').focus();
            $('.region_error_message').addClass("hide");
            return;
        } else if (!(validateCheckbox())) {
            $('.upload_error_message').addClass('hide');
            $('.cat_error').removeClass("hide");
            $('.region_error_message').addClass("hide");
            window.scrollTo({
                top: $('.cat_error')[0].offsetTop,
                behavior: 'smooth'
            });
            return;
        }else if(document.getElementById('land').value === ''){
            $('.upload_error_message').addClass('hide');
            $('.cat_error').addClass("hide");
            $('.region_error_message').removeClass("hide");
            $('.region_error_message').html("Please select the land");
            window.scrollTo({
                top: $('.region_error_message')[0].offsetTop,
                behavior: 'smooth'
            });
            return;
        }

        var b2b_ad_image_val = $('#b2b-ad-img').prop('files')[0];
        var b2b_image_file_url = "";
        if (b2b_ad_edit_mode === "1") {
            if ($('#uploaded-image').attr('src') === "" || $('#uploaded-image').attr('src') === undefined) {
                b2b_image_file_url = $(".c_o_preview img")[0].src;
            } else {
                b2b_ad_image_val = $('#b2b-ad-img').prop('files')[0];
            }
        }

        var b2b_ad_id = $('#b2b_ad_id').val();
        var b2b_ad_land = $('#land').val();
        console.log(b2b_ad_land);
        var b2b_ad_region = $('#region').val();
        var b2b_ad_subregion = $('#subregion').val();
        var b2b_image_file_url = $(".c_o_preview img")[0].src;
        var b2b_ad_title = $('#b2b-ad-title').val();
        // var b2b_ad_main_title = $('#b2b-ad-main-title').val();
        var b2b_ad_description = $('#b2b-ad-description').val();
        var b2b_ad_bold_text = $('#b2b-ad-bold-descp').val();
        var b2b_ad_link = $('#b2b-ad-link').val();
        // var b2b_ad_image = $('#b2b-ad-img').prop('files')[0];
        var b2b_ad_image = b2b_ad_image_val;

        /* Form data preparation for passing to database */
        var form_data = new FormData();
        form_data.append('action', 'wegwb_ads_save_draft');
        form_data.append('nonce', ajax_object.ajax_nonce);
        form_data.append('file', b2b_ad_image);
        form_data.append('b2b_image_file_url', b2b_image_file_url);
        form_data.append('b2b_ad_id', b2b_ad_id);
        form_data.append('b2b_ad_edit_mode', b2b_ad_edit_mode);
        form_data.append('b2b_ad_kategorie', b2b_ad_kategorie.join(", "));
        form_data.append('b2b_ad_land', b2b_ad_land);
        form_data.append('b2b_ad_region', b2b_ad_region);
        form_data.append('b2b_ad_subregion', b2b_ad_subregion);
        form_data.append('b2b_ad_title', b2b_ad_title);
        // form_data.append('b2b_ad_main_title', b2b_ad_main_title);
        form_data.append('b2b_ad_description', b2b_ad_description);
        form_data.append('b2b_ad_bold_text', b2b_ad_bold_text);
        form_data.append('b2b_ad_link', b2b_ad_link);

        $.ajax({
            // dataType: 'text',
            cache: false,
            contentType: false,
            processData: false,
            url: ajax_object.ajax_url,
            type: "POST",
            data: form_data,
            beforeSend: function () {
            },
            complete: function () {
            },
            success: function (response) {
                console.log(response);
                if(response !== ""){
                    const data = JSON.parse(response);

                    if ("ad_title_duplicate_error" in data) {
                        $('.title_error_messgae').html(data.ad_title_duplicate_error);
                        $('.title_error_messgae').removeClass('hide');
                        window.scrollTo({
                            top: $('.title_error_messgae')[0].offsetTop,
                            behavior: 'smooth'
                        });
                        return;
                    }
                }else if(response === ""){
                    $('.title_error_messgae').addClass('hide');
                    $('.title_error_messgae').html("");
                    /* Preview is not shown */
                  //  if (!preview) {
                        previewAd();
                  //  }
                    /* Hide the div not required */
                    $('.c_o_category').addClass('hide');
                    $('.c_o_region_sec').addClass('hide');
                    $('.c_o_content').addClass('hide');
                    $('.c_o_proceed_content').removeClass('hide');
                    $('.edit').removeClass('hide');
                    $('.c_o_preview_wrap .deleteWrap').removeClass('hide');
                }
                
            },
            error: function () {
            },
        });
    });

    /**
     * Onclick save ads as `Pending` in ads create page credits selection section.
     */
    $('#b2b-ads-create-btn').on('click', function () {

        const radioButtons = document.querySelectorAll('input[type="radio"][name="b2b_credits"]');
        let isChecked = false;

        radioButtons.forEach(radioButton => {
            if (radioButton.checked) {
                isChecked = true;
                return;
            }
        });

        const validationDiv = document.querySelector('.klicksVal.required');
        const validationDivDate = document.querySelector('.dateVal.required');

        if ($('#wegwb_b2b_ads_credit_display').length === 0) {
            if(!$('#wegwb_b2b_ads_credit_select').hasClass('hide')){
                if (!isChecked) {
                    validationDiv.textContent = 'Please select an option.';
                    validationDivDate.textContent = '';
                    return;
                }
            }
            
        } else if ($('#wegwb_b2b_ads_credit_display').length > 0) {
            if ($('#wegwb_b2b_ads_credit_display')[0].checked) {
                if (!isChecked) {
                    validationDiv.textContent = 'Please select an option.';
                    validationDivDate.textContent = '';
                    return;
                }
            }
        }



       /* if (!isChecked) {
            validationDiv.textContent = 'Please select an option.';
            validationDivDate.textContent = '';
            return;
        }else*/ if (document.getElementById("clicksCustom").checked && $('.custom_clicks').val() === "") {
            validationDiv.textContent = "Please enter the custom klicks.";
            validationDivDate.textContent = '';
            return; // Prevent form submission
        } else if (document.getElementById('b2b-ad-activate-date').value.trim() === '') {
            validationDivDate.textContent = 'Please select a date.';
            validationDiv.textContent = '';
            return;
        } else {
            validationDiv.textContent = '';
            validationDivDate.textContent = '';
        }


        // verificationAngebote();
        var b2b_ad_id = $('#b2b_ad_id').val();
        var b2b_ad_edit_mode = $('#edit_mode').val();
        console.log(b2b_ad_id);
        var b2b_credits_count = $('input[name="b2b_credits"]:checked').val();
        var b2b_credits_count_custom = $('input[name="b2b_credits_count_custom"]').val();
        if( document.getElementById("clicksCustom").checked && b2b_credits_count_custom && b2b_credits_count_custom !== '' && b2b_credits_count_custom < 1 ) {
            validationDivDate.textContent = '';
            validationDiv.textContent = 'Please select a value greater than zero.';
            return;
        } else {
            validationDivDate.textContent = '';
            validationDiv.textContent = '';
        }
        var b2b_ad_activate_date = $('#b2b-ad-activate-date').val();
        var b2b_ad_end_date = $('#b2b-ad-activate-date-end').val();

        jQuery(".overlay").removeClass('hide');
        //   jQuery('body').addClass('scrollHide');
        $('#create_ad_response').html('<p>Loading...</p>');
        $('#create_ad_response').removeClass('hide');

        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                'action': "wegwb_ads_send_approval",
                'nonce': ajax_object.ajax_nonce,
                'b2b_ad_id': b2b_ad_id,
                'b2b_ad_edit_mode': b2b_ad_edit_mode,
                'b2b_credits_count': b2b_credits_count,
                'b2b_credits_count_custom': b2b_credits_count_custom,
                'b2b_ad_activate_date': b2b_ad_activate_date,
                'b2b_ad_end_date': b2b_ad_end_date,
            },
            success: function (response) {
                console.log(response);
                //  var data = $.parseJSON(response);
                var data = JSON.parse(response);
                $('#create_ad_response').html('<p>' + data.msg + '</p>');
                setTimeout(function () {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    }
                    jQuery(".overlay").addClass('hide');
                    $('#create_ad_response').addClass('hide');
                    //   jQuery('body').removeClass('scrollHide');

                }, 2000);
            },
            error: function () {
            },
        });
    });

    /**
     * Onclick delete ads in listing page
     */
    $('#b2b_ad_confirm_delete').on('click', function () {
        var b2b_ad_id ;
        if($('.b2b-statusPage').length > 0){
             b2b_ad_id = $(this).attr('data-id'); 
        }else{
             b2b_ad_id = $('#b2b_ad_id').val();
        }
       

        $.ajax({
            url: ajax_object.ajax_url,
            type: "POST",
            data: {
                'action': "wegwb_ads_delete",
                'nonce': ajax_object.ajax_nonce,
                'b2b_ad_id': b2b_ad_id,
            },
            success: function (response) {
                console.log(response);
                var data = JSON.parse(response);
                console.log(data);
                $('#delete_ad_response').html('<p>' + data.msg + '</p>');
                setTimeout(function () {
                    if (data.redirect_url != "") {
                        window.location.href = data.redirect_url;
                    } else {
                        location.reload(true);
                    }
                }, 2000);
            },
            error: function () {
            },
        });
    });

    /** functionality when "Speichern" is clicked*/
    $('.edit-mode-btn').on('click', function () {

        //check for validation , if val is present or not
        if ($('#uploaded-image').attr('src') === "" || $('#uploaded-image').attr('src') === undefined) {
            if ($('#edit_mode').val() != 1) {
                //  $('.upload_error .required').addClass('error-bold');
                $('.upload_error_message').removeClass('hide');
                $('.upload_error_message').html('Upload image');
                return;
            }
        }
        else if ($('#b2b-ad-title').val() == "") {
            $('.upload_error_message').addClass('hide');
            $('#b2b-ad-title').focus();
            return;
        }
        // else if ($('#b2b-ad-main-title').val() === "") {
        //     $('.upload_error_message').addClass('hide');
        //     $('#b2b-ad-main-title').focus();
        //     return;
        // }
        else if ($('#b2b-ad-description').val() === "") {
            $('.upload_error_message').addClass('hide');
            $('#b2b-ad-description').focus();
            return;
        }
        else if ($('#b2b-ad-bold-descp').val() === "") {
            $('.upload_error_message').addClass('hide');
            $('#b2b-ad-bold-descp').focus();
            return;
        }
        else if ($('#b2b-ad-link').val() === "") {
            $('.upload_error_message').addClass('hide');
            $('#b2b-ad-link').focus();
            return;
        }
        previewAd();
    });

    // Show/hide the custom input based on the radio button selection
    $('input[name="b2b_credits"]').change(function () {
        if ($(this).val() == 'custom') {
            $('.custom_clicks').removeClass('hide');
        } else {
            $('.custom_clicks').val('');
            $('.custom_clicks').addClass('hide');
            $('.klicksVal.required').html("");
        }
    });
});



// Function to check if at least one checkbox is checked
function validateCheckbox() {
    // Get all the checkboxes with the class "b2b-ad-kategorie"
    const checkboxes = $('.b2b-ad-kategorie');
    for (const checkbox of checkboxes) {
        if (checkbox.checked) {
            return true; // At least one checkbox is checked
        }
    }
    return false; // No checkbox is checked
}

/** click on edit icon on preview image */
function edit() {
    $('.cat_error').addClass("hide");
    $('.region_error_message').addClass("hide");
    $('.upload_error_message').addClass('hide');
    $('.edit').addClass('hide');
    $('.c_o_preview_wrap .deleteWrap').addClass('hide');
    $('.c_o_category').removeClass('hide');
    $('.c_o_region_sec').removeClass('hide');
    $('.c_o_content').removeClass('hide');
    $('.c_o_proceed_content').addClass('hide');
}

/**show the preview of the ad */
function previewAd() {

    // Get the elements in the target div
    var previewImage = document.querySelector('.c_o_preview img');
   // var subHeading = document.querySelector('.c_o_preview h6');
    var mainHeading = document.querySelector('.c_o_preview h5');
    var description = document.querySelector('.c_o_preview p');
    var link = document.querySelector('.c_o_preview a');


    if ($('#edit_mode').val() === "1") {
        if ($('#uploaded-image').attr('src') === "" || $('#uploaded-image').attr('src') === undefined) {
            previewImage.src = $(".c_o_preview img")[0].currentSrc;
        } else {
            previewImage.src = $('#uploaded-image').attr('src');
        }
    } else {
        previewImage.src = $('#uploaded-image').attr('src');
    }


    // Update the content of the elements with the form data
    // previewImage.src = $('#uploaded-image').attr('src');
    // mainHeading.textContent = $('#b2b-ad-title').val();
    // subHeading.textContent = $('#b2b-ad-main-title').val();
    mainHeading.textContent = $('#b2b-ad-title').val();
    // mainHeading.textContent = $('#b2b-ad-main-title').val();
    description.innerHTML = $('#b2b-ad-description').val() + ' <b> ' + $('#b2b-ad-bold-descp').val() + ' </b>';
    //link.href = $('#b2b-ad-link').val();
    // link.textContent = $('#b2b-ad-link').val();

    // Get the current href value
    const currentHref = $('#b2b-ad-link').val();

    // Remove the 'https://' part from the href using the replace method
    const newHref = currentHref.replace(/^https?:\/\//i, '');

    // Update the href of the link with the new value
    link.textContent = newHref;
    link.href = "https://" + newHref;
    link.target = '_blank';
    link.className = 'externalLink';
    preview = true;
}
function verificationAngebote() {
    var acceptanceChecked = document.querySelector('input[name="b2b_credits"]:checked');
    var datepickerValue = document.getElementById("b2b-ad-activate-date").value;

    if (!acceptanceChecked) {
        $('.klicksVal').html("Please select the klicks.");
        return false; // Prevent form submission
    } else {
        $('.klicksVal').html("");
    }

    if (datepickerValue === "") {
        $('.dateVal').html("Please select a date.");
        return false; // Prevent form submission
    } else {
        $('.dateVal').html("");
    }

    if (document.getElementById("clicksCustom").checked && $('.custom_clicks').value.trim() === "") {
        $('.klicksVal').html("Please enter the custom klicks.");
        return false; // Prevent form submission
    } else {
        $('.klicksVal').html("");
    }

    // The form will only submit if both validation checks pass
    return true;
}

/**
 * Onclick calculate Ad clicks
 */
function b2b_ad_click_calculate(ad_id) {
    $.ajax({
        url: ajax_object.ajax_url,
        type: "POST",
        data: {
            'action': "wegwb_ads_click_calculate",
            'nonce': ajax_object.ajax_nonce,
            'b2b_ad_id': ad_id,
        },
        success: function (response) {
            var expDate = new Date();
            // expDate.setTime(expDate.getTime() + (2 * 60 * 1000)); // add 2 minutes expiry
            // expDate.setTime(expDate.getHours() + 2); // add 2 hours expiry
            expDate.setTime(expDate.getTime() + (2*60*1000*60));
            /* Check COOKIE already set. If yes do not set again */
            if (typeof $.cookie('B2B_AD_CLICKED_' + ad_id) === 'undefined') {
                $.cookie('B2B_AD_CLICKED_' + ad_id, '1', {
                    path: '/',
                    expires: expDate
                });
            }
        },
        error: function () {
        },
    });
};

/**
 * Onclick calculate Ad clicks
 */
function kicksPopup(ad_id) {
    jQuery('.overlay').removeClass('hide');
    // jQuery('body').addClass('scrollHide');
    jQuery('#customTable').removeClass('hide');
    $('#ad_clicks_timeline_popup').html('Loading...');

    $.ajax({
        url: ajax_object.ajax_url,
        type: "POST",
        dataType: 'html',
        data: {
            'action': "wegwb_ads_clicks_popup_timeline_display",
            'nonce': ajax_object.ajax_nonce,
            'b2b_ad_id': ad_id,
        },
        success: function (response) {
            $('#ad_clicks_timeline_popup').html(response);

        },
        error: function () {
        },
    });
}

/**
 * Onclick display `Payrexx` modal box
 */
function b2b_ad_get_payment_modal() {
    $(".payrexx-modal-window").payrexxModal();
    var amount = $('input[name="b2b_credits"]:checked').data('price');

    $(".payrexx-modal-window").attr('data-href', "https://test-wegwandern.payrexx.com/de/pay?tid=f45e5040&invoice_amount=" + amount + ".00");
    $(".payrexx-modal-window")[0].click();
    // $(".payrexx-modal-window").attr('data-href', "");
}

/**
 * Filter subregion based on region - Ad create pation dropdown
 */
function b2b_ad_filter_subregion_from_region(region_id, subRegion, customSubRegion) {
    $.ajax({
        url: ajax_object.ajax_url,
        type: "POST",
        async: false, 
        data: {
            'action': "wegw_ads_filter_subregion_from_region",
            'nonce': ajax_object.ajax_nonce,
            'b2b_region_id': region_id,
        },
        success: function (response) {
            console.log(response);
            for (let r = subRegion[0].length - 1; r > 0; r--) {
                subRegion[0].remove(r);
            }
            subRegion[0].nextElementSibling.innerHTML = subRegion[0].options[0].innerHTML;
            customSubRegion.html("");
            subRegion.append(JSON.parse(response));
            var c, j, selElmnt;
            selElmnt =subRegion[0];
            for (j = 1; j < selElmnt.length; j++) {
                /*for each option in the original select element,
                create a new DIV that will act as an option item:*/
                c = document.createElement("DIV");
                c.innerHTML = selElmnt.options[j].innerHTML;
                c.setAttribute("value", selElmnt.options[j].value);
                c.addEventListener("click", function (e) {
                    /*when an item is clicked, update the original select box,
                    and the selected item:*/
                    var y, i, k, s, h, sl, yl, currentSelectDiv;
                    s = this.parentNode.parentNode.getElementsByTagName("select")[0];
                    currentSelectDiv = this.parentNode.parentNode.getElementsByTagName("select")[0].id;
                    sl = s.length;
                    h = this.parentNode.previousSibling;
                    for (i = 0; i < sl; i++) {
                        if (s.options[i].innerHTML == this.innerHTML) {
                            s.selectedIndex = i;
                            h.innerHTML = this.innerHTML;
                            y = this.parentNode.getElementsByClassName("same-as-selected");
                            yl = y.length;
                            for (k = 0; k < yl; k++) {
                                y[k].removeAttribute("class");
                            }
                            this.setAttribute("class", "same-as-selected");
                            break;
                        }
                    }
                    h.click();
                });
                customSubRegion[0].appendChild(c);
            }
        },
        error: function () {
        },
    });
}