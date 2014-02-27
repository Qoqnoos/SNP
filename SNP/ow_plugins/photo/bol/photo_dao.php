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
 * Data Access Object for `photo` table.
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.photo.bol
 * @since 1.0
 */
class PHOTO_BOL_PhotoDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var PHOTO_BOL_PhotoDao
     */
    private static $classInstance;

    const PHOTO_PREFIX = 'photo_';

    const PHOTO_PREVIEW_PREFIX = 'photo_preview_';

    const PHOTO_ORIGINAL_PREFIX = 'photo_original_';
    
    const CACHE_TAG_PHOTO_LIST = 'photo.list';

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return PHOTO_BOL_PhotoDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_Photo';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'photo';
    }

    /**
     * Get photo/preview URL
     *
     * @param int $id
     * @param $hash
     * @param boolean $preview
     * @return string
     */
    public function getPhotoUrl( $id, $hash, $preview = false )
    {
        $userfilesDir = OW::getPluginManager()->getPlugin('photo')->getUserFilesDir();

        $storage = OW::getStorage();
        $hashSlug = !empty($hash) ? '_' . $hash : '';

        if ( $preview )
        {
            return $storage->getFileUrl($userfilesDir . self::PHOTO_PREVIEW_PREFIX . $id . $hashSlug . '.jpg');
        }
        else
        {
            return $storage->getFileUrl($userfilesDir . self::PHOTO_PREFIX . $id . $hashSlug . '.jpg');
        }
    }

    public function getPhotoFullsizeUrl( $id, $hash )
    {
        $userfilesDir = OW::getPluginManager()->getPlugin('photo')->getUserFilesDir();
        $storage = OW::getStorage();
        $hashSlug = !empty($hash) ? '_' . $hash : '';

        return $storage->getFileUrl($userfilesDir . self::PHOTO_ORIGINAL_PREFIX . $id . $hashSlug . '.jpg');
    }

    /**
     * Get directory where 'photo' plugin images are uploaded
     *
     * @return string
     */
    public function getPhotoUploadDir()
    {
        return OW::getPluginManager()->getPlugin('photo')->getUserFilesDir();
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
        if ( !isset($type) || $type == '' )
        {
            $type = 'main';
        }
        $hashSlug = !empty($hash) ? '_' . $hash : '';

        switch ( $type )
        {
            case 'main':
                return $this->getPhotoUploadDir() . self::PHOTO_PREFIX . $photoId . $hashSlug . '.jpg';

            case 'preview':
                return $this->getPhotoUploadDir() . self::PHOTO_PREVIEW_PREFIX . $photoId . $hashSlug . '.jpg';

            case 'original':
                return $this->getPhotoUploadDir() . self::PHOTO_ORIGINAL_PREFIX . $photoId . $hashSlug . '.jpg';

            default:
                return $this->getPhotoUploadDir() . self::PHOTO_PREFIX . $photoId . $hashSlug . '.jpg';
        }
    }

    public function getPhotoPluginFilesPath( $photoId, $type = '' )
    {
        if ( !isset($type) || $type == '' )
        {
            $type = 'main';
        }

        $dir = $this->getPhotoPluginFilesDir();

        switch ( $type )
        {
            case 'main':
                return $dir . self::PHOTO_PREFIX . $photoId . '.jpg';

            case 'preview':
                return $dir . self::PHOTO_PREVIEW_PREFIX . $photoId . '.jpg';

            case 'original':
                return $dir . self::PHOTO_ORIGINAL_PREFIX . $photoId . '.jpg';

            default:
                return $dir . self::PHOTO_PREFIX . $photoId . '.jpg';
        }
    }

    public function getPhotoPluginFilesDir()
    {
        return OW::getPluginManager()->getPlugin('photo')->getPluginFilesDir();
    }

    /**
     * Find photo owner
     *
     * @param int $id
     * @return int
     */
    public function findOwner( $id )
    {
        if ( !$id )
            return null;

        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        $query = "
            SELECT `a`.`userId`       
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
            WHERE `p`.`id` = :id
        ";

        $qParams = array('id' => $id);

        $owner = $this->dbo->queryForColumn($query, $qParams);

        return $owner;
    }

    /**
     * Get photo list (featured|latest|toprated)
     *
     * @param string $listtype
     * @param int $page
     * @param int $limit
     * @param bool $checkPrivacy
     * @param null $exclude
     * @return array
     */
    public function getPhotoList( $listtype, $page, $limit, $checkPrivacy = true, $exclude = null )
    {
        $limit = (int) $limit;
        $first = ( $page - 1 ) * $limit;

        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        
        $privacyCond = $checkPrivacy ? " AND `p`.`privacy` = 'everybody' " : "";
        $excludeCond = $exclude ? ' AND `p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';

        switch ( $listtype )
        {
            case 'featured':
                $photoFeaturedDao = PHOTO_BOL_PhotoFeaturedDao::getInstance();

                $query = "
                    SELECT `p`.*, `a`.`userId`
                    FROM `" . $this->getTableName() . "` AS `p`
                    LEFT JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    LEFT JOIN `" . $photoFeaturedDao->getTableName() . "` AS `f` ON (`f`.`photoId`=`p`.`id`)
                    WHERE `p`.`status` = 'approved' " . $privacyCond . $excludeCond . " AND `f`.`id` IS NOT NULL
                    AND `a`.`entityType` = 'user'
                    ORDER BY `p`.`addDatetime` DESC
                    LIMIT :first, :limit";

                break;

            case 'latest':
            default:

                $query = "
		            SELECT `p`.*, `a`.`userId`
		            FROM `" . $this->getTableName() . "` AS `p`
		            LEFT JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
		            WHERE `p`.`status` = 'approved' " . $privacyCond . $excludeCond . "
		            AND `a`.`entityType` = 'user'
		            ORDER BY `p`.`addDatetime` DESC
		            LIMIT :first, :limit";

                break;
        }
        
        $qParams = array('first' => $first, 'limit' => $limit);
        
        $cacheLifeTime = $first == 0 ? 24 * 3600 : null;
        $cacheTags = $first == 0 ? array(self::CACHE_TAG_PHOTO_LIST) : null;
        
        return $this->dbo->queryForList($query, $qParams, $cacheLifeTime, $cacheTags);
    }

    public function getAlbumPhotoList( $albumId, $listType, $offset, $limit )
    {
        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        switch ( $listType )
        {
            case 'featured':
                $photoFeaturedDao = PHOTO_BOL_PhotoFeaturedDao::getInstance();

                $query = "
                    SELECT `p`.*
                    FROM `" . $this->getTableName() . "` AS `p`
                    INNER JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    LEFT JOIN `" . $photoFeaturedDao->getTableName() . "` AS `f` ON (`f`.`photoId`=`p`.`id`)
                    WHERE `p`.`status` = 'approved' AND `p`.`albumId` = :albumId
                    AND `f`.`id` IS NOT NULL
                    ORDER BY `p`.`addDatetime` DESC
                    LIMIT :first, :limit";
                break;

            case 'toprated':
                $query = "SELECT `p`.*, `r`.`" . BOL_RateDao::ENTITY_ID . "`,
                    COUNT(`r`.id) as `ratesCount`, AVG(`r`.`score`) as `avgScore`
                    FROM `" . $this->getTableName() . "` AS `p`
                        INNER JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                        LEFT JOIN " . BOL_RateDao::getInstance()->getTableName() . " AS `r` ON (`r`.`entityId`=`p`.`id`
                            AND `r`.`" . BOL_RateDao::ENTITY_TYPE . "` = 'photo_rates' AND `r`.`" . BOL_RateDao::ACTIVE . "` = 1)
                    WHERE `p`.`status` = 'approved' AND `p`.`albumId` = :albumId
                    GROUP BY `p`.`id`
                    ORDER BY `avgScore` DESC, `ratesCount` DESC
                    LIMIT :first, :limit";
                break;

            case 'latest':
            default:
                $query = "
		            SELECT `p`.*
		            FROM `" . $this->getTableName() . "` AS `p`
		            INNER JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
		            WHERE `p`.`status` = 'approved' AND `p`.`albumId` = :albumId
		            ORDER BY `p`.`addDatetime` DESC
		            LIMIT :first, :limit";
                break;
        }

        return $this->dbo->queryForList($query, array('albumId' => $albumId, 'first' => $offset, 'limit' => $limit));
    }

    public function findPhotoInfoListByIdList( $idList )
    {
        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        $query = "
            SELECT `p`.*, `a`.`userId`
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
            WHERE `p`.`id` IN (" . $this->dbo->mergeInClause($idList) . ")
        ";

        return $this->dbo->queryForList($query);
    }

    /**
     * Count photos
     *
     * @param string $listtype
     * @param boolean $checkPrivacy
     * @param null $exclude
     * @return int
     */
    public function countPhotos( $listtype, $checkPrivacy = true, $exclude = null )
    {
        $privacyCond = $checkPrivacy ? " AND `p`.`privacy` = 'everybody' " : "";
        $excludeCond = $exclude ? ' AND `p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';
        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        switch ( $listtype )
        {
            case 'featured':
                $featuredDao = PHOTO_BOL_PhotoFeaturedDao::getInstance();

                $query = "
                    SELECT COUNT(`p`.`id`)
                    FROM `" . $this->getTableName() . "` AS `p`
                    INNER JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    LEFT JOIN `" . $featuredDao->getTableName() . "` AS `f` ON ( `p`.`id` = `f`.`photoId` )
                    WHERE `p`.`status` = 'approved' " . $privacyCond . $excludeCond . " AND `f`.`id` IS NOT NULL
                    AND `a`.`entityType` = 'user'
                ";

                return $this->dbo->queryForColumn($query);

            case 'latest':
            default:
                $query = "
                    SELECT COUNT(`p`.`id`)
                    FROM `" . $this->getTableName() . "` AS `p`
                    INNER JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    WHERE `p`.`status` = 'approved' " . $privacyCond . $excludeCond . "
                    AND `a`.`entityType` = 'user'
                ";

                return $this->dbo->queryForColumn($query);
        }
    }

    public function countFullsizePhotos()
    {
        $example = new OW_Example();
        $example->andFieldEqual('hasFullsize', 1);

        return $this->countByExample($example);
    }

    public function deleteFullsizePhotos()
    {
        $photos = $this->getFullsizePhotos();

        $storage = OW::getStorage();

        foreach ( $photos as $photo )
        {
            $photo->hasFullsize = 0;
            $this->save($photo);

            $path = $this->getPhotoPath($photo->id, $photo->hash, 'original');

            if ( $storage->fileExists($path) )
            {
                $storage->removeFile($path);
            }
        }

        return true;
    }

    public function getFullsizePhotos()
    {
        $example = new OW_Example();
        $example->andFieldEqual('hasFullsize', 1);

        return $this->findListByExample($example);
    }

    /**
     * Counts album photos
     *
     * @param int $id
     * @param $exclude
     * @return int
     */
    public function countAlbumPhotos( $id, $exclude )
    {
        if ( !$id )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('albumId', $id);
        $example->andFieldEqual('status', 'approved');

        if ( $exclude )
        {
            $example->andFieldNotInArray('id', $exclude);
        }

        return $this->countByExample($example);
    }
    
    public function countAlbumPhotosForList( $albumIdList )
    {
        if ( !$albumIdList )
        {
            return array();
        }
        
        $sql = "SELECT `albumId`, COUNT(*) AS `photoCount` FROM `".$this->getTableName()."` 
            WHERE `status` = 'approved' 
            AND `albumId` IN (".$this->dbo->mergeInClause($albumIdList).")
            GROUP BY `albumId`";
        
        return $this->dbo->queryForList($sql);
    }

    /**
     * Counts photos uploaded by a user
     *
     * @param int $userId
     * @return int
     */
    public function countUserPhotos( $userId )
    {
        if ( !$userId )
            return false;

        $photoAlbumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        $query = "
            SELECT COUNT(`p`.`id`)
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $photoAlbumDao->getTableName() . "` AS `a` ON ( `a`.`id` = `p`.`albumId` )
            WHERE `a`.`userId` = :user AND `a`.`entityType` = 'user'
        ";

        return $this->dbo->queryForColumn($query, array('user' => $userId));
    }

    /**
     * Returns photos in the album
     *
     * @param int $album
     * @param int $page
     * @param int $limit
     * @param $exclude
     * @return array of PHOTO_Bol_Photo
     */
    public function getAlbumPhotos( $album, $page, $limit, $exclude )
    {
        if ( !$album )
            return false;

        $first = ( $page - 1 ) * $limit;

        $example = new OW_Example();
        $example->andFieldEqual('albumId', $album);
        $example->andFieldEqual('status', 'approved');

        if ( $exclude )
        {
            $example->andFieldNotInArray('id', $exclude);
        }

        $example->setOrder('`id` DESC');
        $example->setLimitClause($first, $limit);

        return $this->findListByExample($example);
    }

    /**
     * Returns all photos in the album
     *
     * @param int $album
     * @return array of PHOTO_Bol_Photo
     */
    public function getAlbumAllPhotos( $album )
    {
        if ( !$album )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('albumId', $album);

        return $this->findListByExample($example);
    }

    /**
     * Returns album's first photo
     *
     * @param int $albumId
     * @return PHOTO_Bol_Photo
     */
    public function getFirstPhoto( $albumId )
    {
        if ( !$albumId )
        {
            return false;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        $example->setOrder('`addDatetime` ASC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    public function getFirstPhotoForList( $albumIdList )
    {
        if ( !$albumIdList )
        {
            return array();
        }
        
        $sql = "SELECT * FROM `".$this->getTableName()."` 
            WHERE `status` = 'approved' 
            AND `albumId` IN (".$this->dbo->mergeInClause($albumIdList).")
            GROUP BY `albumId`
            ORDER BY `addDatetime` DESC";
        
        return $this->dbo->queryForObjectList($sql, 'PHOTO_BOL_Photo');
    }

    /**
     * Returns album's last photo
     *
     * @param int $albumId
     * @return PHOTO_Bol_Photo
     */
    public function getLastPhoto( $albumId )
    {
        if ( !$albumId )
        {
            return false;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        $example->setOrder('`addDatetime` DESC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * Returns album's next photo
     *
     * @param int $albumId
     * @param int $id
     * @return PHOTO_Bol_Photo
     */
    public function getNextPhoto( $albumId, $id )
    {
        if ( !$id )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldLessThan('id', $id);
        $example->andFieldEqual('status', 'approved');
        $example->setOrder('`id` DESC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * Returns album's previous photo
     *
     * @param int $albumId
     * @param int $id
     * @return PHOTO_Bol_Photo
     */
    public function getPreviousPhoto( $albumId, $id )
    {
        if ( !$albumId || !$id )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        $example->andFieldGreaterThan('id', $id);
        $example->setOrder('`id` ASC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }

    /**
     * Returns currently viewed photo index
     *
     * @param int $albumId
     * @param int $id
     * @return int
     */
    public function getPhotoIndex( $albumId, $id )
    {
        if ( !$albumId || !$id )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        $example->andFieldGreaterThenOrEqual('id', $id);

        return $this->countByExample($example);
    }

    /**
     * Removes photo file
     *
     * @param int $id
     * @param $hash
     * @param string $type
     */
    public function removePhotoFile( $id, $hash, $type )
    {
        $path = $this->getPhotoPath($id, $hash, $type);

        $storage = OW::getStorage();

        if ( $storage->fileExists($path) )
        {
            $storage->removeFile($path);
        }
    }
    
    public function updatePrivacyByAlbumIdList( $albumIdList, $privacy )
    {
        $albums = implode(',', $albumIdList);

        $sql = "UPDATE `".$this->getTableName()."` SET `privacy` = :privacy 
            WHERE `albumId` IN (".$albums.")";
        
        $this->dbo->query($sql, array('privacy' => $privacy));
    }
    
    // Entity photos methods
    
    public function findEntityPhotoList( $entityType, $entityId, $first, $count, $status = "approved" )
    {
        $limit = (int) $count;

        $statusSql = $status === null ? "1" : "`p`.`status` = '{$status}'";
        
        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        $query = "
            SELECT `p`.*
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
            WHERE $statusSql
            AND `a`.`entityType` = :entityType
            AND `a`.`entityId` = :entityId
            ORDER BY `p`.`addDatetime` DESC
            LIMIT :first, :limit";

        $qParams = array(
            'first' => $first,
            'limit' => $limit,
            "entityType" => $entityType,
            "entityId" => $entityId
        );

        $cacheLifeTime = $first == 0 ? 24 * 3600 : null;
        $cacheTags = $first == 0 ? array(self::CACHE_TAG_PHOTO_LIST) : null;

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $qParams, $cacheLifeTime, $cacheTags);
    }
    
    public function countEntityPhotos( $entityType, $entityId, $status = "approved" )
    {
        $photoAlbumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        
        $statusSql = $status === null ? "1" : "`p`.`status` = '{$status}'";

        $query = "
            SELECT COUNT(`p`.`id`)
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $photoAlbumDao->getTableName() . "` AS `a` ON ( `a`.`id` = `p`.`albumId` )
            WHERE $statusSql AND `a`.`entityType` = :entityType AND `a`.`entityId`=:entityId
        ";

        return $this->dbo->queryForColumn($query, array(
            "entityType" => $entityType,
            "entityId" => $entityId
        ));
    }

    public function findPhotoListByUploadKey( $uploadKey, $exclude )
    {
        $example = new OW_Example();
        $example->andFieldEqual('uploadKey', $uploadKey);
        if ( $exclude && is_array($exclude) )
        {
            $example->andFieldNotInArray('id', $exclude);
        }
        $example->setOrder('`addDatetime` DESC');

        return $this->findListByExample($example);
    }
}