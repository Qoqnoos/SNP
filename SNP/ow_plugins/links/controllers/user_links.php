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
 * @author Zarif Safiullin <zaph.saph@gmail.com>
 * @package ow_plugins.links.controllers
 * @since 1.0
 */
class Links_CTRL_UserLinks extends OW_ActionController
{

    public function index( $params )
    {
        $plugin = OW::getPluginManager()->getPlugin('links');

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'links', 'main_menu_item');

        if ( !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('links', 'view') )
        {
            $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCtrlViewDir() . 'authorization_failed.html');

            return;
        }

        /*
          @var $service LinkService
         */
        $service = LinkService::getInstance();

        /*
          @var $userService BOL_UserService
         */
        $userService = BOL_UserService::getInstance();

        /*
          @var $author BOL_User
         */
        if ( !empty($params['user']) )
        {
            $author = $userService->findByUsername($params['user']);
        }
        else
        {
            $author = $userService->findUserById(OW::getUser()->getId());
        }

        if ( empty($author) )
        {
            throw new Redirect404Exception();
            return;
        }

        /* Check privacy permissions */
        $eventParams = array(
            'action' => LinkService::PRIVACY_ACTION_VIEW_LINKS,
            'ownerId' => $author->getId(),
            'viewerId' => OW::getUser()->getId()
        );

        OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        /* */

        $displayName = $userService->getDisplayName($author->getId());

        $this->assign('author', $author);
        $this->assign('username', $author->getUsername());
        $this->assign('displayname', $displayName);

        if ($author->getId() == OW::getUser()->getId())
        {
            $this->setPageHeading(OW::getLanguage()->text('links', 'my_links'));
            $this->setPageHeadingIconClass('ow_ic_write');
            OW::getDocument()->setTitle(OW::getLanguage()->text('links', 'my_links'));
        }
        else
        {
            $this->setPageHeading(OW::getLanguage()->text('links', 'user_link_page_heading', array('display_name' => $displayName)));
            $this->setPageHeadingIconClass('ow_ic_write');
            OW::getDocument()->setTitle(OW::getLanguage()->text('links', 'user_links_title', array('display_name'=>$displayName)));
        }
        OW::getDocument()->setDescription(OW::getLanguage()->text('links', 'user_links_description', array('display_name'=>$displayName) ));

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;

        $rpp = (int) OW::getConfig()->getValue('links', 'results_per_page');

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $list = $service->findUserLinkList($author->getId(), $first, $count);

        $itemsCount = $service->countUserLinks($author->getId());

        $posts = array();

        $commentInfo = array();

        $idList = array();

        foreach ( $list as $dto ) /* @var dto Post */
        {
            $idList[] = $dto->getId();
            $text = BASE_CMP_TextFormatter::fromBBtoHtml($dto->getDescription());

            $descLength = 120;
            $text = strip_tags($text);
            if (strlen($text) > $descLength)
            {
                $text = UTIL_String::truncate($text, $descLength, '...');
                $text .= ' <a href="'. OW::getRouter()->urlForRoute('link', array('id'=>$dto->getId())).'" class="ow_lbutton">'.OW::getLanguage()->text('base', 'more').'</a>';
            }

            $posts[$dto->getId()] = array(
                'id' => $dto->getId(),
                'href' => $dto->getUrl(),
                'title' => UTIL_String::truncate($dto->getTitle(), 65, '...'),
                'text' => $text
            );
        }

        if ( !empty($idList) )
        {
            $voteService = BOL_VoteService::getInstance();

            switch ( OW::getConfig()->getValue('links', 'result_mode') )
            {
                case LinkService::RESULT_MODE_SUM:
                    $this->assign('mode', 'sum');
                    break;
                case LinkService::RESULT_MODE_DETAILED:
                    $this->assign('mode', 'detailed');
                    break;
            }

            $voteTotal = $voteService->findTotalVotesResultForList($idList, 'link');

            foreach ( $voteTotal as $val )
            {
                $posts[$val['id']]['isVoted'] = true;
                $posts[$val['id']]['voteTotal'] = $val['sum'];
                $posts[$val['id']]['up'] = $val['up'];
                $posts[$val['id']]['down'] = $val['down'];
            }


            $commentInfo = BOL_CommentService::getInstance()->findCommentCountForEntityList('link', $idList);
            $this->assign('commentInfo', $commentInfo);

            $tagsInfo = BOL_TagService::getInstance()->findTagListByEntityIdList('link', $idList);
            $this->assign('tagsInfo', $tagsInfo);

            $tb = array();

            foreach ( $list as $dto ) /* @var dto Post */
            {

                $tb[$dto->getId()] = array(
                    array(
                        'href' => OW::getRouter()->urlForRoute('link', array('id' => $dto->getId())),
                        'label' => UTIL_DateTime::formatDate($dto->timestamp)
                    ),
                );

                if ( $commentInfo[$dto->getId()] )
                {
                    $tb[$dto->getId()][] = array(
                        'href' => OW::getRouter()->urlForRoute('link', array('id' => $dto->getId()))."#comments",
                        'label' => '<span class="ow_outline">' . $commentInfo[$dto->getId()] . '</span> ' . OW::getLanguage()->text('links', 'toolbar_comments')
                    );
                }

                if ( $tagsInfo[$dto->getId()] )
                {
                    $tags = &$tagsInfo[$dto->getId()];
                    $t = OW::getLanguage()->text('links', 'tags');
                    for ( $i = 0; $i < (count($tags) > 3 ? 3 : count($tags)); $i++ )
                    {
                        $t .= " <a href=\"" . OW::getRouter()->urlForRoute('links-by-tag', array('list'=>'browse-by-tag')) . "?tag={$tags[$i]}\">{$tags[$i]}</a>" . ( $i != 2 ? ',' : '' );
                    }

                    $tb[$dto->getId()][] = array('label' => mb_substr($t, 0, mb_strlen($t) - 1));
                }
            }

            $this->assign('tb', $tb);

            if ( OW::getUser()->isAuthenticated() )
            {
                $userVotes = $voteService->findUserVoteForList($idList, 'link', OW::getUser()->getId());
                $this->assign('userVotes', $userVotes);
            }
        }

        $this->assign('list', $posts);

        $paging = new BASE_CMP_Paging($page, ceil($itemsCount / $rpp), 5);

        $this->assign('paging', $paging->render());
        $this->assign('isAuthenticated', OW::getUser()->isAuthenticated());

    }
}
