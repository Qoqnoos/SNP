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
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow.ow_plugins.ajaxim.classes
 * @since 1.6.0
 */
class AJAXIM_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var AJAXIM_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return AJAXIM_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var AJAXIM_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = AJAXIM_BOL_Service::getInstance();
    }

    public function genericInit()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_FINALIZE, array($this, 'onPluginInit'));
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'onCollectAuthLabels'));
        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($this, 'onCollectPrivacyActions'));
        OW::getEventManager()->bind('base.online_now_click', array($this, 'onShowOnlineButton'));
        OW::getEventManager()->bind('base.ping', array($this, 'onPing'));
        OW::getEventManager()->bind('plugin.ajaxim.on_plugin_init.handle_controller_attributes', array($this, 'onHandleControllerAttributes'));
    }

    public function init()
    {
        $this->genericInit();
    }

    public function onHandleControllerAttributes( OW_Event $event )
    {
        $params = $event->getParams();

        $handlerAttributes = $params['handlerAttributes'];

        if ($handlerAttributes['controller'] == 'BASE_CTRL_MediaPanel')
        {
            $event->setData(false);
        }

        if ($handlerAttributes['controller'] == 'SUPPORTTOOLS_CTRL_Client')
        {
            $event->setData(false);
        }
    }

    public function onPluginInit()
    {
        $handlerAttributes = OW::getRequestHandler()->getHandlerAttributes();
        $event = new OW_Event('plugin.ajaxim.on_plugin_init.handle_controller_attributes', array('handlerAttributes'=>$handlerAttributes));
        OW::getEventManager()->trigger($event);

        $handleResult = $event->getData();

        if ($handleResult === false)
        {
            return;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        else
        {
            if ( !BOL_UserService::getInstance()->isApproved() )
            {
                return;
            }

            $user = BOL_UserService::getInstance()->findUserById(OW::getUser()->getId());

            if (BOL_UserService::getInstance()->isSuspended($user->getId()))
            {
                return;
            }

            if ( (int) $user->emailVerify === 0 && OW::getConfig()->getValue('base', 'confirm_email') )
            {
                return;
            }

            if (!OW::getAuthorization()->isUserAuthorized($user->getId(), 'ajaxim', 'chat'))
            {
                return;
            }
        }

        $im_toolbar = new AJAXIM_CMP_Toolbar();
        OW::getDocument()->appendBody($im_toolbar->render());
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'ajaxim' => array(
                    'label' => $language->text('ajaxim', 'auth_group_label'),
                    'actions' => array(
                        'chat' => $language->text('ajaxim', 'auth_action_label_chat')
                    )
                )
            )
        );
    }

    public function onCollectPrivacyActions( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => 'ajaxim_invite_to_chat',
            'pluginKey' => 'ajaxim',
            'label' => $language->text('ajaxim', 'privacy_action_invite_to_chat'),
            'description' => '',
            'defaultValue' => 'everybody'
        );
        $event->add($action);
    }

    public function onShowOnlineButton( OW_Event $event )
    {
        $params = $event->getParams();

        if (empty($params['userId']))
            return false;

        if ( !OW::getAuthorization()->isUserAuthorized($params['userId'], 'ajaxim', 'chat') )
        {
            return false;
        }

        if ( !OW::getAuthorization()->isUserAuthorized($params['onlineUserId'], 'ajaxim', 'chat') )
        {
            return false;
        }

        if ( BOL_UserService::getInstance()->isBlocked($params['userId'], $params['onlineUserId']) )
        {
            return false;
        }

        $isFriendsOnlyMode = (bool)OW::getEventManager()->call('plugin.friends');
        if ($isFriendsOnlyMode)
        {
            $friendship = OW::getEventManager()->call('plugin.friends.check_friendship', array('userId' => OW::getUser()->getId(), 'friendId' => $params['onlineUserId']));
            if ( empty($friendship) )
            {
                return false;
            }
            else if ( $friendship->getStatus() != 'active' )
            {
                return false;
            }
        }

        $eventParams = array(
            'action' => 'ajaxim_invite_to_chat',
            'ownerId' => $params['onlineUserId'],
            'viewerId' => OW::getUser()->getId()
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {

            return false;
        }

        return true;
    }


    public function onPing( OW_Event $event )
    {
        $eventParams = $event->getParams();
        $params = $eventParams['params'];

        if ($eventParams['command'] != 'ajaxim_ping')
        {
            return;
        }

        $service = AJAXIM_BOL_Service::getInstance();

        if ( empty($_SESSION['lastRequestTimestamp']) )
        {
            $_SESSION['lastRequestTimestamp'] = (int)$params['lastRequestTimestamp'];
        }

        if ( ((int)$params['lastRequestTimestamp'] - (int) $_SESSION['lastRequestTimestamp']) < 3 )
        {
            $event->setData(array('error'=>"Too much requests"));
        }

        $_SESSION['lastRequestTimestamp'] = (int)$params['lastRequestTimestamp'];


        if ( !OW::getUser()->isAuthenticated() )
        {
            $event->setData(array('error'=>"You have to sign in"));
        }

        if ( !OW::getRequest()->isAjax() )
        {
            $event->setData(array('error'=>"Ajax request required"));
        }

        $onlinePeople = AJAXIM_BOL_Service::getInstance()->getOnlinePeople(OW::getUser());

        if ( !empty($params['lastMessageTimestamps']) )
        {
            $clientOnlineList = array_keys($params['lastMessageTimestamps']);
        }
        else
        {
            $clientOnlineList = array();
        }

        $onlineInfo = array();
        /* @var $user BOL_User */
        foreach ( $onlinePeople['users'] as $user )
        {
            if ( !OW::getAuthorization()->isUserAuthorized($user->getId(), 'ajaxim', 'chat') && !OW::getAuthorization()->isUserAuthorized($user->getId(), 'ajaxim') )
            {
                $onlinePeople['count']--;
                continue;
            }

            if ( !in_array($user->getId(), $clientOnlineList) )
            {
                $friendship = OW::getEventManager()->call('plugin.friends.check_friendship', array('userId' => OW::getUser()->getId(), 'friendId' => $user->getId()));
                $roster = $service->getUserInfoByNode($user, $friendship);
                $roster['show'] = 'chat';
                $roster['status'] = 'online';
                $presence = array(
                    'node'=>$user->getId(),
                    'data'=>$roster
                );
                $onlineInfo[] = $presence;
            }
        }

        /* @var $user BOL_User */
        foreach ( $clientOnlineList as $userId )
        {
            if ( !array_key_exists($userId, $onlinePeople['users']) )
            {
                $presence = array(
                    'node'=>$userId,
                    'data'=>array('status'=>'offline')
                );
                $onlineInfo[] = $presence;
            }
        }

        switch ( $params['action'] )
        {
            case "get":
                $response = array();
                if ( !empty($onlineInfo) )
                {
                    $response['presenceList'] = $onlineInfo;
                }

                if ( $onlinePeople['count'] != $params['onlineCount'] )
                {
                    $response['onlineCount'] = $onlinePeople['count'];
                }

                if ( !empty($params['lastMessageTimestamps']) )
                {
                    $messageList = AJAXIM_BOL_Service::getInstance()->findUnreadMessages(OW::getUser(), $params['lastMessageTimestamps']);
                    if ( !empty($messageList) )
                    {
                        $response['messageList'] = $messageList;
                        $response['messageListLength'] = count($messageList);
                    }
                }

                $event->setData($response);
                break;
        }
    }
}