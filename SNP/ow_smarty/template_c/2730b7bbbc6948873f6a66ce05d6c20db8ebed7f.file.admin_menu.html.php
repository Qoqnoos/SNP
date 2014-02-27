<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 20:02:51
         compiled from "/home/kairat/www/private/ow_system_plugins/admin/views/components/admin_menu.html" */ ?>
<?php /*%%SmartyHeaderCode:160954104952cccdeb068c22-94961288%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2730b7bbbc6948873f6a66ce05d6c20db8ebed7f' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/admin/views/components/admin_menu.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '160954104952cccdeb068c22-94961288',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'class' => 0,
    'data' => 0,
    'item' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52cccdeb0f4179_30851510',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52cccdeb0f4179_30851510')) {function content_52cccdeb0f4179_30851510($_smarty_tpl) {?><span class="ow_tooltip_tail"><span></span></span>	
<ul class="<?php echo $_smarty_tpl->tpl_vars['class']->value;?>
 clearfix ow_tooltip_body">
<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['data']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
?>
	<li class="<?php echo $_smarty_tpl->tpl_vars['item']->value['class'];?>
<?php if (!empty($_smarty_tpl->tpl_vars['item']->value['active'])){?> active<?php }?>">
		<a href="<?php echo $_smarty_tpl->tpl_vars['item']->value['url'];?>
"<?php if (!empty($_smarty_tpl->tpl_vars['item']->value['new_window'])){?> target="_blank"<?php }?>>
		   <span><?php echo $_smarty_tpl->tpl_vars['item']->value['label'];?>
</span>
		</a>
	</li>
<?php } ?>
</ul>
<?php }} ?>