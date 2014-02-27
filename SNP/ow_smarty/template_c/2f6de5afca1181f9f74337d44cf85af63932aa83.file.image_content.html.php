<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:50
         compiled from "/home/kairat/www/private/ow_plugins/newsfeed/views/formats/image_content.html" */ ?>
<?php /*%%SmartyHeaderCode:128502955952ccccbe796dc4-79986989%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2f6de5afca1181f9f74337d44cf85af63932aa83' => 
    array (
      0 => '/home/kairat/www/private/ow_plugins/newsfeed/views/formats/image_content.html',
      1 => 1384763007,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '128502955952ccccbe796dc4-79986989',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'vars' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccbe8a04e4_18289531',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccbe8a04e4_18289531')) {function content_52ccccbe8a04e4_18289531($_smarty_tpl) {?><?php if (!empty($_smarty_tpl->tpl_vars['vars']->value['status'])){?><div class="ow_newsfeed_body_status"><?php echo $_smarty_tpl->tpl_vars['vars']->value['status'];?>
</div><?php }?>
<div class="clearfix">
    <div class="ow_newsfeed_item_picture">
    <a href="<?php if (!empty($_smarty_tpl->tpl_vars['vars']->value['url'])){?><?php echo $_smarty_tpl->tpl_vars['vars']->value['url'];?>
<?php }else{ ?>javascript://<?php }?>"><img src="<?php echo $_smarty_tpl->tpl_vars['vars']->value['thumbnail'];?>
" /></a>
    </div>
    <div class="ow_newsfeed_item_content">
        <a class="ow_newsfeed_item_title" href="<?php if (!empty($_smarty_tpl->tpl_vars['vars']->value['url'])){?><?php echo $_smarty_tpl->tpl_vars['vars']->value['url'];?>
<?php }else{ ?>javascript://<?php }?>"><?php echo $_smarty_tpl->tpl_vars['vars']->value['title'];?>
</a>
        <div class="ow_remark ow_smallmargin"><?php echo $_smarty_tpl->tpl_vars['vars']->value['description'];?>
</div>
        
        <?php if ($_smarty_tpl->tpl_vars['vars']->value['userList']){?>
            <div class="owm_newsfeed_ulist">
                <div class="owm_newsfeed_item_padding owm_newsfeed_item_box clearfix">
                    <div class="owm_newsfeed_ulist_count" style="display:inline-block">
                        <?php echo $_smarty_tpl->tpl_vars['vars']->value['userList']['label'];?>

                    </div>
                    <?php echo $_smarty_tpl->tpl_vars['vars']->value['userList']['list'];?>

                </div>
            </div>
        <?php }?>
    </div>
</div><?php }} ?>