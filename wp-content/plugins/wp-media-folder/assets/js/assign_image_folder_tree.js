(function ($) {
    wpmfAssignModule = {
        options: {},
        files_selected: [],
        /**
         * Initialize module related things
         */
        initModule: function ($current_frame) {
            wpmfAssignModule.options = {
                'root': '/',
                'showroot': wpmf.l18n.assign_tree_label,
                'onclick': function (elem, type, file) {
                },
                'oncheck': function (elem, checked, type, file) {
                },
                'usecheckboxes': true, //can be true files dirs or false
                'expandSpeed': 500,
                'collapseSpeed': 500,
                'expandEasing': null,
                'collapseEasing': null,
                'canselect': true
            };

            // add Media folder selection button on toolbar
            if (!$current_frame.find('.open-popup-tree-multiple').length) {
                $current_frame.find('.media-frame-content .media-toolbar-secondary .delete-selected-button').after('<button class="button open-popup-tree-multiple media-button button-large"><span class="material-icons-outlined"> snippet_folder </span>' + wpmf.l18n.assign_tree_label + '</button>');
                wpmfAssignModule.treeshowdialog();

                if (typeof wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].drive_type !== "undefined" && wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].drive_type !== "") {
                    $('.open-popup-tree-multiple').addClass('hide');
                } else {
                    $('.open-popup-tree-multiple').removeClass('hide');
                }

                wpmfFoldersModule.on('changeFolder', function (folder_id) {
                    if (typeof wpmfFoldersModule.categories[folder_id] !== "undefined" && typeof wpmfFoldersModule.categories[folder_id].drive_type !== "undefined" && wpmfFoldersModule.categories[folder_id].drive_type !== "") {
                        $('.open-popup-tree-multiple').addClass('hide');
                    } else {
                        $('.open-popup-tree-multiple').removeClass('hide');
                    }
                });
            }
        },
        initTree: function () {
            $assignimagetree = $('#wpmfjaoassign');
            if (!$assignimagetree) {
                return;
            }

            if (wpmfAssignModule.options.showroot !== '') {
                var tree_init = '';
                tree_init += '<ul class="jaofiletree">';
                tree_init += '<li data-id="0" class="directory collapsed selected" data-group="' + wpmf.vars.wpmf_current_userid + '">';
                tree_init += '<div class="pure-checkbox">';
                tree_init += '<input type="checkbox" id="/" class="wpmf_checkbox_tree" value="wpmf_' + wpmf.vars.root_media_root + '" data-id="' + wpmf.vars.root_media_root + '">';
                tree_init += '<label class="checked" for="/">';
                tree_init += '<a class="title-folder title-root" data-id="0">' + wpmfAssignModule.options.showroot + '</a>';
                tree_init += '</label>';
                tree_init += '</div>';
                tree_init += '</li>';
                tree_init += '</ul>';
                tree_init += '<input type="hidden" class="folder_selections_input">';
                $assignimagetree.html(tree_init);
            }

            wpmfAssignModule.openfolderassign(0);
        },
        /**
         * open folder tree by dir name
         */
        openfolderassign: function (id) {
            if (typeof $assignimagetree === "undefined")
                return;
            if ($assignimagetree.find('a[data-id="' + id + '"]').closest('li').hasClass('expanded') || $assignimagetree.find('a[data-id="' + id + '"]').closest('li').hasClass('wait')) {
                if (typeof callback === 'function')
                    callback();
                return;
            }
            /* ajax get tree assign */
            var ret;
            ret = $.ajax({
                method: 'POST',
                url: ajaxurl,
                data: {
                    id: id,
                    attachment_id: wpmfFoldersModule.editFileId,
                    action: 'wpmf',
                    task: 'get_assign_tree',
                    wpmf_nonce: wpmf.vars.wpmf_nonce
                },
                context: $assignimagetree,
                dataType: 'json',
                beforeSend: function () {
                    this.find('a[data-id="' + id + '"]').closest('li').addClass('wait');
                }
            }).done(function (res) {

                var selectedId = $('#wpmfjaoassign').find('.directory.selected').data('id');
                ret = '<ul class="jaofiletree">';
                if (res.status) {
                    if (typeof res.folders !== "undefined") {
                        $('.folder_selections_input').val(res.folders);
                    }
                    var datas = res.dirs;
                    if ((!$('.media-frame').hasClass('mode-select') && $('body').hasClass('upload-php')) || !$('body').hasClass('upload-php')) {
                        if (res.root_check) {
                            $('.wpmf_checkbox_tree[data-id="' + wpmf.vars.root_media_root + '"]').prop('checked', true);
                        }
                    }

                    for (var ij = 0; ij < datas.length; ij++) {
                        if (wpmf.vars.root_media_root !== datas[ij].id) {
                            var classe = 'directory collapsed';
                            if (parseInt(datas[ij].id) === parseInt(selectedId)) {
                                classe += ' selected';
                            }

                            ret += '<li class="' + classe + '" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-group="' + datas[ij].term_group + '">';
                            if (datas[ij].count_child > 0) {
                                ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '"></div>';
                            } else {
                                ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" style="opacity:0"></div>';
                            }

                            ret += '<div class="pure-checkbox">';

                            if ($('.media-frame').hasClass('mode-select') && $('body').hasClass('upload-php')) {
                                ret += '<input type="checkbox" id="wpmf_folder_selection' + datas[ij].id + '" class="wpmf_checkbox_tree" value="wpmf_' + datas[ij].id + '" data-id="' + datas[ij].id + '">';
                            } else {
                                if (datas[ij].checked) {
                                    ret += '<input type="checkbox" checked id="wpmf_folder_selection' + datas[ij].id + '" class="wpmf_checkbox_tree" value="wpmf_' + datas[ij].id + '" data-id="' + datas[ij].id + '">';
                                } else {
                                    ret += '<input type="checkbox" id="wpmf_folder_selection' + datas[ij].id + '" class="wpmf_checkbox_tree" value="wpmf_' + datas[ij].id + '" data-id="' + datas[ij].id + '">';
                                }
                            }

                            if (datas[ij].checked) {
                                ret += '<label class="check" for="wpmf_folder_selection' + datas[ij].id + '">';
                            } else {
                                if (datas[ij].pchecked) {
                                    ret += '<label class="pchecked" for="wpmf_folder_selection' + datas[ij].id + '">';
                                    ret += '<span class="ppp"></span>'
                                } else {
                                    ret += '<label for="wpmf_folder_selection' + datas[ij].id + '">';
                                }
                            }

                            if (parseInt(datas[ij].id) === parseInt(selectedId)) {
                                ret += '<i class="zmdi wpmf-zmdi-folder-open"></i>';
                            } else {
                                ret += '<i class="zmdi zmdi-folder-outline"></i>';
                            }
                            ret += '<a class="title-folder" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '">' + datas[ij].name + '</a>';
                            ret += '</label>';
                            ret += '</div';
                            ret += '</li>';
                        }
                    }
                }
                ret += '</ul>';

                this.find('a[data-id="' + id + '"]').closest('li').removeClass('wait').removeClass('collapsed').addClass('expanded');
                this.find('a[data-id="' + id + '"]').closest('li').append(ret);
                this.find('a[data-id="' + id + '"]').closest('li').children('.jaofiletree').slideDown(wpmfAssignModule.options.expandSpeed, wpmfAssignModule.options.expandEasing,
                    function () {
                        $assignimagetree.trigger('afteropen');
                        $assignimagetree.trigger('afterupdate');
                        if (typeof callback === 'function')
                            callback();
                    });

                wpmfAssignModule.seteventsassign();

            }).done(function () {
                $assignimagetree.trigger('afteropen');
                $assignimagetree.trigger('afterupdate');
            });
        },

        /**
         * close folder tree by dir name
         * @param id
         */
        closedirassign: function (id) {
            if (typeof $assignimagetree === "undefined") {
                return;
            }

            $assignimagetree.find('a[data-id="' + id + '"]').closest('li').children('.jaofiletree').slideUp(wpmfAssignModule.options.collapseSpeed, wpmfAssignModule.options.collapseEasing, function () {
                $(this).remove();
            });

            $assignimagetree.find('a[data-id="' + id + '"]').closest('li').removeClass('expanded').addClass('collapsed');
            wpmfAssignModule.seteventsassign();

            /* Trigger custom event */
            $assignimagetree.trigger('afterclose');
            $assignimagetree.trigger('afterupdate');
        },

        /**
         * init event click to open/close folder tree
         */
        seteventsassign: function () {
            var $assignimagetree = $('#wpmfjaoassign');
            $assignimagetree.find('li a,li .icon-open-close').unbind('click');
            //Bind for collapse or expand elements
            $assignimagetree.find('li.directory a').bind('click', function (e) {
                e.preventDefault();
                $assignimagetree.find('li').removeClass('selected');
                $assignimagetree.find('i.zmdi').removeClass('wpmf-zmdi-folder-open').addClass("zmdi-folder-outline");
                $(this).closest('li').addClass("selected");
                $(this).closest('li').find(' > .pure-checkbox i.zmdi').removeClass("zmdi-folder-outline").addClass("wpmf-zmdi-folder-open");
                wpmfAssignModule.openfolderassign($(this).attr('data-id'));
            });

            /* open folder tree use icon */
            $assignimagetree.find('li.directory.collapsed .icon-open-close').bind('click', function () {
                wpmfAssignModule.openfolderassign($(this).attr('data-id'));
            });

            /* close folder tree use icon */
            $assignimagetree.find('li.directory.expanded .icon-open-close').bind('click', function () {
                wpmfAssignModule.closedirassign($(this).attr('data-id'));
            });
            /* Check/uncheck folder */
            $assignimagetree.find('li.directory.expanded .wpmf_checkbox_tree').bind('click', function () {
                if ($(this).is(':checked')) {
                    $(this).closest('.pure-checkbox').find('label').removeClass('pchecked').addClass('checked');
                } else {
                    $(this).closest('.pure-checkbox').find('label').removeClass('checked');
                }
            });

            /* Check/uncheck folder */
            $assignimagetree.find('li.directory .wpmf_checkbox_tree').bind('click', function () {
                var folders = $('.folder_selections_input').val();
                var folders_number;
                if (folders != '') {
                    folders_number = folders.split(',').map(function(item) {
                        return parseInt(item, 10);
                    });
                } else {
                    folders_number = [];
                }

                var id = $(this).data('id');
                if ($(this).is(':checked')) {
                    if (folders_number.indexOf(id) == -1) {
                        folders_number.push(id);
                    }
                } else {
                    var index = folders_number.indexOf(id);
                    if (index > -1) {
                        folders_number.splice(index, 1);
                    }
                }
                $('.folder_selections_input').val(folders_number.join());
            });
        },

        /**
         * showdialog
         */
        showdialog: function (type) {
            showDialog({
                title: wpmf.l18n.label_assign_tree,
                id: 'ju-dialog',
                text: '<div id="wpmfjaoassign" class="wpmflocaltree"></div>',
                negative: {
                    title: wpmf.l18n.cancel
                },
                positive: {
                    title: wpmf.l18n.label_apply,
                    onClick: function () {
                        wpmfAssignModule.wpmf_set_term(type);
                    }
                }
            });
        },

        /**
         * Show dialog for tree
         */
        treeshowdialog: function () {
            $(document).on('click', '.open-popup-tree, .open-popup-tree-multiple', function () {
                var $this = $(this);
                if ($('.wpmf-folder_selection').length === 0) {
                    $('body').append('<div class="wpmf-folder_selection" data-wpmftype="folder_selection" data-timeout="3000" data-html-allowed="true" data-content="' + wpmf.l18n.folder_selection + '"></div>');
                }

                if ($this.hasClass('open-popup-tree')) {
                    wpmfAssignModule.showdialog('one');
                } else {
                    wpmfAssignModule.showdialog('multiple');
                }

                wpmfFoldersModule.editFileId = $('.wpmf_attachment_id').val();
                if (typeof wpmfFoldersModule.editFileId === "undefined")
                    wpmfFoldersModule.editFileId = $('#post_ID').val();
                wpmfAssignModule.initTree();
            });
        },

        /**
         * Set files to folder
         */
        wpmf_set_term: function (type) {
            var wpmf_term_ids_check = $('.folder_selections_input').val();
            var attachment_ids = [];
            if (type === 'multiple') {
                attachment_ids = [];
                wpmfFoldersModule.getFrame().find('.attachments-browser .attachment.selected').each(function (i, v) {
                    attachment_ids.push($(v).data('id'));
                });
            } else {
                attachment_ids.push(wpmfFoldersModule.editFileId);
            }

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpmf',
                    task: 'set_object_term',
                    wpmf_term_ids_check: wpmf_term_ids_check,
                    attachment_ids: attachment_ids.join(),
                    wpmf_nonce: wpmf.vars.wpmf_nonce
                },
                success: function (response) {
                    if (!response.status) {
                        return;
                    }

                    let snack_msg = wpmf.l18n.folder_selection;
                    if (wpmf_term_ids_check.length) {
                        let folders = wpmf_term_ids_check.slice(0, 2);
                        let fnames = [];
                        $.each(folders, function () {
                            if (parseInt(wpmf.vars.root_media_root) !== parseInt(this)) {
                                fnames.push(wpmfFoldersModule.categories[this].label);
                            }
                        });

                        if (wpmf_term_ids_check.length > 2) {
                            snack_msg = attachment_ids.length + ' files has moved to "' + fnames.join() + '..."';
                        } else {
                            snack_msg = attachment_ids.length + ' files has moved to "' + fnames.join() + '"';
                        }
                    }
                    // Show snackbar
                    wpmfSnackbarModule.show({
                        id: 'move_to_multiple_folders',
                        content: snack_msg,
                        icon: '<span class="material-icons-outlined wpmf-snack-icon"> snippet_folder </span>',
                    });

                    if (response.folders_count.length) {
                        $.each(response.folders_count, function (i, folders_count) {
                            var folder_count = folders_count.split('-');
                            wpmfFoldersModule.categories[folder_count[0]].files_count = parseInt(folder_count[1]);
                        });

                        wpmfFoldersModule.trigger('foldersSelection', wpmfFoldersModule.last_selected_folder);
                    }

                    if (type === 'multiple') {
                        $('.mode-select .select-mode-toggle-button').click();
                    }

                    wpmfFoldersModule.reloadAttachments();
                    // Reload the folders to update
                    wpmfFoldersModule.renderFolders();
                }
            });
        }
    };

    // Let's initialize WPMF folder tree features
    $(document).ready(function () {
        // only run in list view and grid view in upload.php page
        if (typeof wp === "undefined") {
            return;
        }

        if ((wpmf.vars.wpmf_pagenow === 'upload.php' && !wpmfFoldersModule.page_type) || typeof wp.media === "undefined") {
            return;
        }
        wpmfAssignModule.treeshowdialog();
        if (wpmfFoldersModule.page_type !== 'upload-list') {
            // Wait for the main wpmf module to be ready
            wpmfFoldersModule.on('ready', function ($current_frame) {
                wpmfAssignModule.initModule($current_frame);
            });
        }
    });

}(jQuery));