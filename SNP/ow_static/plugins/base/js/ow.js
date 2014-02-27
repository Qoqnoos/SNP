var OwUtils = function(){
    var langs = {};
    var messageTime = 3000;
    var $messageCont = $('<div class="ow_message_cont"></div>');
    var events = {};

    this.registry = {};

    $(function(){
    	$messageCont.appendTo(document.body);
    });

    this.message = function( message, type, paramTime ){
        var $messageNode = $('<div class="ow_message_node '+type+'" style="display:none;"><div><div><a class="close_button" href="javascript://" onclick="$(this).closest(\'.ow_message_node\').slideUp(200, function(){$(this).remove();})"></a>'+message+'</div></div></div>').appendTo($messageCont);
        if( paramTime == undefined ){
            paramTime = messageTime;
        }

        $messageNode.fadeIn(1000,
            function(){
                window.setTimeout(
                    function(){
                        $messageNode.fadeOut(1000,
                            function() {
                                $messageNode.remove();
                            }
                    );
                    }, paramTime
                );
            }
        );
    }

    this.error = function( message ){
    	this.message(message, 'error');
    };

    this.warning = function( message ){
    	this.message(message, 'warning');
    };

    this.info = function( message ){
    	this.message(message, 'info');
    };

    this.flagContent = function( entity, id, title, href, lang )
    {
        OW.registry['flag-panel-fb'] = OW.ajaxFloatBox("BASE_CMP_Flag", [entity, id, title, href, lang], {
            width: 315,
            title: OW.getLanguageText('base', 'flag_as')
        });
    };

    this.addScriptFiles = function( urlList, callback ){
        var scripts = $('script');

        //TODO: Sardar require once check

/*        var docScripts = [];

        for( var i = 0; i < scripts.length; i++  ){
            docScripts[i] = $(scripts[i]).attr('src');
        }*/

        if( urlList && urlList.length > 0 ){
            var recursiveInclude = function(urlList, i){

                if( (i+1) == urlList.length )
                {
                    $.getScript(urlList[i], callback);
                    return;
                }

                $.getScript(urlList[i], function(){recursiveInclude(urlList, ++i);});
            }

            recursiveInclude(urlList, 0);
        }else{
            callback.apply(this);
        }



    };

    /*this.addScriptFile = function( url, callback ){
        var scripts = $('script');

        for( var i = 0; i < scripts.length; i++  ){
            if( $(scripts[i]).attr('src') == $.trim(url) ){
                return;
            }
        }

        $.getScript(url, callback);

        //$('head').append( $('<script type="text/javascript" src="'+url.trim()+'"></script>') );
    };*/


    this.addScript = function( script, scope, context )
    {
    	if (!script)
    	{
            return;
    	}

        context = context || window;
        scope = scope || window;

        Function('_scope', script).call(context, scope);
    },

    this.addCssFile = function( url )
    {
       $('head').append($('<link type="text/css" rel="stylesheet" href="'+$.trim(url)+'" />'));
    },

    this.addCss = function( css ){
        $('head').append($('<style type="text/css">'+css+'</style>'));
    }

    this.getLanguageText = function(prefix, key, assignedVars)
    {
        if ( langs[prefix] === undefined ) {
                return prefix + '+' + key;
        }

        if ( langs[prefix][key] === undefined ) {
                return prefix + '+' + key;
        }

        var langValue = langs[prefix][key];

        if ( assignedVars ) {
                for( varName in assignedVars ) {
                        langValue = langValue.replace('{$'+varName+'}', assignedVars[varName]);
                }
        }

        return langValue;
    };

    this.registerLanguageKey = function(prefix, key, value)
    {
            if ( langs[prefix] === undefined ) {
                    langs[prefix] = {};
            }

            langs[prefix][key] = value;
    };

    this.inProgressNode = function(node)
    {
    	return $(node).inprogress();
    }

    this.activateNode = function(node)
    {
    	return $(node).activate();
    };

    this.showUsers = function(userIds, title)
    {
    	OW.Users.showUsers(userIds, title);
    },

    this.getActiveFloatBox = function getFloatBox()
    {
    	if ( typeof window.OWActiveFloatBox == 'undefined' )
    	{
    		return false;
    	}

    	return window.OWActiveFloatBox;
    },

    /**
     * Loads a component
     *
     * Examples:
     * 1:
     * OW.loadComponent(cmpClass, '.place_to_node');
     *
     * 2:
     * OW.loadComponent(cmpClass, function(){
     *     //onReady event
     *     //Add the html somewhere
     * });
     *
     * 3:
     * OW.loadComponent(cmpClass, [ p1, p2, ... ], '.place_to_node');
     *
     * 4:
     * OW.loadComponent(cmpClass, [ p1, p2, ... ], function(html){
     *     //onReady event
     *     //Add the html somewhere
     * });
     *
     * 5:
     * OW.loadComponent(cmpClass, [ p1, p2, ... ],
     * {
     *     onLoad: function(){},
     *     onError: function(){},
     *     onComplete: function(){},
     *     onReady: function( html )
     *     {
     *        //Add the html somewhere
     *     }
     * });
     *
     * @param string cmpClass
     *
     * Cmp class params or targetNode selector or targetNod HtmlElement or ready callback
     * @param array|string|HTMLElement|jQuery|function p1
     *
     * Options or targetNode selector or targetNod HtmlElement or ready callback
     * @param object|string|HTMLElement|jQuery|function p2
     */
    this.loadComponent = function( cmpClass, p1, p2 )
    {
        function isNode( node )
        {
            return typeof node === 'string' || node.jquery || node.nodeType
        }

        var params = [], options = {};

        if ( isNode(p2) )
        {
            options.place = $(p2);
        }
        else if ( $.isPlainObject(p2) )
        {
            options = p2;
        }
        else if ( $.isFunction(p2) )
        {
            options.onReady = p2;
        }

        if ( isNode(p1) )
        {
            options.place = $(p1);
        }
        else if ( $.isArray(p1) || $.isPlainObject(p1) )
        {
            params = p1;
        }
        else if ( $.isFunction(p1) )
        {
            options.onReady = p1;
        }

    	options = $.extend({}, {
            place: false,
            scope: window,
            context: window,
            addClass: '',
            onLoad: function() {},
            onReady: function( r ) {},
            onError: function( r ) {},
            onComplete: function( r ) {}
    	}, options);

        var rsp = this.ajaxComponentLoaderRsp,
            jsonParams = JSON.stringify(params),
            $preloader = false;

        if ( options.place )
        {
            $preloader = $('<div class="ow_ajaxloader_preloader ow_preloader_content ' + options.addClass + '"></div>');
            $(options.place).html($preloader);
        }

        var ajaxOptions = {
            url: rsp + '?cmpClass=' + cmpClass + '&r=' + Math.random(),
            dataType: 'json',
            type: 'POST',
            data: {params: jsonParams},
            error: function(r)
            {
                options.onError(r);
            },
            complete: function(r)
            {
                options.onComplete(r);
            },
            success: function(markup)
            {
                var contentHtml = markup.content, $contentHtml = $(contentHtml);

                if ( !$contentHtml.length )
                {
                    contentHtml = '<span>' + contentHtml + '</span>';
                    $contentHtml = $(contentHtml)
                }

                if ( $preloader )
                {
                    $preloader.replaceWith($contentHtml);
                }

                options.onReady($contentHtml);

                OW.bindAutoClicks($contentHtml);
                OW.bindTips($contentHtml);

                if (markup.styleSheets)
                {
                    $.each(markup.styleSheets, function(i, o)
                    {
                        OW.addCssFile(o);
                    });
                }

                if (markup.styleDeclarations)
                {
                    OW.addCss(markup.styleDeclarations);
                }

                if (markup.beforeIncludes)
                {
                    OW.addScript(markup.beforeIncludes, options.scope, options.context);
                }

                if (markup.scriptFiles)
                {

                    OW.addScriptFiles(markup.scriptFiles, function()
                    {
                        if (markup.onloadScript)
                        {
                            OW.addScript(markup.onloadScript, options.scope, options.context);
                            options.onLoad();
                        }
                    });
                }
                else
                {
                    if (markup.onloadScript)
                    {
                        OW.addScript(markup.onloadScript, options.scope, options.context);
                    }
                    options.onLoad();
                }
            }
	};

    	$.ajax(ajaxOptions);
    },

    this.ajaxFloatBox = function(cmpClass, params, options)
    {
    	params = params || [];

    	options = options || {};
    	options = $.extend({}, {
            title: '',
            width: false,
            height: false,
            top: false,
            left: false,
            iconClass: false,
            addClass: false,
            layout: 'default',
            $preloader: null,
            scope: {},
            context: null,
            onLoad: function(){},
            onReady: function(){},
            onError: function(){},
            onComplete: function(){}
    	}, options);

    	var floatBox = new OW_FloatBox({
            $title: options.title,
            width: options.width,
            height: options.height,
            top: options.top,
            left: options.left,
            icon_class: options.iconClass,
            addClass: options.addClass,
            layout: options.layout,
            $preloader: options.$preloader
        });

        options.scope = $.extend({floatBox: floatBox}, options.scope);
        options.context = options.context || floatBox;

        this.loadComponent(cmpClass, params,
        {
            scope: options.scope,
            context: options.context,
            onLoad: options.onLoad,
            onError: options.onError,
            onComplete: options.onComplete,
            onReady: function( r )
            {
                floatBox.setContent(r);

                if ( $.isFunction(options.onReady) )
            	{
                    options.onReady.call(this, r);
            	}
            }
        })

    	return floatBox;
    };

    this.bind = function(type, func)
	{
		if (events[type] == undefined)
		{
			events[type] = [];
		}

		events[type].push(func);

	};

	this.trigger = function(type, params, applyObject)
	{
		if (events[type] == undefined) {
			return false;
		}

		applyObject = applyObject || this;
		params = params || [];

		if ( !$.isArray(params) )
		{
			params = [params];
		}

		for (var i = 0, func; func = events[type][i]; i++)
		{
			if (func.apply(applyObject, params) === false)
			{
				return false;
			}
		}

		return true;
	};

	this.unbind = function( type )
	{
		if (events[type] == undefined) {
			return false;
		}

		events[type] = [];
	};

	this.editLanguageKey = function( prefix, key, success )
	{
        var fb = OW.ajaxFloatBox("BASE_CMP_LanguageValueEdit", [prefix, key, true], {width: 520, title: this.getLanguageText('admin', 'edit_language')});

        OW.bind("admin.language_key_edit_success", function( e ) {
            fb.close();
            OW.unbind("admin.language_key_edit_success");
            success(e);
        });
	};



    this.bindAutoClicks = function(context){
    	var autoClicks;

    	if ( context )
    	{
    		autoClicks = $('.form_auto_click', context);
    	}
    	else
    	{
    		autoClicks = $('.form_auto_click');
    	}

        $.each(autoClicks, function(i,o){
            var context = $(o);
            $('textarea.invitation', context)
            .bind('focus.auto_click', {context:context},
                function(e){
                    $('.ow_submit_auto_click', e.data.context).show();
                    $(this).unbind('focus.auto_click')
                }
            );/*
            .bind('keyup.auto_click',
                function(){
                    if( $(this).val() != '' ){
                        $(this).unbind('focus.auto_click').unbind('keyup.auto_click').unbind('mouseup.auto_click').unbind('blur.auto_click');
                    }
                }
            )
            .bind('mouseup.auto_click',
                function(){
                    if( $(this).val() != '' ){
                        $(this).unbind('focus.auto_click').unbind('keyup.auto_click').unbind('mouseup.auto_click').unbind('blur.auto_click');
                    }
                }
            )
            .bind('blur.auto_click', {context:context},
                function(e){
                    if( $(this).hasClass('invitation') ){
                        $('.ow_submit_auto_click', e.data.context).hide();
                    }
                }
            );*/
        });
    };

    this.initWidgetMenu = function( items ){
        var $toolbarCont = null;
        var $contex = null;
        var condIds = [];
        var linkIds = [];
        $.each( items, function(key, value){
                if( $toolbarCont === null ){
                    $contex = $('#'+value['contId']).closest('.ow_box, .ow_box_empty');
                    $toolbarCont = $('.ow_box_toolbar_cont', $contex);
                }
                condIds.push('#'+value['contId']);
                linkIds.push('#'+value['id']);
            }
        );

        var contIdSelector = $(condIds.join(','));
        var linkIdSelector = $(linkIds.join(','));

        $.each( items, function(key, value){
                $('#'+value['id']).bind('click', {value:value},
                    function(e){
                        contIdSelector.hide();
                        $('#'+e.data.value.contId).show();
                        linkIdSelector.removeClass('active');
                        $(this).addClass('active');

                        if( e.data.value.toolbarId != undefined ){
                            if( e.data.value.toolbarId ){
                                if( $toolbarCont.length === 0 ){
                                    $toolbarCont = $('<div class="ow_box_toolbar_cont"></div>');
                                    $contex.append($toolbarCont);
                                }
                                $toolbarCont.html($('#'+e.data.value.toolbarId).html());
                            }
                            else{
                                if( $toolbarCont.length !== 0 ){
                                    $toolbarCont.remove();
                                    $toolbarCont = [];
                                }
                            }
                        }
                    }
                );
            }
        );
    };

    this.showTip = function( $el, params ){
        params = params || {};
        params = $.extend({side:'top', show:null, width:null, timeout:0, offset:5, hideEvent: null}, params);

        var showTipN = function(){

            var $rootEl = $el.data('owTip');
            var coords = $el.offset();

            switch( params.side )
            {
                case 'top':
                    var left = coords.left + $el.outerWidth()/2 - $rootEl.outerWidth()/2;
                    var top = coords.top - $rootEl.outerHeight() - params.offset;
                    break;

                case 'bot':
                    var left = coords.left + $el.outerWidth()/2 - $rootEl.outerWidth()/2;
                    var top = coords.top + $el.outerHeight() + params.offset;
                    break;

                case 'right':
                    var left = coords.left + $el.outerWidth() + params.offset;
                    var top = coords.top + $el.outerHeight()/2 - $rootEl.outerHeight()/2;
                    break;

                case 'left':
                    var left = coords.left - $rootEl.outerWidth() - params.offset;
                    var top = coords.top + $el.outerHeight()/2 - $rootEl.outerHeight()/2;
                    break;

                 default:
                     return;
            }

            $rootEl.css({left:left, top:top});

            var tod = setTimeout( function(){
                $el.data('owTip').show( 1,
                    function(){
                        if( params.hideEvent ){
                            $el.bind(params.hideEvent, function(){OW.hideTip($el)});
                        }
                        $el.data('owTipStatus', true);
                        if( $el.data('owTipHide') == true ){
                            OW.hideTip($el);
                        }
                    }
                );
            }, params.timeout);
            $el.data('tod', tod)
        }

        if( $el.data('owTip') ){
            if( $el.data('owTipStatus') == true ){
                return;
            }
            showTipN();
            return;
        }

        var showContent;

        if( params.show != null ){
            showContent = ( typeof(params.show) == 'string' ? params.show : params.show.html() );
        }
        else{
            if( !$el.attr('title') ){
                return;
            }

            showContent = '<span class="ow_tip_title">'+$el.attr('title')+'</span>';
        }

        var $rootEl = $('<div class="ow_tip ow_tip_'+params.side+'"></div>').css({display:'none'}).append($('<div class="ow_tip_arrow"><span></span></div><div class="ow_tip_box">'+ showContent +'</div>'));
        if( params.width != null ){
            $rootEl.css({width:params.width});
        }
        $('body').append($rootEl);

        $el.removeAttr('title');
        $el.data('owTip', $rootEl);
        showTipN();
    };

    this.hideTip = function( $el ){
        if( $el.data('tod') ){
            clearTimeout($el.data('tod'));
        }

        if( $el.data('owTip') && $el.data('owTipStatus') == true ){
            $el.data('owTip').hide();
            $el.data('owTipStatus', false);
            $el.data('owTipHide', false);
        }
    };

    this.bindTips = function( $context ){
      $('*[title]', $context).each( function(i, o){ $(o).hover(function(){OW.showTip($(this), {timeout:200})}, function(){ OW.hideTip($(this))})});
    };

    this.resizeImg = function($context, params){
        if( !params.width ){
            return;
        }

        $( 'img', $context ).each(function(){
            $(this).load(
                function(){
                    if( $(this).data('imgResized') != true){
                        var $fakeImg = $(this).clone();
                        $fakeImg.css({width:'auto',height:'auto',visibility:'hidden',position:'absolute',left:'-9999px'}).removeAttr('width').removeAttr('height');
                        $(document.body).append ($fakeImg);
                        var self = this;
                        $fakeImg.load(function(){
                            var width = $(this).width();
                            if( width < params.width  ){
                                $(self).css({width:'auto', height:'auto'});
                            }
                            else if( $(self).width() >= params.width ){
                                $(self).css({width:params.width, height:'auto'});
                            }
                            $(self).data('imgResized', true);
                            $(this).remove();
                        });
                    }
                }
            );
        });
    };

    this.showImageInFloatBox = function( src )
    {
        var floatBox = new OW_FloatBox({layout: 'empty'});
        var $fakeImageC = $('<div></div>');
        var $fakeImg = $('<img src="'+src+'" />');
        $fakeImageC.append($fakeImg);
        $fakeImageC.css({visibility:'hidden',position:'absolute',left:'-9999px'});
        $(document.body).append ($fakeImageC);

        $fakeImg.load(function(){
            var width = $fakeImg.width();
            var height = $fakeImg.height();

            if( width > 340 || height > 220 ){

                if( width > 800 )
                {
                    $fakeImg.css({width:'800px', height:'auto'});
                    width = 800;
                }
                else if( $fakeImg.height > 600 )
                {
                    height = 600;
                    $fakeImg.css({height:'600px', width:'auto'});
                }

                floatBox.setContent($fakeImg);
            }
            else{
                floatBox.setContent($fakeImg);
            }

            floatBox.fitWindow({
                "width": width,
                "height": height
            });

            floatBox.bind('close', function(){
                $fakeImageC.remove();
            });
        });
    }
}


//Enable / Disable node
jQuery.fn.extend({
	inprogress: function() {
		this.each(function()
		{
			var $this = jQuery(this).addClass('ow_inprogress');
			this.disabled = true;

			if ( this.tagName != 'INPUT' && this.tagName != 'TEXTAREA' && this.tagName != 'SELECT' )
			{
				this.jQuery_disabled_clone = $this.clone().removeAttr('id').removeAttr('onclick').get(0);

				$this.hide()
					.bind('unload', function(){
						$this.activate();
					})
					.after(this.jQuery_disabled_clone);
			}
		});

		return this;
	},

	activate: function() {
		this.each(function()
		{
			var $this = jQuery(this).removeClass('ow_inprogress');
			this.disabled = false;

			if ( this.jQuery_disabled_clone )
			{
				jQuery(this.jQuery_disabled_clone).remove();
				this.jQuery_disabled_clone = null;

				jQuery(this)
					.unbind('unload', function(){
						$this.activate();
					})
					.show();
			}
		});

		return this;
	}
});

window.OW = new OwUtils();

function lg(o){
	console.log(o);
}

$( //8aa: resize fullsize images to fit to it's parendt width
	function (){
		if(typeof($) == 'undefined') return;

		$('.fullsize-image').hide();
		var node = $('.fullsize-image')[0];

		while( node = $(node).parent()[0]){

			if( node.tagName != 'DIV'){
				continue;
			}

			if($('.fullsize-image').width() > parseInt($(node).innerWidth()))
				$('.fullsize-image').width( (parseInt($(node).innerWidth()) - 10) + 'px' );

			$('.fullsize-image').show();
			break;
		}

	}
);


/**
 * Float box constructor.
 *
 * @param string|jQuery $title
 * @param string|jQuery $contents
 * @param jQuery $controls
 * @param object position {top, left} = center
 * @param integer width = auto
 * @param integer height = auto
 */
function OW_FloatBox(options)
{
    var fl_box = this;
    var fb_class;
    this.parentBox = OW.getActiveFloatBox();
    this.options = options;
    this.verion = 2;

    this.events = {close: [], show: []};

    if (typeof document.body.style.maxHeight === 'undefined') { //if IE 6
            jQuery('body').css({height: '100%', width: '100%'});
            jQuery('html').css('overflow', 'hidden');
            if (document.getElementById('floatbox_HideSelect') === null)
            { //iframe to hide select elements in ie6
                jQuery('body').append('<iframe id="floatbox_HideSelect"></iframe><div id="floatbox_overlay"></div>');
                fb_class = OW_FloatBox.detectMacXFF() ? 'floatbox_overlayMacFFBGHack' : 'floatbox_overlayBG';
                jQuery('#floatbox_overlay').addClass(fb_class);
            }
    }
    else { //all others
        if (document.getElementById('floatbox_overlay') === null)
        {
            jQuery('body').append('<div id="floatbox_overlay"></div>');
            fb_class = OW_FloatBox.detectMacXFF() ? 'floatbox_overlayMacFFBGHack' : 'floatbox_overlayBG';
            jQuery('#floatbox_overlay').addClass(fb_class).click(function()
            {
                fl_box.close();
            });
        }
    }

    options.layout = options.layout || 'default';

    jQuery('body').addClass('floatbox_nooverflow');

    var activeCanvas = jQuery('.floatbox_canvas_active');
    var fbContext = jQuery('#floatbox_prototype').find('.' + options.layout);

    this.$canvas = jQuery('.floatbox_canvas', fbContext).clone().appendTo(document.body);
    this.$preloader = jQuery('.floatbox_preloader_container', this.$canvas);

    if ( options.addClass )
    {
        this.$canvas.addClass(options.addClass);
    }

    activeCanvas.removeClass('floatbox_canvas_active');
    this.$canvas.addClass('floatbox_canvas_active');

    if (this.parentBox)
    {
        this.$canvas.addClass('floatbox_canvas_sub');
        this.parentBox.bind('close', function()
        {
            fl_box.close();
        });
    }

    this.$canvas.click(function(e)
    {
        if ( $(e.target).is(this) )
        {
            fl_box.close();
        }
    });

    this.$container = jQuery('.floatbox_container', this.$canvas);

    if ( options.$title )
    {
        if (typeof options.$title == 'string')
        {
            options.$title = jQuery('<span>'+options.$title+'</span>');
        }
        else
        {
            this.$title_parent = options.$title.parent();
        }

        this.$header = jQuery('.floatbox_header', this.$container);

        var $fbTitle = jQuery('.floatbox_cap', this.$header)
            .find('.floatbox_title')
                .append(options.$title);
    }

    /*if (typeof options.icon_class == 'string')
    {
    	$fbTitle.addClass(options.icon_class);
    }*/

    this.$body = jQuery('.floatbox_body', this.$container);
    this.$bottom = jQuery('.floatbox_bottom', this.$container);

    this.showPreloader(options.$preloader || null);

    if (options.$controls)
    {
        if (typeof options.$controls == 'string')
        {
            options.$controls = jQuery('<span>'+options.$controls+'</span>');
        }
        else
        {
            this.$controls_parent = options.$controls.parent();
        }

        this.$bottom.append(options.$controls);
    }


    if (options.width)
            this.$container.css("width", options.width);
    if (options.height)
            this.$body.css("height", options.height);

    jQuery('.close', this.$header)
        .one('click', function()
        {
            fl_box.close();
            return false;
        });

    this.esc_listener =
    function(event) {
            if (event.keyCode == 27) {
                    fl_box.close();
                    return false;
            }
            return true;
    }

    jQuery(document).bind('keydown', this.esc_listener);

    if (options.left)
    {
        this.$container.css('margin-left', options.left);
    }

    if (options.top)
    {
        this.$container.css('margin-top', options.top);
    }

    if ( options.$contents )
    {
        this.setContent(options.$contents);
    }

    window.OWActiveFloatBox = this;
}

OW_FloatBox.version = 3;
OW_FloatBox.detectMacXFF = function()
{
    var userAgent = navigator.userAgent.toLowerCase();
    return (userAgent.indexOf('mac') != -1 && userAgent.indexOf('firefox') != -1);
}

OW_FloatBox.prototype = {

    fitWindow: function( params )
    {
        params = params || {};
        params = $.extend({
            "width": null,
            "height": null,
            "top": null,
            "left": null
        }, params);

        var css = {};

        if ( params.width )
        {
            this.options.width = params.width;
            css.width = params.width;
        }

        if ( params.height )
        {
            this.options.height = params.height;
            css.height = params.height;
        }

        if ( params.top )
        {
            this.options.top = params.top;
            css.marginTop = params.top;
        }

        if ( params.left )
        {
            this.options.left = params.left;
            css.marginLeft = params.left;
        }

        this.$container.css(css);
    },

    setContent: function( $contents )
    {
        var self = this;

        if (typeof $contents == 'string')
        {
            var $contentsNode = jQuery($contents);

            if ( !$contentsNode.length )
            {
                $contentsNode = jQuery('<span>' + $contents + '</span>');
            }

            $contents = jQuery($contentsNode);
        }
        else
        {
            this.$contents_parent = $contents.parent();
        }

        var width, fakeNode;

        if ( !this.options.width )
        {
            fakeNode = $('<div></div>').insertAfter(this.$body);
            fakeNode.attr('class', this.$body.attr('class'));
            fakeNode.css({position: 'absolute',top: -2000, visibility: 'hidden'});
            fakeNode.append($contents);
            width = fakeNode.outerWidth();
        }
        else
        {
            width = this.options.width;
        }

        this.$body.empty().append($contents);

        if ( fakeNode )
        {
            fakeNode.remove();
        }

        this.hidePreloader();

        this.fitWindow({width: width});

        window.setTimeout(function(){
            self.trigger('show');
        });
    },

    // OLD
    /*showPreloader: function( preloaderContent )
    {
        var css = {};

        if ( preloaderContent )
        {
            if ( typeof preloaderContent == 'string' )
            {
                preloaderContent = jQuery('<div class="floatbox_string_preloader"><span>' + preloaderContent + '</span></div>');
            }

            this.$preloader.html(preloaderContent);
        }

        this.$canvas.addClass('ow_floatbox_loading');


        this.$preloader.css('visibility', 'hidden');

        css.marginTop = ( jQuery(window).height() / 2 ) - ( this.$preloader.height() / 2 + 100);
        css.visibility = 'visible';

        this.$preloader.css(css);
    },*/

    showPreloader: function( preloaderContent )
    {
        if ( preloaderContent )
        {
            if ( typeof preloaderContent == 'string' )
            {
                preloaderContent = jQuery('<div class="floatbox_string_preloader"><span>' + preloaderContent + '</span></div>');
            }

            this.$preloader.html(preloaderContent);
        }

        this.$canvas.addClass('ow_floatbox_loading');
    },

    hidePreloader: function()
    {
        this.$canvas.removeClass('ow_floatbox_loading');
    },

    close: function()
    {
        if (this.trigger('close') === false) {
                return false;
        }

        jQuery(document).unbind('keydown', this.esc_listener);

        if (this.$title_parent && this.$title_parent.length)
        {
            this.$title_parent.append(
                jQuery('.floatbox_title', this.$header).children()
            );
        }

        if (this.$contents_parent && this.$contents_parent.length)
        {
            this.$contents_parent.append(this.$body.children());
        }

        if (this.$controls_parent && this.$controls_parent.length)
        {
            this.$controls_parent.append(this.$bottom.children());
        }

        this.$canvas.remove();

        if (jQuery('.floatbox_canvas:visible').length === 0)
        {
            jQuery('html, body').removeClass('floatbox_nooverflow');
            jQuery('#floatbox_overlay, #floatbox_HideSelect').remove();
        }

        window.OWActiveFloatBox = this.parentBox;

        return true;
    },

    bind: function(type, func)
    {
            if (this.events[type] == undefined) {
                    throw 'form error: unknown event type "'+type+'"';
            }

            this.events[type].push(func);

    },

    trigger: function(type, params)
    {
            if (this.events[type] == undefined) {
                    throw 'form error: unknown event type "'+type+'"';
            }

            params = params || [];

            for (var i = 0, func; func = this.events[type][i]; i++) {
                    if (func.apply(this, params) === false) {
                            return false;
                    }
            }

            return true;
    }
}


/* OW Forms */

var OwFormElement = function( id, name ){
    this.id = id;
    this.name = name;
    this.input = document.getElementById(id);
    this.validators = [];
}

OwFormElement.prototype = {

    validate: function(){

        var error = false;

        try{
            for( var i = 0; i < this.validators.length; i++ ){
                this.validators[i].validate(this.getValue());
            }
        }catch (e) {
            error = true;
            this.showError(e);
        }

        if( error ){
            throw e;
        }
    },

    addValidator: function( validator ){
        this.validators.push(validator);
    },

    getValue: function(){
        return $(this.input).val();
    },

    setValue: function( value ){
        $(this.input).val(value);
    },

    resetValue: function(){
        $(this.input).val('');
    },

    showError: function( errorMessage ){
        $('#'+this.id+'_error').append(errorMessage).fadeIn(50);
    },

    removeErrors: function(){
        $('#'+this.id+'_error').empty().fadeOut(50);
    }
}

var OwForm = function( params ){
    $.extend(this, params);
    this.form = document.getElementById(this.id);
    this.elements = {};
    var actionUrl = $(this.form).attr('action');
    this.actionUrl = ( !actionUrl ? location.href : actionUrl );
    this.showErrors = true;
    this.events = {
        submit:[],
        success:[]
    }
};

OwForm.prototype = {

    addElement: function( element ){
        this.elements[element.name] = element;
    },

    getElement: function( name ){
        if( this.elements[name] === undefined ){
            return null;
        }

        return this.elements[name];
    },

    validate: function(){

        var error = false;
        var element = null;
        var errorMessage;

        $.each( this.elements,
            function(index, data){
                try{
                    data.validate();
                }catch (e){
                    error = true;

                    if( element == null ){
                        element = data;
                        errorMessage = e;
                    }
                }
            }
            );

        if( error ){
            element.input.focus();

            if( this.validateErrorMessage ){
                throw this.validateErrorMessage;
            }else{
                throw errorMessage;
            }
        }
    },

    bind: function( event, fnc ){
        this.events[event].push(fnc);
    },

    sucess: function( fnc ){
        this.bind('success', fnc);
    },

    submit: function( fnc ){
        this.bind('submit', fnc);
    },

    trigger: function( event, data ){
        if( this.events[event] == undefined || this.events[event].length == 0 ){
            return;
        }

        for( var i = 0; i < this.events[event].length; i++ ){
            this.events[event][i].apply(this.form, [data]);
        }
    },

    getValues: function(){

        var values = {};

        $.each(this.elements,
            function( index, data ){
                values[data.name] = data.getValue();
            }
            );

        return values;
    },

    setValues: function( values ){

        var self = this;

        $.each( values,
            function( index, data ){
                if(self.elements[index]){
                    self.elements[index].setValue(data);
                }
            }
            );
    },

    resetForm: function(){
        $.each( this.elements,
            function( index, data ){
                data.resetValue();
            }
            );
    },

    removeErrors: function(){

        $.each( this.elements,
            function( index, data ){
                data.removeErrors();
            }
            );
    },

    submitForm: function(){

        var self = this;

        this.removeErrors();

        try{
            this.validate();
        }catch(e){
            if( this.showErrors ){
                OW.error(e);
            }
            return false;
        }

        var dataToSend = this.getValues();
        self.trigger('submit', dataToSend);

        var buttons = $('input[type=button], input[type=submit], button', '#' + this.id).addClass('ow_inprogress');

        if( this.ajax ){
            OW.inProgressNode(buttons);
            var postString = '';

            $.each( dataToSend, function( index, data ){
                if ( $.isArray(data) || $.isPlainObject(data) ) {
                    $.each(data, function (key, value){
                        postString += index + '[' + key + ']=' + encodeURIComponent(value) + '&';
                    });
                }
                else{
                    postString += index + '=' + encodeURIComponent(data) + '&';
                }
            } );

            $.ajax({
                type: 'post',
                url: this.actionUrl,
                data: postString,
                dataType: self.ajaxDataType,
                success: function(data){
                    if(self.reset){
                        self.resetForm();
                    }
                    self.trigger('success', data);
                },
                error: function( XMLHttpRequest, textStatus, errorThrown ){
                    OW.error(textStatus);
                    throw textStatus;
                },
                complete: function(){
                    OW.activateNode(buttons);
                }
            });

            return false;
        }

        $.each(this.elements,
            function( i, o ){
                if( $(o.input).hasClass('invitation') ){
                    $(o.input).attr('disabled', 'disabled');
                }
            }
            );

        return true;
    }
}

owForms = {};

// custom fields
var addInvitationBeh = function( formElement, invitationString ){
    formElement.invitationString = invitationString;

    formElement.getValue = function(){
        var val = $(this.input).val();
        if( val != '' && val != this.invitationString ){
            $(this.input).removeClass('invitation');
            return val;
        }
        else{
            return '';
        }
    };

    $(formElement.input
        ).bind('focus.invitation', {formElement:formElement},
            function(e){
                el = $(this);
                el.removeClass('invitation');
                if( el.val() == '' || el.val() == e.data.formElement.invitationString){
                    el.val('');
                    //hotfix for media panel
                    if( 'htmlarea' in el.get(0) ){
                        el.unbind('focus.invitation').unbind('blur.invitation');
                        el.get(0).htmlarea();
                        el.get(0).htmlareaFocus();
                    }
                }
                else{
                    el.unbind('focus.invitation').unbind('blur.invitation');
                }
            }
        )/*.bind('blur.invitation', {formElement:formElement},
            function(e){
                el = $(this);
                if( el.val() == '' || el.val() == e.data.formElement.invitationString){
                    el.addClass('invitation');
                    el.val(e.data.formElement.invitationString);
                }
                else{
                    el.unbind('focus.invitation').unbind('blur.invitation');
                }
            }
    );*/
}

var OwTextField = function( id, name, invitationString ){
    var formElement = new OwFormElement(id, name);
    if( invitationString ){
        addInvitationBeh(formElement, invitationString);
    }
    return formElement;
}

var OwTextArea = function( id, name, invitationString ){
    var formElement = new OwFormElement(id, name);
    if( invitationString ){
        addInvitationBeh(formElement, invitationString);
    }
    return formElement;
}

var OwWysiwyg = function( id, name, invitationString ){
    var formElement = new OwFormElement(id, name);
    formElement.input.focus = function(){this.htmlareaFocus();};
    addInvitationBeh(formElement, invitationString);
    formElement.resetValue = function(){$(this.input).val('');$(this.input).keyup();};
    formElement.getValue = function(){
                var val = $(this.input).val();
                if( val != '' && val != '<br>' && val != '<div><br></div>' && val != this.invitationString ){
                    $(this.input).removeClass('invitation');
                    return val;
                }
                else{
                    return '';
                }
            };

    return formElement;
}

var OwRadioField = function( id, name ){
    var formElement = new OwFormElement(id, name);

    formElement.getValue = function(){
        var value = $("input[name='"+this.name +"']:checked", $(this.input.form)).val();
        return ( value == undefined ? '' : value );
    };

    formElement.resetValue = function(){
        $("input[name='"+this.name +"']:checked", $(this.input.form)).removeAttr('checked');
    };

    formElement.setValue = function(value){
        $("input[name='"+ this.name +"'][value='"+value+"']", $(this.input.form)).attr('checked', 'checked');
    };

    return formElement;
}

var OwCheckboxGroup = function( id, name ){
    var formElement = new OwFormElement(id, name);

    formElement.getValue = function(){
        var $inputs = $("input[name='"+ this.name +"[]']:checked", $(this.input.form));
        var values = [];

        $.each( $inputs, function(index, data){
                if( this.checked == true ){
                    values.push($(this).val());
                }
            }
        );

        return values;
    };

    formElement.resetValue = function(){
        var $inputs = $("input[name='"+ this.name +"[]']:checked", $(this.input.form));

        $.each( $inputs, function(index, data){
                $(this).removeAttr('checked');
            }
        );
    };

    formElement.setValue = function(value){
        for( var i = 0; i < value.length; i++ ){
            $("input[name='"+ this.name +"[]'][value='"+value[i]+"']", $(this.input.form)).attr('checked', 'checked');
        }
    };

    return formElement;
}

var OwCheckboxField = function( id, name ){
    var formElement = new OwFormElement(id, name);

    formElement.getValue = function(){
        var $input = $("input[name='"+this.name+"']:checked", $(this.input.form));
        if( $input.length == 0 ){
            return '';
        }
        return 'on';
    };

    formElement.setValue = function(value){
        var $input = $("input[name='"+this.name+"']:checked", $(this.input.form));
        if( value ){
            $input.attr('checked', 'checked');
        }
        else{
            $input.removeAttr('checked');
        }
    };
    formElement.resetValue = function(){
        var $input = $("input[name='"+this.name+"']:checked", $(this.input.form));
        $input.removeAttr('checked');
    };

    return formElement;
}

var OwRange = function( id, name ){
    var formElement = new OwFormElement(id, name);

    formElement.getValue = function(){
        var $inputFrom = $("select[name='"+ this.name +"[from]']");
        var $inputTo = $("select[name='"+ this.name +"[to]']");
        var values = [];

        values.push($inputFrom.val());
        values.push($inputTo.val());

        return values;
    };

    formElement.setValue = function(value){
        var $inputFrom = $("select[name='"+ this.name +"[from]']");
        var $inputTo = $("select[name='"+ this.name +"[to]']");

        if( value[1] ){
            $("option[value='"+ value[1] +"']", $inputFrom).attr('selected', 'selected');
        }

        if( value[2] ){
            $("option[value='"+ value[2] +"']", $inputTo).attr('selected', 'selected');
        }

    };

    return formElement;
}


/* end of forms */


/* Drag and drop fix */

DND_InterfaceFix = new (function(){

	var embed = function(context){
		var $context = $(context);
		var cWidth = $context.innerWidth();

		var configureEmbed = function($embed) {
			var embed = $embed.get(0);
			if ( embed.default_width === undefined || embed.default_width === null ) {
				embed.default_width = $embed.width();
			}

			if ( cWidth < embed.default_width )
			{
				$embed.css('width', '100%');
			}
			else
			{
				$embed.css('width', embed.default_width + 'px');
			}

                        $embed.attr('wmode', 'transparent');
    	};

    	var configureObject = function($object) {
    		$object.css('width', '100%');
    	};

    	$('embed', context).each(function(){
    		var $node = $(this).hide();
    		configureEmbed($node);
    		$node.show();
    	});

    	$('object', context).each(function() {
    		var $node = $(this).hide(), $embeds = $('embed', this);

    		configureObject($node);
		if ( $embeds.length )
		{
		    configureEmbed($embeds);
		}
    		$node.show();
    	});
	};

	var image = function(context) {

		var $context = $(context), cWidth;
		var cWidth = $context.innerWidth();

		if ( !cWidth )
		{
		    return;
		}

		var resize = function(img) {
			var $img = $(img);

			if ( img.default_width ) {
				img.default_width = $img.width();
			}

			if ( img.default_width > cWidth ) {
				$img.css('width', '100%');
			} else {
				$img.css('width', img.default_width);
			}
		};

		$context.find('img').each(function(){
                    $(this).css('max-width', '100%');
                    if (this.naturalWidth == 0) {
                        $(this).load(function(){
                            resize(this);
                        });
                    } else {
                        resize(this);
                    }
		});
	};


	var iframe = function(context)
        {
            var $iframe = $('iframe', context);
            var cWidth = $(context).innerWidth();
            $iframe.each(function(i, o)
            {
                var $o = $(o), url, wmode;

                if ( $o.width() > cWidth )
                {
                    $o.css('width', '100%');
                }

                url = $(this).attr("src");

                if ( !url ) return;

                wmode = "wmode=transparent";
                if ( url.indexOf("?") != -1)
                {
                    $o.attr("src", url + "&" + wmode);
                }
                else
                {
                    $o.attr("src", url + "?" + wmode);
                }
            });
	};

	this.fix = function(context) {
		this.embed(context);
		this.image(context);
		this.iframe(context);
	};

	this.embed = function(context) {
		$(context).each(function(){
			embed(this);
		})
	};

	this.image = function(context) {
		$(context).each(function(){
			image(this);
		});
	};

	this.iframe = function(context) {
		$(context).each(function(){
			iframe(this);
		});
	};

})();

/* Comments */
var OwComments = function( contextId, formName, genId, formLoadParams ){
	this.formName = formName;
	this.$cmpContext = $('#' + contextId);
    this.genId = genId;
    this.formLoadParams = formLoadParams;
}

OwComments.prototype = {
    repaintCommentsList: function( data ){
        owForms[this.formName].getElement('commentText').resetValue();

        if(data.error){
                OW.error(data.error);
                return;
        }
        OW.trigger('base.comments_list_update', {entityType: data.entityType, entityId: data.entityId, id:this.genId});
        $('.comments_list_cont', this.$cmpContext).empty().append($(data.commentList));
        OW.addScript(data.onloadScript);
    },

    updateCommentsCountOnPage: function( count ){
        if( count == 0 )
        {
            count = parseInt($('input[name=commentCountOnPage]', this.$cmpContext).val()) + 1;
        }
        $('input[name=commentCountOnPage]', this.$cmpContext).val(count);
    },

    loadForm: function(){
      var self = this;
      var height = $('.base_cmnt_mark .comments_fake_autoclick', this.$cmpContext).outerHeight();
      $('.base_cmnt_mark', this.$cmpContext).replaceWith('<div class="ow_preloader ow_smallmargin" style="height:'+height+'px;"></div>');

      OW.loadComponent('BASE_CMP_CommentsForm', self.formLoadParams,
      {
          onReady: function( html ){
             $('.base_cmnts_temp_cont', self.$cmpContext).append(html);

          },
          onLoad: function(){
              $('.ow_preloader', self.$cmpContext).remove();
              $('.base_cmnts_temp_cont', self.$cmpContext).css({display:'block'});
              $('textarea', self.$cmpContext).focus();

          }
      });
    },

    initForm: function( textareaId, attchId ){
        var self = this;

        OWLinkObserver.observeInput(textareaId, function( link ){
            var ac = $('#attachment_preview_' + self.genId);
            ac.show().html('<div class="ow_preloader" style="height: 30px;"></div>');

            this.requestResult(
                function( r ){
                    ac.html(r);
                }
            );

            this.onResult = function( r ){
                $('#' + attchId).val(JSON.stringify(r));
            };

        });

        owForms[this.formName].bind('success',
            function(data){
                owCommentCmps[self.genId].updateCommentsCountOnPage(0);
                owCommentCmps[self.genId].repaintCommentsList(data);
                OW.trigger('base.init_attachment', self.genId);
                owForms[self.formName].getElement('attch').setValue('');
                OWLinkObserver.getObserver(textareaId).resetObserver();
                OW.trigger('base.comment_add', {entityType: data.entityType, entityId: data.entityId}, this);
            }
        );

        owForms[this.formName].reset = false;

        OW.bind('base.attachment_added',
            function(data){
                if( data.uid == self.genId ){
                    owForms[self.formName].getElement('attch').setValue(JSON.stringify(data));
                }
            }
        );

        OW.bind('base.attachment_deleted',
            function(data){
                if( data.uid == self.genId ){
                    owForms[self.formName].getElement('attch').setValue('');
                    OWLinkObserver.getObserver(textareaId).resetObserver();
                }
            }
        );
    }

};

var OwCommentsList = function( params ){
	this.$context = $('#' + params.contextId);
	$.extend(this, params, owCommentListCmps.staticData);
}

OwCommentsList.prototype = {
	init: function(){
		var self = this;
        $('.ow_comments_item').hover(function(){$('.cnx_action', this).show();$('.ow_comments_date_hover', this).hide();}, function(){$('.cnx_action', this).hide();$('.ow_comments_date_hover', this).show();});

        OW.bind('base.comments_list_update',
            function(data){
                if( data.entityType == self.entityType && data.entityId == self.entityId && data.id != self.cid ){
                    self.reload();
                }
            }
        );

        OW.trigger('base.comments_list_init', {entityType: this.entityType, entityId: this.entityId}, this);

        if( this.pagesCount > 0 )
        {
            for( var i = 1; i <= this.pagesCount; i++ )
            {
                $('a.page-'+i, self.$context).bind( 'click', {i:i},
                    function(event){
                        self.reload(event.data.i);
                    }
                );
            }
        }

        $.each(this.actionArray.comments,
            function(i,o){
                $('#'+i).click(
                    function(){
                        if( confirm(self.delConfirmMsg) )
                        {
                           $(this).closest('div.ow_comments_item').slideUp(300, function(){$(this).remove();});

                            $.ajax({
                                type: 'POST',
                                url: self.delUrl,
                                data: 'cid='+self.cid+'&commentCountOnPage='+self.commentCountOnPage+'&ownerId='+self.ownerId+'&pluginKey='+self.pluginKey+'&displayType='+self.displayType+'&entityType='+self.entityType+'&entityId='+self.entityId+'&page='+self.page + '&commentId=' + o,
                                dataType: 'json',
                                success : function(data){
                                    if(data.error){
                                            OW.error(data.error);
                                            return;
                                    }

                                    self.$context.replaceWith(data.commentList);
                                    OW.addScript(data.onloadScript);
                                    OW.trigger('base.comment_delete', {entityType: self.entityType, entityId: self.entityId}, this);
                                }
                            });
                        }
                    }
             );
            }
        );

        $.each(this.actionArray.users,
            function(i,o){
                $('#'+i).click(
                    function(){
                        OW.Users.deleteUser(o);
                    }
             );
            }
        );

        for( i = 0; i < this.commentIds.length; i++ )
        {
            if( $('#att'+this.commentIds[i]).length > 0 )
             {
                 $('.attachment_delete',$('#att'+this.commentIds[i])).bind( 'click', {i:i},
                    function(e){

                        $('#att'+self.commentIds[e.data.i]).slideUp(300, function(){$(this).remove();});

                        $.ajax({
                            type: 'POST',
                            url: self.delAtchUrl,
                            data: 'cid='+self.cid+'&commentCountOnPage='+self.commentCountOnPage+'&ownerId='+self.ownerId+'&pluginKey='+self.pluginKey+'&displayType='+self.displayType+'&entityType='+self.entityType+'&entityId='+self.entityId+'&page='+self.page + '&commentId=' + self.commentIds[e.data.i],
                            dataType: 'json'
                        });
                    }
                 );
             }
        }

        if( this.displayType == 3 || this.displayType == 4 )
        {
            $('.comments_view_all a', this.$context).one('click',
                function(){
                    $(this).replaceWith('<img src="'+self.preloaderImgUrl+'" alt="" />');
                    self.commentCountOnPage = 1000;
                    if( window.commentCmps && window.commentCmps[self.cid]  )
                    {
                        window.commentCmps[self.cid].updateCommentsCountOnPage(1000);
                    }
                    self.reload(1);
                }
            );

        }
	},

	reload:function( page ){
		var self = this;
		$.ajax({
            type: 'POST',
            url: self.respondUrl,
            data: 'cid='+self.cid+'&commentCountOnPage='+self.commentCountOnPage+'&ownerId='+self.ownerId+'&pluginKey='+self.pluginKey+'&displayType='+self.displayType+'&entityType='+self.entityType+'&entityId='+self.entityId+'&page='+page,
            dataType: 'json',
            success : function(data){
               if(data.error){
                        OW.error(data.error);
                        return;
                }

                self.$context.replaceWith(data.commentList);
                OW.addScript(data.onloadScript);
            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                OW.error('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
	}
}

owCommentCmps = {};
owCommentListCmps = {items:{},staticData:{}};

var OwAttachment = function( params )
{
    var self = this;
    var floatbox;
    var $context = $('#'+params.uid);
    var $previewCont = $('#attachment_preview_'+params.uid);
    var $videoCont = $('#video_code_'+params.uid);
    this.params = params;

    OW.bind('base.init_attachment', function(uid){if(uid == self.params.uid) self.init();});

    this.init = function(){
        $previewCont.empty();
        this.item = null;
        while( $('#hd_'+this.params.uid).length > 0 ){
            $('#hd_'+this.params.uid).remove();
        }

        $('input[type=button]', $videoCont).unbind('click').click(function(){self.submitVideo();});

        $('a.video', $context).unbind('click').click(
            function(){
                floatbox = new OW_FloatBox({
                    $title: self.params.langs.addVideoLabel,
                    $contents: $videoCont,
                    width: 500,
                    height: 300,
                    icon_class: 'ow_ic_video'
                });
            }
        );

        this.hideLoader();
        $('textarea', $videoCont).val('');
        $previewCont.removeClass('item_loaded').empty();
        $('a.image', $context).empty().append($('<input type="file" name="attachment" />'));
        $('input[type=file]', $context).unbind('change').change(
            function(){
                self.submitFile();
            }
        );
        $context.closest('.ow_attachment_icons').show();
    }

    this.submitFile = function(){
        this.disableButtons();
        this.showLoader();
        $form = $('<form method="post" action="' + this.params.addPhotoUrl + '" enctype="multipart/form-data" target="form_' + this.params.uid + '"></form>')
            .append($('input[type=file]', $context));
        $('<div style="display:none" id="hd_'+this.params.uid+'"><div>').appendTo($('body'))
            .append($('<iframe name="form_' + this.params.uid + '"></iframe>'))
            .append($form);
        $form.submit();
    }

    this.submitVideo = function(){
        code = $('textarea', $videoCont).val();

        if( !code || code.length < 10 ){
            OW.error(this.params.langs.emptyVideoCode);
            return;
        }
        //this.showLoader();
        this.disableButtons();
        floatbox.close();
        $.ajax({type:'POST', url:this.params.addVideo, data:{code:code}, dataType: 'json',
            success: function(item){
                self.hideLoader();
                self.addItem(item);
            },
            error: function(){
                OW.error('error');
            }
        });
    }

    this.addItem = function( item ){
        this.item = item;
        this.aa = item;
        
        if( $previewCont.length > 0 ){
            $previewCont.show().addClass('item_loaded').css({height:'auto'}).append(item.cmp);
            $('.attachment_delete', $previewCont).click(function(){self.deleteItem();});
        }
        OW.resizeImg($('.ow_oembed_attachment'),{width:'150'});
        OW.trigger('base.attachment_added', this.getEventParams());        
    }

    this.deleteItem = function(){
        if( this.item.type == 'photo' ){
            $.ajax({type:'POST', url:this.params.deleteUrl, data:this.item});
        }

        OW.trigger('base.attachment_deleted', this.getEventParams());

        $previewCont.children().slideUp(300, function(){
            self.init();
            $previewCont.hide();
        });        
    }

    this.showLoader = function(){
        $previewCont.show();
        $previewCont.empty().addClass('attachment_preloader').css({height:45});
    }

    this.hideLoader = function(){
        $previewCont.empty().removeClass('attachment_preloader').css({height:'auto'});
        return this;
    }

    this.disableButtons = function(){
        OW.hideTip($('a.video', $context));
        OW.hideTip($('a.image', $context));
        $context.closest('.ow_attachment_icons').hide();
    }

    this.getEventParams = function(){        
        var data = {uid:this.item.uid, type:this.item.type, genId:this.item.genId};
        if( this.item.type == 'photo' ){
            data.url = this.item.url;
            data.href = this.item.url;
        }

        if( this.item.type == 'video' ){
            data.html = this.item.code;
        }

        return data;
    }
}
owattachments = {};

var OwRate = function( params ){
    this.cmpId = params.cmpId;
    this.userRate = params.userRate;
    this.entityId = params.entityId;
    this.entityType = params.entityType;
    this.itemsCount = params.itemsCount;
    this.respondUrl = params.respondUrl;
    this.ownerId = params.ownerId;
    this.$context = $('#rate_'+params.cmpId);
}

OwRate.prototype = {
    init: function(){
        var self = this;
        this.setRate(this.userRate);
        for( var i = 1; i <= this.itemsCount; i++ ){
            $('#' + this.cmpId + '_rate_item_' + i).bind( 'mouseover', {i:i},
                function(e){
                    self.setRate(e.data.i);
                }
            ).bind( 'mouseout',
                function(){
                    self.setRate(self.userRate);
                }
            ).bind( 'click', {i:i},
                function(e){
                    self.updateRate(e.data.i);
                }
            );
        }
    },

    setRate: function( rate ){
        for( var i = 1; i <= this.itemsCount; i++ ){
            var $el = $('#' + this.cmpId + '_rate_item_' + i);
            $el.removeClass('active');
            if( !rate ){
                continue;
            }
            if( i <= rate ){
                $el.addClass('active');
            }
        }
    },

    updateRate: function( rate ){
        var self = this;
        if( rate == this.userRate ){
            return;
        }
        this.userRateBackup = this.userRate;
        this.userRate = rate;
        $.ajax({
            type: 'POST',
            url: self.respondUrl,
            data: 'entityType='+encodeURIComponent(self.entityType)+'&entityId='+encodeURIComponent(self.entityId)+'&rate='+encodeURIComponent(rate)+'&ownerId='+encodeURIComponent(self.ownerId),
            dataType: 'json',
            success : function(data){

                if( data.errorMessage ){
                    OW.error(data.errorMessage);
                    self.userRate = self.userRateBackup;
                    self.setRate(self.userRateBackup);
                    return;
                }

                if( data.message ){
                    OW.info(data.message);
                }

                $('.total_score', self.$context).empty().append(data.totalScoreCmp);
                OW.trigger('base.rate_update', {entityType: self.entityType, entityId: self.entityId, rate: rate, ownerId: self.ownerId}, this);
            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                alert('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
    }
}

OWLinkObserver =
{
    observers: {},

    observeInput: function( inputId, callBack )
    {
        this.observers[inputId] = new OWLinkObserver.handler(inputId, callBack);
    },

    getObserver: function( inputId )
    {
        return this.observers[inputId] || null;
    }
};

OWLinkObserver.handler = function( inputId, callBack )
{
    this.callback = callBack;
    this.input = $('#' + inputId);
    this.inputId = inputId;
    this.detectedUrl = null;

    this.onResult = function(){};

    this.startObserve();
};

OWLinkObserver.handler.prototype =
{
    startObserve: function()
    {
        var self = this;

        var detect = function()
        {
            var val = self.input.val();

            if ( $.trim(val) )
            {
                self.detectLink();
            }
        };

        this.input.bind('paste', function(){
            setTimeout(function() {
                detect();
            }, 100);
        });

        this.input.bind('blur', detect);

        this.input.keyup(function(e)
        {
            if (e.keyCode == 32 || e.keyCode == 13) {
                detect();
            }
        });
    },

    detectLink: function( text )
    {
        var text, rgxp, result;

        text = this.input.val();
        rgxp = /(http(s)?:\/\/|www\.)((\d+\.\d+\.\d+\.\d+)|(([\w-]+\.)+([a-z,A-Z][\w-]*)))(:[1-9][0-9]*)?(\/?([?\w\-.\,\/:%+@&*=]+[\w\-\,.\/?\':%+@&=*|]*)?)?/;
        result = text.match(rgxp);

        if ( !result )
        {
            return false;
        }

        if ( this.detectedUrl == result[0] )
        {
            return false;
        }

        this.detectedUrl = result[0];

        this.callback.call(this, this.detectedUrl);
    },

    requestResult: function( callback, link )
    {
        var self = this;

        link = link || this.detectedUrl;

        $.ajax({
            type: 'POST',
            url: OW.ajaxAttachmentLinkRsp,
            data: {"url": link},
            dataType: 'json',
            success: function( r )
            {
                if ( r.content )
                {
                    if ( r.content.css )
                    {
                        OW.addCss(r.content.css);
                    }

                    if ( $.isFunction(callback) )
                    {
                        callback.call(self, r.content.html, r.result);
                    }

                    if ( $.isFunction(self.onResult) )
                    {
                        self.onResult(r.result);
                    }

                    if ( r.content.js )
                    {
                        OW.addScript(r.content.js);
                    }
                }

                if ( r.attachment && OW_AttachmentItemColletction[r.attachment] )
                {
                    OW_AttachmentItemColletction[r.attachment].onChange = function(oembed){
                        self.onResult(oembed);
                    };
                }
            }
        });
    },

    resetObserver: function()
    {
        this.detectedUrl = null;
    }
};

OW_AttachmentItemColletction = {};

OW_Attachment = function(uniqId, data)
{
    var self = this;

    this.data = data;
    this.uniqId = uniqId;
    this.node = document.getElementById(this.uniqId);
    this.onChange = function(){};

    //OW.resizeImg(this.$('.EQ_AttachmentImageC'),{width:'150'});

    this.$('.OW_AttachmentSelectPicture').click(function()
    {
        self.showImageSelector();
    });

    this.$('.OW_AttachmentDelete').click(function()
    {
        $(self.node).remove();

        if ( $.isFunction(self.onChange) )
        {
            self.data = [];
            self.onChange.call(self, self.data);
        }
    });
};

OW_AttachmentProto = function()
{
    this.$ = function (sel)
    {
        return $(sel, this.node);
    };

    this.showImageSelector = function()
    {
        var fb, $contents, self = this;

        $contents = this.$('.OW_AttachmentPicturesFbContent')

        fb = new OW_FloatBox({
            $title: this.$('.OW_AttachmentPicturesFbTitle'),
            $contents: $contents,
            width: 520
        });

        $contents.find('.OW_AttachmentPictureItem').unbind().click(function()
        {
            var img = $('img', this);
            self.changeImage(img.attr('src'));

            fb.close();
        });
    };

    this.changeImage = function( url )
    {
        var clone, original;

        original = this.$('.OW_AttachmentImage');
        clone = original.clone();
        clone.attr('src', url);
        original.replaceWith(clone);

        if ( $.isFunction(this.onChange) )
        {
            this.data["thumbnail_url"] = url;

            this.onChange.call(this, this.data);
        }
    };

};
OW_Attachment.prototype = new OW_AttachmentProto();

// Additional jQuery plugins

jQuery.fn.onImageLoad = function(fn)
{
    this.load(fn);
    this.each( function() {
        if ( this.complete && this.naturalWidth !== 0 ) {
            $(this).trigger('load');
        }
    });
};


/* PING */

OW_PingCommand = function( commandName, commandObject, stack )
{
    $.extend(this, commandObject);

    this.commandName = commandName;
    this.repeatTime = false;
    this.minRepeatTime = null;

    this.stack = stack;
    this.commandTimeout = null;
    this.stopped = true;
    this.skipped = false;
    this.inProcess = false;
    this.isRootCommand = false;

    this._lastRunTime = null;
};

OW_PingCommand.PROTO = function()
{
    this._updateLastRunTime = function()
    {
        this._lastRunTime = $.now();
    };

    this._received = function( r )
    {
        this.after(r);
    },

    this._delayCommand = function()
    {
        var self = this;

        if ( this.commandTimeout )
        {
            window.clearTimeout(this.commandTimeout);
        }

        this.commandTimeout = window.setTimeout(function()
        {
            self._run();
            self.skipped = false;
        }, this.repeatTime);
    },

    this._completed = function()
    {
        this.inProcess = false;
        this._updateLastRunTime();

        if ( this.skipped || this.stopped || this.repeatTime === false )
        {
            return;
        }

        this._delayCommand();
    },

    this._getStackCommand = function()
    {
        return {
            "command": this.commandName,
            "params": this.params
        };
    };

    this._beforeStackSend = function()
    {
        if ( this.minRepeatTime === null || this.stopped || this.inProcess || this.isRootCommand )
        {
            return;
        }

        if ( $.now() - this._lastRunTime < this.minRepeatTime )
        {
            return;
        }

        this._run();
    };

    this._run = function()
    {
        if ( !this.stopped )
        {
            this.inProcess = true;
            this.stack.push(this);
        }

        if ( this.onRun )
        {
            this.onRun(this);
        }
    };

    this.params = {};
    this.before = function(){};
    this.after = function(){};

    this.start = function( repeatTime )
    {
        if ( $.isNumeric(repeatTime) )
        {
            this.repeatTime = repeatTime;
        }
        else if ( $.isPlainObject(repeatTime) )
        {
            if ( repeatTime.max )
            {
                this.repeatTime = repeatTime.max;
            }

            if ( repeatTime.min )
            {
                this.minRepeatTime = repeatTime.min == 'each' ? 0 : repeatTime.min;
            }
        }

        this.stop();
        this.stopped = false;

        if ( !this.inProcess )
        {
            this._run();
        }
    };

    this.skip = function()
    {
        this.skipped = true;
        this._delayCommand();
    };

    this.stop = function()
    {
        this.stopped = true;
    };
};

OW_PingCommand.prototype = new OW_PingCommand.PROTO();


OW_Ping = function()
{
    var _stack = [], _commands = {};

    var rspUrl;
    var _rootCommand = null;

    var beforeStackSend, sendStack, refreshRootCommand, rootOnCommandRun, genericOnCommandRun, setRootCommand;

    rootOnCommandRun = function( command )
    {
        window.setTimeout(function(){
            sendStack();
        }, 10);
    };

    genericOnCommandRun = function( command )
    {
        if ( !_rootCommand )
        {
            setRootCommand(command);
            rootOnCommandRun(command);

            return;
        }

        if ( command.repeatTime === false )
        {
            return;
        }

        if ( _rootCommand.repeatTime === false || _rootCommand.repeatTime > command.repeatTime )
        {
            setRootCommand(command);
            rootOnCommandRun(command);
        }
    };

    refreshRootCommand = function()
    {
        var rootCommand = null;

        for ( var c in _commands )
        {
            if ( _commands[c].repeatTime === false || _commands[c].stopped  )
            {
                continue;
            }

            if ( !rootCommand || _commands[c].repeatTime < rootCommand.repeatTime )
            {
                rootCommand = _commands[c];
            }
        }

        if ( rootCommand )
        {
           setRootCommand(rootCommand);
        }
    };

    setRootCommand = function( command )
    {
        if ( _rootCommand )
        {
            _rootCommand.onRun = genericOnCommandRun;
            _rootCommand.isRootCommand = false;
        }

        command.isRootCommand = true;
        _rootCommand = command;
        _rootCommand.onRun = rootOnCommandRun;
    };

    beforeStackSend = function()
    {
        for ( var c in _commands )
        {
            _commands[c]._beforeStackSend();
        }
    };

    sendStack = function()
    {
        beforeStackSend();

        if ( !_stack.length )
        {
            return;
        }

        var stack = [], commands = [];

        while ( _stack.length )
        {
            var c = _stack.pop();
            commands.push(c);

            if ( c.before() === false )
            {
                c.skip();
                continue;
            }

            stack.push(c._getStackCommand());
        }

        if ( !stack.length )
        {
            return;
        }

        var request = {
            "stack": stack
        };

        var jsonRequest = JSON.stringify(request);

        var ajaxOptions =
        {
            url: rspUrl,
            dataType: 'json',
            type: 'POST',
            data: {request: jsonRequest},
            success: function(result)
            {
                if ( !result || !result.stack )
                {
                    return;
                }

                $.each(result.stack, function(i, command)
                {
                    if ( _commands[command.command] )
                    {
                        _commands[command.command]._received(command.result);
                    }
                });
            },

            complete: function()
            {
                $(commands).each(function(i, command)
                {
                    command._completed();
                });

                refreshRootCommand();
            }
        };

        $.ajax(ajaxOptions);
    };

    this.addCommand = function( commandName, commandObject )
    {
        if ( _commands[commandName] )
        {
            return _commands[commandName];
        }

        commandObject = commandObject || {};

        _commands[commandName] = new OW_PingCommand(commandName, commandObject, _stack);
        _commands[commandName].onRun = genericOnCommandRun;

        return _commands[commandName]
    };

    this.getCommand = function( commandName )
    {
        return _commands[commandName] || null;
    };

    this.setRspUrl = function( url )
    {
        rspUrl = url;
    };

};


OW_Ping.getInstance = function()
{
    if ( !OW_Ping.pingInstance )
    {
        OW_Ping.pingInstance = new OW_Ping();
    }

    return OW_Ping.pingInstance;
}



OW.getPing = function()
{
    return OW_Ping.getInstance();
};

/* Add command example
OW.getPing().addCommand('ajaxim', {
    params: {
        p1: 1,
        p2: 3
    },
    before: function()
    {
        this.params.p1 = 4;
    },
    after: function( res )
    {

    }
}).start(5000); // repeatTime ( Number or Object {max: maxRepeatTime, min: minRepeatTime} )
*/




/* Scroll */

OW.Scroll = {};

OW.Scroll.settings =
{
    verticalGutter: 0,
    horizontalGutter: 0,
    showArrows: false
};

OW.addScroll = function( node, settings )
{
    settings = settings || {};
    settings = $.extend({}, OW.Scroll.settings, settings);

    var $node = $(node);

    $node.unbind('jsp-initialised').bind('jsp-initialised', function()
    {
        var track = $node.find('.jspTrack');

        $node.hover(function()
        {
            track.fadeIn('fast');
        }, function()
        {
            track.fadeOut(200);
        });
    });

    if ( !$node.hasClass('ow_scrollable') )
    {
        $node.addClass('ow_scrollable')
    }

    $node.jScrollPane(settings);

    return $node.data().jsp;
};

OW.removeScroll = function( node )
{
    var $node = $(node);
    var jsp = $node.data('jsp');

    if ( jsp )
    {
        jsp.destroy();
    }
    //$node.jScrollPaneRemove();
    $node.removeClass('ow_scrollable');
};

OW.updateScroll = function( node, settings )
{
    var jsp = $(node).data('jsp');

    settings = settings || {};
    settings = $.extend({}, OW.Scroll.settings, settings);

    if ( jsp )
    {
        jsp.reinitialise(settings);
    }
};

OW.addScrolls = function( context )
{
    context = context || null;

    $('.ow_scrollable', context).each(function()
    {
        OW.addScroll(this);
    });
};

OW.removeScrolls = function( context )
{
    context = context || null;

    $('.ow_scrollable', context).each(function()
    {
        OW.removeScroll(this);
    });
};

OW.updateScrolls = function( context )
{
    context = context || null;

    $('.ow_scrollable', context).each(function()
    {
        OW.updateScroll(this);
    });
};

OW.Utils = (function() {

    return {
        toggleText: function( node, value, alternateValue ) {
            var $node = $(node), text = $node.text();

            if ( !$node.data("toggle-text") )
                $node.data("toggle-text", text);

            alternateValue = alternateValue || $node.data("toggle-text");
            $node.text(text == alternateValue ? value : alternateValue);
        },

        toggleAttr: function( node, attributeName, value, alternateValue ) {
            var $node = $(node), attributeValue = $node.attr(attributeName);

            if ( !$node.data("toggle-" + attributeName) )
                $node.data("toggle-" + attributeName, attributeValue);

            alternateValue = alternateValue || $node.data("toggle-" + attributeName);
            $node.attr(attributeName, attributeValue == alternateValue ? value : alternateValue);
        }
    };
})();


OW.Users = null;
OW_UsersApi = function( _settings )
{
    var _usersApi = this;

    var _query = function(command, params, callback) {
        callback = callback || function( r ) {
            if ( !r ) return;
            if (r.info) OW.info(r.info);
            if (r.error) OW.error(r.error);
        };

        return $.getJSON(_settings.rsp, {"command": command, "params": JSON.stringify(params)}, callback);
    };

    OW.Users = this;

    //Public methods

    this.deleteUser = function(userId, callBack, showMessage)
    {
        showMessage = showMessage === false ? false : true;

        var redirectUrl = null;

        if ( typeof callBack == 'string' )
        {
            redirectUrl = callBack;
        }

        var floatBox;

        floatBox = OW.ajaxFloatBox('BASE_CMP_DeleteUser', [{userId: userId, showMessage : showMessage && redirectUrl}],
        {
            title: OW.getLanguageText('base', 'delete_user_confirmation_label'),
            iconClass: 'ow_ic_add',
            scope: {
                deleteCallback: function( r )
                {
                    if ( r.result == 'error' )
                    {
                        if ( r.message )
                        {
                            OW.error(r.message);
                        }

                        return;
                    }

                    if ( redirectUrl )
                    {
                        window.location.href = redirectUrl;

                        return;
                    }

                    if ( showMessage )
                    {
                        OW.info(r.message);
                    }

                    if ( callBack )
                    {
                        callBack.call(floatBox, r);

                        return;
                    }
                }
            }
        });
    },

    this.showUsers = function(userIds, title)
    {
    	title = title || OW.getLanguageText('base', 'ajax_floatbox_users_title');

    	OW.ajaxFloatBox('BASE_CMP_FloatboxUserList', [userIds], {iconClass: "ow_ic_user", title: title, width: 470});
    },

    this.suspendUser = function( userId, callback ) {
        return _query("suspend", {"userId": userId}, callback);
    };

    this.unSuspendUser = function( userId, callback ) {
        return _query("unsuspend", {"userId": userId}, callback);
    };

    this.blockUserWithConfirmation = function( userId, confirmationCallback, callback ) {
        var floatBox = OW.ajaxFloatBox("BASE_CMP_BlockUser", [userId], {
            scope: {
                "confirmCallback": function() {
                    _usersApi.blockUser(userId, callback);
                    if ( $.isFunction(confirmationCallback) ) {
                        confirmationCallback.call(null, userId);

                        floatBox.close();
                    }
                }
            }
        });
    };

    this.blockUser = function( userId, callback ) {
        return _query("block", {"userId": userId}, callback);
    };

    this.unBlockUser = function( userId, callback ) {
        return _query("unblock", {"userId": userId}, callback);
    };

    this.featureUser = function( userId, callback ) {
        return _query("feature", {"userId": userId}, callback);
    };

    this.unFeatureUser = function( userId, callback ) {
        return _query("unfeature", {"userId": userId}, callback);
    };
};