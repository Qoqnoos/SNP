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
 * @package ow.ow_plugins.links.classes
 * @since 1.6.0
 */
class LINKS_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var LINKS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return LINKS_CLASS_EventHandler
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
     * @var LinkService
     */
    private $service;

    private function __construct()
    {
        $this->service = LinkService::getInstance();
    }

    public function genericInit()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER,    array($this, 'onUnregisterUser'));
        OW::getEventManager()->bind(LinkService::EVENT_EDIT,                array($this, 'onAfterEditLink'));
        OW::getEventManager()->bind('notifications.collect_actions',        array($this, 'onCollectNotificationActions'));
        OW::getEventManager()->bind('base_add_comment',                     array($this, 'onAddLinkComment'));
        OW::getEventManager()->bind('ads.enabled_plugins',                  array($this, 'onCollectEnabledAdsPages'));
        OW::getEventManager()->bind('admin.add_auth_labels',                array($this, 'onCollectAuthLabels'));
        OW::getEventManager()->bind('feed.collect_configurable_activity',   array($this, 'onCollectFeedConfigurableActivity'));
        OW::getEventManager()->bind('plugin.privacy.get_action_list',       array($this, 'onCollectPrivacyActionList'));
        OW::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($this, 'onChangeActionPrivacy'));
        OW::getEventManager()->bind('feed.on_entity_add',                   array($this, 'onAddLink'));
        OW::getEventManager()->bind('feed.after_comment_add',               array($this, 'onFeedAddComment'));
        OW::getEventManager()->bind('feed.after_like_added',                array($this, 'onFeedAddLike'));
        //OW::getEventManager()->bind('base_delete_comment',                array($this, 'onDeleteComment'));

        $credits = new LINKS_CLASS_Credits();
        OW::getEventManager()->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));
    }


    public function onCollectAddNewContentItem( BASE_CLASS_EventCollector $event )
    {
        if ( OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('links', 'add') )
        {
            $resultArray = array(
                BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_link',
                BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('link-save-new'),
                BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('links', 'add_new_link')
            );

            $event->add($resultArray);
        }
    }

    public function onUnregisterUser( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['deleteContent']) )
        {
            return;
        }

        $userId = $params['userId'];

        OW::getCacheManager()->clean( array( LinkDao::CACHE_TAG_LINK_COUNT ));

        $count = (int) $this->service->countUserLinks($userId);

        if ( $count == 0 )
        {
            return;
        }

        $list = $this->service->findUserLinkList($userId, 0, $count);

        foreach ( $list as $link )
        {
            $this->service->delete($link);
        }
    }

    public function onDeleteComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || $params['entityType'] !== 'link' )
            return;

        $entityId = $params['entityId'];
        $userId = $params['userId'];
        $commentId = (int) $params['commentId'];
    }

    public function onCollectNotificationActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'links',
            'action' => 'links-add_comment',
            'sectionLabel' => OW::getLanguage()->text('links', 'notification_section_label'),
            'description' => OW::getLanguage()->text('links', 'email_notifications_setting_comment'),
            'selected' => 1,
            'sectionIcon' => 'ow_ic_write'
        ));
    }

    public function onAddLinkComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || $params['entityType'] !== 'link' )
            return;

        $entityId = $params['entityId'];
        $userId = $params['userId'];
        $commentId = $params['commentId'];

        $linkService = LinkService::getInstance();

        $link = $linkService->findById($entityId);

        if (empty($link))
        {
            return;
        }

        if ($userId == $link->userId)
        {
            return;
        }

        $actor = array(
            'name' => BOL_UserService::getInstance()->getDisplayName($userId),
            'url' => BOL_UserService::getInstance()->getUserUrl($userId)
        );

        $comment = BOL_CommentService::getInstance()->findComment($commentId);

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));

        $event = new OW_Event('notifications.add', array(
            'pluginKey' => 'links',
            'entityType' => 'links-add_comment',
            'entityId' => (int) $comment->getId(),
            'action' => 'links-add_comment',
            'userId' => $link->getUserId(),
            'time' => time()
        ), array(
            'avatar' => $avatars[$userId],
            'string' => array(
                'key' => 'links+comment_notification_string',
                'vars' => array(
                    'actor' => $actor['name'],
                    'actorUrl' => $actor['url'],
                    'title' => $link->getTitle(),
                    'url' => OW::getRouter()->urlForRoute('link', array('id' => $link->getId()))
                )
            ),
            'content' => ( mb_strlen($comment->getMessage()) > 30 ) ? nl2br(mb_substr($comment->getMessage(), 0, 30)) . '...' : $comment->getMessage(),
            'url' => OW::getRouter()->urlForRoute('link', array('id' => $link->getId()))
        ));

        OW::getEventManager()->trigger($event);
    }

    public function onCollectEnabledAdsPages( BASE_EventCollector $event )
    {
        $event->add('links');
    }

    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'links' => array(
                    'label' => $language->text('links', 'auth_group_label'),
                    'actions' => array(
                        'add' => $language->text('links', 'auth_action_label_add'),
                        'view' => $language->text('links', 'auth_action_label_view'),
                        'add_comment' => $language->text('links', 'auth_action_label_add_comment'),
                        'delete_comment_by_content_owner' => $language->text('links', 'auth_action_label_delete_comment_by_content_owner')
                    )
                )
            )
        );
    }

    public function onCollectFeedConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('links', 'feed_content_label'),
            'activity' => '*:link'
        ));
    }

    public function onCollectPrivacyActionList( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => LinkService::PRIVACY_ACTION_VIEW_LINKS,
            'pluginKey' => 'links',
            'label' => $language->text('links', 'privacy_action_view_links'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);

        $action = array(
            'key' => LinkService::PRIVACY_ACTION_COMMENT_LINKS,
            'pluginKey' => 'links',
            'label' => $language->text('links', 'privacy_action_comment_links'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);

    }

    public function onChangeActionPrivacy( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        $actionList = $params['actionList'];
        $actionList = is_array($actionList) ? $actionList : array();

        if ( empty($actionList[LinkService::PRIVACY_ACTION_VIEW_LINKS]) )
        {
            return;
        }

        LinkService::getInstance()->updateLinksPrivacy($userId, $actionList[LinkService::PRIVACY_ACTION_VIEW_LINKS]);
    }

    public function onAddLink( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( $params['entityType'] != 'link' )
        {
            return;
        }

        $linkService = LinkService::getInstance();
        $link = $linkService->findById($params['entityId']);

        $url = OW::getRouter()->urlForRoute('link', array('id' => $link->id));

        $title = UTIL_String::truncate(strip_tags($link->title), 100, '...');
        $description = UTIL_String::truncate(strip_tags($link->description), 150, '...');

        $vars = array();
        $format = "content";
        
        if ( isset($data["content"]) && is_array($data["content"]) )
        {
            $vars = empty($data["content"]["vars"]) ? array() : $data["content"]["vars"];
            
            if ( !empty($data["content"]["format"]) )
            {
                $format = $data["content"]["format"];
            }
        }
        
        $data = array_merge($data, array(
            'time' => $link->timestamp,
            'ownerId' => $link->userId,
            'content' => array(
                'format' => $format,
                'vars' => array_merge(array(
                    'title' => $title,
                    'description' => $description,
                    'url' => $link->url,
                    'iconClass' => 'ow_ic_link'
                ), $vars)
            ),
            'view' => array(
                'iconClass' => 'ow_ic_link'
            ),
            'toolbar' => array(array(
                'href' => $url,
                'label' => OW::getLanguage()->text('links', 'feed_toolbar_permalink')
            ))
        ));

        $e->setData($data);
    }

    public function onAfterEditLink( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        $linkService = LinkService::getInstance();
        $link = $linkService->findById($params['id']);

        $url = OW::getRouter()->urlForRoute('link', array('id' => $link->id));

        $title = UTIL_String::truncate(strip_tags($link->title), 100, '...');
        $description = UTIL_String::truncate(strip_tags($link->description), 150, '...');

        $data = array(
            'time' => $link->timestamp,
            'ownerId' => $link->userId,
            'content' => array(
                'format' => 'content',
                'vars' => array(
                    'title' => $title,
                    'description' => $description,
                    'url' => $link->url,
                    'iconClass' => 'ow_ic_link'
                )
            ),
            'view' => array(
                'iconClass' => 'ow_ic_link'
            ),
            'toolbar' => array(array(
                'href' => $url,
                'label' => OW::getLanguage()->text('links', 'feed_toolbar_permalink')
            ))
        );

        $event = new OW_Event('feed.action',
            array(
                'pluginKey' => 'links',
                'entityType' => 'link',
                'entityId' => $link->id,
                'userId' => $link->userId
            ),
            $data
        );
        OW::getEventManager()->trigger($event);
    }

    public function onFeedAddComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'link' )
        {
            return;
        }

        $linkService = LinkService::getInstance();
        $link = $linkService->findById($params['entityId']);

        if (empty($link))
        {
            return;
        }

        $userId = $link->userId;

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        if ( $userId == $params['userId'] )
        {
            $string = OW::getLanguage()->text('links', 'feed_activity_owner_post_string');
        }
        else
        {
            $string = OW::getLanguage()->text('links', 'feed_activity_link_string', array( 'user' => $userEmbed ));
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'comment',
            'activityId' => $params['commentId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'links'
        ), array(
            'string' => $string
        )));
    }

    public function onFeedAddLike( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'link' )
        {
            return;
        }

        $linkService = LinkService::getInstance();
        $link = $linkService->findById($params['entityId']);
        $userId = $link->getUserId();

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        if ( $userId == $params['userId'] )
        {
            $string = OW::getLanguage()->text('links', 'feed_activity_owner_link_string_like');
        }
        else
        {
            $string = OW::getLanguage()->text('links', 'feed_activity_link_string_like', array( 'user' => $userEmbed ));
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'like',
            'activityId' => $params['userId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'links'
        ), array(
            'string' => $string
        )));
    }

    public function onCollectQuickLinks( BASE_CLASS_EventCollector $event )
    {
        $userId = OW::getUser()->getId();
        $username = OW::getUser()->getUserObject()->getUsername();

        $linkCount = (int) $this->service->countUserLinks($userId);

        if ( $linkCount > 0 )
        {
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('links', 'my_links'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => OW::getRouter()->urlForRoute('links-user', array('user'=>$username)),
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $linkCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => OW::getRouter()->urlForRoute('links-user', array('user'=>$username)),
            ));
        }
    }

}