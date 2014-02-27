<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_AttachmentService
{
    /**
     * @var BOL_AttachmentDao
     */
    private $attachmentDao;
    /**
     * Singleton instance.
     *
     * @var BOL_AttachmentService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AttachmentService
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
     * Constructor.
     */
    private function __construct()
    {
        $this->attachmentDao = BOL_AttachmentDao::getInstance();
    }

    public function deleteExpiredTempImages()
    {
        $attachList = $this->attachmentDao->findExpiredInactiveItems(time() - 3600);

        /* @var $item BOL_Attachment */
        foreach ( $attachList as $item )
        {
            if ( file_exists($this->getAttachmentsTempDir() . $item->getFileName()) )
            {
                unlink($this->getAttachmentsTempDir() . $item->getFileName());
            }

            $this->attachmentDao->delete($item);
        }
    }

    public function deleteUserAttachments( $userId )
    {
        $list = $this->attachmentDao->findByUserId($userId);

        /* @var $item BOL_Attachment */
        foreach ( $list as $item )
        {
            if ( file_exists($this->getAttachmentsDir() . $item->getFileName()) )
            {
                unlink($this->getAttachmentsDir() . $item->getFileName());
            }

            $this->attachmentDao->delete($item);
        }
    }

    public function deleteAttachmentByUrl( $url )
    {
        $attch = $this->attachmentDao->findAttachmentByFileName(trim(basename($url)));

        if ( $attch != NULL )
        {
            if ( OW::getStorage()->fileExists($this->getAttachmentsDir() . $attch->getFileName()) )
            {
                OW::getStorage()->removeFile($this->getAttachmentsDir() . $attch->getFileName());
            }

            $this->attachmentDao->delete($attch);
        }
        else
        {
            if ( OW::getStorage()->fileExists($this->getAttachmentsDir() . basename($url)) )
            {
                OW::getStorage()->removeFile($this->getAttachmentsDir() . basename($url));
            }
        }
    }

    public function deleteAttachmentById( $id )
    {
        // TODO: implement
    }

    public function saveTempImage( $id )
    {
        $attch = $this->attachmentDao->findById($id);
        /* @var $attch BOL_Attachment */
        if ( $attch === null )
        {
            return '_INVALID_URL_';
        }

        $filePath = $this->getAttachmentsTempDir() . $attch->getFileName();

        if ( OW::getUser()->isAuthenticated() && file_exists($filePath) )
        {
            OW::getStorage()->copyFile($filePath, $this->getAttachmentsDir() . $attch->getFileName());
            unlink($filePath);
        }

        $attch->setStatus(true);
        $this->attachmentDao->save($attch);

        return OW::getStorage()->getFileUrl($this->getAttachmentsDir() . $attch->getFileName());
    }
    /*
     * @param array $fileInfo
     * @return array
     */

    public function processPhotoAttachment( array $fileInfo )
    {
        $language = OW::getLanguage();
        $error = false;

        if ( !OW::getUser()->isAuthenticated() || empty($fileInfo) || !is_uploaded_file($fileInfo['tmp_name']) )
        {
            $error = $language->text('base', 'upload_file_fail');
        }

        if ( $fileInfo['error'] != UPLOAD_ERR_OK )
        {
            switch ( $fileInfo['error'] )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $error = $language->text('base', 'upload_file_max_upload_filesize_error');
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $error = $language->text('base', 'upload_file_file_partially_uploaded_error');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $error = $language->text('base', 'upload_file_no_file_error');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = $language->text('base', 'upload_file_no_tmp_dir_error');
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $error = $language->text('base', 'upload_file_cant_write_file_error');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $error = $language->text('base', 'upload_file_invalid_extention_error');
                    break;

                default:
                    $error = $language->text('base', 'upload_file_fail');
            }
        }

        if ( !in_array(UTIL_File::getExtension($_FILES['attachment']['name']), array('jpeg', 'jpg', 'png', 'gif')) )
        {
            $error = $language->text('base', 'upload_file_extension_is_not_allowed');
        }

        if ( (int) $_FILES['attachment']['size'] > (float) OW::getConfig()->getValue('base', 'tf_max_pic_size') * 1024 * 1024 )
        {
            $error = $language->text('base', 'upload_file_max_upload_filesize_error');
        }

        if ( $error !== false )
        {
            throw new InvalidArgumentException($error);
        }

        $attachDto = new BOL_Attachment();
        $attachDto->setUserId(OW::getUser()->getId());
        $attachDto->setAddStamp(time());
        $attachDto->setStatus(0);
        $this->attachmentDao->save($attachDto);
        $fileName = 'attach_' . $attachDto->getId() . '.' . UTIL_File::getExtension($_FILES['attachment']['name']);
        $attachDto->setFileName($fileName);
        $this->attachmentDao->save($attachDto);

        $uploadPath = $this->getAttachmentsDir() . $fileName;
        $uploadUrl = $this->getAttachmentsUrl() . $fileName;

        try
        {
            $image = new UTIL_Image($fileInfo['tmp_name']);
            $image->resizeImage(1000, 1000)->orientateImage()->saveImage($uploadPath);
        }
        catch ( Exception $e )
        {
            throw new InvalidArgumentException($language->text('base', 'upload_file_fail'));
        }

        chmod($uploadPath, 0666);

        return array('genId' => $attachDto->getId(), 'url' => $uploadUrl);
    }

    public function getAttachmentsTempUrl()
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'attachments/temp/';
    }

    public function getAttachmentsTempDir()
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS . 'temp' . DS;
    }

    public function getAttachmentsUrl()
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'attachments/';
    }

    public function getAttachmentsDir()
    {
        return OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments' . DS;
    }

    public function saveAttachment( BOL_Attachment $dto )
    {
        $this->attachmentDao->save($dto);
    }
}