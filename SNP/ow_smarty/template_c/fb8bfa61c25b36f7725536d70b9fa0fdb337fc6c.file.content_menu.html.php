<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 20:02:50
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/content_menu.html" */ ?>
<?php /*%%SmartyHeaderCode:173391778052cccdead8c999-80166363%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fb8bfa61c25b36f7725536d70b9fa0fdb337fc6c' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/content_menu.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '173391778052cccdead8c999-80166363',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'data' => 0,
    'item' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52cccdeae3c4d8_03576395',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52cccdeae3c4d8_03576395')) {function content_52cccdeae3c4d8_03576395($_smarty_tpl) {?><div class="ow_content_menu_wrap">
<ul class="ow_content_menu clearfix">
 	<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['data']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
?>
    <li class="<?php echo $_smarty_tpl->tpl_vars['item']->value['class'];?>
 <?php if ($_smarty_tpl->tpl_vars['item']->value['active']){?> active<?php }?>"><a href="<?php echo $_smarty_tpl->tpl_vars['item']->value['url'];?>
"><span<?php if ($_smarty_tpl->tpl_vars['item']->value['iconClass']){?> class="<?php echo $_smarty_tpl->tpl_vars['item']->value['iconClass'];?>
"<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value['label'];?>
</span></a></li>
	<?php } ?>
</ul>
</div><?php }} ?>