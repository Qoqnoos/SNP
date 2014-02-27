<?php /* Smarty version Smarty-3.1.12, created on 2014-01-07 19:57:53
         compiled from "/home/kairat/www/private/ow_plugins/ajaxim/views/components/toolbar.html" */ ?>
<?php /*%%SmartyHeaderCode:6387584552ccccc12bfd11-47399860%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '7f36c83b15802d2ee8435ea4e6f760957dc7c535' => 
    array (
      0 => '/home/kairat/www/private/ow_plugins/ajaxim/views/components/toolbar.html',
      1 => 1378981012,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '6387584552ccccc12bfd11-47399860',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'avatar_proto_data' => 0,
    'im_sound_url' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_52ccccc1400561_84632006',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_52ccccc1400561_84632006')) {function content_52ccccc1400561_84632006($_smarty_tpl) {?><?php if (!is_callable('smarty_block_form')) include '/home/kairat/www/private/ow_smarty/plugin/block.form.php';
if (!is_callable('smarty_function_text')) include '/home/kairat/www/private/ow_smarty/plugin/function.text.php';
if (!is_callable('smarty_function_input')) include '/home/kairat/www/private/ow_smarty/plugin/function.input.php';
if (!is_callable('smarty_function_label')) include '/home/kairat/www/private/ow_smarty/plugin/function.label.php';
?><div class="ow_chat_cont">
    <div class="ow_chat_wrap">
        <div class="ow_chat">
            <div class="ow_puller"></div>
            <div class="ow_chat_block_wrap ow_border">
                <div class="ow_chat_block">
                    <?php $_smarty_tpl->smarty->_tag_stack[] = array('form', array('name'=>'im_user_settings_form')); $_block_repeat=true; echo smarty_block_form(array('name'=>'im_user_settings_form'), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                    <div class="ow_chat_block_main">
                        <div class="ow_top_panel">
                            <div class="ow_count_block">
                                <a href="javascript://" class="btn2_panel">
                                <span class="ow_count_txt"><?php echo smarty_function_text(array('key'=>"ajaxim+chat"),$_smarty_tpl);?>
</span>
                                <span class="ow_count_wrap">
									<span class="ow_count_bg TotalUserOnlineCountBackground">
										<span class="ow_count TotalUserOnlineCount">0</span>
									</span>
								</span>
                                </a>
                            </div>
                        </div>
                        <div class="ow_chat_in_block">
                            <div class="ow_chat_search">
                                <span><?php echo smarty_function_input(array('name'=>'im_find_contact','id'=>"im_find_contact"),$_smarty_tpl);?>
</span>
                            </div>

                            <div class="ow_chat_list" style="width: 245px;">
                                <div class="ow_chat_preloader"></div>
                                <ul></ul>
                            </div>

                        </div>
                    </div>
                    <div class="ow_bot_panel">
                        <div class="ow_count_block">
                            <a href="javascript://" class="btn2_panel"><span class="ow_count_txt"><?php echo smarty_function_text(array('key'=>"ajaxim+chat"),$_smarty_tpl);?>
</span>
                                <span class="ow_count_wrap">
                                    <span class="ow_count_bg TotalUserOnlineCountBackground">
                                        <span class="ow_count TotalUserOnlineCount">0</span>
                                    </span>
                                </span>
                            </a>
                        </div>
                        <a href="javascript://" class="ow_btn_settings"><span></span></a>
                    </div>
                    <div class="ow_chat_settings ow_tooltip ow_tooltip_bottom_left ow_hidden">
 						<div class="ow_tooltip_body">
                            <ul class="ow_settings_items">
								<li class="ow_settings_item">
                                    <span class="ow_left">
                                        <?php echo smarty_function_input(array('name'=>'im_enable_sound','class'=>"ow_settings_check",'id'=>"im_enable_sound"),$_smarty_tpl);?>

                                        <?php echo smarty_function_label(array('name'=>'im_enable_sound'),$_smarty_tpl);?>

                                    </span>
                                </li>
                            </ul>
						</div>
                        <div class="ow_tooltip_tail"><span></span></div>
                    </div>
                    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_form(array('name'=>'im_user_settings_form'), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="ow_chat_dialog_wrap">

	<div class="ow_chat_selector ow_hidden">
		<div class="ow_chat_block">
			<div class="ow_selector_panel">
				<a class="ow_btn_dialogs" href="javascript://"><span></span></a>
				<span class="ow_dialog_count">0</span>
				<span class="ow_count_wrap" style="display: none;">
					<span class="ow_count_bg ow_count_active">
						<span class="ow_count">0</span>
					</span>
				</span>
			</div>
			<div class="ow_chat_selector_list ow_tooltip ow_tooltip_bottom_left ow_hidden">
				<div class="ow_tooltip_body">
					<ul class="ow_chat_selector_items"></ul>
				</div>
				<div class="ow_tooltip_tail"><span></span></div>
			</div>
		</div>
	</div>

</div>

<div class="ow_chat_notification_wrap"></div>




<div id="ow_chat_prototypes" style="display: none;">
    <div id="ow_chat_list_proto">
        <ul>
            <li>
                <a href="javascript://" class="clearfix ow_chat_item">
                    <span class="ow_chat_item_photo_wrap">
                        <span class="ow_chat_item_photo">
                            <span class="ow_chat_in_item_photo"><img src="<?php echo $_smarty_tpl->tpl_vars['avatar_proto_data']->value['src'];?>
"  width="32px" height="32px" alt="" /></span>
                        </span>
                    </span>
                    <span class="ow_chat_item_author">
                        <span class="ow_chat_in_item_author"></span>
                    </span>
                    <span class="ow_count_wrap ow_count_active" style="display: none;"><span class="ow_count">0</span></span>
                </a>
            </li>
        </ul>
    </div>

    <div id="ow_chat_dialog_proto">
        <div class="ow_chat_block">
            <div class="ow_author_block ow_chat_hover clearfix">
                <div class="ow_puller"></div>
                <a href="#" target="_blank" class="ow_chat_in_item_author_href ow_chat_in_item_photo_wrap">
                    <span class="ow_chat_in_item_photo"><img width="32px" height="32px" alt="" src="<?php echo $_smarty_tpl->tpl_vars['avatar_proto_data']->value['src'];?>
" /></span>
                </a>
                <a href="javascript://" class="ow_chat_item_author_wrap MinimizeMaximizeButton">
                    <span class="ow_chat_item_author">
                        <span class="ow_chat_in_item_author"></span>
                    </span>
                </a>
                <a class="ow_btn_close" href="javascript://"><span></span></a>
            </div>
            <div class="ow_chat_in_dialog">
                <div class="ow_dialog_items_wrap"></div>
                <div class="ow_chat_preloader"></div>
            </div>
            <div class="ow_chat_message_block">
                <span class="ow_chat_in_item_photo"><img width="32px" height="32px" alt="" src="<?php echo $_smarty_tpl->tpl_vars['avatar_proto_data']->value['src'];?>
" /></span>
                <div class="ow_chat_message">
                    <textarea></textarea>
                    <span class="ow_input_tail"></span>
                </div>
            </div>
        </div>
    </div>

    <div id="ow_dialog_item_proto" class="clearfix">
        <div class="ow_dialog_item">
            <div class="ow_dialog_in_item ">
            <p>...</p>
            </div><i></i>
        </div>
    </div>

    <div id="ow_time_block_proto" class="clearfix">
        <div class="ow_time_block">
            <p><span class="ow_time_text"></span><span></span></p>
        </div><i></i>
    </div>

    <div id="ow_chat_notification_proto" class="ow_chat_notification">
        <div class="ow_chat_block">
            <div class="ow_author_block">
                <a class="btn4_open" href="javascript://">
                    <span class="ow_chat_item_photo_wrap">
                        <span class="ow_chat_item_photo">
                            <span class="ow_chat_in_item_photo"><img width="32px" height="32px" alt="" src="<?php echo $_smarty_tpl->tpl_vars['avatar_proto_data']->value['src'];?>
" /></span>
                        </span>
                    </span>
                    <span class="ow_chat_item_text">
                        <span class="ow_chat_in_item_text"></span>
                    </span>
                </a>
                <a class="ow_btn_close" href="javascript://"><span></span></a>
            </div>
        </div>
    </div>

    <ul id="ow_chat_selector_items_proto">
        <li class="ow_chat_selector_item ow_sets_button ow_hidden">
            <a href="javascript://"></a>
            <span class="ow_count_wrap">
                <span class="ow_count_bg ow_count_active" style="display: none;">
                    <span class="ow_count">0</span>
                </span>
            </span>
        </li>
    </ul>

    <span id="ow_last_message_sent_label" style="display: none;"><?php echo smarty_function_text(array('key'=>'ajaxim+last_message_sent'),$_smarty_tpl);?>
</span>
    <span id="ow_new_message_label" style="display: none;"><?php echo smarty_function_text(array('key'=>'ajaxim+new_message'),$_smarty_tpl);?>
</span>

    <span id="ow_language_suffixAgo"><?php echo smarty_function_text(array('key'=>'ajaxim+ago'),$_smarty_tpl);?>
</span>
    <span id="ow_language_suffixFromNow"><?php echo smarty_function_text(array('key'=>'ajaxim+from_now'),$_smarty_tpl);?>
</span>
    <span id="ow_language_seconds"><?php echo smarty_function_text(array('key'=>'ajaxim+less_than_minute'),$_smarty_tpl);?>
</span>
    <span id="ow_language_minute"><?php echo smarty_function_text(array('key'=>'ajaxim+about_minute'),$_smarty_tpl);?>
</span>
    <span id="ow_language_minutes"><?php echo smarty_function_text(array('key'=>'ajaxim+minutes'),$_smarty_tpl);?>
</span>
    <span id="ow_language_hour"><?php echo smarty_function_text(array('key'=>'ajaxim+about_hour'),$_smarty_tpl);?>
</span>
    <span id="ow_language_hours"><?php echo smarty_function_text(array('key'=>'ajaxim+about_hours'),$_smarty_tpl);?>
</span>
    <span id="ow_language_day"><?php echo smarty_function_text(array('key'=>'ajaxim+a_day'),$_smarty_tpl);?>
</span>
    <span id="ow_language_days"><?php echo smarty_function_text(array('key'=>'ajaxim+days'),$_smarty_tpl);?>
</span>
    <span id="ow_language_month"><?php echo smarty_function_text(array('key'=>'ajaxim+about_month'),$_smarty_tpl);?>
</span>
    <span id="ow_language_months"><?php echo smarty_function_text(array('key'=>'ajaxim+months'),$_smarty_tpl);?>
</span>
    <span id="ow_language_year"><?php echo smarty_function_text(array('key'=>'ajaxim+about_year'),$_smarty_tpl);?>
</span>
    <span id="ow_language_years"><?php echo smarty_function_text(array('key'=>'ajaxim+years'),$_smarty_tpl);?>
</span>
    <span id="ow_language_user_went_offline"><?php echo smarty_function_text(array('key'=>'ajaxim+user_went_offline'),$_smarty_tpl);?>
</span>

</div>

<div id="im_sound_player_audio_container" style="position: fixed; left: -1000px; top: -1000px;">
    <audio id="im_sound_player_audio" src="<?php echo $_smarty_tpl->tpl_vars['im_sound_url']->value;?>
" ></audio>
</div><?php }} ?>