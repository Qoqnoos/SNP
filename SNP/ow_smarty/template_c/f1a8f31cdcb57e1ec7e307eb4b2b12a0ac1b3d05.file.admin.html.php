<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 20:02:51
         compiled from "/home/kairat/www/private/ow_themes/origin/master_pages/admin.html" */ ?>
<?php /*%%SmartyHeaderCode:195883949052cccdeb12c4f9-28707091%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f1a8f31cdcb57e1ec7e307eb4b2b12a0ac1b3d05' => 
    array (
      0 => '/home/kairat/www/private/ow_themes/origin/master_pages/admin.html',
      1 => 1378981014,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '195883949052cccdeb12c4f9-28707091',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'siteUrl' => 0,
    'siteName' => 0,
    'menuArr' => 0,
    'item' => 0,
    'key' => 0,
    'submenu' => 0,
    'warnings' => 0,
    'warning' => 0,
    'notifications' => 0,
    'notification' => 0,
    'heading' => 0,
    'heading_icon_class' => 0,
    'content' => 0,
    'ow_plugin_xp' => 0,
    'softVersion' => 0,
    'bottomPoweredByLink' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52cccdeb2fcae9_56909743',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52cccdeb2fcae9_56909743')) {function content_52cccdeb2fcae9_56909743($_smarty_tpl) {?><?php if (!is_callable('smarty_block_style')) include '/home/kairat/www/private/ow_smarty/plugin/block.style.php';
if (!is_callable('smarty_function_component')) include '/home/kairat/www/private/ow_smarty/plugin/function.component.php';
if (!is_callable('smarty_function_url_for_route')) include '/home/kairat/www/private/ow_smarty/plugin/function.url_for_route.php';
if (!is_callable('smarty_function_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/function.decorator.php';
?><?php $_smarty_tpl->smarty->_tag_stack[] = array('style', array()); $_block_repeat=true; echo smarty_block_style(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>


.admin_notification, .admin_warning{
    text-align: center;
	margin-bottom:10px;
	padding: 10px 0;
	border-radius: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
    background-color: #dfd;
	color: #444;
	text-shadow: #fff 1px 1px 0;
}

.admin_warning{
    background-color: #fdd;
}

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_style(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<div class="ow_header ow_admin_header clearfix">
	<?php echo smarty_function_component(array('class'=>'BASE_CMP_Console'),$_smarty_tpl);?>

	<div class="ow_site_panel">
		<div class="ow_logo_wrap">
			<a href="<?php echo $_smarty_tpl->tpl_vars['siteUrl']->value;?>
" class="ow_tagline"><?php echo $_smarty_tpl->tpl_vars['siteName']->value;?>
</a>
			<a href="<?php echo smarty_function_url_for_route(array('for'=>"admin_default"),$_smarty_tpl);?>
" class="ow_admin_title">Admin Area</a>
		</div>
	</div>
	<div class="ow_canvas">
		<div class="ow_page">
			<div class="admin_menu_cont clearfix">
			<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['menuArr']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
				<div id="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" class="menu_item<?php if ($_smarty_tpl->tpl_vars['item']->value['isActive']){?> active<?php }?>">
					<span class="icon <?php echo $_smarty_tpl->tpl_vars['key']->value;?>
">
						<span class="label"><?php echo $_smarty_tpl->tpl_vars['item']->value['label'];?>
</span>
						<span class="menu_items ow_tooltip">
						<div class="menu_cont"><?php echo $_smarty_tpl->tpl_vars['item']->value['cmp'];?>
</div>
						</span>
					</span>
				</div>
			<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php if (!empty($_smarty_tpl->tpl_vars['submenu']->value)){?><div class="ow_admin_sub_menu"><div class="ow_canvas"><?php echo $_smarty_tpl->tpl_vars['submenu']->value;?>
</div></div><?php }?>
<div class="ow_page_container ow_admin">
	<div class="ow_canvas">
		<div class="ow_page">
			<?php if (!empty($_smarty_tpl->tpl_vars['warnings']->value)){?>
			<?php  $_smarty_tpl->tpl_vars['warning'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['warning']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['warnings']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['warning']->key => $_smarty_tpl->tpl_vars['warning']->value){
$_smarty_tpl->tpl_vars['warning']->_loop = true;
?>
				<div class="admin_warning"><?php echo $_smarty_tpl->tpl_vars['warning']->value;?>
</div>
			<?php } ?>
			<?php }?>
			<?php if (!empty($_smarty_tpl->tpl_vars['notifications']->value)){?>
			<?php  $_smarty_tpl->tpl_vars['notification'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['notification']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['notifications']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['notification']->key => $_smarty_tpl->tpl_vars['notification']->value){
$_smarty_tpl->tpl_vars['notification']->_loop = true;
?>
				<div class="admin_notification"><?php echo $_smarty_tpl->tpl_vars['notification']->value;?>
</div>
			<?php } ?>
			<br />
			<?php }?>
			<?php if (!empty($_smarty_tpl->tpl_vars['heading']->value)){?><h1 class="ow_stdmargin <?php echo $_smarty_tpl->tpl_vars['heading_icon_class']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['heading']->value;?>
</h1><?php }?>
			<div class="ow_content">
				<?php echo $_smarty_tpl->tpl_vars['content']->value;?>

			</div>
		</div>
	</div>
</div>
<div class="ow_footer ow_admin">
	<div class="ow_canvas">
		<div class="ow_page clearfix" style="padding:20px 0;">
			<div style="float:left;">
				<?php if (empty($_smarty_tpl->tpl_vars['ow_plugin_xp']->value)){?><div class="ow_right ow_std_margin ow_small ow_remark"><?php echo $_smarty_tpl->tpl_vars['softVersion']->value;?>
</div><?php }?>
			</div>
			<div style="float:right;line-height:15px;font-size:11px;padding-bottom: 30px;">
				<?php echo $_smarty_tpl->tpl_vars['bottomPoweredByLink']->value;?>

			</div>
		</div>
	</div>
</div>    
<?php echo smarty_function_decorator(array('name'=>'floatbox'),$_smarty_tpl);?>
<?php }} ?>