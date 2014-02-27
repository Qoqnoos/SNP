<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:53
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/console_list.html" */ ?>
<?php /*%%SmartyHeaderCode:127845250952ccccc1812cd4-42605004%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'da03fa135132d029e27efa2c6a556a8387882363' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/console_list.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '127845250952ccccc1812cd4-42605004',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'viewAll' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccc184fd90_04469074',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccc184fd90_04469074')) {function content_52ccccc184fd90_04469074($_smarty_tpl) {?><div class="ow_console_list_wrapper OW_ConsoleListContainer">
    <div class="ow_nocontent OW_ConsoleListNoContent">No items</div>
    <ul class="ow_console_list OW_ConsoleList">

    </ul>
    <div class="ow_preloader_content ow_console_list_preloader OW_ConsoleListPreloader" style="visibility: hidden"></div>
</div>

<?php if (!empty($_smarty_tpl->tpl_vars['viewAll']->value)){?>
    <div class="ow_console_view_all_btn_wrap"><a href="<?php echo $_smarty_tpl->tpl_vars['viewAll']->value['url'];?>
" class="ow_console_view_all_btn"><?php echo $_smarty_tpl->tpl_vars['viewAll']->value['label'];?>
</a></div>
<?php }?>
<?php }} ?>