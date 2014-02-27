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
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_plugins.groups.classes
 * @since 1.0
 */
class GROUPS_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var GROUPS_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return GROUPS_CLASS_EventHandler
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
     * @var GROUPS_BOL_Service
     */
    private $service;

    private function __construct()
    {
        $this->service = GROUPS_BOL_Service::getInstance();
    }
    
    public function onAddNewContent( BASE_CLASS_EventCollector $event )
    {
        if (GROUPS_BOL_Service::getInstance()->isCurrentUserCanCreate())
        {
            $event->add(array(
                BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_comment',
                BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlForRoute('groups-create'),
                BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('groups', 'add_new_label')
            ));
        }
    }
    
    public function onBeforeGroupDelete( OW_Event $event )
    {
        $params = $event->getParams();
        $groupId = $params['groupId'];

        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        $fileName = GROUPS_BOL_Service::getInstance()->getGroupImagePath($group);

        if ( $fileName !== null )
        {
            OW::getStorage()->removeFile($fileName);
        }
    }
    
    public function onAfterGroupDelete( OW_Event $event )
    {
        $params = $event->getParams();

        $groupId = $params['groupId'];

        BOL_ComponentEntityService::getInstance()->onEntityDelete(GROUPS_BOL_Service::WIDGET_PANEL_NAME, $groupId);
        BOL_CommentService::getInstance()->deleteEntityComments(GROUPS_BOL_Service::ENTITY_TYPE_WAL, $groupId);

        BOL_FlagService::getInstance()->deleteByTypeAndEntityId(GROUPS_BOL_Service::ENTITY_TYPE_GROUP, $groupId);

        OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array(
            'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'entityId' => $groupId
        )));
    }
    
    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = (int) $params['userId'];

        GROUPS_BOL_Service::getInstance()->onUserUnregister( $userId, !empty($params['deleteContent']) );
    }
    
    public function onForumCheckPermissions( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['entityId']) || !isset($params['entity']) )
        {
            return;
        }

        if ( $params['entity'] == 'groups' )
        {
            $groupService = GROUPS_BOL_Service::getInstance();

            if ( $params['action'] == 'edit_topic' )
            {
                $group = $groupService->findGroupById($params['entityId']);

                if ( $group->userId == OW::getUser()->getId() || OW::getUser()->isAuthorized($params['entity']) )
                {
                    $event->setData(true);
                }
            }
            else if ( $params['action'] == 'add_topic' )
            {
                if ( OW::getUser()->isAuthorized($params['entity'], 'add_topic') && $groupService->findUser($params['entityId'], OW::getUser()->getId()) )
                {
                    $event->setData(true);
                }
                else
                {
                    $event->setData(false);
                }
            }
            else if ( $groupService->findUser($params['entityId'], OW::getUser()->getId()) )
            {
                $event->setData(true);
            }
            else
            {
                $event->setData(false);
            }
        }
    }
    
    public function onForumFindCaption( OW_Event $event )
    {

        $params = $event->getParams();
        if ( !isset($params['entity']) || !isset($params['entityId']) )
        {
            return;
        }

        if ( $params['entity'] == 'groups' )
        {
            $component = new GROUPS_CMP_BriefInfo($params['entityId']);
            $eventData['component'] = $component;
            $eventData['key'] = 'main_menu_list';
            $event->setData($eventData);
        }
    }
    
    public function onCollectAdminNotifications( BASE_CLASS_EventCollector $event )
    {
        $is_forum_connected = OW::getConfig()->getValue('groups', 'is_forum_connected');

        if ( $is_forum_connected && !OW::getPluginManager()->isPluginActive('forum') )
        {
            $language = OW::getLanguage();

            $event->add($language->text('groups', 'error_forum_disconnected', array('url' => OW::getRouter()->urlForRoute('admin_plugins_installed'))));
        }
    }
    
    public function onForumUninstall( OW_Event $event )
    {
        $config = OW::getConfig();

        if ( $config->getValue('groups', 'is_forum_connected') )
        {
            $event = new OW_Event('forum.delete_section', array('entity' => 'groups'));
            OW::getEventManager()->trigger($event);

            $event = new OW_Event('forum.delete_widget');
            OW::getEventManager()->trigger($event);

            $config->saveConfig('groups', 'is_forum_connected', 0);

            $actionId = BOL_AuthorizationActionDao::getInstance()->getIdByName('add_topic');

            BOL_AuthorizationService::getInstance()->deleteAction($actionId);
        }
    }
    
    public function onForumActivate( OW_Event $event )
    {
        $is_forum_connected = OW::getConfig()->getValue('groups', 'is_forum_connected');

        // Add latest topic widget if forum plugin is connected
        if ( $is_forum_connected )
        {
            $event->setData(array('forum_connected' => true, 'place' => 'group', 'section' => BOL_ComponentAdminService::SECTION_RIGHT));
        }
    }
    
    public function onAfterGroupCreate( OW_Event $event )
    {
        $params = $event->getParams();
        $groupId = (int) $params['groupId'];

        $event = new OW_Event('feed.action', array(
            'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'entityId' => $groupId,
            'pluginKey' => 'groups'
        ));

        OW::getEventManager()->trigger($event);
    }
    
    public function onFeedEntityAction( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['entityType'] != GROUPS_BOL_Service::FEED_ENTITY_TYPE )
        {
            return;
        }

        $groupId = (int) $params['entityId'];
        $groupService = GROUPS_BOL_Service::getInstance();
        $group = $groupService->findGroupById($groupId);

        if ( $group === null )
        {
            return;
        }

        $private = $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE;
        $visibility = $private
                ? 4 + 8 // Visible for autor (4) and current feed (8)
                : 15; // Visible for all (15)

        $content = array(
            "format" => "image_content",
            "vars" => array(
                "image" => $groupService->getGroupImageUrl($group, GROUPS_BOL_Service::IMAGE_SIZE_BIG),
                "thumbnail" => $groupService->getGroupImageUrl($group),
                "title" => UTIL_String::truncate(strip_tags($group->title), 100, '...'),
                "description" => UTIL_String::truncate(strip_tags($group->description), 150, '...'),
                "url" => array( "routeName" => "groups-view", "vars" => array('groupId' => $group->id)),
                "iconClass" => "ow_ic_group"
            )
        );

        $data = array(
            'params' => array(
                'feedType' => 'groups',
                'feedId' => $groupId,
                'visibility' => $visibility,
                'postOnUserFeed' => !$private
            ),
            'ownerId' => $group->userId,
            'time' => (int) $group->timeStamp,
            'string' => array("key" => "groups+feed_create_string"),
            'content' => $content,
            'view' => array(
                'iconClass' => 'ow_ic_files'
            )
        );

        $e->setData($data);
    }
    
    public function onAfterGroupEdit( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $groupId = (int) $params['groupId'];

        $groupService = GROUPS_BOL_Service::getInstance();
        $group = $groupService->findGroupById($groupId);
        $private = $group->whoCanView == GROUPS_BOL_Service::WCV_INVITE;
        
        $event = new OW_Event('feed.action', array(
            'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'entityId' => $groupId,
            'pluginKey' => 'groups'
        ));

        OW::getEventManager()->trigger($event);

        if ( $private )
        {
            $users = $groupService->findGroupUserIdList($groupId);
            $follows = OW::getEventManager()->call('feed.get_all_follows', array(
                'feedType' => 'groups',
                'feedId' => $groupId
            ));

            foreach ( $follows as $follow )
            {
                if ( in_array($follow['userId'], $users) )
                {
                    continue;
                }

                OW::getEventManager()->call('feed.remove_follow', array(
                    'feedType' => 'groups',
                    'feedId' => $groupId,
                    'userId' => $follow['userId']
                ));
            }
        }
    }
    
    public function onGroupUserJoin( OW_Event $e )
    {
        $params = $e->getParams();

        $groupId = (int) $params['groupId'];
        $userId = (int) $params['userId'];
        $groupUserId = (int) $params['groupUserId'];

        $groupService = GROUPS_BOL_Service::getInstance();
        $group = $groupService->findGroupById($groupId);

        if ( $group->userId == $userId )
        {
            return;
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'groups-join',
            'activityId' => $userId,
            'entityId' => $group->id,
            'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'userId' => $userId,
            'pluginKey' => 'groups',
            'feedType' => 'groups',
            'feedId' => $group->id
        ), array(
            'groupId' => $group->id,
            'userId' => $userId,
            'groupUserId' => $groupUserId,

            'string' => OW::getLanguage()->text('groups', 'user_join_activity_string'),
            'features' => array()
        )));

        $url = $groupService->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

        $data = array(
            'time' => time(),
            'string' => OW::getLanguage()->text('groups', 'feed_join_string',
                array(
                    'groupTitle' => $title,
                    'groupUrl' => $url
                )
            ),
            'view' => array(
                'iconClass' => 'ow_ic_add'
            ),
            'data' => array(
                'joinUsersId' => $userId
            )
        );

        $event = new OW_Event('feed.action', array(
            'feedType' => 'groups',
            'feedId' => $group->id,
            'entityType' => 'groups-join',
            'entityId' => $groupUserId,
            'pluginKey' => 'groups',
            'userId' => $userId,
            'visibility' => 8,
            'postOnUserFeed' => false
        ), $data);

        OW::getEventManager()->trigger($event);
    }
    
    public function onFeedCollectWidgets( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'place' => 'group',
            'section' => BOL_ComponentService::SECTION_RIGHT,
            'order' => 0
        ));
    }
    
    public function onForumCollectWidgetPlaces( BASE_CLASS_EventCollector $e )
    {
        if ( OW::getConfig()->getValue('groups', 'is_forum_connected') )
        {
            $e->add(array(
                'place' => 'group',
                'section' => BOL_ComponentService::SECTION_RIGHT,
                'order' => 0
            ));
        }
    }
    
    public function onFeedWidgetConstruct( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['feedType'] != 'groups' )
        {
            return;
        }

        $data = $e->getData();

        if ( !OW::getUser()->isAuthorized('groups', 'add_comment') )
        {
            $data['statusForm'] = false;
            $e->setData($data);

            return;
        }

        $groupId = (int) $params['feedId'];
        $userId = OW::getUser()->getId();

        $userDto = GROUPS_BOL_Service::getInstance()->findUser($groupId, $userId);


        $data['statusForm'] = $userDto !== null;

        $e->setData($data);
    }
    
    public function onGroupToolbarCollect( BASE_CLASS_EventCollector $e )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        $params = $e->getParams();
        $backUri = OW::getRequest()->getRequestUri();

        if ( OW::getEventManager()->call('feed.is_inited') )
        {
            $url = OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'follow');

            $eventParams = array(
                'userId' => OW::getUser()->getId(),
                'feedType' => GROUPS_BOL_Service::ENTITY_TYPE_GROUP,
                'feedId' => $params['groupId']
            );

            if ( !OW::getEventManager()->call('feed.is_follow', $eventParams) )
            {
                $e->add(array(
                    'label' => OW::getLanguage()->text('groups', 'feed_group_follow'),
                    'href' => OW::getRequest()->buildUrlQueryString($url, array(
                        'backUri' => $backUri,
                        'groupId' => $params['groupId'],
                        'command' => 'follow'))
                ));
            }
            else
            {
                $e->add(array(
                    'label' => OW::getLanguage()->text('groups', 'feed_group_unfollow'),
                    'href' => OW::getRequest()->buildUrlQueryString($url, array(
                        'backUri' => $backUri,
                        'groupId' => $params['groupId'],
                        'command' => 'unfollow'))
                ));
            }
        }
    }
    
    public function onAdsCollectEnabledPlugins( BASE_EventCollector $event )
    {
        $event->add('groups');
    }
    
    public function findAllGroupsUsers( OW_Event $e )
    {
        $out = GROUPS_BOL_Service::getInstance()->findAllGroupsUserList();
        $e->setData($out);

        return $out;
    }
    
    public function onFeedCollectFollow( BASE_CLASS_EventCollector $e )
    {
        $groupUsers = GROUPS_BOL_Service::getInstance()->findAllGroupsUserList();
        foreach ( $groupUsers as $groupId => $users )
        {
            foreach ( $users as $userId )
            {
                $e->add(array(
                    'feedType' => 'groups',
                    'feedId' => $groupId,
                    'userId' => $userId
                ));
            }
        }
    }
    
    public function onGroupUserJoinFeedAddFollow( OW_Event $event )
    {
        $params = $event->getParams();

        $groupId = $params['groupId'];
        $userId = $params['userId'];

        OW::getEventManager()->call('feed.add_follow', array(
            'feedType' => 'groups',
            'feedId' => $groupId,
            'userId' => $userId
        ));
    }
    
    public function onFeedStatusAdd( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['entityType'] != 'groups-status' )
        {
            return;
        }

        $service = GROUPS_BOL_Service::getInstance();
        $group = $service->findGroupById($params['feedId']);
        $url = $service->getGroupUrl($group);
        $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

        $data['context'] = array(
            'label' => $title,
            'url' => $url
        );

        $event->setData($data);
    }
    
    public function onFeedItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        $groupActions = array(
            'groups-status'
        );

        if ( in_array($params['action']['entityType'], $groupActions) && $params['feedType'] == 'groups' )
        {
            $data['context'] = null;
        }

        if ( $params['action']['entityType'] == 'forum-topic' && isset($data['contextFeedType'])
                && $data['contextFeedType'] == 'groups' && $data['contextFeedType'] != $params['feedType'] )
        {
            $service = GROUPS_BOL_Service::getInstance();
            $group = $service->findGroupById($data['contextFeedId']);
            $url = $service->getGroupUrl($group);
            $title = UTIL_String::truncate(strip_tags($group->title), 100, '...');

            $data['context'] = array(
                'label' => $title,
                'url' => $url
            );
        }

        $actionUserId = $userId = (int) $data['action']['userId'];
        if ( OW::getUser()->isAuthenticated() && in_array($params['feedType'], array('groups')) )
        {
            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($params['feedId']);
            $isGroupOwner = $groupDto->userId == OW::getUser()->getId();
            $isGroupModerator = OW::getUser()->isAuthorized('groups');

            if ( $actionUserId != OW::getUser()->getId() && ($isGroupOwner || $isGroupModerator) )
            {
                $groupUserDto = GROUPS_BOL_Service::getInstance()->findUser($groupDto->id, $actionUserId);
                if ( $groupUserDto !== null )
                {
                    $data['contextMenu'] = empty($data['contextMenu']) ? array() : $data['contextMenu'];


                    if ( $groupDto->userId == $userId )
                    {
                        array_unshift($data['contextMenu'], array(
                            'label' => OW::getLanguage()->text('groups', 'delete_group_user_label'),
                            'url' => 'javascript://',
                            'attributes' => array(
                                'data-message' => OW::getLanguage()->text('groups', 'group_owner_delete_error'),
                                'onclick' => 'OW.error($(this).data().message); return false;'
                            )
                        ));
                    }
                    else
                    {
                        $callbackUri = OW::getRequest()->getRequestUri();
                        $deleteUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('GROUPS_CTRL_Groups', 'deleteUser', array(
                            'groupId' => $groupDto->id,
                            'userId' => $userId
                        )), array(
                            'redirectUri' => urlencode($callbackUri)
                        ));

                        array_unshift($data['contextMenu'], array(
                            'label' => OW::getLanguage()->text('groups', 'delete_group_user_label'),
                            'url' => $deleteUrl,
                            'attributes' => array(
                                'data-message' => OW::getLanguage()->text('groups', 'delete_group_user_confirmation'),
                                'onclick' => 'return confirm($(this).data().message);'
                            )
                        ));
                    }
                }
            }

            $canRemove = $isGroupOwner || $params['action']['userId'] == OW::getUser()->getId() || $isGroupModerator;

            if ( $canRemove )
            {
                array_unshift($data['contextMenu'], array(
                    'label' => OW::getLanguage()->text('groups', 'delete_feed_item_label'),
                    'class' => 'newsfeed_remove_btn',
                    'attributes' => array(
                        'rel' => OW::getLanguage()->text('groups', 'delete_feed_item_confirmation')
                    )
                ));
            }
        }

        $event->setData($data);
    }
    
    public function onFeedItemRenderActivity( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();

        if ( $params['action']['entityType'] != GROUPS_BOL_Service::FEED_ENTITY_TYPE || $params['feedType'] == 'groups')
        {
            return;
        }

        $groupId = $params['action']['entityId'];
        $usersCount = GROUPS_BOL_Service::getInstance()->findUserListCount($groupId);

        if ( $usersCount == 1 )
        {
            return;
        }

        $users = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($groupId, GROUPS_BOL_Service::PRIVACY_EVERYBODY);
        $activityUserIds = array();

        foreach ( $params['activity'] as $activity )
        {
            if ( $activity['activityType'] == 'groups-join')
            {
                $activityUserIds[] = $activity['data']['userId'];
            }
        }

        $lastUserId = reset($activityUserIds);
        $follows = array_intersect($activityUserIds, $users);
        $notFollows = array_diff($users, $activityUserIds);
        $idlist = array_merge($follows, $notFollows);

        $viewMoreUrl = null;
        
        if ( count($idlist) > 5 )
        {
            $viewMoreUrl = array("routeName" => "groups-user-list", "vars" => array(
                "groupId" => $groupId
            ));
        }
        
        if ( is_array($data["content"])  )
        {
            $data["content"]["vars"]["userList"] = array(
                "label" => array(
                    "key" => "groups+feed_activity_users",
                    "vars" => array(
                        "usersCount" => $usersCount
                    )
                ),
                "viewAllUrl" => $viewMoreUrl,
                "ids" => array_slice($idlist, 0, 5)
            );
        }
        else // Backward compatibility
        {
            $avatarList = new BASE_CMP_MiniAvatarUserList( array_slice($idlist, 0, 5) );
            $avatarList->setEmptyListNoRender(true);

            if ( count($idlist) > 5 )
            {
                $avatarList->setViewMoreUrl(OW::getRouter()->urlForRoute($viewMoreUrl["routeName"], $viewMoreUrl["vars"]));
            }

            $language = OW::getLanguage();
            $content = $avatarList->render();

            if ( $lastUserId )
            {
                $userName = BOL_UserService::getInstance()->getDisplayName($lastUserId);
                $userUrl = BOL_UserService::getInstance()->getUserUrl($lastUserId);
                $content .= $language->text('groups', 'feed_activity_joined', array('user' => '<a href="' . $userUrl . '">' . $userName . '</a>'));
            }

            $data['assign']['activity'] = array('template' => 'activity', 'vars' => array(
                'title' => $language->text('groups', 'feed_activity_users', array('usersCount' => $usersCount)),
                'content' => $content
            ));
        }

        $event->setData($data);
    }
    
    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'groups' => array(
                    'label' => $language->text('groups', 'auth_group_label'),
                    'actions' => array(
                        'add_topic' => $language->text('groups', 'auth_action_label_add_topic'),
                        'create' => $language->text('groups', 'auth_action_label_create'),
                        'view' => $language->text('groups', 'auth_action_label_view'),
                        'add_comment' => $language->text('groups', 'auth_action_label_wall_post'),
                        'delete_comment_by_content_owner' => $language->text('groups', 'auth_action_label_delete_comment_by_content_owner')
                    )
                )
            )
        );
    }
    
    public function onFeedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('groups', 'feed_content_label'),
            'activity' => '*:' . GROUPS_BOL_Service::FEED_ENTITY_TYPE
        ));
    }
    
    public function onPrivacyCollectActions( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS,
            'pluginKey' => 'groups',
            'label' => $language->text('groups', 'privacy_action_view_my_groups'),
            'description' => '',
            'defaultValue' => GROUPS_BOL_Service::PRIVACY_EVERYBODY,
            'sortOrder' => 1000
        );

        $event->add($action);
    }
    
    public function onFeedCollectPrivacy( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('groups-join:*', GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
        $event->add(array('create:groups-join', GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
        $event->add(array('create:' . GROUPS_BOL_Service::FEED_ENTITY_TYPE, GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS));
    }
    
    public function onPrivacyChange( OW_Event $e )
    {
        $params = $e->getParams();

        $userId = (int) $params['userId'];
        $actionList = $params['actionList'];
        $actionList = is_array($actionList) ? $actionList : array();

        if ( empty($actionList[GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS]) )
        {
            return;
        }

        GROUPS_BOL_Service::getInstance()->setGroupUserPrivacy($userId, $actionList[GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS]);
        //GROUPS_BOL_Service::getInstance()->setGroupsPrivacy($userId, $actionList[GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS]);
    }
    
    public function onBeforeUserJoin( OW_Event $event )
    {
        $data = $event->getData();
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        $privacy = GROUPS_BOL_Service::PRIVACY_EVERYBODY;

        $t = OW::getEventManager()->call('plugin.privacy.get_privacy', array(
            'ownerId' => $params['userId'],
            'action' => GROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS
        ));

        $data['privacy'] = empty($t) ? $privacy : $t;

        $event->setData($data);
    }
    
    public function onForumCanView( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['entityId']) || !isset($params['entity']) )
        {
            return;
        }

        if ( $params['entity'] != 'groups' )
        {
            return;
        }


        $groupId = $params['entityId'];
        $group = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);

        if ( empty($group) )
        {
            return;
        }

        $privateUrl = OW::getRouter()->urlForRoute('groups-private-group', array(
            'groupId' => $group->id
        ));

        $canView = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($group->userId);

        if ( $group->whoCanView != GROUPS_BOL_Service::WCV_INVITE )
        {
            $event->setData($canView);

            return;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new RedirectException($privateUrl);
        }

        $isUser = GROUPS_BOL_Service::getInstance()->findUser($group->id, OW::getUser()->getId()) !== null;

        if ( !$isUser && !OW::getUser()->isAuthorized('groups') )
        {
            throw new RedirectException($privateUrl);
        }
    }
    
    public function onCollectQuickLinks( BASE_CLASS_EventCollector $event )
    {
        $service = GROUPS_BOL_Service::getInstance();
        $userId = OW::getUser()->getId();

        $groupsCount = $service->findMyGroupsCount($userId);
        $invitesCount = $service->findUserInvitedGroupsCount($userId, true);

        if ( $groupsCount > 0 || $invitesCount > 0 )
        {
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('groups', 'my_groups'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => OW::getRouter()->urlForRoute('groups-my-list'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $groupsCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => OW::getRouter()->urlForRoute('groups-my-list'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_ACTIVE_COUNT => $invitesCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_ACTIVE_COUNT_URL => OW::getRouter()->urlForRoute('groups-invite-list')
            ));
        }
    }
    
    public function onAfterFeedCommentAdd( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != GROUPS_BOL_Service::FEED_ENTITY_TYPE )
        {
            return;
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'comment',
            'activityId' => $params['commentId'],
            'entityId' => $params['entityId'],
            'entityType' => GROUPS_BOL_Service::FEED_ENTITY_TYPE,
            'userId' => $params['userId'],
            'pluginKey' => 'groups'
        ), array(
            'string' => OW::getLanguage()->text('groups', 'comment_activity_string'),
            'features' => array('comments')
        )));
    }
    
    public function cleanCache( OW_Event $event )
    {
        GROUPS_BOL_Service::getInstance()->clearListingCache();
    }
    
    public function sosialSharingGetGroupInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( $params['entityType'] == 'groups' )
        {
            if ( !BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser(0, 'groups', 'view') )
            {
                $event->setData($data);
                return;
            }

            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($params['entityId']);
            
            if ( !empty($groupDto) )
            {
                $data['display'] = $groupDto->whoCanView !==  GROUPS_BOL_Service::WCV_INVITE;
            }
        }

        $event->setData($data);
    }

    
    public function genericInit()
    {
        $eventHandler = $this;
        
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_ON_DELETE, array($eventHandler, "onBeforeGroupDelete"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_DELETE_COMPLETE, array($eventHandler, "onAfterGroupDelete"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_CREATE, array($eventHandler, "onAfterGroupCreate"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_DELETE_COMPLETE, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_CREATE, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_EDIT, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_ADDED, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_DELETED, array($eventHandler, "cleanCache"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_EDIT, array($eventHandler, "onAfterGroupEdit"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_ADDED, array($eventHandler, "onGroupUserJoin"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_ADDED, array($eventHandler, "onGroupUserJoinFeedAddFollow"));
        OW::getEventManager()->bind(GROUPS_BOL_Service::EVENT_USER_BEFORE_ADDED, array($eventHandler, "onBeforeUserJoin"));
        
        OW::getEventManager()->bind('groups.on_toolbar_collect', array($eventHandler, "onGroupToolbarCollect"));
        OW::getEventManager()->bind('groups.get_all_group_users', array($eventHandler, "findAllGroupsUsers"));
        
        OW::getEventManager()->bind('feed.on_entity_action', array($eventHandler, "onFeedEntityAction"));
        OW::getEventManager()->bind('feed.collect_follow', array($eventHandler, "onFeedCollectFollow"));
        OW::getEventManager()->bind('feed.collect_privacy', array($eventHandler, "onFeedCollectPrivacy"));
        OW::getEventManager()->bind('feed.on_entity_add', array($eventHandler, "onFeedStatusAdd"));
        OW::getEventManager()->bind('feed.collect_configurable_activity', array($eventHandler, "onFeedCollectConfigurableActivity"));
        OW::getEventManager()->bind('feed.after_comment_add', array($eventHandler, "onAfterFeedCommentAdd"));
        
        OW::getEventManager()->bind('plugin.privacy.get_action_list', array($eventHandler, "onPrivacyCollectActions"));
        OW::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($eventHandler, "onPrivacyChange"));
        
        OW::getEventManager()->bind('forum.check_permissions', array($eventHandler, "onForumCheckPermissions"));
        OW::getEventManager()->bind('forum.can_view', array($eventHandler, 'onForumCanView'));
        
        OW::getEventManager()->bind(OW_EventManager::ON_USER_UNREGISTER, array($eventHandler, "onUserUnregister"));
        OW::getEventManager()->bind('ads.enabled_plugins', array($eventHandler, "onAdsCollectEnabledPlugins"));
        OW::getEventManager()->bind('admin.add_auth_labels', array($eventHandler, "onCollectAuthLabels"));
        
        OW::getEventManager()->bind('feed.on_item_render', array($eventHandler, "onFeedItemRenderActivity"));
    }
}