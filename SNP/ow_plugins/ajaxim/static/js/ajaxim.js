var ImSearchField = function( id, name, invitationString ){
    var handler = this;
    var formElement = new OwFormElement(id, name);
    if( invitationString ){
        addInvitationBeh(formElement, invitationString);
    }

    $(formElement.input).keydown(function(ev){

        if (ev.which === 13 && !ev.ctrlKey && !ev.shiftKey) {
            ev.preventDefault();

            return false;
        }
    });

    $(formElement.input).keyup(function(ev){

        if (ev.which === 13 && !ev.ctrlKey && !ev.shiftKey) {
            ev.preventDefault();

            return false;
        }

        handler.updateList($(this).val());
    });

    $('#'+name+'_close_btn_search').click(function(){
        $(formElement.input).val('');
        $(formElement.input).focus();

        handler.updateList($(formElement.input).val());
    });

    this.updateList = function(name){

        var expr = new RegExp("^"+name+".*", "i");

        $('.ow_chat_list ul li').each(function(){
            var author = $(this).find('.ow_chat_in_item_author').html();
            if (!expr.test(author))
            {
                $(this).css('display', 'none');
            }
            else
            {
                $(this).css('display', 'block');
            }
        });
    }

    return formElement;
}
/*
function getClientTimezone() {
    var rightNow = new Date();
    var date1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);
    var temp = date1.toGMTString();
    var date2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
    var std_time_offset = (date1 - date2) / (1000 * 60 * 60);

    var june1 = new Date(rightNow.getFullYear(), 6, 1, 0, 0, 0, 0);
        temp = june1.toGMTString();
    var june2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
    var daylight_time_offset = (june1 - june2) / (1000 * 60 * 60);
    var dst;
    if (std_time_offset == daylight_time_offset)
    {
        dst = 0; // daylight savings time is NOT observed
    }
    else
    {
        dst = 1; // daylight savings time is observed
    }

    return (std_time_offset + dst) * 3600 * 1000;
}
*/
function htmlspecialchars(string, quote_style, charset, double_encode) {
    // Convert special characters to HTML entities
    //
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/htmlspecialchars    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Nathan
    // +   bugfixed by: Arno
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +      input by: felix    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // %        note 1: charset argument not supported
    // *     example 1: htmlspecialchars("<a href='test'>Test</a>", 'ENT_QUOTES');
    // *     returns 1: '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
    // *     example 2: htmlspecialchars("ab\"c'd", ['ENT_NOQUOTES', 'ENT_QUOTES']);    // *     returns 2: 'ab"c&#039;d'
    // *     example 3: htmlspecialchars("my "&entity;" is still here", null, null, false);
    // *     returns 3: 'my &quot;&entity;&quot; is still here'
    var optTemp = 0,
    i = 0,        noquotes = false;
    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
    }
    string = string.toString();
    if (double_encode !== false) { // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else
            if (OPTS[quote_style[i]])
            {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }

    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE)
    {
        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes)
    {
        string = string.replace(/"/g, '&quot;');
    }
    string = string.replace(/\n/g, '<br />');
    return string;
}

function im_createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function im_readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if ( c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function im_eraseCookie(name) {
    im_createCookie(name,"",-1);
}

/*                          Instant Chat MVC                    */

var OW_InstantChat = {};

OW_InstantChat.Handler = function(){

    var self = this;
    this.handlers = {};
    this.pingTimeout = 5000;
    this.sendInProcess = false;

    this.addHandler = function(callback, eventName){
        this.handlers[eventName] = callback;
    }

    this.connect = function(obj){

        OW.getPing().addCommand('ajaxim_ping', {
            params: {},
            before: function()
            {
                var timestamps = OW_InstantChat_App.contactManager.getLastMessageTimestamps();
                var date = new Date();
                var time = parseInt(date.getTime() / 1000);

                this.params.action = 'get'
                this.params.lastMessageTimestamps = timestamps;
                this.params.onlineCount = OW_InstantChat_App.contactManager.getRosterLength();
                this.params.lastRequestTimestamp = time;

            },
            after: function( data )
            {
                if (self.sendInProcess)
                {
                    return false;
                }

                $('.ow_chat_list .ow_chat_preloader').remove();

                if (typeof data.presenceList != 'undefined' && data.presenceList.length > 0)
                {
                    $.each(data.presenceList, function(){
                        self.handlers['presence'].call(obj, this );
                    } );
                }

                if (data.messageListLength > 0)
                {
                    $.each(data.messageList, function(){
                        self.handlers['message'].call(obj, this );
                    } );
                }
            }
        }).start(this.pingTimeout);
    }

};

OW_InstantChat.Application = function(){ 

    var self = this;

    this.node = OW_InstantChat.Details.node;
    this.password = OW_InstantChat.Details.password;
    this.domain = OW_InstantChat.Details.domain;
    this.jid = this.node+'@'+this.domain;
    this.connection = null;

    this.newMessageTimeout = 0;
    this.status = 'online';
    this.pendingSubscriber = null;

    this.userInfoPackage = {};
    this.userInfoPackageEmpty = true;

    this.onConnect = function(data){

    }

    this.onConnected = function() {

    }

    this.onDisconnected = function() {

    }

    this.init = function(){

        this.contactManager = new OW_InstantChat.ContactManager_Controller(this);
        this.tab = this.getNewTabResource();

        im_createCookie('im_soundEnabled', (im_soundEnabled)?1:0, 1);

        this.onConnect({
            jid: self.jid+'/'+self.tab,
            password: self.password
        });

        self.connection = new OW_InstantChat.Handler();

        self.connection.addHandler(self.onRosterChanged, "rosterChanged");

        self.connection.addHandler(self.onMessage, "message");

        self.connection.addHandler(self.onPresence, "presence");

        self.connection.connect(this);

    }

    this.getNewTabResource = function() {
        for (var i=1; i<50; i++)
        {
            var tab = im_readCookie('im_tab_'+i);
            if ( parseInt(tab) == NaN || typeof tab == 'undefined' || tab == null)
            {
                var im_tab = 'im_tab_'+i;
                im_createCookie('im_tab_'+i, im_tab, 1);
                return im_tab;
            }
        }

        return 'im_tab_1';
    }

    this.jidToId = function(jid) {
        return jid;
    }


    this.onRoster = function(iq) {

    }

    this.statusUpdatedTimeout = {};

    this.onPresence = function(presence) {

        var jid_id = presence.node;

        var contact = self.contactManager.getItem(jid_id)
        .removeClass("online")
        .removeClass("away")
        .removeClass("chat")
        .removeClass("ow_chat_offline");

        if (typeof contact != 'undefined')
        {
            if (presence.data.status == 'offline') {

                    if (!self.statusUpdatedTimeout[jid_id])
                    {
                        self.statusUpdatedTimeout[jid_id] = setTimeout(function(){

                            OW.trigger('statusUpdated', {
                                node: jid_id,
                                status: 'offline'
                            });

                        }, 5000);
                    }

                    return true;

            } else {

                if (self.statusUpdatedTimeout[jid_id])
                {
                    if (im_debug_mode){console.log('Application.onPresence: Cancel '+jid_id+' offline status '+self.statusUpdatedTimeout[jid_id]);}
                    window.clearTimeout(self.statusUpdatedTimeout[jid_id]);
                    self.statusUpdatedTimeout[jid_id] = null;
                }

                if(presence.data.show == 'away')
                {
                    if ( contact.data('show') != 'offline' )
                    {
                        var status_show_timeout = setTimeout(function(){
                            if (im_debug_mode){console.log('Application.onPresence: '+jid_id+' delayed show change to away');}
                            if ( contact.data('show') == show)
                            {
                                contact.addClass(show);
                            }

                        }, im_awayTimeout);
                    }
                }
                else
                {
                    contact.addClass(presence.data.show);
                }

                contact.addClass("online");
                OW.trigger('statusUpdated', {
                    node: jid_id,
                    status: 'online'
                });
            }
        }

        if ( jid_id != self.node )
        {
            if (contact.length == 0 )
            {
                OW.trigger('userInfoReceived', presence);
            }
        }

        return true;
    }

    this.onRosterChanged = function(iq) {
        return true;
    }

    this.onMessage = function(message) {

        //TODO Composing...
//        var composing = $(message).find('composing');
//        if (composing.length > 0) {
//
//            if (jid_id != self.node && typeof self.contactManager.dialogs[jid_id] != 'undefined')
//            {
//                var dialog = self.contactManager.getDialog(jid_id);
//                dialog.showComposing();
//            }
//        }
//
//        if (typeof self.contactManager.dialogs[jid_id] != 'undefined')
//        {
//            var dialog = self.contactManager.getDialog(jid_id);
//            dialog.hideComposing();
//        }

        var from = message.from;
        var to = message.to;
        var timestamp = message.timestamp;

        var dialog;
        // Message from my other resources
        if( message.from == OW_InstantChat.Details.node){


            if (im_debug_mode){console.log('Application.onMessage: Message from other tab '+from+' to '+to);}

            dialog = self.contactManager.getDialog(to);

            if (!$('#'+to).data('isOpened'))
            {
                dialog.showTab();

                if (!$('#'+to).data('isActive'))
                {
                    dialog.open();
                }
            }
            else
            {
                dialog.showTimeBlock();
                dialog.write({
                    message: message.message,
                    timestamp: timestamp,
                    sender: from
                });
            }

        }
        else{

            // Message to other contact
            if (im_debug_mode){console.log('Application.onMessage: Message '+from+' to '+to);}

            dialog = self.contactManager.getDialog(from);

            if( $('#'+from).data('isOpened') )
            {
                dialog.showTimeBlock();
                dialog.write({
                    message: message.message,
                    timestamp: timestamp,
                    sender: from
                });
            }

            var focusedElement = $("*:focus");

            if ( focusedElement.length == 0 )
            {
                if (im_debug_mode){console.log('Application.onMessage: Dialog is closed or not activated');}
                dialog.notifyOnNewMessage(message.message);
            }

            if ( focusedElement.length > 0 )
            {
                if (!$.contains(dialog.view.dialog[0], focusedElement[0]))
                {
                    if (im_debug_mode){console.log('Application.onMessage: Dialog is closed or not activated');}
                    dialog.notifyOnNewMessage(message.message);
                }
            }

        }

        dialog.view.item.data('lastMessageTimestamp', timestamp);

        return true;
    }

    this.sendGetUserInfoPackage = function() {
        if (self.userInfoPackageEmpty != true)
        {
            console.debug('Application.sendGetUserInfoPackage: Get User Info Package');
            $.post(im_getUserInfoUrl, {
                nodes: self.userInfoPackage
            }, function(data){

                if ( typeof data != 'undefined' && data != null )
                {
                    $('.ow_chat_list .ow_chat_preloader').remove();
                    OW.addScroll($('.ow_chat_list'));
                    $.each(data, function(){

                        if ( self.node != this.node )
                        {
                            OW.trigger('userInfoReceived', this);
                            delete self.userInfoPackage[this.node];
                        }

                    })
                    self.userInfoPackageEmpty = true;
                }
                else
                {
                    $('.ow_chat_list .ow_chat_preloader').remove();
                }
            }, 'json');
        }
    }

    this.setOnlineList = function(onlineList) {

    }

    this.setSoundEnabled = function( soundEnabled ) {
        im_createCookie('im_soundEnabled', (soundEnabled)?1:0, 1);
    }

};

/*                          Dialog                              */
OW_InstantChat.Dialog_Model = function (node, domain)
{
    this.node = node;
    this.domain = domain;
    this.jid = node+'@'+domain;
    this.unreadMessages = 0;
    this.newMessageTimeout = 0;

};
OW_InstantChat.Dialog_Model.prototype =
{
    sendComposing: function(composing){
        
        if (!composing) {
            //TODO
        }
    },

    sendMessage: function(body, timestamp){

        var self = this;

        var data = {
            'to': self.node,
            'message': body
        };

        OW_InstantChat_App.connection.sendInProcess = true;

        $.ajax({
            'type': 'POST',
            'url': window.ajaximLogMsgUrl,
            'data': data,
            'success': function(data){

                $('#'+self.node).data('lastMessageTimestamp', data.timestamp);
                $('#'+timestamp).attr('id', data.timestamp);
                if (im_debug_mode){console.log('OW_InstantChat.Dialog_Model.sendMessage: Change '+timestamp+' to '+data.timestamp);}

                OW_InstantChat_App.connection.sendInProcess = false;
            },
            'error': function(){
                OW_InstantChat_App.connection.sendInProcess = false;
                if (im_debug_mode){console.log('OW_InstantChat.Dialog_Model.sendMessage: Error during sending');}
            },
            'complete': function(){
                OW_InstantChat_App.connection.sendInProcess = false;
            },
            'dataType': 'json'
        });

    },

    //TODO test this function
    splitLongMessage: function(text){
        var strings = text.split(' ');
        var str = '';
        for (var i = 0; i < strings.length; i++)
        {
            if ( strings[i].length > 30  )
            {
                str = strings[i].substr(0, 30)+' '+this.splitLongMessage(strings[i].substr(30, strings[i].length-1));
            }
            else
            {
                str += strings[i];
            }
        }

        return str;

    }
}
OW_InstantChat.Dialog_View = function(model)
{
    this.model = model;
    this.id = null;
    this.box = null;
    this.avatar = null;
    this.usernameButton = null;
    this.closeButton = null;
    this.textarea = null;
    this.username = null;
    this.myAvatar = null;
    this.dialog = null;
    this.messageListWrapper = null;
    this.dialogWindowHeight = null;

    this.item = $('#'+this.model.node);
}
OW_InstantChat.Dialog_View.prototype =
{
    /* Properties */
    smallItem: function() { return $('#small_'+this.model.node); },
    chatSelectorUnreadMessagesCountWrapper: function() { return $('#small_'+this.model.node+' .ow_count_bg'); },
    chatSelectorUnreadMessagesCount: function() { return  $('#small_'+this.model.node+' .ow_count'); },

    unreadMessagesCountWrapper: function() { return $('#'+this.model.node+' .ow_count_wrap'); },
    unreadMessagesCount: function() { return $('#'+this.model.node+' .ow_count'); },

    appendTimeBlock: function(timeBlock, message){

        $('.ow_time_text', timeBlock).html(message);

        this.messageList.append(timeBlock);

        this.scrollDialog();

    },

    clearUnreadMessages: function() {

        this.unreadMessagesCountWrapper().css('display', 'none');
        this.unreadMessagesCount().html(0);

        this.chatSelectorUnreadMessagesCountWrapper().css('display', 'none');
        this.chatSelectorUnreadMessagesCount().html(0);

        this.model.unreadMessages = 0;

        $('.ow_chat_notification_'+this.model.node).each(function(){
            $(this).remove();
        });

        if ( OW_InstantChat_App.newMessageTimeout !== 0 )
        {
            clearInterval( OW_InstantChat_App.newMessageTimeout );
            document.title = im_oldTitle;

            OW_InstantChat_App.newMessageTimeout = 0;
        }

        if ( this.model.newMessageTimeout !== 0 )
        {
            clearInterval( this.model.newMessageTimeout );
            if ( this.dialog.hasClass('ow_chat_new_message') )
            {
                this.dialog.removeClass('ow_chat_new_message');
            }

            this.model.newMessageTimeout = 0;
        }

        this.itemLink.removeClass('ow_active');
        this.box.removeClass('ow_chat_new_message_notification');
    },

    create: function(){
        var self = this;

        this.dialog = $("#ow_chat_dialog_proto").clone();
        this.dialog.addClass('ow_chat_dialog');
        this.dialog.attr('id', 'main_tab_contact_' + this.model.node);
        this.dialog.data('jid_id', this.model.node);

        this.id = this.dialog.attr('id');
        
        this.messageListWrapper = $('.ow_chat_in_dialog', this.dialog);

        this.dialogWindowHeight = this.messageListWrapper.height();

        this.messageList = $('div.ow_dialog_items_wrap', this.messageListWrapper);

        this.avatar = $('.ow_chat_in_item_author_href .ow_chat_in_item_photo img', this.dialog);
        this.myAvatar = $('.ow_chat_message_block .ow_chat_in_item_photo img', this.dialog);
        this.username = $('.ow_chat_in_item_author', this.dialog);

        this.usernameButton = $('.ow_author_block a.ow_chat_in_item_author_href', this.dialog);

        this.minimizeMaximizeButton = $('.MinimizeMaximizeButton', this.dialog);
        this.closeButton = $('.ow_btn_close', this.dialog);

        this.textarea = $('.ow_chat_message textarea', this.dialog);

        this.item = $('#'+this.model.node);

        this.itemLink = $('a.ow_chat_item', this.item);

        this.puller = $('.ow_puller', this.dialog);

        this.puller.css('position','absolute');

        this.puller.draggable({

            disabled: true,
            axis: "y",
            cursor: 'row-resize',
            drag: function(event, ui){
                if (ui.position.top < 0)
                {
                    if ( self.messageListWrapper.height() > $(window).innerHeight() * 0.8  )
                    {
                        return;
                    }
                }
                self.messageListWrapper.height( self.dialogWindowHeight - ui.position.top );
            },
            stop: function(event, ui){
                if ( self.messageListWrapper.height() > $(window).innerHeight() * 0.8  )
                {
                    self.messageListWrapper.height( $(window).innerHeight() * 0.8 );
                }
                self.puller.css('top','-10px');
                OW.updateScroll(self.messageListWrapper);
            },
            start: function(event, ui){
                self.dialogWindowHeight = self.messageListWrapper.height();
            }

        });

        this.historyPreloader = $('.ow_chat_preloader', this.dialog);

        this.lastMessageSentLabel = $('#ow_chat_prototypes span#ow_last_message_sent_label');

        this.setData(this.item.data());

        $('.ow_chat_dialog_wrap').prepend(this.dialog);

        this.box = $('#'+this.id+', #'+this.id+' *');
        this.textareaHeight = this.textarea.css('height');
    },

    createNewMessageNotification: function(message){

        var notification_container = $('#ow_chat_notification_proto').clone();
            notification_container.removeAttr('id');
            notification_container.addClass('ow_chat_notification_'+this.model.node);

        $('.ow_chat_in_item_photo img', notification_container).attr('src', this.item.data('user_avatar_src'));
        $('.ow_chat_in_item_photo img', notification_container).attr('alt', this.item.data('username'));
        var message = htmlspecialchars( message, 'ENT_QUOTES');

        $('span.ow_chat_in_item_text', notification_container).html( message );

        $('.ow_chat_notification_wrap').prepend(notification_container);

        return notification_container;
    },

    disablePuller: function(){
        this.puller.draggable("disable");
    },

    enablePuller: function() {
        this.puller.draggable("enable");
    },

    getNewTimeBlock: function(){
        return $('#ow_time_block_proto').clone();
    },

    hideComposing: function(){
        $('#message_composing', this.messageList).remove();
        this.item.data('composing', false);
        //this.scrollDialog();
    },

    hide: function(){

        this.dialog.removeClass('ow_active');

        this.disablePuller();

        OW.updateScroll(this.messageListWrapper);

        return this;
    },

    hideTab: function(){

        this.messageListWrapper.hide();
        this.dialog.hide();
        this.smallItem().hide();

        this.messageListWrapper.remove();
        this.dialog.remove();
    },

    isVisible: function(){
        return this.dialog.hasClass('ow_open');
    },

    moveToChatSelector: function() {

        //OW_InstantChat_App.chatSelector.show();
        this.item.data('isHidden', true);

        this.smallItem().show();
        this.smallItem().addClass('ow_dialog_in_selector');

        this.dialog.addClass('ow_hidden');
        this.dialog.removeClass('ow_open');

    },

    onOpen: function() {
        this.item.data('isActive', true);
        this.item.data('isOpened', true);

        this.enablePuller();

        this.dialog.addClass('ow_active');

        this.textarea.focus();

        this.scrollDialog();
    },

    onShowTab: function(){

        this.item.data('isOpened', true);

        this.removeFromChatSelector();

    },

    removeFromChatSelector: function() {
        this.item.data('isHidden', false);

        this.smallItem().hide();
        this.smallItem().removeClass('ow_dialog_in_selector');

        this.dialog.removeClass('ow_hidden');
        this.dialog.addClass('ow_open');

        this.scrollDialog();
    },

    removePreloader: function(){
        this.historyPreloader.remove();
        OW.addScroll(this.messageListWrapper);
    },

    showTab: function(omit_last_message){
        this.dialog.addClass('ow_open');
        return this;
    },

    showComposing: function(){
        var self = this;

        if (this.item.data('composing'))
        {
            return;
        }

        var message_composing_container = $('#ow_dialog_item_proto').clone();
        message_composing_container.attr('id', 'message_composing');
        $('.ow_dialog_item', message_composing_container).addClass('odd');

        this.messageList.append(message_composing_container);
        this.scrollDialog();

        this.item.data('composing', true);

        // Autohide after sometime
        this.model.showComposingTimeout = setTimeout(function(){
            self.hideComposing();
        }, 2000); // TODO Select autohide time

    },

    scrollDialog: function(){
        OW.updateScroll(this.messageListWrapper);

        var jsp = this.messageListWrapper.data('jsp');
        if (typeof jsp != 'undefined' && jsp != null)
        {
            jsp.scrollToY( $('.jspPane', this.messageListWrapper).outerHeight() - this.messageListWrapper.outerHeight() );
        }

    },

    setData: function(data){

        this.avatar.attr('src', data['user_avatar_src']);
        this.avatar.attr('alt', data['username']);
        this.avatar.attr('title', data['username']);
        this.usernameButton.attr('href', data['user_url']);
        this.username.html( data['username'] );
        this.myAvatar.attr('src', OW_InstantChat.Details.avatar);
    },

    toggle: function(){
        if(!this.isVisible())
        {
            return this.open();
        }
        else
        {
            return this.hide();
        }
    },

    write: function(msg, css_class){

        var css_class = css_class || null;

        if (msg.timestamp  ==  $('#'+this.model.node).data('lastMessageTimestamp') && css_class == null)
        {
            return;
        }

        msg.timestamp = parseInt(msg.timestamp);

        var canAppend = false;

        if ((msg.timestamp - $('#'+this.model.node).data('lastMessageTimestamp')) < 3000 ) //TODO Select time interval between two messages from one user
        {
            var message_dialog_item = $('#'+$('#'+this.model.node).data('lastMessageTimestamp')).parent().parent();
            if ( (message_dialog_item.hasClass('even') && ( typeof msg.sender == 'undefined' || msg.sender == OW_InstantChat_App.node ) ) || ( message_dialog_item.hasClass('odd') && ( typeof msg.sender != 'undefined' && msg.sender != OW_InstantChat_App.node ) ) )
            {
                canAppend = true;
            }
        }

        var message_container = null;

        if (canAppend)
        {
            message_container = $('#'+$('#'+this.model.node).data('lastMessageTimestamp')).parent().parent().parent();
        }
        else
        {
            message_container = $('#ow_dialog_item_proto').clone();
            message_container.removeAttr('id');
        }

        if ( typeof msg != 'undefined' && typeof msg.message != 'undefined' )
        {
            if (canAppend)
            {
                var html = htmlspecialchars(msg.message, 'ENT_QUOTES');
                html = $("<span>"+html+"</span>").autolink().html();
                $('p', message_container).append(' '+html);
            }
            else
            {
                $('p', message_container).html( htmlspecialchars(msg.message, 'ENT_QUOTES') );
                $('p', message_container).autolink();
            }


            $('#'+this.model.node).data('lastMessageTimestamp', msg.timestamp);
            this.model.lastMessageBody = msg.message;
            $('p', message_container).attr('id', $('#'+this.model.node).data('lastMessageTimestamp') );

            if( typeof msg.sender == 'undefined' || msg.sender == OW_InstantChat_App.node ){
                $('div.ow_dialog_item', message_container).addClass('even');
            }
            else{
                $('div.ow_dialog_item', message_container).addClass('odd');
            }
        }

        if (css_class != null)
        {
            $('div.ow_dialog_item', message_container).addClass(css_class);
        }

        this.messageList.append(message_container);
        this.scrollDialog();


        if ( parseInt(im_readCookie('im_soundEnabled')) && css_class == null)
        {
            var audioTag = document.createElement('audio');
            if (!(!!(audioTag.canPlayType) && ("no" != audioTag.canPlayType("audio/mp3")) && ("" != audioTag.canPlayType("audio/mp3")) && ("maybe" != audioTag.canPlayType("audio/mp3")) )) {
                AudioPlayer.embed("im_sound_player_audio", {
                    soundFile: im_soundUrl,
                    autostart: 'yes'
                });
            }
            else
            {
                $('#im_sound_player_audio')[0].play();
            }
        }

        return this;
    },

    updateSmallItemUnreadMessagesCounter: function(){
        this.chatSelectorUnreadMessagesCountWrapper().css('display', 'block');
        this.chatSelectorUnreadMessagesCount().html(this.model.unreadMessages);
    },

    updateUnreadMessagesCounter: function(){

        this.unreadMessagesCount().html(this.model.unreadMessages);
        this.unreadMessagesCountWrapper().css('display', 'block');

        this.itemLink.addClass('ow_active');
    }

}
OW_InstantChat.Dialog_Controller = function(node, domain)
{
    var self = this;

    this.model = new OW_InstantChat.Dialog_Model(node, domain);
    this.view = new OW_InstantChat.Dialog_View(this.model, node);

    this.node = node;
    this.domain = domain;

    this.view.create();

    this.view.box.click(function(){

        if ( self.view.box.hasClass('ow_chat_new_message_notification') )
        {
            if (im_debug_mode){console.log('Dialog_Controller: Activate '+self.view.id);}
            self.clearUnreadMessagesCounters();
        }

        $('.ow_chat_dialog.ow_chat_dialog_active').removeClass('ow_chat_dialog_active');
        self.view.dialog.addClass('ow_chat_dialog_active');
    });


    this.view.minimizeMaximizeButton.click(function(){

            if ( self.view.item.data('isActive') )
            {
                self.hide();
                OW.trigger('allDialogsCollapsed');
            }
            else
            {
                self.open();
            }

            return false;
    });
 
    this.view.closeButton.click(function(){
        self.hideTab();
        OW.trigger('allDialogsClosed');
    });

    this.view.textarea.keyup(function(ev){

        if ( self.view.box.hasClass('ow_chat_new_message_notification') )
        {
            if (im_debug_mode){console.log('Dialog_Controller: KeyUp Activate '+self.view.id);}
            self.clearUnreadMessagesCounters();
        }

        var textarea = $(this);
        var currentRows = 0;
        if (typeof textarea.attr('rows') != 'undefined')
        {
            parseInt(textarea.attr('rows'));
        }
        var isScrollable = textarea.scrollTop();
        if(isScrollable) {
            textarea.attr('rows', currentRows+2);
            $(this).css('height', $(this).outerHeight()+parseInt($(this).css('line-height')) *2 );
        }

//        if (this.value.length > 0 && this.value.length % 27 == 0){
//            $(this).css('height', $(this).height()+parseInt($(this).css('line-height')) );
//        }
    });

    this.view.textarea.keypress(function (ev) {

        if (ev.which === 13 && !ev.shiftKey)
        {
            ev.preventDefault();

            var body = $(this).val();

            if ( $.trim(body) == '')
                return;

            //self.model.sendMessage(body, (new Date()).getTime() / 1000);
            self.sendMessage(body, (new Date()).getTime());

            $(this).css('height', self.view.textareaHeight);
            $(this).val('');

            self.view.item.data('composing', false);
        }
        else if (ev.which === 13 && ev.shiftKey)
            {
                $(this).css('height', $(this).outerHeight()+parseInt($(this).css('line-height')) );
            }
            else
            {
                var composing = self.view.item.data('composing');
                if (!composing)
                {
                    self.model.sendComposing(composing);
                    self.view.item.data('composing', true);

                    var sendComposingTimeout = setTimeout(function(){
                        self.view.item.data('composing', false);
                    }, 2000); // TODO Select autohide time
                }
            }
    });

    self.view.item.data('isLoaded', true);

    OW.bind('statusUpdated', function(data){
        if (data.node != self.node)
        {
            return;
        }

        self.updateStatus(data.status);

    });

}
OW_InstantChat.Dialog_Controller.prototype =
{

    clearUnreadMessagesCounters: function(){

        OW.trigger('clearUnreadMessagesCounters', {unreadMessages: this.model.unreadMessages});

        this.view.clearUnreadMessages();
    },

    createNewMessageNotification: function(message){

        var self = this;
        var notification_container = this.view.createNewMessageNotification(message);

        $('a.ow_btn_close', notification_container).click(function(){

            notification_container.remove();

        });

        $('a.btn4_open', notification_container).click(function(){

            var dialog = OW_InstantChat_App.contactManager.getDialog(self.model.node);
            dialog.showTab().open();

            $('.ow_chat_notification_'+self.model.node).remove();
        });

        setTimeout(function(){
            notification_container.fadeOut('slow', function(){
                notification_container.remove();
            })
        }, 7000); // TODO Select autohide time
    },

    hide: function(){
        this.view.hide();
        im_createCookie('contact_tab_'+this.model.node, 0, 1);
        this.view.item.data('isActive', false);
    },

    hideComposing: function(){
        this.view.hideComposing();
    },

    hideTab: function(){
        this.view.hideTab();

        im_eraseCookie('contact_tab_'+this.model.node);
        this.view.item.data('isActive', false);
        this.view.item.data('isOpened', false);
        this.view.item.data('isHidden', false);
        this.view.item.data('isLogLoaded', false);

        OW.trigger('dialogClosed', {node: this.model.node});
    },

    loadHistory: function(omit_last_message){

        var self = this;

        self.historyLoadInProgress = true;
        $.ajax({
            url: ajaximGetLogUrl,
            type: 'POST',
            data: {
                userId: this.node,
                lastMessageTimestamp: self.view.item.data('lastMessageTimestamp'),
                omit_last_message: omit_last_message
            },
            success: function(data){

                self.view.removePreloader();

                if ( data.length > 0 )
                {
                    //var lastMessageTimeString = '';
                    var date = new Date();

                    $(data).each(function(){
    
                        var sender = OW_InstantChat.Details.node;
                        if ( parseInt(this.from) != sender )
                        {
                            sender = this.from;
                        }

                        var timestamp = this.timestamp;

                        var msg = {
                            message: this.message,
                            timestamp: timestamp,
                            sender: sender
                        }

                        self.write(msg, 'history');

                        self.view.item.data('lastMessageTimestamp', timestamp);

                    });

                    if (self.view.item.data('lastMessageTimestamp') != null && self.view.item.data('lastMessageTimestamp') != 0)
                    {
                        var date = new Date(self.view.item.data('lastMessageTimestamp'));
                        self.showTimeBlock(self.view.lastMessageSentLabel.html()+' '+ $.timeago(date));
                    }

                }

                self.view.item.data('isLogLoaded', true);
                OW.trigger(self.model.node+'_isLogLoaded');

                self.historyLoadInProgress = false;

            },
            error: function(){
                if (im_debug_mode){console.log('Dialog_Controller.loadHistory: History Load Error');}
                self.view.removePreloader();
                self.historyLoadInProgress = false;
            },
            complete: function(){
                self.historyLoadInProgress = false;
            },
            dataType: 'json'
        });

    },

    moveToChatSelector: function(){
        this.view.moveToChatSelector();
        OW_InstantChat_App.contactManager.updateChatSelector();
    },

    notifyOnNewMessage: function(message){

        var self = this;

        if (typeof message == 'undefined')
        {
            message = '';
        }

        this.model.unreadMessages++;

        if ( !self.view.item.data('isOpened') )
        {
            OW_InstantChat_App.contactManager.totalUnreadMessages++;
            OW.trigger('updateTotalCounter', {'classname': 'ow_count_active', 'action': 1 } );

            this.view.updateUnreadMessagesCounter();

            /**/
            var new_message_label = $("#ow_new_message_label").html();

            if ( OW_InstantChat_App.newMessageTimeout === 0  )
            {
                OW_InstantChat_App.newMessageTimeout = setInterval(function() {
                    document.title = document.title == new_message_label ? im_oldTitle : new_message_label;
                }, 1000);
            }

            if (OW_InstantChat_App.contactManager.isActiveMode())
            {
                this.createNewMessageNotification(message);
            }

        }
        else
        {
            OW_InstantChat_App.contactManager.chatSelectorUnreadMessages++;
            this.view.updateSmallItemUnreadMessagesCounter();
            OW.trigger('updateChatSelectorUnreadMessagesCounter');

            if ( self.view.item.data('isHidden') )
            {
                OW.trigger('showChatSelectorUnreadMessagesCounter');
            }
        }

        if ( self.view.item.data('isOpened') && self.model.newMessageTimeout === 0  )
        {
            self.view.box.addClass('ow_chat_new_message_notification');

            self.model.newMessageTimeout = setInterval(function() {

                if ( self.view.dialog.hasClass('ow_chat_new_message') )
                {
                    self.view.dialog.removeClass('ow_chat_new_message');
                }
                else
                {
                    self.view.dialog.addClass('ow_chat_new_message');
                }

            }, 1000); //TODO Select time of switching
        }

        return self;
    },

    removeFromChatSelector: function() {
        this.view.removeFromChatSelector();
        OW_InstantChat_App.contactManager.updateChatSelector();
    },

    onOpen: function(){
        //if ( this.view.box.hasClass('ow_chat_new_message_notification') )
        //{
        this.clearUnreadMessagesCounters();
        //}
        this.view.onOpen();
        im_createCookie('contact_tab_'+this.model.node, 1, 1);
    },

    onShowTab: function(omit_last_message){

        var self = this;

        this.view.onShowTab();

        OW.trigger('dialogOpened', {node: this.model.node});
        OW.trigger(self.node+'_tabOpened');
        if (im_debug_mode){console.log('Dialog_Controller.onShowTab: '+this.model.node+'_tabOpened');}

        if ( !this.view.item.data('isLogLoaded') )
        {
            this.loadHistory(omit_last_message);
        }
    },

    open: function(){

        var self = this;

        if (this.view.item.data('isOpened'))
        {
            this.onOpen();
        }
        else
        {
            if (im_debug_mode){console.log('Dialog_Controller.open: bind to tabOpened');}
            OW.bind(self.node+'_tabOpened', function(){
                self.onOpen();
                OW.unbind(self.node+'_tabOpened');
            });
        }

        return this;
    },

    sendMessage: function(body, timestamp){
        if ( OW_InstantChat_App.connection == null )
            return;

        var self = this;
        //this.model.sendMessage(body, timestamp);

        var data = {
            'to': self.node,
            'message': body
        };

        OW_InstantChat_App.connection.sendInProcess = true;

        $.ajax({
            'type': 'POST',
            'url': window.ajaximLogMsgUrl,
            'data': data,
            'success': function(data){

                self.showTimeBlock();
                self.write({
                    message: body,
                    timestamp: data.timestamp,
                    sender: OW_InstantChat.Details.node
                });

                $('#'+self.node).data('lastMessageTimestamp', data.timestamp);
                //$('#'+timestamp).attr('id', data.timestamp);
                if (im_debug_mode){console.log('OW_InstantChat.Dialog_Model.sendMessage: Change '+timestamp+' to '+data.timestamp);}

                OW_InstantChat_App.connection.sendInProcess = false;
            },
            'error': function(){
                OW_InstantChat_App.connection.sendInProcess = false;
                if (im_debug_mode){console.log('OW_InstantChat.Dialog_Model.sendMessage: Error during sending');}
            },
            'complete': function(){
                OW_InstantChat_App.connection.sendInProcess = false;
            },
            'dataType': 'json'
        });
    },

    setData: function(data){
        this.view.setData(data);
    },

    showComposing: function(){
        this.view.showComposing();
    },

    showTab: function(omit_last_message){

        if (typeof omit_last_message == 'undefined')
            omit_last_message = 0;

        var self = this

        if (this.view.item.data('isOpened'))
        {
            if (this.view.item.data('isHidden'))
            {
                var dialogs = $('.ow_chat_dialog.ow_open');
                var dialog = dialogs.first();
                var box = OW_InstantChat_App.contactManager.getDialog($(dialog).data('jid_id'));
                box.moveToChatSelector();

                box = OW_InstantChat_App.contactManager.getDialog(this.model.node);
                box.removeFromChatSelector();
                box.clearUnreadMessagesCounters();
            }

            return this;
        }

        OW_InstantChat_App.contactManager.moveToActiveMode();

        this.view.showTab(omit_last_message);

        if ( this.view.item.data('isLoaded') )
        {
            this.onShowTab(omit_last_message);
        }
        else
        {
            OW.bind(self.node+'_isLoaded', function(){

                self.view.removePreloader();

                self.onShowTab(omit_last_message);

                OW.unbind(self.node+'_isLoaded');
            });
        }

        return this;
    },

    updateStatus: function(status){

        var self = this;

        if (status == 'offline')
        {
            //TODO if dialog is opened and user is offline, show some message
            self.clearUnreadMessagesCounters();
            self.view.item.css('opacity', 0.5);
            self.view.textarea.attr('disabled', 'disabled');
            self.view.item.addClass("ow_chat_offline");
            self.showTimeBlock($('#ow_language_user_went_offline').html().replace("{$displayname}", self.view.item.data('username')), 'ow_chat_offline');
            self.view.item.removeClass("ow_chat_online");
            if (self.view.item.data('isOpened'))
            {
                self.view.dialog.addClass('ow_chat_offline');
            }
            self.view.smallItem().removeClass('ow_chat_online');
            var removeTimeout = setTimeout(function(){
                //self.view.item.remove();
                //self.view.smallItem().remove();
                self.view.item.hide();
                self.view.smallItem().hide();
                OW.trigger('removeItem', {node: self.model.node});
            }, 10000);
        }
        else if (status == 'online')
        {
            self.view.item.removeClass("ow_chat_offline");
            self.view.item.show();
            self.view.item.css('opacity', 1);
            self.view.item.addClass("ow_chat_online");
            if (self.view.item.data('isOpened'))
            {
                self.view.dialog.removeClass('ow_chat_offline');
            }
            self.view.smallItem().addClass('ow_chat_online');
            self.view.smallItem().show();
            self.view.textarea.removeAttr('disabled');
            $('.ow_time_block.ow_chat_offline', self.view.dialog).parent().remove();
            
            OW.trigger('removeItem', {node: self.model.node});
        }
        else
        {
            this.model.status = status;
        }
    },

    write: function(msg, css_class){

        this.view.write(msg, css_class);

        return this;
    },

    showTimeBlock: function(message, addClass){

        if ($('#time_block_'+this.view.item.data('lastMessageTimestamp')).length > 0 && typeof addClass == 'undefined')
        {
            return this;
        }

        var timeBlock = this.view.getNewTimeBlock();

        timeBlock.attr('id', 'time_block_'+this.view.item.data('lastMessageTimestamp'));

        if (typeof addClass != 'undefined')
        {
            $('.ow_time_block', timeBlock).addClass(addClass);
        }

        if (typeof message == 'undefined')
        {
            var now = new Date();
            now = now.getTime();
            var lastMessageTimestamp = this.view.item.data('lastMessageTimestamp');
            if ( now - lastMessageTimestamp > 300 * 1000 ) // TODO Select time period
            {
                var time = new Date();
                var minutes = time.getMinutes();
                if ( time.getMinutes() < 10 )
                {
                    minutes = '0'+time.getMinutes();
                }
                message=time.getHours()+':'+minutes;
            }
            else
                return this;
        }

        this.view.appendTimeBlock(timeBlock, message);

        return this;
    }


}
/*                      End Dialog                              */


/*                          Contact Manager                              */
OW_InstantChat.ContactManager_View = function(){

    var self = this;

    this.contactListWrapper = $('.ow_chat_list');

    this.chatSelector = $('.ow_chat_selector');
    this.chatSelectorButton = $('.ow_btn_dialogs');
    this.chatSelectorList = $('.ow_chat_selector_list');
    this.chatSelectorRosterContainer = $('.ow_chat_selector_items');

    this.chatSelectorTotalUnreadMessagesCountWrapper = $('.ow_chat_selector .ow_chat_block .ow_selector_panel .ow_count_wrap');

    this.hiddenContactsCountWrapper = $('.ow_chat_selector .ow_chat_block .ow_selector_panel .ow_dialog_count');

    this.mainWindow = $('.ow_chat .ow_chat_block_wrap .ow_chat_block');

    this.minimizeButton = $('.btn2_panel');

    this.puller = $('.ow_chat .ow_puller');
    this.inDragging = false;

    this.puller.draggable({

        disabled: true,
        axis: "y",
        cursor: 'row-resize',
        drag: function(event, ui){
            //if (im_debug_mode){console.log(ui.originalPosition.top+' '+ui.position.top+' '+ui.offset.top);}
            if (ui.position.top < 0)
            {
                if ( $('.ow_chat_list').height() > $(window).innerHeight() * 0.8  )
                {
                    return;
                }
            }

            $('.ow_chat_list').height( self.mainWindowHeight - ui.position.top );
        },
        stop: function(event, ui){
            if ( $('.ow_chat_list').height() > $(window).innerHeight() * 0.8  )
            {
                $('.ow_chat_list').height( $(window).innerHeight() * 0.8 );
            }
            self.puller.css('top','-10px');

            if ( $('.ow_chat_list ul').height() > $('.ow_chat_list').height() )
            {
                OW.addScroll($('.ow_chat_list'));
            }
            else
            {
                $('.ow_chat_list').width(245);
            }

            self.inDragging = false;
        },
        start: function(event, ui){
            self.inDragging = true;
            OW.removeScroll($('.ow_chat_list'));
            self.mainWindowHeight = $('.ow_chat_list').height();
        }

    });

    this.rosterWrapper = $('.ow_chat_list ul');
    this.settingsWrapper = $('.ow_chat_settings');
    this.settingsButton = $('.ow_btn_settings');
    this.settingsForm = $("form[name='im_user_settings_form']");
    this.soundSettings = $('#im_enable_sound');

    this.totalUserOnlineCount = $('.TotalUserOnlineCount');
    this.totalUserOnlineCountBackground = $('.TotalUserOnlineCountBackground');

    this.notificationWrapper = $('.ow_chat, .ow_chat_notification_wrap');

    this.createItem = function(info){

        var item = $('#ow_chat_list_proto ul li').clone();
        item.attr('id', info.node);
        item.data(info.data);
        item.addClass('ow_chat_online');

        this.rosterWrapper.append(item);

        OW.updateScroll($('.ow_chat_list'));

        return item;
    }

    this.createSmallItem = function(info){
        var smallItem = $('#ow_chat_selector_items_proto li').clone();
        smallItem.attr('id', 'small_'+info.node);
        smallItem.data('jid_id', info.node);
        smallItem.addClass('ow_chat_online');

        this.chatSelectorRosterContainer.append(smallItem);

        return smallItem;
    }

    this.hideChatSelector = function(){
        this.chatSelectorButton.removeClass('ow_active');
        this.chatSelectorList.addClass('ow_hidden');
    }

    this.hideSettings = function(){
        this.settingsButton.removeClass('ow_active');
        this.settingsWrapper.addClass('ow_hidden');
    }

    this.getChatSelectorItem = function(node){
        return $('#' + 'small_'+node);
    }

    this.getItem = function(node){
        return $('#' + node);
    }

    this.getRosterLength = function(){

        return $('.ow_chat_list ul li.ow_chat_online').length;
    }

    this.maximize = function(){
        this.mainWindow.addClass('ow_active');
        if (this.mainWindow.hasClass('ow_compact'))
        {
            this.mainWindow.removeClass('ow_compact');
        }
        this.puller.draggable("enable");
        //this.puller.addClass('ow_im_draggable');
    }

    this.minimize = function(){
        this.mainWindow.removeClass('ow_active');
        this.puller.draggable("disable");
        //this.puller.removeClass('ow_im_draggable');
    }

    this.moveToActiveMode = function(){

        if ( self.mainWindow.hasClass('ow_compact') && self.mainWindow.hasClass('ow_active') )
        {
            var speed = 'normal';

            self.mainWindow.removeClass('ow_compact');
            $('.ow_chat_cont').animate({
                right: 0
            }, speed);

            $('.ow_chat_notification_wrap').removeClass('ow_compact');

            $('.ow_chat_notification_wrap').animate({
                right: -3
            }, speed);

            if ( $('.ow_chat_list ul').height() > $('.ow_chat_list').height() )
            {
                OW.updateScroll($('.ow_chat_list'));
            }

            im_createCookie('chatBlockCompact', 0, 1);

            self.puller.draggable("enable");
        }
    }

    this.moveToCompactMode = function(force){

        var force = force || false;

        var focusedElement = $("input#im_find_contact:focus");

        if ( focusedElement.length != 0 )
        {
            return;
        }

        if (force)
        {
            this.mainWindow.addClass('ow_compact');
            this.mainWindow.addClass('ow_active');
            $('.ow_chat_cont').css('right', -204);

            return;
        }

        if ( !self.inDragging && !self.mainWindow.hasClass('ow_compact') && (self.mainWindow.hasClass('ow_active') || force)
            && ( $('.ow_chat_dialog.ow_open').length == 0 || $('.ow_chat_dialog.ow_active').length == 0 ) )
        {
            var speed = 'normal';
            var position_right = -204;

            $('.ow_chat_cont').animate({
                right: position_right
            }, speed, function(){
                self.mainWindow.addClass('ow_compact');
                //$('.ow_chat_list').width(44);
            });

            $('.ow_chat_notification_wrap').animate({
                right: position_right
            }, speed, function(){
                $(this).addClass('ow_compact');

            });

//                OW.updateScroll($('.ow_chat_list'));
            im_createCookie('chatBlockCompact', 1, 1);

            self.puller.draggable("disable");
        }
    }

    this.showChatSelector = function(){
        this.chatSelectorButton.addClass('ow_active');
        this.chatSelectorList.removeClass('ow_hidden');
    }

    this.showSettings = function(){
        this.settingsButton.addClass('ow_active');
        this.settingsWrapper.removeClass('ow_hidden');
    }

    this.sortRoster = function(){
        if (im_debug_mode){console.log('ContactManager_View.sortRoster');}
        /* Sort Contacts*/
        $($('.ow_chat_list ul li').get().reverse()).each(function(outer) {
            var sorting = this;
            $($('.ow_chat_list ul li').get().reverse()).each(function(inner) {
                if($('.ow_chat_in_item_author', this).text().toLowerCase().localeCompare($('.ow_chat_in_item_author', sorting).text().toLowerCase()) > 0) {
                    this.parentNode.insertBefore(sorting.parentNode.removeChild(sorting), this);
                }
            });
        });

        /* Sort Chat Selector */
        $($('.ow_chat_selector_items li').get().reverse()).each(function(outer) {
            var sorting = this;
            $($('.ow_chat_selector_items li').get().reverse()).each(function(inner) {
                if($('a', this).text().toLowerCase().localeCompare($('a', sorting).text().toLowerCase()) > 0) {
                    this.parentNode.insertBefore(sorting.parentNode.removeChild(sorting), this);
                }
            });
        });
    }

    this.updateChatSelector = function(){
        var count = $('.ow_chat_selector_items li.ow_dialog_in_selector.ow_chat_online').length;
        this.hiddenContactsCountWrapper.html(count);
        if (count > 0)
        {
           this.chatSelector.show();
        }
        if (count == 0)
        {
           this.chatSelector.hide();
        }
    }

    this.hideChatSelectorUnreadMessagesCounter = function(){
        $('.ow_chat_selector .ow_chat_block .ow_selector_panel .ow_count_wrap').css('display', 'none');
    }

    this.showChatSelectorUnreadMessagesCounter = function(){
        $('.ow_chat_selector .ow_chat_block .ow_selector_panel .ow_count_wrap').css('display', 'block');
    }

    this.updateChatSelectorUnreadMessagesCounter = function(count){
        $('.ow_chat_selector .ow_chat_block .ow_selector_panel .ow_count_wrap .ow_count_bg .ow_count').html(count);
    }

    this.updateTotalCounter = function(count, classname, action)
    {
        if (classname != null && action != null)
        {
            if (action == 1)
            {
                this.totalUserOnlineCountBackground.addClass(classname);
            }
            else if (action == -1)
            {
                this.totalUserOnlineCountBackground.removeClass(classname);
            }
        }
        else
        {
            if (this.totalUserOnlineCountBackground.hasClass('ow_count_active'))
            {
                return;
            }
        }


        this.totalUserOnlineCount.html(count);
    }

    this.updateUserInfo = function(item, smallItem, info){
        item.data(info.data);
        $( '.ow_chat_in_item_author', item ).html(info.data['username']);
        $( '.ow_chat_in_item_photo img', item ).attr('src', info.data['user_avatar_src']);

        $('a', smallItem).html(info.data['username']);
    }
}

OW_InstantChat.ContactManager_Controller = function(){
    var self = this;

    this.dialogs = {};
    this.view = new OW_InstantChat.ContactManager_View();
    this.chatBlockActive = parseInt(im_readCookie('chatBlockActive')) || false;
    this.chatBlockCompact = parseInt(im_readCookie('chatBlockCompact')) || false;

    if (this.chatBlockActive)
    {
        if (this.chatBlockCompact)
        {
            this.view.moveToCompactMode(true);
        }
        else
        {
            this.view.maximize();
        }
    }

    this.chatSettingsActive = false;
    this.contactInfo = {};

    this.totalUnreadMessages = 0;
    this.chatSelectorUnreadMessages = 0;

    this.fitWindowNumber = 0;

    //Expand or collapse main window
    this.view.minimizeButton.click(function(){

        if (self.chatBlockActive){
            self.view.minimize();
            self.chatBlockActive = false;
            im_createCookie('chatBlockActive', 0, 1);
        }
        else{
            self.view.maximize();
            self.chatBlockActive = true;
            im_createCookie('chatBlockActive', 1, 1);
        }

        OW.updateScroll($('.ow_chat_list'));
    });

    // Settings
    this.view.soundSettings.click(function(){
        self.view.settingsForm.submit();
    });

    this.foldingTimeout = false;
    this.foldingTime = 2000; //TODO set time to auto collapse

    this.view.notificationWrapper.hover(function(e)
        {
            if (self.foldingTimeout)
            {
                window.clearTimeout(self.foldingTimeout);
            }

            self.moveToActiveMode();
        },
        function(e)
        {
            self.foldingTimeout = setTimeout(function(){
                self.moveToCompactMode();
            }, self.foldingTime);

        });

    $(document).click(function( e )
    {
        if ( !$(e.target).is(':visible') )
        {
            return;
        }

        var isContent = self.view.chatSelectorList.find(e.target).length;
        var isTarget = self.view.chatSelectorButton.is(e.target) || self.view.chatSelectorButton.find(e.target).length;
        //Show or hide chat dialog selector
        if ( isTarget && !isContent )
        {
            if (self.view.chatSelectorList.hasClass('ow_hidden'))
            {
                if ($('.ow_chat_selector_items li.ow_dialog_in_selector.ow_chat_online').length > 0)
                {
                    self.view.showChatSelector();

                    //Hide if there are no hidden unread dialogs
                    self.view.chatSelectorTotalUnreadMessagesCountWrapper.hide();
                }
            }
            else
            {
                self.view.hideChatSelector();

                //Show if there are hidden unread dialogs
                if ( self.chatSelectorUnreadMessages > 0 )
                {
                    self.view.chatSelectorTotalUnreadMessagesCountWrapper.show();
                }
            }

            if ($('.ow_chat_selector_items li.ow_dialog_in_selector.ow_chat_online').length == 0)
            {
                self.view.chatSelector.hide();
            }
        }
        else if ( !isContent )
        {
            self.view.hideChatSelector();

            //Show if there are hidden unread dialogs
            if ( self.chatSelectorUnreadMessages > 0 )
            {
                self.view.chatSelectorTotalUnreadMessagesCountWrapper.show();
            }
        }

        // Show or hide settings
        isContent = self.view.settingsWrapper.find(e.target).length;
        isTarget = self.view.settingsButton.is(e.target) || self.view.settingsButton.find(e.target).length;
        if ( isTarget && !isContent )
        {
            if (self.chatSettingsActive)
            {
                self.view.hideSettings();
                self.chatSettingsActive = false;
            }
            else
            {
                self.view.showSettings();
                self.chatSettingsActive = true;
            }
        }
        else if ( !isContent )
        {
            self.view.hideSettings();
            self.chatSettingsActive = false;
        }

    });

    this.isActiveMode = function(){
        return this.view.mainWindow.hasClass('ow_active');
    }

    this.isCompactMode = function(){
        return this.view.mainWindow.hasClass('ow_compact');
    }

    this.getLastMessageTimestamps = function(){

        var timestamps = {};

        $('.ow_chat_list ul li.ow_chat_online').each(function(){
            if ( $(this).data('status') == 'online' )
            {
                timestamps[$(this).data('node')] = parseInt($(this).data('lastMessageTimestamp') );
            }
        });

        return timestamps;
    }


    this.getRosterLength = function(){

        return this.view.getRosterLength();
    }

    this.moveToActiveMode = function(){

        this.view.moveToActiveMode();
    }

    this.moveToCompactMode = function(){
        this.view.moveToCompactMode();
    }

    this.fitWindow = function() {

        if (self.fitWindowNumber > 20)
        {
            self.fitWindowNumber = 0;
            return;
        }

        self.fitWindowNumber++;

        var chat_width = $('.ow_chat').width() + $('.ow_chat_dialog_wrap').width() + $('.ow_chat_selector').width() + 20;

        var win_width = $(window).innerWidth();

        if (win_width < chat_width && $('.ow_chat_dialog.ow_open').length > 1)
        {
            //Folding
            self.view.chatSelector.show();
            var dialogs = $('.ow_chat_dialog.ow_open');
            var box = dialogs[1];
            var dialog = self.getDialog($(box).data('jid_id'));
            dialog.moveToChatSelector();

            self.fitWindow();
        }
        else if (win_width > (chat_width + 260))
        {
            //Unfolding
            if ($('.ow_chat_selector_items li.ow_dialog_in_selector.ow_chat_online').length > 0)
            {
                var dialogs = $('div.ow_chat_dialog.ow_hidden');
                var box = dialogs.last();
                var dialog = self.getDialog($(box).data('jid_id'));
                dialog.removeFromChatSelector();

                self.fitWindow();
            }

            if ($('.ow_chat_selector_items li.ow_dialog_in_selector.ow_chat_online').length == 0)
            {
                self.view.chatSelector.hide();
            }
        }

        if (self.fitWindowNumber > 0)
        {
            self.fitWindowNumber--;
        }
    }

    this.getDialog = function(node){
        if( typeof self.dialogs[node] == 'undefined' ){

           self.dialogs[node] = new OW_InstantChat.Dialog_Controller(node, OW_InstantChat.Details.domain);
        }

       return self.dialogs[node];
    }

    this.removeDialog = function(node){
        delete this.dialogs[node];
    }

    this.getChatSelectorItem = function(node){
        return this.view.getChatSelectorItem(node);
    }

    this.getItem = function(node){
        return this.view.getItem(node);
    }

    this.removeItem = function(node){

        if (im_debug_mode){console.log('ContactManager_Controller.removeItem: Removing '+node);}

        var contactsCount = self.view.getRosterLength();
        OW.trigger('updateTotalCounter', {'count': contactsCount});

        this.updateChatSelector();

        //this.removeDialog(node);

        //im_eraseCookie('contact_tab_' + node);

        OW.updateScroll($('.ow_chat_list'));
    }

    this.sortRoster = function(){
        this.view.sortRoster();
    }

    this.updateChatSelector = function() {
        this.view.updateChatSelector();
    }

    OW.bind('clearUnreadMessagesCounters', function(data){
        self.totalUnreadMessages -= data.unreadMessages;
        self.chatSelectorUnreadMessages -= data.unreadMessages;

        // Update Chat Selector counter
        if (self.chatSelectorUnreadMessages > 0)
        {
            self.view.updateChatSelectorUnreadMessagesCounter(self.chatSelectorUnreadMessages);
        }
        else
        {
            self.view.hideChatSelectorUnreadMessagesCounter();
            self.view.updateChatSelectorUnreadMessagesCounter(0);
            self.chatSelectorUnreadMessages = 0;
        }

        // Update Main counter
        if ( self.totalUnreadMessages > 0 )
        {
            $('.ow_chat .ow_count_wrap span.ow_count').html(self.totalUnreadMessages);
        }
        else
        {
            var contactsCount = self.view.getRosterLength();
            $('.ow_chat .ow_count_block .ow_count_wrap span.ow_count').html(contactsCount);
            $('.ow_chat .ow_count_block .ow_count_wrap .ow_count_bg').removeClass('ow_count_active');
            self.totalUnreadMessages = 0;
        }
    });

    OW.bind('userInfoReceived', function(info) {

        $('.ow_chat_list .ow_chat_preloader').remove();
        /* Add item to main chat window*/
        var item = self.view.getItem(info.node);
        if (item.length == 0)
        {
            item = self.view.createItem(info);

            var dialog = self.getDialog(info.node);

            item.click(function () {

                var node = $(this).attr('id');

                if ( $(this).data('isBlocker') )
                {
                    OW.error(window.im_userBlockedMessage);
                    return;
                }

                var dialog = self.getDialog(node);
                dialog.showTab().open();

            });

            var contactIsOpened = im_readCookie('contact_tab_'+info.node);
            if ( typeof contactIsOpened != 'undefined' && contactIsOpened != null )
            {
                self.moveToActiveMode();
                var dialog = self.getDialog(info.node);
                dialog.showTab();
                if (contactIsOpened == 1)
                {
                    dialog.open();
                }
            }
            else
            {
                //TODO Make it work properly if user returned online and dialog was opened
                if ( $('#main_tab_contact_' + info.node).length > 0 )
                {
                    $('#main_tab_contact_' + info.node).remove();
                    OW.trigger('dialogClosed', {node: info.node});
                }
            }
        }

        /* Add item to chat selector */
        var smallItem = self.view.getChatSelectorItem(info.node);
        if (smallItem.length == 0)
        {
            smallItem = self.view.createSmallItem(info);
            smallItem.click(function() {
                var node = $(this).data('jid_id');

                var dialog = self.getDialog(node);
                dialog.showTab().open();

                $('.ow_btn_dialogs').click();
            });
        }

        self.view.updateUserInfo(item, smallItem, info);

        var contactsCount = self.view.getRosterLength();

        if (contactsCount > 1)
        {
            self.sortRoster();
        }

        OW.addScroll($('.ow_chat_list'));

        OW.trigger('updateTotalCounter', {'count': contactsCount});
    });

    OW.bind('updateTotalCounter', function(params){

        params = params || {};
        var count = params['count'] || self.totalUnreadMessages;
        var classname = params['classname'] || null;
        var action = params['action'] || null;

        self.view.updateTotalCounter(count, classname, action);
    });

    OW.bind('updateChatSelectorUnreadMessagesCounter', function(params){

        params = params || {};
        var count = params['count'] || self.chatSelectorUnreadMessages;

        self.view.updateChatSelectorUnreadMessagesCounter(count);
    });

    OW.bind('showChatSelectorUnreadMessagesCounter', function(){
        self.view.showChatSelectorUnreadMessagesCounter();
    });

    OW.bind('allDialogsClosed', function(){

        self.foldingTimeout = setTimeout(function(){
            self.moveToCompactMode();
        }, self.foldingTime);

    });

    OW.bind('allDialogsCollapsed', function(){

        self.foldingTimeout = setTimeout(function(){
            self.moveToCompactMode();
        }, self.foldingTime);

    });

    OW.bind('dialogClosed', function(params){
        self.updateChatSelector();
        self.fitWindow();
        self.removeDialog(params.node);
    });

    OW.bind('dialogOpened', function(params){
        self.moveToActiveMode();
        self.fitWindow();
    });

    OW.bind('removeItem', function(data){
        self.removeItem(data.node);
    });

    $(window).resize(function() {
        self.fitWindow();
    });
}

/*                          End Contact Manager                              */

/*                    End MVC                                      */

$(function(){

    /*
     * timeago: a jQuery plugin, version: 0.9.3 (2011-01-21)
     * @requires jQuery v1.2.3 or later
     *
     * Timeago is a jQuery plugin that makes it easy to support automatically
     * updating fuzzy timestamps (e.g. '4 minutes ago' or 'about 1 day ago').
     *
     * For usage and examples, visit:
     * http://timeago.yarp.com/
     *
     * Licensed under the MIT:
     * http://www.opensource.org/licenses/mit-license.php
     *
     * Copyright (c) 2008-2011, Ryan McGeary (ryanonjavascript -[at]- mcgeary [*dot*] org)
     */
    $.timeago = function(timestamp) {
        if (timestamp instanceof Date) {
            return inWords(timestamp);
        } else if (typeof timestamp === 'string') {
            return inWords($.timeago.parse(timestamp));
        } else {
            return inWords($.timeago.datetime(timestamp));
        }
    };
    var $t = $.timeago;

    $.extend($.timeago, {
        settings: {
            refreshMillis: 60000,
            allowFuture: false,
            strings: {
                prefixAgo: null,
                prefixFromNow: null,
                suffixAgo: $('#ow_language_suffixAgo').html(),
                suffixFromNow: $('#ow_language_suffixFromNow').html(),
                seconds: $('#ow_language_seconds').html(),
                minute: $('#ow_language_minute').html(),
                minutes: $('#ow_language_minutes').html(),
                hour:  $('#ow_language_hour').html(),
                hours: $('#ow_language_hours').html(),
                day: $('#ow_language_day').html(),
                days: $('#ow_language_days').html(),
                month: $('#ow_language_month').html(),
                months: $('#ow_language_months').html(),
                year: $('#ow_language_year').html(),
                years: $('#ow_language_years').html(),
                numbers: []
            }
        },
        inWords: function(distanceMillis) {
            var $l = this.settings.strings;
            var prefix = $l.prefixAgo;
            var suffix = $l.suffixAgo;
            if (this.settings.allowFuture) {
                if (distanceMillis < 0) {
                    prefix = $l.prefixFromNow;
                    suffix = $l.suffixFromNow;
                }
                distanceMillis = Math.abs(distanceMillis);
            }

            var seconds = distanceMillis / 1000;
            var minutes = seconds / 60;
            var hours = minutes / 60;
            var days = hours / 24;
            var years = days / 365;

            function substitute(stringOrFunction, number) {
                var string = $.isFunction(stringOrFunction) ? stringOrFunction(number, distanceMillis) : stringOrFunction;
                var value = ($l.numbers && $l.numbers[number]) || number;
                return string.replace(/%d/i, value);
            }

            var words = seconds < 45 && substitute($l.seconds, Math.round(seconds)) ||
            seconds < 90 && substitute($l.minute, 1) ||
            minutes < 45 && substitute($l.minutes, Math.round(minutes)) ||
            minutes < 90 && substitute($l.hour, 1) ||
            hours < 24 && substitute($l.hours, Math.round(hours)) ||
            hours < 48 && substitute($l.day, 1) ||
            days < 30 && substitute($l.days, Math.floor(days)) ||
            days < 60 && substitute($l.month, 1) ||
            days < 365 && substitute($l.months, Math.floor(days / 30)) ||
            years < 2 && substitute($l.year, 1) ||
            substitute($l.years, Math.floor(years));

            return $.trim([prefix, words, suffix].join(' '));
        },
        parse: function(iso8601) {
            var s = $.trim(iso8601);
            s = s.replace(/\.\d\d\d+/,''); // remove milliseconds
            s = s.replace(/-/,'/').replace(/-/,'/');
            s = s.replace(/T/,' ').replace(/Z/,' UTC');
            s = s.replace(/([\+\-]\d\d)\:?(\d\d)/,' $1$2'); // -04:00 -> -0400
            return new Date(s);
        },
        datetime: function(elem) {
            // jQuery's `is()` doesn't play well with HTML5 in IE
            var isTime = $(elem).get(0).tagName.toLowerCase() === 'time'; // $(elem).is('time');
            var iso8601 = isTime ? $(elem).attr('datetime') : $(elem).attr('title');
            return $t.parse(iso8601);
        }
    });

    $.fn.timeago = function() {
        var self = this;
        self.each(refresh);

        var $s = $t.settings;
        if ($s.refreshMillis > 0) {
            setInterval(function() {
                self.each(refresh);
            }, $s.refreshMillis);
        }
        return self;
    };

    function refresh() {
        var data = prepareData(this);
        if (!isNaN(data.datetime)) {
            $(this).text(inWords(data.datetime));
        }
        return this;
    }

    function prepareData(element) {
        element = $(element);
        if (!element.data('timeago')) {
            element.data('timeago', {
                datetime: $t.datetime(element)
            });
            var text = $.trim(element.text());
            if (text.length > 0) {
                element.attr('title', text);
            }
        }
        return element.data('timeago');
    }

    function inWords(date) {
        return $t.inWords(distance(date));
    }

    function distance(date) {
        return (new Date().getTime() - date.getTime());
    }

    // fix for IE6 suckage
    document.createElement('abbr');
    document.createElement('time');

    $.fn.extend({
        autolink: function(options){
          var exp =  new RegExp("(\\b(https?|ftp|file)://[-A-Z0-9+&amp;@#\\/%?=~_|!:,.;]*[-A-Z0-9+&amp;@#\\/%=~_|])", "ig");
          /* Credit for the regex above goes to @elijahmanor on Twitter, so follow that awesome guy! */

        this.each( function(id, item){

            if ($(item).html() == "")
            {
                return 1;
            }
            var text = $(item).html().replace(exp,"<a href='$1' target='_blank'>$1</a>");
            $(item).html( text );

        });

          return this;
        }
    });

    /*Instant Chat*/

    AudioPlayer.setup(im_soundSwf, { width: 100 });

    OW_InstantChat_App = new OW_InstantChat.Application();
    OW_InstantChat_App.init();

    onerror = function(e) {

        return false;
    };

    onunload = function() {
        if (typeof OW_InstantChat_App.connection != 'undefined' && OW_InstantChat_App.connection.connected) {
            im_eraseCookie(OW_InstantChat_App.tab);
        }
        return false;
    };

    OW.bind('base.online_now_click',
        function(userId){

            if (parseInt(userId) != OW_InstantChat.Details.userId)
            {
                $('#ow_chat_now_'+userId).addClass('ow_hidden');
                $('#ow_preloader_content_'+userId).removeClass('ow_hidden');

                $.post(im_updateUserInfoUrl, {
                    userId: userId,
                    domain: OW_InstantChat.Details.domain,
                    action: 'open',
                    option: 'activate',
                    click: 'online_now'
                }, function(data){

                    if ( typeof data != 'undefined')
                    {
                        if ( typeof data['warning'] != 'undefined' && data['warning'] )
                        {
                            OW.message(data['message'], data['type']);
                            return;
                        }
                        else
                        {
                            var dialog = OW_InstantChat_App.contactManager.getDialog(data['node']);
                            dialog.showTab().open();
                        }
                    }
                }, 'json').complete(function(){
                    $('#ow_chat_now_'+userId).removeClass('ow_hidden');
                    $('#ow_preloader_content_'+userId).addClass('ow_hidden');
                });
            }
        });

//    OW.bind('base.sign_out_click',
//        function(userId){
//        });

});