jQuery(document).ready(function ($) {
    if ($('.region-dropdown').length > 0) {
        let entryId = '';
        var regionDropdown = $('.region-dropdown select');
        var subRegionDropdown = $('.subregion-dropdown select');

        if (typeof $('input[name=id]') !== undefined) {
            entryId = $('input[name=id]').val();
        }

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: { 'action': 'wegwandern_summit_book_region_dropdown', 'entryId': entryId },
            dataType: 'json',
            success: function (resp) {
                console.log(resp);
                regionDropdown.append(createOptions(resp.regions));
                var c;
                var selElmnt = regionDropdown[0];
                for (var j = 1; j < selElmnt.length; j++) {
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
                        sl = s.length;
                        h = this.parentNode.previousSibling;
                        for (i = 0; i < sl; i++) {
                            if (s.options[i].innerHTML == this.innerHTML) {
                                s.selectedIndex = i;
                                h.innerHTML = this.innerHTML;
                                if (this.parentNode.parentNode.classList.contains('region-dropdown')) {
                                    const regex = /value="([^"]+)"/;
                                    const match = this.outerHTML.match(regex);

                                    console.log(match[1]);
                                    b2b_ad_filter_subregion_from_region(match[1], $('.subregion-dropdown select'), $('.subregion-dropdown .select-items'));
                                    if(subRegionDropdown[0].nextElementSibling.nextElementSibling.children.length === 0){
                                        subRegionDropdown[0].nextElementSibling.classList.add('disable');
                                    }else{
                                        subRegionDropdown[0].nextElementSibling.classList.remove('disable');
                                    }

                                }
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

                    $('.region-dropdown select')[0].nextElementSibling.nextElementSibling.appendChild(c);
                }

                $(regionDropdown).on('change', function (e) {
                    let currentRegion = e.target.value;
                    let allParents = Object.keys(resp.subregions);
                    clearOptions(subRegionDropdown);
                    subRegionDropdown[0].nextElementSibling.classList.add('disable');
                    if (allParents.indexOf(currentRegion) >= 0) {
                        subRegionDropdown.append(createOptions(resp.subregions[currentRegion])).removeAttr('disabled');
                        b2b_ad_filter_subregion_from_region(currentRegion, $('.subregion-dropdown select'), $('.subregion-dropdown .select-items'));
                        subRegionDropdown[0].nextElementSibling.innerHTML = resp.entrySelectedSubRegionText;
                        if(subRegionDropdown[0].nextElementSibling.nextElementSibling.children.length === 0){
                            subRegionDropdown[0].nextElementSibling.classList.add('disable');
                        }else{
                            subRegionDropdown[0].nextElementSibling.classList.remove('disable');
                        }
                        
                    }
                });

                if (resp.entrySelectedRegion !== '') {
                    regionDropdown.val(resp.entrySelectedRegion);
                    regionDropdown[0].nextElementSibling.innerHTML = resp.entrySelectedRegionText;
                    $(regionDropdown).trigger('change');
                    if (resp.entrySelectedSubRegion !== '') {
                        subRegionDropdown.val(resp.entrySelectedSubRegion);
                        subRegionDropdown[0].nextElementSibling.innerHTML = resp.entrySelectedSubRegionText;
                    }
                }
            }
        });
    }
    $('#article-loadmore').on('click', function(){
        var itemcount = jQuery(".article-wander").length;
        var loadmore  = jQuery(".LoadMore");
        var listDiv   = jQuery('.article_list');
        jQuery('#loader-icon').removeClass("hide");
        var data = {
            'action' : 'wegwandern_summit_book_article_loadmore',
            'nonce': ajax_object.ajax_nonce,
            'count'  : itemcount,
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
            var countp = jQuery(posts[0]).filter(".article-wander").length;
            console.log(countp);
            if (posts == "" || countp < 1) {
                console.log("empty");
                jQuery(".LoadMore").hide();
            }
            jQuery('#loader-icon').addClass("hide");

            if (countp > 0) {
                console.log(posts[0]);
                listDiv.append(posts[0]);
                jQuery(".noWanderung").remove();
            } else {

                if (jQuery(".noWanderung").length < 1) {
                jQuery("#article-loadmore").before(posts);
                }
            }
            },
            error: function () {
            jQuery('#loader-icon').addClass("hide");
            },
        });
    });
    //re-arrenge the html such a way as XD in quick login form in hike detail page
    if(jQuery(".comment-desc-section .frm_login_form").length > 0){
        // valLogin();
         var formFields = jQuery('.comment-desc-section .frm_form_field.form-field');
         if (formFields.length > 0) {
           jQuery(formFields[1].append(formFields[2]));
           jQuery('div[style*=both]').remove();
     
           jQuery("<div id='valid_message'></div>").appendTo(formFields[2]);
     
           jQuery(".comment-desc-section .login_lost_pw a")[0].removeAttribute("href");
         }
         
         // Find the elements you want to wrap
         const frmSubmitDiv = document.querySelector('.comment-desc-section .frm_submit');
         const loginLostDiv = document.querySelector('.comment-desc-section .login_lost_pw');
 
         // Create a new div element to wrap the elements
         const wrapperDiv = document.createElement('div');
         wrapperDiv.classList.add('hikeLoginRememSubmitWrapper'); // Replace 'your-custom-class' with your desired class name
 
         // Append the wrapper div before the frm_submit div
         frmSubmitDiv.parentNode.insertBefore(wrapperDiv, frmSubmitDiv);
 
         // Move the frm_submit and login-remember divs into the wrapper div
         wrapperDiv.appendChild(frmSubmitDiv);
         wrapperDiv.appendChild(loginLostDiv);
     }
     /**img slider in community comments section*/
     jQuery('.comment-imgs').owlCarousel({
        nav:true,
        dots:false,
        loop:false,
        navText: ["<div class='nav-btn prev-slide'></div>","<div class='nav-btn next-slide'></div>"],
        responsive : {
            0 : {
                margin:9, 
                items:3
            },
            700 : {
                margin:15, 
                items:3
            },
            900 : {
                margin:15, 
                items:3
            },
            1200 : {
                margin:18,
                items:3
                
            }
        }
    });
    /**img slider in community comments section*/
    jQuery('.user-navigation.horizontal .menu-summit-book-user-menu-container ul').owlCarousel({
        loop: false,
        autoWidth:true,
        dots:false,
        nav:false,
         autoplay:false,
         responsive : {
            0 : {
              items:1,
            },
            700 : {
             items:1,
            },
            900 : {
              items:1,
            },
            1200 : {
                items:1,
                
            }
        }
    });

    if(jQuery('.avatar-with-nickname').length > 0){
        jQuery('.avatar-with-nickname').append('<h2 class="summit_nickname">'+ profileObj.userNickname+'</h2>')
    }
    if(jQuery('#form_edit-user-profile-summit-book').length > 0){
       if(profileObj.hideDeleteProfile === "yes"){
           jQuery('.delete-summit-book-user-profile').addClass('hide');
       }else{
           jQuery('.delete-summit-book-user-profile').removeClass('hide');
       }
   }

    var imgSrc = $('.user-avatar-shortcode img').attr('src');
    if (typeof imgSrc === 'undefined') {
        $('.usr-avatar').addClass('default-avatar');
        return false;
    }
    $('.usr-avatar').css({ "background-image": "url(" + imgSrc + ")", "border-radius": "50%", "background-size": "cover" });
    $('.usr-avatar').addClass('summit-book-user-logged-in');
    $('.usr-avatar').removeClass('default-avatar');


    let titleField = $('#field_y6erv');
    let titleHiddenField = $('#field_ojs9m');
    if (typeof titleField !== undefined && typeof titleHiddenField !== undefined && titleField[0] !== undefined && titleHiddenField[0] !== undefined) {
        let titleFieldId = getFieldIdFromName(titleField[0].name);
        let titleHiddenFieldId = getFieldIdFromName(titleHiddenField[0].name);
        duplicateFormidableFieldContent(titleFieldId, titleHiddenFieldId);
    }

    let pinwandtitleField = $('#field_3ypso');
   let pinwandtitleHiddenField = $('#field_ha1b1');
   if (typeof pinwandtitleField !== undefined && typeof pinwandtitleHiddenField !== undefined && pinwandtitleField[0] !== undefined && pinwandtitleHiddenField[0] !== undefined) {
       let pinwandtitleFieldId = getFieldIdFromName(pinwandtitleField[0].name);
       let pinwandtitleHiddenFieldId = getFieldIdFromName(pinwandtitleHiddenField[0].name);
       duplicateFormidableFieldContent(pinwandtitleFieldId, pinwandtitleHiddenFieldId);
   }

    /** make grey button of save and publish when clicked on pulish button */
   $('.publish-button').on('click', function () {
       let savedStatus = $('#field_tobcx');
       savedStatus.val('inVerification');
       $(savedStatus).change();
   // $('.frm_final_submit').attr('disabled', 'disabled');
       //$(this).attr('disabled', 'disabled');
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

   
   /** make grey button of save and publish when clicked on pulish button */
   jQuery('.pinwand-ad-publish-button').on('click', function () {
       let savedStatus = jQuery('#field_q2dk0');
       savedStatus.val('inVerification');
       jQuery(savedStatus).change();
       // $('.frm_final_submit').attr('disabled', 'disabled');
       // $(this).attr('disabled', 'disabled');
       // $('form').submit();
       $('.summit-book-create-pinwall-ad-form-section form#form_pinnwandeintragform').submit();
       if($('.summit-book-create-pinwall-ad-form-section .frm_error').length <= 0){
           $('.summit-book-create-pinwall-ad-form-section .frm_final_submit').addClass('disableButton');
           $(this).addClass('disableButton');
       }
       return false;
   });
   /**go to dashbord  when clicked on Schliessen*/
   jQuery('.pinwand-ad-cancel-button').on('click', function () {
       window.location.href = profileObj.dashboardUrl;
       return false;
   });
   /**show delete popup when clicked on delete icon in dashborad of pinwand section */
   jQuery('.delete-pinwand-ad').on('click', function (e) {
       let entryIdText = e.target.id;
       processDeleteSummitBook(entryIdText, 'confirm', 'pinwand-ad');
       return false;
   });

   jQuery('.pinwand-create-btn').on('click', function () {
       /**check if the user is logged in, if loggd in go to dashborad else login form opens up*/
       if($('.summitLoginMenu').length > 0) {
       openSummitBookLoginMenu()
       } else {
           window.location.href = pinwandObj.inseratUrl;
       }
   });
   jQuery('.show-full-comment').on('click', function(){
       $(this).parent().parent().find('.long-comment-version').removeClass('hide');
       $(this).parent().addClass('hide');
   });
   jQuery('.delete-comment').on('click', function (e) {
       let commentIdText = e.target.id;
       processDeleteSummitBook(commentIdText, 'confirm', 'comment');
       return false;
   });
});
function summitUserRoleSubmit(){
    var data = {
        'action' : 'wegwandern_summit_book_add_b2b_user_role_action'
    };
   
    jQuery.ajax({
        url: ajax_object.ajax_url,
        type: "post",
        data: data,
        success: function (response) {
            location.reload();
        },
        error: function () {
        },
    });
}
function processDeleteSummitBook(entryIdText, process, item) {
    let entryArray = entryIdText.split('_');
    var entryId = entryArray[1];
    var confirmationPopup = $('.user-confirmation-popup');
    if(process != 'confirm') {
        jQuery(".overlay").addClass('hide');
        jQuery(".user-confirmation").addClass("hide");
        let currentDeletedDiv = jQuery("#data-" + item + '-' + entryId);
        if (currentDeletedDiv.length > 0) {
            let parentDiv = currentDeletedDiv.parent();
            currentDeletedDiv.remove();
            let parentLength = parentDiv.children().length;
            if(item === "watchlist") {
                parentLength = parentDiv.children().find('div').length;
            }
            if (parentLength === 0) {
                let errorMessage = jQuery("#data-" + item + "-error").val();
                if (item === "watchlist") {
                    parentDiv.parent().parent().append('<div class="message user-dash-no-content-msg">' + errorMessage + '</div>');
                } else {
                    parentDiv.parent().append('<div class="message user-dash-no-content-msg">' + errorMessage + '</div>');
                }
            }
        }
    }
    $.ajax({
        url: ajax_object.ajax_url,
        type: 'post',
        data: {
            'action': 'wegwandern_summit_book_delete_summit_book',
            'entryId': entryId,
            'process': process,
            'item': item
        },
        dataType: 'json',
        success: function (resp) {
            if (resp.process === 'confirm') {
                $(confirmationPopup).find('.summit-delete-title').html(resp.outputArray.title);
                $(confirmationPopup).find('.summit-delete-content').html(resp.outputArray.content);
                $(confirmationPopup).find('.summit-delete-edit-submit').attr('data-id', resp.outputArray.data_id);
                $(confirmationPopup).find('.summit-delete-type').val(resp.outputArray.type);
                // $(confirmationPopup).removeClass('hide');
                activateDeleteSummitBook();
                jQuery(".overlay").removeClass('hide');
                jQuery(".user-confirmation").removeClass("hide");
            }
        },
    });
}
function summitCloseDeletePopup() {
    jQuery(".user-confirmation").addClass("hide");
    jQuery(".overlay").addClass('hide');
}
function createOptions(regions) {
    let options = "";
    let regionKeys = Object.keys(regions);
    let regionLength = regionKeys.length;
    for (let i = 0; i < regionLength; i++) {
        let currentKey = regionKeys[i];
        options = options + "<option value='" + currentKey + "'>" + regions[currentKey] + "</option>";
    }
    return options;
}

function duplicateFormidableFieldContent(from, to) {
    $('textarea[name="item_meta[' + from + ']"]').keypress(function () {
        $('input[name="item_meta[' + to + ']"]').val($(this).val());
    });

    $('textarea[name="item_meta[' + from + ']"]').keydown(function () {
        $('input[name="item_meta[' + to + ']"]').val($(this).val());
    });

    $('textarea[name="item_meta[' + from + ']"]').blur(function () {
        $('input[name="item_meta[' + to + ']"]').val($(this).val());
    });
}
function openSummitBookLoginMenu() {
    // $('.summit-login').removeClass('hide');
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
 function clearOptions(dropdown) {
    $(dropdown).html('<option>Select</option>').attr('disabled', 'disabled');
}
function activateDeleteSummitBook() {
    $('.summit-delete-edit-submit').on('click', function (e){
        let entryIdText = $('.summit-delete-edit-submit').attr('data-id');
        let type = $('.summit-delete-type').val();
        console.log(entryIdText);
        console.log(type);
        processDeleteSummitBook('delete_'+entryIdText, 'delete', type);
    });
}
function getFieldIdFromName(titleFieldName) {
    const myArray = titleFieldName.split("[");
    const myArray2 = myArray[1].split("]");
    return myArray2[0];
}
