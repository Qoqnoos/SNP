<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:50
         compiled from "/home/kairat/www/private/ow_plugins/newsfeed/views/components/feed_list.html" */ ?>
<?php /*%%SmartyHeaderCode:195225879252ccccbe7113c4-86006566%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '91c2050203ad780a3d6f343acd76a78fd43d308e' => 
    array (
      0 => '/home/kairat/www/private/ow_plugins/newsfeed/views/components/feed_list.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '195225879252ccccbe7113c4-86006566',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'feed' => 0,
    'item' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccbe77c4b2_82899996',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccbe77c4b2_82899996')) {function content_52ccccbe77c4b2_82899996($_smarty_tpl) {?><?php if (!is_callable('smarty_function_text')) include '/home/kairat/www/private/ow_smarty/plugin/function.text.php';
?><li <?php if (count($_smarty_tpl->tpl_vars['feed']->value)){?>style="display: none;"<?php }?> class="ow_newsfeed_item ow_nocontent newsfeed_nocontent"><?php echo smarty_function_text(array('key'=>"newsfeed+empty_feed_message"),$_smarty_tpl);?>
</li>

<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['feed']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['item']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['item']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['item']->iteration++;
 $_smarty_tpl->tpl_vars['item']->last = $_smarty_tpl->tpl_vars['item']->iteration === $_smarty_tpl->tpl_vars['item']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['feed']['last'] = $_smarty_tpl->tpl_vars['item']->last;
?>
    <?php echo $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['newsfeed_item'][0][0]->tplRenderItem(array('action'=>$_smarty_tpl->tpl_vars['item']->value,'lastItem'=>$_smarty_tpl->getVariable('smarty')->value['foreach']['feed']['last']),$_smarty_tpl);?>

<?php } ?>
<?php }} ?>