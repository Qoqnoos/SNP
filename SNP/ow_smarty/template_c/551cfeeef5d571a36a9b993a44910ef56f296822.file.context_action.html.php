<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:50
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/context_action.html" */ ?>
<?php /*%%SmartyHeaderCode:170331284152ccccbed4d3b8-66057996%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '551cfeeef5d571a36a9b993a44910ef56f296822' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/context_action.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '170331284152ccccbed4d3b8-66057996',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'actions' => 0,
    'a' => 0,
    'aname' => 0,
    'attr' => 0,
    'tooltipClass' => 0,
    'position' => 0,
    'subact' => 0,
    'name' => 0,
    'sattr' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccbf49a5b5_64370091',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccbf49a5b5_64370091')) {function content_52ccccbf49a5b5_64370091($_smarty_tpl) {?><?php if (!is_callable('smarty_block_block_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/block.block_decorator.php';
?><div class="ow_context_action_block clearfix">
    <?php  $_smarty_tpl->tpl_vars['a'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['a']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['actions']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['a']->key => $_smarty_tpl->tpl_vars['a']->value){
$_smarty_tpl->tpl_vars['a']->_loop = true;
?>
        <div class="ow_context_action">
			<?php if ($_smarty_tpl->tpl_vars['a']->value['action']->getLabel()){?>
			<a href="<?php if ($_smarty_tpl->tpl_vars['a']->value['action']->getUrl()!=null){?><?php echo $_smarty_tpl->tpl_vars['a']->value['action']->getUrl();?>
<?php }else{ ?>javascript://<?php }?>"<?php if ($_smarty_tpl->tpl_vars['a']->value['action']->getId()!=null){?> id="<?php echo $_smarty_tpl->tpl_vars['a']->value['action']->getId();?>
"<?php }?><?php if ($_smarty_tpl->tpl_vars['a']->value['action']->getAttributes()){?><?php  $_smarty_tpl->tpl_vars['attr'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['attr']->_loop = false;
 $_smarty_tpl->tpl_vars['aname'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['a']->value['action']->getAttributes(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['attr']->key => $_smarty_tpl->tpl_vars['attr']->value){
$_smarty_tpl->tpl_vars['attr']->_loop = true;
 $_smarty_tpl->tpl_vars['aname']->value = $_smarty_tpl->tpl_vars['attr']->key;
?> <?php echo $_smarty_tpl->tpl_vars['aname']->value;?>
="<?php echo $_smarty_tpl->tpl_vars['attr']->value;?>
"<?php } ?><?php }?>class="ow_context_action_value<?php if ($_smarty_tpl->tpl_vars['a']->value['action']->getClass()!=null){?> <?php echo $_smarty_tpl->tpl_vars['a']->value['action']->getClass();?>
<?php }?>"><?php echo $_smarty_tpl->tpl_vars['a']->value['action']->getLabel();?>
</a><?php }?>
			<?php if (!empty($_smarty_tpl->tpl_vars['a']->value['subactions'])){?>
			<span class="ow_context_more"></span>

			<!-- div class="ow_context_action_wrap" -->
			    <?php $_smarty_tpl->_capture_stack[0][] = array('default', 'tooltipClass', null); ob_start(); ?><?php if ($_smarty_tpl->tpl_vars['a']->value['action']->getClass()!=null){?><?php echo $_smarty_tpl->tpl_vars['a']->value['action']->getClass();?>
_tooltip<?php }?><?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
			    
				<?php $_smarty_tpl->smarty->_tag_stack[] = array('block_decorator', array('name'=>'tooltip','addClass'=>((string)$_smarty_tpl->tpl_vars['tooltipClass']->value)." ow_small ".((string)$_smarty_tpl->tpl_vars['position']->value))); $_block_repeat=true; echo smarty_block_block_decorator(array('name'=>'tooltip','addClass'=>((string)$_smarty_tpl->tpl_vars['tooltipClass']->value)." ow_small ".((string)$_smarty_tpl->tpl_vars['position']->value)), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

				<ul class="ow_context_action_list ow_border">
				<?php  $_smarty_tpl->tpl_vars['subact'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['subact']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['a']->value['subactions']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['subact']->key => $_smarty_tpl->tpl_vars['subact']->value){
$_smarty_tpl->tpl_vars['subact']->_loop = true;
?>
					<li><a href="<?php if ($_smarty_tpl->tpl_vars['subact']->value->getUrl()!=null){?><?php echo $_smarty_tpl->tpl_vars['subact']->value->getUrl();?>
<?php }else{ ?>javascript://<?php }?>"<?php if ($_smarty_tpl->tpl_vars['subact']->value->getId()!=null){?> id="<?php echo $_smarty_tpl->tpl_vars['subact']->value->getId();?>
"<?php }?><?php if ($_smarty_tpl->tpl_vars['subact']->value->getClass()!=null){?> class="<?php echo $_smarty_tpl->tpl_vars['subact']->value->getClass();?>
"<?php }?><?php if ($_smarty_tpl->tpl_vars['subact']->value->getAttributes()){?><?php  $_smarty_tpl->tpl_vars['sattr'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['sattr']->_loop = false;
 $_smarty_tpl->tpl_vars['name'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['subact']->value->getAttributes(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['sattr']->key => $_smarty_tpl->tpl_vars['sattr']->value){
$_smarty_tpl->tpl_vars['sattr']->_loop = true;
 $_smarty_tpl->tpl_vars['name']->value = $_smarty_tpl->tpl_vars['sattr']->key;
?> <?php echo $_smarty_tpl->tpl_vars['name']->value;?>
="<?php echo $_smarty_tpl->tpl_vars['sattr']->value;?>
"<?php } ?><?php }?>><?php echo $_smarty_tpl->tpl_vars['subact']->value->getLabel();?>
</a></li>
				<?php } ?>
				</ul>
                <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_block_decorator(array('name'=>'tooltip','addClass'=>((string)$_smarty_tpl->tpl_vars['tooltipClass']->value)." ow_small ".((string)$_smarty_tpl->tpl_vars['position']->value)), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

			<!-- /div -->
			<?php }?>
        </div>
    <?php } ?>
</div><?php }} ?>