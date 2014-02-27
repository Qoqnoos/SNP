var photoView = function( params )
{
    var self = this;

    self.params = params;
    self.params = params;
    self.cache = new Array();
    self.direction = null;
    self.current = null;

    this.setId = function( photo_id )
    {
        if ( !photo_id ) { return false; }

        $.bbq.pushState({ "view-photo" : photo_id });
    };

    this.checkIsCached = function( photo_id )
    {
        return (self.cache[photo_id] != undefined ? true : false);
    };

    this.cachePhotoCmp = function( photo_id, $markup )
    {
        self.cache[photo_id] = $markup;
    };

    this.showPhotoCmp = function( photo_id )
    {
        self.current = photo_id;

        if ( !window.photoFB && self.params.layout != 'page' )
        {
            window.photoFB = new OW_FloatBox({ layout : 'empty', addClass:"floatbox_photo_preview" });

            window.photoFB.bind("close", function() {
                if ( history.pushState ) {
                    history.pushState("", document.title, window.location.pathname + window.location.search );
                }
                else {
                    document.location.hash = "";
                }
                window.photoFB = null;
                window.photoFBWidth = null;
                window.photoFBHeight = null;
            });
        }

        var load_current = true;

        var next_id = $(".ow_photo_nav_r").attr("rel");
        var prev_id = $(".ow_photo_nav_l").attr("rel");

        if ( self.checkIsCached(photo_id) )
        {
            self.loadCachedCmp(photo_id);
            var load_current = false;

            var cmp = self.cache[photo_id];

            switch ( self.direction )
            {
                case 'right':
                    if ( next_id && photo_id == next_id && cmp.next && !self.checkIsCached(cmp.next) )
                    {
                        self.fetchCmp(photo_id, false, false, true);
                        return;
                    }
                    break;

                case 'left':
                    if ( prev_id && photo_id == prev_id && cmp.prev && !self.checkIsCached(cmp.prev) )
                    {
                        self.fetchCmp(photo_id, true, false, false);
                        return;
                    }
                    break;
            }
        }

        var load_next = false;
        var load_prev = false;

        switch ( self.direction )
        {
            case 'right':
                load_next = next_id ? !self.checkIsCached(next_id) : false;
                break;

            case 'left':
                load_prev = prev_id ? !self.checkIsCached(prev_id) : false;
                break;

            default:
                load_next = true;
                load_prev = true;
        }

        if ( load_current || load_prev || load_next )
        {
            self.fetchCmp(photo_id, load_prev, load_current, load_next);
        }
    };

    this.showPreloader = function()
    {
        var $preload = $(".ow_photo_preload");
        $('<div class="ow_floatbox_preloader"></div>').appendTo($preload);
        $preload.addClass('floatbox_preloader_container').show();
    }

    this.prepareCmpResources = function(data)
    {
        return data;
    };

    this.fetchCmp = function(photo_id, prev, current, next)
    {
        $.ajax( {
            url : self.params.fbResponder,
            type : "POST",
            data : "photoId=" + photo_id + "&current=" + current + "&prev=" + prev + "&next=" + next,
            dataType : "json",
            success : function(data) {
                if ( data && data.result == "success" )
                {
                    if ( current && data.current ) {
                        var cmp = self.prepareCmpResources(data.current);
                        self.cachePhotoCmp(photo_id, cmp);
                        self.loadCachedCmp(photo_id);
                    }
                    if ( prev && data.prev ) {
                        var cmp = self.prepareCmpResources(data.prev);
                        self.cachePhotoCmp(data.prev.id, cmp);
                    }
                    if ( next && data.next ) {
                        var cmp = self.prepareCmpResources(data.next);
                        self.cachePhotoCmp(data.next.id, cmp);
                    }
                }
            }
        });
    };

    this.loadCachedCmp = function(photo_id, resize)
    {
        var cmp = self.cache[photo_id];
        if ( cmp )
        {
            if ( !cmp.authorized ) {
                window.photoFB.setContent($(cmp.html));
                window.photoFB.fitWindow( { width : 500, height: 300 });
            }
            else {
                self.fitSize( cmp, resize, function(newCmp) {
                    var $contentHtml = $(cmp.html);
                    if ( self.params.layout == 'page' ) {
                        $("#ow-photo-view").replaceWith($contentHtml);
                    }
                    else {
                        window.photoFB.setContent($contentHtml);
                        window.photoFB.fitWindow( { width : newCmp.width });
                    }

                    OW.bindAutoClicks($contentHtml);
                    OW.bindTips($contentHtml);

                    self.addCmpMarkup(newCmp);
                    self.bindUI(photo_id);
                });
            }
        }
        else {
            self.bindUI(photo_id);
        }

        OW.trigger('photo.photo_show', {photoId: photo_id});
    };

    this.addCmpMarkup = function( cmp )
    {
        if ( cmp.css ) { OW.addCss(cmp.css); }

        if ( cmp.cssFiles )
        {
            $.each( cmp.cssFiles, function(key, value) { OW.addCssFile(value) }  );
        }

        if ( cmp.scriptFiles )
        {
            OW.addScriptFiles(cmp.scriptFiles, function() {
                if ( cmp.onloadScript ) { OW.addScript(cmp.onloadScript); }
            });
        }
        else {
            if ( cmp.onloadScript ) { OW.addScript(cmp.onloadScript); }
        }
    }

    this.bindUI = function(photoId)
    {
        var $prevb = $(".ow_photo_nav_l");
        var $nextb = $(".ow_photo_nav_r");
        var $context = $("#ow-photo-view .ow_photo_context_action");

        $(".ow_photo_holder").hover(function() {
            $(".ow_photo_hover_info").slideDown("fast");
            $prevb.show(); $nextb.show(); $context.show();
        }, function() {
            $(".ow_photo_hover_info").slideUp("fast");
            $prevb.hide(); $nextb.hide(); $context.hide();
        });

        $(".ow_photo_holder").trigger("mouseenter");

        $prevb.on("click", function() {
            var photo_id = $(this).attr("rel");
            if ( !photo_id ) { return false; }
            self.direction = 'left';
            self.setId(photo_id);
            self.showPreloader();
        });

        $nextb.on("click", function() {
            var photo_id = $(this).attr("rel");
            if ( !photo_id ) { return false; }
            self.direction = 'right';
            self.setId(photo_id);
            self.showPreloader();
        });

        $("#btn-photo-edit").click(function() {
            var photo_id = $(this).attr("rel");
            window.edit_photo_floatbox = OW.ajaxFloatBox(
                "PHOTO_CMP_EditPhoto",
                { photoId : photo_id },
                { width : 500, iconClass : "ow_ic_edit", title : OW.getLanguageText('photo', 'tb_edit_photo'),
                    onLoad : function() {
                        var textarea = $("#photo-desc-area").get(0);
                        textarea.htmlarea();
                        textarea.htmlareaRefresh();

                        owForms['photo-edit-form'].bind("success", function(data){
                            self.cache[photo_id] = null;
                            window.edit_photo_floatbox.close();
                            self.showPhotoCmp(photo_id);
                        });
                    }
                }
            );
        });

        $("#photo-mark-featured").click(function() {
            var status = $(this).attr('rel');
            var photo_id = $(this).attr('photo-id');
            var $this = this;

            $.ajax( {
                url : self.params.ajaxResponder,
                type : 'POST',
                data : { ajaxFunc : 'ajaxSetFeaturedStatus', photoId : photo_id, status : status },
                dataType : 'json',
                success : function(data) {
                    if ( data.result == true ) {
                        var newStatus = status == 'remove_from_featured' ? 'mark_featured' : 'remove_from_featured';
                        var newLabel = status == 'remove_from_featured' ? OW.getLanguageText('photo', 'mark_featured') : OW.getLanguageText('photo', 'remove_from_featured');
                        $($this).html(newLabel);
                        $($this).attr('rel', newStatus);
                        OW.info(data.msg);
                    } else if ( data.error != undefined ) {
                        OW.warning(data.error);
                    }
                }
            });
        });

        $("#photo-delete").click(function() {
            var photo_id = $(this).attr("rel");

            if ( confirm(OW.getLanguageText('photo', 'confirm_delete')) ) {
                $.ajax( {
                    url : self.params.ajaxResponder,
                    type : 'POST',
                    data : { ajaxFunc : 'ajaxDeletePhoto', photoId : photo_id },
                    dataType : 'json',
                    success : function(data) {
                        if ( data.result == true ) {
                            OW.info(data.msg);
                            if ( data.url ) { document.location = data.url };
                        } else if ( data.error != undefined ) {
                            OW.warning(data.error);
                        }
                    }
                });
            }
            else {
                return false;
            }
        });

        $("#btn-photo-flag").click(function() {
            var photo_id = $(this).attr("rel");
            var url = $(this).attr("url");
            var photoDesc = $("#photo-description").html();

            if ( photoDesc == null )
            {
                photoDesc = photo_id;
            }

            OW.flagContent("photo", photo_id, photoDesc, url, "photo+flags");
        });

        OW.bind('base.comment_add', function(e) {
            if ( e.entityType == "photo_comments" ) {
                if ( e.entityId && self.checkIsCached(e.entityId) ) { self.cache[e.entityId] = null; }
            }

            OW.unbind("base.comment_add");
        });

        OW.bind('base.comment_delete', function(e) {
            if ( e.entityType == "photo_comments" ) {
                if ( e.entityId && self.checkIsCached(e.entityId) ) { self.cache[e.entityId] = null; }
            }

            OW.unbind("base.comment_delete");
        });

        OW.bind('base.rate_update', function(e) {
            if ( e.entityType == "photo_rates" ) {
                if ( e.entityId && self.checkIsCached(e.entityId) ) { self.cache[e.entityId] = null; }
            }

            OW.unbind("base.rate_update");
        });

        OW.trigger('photo.photo_show_complete', {photoId: photoId});
    };

    this.fitSize = function(cmp, resize, callback) {
        var $prerendered = $('<div id="fb-prerendered"></div>').html(cmp.html);

        $("body").append($prerendered);
        $prerendered.css({"position" : "absolute", "left" : "-2000px"});
        var img = $prerendered.find("img.ow_photo_img");

        img.onImageLoad(function() {
            var $stage = $prerendered.find(".ow_photo_stage");

            var topMargin = parseInt($(".floatbox_container").css("margin-top"));
            var screenW = self.params.layout == 'page' ? $("#ow-photo-view").width() : $(window).width();
            var screenH = $(window).height();

            var spacing = 30;
            var minW = screenW < 600 ? (screenW - 2 * spacing) : 600;
            var minH = screenH < 400 ? (screenH - 2 * spacing) : 400;
            var maxW = screenW;
            if ( self.params.layout != 'page' )
                maxW = maxW - 2 * spacing;
            var margin = topMargin > screenH - 2 * spacing ? topMargin : 0;

            var maxH = screenH - ( 2 * spacing - margin > screenH ? 2 * spacing - margin : spacing );

            $(this).css({"width": "auto", "height": "auto"});
            var imageW = $(this).width();
            var imageH = $(this).height();

            var dim = self.getScaledDimensions(minW, minH, maxW, maxH, imageW, imageH, resize);

            $(this).width(dim[0]);
            $(this).height(dim[1]);

            $stage.width(dim[2]);
            $stage.height(dim[3]);

            $(this).css({"top" : dim[4], "left" : dim[5]});

            cmp.html = $prerendered.html();
            cmp.width = dim[2];
            $prerendered.remove();

            callback(cmp);
        });

        return cmp;
    };

    this.getScaledDimensions = function(minW, minH, maxW, maxH, imgW, imgH, resize)
    {
        var xRatio = imgH / imgW;
        var yRatio = imgW / imgH;

        if ( imgW > maxW )
        {
            var width = maxW;
            var height = maxW * xRatio;

            if ( height > maxH ) {
                height = maxH;
                width = maxH * yRatio;
            }
        }
        else if ( imgH > maxH ) {
            var height = maxH;
            var width = maxH * yRatio;
        }
        else
        {
            var width = imgW;
            var height = imgH;
        }

        if ( resize )
        {
            window.photoFBWidth = width;
            window.photoFBHeight = height;
        }

        var stageW = self.params.layout != 'page'
            ? (window.photoFBWidth > width ? window.photoFBWidth : (imgW > minW ? width : minW))
            : $("#ow-photo-view").width();

        var stageH = window.photoFBHeight > height ? window.photoFBHeight : (imgH > minH ? height : minH);

        if ( window.photoFBWidth ) {
            if ( stageW > window.photoFBWidth ) {
                window.photoFBWidth = stageW;
            }
        } else {
            window.photoFBWidth = stageW;
        }

        if ( window.photoFBHeight ) {
            if ( stageH > window.photoFBHeight ) {
                window.photoFBHeight = stageH;
            }
        } else {
            window.photoFBHeight = stageH;
        }

        var top = (stageH - height) / 2;
        var left = (stageW - width) / 2;

        return[Math.round(width), Math.round(height),
            Math.round(stageW), Math.round(stageH),
            Math.round(top), Math.round(left)];
    }

    $(document).bind('keydown', function(e){
        var target = e ? e.target : window.event.srcElement;
        if ( $(target).is('input') || $(target).is('textarea') ) { return; }

        var code = e.which;
        switch ( code ) {
            case 37:
                if ( !window.photoFB && self.params.layout != 'page' ) { return false; }
                e.preventDefault();
                var photo_id = $(".ow_photo_nav_l").attr("rel");
                if ( !photo_id ) { return false; }
                self.setId(photo_id);
                break;

            case 13:
            case 32:
            case 39:
                if ( !window.photoFB && self.params.layout != 'page' ) { return false; }
                e.preventDefault();
                var photo_id = $(".ow_photo_nav_r").attr("rel");
                if ( !photo_id ) { return false; }
                self.setId(photo_id);
                break;
        }
    });

    $(window).resize(function() {
        if ( this.resizeTimeout )
            clearTimeout(this.resizeTimeout);

        this.resizeTimeout = setTimeout(function() {
            $(this).trigger('resizeComplete');
        }, 500);
    });

    $(window).bind('resizeComplete', function() {
        if ( window.photoFB )
            self.loadCachedCmp(self.current, true);
    });
}


PHOTO = (function() {

    var _photoView;
    var onHashChange;

    onHashChange = function() {
        var photoId = $.bbq.getState("view-photo");
        if ( photoId ) {
            if ( window.photoFBLoading )
                return;

            _photoView.showPhotoCmp(photoId);
        }
    };

    return {
        init: function ( settings ) {
            if ( !window.photoViewObj ) {
                window.photoViewObj = new photoView(settings);
            }

            _photoView = window.photoViewObj;

            if ( !window.photoPollingEnabled ) {
                $(window).bind( "hashchange", onHashChange);
                window.photoPollingEnabled = true;
            }
        },

        showPhoto: function( photoId ) {
            _photoView.setId(photoId);
        }
    };
})();