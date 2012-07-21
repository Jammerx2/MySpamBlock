<?php

	/*
	*
	*	MySpamBlock
	*
	*	MyBB spam prevention plugin.
	*	Created by Ethan DeLong & Josh Medeiros
	*	http://mybbplug.in/s/
	*
	*	Admin CP Module
	*
	*/

	// Prevent users from accessing this file directly.
	if(!defined("IN_MYBB")) die( "You are not allowed to view this file directly.<br /><br />Please make sure IN_MYBB is defined." );
	
	$page->add_breadcrumb_item($lang->myspamblock, "index.php?module=forum-myspamblock");
	
	$sub_tabs['myspamblock'] = array(
		'title' => $lang->myspamblock,
		'link' => "index.php?module=forum-myspamblock",
		'description' => $lang->myspamblock_desc
	);
	$sub_tabs['flagged'] = array(
		'title' => $lang->flagged,
		'link' => "index.php?module=forum-myspamblock&amp;action=flagged",
		'description' => $lang->flagged_desc
	);
	$sub_tabs['logs'] = array(
		'title' => $lang->logs,
		'link' => "index.php?module=forum-myspamblock&amp;action=logs",
		'description' => $lang->logs_desc
	);
	
	if(!$mybb->input['action'])
	{
		$page->output_header($lang->manage);

		$page->output_nav_tabs($sub_tabs, 'manage');

		$table = new Table;
		$table->construct_header($lang->subscription);
		$table->construct_header($lang->price_length);
		$table->construct_header($lang->controls, array('width' => '100', 'style' => 'text-align: center;'));
	
		$subs_result = $db->simple_select('mysubs');
		while($sub = $db->fetch_array($subs_result))
		{
			if($sub['active'] == 1)
				$icon = "<img src=\"styles/{$page->style}/images/icons/bullet_on.gif\" alt=\"({$lang->active})\" title=\"{$lang->active}\"  style=\"vertical-align: middle;\" /> ";
			else
				$icon = "<img src=\"styles/{$page->style}/images/icons/bullet_off.gif\" alt=\"({$lang->alt_unactive})\" title=\"{$lang->alt_unactive}\"  style=\"vertical-align: middle;\" /> ";
			
			$price = array();
			$prices = unserialize($sub['price']);
			foreach($prices as $val)
			{
				if($val['l'] < 1) $lt = $lang->forever;
				else $lt = $val['l'].' '.(($val['lt'] == 'y') ? (($val['l'] > 1) ? $lang->years : $lang->year) : (($val['lt'] == 'm') ? (($val['l'] > 1) ? $lang->months : $lang->month) :  (($val['l'] > 1) ? $lang->days : $lang->day)));
				$price[] = $val['c'].' '.$sub['currency'].' / '.$lt;
			}
			
			$options = new PopupMenu("sub_{$sub['sid']}", $lang->options);
			$options->add_item($lang->edit_sub, "index.php?module=user-mysubs&amp;action=edit&amp;sid={$sub['sid']}");
			$options->add_item($lang->view_subs, "index.php?module=user-mysubs&amp;action=notifications&sid={$sub['sid']}");
			$options->add_item($lang->delete_sub, "index.php?module=user-mysubs&amp;action=delete&amp;sid={$sub['sid']}&amp;my_post_key={$mybb->post_code}", "return AdminCP.deleteConfirmation(this, '{$lang->confirm_sub_deletion}')");
			
			$table->construct_cell("<div class=\"float_right\">{$icon}</div><div><strong><a href=\"index.php?module=user-mysubs&amp;action=edit&amp;sid={$sub['sid']}\">{$sub['name']}</a></strong><br /><small>{$sub['admin_desc']}</small></div>");
			$table->construct_cell(implode('<br />', $price));
			$table->construct_cell($options->fetch(), array("class" => "align_center"));
			$table->construct_row();
		}
		
		if($table->num_rows() == 0)
		{
			$table->construct_cell($lang->no_subs, array('colspan' => 3));
			$table->construct_row();
		}
		
		$table->output($lang->manage_subs);
	}
	else
	if($mybb->input['action'] == 'settings')
	{
		$page->output_header($lang->settngs);

		$page->output_nav_tabs($sub_tabs, 'settings');
		
		if($mybb->request_method == 'post')
		{
			$query = $db->simple_select('mysubs_settings');
			while($set = $db->fetch_array($query))
			{
				$name = 'setting_'.$set['name'];
				if(isset($mybb->input[$name]))
					$db->update_query('mysubs_settings', array('value' => $db->escape_string($mybb->input[$name])), "`id` = $set[id]");
			}
			flash_message($lang->success_save, 'success');
			admin_redirect("index.php?module=user-mysubs&amp;action=settings");
		}
	
		$form = new Form("index.php?module=user-mysubs&amp;action=settings", "post");
		
		if($errors)
		{
			$page->output_inline_error($errors);
		}
		
		$normal_container = new FormContainer($lang->basic_settings, '', '', 0, '', true);
		$advanced_container = new FormContainer($lang->advanced_settings, '', '', 0, '', true);
		
		$query = $db->simple_select('mysubs_settings');
		while($set = $db->fetch_array($query))
		{
			$container = ($set['cat'] == 'n') ? 'normal_container' : 'advanced_container';
			$name = 'setting_'.$set['name'];
			$lname = $lang->{$name};
			$desc = $lang->{'setting_'.$set['name'].'_desc'};
			$type = '';
			if(!isset($mybb->input[$name])) $mybb->input[$name] = $set['value'];
			switch($set['type'])
			{
				case 'yesno':
					$type = $form->generate_yes_no_radio($name, $mybb->input[$name], true);
					break;
				case 'select':
					// $type = $form->generate_select_box();
					break;
				case 'text':
				default:
					$type = $form->generate_text_box($name, $mybb->input[$name], array('id' => $name), $name);
					break;
			}
			${$container}->output_row($lname, $desc, $type, $lname);
		}
		
		$normal_container->end();
		$advanced_container->end();
		
		$buttons[] = $form->generate_submit_button($lang->save_settings);
		
		$form->output_submit_wrapper($buttons);

		$form->end();
	}
	else
	{
		admin_redirect("index.php?module=forum-myspamblock");
	}
	$page->output_footer();

?>