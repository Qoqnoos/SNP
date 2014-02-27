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
 * @package ow.ow_plugins.ajaxim.controllers
 * @since 1.0
 */
class AJAXIM_CTRL_Action extends OW_ActionController
{
    public function getLog()
    {
        $service = AJAXIM_BOL_Service::getInstance();

        if ($errorMessage = $service->checkPermissions())
        {
            exit(json_encode(array('error'=>$errorMessage)));
        }

        $lastMessageTimestamp = $_POST['lastMessageTimestamp'] / 1000;

        $list = AJAXIM_BOL_Service::getInstance()->findLastMessages(OW::getUser()->getId(), $_POST['userId'], $lastMessageTimestamp, 10, $_POST['omit_last_message']);

        exit(json_encode($list));
    }

    public function logMsg()
    {
        $service = AJAXIM_BOL_Service::getInstance();

        if ($errorMessage = $service->checkPermissions())
        {
            exit(json_encode(array('error'=>$errorMessage)));
        }

        if ( empty($_POST['to']) )
        {
            exit(json_encode(array('error'=>"Receiver is not defined")));
        }

        if ( empty($_POST['message']) )
        {
            exit(json_encode(array('error'=>"Message is empty")));
        }

        $message = UTIL_HtmlTag::stripTags(UTIL_HtmlTag::stripJs($_POST['message']));

        $dto = new AJAXIM_BOL_Message();

        $dto->setFrom(OW::getUser()->getId());
        $dto->setTo($_POST['to']);
        $dto->setMessage($message);
        $dto->setTimestamp(time());
        $dto->setRead(0);

        AJAXIM_BOL_Service::getInstance()->save($dto);

        //$message = AJAXIM_BOL_Service::getInstance()->splitLongMessages($message);
        //$dto->setMessage(UTIL_HtmlTag::autoLink($message));
        $dto->setTimestamp( $dto->getTimestamp() * 1000 );

        exit(json_encode($dto));
    }

    public function updateUserInfo()
    {
        //DDoS check
        if ( empty($_SESSION['lastUpdateRequestTimestamp']) )
        {
            $_SESSION['lastUpdateRequestTimestamp'] = time();
        }
        else if ( (time() - (int) $_SESSION['lastUpdateRequestTimestamp']) < 3 )
        {
            exit('{error: "Too much requests"}');
        }

        $_SESSION['lastUpdateRequestTimestamp'] = time();

        $service = AJAXIM_BOL_Service::getInstance();

        if ($errorMessage = $service->checkPermissions())
        {
            exit(json_encode(array('error'=>$errorMessage)));
        }

        /* @var BOL_User $user */
        $user = null;
        $friendship = null;
        if ( !empty($_POST['click']) && $_POST['click'] == 'online_now' )
        {
            $user = BOL_UserService::getInstance()->findUserById($_POST['userId']);

            if ( !OW::getAuthorization()->isUserAuthorized($user->getId(), 'ajaxim', 'chat') )
            {
                $info = array(
                    'warning' => true,
                    'message' => OW::getLanguage()->text('ajaxim', 'user_is_not_authorized_chat', array('username' => BOL_UserService::getInstance()->getDisplayName($user->getId()))),
                    'type' => 'warning'
                );
                exit(json_encode($info));
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
                $info = array(
                    'warning' => true,
                    'message' => OW::getLanguage()->text('ajaxim', 'warning_user_privacy_friends_only', array('displayname' => BOL_UserService::getInstance()->getDisplayName($user->getId()))),
                    'type' => 'warning'
                );
                exit(json_encode($info));
            }

            $isFriendsOnlyMode = (bool)OW::getEventManager()->call('plugin.friends');
            if ($isFriendsOnlyMode)
            {
                $friendship = OW::getEventManager()->call('plugin.friends.check_friendship', array('userId' => OW::getUser()->getId(), 'friendId' => $user->getId()));
                if ( empty($friendship) )
                {
                    $info = array(
                        'warning' => true,
                        'message' => OW::getLanguage()->text('ajaxim', 'warning_user_privacy_friends_only', array('displayname' => BOL_UserService::getInstance()->getDisplayName($user->getId()))),
                        'type' => 'warning'
                    );
                    exit(json_encode($info));
                }
                else if ( $friendship->getStatus() != 'active' )
                {
                    $info = array(
                        'warning' => true,
                        'message' => OW::getLanguage()->text('ajaxim', 'warning_user_privacy_friends_only', array('displayname' => BOL_UserService::getInstance()->getDisplayName($user->getId()))),
                        'type' => 'warning'
                    );
                    exit(json_encode($info));
                }

            }

            if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $user->getId()) )
            {
                $errorMessage = OW::getLanguage()->text('base', 'user_block_message');
                $info = array(
                    'warning' => true,
                    'message' => $errorMessage,
                    'type' => 'error'
                );
                exit(json_encode($info));
            }

            $onlineStatus = BOL_UserService::getInstance()->findOnlineStatusForUserList(array($user->getId()));
            if (!$onlineStatus[$user->getId()])
            {
                $displayname = BOL_UserService::getInstance()->getDisplayName($user->getId());
                $info = array(
                    'warning' => true,
                    'message' => OW::getLanguage()->text('ajaxim', 'user_went_offline', array('displayname'=>$displayname)),
                    'type' => 'warning'
                );
                exit(json_encode($info));
            }
        }
        else
        {
            if ( !empty($_POST['userId']) )
            {
                $user = BOL_UserService::getInstance()->findUserById($_POST['userId']);
            }
        }

        if ( empty($user) )
        {
            exit('{error: "User not found"}');
        }

        $friendship = OW::getEventManager()->call('plugin.friends.check_friendship', array('userId' => OW::getUser()->getId(), 'friendId' => $user->getId()));

        $info = '';

        switch ( $_POST['action'] )
        {
            case "open":

                $info['node'] = $user->getId();
                $info = $service->getUserInfoByNode($user, $friendship);

                break;

        }

        exit(json_encode($info));
    }

    public function processUserSettingsForm()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $form = AJAXIM_BOL_Service::getInstance()->getUserSettingsForm();

        if ( $form->isValid($_POST) )
        {
            $data = $form->getValues();
            BOL_PreferenceService::getInstance()->savePreferenceValue('ajaxim_user_settings_enable_sound', (bool) $data['im_enable_sound'], $data['user_id']);

            echo json_encode(
                array(
                    'im_soundEnabled' => (bool) $data['im_enable_sound']
                )
            );
            exit;
        }
    }
}

