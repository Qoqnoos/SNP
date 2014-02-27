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
 * @package ow.ow_system_plugins.base.comments
 * @since 1.0
 */
class BASE_CMP_Comments extends OW_Component
{
    /**
     * @var BASE_CommentsParams
     */
    protected $params;
    protected $batchData;
    protected $staticData;
    protected $id;
    protected $cmpContextId;
    protected $formName;
    protected $isAuthorized;

    /**
     * Constructor.
     *
     * @param BASE_CommentsParams $params
     */
    public function __construct( BASE_CommentsParams $params )
    {
        parent::__construct();
        $this->params = $params;
        $this->batchData = $params->getBatchData();
        $this->staticData = empty($this->batchData['_static']) ? array() : $this->batchData['_static'];
        $this->batchData = isset($this->batchData[$params->getEntityType()][$params->getEntityId()]) ? $this->batchData[$params->getEntityType()][$params->getEntityId()] : array();

        srand(time());
        $this->id = $params->getEntityType() . $params->getEntityId() . rand(1, 10000);
        $this->cmpContextId = "comments-$this->id";
        $this->formName = "comment-add-$this->id";
        $this->assign('cmpContext', $this->cmpContextId);
        $this->assign('wrapInBox', $params->getWrapInBox());
        $this->isAuthorized = OW::getUser()->isAuthorized($params->getPluginKey(), 'add_comment') && $params->getAddComment();

        if ( !$this->isAuthorized )
        {
            $errorMessage = $params->getErrorMessage();

            if ( empty($errorMessage) )
            {
                $errorMessage = OW::getLanguage()->text('base', ( OW::getUser()->isAuthenticated() ) ? 'comments_add_auth_message' : 'comments_add_login_message');
            }

            $this->assign('authErrorMessage', $errorMessage);
        }
        else
        {
            $eventParams = array('pluginKey' => $params->getPluginKey(), 'action' => 'add_comment');

            if ( isset($this->staticData['credits'][$params->getPluginKey()]) )
            {
                $credits = $this->staticData['credits'][$params->getPluginKey()];
            }
            else
            {
                $credits = OW::getEventManager()->call('usercredits.check_balance', $eventParams);
            }

            if ( $credits === false )
            {
                $this->isAuthorized = false;
                $this->assign('authErrorMessage', OW::getEventManager()->call('usercredits.error_message', $eventParams));
            }
            else
            {
                //
            }
        }

        $this->initForm();
    }

    public function initForm()
    {
        if ( $this->isAuthorized )
        {
            $countOnPage = isset($this->batchData['countOnPage']) ? $this->batchData['countOnPage'] : $this->params->getCommentCountOnPage();

            $formCmpParams = array(
                $this->params->getEntityType(), $this->params->getEntityId(), $this->params->getDisplayType(), $this->params->getPluginKey(), $this->params->getOwnerId(),
                in_array($this->params->getDisplayType(), array(BASE_CommentsParams::DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST, BASE_CommentsParams::DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST_AND_MINI_IPC)) ? ($countOnPage + 1) : $countOnPage,
                $this->id, $this->cmpContextId, $this->formName
            );

            OW::getDocument()->addOnloadScript(
                "window.owCommentCmps['$this->id'] = new OwComments('$this->cmpContextId', '$this->formName', '$this->id', " . json_encode($formCmpParams) . ");
                    $('.comments_fake_autoclick', $('#$this->cmpContextId')).one('focus', function(){window.owCommentCmps['$this->id'].loadForm();});"
            );
            $this->assign('formCmp', true);

            if ( !empty($this->staticData['currentUserInfo']) )
            {
                $userInfoToAssign = $this->staticData['currentUserInfo'];
            }
            else
            {
                $currentUserInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));
                $userInfoToAssign = $currentUserInfo[OW::getUser()->getId()];
            }

            $this->assign('currentUserInfo', $userInfoToAssign);
        }

        $this->assign('displayType', $this->params->getDisplayType());

        // add comment list cmp
        $this->addComponent('commentList', new BASE_CMP_CommentsList($this->params, $this->id));
    }
}

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow.ow_system_plugins.base.comments
 * @since 1.0
 */
final class BASE_CommentsParams
{
    const DISPLAY_TYPE_BOTTOM_FORM_WITH_FULL_LIST = 1;
    const DISPLAY_TYPE_TOP_FORM_WITH_PAGING = 2;
    const DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST = 3;
    const DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST_AND_MINI_IPC = 4;

    private $pluginKey;
    private $entityType;
    private $entityId;
    private $ownerId;
    private $displayType;
    private $commentCountOnPage;
    private $addComment;
    private $wrapInBox;
    private $batchData;
    private $errorMessage;

    /**
     * Constructor.
     *
     * @param string $pluginKey
     * @param string $entityType
     */
    public function __construct( $pluginKey, $entityType )
    {
        $this->pluginKey = trim($pluginKey);
        $this->entityType = trim($entityType);
        $this->entityId = 1;
        $this->displayType = self::DISPLAY_TYPE_TOP_FORM_WITH_PAGING;
        $this->addComment = true;
        $this->wrapInBox = true;
    }

    /**
     * @return string
     */
    public function getPluginKey()
    {
        return $this->pluginKey;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     *
     * @param integer $entityId
     * @return BASE_CommentsParams
     */
    public function setEntityId( $entityId )
    {
        $this->entityId = (int) $entityId;
        return $this;
    }

    /**
     * @return integer
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param integer $ownerId
     * @return BASE_CommentsParams
     */
    public function setOwnerId( $ownerId )
    {
        $this->ownerId = (int) $ownerId;
        return $this;
    }

    /**
     * @return integer
     */
    public function getDisplayType()
    {
        return $this->displayType;
    }

    /**
     * @param integer $displayType
     * @return BASE_CommentsParams
     */
    public function setDisplayType( $displayType )
    {
        $this->displayType = (int) $displayType;
        return $this;
    }

    /**
     * @return integer
     */
    public function getCommentCountOnPage()
    {
        return $this->commentCountOnPage;
    }

    /**
     * @param integer $commentCountOnPage
     * @return BASE_CommentsParams
     */
    public function setCommentCountOnPage( $commentCountOnPage )
    {
        $this->commentCountOnPage = (int) $commentCountOnPage;
        return $this;
    }

    public function getAddComment()
    {
        return $this->addComment;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setErrorMessage( $errorMessage )
    {
        $this->errorMessage = $errorMessage;
    }

    public function setAddComment( $addComment )
    {
        $this->addComment = (bool) $addComment;

        return $this;
    }

    public function getWrapInBox()
    {
        return $this->wrapInBox;
    }

    public function setWrapInBox( $wrapInBox )
    {
        $this->wrapInBox = (bool) $wrapInBox;
    }

    public function getBatchData()
    {
        return $this->batchData;
    }

    public function setBatchData( array $data )
    {
        $this->batchData = $data;
    }
}