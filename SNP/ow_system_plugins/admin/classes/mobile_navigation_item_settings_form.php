<?php

class ADMIN_CLASS_MobileNavigationItemSettingsForm extends Form
{
    /**
     *
     * @var BOL_MenuItem
     */
    private $menuItem;
    
    public function __construct( BOL_MenuItem $menuItem, $custom = false )
    {
        parent::__construct("settingForm");
        
        $this->menuItem = $menuItem;
        
        $language = OW::getLanguage();
        
        $this->setAjax();
        $this->setAction(OW::getRouter()->urlFor("ADMIN_CTRL_MobileNavigation", "saveItemSettings"));
        
        $item = new HiddenField("key");
        $item->setValue($menuItem->prefix . ':' . $menuItem->key);
        $this->addElement($item);
        
        $settings = BOL_MobileNavigationService::getInstance()->getItemSettings($this->menuItem);
        
        // Mail Settings
        $item = new TextField(BOL_MobileNavigationService::SETTING_LABEL);
        $item->setLabel($language->text("mobile", "admin_nav_item_label_field"));
        $item->setValue($settings[BOL_MobileNavigationService::SETTING_LABEL]);
        $this->addElement($item);
        
        if ( $custom )
        {
            $item = new TextField(BOL_MobileNavigationService::SETTING_TITLE);
            $item->setLabel($language->text("mobile", "admin_nav_item_title_field"));
            $item->setValue($settings[BOL_MobileNavigationService::SETTING_TITLE]);
            $this->addElement($item);

            $item = new Textarea(BOL_MobileNavigationService::SETTING_CONTENT);
            $item->setLabel($language->text("mobile", "admin_nav_item_content_field"));
            $item->setValue($settings[BOL_MobileNavigationService::SETTING_CONTENT]);
            $this->addElement($item);

            $item = new CheckboxGroup(BOL_MobileNavigationService::SETTING_VISIBLE_FOR);
            $visibleFor = empty($settings[BOL_MobileNavigationService::SETTING_VISIBLE_FOR]) 
                    ? 0
                    : $settings[BOL_MobileNavigationService::SETTING_VISIBLE_FOR];
                    
            $options = array(
                '1' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_guests'),
                '2' => OW::getLanguage()->text('admin', 'pages_edit_visible_for_members')
            );

            $values = array();

            foreach ( $options as $value => $option )
            {
                if ( !($value & $visibleFor) )
                    continue;
                
                $values[] = $value;
            }

            $this->addElement(
                    $item->setOptions($options)
                    ->setValue($values)
                    ->setLabel(OW::getLanguage()->text('admin', 'pages_edit_local_visible_for'))
            );
        }

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'save_btn_label'));
        $this->addElement($submit);
    }
    
    public function process() 
    {
        $values = $this->getValues();
        
        BOL_MobileNavigationService::getInstance()->editItem($this->menuItem, $values);
        
        $items = array();
        $items[$values["key"]] = array(
            "title" => $values[BOL_MobileNavigationService::SETTING_LABEL]
        );
        
        return array(
            "items" => $items
        );
    }
}