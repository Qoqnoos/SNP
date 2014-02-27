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
 * @package ow.plugin.photo.mobile.controllers
 * @since 1.6.0
 */
class PHOTO_MCTRL_Upload extends OW_MobileActionController
{
    /**
     * @var PHOTO_BOL_PhotoService
     */
    private $photoService;

    public function __construct()
    {
        parent::__construct();

        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
    }

    public function photo( array $params = null )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();

        if ( !OW::getUser()->isAuthorized('photo', 'upload') )
        {
            $this->assign('auth_msg', $language->text('photo', 'auth_upload_permissions'));
            return;
        }

        $config = OW::getConfig();
        $userQuota = (int) $config->getValue('photo', 'user_quota');
        $userId = OW::getUser()->getId();

        $eventParams = array('pluginKey' => 'photo', 'action' => 'add_photo');
        $credits = OW::getEventManager()->call('usercredits.check_balance', $eventParams);

        if ( $credits === false )
        {
            $this->assign('auth_msg', OW::getEventManager()->call('usercredits.error_message', $eventParams));
        }
        else if ( !($this->photoService->countUserPhotos($userId) <= $userQuota) )
        {
            $this->assign('auth_msg', $language->text('photo', 'quota_exceeded', array('limit' => $userQuota)));
        }
        else
        {
            $accepted = floatval($config->getValue('photo', 'accepted_filesize') * 1024 * 1024);
            $this->assign('auth_msg', null);

            $form = new PHOTO_MCLASS_UploadForm();
            $this->addForm($form);

            $photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();
            $albums = $photoAlbumService->findUserAlbumList($userId, 1, 100);
            $this->assign('albums', $albums);

            if ( !empty($params['album']) && (int) $params['album'] )
            {
                $albumId = (int) $params['album'];
                $uploadToAlbum = $photoAlbumService->findAlbumById($albumId);
                if ( !$uploadToAlbum || $uploadToAlbum->userId != $userId )
                {
                    $this->redirect(OW::getRouter()->urlForRoute('photo_upload'));
                }
                $form->getElement('album')->setValue($uploadToAlbum->name);
            }

            if ( $albums )
            {
                $script = '$("#album_select").change(function(event){
                    $("#album_input").val($(this).val());
                });';
                OW::getDocument()->addOnloadScript($script);
            }

            $script = '$("#upload-file-field").change(function(){
                var img = $("#photo-file-prevew");
                var name = $(".owm_upload_img_name_label span");

                img.hide();
                name.text("");

                if (!this.files || !this.files[0]) return;

                if ( window.FileReader ) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        img.show().attr("src", e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);
                } else {
                    name.text(this.files[0].name);
                }
                $(".owm_upload_photo_browse_wrap").addClass("owm_upload_photo_attach_wrap");
            });';
            OW::getDocument()->addOnloadScript($script);

            if ( OW::getRequest()->isPost() )
            {
                $form->isValid($_POST);
                $values = $form->getValues();

                // Delete old temporary photos
                $tmpPhotoService = PHOTO_BOL_PhotoTemporaryService::getInstance();
                $photoService = PHOTO_BOL_PhotoService::getInstance();

                $file = $_FILES['photo'];
                $tmpPhotoService->deleteUserTemporaryPhotos($userId);
                if ( strlen($file['tmp_name']) )
                {
                    if ( !UTIL_File::validateImage($file['name']) || $file['size'] > $accepted )
                    {
                        OW::getFeedback()->warning($language->text('photo', 'no_photo_uploaded'));
                        $this->redirect();
                    }

                    $tmpPhotoService->addTemporaryPhoto($file['tmp_name'], $userId, 1);
                    $tmpList = $tmpPhotoService->findUserTemporaryPhotos($userId, 'order');
                    $tmpList = array_reverse($tmpList);

                    // check album exists
                    if ( !($album = $photoAlbumService->findAlbumByName($values['album'], $userId)) )
                    {
                        $album = new PHOTO_BOL_PhotoAlbum();
                        $album->name = $values['album'];
                        $album->userId = $userId;
                        $album->createDatetime = time();

                        $photoAlbumService->addAlbum($album);
                    }

                    foreach ( $tmpList as $tmpPhoto )
                    {
                        $photo = $tmpPhotoService->moveTemporaryPhoto($tmpPhoto['dto']->id, $album->id, $values['description'], null);

                        if ( $photo && $credits === true )
                        {
                            OW::getEventManager()->call('usercredits.track_action', $eventParams);
                        }

                        if ( $photo )
                        {
                            $photoParams = array('addTimestamp' => $photo->addDatetime, 'photoId' => $photo->id, 'hash' => $photo->hash);
                            $event = new OW_Event(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_ADD, array($photoParams));
                            OW::getEventManager()->trigger($event);

                            $photoService->triggerNewsfeedEventOnSinglePhotoAdd($photo->id, $userId);

                            OW::getFeedback()->info($language->text('photo', 'photos_uploaded', array('count' => 1)));
                            $this->redirect(OW::getRouter()->urlForRoute('view_photo', array('id' => $photo->id)));
                        }
                    }
                }
                else
                {
                    OW::getFeedback()->warning($language->text('photo', 'no_photo_uploaded'));
                    $this->redirect();
                }
            }
        }

        OW::getDocument()->setHeading($language->text('photo', 'upload_photos'));
        OW::getDocument()->setTitle($language->text('photo', 'meta_title_photo_upload'));
    }
}