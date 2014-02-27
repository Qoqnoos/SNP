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

/**
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow_plugins.virtualgifts.classes
 * @since 1.6.0
 */
class VIRTUALGIFTS_CLASS_EventHandler
{
    /**
     * @var VIRTUALGIFTS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return VIRTUALGIFTS_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }

    public function quickLinks( BASE_CLASS_EventCollector $event )
    {
        $service = VIRTUALGIFTS_BOL_VirtualGiftsService::getInstance();
        $userId = OW::getUser()->getId();

        $giftCount = $service->countUserReceivedGifts($userId, false);

        if ( $giftCount > 0 )
        {
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('virtualgifts', 'my_gifts_quick_link'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => OW::getRouter()->urlForRoute('virtual_gifts_private_list'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $giftCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => OW::getRouter()->urlForRoute('virtual_gifts_private_list')
            ));
        }
    }

    public function sendGiftActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) || $params['userId'] == OW::getUser()->getId() )
        {
            return;
        }

        $linkId = 'gi' . rand(10, 1000000);
        $user = BOL_UserService::getInstance()->getUserName((int) $params['userId']);

        if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), (int) $params['userId']) )
        {
            $script = "\$('#" . $linkId . "').click(function(){

            window.OW.error('" . OW::getLanguage()->text('base', 'user_block_message') . "');

        });";

            OW::getDocument()->addOnloadScript($script);
        }
        else
        {

            $script = '$("#' . $linkId . '").click(function(){
        sendGiftFloatBox = OW.ajaxFloatBox("VIRTUALGIFTS_CMP_SendGift", {recipientId: ' . $params['userId'] . ' } , {width:580, iconClass: "ow_ic_heart", title: "' . OW::getLanguage()->text('virtualgifts', 'send_gift_to', array('user' => $user)) . '"});
    });';

            OW::getDocument()->addOnloadScript($script);
        }

        $resultArray = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('virtualgifts', 'profile_toolbar_item_send_gift'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "virtualgifts.send_gift"
        );

        $event->add($resultArray);
    }

    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'virtualgifts',
            'action' => 'virtualgifts-send_gift',
            'sectionIcon' => 'ow_ic_birthday',
            'sectionLabel' => OW::getLanguage()->text('virtualgifts', 'email_notifications_section_label'),
            'description' => OW::getLanguage()->text('virtualgifts', 'email_notifications_setting_send_gift'),
            'selected' => true
        ));
    }

    public function onSendGift( OW_Event $e )
    {
        $params = $e->getParams();

        $giftId = (int) $params['giftId'];
        $senderId = (int) $params['senderId'];
        $recipientId = (int) $params['recipientId'];

        $giftService = VIRTUALGIFTS_BOL_VirtualGiftsService::getInstance();

        if ( !$giftId || !$gift = $giftService->findUserGiftById($giftId) )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();
        $giftUrl = OW::getRouter()->urlForRoute('virtual_gifts_view_gift', array('giftId' => $giftId));

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($senderId));

        $params = array(
            'pluginKey' => 'virtualgifts',
            'entityType' => 'virtualgifts_send_gift',
            'entityId' => $giftId,
            'action' => 'virtualgifts-send_gift',
            'userId' => $recipientId,
            'time' => time()
        );

        $data = array(
            'avatar' => $avatars[$senderId],
            'string' => array(
                'key' => 'virtualgifts+email_notifications_send_gift',
                'vars' =>array(
                    'senderName' => $userService->getDisplayName($senderId),
                    'senderUrl' => $userService->getUserUrl($senderId),
                    'giftUrl' => $giftUrl
                )
            ),
            'content' => !empty($gift['dto']->message) ? $gift['dto']->message : '',
            'url' => $giftUrl,
            'contentImage' => $gift['imageUrl']
        );

        $event = new OW_Event('notifications.add', $params, $data);
        OW::getEventManager()->trigger($event);
    }

    public function onCreditsCollect( OW_Event $e )
    {
        $credits = new VIRTUALGIFTS_CLASS_Credits();
        $credits->bindCreditActionsCollect($e);
    }

    public function afterInits()
    {
        // Add user credits actions on first init
        if ( !OW::getConfig()->getValue('virtualgifts', 'is_once_initialized') )
        {
            if ( OW::getConfig()->configExists('virtualgifts', 'is_once_initialized') )
            {
                OW::getConfig()->saveConfig('virtualgifts', 'is_once_initialized', 1);
            }
            else
            {
                OW::getConfig()->addConfig('virtualgifts', 'is_once_initialized', 1);
            }

            $credits = new VIRTUALGIFTS_CLASS_Credits();
            $credits->triggerCreditActionsAdd();
        }
    }

    public function feedEntityAdd( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( $params['entityType'] != 'user_gift' )
        {
            return;
        }

        $giftService = VIRTUALGIFTS_BOL_VirtualGiftsService::getInstance();
        $gift = $giftService->findUserGiftById($params['entityId']);

        if ( !$gift )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();

        $params = array(
            'recipientName' => $userService->getDisplayName($gift['dto']->recipientId),
            'recipientUrl' => $userService->getUserUrl($gift['dto']->recipientId)
        );

        $message = htmlspecialchars($gift['dto']->message);

        $content = array(
            "format" => "image_content",
            "vars" => array(
                "thumbnail" => $gift['imageUrl'],
                "title" => '',
                "description" => $message,
                "url" => array("routeName" => "virtual_gifts_view_gift", "vars" => array('giftId' => $gift['dto']->id)),
                'view' => array(
                    'iconClass' => 'ow_ic_heart'
                )
            )
        );

        $data = array(
            'params' => array(
                'userId' => $gift['dto']->senderId,
                'feedType' => 'user',
                'feedId' => $gift['dto']->recipientId
            ),
            'string' => array('key' => 'virtualgifts+feed_string', 'vars' => $params),
            'content' => $content
        );

        $e->setData($data);
    }

    public function adsEnabled( BASE_EventCollector $event )
    {
        $event->add('virtualgifts');
    }

    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'virtualgifts' => array(
                    'label' => $language->text('virtualgifts', 'auth_group_label'),
                    'actions' => array(
                        'send_gift' => $language->text('virtualgifts', 'auth_action_label_send_gift')
                    )
                )
            )
        );
    }

    public function feedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('virtualgifts', 'feed_content_gift'),
            'activity' => 'create:user_gift'
        ));
    }

    public function feedGiftLike( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'user_gift' )
        {
            return;
        }

        $service = VIRTUALGIFTS_BOL_VirtualGiftsService::getInstance();
        $gift = $service->findUserGiftById($params['entityId']);

        if ( !$gift )
        {
            return;
        }

        $senderId = $gift['dto']->senderId;
        $recipientId = $gift['dto']->recipientId;

        $userService = BOL_UserService::getInstance();
        $senderEmbed = '<a href="' . $userService->getUserUrl($senderId) . '">' . $userService->getDisplayName($senderId) . '</a>';
        $recipientEmbed = '<a href="' . $userService->getUserUrl($recipientId) . '">' . $userService->getDisplayName($recipientId) . '</a>';

        $lang = OW::getLanguage();
        if ( $params['userId'] == $senderId )
        {
            $string = $lang->text('virtualgifts', 'feed_activity_sender_gift_like', array('recipient' => $recipientEmbed));
        }
        else
        {
            $string = $lang->text('virtualgifts', 'feed_activity_gift_string_like', array(
                'sender' => $senderEmbed,
                'recipient' => $recipientEmbed
            ));
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'like',
            'activityId' => $params['userId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'virtualgifts'
        ), array(
            'string' => $string
        )));
    }

    public function feedGiftComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'user_gift' )
        {
            return;
        }

        $service = VIRTUALGIFTS_BOL_VirtualGiftsService::getInstance();
        $gift = $service->findUserGiftById($params['entityId']);

        if ( !$gift )
        {
            return;
        }

        $senderId = $gift['dto']->senderId;
        $recipientId = $gift['dto']->recipientId;

        $userService = BOL_UserService::getInstance();
        $senderEmbed = '<a href="' . $userService->getUserUrl($senderId) . '">' . $userService->getDisplayName($senderId) . '</a>';
        $recipientEmbed = '<a href="' . $userService->getUserUrl($recipientId) . '">' . $userService->getDisplayName($recipientId) . '</a>';

        if ( $senderId == $params['userId'] )
        {
            $string = OW::getLanguage()->text('virtualgifts', 'feed_activity_owner_gift_string_comment', array('recipient' => $recipientEmbed));
        }
        else
        {
            $string = OW::getLanguage()->text('virtualgifts', 'feed_activity_gift_string_comment', array('sender' => $senderEmbed, 'recipient' => $recipientEmbed)
            );
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'comment',
            'activityId' => $params['commentId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'virtualgifts'
        ), array(
            'string' => $string
        )));
    }

    public function sosialSharingGetGiftInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( $params['entityType'] == 'virtualgifts' )
        {
            $giftDto = VIRTUALGIFTS_BOL_VirtualGiftsService::getInstance()->findUserGiftById($params['entityId']);

            if ( !empty($giftDto) )
            {
                $data['display'] = !$giftDto->private;
            }
            
            $event->setData($data);
        }


    }
    
    public function init()
    {
        $this->genericInit();
        $em = OW::getEventManager();

        $em->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($this, 'quickLinks'));
        $em->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'sendGiftActionTool'));
    }

    public function genericInit()
    {
        $em = OW::getEventManager();

        $em->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        $em->bind('virtualgifts.send_gift', array($this, 'onSendGift'));
        $em->bind('usercredits.on_action_collect', array($this, 'onCreditsCollect'));
        $em->bind(OW_EventManager::ON_APPLICATION_INIT, array($this, 'afterInits'));
        $em->bind('feed.on_entity_add', array($this, 'feedEntityAdd'));
        $em->bind('ads.enabled_plugins', array($this, 'adsEnabled'));
        $em->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        $em->bind('feed.collect_configurable_activity', array($this, 'feedCollectConfigurableActivity'));
        $em->bind('feed.after_like_added', array($this, 'feedGiftLike'));
        $em->bind('feed.after_comment_add', array($this, 'feedGiftComment'));
        $em->bind('socialsharing.get_entity_info', array($this, 'sosialSharingGetGiftInfo'));
    }
}