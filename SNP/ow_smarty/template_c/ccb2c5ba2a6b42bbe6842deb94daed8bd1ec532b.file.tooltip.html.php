<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:51
         compiled from "/home/kairat/www/private/ow_system_plugins/base/decorators/tooltip.html" */ ?>
<?php /*%%SmartyHeaderCode:179399945952ccccbf4a3f23-05516402%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ccb2c5ba2a6b42bbe6842deb94daed8bd1ec532b' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/decorators/tooltip.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '179399945952ccccbf4a3f23-05516402',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccbf4e0f54_82231769',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccbf4e0f54_82231769')) {function content_52ccccbf4e0f54_82231769($_smarty_tpl) {?>
<div class="ow_tooltip <?php if (!empty($_smarty_tpl->tpl_vars['data']->value['addClass'])){?> <?php echo $_smarty_tpl->tpl_vars['data']->value['addClass'];?>
<?php }?>">
    <div class="ow_tooltip_tail">
        <span></span>
    </div>
    <div class="ow_tooltip_body">
        <?php echo $_smarty_tpl->tpl_vars['data']->value['content'];?>

    </div>
</div><?php }} ?>