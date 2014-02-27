<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 20:03:05
         compiled from "/home/kairat/www/private/ow_plugins/advertisement/views/controllers/admin_index.html" */ ?>
<?php /*%%SmartyHeaderCode:112141283852cccdf9788f38-79129141%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '58c2f60060f05f36d538bd1672f4f34cc99fd13a' => 
    array (
      0 => '/home/kairat/www/private/ow_plugins/advertisement/views/controllers/admin_index.html',
      1 => 1378981012,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '112141283852cccdf9788f38-79129141',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'menu' => 0,
    'addUrl' => 0,
    'banners' => 0,
    'banner' => 0,
    'loc' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52cccdf992c0f9_13514582',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52cccdf992c0f9_13514582')) {function content_52cccdf992c0f9_13514582($_smarty_tpl) {?><?php if (!is_callable('smarty_block_style')) include '/home/kairat/www/private/ow_smarty/plugin/block.style.php';
if (!is_callable('smarty_function_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/function.decorator.php';
if (!is_callable('smarty_block_block_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/block.block_decorator.php';
if (!is_callable('smarty_function_text')) include '/home/kairat/www/private/ow_smarty/plugin/function.text.php';
?><?php $_smarty_tpl->smarty->_tag_stack[] = array('style', array()); $_block_repeat=true; echo smarty_block_style(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>


.ow_banner_list tr{
border-top:1px solid #ccc;
}
.ow_banner_controls{
display:none;
}
.banner_label{
    padding-bottom:5px;
    font-weight:bold;
}
.banner_locations{
    font-size:11px;
    padding-top:10px;
}
.banner_code{
width:550px;
overflow:hidden;
}

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_style(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<?php echo $_smarty_tpl->tpl_vars['menu']->value;?>

<div>
    <div class="ow_stdmargin" style="text-align: right;"><?php echo smarty_function_decorator(array('name'=>'button','langLabel'=>'ads+ads_add_banner','extraString'=>" onclick=\"window.location='".((string)$_smarty_tpl->tpl_vars['addUrl']->value)."'\""),$_smarty_tpl);?>
</div>
    <?php $_smarty_tpl->smarty->_tag_stack[] = array('block_decorator', array('name'=>'box','type'=>'empty','addClass'=>'ow_stdmargin','iconClass'=>'ow_ic_files','langLabel'=>'ads+ads_index_list_box_cap_label')); $_block_repeat=true; echo smarty_block_block_decorator(array('name'=>'box','type'=>'empty','addClass'=>'ow_stdmargin','iconClass'=>'ow_ic_files','langLabel'=>'ads+ads_index_list_box_cap_label'), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

       <table class="ow_banner_list" style="width:100%;">
        <?php  $_smarty_tpl->tpl_vars['banner'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['banner']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['banners']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['banner']->key => $_smarty_tpl->tpl_vars['banner']->value){
$_smarty_tpl->tpl_vars['banner']->_loop = true;
?>
            <tr class="ow_high1 <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['banner']['first']){?>ow_tr_first<?php }?> <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['banner']['last']){?>ow_tr_last<?php }?>" onmouseover="$('span.ow_banner_controls', $(this)).css({display:'block'});" onmouseout="$('span.ow_banner_controls', $(this)).css({display:'none'});">
                <td style="padding: 10px 15px; width: 68%;">
                    <div class="banner_label"><?php echo $_smarty_tpl->tpl_vars['banner']->value['label'];?>
</div>
                    <div class="banner_code"><?php echo $_smarty_tpl->tpl_vars['banner']->value['code'];?>
</div>
             </td>
             <td style="width: 20%;">
                 <div class="banner_locations">
                 <?php if (empty($_smarty_tpl->tpl_vars['banner']->value['location'])){?>
                    <?php echo smarty_function_text(array('key'=>'ads+ads_manage_global_label'),$_smarty_tpl);?>

                 <?php }else{ ?>
                    <ul>
                    <?php  $_smarty_tpl->tpl_vars['loc'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['loc']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['banner']->value['location']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['loc']->key => $_smarty_tpl->tpl_vars['loc']->value){
$_smarty_tpl->tpl_vars['loc']->_loop = true;
?>
                        <li><?php echo $_smarty_tpl->tpl_vars['loc']->value;?>
</li>
                    <?php } ?>
                    </ul>
                 <?php }?>
                 </div>
             </td>
             <td class="ow_small" style="text-align:right;width:12%;vertical-align:middle;">
                 <span class="ow_banner_controls">
                     <a class="ow_lbutton" href="<?php echo $_smarty_tpl->tpl_vars['banner']->value['editUrl'];?>
"><?php echo smarty_function_text(array('key'=>'ads+ads_edit_banner_button_label'),$_smarty_tpl);?>
</a>
                     <a onclick="return confirm('<?php echo smarty_function_text(array('key'=>'ads+ads_delete_banner_confirm_message'),$_smarty_tpl);?>
');" class="ow_lbutton ow_red" href="<?php echo $_smarty_tpl->tpl_vars['banner']->value['deleteUrl'];?>
"><?php echo smarty_function_text(array('key'=>'ads+ads_delete_button_label'),$_smarty_tpl);?>
</a>
                    </span>
             </td>
          </tr>
          <?php } ?>
       </table>
    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_block_decorator(array('name'=>'box','type'=>'empty','addClass'=>'ow_stdmargin','iconClass'=>'ow_ic_files','langLabel'=>'ads+ads_index_list_box_cap_label'), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

</div>
<?php }} ?>