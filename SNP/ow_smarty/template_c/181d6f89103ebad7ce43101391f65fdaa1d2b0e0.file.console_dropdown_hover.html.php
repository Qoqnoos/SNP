<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:53
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/console_dropdown_hover.html" */ ?>
<?php /*%%SmartyHeaderCode:110857039152ccccc1737ba3-96726905%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '181d6f89103ebad7ce43101391f65fdaa1d2b0e0' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/console_dropdown_hover.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '110857039152ccccc1737ba3-96726905',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'url' => 0,
    'label' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccc1754348_56344628',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccc1754348_56344628')) {function content_52ccccc1754348_56344628($_smarty_tpl) {?><a href="<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
" class="ow_console_item_link"><?php echo $_smarty_tpl->tpl_vars['label']->value;?>
</a>
<span class="ow_console_more"></span><?php }} ?>