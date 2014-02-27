<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:52
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/custom_html_widget.html" */ ?>
<?php /*%%SmartyHeaderCode:87414975552ccccc0e5b554-55047378%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3d40f155b6c47b59d3125d08ba625bac059fac85' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/custom_html_widget.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '87414975552ccccc0e5b554-55047378',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'content' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccc0eb02b0_47816793',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccc0eb02b0_47816793')) {function content_52ccccc0eb02b0_47816793($_smarty_tpl) {?><?php if (!is_callable('smarty_function_text')) include '/home/kairat/www/private/ow_smarty/plugin/function.text.php';
?><div class="ow_custom_html_widget">
	<?php if ($_smarty_tpl->tpl_vars['content']->value){?>
		<?php echo $_smarty_tpl->tpl_vars['content']->value;?>

	<?php }else{ ?>
            <div class="ow_nocontent">
                <?php echo smarty_function_text(array('key'=>"base+custom_html_widget_no_content"),$_smarty_tpl);?>

            </div>
	<?php }?>
</div><?php }} ?>