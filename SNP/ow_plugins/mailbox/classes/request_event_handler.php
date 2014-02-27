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
 *
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugin.mailbox.classes
 * @since 1.0
 */

class MAILBOX_CLASS_RequestEventHandler
{
    /**
     * Class instance
     *
     * @var MAILBOX_CLASS_RequestEventHandler
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return MAILBOX_CLASS_RequestEventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    const CONSOLE_ITEM_KEY = 'mailbox';

    /**
     *
     * @var MAILBOX_BOL_ConversationService
     */
    private $service;

    private function __construct()
    {
        $this->service = MAILBOX_BOL_ConversationService::getInstance();
    }

    public function collectItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        if (OW::getUser()->isAuthenticated())
        {
            $item = new MAILBOX_CMP_ConsoleMailbox();
            $event->addItem($item, 4);
        }
    }

    /* Console list */

    public function ping( BASE_CLASS_ConsoleDataEvent $event )
    {
        $userId = OW::getUser()->getId();
        $data = $event->getItemData(self::CONSOLE_ITEM_KEY);

        $newInvitationCount = $this->service->getNewConsoleConversationCount($userId);
        $viewInvitationCount = $this->service->getVievedConversationCountForConsole($userId);
        
        $data['counter'] = array(
            'all' => $newInvitationCount + $viewInvitationCount,
            'new' => $newInvitationCount
        );

        $event->setItemData('mailbox', $data);
    }

    public function loadList( BASE_CLASS_ConsoleListEvent $event )
    {
        $params = $event->getParams();
        $userId = OW::getUser()->getId();
        
        if ( $params['target'] != self::CONSOLE_ITEM_KEY )
        {
            return;
        }

//        if ( !empty($params['ids']) && is_array($params['ids']) && count($params['ids']) >= 30 )
//        {
//            $requests = array();
//        }
//        else
//        {
       $requests = $this->service->getConsoleConversationList($userId, 0, 10, $params['console']['time'], $params['ids']);
//        }

        $conversationIdList = array();

        foreach ( $requests as $conversation )
        {
            $conversationIdList[] = $conversation['conversationId'];
        }

        /* @var $conversation MAILBOX_BOL_Conversation  */

        $renderedItems = array();

        foreach ( $requests as $request )
        {
            $senderId = 0;
            $userType = '';
            $messageId = 0;

            if ( $request['initiatorId'] == $userId )
            {
                $senderId = $request['interlocutorId'];
                $userType = 'initiator';
                $messageId = $request['interlocutorMessageId'];
            }
            
            if ( $request['interlocutorId'] == $userId )
            {
                $senderId = $request['initiatorId'];
                $userType = 'interlocutor';
                $messageId = $request['initiatorMessageId'];
            }
            
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($senderId), true, true, true, false );
            $avatar = $avatar[$senderId];

            $userUrl = BOL_UserService::getInstance()->getUserUrl($senderId);
            $displayName = BOL_UserService::getInstance()->getDisplayName($senderId);
            

            $subject = $request['subject'];
            $text = '<span class="error">' . OW::getLanguage()->text('mailbox', 'read_permission_denied') . '</span>';
            $conversationUrl = MAILBOX_BOL_ConversationService::getInstance()->getConversationUrl($request['conversationId']);
            
            if ( OW::getUser()->isAuthorized('mailbox', 'read_message') )
            {
                // check credits
                $eventParams = array('pluginKey' => 'mailbox', 'action' => 'read_message');
                $credits = OW::getEventManager()->call('usercredits.check_balance', $eventParams);
                if ( $credits === false && !$request['recipientRead'] )
                {
                    $creditsMsg = OW::getEventManager()->call('usercredits.error_message', $eventParams);
                    $text = '<span class="error">' . $creditsMsg . '</span>';
                }
                else
                {
                    $text = mb_strlen($request['text']) > 100 ? mb_substr(strip_tags($request['text']), 0, 100) . '...' : $request['text'];

                    $e = new OW_Event('mailbox.message_render', array(
                            'conversationId' => $request['conversationId'],
                            'messageId' => $messageId,
                            'senderId' => $senderId,
                            'recipientId' => $userId,
                        ), array( 'short' => $text, 'full' => $request['text'] ));

                    OW::getEventManager()->trigger($e);

                    $eventData = $e->getData();

                    $text = $eventData['short'];
                }
            }
            
            $langVars = array(
                'userUrl'=> $userUrl,
                'displayName'=>$displayName,
                'subject' => $subject,
                'text' => $text,
                'conversationUrl' => $conversationUrl );

            $string = OW::getLanguage()->text( 'mailbox', 'console_request_item', $langVars );

            $item = new MAILBOX_CMP_RequestItem();
            $item->setAvatar($avatar);
            $item->setContent($string);
            $item->setUrl($conversationUrl);
            
            if ( empty($request['viewed']) || ( ( !($request['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INITIATOR) && $userType == 'initiator' ) || ( !($request['viewed'] & MAILBOX_BOL_ConversationDao::VIEW_INTERLOCUTOR) && $userType == 'interlocutorId' ) ) )
            {
                $item->addClass('ow_console_new_message');
            }
            
            $js = UTIL_JsGenerator::newInstance();
            OW::getDocument()->addOnloadScript($js->generateJs());

            $event->addItem($item->render(), $request['id']);
        }

        $this->service->setConversationViewedInConsole($conversationIdList, $userId);
    }

    public function init()
    {
        OW::getEventManager()->bind('console.collect_items', array($this, 'collectItems'));
        OW::getEventManager()->bind('console.ping', array($this, 'ping'));
        OW::getEventManager()->bind('console.load_list', array($this, 'loadList'));
    }
}