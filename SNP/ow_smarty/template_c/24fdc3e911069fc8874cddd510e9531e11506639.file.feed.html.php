<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:52
         compiled from "/home/kairat/www/private/ow_plugins/newsfeed/views/components/feed.html" */ ?>
<?php /*%%SmartyHeaderCode:48673865052ccccc0017419-67208777%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '24fdc3e911069fc8874cddd510e9531e11506639' => 
    array (
      0 => '/home/kairat/www/private/ow_plugins/newsfeed/views/components/feed.html',
      1 => 1384763006,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '48673865052ccccc0017419-67208777',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'autoId' => 0,
    'statusMessage' => 0,
    'status' => 0,
    'list' => 0,
    'viewMore' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccc00b9992_46313816',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccc00b9992_46313816')) {function content_52ccccc00b9992_46313816($_smarty_tpl) {?><?php if (!is_callable('smarty_block_style')) include '/home/kairat/www/private/ow_smarty/plugin/block.style.php';
if (!is_callable('smarty_function_decorator')) include '/home/kairat/www/private/ow_smarty/plugin/function.decorator.php';
?><?php $_smarty_tpl->smarty->_tag_stack[] = array('style', array()); $_block_repeat=true; echo smarty_block_style(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>


    ul.ow_newsfeed {
        padding: 5px 0px 0px 5px;
    }

    .ow_newsfeed_avatar {
        height: 45px;
        width: 45px;
        margin-right: -45px;
        float: left;
    }

    .ow_newsfeed_avatar img {
        height: 45px;
        width: 45px;
    }

    .ow_newsfeed_body {
        margin-left: 45px;
        padding-left: 10px;
    }

    .ow_newsfeed .ow_newsfeed_item {
       list-style-image: none;
        position: relative;
    }

    .ow_newsfeed_toolbar {
        float: none;
    }

    .ow_newsfeed .ow_comments_list {
        margin-bottom: 0px;
    }

    .ow_newsfeed_remove {
        position: absolute;
        top: 5px;
        right: 0px;
        display: none;
    }

    .ow_newsfeed_body:hover .ow_newsfeed_remove {
        display: block;
    }

    .ow_newsfeed_delimiter {
        border-bottom-width: 1px;
        height: 1px;
        margin-bottom: 7px;
    }

    .ow_newsfeed_doublesided_stdmargin {
        margin: 14px 0px;
    }

    .ow_newsfeed_likes {
        margin-bottom: 3px;
    }

    .ow_newsfeed_tooltip .tail {
        padding-left: 25px;
    }

    .ow_newsfeed_placeholder {
        height: 30px;
        background-position: center 5px;
    }

    .ow_newsfeed_view_more_c {
        text-align: center;
    }

    .ow_newsfeed_string {
    	max-width: 600px;
    }

    .ow_newsfeed_item_picture {
        width: 28%;
        float: left;
        max-width: 100px;
        min-width: 70px;
        margin-right: 1%;
    }

    .ow_newsfeed_large_image .ow_newsfeed_item_picture {
        width: 38%;
        max-width: 150px;
    }

    .ow_newsfeed_large_image .ow_newsfeed_item_content {
        width: 60%;
        max-width: 390px;
    }

    .ow_newsfeed_item_picture img {
        width: 100%;
    }

    .ow_newsfeed_item_content {
        float: left;
        min-width: 50px;
        width: 69%;
        max-width: 440px;
    }

    .ow_newsfeed_features {
        max-width: 450px;
        overflow: hidden;
    }

    .ow_newsfeed_feedback_counter {
        padding: 2px 5px;
    }

    .ow_newsfeed_activity_content {
        border-top-style: dashed;
        border-top-width: 1px;
        padding-top: 3px;
    }

    .ow_newsfeed_comments .ow_comment_textarea {
        height: 50px;
    }

    .ow_newsfeed_comments .ow_add_comments_form
    {
        margin-bottom: 0px;
    }

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_style(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<div id="<?php echo $_smarty_tpl->tpl_vars['autoId']->value;?>
">
    <?php if (!empty($_smarty_tpl->tpl_vars['statusMessage']->value)){?>
        <div class="ow_smallmargin ow_center">
            <?php echo $_smarty_tpl->tpl_vars['statusMessage']->value;?>

        </div>
    <?php }elseif(!empty($_smarty_tpl->tpl_vars['status']->value)){?>
        <div class="ow_smallmargin">
            <?php echo $_smarty_tpl->tpl_vars['status']->value;?>

        </div>
    <?php }?>
    
    <ul class="ow_newsfeed ow_smallmargin">
        <?php echo $_smarty_tpl->tpl_vars['list']->value;?>

    </ul>
    
    <?php if ($_smarty_tpl->tpl_vars['viewMore']->value){?>
            <div class="ow_newsfeed_view_more_c">
                <?php echo smarty_function_decorator(array('name'=>"button",'class'=>"ow_newsfeed_view_more ow_ic_down_arrow",'langLabel'=>"newsfeed+feed_view_more_btn"),$_smarty_tpl);?>

            </div>
    <?php }?>
</div><?php }} ?>