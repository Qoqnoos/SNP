<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:51
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/attachment.html" */ ?>
<?php /*%%SmartyHeaderCode:177199685552ccccbfcd4324-41186853%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '93d313e140bbdc35fe3c4bd83a88fd0b46298462' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/attachment.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '177199685552ccccbfcd4324-41186853',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'wsImageUrl' => 0,
    'themeImagesUrl' => 0,
    'uid' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccbfd5cf22_11779895',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccbfd5cf22_11779895')) {function content_52ccccbfd5cf22_11779895($_smarty_tpl) {?><?php if (!is_callable('smarty_block_style')) include '/home/kairat/www/private/ow_smarty/plugin/block.style.php';
if (!is_callable('smarty_function_text')) include '/home/kairat/www/private/ow_smarty/plugin/function.text.php';
if (!is_callable('smarty_function_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/function.decorator.php';
?><?php $_smarty_tpl->smarty->_tag_stack[] = array('style', array()); $_block_repeat=true; echo smarty_block_style(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>


.ow_attachments a{
    background:url(<?php echo $_smarty_tpl->tpl_vars['wsImageUrl']->value;?>
) -175px 1px no-repeat;
    width:22px;
    height:22px;
    overflow:hidden;
    cursor:pointer;
    text-decoration:none;
    float:left;
}

.ow_attachments a.video{
    background:url(<?php echo $_smarty_tpl->tpl_vars['wsImageUrl']->value;?>
) -197px 1px no-repeat;
}

.ow_attachments input[type=file]{
    opacity:0;
    cursor:pointer;
	float:right;
	filter: alpha(opacity=0);
	-ms-filter:"progid:DXImageTransform.Microsoft.Alpha(opacity=0)";
}

.attachment_preloader{
    background:url(<?php echo $_smarty_tpl->tpl_vars['themeImagesUrl']->value;?>
ajax_preloader_content.gif) 50% 50% no-repeat;
}

.item_loaded{
    border:1px solid #ccc;
    padding:3px;
}

.remove_attachment span{
    float:left;
    font-size:9px;
    line-height:9px;
}

.remove_attachment span.close{
    float:right;
    padding:4px;
}

.remove_attachment a{
    cursor:pointer;
    font-size:14px;
    font-weight:bold;
    color:#aaa;
}

.remove_attachment a:hover{
    text-decoration:none;
    color:#666;
}

.ow_attachments .buttons{
	padding-top:5px;
}
.ow_attachment_btn {
	float: right;
	padding: 0px 0px 0px 0px;
	margin: 0px 0px 0px 4px;
}
.ow_attachment_icons {
	float: right;
}

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_style(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<div class="ow_attachments" id="<?php echo $_smarty_tpl->tpl_vars['uid']->value;?>
">
  <span class="buttons clearfix">
      <a class="image" href="javascript://" title="<?php echo smarty_function_text(array('key'=>'base+attch_photo_button_label'),$_smarty_tpl);?>
"></a>
      <a class="video" href="javascript://" title="<?php echo smarty_function_text(array('key'=>'base+attch_video_button_label'),$_smarty_tpl);?>
"></a>
  </span>
  <div style="display: none;">
      <div id="video_code_<?php echo $_smarty_tpl->tpl_vars['uid']->value;?>
">
      <?php echo smarty_function_text(array('key'=>'base+ws_video_textarea_label'),$_smarty_tpl);?>

      <textarea style="height: 140px;"></textarea>
      <div style="padding-top:10px;text-align: center;">
        <?php echo smarty_function_decorator(array('name'=>"button",'langLabel'=>'base+attch_video_add_button_label'),$_smarty_tpl);?>

      </div>
      </div>
  </div>
</div><?php }} ?>