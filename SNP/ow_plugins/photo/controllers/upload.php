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
 * Photo upload action controller
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
 * @package ow.plugin.photo.controllers
 * @since 1.0
 */
class PHOTO_CTRL_Upload extends OW_ActionController
{
    /**
     * @var PHOTO_BOL_PhotoService
     */
    protected $photoService;
    /**
     * @var PHOTO_BOL_PhotoAlbumService
     */
    protected $photoAlbumService;

    
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
        $this->photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();
    }
    
    public function init()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'photo', 'photo');
        }
    }

    protected function checkUploadPermissins( $entityType, $entityId )
    {
        // disallow not authenticated access
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $language = OW::getLanguage();
        $userId = OW::getUser()->getId();

        $config = OW::getConfig();
        $userQuota = (int) $config->getValue('photo', 'user_quota');
        
        if ( !OW::getUser()->isAuthorized('photo', 'upload') )
        {
            throw new PHOTO_Exception($language->text('photo', 'auth_upload_permissions'));
        }
        
        $eventParams = array('pluginKey' => 'photo', 'action' => 'add_photo');
        
        $credits = OW::getEventManager()->call('usercredits.check_balance', $eventParams);

        if ( $credits === false )
        {
            throw new PHOTO_Exception(OW::getEventManager()->call('usercredits.error_message', $eventParams));
        }
        else if ( !($this->photoService->countUserPhotos($userId) <= $userQuota) )
        {
            throw new PHOTO_Exception($language->text('photo', 'quota_exceeded', array(
                'limit' => $userQuota
            )));
        }
    }
    
    protected function getEntity( $params )
    {
        if ( empty($params["entityType"]) || empty($params["entityId"]) )
        {
            $params["entityType"] = "user";
            $params["entityId"] = OW::getUser()->getId();
        }
        
        return array($params["entityType"], $params["entityId"]);
    }
    
    protected function onUploadComplete( $entityType, $entityId, $albumId )
    {
        if ( !empty($albumId) )
        {
            $this->redirect(OW::getRouter()->urlForRoute("photo_upload_submit_album", array('album' => $albumId)));
        }
        else
        {
            $this->redirect(OW::getRouter()->urlForRoute("photo_upload_submit"));
        }
    }
    
    protected function onUploadReset( $entityType, $entityId )
    {
        $this->redirect(OW::getRouter()->urlForRoute('photo_upload'));
    }
    
    public function flashUploadComplete( $params )
    {
        $this->onUploadComplete($params["entityType"], $params["entityId"], empty($params["albumId"]) ? null : $params["albumId"]);
    }
    
    public function uploadReset( $params )
    {
        $this->onUploadReset($params["entityType"], $params["entityId"]);
    }
    
    
    public function flashUpload( )
    {
        $photo = $_FILES['photo'];
        $order = !empty($_POST['order']) ? (int) $_POST['order'] : 0;
        $tmpPhotoService = PHOTO_BOL_PhotoTemporaryService::getInstance();

        $config = OW::getConfig();
        $accepted = floatval($config->getValue('photo', 'accepted_filesize') * 1024 * 1024);

        if ( strlen($photo['tmp_name']) )
        {
            if ( !UTIL_File::validateImage($photo['name']) || $photo['size'] > $accepted )
            {
                echo "error"; exit;
            }

            if ( $tmpPhotoService->addTemporaryPhoto($photo['tmp_name'], OW::getUser()->getId(), $order) )
            {
                echo "ok"; exit;
            }
        }

        echo "error"; exit;
    }
    
    
    /**
     * 
     * @param string $entityType
     * @param int $entityId
     * @return PhotoUploadForm
     */
    protected function createPhotoUploadForm( $entityType, $entityId )
    {
        return new PhotoUploadForm(get_class($this), $entityType, $entityId);
    }
    
    
    /**
     * 
     * @return BASE_CMP_ContentMenu
     */
    protected function getMenu()
    {
        $advancedUpload = OW::getConfig()->getValue('photo', 'advanced_upload_enabled');
        
        if ( !$advancedUpload )
        {
            return null;
        }
        
        $language = OW::getLanguage();
        
        $menuItems = array();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('photo', 'advanced_upload'));
        $item->setUrl('js-call:upload_advanced');
        $item->setKey('upload_advanced');
        $item->setIconClass('ow_ic_files');
        $item->setOrder(1);
        $item->setActive(true);
        array_push($menuItems, $item);

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('photo', 'simple_upload'));
        $item->setUrl('js-call:upload_simple');
        $item->setKey('upload_simple');
        $item->setIconClass('ow_ic_file');
        $item->setOrder(2);
        $item->setActive(false);
        array_push($menuItems, $item);

        $menu = new BASE_CMP_ContentMenu($menuItems);
        
        return $menu;
    }
    
    /**
     * Default action
     */
    public function index( array $params = null )
    {
        $this->setTemplate(OW::getPluginManager()->getPlugin("photo")->getCtrlViewDir() . "upload_index.html");
        
        list($entityType, $entityId) = $this->getEntity($params);
        
        try
        {
            $this->checkUploadPermissins($entityType, $entityId);
        }
        catch ( PHOTO_Exception $e )
        {
            $this->assign("auth_msg", $e->getMessage());
            
            return;
        }
        
        $language = OW::getLanguage();
        $userId = OW::getUser()->getId();

        $config = OW::getConfig();
        
        if ( !empty($params['album']) && (int) $params['album'] )
        {
            $albumId = (int) $params['album'];
            $uploadToAlbum = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($albumId);
            if ( !$uploadToAlbum || $uploadToAlbum->userId != $userId )
            {
                $this->onUploadReset($entityType, $entityId);
            }
        }

        
        $fileSizeLimit = $config->getValue('photo', 'accepted_filesize');
        $this->assign('limitMsg', $language->text('photo', 'size_limit', array('size' => $fileSizeLimit)));

        $this->assign('auth_msg', null);

        $photoUploadForm = $this->createPhotoUploadForm($entityType, $entityId);
        
        if ( isset($uploadToAlbum) )
        {
            $photoUploadForm->getElement('albumId')->setValue($uploadToAlbum->id);
        }
        
        $this->addForm($photoUploadForm);
        
        $tmpPhotoService = PHOTO_BOL_PhotoTemporaryService::getInstance();

        if ( OW::getRequest()->isPost() )
        {
            if ( !$photoUploadForm->isValid($_POST) )
            {
                OW::getFeedback()->warning($language->text('photo', 'no_photo_uploaded'));
                $this->redirect();
            }

            $values = $photoUploadForm->getValues();
            $photosArray = $values['photos'];

            if ( !count($photosArray['name']) )
            {
                OW::getFeedback()->warning($language->text('photo', 'no_photo_uploaded'));
                $this->redirect();
            }
            $accepted = floatval($config->getValue('photo', 'accepted_filesize') * 1024 * 1024);

            // Delete old temporary photos
            $tmpPhotoService->deleteUserTemporaryPhotos($userId);

            $uploadedCount = 0;
            $selectedCount = 0;
            $photosArray = array_reverse($photosArray);

            for ( $i = 0; $i < count($photosArray['name']); $i++ )
            {
                if ( strlen($photosArray['name'][$i]) )
                {
                    $selectedCount++;
                }

                if ( strlen($photosArray['tmp_name'][$i]) )
                {
                    if ( !UTIL_File::validateImage($photosArray['name'][$i]) || $photosArray['size'][$i] > $accepted )
                    {
                        continue;
                    }

                    if ( $tmpPhotoService->addTemporaryPhoto($photosArray['tmp_name'][$i], $userId, $i) )
                    {
                        $uploadedCount++;
                    }
                }
            }

            if ( $uploadedCount == 0 )
            {
                OW::getFeedback()->warning($language->text('photo', 'no_photo_uploaded'));
                $this->redirect();
            }
            else if ( $selectedCount > $uploadedCount )
            {
                OW::getFeedback()->warning($language->text('photo', 'not_all_photos_uploaded'));
            }

            $this->onUploadComplete($entityType, $entityId, $uploadedCount, empty($values['albumId']) ? null : $values['albumId']);
        }

        $advancedUpload = OW::getConfig()->getValue('photo', 'advanced_upload_enabled');

        if ( $advancedUpload )
        {
            $menuJs = 'var $tabs = $("a[href^=js-call]", "#ow_photo_upload_menu");
                $tabs.click(function(){
                    var $this = $(this);
                    $tabs.parent().removeClass("active");
                    $this.parent().addClass("active");
                    $(".ow_photo_upload_page").hide();
                    $("#page_" + $this.data("tab_content")).show();

                }).each(function(){
                    var command = this.href.split(":");
                    $(this).data("tab_content", command[1]);
                    $(this).attr("href", "javascript://");
                });';

            OW::getDocument()->addOnloadScript($menuJs);

            $completeUrl = OW::getRouter()->urlFor(get_class($this), "flashUploadComplete", array(
                "entityType" => $entityType,
                "entityId" => $entityId,
                "albumId" => empty($uploadToAlbum) ? null : $uploadToAlbum->id
            ));

            OW::getDocument()->addScriptDeclaration(
                'window.flashUploadComplete = function() {
                    document.location.href = '.json_encode($completeUrl).';
                };');

            $plugin = OW::getPluginManager()->getPlugin('photo');
            OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'swfobject.js');

            $mainSwfUrl = $plugin->getStaticUrl() . 'swf/main.swf';
            $xiSwfUrl = $plugin->getStaticUrl() . 'swf/playerProductInstall.swf';

            $res = OW::getConfig()->getValue('photo', 'fullsize_resolution');

            $path = OW::getRouter()->urlFor(get_class($this), 'flashUpload', array(
                "entityType" => $entityType, "entityId" => $entityId
            ));
            
            preg_match('/^http(s)?:\/\/[^?#%\/]+\/(.*)/', $path, $match);
            $path = $match[2];

            $js = 'var swfVersionStr = "10.0.0";
            var xiSwfUrlStr = "'.$xiSwfUrl.'";
            var flashvars = {};
            flashvars.uploadPath = "'.$path.'";
            flashvars.fileName = "photo";
            flashvars.lang = '.$this->getLangXml().';
            flashvars.album = "my-album";
            flashvars.description = "description";
            flashvars.res = '.json_encode($res ? $res : 1024).';
            var params = {};
            params.wmode = "transparent";
            params.quality = "high";
            params.bgcolor = "#ffffff";
            params.allowscriptaccess = "sameDomain";
            params.allowfullscreen = "false";
            var attributes = {};
            attributes.id = "Main";
            attributes.name = "Main";
            attributes.align = "middle";
            swfobject.embedSWF("'.$mainSwfUrl.'", "ow_flash_photo_uploader", "695", "440", swfVersionStr, xiSwfUrlStr, flashvars, params, attributes);
            swfobject.createCSS("#ow_flash_photo_uploader", "display:block; text-align:left;");';

            OW::getDocument()->addOnloadScript($js);

            $tmpPhotoService->deleteUserTemporaryPhotos($userId);
        }

        $this->assign('advancedUpload', $advancedUpload);

        OW::getDocument()->setTitle($language->text('photo', 'meta_title_photo_upload'));
        OW::getDocument()->setHeading($language->text('photo', 'upload_photos'));
        OW::getDocument()->setHeadingIconClass('ow_ic_picture');
        OW::getDocument()->setDescription($language->text('photo', 'meta_description_photo_upload'));
        
        $albumsUrl = OW::getRouter()->urlForRoute(
            'photo_user_albums',
            array('user' => BOL_UserService::getInstance()->getUserName($userId))
        );

        $this->assign("allAlbumsBtn", array(
            "label" => $language->text("photo", "my_albums"),
            "url" => $albumsUrl
        ));

        $menu = $this->getMenu();
        
        if ( $menu !== null )
        {
            $this->addComponent("menu", $menu);
        }
    }

    private function getLangXml( )
    {
        $lang = OW::getLanguage();

        $xml = "<langs>".
            "<browse>".$lang->text('photo', 'advanced_upload_browse')."</browse>".
            "<upload>".$lang->text('photo', 'advanced_upload_upload')."</upload>".
            "<processing>".$lang->text('photo', 'advanced_upload_processing')."</processing>".
            "<uploading>".$lang->text('photo', 'advanced_upload_uploading')."</uploading>".
            "<complete>".$lang->text('photo', 'advanced_upload_complete')."</complete>".
            "<popup_add_more>".$lang->text('photo', 'advanced_upload_add_more')."</popup_add_more>".
            "<popup_upload>".$lang->text('photo', 'advanced_upload_yes')."</popup_upload>".
            "<upload_confirm_question>".$lang->text('photo', 'advanced_upload_confirm')."</upload_confirm_question>".
            "</langs>";

        return json_encode($xml);
    }

    /**
     * 
     * @param array $list
     * @param string $entityType
     * @param int $entityId
     * @return PhotoSubmitForm
     */
    protected function createPhotoSubmitForm( $list, $entityType, $entityId )
    {
        $responderUrl = OW::getRouter()->urlFor(get_class($this), 'suggestAlbum', array(
            'userId' => OW::getUser()->getId(),
            "entityType" => $entityType,
            "entityId" => $entityId
        ));
        
        return new PhotoSubmitForm($list, $entityType, $entityId, $responderUrl);
    }
    
    public function submit( array $params = null )
    {
        $this->setTemplate(OW::getPluginManager()->getPlugin("photo")->getCtrlViewDir() . "upload_submit.html");
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }
        
        list($entityType, $entityId) = $this->getEntity($params);

        $lang = OW::getLanguage();
        $service = PHOTO_BOL_PhotoTemporaryService::getInstance();
        $list = $service->findUserTemporaryPhotos(OW::getUser()->getId(), 'order');

        if ( !$list )
        {
            $this->onUploadReset($entityType, $entityId);
        }

        $this->assign('list', $list);

        $form = $this->createPhotoSubmitForm($list, $entityType, $entityId);
        if ( !empty($params['album']) && (int) $params['album'] )
        {
            $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($params['album']);
            if ( $album && $album->userId == OW::getUser()->getId() )
            {
                $form->getElement('album')->setValue($album->name);
            }
        }
        $this->addForm($form);

        $slots = array();
        foreach ( $list as $photo )
        {
            $slots[$photo['dto']->id] = array('id' => $photo['dto']->id, 'tag' => '', 'desc' => '');
        }

        $lang->addKeyForJs('photo', 'confirm_delete');
        $lang->addKeyForJs('photo', 'add_tags');
        $lang->addKeyForJs('photo', 'describe_photo');
        $lang->addKeyForJs('photo', 'no_photo_selected');
        $lang->addKeyForJs('photo', 'add_description');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('photo')->getStaticJsUrl() . 'upload_photo.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'jquery.tagsinput.js');
        OW::getDocument()->addOnloadScript("$('#photo-tag-input').tagsInput({'height':'auto', 'width':'auto', 'interactive':true, 'defaultText':'".OW::getLanguage()->text('base', 'tags_input_field_invitation')."', 'removeWithBackspace':true, 'minChars':3, 'maxChars':0, 'placeholderColor':'#666666'});");
        $params = array();
        $params['slots'] = $slots;
        $params['ajaxSubmitResponder'] = OW::getRouter()->urlFor(get_class($this), "ajaxSubmitPhotos", array(
            "entityType" => $entityType, "entityId" => $entityId
        ));
        
        $params['ajaxDeleteResponder'] = OW::getRouter()->urlFor(get_class($this), "ajaxDeletePhoto", array(
            "entityType" => $entityType, "entityId" => $entityId
        ));
        
        $params['formId'] = $form->getId();
        $params['singleSlotId'] = count($list) == 1 ? $photo['dto']->id : 0;
        OW::getDocument()->addOnloadScript("var upload_photo = new UploadPhoto(".json_encode($params).");");

        OW::getDocument()->setHeading($lang->text('photo', 'describe_photos'));
        OW::getDocument()->setHeadingIconClass('ow_ic_picture');
        OW::getDocument()->setTitle($lang->text('photo', 'meta_title_photo_upload'));
    }

    /**
     * Prepare values for suggest field
     *
     * @param array $params
     */
    public function suggestAlbum( array $params )
    {
        list($entityType, $entityId) = $this->getEntity($params);
        
        $userId = trim($params['userId']);

        if ( OW::getRequest()->isAjax() )
        {
            // collect default album names
            $event = new BASE_CLASS_EventCollector(PHOTO_CLASS_EventHandler::EVENT_SUGGEST_DEFAULT_ALBUM, array(
                'userId' => $userId,
                "entityType" => $entityType,
                "entityId" => $entityId
            ));

            OW::getEventManager()->trigger($event);

            $data = $event->getData();

            foreach ( $data as $album )
            {
                echo $album."\t0\n";
            }

            $albums = $this->photoAlbumService->suggestEntityAlbums($entityType, $entityId, $_GET['q']);

            if ( $albums )
            {
                foreach ( $albums as $album )
                {
                    if ( !empty($data['albums']) && in_array($album->name, $data['albums']) )
                    {
                        continue;
                    }
                    echo "$album->name\t$album->id\n";
                }
            }
            exit();
        }
        else
        {
            throw new Redirect404Exception();
        }
    }

    public function ajaxSubmitPhotos( $params )
    {
        list($entityType, $entityId) = $this->getEntity($params);
        
        $lang = OW::getLanguage();

        if ( !strlen($albumName = htmlspecialchars(trim($_POST['album']))) )
        {
            $resp = array('result' => false, 'msg' => $lang->text('photo', 'photo_upload_error'));
            exit(json_encode($resp));
        }

        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();
        $photoTmpService = PHOTO_BOL_PhotoTemporaryService::getInstance();

        $userId = OW::getUser()->getId();

        $tmpList = $photoTmpService->findUserTemporaryPhotos($userId, 'order');
        if ( !$tmpList )
        {
            $resp = array('result' => false, 'msg' => $lang->text('photo', 'photo_upload_error'));
            exit(json_encode($resp));
        }

        // check album exists
        if ( !($album = $photoAlbumService->findAlbumByName($albumName, $userId)) )
        {
            $album = new PHOTO_BOL_PhotoAlbum();
            $album->name = $albumName;
            $album->userId = $userId;
            $album->entityId = $entityId;
            $album->entityType = $entityType;
            $album->createDatetime = time();

            $photoAlbumService->addAlbum($album);
        }

        $photos = array();

        $slots = $_POST['slots'];
        $tmpList = array_reverse($tmpList);

        foreach ( $tmpList as $tmpPhoto )
        {
            $tmpId = $tmpPhoto['dto']->id;
            if ( !empty($slots[$tmpId]) )
            {
                try
                {
                    $this->onBeforeTmpPhotoMove($slots[$tmpId]);
                }
                catch ( PHOTO_Exception $e )
                {
                    $resp = array('result' => false, 'msg' => $e->getMessage());
                    exit(json_encode($resp));
                }

                $photo = $photoTmpService->moveTemporaryPhoto($tmpId, $album->id, $slots[$tmpId]['desc'], $slots[$tmpId]['tag']);

                if ( $photo )
                {
                    $photos[] = $photo;
                    
                    $this->onAfterTmpPhotoMove($photo);
                }
            }
        }

        $resp = $this->onSubmitComplete($entityType, $entityId, $album, $photos);
        
        exit(json_encode($resp));
    }
    
    protected function onBeforeTmpPhotoMove()
    {
        $eventParams = array('pluginKey' => 'photo', 'action' => 'add_photo');
        $credits = OW::getEventManager()->call('usercredits.check_balance', $eventParams);
        if ( $credits === false )
        {
            throw new PHOTO_Exception(OW::getEventManager()->call('usercredits.error_message', $eventParams));
        }
    }
    
    protected function onAfterTmpPhotoMove( PHOTO_BOL_Photo $newPhoto )
    {
        OW::getEventManager()->call('usercredits.track_action', array(
            'pluginKey' => 'photo',
            'action' => 'add_photo'
        ));
    }
    
    protected function onSubmitComplete( $entityType, $entityId, PHOTO_BOL_PhotoAlbum $album, $photos )
    {
        $result = array();
        
        if ( empty($photos) )
        {
            OW::getFeedback()->warning(OW::getLanguage()->text('photo', 'no_photo_uploaded'));
            
            $result["url"] = OW::getRouter()->urlFor(get_class($this), "uploadReset", array(
                "entityType" => $entityType, "entityId" => $entityId
            ));
            
            return $result;
        }
        
        $movedArray = array();
        foreach ( $photos as $photo )
        {
            $movedArray[] = array(
                'entityType' => $entityType,
                'entityId' => $entityId,
                'addTimestamp' => $photo->addDatetime,
                'photoId' => $photo->id,
                'hash' => $photo->hash
            );
        }
        
        $event = new OW_Event(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_ADD, $movedArray);
        OW::getEventManager()->trigger($event);
        
        $userId = OW::getUser()->getId();
        $result["url"] = OW::getRouter()->urlForRoute('photo_user_album', array(
            'user' => BOL_UserService::getInstance()->getUserName($userId),
            'album' => $album->id
        ));

        $photoCount = count($photos);
        
        if ( $photoCount == 1 )
        {
            $this->photoService->triggerNewsfeedEventOnSinglePhotoAdd($photos[0]->id, $userId);
        }
        else
        {
            $this->photoService->triggerNewsfeedEventOnMultiplePhotosAdd($photos, $userId, $album);
        }

        OW::getFeedback()->info(OW::getLanguage()->text('photo', 'photos_uploaded', array('count' => $photoCount)));
        
        return $result;
    }

    public function ajaxDeletePhoto( $params )
    {
        list($entityType, $entityId) = $this->getEntity($params);
        
        if ( empty($_POST['photoId']) || !$_POST['photoId'] )
        {
            $resp = array('result' => false);
            exit(json_encode($resp));
        }

        $service = PHOTO_BOL_PhotoTemporaryService::getInstance();

        if ( $service->deleteTemporaryPhoto($_POST['photoId']) )
        {
            $resp = array('result' => true);
            exit(json_encode($resp));
        }
    }
}


/**
 * Photo upload form class
 */
class PhotoUploadForm extends Form
{
    public function __construct()
    {
        parent::__construct('photoUploadForm');

        $language = OW::getLanguage();

        $this->setEnctype('multipart/form-data');

        $filesNumber = 5;
        $labels = array();
        for ( $i = 0; $i < $filesNumber; $i++ )
        {
            $labels[$i] = $language->text('photo', 'pic_number', array('number' => $i + 1));
        }

        $filesField = new MultiFileField('photos', $filesNumber, $labels);
        $this->addElement($filesField);
        $filesField->setRequired(true);

        $albumIdField = new HiddenField('albumId');
        $this->addElement($albumIdField);

        $submit = new Submit('upload');
        $submit->setValue($language->text('photo', 'btn_upload'));
        $this->addElement($submit);
    }
}

/**
 * Photo submit form class
 */
class PhotoSubmitForm extends Form
{
    public function __construct( $list, $entityType, $entityId, $albumSuggestRsp )
    {
        parent::__construct('photoSubmitForm');

        $language = OW::getLanguage();

        // album suggest Field
        $albumField = new SuggestField('album');
        $albumField->setRequired(true);
        $albumField->setMinChars(1);

        // description Field
        $descField = new Textarea('description');
        $this->addElement($descField->setLabel($language->text('photo', 'description')));

        if ( count($list) == 1 )
        {
            $tagsField = new TagsInputField('tags');
            $this->addElement($tagsField->setLabel($language->text('photo', 'tags')));
        }

        $userId = OW::getUser()->getId();

        // collect default album names
        $event = new BASE_CLASS_EventCollector(PHOTO_CLASS_EventHandler::EVENT_SUGGEST_DEFAULT_ALBUM, array(
            'userId' => $userId,
            "entityType" => $entityType,
            "entityId" => $entityId
        ));

        OW::getEventManager()->trigger($event);
        $data = $event->getData();

        if ( !empty($data) )
        {
            $albumField->setValue($data[0]);
        }

        $albumField->setResponderUrl($albumSuggestRsp);
        $albumField->setLabel($language->text('photo', 'album'));
        $this->addElement($albumField);

        $submit = new Submit('submit');
        $this->addElement($submit);
    }
}

class PHOTO_Exception extends Exception {}