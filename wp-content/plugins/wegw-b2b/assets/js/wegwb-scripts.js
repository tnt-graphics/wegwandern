jQuery(document).ready(function ($) {

  /** for pages with only header and footer , set min-height for the main*/
  var footerHeight = $('footer').innerHeight();
  var headerHeight = $('header').innerHeight();
  var mainHeight = $('main').innerHeight();
  var windowHeight = window.innerHeight;
  var minHeight = windowHeight - (headerHeight + footerHeight);
  if(mainHeight < minHeight){
    $('main').css('min-height', minHeight);
  }

  
  /**login validation */
  $('.summitLoginMenu .login-username input').on("keyup", function () {
    valLogin("summitLoginMenu");
  });
  $('.summitLoginMenu .login-password input').on("keyup", function () {
    valLogin("summitLoginMenu");
  });
  $('.loginMenu.fullwidthB2BLogin .login-username input').on("keyup", function () {
    valLogin("fullwidthB2BLogin");
  });
  $('.loginMenu.fullwidthB2BLogin .login-password input').on("keyup", function () {
    valLogin("fullwidthB2BLogin");
  });
  $('.comment-desc-section .login-username input').on("keyup", function () {
    valLogin("comment-desc-section");
  });
  $('.comment-desc-section .login-password input').on("keyup", function () {
    valLogin("comment-desc-section");
  });

  /**img slider in community comments section*/
  jQuery('.c_o_menu_wrapper .c_o_menu_list_wrap ul').owlCarousel({
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


  // $('.forgot-password ').on('click', function(){
  //   jQuery('body').addClass('posFixed');
  // });

  /** Add class 'externalLink' for the a tag  where there is external link using target _blank */
  jQuery('.b2b-status-list .b2b-item-content a[target="_blank"]').addClass('externalLink');
  jQuery('.angebote_list .angebote-wander a[target="_blank"]').addClass('externalLink');

  //$(".dz-message.needsclick .frm_upload_icon").attr('title', 'File size should be less than 1MB');

  /**datatable in status page */
  if($('.b2b-table-list').length > 0){
    $('.b2b-table-list').DataTable({
      responsive: true,
      language: {
        paginate: {
          next: 'Vor', // or '→'
          previous: 'Zurück' // or '←' 
        }
      },
      columnDefs: [
        { targets: 0, width: '35%', orderable: false }, 
        { targets: 1, width: '22%', orderable: false }, 
        { targets: 2, width: '10%', orderable: false }, 
        { targets: 3, width: '9%', orderable: false }, 
        { targets: 4, width: '9%', orderable: false }, 
        { targets: 5, width: '15%', orderable: false }, 
      ]
    });
  }
  
  
  $('.login-remember input').prop('checked', true);

  // Initialize the date picker
  if($("#b2b-ad-activate-date").length > 0){
    $("#b2b-ad-activate-date").datepicker({
      minDate: "+3D", // Minimum selectable date is 3 days after the current date
      beforeShowDay: function (date) {
        var isEnabled = isDateEnabled(date, "start");
        return [isEnabled];
      },
      onSelect: function (selectedDate) {
        // When the start date is selected, update the end date's minDate
        var endDatePicker = $("#b2b-ad-activate-date-end");
        var startDate = $(this).datepicker("getDate");
    
        // Set the minimum date for end date as the selected start date + 1 day
        var minEndDate = new Date(startDate);
        minEndDate.setDate(startDate.getDate() + 1);
    
        // Update the minDate option for the end date datepicker
        endDatePicker.datepicker("option", "minDate", minEndDate);
    
        // If the current selected end date is before the updated minEndDate, clear the end date selection
        var endDate = endDatePicker.datepicker("getDate");
        if (endDate && endDate < minEndDate) {
          endDatePicker.val("");
        }
      },
      dateFormat: "dd/mm/yy"
    });
  }
  

  // Initialize the date picker
  if($("#b2b-ad-activate-date-end").length > 0){
    $("#b2b-ad-activate-date-end").datepicker({
      minDate: "+3D",// Minimum selectable date is 3 days after the current date
      beforeShowDay: function (date) {
        var isEnabled = isDateEnabled(date, "end");
        return [isEnabled];
      },
      dateFormat: "dd/mm/yy"
    });
  }
  

  // Check if user need to update credits for ad in Edit mode
  $('#wegwb_b2b_ads_credit_display').click(function (e) {
    if (this.checked) {
      $('#wegwb_b2b_ads_credit_select').removeClass('hide');
    } else {
      $('#wegwb_b2b_ads_credit_select').addClass('hide');
      let radioButtons = document.querySelectorAll('input[type="radio"][name="b2b_credits"]');
      radioButtons.forEach(radioButton => {
          if (radioButton.checked) {
              $(radioButton).prop('checked', false);
          }
      });
    }
  });

  /**login menu */
  if (jQuery(".login_content_wrapper").length > 0) {
  //summit book login
  if(jQuery(".summitLoginMenu").length > 0){
    var formFields = jQuery('.login_content_wrapper .frm_form_field.form-field');
    if (formFields.length > 0) {
      jQuery(formFields[1].append(formFields[2]));
      jQuery('div[style*=both]').remove();

      jQuery("<div id='valid_message'></div>").appendTo(formFields[2]);

      jQuery(".login_lost_pw a")[0].removeAttribute("href");
    }
  }

  // b2b login
  if(jQuery(".loginMenu.fullwidthB2BLogin").length > 0){
    var formFields = jQuery('.fullwidthB2BLogin .login_content_wrapper .frm_form_field.form-field');
    if (formFields.length > 0) {
      jQuery(formFields[1].append(formFields[2]));
      jQuery('div[style*=both]').remove();
  
      jQuery("<div id='valid_message'></div>").appendTo(formFields[2]);
  
      jQuery(".login_lost_pw a")[0].removeAttribute("href");
    }
  }
  }

  /** Reset password div*/
  if (jQuery(".reset-paswd-frm").length > 0) {
    jQuery(".reset-paswd-frm .frm_primary_label")[0].innerText = b2b_params.paswd_label;
    jQuery(".reset-paswd-frm .frm_primary_label")[1].innerText = b2b_params.conf_paswd_label;
  }
  if (jQuery(".resetMenu").length > 0) {
    jQuery(".resetMenu .frm_primary_label")[0].innerText = b2b_params.email_label;
  }
  if (jQuery(".summitResetMenu").length > 0) {
    jQuery(".summitResetMenu .frm_primary_label")[0].innerText = b2b_params.email_label;
  }
  if (jQuery(".commentResetMenu #lostpasswordform_0").length > 0) {
    jQuery(".commentResetMenu .frm_primary_label")[0].innerText = b2b_params.email_label;
  }
  if($('.edit-profile-form').length > 0){
    jQuery(".edit-profile-form .frm_conf_field label")[0].innerText = b2b_params.b2b_edit_prof_conf_paswd_label;
  }
  //summit edit profile
  if($('.user-edit-profile-page').length > 0){
    jQuery(".user-edit-profile-page .frm_conf_field label")[0].innerText = profileObj.confirmPassLabel;
  }

  // When the user starts to type something inside the password field
  var divElement = "<div class='profile_valid_message'></div>";
  var strengthPassDiv = "<progress id='strengthProgressBar' value='0' max='5'></progress>";
  $(".fld-paswd").after(divElement);
  jQuery(".reset-paswd-frm .frm_form_fields input").eq(0).after(divElement);
  // Insert the strengthPassDiv after the divElement
  $(strengthPassDiv).insertAfter(".profile_valid_message");
  $(".fld-paswd").on("keyup", function () {
    var messageElement = $(this)[0].nextElementSibling;
    var progressBarElement = $(this)[0].nextElementSibling.nextElementSibling;
    /**check if its profile page password field */
    if($('.b2b_paswd').length > 0){
      $('.edit-profile-form .frm_final_submit').addClass('disableButton');
      validatePassword(this, messageElement, progressBarElement);
      if (strength == 5 || $('.b2b_paswd input').val() === "") {
        $('.edit-profile-form .frm_final_submit').removeClass('disableButton');
      } else {
        $('.edit-profile-form .frm_final_submit').addClass('disableButton');
      }
    }else{
      /**other than profile password */
      validatePassword(this, messageElement, progressBarElement);
      if (strength == 5) {
        $('.regWindow .frm_final_submit').removeClass('disableButton');
      } else {
        $('.regWindow .frm_final_submit').addClass('disableButton');
      }
    } 
  });


  jQuery('.edit-profile-form .b2b_paswd input').on('keyup', function(){   
    var pwd = jQuery('.edit-profile-form #field_b2b_prof_frm_user_pwd').val();
    var conform_pwd = jQuery('.edit-profile-form #field_conf_b2b_prof_frm_user_pwd').val();
    if( pwd === conform_pwd ){
      $('.edit-profile-form .frm_final_submit').removeClass('disableButton');
    }else{
       $('.edit-profile-form .frm_final_submit').addClass('disableButton');
    } 
  });


  jQuery(".reset-paswd-frm input").eq(0).on("keyup", function () {
    var messageElement = $(this)[0].nextElementSibling;
    var progressBarElement = $(this)[0].nextElementSibling.nextElementSibling;
    validatePassword(this, messageElement, progressBarElement);
    if (strength == 5) {
      $('.reset-paswd-frm .frm_submit input').removeClass('disableButton');
    } else {
      $('.reset-paswd-frm .frm_submit input').addClass('disableButton');
    }
  });

  /**email id change on keyup is passed to a hidden field */
  $(".email-field input").on("keyup", function () {
   $('#field_hidden_email').val($(".email-field input").val());
  });

  /**login div */
  jQuery(".loginMenu  .frm_submit #wp-submit0").on("click", function (e) {
    email = jQuery("#user_login0").val();
    password = jQuery("#user_pass0").val();
    if (email === undefined || password === undefined) {
      e.preventDefault();
      jQuery(".loginMenu  #valid_message").html(b2b_params.mail_pasd_blank);
    } else {
      jQuery(".loginMenu #valid_message").html();
    }
  });
  jQuery(".summitLoginMenu  .frm_submit #wp-submit0").on("click", function (e) {
    email = jQuery("#user_login0").val();
    password = jQuery("#user_pass0").val();
    if (email === undefined || password === undefined) {
      e.preventDefault();
      jQuery(".summitLoginMenu  #valid_message").html(b2b_params.mail_pasd_blank);
    } else {
      jQuery(".summitLoginMenu #valid_message").html();
    }
  });

  //summit book lost password click
  jQuery(".summitLoginMenu .login_lost_pw").on("click", function (e) {
    jQuery(".overlay").removeClass('hide');
    jQuery(".summitResetMenu ").removeClass('hide');
  });
  //b2b lost password click
  jQuery(".loginMenu.fullwidthB2BLogin .login_lost_pw").on("click", function (e) {
    jQuery(".overlay").removeClass('hide');
    jQuery(".resetMenu ").removeClass('hide');
  });
  //comment section lost password click
  jQuery(".comment-desc-section .login_lost_pw").on("click", function (e) {
    jQuery(".overlay").removeClass('hide');
    jQuery(".commentResetMenu ").removeClass('hide');
  });

  //edit profile section toggle the password field on a button click
  jQuery(".change_password_div").on("click", function (e) {
    jQuery('.edit-profile-form .b2b_paswd').toggleClass('showPassword');
  });
  /**custom dropdown instead of default select*/
  var x, i, j, l, ll, selElmnt, a, b, c;
  /*look for any elements with the class "custom-select":*/
  x = document.getElementsByClassName("custom-select");
  l = x.length;
  for (i = 0; i < l; i++) {
    selElmnt = x[i].getElementsByTagName("select")[0];
    ll = selElmnt.length;
    /*for each element, create a new DIV that will act as the selected item:*/
    a = document.createElement("DIV");
    a.setAttribute("class", "select-selected");
    if(selElmnt.id === 'land'){

      a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML+ '<span class="required left-5">*</span>';
    }else{
      a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
    }
   
    x[i].appendChild(a);
    /*for each element, create a new DIV that will contain the option list:*/
    b = document.createElement("DIV");
    b.setAttribute("class", "select-items select-hide");
    for (j = 1; j < ll; j++) {
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

            if (this.childNodes[0].textContent.trim() === "Ausland") {
              $('#land')[0].nextElementSibling.innerHTML =this.childNodes[0].textContent.trim()+ '<span class="required left-5">*</span>';
              $('#region')[0].nextElementSibling.classList.add('disable');
              $('#region')[0].nextElementSibling.innerHTML = region[0].innerHTML/*+ '<span class="required">*</span>'*/;
              $('#subregion')[0].nextElementSibling.classList.add('disable');
              $('#subregion')[0].nextElementSibling.innerHTML = subregion[0].innerHTML/*+ '<span class="required">*</span>'*/;
            } else if (this.innerHTML === "Schweiz") {
              $('#land')[0].nextElementSibling.innerHTML = this.childNodes[0].textContent.trim()+ '<span class="required left-5">*</span>';
              $('#region')[0].nextElementSibling.classList.remove('disable');
              $('#subregion')[0].nextElementSibling.classList.remove('disable');
              for (let r = $('#subregion')[0].length - 1; r > 0; r--) {
                $('#subregion')[0].remove(r);
              }
              $('.b2b-ads-subregion-dropdown .select-items').html("");
            }
            if (currentSelectDiv === "region") {
              const regex = /value="([^"]+)"/;
              const match = this.outerHTML.match(regex);

              console.log(match[1]);
              b2b_ad_filter_subregion_from_region(match[1],  $('#subregion'),  $('.b2b-ads-subregion-dropdown .select-items'));

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
      b.appendChild(c);
    }
    x[i].appendChild(b);
    a.addEventListener("click", function (e) {
      /*when the select box is clicked, close any other select boxes,
      and open/close the current select box:*/
      e.stopPropagation();
      closeAllSelect(this);
      this.nextSibling.classList.toggle("select-hide");
      this.classList.toggle("select-arrow-active");
    });
  }
  /*if the user clicks anywhere outside the select box,
  then close all select boxes:*/
  document.addEventListener("click", closeAllSelect);


  // Add event listener to the select element
  $('#land').on("change", function () {
    var selectedValue = this.value;
    console.log("Selected value: " + selectedValue);
    // You can now use the selectedValue as needed, for example, send it to the server, etc.
  });
  if($('#land').length > 0){
    if($('#land')[0].nextElementSibling.childNodes[0].textContent.trim() === "Ausland"){
      $('#region')[0].nextElementSibling.classList.add('disable');
      $('#subregion')[0].nextElementSibling.classList.add('disable');
    }
  }
  
  if(jQuery(".loginMenu.fullwidthB2BLogin").length > 0){
    valLogin("fullwidthB2BLogin");
  }
  if(jQuery(".comment-desc-section .frm_login_form ").length > 0){
    valLogin("comment-desc-section ");
  }
 

  
});
function closeAllSelect(elmnt) {
  /*a function that will close all select boxes in the document,
  except the current select box:*/
  var x, y, i, xl, yl, arrNo = [];
  x = document.getElementsByClassName("select-items");
  y = document.getElementsByClassName("select-selected");
  xl = x.length;
  yl = y.length;
  for (i = 0; i < yl; i++) {
    if (elmnt == y[i]) {
      arrNo.push(i)
    } else {
      y[i].classList.remove("select-arrow-active");
    }
  }
  for (i = 0; i < xl; i++) {
    if (arrNo.indexOf(i)) {
      x[i].classList.add("select-hide");
    }
  }
}

jQuery(window).resize(function () {
  
  scrollHeight();
  if($('.b2b-table-list').length > 0){
    var table = $('.b2b-table-list').DataTable();

    // Destroy the existing datatable instance
    table.destroy();

    // Reinitialize with responsive options
    $('.b2b-table-list').DataTable({
      responsive: true,
      language: {
        paginate: {
          next: 'Vor', // or '→'
          previous: 'Zurück' // or '←' 
        }
      },
      columnDefs: [
        { targets: 0, width: '35%', orderable: false }, 
        { targets: 1, width: '22%', orderable: false }, 
        { targets: 2, width: '10%', orderable: false }, 
        { targets: 3, width: '9%', orderable: false }, 
        { targets: 4, width: '9%', orderable: false }, 
        { targets: 5, width: '15%', orderable: false },  
      ]
    });
  }
  
  loginWindowPosition('summitLoginMenu');
  loginWindowPosition('userNavigationMenu');

});

function scrollHeight(){
  var div = $(".login_content_wrapper").height();
  var win = $(window).innerHeight();
  if ((div + 50) > win ) {
    $(".login_content_wrapper").addClass('scrollHeight');
  }else{
    $(".login_content_wrapper").removeClass('scrollHeight');
  }
}

/**open register login popup */
function openRegPoppup(element) {
  
  jQuery(".overlay").removeClass('hide');
  jQuery("."+element).removeClass('hide');
  jQuery('.frm_message').addClass("hide");
  jQuery('.frm_error_style').addClass("hide");
  jQuery("."+element+" .frm_form_fields ").removeClass('hide');

  if(jQuery("#strengthProgressBar").length < 1){
    // When the user starts to type something inside the password field
    var divElement = "<div class='profile_valid_message'></div>";
    var strengthPassDiv = "<progress id='strengthProgressBar' value='0' max='5'></progress>";
    $(".fld-paswd").after(divElement);
    $(strengthPassDiv).insertAfter(".profile_valid_message");

    
    var messageElement = jQuery("."+element+" .frm_form_fields .fld-paswd")[0].nextElementSibling;
    var progressBarElement = jQuery("."+element+" .frm_form_fields .fld-paswd")[0].nextElementSibling.nextElementSibling;
    validatePassword(jQuery("."+element+" .frm_form_fields .fld-paswd")[0], messageElement, progressBarElement);
    if (strength == 5) {
      $('.'+element+' .frm_final_submit').removeClass('disableButton');
    } else {
      $('.'+element+' .frm_final_submit').addClass('disableButton');
    }

    $(".fld-paswd").on("keyup", function () {
      var messageElement = $(this)[0].nextElementSibling;
      var progressBarElement = $(this)[0].nextElementSibling.nextElementSibling;
     
        validatePassword(this, messageElement, progressBarElement);
        if (strength == 5) {
          $('.'+element+' .frm_final_submit').removeClass('disableButton');
        } else {
          $('.'+element+' .frm_final_submit').addClass('disableButton');
        }
       
    });
  }
 
}
/**close register login popup */
function closeReg() {
  jQuery(".overlay").addClass('hide');
  //jQuery(".regMenu").addClass('hide');
  jQuery(".regWindow").addClass('hide');
}
/**close user role popup */
function closeUserRolePopup() {
  jQuery(".overlay").addClass('hide');
  jQuery(".userRoleWindow").addClass('hide');
}
/**open user role popup */
function openUserRolePopup(){
  jQuery(".overlay").removeClass('hide');
  jQuery(".userRoleWindow").removeClass('hide');
}
/**close reset  popup b2b */
function closeResetReg() {
  jQuery(".overlay").addClass('hide');
  jQuery(".resetMenu ").addClass('hide');
}
/**close reset  popup summitbook */
function closeSummitResetReg() {
  jQuery(".overlay").addClass('hide');
  jQuery(".summitResetMenu ").addClass('hide');
}
/**close reset  popup comment section */
function closeCommentResetReg() {
  jQuery(".overlay").addClass('hide');
  jQuery(".commentResetMenu ").addClass('hide');
}

/** show the login/logout window aligned with header when there is ad above the header*/
function loginWindowPosition(element){
  if(jQuery('body').hasClass('Top')){
   var visibleAreaAboveHeader, loginextraHeight =0;
    if(window.innerWidth > 900){
      if(window.innerWidth >= 1200){
        visibleAreaAboveHeader = $('.ad-above-header-container.header-ad-desktop-wrapper').innerHeight() - (window.scrollY || window.pageYOffset);
        if($('.home').length > 0){
          loginextraHeight = $('header').outerHeight();
        }
      }else{
       visibleAreaAboveHeader = $('.ad-above-header-container.header-ad-tablet-wrapper').innerHeight() - (window.scrollY || window.pageYOffset);
       if($('.home').length > 0){
          loginextraHeight = $('header').outerHeight();
        }
      }
      if (!(visibleAreaAboveHeader < 0)){
        jQuery("."+element).css('top',visibleAreaAboveHeader + loginextraHeight);
        jQuery("."+element).css('top',visibleAreaAboveHeader + loginextraHeight);
      }else{
        jQuery("."+element).css('top','0');
        jQuery("."+element).css('top','0');
      }
    }else{
      visibleAreaAboveHeader = $('.ad-above-header-container.header-ad-mobile-wrapper').innerHeight() - (window.scrollY || window.pageYOffset);
      if (!(visibleAreaAboveHeader < 0)){
        if($('.home').length > 0){
          loginextraHeight = $('header').outerHeight();
        }
        jQuery("."+element).css('top',visibleAreaAboveHeader + 20 + loginextraHeight);
        jQuery("."+element).css('top',visibleAreaAboveHeader + 20 + loginextraHeight);
      }else{
        jQuery("."+element).css('top','0');
        jQuery("."+element).css('top','0');
      }
    } 
  }else{
    jQuery("."+element).css('top','0');
    jQuery("."+element).css('top','0');
  }
}
/** when scrolling the login window to be aligned along the header*/
window.addEventListener('scroll', function() {
    loginWindowPosition('summitLoginMenu');
    loginWindowPosition('userNavigationMenu');
});
/**open logout popup */
function openLogoutMenu() {
  jQuery(".logoutMenu ").toggleClass("logoutWindow");
  loginWindowPosition('summitLoginMenu');
  loginWindowPosition('userNavigationMenu');
}
/**picture uplaod in ad create page */
function pictureUpload() {
  // Get the file input element
var photoUploadInput = document.getElementById('b2b-ad-img');

// Programmatically trigger the file input's click event
photoUploadInput.click();

// Add an event listener to the file input
photoUploadInput.addEventListener('change', function (event) {
  var file = event.target.files[0]; // Get the selected file

  if (file) {
    // Validate file extension
    var allowedExtensions = ['png', 'jpg', 'jpeg'];
    var fileExtension = file.name.split('.').pop().toLowerCase();
    if (!allowedExtensions.includes(fileExtension)) {
      alert('Invalid file format. Only PNG, JPG, and JPEG images are allowed.');
      return;
    }
    if (file.size > 1000000) {
      $('.upload_error_message').removeClass('hide');
      $('.upload_error_message').html('Upload image size less than 1MB');
      return;
    }

    var reader = new FileReader();

    // Read the file as a data URL
    reader.readAsDataURL(file);

    // Add image width check after the image has loaded
    reader.onload = function (e) {
      var uploadedImage = new Image();
      uploadedImage.src = e.target.result;

      // Add an onload event to the uploaded image to get its width
      uploadedImage.onload = function () {
        var imageWidth = this.width;

        // Check if the image width is less than 500 pixels
        if (imageWidth < 500) {
          $('.upload_error_message').removeClass('hide');
          $('.upload_error_message').html('Image width must be at least 500 pixels.');
          return;
        }

        // If all checks passed, display the uploaded image
        var imageContainer = document.getElementById('uploaded-image');
        imageContainer.src = e.target.result;
        $('.upload_error_message').addClass('hide');
      };
    };
    $('.uploadFileName').html(file.name);
  }
});
}
// Function to validate password and update the message
var vaildidate, strength;
function validatePassword(inputField, messageElement, progressBarElement) {
   vaildidate = false;
  var lowerCaseLetters = /[a-z]/g;
  var upperCaseLetters = /[A-Z]/g;
  var numbers = /[0-9]/g;
  var specialCharacters = /[!@#$%^&*()_+={}\[\]:;"'<>,.?/\\|`~]/g;

  
  strength = 0; // Initialize password strength as 0

  if (inputField.value.length < 12) {
    $(messageElement).html(b2b_params.min_length);
  } else {
    strength++; // Increment strength if length condition is met
    $(messageElement).html("");
  }

  if (inputField.value.match(numbers)) {
    strength++; // Increment strength if number condition is met
  } else {
    $(messageElement).html(b2b_params.min_number);
  }

  if (inputField.value.match(upperCaseLetters)) {
    strength++; // Increment strength if uppercase letter condition is met
  } else {
    $(messageElement).html(b2b_params.uppercase);
  }

  if (inputField.value.match(lowerCaseLetters)) {
    strength++; // Increment strength if lowercase letter condition is met
  } else {
    $(messageElement).html(b2b_params.lowercase);
  }

  if (inputField.value.match(specialCharacters)) {
    strength++; // Increment strength if special character condition is met
  } else {
    $(messageElement).html(b2b_params.special_char);
  }

  // Password strength levels and corresponding messages
  var strengthLevels = {
    0: "Very Weak",
    1: "Weak",
    2: "Medium",
    3: "Strong",
    4: "Very Strong"
  };

  // Update the messageElement with the password strength indication
  // $(messageElement).html(strengthLevels[strength]);

  // Update the password strength progress bar
  $(progressBarElement).val(strength);
  vaildidate = true;
}

// close cliks popup
function closetable() {
  jQuery(".overlay").addClass('hide');
  jQuery("#customTable").addClass('hide');
  //jQuery('body').removeClass('scrollHide');
}

// Function to check if a date is after 4 days from the current date
function isDateEnabled(date, status) {
  var currentDate = new Date();
  var fourDaysAfterCurrent = new Date();
  fourDaysAfterCurrent.setDate(currentDate.getDate() + 3);
  // return date >= fourDaysAfterCurrent;
  // Check if the date is after 3 days from the current date
  if (date < fourDaysAfterCurrent) {
    return false;
  }

  /**check if its a start date or end date */
  if (status === "start") {
    // Check if the date is within any custom holidays
    var customHolidays = getCustomHolidays(); // Fetch custom holidays dynamically or use a static array
    for (var i = 0; i < customHolidays.length; i++) {
      var holidayStart = customHolidays[i].start;
      var holidayEnd = customHolidays[i].end;

      if (date >= holidayStart && date <= holidayEnd) {
        return false;
      }
    }
  }

  return true; // Date is enabled (not a custom holiday)

}

// Function to fetch custom holidays dynamically (or you can use a static array)
function getCustomHolidays() {
  var customHolidays = [];
  var holidayInputs = $("#b2b-ad-activate-date").data(); // Get data attributes from the input element

  if (holidayInputs.startdate && holidayInputs.enddate) {
    // Parse start and end dates correctly based on "dd.mm.yyyy" format
    var startDateParts = holidayInputs.startdate.split(".");
    var endDateParts = holidayInputs.enddate.split(".");

    if (startDateParts.length === 3 && endDateParts.length === 3) {
      var startDate = new Date(
        parseInt(startDateParts[2]),
        parseInt(startDateParts[1]) - 1,
        parseInt(startDateParts[0])
      );

      var endDate = new Date(
        parseInt(endDateParts[2]),
        parseInt(endDateParts[1]) - 1,
        parseInt(endDateParts[0])
      );

      // Push the date range as an object to the customHolidays array
      customHolidays.push({ start: startDate, end: endDate });
    }
  }

  return customHolidays;
}

// Function to check if the URL contains "login=failed"
function isLoginFailed() {
  return window.location.href.indexOf('login=failed') !== -1;
}

// Function to check if the URL contains "frm_message=complete"
function isFormComplete() {
  return window.location.href.indexOf('frm_message=complete') !== -1;
}
//Function to check if the URL contains reset-link-sent=true
function isResetFormComplete() {
  return window.location.href.indexOf('reset-link-sent=true') !== -1;
}
//Function to check if the URL contains password-reset=true
function isPasswordResetFormComplete() {
  return window.location.href.indexOf('password-reset=true') !== -1;
}
//Function to check if the URL contains msg=login
function isMsgLogin() {
  return window.location.href.indexOf('msg=login') !== -1;
}
// Function to check if the URL contains "frmreg_msg=clicked"
function isMsgClicked(){
  return window.location.href.indexOf('frmreg_msg=clicked') !== -1;
}
// Function to check if the URL contains "email-reset=true"
function isEmailReset(){
  return window.location.href.indexOf('email-reset=true') !== -1;
}
// Function to check if the URL contains "password-email-reset=true"
function isPasswordEmailReset(){
  return window.location.href.indexOf('password-email-reset=true') !== -1;
}
// Function to check if the URL contains "reset-link-sent=false"
function isResetLinkFalse(){
  return window.location.href.indexOf('reset-link-sent=false') !== -1;
}
// Function to check if the URL contains "reset-link-sent=false"
function authFailed(){
  return window.location.href.indexOf('authentication=failed') !== -1;
}
// Function to check if the URL contains "reset-link-sent=false"
function activationPending(){
  return window.location.href.indexOf('activation=pending') !== -1;
}
function kommentarLogin(){
  return window.location.href.indexOf('kommentar-login=yes') !== -1;
}

//confirm detele popup
function deleteItem(ad_id) {
  $('#b2b_ad_confirm_delete').attr('data-id', ad_id);
  jQuery(".overlay").removeClass('hide');
  $(".deleteConfirm").removeClass("hide");
}
//close confirm popup
function closeDeletePopup() {
  jQuery(".overlay").addClass('hide');
  $(".deleteConfirm").addClass("hide");
}
//close confirm payment popup
function closePaymetnConfirmPopup() {
  jQuery(".overlay").addClass('hide');
  $(".paymentConfirm ").addClass("hide");
}
function validateLogin(element) {
  if ($('.'+ element + ' .login-username input')[0].value === "") {
    return false; // Validation failed
  } else if ($('.'+ element + ' .login-password input')[0].value === "") {
    return false; // Validation failed
  }

  return true; // Validation succeeded
}
function valLogin(element){
  if (validateLogin(element)) {
    $('.'+element + ' .frm_submit input').removeClass('disableButton');
  } else {
    $('.'+element + ' .frm_submit input').addClass('disableButton');
  }
}
function checkFormidableConditions() {
 // Check if login failed and show the appropriate validation message
  if (isLoginFailed()) {
      if(window.location.href.indexOf('summit_book_login=yes') !== -1){
        //summit book 
        $('.summit-error-msg').removeClass('hide');
        $('.summit-error-msg').addClass('error');
        $('.comment-error-msg ').addClass('hide');
        $('.error-msg ').addClass('hide');
        openSummitBookLoginMenu();

      }else if(window.location.href.indexOf('summit_book_comment_login=yes') !== -1){
        //comment section
        $('.comment-error-msg ').removeClass('hide');
        $('.comment-error-msg ').addClass('error');
        $('.summit-error-msg').addClass('hide');
        $('.error-msg ').addClass('hide');
        if(window.innerWidth > 1200){
          jQuery('html, body').animate({
            scrollTop: jQuery('.community-section-main').offset().top - 80
          }, 500);
        }
        else if(window.innerWidth > 900){
          jQuery('html, body').animate({
            scrollTop: jQuery('.community-section-main').offset().top - 65
          }, 500);
        }
        else if(window.innerWidth > 0){
          jQuery('html, body').animate({
            scrollTop: jQuery('.community-section-main').offset().top - 48
          }, 500);
        }
      }else{
        //b2b
        $('.error-msg').addClass('error');
        $('.summit-error-msg').addClass('hide');
        $('.comment-error-msg ').addClass('hide');
        if(window.innerWidth > 1200){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 80
          }, 500);
        }
        else if(window.innerWidth > 900){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 65
          }, 500);
        }
        else if(window.innerWidth > 0){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 48
          }, 500);
        }
      }
  //  openLoginMenu();
  } else if (isFormComplete()) {
      if(window.location.href.indexOf('summit_book_user_activation=yes') !== -1){
        //summitbook
        $('.summit-success-msg').addClass('sucess');
        $('.success-msg').addClass('hide');
        openSummitBookLoginMenu();
      }else{
        //b2b
        $('.summit-success-msg').addClass('hide');
        $('.success-msg').addClass('sucess');
        if(window.innerWidth > 1200){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 80
          }, 500);
        }
        else if(window.innerWidth > 900){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 65
          }, 500);
        }
        else if(window.innerWidth > 0){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 48
          }, 500);
        }
      }
  // openLoginMenu();
  } else if (isResetFormComplete()) {
      if(window.location.href.indexOf('summit_book_login=yes') !== -1){
        //summitbook
         $('.summit-success-msg').addClass('sucess');
         openSummitBookLoginMenu();
        // $('.reset-success-msg').addClass('sucess');
        // jQuery(".overlay").removeClass('hide');
        // $('.summitResetMenu').removeClass('hide');
      }else{
        //b2b
        $('.summit-success-msg').addClass('hide');
        $('.success-msg').addClass('sucess');
        if(window.innerWidth > 1200){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 80
          }, 500);
        }
        else if(window.innerWidth > 900){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 65
          }, 500);
        }
        else if(window.innerWidth > 0){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 48
          }, 500);
        }
      }
  //  openLoginMenu();
  } else if (isPasswordResetFormComplete()) {
      if(window.location.href.indexOf('summit_book_login=yes') !== -1){
        //summitbook
        $('.summit-success-msg').addClass('sucess');
        openSummitBookLoginMenu();
      }else{
        //b2b
        $('.summit-success-msg').addClass('hide');
        $('.success-msg').addClass('sucess');
        if(window.innerWidth > 1200){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 80
          }, 500);
        }
        else if(window.innerWidth > 900){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 65
          }, 500);
        }
        else if(window.innerWidth > 0){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 48
          }, 500);
        }
      }
  //  openLoginMenu();
  }else if(isMsgLogin()){
  //  openLoginMenu();
  }else if(isMsgClicked()){
    if(window.location.href.indexOf('summit_book_login=yes') !== -1){
      //summit book
      $('.summit-success-msg').addClass('sucess');
      openSummitBookLoginMenu();
    }
  // openLoginMenu();
  }else if(isEmailReset()){
      //  openLoginMenu();
      if(window.location.href.indexOf('summit_book_login=yes') !== -1){
        //summit book
        $('.summit-success-msg').addClass('sucess');
        openSummitBookLoginMenu();
      }else{
        $('.summit-success-msg').addClass('hide');
        if(window.innerWidth > 1200){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 80
          }, 500);
        }
        else if(window.innerWidth > 900){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 65
          }, 500);
        }
        else if(window.innerWidth > 0){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 48
          }, 500);
        }
      }
  }else if(isPasswordEmailReset()){
      // openLoginMenu();
      if(window.location.href.indexOf('summit_book_login=yes') !== -1){
        //summit book
        openSummitBookLoginMenu();
      }
  }else if(isResetLinkFalse()){
      jQuery(".overlay").removeClass('hide');
      if(window.location.href.indexOf('summit_book_login=yes') !== -1){
        //summitbook 
        jQuery(".summitResetMenu ").removeClass('hide');
      }else{
        //b2b 
        jQuery(".resetMenu").removeClass('hide');
      }
    
  }
  else if(authFailed()){
      if(window.location.href.indexOf('summit_book_login=yes') !== -1){
        //summitbook
        $('.summit-error-msg').removeClass('hide');
        $('.summit-error-msg').addClass('error');
        $('.comment-error-msg ').addClass('hide');
        $('.error-msg').addClass('hide');
        openSummitBookLoginMenu();
      }else if(window.location.href.indexOf('summit_book_comment_login=yes') !== -1){
        //comment section
        $('.comment-error-msg ').removeClass('hide');
        $('.comment-error-msg ').addClass('error');
        $('.summit-error-msg').addClass('hide');
        $('.error-msg').addClass('hide');
        if(window.innerWidth > 1200){
          jQuery('html, body').animate({
            scrollTop: jQuery('.community-section-main').offset().top - 80
          }, 500);
        }
        else if(window.innerWidth > 900){
          jQuery('html, body').animate({
            scrollTop: jQuery('.community-section-main').offset().top - 65
          }, 500);
        }
        else if(window.innerWidth > 0){
          jQuery('html, body').animate({
            scrollTop: jQuery('.community-section-main').offset().top - 48
          }, 500);
        }
      }else{
        $('.error-msg').removeClass('hide');
        $('.error-msg').addClass('error');
        $('.summit-error-msg').addClass('hide');
        $('.comment-error-msg ').addClass('hide');
        if(window.innerWidth > 1200){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 80
          }, 500);
        }
        else if(window.innerWidth > 900){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 65
          }, 500);
        }
        else if(window.innerWidth > 0){
          jQuery('html, body').animate({
            scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 48
          }, 500);
        }
      }
  }else if(activationPending()){
    if(window.location.href.indexOf('summit_book_login=yes') !== -1){
      //summitbook
      $('.summit-error-msg').removeClass('hide');
      $('.summit-error-msg').addClass('error');
      $('.comment-error-msg ').addClass('hide');
      $('.error-msg').addClass('hide');
      openSummitBookLoginMenu();
    }else if(window.location.href.indexOf('summit_book_comment_login=yes') !== -1){
      //comment section
      $('.comment-error-msg ').removeClass('hide');
      $('.comment-error-msg ').addClass('error');
      $('.summit-error-msg').addClass('hide');
      $('.error-msg').addClass('hide');
      if(window.innerWidth > 1200){
        jQuery('html, body').animate({
          scrollTop: jQuery('.community-section-main').offset().top - 80
        }, 500);
      }
      else if(window.innerWidth > 900){
        jQuery('html, body').animate({
          scrollTop: jQuery('.community-section-main').offset().top - 65
        }, 500);
      }
      else if(window.innerWidth > 0){
        jQuery('html, body').animate({
          scrollTop: jQuery('.community-section-main').offset().top - 48
        }, 500);
      }
    }else{
      $('.error-msg').removeClass('hide');
      $('.error-msg').addClass('error');
      $('.summit-error-msg').addClass('hide');
      $('.comment-error-msg ').addClass('hide');
      if(window.innerWidth > 1200){
        jQuery('html, body').animate({
          scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 80
        }, 500);
      }
      else if(window.innerWidth > 900){
        jQuery('html, body').animate({
          scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 65
        }, 500);
      }
      else if(window.innerWidth > 0){
        jQuery('html, body').animate({
          scrollTop: jQuery('.fullwidthB2BLogin').offset().top - 48
        }, 500);
      }
    }
  }else if (kommentarLogin()){
    if(window.innerWidth > 1200){
      jQuery('html, body').animate({
        scrollTop: jQuery('.community-section-main').offset().top - 80
      }, 500);
    }
    else if(window.innerWidth > 900){
      jQuery('html, body').animate({
        scrollTop: jQuery('.community-section-main').offset().top - 65
      }, 500);
    }
    else if(window.innerWidth > 0){
      jQuery('html, body').animate({
        scrollTop: jQuery('.community-section-main').offset().top - 48
      }, 500);
    }
  }
}
document.addEventListener("DOMContentLoaded", function() {
  loginWindowPosition('summitLoginMenu');
  loginWindowPosition('userNavigationMenu');
  checkFormidableConditions();
  if(jQuery("#user_login0").length > 0){
    var usernameInput = document.getElementById("user_login0");
    var passwordInput = document.getElementById("user_pass0");
  
    usernameInput.setAttribute("autocomplete", "new-username");
    passwordInput.setAttribute("autocomplete", "new-password");
  }
});
