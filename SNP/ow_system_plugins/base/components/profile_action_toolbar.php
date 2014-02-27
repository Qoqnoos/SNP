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
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_ProfileActionToolbar extends OW_Component
{
    /**
     * @deprecated constant
     */
    const REGISTRY_DATA_KEY = 'base_cmp_profile_action_toolbar';

    const EVENT_NAME = 'base.add_profile_action_toolbar';
    const EVENT_PROCESS_TOOLBAR = 'base.process_profile_action_toolbar';
    const DATA_KEY_LABEL = 'label';
    const DATA_KEY_LINK_ID = 'id';
    const DATA_KEY_LINK_CLASS = 'linkClass';
    const DATA_KEY_CMP_CLASS = 'cmpClass';
    const DATA_KEY_LINK_HREF = 'href';
    const DATA_KEY_LINK_ORDER = 'order';
    const DATA_KEY_ITEM_KEY = 'key';

    const DATA_KEY_LINK_ATTRIBUTES = 'attributes';
    const DATA_KEY_LINK_GROUP_KEY = 'groupKey';
    const DATA_KEY_LINK_GROUP_LABEL = 'groupLabel';

    const GROUP_GLOBAL = 'global';

    protected $userId;

    /**
     * Constructor.
     */
    public function __construct( $userId )
    {
        parent::__construct();

        $this->userId = (int) $userId;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $event = new BASE_CLASS_EventCollector(self::EVENT_NAME, array('userId' => $this->userId));

        OW::getEventManager()->trigger($event);

        $event = new OW_Event(self::EVENT_PROCESS_TOOLBAR, array('userId' => $this->userId), $event->getData());

        OW::getEventManager()->trigger($event);

        $addedData = $event->getData();

        if ( empty($addedData) )
        {
            $this->setVisible(false);

            return;
        }

        $this->initToolbar($addedData);
    }

    /*public function initToolbar( $items )
    {
        $cmpsMarkup = '';
        $contextActionMenu = new BASE_CMP_ContextAction();
        $ghroupsCount = 0;

        foreach ( $items as $item )
        {
            if ( empty($item[self::DATA_KEY_LINK_GROUP_KEY]) )
            {
                continue;
            }

            $contextAction = new BASE_ContextAction();
            $contextAction->setKey($item[self::DATA_KEY_LINK_GROUP_KEY]);
            $contextAction->setOrder(-1);

            if ( isset($item[self::DATA_KEY_LINK_GROUP_LABEL]) )
            {
                $contextAction->setLabel($item[self::DATA_KEY_LINK_GROUP_LABEL]);
            }

            $contextActionMenu->addAction($contextAction);
        }

        foreach ( $items as $item  )
        {
            $contextAction = new BASE_ContextAction();

            if ( !empty($item[self::DATA_KEY_LINK_GROUP_KEY]) )
            {
                $contextAction->setParentKey($item[self::DATA_KEY_LINK_GROUP_KEY]);
            }

            $contextAction->setKey(uniqid('pat_'));
            $contextAction->setLabel($item[self::DATA_KEY_LABEL]);

            if ( isset($item[self::DATA_KEY_LINK_HREF]) )
            {
                $contextAction->setUrl($item[self::DATA_KEY_LINK_HREF]);
            }

            if ( isset($item[self::DATA_KEY_LINK_ID]) )
            {
                $contextAction->setId($item[self::DATA_KEY_LINK_ID]);
            }

            if ( isset($item[self::DATA_KEY_LINK_CLASS]) )
            {
                $contextAction->setClass($item[self::DATA_KEY_LINK_CLASS]);
            }

            if ( isset($item[self::DATA_KEY_LINK_ATTRIBUTES]) )
            {
                foreach ($item[self::DATA_KEY_LINK_ATTRIBUTES] as $name => $value )
                {
                    $contextAction->addAttribute($name, $value);
                }
            }

            if ( isset($item[self::DATA_KEY_LINK_ORDER]) )
            {
                $contextAction->setOrder($item[self::DATA_KEY_LINK_ORDER]);
            }

            if ( isset($item[self::DATA_KEY_CMP_CLASS]) )
            {
                $cmpClass = trim($item[self::DATA_KEY_CMP_CLASS]);
                $cmp = new $cmpClass(array('userId' => $this->userId));

                $cmpsMarkup .= $cmp->render();
            }

            $contextActionMenu->addAction($contextAction);
        }

        $this->addComponent('toolbar', $contextActionMenu);
        $this->assign('cmpsMarkup', $cmpsMarkup);
    }*/

    public function initToolbar( $items )
    {
        $cmpsMarkup = '';
        $ghroupsCount = 0;

        $tplActions = array();

        foreach ( $items as $item  )
        {
            $action = &$tplActions[];

            $action['label'] = $item[self::DATA_KEY_LABEL];
            $action['order'] = count($tplActions);

            $attrs = isset($item[self::DATA_KEY_LINK_ATTRIBUTES]) && is_array($item[self::DATA_KEY_LINK_ATTRIBUTES])
                ? $item[self::DATA_KEY_LINK_ATTRIBUTES]
                : array();

            $attrs['href'] = isset($item[self::DATA_KEY_LINK_HREF]) ? $item[self::DATA_KEY_LINK_HREF] : 'javascript://';

            if ( isset($item[self::DATA_KEY_LINK_ID]) )
            {
                $attrs['id'] = $item[self::DATA_KEY_LINK_ID];
            }

            if ( isset($item[self::DATA_KEY_LINK_CLASS]) )
            {
                $attrs['class'] = $item[self::DATA_KEY_LINK_CLASS];
            }
            
            if ( isset($item[self::DATA_KEY_LINK_ORDER]) )
            {
                $action['order'] = $item[self::DATA_KEY_LINK_ORDER];
            }

            if ( isset($item[self::DATA_KEY_CMP_CLASS]) )
            {
                $cmpClass = trim($item[self::DATA_KEY_CMP_CLASS]);

                $cmp = OW::getClassInstance($cmpClass, array(
                    'userId' => $this->userId
                ));

                $cmpsMarkup .= $cmp->render();
            }

            $_attrs = array();
            foreach ( $attrs as $name => $value )
            {
                $_attrs[] = $name . '="' . $value . '"';
            }

            $action['attrs'] = implode(' ', $_attrs);
        }

        $this->assign('toolbar', $tplActions);
        $this->assign('cmpsMarkup', $cmpsMarkup);
    }
}