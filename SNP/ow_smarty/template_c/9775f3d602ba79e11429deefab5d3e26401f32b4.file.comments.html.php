<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:50
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/comments.html" */ ?>
<?php /*%%SmartyHeaderCode:87657481052ccccbea9e2e0-01456167%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9775f3d602ba79e11429deefab5d3e26401f32b4' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/comments.html',
      1 => 1378981013,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '87657481052ccccbea9e2e0-01456167',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'cmpContext' => 0,
    'displayType' => 0,
    'commentList' => 0,
    'formCmp' => 0,
    'currentUserInfo' => 0,
    'authErrorMessage' => 0,
    'wrapInBox' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccbebf8d18_35078141',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccbebf8d18_35078141')) {function content_52ccccbebf8d18_35078141($_smarty_tpl) {?><?php if (!is_callable('smarty_block_style')) include '/home/kairat/www/private/ow_smarty/plugin/block.style.php';
if (!is_callable('smarty_function_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/function.decorator.php';
if (!is_callable('smarty_function_text')) include '/home/kairat/www/private/ow_smarty/plugin/function.text.php';
if (!is_callable('smarty_block_block_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/block.block_decorator.php';
?><?php $_smarty_tpl->smarty->_tag_stack[] = array('style', array()); $_block_repeat=true; echo smarty_block_style(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>


.ow_add_comments_form .ow_submit_auto_click .item_loaded{
    margin-bottom:4px;
}
.comments_fake_autoclick {
    height:20px;
}

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_style(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<div id="<?php echo $_smarty_tpl->tpl_vars['cmpContext']->value;?>
">
    <?php $_smarty_tpl->_capture_stack[0][] = array('comments', null, null); ob_start(); ?>
    <?php if ($_smarty_tpl->tpl_vars['displayType']->value==1||$_smarty_tpl->tpl_vars['displayType']->value==3||$_smarty_tpl->tpl_vars['displayType']->value==4){?>
   <div class="comments_list_cont">
	   <?php echo $_smarty_tpl->tpl_vars['commentList']->value;?>

   </div>
   <?php }?>
   	<?php if (isset($_smarty_tpl->tpl_vars['formCmp']->value)){?>
        <div class="clearfix base_cmnt_mark ow_smallmargin">
          <div class="ow_comments_item_picture"><?php echo smarty_function_decorator(array('name'=>'avatar_item','data'=>$_smarty_tpl->tpl_vars['currentUserInfo']->value),$_smarty_tpl);?>
</div>
          <div class="ow_comments_item_info clearfix"><span class="comment_add_arr"></span><textarea class="comments_fake_autoclick invitation"><?php echo smarty_function_text(array('key'=>'base+comment_form_element_invitation_text'),$_smarty_tpl);?>
</textarea></div>
      </div>
      <div class="base_cmnts_temp_cont ow_smallmargin" style="display:none"></div>
    <?php }else{ ?>
      <div class="ow_smallmargin ow_center ow_comments_msg"><?php echo $_smarty_tpl->tpl_vars['authErrorMessage']->value;?>
</div>
   <?php }?>
   <?php if ($_smarty_tpl->tpl_vars['displayType']->value==2){?>
   <div class="comments_list_cont">
	   <?php echo $_smarty_tpl->tpl_vars['commentList']->value;?>

   </div>
   <?php }?>
   <?php list($_capture_buffer, $_capture_assign, $_capture_append) = array_pop($_smarty_tpl->_capture_stack[0]);
if (!empty($_capture_buffer)) {
 if (isset($_capture_assign)) $_smarty_tpl->assign($_capture_assign, ob_get_contents());
 if (isset( $_capture_append)) $_smarty_tpl->append( $_capture_append, ob_get_contents());
 Smarty::$_smarty_vars['capture'][$_capture_buffer]=ob_get_clean();
} else $_smarty_tpl->capture_error();?>
   <?php if ($_smarty_tpl->tpl_vars['wrapInBox']->value){?>
   <?php $_smarty_tpl->smarty->_tag_stack[] = array('block_decorator', array('name'=>'box','type'=>'empty','addClass'=>'ow_add_comments_form ow_stdmargin','langLabel'=>'base+comment_box_cap_label','iconClass'=>'ow_ic_comment')); $_block_repeat=true; echo smarty_block_block_decorator(array('name'=>'box','type'=>'empty','addClass'=>'ow_add_comments_form ow_stdmargin','langLabel'=>'base+comment_box_cap_label','iconClass'=>'ow_ic_comment'), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

   <?php echo Smarty::$_smarty_vars['capture']['comments'];?>

   <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_block_decorator(array('name'=>'box','type'=>'empty','addClass'=>'ow_add_comments_form ow_stdmargin','langLabel'=>'base+comment_box_cap_label','iconClass'=>'ow_ic_comment'), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

   <?php }else{ ?>
   <?php echo Smarty::$_smarty_vars['capture']['comments'];?>

   <?php }?>
</div><?php }} ?>