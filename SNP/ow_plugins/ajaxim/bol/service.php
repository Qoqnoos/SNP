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
 * @package ow_plugins.ajaxim.bol
 */
class AJAXIM_BOL_Service
{
    /**
     *
     * @var AJAXIM_BOL_MessageDao
     */
    private $messageDao;
    /**
     * Class instance
     *
     * @var AJAXIM_BOL_Service
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        $this->messageDao = AJAXIM_BOL_MessageDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return AJAXIM_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function save( AJAXIM_BOL_Message $msg )
    {
        $this->messageDao->save($msg);
    }

    public function findLastMessages( $userId, $rosterId, $lastMessageTimestamp, $count = 10, $omit_last_message = 0 )
    {
        $result_msg_list = array();
        $msg_list = $this->messageDao->findLastMessages($userId, $rosterId, $lastMessageTimestamp, $count);

        foreach ( $msg_list as $id => $msg )
        {
            if ( $omit_last_message == 1 && ($id == (count($msg_list) - 1)) )
            {
                continue;
            }
            else
            {
                //$msg->setMessage(UTIL_HtmlTag::autoLink($msg->getMessage()));
                $msg->setRead(UTIL_DateTime::formatDate($msg->getTimestamp()));
                $msg->setTimestamp( $msg->getTimestamp() * 1000 );
                $result_msg_list[$id] = $msg;
            }
        }

        return $result_msg_list;
    }
    /**
     *
     * @param BOL_User $user
     */
    public function getOnlinePeople( $user )
    {
        $users = array();
        $count = 0;
        if ( (bool)OW::getEventManager()->call('plugin.friends') )
        {
            $count_friends = (int) OW::getEventManager()->call('plugin.friends.count_friends', array('userId' => $user->getId()));
            $idList = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $user->getId(), 'count' => $count_friends));

            if ( !empty($idList) )
            {
                $statusForUserList = BOL_UserService::getInstance()->findOnlineStatusForUserList($idList);

                foreach ( $statusForUserList as $userId => $isOnline )
                {
                    if ( !$isOnline )
                    {
                        continue;
                    }

                    $eventParams = array(
                        'action' => 'ajaxim_invite_to_chat',
                        'ownerId' => $userId,
                        'viewerId' => OW::getUser()->getId()
                    );

                    try
                    {
                        OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                    }
                    catch ( RedirectException $e )
                    {
                        continue;
                    }

                    if (BOL_UserService::getInstance()->isBlocked($userId, OW::getUser()->getId()) ||
                        BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId) )
                    {
                        continue;
                    }

                    $count++;
                    $users[$userId] = BOL_UserService::getInstance()->findUserById($userId);
                }
            }
        }
        else
        {
            $onlineCount = (int)BOL_UserService::getInstance()->countOnline();
            $onlineList = BOL_UserService::getInstance()->findOnlineList(0, $onlineCount);

            foreach($onlineList as $user)
            {
                if ($user->getId() == OW::getUser()->getId())
                {
                    continue;
                }

                $eventParams = array(
                    'action' => 'ajaxim_invite_to_chat',
                    'ownerId' => $user->getId(),
                    'viewerId' => OW::getUser()->getId()
                );

                try
                {
                    OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                }
                catch ( RedirectException $e )
                {
                    continue;
                }

                if (BOL_UserService::getInstance()->isBlocked($user->getId(), OW::getUser()->getId()) ||
                    BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $user->getId()) )
                {
                    continue;
                }

                $users[$user->getId()] = $user;
                $count++;
            }
        }

        return array('users' => $users, 'count' => $count);


    }

    public function getLastMessageTimestamp( $userId, $rosterId )
    {
        $message = $this->messageDao->findLastMessage($userId, $rosterId);

        return (!empty($message)) ? $message->getTimestamp() : 0;
    }

    public function findUnreadMessages( $user, $lastMessageTimeStamps )
    {
        $messages = array();

        foreach ( $lastMessageTimeStamps as $rosterId => $milliseconds )
        {
            $timestamp = $milliseconds / 1000;
            $messages = array_merge($messages, $this->messageDao->findUnreadMessages($rosterId, $user->getId(), $timestamp));
        }


        foreach($messages as $id=>$message)
        {
            $messages[$id]->message = $this->splitLongMessages($message->message);
            $messages[$id]->timestamp = $message->timestamp * 1000;
        }

        return $messages;
    }

    public function splitLongMessages($string)
    {
        $split_length = 30;
        $delimiter = ' ';
        $string_array = explode(' ', $string);

        foreach ( $string_array as $id => $word )
        {
            if ( mb_strlen(trim($word)) > $split_length )
            {
                $originalWord = $word;
                if ( strlen( UTIL_HtmlTag::autoLink(trim($word)) ) != strlen( trim($originalWord) ) )
                {
                    $str = mb_substr($originalWord, $split_length);
                    $str = $this->splitLongMessages($str);
                    $string_array[$id] = '<a href="'.$originalWord.'" target="_blank">'.mb_substr($originalWord, 7, $split_length) . $delimiter . $str."</a>";
                }
                else
                {
                    $str = mb_substr($word, $split_length);
                    $str = $this->splitLongMessages($str);
                    $string_array[$id] = mb_substr($word, 0, $split_length) . $delimiter . $str;
                }
            }
        }

        return implode(' ', $string_array);
    }

    public function findMessages( $user, $lastMessageId=null )
    {
        return $this->messageDao->findMessages($user, $lastMessageId);
    }

    /**
     *
     * @param string $name
     * @return Form
     */
    public function getUserSettingsForm()
    {
        $form = new Form('im_user_settings_form');
        $form->setAjax(true);
        $form->setAction(OW::getRouter()->urlFor('AJAXIM_CTRL_Action', 'processUserSettingsForm'));
        $form->setAjaxResetOnSuccess(false);
        $form->bindJsFunction(Form::BIND_SUCCESS, "function(data){
            OW_InstantChat_App.setSoundEnabled(data.im_soundEnabled);
        }");

        $findContact = new ImSearchField('im_find_contact');
        $findContact->setHasInvitation(true);
        $findContact->setInvitation(OW::getLanguage()->text('ajaxim', 'find_contact'));
        $form->addElement($findContact);

        $enableSound = new CheckboxField('im_enable_sound');
        $user_preference_enable_sound = BOL_PreferenceService::getInstance()->getPreferenceValue('ajaxim_user_settings_enable_sound', OW::getUser()->getId());
        $enableSound->setValue($user_preference_enable_sound);
        $enableSound->setLabel(OW::getLanguage()->text('ajaxim', 'enable_sound_label'));
        $form->addElement($enableSound);

        $userIdHidden = new HiddenField('user_id');
        $form->addElement($userIdHidden);


        return $form;
    }

    public function checkPermissions()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return "You need to sign in";
        }

        if ( !OW::getRequest()->isAjax() )
        {
            return "Ajax request required";
        }

        return false;
    }

     public function getUserInfoByNode( $user, $friendship )
     {
        $this->checkPermissions();

        $url = BOL_UserService::getInstance()->getUserUrl($user->getId());
        $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($user->getId());
        if ( empty($avatar) )
        {
            $avatar = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        }

        $is_friend = false;
        if ( !empty($friendship) && $friendship->getStatus() == 'active' )
        {
            $is_friend = true;
        }

        $info = array(
            'node' => $user->getId(),
            'username' => BOL_UserService::getInstance()->getDisplayName($user->getId()),
            'user_avatar_src' => $avatar,
            'user_url' => $url,
            'isFriend' => $is_friend,
            'isBlocker' =>  BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $user->getId()),
            'lastMessageTimestamp' => 1000 * AJAXIM_BOL_Service::getInstance()->getLastMessageTimestamp(OW::getUser()->getId(), $user->getId())
        );

        return $info;
    }
}


/**
 * Form element: ImSearchField.
 *
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ajaxim
 * @since 1.0
 */
class ImSearchField extends InvitationFormElement
{
    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'text');
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        if ( $this->getValue() !== null )
        {
            $this->addAttribute('value', $this->value);
        }
        else if ( $this->getHasInvitation() )
        {
            $this->addAttribute('value', $this->invitation);
            $this->addAttribute('class', 'invitation');
        }

        return UTIL_HtmlTag::generateTag('input', $this->attributes).'<a href="javascript://" class="ow_btn_close_search" id="'.$this->attributes['name'].'_close_btn_search"></a>';
    }

    public function getElementJs()
    {
        $jsString = "var formElement = new ImSearchField(" . json_encode($this->getId()) . ", " . json_encode($this->getName()) . ", " . json_encode(( $this->getHasInvitation() ? $this->getInvitation() : false)) . ");";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $jsString;
    }
}