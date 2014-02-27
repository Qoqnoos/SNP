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
class BASE_CTRL_Attachment extends OW_ActionController
{

    public function addPhoto( $params )
    {
        try
        {
            $info = BOL_AttachmentService::getInstance()->processPhotoAttachment($_FILES['attachment']);
        }
        catch ( InvalidArgumentException $e )
        {
            exit("<script>parent.window.OW.error(" . json_encode($e->getMessage()) . "); parent.window.owattachments['" . $params['uid'] . "'].init();</script>");
        }

        $oembedCmp = new BASE_CMP_OembedAttachment(array('type' => 'photo', 'url' => $info['url'], 'href' => $info['url']), true);

        $returnArray = array(
            'cmp' => $oembedCmp->render(),
            'url' => $info['url'],
            'type' => 'photo',
            'uid' => $params['uid'],
            'genId' => $info['genId']
        );

        exit("<script>parent.window.owattachments['" . $params['uid'] . "'].hideLoader().addItem(" . json_encode($returnArray) . ");</script>");
    }

    public function addVideo( $params )
    {
        $cmp = new BASE_CMP_OembedAttachment(array('type' => 'video', 'html' => $_POST['code']), true);
        exit(json_encode(array('cmp' => $cmp->render(), 'uid' => $params['uid'], 'genId' => uniqid('attchvi' . md5($params['uid'])), 'type' => 'video', 'code' => $_POST['code'])));
    }

    public function delete( $params )
    {
        exit;
    }

    public function addLink()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $url = $_POST['url'];

        $urlInfo = parse_url($url);
        if ( empty($urlInfo['scheme']) )
        {
            $url = 'http://' . $url;
        }

        $url = str_replace("'", '%27', $url);

        $oembed = UTIL_HttpResource::getOEmbed($url);
        $oembedCmp = new BASE_CMP_AjaxOembedAttachment($oembed);

        $attacmentUniqId = $oembedCmp->initJs();

        unset($oembed['allImages']);

        $response = array(
            'content' => $this->getMarkup($oembedCmp->render()),
            'type' => 'link',
            'result' => $oembed,
            'attachment' => $attacmentUniqId
        );

        echo json_encode($response);

        exit;
    }

    private function getMarkup( $html )
    {
        /* @var $document OW_AjaxDocument */
        $document = OW::getDocument();

        $markup = array();
        $markup['html'] = $html;

        $onloadScript = $document->getOnloadScript();
        $markup['js'] = empty($onloadScript) ? null : $onloadScript;

        $styleDeclarations = $document->getStyleDeclarations();
        $markup['css'] = empty($styleDeclarations) ? null : $styleDeclarations;

        return $markup;
    }
}
