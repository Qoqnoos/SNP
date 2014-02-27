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
 * Photo floatbox component
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.3.2
 */
class PHOTO_CMP_PhotoFloatbox extends OW_Component
{
    /**
     * @var PHOTO_BOL_PhotoService
     */
    private $photoService;
    /**
     * @var PHOTO_BOL_PhotoAlbumService
     */
    private $photoAlbumService;

    /**
     * Class constructor
     *
     * @param string $listType
     * @param int $count
     * @param string $tag
     */
    public function __construct( array $params )
    {
        parent::__construct();

        $photoId = $params['photoId'];

        $config = OW::getConfig();
        $lang = OW::getLanguage();

        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
        $this->photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();

        $photo = $this->photoService->findPhotoById($photoId);
        $album = $this->photoAlbumService->findAlbumById($photo->albumId);
        $this->assign('album', $album);

        // is owner
        $contentOwner = $this->photoService->findPhotoOwner($photo->id);
        $userId = OW::getUser()->getId();
        $ownerMode = $contentOwner == $userId;
        $this->assign('ownerMode', $ownerMode);

        // is moderator
        $modPermissions = OW::getUser()->isAuthorized('photo');
        $this->assign('moderatorMode', $modPermissions);

        $canView = true;
        if ( !$ownerMode && !$modPermissions && !OW::getUser()->isAuthorized('photo', 'view') )
        {
            $canView = false;
        }

        $this->assign('canView', $canView);

        $cmtParams = new BASE_CommentsParams('photo', 'photo_comments');
        $cmtParams->setEntityId($photo->id);
        $cmtParams->setOwnerId($contentOwner);
        $cmtParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_BOTTOM_FORM_WITH_FULL_LIST);

        $photoCmts = new BASE_CMP_Comments($cmtParams);
        $this->addComponent('comments', $photoCmts);

        $photoRates = new BASE_CMP_Rate('photo', 'photo_rates', $photo->id, $contentOwner);
        $this->addComponent('rate', $photoRates);

        $photoTags = new BASE_CMP_EntityTagCloud('photo');
        $photoTags->setEntityId($photo->id);
        $photoTags->setRouteName('view_tagged_photo_list');
        $this->addComponent('tags', $photoTags);

        $description = $photo->description;
        $photo->description = UTIL_HtmlTag::autoLink($photo->description);

        $this->assign('photo', $photo);
        $this->assign('url', $this->photoService->getPhotoUrl($photo->id, false, $photo->hash));
        $this->assign('ownerName', BOL_UserService::getInstance()->getUserName($album->userId));

        $is_featured = PHOTO_BOL_PhotoFeaturedService::getInstance()->isFeatured($photo->id);

        if ( (int) $config->getValue('photo', 'store_fullsize') && $photo->hasFullsize )
        {
            $this->assign('fullsizeUrl', $this->photoService->getPhotoFullsizeUrl($photo->id, $photo->hash));
        }
        else
        {
            $this->assign('fullsizeUrl', null);
        }

        $action = new BASE_ContextAction();
        $action->setKey('photo-moderate');

        $context = new BASE_CMP_ContextAction();
        $context->addAction($action);

        $contextEvent = new BASE_CLASS_EventCollector('photo.collect_photo_context_actions', array(
            'photoId' => $photoId,
            'photoDto' => $photo
        ));

        OW::getEventManager()->trigger($contextEvent);

        foreach ( $contextEvent->getData() as $contextAction )
        {
            $action = new BASE_ContextAction();
            $action->setKey(empty($contextAction['key']) ? uniqid() : $contextAction['key']);
            $action->setParentKey('photo-moderate');
            $action->setLabel($contextAction['label']);

            if ( !empty($contextAction['id']) )
            {
                $action->setId($contextAction['id']);
            }

            if ( !empty($contextAction['order']) )
            {
                $action->setOrder($contextAction['order']);
            }

            if ( !empty($contextAction['class']) )
            {
                $action->setClass($contextAction['class']);
            }

            if ( !empty($contextAction['url']) )
            {
                $action->setUrl($contextAction['url']);
            }

            $attributes = empty($contextAction['attributes']) ? array() : $contextAction['attributes'];
            foreach ( $attributes as $key => $value )
            {
                $action->addAttribute($key, $value);
            }

            $context->addAction($action);
        }

        if ( $userId && !$ownerMode )
        {
            $action = new BASE_ContextAction();
            $action->setKey('flag');
            $action->setParentKey('photo-moderate');
            $action->setLabel($lang->text('base', 'flag'));
            $action->setId('btn-photo-flag');
            $action->addAttribute('rel', $photoId);
            $action->addAttribute('url', OW::getRouter()->urlForRoute('view_photo', array('id' => $photo->id)));

            $context->addAction($action);
        }

        if ( $ownerMode || $modPermissions )
        {
            $action = new BASE_ContextAction();
            $action->setKey('edit');
            $action->setParentKey('photo-moderate');
            $action->setLabel($lang->text('base', 'edit'));
            $action->setId('btn-photo-edit');
            $action->addAttribute('rel', $photoId);

            $context->addAction($action);

            $action = new BASE_ContextAction();
            $action->setKey('delete');
            $action->setParentKey('photo-moderate');
            $action->setLabel($lang->text('base', 'delete'));
            $action->setId('photo-delete');
            $action->addAttribute('rel', $photoId);

            $context->addAction($action);
        }

        if ( $modPermissions )
        {
            if ( $is_featured )
            {
                $action = new BASE_ContextAction();
                $action->setKey('unmark-featured');
                $action->setParentKey('photo-moderate');
                $action->setLabel($lang->text('photo', 'remove_from_featured'));
                $action->setId('photo-mark-featured');
                $action->addAttribute('rel', 'remove_from_featured');
                $action->addAttribute('photo-id', $photoId);

                $context->addAction($action);
            }
            else
            {
                $action = new BASE_ContextAction();
                $action->setKey('mark-featured');
                $action->setParentKey('photo-moderate');
                $action->setLabel($lang->text('photo', 'mark_featured'));
                $action->setId('photo-mark-featured');
                $action->addAttribute('rel', 'mark_featured');
                $action->addAttribute('photo-id', $photoId);

                $context->addAction($action);
            }
        }

        $this->addComponent('contextAction', $context);

        $nextPhoto = $this->photoService->getNextPhoto($photo->albumId, $photo->id);
        $this->assign('nextPhoto', $nextPhoto);

        $previousPhoto = $this->photoService->getPreviousPhoto($photo->albumId, $photo->id);
        $this->assign('previousPhoto', $previousPhoto);

        $photoCount = $this->photoAlbumService->countAlbumPhotos($photo->albumId);
        $this->assign('photoCount', $photoCount);

        $photoIndex = $this->photoService->getPhotoIndex($photo->albumId, $photo->id);
        $this->assign('photoIndex', $photoIndex);

        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($contentOwner), true, true, true, false);
        $this->assign('avatar', $avatar[$contentOwner]);
    }
}