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
$plugin = OW::getPluginManager()->getPlugin('photo');

OW::getRouter()->addRoute(new OW_Route('view_photo_list', 'photo/viewlist/:listType/', 'PHOTO_CTRL_Photo', 'viewList', array('listType' => array('default' => 'latest'))));
OW::getRouter()->addRoute(new OW_Route('photo_list_index', 'photo/', 'PHOTO_CTRL_Photo', 'viewList'));
OW::getRouter()->addRoute(new OW_Route('view_tagged_photo_list_st', 'photo/viewlist/tagged/', 'PHOTO_CTRL_Photo', 'viewTaggedList'));
OW::getRouter()->addRoute(new OW_Route('view_tagged_photo_list', 'photo/viewlist/tagged/:tag', 'PHOTO_CTRL_Photo', 'viewTaggedList'));
OW::getRouter()->addRoute(new OW_Route('view_photo', 'photo/view/:id/', 'PHOTO_CTRL_Photo', 'view'));
OW::getRouter()->addRoute(new OW_Route('photo_admin_config', 'admin/photo', 'PHOTO_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('photo_uninstall', 'admin/photo/uninstall', 'PHOTO_CTRL_Admin', 'uninstall'));
OW::getRouter()->addRoute(new OW_Route('photo_user_albums', 'photo/useralbums/:user/', 'PHOTO_CTRL_Photo', 'userAlbums'));
OW::getRouter()->addRoute(new OW_Route('photo_user_album', 'photo/useralbum/:user/:album', 'PHOTO_CTRL_Photo', 'userAlbum'));
OW::getRouter()->addRoute(new OW_Route('photo.floatbox', 'photo/ajax/get-floatbox/', 'PHOTO_CTRL_Photo', 'getFloatbox'));
OW::getRouter()->addRoute(new OW_Route('photo.ajax_submit', 'photo/ajax/submit/', 'PHOTO_CTRL_Upload', 'ajaxSubmitPhotos'));
OW::getRouter()->addRoute(new OW_Route('photo.ajax_delete', 'photo/ajax/delete/', 'PHOTO_CTRL_Upload', 'ajaxDeletePhoto'));
OW::getRouter()->addRoute(new OW_Route('photo.flash_upload', 'photo/upload/flash', 'PHOTO_CTRL_Upload', 'flashUpload'));
OW::getRouter()->addRoute(new OW_Route('photo_upload', 'photo/upload', 'PHOTO_CTRL_Upload', 'index'));
OW::getRouter()->addRoute(new OW_Route('photo_upload_album', 'photo/upload/:album', 'PHOTO_CTRL_Upload', 'index'));
OW::getRouter()->addRoute(new OW_Route('photo_upload_submit', 'photo/submit', 'PHOTO_CTRL_Upload', 'submit'));
OW::getRouter()->addRoute(new OW_Route('photo_upload_submit_album', 'photo/submit/:album',  'PHOTO_CTRL_Upload', 'submit'));

OW::getThemeManager()->addDecorator('photo_list_item', $plugin->getKey());

PHOTO_CLASS_EventHandler::getInstance()->init();