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
 * @package ow_plugins.links.components
 * @since 1.0
 */
class LINKS_CMP_UserLinksWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = LinkService::getInstance();

        $userId = $params->additionalParamList['entityId'];

        if ( $userId != OW::getUser()->getId() && !OW::getUser()->isAuthorized('links', 'view') )
        {
            $this->setVisible(false);
            return;
        }

        /* Check privacy permissions */

        $eventParams = array(
            'action' => LinkService::PRIVACY_ACTION_VIEW_LINKS,
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );

        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $ex )
        {
            $this->setVisible(false);
            return;
        }
        /* */

        if ( $service->countUserLinks($userId) == 0 && !$params->customizeMode )
        {
            $this->setVisible(false);
            return;
        }

        $this->assign('displayname', BOL_UserService::getInstance()->getDisplayName($userId));
        $this->assign('username', BOL_UserService::getInstance()->getUsername($userId));

        $list = array();

        $count = $params->customParamList['count'];

        $userLinkList = $service->findUserLinkList($userId, 0, $count);

        $idList = array();

        foreach ( $userLinkList as $item )
        {
            /* Check privacy permissions */
            if ( $item->userId != OW::getUser()->getId() && !OW::getUser()->isAuthorized('links') )
            {
                $eventParams = array(
                    'action' => LinkService::PRIVACY_ACTION_VIEW_LINKS,
                    'ownerId' => $item->userId,
                    'viewerId' => OW::getUser()->getId()
                );

                try
                {
                    OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                }
                catch ( RedirectException $ex )
                {
                    continue;
                }
            }
            /* */

            $list[] = $item;
            $idList[] = $item->id;
        }

        $commentInfo = array();

        if ( !empty($idList) )
        {
            $commentInfo = BOL_CommentService::getInstance()->findCommentCountForEntityList('link', $idList);

            $tb = array();
            foreach ( $list as $key => $item )
            {
                if ( mb_strlen($item->getDescription()) > 100 )
                {
                    $item->setDescription(UTIL_String::truncate($item->getDescription(), 100, '...'));
                }

                $list[$key]->setDescription(strip_tags($item->getDescription()));


                $tb[$item->getId()] = array(
                    array(
                        'label' => '<span class="ow_txt_value">' . $commentInfo[$item->getId()] . '</span> ' . OW::getLanguage()->text('links', 'comments'),
                        'href' => OW::getRouter()->urlForRoute('link', array('id' => $item->getId()))
                    ),
                    array(
                        'label' => UTIL_DateTime::formatDate($item->getTimestamp()),
                        'class' => 'ow_ic_date'
                    )
                );
            }

            $this->assign('tb', $tb);
        }

        $this->assign('list', $list);

        $this->setSettingValue(self::SETTING_TOOLBAR, array(
            array('label' => OW::getLanguage()->text('base', 'view_all'), 'href' => OW::getRouter()->urlForRoute('links-user', array('user' =>  BOL_UserService::getInstance()->getUserName($userId) )))
        ));
    }

    public static function getSettingList()
    {
        $settingList = array();

        $options = array();

        for ( $i = 3; $i <= 10; $i++ )
        {
            $options[$i] = $i;
        }

        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('links', 'cmp_widget_post_count'),
            'optionList' => $options,
            'value' => 3,
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('links', 'main_menu_item'),
            self::SETTING_ICON => 'ow_ic_link',
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_SHOW_TITLE => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}