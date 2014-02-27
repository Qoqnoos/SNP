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
class BASE_CLASS_EventHandler
{

    public function genericInit()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind('base.add_global_lang_keys', array($this, 'onAddGlobalLangs'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onDeleteUserContent'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'onJoinMandatoryUserApprove'));
        $eventManager->bind(OW_EventManager::ON_USER_EDIT, array($this, 'onUserEditFeed'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'onJoinFeed'));
        $eventManager->bind('feed.after_comment_add', array($this, 'onUserJoinCommentFeed'));
        $eventManager->bind('feed.after_like_added', array($this, 'onLikeUserJoin'));
        $eventManager->bind('feed.after_like_added', array($this, 'onUserAvatarLikeFeed'));
        $eventManager->bind('feed.after_comment_add', array($this, 'onUserAvatarCommentFeed'));
        $eventManager->bind(OW_EventManager::ON_USER_REGISTER, array($this, 'onUserRegisterWelcomeLetter'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregisterDeleteDisaproved'));
        $eventManager->bind('notifications.collect_actions', array($this, 'onNotifyActions'));
        $eventManager->bind('base_add_comment', array($this, 'onAddComment'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onUserUnregisterRemovePreference'));
        $eventManager->bind('base.update_entity_items_status', array($this, 'onUpdateEntityItemsStatus'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onDeleteMediaPanelFiles'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_SUSPEND, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_UNSUSPEND, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_APPROVE, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_DISAPPROVE, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_MARK_FEATURED, array($this, 'clearUserListQueryCache'));
        $eventManager->bind(OW_EventManager::ON_USER_UNMARK_FEATURED, array($this, 'clearUserListQueryCache'));
        $eventManager->bind('base.questions_field_get_label', array($this, 'getQuestionLabel'));
        $eventManager->bind('base.before_decorator', array($this, 'onBeforeDecoratorRender'));
        $eventManager->bind('plugin.privacy.get_action_list', array($this, 'onPrivacyAddAction'));

        $eventManager->bind('socialsharing.get_entity_info', array($this, 'sosialSharingGetUserInfo'));
    }

    public function init()
    {
        $this->genericInit();
        $eventManager = OW::getEventManager();
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddDeleteActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddSuspendActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddAuthActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserApproveActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserFeatureActionTool'));
        $eventManager->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'onActionToolbarAddUserBlockActionTool'));
        $eventManager->bind(OW_EventManager::ON_JOIN_FORM_RENDER, array($this, 'onInviteMembersProcessJoinForm'));
        $eventManager->bind('base.dashboard_menu_items', array($this, 'onDashboardMenuItem'));
        $eventManager->bind('base.preference_menu_items', array($this, 'onPreferenceMenuItem'));
        $eventManager->bind('base.members_only_exceptions', array($this, 'onAddMembersOnlyException'));
        $eventManager->bind('base.password_protected_exceptions', array($this, 'onAddPasswordProtectedExceptions'));
        $eventManager->bind('base.maintenance_mode_exceptions', array($this, 'onAddMaintenanceModeExceptions'));
        $eventManager->bind('base.on_avatar_toolbar_collect', array($this, 'onAvatarToolbarCollect'));
        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'addJsDeclarations'));
        $eventManager->bind('admin.add_admin_notification', array($this, 'addAdminNotification'));
        $eventManager->bind('ads.enabled_plugins', array($this, 'onAddAdsEnabled'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'onPluginUninstallDeleteComments'));
        $eventManager->bind('admin.add_auth_labels', array($this, 'onAddAuthLabels'));
        $eventManager->bind(BOL_PreferenceService::PREFERENCE_ADD_FORM_ELEMENT_EVENT, array($this, 'onPreferenceAddFormElement'));
        $eventManager->bind(BOL_PreferenceService::PREFERENCE_SECTION_LABEL_EVENT, array($this, 'onAddPreferenceSectionLabels'));
        $eventManager->bind('feed.collect_configurable_activity', array($this, 'onFeedCollectConfigurableActivity'));
        $eventManager->bind('base.attachment_delete_image', array($this, 'onDeleteAttachmentImage'));
        $eventManager->bind('base.attachment_save_image', array($this, 'onSaveAttachmentImage'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'onPluginsUninstallDeleteGateway'));
        $eventManager->bind(OW_EventManager::ON_USER_UNREGISTER, array($this, 'onDeleteUserAttachments'));
        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalizeAddScrollJs'));
        $eventManager->bind('join.get_captcha_field', array($this, 'getCaptcha'));
        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalizeCheckIfSiteFullyInstalled'));
        $eventManager->bind(OW_EventManager::ON_PLUGINS_INIT, array($this, 'onPluginsInitCheckUserStatus'));

        if ( defined('OW_ADS_XP_TOP') )
        {
            $eventManager->bind('base.add_page_content', array($this, 'addPageBanner'));
        }
        
        $eventManager->bind('class.get_instance.BASE_CMP_Users', array($this, 'getInstanceBaseCmpUsers'));
    }

    public function onBeforeDecoratorRender( BASE_CLASS_PropertyEvent $e )
    {
        // to add some logic for decorators
    }

    public function onPluginsInitCheckUserStatus()
    {
        if ( OW::getUser()->isAuthenticated() )
        {
            $user = BOL_UserService::getInstance()->findUserById(OW::getUser()->getId());

            if ( OW::getConfig()->getValue('base', 'mandatory_user_approve') && OW::getUser()->isAuthenticated() && !BOL_UserService::getInstance()->isApproved() )
            {
                OW::getRequestHandler()->setCatchAllRequestsAttributes('base.wait_for_approval', array('controller' => 'BASE_CTRL_WaitForApproval', 'action' => 'index'));
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.wait_for_approval', 'BASE_CTRL_User', 'signOut');
            }

            if ( $user !== null )
            {
                if ( BOL_UserService::getInstance()->isSuspended($user->getId()) && !OW::getUser()->isAdmin() )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.suspended_user', array('controller' => 'BASE_CTRL_SuspendedUser', 'action' => 'index'));
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_User', 'signOut');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_Avatar');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.suspended_user', 'BASE_CTRL_Edit');
                }

                if ( (int) $user->emailVerify === 0 && OW::getConfig()->getValue('base', 'confirm_email') )
                {
                    OW::getRequestHandler()->setCatchAllRequestsAttributes('base.email_verify', array(OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_EmailVerify', OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'index'));

                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.email_verify', 'BASE_CTRL_User', 'signOut');
                    OW::getRequestHandler()->addCatchAllRequestsExclude('base.email_verify', 'BASE_CTRL_EmailVerify');
                }
            }
            else
            {
                OW::getUser()->logout();
            }
        }
    }

    public function addPageBanner( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( $params['key'] == 'base.content_top' )
        {
            $event->add(OW_ADS_XP_TOP);
        }
        elseif ( $params['key'] == 'base.content_bottom' )
        {
            $event->add(OW_ADS_XP_BOT);
        }
    }

    public function getQuestionLabel( OW_Event $event )
    {
        $params = $event->getParams();

        $presentation = !empty($params['presentation']) ? $params['presentation'] : null;
        $fieldName = !empty($params['fieldName']) ? $params['fieldName'] : null;
        $configs = !empty($params['configs']) ? $params['configs'] : null;
        $type = !empty($params['type']) ? $params['type'] : null;

        if ( $type == 'view' && $fieldName == 'birthdate' && $presentation == BOL_QuestionService::QUESTION_PRESENTATION_AGE )
        {
            $event->setData(OW::getLanguage()->text('base', 'questions_question_birthday_label_presentation_age'));
        }
    }

    public function onFinalizeCheckIfSiteFullyInstalled( OW_Event $event )
    {
        if ( !OW::getConfig()->getValue("base", "install_complete") )
        {
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute("base_page_install_completed") . "?redirect=1");
        }
    }

    public function getCaptcha( OW_Event $e )
    {
        $e->setData(new CaptchaField('captchaField'));
    }

    public function onFinalizeAddScrollJs( $e )
    {
        $plugin = OW::getPluginManager()->getPlugin('base');

        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'jquery.mousewheel.js');
        OW::getDocument()->addScript($plugin->getStaticJsUrl() . 'jquery.jscrollpane.js');
    }

    public function clearUserListQueryCache( OW_Event $event )
    {
        $params = $event->getParams();
        $userId = (int) $params['userId'];

        OW::getCacheManager()->clean(array(BOL_UserDao::CACHE_TAG_ALL_USER_LIST));
    }

    public function onDeleteUserAttachments( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            if ( isset($params['deleteContent']) && (bool) $params['deleteContent'] )
            {
                BOL_AttachmentService::getInstance()->deleteUserAttachments($userId);
            }
        }
    }

    public function onDeleteMediaPanelFiles( OW_Event $e )
    {
        $params = $e->getParams();
        $userId = (int) $params['userId'];

        BOL_MediaPanelService::getInstance()->deleteImagesByUserId($userId);
    }

    public function onPluginsUninstallDeleteGateway( OW_Event $e )
    {
        $params = $e->getParams();
        $pluginKey = $params['pluginKey'];

        BOL_BillingService::getInstance()->deleteGatewayProductsByPluginKey($pluginKey);
    }

    public function onSaveAttachmentImage( OW_Event $event )
    {
        $params = $event->getParams();
        if ( !empty($params['genId']) )
        {
            return BOL_AttachmentService::getInstance()->saveTempImage($params['genId']);
        }

        return null;
    }

    public function onDeleteAttachmentImage( OW_Event $event )
    {
        $params = $event->getParams();
        
        if ( !empty($params["id"]) )
        {
            BOL_AttachmentService::getInstance()->deleteAttachmentById($params["id"]);
        }
        else if ( !empty($params['url']) && strstr($params['url'], OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . 'attachments')) )
        {
            BOL_AttachmentService::getInstance()->deleteAttachmentByUrl($params['url']);
        }
    }

    public function onFeedCollectConfigurableActivity( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(array(
            'label' => $language->text('admin', 'feed_content_registration'),
            'activity' => 'create:user_join'
        ));

        $event->add(array(
            'label' => $language->text('admin', 'feed_content_edit'),
            'activity' => 'create:user_edit'
        ));

        $event->add(array(
            'label' => $language->text('admin', 'feed_content_avatar_change'),
            'activity' => 'create:avatar-change'
        ));

        $event->add(array(
            'label' => $language->text('admin', 'feed_content_user_comment'),
            'activity' => 'create:user-comment'
        ));
    }

    public function onUpdateEntityItemsStatus( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || empty($params['entityIds']) || !isset($params['status']) || !is_array($params['entityIds']) )
        {
            return;
        }

        $status = empty($params['status']) ? 0 : 1;

        foreach ( $params['entityIds'] as $entityId )
        {
            BOL_CommentService::getInstance()->setEntityStatus($params['entityType'], $entityId, $status);
            BOL_TagService::getInstance()->updateEntityItemStatus($params['entityType'], $entityId, $status);
            BOL_RateService::getInstance()->updateEntityStatus($params['entityType'], $entityId, $status);
            BOL_VoteService::getInstance()->updateEntityItemStatus($params['entityType'], $entityId, $status);
        }
    }

    public function onUserUnregisterRemovePreference( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        BOL_PreferenceService::getInstance()->deletePreferenceDataByUserId($userId);
    }

    public function onPrivacyAddAction( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => 'base_view_profile',
            'pluginKey' => 'base',
            'label' => $language->text('base', 'privacy_action_view_profile'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);

        $action = array(
            'key' => 'base_view_my_presence_on_site',
            'pluginKey' => 'base',
            'label' => $language->text('base', 'privacy_action_view_my_presence_on_site'),
            'description' => '',
            'defaultValue' => 'everybody'
        );

        $event->add($action);
    }

    public function onAddPreferenceSectionLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $sectionLabels = array(
            'general' => array(
                'label' => $language->text('base', 'preference_section_general'),
                'iconClass' => 'ow_ic_script'
            )
        );

        $event->add($sectionLabels);
    }

    public function onPreferenceAddFormElement( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();

        $params = $event->getParams();
        $values = $params['values'];

        $fromElementList = array();

        $fromElement = new CheckboxField('mass_mailing_subscribe');
        $fromElement->setLabel($language->text('base', 'preference_mass_mailing_subscribe_label'));
        $fromElement->setDescription($language->text('base', 'preference_mass_mailing_subscribe_description'));

        if ( isset($values['mass_mailing_subscribe']) )
        {
            $fromElement->setValue($values['mass_mailing_subscribe']);
        }

        $fromElementList[] = $fromElement;

        $event->add($fromElementList);
    }

    public function onAddAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'base' => array(
                    'label' => $language->text('base', 'auth_group_label'),
                    'actions' => array(
                        'add_comment' => $language->text('base', 'auth_action_add_comment'),
                        'delete_comment_by_content_owner' => $language->text('base', 'delete_comment_by_content_owner'),
                        'search_users' => $language->text('base', 'search_users'),
                        'view_profile' => $language->text('base', 'auth_view_profile')
                    )
                )
            )
        );
    }

    public function onPluginUninstallDeleteComments( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !empty($params['pluginKey']) )
        {
            BOL_CommentService::getInstance()->deletePluginComments($params['pluginKey']);
        }
    }

    public function onAddAdsEnabled( BASE_EventCollector $event )
    {
        $event->add('base');
    }

    public function addAdminNotification( BASE_CLASS_EventCollector $coll )
    {
        if ( OW::getConfig()->getValue('base', 'cron_is_configured') || defined('OW_PLUGIN_XP') )
        {
            return;
        }

        $coll->add(OW::getLanguage()->text('admin', 'cron_configuration_required_notice', array(
                'helpUrl' => 'http://docs.oxwall.org/install:cron'
            )));
    }

    public function addJsDeclarations( OW_Event $e )
    {
        //Langs
        OW::getLanguage()->addKeyForJs('base', 'ajax_floatbox_users_title');
        OW::getLanguage()->addKeyForJs('base', 'flag_as');
        OW::getLanguage()->addKeyForJs('base', 'delete_user_confirmation_label');

        $scriptGen = UTIL_JsGenerator::newInstance()->setVariable(array('OW', 'ajaxComponentLoaderRsp'), OW::getRouter()->urlFor('BASE_CTRL_AjaxLoader', 'component'));
        $scriptGen->setVariable(array('OW', 'ajaxAttachmentLinkRsp'), OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'addLink'));

        //Ping
        $scriptGen->addScript('OW.getPing().setRspUrl({$url});', array(
            'url' => OW::getRouter()->urlFor('BASE_CTRL_Ping', 'index')
        ));

        //UsersApi
        $scriptGen->newObject(array('OW', 'Users'), 'OW_UsersApi', array(array(
                "rsp" => OW::getRouter()->urlFor('BASE_CTRL_AjaxUsersApi', 'rsp')
            )));

        OW::getDocument()->addScriptDeclaration($scriptGen->generateJs());

        //Light Cron
        $cronReady = OW::getConfig()->configExists('base', 'cron_is_configured') && OW::getConfig()->getValue('base', 'cron_is_configured');

        if ( !$cronReady && !defined('OW_PLUGIN_XP') )
        {
            OW::getDocument()->addOnloadScript(UTIL_JsGenerator::composeJsString(
                    '$.get({$cron});'
                    , array(
                    'cron' => OW::getRequest()->buildUrlQueryString(OW_URL_HOME . 'ow_cron/run.php', array(
                        'ow-light-cron' => 1
                    ))
                )));
        }
    }

    public function onAvatarToolbarCollect( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'title' => OW::getLanguage()->text('base', 'console_item_label_dashboard'),
            'iconClass' => 'ow_ic_house',
            'url' => OW::getRouter()->urlForRoute('base_member_dashboard'),
            'order' => 1
        ));

        $e->add(array(
            'title' => OW::getLanguage()->text('base', 'console_item_label_profile'),
            'iconClass' => 'ow_ic_user',
            'url' => OW::getRouter()->urlForRoute('base_member_profile'),
            'order' => 3
        ));
    }

    public function onAddComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || $params['entityType'] !== 'base_profile_wall' )
        {
            return;
        }

        $entityId = $params['entityId'];
        $userId = $params['userId'];
        $commentId = $params['commentId'];

        $userService = BOL_UserService::getInstance();

        $user = $userService->findUserById($entityId);

        if ( $user->getId() == $userId )
        {
            return;
        }

        $comment = BOL_CommentService::getInstance()->findComment($commentId);
        $url = OW::getRouter()->urlForRoute('base_user_profile', array('username' => BOL_UserService::getInstance()->getUserName($entityId)));

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
        $avatar = $avatars[$userId];

        $event = new OW_Event('notifications.add', array(
                'pluginKey' => 'base',
                'entityType' => 'base_profile_wall',
                'entityId' => $commentId,
                'action' => 'base_add_user_comment',
                'userId' => $user->getId(),
                ), array(
                'avatar' => $avatar,
                'string' => array(
                    'key' => 'base+profile_comment_notification',
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'profileUrl' => $userService->getUserUrl($user->getId())
                    )
                ),
                'content' => $comment->getMessage(),
                'url' => $userService->getUserUrl($user->getId())
            ));

        OW::getEventManager()->trigger($event);
    }

    public function onNotifyActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'base',
            'sectionLabel' => OW::getLanguage()->text('base', 'notification_section_label'),
            'action' => 'base_add_user_comment',
            'description' => OW::getLanguage()->text('base', 'email_notifications_setting_user_comment'),
            'sectionIcon' => 'ow_ic_file',
            'selected' => true
        ));
    }

    public function onAddMaintenanceModeExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordCodeExpired'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_CTRL_ApiServer', 'action' => 'request'));
    }

    public function onAddPasswordProtectedExceptions( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'standardSignIn'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'ajaxSignIn'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordCodeExpired'));
        $event->add(array('controller' => 'BASE_CTRL_EmailVerify', 'action' => 'verify'));
        $event->add(array('controller' => 'BASE_CTRL_ApiServer', 'action' => 'request'));
        $event->add(array('controller' => 'BASE_CTRL_Unsubscribe', 'action' => 'index'));
    }

    public function onAddMembersOnlyException( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('controller' => 'BASE_CTRL_Join', 'action' => 'index'));
        $event->add(array('controller' => 'BASE_CTRL_Join', 'action' => 'joinFormSubmit'));
        $event->add(array('controller' => 'BASE_CTRL_Join', 'action' => 'ajaxResponder'));
        $event->add(array('controller' => 'BASE_CTRL_Captcha', 'action' => 'index'));
        $event->add(array('controller' => 'BASE_CTRL_Captcha', 'action' => 'ajaxResponder'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'forgotPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPasswordRequest'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'resetPassword'));
        $event->add(array('controller' => 'BASE_CTRL_User', 'action' => 'ajaxSignIn'));
        $event->add(array('controller' => 'BASE_CTRL_ApiServer', 'action' => 'request'));
        $event->add(array('controller' => 'BASE_CTRL_Unsubscribe', 'action' => 'index'));
    }

    public function onPreferenceMenuItem( BASE_CLASS_EventCollector $event )
    {
        $router = OW_Router::getInstance();
        $language = OW::getLanguage();

        $menuItem = new BASE_MenuItem();

        $menuItem->setKey('preference');
        $menuItem->setLabel($language->text('base', 'preference_menu_item'));
        $menuItem->setIconClass('ow_ic_gear_wheel');
        $menuItem->setUrl($router->urlForRoute('base_preference_index'));
        $menuItem->setOrder(1);

        $event->add($menuItem);
    }

    public function onDashboardMenuItem( BASE_CLASS_EventCollector $event )
    {
        $router = OW_Router::getInstance();
        $language = OW::getLanguage();

        $menuItems = array();

        $menuItem = new BASE_MenuItem();

        $menuItem->setKey('widget_panel');
        $menuItem->setLabel($language->text('base', 'widgets_panel_dashboard_label'));
        $menuItem->setIconClass('ow_ic_house');
        $menuItem->setUrl($router->urlForRoute('base_member_dashboard'));
        $menuItem->setOrder(1);

        $event->add($menuItem);


        $menuItem = new BASE_MenuItem();

        $menuItem->setKey('profile_edit');
        $menuItem->setLabel($language->text('base', 'edit_index'));
        $menuItem->setIconClass('ow_ic_user');
        $menuItem->setUrl($router->urlForRoute('base_edit'));
        $menuItem->setOrder(2);

        $event->add($menuItem);

        $menuItem = new BASE_MenuItem();

        $menuItem->setKey('preference');
        $menuItem->setLabel($language->text('base', 'preference_index'));
        $menuItem->setIconClass('ow_ic_gear_wheel');
        $menuItem->setUrl($router->urlForRoute('base_preference_index'));
        $menuItem->setOrder(4);

        $event->add($menuItem);
    }

    public function onUserUnregisterDeleteDisaproved( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];
        $userService = BOL_UserService::getInstance();

        if ( !$userService->isApproved($userId) )
        {
            return;
        }

        $userService->deleteDisaproveByUserId($userId);
    }

    public function onInviteMembersProcessJoinForm( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['code'] !== null )
        {
            $info = BOL_UserService::getInstance()->findInvitationInfo($params['code']);

            if ( $info !== null )
            {
                throw new JoinRenderException();
            }
        }
    }

    public function onActionToolbarAddUserBlockActionTool( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( $params['userId'] == OW::getUser()->getId() )
        {
            return;
        }

        $authorizationService = BOL_AuthorizationService::getInstance();

        if ( $authorizationService->isActionAuthorizedForUser($params['userId'], 'base') || $authorizationService->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $resultArray = array();

        $uniqId = uniqid("block-");
        $isBlocked = BOL_UserService::getInstance()->isBlocked($userId, OW::getUser()->getId());

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL] = $isBlocked ? OW::getLanguage()->text('base', 'user_unblock_btn_lbl') : OW::getLanguage()->text('base', 'user_block_btn_lbl');

        $toggleText = !$isBlocked ? OW::getLanguage()->text('base', 'user_unblock_btn_lbl') : OW::getLanguage()->text('base', 'user_block_btn_lbl');

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS] = $isBlocked ? 'ow_mild_green' : 'ow_mild_red';

        $toggleClass = !$isBlocked ? 'ow_mild_green' : 'ow_mild_red';

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES] = array();
        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES]["data-command"] = $isBlocked ? "unblock" : "block";

        $toggleCommand = !$isBlocked ? "unblock" : "block";

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF] = 'javascript://';
        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID] = $uniqId;

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click",
            'var self = $(this); toggle = function() {
            OW.Utils.toggleText(self, e.data.toggleText);
            OW.Utils.toggleAttr(self, "class", e.data.toggleClass);
            OW.Utils.toggleAttr(self, "data-command", e.data.toggleCommand);
        };
        if ( self.attr("data-command") == "block" )
            OW.Users.blockUserWithConfirmation(e.data.userId, toggle);
        else {
            OW.Users.unBlockUser(e.data.userId);
            toggle();
        }'
            , array("e"), array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "toggleClass" => $toggleClass
        ));

        OW::getDocument()->addOnloadScript($js);

        $resultArray[BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY] = "base.block_user";

        $event->add($resultArray);
    }

    public function onActionToolbarAddUserFeatureActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 1
        );

        $userId = (int) $params['userId'];

        $uniqId = uniqid("feature-");
        $isFeatured = BOL_UserService::getInstance()->isUserFeatured($userId);

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL] = $isFeatured ? OW::getLanguage()->text('base', 'user_action_unmark_as_featured') : OW::getLanguage()->text('base', 'user_action_mark_as_featured');

        $toggleText = !$isFeatured ? OW::getLanguage()->text('base', 'user_action_unmark_as_featured') : OW::getLanguage()->text('base', 'user_action_mark_as_featured');

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES] = array();
        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES]["data-command"] = $isFeatured ? "unfeature" : "feature";

        $toggleCommand = !$isFeatured ? "unfeature" : "feature";

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF] = 'javascript://';
        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID] = $uniqId;

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click",
            'OW.Users[$(this).attr("data-command") == "feature" ? "featureUser" : "unFeatureUser"](e.data.userId);
         OW.Utils.toggleText(this, e.data.toggleText);
         OW.Utils.toggleAttr(this, "data-command", e.data.toggleCommand);'
            , array("e"), array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand
        ));

        OW::getDocument()->addOnloadScript($js);

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY] = "base.make_featured";
        $event->add($action);
    }

    public function onActionToolbarAddUserApproveActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( BOL_UserService::getInstance()->isApproved($userId) )
        {
            return;
        }

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => OW::getRouter()->urlFor('BASE_CTRL_User', 'approve', array('userId' => $userId)),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_user_approve_label'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_green',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.approve_user"
        );

        $event->add($action);
    }

    public function onActionToolbarAddAuthActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];
        $uniqId = uniqid('change-role-');

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $uniqId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'authorization_give_user_role'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_green',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.change_role"
        );

        $event->add($action);

        $js = UTIL_JsGenerator::newInstance()->jQueryEvent('#' . $uniqId, 'click',
                'window.baseChangeUserRoleFB = OW.ajaxFloatBox("BASE_CMP_GiveUserRole", [e.data.userId], { width:556, title: e.data.title });',
                array('e'), array(
                'userId' => $userId,
                'title' => OW::getLanguage()->text('base', 'authorization_user_roles')
            ));

        OW::getDocument()->addOnloadScript($js);
    }

    public function onActionToolbarAddSuspendActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();
        $userId = (int) $params['userId'];

        $action = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 2
        );

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF] = 'javascript://';

        $uniqId = uniqid('pat-suspend-');
        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID] = $uniqId;

        $toogleText = null;
        $toggleCommand = null;
        $toggleClass = null;

        $suspended = $userService->isSuspended($userId);

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES] = array();
        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL] = $suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

        $toggleText = !$suspended ? OW::getLanguage()->text('base', 'user_unsuspend_btn_lbl') : OW::getLanguage()->text('base', 'user_suspend_btn_lbl');

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ATTRIBUTES]["data-command"] = $suspended ? "unsuspend" : "suspend";

        $toggleCommand = !$suspended ? "unsuspend" : "suspend";

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS] = $suspended ? "ow_mild_green" : "ow_mild_red";

        $toggleClass = !$suspended ? "ow_mild_green" : "ow_mild_red";

        $rsp = OW::getRouter()->urlFor('BASE_CTRL_SuspendedUser', 'ajaxRsp');
        $rsp = OW::getRequest()->buildUrlQueryString($rsp, array(
                "userId" => $userId
            ));

        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $uniqId, "click",
            'OW.Users[$(this).attr("data-command") == "suspend" ? "suspendUser" : "unSuspendUser"](e.data.userId);
         OW.Utils.toggleText(this, e.data.toggleText);
         OW.Utils.toggleAttr(this, "class", e.data.toggleClass);
         OW.Utils.toggleAttr(this, "data-command", e.data.toggleCommand);'
            , array("e"), array(
            "userId" => $userId,
            "toggleText" => $toggleText,
            "toggleCommand" => $toggleCommand,
            "toggleClass" => $toggleClass
        ));

        OW::getDocument()->addOnloadScript($js);

        $action[BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY] = "base.suspend_user";

        $event->add($action);
    }

    public function onActionToolbarAddDeleteActionTool( BASE_CLASS_EventCollector $event )
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            return;
        }

        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        if ( BOL_AuthorizationService::getInstance()->isSuperModerator($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $callbackUrl = OW::getRouter()->urlFor('BASE_CTRL_User', 'userDeleted');

        $linkId = 'ud' . rand(10, 1000000);
        $script = UTIL_JsGenerator::newInstance()->jQueryEvent('#' . $linkId, 'click',
                'OW.Users.deleteUser(e.data.userId, e.data.callbackUrl, false);'
                , array('e'), array('userId' => $userId, 'callbackUrl' => $callbackUrl));

        OW::getDocument()->addOnloadScript($script);

        $resultArray = array(
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_user_delete_label'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_CLASS => 'ow_mild_red',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_KEY => 'base.moderation',
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_GROUP_LABEL => OW::getLanguage()->text('base', 'profile_toolbar_group_moderation'),
            BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 3,
            BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "base.delete_user"
        );

        $event->add($resultArray);
    }

    public function onUserRegisterWelcomeLetter( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId === 0 )
        {
            return;
        }

        BOL_PreferenceService::getInstance()->savePreferenceValue('send_wellcome_letter', 1, $userId);
    }

    public function onUserAvatarCommentFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'avatar-change' )
        {
            return;
        }

        $avatarId = $params['entityId'];

        $service = BOL_AvatarService::getInstance();
        $avatar = $service->findAvatarById($avatarId);

        if ( !$avatar )
        {
            return;
        }

        $userId = $avatar->userId;

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                'activityType' => 'comment',
                'activityId' => $params['userId'],
                'entityId' => $params['entityId'],
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'base'
                ), array(
                'string' => OW::getLanguage()->text('base', 'feed_activity_avatar_string', array(
                    'user' => $userEmbed
                ))
            )));
    }

    public function onUserAvatarLikeFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'avatar-change' )
        {
            return;
        }

        $avatarId = $params['entityId'];

        $service = BOL_AvatarService::getInstance();
        $avatar = $service->findAvatarById($avatarId);

        if ( !$avatar )
        {
            return;
        }

        $userId = $avatar->userId;

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                'activityType' => 'like',
                'activityId' => $params['userId'],
                'entityId' => $params['entityId'],
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'base'
                ), array(
                'string' => OW::getLanguage()->text('base', 'feed_activity_avatar_string_like', array(
                    'user' => $userEmbed
                ))
            )));
    }

    public function onLikeUserJoin( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'user_join' )
        {
            return;
        }

        $userId = $params['entityId'];

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                'activityType' => 'like',
                'activityId' => $params['userId'],
                'entityId' => $params['entityId'],
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'base'
                ), array(
                'string' => OW::getLanguage()->text('base', 'feed_activity_join_profile_string_like', array(
                    'user' => $userEmbed
                ))
            )));
    }

    public function onUserJoinCommentFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['entityType'] != 'user_join' )
        {
            return;
        }

        $userId = $params['entityId'];

        $userName = BOL_UserService::getInstance()->getDisplayName($userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
        $userEmbed = '<a href="' . $userUrl . '">' . $userName . '</a>';

        OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                'activityType' => 'comment',
                'activityId' => $params['commentId'],
                'entityId' => $params['entityId'],
                'entityType' => $params['entityType'],
                'userId' => $params['userId'],
                'pluginKey' => 'base'
                ), array(
                'string' => OW::getLanguage()->text('base', 'feed_activity_join_profile_string', array(
                    'user' => $userEmbed
                ))
            )));
    }

    public function onJoinFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['method'] != 'native' )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $event = new OW_Event('feed.action', array(
                'pluginKey' => 'base',
                'entityType' => 'user_join',
                'entityId' => $userId,
                'userId' => $userId,
                'replace' => true
                ), array(
                'string' => OW::getLanguage()->text('base', 'feed_user_join'),
                'view' => array(
                    'iconClass' => 'ow_ic_user'
                )
            ));
        OW::getEventManager()->trigger($event);
    }

    public function onUserEditFeed( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['method'] != 'native' )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $event = new OW_Event('feed.action', array(
                'pluginKey' => 'base',
                'entityType' => 'user_edit',
                'entityId' => $userId,
                'userId' => $userId,
                'replace' => true
                ), array(
                'string' => OW::getLanguage()->text('base', 'feed_user_edit_profile'),
                'data' => array(
                    'userId' => $userId
                ),
                'features' => array(),
                'view' => array(
                    'iconClass' => 'ow_ic_user'
                )
            ));
        OW::getEventManager()->trigger($event);
    }

    public function onJoinMandatoryUserApprove( OW_Event $event )
    {
        $params = $event->getParams();

        if ( !OW::getConfig()->getValue('base', 'mandatory_user_approve') )
        {
            $e = new OW_Event(OW_EventManager::ON_USER_APPROVE, array('userId' => (int) $params['userId']));
            OW::getEventManager()->trigger($e);

            return;
        }

        BOL_UserService::getInstance()->disapprove((int) $params['userId']);
    }

    public function onAddGlobalLangs( BASE_CLASS_EventCollector $event )
    {
        $event->add(array('site_name' => OW::getConfig()->getValue('base', 'site_name')));
        $event->add(array('site_url' => OW_URL_HOME));
        $event->add(array('site_email' => OW::getConfig()->getValue('base', 'site_email')));
        $event->add(array('default_currency' => BOL_BillingService::getInstance()->getActiveCurrency()));
    }

    public function onDeleteUserContent( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = (int) $params['userId'];

        if ( $userId > 0 )
        {
            $moderatorId = BOL_AuthorizationService::getInstance()->getModeratorIdByUserId($userId);
            if ( $moderatorId !== null )
            {
                BOL_AuthorizationService::getInstance()->deleteModerator($moderatorId);
            }

            BOL_AuthorizationService::getInstance()->deleteUserRolesByUserId($userId);

            if ( isset($params['deleteContent']) && (bool) $params['deleteContent'] )
            {
                BOL_CommentService::getInstance()->deleteUserComments($userId);
                BOL_RateService::getInstance()->deleteUserRates($userId);
                BOL_VoteService::getInstance()->deleteUserVotes($userId);
            }

            //delete widgets
            BOL_ComponentEntityService::getInstance()->onEntityDelete(BOL_ComponentEntityService::PLACE_DASHBOARD, $userId);
            BOL_ComponentEntityService::getInstance()->onEntityDelete(BOL_ComponentEntityService::PLACE_PROFILE, $userId);

            // delete email verify
            BOL_EmailVerifyService::getInstance()->deleteByUserId($userId);

            // delete remote auth info
            BOL_RemoteAuthService::getInstance()->deleteByUserId($userId);
        }
    }
    
    public function getInstanceBaseCmpUsers( OW_Event $event )
    {
        $params = $event->getParams();
        
        return new BASE_CMP_UserList($params['arguments'][0], $params['arguments'][1], $params['arguments'][2]);
    }

    public function sosialSharingGetUserInfo( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $event->getData();
        $data['display'] = false;

        if ( empty($params['entityId']) )
        {
            return;
        }

        if ( $params['entityType'] == 'user' )
        {
            $user = BOL_UserService::getInstance()->findUserById($params['entityId']);

            $displaySocialSharing = true;

            if ( BOL_AuthorizationService::getInstance()->isActionAuthorizedForGuest('base', 'view_profile') )
            {
                $displaySocialSharing = false;
            }

            $eventParams = array(
                'action' => 'base_view_profile',
                'ownerId' => $user->id,
                'viewerId' => 0
            );

            try
            {
                OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
            }
            catch ( RedirectException $ex )
            {
                $displaySocialSharing = false;
            }

            if ( !empty($blogtDto) )
            {
                $data['display'] = $displaySocialSharing;
            }

            $event->setData($data);
        }
    }
}
