if (typeof $ === 'undefined') {
  var $ = jQuery;
}
var filterOtherPage, filteredData;
var triggerFilterResult = false;
jQuery(document).ready(function () {

  /**check if the page has filter button(not tourenportal) */
  if ($('.filter-btn.region-filter').length > 0) {
    filterOtherPage = 'region';

    regionCheckbox()
    themaCheckbox();
    routenverlaufCheckbox();
    angebotCheckbox();
    ausdauerCheckbox();
    aktivitatCheckbox();
    saisonCheckbox();
    anforderungCheckbox();

    /* Show count in the filter during initial load in region block */
   // wegw_map_filter_results('hover', filterOtherPage);
   // FilterList('hover', filterOtherPage);

  } else {
    filterOtherPage = '';
  }

  /*Show count in the filter during initial load
  wegw_map_filter_results('hover', filterOtherPage); */
  jQuery(".filterMenu .filter_content_wrapper .fc_block_select label input[type=checkbox]").on('change', function () {
    jQuery(this).parent().toggleClass('active');

  });
  /*show only count in filter when checkbox is selected*/
  jQuery('.filterMenu .filter_content_wrapper input[type=checkbox]').on('change', function () {
   // wegw_map_filter_results('hover', filterOtherPage);
    FilterList('hover', filterOtherPage);
    
  });

  // /*keyup event on search field*/
  // jQuery(".search input").on("keyup", function (e) {
  //   /* show/hide close icon in search in menu sidebar  */
  //   if (jQuery('.navigation_search input').val().length != 0) {
  //     jQuery('.navigation_search_close').removeClass("hide");
  //   } else {
  //     jQuery('.navigation_search_close').addClass("hide");
  //   }
  //   /* show/hide close icon in search in header  */
  //   if (jQuery('.head_navigation_search input').val().length != 0) {
  //     jQuery('.head_navigation_search_close').removeClass("hide");
  //   } else {
  //     jQuery('.head_navigation_search_close').addClass("hide");
  //   }
  //   /* show/hide close icon in search in responsive cluster map(tourenportal)  */
  //   if (jQuery('#map-resp .map_main_search input').val().length != 0) {
  //     jQuery('#map-resp .map_main_search_close').removeClass("hide");
  //   } else {
  //     jQuery('#map-resp .map_main_search_close').addClass("hide");
  //   }
  //   /* show/hide close icon in search in desktop cluster map(tourenportal)  */
  //   if (jQuery('#map_desktop .map_main_search input').val().length != 0) {
  //     jQuery('#map_desktop .map_main_search_close').removeClass("hide");
  //   } else {
  //     jQuery('#map_desktop .map_main_search_close').addClass("hide");
  //   }
  // });

  // if (jQuery('.searchinputField input').length > 0) {
  //   /* show/hide close icon in search in search results page  */
  //   if (jQuery('.searchinputField input').val().length != 0) {
  //     jQuery('.searchResult_search_close').removeClass("hide");
  //   } else {
  //     jQuery('.searchResult_search_close').addClass("hide");
  //   }
  // }
  

  // jQuery(".searchinputField input").on("keyup", function (e) {
  //   /* show/hide close icon in search in search results page  */
  //   if (jQuery('.searchinputField input').val().length != 0) {
  //     jQuery('.searchResult_search_close').removeClass("hide");
  //   } else {
  //     jQuery('.searchResult_search_close').addClass("hide");
  //   }
  // });

  /*double side slider in the filter sidebar*/
  jQuery(".slider.multi-slide").each(function (i, data) {
    // $this is a reference to .slider in current iteration of each
    var $this = jQuery(this);
    // find any .slider-range element WITHIN scope of $this
    jQuery(".slider-range", $this).slider({
      range: true,
      min: parseInt($this.attr('data-first')),
      max: parseInt($this.attr('data-second')),
      values: [parseInt($this.attr('data-first')), parseInt($this.attr('data-second'))],
      /** change the first and last value while sliding the slider */
      slide: function (event, ui) {
        // find any element with class .amount WITHIN scope of $this
        if ($this.attr('data-notation') === 'h') {
          /**check if two digit no or not for first value*/
          if (ui.values[0] <= 9) {
            jQuery(".firstVal", $this).val("0" + ui.values[0] + ':00 ' + $this.attr('data-notation'));
          } else {
            jQuery(".firstVal", $this).val(ui.values[0] + ':00 ' + $this.attr('data-notation'));
          }
          /**check if two digit no or not for second value*/
          if (ui.values[1] <= 9) {
            jQuery(".secondVal", $this).val('> 0' + ui.values[1] + ':00 ' + $this.attr('data-notation'));
          } else {
            jQuery(".secondVal", $this).val('> ' + ui.values[1] + ':00 ' + $this.attr('data-notation'));
          }

          jQuery($this).attr("data-first", ui.values[0]);
          jQuery($this).attr("data-second", ui.values[1]);
       //   wegw_map_filter_results('hover', filterOtherPage);
        } else {
          jQuery(".firstVal", $this).val(ui.values[0] + " " + $this.attr('data-notation'));
          jQuery(".secondVal", $this).val(ui.values[1] + " " + $this.attr('data-notation'));
          jQuery($this).attr("data-first", ui.values[0]);
          jQuery($this).attr("data-second", ui.values[1]);
        //  wegw_map_filter_results('hover', filterOtherPage);
        }

        if (ui.handle === jQuery(".ui-slider-handle:first", $this)[0]){ // Left handle is being moved
          if (ui.values[0] >= ui.values[1]) {
            jQuery(".ui-slider-handle:first", $this).eq(0).addClass("startHandleEnd");
            jQuery(".ui-slider-handle:first", $this).eq(0).removeClass("startHandle");
          }else{
            jQuery(".ui-slider-handle:first", $this).eq(0).removeClass("startHandleEnd");
            jQuery(".ui-slider-handle:first", $this).eq(0).removeClass("startHandle");
          }
        }else if (ui.handle === jQuery(".ui-slider-handle:last", $this)[0]){// Right handle is being moved
          if (ui.values[1] <= ui.values[0]) {
            jQuery(".ui-slider-handle:last", $this).eq(0).addClass("endHandle");
          }else{
            jQuery(".ui-slider-handle:last", $this).eq(0).removeClass("endHandle");
          }
        }

      },
      stop: function (event, ui) {
       // wegw_map_filter_results('hover', filterOtherPage);
        FilterList('hover', filterOtherPage);
      }
    });
    /** change the first and last value based on the slider */
    if ($this.attr('data-notation') === 'h') {
      /**check if two digit no or not for first value*/
      if ($this.attr('data-first') <= 9) {
        jQuery(".firstVal", $this).val("0" + $this.attr('data-first') + ':00 ' + $this.attr('data-notation'));
      } else {
        jQuery(".firstVal", $this).val($this.attr('data-first') + ':00 ' + $this.attr('data-notation'));
      }
      /**check if two digit no or not for second value*/
      if ($this.attr('data-second') <= 9) {
        jQuery(".secondVal", $this).val('> 0' + $this.attr('data-second') + ':00 ' + $this.attr('data-notation'));
      } else {
        jQuery(".secondVal", $this).val('> ' + $this.attr('data-second') + ':00 ' + $this.attr('data-notation'));
      }
    } else {
      jQuery(".firstVal", $this).val($this.attr('data-first') + " " + $this.attr('data-notation'));
      jQuery(".secondVal", $this).val($this.attr('data-second') + " " + $this.attr('data-notation'));
    }
  });

  //loadmore button
  var loadmore = jQuery(".LoadMore");
  if (jQuery(".single-wander-wrappe").length) {
    var wanderung_filter_query = jQuery("#wanderung_filter_query").val();
  }
  // loadmore for hikngs
  jQuery(document).on("click", "#wanderung-loadmore", function () {
    var itemcount = jQuery(".single-wander").length;
    var loc = jQuery(".header_menu .search_head").val();
    var sort = '';
    /** get the sort value large/short */
    if (window.innerWidth < 1200) {
      /** sort div for responsive */
      if (jQuery(".ListHead.mob .sort-largest").prop('checked') == true) {
        var sort = 'large';
      }
      if (jQuery(".ListHead.mob .sort-shortest").prop('checked') == true) {
        var sort = 'short';
      }
    } else {
      /**sort div for desktop */
      if (jQuery(".ListHead .sort-largest").prop('checked') == true) {
        var sort = 'large';
      }
      if (jQuery(".ListHead .sort-shortest").prop('checked') == true) {
        var sort = 'short';
      }
    }

    

    jQuery('#loader-icon').removeClass("hide");
    /**loadmore in tourenportal page and region hike page  */
    if (jQuery(".single-wander-wrappe-json").length || jQuery(".region-single-wander-wrappe").length) {
     // var data = generate_data_array(null);
      var mapDragEvent = $('#wanderung-loadmore').data("event");
      var listDiv;

      /* Update on 11/07/2023 for intial sort drag map issue */
      // if (mapDragEvent == 'regionenMap') {
      if (filterOtherPage === 'region') {
        listDiv = $('.region-single-wander-wrappe');
        //  data['action'] = "wanderung_regionen_map_load_more";
        //   data['regionen_id'] = $('#regionen_id').val();
      //  data['action'] = "wanderung_drag_map_hikes_load_more";
     //   data['map_page'] = "region";
      } else {
        listDiv = $('.single-wander-wrappe');
      //  data['action'] = "wanderung_drag_map_hikes_load_more";
        // data['filtered_map_ids'] = $('#mapDragFilterHikeId').val();
      }

      // data['filtered_map_ids'] = $('#mapDragFilterHikeId').val();
      // data['nonce'] = ajax_object.ajax_nonce;
      // data['sort'] = sort;
      // data['count'] = itemcount;
      // var data = data;

      // jQuery.ajax({
      //   url: ajax_object.ajax_url,
      //   type: "post",
      //   data: data,
      //   beforeSend: function () {
      //     loadmore.addClass("active");
      //   },
      //   complete: function () {
      //     loadmore.removeClass("active");
      //   },
      //   success: function (response) {
      //     var posts = JSON.parse(response);
      //     var countp = jQuery(posts[0]).filter(".single-wander").length;
      //     console.log(countp);
      //     if (posts == "" || countp < 1) {
      //       console.log("empty");
      //       jQuery(".LoadMore").hide();
      //     }
      //     jQuery('#loader-icon').addClass("hide");

      //     if (countp > 0) {
      //       console.log(posts[0]);
      //       listDiv.append(posts[0]);
      //       /**to remove empty hike info*/
      //       toRemoveEmptyHikeInfo();
      //       jQuery(".noWanderung").remove();
      //     } else {

      //       if (jQuery(".noWanderung").length < 1) {
      //         jQuery("#wanderung-loadmore").before(posts);
      //       }
      //     }
      //   },
      //   error: function () {
      //     jQuery('#loader-icon').addClass("hide");
      //   },
      // });
      loadmoreData();
      var posts = filteredData;
      var countp = currentIndex;
      console.log(countp);
      if (posts == "" || countp < 1) {
        console.log("empty");
        jQuery(".LoadMore").hide();
      }
      jQuery('#loader-icon').addClass("hide");

      if (countp > 0) {
        console.log(posts[0]);
       // listDiv.append(posts[0]);
        listModifiedData(filteredData,currentIndex );
        /**to remove empty hike info*/
        toRemoveEmptyHikeInfo();
        jQuery(".noWanderung").remove();
      } else {

        if (jQuery(".noWanderung").length < 1) {
          jQuery("#wanderung-loadmore").before('<h2 class="noWanderung">Keine Wanderungen gefunden</h2>');
        }
      }
      jQuery('#loader-icon').addClass("hide");
    }

  });

  // cookie set for checking checkbox
  jQuery(document).on("change", " .sort_dropdown label input[type=checkbox]", function () {

    var sort_type = jQuery(this).prop("name");
    var sort = '';

    if (jQuery(this).prop('checked') == true) {

      if (sort_type == 'sort_large') {
        var sort = "large";
        //check if its region page or tourenportal page
        if (filterOtherPage === 'region') {
          if (window.innerWidth < 900) {
            if (jQuery(".ListHead.mob .sort-shortest").prop('checked') == true) {
              jQuery(".ListHead.mob .sort-shortest").prop('checked', false);

            }
          } else {
            if (jQuery(".ListHead .sort-shortest").prop('checked') == true) {
              jQuery(".ListHead .sort-shortest").prop('checked', false);

            }
          }
        } else {
          if (window.innerWidth < 1200) {
            if (jQuery(".ListHead.mob .sort-shortest").prop('checked') == true) {
              jQuery(".ListHead.mob .sort-shortest").prop('checked', false);

            }
          } else {
            if (jQuery(".ListHead .sort-shortest").prop('checked') == true) {
              jQuery(".ListHead .sort-shortest").prop('checked', false);

            }
          }
        }

      } else if (sort_type == 'sort_short') {
        var sort = "short";
        //check if its region page or tourenportal page
        if (filterOtherPage === 'region') {
          if (window.innerWidth < 900) {
            if (jQuery(".ListHead.mob .sort-largest").prop('checked') == true) {
              jQuery(".ListHead.mob .sort-largest").prop('checked', false);
            }
          } else {
            if (jQuery(".ListHead .sort-largest").prop('checked') == true) {
              jQuery(".ListHead .sort-largest").prop('checked', false);
            }
          }
        } else {
          if (window.innerWidth < 1200) {
            if (jQuery(".ListHead.mob .sort-largest").prop('checked') == true) {
              jQuery(".ListHead.mob .sort-largest").prop('checked', false);
            }
          } else {
            if (jQuery(".ListHead .sort-largest").prop('checked') == true) {
              jQuery(".ListHead .sort-largest").prop('checked', false);
            }
          }
        }

      }
    } else if (jQuery(this).prop('checked') == false) {

    }
   // var data = generate_data_array(null);

   // var mapDragEvent = $('#wanderung-loadmore').data("event");

    /* Check page type */
    // if (filterOtherPage === 'region') {
    //   data['map_page'] = "region";
    // } else {
    //   data['map_page'] = "";
    // }

   // data['filtered_map_ids'] = $('#mapDragFilterHikeId').val();

    // if (mapDragEvent == 'dragMap') {
    // data['action'] = "wanderung_drag_map_hikes_sort_query";
    // } else {
    //   data['action'] = "get_wanderung_sort_query";
    // }
    // data['nonce'] = ajax_object.ajax_nonce;
    // data['sort'] = sort;

    // jQuery.ajax({
    //   url: ajax_object.ajax_url,
    //   type: "post",
    //   data: data,
    //   beforeSend: function () {
    //     loadmore.show();
    //     jQuery("#wegw-preloader").css("display", "block");
    //   },
    //   success: function (response) {
    //     var posts = JSON.parse(response);
    //     var countp = jQuery(posts[0]).filter(".single-wander").length;
    //     console.log(countp);
    //     if (posts == "" || countp < 1) {
    //       console.log("empty");
    //       jQuery(".LoadMore").hide();
    //     }
    //     if (countp > 0) {
    //       if (filterOtherPage === 'region') {
    //         jQuery(".region-single-wander-wrappe").html(posts);
    //       } else {
    //         jQuery(".single-wander-wrappe").html(posts);
    //       }

    //     }
    //     /**to remove empty hike info*/
    //     toRemoveEmptyHikeInfo();
    //     jQuery("#wegw-preloader").css("display", "none");
    //   },
    //   error: function () { },
    // });
    sortJsonData(sort,filteredData);
     /**to remove empty hike info*/
  toRemoveEmptyHikeInfo();

  });

  // // header search
  // jQuery('.header_menu .search_head').keyup(function (e) {
  //   if (e.key === 'Enter' || e.keyCode === 13) {

  //     /* Remove `dragMap` from load more btn */
  //     $('#wanderung-loadmore').attr("data-event", "");

  //     var data = generate_data_array(null);

  //     data['action'] = "get_wanderung_sort_query";
  //     data['nonce'] = ajax_object.ajax_nonce;
  //     jQuery.ajax({
  //       url: ajax_object.ajax_url,
  //       type: "post",
  //       data: data,
  //       beforeSend: function () {
  //         loadmore.show();
  //         jQuery("#wegw-preloader").css("display", "block");
  //       },
  //       success: function (response) {
  //         var posts = JSON.parse(response);
  //         var countp = jQuery(posts[0]).filter(".single-wander").length;
  //         console.log(countp);
  //         if (posts == "" || countp < 1) {
  //           jQuery(".LoadMore").hide();
  //         }
  //         if (countp > 0) {
  //           if (filterOtherPage === 'region') {
  //             jQuery(".region-single-wander-wrappe").html(posts);
  //           } else {
  //             jQuery(".single-wander-wrappe").html(posts);
  //           }

  //           jQuery(".noWanderung").remove();
  //         } else {
  //           if (filterOtherPage === 'region') {
  //             jQuery(".region-single-wander-wrappe").html('');
  //           } else {
  //             jQuery(".single-wander-wrappe").html('');
  //           }

  //           if (jQuery(".noWanderung").length < 1) {
  //             jQuery("#wanderung-loadmore").before('<h2 class="noWanderung">' + posts[1] + '</h2>');
  //           }
  //         }
  //         jQuery("#wegw-preloader").css("display", "none");
  //         jQuery('#searchbox_main_filter').val(jQuery('.header_menu .search_head').val());
  //       },
  //       error: function () { },
  //     });
  //     // wegw_map_filter_results('hover', e);
  //    // wegw_map_filter_results('btnClick', e, filterOtherPage);
  //     FilterList('btnClick', e, filterOtherPage);
  //   }
  // });
  /*
  * Filter hike results via main sidebar in tourenportal page
  */
  jQuery('#wegw_map_filter_btn').on("click", function (e) {
    /* Remove `dragMap` from load more btn */
    // if(triggerFilterResult){
    //   triggerFilterResult = false;
    //   return;
    // }
    $('#wanderung-loadmore').attr("data-event", "");
    $('#mapDragFilterHikeId').remove();
    // if (filterOtherPage === 'region') {
    //   //Filter hike results via main sidebar in region page
    // //  wegw_map_filter_results('btnClick', e, filterOtherPage);
    //   FilterList('btnClick', e, filterOtherPage);
    // } else {
    //   //Filter hike results via main sidebar in tourenportal page
    //  // wegw_map_filter_results('btnClick', e);
    //   FilterList('btnClick', e);
    // }

    jQuery("#wegw-preloader").css("display", "block");
    jQuery('.filterMenu').addClass('filterWindow');
  });


  // jQuery('.filter_reset').on("click", function (e) {
  //   /* Remove `dragMap` from load more btn */
  //     $('#wanderung-loadmore').attr("data-event", "");
  //     $('#mapDragFilterHikeId').remove();
  //     reset_filter('btnClick', e);
  // });

  // /* clear the menu serach when clicked on close icon  */
  // jQuery('.navigation_search .navigation_search_close').on("click", function (e) {
  //   jQuery(".navigation_search input").val("");
  //   jQuery('.navigation_search_close').addClass("hide");
  // });
  // /* clear the serach on responsive map when clicked on close icon  */
  // jQuery('#map-resp .map_main_search  .map_main_search_close').on("click", function (e) {
  //   jQuery("#map-resp .map_main_search  input").val("");
  //   jQuery('#map-resp .map_main_search_close').addClass("hide");
  // });
  // /* clear the serach on desktop map when clicked on close icon  */
  // jQuery('#map_desktop .map_main_search  .map_main_search_close').on("click", function (e) {
  //   jQuery("#map_desktop .map_main_search  input").val("");
  //   jQuery('#map_desktop .map_main_search_close').addClass("hide");
  // });
  // /* clear the serach in header when clicked on close icon  */
  // jQuery('.head_navigation_search .head_navigation_search_close').on("click", function (e) {
  //   jQuery(".head_navigation_search input").val("");
  //   jQuery('.head_navigation_search_close').addClass("hide");
  // });
  // /* clear the serach in search results page  when clicked on close icon  */
  // jQuery('.searchinputField .searchResult_search_close').on("click", function (e) {
  //   jQuery(".searchinputField input").val("");
  //   jQuery('.searchResult_search_close').addClass("hide");
  // });

});

// /** header search clear  */
// function clearSearch() {
//   jQuery(".header_menu .search.search input").val("");
//   jQuery('.header_menu .filter_search_close').addClass("hide");
//   jQuery('#searchbox_main_filter').val("");
//   //  document.cookie = 'wegw_loc' +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
//   location.reload();
// }

/* Function to reset multi sliders value */
function resetSlider() {

  jQuery(".slider.multi-slide").each(function (i, data) {
    // $this is a reference to .slider in current iteration of each
    var $this = jQuery(this);
    // find any .slider-range element WITHIN scope of $this
    jQuery(".slider-range", $this).slider({
      range: true,
      min: parseInt($this.attr('data-initial')),
      max: parseInt($this.attr('data-final')),
      values: [parseInt($this.attr('data-initial')), parseInt($this.attr('data-final'))],
    });
    if ($this.attr('data-notation') === 'h') {
      if ($this.attr('data-first') <= 9) {
        jQuery(".firstVal", $this).val("0" + $this.attr('data-initial') + ':00 ' + $this.attr('data-notation'));
        jQuery($this).attr("data-first", $this.attr('data-initial'));
      } else {
        jQuery(".firstVal", $this).val($this.attr('data-initial') + ':00 ' + $this.attr('data-notation'));
        jQuery($this).attr("data-first", $this.attr('data-initial'));
      }
      if ($this.attr('data-second') <= 9) {
        jQuery(".secondVal", $this).val('> 0' + $this.attr('data-final') + ':00 ' + $this.attr('data-notation'));
        jQuery($this).attr("data-second", $this.attr('data-final'));
      } else {
        jQuery(".secondVal", $this).val('> ' + $this.attr('data-final') + ':00 ' + $this.attr('data-notation'));
        jQuery($this).attr("data-second", $this.attr('data-final'));
      }

    } else {
      jQuery(".firstVal", $this).val($this.attr('data-initial') + " " + $this.attr('data-notation'));
      jQuery(".secondVal", $this).val($this.attr('data-final') + " " + $this.attr('data-notation'));
      jQuery($this).attr("data-first", $this.attr('data-initial'));
      jQuery($this).attr("data-second", $this.attr('data-final'));
    }

  });

}
function regionCheckbox() {
  // var checkboxes = document.querySelectorAll(".wanderregionen_search");

  // // Find and check the checkbox with the value "21"
  // checkboxes.forEach(function (checkbox) {
  //   if (parseInt(checkbox.value) === $('.filter-btn.region-filter').data('val')) {
  //     checkbox.checked = true;
  //   }
  // });


  // Get all checkboxes with the specified class name (wanderregionen_search)
  var checkboxesRegion = document.querySelectorAll(".wanderregionen_search");

  // Get the data-region value from the hidden input element
  var selectedRegion = $('#regionen_id').data('region');
  if (selectedRegion) {
    // Convert the selectedThema value to an array if it's a comma-separated string
    var selectedRegionArray = selectedRegion.toString().split(',').map(Number);

    // Loop through the checkboxes and check those with values in selectedThemaArray
    checkboxesRegion.forEach(function (checkbox) {
      var checkboxValue = parseInt(checkbox.value);
      if (selectedRegionArray.includes(checkboxValue)) {
        checkbox.checked = true;
      }
    });
  }
}
function themaCheckbox() {
  // Get all checkboxes with the specified class name (thema_search)
  var checkboxesThema = document.querySelectorAll(".thema_search");

  // Get the data-thema value from the hidden input element
  var selectedThema = $('#regionen_id').data('thema');
  if (selectedThema) {
    // Convert the selectedThema value to an array if it's a comma-separated string
    var selectedThemaArray = selectedThema.toString().split(',').map(Number);

    // Loop through the checkboxes and check those with values in selectedThemaArray
    checkboxesThema.forEach(function (checkbox) {
      var checkboxValue = parseInt(checkbox.value);
      if (selectedThemaArray.includes(checkboxValue)) {
        checkbox.checked = true;
      }
    });
  }
}

function routenverlaufCheckbox() {

  // Get all checkboxes with the specified class name(routenverlauf_search)
  var checkboxesRoutenverlauf = document.querySelectorAll(".routenverlauf_search");
  var selectedRoutenverlauf = $('#regionen_id').data('routenverlauf');
  if (selectedRoutenverlauf) {
    var selectedRoutenverlaufArray = selectedRoutenverlauf.toString().split(',').map(Number);

    // Find and check the checkbox with the data-routenverlauf value
    checkboxesRoutenverlauf.forEach(function (checkbox) {
      var checkboxValue = parseInt(checkbox.value);
      if (selectedRoutenverlaufArray.includes(checkboxValue)) {
        checkbox.checked = true;
      }
    });
  }
}

function angebotCheckbox() {
  // Get all checkboxes with the specified class name(angebote_search)
  var checkboxesAngebot = document.querySelectorAll(".angebote_search");
  var selectedAngebot = $('#regionen_id').data('angebot');
  if (selectedAngebot) {
    var selectedAngebotArray = selectedAngebot.toString().split(',').map(Number);

    // Find and check the checkbox with the data-angebot value
    checkboxesAngebot.forEach(function (checkbox) {
      var checkboxValue = parseInt(checkbox.value);
      if (selectedAngebotArray.includes(checkboxValue)) {
        checkbox.checked = true;
      }
    });
  }
}

function ausdauerCheckbox() {
  // Get all checkboxes with the specified class name(ausdauer_search)
  var checkboxesAusdauer = document.querySelectorAll(".ausdauer_search");
  var selectedAusdauer = $('#regionen_id').data('ausdauer');
  if (selectedAusdauer) {
    var selectedAusdauerArray = selectedAusdauer.toString().split(',').map(Number);

    // Find and check the checkbox with the data-angebot value
    checkboxesAusdauer.forEach(function (checkbox) {
      var checkboxValue = parseInt(checkbox.value);
      if (selectedAusdauerArray.includes(checkboxValue)) {
        checkbox.checked = true;
      }
    });
  }
}
function aktivitatCheckbox() {
  // Get all checkboxes with the specified class name(activity_search)
  var checkboxesAktivitat = document.querySelectorAll(".activity_search");
  var selectedAktivitat = $('#regionen_id').data('aktivitat');
  if (selectedAktivitat) {
    var selectedAktivitatArray = selectedAktivitat.toString().split(',').map(Number);

    // Find and check the checkbox with the data-angebot value
    checkboxesAktivitat.forEach(function (checkbox) {
      var checkboxValue = parseInt(checkbox.value);
      if (selectedAktivitatArray.includes(checkboxValue)) {
        checkbox.checked = true;
        if (checkbox.value === '20') {
          if (jQuery(".activity_type_1").prop('checked') === true) {
            jQuery(".activity_type_2").prop('checked', false);
            jQuery(".activity_type_3").prop('checked', false);
            jQuery(".fc_difficult_wt_block").removeClass('hide');
            jQuery(".fc_difficult_t_block").addClass('hide');
            jQuery('.fc_heading.fc_diff_level').removeClass('fc_devel_default');
            jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_difficult_t_block.fc_block_select label').removeClass('active');
          } else {
            clearActivityCheck();

          }

        } else if (checkbox.value === '18') {
          if (jQuery(".activity_type_2").prop('checked') === true) {
            jQuery(".activity_type_1").prop('checked', false);
            jQuery(".activity_type_3").prop('checked', false);
            jQuery(".fc_difficult_t_block").removeClass('hide');
            jQuery(".fc_difficult_wt_block").addClass('hide');
            jQuery('.fc_heading.fc_diff_level').removeClass('fc_devel_default');
            jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_difficult_wt_block.fc_block_select label').removeClass('active');
          } else {
            clearActivityCheck();

          }

        } else if (checkbox.value === '19') {

          jQuery(".activity_type_1").prop('checked', false);
          jQuery(".activity_type_2").prop('checked', false);
          jQuery(".fc_difficult_wt_block").removeClass('hide');
          jQuery(".fc_difficult_t_block").removeClass('hide');
          jQuery('.fc_heading.fc_diff_level').addClass('fc_devel_default');
          jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_block_select label').removeClass('active');
        }
      }
    });
  }
}

function saisonCheckbox() {
  // Get all checkboxes with the specified class name(wander_saison_search)
  var checkboxesSaison = document.querySelectorAll(".wander_saison_search");
  var selectedSaison = $('#regionen_id').data('saison');
  if (selectedSaison) {
    var selectedSaisonArray = selectedSaison.toString().split(',').map(Number);

    // Find and check the checkbox with the data-angebot value
    checkboxesSaison.forEach(function (checkbox) {
      var checkboxValue = parseInt(checkbox.value);
      if (selectedSaisonArray.includes(checkboxValue)) {
        checkbox.checked = true;
        checkbox.parentElement.classList.toggle('active');
      }
    });
  }
}

function anforderungCheckbox() {
  // Get all checkboxes with the specified class name(difficulty_search)
  var checkboxesAnforderung = document.querySelectorAll(".difficulty_search");
  var selectedAnforderung = $('#regionen_id').data('anforderung');
  if (selectedAnforderung) {
    var selectedAnforderungArray = selectedAnforderung.toString().split(',').map(Number);

    // Find and check the checkbox with the data-angebot value
    checkboxesAnforderung.forEach(function (checkbox) {
      var checkboxValue = parseInt(checkbox.value);
      if (selectedAnforderungArray.includes(checkboxValue)) {
        checkbox.checked = true;
        checkbox.parentElement.classList.toggle('active');
      }
    });
  }
}

// //loadmore for blog

// jQuery(document).on("click", "#blog-loadmore", function () {
//   var itemcount = jQuery(".blog-wander").length;
//   var loadmore  = jQuery(".LoadMore");
//   var listDiv   = jQuery('.blog_list');
//   var page_type = jQuery(".page_type").val();
//   jQuery('#loader-icon').removeClass("hide");
//   var data = {
//     'action' : 'wanderung_blogs_load_more',
//     'nonce': ajax_object.ajax_nonce,
//     'count'  : itemcount,
//     'page_type' : page_type,
//   };

//   jQuery.ajax({
//     url: ajax_object.ajax_url,
//     type: "post",
//     data: data,
//     beforeSend: function () {
//       loadmore.addClass("active");
//     },
//     complete: function () {
//       loadmore.removeClass("active");
//     },
//     success: function (response) {
//       var posts = JSON.parse(response);
//       var countp = jQuery(posts[0]).filter(".blog-wander").length;
//       console.log(countp);
//       if (posts == "" || countp < 1) {
//         console.log("empty");
//         jQuery(".LoadMore").hide();
//       }
//       jQuery('#loader-icon').addClass("hide");

//       if (countp > 0) {
//         console.log(posts[0]);
//         listDiv.append(posts[0]);
//         jQuery(".noWanderung").remove();
//       } else {

//         if (jQuery(".noWanderung").length < 1) {
//           jQuery("#blog-loadmore").before(posts);
//         }
//       }
//     },
//     error: function () {
//       jQuery('#loader-icon').addClass("hide");
//     },
//   });

// });

// //loadmore for Search page
// jQuery(document).on("click", "#search-loadmore", function () {
  
//   var loadmore  = jQuery(".LoadMore");
 
//   var itemcounts = jQuery(this).data("count");
//   var search_query =  jQuery(this).data("query");
//   var search_offset =  jQuery(this).data("offset");
//   var post_type = jQuery(this).attr("data-postType");
//   let next_offset = parseInt(search_offset) + 9 ; 
//   jQuery(this).data("offset", next_offset);
  
//   var search_nonce =  jQuery(this).data("nonce");


//   if( itemcounts < next_offset ){
//     jQuery('#search-loadmore').hide();
//     jQuery('.noWanderungSearchPost').show();
//   }


//   jQuery('#loader-icon').removeClass("hide");
//   var data = {
//     'action' : 'wanderung_search_load_more',
//     'count'  : itemcounts,
//     'search_query' : search_query,
//     'offset' : search_offset,
//     'search_nonce' : search_nonce,
//     'post_type' : post_type
  
//   };

//   jQuery.ajax({
//     url: ajax_object.ajax_url,
//     type: "post",
//     data: data,
//     beforeSend: function () {
//       loadmore.addClass("active");
//     },
//     complete: function () {
//       loadmore.removeClass("active");
//     },
//     success: function (response) {
//       jQuery('.searchResult_list').append(response.data);
// 		jQuery('#loader-icon').addClass("hide");
//     },
//     error: function () {
//       jQuery('#loader-icon').addClass("hide");
//     },
//   });

// });

// //loadmore for taxonomies
// jQuery(document).on("click", "#taxonomy-loadmore", function () {
  
//   var loadmore  = jQuery(".LoadMore");
 
//   var itemcounts = jQuery(this).data("count");
//   var search_offset =  jQuery(this).data("offset");
//   var post_type = jQuery(this).attr("data-postType");
//   var taxonomy = jQuery(this).attr("data-taxonomy");
//   var term_id = jQuery(this).attr("data-termId");
//   let next_offset = parseInt(search_offset) + 9 ; 
//   jQuery(this).data("offset", next_offset);
  
//   var taxonomy_nonce =  jQuery(this).data("nonce");


//   if( itemcounts < next_offset ){
//     jQuery('#taxonomy-loadmore').hide();
//     jQuery('.noWanderungSearchPost').show();
//   }


//   jQuery('#loader-icon').removeClass("hide");
//   var data = {
//     'action' : 'wanderung_taxonomy_load_more',
//     'count'  : itemcounts,
//     'offset' : search_offset,
//     'taxonomy_nonce' : taxonomy_nonce,
//     'post_type' : post_type,
//     'taxonomy' : taxonomy,
//     'term_id' : term_id
//   };

//   jQuery.ajax({
//     url: ajax_object.ajax_url,
//     type: "post",
//     data: data,
//     beforeSend: function () {
//       loadmore.addClass("active");
//     },
//     complete: function () {
//       loadmore.removeClass("active");
//     },
//     success: function (response) {
//       jQuery('.searchResult_list').append(response.data);
// 		jQuery('#loader-icon').addClass("hide");
//     },
//     error: function () {
//       jQuery('#loader-icon').addClass("hide");
//     },
//   });

// });

/* Function to reset multi sliders value */
function resetSlider() {

  jQuery(".slider.multi-slide").each(function (i, data) {
    // $this is a reference to .slider in current iteration of each
    var $this = jQuery(this);
    // find any .slider-range element WITHIN scope of $this
    jQuery(".slider-range", $this).slider({
      range: true,
      min: parseInt($this.attr('data-initial')),
      max: parseInt($this.attr('data-final')),
      values: [parseInt($this.attr('data-initial')), parseInt($this.attr('data-final'))],
    });
    if ($this.attr('data-notation') === 'h') {
      if ($this.attr('data-first') <= 9) {
        jQuery(".firstVal", $this).val("0" + $this.attr('data-initial') + ':00 ' + $this.attr('data-notation'));
        jQuery($this).attr("data-first", $this.attr('data-initial'));
      } else {
        jQuery(".firstVal", $this).val($this.attr('data-initial') + ':00 ' + $this.attr('data-notation'));
        jQuery($this).attr("data-first", $this.attr('data-initial'));
      }
      if ($this.attr('data-second') <= 9) {
        jQuery(".secondVal", $this).val('> 0' + $this.attr('data-final') + ':00 ' + $this.attr('data-notation'));
        jQuery($this).attr("data-second", $this.attr('data-final'));
      } else {
        jQuery(".secondVal", $this).val('> ' + $this.attr('data-final') + ':00 ' + $this.attr('data-notation'));
        jQuery($this).attr("data-second", $this.attr('data-final'));
      }

    } else {
      jQuery(".firstVal", $this).val($this.attr('data-initial') + " " + $this.attr('data-notation'));
      jQuery(".secondVal", $this).val($this.attr('data-final') + " " + $this.attr('data-notation'));
      jQuery($this).attr("data-first", $this.attr('data-initial'));
      jQuery($this).attr("data-second", $this.attr('data-final'));
    }

  });

}
/*Open filter sidebar*/
function openFilter(event) {
   jQuery(".filterMenu").toggleClass("filterWindow");
}

/****************** */
function sortJsonData(sort, filteredData){
  if(filteredData === undefined){
    filteredData = jsonplaces;
  }
  
  filteredData.sort(function(a, b) {
      if (sort ==="short") {
        return a.location_travel_distance - b.location_travel_distance;
      } else if(sort ==="large") {
        return b.location_travel_distance - a.location_travel_distance;
      }
    });
    if (filterOtherPage === 'region'){
      $('.region-single-wander-wrappe').html("");
    }else{
      $('.single-wander-wrappe-json').html("");
    } 
    currentIndex = 0;
    listModifiedData(filteredData, 0);
    if (filteredData.length < 1) {
      console.log("empty");
      jQuery(".LoadMore").hide();
    }else if (filteredData.length > 0) {
      
      jQuery(".LoadMore").show();
      jQuery(".noWanderung").remove();
    } else {

      if (jQuery(".noWanderung").length < 1) {
        jQuery("#wanderung-loadmore").before('<h2 class="noWanderung">Keine Wanderungen gefunden</h2>');
      }
    }
  }
  
  function listModifiedData(jsonplaces, currentIndex){
    var i =currentIndex;
   
    
   count = -1;
    for(i; i< jsonplaces.length; i++) {
      count = count+1;
      var loc_season = jsonplaces[i].location_wander_saison_name;
      if( loc_season.length > 7 ) {
          loc_season = loc_season.slice(0,7) + "...";
      }
  
      var hikeDescriptionZoomFunc = jsonplaces[i].location_desc.substring(0, hikeDescriptionLetterCount) + "...";
      if (filterOtherPage === 'region'){

        var wanderHtml = '<div class="single-wander"><div class="single-wander-img"><a href="' + 
          jsonplaces[i].location_link + '"><img class="wander-img" src="' + 
          jsonplaces[i].location_feature_image + '"></a><div '+
          (jsonplaces[i].watchlisted_by.indexOf( $('.region-single-wander-wrappe').data('logged-user').toString()) !== -1 ? "class='single-wander-heart watchlisted' ":"class='single-wander-heart' onclick='addToWatchlist(this, "+jsonplaces[i].location_id +")'" )+'></div><div class="single-wander-map" onclick="openPopupMap(this)" data-hikeid="' + 
          jsonplaces[i].location_id + '"></div></div><div class="single-region-rating"><h6 class="single-region">' + 
          jsonplaces[i].location_regionen_name + '</h6><span class="average-rating-display">' + 
          jsonplaces[i].average_rating + '<i class="fa fa-star"></i></span></div><a href="' + 
          jsonplaces[i].location_link + '" class="wander-redirect"><h2>' + 
          jsonplaces[i].location_name + '</h2></a><div class="wanderung-infobox"><div class="hiking_info"><div class="hike_level"><span class="' + 
          jsonplaces[i].location_level_cls + '"></span><p>' + jsonplaces[i].location_level_name + '</p></div><div class="hike_time"><span class="hike-time-icon"></span><p>' + 
          jsonplaces[i].location_hike_time + ' h </p></div><div class="hike_distance"><span class="hike-distance-icon"></span><p>' + 
          jsonplaces[i].location_travel_distance + ' km</p></div><div class="hike_ascent"><span class="hike-ascent-icon"></span><p>' + 
          jsonplaces[i].location_hike_ascent + ' m</p></div><div class="hike_descent"><span class="hike-descent-icon"></span><p>' + 
          jsonplaces[i].location_hike_descent + ' m</p></div><div class="hike_month"><span class="hike-month-icon"></span><p>' +
          loc_season + '</p></div></div></div><div class="wanderung-desc">' + hikeDescriptionZoomFunc + '</div>'+
          
          
          '<div class="weg-map-popup-outter"><div id="weg-map-popup'+jsonplaces[i].location_id+'" ><div class="map-fixed-position">'+
          '<div id="weg-map-popup-inner-wrapper'+jsonplaces[i].location_id+'">'+
            '<div class="close_map" onclick="closeElement(this)"><span class="close_map_icon"></span></div>'+
            '<div id="cesiumContainer" class="cesiumContainer"></div>'+
            '<div class="map_currentLocation"></div>'+
            '<div id="threeD" class="map_3d"></div>'+
            '<div id="map_direction" class="map_direction"></div>'+
            '<div class="botom_layer_icon">'+
              '<div class="accordion" >'+
                '<div class="weg-layer-wrap layer_head">'+
                  '<div class="weg-layer-text">Hintergrund</div>'+
                '</div>'+
              '</div>'+
              '<div class="panel">'+
                '<div class="weg-layer-wrap activeLayer" id="colormap_view_section">'+
                  '<div class="weg-layer-text">Karte farbig</div>'+
                '</div>'+
                '<div class="weg-layer-wrap" id="aerial_view_section">'+
                  '<div class="weg-layer-text">Luftbild</div>'+
                '</div>'+
                '<div class="weg-layer-wrap" id="grey_view_section">'+
                  '<div class="weg-layer-text">Karte SW</div>'+
                '</div>'+
              '</div>'+
            '</div>'+
            '<div class="copyRight">'+
              '<a target="_blank" href="https://www.swisstopo.admin.ch/de/home.html">© swisstopo</a>'+
            '</div>'+
            '<div class="map_filter">'+
              '<div class="map_filter_inner_wrapper">'+
                '<div class="accordion">Karteninformationen</div>'+
                '<div class="panel">'+
                  '<div class="fc_check_wrap">'+
                    '<label class="check_wrapper">ÖV-Haltestellen'+
                      '<input type="checkbox" name="" id="transport_layer_checkbox" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Wanderwege'+
                      '<input type="checkbox" name="" id="hikes_trailing_layer" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Gesperrte Wanderwege'+
                      '<input type="checkbox" name="" id="closure_hikes_layer" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Schneehöhe Exolabs'+
                      '<input type="checkbox" name="" id="snow_depth_layer" value="">'+
                      '<span class="redmark"></span>'+
                      '<div class="info_icon" onclick="infoIconClicked(event,&quot;weg-map-popup'+jsonplaces[i].location_id+'&quot;)"></div>'+
                    '</label>'+
                    '<label class="check_wrapper">Schneebedeckung ExoLabs'+
                      '<input type="checkbox" name="" id="snow_cover_layer" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Hangneigungen über 30°'+
                      '<input type="checkbox" id="slope_30_layer" name="" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Wildruhezonen'+
                      '<input type="checkbox" id="wildlife_layer" name="" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Wegpunkte WegWandern.ch'+
                      '<input type="checkbox" id="waypoints_layer" name="" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                  '</div>'+
                '</div>'+
              '</div>'+
            '</div>'+
          '</div>'+
          '<div id="detailPgPopup"><div id="detailPgPopupContent"></div></div>'+
          '<div class="elevationGraph"></div>	'+
          '<div class="options" id="mapOptions"></div>'+
          '<div class="snow_info_details hide">'+
		                '<div class="snow_inner_wrapper">'+
			                '<div class="snow_close_wrapper" onclick="infoIconClosed(event,&quot;weg-map-popup'+jsonplaces[i].location_id+'&quot;)"><div class="snow_close"></div></div>'+
			                '<div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>'+
			                '<div class="snow_image"></div>'+
			                '<a href="https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>'+
		                '</div>'+
	                '</div>'+
          '<div id="info"></div>'+
          '<div class="popover" id="transport-layer-info-popup">'+
            '<div class="arrow"></div>'+
            '<div class="popover-title">'+
              '<div class="popup-title">Objekt Informationen</div>'+
              '<div class="popup-buttons">'+
               
                '<button class="fa fa-remove" title="Close" onclick="closeTransportLayerPopup()"></button>'+
              '</div>'+
            '</div>'+
            '<div class="popover-content">'+
              '<div class="popover-scope">'+
                '<div class="popover-binding">'+
                  '<div class="htmlpopup-container" id="tl-content-area">'+
                  '</div>'+
                '</div>'+
              '</div>'+
            '</div>'+
          '</div>'+
        '</div></div></div></div>';
     
         if(count < 9) {
              // All 20
             
              $('.region-single-wander-wrappe').append(wanderHtml);
         
          }

      }else{
        var wanderHtml = '<div class="single-wander"><div class="single-wander-img"><a href="' + 
          jsonplaces[i].location_link + '"><img class="wander-img" src="' + 
          jsonplaces[i].location_feature_image + '"></a><div '+
          (jsonplaces[i].watchlisted_by.indexOf( $('.single-wander-wrappe-json').data('logged-user').toString()) !== -1 ? "class='single-wander-heart watchlisted' ":"class='single-wander-heart' onclick='addToWatchlist(this, "+jsonplaces[i].location_id +")'" )+'></div><div class="single-wander-map" onclick="openPopupMap(this)" data-hikeid="' + 
          jsonplaces[i].location_id + '"></div></div><div class="single-region-rating"><h6 class="single-region">' + 
          jsonplaces[i].location_regionen_name + '</h6><span class="average-rating-display">' + 
          jsonplaces[i].average_rating + '<i class="fa fa-star"></i></span></div><a href="' + 
          jsonplaces[i].location_link + '" class="wander-redirect"><h2>' + 
          jsonplaces[i].location_name + '</h2></a><div class="wanderung-infobox"><div class="hiking_info"><div class="hike_level"><span class="' + 
          jsonplaces[i].location_level_cls + '"></span><p>' + jsonplaces[i].location_level_name + '</p></div><div class="hike_time"><span class="hike-time-icon"></span><p>' + 
          jsonplaces[i].location_hike_time + ' h </p></div><div class="hike_distance"><span class="hike-distance-icon"></span><p>' + 
          jsonplaces[i].location_travel_distance + ' km</p></div><div class="hike_ascent"><span class="hike-ascent-icon"></span><p>' + 
          jsonplaces[i].location_hike_ascent + ' m</p></div><div class="hike_descent"><span class="hike-descent-icon"></span><p>' + 
          jsonplaces[i].location_hike_descent + ' m</p></div><div class="hike_month"><span class="hike-month-icon"></span><p>' +
          loc_season + '</p></div></div></div><div class="wanderung-desc">' + hikeDescriptionZoomFunc + '</div>'+
          
          
          '<div class="weg-map-popup-outter"><div id="weg-map-popup'+jsonplaces[i].location_id+'" ><div class="map-fixed-position">'+
          '<div id="weg-map-popup-inner-wrapper'+jsonplaces[i].location_id+'">'+
            '<div class="close_map" onclick="closeElement(this)"><span class="close_map_icon"></span></div>'+
            '<div id="cesiumContainer" class="cesiumContainer"></div>'+
            '<div class="map_currentLocation"></div>'+
            '<div id="threeD" class="map_3d"></div>'+
            '<div id="map_direction" class="map_direction"></div>'+
            '<div class="botom_layer_icon">'+
              '<div class="accordion" >'+
                '<div class="weg-layer-wrap layer_head">'+
                  '<div class="weg-layer-text">Hintergrund</div>'+
                '</div>'+
              '</div>'+
              '<div class="panel">'+
                '<div class="weg-layer-wrap activeLayer" id="colormap_view_section">'+
                  '<div class="weg-layer-text">Karte farbig</div>'+
                '</div>'+
                '<div class="weg-layer-wrap" id="aerial_view_section">'+
                  '<div class="weg-layer-text">Luftbild</div>'+
                '</div>'+
                '<div class="weg-layer-wrap" id="grey_view_section">'+
                  '<div class="weg-layer-text">Karte SW</div>'+
                '</div>'+
              '</div>'+
            '</div>'+
            '<div class="copyRight">'+
              '<a target="_blank" href="https://www.swisstopo.admin.ch/de/home.html">© swisstopo</a>'+
            '</div>'+
            '<div class="map_filter">'+
              '<div class="map_filter_inner_wrapper">'+
                '<div class="accordion">Karteninformationen</div>'+
                '<div class="panel">'+
                  '<div class="fc_check_wrap">'+
                    '<label class="check_wrapper">ÖV-Haltestellen'+
                      '<input type="checkbox" name="" id="transport_layer_checkbox" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Wanderwege'+
                      '<input type="checkbox" name="" id="hikes_trailing_layer" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Gesperrte Wanderwege'+
                      '<input type="checkbox" name="" id="closure_hikes_layer" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Schneehöhe Exolabs'+
                      '<input type="checkbox" name="" id="snow_depth_layer" value="">'+
                      '<span class="redmark"></span>'+
                      '<div class="info_icon" onclick="infoIconClicked(event,&quot;weg-map-popup'+jsonplaces[i].location_id+'&quot;)"></div>'+
                    '</label>'+
                    '<label class="check_wrapper">Schneebedeckung ExoLabs'+
                      '<input type="checkbox" name="" id="snow_cover_layer" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Hangneigungen über 30°'+
                      '<input type="checkbox" id="slope_30_layer" name="" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Wildruhezonen'+
                      '<input type="checkbox" id="wildlife_layer" name="" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                    '<label class="check_wrapper">Wegpunkte WegWandern.ch'+
                      '<input type="checkbox" id="waypoints_layer" name="" value="">'+
                      '<span class="redmark"></span>'+
                    '</label>'+
                  '</div>'+
                '</div>'+
              '</div>'+
            '</div>'+
          '</div>'+
          '<div id="detailPgPopup"><div id="detailPgPopupContent"></div></div>'+
          '<div class="elevationGraph"></div>	'+
          '<div class="options" id="mapOptions"></div>'+
          '<div class="snow_info_details hide">'+
		                '<div class="snow_inner_wrapper">'+
			                '<div class="snow_close_wrapper" onclick="infoIconClosed(event,&quot;weg-map-popup'+jsonplaces[i].location_id+'&quot;)"><div class="snow_close"></div></div>'+
			                '<div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>'+
			                '<div class="snow_image"></div>'+
			                '<a href="https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>'+
		                '</div>'+
	                '</div>'+
          '<div id="info"></div>'+
          '<div class="popover" id="transport-layer-info-popup">'+
            '<div class="arrow"></div>'+
            '<div class="popover-title">'+
              '<div class="popup-title">Objekt Informationen</div>'+
              '<div class="popup-buttons">'+
               
                '<button class="fa fa-remove" title="Close" onclick="closeTransportLayerPopup()"></button>'+
              '</div>'+
            '</div>'+
            '<div class="popover-content">'+
              '<div class="popover-scope">'+
                '<div class="popover-binding">'+
                  '<div class="htmlpopup-container" id="tl-content-area">'+
                  '</div>'+
                '</div>'+
              '</div>'+
            '</div>'+
          '</div>'+
        '</div></div></div></div>';
     
         if(count < 20) {
              // All 20
             
              $('.single-wander-wrappe-json').append(wanderHtml);
         
          }
      }
      
          
    }
  }
  
  // Number of items to show per page
  let itemsPerPage;
  if($('.region-single-wander-wrappe').length > 0){
    itemsPerPage = 9;
  }
  if($('.single-wander-wrappe-json').length > 0){
    itemsPerPage = 20;
  }
  // Variable to keep track of the current index
  currentIndex = 0;
  // Function to show the next page
  function loadmoreData(){
    if(filteredData === undefined){
      filteredData = jsonplaces;
    }
    currentIndex += itemsPerPage;
    if (currentIndex >= filteredData.length) {
        currentIndex = 0; // Loop back to the beginning
    }
   
  }
 
  


  
function getActiveCheckboxValues(className) {
  // Get all checkboxes with the specified class
  var checkboxes = document.querySelectorAll('.' + className);
  
  // Create an array to store the values of active checkboxes
  var activeCheckboxValues = [];

  // Loop through each checkbox
  checkboxes.forEach(function (checkbox) {
      // Check if the checkbox is checked (active)
      if (checkbox.checked) {
          // Get the value attribute of the checked checkbox and push it to the array
          activeCheckboxValues.push(parseInt(checkbox.value));
      }
  });

  // Return the array of active checkbox values
  return activeCheckboxValues;
}

  //filter data
function FilterList(eventType, e, filterOtherPage){

    if (eventType == "hover") {
      jQuery('#loader-icon-filter').removeClass("hide");
      jQuery('.wegw_filtered_result_count').addClass("hide");
    } else {
      jQuery("#wegw-preloader").css("display", "block");
      jQuery('.ListSec').addClass('disabled');
    }

    var initial, final;
    dataReset = null;
    //get initial and final data of double slider in filter window
    if (dataReset === 'reset') {
      initial = 'data-initial';
      final = 'data-final';
    } else {
      initial = 'data-first';
      final = 'data-second';
    }

    var sort = '';
    //get the sort large/short
    if (window.innerWidth < 1200) {
        if (jQuery(".ListHead.mob .sort-largest").prop('checked') == true) {
        var sort = 'large';
        }
        if (jQuery(".ListHead.mob .sort-shortest").prop('checked') == true) {
        var sort = 'short';
        }
    } else {
        if (jQuery(".ListHead .sort-largest").prop('checked') == true) {
        var sort = 'large';
        }
        if (jQuery(".ListHead .sort-shortest").prop('checked') == true) {
        var sort = 'short';
        }
    }
    

    var wanderSaisonValues = getActiveCheckboxValues('wander_saison_search');
    var ausdauerValues = getActiveCheckboxValues('ausdauer_search');
    var routenverlaufValues = getActiveCheckboxValues('routenverlauf_search');
    var themaValues = getActiveCheckboxValues('thema_search');
    var angeboteValues = getActiveCheckboxValues('angebote_search');
    var wanderregionenValues = getActiveCheckboxValues('wanderregionen_search');
    var activityValues = getActiveCheckboxValues('activity_search');
    var levelIdValues = getActiveCheckboxValues('difficulty_search');

    


    // generic for slider filters
    const createSliderFilter = (propertyName, sliderType) => (item) => {
      const firstValue = parseInt($(`.${sliderType}`).attr('data-first'));
      const initialValue = parseInt($(`.${sliderType}`).attr('data-initial'));
      const secondValue = parseInt($(`.${sliderType}`).attr('data-second'));
      const finalValue = parseInt($(`.${sliderType}`).attr('data-final'));

      // Check if the relevant values are different before applying the filter
      if (
        (firstValue !== initialValue || secondValue !== finalValue) &&
        (firstValue !== secondValue || initialValue !== finalValue)
      ) {
        return (
          item[propertyName] >= firstValue &&
          item[propertyName] <= secondValue
        );
      }

      // If the values are the same, return true to include the item in the filter
      return true;
    };

    // generic for array filters
    const createArrayFilter = (propertyName, values) => (item) => {
      // Check if values array is not null before applying the filter
      if (values.length !== 0) {
        return item[propertyName].some(data => values.includes(data));
      }
      // If values array is null, return true to include the item in the filter
      return true;
    };

    const filterByAltitude = (item) => {
      const firstValue = parseFloat($('.aaltitude-slider').attr('data-first'));
      const initialValue = parseFloat($('.aaltitude-slider').attr('data-initial'));
      const secondValue = parseFloat($('.aaltitude-slider').attr('data-second'));
      const finalValue = parseFloat($('.aaltitude-slider').attr('data-final'));

      const highestPoint = parseFloat(item.location_hike_hochster_punkt);
      const lowestPoint = parseFloat(item.location_hike_tiefster_punkt);

      // Check if either highest or lowest point is within the slider range
      const isInRange =(lowestPoint >= firstValue ) && ( highestPoint <= secondValue);

      // Check if the relevant values are different before applying the filter
      if (
        (firstValue !== initialValue || secondValue !== finalValue) &&
        (firstValue !== secondValue || initialValue !== finalValue)
      ) {
        return isInRange;
      }

      // If the values are the same, return true to include the item in the filter
      return true;
    };

    const filterByTime = (item) => {
      const firstValue = parseFloat($('.dauer-slider').attr('data-first'));
      const initialValue = parseFloat($('.dauer-slider').attr('data-initial'));
      const secondValue = parseFloat($('.dauer-slider').attr('data-second'));
      const finalValue = parseFloat($('.dauer-slider').attr('data-final'));

      // const hikeTime = item.location_hike_time.split(':')[0];
      // Check if hike time is empty
      if (item.location_hike_time === "") {
        return true;
      }
      const hikeTime = parseFloat(item.location_hike_time.split(':')[0]+"."+item.location_hike_time.split(':')[1]);

      // Check if either highest or lowest point is within the slider range
      const isInRange = hikeTime >= firstValue+".00" &&
                        hikeTime <= secondValue+".00";

      // Check if the relevant values are different before applying the filter
      if (
        (firstValue !== initialValue || secondValue !== finalValue) &&
        (firstValue !== secondValue || initialValue !== finalValue)
      ) {
        return isInRange;
      }

      // If the values are the same, return true to include the item in the filter
      return true;
    };


    // Add more slider filters as needed
    const filterByDistance = createSliderFilter('location_travel_distance', 'km-slider');
    const filterByAscent = createSliderFilter('location_hike_ascent', 'aufstieg-slider');
    const filterByDescent = createSliderFilter('location_hike_descent', 'abstieg-slider');
    //const filterByTime = createSliderFilter('location_hike_time', 'dauer-slider');

    // Add more array filters as needed
    const filterBySaison = createArrayFilter('location_wander_saison', wanderSaisonValues);
    const filterByAusdauer = createArrayFilter('location_ausdauer', ausdauerValues);
    const filterByThema = createArrayFilter('location_thema', themaValues);
    const filterByAngebote = createArrayFilter('location_angebote', angeboteValues);
    // if(filterOtherPage === 'region') {
    //   const filterByWanderregionen = createArrayFilter('location_regionen_id', wanderregionenValues);
     
    // }else{
    //   const filterByWanderParentregionen = createArrayFilter('location_regionen_parent_id', wanderregionenValues);
    // }
   
    
    // const filterByWanderregionen = createArrayFilter('location_regionen_id', wanderregionenValues);
    const filterByRoutenverlauf = createArrayFilter('location_routenverlauf', routenverlaufValues);
    const filterByActivity = createArrayFilter('location_aktivitat', activityValues);
    const filterBylevelId = createArrayFilter('location_level_id', levelIdValues);


    const filterFunctions = [
      filterByDistance,
      filterByAscent,
      filterByDescent,
      filterByTime,
      filterByAltitude,
      filterBySaison,
      filterByAusdauer,
      filterByThema,
      filterByAngebote,
     // filterByWanderregionen,
      filterByRoutenverlauf,
      filterByActivity,
      filterBylevelId,
      
    ];

    //for region pages only
    if ($('.filter-btn.region-filter').length > 0) {
      if($('#template_region_type')[0].value === 'parent'){
        //if parent
        const filterByWanderParentregionen = createArrayFilter('location_regionen_parent_id', wanderregionenValues);
        filterFunctions.push(filterByWanderParentregionen);
      }else{
        const filterByWanderregionen = createArrayFilter('location_regionen_id', wanderregionenValues);
        filterFunctions.push(filterByWanderregionen);
      }
      
    } else {//for tourenportal page only
      const filterByWanderParentregionen = createArrayFilter('location_regionen_parent_id', wanderregionenValues);
      filterFunctions.push(filterByWanderParentregionen);
    }

    
    
    const combinedFilter = item => filterFunctions.every(filterFn => filterFn(item));

    filteredData = jsonplaces.filter(combinedFilter);
    console.log(filteredData);


    if (eventType == "hover") {
      $('.wegw_filtered_result_count').html(filteredData.length);
      jQuery('.header_menu .search_head').val(jQuery('#searchbox_main_filter').val());
      jQuery('#loader-icon-filter').addClass("hide");
      jQuery('.wegw_filtered_result_count').removeClass("hide");
      if (filteredData.length == "0") {
        jQuery(".LoadMore").hide();
      } else {
        jQuery(".LoadMore").show();
      }

    } else if (eventType == "btnClick") {

      /* Check if the `Click` event is triggered or actually clicked */
      if (e.which) {
        var posts = filteredData;
        var countp = filteredData.length;

        if (posts == "" || countp < 1) {
          jQuery(".LoadMore").hide();
        } else {
          jQuery(".LoadMore").show();
        }

        /* 
          * Update respose coming from ajax function inside html
          * If hike count = 0. In script `places` load as empty json
          * Else load complete hike html inside the script
          */

        //check if its region page or tourenportal page
        if (filterOtherPage === 'region') {
          // jQuery(".region-single-wander-wrappe").html(posts[0]);
          jQuery(".region-single-wander-wrappe").html("");
          listModifiedData(posts, 0);
        } else {
        // jQuery(".single-wander-wrappe-json").html(posts[0]);
        $('.single-wander-wrappe-json').html("");
          listModifiedData(posts, 0);
        }

        if (countp > 0) {
          $('.wegw_filtered_result_count').html(posts.length);
          jQuery(".noWanderung").remove();
        } else {
          //check if its region page or tourenportal page
          if (filterOtherPage === 'region') {
            jQuery(".region-single-wander-wrappe").html('');
            listModifiedData(posts,0);
          } else {
          // jQuery(".single-wander-wrappe").html('');
          $('.single-wander-wrappe-json').html("");
            listModifiedData(posts,0);
          }

          if (jQuery(".noWanderung").length < 1) {
            jQuery("#wanderung-loadmore").before('<h2 class="noWanderung">Keine Wanderungen gefunden</h2>');
          }
        }
        /* If `Click` event is actually clicked trigger automatically to plot cluster markers */

        //check if its region page or tourenportal page
        triggerFilterResult = true;
        sortJsonData(sort, filteredData);

      }

    }
    /**to remove empty hike info*/
    toRemoveEmptyHikeInfo();
    jQuery("#wegw-preloader").css("display", "none");
    jQuery('.ListSec').removeClass('disabled');

}
  

