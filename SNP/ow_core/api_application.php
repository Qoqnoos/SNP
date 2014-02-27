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
 * Description...
 *
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_ApiApplication extends OW_Application
{

    private function __construct()
    {
        $this->context = self::CONTEXT_API;
    }
    /**
     * Singleton instance.
     *
     * @var OW_ApiApplication
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_ApiApplication
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
     * Application init actions.
     */
    public function init()
    {
//        $this->urlHostRedirect();
//        //printVar(10);exit;
        $this->userAutoLogin();

        // setting default time zone
        date_default_timezone_set(OW::getConfig()->getValue('base', 'site_timezone'));

//        OW::getRequestHandler()->setIndexPageAttributes('BASE_CTRL_ComponentPanel');
//        OW::getRequestHandler()->setStaticPageAttributes('BASE_CTRL_StaticDocument');
//
//        // router init - need to set current page uri and base url
        $router = OW::getRouter();
        $router->setBaseUrl(OW_URL_HOME . 'api/');
        $uri = OW::getRequest()->getRequestUri();

        // before setting in router need to remove get params
        if ( strstr($uri, '?') )
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        $router->setUri($uri);

        $router->setDefaultRoute(new OW_ApiDefaultRoute());

        $beckend = OW::getEventManager()->call('base.cache_backend_init');

        if ( $beckend !== null )
        {
            OW::getCacheManager()->setCacheBackend($beckend);
            OW::getCacheManager()->setLifetime(3600);
            OW::getDbo()->setUseCashe(true);
        }



        OW::getResponse()->setDocument($this->newDocument());
    }

    /**
     * Finds controller and action for current request.
     */
    public function route()
    {
        try
        {
            OW::getRequestHandler()->setHandlerAttributes(OW::getRouter()->route());
        }
        catch ( RedirectException $e )
        {
            $this->redirect($e->getUrl(), $e->getRedirectCode());
        }
        catch ( InterceptException $e )
        {
            OW::getRequestHandler()->setHandlerAttributes($e->getHandlerAttrs());
        }
    }

    /**
     * ---------
     */
    public function handleRequest()
    {
        $baseConfigs = OW::getConfig()->getValues('base');

        //members only
        if ( (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_CANT_VIEW
            && !OW::getUser()->isAuthenticated() )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_User',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'standardSignIn'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.members_only', $attributes);
            $this->addCatchAllRequestsException('base.members_only_exceptions', 'base.members_only');
        }

        //splash screen
        if ( (bool) OW::getConfig()->getValue('base', 'splash_screen') && !isset($_COOKIE['splashScreen']) )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'splashScreen',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_REDIRECT => true,
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_JS => true,
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ROUTE => 'base_page_splash_screen'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.splash_screen', $attributes);
            $this->addCatchAllRequestsException('base.splash_screen_exceptions', 'base.splash_screen');
        }

        // password protected
        if ( (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_PASSWORD_VIEW
            && !OW::getUser()->isAuthenticated() && !isset($_COOKIE['base_password_protection'])
        )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'passwordProtection'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.password_protected', $attributes);
            $this->addCatchAllRequestsException('base.password_protected_exceptions', 'base.password_protected');
        }

        // maintenance mode
        if ( (bool) $baseConfigs['maintenance'] && !OW::getUser()->isAdmin() )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'maintenance',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_REDIRECT => true
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.maintenance_mode', $attributes);
            $this->addCatchAllRequestsException('base.maintenance_mode_exceptions', 'base.maintenance_mode');
        }

        try
        {
            OW_ApiRequestHandler::getInstance()->dispatch();
        }
        catch ( RedirectException $e )
        {
            $this->redirect($e->getUrl(), $e->getRedirectCode());
        }
        catch ( InterceptException $e )
        {
            OW::getRequestHandler()->setHandlerAttributes($e->getHandlerAttrs());
            $this->handleRequest();
        }
    }

    /**
     * Method called just before request responding.
     */
    public function finalize()
    {
//        $document = OW::getDocument();
//
//        $meassages = OW::getFeedback()->getFeedback();
//
//        foreach ( $meassages as $messageType => $messageList )
//        {
//            foreach ( $messageList as $message )
//            {
//                $document->addOnloadScript("OW.message(" . json_encode($message) . ", '" . $messageType . "');");
//            }
//        }

        $event = new OW_Event(OW_EventManager::ON_FINALIZE);
        OW::getEventManager()->trigger($event);
    }

    /**
     * System method. Don't call it!!!
     */
    public function onBeforeDocumentRender()
    {
//        $document = OW::getDocument();
//
//        $document->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'ow.css' . '?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', -100);
//        $document->addStyleSheet(OW::getThemeManager()->getCssFileUrl() . '?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'all', (-90));
//
//        // add custom css if page is not admin TODO replace with another condition
//        if ( !OW::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
//        {
//            if ( OW::getThemeManager()->getCurrentTheme()->getDto()->getCustomCssFileName() !== null )
//            {
//                $document->addStyleSheet(OW::getThemeManager()->getThemeService()->getCustomCssFileUrl(OW::getThemeManager()->getCurrentTheme()->getDto()->getName()));
//            }
//
//            if ( $this->getDocumentKey() !== 'base.sign_in' )
//            {
//                $customHeadCode = OW::getConfig()->getValue('base', 'html_head_code');
//                $customAppendCode = OW::getConfig()->getValue('base', 'html_prebody_code');
//
//                if ( !empty($customHeadCode) )
//                {
//                    $document->addCustomHeadInfo($customHeadCode);
//                }
//
//                if ( !empty($customAppendCode) )
//                {
//                    $document->appendBody($customAppendCode);
//                }
//            }
//        }
//
//        $language = OW::getLanguage();
//
//        if ( $document->getTitle() === null )
//        {
//            $document->setTitle($language->text('nav', 'page_default_title'));
//        }
//
//        if ( $document->getDescription() === null )
//        {
//            $document->setDescription($language->text('nav', 'page_default_description'));
//        }
//
//        /* if ( $document->getKeywords() === null )
//          {
//          $document->setKeywords($language->text('nav', 'page_default_keywords'));
//          } */
//
//        if ( $document->getHeadingIconClass() === null )
//        {
//            $document->setHeadingIconClass('ow_ic_file');
//        }
//
//        if ( !empty($this->documentKey) )
//        {
//            $document->setBodyClass($this->documentKey);
//        }
//
//        if ( $this->getDocumentKey() !== null )
//        {
//            $masterPagePath = OW::getThemeManager()->getDocumentMasterPage($this->getDocumentKey());
//
//            if ( $masterPagePath !== null )
//            {
//                $document->getMasterPage()->setTemplate($masterPagePath);
//            }
//        }
    }

    /**
     * Triggers response object to send rendered page.
     */
    public function returnResponse()
    {
        OW::getResponse()->respond();
    }

    /**
     * Makes header redirect to provided URL or URI.
     *
     * @param string $redirectTo
     */
    public function redirect( $redirectTo = null, $switchContextTo = false )
    {
//        if ( $switchContextTo !== false && in_array($switchContextTo, array(self::CONTEXT_DESKTOP, self::CONTEXT_MOBILE)) )
//        {
//            OW::getSession()->set(self::CONTEXT_NAME, $switchContextTo);
//        }
//
//        // if empty redirect location -> current URI is used
//        if ( $redirectTo === null )
//        {
//            $redirectTo = OW::getRequest()->getRequestUri();
//        }
//
//        // if URI is provided need to add site home URL
//        if ( !strstr($redirectTo, 'http://') && !strstr($redirectTo, 'https://') )
//        {
//            $redirectTo = OW::getRouter()->getBaseUrl() . UTIL_String::removeFirstAndLastSlashes($redirectTo);
//        }
//
//        UTIL_Url::redirect($redirectTo);
    }

    /**
     * Menu item to activate.
     *
     * @var BOL_MenuItem
     */
    public function activateMenuItem()
    {
//        if ( !OW::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
//        {
//            if ( OW::getRequest()->getRequestUri() === '/' || OW::getRequest()->getRequestUri() === '' )
//            {
//                OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, $this->indexMenuItem->getPrefix(), $this->indexMenuItem->getKey());
//            }
//        }
    }
    /* private auxilary methods */

    protected function newDocument()
    {
        $document = new OW_ApiDocument();

        return $document;

//        $language = BOL_LanguageService::getInstance()->getCurrent();
//        $document = new OW_HtmlDocument();
//        $document->setCharset('UTF-8');
//        $document->setMime('text/html');
//        $document->setLanguage($language->getTag());
//
//        if ( $language->getRtl() )
//        {
//            $document->setDirection('rtl');
//        }
//        else
//        {
//            $document->setDirection('ltr');
//        }
//
//        if ( (bool) OW::getConfig()->getValue('base', 'favicon') )
//        {
//            $document->setFavicon(OW::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'favicon.ico');
//        }
//
//        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.min.js', 'text/javascript', (-100));
//        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-migrate.min.js', 'text/javascript', (-100));
//
//        //$document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'json2.js', 'text/javascript', (-99));
//        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'ow.js?' . OW::getConfig()->getValue('base', 'cachedEntitiesPostfix'), 'text/javascript', (-50));
//
//        $onloadJs = "OW.bindAutoClicks();OW.bindTips($('body'));";
//
//        if ( OW::getUser()->isAuthenticated() )
//        {
//            $activityUrl = OW::getRouter()->urlFor('BASE_CTRL_User', 'updateActivity');
//            $onloadJs .= "OW.getPing().addCommand('user_activity_update').start(600000);";
//        }
//
//        $document->addOnloadScript($onloadJs);
//        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'onBeforeDocumentRender'));

        return $document;
    }

    protected function userAutoLogin()
    {
        //TODO remake with tokens
//        if ( OW::getSession()->isKeySet('no_autologin') )
//        {
//            OW::getSession()->delete('no_autologin');
//            return;
//        }
//
//        if ( !empty($_COOKIE['ow_login']) && !OW::getUser()->isAuthenticated() )
//        {
//            $id = BOL_UserService::getInstance()->findUserIdByCookie(trim($_COOKIE['ow_login']));
//
//            if ( !empty($id) )
//            {
//                OW_User::getInstance()->login($id);
//                $loginCookie = BOL_UserService::getInstance()->findLoginCookieByUserId($id);
//                setcookie('ow_login', $loginCookie->getCookie(), (time() + 86400 * 7), '/', null, null, true);
//            }
//        }
    }

    protected function addCatchAllRequestsException( $eventName, $key )
    {
        $event = new BASE_CLASS_EventCollector($eventName);
        OW::getEventManager()->trigger($event);
        $exceptions = $event->getData();

        foreach ( $exceptions as $item )
        {
            if ( is_array($item) && !empty($item['controller']) && !empty($item['action']) )
            {
                OW::getRequestHandler()->addCatchAllRequestsExclude($key, trim($item['controller']), trim($item['action']));
            }
        }
    }
}
