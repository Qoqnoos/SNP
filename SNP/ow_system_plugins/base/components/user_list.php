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
 * User List
 *
 * @author Kairat Bakitow <kainisoft@gmail.com>
 * @package ow.ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_UserList extends OW_Component
{
    private $userList = array();
    private $fieldList = array();
    private $displayActivity = TRUE;
    
    public function __construct( $userList, $fieldList, $usersCount )
    {
        parent::__construct();
        
        $this->userList = $userList;
        $this->fieldList = array_unique($fieldList);
        
        try
        {
            OW::getThemeManager()->addDecorator('user_big_list_item', 'base');
        }
        catch ( LogicException $e ){}
        
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $usersOnPage = OW::getConfig()->getValue('base', 'users_on_page');
        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($usersCount / $usersOnPage), 5));
    }
    
    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $userList = array();
        $userDtoList = array();
        
        $userService = BOL_UserService::getInstance();
        $questionService = BOL_QuestionService::getInstance();
        
        $userIdList = array_keys($this->userList);
        $userDataList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $this->fieldList);
        
        foreach ( $userService->findUserListByIdList($userIdList) as $userDto )
        {
            $userDtoList[$userDto->id] = $userDto;
        }
        
        foreach ( $this->userList as $userId => $fieldList )
        {
            $fields = array_diff(array_keys($fieldList), $this->fieldList);
            $fieldsData = $questionService->getQuestionData(array($userId), $fields);
            $userList[$userId]['fields'] = array_merge(!empty($userDataList[$userId]) ? $userDataList[$userId] : array() , !empty($fieldsData[$userId]) ? $fieldsData[$userId] : array(), $fieldList);
            $userList[$userId]['dto'] = $userDtoList[$userId];
        }
        
        $this->assign('userList', $userList);
        $this->assign('avatars', BOL_AvatarService::getInstance()->getAvatarsUrlList($userIdList, 2));
        $this->assign('onlineList', !empty($userIdList) ? $userService->findOnlineStatusForUserList($userIdList) : array());
        $this->assign('usernameList', $userService->getUserNamesForList($userIdList));
        $this->assign('displaynameList', $userService->getDisplayNamesForList($userIdList));
        $this->assign('displayActivity', $this->displayActivity);
    }
    
    public function setDisplayActivity( $value )
    {
        $this->displayActivity = (booL)$value;
        
        return $this;
    }
}
