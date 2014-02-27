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
$plugin = OW::getPluginManager()->getPlugin('links');

OW::getAutoloader()->addClass('Link', $plugin->getBolDir() . 'dto' . DS . 'link.php');
OW::getAutoloader()->addClass('LinkDao', $plugin->getBolDir() . 'dao' . DS . 'link_dao.php');
OW::getAutoloader()->addClass('LinkService', $plugin->getBolDir() . 'service' . DS . 'link_service.php');

OW::getRouter()->addRoute(new OW_Route('links', 'links', "LINKS_CTRL_List", 'index'));
OW::getRouter()->addRoute(new OW_Route('links-user', 'links/user/:user', "LINKS_CTRL_UserLinks", 'index'));
OW::getRouter()->addRoute(new OW_Route('link', 'link/:id', "LINKS_CTRL_View", 'index'));

OW::getRouter()->addRoute(new OW_Route('link-save-new', 'links/new', "LINKS_CTRL_Save", 'index'));
OW::getRouter()->addRoute(new OW_Route('link-save-edit', 'links/edit/:id', "LINKS_CTRL_Save", 'index'));

OW::getRouter()->addRoute(new OW_Route('links-latest', 'links/latest', "LINKS_CTRL_List", 'index'));
OW::getRouter()->addRoute(new OW_Route('links-most-discussed', 'links/most-discussed', "LINKS_CTRL_List", 'index'));
OW::getRouter()->addRoute(new OW_Route('links-top-rated', 'links/top-rated', "LINKS_CTRL_List", 'index'));
OW::getRouter()->addRoute(new OW_Route('links-by-tag', 'links/browse-by-tag/', "LINKS_CTRL_List", 'index'));

OW::getRouter()->addRoute(new OW_Route('links-admin', 'admin/links', "LINKS_CTRL_Admin", 'index'));

$eventHandler = LINKS_CLASS_EventHandler::getInstance();
$eventHandler->genericInit();

OW::getEventManager()->bind(BASE_CMP_AddNewContent::EVENT_NAME,     array($eventHandler, 'onCollectAddNewContentItem'));
OW::getEventManager()->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME,  array($eventHandler, 'onCollectQuickLinks'));
