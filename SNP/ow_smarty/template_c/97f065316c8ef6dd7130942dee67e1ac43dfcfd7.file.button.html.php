<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:51
         compiled from "/home/kairat/www/private/ow_themes/origin/decorators/button.html" */ ?>
<?php /*%%SmartyHeaderCode:152821492852ccccbfd65621-85993006%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '97f065316c8ef6dd7130942dee67e1ac43dfcfd7' => 
    array (
      0 => '/home/kairat/www/private/ow_themes/origin/decorators/button.html',
      1 => 1378981014,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '152821492852ccccbfd65621-85993006',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccbfedfa54_25797055',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccbfedfa54_25797055')) {function content_52ccccbfedfa54_25797055($_smarty_tpl) {?><?php if (!is_callable('smarty_function_text')) include '/home/kairat/www/private/ow_smarty/plugin/function.text.php';
?>
<span class="ow_button"><span class="<?php if (!empty($_smarty_tpl->tpl_vars['data']->value['class'])){?> <?php echo $_smarty_tpl->tpl_vars['data']->value['class'];?>
<?php }?>"><input type="<?php if (!empty($_smarty_tpl->tpl_vars['data']->value['type'])&&$_smarty_tpl->tpl_vars['data']->value['type']=='submit'){?>submit<?php }else{ ?>button<?php }?>"  value="<?php if (!empty($_smarty_tpl->tpl_vars['data']->value['langLabel'])){?><?php echo smarty_function_text(array('key'=>$_smarty_tpl->tpl_vars['data']->value['langLabel']),$_smarty_tpl);?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['data']->value['label'];?>
<?php }?>"<?php if (!empty($_smarty_tpl->tpl_vars['data']->value['buttonName'])){?> name="<?php echo $_smarty_tpl->tpl_vars['data']->value['buttonName'];?>
"<?php }?><?php if (!empty($_smarty_tpl->tpl_vars['data']->value['id'])){?> id="<?php echo $_smarty_tpl->tpl_vars['data']->value['id'];?>
"<?php }?><?php if (!empty($_smarty_tpl->tpl_vars['data']->value['class'])){?> class="<?php echo $_smarty_tpl->tpl_vars['data']->value['class'];?>
"<?php }?><?php if (!empty($_smarty_tpl->tpl_vars['data']->value['extraString'])){?><?php echo $_smarty_tpl->tpl_vars['data']->value['extraString'];?>
<?php }?> <?php if (!empty($_smarty_tpl->tpl_vars['data']->value['onclick'])){?>onclick="<?php echo $_smarty_tpl->tpl_vars['data']->value['onclick'];?>
"<?php }?> /></span></span><?php }} ?>