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
 * @package ow_core
 * @since 1.0
 */
class BASE_CLASS_Attachment extends OW_Component
{
    private $uid;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $uid )
    {
        parent::__construct();
        $language = OW::getLanguage();
        $this->uid = $uid;
        $this->assign('uid', $uid);
        $this->assign('wsImageUrl', OW::getPluginManager()->getPlugin('base')->getStaticCssUrl().'wysiwyg.png');
        
        $urlParams = array('uid' => $uid);
        
        $params = array(
            'uid' => $uid,
            'addPhotoUrl' => OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'addPhoto', $urlParams),
            'addVideo' => OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'addVideo', $urlParams),
            'deleteUrl' => OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'delete', $urlParams),
            'langs' => array( 
                'emptyVideoCode' => $language->text('base', 'ws_video_empty_field'),
                'addVideoLabel' => $language->text('base', 'attch_add_video_button_label'),
                'attchLabel' => $language->text('base', 'attch_attachment_label')
             )
        );

        OW::getDocument()->addOnloadScript("
            window.owattachments['".$uid."'] =  new OwAttachment(".  json_encode($params).");window.owattachments['".$uid."'].init();");
    }
}

