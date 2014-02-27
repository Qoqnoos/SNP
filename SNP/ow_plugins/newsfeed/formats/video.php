<?php

class NEWSFEED_FORMAT_Video extends NEWSFEED_CLASS_Format
{
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $defaults = array(
            "image" => null,
            "iconClass" => null,
            "title" => '',
            "description" => '',
            "status" => null,
            "url" => null,
            "embed" => ''
        );

        $tplVars = array_merge($defaults, $this->vars);
        
        $tplVars["url"] = $this->getUrl($tplVars["url"]);
        $tplVars['blankImg'] = OW::getThemeManager()->getCurrentTheme()->getStaticUrl() . 'mobile/images/1px.png';
        
        $this->assign('vars', $tplVars);

        if ( $tplVars['embed'] )
        {
            $script = '$("a.ow_format_video_play").click(function(){
                var $embed = $($(this).parent().find(".ow_format_video_embed").val());
                $(this).replaceWith($embed);
            });';
            OW::getDocument()->addOnloadScript($script);
        }
    }
}
