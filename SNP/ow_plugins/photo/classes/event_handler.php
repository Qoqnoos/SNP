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
 * @package ow_plugins.photo.classes
 * @since 1.5.3
 */
class PHOTO_CLASS_EventHandler
{
    /**
     * @var PHOTO_CLASS_EventHandler
     */
    private static $classInstance;

    const EVENT_ALBUM_ADD = 'photo.album_add';
    const EVENT_ALBUM_FIND = 'photo.album_find';
    const EVENT_ALBUM_DELETE = 'photo.album_delete';
    const EVENT_ENTITY_ALBUMS_FIND = 'photo.entity_albums_find';
    
    const EVENT_ENTITY_PHOTOS_FIND = 'photo.entity_photos_find';
    const EVENT_ENTITY_PHOTOS_COUNT = 'photo.entity_photos_count';
    const EVENT_ENTITY_ALBUMS_DELETE = 'photo.entity_albums_delete';

    const EVENT_ON_ALBUM_ADD = 'photo.on_album_add';
    const EVENT_ON_ALBUM_EDIT = 'photo.on_album_edit';
    const EVENT_ON_ALBUM_DELETE = 'photo.on_album_delete';
    const EVENT_BEFORE_ALBUM_DELETE = 'photo.before_album_delete';

    const EVENT_PHOTO_ADD = 'photo.add';
    const EVENT_PHOTO_FIND = 'photo.find';
    const EVENT_PHOTO_DELETE = 'photo.delete';
    const EVENT_ALBUM_PHOTOS_FIND = 'photo.album_photos_find';
    const EVENT_INIT_FLOATBOX = 'photo.init_floatbox';

    const EVENT_ON_PHOTO_ADD = 'plugin.photos.add_photo';
    const EVENT_ON_PHOTO_EDIT = 'photo.after_edit';
    const EVENT_ON_PHOTO_DELETE = 'photo.after_delete';
    const EVENT_BEFORE_PHOTO_DELETE = 'photo.before_delete';

    const EVENT_SUGGEST_DEFAULT_ALBUM = 'photo.suggest_default_album';

    /**
     * @return PHOTO_CLASS_EventHandler
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
     * @var PHOTO_BOL_PhotoAlbumService
     */
    private $albumService;

    /**
     * @var PHOTO_BOL_PhotoService
     */
    private $photoService;

    private function __construct()
    {
        $this->albumService = PHOTO_BOL_PhotoAlbumService::getInstance();
        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
    }

    public function albumAdd( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( empty($params['name']) )
        {
            return false;
        }

        $albumName = trim($params['name']);
        $userId = !empty($params['userId']) ? (int) $params['userId'] : null;
        $entityId = !empty($params['entityId']) ? (int) $params['entityId'] : $userId;
        $entityType = !empty($params['entityType']) ? (int) $params['entityType'] : 'user';

        if ( $entityId && mb_strlen($entityType) )
        {
            $album = $this->albumService->findEntityAlbumByName($albumName, $entityId, $entityType);
        }

        if ( !isset($album) )
        {
            return false;
        }

        $album = new PHOTO_BOL_PhotoAlbum();
        $album->name = $albumName;
        $album->userId = $userId;
        $album->entityId = $entityId;
        $album->entityType = $entityType;
        $album->createDatetime = time();

        $albumId = $this->albumService->addAlbum($album);

        $data['albumId'] = $albumId;
        $e->setData($data);

        return $data;
    }

    public function albumFind( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        $album = $this->findRequestedAlbum($params);

        if ( empty($album) )
        {
            return null;
        }

        $list = $this->prepareAlbums(array($album));

        $data = $list[$album->id];
        $e->setData($data);

        return $data;
    }

    public function albumDelete( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( empty($params['albumId']) )
        {
            return false;
        }

        $album = $this->albumService->findAlbumById($params['albumId']);

        if ( !$album )
        {
            return false;
        }

        $this->albumService->deleteAlbum($album->id);

        return $data;
    }

    public function entityAlbumsFind( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( empty($params['entityId']) )
        {
            return false;
        }

        $entityType = !empty($params['entityType']) ? $params['entityType'] : 'user';
        $offset = !empty($params['offset']) ? (int) $params['offset'] : 0;
        $limit = !empty($params['limit']) ? (int) $params['limit'] : OW::getConfig()->getValue('photo', 'photos_per_page');

        $albums = $this->albumService->findEntityAlbums($params['entityId'], $entityType, $offset, $limit);
        $list = $this->prepareAlbums($albums);
        $data['albums'] = $list;
        $e->setData($data);

        return $data;
    }

    public function photoAdd( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( empty($params['albumId']) )
        {
            return false;
        }

        $addToFeed = !isset($params["addToFeed"]) || $params["addToFeed"];
        
        $album = $this->albumService->findAlbumById($params['albumId']);

        if ( !$album )
        {
            return false;
        }

        if ( empty($params['path']) || !file_exists($params['path']) )
        {
            return false;
        }

        $description = !empty($params['description']) ? $params['description'] : null;
        $tags = !empty($params['tags']) ? $params['tags'] : null;

        $tmpPhotoService = PHOTO_BOL_PhotoTemporaryService::getInstance();
        if ( $tmpId = $tmpPhotoService->addTemporaryPhoto($params['path'], $album->userId, 1) )
        {
            $photo = $tmpPhotoService->moveTemporaryPhoto($tmpId, $album->id, $description, $tags);
            if ( $photo )
            {
                $data['photoId'] = $photo->id;

                if ( $album->userId && $addToFeed )
                {
                    //Newsfeed
                    $event = new OW_Event('feed.action', array(
                        'pluginKey' => 'photo',
                        'entityType' => 'photo_comments',
                        'entityId' => $photo->id,
                        'userId' => $album->userId
                    ));
                    OW::getEventManager()->trigger($event);
                }

                $movedArray[] = array('addTimestamp' => time(), 'photoId' => $photo->id);
                $event = new OW_Event(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_ADD, $movedArray);
                OW::getEventManager()->trigger($event);
            }
        }

        $e->setData($data);

        return $data;
    }

    public function photoFind( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( empty($params['photoId']) )
        {
            return false;
        }

        $photoId = (int) $params['photoId'];
        $photo = $this->photoService->findPhotoById($photoId);

        if ( !$photo )
        {
            return false;
        }

        $list = $this->preparePhotos(array($photo));

        $data['photo'] = $list[$photoId];
        $e->setData($data);

        return $data;
    }

    public function photoDelete( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( empty($params['photoId']) )
        {
            return false;
        }

        $photo = $this->photoService->findPhotoById($params['photoId']);

        if ( !$photo )
        {
            return false;
        }

        $this->photoService->deletePhoto($photo->id);

        return $data;
    }

    /**
     * @param OW_Event $e
     * @return array
     */
    public function albumPhotosFind( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        $album = $this->findRequestedAlbum($params);

        if ( empty($album) )
        {
            return false;
        }

        $offset = !empty($params['offset']) ? (int) $params['offset'] : 0;
        $limit = !empty($params['limit']) ? (int) $params['limit'] : OW::getConfig()->getValue('photo', 'photos_per_page');
        $listType = !empty($params['listType']) ? $params['listType'] : 'latest';

        $photos = $this->photoService->findAlbumPhotoList($album->id, $listType, $offset, $limit);

        if ( $photos )
        {
            foreach ( $photos as $key => $photo )
            {
                $id = $photo['id'];
                $photos[$key]['userId'] = $album->userId;
                $photos[$key]['url'] = OW::getRouter()->urlForRoute('view_photo', array('id' => $id));
                $photos[$key]['photoUrl'] = $this->photoService->getPhotoUrl($id);
                $photos[$key]['previewUrl'] = $this->photoService->getPhotoUrl($id, true);
            }
        }

        $e->setData($photos);

        return $photos;
    }

    private function findRequestedAlbum( $params )
    {
        if ( empty($params['albumId']) )
        {
            if ( empty($params['userId']) || empty($params['albumTitle']) )
            {
                return null;
            }

            $album = $this->albumService->findAlbumByName($params['albumTitle'], $params['userId']);
        }
        else
        {
            $album = $this->albumService->findAlbumById($params['albumId']);
        }

        return $album;
    }
    
    public function entityPhotosFind( OW_Event $e )
    {
        $params = $e->getParams();

        if ( empty($params['entityId']) || empty($params['entityType']) )
        {
            return null;
        }

        $offset = !empty($params['offset']) ? (int) $params['offset'] : 0;
        $limit = !empty($params['limit']) ? (int) $params['limit'] : OW::getConfig()->getValue('photo', 'photos_per_page');
        $status = isset($params["status"]) ? $params["status"] : "approved";

        $photos = $this->photoService->findEntityPhotoList($params['entityType'], $params['entityId'], $offset, $limit, $status);
        
        $list = $this->preparePhotos($photos);
        $e->setData($list);

        return $list;
    }
    
    public function entityPhotosCount( OW_Event $e )
    {
        $params = $e->getParams();

        if ( empty($params['entityId']) || empty($params['entityType']) )
        {
            return null;
        }

        $status = isset($params["status"]) ? $params["status"] : "approved";

        $count = $this->photoService->countEntityPhotos($params['entityType'], $params['entityId'], $status);
        $e->setData($count);

        return $count;
    }
    
    public function entityAlbumsDelete( OW_Event $e )
    {
        $params = $e->getParams();

        if ( empty($params['entityId']) || empty($params['entityType']) )
        {
            return null;
        }

        $this->albumService->deleteEntityAlbums($params['entityId'], $params['entityType']);
    }
        
    private function prepareAlbums( array $albums )
    {
        if ( !count($albums) )
        {
            return null;
        }

        $list = array();
        foreach ( $albums as $album )
        {
            $id = $album->id;
            $username = BOL_UserService::getInstance()->getUserName($album->userId);

            $list[$id]['id'] = $id;
            $list[$id]['name'] = $album->name;
            $list[$id]['userId'] = $album->userId;
            $list[$id]['url'] = OW::getRouter()->urlForRoute('photo_user_album', array('user' => $username, 'album' => $album->id));
            $list[$id]['coverImage'] = $this->albumService->getAlbumCover($album->id);
            $list[$id]['photoCount'] = $this->albumService->countAlbumPhotos($album->id);
        }

        return $list;
    }

    private function preparePhotos( array $photos )
    {
        if ( !count($photos) )
        {
            return array();
        }

        $list = array();
        foreach ( $photos as $photo )
        {
            $id = $photo->id;
            $album = $this->albumService->findAlbumById($photo->albumId);
            $list[$id]['albumId'] = $photo->albumId;
            $list[$id]['description'] = $photo->description;
            $list[$id]['userId'] = $album->userId;
            $list[$id]['url'] = OW::getRouter()->urlForRoute('view_photo', array('id' => $id));
            $list[$id]['photoUrl'] = $this->photoService->getPhotoUrl($id);
            $list[$id]['previewUrl'] = $this->photoService->getPhotoUrl($id, true);
        }

        return $list;
    }


    public function initFloatbox( OW_Event $e )
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.bbq.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('photo')->getStaticJsUrl() . 'photo.js');

        OW::getLanguage()->addKeyForJs('photo', 'tb_edit_photo');
        OW::getLanguage()->addKeyForJs('photo', 'confirm_delete');
        OW::getLanguage()->addKeyForJs('photo', 'mark_featured');
        OW::getLanguage()->addKeyForJs('photo', 'remove_from_featured');

        $objParams = array(
            'ajaxResponder' => OW::getRouter()->urlFor('PHOTO_CTRL_Photo', 'ajaxResponder'),
            'fbResponder' => OW::getRouter()->urlForRoute('photo.floatbox')
        );

        $script = UTIL_JsGenerator::composeJsString('PHOTO.init({$settings})', array(
            'settings' => $objParams
        ));

        OW::getDocument()->addOnloadScript($script);

    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addNewContentItem( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('photo', 'upload') )
        {
            return;
        }

        $resultArray = array(
            BASE_CMP_AddNewContent::DATA_KEY_ICON_CLASS => 'ow_ic_picture',
            BASE_CMP_AddNewContent::DATA_KEY_URL => OW::getRouter()->urlFor('PHOTO_CTRL_Upload', 'index'),
            BASE_CMP_AddNewContent::DATA_KEY_LABEL => OW::getLanguage()->text('photo', 'photo')
        );

        $event->add($resultArray);
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addQuickLink( BASE_CLASS_EventCollector $event )
    {
        $service = PHOTO_BOL_PhotoAlbumService::getInstance();
        $userId = OW::getUser()->getId();
        $username = OW::getUser()->getUserObject()->getUsername();

        $albumCount = (int) $service->countUserAlbums($userId);

        if ( $albumCount > 0 )
        {
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('photo', 'my_albums'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => OW::getRouter()->urlForRoute('photo_user_albums', array('user' => $username)),
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $albumCount,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => OW::getRouter()->urlForRoute('photo_user_albums', array('user' => $username))
            ));
        }
    }

    /**
     * @param BASE_EventCollector $event
     */
    public function adsEnabled( BASE_EventCollector $event )
    {
        $event->add('photo');
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'photo' => array(
                    'label' => $language->text('photo', 'auth_group_label'),
                    'actions' => array(
                        'upload' => $language->text('photo', 'auth_action_label_upload'),
                        'view' => $language->text('photo', 'auth_action_label_view'),
                        'add_comment' => $language->text('photo', 'auth_action_label_add_comment'),
                        'delete_comment_by_content_owner' => $language->text('photo', 'auth_action_label_delete_comment_by_content_owner')
                    )
                )
            )
        );
    }

    /**
     * @param OW_Event $event
     */
    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !isset($params['deleteContent']) || !(bool) $params['deleteContent'] )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            PHOTO_BOL_PhotoAlbumService::getInstance()->deleteUserAlbums($userId);
        }
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addPrivacyAction( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => 'photo_view_album',
            'pluginKey' => 'photo',
            'label' => $language->text('photo', 'privacy_action_view_album'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);
    }

    /**
     * @param OW_Event $e
     */
    public function onChangePrivacy( OW_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];

        $actionList = $params['actionList'];

        if ( empty($actionList['photo_view_album']) )
        {
            return;
        }

        PHOTO_BOL_PhotoAlbumService::getInstance()->updatePhotosPrivacy($userId, $actionList['photo_view_album']);
    }

    /**
     * @param BASE_CLASS_EventCollector $e
     */
    public function collectNotificationActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'photo',
            'action' => 'photo-add_comment',
            'sectionIcon' => 'ow_ic_picture',
            'sectionLabel' => OW::getLanguage()->text('photo', 'email_notifications_section_label'),
            'description' => OW::getLanguage()->text('photo', 'email_notifications_setting_comment'),
            'selected' => true
        ));
    }

    /**
     * @param OW_Event $event
     */
    public function notifyOnNewComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || $params['entityType'] !== 'photo_comments' )
        {
            return;
        }

        $entityId = $params['entityId'];
        $userId = $params['userId'];
        $commentId = $params['commentId'];

        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $userService = BOL_UserService::getInstance();
        $ownerId = $photoService->findPhotoOwner($entityId);

        if ( $ownerId != $userId )
        {
            $params = array(
                'pluginKey' => 'photo',
                'entityType' => 'photo_add_comment',
                'entityId' => $commentId,
                'action' => 'photo-add_comment',
                'userId' => $ownerId,
                'time' => time()
            );

            $comment = BOL_CommentService::getInstance()->findComment($commentId);
            $url = OW::getRouter()->urlForRoute('view_photo', array('id' => $entityId));
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));

            $data = array(
                'avatar' => $avatars[$userId],
                'string' => array(
                    'key' => 'photo+email_notifications_comment',
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'photoUrl' => $url
                    )
                ),
                'content' => $comment->getMessage(),
                'url' => $url,
                'contentImage' => $photoService->getPhotoUrl($entityId, true)
            );

            $event = new OW_Event('notifications.add', $params, $data);
            OW::getEventManager()->trigger($event);
        }
    }

    public function addHashRedirect()
    {
        $script =
        'var lochash = document.location.hash.substr(1);
        if ( lochash )
        {
            var photo_id = lochash.substr(lochash.indexOf("view-photo=")).split("&")[0].split("=")[1];
            if ( photo_id )
            {
                document.location = '.json_encode(OW::getRouter()->urlForRoute('view_photo', array('id' => ''))).' + photo_id;
            }
        }
        ';

        OW::getDocument()->addScriptDeclarationBeforeIncludes($script);
    }

    /** Newsfeed events */

    /**
     * @param OW_Event $e
     */
    /*public function feedOnEntityAdd( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['entityType'] != 'photo_comments' )
        {
            return;
        }

        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $photo = $photoService->findPhotoById($params['entityId']);
        $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);

        $url = OW::getRouter()->urlForRoute('view_photo', array('id' => $photo->id));

        $title = UTIL_String::truncate(strip_tags($photo->description), 100, '...');

        $data = array(
            'time' => $photo->addDatetime,
            'ownerId' => $album->userId,
            'string' => $title,
            'content' => '<div class="ow_newsfeed_large_image clearfix"><div class="ow_newsfeed_item_picture"><a href="'
                . $url . '"><img src="' . $photoService->getPhotoUrl($photo->id, 1) . '" /></a></div></div>',
            'view' => array(
                'iconClass' => 'ow_ic_picture'
            )
        );

        $e->setData($data);
    }*/

    public function feedOnEntityAction( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();

        if ( !in_array($params['entityType'], array('photo_comments', 'multiple_photo_upload')) )
        {
            return;
        }

        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $albumService = PHOTO_BOL_PhotoAlbumService::getInstance();
        $photoId = !empty($data['photoIdList']) ? $data['photoIdList'][0] : $params['entityId'];
        $photo = $photoService->findPhotoById($photoId);
        if ( !$photo )
        {
            return;
        }

        $album = $albumService->findAlbumById($photo->albumId);
        if ( !$album )
        {
            return;
        }

        $info = array('route' => array(
            'textKey' => 'photo+album',
            'label' =>  UTIL_String::truncate(strip_tags($album->name), 100, '...'),
            'routeName' => 'photo_user_album',
            'vars' => array(
                'user' => BOL_UserService::getInstance()->getUserName($params['userId']),
                'album' => $album->id
            )
        ));

        $entityType = $params['entityType'];
        if ( $params['entityType'] == 'multiple_photo_upload' && count($data['photoIdList']) == 1 )
        {
            $data['params'] = array(
                'entityType' => 'photo_comments',
                'entityId' => $data['photoIdList'][0],
                'merge' => array(
                    'entityType' => 'multiple_photo_upload',
                    'entityId' => $params['entityId']
                )
            );
            $entityType = 'photo_comments';
        }
        
        $vars = array();
        $actionFormat = null;
        
        if ( isset($data["content"]) && is_array($data["content"]) )
        {
            $vars = empty($data["content"]["vars"]) ? array() : $data["content"]["vars"];
            $actionFormat = empty($data["content"]["format"]) ? null : $data["content"]["format"];
        }

        switch ( $entityType )
        {
            case 'multiple_photo_upload':
                $format = 'image_list';
                $photoIdList = $data['photoIdList'];
                $list = array();
                foreach ( $photoIdList as $id )
                {
                    $list[] = array(
                        "image" => $photoService->getPhotoUrl($id, true),
                        "url" => array("routeName" => "view_photo", "vars" => array('id' => $id))
                    );
                }
                
                $vars["list"] = $list;
                $vars["more"] = array(
                    'routeName' => $info['route']['routeName'],
                    'vars' => $info['route']['vars']
                );

                $data['features'] = array('likes');
                break;

            case 'photo_comments':
                $format = 'image';
                $vars["image"] = $photoService->getPhotoUrl($photoId);
                $vars["url"] = array("routeName" => "view_photo", "vars" => array('id' => $photoId));
                
                break;

            default:
                return;
        }

        $vars['info'] = $info;
        
        if ( !empty($actionFormat) )
        {
            $format = $actionFormat;
        }
        
        $data['content'] = array('format' => $format, 'vars' => $vars);
        
        $data['view'] = array('iconClass' => 'ow_ic_picture');

        $e->setData($data);
    }

    public function onBeforePhotoDelete( OW_Event $event )
    {
        $params = $event->getParams();

        $photoId = $params['id'];

        if ( $photoId )
        {
            $photo = $this->photoService->findPhotoById($photoId);
            $album = $this->albumService->findAlbumById($photo->albumId);

            if ( $photo->uploadKey )
            {
                $remainingList = $this->photoService->getPhotoListByUploadKey($photo->uploadKey, array($photo->id));

                /*if ( count($remainingList) == 1 )
                {
                    $this->photoService->triggerNewsfeedEventOnSinglePhotoAdd($remainingList[0]->id, $album->userId);
                }
                elseif ( count($remainingList) > 1 )
                {*/
                    $this->photoService->triggerNewsfeedEventOnMultiplePhotosAdd($remainingList, $album->userId, $album);
                //}
            }
        }
    }

    /**
     * @param OW_Event $event
     */
    public function feedOnItemRender( OW_Event $event )
    {
        $params = $event->getParams();

        $entityType = $params['action']['entityType'];
        $photoId = $params['action']['entityId'];
        $autoId = $params['autoId'];

        switch ( $entityType )
        {
            case 'photo_comments':
                $query = '.ow_newsfeed_item_picture a';
                $reference = 'var photo_id = ' . $photoId .';';
                break;

            case 'multiple_photo_upload':
                $query = '.ow_newsfeed_content a[class!=photo_view_more]';
                $reference = 'var href = $(this).attr("href");
                var pos = href.lastIndexOf("/");
                var photo_id = href.substring(pos+1);';
                break;

            default: return;
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'jquery.bbq.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('photo')->getStaticJsUrl() . 'photo.js');

        OW::getLanguage()->addKeyForJs('photo', 'tb_edit_photo');
        OW::getLanguage()->addKeyForJs('photo', 'confirm_delete');
        OW::getLanguage()->addKeyForJs('photo', 'mark_featured');
        OW::getLanguage()->addKeyForJs('photo', 'remove_from_featured');

        $objParams = array(
            'ajaxResponder' => OW::getRouter()->urlFor('PHOTO_CTRL_Photo', 'ajaxResponder'),
            'fbResponder' => OW::getRouter()->urlForRoute('photo.floatbox')
        );

        $script = '$("'.$query.'", "#'.$autoId.'").on("click", function(e){
            e.preventDefault();
            '.$reference.'

            if ( !window.photoViewObj ) {
                window.photoViewObj = new photoView('.json_encode($objParams).');
            }

            window.photoViewObj.setId(photo_id);
        });
        ';

        OW::getDocument()->addOnloadScript($script);

        $script =
            'if ( !window.photoPollingEnabled )
            {
                $(window).bind( "hashchange", function(e) {
                    var photo_id = $.bbq.getState("view-photo");
                    if ( photo_id != undefined )
                    {
                        if ( window.photoFBLoading ) { return; }
                        window.photoViewObj.showPhotoCmp(photo_id);
                    }
                });
                window.photoPollingEnabled = true;
            }';

        OW::getDocument()->addOnloadScript($script);
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function feedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('photo', 'feed_content_label'),
            'activity' => array('*:photo_comments', '*:multiple_photo_upload')
        ));
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function feedCollectPrivacy( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('create:photo_comments,create:multiple_photo_upload', 'photo_view_album'));
    }

    /**
     * @param OW_Event $event
     */
    public function feedAfterCommentAdd( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'photo_comments' )
        {
            return;
        }

        $service = PHOTO_BOL_PhotoService::getInstance();
        $photo = $service->findPhotoById($params['entityId']);
        $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
        $userId = $album->userId;

        if ( $userId == $params['userId'] )
        {
            $string = array('key' => 'photo+feed_activity_owner_photo_string');
        }
        else
        {
            $userName = BOL_UserService::getInstance()->getDisplayName($userId);
            $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
            $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';
            $string = array('key' => 'photo+feed_activity_photo_string', 'vars' => array('user' => $userEmbed));
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'comment',
            'activityId' => $params['commentId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'photo'
        ), array(
            'string' => $string
        )));
    }

    /**
     * @param OW_Event $event
     */
    public function feedAfterLikeAdded( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'photo_comments' )
        {
            return;
        }

        $service = PHOTO_BOL_PhotoService::getInstance();
        $photo = $service->findPhotoById($params['entityId']);
        $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
        $userId = $album->userId;

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        $lang = OW::getLanguage();
        if ( $params['userId'] == $userId )
        {
            $string = array('key' => 'photo+feed_activity_owner_photo_like');
        }
        else
        {
            $string = array('key' => 'photo+feed_activity_photo_string_like', 'vars' => array('user' => $userEmbed));
        }

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
            'activityType' => 'like',
            'activityId' => $params['userId'],
            'entityId' => $params['entityId'],
            'entityType' => $params['entityType'],
            'userId' => $params['userId'],
            'pluginKey' => 'photo'
        ), array(
            'string' => $string
        )));
    }

    public function sosialSharingGetPhotoInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $service = PHOTO_BOL_PhotoService::getInstance();

        $data['display'] = false;
        
        if ( empty($params['entityId']) )
        {
            return;
        }
        
        if ( $params['entityType'] == 'photo' )
        {
            if ( !BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('photo', 'view') )
            {
                $event->setData($data);
                return;
            }

            $photo = $service->findPhotoById($params['entityId']);
            $data['display'] = $photo->privacy != 'everybody';

            $event->setData($data);
        }
        else if ( $params['entityType'] == 'photo_album' )
        {
            if ( !BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('photo', 'view') )
            {
                $event->setData($data);
                return;
            }

            $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($params['entityId']);
            $list = $service->findAlbumPhotoList($params['entityId'], 'latest', 0, 500);

            foreach ( $list as $photo )
            {
                if ( $photo['privacy'] == 'everybody' );
                {
                    $data['image'] = $service->getPhotoUrl($photo['id']);
                    $data['title'] = $album->name;
                    $data['display'] = true;
                    break;
                }
            }
            
            $event->setData($data);
        }
    }

    public function init()
    {
        $this->genericInit();
        $em = OW::getEventManager();

        $em->bind(BASE_CMP_AddNewContent::EVENT_NAME, array($this, 'addNewContentItem'));
        $em->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($this, 'addQuickLink'));
        $em->bind(OW_EventManager::ON_FINALIZE, array($this, 'addHashRedirect'));
        $em->bind('feed.on_item_render', array($this, 'feedOnItemRender'));
    }

    public function genericInit()
    {
        $em = OW::getEventManager();

        $em->bind(self::EVENT_ALBUM_ADD, array($this, 'albumAdd'));
        $em->bind(self::EVENT_ALBUM_FIND, array($this, 'albumFind'));
        $em->bind(self::EVENT_ALBUM_DELETE, array($this, 'albumDelete'));
        $em->bind(self::EVENT_ENTITY_ALBUMS_FIND, array($this, 'entityAlbumsFind'));
        $em->bind(self::EVENT_PHOTO_ADD, array($this, 'photoAdd'));
        $em->bind(self::EVENT_PHOTO_FIND, array($this, 'photoFind'));
        $em->bind(self::EVENT_PHOTO_DELETE, array($this, 'photoDelete'));
        $em->bind(self::EVENT_ALBUM_PHOTOS_FIND, array($this, 'albumPhotosFind'));
        
        $em->bind(self::EVENT_ENTITY_PHOTOS_FIND, array($this, 'entityPhotosFind'));
        $em->bind(self::EVENT_ENTITY_PHOTOS_COUNT, array($this, 'entityPhotosCount'));
        $em->bind(self::EVENT_ENTITY_ALBUMS_DELETE, array($this, 'entityAlbumsDelete'));
        
        $em->bind(self::EVENT_INIT_FLOATBOX, array($this, 'initFloatbox'));

        $em->bind('ads.enabled_plugins', array($this, 'adsEnabled'));
        $em->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        $em->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregister'));
        $em->bind('plugin.privacy.get_action_list', array($this, 'addPrivacyAction'));
        $em->bind('plugin.privacy.on_change_action_privacy', array($this, 'onChangePrivacy'));
        $em->bind('notifications.collect_actions', array($this, 'collectNotificationActions'));
        $em->bind('base_add_comment', array($this, 'notifyOnNewComment'));
        $em->bind('feed.on_entity_action', array($this, 'feedOnEntityAction'));
        $em->bind(PHOTO_CLASS_EventHandler::EVENT_BEFORE_PHOTO_DELETE, array($this, 'onBeforePhotoDelete'));
        $em->bind('feed.collect_configurable_activity', array($this, 'feedCollectConfigurableActivity'));
        $em->bind('feed.collect_privacy', array($this, 'feedCollectPrivacy'));
        $em->bind('feed.after_comment_add', array($this, 'feedAfterCommentAdd'));
        $em->bind('feed.after_like_added', array($this, 'feedAfterLikeAdded'));

        $credits = new PHOTO_CLASS_Credits();
        $em->bind('usercredits.on_action_collect', array($credits, 'bindCreditActionsCollect'));
        
        $em->bind('socialsharing.get_entity_info', array($this, 'sosialSharingGetPhotoInfo'));

    }
}