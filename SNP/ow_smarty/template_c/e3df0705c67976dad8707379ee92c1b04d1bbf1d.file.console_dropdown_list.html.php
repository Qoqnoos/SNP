<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:53
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/console_dropdown_list.html" */ ?>
<?php /*%%SmartyHeaderCode:60782250452ccccc1858261-97925577%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e3df0705c67976dad8707379ee92c1b04d1bbf1d' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/console_dropdown_list.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '60782250452ccccc1858261-97925577',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'label' => 0,
    'counter' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccc18c7b81_07533168',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccc18c7b81_07533168')) {function content_52ccccc18c7b81_07533168($_smarty_tpl) {?><a href="javascript://" class="ow_console_item_link"><?php echo $_smarty_tpl->tpl_vars['label']->value;?>
</a>

<span <?php if (empty($_smarty_tpl->tpl_vars['counter']->value['number'])){?>style="display: none;"<?php }?> class="ow_count_wrap OW_ConsoleItemCounter" >
    <span class="<?php if ($_smarty_tpl->tpl_vars['counter']->value['active']){?>ow_count_active<?php }?> ow_count_bg OW_ConsoleItemCounterPlace">
        <span class="ow_count OW_ConsoleItemCounterNumber" <?php if (empty($_smarty_tpl->tpl_vars['counter']->value['number'])){?>style="visibility: hidden;"<?php }?>><?php echo $_smarty_tpl->tpl_vars['counter']->value['number'];?>
</span>
    </span>
</span>
<?php }} ?>