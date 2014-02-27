<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:50
         compiled from "/home/kairat/www/private/ow_system_plugins/base/views/components/comments_list.html" */ ?>
<?php /*%%SmartyHeaderCode:195960748052ccccbe8c2378-28434017%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4b93847cd9e6b187ceb59922224fa7d65d5afc2e' => 
    array (
      0 => '/home/kairat/www/private/ow_system_plugins/base/views/components/comments_list.html',
      1 => 1384763008,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '195960748052ccccbe8c2378-28434017',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'cmpContext' => 0,
    'noComments' => 0,
    'viewAllLink' => 0,
    'comments' => 0,
    'comment' => 0,
    'pages' => 0,
    'page' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccbea961d0_80545180',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccbea961d0_80545180')) {function content_52ccccbea961d0_80545180($_smarty_tpl) {?><?php if (!is_callable('smarty_block_style')) include '/home/kairat/www/private/ow_smarty/plugin/block.style.php';
if (!is_callable('smarty_function_text')) include '/home/kairat/www/private/ow_smarty/plugin/function.text.php';
if (!is_callable('smarty_function_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/function.decorator.php';
?><?php $_smarty_tpl->smarty->_tag_stack[] = array('style', array()); $_block_repeat=true; echo smarty_block_style(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>


.ow_comments_list .ow_attachment{
    padding-top:10px;
}
.ow_comments_list .comments_view_all{
    text-align:right;
    font-size:10px;
    height:15px;
}
.ow_comments_item .cnx_action{
    display:none;
}

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_style(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<div id="<?php echo $_smarty_tpl->tpl_vars['cmpContext']->value;?>
">
   <div class="ow_comments_list ow_std_margin">
   		<?php if (isset($_smarty_tpl->tpl_vars['noComments']->value)){?>
   		<div class="ow_nocontent"><?php echo smarty_function_text(array('key'=>"base+comment_no_comments"),$_smarty_tpl);?>
</div>
   		<?php }else{ ?>
        <?php if (!empty($_smarty_tpl->tpl_vars['viewAllLink']->value)){?><div class="comments_view_all"><a href="javascript://"><?php echo $_smarty_tpl->tpl_vars['viewAllLink']->value;?>
</a></div><?php }?>
      <?php  $_smarty_tpl->tpl_vars['comment'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['comment']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['comments']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['comment']->key => $_smarty_tpl->tpl_vars['comment']->value){
$_smarty_tpl->tpl_vars['comment']->_loop = true;
?>
      <div class="ow_comments_item ow_smallmargin clearfix">
			<div class="cnx_action"><?php echo $_smarty_tpl->tpl_vars['comment']->value['cnxAction'];?>
</div>
			<div class="ow_comments_item_picture">
                <?php echo smarty_function_decorator(array('name'=>'avatar_item','data'=>$_smarty_tpl->tpl_vars['comment']->value['avatar']),$_smarty_tpl);?>

            </div>
			<div class="ow_comments_item_info">
				<span class="<?php if (!empty($_smarty_tpl->tpl_vars['comment']->value['cnxAction'])){?>ow_comments_date_hover <?php }?>ow_comments_date ow_nowrap ow_tiny ow_remark"><?php echo $_smarty_tpl->tpl_vars['comment']->value['date'];?>
</span>
				<div class="ow_comments_item_header"><a href="<?php echo $_smarty_tpl->tpl_vars['comment']->value['profileUrl'];?>
"><?php echo $_smarty_tpl->tpl_vars['comment']->value['displayName'];?>
</a></div>
				<span class="comment_arr"></span>
				<div class="ow_comments_item_content">
                    <div class="ow_comments_content ow_smallmargin"><?php echo $_smarty_tpl->tpl_vars['comment']->value['content'];?>
</div><?php echo $_smarty_tpl->tpl_vars['comment']->value['content_add'];?>

                </div>
			</div>
		</div>
      <?php } ?>
      <?php if (!empty($_smarty_tpl->tpl_vars['pages']->value)){?>
         <div class="ow_paging clearfix ow_stdmargin">
         	<span><?php echo smarty_function_text(array('key'=>'base+pages_label'),$_smarty_tpl);?>
</span>
            <?php  $_smarty_tpl->tpl_vars['page'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['page']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['pages']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['page']->key => $_smarty_tpl->tpl_vars['page']->value){
$_smarty_tpl->tpl_vars['page']->_loop = true;
?>
            <?php if (!isset($_smarty_tpl->tpl_vars['page']->value['pageIndex'])){?>
            <span><?php echo $_smarty_tpl->tpl_vars['page']->value['label'];?>
</span>
            <?php }else{ ?>
            <a href="javascript://" class="page-<?php echo $_smarty_tpl->tpl_vars['page']->value['pageIndex'];?>
<?php if (isset($_smarty_tpl->tpl_vars['page']->value['active'])&&$_smarty_tpl->tpl_vars['page']->value['active']){?> active<?php }?>"><?php echo $_smarty_tpl->tpl_vars['page']->value['label'];?>
</a>
            <?php }?>
            <?php } ?>
         </div>
      <?php }?>
      <?php }?>
   </div>
</div>
<?php }} ?>