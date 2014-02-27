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
 * Photo Service Class.  
 * 
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.photo.bol
 * @since 1.0
 */
final class PHOTO_BOL_PhotoService
{
    /**
     * @var PHOTO_BOL_PhotoDao
     */
    private $photoDao;
    /**
     * @var PHOTO_BOL_PhotoFeaturedDao
     */
    private $photoFeaturedDao;
    /**
     * Class instance
     *
     * @var PHOTO_BOL_PhotoService
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->photoDao = PHOTO_BOL_PhotoDao::getInstance();
        $this->photoFeaturedDao = PHOTO_BOL_PhotoFeaturedDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return PHOTO_BOL_PhotoService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Adds photo
     *
     * @param PHOTO_BOL_Photo $photo
     * @return int
     */
    public function addPhoto( PHOTO_BOL_Photo $photo )
    {
        $this->photoDao->save($photo);
        
        $this->cleanListCache();

        return $photo->id;
    }

    /**
     * Updates photo
     *
     * @param PHOTO_BOL_Photo $photo
     * @return int
     */
    public function updatePhoto( PHOTO_BOL_Photo $photo )
    {
        $this->photoDao->save($photo);
        
        $this->cleanListCache();

        $event = new OW_Event(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_EDIT, array('id' => $photo->id));
        OW::getEventManager()->trigger($event);

        return $photo->id;
    }

    /**
     * Finds photo by id
     *
     * @param int $id
     * @return PHOTO_BOL_Photo
     */
    public function findPhotoById( $id )
    {
        return $this->photoDao->findById($id);
    }

    /**
     * Finds photo owner
     *
     * @param int $id
     * @return int
     */
    public function findPhotoOwner( $id )
    {
        return $this->photoDao->findOwner($id);
    }

    /**
     * Returns photo list
     *
     * @param string $type
     * @param int $page
     * @param int $limit
     * @param bool $checkPrivacy
     * @param null $exclude
     * @return array of PHOTO_BOL_Photo
     */
    public function findPhotoList( $type, $page, $limit, $checkPrivacy = true, $exclude = null )
    {
        if ( $type == 'toprated' )
        {
            $first = ( $page - 1 ) * $limit;
            $topRatedList = BOL_RateService::getInstance()->findMostRatedEntityList('photo_rates', $first, $limit, $exclude);

            if ( !$topRatedList )
            {
                return array();
            }
            $photoArr = $this->photoDao->findPhotoInfoListByIdList(array_keys($topRatedList));

            $photos = array();

            foreach ( $photoArr as $key => $photo )
            {
                $photos[$key] = $photo;
                $photos[$key]['score'] = $topRatedList[$photo['id']]['avgScore'];
                $photos[$key]['rates'] = $topRatedList[$photo['id']]['ratesCount'];
            }

            usort($photos, array('PHOTO_BOL_PhotoService', 'sortArrayItemByDesc'));
        }
        else
        {
            $photos = $this->photoDao->getPhotoList($type, $page, $limit, $checkPrivacy,$exclude);
        }

        if ( $photos )
        {
            foreach ( $photos as $key => $photo )
            {
                $photos[$key]['url'] = $this->getPhotoPreviewUrl($photo['id'], $photo['hash']);
            }
        }

        return $photos;
    }

    public function findAlbumPhotoList( $albumId, $listType, $offset, $limit )
    {
        if ( !$albumId )
        {
            return array();
        }

        if ( !$listType || !in_array($listType, array('latest', 'toprated', 'featured')) )
        {
            return array();
        }

        return $this->photoDao->getAlbumPhotoList($albumId, $listType, $offset, $limit);
    }

    public static function sortArrayItemByDesc( $el1, $el2 )
    {
        if ( $el1['score'] === $el2['score'] )
        {
            if ( $el1['rates'] === $el2['rates'] )
            {
                return 0;
            }
            
            return $el1['rates'] < $el2['rates'] ? 1 : -1;
        }

        return $el1['score'] < $el2['score'] ? 1 : -1;
    }

    /**
     * Returns tagged photo list
     *
     * @param $tag
     * @param int $page
     * @param int $limit
     * @internal param string $type
     * @return array of PHOTO_BOL_Photo
     */
    public function findTaggedPhotos( $tag, $page, $limit )
    {
        $first = ($page - 1 ) * $limit;

        $photoIdList = BOL_TagService::getInstance()->findEntityListByTag('photo', $tag, $first, $limit);

        if ( !$photoIdList )
        {
            return array();
        }
        
        $photos = $this->photoDao->findPhotoInfoListByIdList($photoIdList);

        if ( $photos )
        {
            foreach ( $photos as $key => $photo )
            {
                $photos[$key]['url'] = $this->getPhotoPreviewUrl($photo['id'], $photo['hash']);
            }
        }

        return $photos;
    }

    /**
     * Counts photos
     *
     * @param string $type
     * @param bool $checkPrivacy
     * @param null $exclude
     * @return int
     */
    public function countPhotos( $type, $checkPrivacy = true, $exclude = null )
    {
        if ( $type == 'toprated' )
        {
            return BOL_RateService::getInstance()->findMostRatedEntityCount('photo_rates', $exclude);
        }

        return $this->photoDao->countPhotos($type, $checkPrivacy, $exclude);
    }

    public function countFullsizePhotos()
    {
        return (int) $this->photoDao->countFullsizePhotos();
    }

    /**
     * Counts all user uploaded photos
     *
     * @param int $userId
     * @return int
     */
    public function countUserPhotos( $userId )
    {
        return $this->photoDao->countUserPhotos($userId);
    }

    /**
     * Counts photos with tag
     *
     * @param string $tag
     * @return int
     */
    public function countTaggedPhotos( $tag )
    {
        return BOL_TagService::getInstance()->findEntityCountByTag('photo', $tag);
    }

    /**
     * Returns photo URL
     *
     * @param int $id
     * @param bool $preview
     * @param null $hash
     * @return string
     */
    public function getPhotoUrl( $id, $preview = false, $hash = null )
    {
        if ( !$hash )
        {
            /** @var $photo PHOTO_BOL_Photo */
            $photo = $this->photoDao->findById($id);
            $hash = $photo->hash;
        }

        return $this->photoDao->getPhotoUrl($id, $hash, $preview);
    }

    /**
     * Returns photo preview URL
     *
     * @param int $id
     * @param $hash
     * @return string
     */
    public function getPhotoPreviewUrl( $id, $hash )
    {
        return $this->photoDao->getPhotoUrl($id, $hash, true);
    }

    public function getPhotoFullsizeUrl( $id, $hash )
    {
        return $this->photoDao->getPhotoFullsizeUrl($id, $hash);
    }

    /**
     * Get directory where 'photo' plugin images are uploaded
     *
     * @return string
     */
    public function getPhotoUploadDir()
    {
        return $this->photoDao->getPhotoUploadDir();
    }

    /**
     * Get path to photo in file system
     *
     * @param int $photoId
     * @param $hash
     * @param string $type
     * @return string
     */
    public function getPhotoPath( $photoId, $hash, $type = '' )
    {
        return $this->photoDao->getPhotoPath($photoId, $hash, $type);
    }

    public function getPhotoPluginFilesPath( $photoId, $type = '' )
    {
        return $this->photoDao->getPhotoPluginFilesPath($photoId, $type);
    }

    /**
     * Returns a list of thotos in album
     *
     * @param int $album
     * @param int $page
     * @param int $limit
     * @param null $exclude
     * @return string
     */
    public function getAlbumPhotos( $album, $page, $limit, $exclude = null )
    {
        $photos = $this->photoDao->getAlbumPhotos($album, $page, $limit, $exclude);

        $list = array();

        if ( $photos )
        {
            $commentService = BOL_CommentService::getInstance();

            foreach ( $photos as $key => $photo )
            {
                $list[$key]['id'] = $photo->id;
                $list[$key]['dto'] = $photo;
                $list[$key]['comments_count'] = $commentService->findCommentCount('photo', $photo->id);
                $list[$key]['url'] = $this->getPhotoPreviewUrl($photo->id, $photo->hash);
            }
        }

        return $list;
    }

    /**
     * Updates the 'status' field of the photo object 
     *
     * @param int $id
     * @param string $status
     * @return boolean
     */
    public function updatePhotoStatus( $id, $status )
    {
        /** @var $photo PHOTO_BOL_Photo */
        $photo = $this->photoDao->findById($id);

        $newStatus = $status == 'approve' ? 'approved' : 'blocked';

        $photo->status = $newStatus;

        $this->updatePhoto($photo);

        return $photo->id ? true : false;
    }

    /**
     * Changes photo's 'featured' status
     *
     * @param int $id
     * @param string $status
     * @return boolean
     */
    public function updatePhotoFeaturedStatus( $id, $status )
    {
        $photo = $this->photoDao->findById($id);

        if ( $photo )
        {
            $photoFeaturedService = PHOTO_BOL_PhotoFeaturedService::getInstance();

            if ( $status == 'mark_featured' )
            {
                return $photoFeaturedService->markFeatured($id);
            }
            else
            {
                return $photoFeaturedService->markUnfeatured($id);
            }
        }

        return false;
    }

    /**
     * Returns album's next photo
     *
     * @param int $albumId
     * @param int $id
     * @return array
     */
    public function getNextPhoto( $albumId, $id )
    {
        $photo = $this->photoDao->getNextPhoto($albumId, $id);

        if ( $photo )
        {
            $router = OW_Router::getInstance();

            $nextPhoto= array();

            $nextPhoto['dto'] = $photo;
            $nextPhoto['url'] = $this->getPhotoPreviewUrl($photo->id, $photo->hash);
            $nextPhoto['href'] = $router->urlForRoute('view_photo', array('id' => $photo->id));

            return $nextPhoto;
        }

        return null;
    }
    
    public function getNextPhotoId( $albumId, $id )
    {
        $next = $this->photoDao->getNextPhoto($albumId, $id);
                
        return $next ? $next->id : null;
    }

    /**
     * Returns album's previous photo
     *
     * @param int $albumId
     * @param int $id
     * @return array
     */
    public function getPreviousPhoto( $albumId, $id )
    {
        $photo = $this->photoDao->getPreviousPhoto($albumId, $id);

        if ( $photo )
        {
            $router = OW_Router::getInstance();

            $prevPhoto = array();

            $prevPhoto['dto'] = $photo;
            $prevPhoto['url'] = $this->getPhotoPreviewUrl($photo->id, $photo->hash);

            $prevPhoto['href'] = $router->urlForRoute('view_photo', array('id' => $photo->id));

            return $prevPhoto;
        }

        return null;
    }
    
    public function getPreviousPhotoId( $albumId, $id )
    {
        $prev = $this->photoDao->getPreviousPhoto($albumId, $id);
                
        return $prev ? $prev->id : null;
    }

    /**
     * Returns current photo index in album
     *
     * @param int $albumId
     * @param int $id
     * @return int
     */
    public function getPhotoIndex( $albumId, $id )
    {
        return $this->photoDao->getPhotoIndex($albumId, $id);
    }

    /**
     * Deletes photo
     *
     * @param int $id
     * @return int
     */
    public function deletePhoto( $id )
    {
        /** @var $photo PHOTO_BOL_Photo */
        if ( !$id || !$photo = $this->photoDao->findById($id) )
        {
            return false;
        }

        $event = new OW_Event(PHOTO_CLASS_EventHandler::EVENT_BEFORE_PHOTO_DELETE, array('id' => $id));
        OW::getEventManager()->trigger($event);

        if ( $this->photoDao->deleteById($id) )
        {
            BOL_CommentService::getInstance()->deleteEntityComments('photo_comments', $id);
            BOL_RateService::getInstance()->deleteEntityRates($id, 'photo_rates');
            BOL_TagService::getInstance()->deleteEntityTags($id, 'photo');

            // remove files
            $this->photoDao->removePhotoFile($id, $photo->hash, 'main');
            $this->photoDao->removePhotoFile($id, $photo->hash, 'preview');
            $this->photoDao->removePhotoFile($id, $photo->hash, 'original');

            $this->photoFeaturedDao->markUnfeatured($id);

            BOL_FlagService::getInstance()->deleteByTypeAndEntityId('photo', $id);
            
            OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array(
                'entityType' => 'photo_comments',
                'entityId' => $id
            )));

            $this->cleanListCache();

            $event = new OW_Event(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_DELETE, array('id' => $id));
            OW::getEventManager()->trigger($event);
            
            return true;
        }

        return false;
    }
    
    public function deleteFullsizePhotos()
    {
        $this->photoDao->deleteFullsizePhotos();
    }
    
    public function setMaintenanceMode( $mode = true )
    {
        $config = OW::getConfig();
        
        if ( $mode )
        {
            $state = (int) $config->getValue('base', 'maintenance');
            $config->saveConfig('photo', 'maintenance_mode_state', $state);
            OW::getApplication()->setMaintenanceMode($mode);
        }
        else 
        {
            $state = (int) $config->getValue('photo', 'maintenance_mode_state');
            $config->saveConfig('base', 'maintenance', $state);
        }
    }
    
    public function cleanListCache()
    {
        OW::getCacheManager()->clean(array(PHOTO_BOL_PhotoDao::CACHE_TAG_PHOTO_LIST));
    }

    public function triggerNewsfeedEventOnSinglePhotoAdd( $photoId, $userId )
    {
        if ( !$photoId || !$userId )
        {
            return false;
        }

        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'photo',
            'entityType' => 'photo_comments',
            'entityId' => $photoId,
            'userId' => $userId
        ), array(
            'photoIdList' => array($photoId)
        ));

        OW::getEventManager()->trigger($event);

        return true;
    }

    public function triggerNewsfeedEventOnMultiplePhotosAdd( array $photos, $userId, $album )
    {
        $photos = array_reverse($photos);

        $photoIdList = array();
        foreach ( $photos as $photo )
        {
            $photoIdList[] = $photo->id;
        }

        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'photo',
            'entityType' => 'multiple_photo_upload',
            'entityId' => $this->getPhotoUploadKey($album->id),
            'userId' => $userId
        ), array(
            'photoIdList' => $photoIdList
        ));

        OW::getEventManager()->trigger($event);
    }

    public function getPhotoUploadKey( $albumId )
    {
        $photo = $this->photoDao->getLastPhoto($albumId);

        if ( $photo && (time() - $photo->addDatetime < 60 * 15) && $photo->uploadKey )
        {
            return $photo->uploadKey;
        }

        return md5($albumId . time());
    }

    public function getPhotoListByUploadKey( $uploadKey, array $exclude = null )
    {
        return $this->photoDao->findPhotoListByUploadKey($uploadKey, $exclude);
    }
    
    public function findEntityPhotoList( $entityType, $entityId, $first, $count, $status = "approved" )
    {
        return $this->photoDao->findEntityPhotoList($entityType, $entityId, $first, $count, $status);
    }
    
    public function countEntityPhotos( $entityType, $entityId, $status = "approved" )
    {
        return $this->photoDao->countEntityPhotos($entityType, $entityId, $status);
    }
}