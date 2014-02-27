<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 20:08:12
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/controllers/base_document_page404.html" */ ?>
<?php /*%%SmartyHeaderCode:148875086052cccf2c040294-40046769%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f9bf7a4f2099ea7e6505b57dbf3e27c94f80b93a' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/controllers/base_document_page404.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '148875086052cccf2c040294-40046769',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'base404RedirectMessage' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52cccf2c0a72e6_45338008',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52cccf2c0a72e6_45338008')) {function content_52cccf2c0a72e6_45338008($_smarty_tpl) {?><?php if (!is_callable('smarty_function_text')) include '/home/kairat/www/private/ow_smarty/plugin/function.text.php';
?><?php if (!empty($_smarty_tpl->tpl_vars['base404RedirectMessage']->value)){?><?php echo $_smarty_tpl->tpl_vars['base404RedirectMessage']->value;?>
<?php }else{ ?><?php echo smarty_function_text(array('key'=>'base+base_document_404'),$_smarty_tpl);?>
<?php }?>
<?php }} ?>