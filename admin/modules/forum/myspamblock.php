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

		$page->output_nav_tabs($sub_tabs, 'myspamblock');

		if($mybb->request_method == 'post')
		{
			$query = $db->simple_select('myspamblock_settings');
			while($set = $db->fetch_array($query))
			{
				$name = 'setting_'.$set['name'];
				if($set['type'] == 'select')
				{
					$val = unserialize($set['value']);
					$val['selected'] = (isset($mybb->input[$name]) ? $mybb->input[$name] : 0);
					$mybb->input[$name] = serialize($val);
				}
				if(isset($mybb->input[$name]))
					$db->update_query('myspamblock_settings', array('value' => $db->escape_string($mybb->input[$name])), "`id` = $set[id]");
			}
			flash_message($lang->success_save, 'success');
			admin_redirect("index.php?module=forum-myspamblock");
		}
	
		$form = new Form("index.php?module=forum-myspamblock", "post");
		
		$main_container = new FormContainer($lang->main_settings, '', '', 0, '', true);
		$reg_container = new FormContainer($lang->registration_settings, '', '', 0, '', true);
		$post_container = new FormContainer($lang->post_settings, '', '', 0, '', true);
		$thread_container = new FormContainer($lang->thread_settings, '', '', 0, '', true);
		$other_container = new FormContainer($lang->other_settings, '', '', 0, '', true);
		
		$query = $db->simple_select('myspamblock_settings');
		while($set = $db->fetch_array($query))
		{
			if($set['cat'] == 'm') $container = 'main_container';
			else if($set['cat'] == 'r') $container = 'reg_container';
			else if($set['cat'] == 'p') $container = 'post_container';
			else if($set['cat'] == 't') $container = 'thread_container';
			else $container = 'other_container';
			$name = 'setting_'.$set['name'];
			$lname = $lang->{$name};
			$desc = $lang->{'setting_'.$set['name'].'_desc'};
			$type = '';
			if($set['type'] == 'select')
			{
				$set['value'] = unserialize($set['value']);
				$set['options'] = $set['value']['options'];
				$set['value'] = $set['value']['selected'];
				foreach($set['options'] as $k => $v) $set['options'][$k] = $lang->{$name.'_options_'.$v};
			}
			if(!isset($mybb->input[$name])) $mybb->input[$name] = $set['value'];
			switch($set['type'])
			{
				case 'yesno':
					$type = $form->generate_yes_no_radio($name, $mybb->input[$name], true);
					break;
				case 'textarea':
					$type = $form->generate_text_area($name, $mybb->input[$name], array('id' => $name));
					break;
				case 'select':
					$type = $form->generate_select_box($name, $set['options'], $mybb->input[$name], array('id' => $name));
					break;
				case 'text':
				default:
					$type = $form->generate_text_box($name, $mybb->input[$name], array('id' => $name), $name);
					break;
			}
			${$container}->output_row($lname, $desc, $type, $lname);
		}
		
		$main_container->end();
		$reg_container->end();
		$post_container->end();
		$thread_container->end();
		$other_container->end();
		
		$buttons[] = $form->generate_submit_button($lang->save_settings);
		
		$form->output_submit_wrapper($buttons);

		$form->end();
	}
	else
	if($mybb->input['action'] == 'flagged')
	{
		$page->output_header($lang->manage);

		$page->output_nav_tabs($sub_tabs, 'flagged');
	}
	else
	if($mybb->input['action'] == 'logs')
	{
		$page->output_header($lang->manage);

		$page->output_nav_tabs($sub_tabs, 'logs');
	}
	else
	{
		admin_redirect("index.php?module=forum-myspamblock");
	}
	$page->output_footer();

?>