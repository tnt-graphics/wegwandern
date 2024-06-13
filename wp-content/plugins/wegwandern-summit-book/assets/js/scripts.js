jQuery(document).ready(function ($) {
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
});
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

