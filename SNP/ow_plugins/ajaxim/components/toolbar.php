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
 * @package ow.ow_system_plugins.base.controllers
 * @since 1.0
 */
class AJAXIM_CMP_Toolbar extends OW_Component
{

    public function render()
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("base")->getStaticJsUrl() . "jquery-ui.min.js");
        
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('ajaxim')->getStaticJsUrl() . 'audio-player.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('ajaxim')->getStaticJsUrl() . 'ajaxim.js');

        $node = OW::getUser()->getId();
        $password = '';
        $domain = 'localhost';
        $port = 0;

        $username = BOL_UserService::getInstance()->getDisplayName($node);
        $avatar = BOL_AvatarService::getInstance()->getAvatarUrl(OW::getUser()->getId());

        if ( empty($avatar) )
        {
            $avatar = BOL_AvatarService::getInstance()->getDefaultAvatarUrl();
        }

        $jsGenerator = UTIL_JsGenerator::newInstance();
        $jsGenerator->setVariable('im_oldTitle', OW::getDocument()->getTitle());
        $jsGenerator->setVariable('im_soundEnabled', (bool) BOL_PreferenceService::getInstance()->getPreferenceValue('ajaxim_user_settings_enable_sound', OW::getUser()->getId()));
        $jsGenerator->setVariable('im_awayTimeout', 5000); //TODO change to 30000
        $jsGenerator->setVariable('im_soundSwf', OW::getPluginManager()->getPlugin('ajaxim')->getStaticUrl() . 'js/player.swf');
        $jsGenerator->setVariable('im_soundUrl', OW::getPluginManager()->getPlugin('ajaxim')->getStaticUrl() . 'sound/receive.mp3');

        $jsGenerator->setVariable('window.ajaximLogMsgUrl', OW::getRouter()->urlFor('AJAXIM_CTRL_Action', 'logMsg'));
        $jsGenerator->setVariable('window.ajaximGetLogUrl', OW::getRouter()->urlFor('AJAXIM_CTRL_Action', 'getLog'));
        $jsGenerator->setVariable('window.im_updateUserInfoUrl', OW::getRouter()->urlFor('AJAXIM_CTRL_Action', 'updateUserInfo'));
        $jsGenerator->setVariable('window.im_userBlockedMessage', OW::getLanguage()->text('base', 'user_block_message') );

        $site_timezone = OW::getConfig()->getValue('base', 'site_timezone');
        $site_datetimezone = new DateTimeZone($site_timezone);
        $site_datetime = new DateTime("now", $site_datetimezone);

        $jsGenerator->setVariable('ajaximSiteTimezoneOffset', $site_datetimezone->getOffset($site_datetime) * 1000 );

        $isFriendsOnlyMode = (bool)OW::getEventManager()->call('plugin.friends');
        $jsGenerator->setVariable('im_isFriendsOnlyMode', (bool)$isFriendsOnlyMode);

        $privacyPluginActive = OW::getEventManager()->call('plugin.privacy');
        $this->assign('privacyPluginActive', $privacyPluginActive);

        $privacyActionValue = OW::getEventManager()->call('plugin.privacy.get_privacy', array('ownerId' => OW::getUser()->getId(), 'action' => 'ajaxim_invite_to_chat'));

        $jsGenerator->setVariable('im_privacyActionValue', $privacyActionValue);
        if ($privacyPluginActive)
        {
            $this->assign('privacy_settings_url', OW::getRouter()->urlForRoute('privacy_index'));
            $visibleForFriends = $privacyActionValue == 'friends_only';
            $visibleForEverybody = $privacyActionValue == 'everybody';
        }
        else
        {
            $visibleForFriends = false;
            $visibleForEverybody = true;
        }

        $jsGenerator->setVariable('im_visibleForFriends', $visibleForFriends);
        $jsGenerator->setVariable('im_visibleForEverybody', $visibleForEverybody);

        $this->assign('im_sound_url', OW::getPluginManager()->getPlugin('ajaxim')->getStaticUrl() . 'sound/receive.mp3');

        /* Instant Chat DEBUG MODE */
        $debugMode = false;
        $jsGenerator->setVariable('im_debug_mode', $debugMode);
        $this->assign('debug_mode', $debugMode);

        $variables = $jsGenerator->generateJs();

        $details = array(
            'userId' => OW::getUser()->getId(),
            'node' => $node,
            'password' => $password,
            'username' => $username,
            'domain' => $domain,
            'avatar' => $avatar
        );
        OW::getDocument()->addScriptDeclaration("window.OW_InstantChat.Details = " . json_encode($details) . ";\n " . $variables);

        $userSettingsForm = AJAXIM_BOL_Service::getInstance()->getUserSettingsForm();
        $this->addForm($userSettingsForm);
        $userSettingsForm->getElement('user_id')->setValue(OW::getUser()->getId());

        $avatar_proto_data = array('url' => 1, 'src' => BOL_AvatarService::getInstance()->getDefaultAvatarUrl(), 'class' => 'talk_box_avatar');
        $this->assign('avatar_proto_data', $avatar_proto_data);
        
        $this->assign('no_avatar_url', BOL_AvatarService::getInstance()->getDefaultAvatarUrl());
        $this->assign('online_list_url', OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'online')));


        return parent::render();
    }
}
