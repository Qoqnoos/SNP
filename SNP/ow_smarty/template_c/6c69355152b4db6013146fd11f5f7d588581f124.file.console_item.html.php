<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:53
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/console_item.html" */ ?>
<?php /*%%SmartyHeaderCode:84974370452ccccc175af03-00896353%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6c69355152b4db6013146fd11f5f7d588581f124' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/console_item.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '84974370452ccccc175af03-00896353',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'item' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccc17f9c29_25190659',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccc17f9c29_25190659')) {function content_52ccccc17f9c29_25190659($_smarty_tpl) {?><?php if (!is_callable('smarty_block_block_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/block.block_decorator.php';
?><div id="<?php echo $_smarty_tpl->tpl_vars['item']->value['uniqId'];?>
" class="ow_console_item <?php echo $_smarty_tpl->tpl_vars['item']->value['class'];?>
" <?php if ($_smarty_tpl->tpl_vars['item']->value['hidden']){?>style="display: none;"<?php }?>>
    <?php echo $_smarty_tpl->tpl_vars['item']->value['html'];?>

    <?php if (!empty($_smarty_tpl->tpl_vars['item']->value['content'])){?>
        <div id="<?php echo $_smarty_tpl->tpl_vars['item']->value['content']['uniqId'];?>
" class="OW_ConsoleItemContent" style="display: none;">

            <?php $_smarty_tpl->smarty->_tag_stack[] = array('block_decorator', array('name'=>"tooltip",'addClass'=>"console_tooltip ow_tooltip_top_right")); $_block_repeat=true; echo smarty_block_block_decorator(array('name'=>"tooltip",'addClass'=>"console_tooltip ow_tooltip_top_right"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                <?php echo $_smarty_tpl->tpl_vars['item']->value['content']['html'];?>

            <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_block_decorator(array('name'=>"tooltip",'addClass'=>"console_tooltip ow_tooltip_top_right"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>


        </div>
    <?php }?>
</div><?php }} ?>