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
 * @package ow_utilities
 * @since 1.0
 */
require_once OW_DIR_LIB . 'browser' . DS . 'browser.php';

class UTIL_Browser
{

    public static function isSmartphone()
    {
        $wurflDir = OW_DIR_LIB . 'wurfl' . DS;
        $resourcesDir = OW_DIR_PLUGINFILES . 'base' . DS . 'wurfl' . DS;

        if ( defined('OW_PLUGIN_XP') && OW_PLUGIN_XP )
        {
            $resourcesDir = OW_DIR_STATIC_PLUGIN . 'base' . DS . 'wurfl' . DS;
        }

        require_once $wurflDir . 'Application.php';
        $persistenceDir = $resourcesDir . 'persistence' . DS;
        $cacheDir = $resourcesDir . 'cache' . DS;

//        if ( !file_exists($wurflDir) )
//        {
//            mkdir($wurflDir);
//            chmod($wurflDir, 0777);
//            mkdir($persistenceDir);
//            chmod($persistenceDir, 0777);
//            mkdir($cacheDir);
//            chmod($cacheDir, 0777);
//        }

        $wurflConfig = new WURFL_Configuration_InMemoryConfig();
        $wurflConfig->wurflFile($wurflDir . 'wurfl.zip');
        $wurflConfig->matchMode('accuracy');
        $wurflConfig->allowReload(true);
        $wurflConfig->capabilityFilter(array(
            "device_os",
            "device_os_version",
            "is_tablet",
            "is_wireless_device",
            "mobile_browser",
            "mobile_browser_version",
            "pointing_method",
            "preferred_markup",
            "resolution_height",
            "resolution_width",
            "ux_full_desktop",
            "xhtml_support_level"
        ));

        $wurflConfig->persistence('file', array('dir' => $persistenceDir));
        $wurflConfig->cache('file', array('dir' => $cacheDir, 'expiration' => 36000));
        $wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
        $wurflManager = $wurflManagerFactory->create();
        $requestingDevice = $wurflManager->getDeviceForHttpRequest($_SERVER);
        return filter_var($requestingDevice->getVirtualCapability('is_smartphone'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $agentString
     * @return boolean
     */
    public static function isMobile( $agentString )
    {
        return self::getBrowserObj($agentString)->isMobile();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function getBrowser( $agentString )
    {
        return self::getBrowserObj($agentString)->getBrowser();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function getVersion( $agentString )
    {
        return self::getBrowserObj($agentString)->getVersion();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function getPlatform( $agentString )
    {
        return self::getBrowserObj($agentString)->getPlatform();
    }

    /**
     * @param string $agentString
     * @return string
     */
    public static function isRobot( $agentString )
    {
        return self::getBrowserObj($agentString)->isRobot();
    }

    /**
     * @param string $agentString
     * @return CSBrowser
     */
    private static function getBrowserObj( $agentString )
    {
        return new CSBrowser($agentString);
    }

    private static function getWurfl()
    {
        
    }
}