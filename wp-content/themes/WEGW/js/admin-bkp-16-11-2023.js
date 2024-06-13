if (typeof $ === 'undefined') {
  var $ = jQuery;
}
var filterOtherPage;
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

  } else {
    filterOtherPage = '';
  }

  /*Show count in the filter during initial load*/
  wegw_map_filter_results('hover', filterOtherPage);
  jQuery(".filterMenu .filter_content_wrapper .fc_block_select label input[type=checkbox]").on('change', function () {
    jQuery(this).parent().toggleClass('active');

  });
  /*show only count in filter when checkbox is selected*/
  jQuery('.filterMenu .filter_content_wrapper input[type=checkbox]').on('change', function () {
    wegw_map_filter_results('hover', filterOtherPage);
  });

  /*keyup event on search field*/
  jQuery(".search input").on("keyup", function (e) {
    /* show/hide close icon in search in menu sidebar  */
    if (jQuery('.navigation_search input').val().length != 0) {
      jQuery('.navigation_search_close').removeClass("hide");
    } else {
      jQuery('.navigation_search_close').addClass("hide");
    }
    /* show/hide close icon in search in header  */
    if (jQuery('.head_navigation_search input').val().length != 0) {
      jQuery('.head_navigation_search_close').removeClass("hide");
    } else {
      jQuery('.head_navigation_search_close').addClass("hide");
    }
    /* show/hide close icon in search in responsive cluster map(tourenportal)  */
    if (jQuery('#map-resp .map_main_search input').val().length != 0) {
      jQuery('#map-resp .map_main_search_close').removeClass("hide");
    } else {
      jQuery('#map-resp .map_main_search_close').addClass("hide");
    }
    /* show/hide close icon in search in desktop cluster map(tourenportal)  */
    if (jQuery('#map_desktop .map_main_search input').val().length != 0) {
      jQuery('#map_desktop .map_main_search_close').removeClass("hide");
    } else {
      jQuery('#map_desktop .map_main_search_close').addClass("hide");
    }
  });

  if (jQuery('.searchinputField input').length > 0) {
    /* show/hide close icon in search in search results page  */
    if (jQuery('.searchinputField input').val().length != 0) {
      jQuery('.searchResult_search_close').removeClass("hide");
    } else {
      jQuery('.searchResult_search_close').addClass("hide");
    }
  }
  

  jQuery(".searchinputField input").on("keyup", function (e) {
    /* show/hide close icon in search in search results page  */
    if (jQuery('.searchinputField input').val().length != 0) {
      jQuery('.searchResult_search_close').removeClass("hide");
    } else {
      jQuery('.searchResult_search_close').addClass("hide");
    }
  });

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
        wegw_map_filter_results('hover', filterOtherPage);
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
    if (jQuery(".single-wander-wrappe").length || jQuery(".region-single-wander-wrappe").length) {
      var data = generate_data_array(null);
      var mapDragEvent = $('#wanderung-loadmore').data("event");
      var listDiv;

      /* Update on 11/07/2023 for intial sort drag map issue */
      // if (mapDragEvent == 'regionenMap') {
      if (filterOtherPage === 'region') {
        listDiv = $('.region-single-wander-wrappe');
        //  data['action'] = "wanderung_regionen_map_load_more";
        //   data['regionen_id'] = $('#regionen_id').val();
        data['action'] = "wanderung_drag_map_hikes_load_more";
        data['map_page'] = "region";
      } else {
        listDiv = $('.single-wander-wrappe');
        data['action'] = "wanderung_drag_map_hikes_load_more";
        // data['filtered_map_ids'] = $('#mapDragFilterHikeId').val();
      }

      data['filtered_map_ids'] = $('#mapDragFilterHikeId').val();
      data['nonce'] = ajax_object.ajax_nonce;
      data['sort'] = sort;
      data['count'] = itemcount;
      var data = data;

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
          var countp = jQuery(posts[0]).filter(".single-wander").length;
          console.log(countp);
          if (posts == "" || countp < 1) {
            console.log("empty");
            jQuery(".LoadMore").hide();
          }
          jQuery('#loader-icon').addClass("hide");

          if (countp > 0) {
            console.log(posts[0]);
            listDiv.append(posts[0]);
            /**to remove empty hike info*/
            toRemoveEmptyHikeInfo();
            jQuery(".noWanderung").remove();
          } else {

            if (jQuery(".noWanderung").length < 1) {
              jQuery("#wanderung-loadmore").before(posts);
            }
          }
        },
        error: function () {
          jQuery('#loader-icon').addClass("hide");
        },
      });
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
    var data = generate_data_array(null);

    var mapDragEvent = $('#wanderung-loadmore').data("event");

    /* Check page type */
    if (filterOtherPage === 'region') {
      data['map_page'] = "region";
    } else {
      data['map_page'] = "";
    }

    data['filtered_map_ids'] = $('#mapDragFilterHikeId').val();

    // if (mapDragEvent == 'dragMap') {
    data['action'] = "wanderung_drag_map_hikes_sort_query";
    // } else {
    //   data['action'] = "get_wanderung_sort_query";
    // }
    data['nonce'] = ajax_object.ajax_nonce;
    data['sort'] = sort;

    jQuery.ajax({
      url: ajax_object.ajax_url,
      type: "post",
      data: data,
      beforeSend: function () {
        loadmore.show();
        jQuery("#wegw-preloader").css("display", "block");
      },
      success: function (response) {
        var posts = JSON.parse(response);
        var countp = jQuery(posts[0]).filter(".single-wander").length;
        console.log(countp);
        if (posts == "" || countp < 1) {
          console.log("empty");
          jQuery(".LoadMore").hide();
        }
        if (countp > 0) {
          if (filterOtherPage === 'region') {
            jQuery(".region-single-wander-wrappe").html(posts);
          } else {
            jQuery(".single-wander-wrappe").html(posts);
          }

        }
        /**to remove empty hike info*/
        toRemoveEmptyHikeInfo();
        jQuery("#wegw-preloader").css("display", "none");
      },
      error: function () { },
    });

  });

  // header search
  jQuery('.header_menu .search_head').keyup(function (e) {
    if (e.key === 'Enter' || e.keyCode === 13) {

      /* Remove `dragMap` from load more btn */
      $('#wanderung-loadmore').attr("data-event", "");

      var data = generate_data_array(null);

      data['action'] = "get_wanderung_sort_query";
      data['nonce'] = ajax_object.ajax_nonce;
      jQuery.ajax({
        url: ajax_object.ajax_url,
        type: "post",
        data: data,
        beforeSend: function () {
          loadmore.show();
          jQuery("#wegw-preloader").css("display", "block");
        },
        success: function (response) {
          var posts = JSON.parse(response);
          var countp = jQuery(posts[0]).filter(".single-wander").length;
          console.log(countp);
          if (posts == "" || countp < 1) {
            jQuery(".LoadMore").hide();
          }
          if (countp > 0) {
            if (filterOtherPage === 'region') {
              jQuery(".region-single-wander-wrappe").html(posts);
            } else {
              jQuery(".single-wander-wrappe").html(posts);
            }

            jQuery(".noWanderung").remove();
          } else {
            if (filterOtherPage === 'region') {
              jQuery(".region-single-wander-wrappe").html('');
            } else {
              jQuery(".single-wander-wrappe").html('');
            }

            if (jQuery(".noWanderung").length < 1) {
              jQuery("#wanderung-loadmore").before('<h2 class="noWanderung">' + posts[1] + '</h2>');
            }
          }
          jQuery("#wegw-preloader").css("display", "none");
          jQuery('#searchbox_main_filter').val(jQuery('.header_menu .search_head').val());
        },
        error: function () { },
      });
      // wegw_map_filter_results('hover', e);
      wegw_map_filter_results('btnClick', e, filterOtherPage);
    }
  });
  /*
  * Filter hike results via main sidebar in tourenportal page
  */
  jQuery('#wegw_map_filter_btn').on("click", function (e) {
    /* Remove `dragMap` from load more btn */
    $('#wanderung-loadmore').attr("data-event", "");
    $('#mapDragFilterHikeId').remove();
    if (filterOtherPage === 'region') {
      //Filter hike results via main sidebar in region page
      wegw_map_filter_results('btnClick', e, filterOtherPage);
    } else {
      //Filter hike results via main sidebar in tourenportal page
      wegw_map_filter_results('btnClick', e);
    }

    jQuery("#wegw-preloader").css("display", "block");
    jQuery('.filterMenu').addClass('filterWindow');
  });


  jQuery('.filter_reset').on("click", function (e) {
    /* Remove `dragMap` from load more btn */
    $('#wanderung-loadmore').attr("data-event", "");
    $('#mapDragFilterHikeId').remove();
    reset_filter('btnClick', e);

  });

  /* clear the menu serach when clicked on close icon  */
  jQuery('.navigation_search .navigation_search_close').on("click", function (e) {
    jQuery(".navigation_search input").val("");
    jQuery('.navigation_search_close').addClass("hide");
  });
  /* clear the serach on responsive map when clicked on close icon  */
  jQuery('#map-resp .map_main_search  .map_main_search_close').on("click", function (e) {
    jQuery("#map-resp .map_main_search  input").val("");
    jQuery('#map-resp .map_main_search_close').addClass("hide");
  });
  /* clear the serach on desktop map when clicked on close icon  */
  jQuery('#map_desktop .map_main_search  .map_main_search_close').on("click", function (e) {
    jQuery("#map_desktop .map_main_search  input").val("");
    jQuery('#map_desktop .map_main_search_close').addClass("hide");
  });
  /* clear the serach in header when clicked on close icon  */
  jQuery('.head_navigation_search .head_navigation_search_close').on("click", function (e) {
    jQuery(".head_navigation_search input").val("");
    jQuery('.head_navigation_search_close').addClass("hide");
  });
  /* clear the serach in search results page  when clicked on close icon  */
  jQuery('.searchinputField .searchResult_search_close').on("click", function (e) {
    jQuery(".searchinputField input").val("");
    jQuery('.searchResult_search_close').addClass("hide");
  });

});

function wegw_map_filter_results(eventType, e, filterOtherPage) {

  /* Sort Order */

  /* Searchbox Filter */
  var data = generate_data_array(null);

  /* Check page type */
  if (filterOtherPage == 'region') {
    data['map_page'] = "region";
  } else {
    data['map_page'] = "";
  }

  data['action'] = "get_wanderung_sidebar_filter_query";
  data['event_type'] = eventType;
  data['event'] = "total_hikes_filter";
  data['nonce'] = ajax_object.ajax_nonce;
  $.ajax({
    url: ajax_object.ajax_url,
    type: "POST",
    data: data,
    beforeSend: function () {
      if (eventType == "hover") {
        jQuery('#loader-icon-filter').removeClass("hide");
        jQuery('.wegw_filtered_result_count').addClass("hide");
      } else {
        jQuery("#wegw-preloader").css("display", "block");
        jQuery('.ListSec').addClass('disabled');
      }
    },
    success: function (response) {
      if (eventType == "hover") {
        $('.wegw_filtered_result_count').html(response);
        jQuery('.header_menu .search_head').val(jQuery('#searchbox_main_filter').val());
        jQuery('#loader-icon-filter').addClass("hide");
        jQuery('.wegw_filtered_result_count').removeClass("hide");
        if (response == "0") {
          jQuery(".LoadMore").hide();
        } else {
          jQuery(".LoadMore").show();
        }

      } else if (eventType == "btnClick") {

        /* Check if the `Click` event is triggered or actually clicked */
        if (e.which) {
          var posts = JSON.parse(response);
          var countp = jQuery(posts[0]).filter(".single-wander").length;

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
            jQuery(".region-single-wander-wrappe").html(posts[0]);
          } else {
            jQuery(".single-wander-wrappe").html(posts[0]);
          }

          if (countp > 0) {
            $('.wegw_filtered_result_count').html(posts.count);
            jQuery(".noWanderung").remove();
          } else {
            //check if its region page or tourenportal page
            if (filterOtherPage === 'region') {
              jQuery(".region-single-wander-wrappe").html('');
            } else {
              jQuery(".single-wander-wrappe").html('');
            }

            if (jQuery(".noWanderung").length < 1) {
              jQuery("#wanderung-loadmore").before('<h2 class="noWanderung">' + posts[1] + '</h2>');
            }
          }
          /* If `Click` event is actually clicked trigger automatically to plot cluster markers */

          //check if its region page or tourenportal page
          $("#wegw_map_filter_btn").trigger("click");

        }
        /**to remove empty hike info*/
        toRemoveEmptyHikeInfo();
        jQuery("#wegw-preloader").css("display", "none");
        jQuery('.ListSec').removeClass('disabled');
      }
    },
    error: function () { },
  });
clearSearch
}

/** header search clear  */
function clearSearch() {
  jQuery(".header_menu .search.search input").val("");
  jQuery('.header_menu .filter_search_close').addClass("hide");
  jQuery('#searchbox_main_filter').val("");
  //  document.cookie = 'wegw_loc' +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
  location.reload();
}

/* Reset filter option values */
function reset_filter(eventType, e) {

  /* Reset search place textbox value & remove location cookie */
  clearSearchFilter();

  /* Reset slider `Umgebungssuche` */
  $('#radius_search').val('0km');

  /* Reset `Schwierigkeitsgrad` values */
  $('.fc_diff_level label').removeClass('active');

  /* Reset `Nach Monaten` values */
  $('.fc_block_month label').removeClass('active');

  /* Reset multi sliders */
  resetSlider();

  clearActivityCheck();
  jQuery('.filterMenu .filter_content_wrapper input[type=checkbox]').prop('checked', false);

  if ($('.filter-btn.region-filter').length > 0) {
    
    regionCheckbox();
    themaCheckbox();
    routenverlaufCheckbox();
    angebotCheckbox();
    ausdauerCheckbox();
    aktivitatCheckbox();
    saisonCheckbox();
    anforderungCheckbox();


  } else {

    $('.wanderregionen_search').prop('checked', false);
    $('.thema_search').prop('checked', false);
    $('.routenverlauf_search').prop('checked', false);
    $('.angebote_search').prop('checked', false);
    $('.ausdauer_search').prop('checked', false);
    $('.activity_search').prop('checked', false);
    $('.difficulty_search').prop('checked', false);
  }

  var data = generate_data_array('reset');

  /* Check page type */
  if (filterOtherPage == 'region') {
    data['map_page'] = "region";
  } else {
    data['map_page'] = "";
  }

  data['action'] = "get_wanderung_sidebar_filter_query";
  data['event_type'] = eventType;
  data['event'] = "total_hikes_filter";
  data['nonce'] = ajax_object.ajax_nonce;
  $.ajax({
    url: ajax_object.ajax_url,
    type: "POST",
    data: data,
    beforeSend: function () {
      if (eventType == 'hover') {
        jQuery('#loader-icon-filter').removeClass("hide");
        jQuery('.wegw_filtered_result_count').addClass("hide");
      } else {
        jQuery("#wegw-preloader").css("display", "block");
        jQuery('.ListSec').addClass('disabled');
      }

    },
    success: function (response) {
      if (eventType == 'hover') {
        jQuery('.wegw_filtered_result_count').html(response);
        jQuery('#loader-icon-filter').addClass("hide");
        jQuery('.wegw_filtered_result_count').removeClass("hide");
      } else if (eventType == 'btnClick') {
        if (e.which) {
          var posts = JSON.parse(response);
          var countp = jQuery(posts[0]).filter(".single-wander").length;
          if (posts == "" || countp < 1) {
            console.log("empty");
            jQuery(".LoadMore").hide();
          } else {
            jQuery(".LoadMore").show();
          }

          /* 
          * Update respose coming from ajax function inside html
          * If hike count = 0. In script `places` load as empty json
          * Else load complete hike html inside the script
          */
          if (filterOtherPage === 'region') {
            jQuery(".region-single-wander-wrappe").html(posts[0]);
          } else {
            jQuery(".single-wander-wrappe").html(posts[0]);
          }
          if (countp > 0) {
            $('.wegw_filtered_result_count').html(posts.count);
            jQuery(".noWanderung").remove();
          } else {
            if (filterOtherPage === 'region') {
              jQuery(".region-single-wander-wrappe").html('');
            } else {
              jQuery(".single-wander-wrappe").html('');
            }

            $('.wegw_filtered_result_count').html(posts.count);
            if (jQuery(".noWanderung").length < 1) {
              jQuery("#wanderung-loadmore").before('<h2 class="noWanderung">' + posts[1] + '</h2>');
            }
          }
          $(".filter_reset").trigger("click");

        }
        /**to remove empty hike info*/
        toRemoveEmptyHikeInfo();
        jQuery("#wegw-preloader").css("display", "none");
        jQuery('.ListSec').removeClass('disabled');
      }
    },
    error: function () { },
  });
}

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

function generate_data_array(dataReset) {
  var initial, final;
  //get initial and final data of double slider in filter window
  if (dataReset === 'reset') {
    initial = 'data-initial';
    final = 'data-final';
  } else {
    initial = 'data-first';
    final = 'data-second';
  }

  var loc = jQuery('.header_menu .search_head').val();
  var itemcount = jQuery(".single-wander").length;
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

  // var searchbox_filter = $('#searchbox_main_filter').val();
  var searchbox_filter = "";

  /* Umgebungssuche - Radius Search */
  // var radius_search = $('#radius_search').val().replace(/\D/g,'');
  var radius_search = "";

  jQuery('.singleVal').css('left', '0');
  /* Activity */
  var activity_search = [];
  $('.activity_search:checked').each(function () {
    activity_search.push($(this).val());
  });

  /* Schwierigkeitsgrad - Difficulty level */
  var difficulty_search = [];
  // $('.difficulty_search:checked').each(function(){
  //     difficulty_search.push($(this).val());
  // });
  var valuesArray;
  $('.difficulty_search:checked').each(function () {
    var activeParentCheckboxes = $('.difficulty_search').filter(function () {
      return $(this).parent().hasClass('active');
    });

    valuesArray = activeParentCheckboxes.map(function () {
      return $(this).val();
    }).get();
  });
  difficulty_search = difficulty_search.concat(valuesArray);
  /** if Winterwandern is selected */
  if (activity_search.includes('19')) {
    difficulty_search = [];
  }

  /* Dauer - Hiking Duration */
  var duration_start_point = $('.dauer-slider').attr(initial);
  var duration_end_point = $('.dauer-slider').attr(final);

  /* Kilometer */
  var distance_start_point = $('.km-slider').attr(initial);
  var distance_end_point = $('.km-slider').attr(final);
  /* Ascent */
  var ascent_start_point = $('.aufstieg-slider').attr(initial);
  var ascent_end_point = $('.aufstieg-slider').attr(final);

  /* Descent */
  var descent_start_point = $('.abstieg-slider').attr(initial);
  var descent_end_point = $('.abstieg-slider').attr(final);

  /* Wanderregion */
  var wanderregionen_search = [];
  $('.wanderregionen_search:checked').each(function () {
    wanderregionen_search.push($(this).val());
  });

  /* Angebote */
  var angebote_search = [];
  $('.angebote_search:checked').each(function () {
    angebote_search.push($(this).val());
  });

  /* Thema */
  var thema_search = [];
  $('.thema_search:checked').each(function () {
    thema_search.push($(this).val());
  });

  /* Routenverlauf */
  var routenverlauf_search = [];
  $('.routenverlauf_search:checked').each(function () {
    routenverlauf_search.push($(this).val());
  });

  /* Ausdauer */
  var ausdauer_search = [];
  $('.ausdauer_search:checked').each(function () {
    ausdauer_search.push($(this).val());
  });

  /* Tiefster / höchster Punkt */
  // var altitude_start_point = $('#altitude_start_point').val().replace(/\D/g,'');
  // var altitude_end_point = $('#altitude_end_point').val().replace(/\D/g,'');
  var altitude_start_point = $('.aaltitude-slider').attr(initial);
  var altitude_end_point = $('.aaltitude-slider').attr(final);

  /* Nach Monaten */
  var wander_saison_search = [];
  $('.wander_saison_search:checked').each(function () {
    wander_saison_search.push($(this).val());
  });

  var data = {
    loc: loc,
    sort: sort,
    itemcount: itemcount,
    'searchbox_filter': searchbox_filter,
    'radius_search': radius_search,
    'activity_search': activity_search,
    'difficulty_search': difficulty_search,
    'duration_start_point': duration_start_point,
    'duration_end_point': duration_end_point,
    'distance_start_point': distance_start_point,
    'distance_end_point': distance_end_point,
    'ascent_start_point': ascent_start_point,
    'ascent_end_point': ascent_end_point,
    'descent_start_point': descent_start_point,
    'descent_end_point': descent_end_point,
    'wanderregionen_search': wanderregionen_search,
    'angebote_search': angebote_search,
    'thema_search': thema_search,
    'routenverlauf_search': routenverlauf_search,
    'ausdauer_search': ausdauer_search,
    'altitude_start_point': altitude_start_point,
    'altitude_end_point': altitude_end_point,
    'wander_saison_search': wander_saison_search,
  };
  return data;
}
function regionCheckbox() {
  var checkboxes = document.querySelectorAll(".wanderregionen_search");

  // Find and check the checkbox with the value "21"
  checkboxes.forEach(function (checkbox) {
    if (parseInt(checkbox.value) === $('.filter-btn.region-filter').data('val')) {
      checkbox.checked = true;
    }
  });


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

//loadmore for blog

jQuery(document).on("click", "#blog-loadmore", function () {
  var itemcount = jQuery(".blog-wander").length;
  var loadmore  = jQuery(".LoadMore");
  var listDiv   = jQuery('.blog_list');
  var page_type = jQuery(".page_type").val();
  jQuery('#loader-icon').removeClass("hide");
  var data = {
    'action' : 'wanderung_blogs_load_more',
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
      var countp = jQuery(posts[0]).filter(".blog-wander").length;
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
          jQuery("#blog-loadmore").before(posts);
        }
      }
    },
    error: function () {
      jQuery('#loader-icon').addClass("hide");
    },
  });

});

//loadmore for Search page
jQuery(document).on("click", "#search-loadmore", function () {
  
  var loadmore  = jQuery(".LoadMore");
 
  var itemcounts = jQuery(this).data("count");
  var search_query =  jQuery(this).data("query");
  var search_offset =  jQuery(this).data("offset");
  var post_type = jQuery(this).attr("data-postType");
  let next_offset = parseInt(search_offset) + 9 ; 
  jQuery(this).data("offset", next_offset);
  
  var search_nonce =  jQuery(this).data("nonce");


  if( itemcounts < next_offset ){
    jQuery('#search-loadmore').hide();
    jQuery('.noWanderungSearchPost').show();
  }


  jQuery('#loader-icon').removeClass("hide");
  var data = {
    'action' : 'wanderung_search_load_more',
    'count'  : itemcounts,
    'search_query' : search_query,
    'offset' : search_offset,
    'search_nonce' : search_nonce,
    'post_type' : post_type
  
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
      jQuery('.searchResult_list').append(response.data);
		jQuery('#loader-icon').addClass("hide");
    },
    error: function () {
      jQuery('#loader-icon').addClass("hide");
    },
  });

});

//loadmore for taxonomies
jQuery(document).on("click", "#taxonomy-loadmore", function () {
  
  var loadmore  = jQuery(".LoadMore");
 
  var itemcounts = jQuery(this).data("count");
  var search_offset =  jQuery(this).data("offset");
  var post_type = jQuery(this).attr("data-postType");
  var taxonomy = jQuery(this).attr("data-taxonomy");
  var term_id = jQuery(this).attr("data-termId");
  let next_offset = parseInt(search_offset) + 9 ; 
  jQuery(this).data("offset", next_offset);
  
  var taxonomy_nonce =  jQuery(this).data("nonce");


  if( itemcounts < next_offset ){
    jQuery('#taxonomy-loadmore').hide();
    jQuery('.noWanderungSearchPost').show();
  }


  jQuery('#loader-icon').removeClass("hide");
  var data = {
    'action' : 'wanderung_taxonomy_load_more',
    'count'  : itemcounts,
    'offset' : search_offset,
    'taxonomy_nonce' : taxonomy_nonce,
    'post_type' : post_type,
    'taxonomy' : taxonomy,
    'term_id' : term_id
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
      jQuery('.searchResult_list').append(response.data);
		jQuery('#loader-icon').addClass("hide");
    },
    error: function () {
      jQuery('#loader-icon').addClass("hide");
    },
  });

});