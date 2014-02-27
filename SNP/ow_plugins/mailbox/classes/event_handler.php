<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class MAILBOX_CLASS_EventHandler
{

    public function __construct()
    {
        
    }

    public function sendPrivateMessageActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( OW::getUser()->getId() == $userId )
        {
            return;
        }

        if ( !OW::getUser()->isAuthorized('mailbox', 'send_message') )
        {
            return;
        }

        if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId) )
        {
            $linkId = 'mb' . rand(10, 1000000);
            $script = "\$('#" . $linkId . "').click(function(){

                window.OW.error('".OW::getLanguage()->text('base', 'user_block_message')."');

            });";

            OW::getDocument()->addOnloadScript($script);
        }
        else
        {
            $linkId = 'mb' . rand(10, 1000000);
            $script = "\$('#" . $linkId . "').click(function(){
                \$form = $('#create-conversation-div').children();

                window.mailbox_send_message_floatbox = new OW_FloatBox({
                    \$title: '" . OW::getLanguage()->text('mailbox', 'compose_message') . "',
                    \$contents: \$form,
                    'width': '480px',
                    'class': 'ow_ic_add'
                });

                window.mailbox_send_message_floatbox.bind('show', function()
                {
                    var textarea = \$form.find('textarea[name=message]').get(0);
                    textarea.htmlarea();
                    textarea.htmlareaRefresh();
                });
            });";

            OW::getDocument()->addOnloadScript($script);
        }


        $resultArray = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('mailbox', 'create_conversation_button'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_CMP_CLASS => 'MAILBOX_CMP_CreateConversation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "mailbox.send_message"
        );

        $event->add($resultArray);
    }



    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'mailbox',
            'action' => 'mailbox-new_message',
            'sectionIcon' => 'ow_ic_mail',
            'sectionLabel' => OW::getLanguage()->text('mailbox', 'email_notifications_section_label'),
            'description' => OW::getLanguage()->text('mailbox', 'email_notifications_new_message'),
            'selected' => true
        ));
    }

    public function onSendMessage( OW_Event $e )
    {
        $params = $e->getParams();

        OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $params['senderId'] ));
        OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . $params['recipientId'] ));
    }

    public function onAvatarToolbarCollect( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'title' => OW::getLanguage()->text('mailbox', 'mailbox'),
            'iconClass' => 'ow_ic_mail',
            'url' => OW::getRouter()->urlForRoute('mailbox_default'),
            'order' => 2
        ));
    }

    public function mailboxAdsEnabled( BASE_EventCollector $event )
    {
        $event->add('mailbox');
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'mailbox' => array(
                    'label' => $language->text('mailbox', 'auth_group_label'),
                    'actions' => array(
                        'read_message' => $language->text('mailbox', 'auth_action_label_read_message'),
                        'send_message' => $language->text('mailbox', 'auth_action_label_send_message'),
                    )
                )
            )
        );
    }

    public function markConversation( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = (int)$params['userId'];

        OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_NEW_CONVERSATION_COUNT . ($userId) ));
        //OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . ($userId) ));
    }

    public function deleteConversation( OW_Event $event )
    {
        $params = $event->getParams();
        $dto = $params['conversationDto'];
        /* @var $dto MAILBOX_BOL_Conversation */
        if ( $dto )
        {
            OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . ($dto->initiatorId) ));
            OW::getCacheManager()->clean( array( MAILBOX_BOL_ConversationDao::CACHE_TAG_USER_CONVERSATION_COUNT . ($dto->interlocutorId) ));
        }
    }

    public function consoleSendList( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        $userIdList = $params['userIdList'];

        $conversationListByUserId = MAILBOX_BOL_ConversationService::getInstance()->getConversationListForConsoleNotificationMailer($userIdList);

        $conversationIdList = array();

        foreach ( $conversationListByUserId as $recipientId => $conversationList )
        {
            foreach ( $conversationList as $conversation )
            {
                $conversationIdList[$conversation['id']] = $conversation['id'];
            }
        }

        $result = MAILBOX_BOL_ConversationService::getInstance()->getConversationListByIdList($conversationIdList);
        $conversationList = array();

        foreach( $result as $conversation )
        {
            $conversationList[$conversation->id] = $conversation;
        }

        foreach ( $conversationListByUserId as $recipientId => $list )
        {
            foreach ( $list as $conversation )
            {
                $senderId = ($conversation['initiatorId'] == $recipientId) ? $conversation['interlocutorId'] : $conversation['initiatorId'];

                $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array( $senderId ) );
                $avatar = $avatars[$senderId];

                $event->add(array(
                    'pluginKey' => 'mailbox',
                    'entityType' => 'mailbox-conversation',
                    'entityId' => $conversation['id'],
                    'userId' => $recipientId,
                    'action' => 'mailbox-new_message',
                    'time' => $conversation['timeStamp'],

                    'data' => array(
                        'avatar' => $avatar,
                        'string' => OW::getLanguage()->text('mailbox', 'email_notifications_comment', array(
                                'userName' => BOL_UserService::getInstance()->getDisplayName($senderId),
                                'userUrl' => BOL_UserService::getInstance()->getUserUrl($senderId),
                                'conversationUrl' => MAILBOX_BOL_ConversationService::getInstance()->getConversationUrl($conversation['id'])
                            )),
                       'content' => $conversation['text']
                    )
                ));

                if( !empty($conversationList[$conversation['id']]) )
                {
                    $conversationList[$conversation['id']]->notificationSent = 1;
                    MAILBOX_BOL_ConversationService::getInstance()->saveConversation($conversationList[$conversation['id']]);
                }
            }
        }
    }

    public function genericInit()
    {
        OW::getEventManager()->bind('ads.enabled_plugins', array($this, 'mailboxAdsEnabled'));

        $credits = new MAILBOX_CLASS_Credits();
        OW::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));
    }

    public function init()
    {
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        OW::getEventManager()->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'sendPrivateMessageActionTool'));

        OW::getEventManager()->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        OW::getEventManager()->bind('mailbox.send_message', array($this, 'onSendMessage'));
        OW::getEventManager()->bind('base.on_avatar_toolbar_collect', array($this, 'onAvatarToolbarCollect'));

        OW::getEventManager()->bind(MAILBOX_BOL_ConversationService::EVENT_MARK_CONVERSATION, array($this, 'markConversation'));
        OW::getEventManager()->bind(MAILBOX_BOL_ConversationService::EVENT_DELETE_CONVERSATION, array($this, 'deleteConversation'));

        if ( OW::getPluginManager()->getPlugin('mailbox')->getDto()->build >= 5236 )
        {
            MAILBOX_CLASS_RequestEventHandler::getInstance()->init();
        }

        OW::getEventManager()->bind('notifications.send_list', array($this, 'consoleSendList'));
    }
}

