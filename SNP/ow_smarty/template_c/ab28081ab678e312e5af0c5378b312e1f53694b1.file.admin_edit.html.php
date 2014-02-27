<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 20:02:50
         compiled from "/home/kairat/www/private/ow_plugins/advertisement/views/controllers/admin_edit.html" */ ?>
<?php /*%%SmartyHeaderCode:116701269052cccdeae44440-99366638%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ab28081ab678e312e5af0c5378b312e1f53694b1' => 
    array (
      0 => '/home/kairat/www/private/ow_plugins/advertisement/views/controllers/admin_edit.html',
      1 => 1384763006,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '116701269052cccdeae44440-99366638',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'menu' => 0,
    'locDisabled' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52cccdeaf07c00_15591312',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52cccdeaf07c00_15591312')) {function content_52cccdeaf07c00_15591312($_smarty_tpl) {?><?php if (!is_callable('smarty_block_form')) include '/home/kairat/www/private/ow_smarty/plugin/block.form.php';
if (!is_callable('smarty_function_cycle')) include '/home/kairat/www/private/ow_libraries/smarty3/plugins/function.cycle.php';
if (!is_callable('smarty_function_label')) include '/home/kairat/www/private/ow_smarty/plugin/function.label.php';
if (!is_callable('smarty_function_input')) include '/home/kairat/www/private/ow_smarty/plugin/function.input.php';
if (!is_callable('smarty_function_error')) include '/home/kairat/www/private/ow_smarty/plugin/function.error.php';
if (!is_callable('smarty_function_desc')) include '/home/kairat/www/private/ow_smarty/plugin/function.desc.php';
if (!is_callable('smarty_function_submit')) include '/home/kairat/www/private/ow_smarty/plugin/function.submit.php';
?><?php echo $_smarty_tpl->tpl_vars['menu']->value;?>

<div class="ow_superwide ow_automargin ow_stdmargin">
<?php $_smarty_tpl->smarty->_tag_stack[] = array('form', array('name'=>'banner_edit_form')); $_block_repeat=true; echo smarty_block_form(array('name'=>'banner_edit_form'), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

<table class="ow_table_1 ow_form">
    <tr class="ow_tr_first <?php echo smarty_function_cycle(array('values'=>'ow_alt2,ow_alt1'),$_smarty_tpl);?>
">
		<td class="ow_label"><?php echo smarty_function_label(array('name'=>'title'),$_smarty_tpl);?>
</td>
		<td class="ow_value"><?php echo smarty_function_input(array('name'=>'title'),$_smarty_tpl);?>
<br /><?php echo smarty_function_error(array('name'=>'title'),$_smarty_tpl);?>
</td>
		<td class="ow_desc"></td>
	</tr>
    <tr class="<?php echo smarty_function_cycle(array('values'=>'ow_alt2,ow_alt1'),$_smarty_tpl);?>
">
		<td class="ow_label"><?php echo smarty_function_label(array('name'=>'code'),$_smarty_tpl);?>
</td>
		<td class="ow_value"><?php echo smarty_function_input(array('name'=>'code'),$_smarty_tpl);?>
<br /><?php echo smarty_function_error(array('name'=>'code'),$_smarty_tpl);?>
</td>
		<td class="ow_desc"><?php echo smarty_function_desc(array('name'=>'code'),$_smarty_tpl);?>
</td>
	</tr>
    <?php if (empty($_smarty_tpl->tpl_vars['locDisabled']->value)){?>
    <tr class="ow_tr_last <?php echo smarty_function_cycle(array('values'=>'ow_alt2,ow_alt1'),$_smarty_tpl);?>
">
		<td class="ow_label"><?php echo smarty_function_label(array('name'=>'select_country'),$_smarty_tpl);?>
</td>
		<td class="ow_value"><?php echo smarty_function_input(array('name'=>'select_country'),$_smarty_tpl);?>
<br /><?php echo smarty_function_error(array('name'=>'select_country'),$_smarty_tpl);?>
</td>
		<td class="ow_desc"><?php echo smarty_function_desc(array('name'=>'select_country'),$_smarty_tpl);?>
</td>
	</tr>
    <?php }?>
</table>
<div class="clearfix ow_stdmargin">
	<div class="ow_right">
		<?php echo smarty_function_submit(array('name'=>'submit'),$_smarty_tpl);?>

	</div>
</div>

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_form(array('name'=>'banner_edit_form'), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

</div><?php }} ?>