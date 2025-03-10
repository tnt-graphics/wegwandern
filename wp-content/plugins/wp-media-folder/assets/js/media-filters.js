'use strict';

/**
 * Folder filters for WPMF
 */
var wpmfFoldersFiltersModule = void 0;
(function ($) {
    var this_url = new URL(location.href);
    var get_taxonomy = this_url.searchParams.get("taxonomy");
    var get_term = this_url.searchParams.get("term");
    var get_wpmf_tag = this_url.searchParams.get("wpmf_tag");
    var wpmf_tag = 0;

    wpmfFoldersFiltersModule = {
        events: [], // event handling
        wpmf_all_media: false,
        /**
         * Initialize module related things
         */
        initModule: function initModule(page_type) {
            if (wpmf.vars.usefilter === 1) {
                // fix conflict with WP smush , image recycle plugin
                if (wpmf.vars.wpmf_pagenow === 'upload.php' && !page_type) {
                    return;
                }

                if (page_type === 'upload-list') {
                    wpmfFoldersFiltersModule.initListSizeFilter();

                    wpmfFoldersFiltersModule.initListWeightFilter();

                    wpmfFoldersFiltersModule.initListMyMediasFilter();

                    wpmfFoldersFiltersModule.initListAllMediasFilter();

                    wpmfFoldersFiltersModule.initListFolderOrderFilter();

                    wpmfFoldersFiltersModule.initListFilesOrderFilter();

                    // Auto submit when a select box is changed
                    $('.filter-items select').on('change', function () {
                        $('#post-query-submit').click();
                    });
                } else {
                    if (typeof wp.media.view.AttachmentsBrowser !== "undefined") {
                        wpmfFoldersFiltersModule.initSizeFilter();

                        wpmfFoldersFiltersModule.initWeightFilter();

                        wpmfFoldersFiltersModule.initMyMediasFilter();

                        wpmfFoldersFiltersModule.initFoldersOrderFilter();

                        wpmfFoldersFiltersModule.initFilesOrderFilter();
                    }
                }

                var initDropdown = function initDropdown($current_frame) {
                    // Check if the dropdown has already been added to the current frame
                    if (!$current_frame.find('.wpmf-dropdown').length) {
                        // Initialize dropdown
                        wpmfFoldersFiltersModule.initDropdown($current_frame);
                    }
                };

                if (wpmfFoldersModule.page_type === 'upload-list') {
                    wpmfFoldersFiltersModule.initFilter();
                    // Don't need to wait on list page
                    initDropdown(wpmfFoldersModule.getFrame());
                } else {
                    // Wait main module to be ready on modal window
                    wpmfFoldersModule.on('ready', function ($current_frame) {
                        wpmfFoldersFiltersModule.initFilter();
                        initDropdown($current_frame);
                    });
                }
            }
        },

        /**
         * Init filter
         */
        initFilter: function initFilter() {
            var data = {};
            var order_folder = wpmfFoldersModule.getCookie('media-order-folder' + wpmf.vars.host);
            var order_media = wpmfFoldersModule.getCookie('media-order-media' + wpmf.vars.host);
            var wpmf_all_media = wpmfFoldersModule.getCookie('wpmf_all_media' + wpmf.vars.host);
            var ownMedia = wpmfFoldersModule.getCookie('wpmf-display-media-filters' + wpmf.vars.host);

            if (typeof wpmf_all_media !== "undefined" && parseInt(wpmf_all_media) === 1) {
                $('.display-all-media').prepend('<span class="check"><i class="material-icons">check</i></span>');
                data.wpmf_all_media = wpmf_all_media;
                wpmfFoldersFiltersModule.wpmf_all_media = 1;
            }

            if (typeof ownMedia !== "undefined" && ownMedia === 'yes') {
                $('.own-user-media').prepend('<span class="check"><i class="material-icons">check</i></span>');
                $('#wpmf-display-media-filters').val(ownMedia);
                data.wpmf_display_media = 'yes';
            }

            if (typeof order_folder !== "undefined" && order_folder !== "undefined" && order_folder !== "null" && order_folder !== 'all' && order_folder !== '') {
                $('#media-order-folder').val(order_folder);
            }

            if (typeof order_media !== "undefined" && order_media !== "undefined" && order_media !== "null" && order_media !== 'all' && order_media !== '') {
                $('#media-order-media').val(order_media);
                switch (order_media) {
                    case 'date|asc':
                        data.orderby = false;
                        data.wpmf_orderby = 'date';
                        data.order = 'ASC';
                        break;

                    case 'date|desc':
                        data.orderby = false;
                        data.wpmf_orderby = 'date';
                        data.order = 'DESC';
                        break;

                    case 'title|asc':
                        data.meta_key = '';
                        data.orderby = false;
                        data.wpmf_orderby = 'title';
                        data.order = 'ASC';
                        break;

                    case 'title|desc':
                        data.meta_key = '';
                        data.orderby = false;
                        data.wpmf_orderby = 'title';
                        data.order = 'DESC';
                        break;

                    case 'size|asc':
                        data.meta_key = 'wpmf_size';
                        data.orderby = false;
                        data.wpmf_orderby = 'meta_value_num';
                        data.order = 'ASC';
                        break;

                    case 'size|desc':
                        data.meta_key = 'wpmf_size';
                        data.orderby = false;
                        data.wpmf_orderby = 'meta_value_num';
                        data.order = 'DESC';
                        break;

                    case 'filetype|asc':
                        data.meta_key = 'wpmf_filetype';
                        data.orderby = false;
                        data.wpmf_orderby = 'meta_value';
                        data.order = 'ASC';
                        break;

                    case 'filetype|desc':
                        data.meta_key = 'wpmf_filetype';
                        data.orderby = false;
                        data.wpmf_orderby = 'meta_value';
                        data.order = 'DESC';
                        break;
                    case 'custom':
                        data.meta_key = 'wpmf_order';
                        data.orderby = false;
                        data.wpmf_orderby = 'meta_value_num';
                        data.order = 'ASC';
                        break;

                }
            }

            if (wpmfFoldersModule.page_type !== 'upload-list') {
                var n = wpmfFoldersModule.getBackboneOfMedia();
                if (typeof n.view !== 'undefined') {
                    n.view.collection.props.set(data);
                }
            }
        },

        /**
         * Initialize media size filtering
         */
        initSizeFilter: function initSizeFilter() {
            var myMediaViewAttachmentsBrowser = wp.media.view.AttachmentsBrowser;

            // render filter to toolbar
            if (typeof myMediaViewAttachmentsBrowser !== "undefined") {
                wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
                    createToolbar: function createToolbar() {
                        // call the original method
                        myMediaViewAttachmentsBrowser.prototype.createToolbar.apply(this, arguments);

                        // add our custom filter
                        wpmfFoldersModule.attachments_browser.toolbar.set('sizetags', new wp.media.view.AttachmentFilters['wpmf_attachment_size']({
                            controller: wpmfFoldersModule.attachments_browser.controller,
                            model: wpmfFoldersModule.attachments_browser.collection.props,
                            priority: -74
                        }).render());
                    }
                });

                wp.media.view.AttachmentFilters['wpmf_attachment_size'] = wp.media.view.AttachmentFilters.extend({
                    className: 'wpmf-attachment-size attachment-filters',
                    id: 'media-attachment-size-filters',
                    createFilters: function createFilters() {
                        var filters = {};
                        _.each(wpmf.vars.wpmf_size || [], function (text) {
                            filters[text] = {
                                text: text,
                                props: {
                                    wpmf_size: text
                                }
                            };
                        });

                        filters.all = {
                            text: wpmf.l18n.all_size_label,
                            props: {
                                wpmf_size: 'all'
                            },
                            priority: 10
                        };

                        this.filters = filters;
                    }
                });
            }
        },

        /**
         * Initialize the media size filtering for list view
         */
        initListSizeFilter: function initListSizeFilter() {
            var filter_size = '<select id="media-attachment-size-filters" class="wpmf-attachment-size">';
            filter_size += '<option value="all" selected>' + wpmf.l18n.all_size_label + '</option>';
            $.each(wpmf.vars.wpmf_size, function (key) {
                if (this === wpmf.vars.size) {
                    filter_size += '<option value="' + this + '" selected>' + this + '</option>';
                } else {
                    filter_size += '<option value="' + this + '">' + this + '</option>';
                }
            });
            filter_size += '</select>';

            $('#wpmf-media-category').after('<input type="hidden" class="attachment_dates" name="attachment_dates" value="' + wpmf.vars.attachment_dates + '">');
            $('#wpmf-media-category').after('<input type="hidden" class="attachment_types" name="attachment_types" value="' + wpmf.vars.attachment_types + '">');
            $('#wpmf-media-category').after(filter_size + '<input type="hidden" class="attachment_sizes" name="attachment_sizes" value="' + wpmf.vars.size + '">');
        },

        /**
         * Initialize media weight filtering
         */
        initWeightFilter: function initWeightFilter() {
            var myMediaViewAttachmentsBrowser = wp.media.view.AttachmentsBrowser;

            wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
                createToolbar: function createToolbar() {
                    // call the original method
                    myMediaViewAttachmentsBrowser.prototype.createToolbar.apply(this, arguments);

                    // add our custom filter
                    wpmfFoldersModule.attachments_browser.toolbar.set('weighttags', new wp.media.view.AttachmentFilters['wpmf_attachment_weight']({
                        controller: this.controller,
                        model: this.collection.props,
                        priority: -74
                    }).render());
                }
            });

            wp.media.view.AttachmentFilters['wpmf_attachment_weight'] = wp.media.view.AttachmentFilters.extend({
                className: 'wpmf-attachment-weight attachment-filters',
                id: 'media-attachment-weight-filters',
                createFilters: function createFilters() {
                    var filters = {};
                    _.each(wpmf.vars.wpmf_weight || [], function (text) {
                        var labels = text[0].split('-');
                        var label = void 0;
                        if (text[1] === 'kB') {
                            label = labels[0] / 1024 + ' kB-' + labels[1] / 1024 + ' kB';
                        } else {
                            label = labels[0] / (1024 * 1024) + ' MB-' + labels[1] / (1024 * 1024) + ' MB';
                        }
                        filters[text[0]] = {
                            text: label,
                            props: {
                                wpmf_weight: text[0]
                            }
                        };
                    });

                    filters.all = {
                        text: wpmf.l18n.all_weight_label,
                        props: {
                            wpmf_weight: 'all'
                        },
                        priority: -74
                    };

                    this.filters = filters;
                }
            });
        },

        /**
         * Initialize the media weight filtering for list view
         */
        initListWeightFilter: function initListWeightFilter() {
            var filter_weight = '<select id="media-attachment-weight-filters" class="wpmf-attachment-weight">';
            filter_weight += '<option value="all" selected>' + wpmf.l18n.all_weight_label + '</option>';
            $.each(wpmf.vars.wpmf_weight, function (key, text) {
                var labels = text[0].split('-');
                var label = void 0;
                if (text[1] === 'kB') {
                    label = labels[0] / 1024 + ' kB-' + labels[1] / 1024 + ' kB';
                } else {
                    label = labels[0] / (1024 * 1024) + ' MB-' + labels[1] / (1024 * 1024) + ' MB';
                }
                if (text[0] === wpmf.vars.weight) {
                    filter_weight += '<option value="' + text[0] + '" selected>' + label + '</option>';
                } else {
                    filter_weight += '<option value="' + text[0] + '">' + label + '</option>';
                }
            });
            filter_weight += '</select>';
            $('#wpmf-media-category').after(filter_weight + '<input type="hidden" class="attachment_weights" name="attachment_weights" value="' + wpmf.vars.weight + '">');
        },

        /**
         * Initialize media folders ordering
         */
        initFoldersOrderFilter: function initFoldersOrderFilter() {
            wpmfFoldersModule.on('ready', function ($current_frame) {
                if ($current_frame.find('#media-order-folder').length) {
                    // Filter already initialized
                    return;
                }

                var element = '<select id="media-order-folder" class="wpmf-order-folder attachment-filters">';
                _.each(wpmf.l18n.order_folder || [], function (text, key) {
                    element += '<option value="' + key + '">' + text + '</option>';
                });
                element += '</select>';

                $current_frame.find('.media-frame-content .media-toolbar-secondary').append(element);

                $current_frame.find('#media-order-folder').on('change', function () {
                    var a = $(this).val();
                    if (typeof a !== "undefined" && a !== "undefined" && a !== "null" && a !== '') {
                        wpmfFoldersModule.setCookie('media-order-folder' + wpmf.vars.host, a, 365);
                    }

                    wpmfFoldersModule.setFolderOrdering(this.value);
                    wpmfFoldersFiltersModule.trigger('foldersOrderChanged');
                });
            });
        },

        /**
         * Initialize the media ordering for list view
         */
        initListFolderOrderFilter: function initListFolderOrderFilter() {
            var filter_order = '<select name="folder_order" id="media-order-folder" class="wpmf-order-folder wpmf-order">';
            $.each(wpmf.l18n.order_folder, function (key, text) {
                filter_order += '<option value="' + key + '">' + text + '</option>';
            });
            filter_order += '</select>';
            $('#wpmf-media-category').after(filter_order);

            if (wpmf.vars.wpmf_order_f && wpmf.vars.wpmf_order_f !== '') {
                $('.wpmf-order-folder option[value="' + wpmf.vars.wpmf_order_f + '"]').prop('selected', true);
            }
        },

        /**
         * Initialize media ordering
         */
        initFilesOrderFilter: function initFilesOrderFilter() {
            var myMediaViewAttachmentsBrowser = wp.media.view.AttachmentsBrowser;

            // render filter to toolbar
            wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
                createToolbar: function createToolbar() {
                    // call the original method
                    myMediaViewAttachmentsBrowser.prototype.createToolbar.apply(this, arguments);

                    // add our custom filter
                    wpmfFoldersModule.attachments_browser.toolbar.set('ordermediatags', new wp.media.view.AttachmentFilters['wpmf_order_media']({
                        controller: this.controller,
                        model: this.collection.props,
                        priority: -74
                    }).render());
                }
            });

            /* Filter sort media */
            wp.media.view.AttachmentFilters['wpmf_order_media'] = wp.media.view.AttachmentFilters.extend({
                className: 'wpmf-order-media attachment-filters',
                id: 'media-order-media',
                createFilters: function createFilters() {
                    var filters = {};
                    _.each(wpmf.l18n.order_media || [], function (text, key) {
                        switch (key) {
                            case 'date|asc':
                                filters[key] = {
                                    text: text,
                                    props: {
                                        orderby: false,
                                        wpmf_orderby: 'date',
                                        order: 'ASC'
                                    }
                                };
                                break;

                            case 'date|desc':
                                filters[key] = {
                                    text: text,
                                    props: {
                                        orderby: false,
                                        wpmf_orderby: 'date',
                                        order: 'DESC'
                                    }
                                };
                                break;

                            case 'title|asc':
                                filters[key] = {
                                    text: text,
                                    props: {
                                        meta_key: '',
                                        orderby: false,
                                        wpmf_orderby: 'title',
                                        order: 'ASC'
                                    }
                                };
                                break;

                            case 'title|desc':
                                filters[key] = {
                                    text: text,
                                    props: {
                                        meta_key: '',
                                        orderby: false,
                                        wpmf_orderby: 'title',
                                        order: 'DESC'
                                    }
                                };
                                break;

                            case 'size|asc':
                                filters[key] = {
                                    text: text,
                                    props: {
                                        meta_key: 'wpmf_size',
                                        orderby: false,
                                        wpmf_orderby: 'meta_value_num',
                                        order: 'ASC'
                                    }
                                };
                                break;

                            case 'size|desc':
                                filters[key] = {
                                    text: text,
                                    props: {
                                        meta_key: 'wpmf_size',
                                        orderby: false,
                                        wpmf_orderby: 'meta_value_num',
                                        order: 'DESC'
                                    }
                                };
                                break;

                            case 'filetype|asc':
                                filters[key] = {
                                    text: text,
                                    props: {
                                        meta_key: 'wpmf_filetype',
                                        orderby: false,
                                        wpmf_orderby: 'meta_value',
                                        order: 'ASC'
                                    }
                                };
                                break;

                            case 'filetype|desc':
                                filters[key] = {
                                    text: text,
                                    props: {
                                        meta_key: 'wpmf_filetype',
                                        orderby: false,
                                        wpmf_orderby: 'meta_value',
                                        order: 'DESC'
                                    }
                                };
                                break;
                            case 'custom':
                                filters[key] = {
                                    text: text,
                                    props: {
                                        meta_key: 'wpmf_order',
                                        orderby: false,
                                        wpmf_orderby: 'meta_value_num',
                                        order: 'ASC'
                                    }
                                };
                                break;

                        }
                    });

                    filters.all = {
                        text: wpmf.l18n.sort_media,
                        props: {
                            orderby: 'date',
                            order: 'DESC'
                        },
                        priority: 10
                    };

                    this.filters = filters;
                }
            });
        },

        initListFilesOrderFilter: function initListFilesOrderFilter() {
            var filter_order = '<select name="media-order-media" id="media-order-media" class="wpmf-order-media attachment-filters">';
            filter_order += '<option value="all" selected>' + wpmf.l18n.sort_media + '</option>';
            $.each(wpmf.l18n.order_media, function (key, text) {
                if (key === wpmf.vars.wpmf_order_media) {
                    filter_order += '<option value="' + key + '" selected>' + text + '</option>';
                } else {
                    filter_order += '<option value="' + key + '">' + text + '</option>';
                }
            });
            filter_order += '</select>';
            $('#wpmf-media-category').after(filter_order);

            if (wpmf.vars.wpmf_order_media && wpmf.vars.wpmf_order_media !== '') {
                $('.wpmf-order-folder option[value="' + wpmf.vars.wpmf_order_media + '"]').prop('selected', true);
            }
        },

        /**
         * Initialize own user media filtering
         */
        initMyMediasFilter: function initMyMediasFilter() {
            var myMediaViewAttachmentsBrowser = wp.media.view.AttachmentsBrowser;

            // render filter to toolbar
            wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
                createToolbar: function createToolbar() {
                    // call the original method
                    myMediaViewAttachmentsBrowser.prototype.createToolbar.apply(this, arguments);
                    this.toolbar.set('displaymediatags', new wp.media.view.AttachmentFilters['wpmf_filter_display_media']({
                        controller: this.controller,
                        model: this.collection.props,
                        priority: -80
                    }).render());
                }
            });

            wp.media.view.AttachmentFilters['wpmf_filter_display_media'] = wp.media.view.AttachmentFilters.extend({
                className: 'wpmf-filter-display-media attachment-filters',
                id: 'wpmf-display-media-filters',
                createFilters: function createFilters() {
                    var filters = {};
                    filters['yes'] = {
                        text: 'Yes',
                        props: {
                            wpmf_display_media: 'yes'
                        }
                    };

                    filters.all = {
                        text: 'No',
                        props: {
                            wpmf_display_media: 'no'
                        },
                        priority: 10
                    };

                    this.filters = filters;
                }
            });
        },

        /**
         * Initialize own user media filtering for list view
         */
        initListMyMediasFilter: function initListMyMediasFilter() {
            var filter_media = '<select id="wpmf-display-media-filters" name="wpmf-display-media-filters" class="wpmf-filter-display-media attachment-filters">';
            if (wpmf.vars.display_own_media === 'all') {
                filter_media += '<option value="all" selected>No</option>';
            } else {
                filter_media += '<option value="all">No</option>';
            }

            if (wpmf.vars.display_own_media === 'yes') {
                filter_media += '<option value="yes" selected>Yes</option>';
            } else {
                filter_media += '<option value="yes">Yes</option>';
            }

            filter_media += '</select>';
            $('#wpmf-media-category').after(filter_media);
        },

        /**
         * Initialize own user media filtering for list view
         */
        initListAllMediasFilter: function initListAllMediasFilter() {
            var filter_media = '<select id="wpmf_all_media" name="wpmf_all_media" class="wpmf_all_media attachment-filters">';
            if (parseInt(wpmf.vars.display_all_media) === 0) {
                filter_media += '<option value="0" selected>0</option>';
            } else {
                filter_media += '<option value="0">0</option>';
            }

            if (parseInt(wpmf.vars.display_all_media) === 1) {
                filter_media += '<option value="1" selected>1</option>';
            } else {
                filter_media += '<option value="1">1</option>';
            }

            filter_media += '</select>';
            $('#wpmf-media-category').after(filter_media);
        },

        /**
         * Generate the dropdown button which replace the filters
         */
        generateDropdown: function generateDropdown($current_frame) {
            var clear_filters = void 0,
                my_medias = '',
                all_medias = '',
                filter_type = '',
                filter_date = '',
                filter_size = '',
                filter_weight = '',
                sort_folder = '',
                sort_file = '';

            // Add folder ordering
            var folder_order_options = $current_frame.find('#media-order-folder option');
            if (folder_order_options.length) {
                sort_folder = '<li class="wpmf_filter_sort_folders"><i class="material-icons-outlined wpmf-filter-icon-left">sort</i><label class="wpmf_filter_label">' + wpmf.l18n.order_folder_label + '</label><i class="material-icons-outlined wpmf_icon_right">arrow_right</i><ul>';
                folder_order_options.each(function () {
                    sort_folder += '<li onclick="wpmfFoldersFiltersModule.selectFilter(\'#media-order-folder\', \'' + $(this).val() + '\');">';
                    sort_folder += $(this).html();
                    if ($(this).is(':selected')) {
                        sort_folder += '<span class="check"><i class="material-icons">check</i></span>';
                    }
                    sort_folder += '</li>';
                });
                sort_folder += '</ul></li>';
            }

            // Add media sorting
            var media_sort_options = $current_frame.find('#media-order-media option');
            if (media_sort_options.length) {
                sort_file = '<li class="wpmf_filter_sort_files"> <i class="material-icons-outlined wpmf-filter-icon-left">sort</i><label class="wpmf_filter_label">' + wpmf.l18n.order_img_label + '</label><i class="material-icons-outlined wpmf_icon_right">arrow_right</i><ul>';
                media_sort_options.each(function () {
                    sort_file += '<li onclick="wpmfFoldersFiltersModule.selectFilter(\'#media-order-media\', \'' + $(this).val() + '\');">';
                    sort_file += $(this).html();
                    if ($(this).is(':selected')) {
                        sort_file += '<span class="check"><i class="material-icons">check</i></span>';
                    }
                    sort_file += '</li>';
                });
                sort_file += '</ul></li>';
            }

            // add custom media type
            if (wpmfFoldersModule.page_type === 'upload-list') {
                if (typeof wpmf.vars.wpmfcount_pdf !== "undefined" && typeof wpmf.vars.wpmfcount_zip !== "undefined" && typeof wpmf.vars.wpmf_file !== "undefined") {
                    var wpmfoption = '<option data-filetype="pdf" value="wpmf-pdf">' + wpmf.l18n.pdf + ' (' + wpmf.vars.wpmfcount_pdf + ')</option>';
                    wpmfoption += '<option data-filetype="other" value="wpmf-other">' + wpmf.l18n.other + '</option>';
                    $('select[name="attachment-filter"] option[value="detached"]').before(wpmfoption);

                    if (wpmf.vars.wpmf_file !== '') {
                        $('select[name="attachment-filter"] option[value="' + wpmf.vars.wpmf_file + '"]').prop('selected', true);
                    }
                }
            }

            // Add type filtering for both grid and list views
            if (wpmfFoldersModule.page_type === 'upload-list') {
                var media_filter_options = $current_frame.find('#attachment-filter option');
            } else {
                media_filter_options = $current_frame.find('#media-attachment-filters option');
            }

            if (media_filter_options.length) {
                filter_type = '<li class="media_type_item wpmf-filter-lv1"> <i class="material-icons-outlined wpmf-filter-icon-left">play_lesson</i><label class="wpmf_filter_label">' + wpmf.l18n.media_type + '</label><i class="material-icons-outlined wpmf_icon_right">arrow_right</i><ul class="wpmf-filter-type">';
                var types = wpmf.vars.attachment_types;
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    types = types.replace('unattached', 'detached');
                } else {
                    types = types.replace('detached', 'unattached');
                }
                types = types.split(',');
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    if (types[0] == 'all') {
                        types[0] = '';
                    }
                } else {
                    if (types[0] == '') {
                        types[0] = 'all';
                    }
                }

                media_filter_options.each(function () {
                    var val = $(this).val();
                    var vals = val.split(',');
                    var val1 = vals[0];
                    var lb = $(this).html();
                    val = val.replace('post_mime_type:', '');
                    val1 = val1.replace('post_mime_type:', '');
                    filter_type += '<li class="wpmf-filter-li" data-value="' + val + '" data-label="' + lb + '" data-type="' + val + '" onclick="wpmfFoldersFiltersModule.selectFilter(\'#attachment-filter\', \'' + val + '\', true, \'.wpmf-filter-type\', \'post_mime_type\', \'' + lb + '\');">';
                    filter_type += lb;
                    if (types.indexOf(val1) != -1) {
                        filter_type += '<span class="check"><i class="material-icons">check</i></span>';
                    }
                    filter_type += '</li>';
                });
                filter_type += '</ul></li>';
            }

            // Add date filtering
            var date_filter_options = $current_frame.find('#media-attachment-date-filters option, #filter-by-date option');
            if (date_filter_options.length) {
                filter_date = '<li class="date_item wpmf-filter-lv1"> <i class="material-icons-outlined wpmf-filter-icon-left">date_range</i><label class="wpmf_filter_label">' + wpmf.l18n.date + '</label><i class="material-icons-outlined wpmf_icon_right">arrow_right</i><ul class="wpmf-filter-date">';
                var dates = wpmf.vars.attachment_dates;
                dates = dates.split(',');
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    if (dates[0] == 'all' || dates[0] == '') {
                        dates[0] = '0';
                    }
                }

                date_filter_options.each(function () {
                    var val = $(this).val();
                    var lb = $(this).html();
                    filter_date += '<li class="wpmf-filter-li" data-value="' + val + '" data-label="' + lb + '" onclick="wpmfFoldersFiltersModule.selectFilter(\'#media-attachment-date-filters, #filter-by-date\', \'' + val + '\', true, \'.wpmf-filter-date\', \'wpmf_date\', \'' + lb + '\');">';
                    filter_date += lb;
                    if (dates.indexOf(val) != -1) {
                        filter_date += '<span class="check"><i class="material-icons">check</i></span>';
                    }
                    filter_date += '</li>';
                });
                filter_date += '</ul></li>';
            }

            // Add size filtering
            var size_filter_options = $current_frame.find('#media-attachment-size-filters option');
            if (size_filter_options.length) {
                if (size_filter_options.length <= 3) {
                    filter_size = '<li class="filter_sort_size_item wpmf-filter-lv1"> <i class="material-icons-outlined wpmf-filter-icon-left">aspect_ratio</i><label class="wpmf_filter_label">' + wpmf.l18n.lang_size + '</label><i class="material-icons-outlined wpmf_icon_right">arrow_right</i><ul class="wpmf-filter-sizes">';
                } else {
                    filter_size = '<li class="wpmf_filter_sort_size filter_sort_size_item wpmf-filter-lv1"> <i class="material-icons-outlined wpmf-filter-icon-left">aspect_ratio</i><label class="wpmf_filter_label">' + wpmf.l18n.lang_size + '</label><i class="material-icons-outlined wpmf_icon_right">arrow_right</i><ul class="wpmf-filter-sizes">';
                }

                var sizes = wpmf.vars.size;
                sizes = sizes.split(',');
                size_filter_options.each(function () {
                    var val = $(this).val();
                    var lb = $(this).html();
                    filter_size += '<li class="wpmf-filter-li" data-value="' + val + '" data-label="' + lb + '" onclick="wpmfFoldersFiltersModule.selectFilter(\'#media-attachment-size-filters\', \'' + val + '\', true, \'.wpmf-filter-sizes\', \'wpmf_size\', \'' + lb + '\');">';
                    filter_size += $(this).html();
                    if (sizes.indexOf(val) != -1) {
                        filter_size += '<span class="check"><i class="material-icons">check</i></span>';
                    }
                    filter_size += '</li>';
                });

                filter_size += '</ul></li>';
            }

            // Add weight filtering
            var weight_filter_options = $current_frame.find('#media-attachment-weight-filters option');
            if (weight_filter_options.length) {
                if (weight_filter_options.length <= 3) {
                    filter_weight = '<li class="filter_sort_weight_item wpmf-filter-lv1"> <i class="material-icons-outlined wpmf-filter-icon-left">hourglass_empty</i><label class="wpmf_filter_label">' + wpmf.l18n.lang_weight + '</label><i class="material-icons-outlined wpmf_icon_right">arrow_right</i><ul class="wpmf-filter-weights">';
                } else {
                    filter_weight = '<li class="wpmf_filter_sort_weight filter_sort_weight_item wpmf-filter-lv1"> <i class="material-icons-outlined wpmf-filter-icon-left">hourglass_empty</i><label class="wpmf_filter_label">' + wpmf.l18n.lang_weight + '</label><i class="material-icons-outlined wpmf_icon_right">arrow_right</i><ul class="wpmf-filter-weights">';
                }

                var weights = wpmf.vars.weight;
                weights = weights.split(',');
                weight_filter_options.each(function () {
                    var val = $(this).val();
                    var lb = $(this).html();
                    filter_weight += '<li class="wpmf-filter-li" data-value="' + val + '" data-label="' + lb + '" onclick="wpmfFoldersFiltersModule.selectFilter(\'#media-attachment-weight-filters\', \'' + val + '\', true, \'.wpmf-filter-weights\', \'wpmf_weight\', \'' + lb + '\');">';
                    filter_weight += $(this).html();
                    if (weights.indexOf(val) != -1) {
                        filter_weight += '<span class="check"><i class="material-icons">check</i></span>';
                    }
                    filter_weight += '</li>';
                });

                filter_weight += '</ul></li>';
            }

            clear_filters = '<li onclick="wpmfFoldersFiltersModule.clearFilters();" class="clearfilters-item"><i class="material-icons-outlined wpmf-filter-icon-left">cleaning_services</i><label class="wpmf_filter_label">' + wpmf.l18n.clear_filters + '</label></li>';

            // Own user media
            if (parseInt(wpmf.vars.hide_own_media_button) === 0) {
                my_medias += '<li class="own-user-media" onclick="wpmfFoldersFiltersModule.displayOwnMedia(\'#wpmf-display-media-filters\');">';
                my_medias += '<i class="material-icons-outlined wpmf-filter-icon-left">person</i>';
                my_medias += '<label class="wpmf_filter_label">' + wpmf.l18n.display_own_media + '</label>';
                if ($current_frame.find('#wpmf-display-media-filters').val() === 'yes') {
                    my_medias += '<span class="check"><i class="material-icons">check</i></span>';
                }
                my_medias += '</li>';
            }

            if (parseInt(wpmf.vars.show_all_files_button) === 1) {
                all_medias += '<li class="display-all-media" onclick="wpmfFoldersFiltersModule.displayAllMedia(\'#wpmf_all_media\');">';
                all_medias += '<i class="material-icons-outlined wpmf-filter-icon-left">all_inclusive</i>';
                all_medias += '<label class="wpmf_filter_label">' + wpmf.l18n.display_all_files + '</label>';
                if (wpmfFoldersFiltersModule.wpmf_all_media || parseInt($current_frame.find('#wpmf_all_media').val()) === 1) {
                    all_medias += '<span class="check"><i class="material-icons">check</i></span>';
                }
                all_medias += '</li>';
            }

            return '<div class="wpmf-dropdowns-wrap"><div class="wpmf-filters-dropdown wpmf-dropdown-wrap">\n                            <a class="wpmf-filters-dropdown-button button wpmf-dropdown-btn">' + wpmf.l18n.filter_label + '</a>\n                            <ul>\n                                ' + clear_filters + '\n                                \n                                ' + my_medias + '\n                                \n                                ' + all_medias + '\n                                \n                                ' + filter_type + '\n                                \n                                ' + filter_date + '\n                                \n                                ' + filter_size + '\n                                \n                                ' + filter_weight + '\n                            </ul>\n                        </div>\n                        <div class="wpmf-sorts-dropdown wpmf-dropdown-wrap">\n                            <a class="wpmf-filters-dropdown-button button wpmf-dropdown-btn">' + wpmf.l18n.sort_label + '</a>\n                            <ul>\n                                ' + sort_folder + '\n                                \n                                ' + sort_file + '\n                            </ul>\n                        </div></div>\n                        ';
        },

        /**
         * Reset the dropdown button
         * @param $current_frame
         */
        initDropdown: function initDropdown($current_frame) {
            // Add dropdown
            if ($current_frame.find('.wpmf-dropdowns-wrap').length) {
                // Create dropdown
                $current_frame.find('.wpmf-dropdowns-wrap').replaceWith(wpmfFoldersFiltersModule.generateDropdown($current_frame));

                // Replace dropdown if exists
            } else if (wpmfFoldersModule.page_type === 'upload-list') {
                $current_frame.find('.filter-items').append(wpmfFoldersFiltersModule.generateDropdown($current_frame));
            } else {
                if ($current_frame.find('.media-toolbar-secondary .select-mode-toggle-button').length) {
                    $current_frame.find('.media-toolbar-secondary .select-mode-toggle-button').after(wpmfFoldersFiltersModule.generateDropdown($current_frame));
                } else {
                    $current_frame.find('.media-toolbar-secondary .wpmf-media-categories').after(wpmfFoldersFiltersModule.generateDropdown($current_frame));
                }
            }

            if (parseInt(wpmf.vars.show_all_files_button) === 1 && !$current_frame.find('.wpmf-allfiles-btn').length) {
                $current_frame.find('.wpmf-dropdowns-wrap').append('<a class="wpmf-allfiles-btn button">' + wpmf.l18n.display_all_files + '</a>');
            }

            wpmfFoldersFiltersModule.renderFilters();
            // remove filter if not checked
            var filters = ['.wpmf-filter-type', '.wpmf-filter-date', '.wpmf-filter-sizes', '.wpmf-filter-weights'];
            $.each(filters, function (i, selector) {
                var query_param = void 0;
                switch (selector) {
                    case '.wpmf-filter-type':
                        query_param = 'post_mime_type';
                        break;
                    case '.wpmf-filter-date':
                        query_param = 'wpmf_date';
                        break;
                    case '.wpmf-filter-sizes':
                        query_param = 'wpmf_size';
                        break;
                    case '.wpmf-filter-weights':
                        query_param = 'wpmf_weight';
                        break;
                }
                $(selector).find('.check').each(function (j, item) {
                    var value = $(item).closest('li').data('value');
                    var label = $(item).closest('li').data('label');
                    if (value != 0 && value != '' && value != 'all') {
                        $('.wpmf-filter-row[data-filter="' + selector + '"] .wpmf-filter-row-body').append('<div class="wpmf-filter-row-body-item" data-value="' + value + '">' + label + '<span class="material-icons-outlined" onclick="wpmfFoldersFiltersModule.selectFilter(\'\', \'' + value + '\', true, \'' + selector + '\', \'' + query_param + '\', \'' + label + '\');"> close </span></div>');
                    }
                });
            });

            $.each(filters, function (i, key) {
                if ($('.wpmf-filter-row[data-filter="' + key + '"] .wpmf-filter-row-body-item').length) {
                    $('.wpmf-filter-row[data-filter="' + key + '"]').show();
                } else {
                    $('.wpmf-filter-row[data-filter="' + key + '"]').hide();
                }
            });

            // update filter status
            wpmfFoldersFiltersModule.updateFiltersStatus();

            // add active class if selected a filter
            $('.wpmf-dropdown-wrap').each(function () {
                if ($(this).find('ul li ul li:not(:first-child) .check').length || $(this).find('.own-user-media .check').length || $(this).find('.display-all-media .check').length) {
                    var count = $(this).find('ul li ul li:not(:first-child) .check').length + $(this).find('.own-user-media .check').length + $(this).find('.display-all-media .check').length;
                    $(this).append('<span class="wpmf_dropdown_item_active" data-count="' + count + '"></span>');
                    $(this).find('.wpmf-dropdown-btn').addClass('active');
                }
            });

            if ($('.display-all-media .check').length || wpmfFoldersFiltersModule.wpmf_all_media) {
                $('.attachments .wpmf-attachment').hide();
            } else {
                $('.attachments .wpmf-attachment').show();
            }

            // Button to open dropdown
            $current_frame.find('.wpmf-dropdown-wrap > a').bind('click', function () {
                var $this = $(this);
                $this.closest('.wpmf-dropdown-wrap').find(' > ul').css('display', 'inline-block').css('left', $this.position().left);
            });

            $current_frame.find('.wpmf-allfiles-btn').bind('click', function (e) {
                e.preventDefault();
                wpmfFoldersModule.changeFolder(0);
                wpmfFoldersFiltersModule.displayAllMedia('#wpmf_all_media', true);
            });

            // Click outside the dropdown to close it
            $(window).bind('click', function (event) {
                if ($(event.target).hasClass('wpmf-dropdown-btn') || $(event.target).hasClass('wpmf-filter-li')) {
                    return;
                }
                $current_frame.find('.wpmf-dropdown-wrap > ul').css('display', '');
            });
        },

        /**
         * Select a filter and trigger change
         * @param filter_elem
         * @param value
         */
        selectFilter: function selectFilter(filter_elem, value) {
            // Check filter tags
            if ((get_taxonomy && get_term && get_taxonomy == 'wpmf_tag') || get_wpmf_tag) {
                if (get_term) {
                    wpmf_tag = get_term;
                }
                if (get_wpmf_tag) {
                    wpmf_tag = get_wpmf_tag;
                }
                wpmfFoldersModule.setCookie('wpmf_tag', wpmf_tag, 365);
            }

            var multiple = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
            var selector = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
            var query_param = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : '';
            var label = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : '';

            // Save current value in case of undo
            if (!multiple) {
                var _current_value = $(filter_elem).val();
                if (filter_elem.indexOf('#') === -1) {
                    $('#' + filter_elem).val(value).trigger('change');
                } else {
                    $(filter_elem).val(value).trigger('change');
                }
            } else {
                wpmfFoldersFiltersModule.renderFilters();
                var check = void 0;
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    check = value != 'all' && value != '' && value != '0';
                } else {
                    check = value != 'all' && value != '';
                }
                if ($(selector).find('li[data-value="' + value + '"] .check').length) {
                    $(selector).find('li[data-value="' + value + '"] .check').remove();
                    $('.wpmf-filter-row[data-filter="' + selector + '"] .wpmf-filter-row-body .wpmf-filter-row-body-item[data-value="' + value + '"]').remove();
                } else {
                    $(selector).find('li[data-value="' + value + '"]').append('<span class="check"><i class="material-icons">check</i></span>');
                    if (check) {
                        $('.wpmf-filter-row[data-filter="' + selector + '"] .wpmf-filter-row-body').append('<div class="wpmf-filter-row-body-item" data-value="' + value + '">' + label + '<span class="material-icons-outlined" onclick="wpmfFoldersFiltersModule.selectFilter(\'' + filter_elem + '\', \'' + value + '\', true, \'' + selector + '\', \'' + query_param + '\', \'' + label + '\');"> close </span></div>');
                    }
                }

                if (check) {
                    $(selector).find('li:first-child .check').remove();
                } else {
                    $(selector).find('li:not(li:first-child) .check').remove();
                    $('.wpmf-filter-row[data-filter="' + selector + '"] .wpmf-filter-row-body .wpmf-filter-row-body-item').remove();
                }

                if (!$(selector).find('li .check').length) {
                    $(selector).find('li:first-child').append('<span class="check"><i class="material-icons">check</i></span>');
                }

                // remove filter if not checked
                var filters = ['.wpmf-filter-type', '.wpmf-filter-date', '.wpmf-filter-sizes', '.wpmf-filter-weights'];
                $.each(filters, function (i, key) {
                    if ($('.wpmf-filter-row[data-filter="' + key + '"] .wpmf-filter-row-body-item').length) {
                        $('.wpmf-filter-row[data-filter="' + key + '"]').show();
                    } else {
                        $('.wpmf-filter-row[data-filter="' + key + '"]').hide();
                    }
                });

                // update filter status
                wpmfFoldersFiltersModule.updateFiltersStatus();

                var values = [];
                $(selector).find('li .check').each(function (i, item) {
                    var val = $(item).closest('li').data('value');
                    values.push(val);
                });

                if (wpmfFoldersModule.page_type === 'upload-list') {
                    if (selector === '.wpmf-filter-type') {
                        $('.attachment_types').val(values.join());
                        wpmfFoldersModule.setCookie('wpmf_post_mime_type' + wpmf.vars.host, values.join(), 365);
                        if (values.includes("trash")) {
                            wpmfFoldersFiltersModule.selectFilter('#attachment-filter', 'trash');
                        } else {
                            wpmfFoldersFiltersModule.selectFilter('#attachment-filter', '');
                        }
                        $('.upload-php #posts-filter').submit();
                    }

                    if (selector === '.wpmf-filter-date') {
                        $('.attachment_dates').val(values.join());
                        wpmfFoldersModule.setCookie('wpmf_wpmf_date' + wpmf.vars.host, values.join(), 365);
                        $('.upload-php #posts-filter').submit();
                    }

                    if (selector === '.wpmf-filter-sizes') {
                        $('.attachment_sizes').val(values.join());
                        wpmfFoldersModule.setCookie('wpmf_wpmf_size' + wpmf.vars.host, values.join(), 365);
                        $('.upload-php #posts-filter').submit();
                    }

                    if (selector === '.wpmf-filter-weights') {
                        $('.attachment_weights').val(values.join());
                        wpmfFoldersModule.setCookie('wpmf_wpmf_weight' + wpmf.vars.host, values.join(), 365);
                        $('.upload-php #posts-filter').submit();
                    }
                } else {
                    var months = wp.media.view.settings.months;
                    var obj = {};
                    if (query_param === 'wpmf_date') {
                        var new_values = [];
                        $.each(values, function (i, v) {
                            if (v === 'all') {
                                new_values.push('all');
                            } else {
                                if (typeof months[v] !== "undefined") {
                                    new_values.push(months[v].year + months[v].month);
                                }
                            }
                        });
                        obj[query_param] = new_values.join();
                    } else {
                        obj[query_param] = values.join();
                    }
                    var n = wpmfFoldersModule.getBackboneOfMedia(false);
                    n.view.collection.props.set(obj);

                    // update filter value
                    if (selector === '.wpmf-filter-type') {
                        wpmf.vars.attachment_types = values.join();
                    }

                    if (selector === '.wpmf-filter-date') {
                        wpmf.vars.attachment_dates = values.join();
                    }

                    if (selector === '.wpmf-filter-sizes') {
                        wpmf.vars.size = values.join();
                    }

                    if (selector === '.wpmf-filter-weights') {
                        wpmf.vars.weight = values.join();
                    }
                    wpmfFoldersModule.setCookie('wpmf_' + query_param + wpmf.vars.host, values.join(), 365);
                }
            }

            if ((filter_elem === 'media-order-media' || filter_elem === 'media-order-folder') && wpmfFoldersModule.page_type !== 'upload-list') {
                wpmfFoldersModule.reloadAttachments();
            }

            $(['media-order-folder', 'media-order-media', 'wpmf-display-media-filters']).each(function () {
                var a = $('#' + this.toString()).val();
                wpmfFoldersModule.setCookie(this.toString() + wpmf.vars.host, a, 365);
            });

            if (!multiple) {
                // Show snackbar
                wpmfSnackbarModule.show({
                    id: 'undo_filter',
                    content: wpmf.l18n.wpmf_undofilter,
                    is_undoable: true,
                    onUndo: function onUndo() {
                        wpmfFoldersFiltersModule.selectFilter(filter_elem, current_value);
                    }
                });
            }

            // Force reloading folders
            wpmfFoldersModule.renderFolders();

            if (!multiple) {
                wpmfFoldersFiltersModule.initDropdown(wpmfFoldersModule.getFrame());
            }
        },

        updateFiltersStatus: function updateFiltersStatus() {
            $('.wpmf-filters-dropdown').each(function () {
                if ($(this).find('ul li ul li:not(:first-child) .check').length || $(this).find('.own-user-media .check').length || $(this).find('.display-all-media .check').length) {
                    $(this).find('.wpmf-filters-dropdown-button').addClass('active');
                } else {
                    $(this).find('.wpmf-filters-dropdown-button').removeClass('active');
                }
            });
        },

        renderFilters: function renderFilters() {
            if ($('.wpmf-filters-dropdown .wpmf-filter-lv1 ul li .check').length) {
                if (!$('.wpmf-filter-rows-li').length) {
                    var html = '<li class="wpmf-filter-rows-li"><div class="wpmf-filter-rows">';
                    var filters = ['.wpmf-filter-type', '.wpmf-filter-date', '.wpmf-filter-sizes', '.wpmf-filter-weights'];

                    $.each(filters, function (i, key) {
                        var label = void 0,
                            icon = void 0;
                        switch (key) {
                            case '.wpmf-filter-type':
                                label = wpmf.l18n.media_type;
                                icon = '<i class="material-icons-outlined">play_lesson</i>';
                                break;
                            case '.wpmf-filter-date':
                                label = wpmf.l18n.date;
                                icon = '<i class="material-icons-outlined">date_range</i>';
                                break;
                            case '.wpmf-filter-sizes':
                                label = wpmf.l18n.lang_size;
                                icon = '<i class="material-icons-outlined">aspect_ratio</i>';
                                break;
                            case '.wpmf-filter-weights':
                                label = wpmf.l18n.lang_weight;
                                icon = '<i class="material-icons-outlined">hourglass_empty</i>';
                                break;
                        }

                        html += '<div class="wpmf-filter-row" data-filter="' + key + '">';
                        html += '<div class="wpmf-filter-row-header">' + icon + label + '</div>';
                        html += '<div class="wpmf-filter-row-body">';
                        html += '</div>';
                        html += '</div>';
                    });

                    html += '</div></li>';
                    $('.wpmf-filters-dropdown > ul').prepend(html);
                }
            } else {
                $('.wpmf-filter-rows-li').remove();
            }
        },

        displayAllMedia: function displayAllMedia(filter_elem) {
            var all = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

            var wpmf_all_media = void 0;
            if (wpmfFoldersModule.page_type === 'upload-list') {
                if (!all) {
                    wpmf_all_media = $(filter_elem).find('option:not(:selected)').val();
                    $(filter_elem).val(wpmf_all_media).trigger('change');
                } else {
                    wpmf_all_media = 1;
                    $(filter_elem).val(1).trigger('change');
                }
                wpmfFoldersModule.setCookie('wpmf_all_media' + wpmf.vars.host, wpmf_all_media, 365);
            } else {
                if (!all) {
                    wpmf_all_media = wpmfFoldersFiltersModule.wpmf_all_media ? 0 : 1;
                } else {
                    wpmf_all_media = 1;
                }
                wpmfFoldersFiltersModule.wpmf_all_media = wpmf_all_media;
                var n = wpmfFoldersModule.getBackboneOfMedia();
                n.view.collection.props.set({ wpmf_all_media: wpmf_all_media });
                wpmfFoldersModule.setCookie('wpmf_all_media' + wpmf.vars.host, wpmf_all_media, 365);
            }

            wpmfFoldersFiltersModule.initDropdown(wpmfFoldersModule.getFrame());
        },

        /**
         * Toggle a filter value and trigger change
         * @param filter_elem
         */
        displayOwnMedia: function displayOwnMedia(filter_elem) {
            var ownMedia = $(filter_elem).find('option:not(:selected)').val();
            $(filter_elem).val(ownMedia).trigger('change');
            wpmfFoldersModule.setCookie('wpmf-display-media-filters' + wpmf.vars.host, ownMedia, 365);
            // Force reloading folders
            wpmfFoldersModule.renderFolders();

            wpmfFoldersFiltersModule.initDropdown(wpmfFoldersModule.getFrame());
        },

        /**
         * Clear all filters
         */
        clearFilters: function clearFilters() {
            // delete cookie filter tag
            wpmfFoldersModule.setCookie('wpmf_tag', '0');

            $(['wpmf_post_mime_type', 'attachment-filter', 'wpmf_wpmf_date', 'wpmf_wpmf_size', 'wpmf_wpmf_weight', 'media-order-folder', 'media-order-media', 'wpmf-display-media-filters', 'wpmf_all_media']).each(function () {
                // delete cookie filter
                wpmfFoldersModule.setCookie(this.toString() + wpmf.vars.host, 'all', 365);
                if (wpmfFoldersModule.page_type !== 'upload-list') {
                    if (this.toString() === 'media-order-folder') {
                        wpmfFoldersFiltersModule.selectFilter(this.toString(), 'name-ASC');
                    } else {
                        wpmfFoldersFiltersModule.selectFilter(this.toString(), 'all');
                    }
                }
            });

            $('.wpmf-filter-rows-li').remove();
            if (wpmfFoldersModule.page_type !== 'upload-list') {
                $('.display-all-media .check').remove();
                wpmfFoldersFiltersModule.wpmf_all_media = false;
                var n = wpmfFoldersModule.getBackboneOfMedia();
                n.view.collection.props.set({ wpmf_all_media: 0, 'wpmf_display_media': 'no', 'wpmf_orderby': 'date', 'order': 'DESC', 'post_mime_type': 'all', 'wpmf_date': 'all', 'wpmf_size': 'all', 'wpmf_weight': 'all' });
                wpmf.vars.attachment_types = 'all';
                wpmf.vars.attachment_dates = 'all';
                wpmf.vars.size = 'all';
                wpmf.vars.weight = 'all';
            } else {
                $('.attachment_types, .attachment_dates, #attachment-filter').val('');
                $('.attachment_dates').val('0');
                $('.attachment_sizes, .attachment_weights').val('all');
                $('#wpmf-display-media-filters, #media-order-media').val('all');
                $('#media-order-folder').val('name-ASC');
                $('#wpmf_all_media').val(0);
                $('#posts-filter').submit();
            }

            // Force reloading folders
            wpmfFoldersModule.renderFolders();
            // Reload the dropdown
            wpmfFoldersFiltersModule.initDropdown(wpmfFoldersModule.getFrame());
            if (wpmfFoldersModule.page_type === 'upload-grid') {
                wpmfFoldersModule.setCookie('wpmf_tag', '0');
                location.href = wpmf.vars.site_url + '/wp-admin/upload.php';
            }
        },

        /**
         * Trigger an event
         * @param event string the event name
         * @param arguments
         */
        trigger: function trigger(event) {
            // Retrieve the list of arguments to send to the function
            var args = Array.from(arguments).slice(1);

            // Retrieve registered function
            var events = wpmfFoldersFiltersModule.events[event];

            // For each registered function apply arguments
            for (var e in events) {
                events[e].apply(this, args);
            }
        },

        /**
         * Subscribe to an or multiple events
         * @param events {string|array} event name
         * @param subscriber function the callback function
         */
        on: function on(events, subscriber) {
            // If event is a string convert it as an array
            if (typeof events === 'string') {
                events = [events];
            }

            // Allow multiple event to subscript
            for (var ij in events) {
                if (typeof subscriber === 'function') {
                    if (typeof wpmfFoldersFiltersModule.events[events[ij]] === "undefined") {
                        this.events[events[ij]] = [];
                    }
                    wpmfFoldersFiltersModule.events[events[ij]].push(subscriber);
                }
            }
        }
    };

    // add filter work with Easing Slider plugin
    if (wpmf.vars.base === 'toplevel_page_easingslider') {
        wpmfFoldersFiltersModule.initSizeFilter();

        wpmfFoldersFiltersModule.initWeightFilter();

        wpmfFoldersFiltersModule.initMyMediasFilter();

        wpmfFoldersFiltersModule.initFoldersOrderFilter();

        wpmfFoldersFiltersModule.initFilesOrderFilter();
    }

    // Wait for the main WPMF module filters initialization
    wpmfFoldersModule.on('afterFiltersInitialization', function () {
        wpmfFoldersFiltersModule.initModule(wpmfFoldersModule.page_type);
    });

    jQuery(document).ready(function ($) {
        if (get_wpmf_tag) {
            wpmfFoldersModule.setCookie('lastAccessFolder_' + wpmf.vars.host, 0);
            wpmfFoldersModule.setCookie('wpmf_all_media' + wpmf.vars.host, 1);
            wpmfFoldersModule.setCookie('wpmf_tag', get_wpmf_tag, 365);
        };
        // Count tag button
        var element_count_tag = $('#the-list .column-posts').attr('data-colname');
        if (element_count_tag && element_count_tag.toLowerCase() == 'count') {
            $('#the-list .column-posts a').on('click', function(e){
                e.preventDefault();
                let url = $(this).attr('href');
                let url_array = url.split("=");
                let wpmf_tag_array = null;
                let wpmf_tag_filter = null;
                if (url_array) {
                    wpmf_tag_array = url_array[1].split("&");
                    if (wpmf_tag_array) {
                        wpmf_tag_filter = wpmf_tag_array[0];
                    }
                }
                
                if (url) {
                    wpmfFoldersModule.setCookie('lastAccessFolder_' + wpmf.vars.host, 0);
                    wpmfFoldersModule.setCookie('wpmf_all_media' + wpmf.vars.host, 1);
                    if (wpmf_tag_filter) {
                        wpmfFoldersModule.setCookie('wpmf_tag', wpmf_tag_filter, 365);
                    }
                    location.href = url;
                }
            });
        }
    });
})(jQuery);
