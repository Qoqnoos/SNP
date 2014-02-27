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
 * @since 1.3.2
 */
class PHOTO_CLASS_EditForm extends Form
{
/**
     * Class constructor
     */
    public function __construct( $photoId )
    {
        parent::__construct('photo-edit-form');
        
        $this->setAjax(true);
        
        $this->setAction(OW::getRouter()->urlFor('PHOTO_CTRL_Photo', 'ajaxUpdatePhoto'));
        
        $language = OW::getLanguage();
        $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);

        $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
        $userId = OW::getUser()->getId();

        // photo id field
        $photoIdField = new HiddenField('id');
        $photoIdField->setRequired(true);
        $this->addElement($photoIdField);

        // photo album Field
        $albumField = new SuggestField('album');
        $responderUrl = OW::getRouter()->urlFor('PHOTO_CTRL_Upload', 'suggestAlbum', array('userId' => $userId));

        $albumField->setResponderUrl($responderUrl);
        if ( $album )
        {
            $albumField->setValue($album->name);
        }
        $albumField->setRequired(true);
        $albumField->setLabel($language->text('photo', 'album'));
        $this->addElement($albumField);

        // description Field
        $descField = new WysiwygTextarea('description', null, false);
        $descField->setId("photo-desc-area");
        $this->addElement($descField->setLabel($language->text('photo', 'description')));
        $tags = array();

        $entityTags = BOL_TagService::getInstance()->findEntityTags($photo->id, 'photo');

        if ( $entityTags )
        {
            $tags = array();
            foreach ( $entityTags as $entityTag )
            {
                $tags[] = $entityTag->label;
            }

            $tagsField = new TagsInputField('tags');
            $tagsField->setValue($tags);
        }
        else
        {
            $tagsField = new TagsInputField('tags');
        }

        $this->addElement($tagsField->setLabel($language->text('photo', 'tags')));

        $submit = new Submit('edit');
        $submit->setValue($language->text('photo', 'btn_edit'));
        $this->addElement($submit);
    }
}