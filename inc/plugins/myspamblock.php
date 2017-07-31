<?php

	/*
	*
	*	MySpamBlock
	*
	*	MyBB spam prevention plugin.
	*	Created by Glitch & Josh Medeiros
	*	http://mybbplug.in/s/
	*
	*	This plugin will install a spam prevention system to prevent
	*	spammers from registering and/or posting spam on your forum.
	*	The settings are highly customizable and you can alter them
	*	by going to your ACP => Forums & Posts => MySpamBlock
	*
	*	You are free to use, modify, and redistribute this plugin at
	*	will, so long as you provide credit to MybbPlug.in/s/ for the
	*	original version.
	*
	*	Thanks for using, and we hope you enjoy!
	*
	*/
	
	$plugins->add_hook('member_register_start', 'myspamblock_register_form');
	$plugins->add_hook('xmlhttp', 'myspamblock_register_form_check');
	$plugins->add_hook('member_register_end', 'myspamblock_register_form_end');
	$plugins->add_hook('member_do_register_start', 'myspamblock_register_submit');
	$plugins->add_hook('datahandler_user_validate', 'myspamblock_register_submit_validate');
	$plugins->add_hook('member_do_register_end', 'myspamblock_register_submit_complete');
	$plugins->add_hook('admin_forum_menu', 'myspamblock_admin_menu');
	$plugins->add_hook('admin_forum_action_handler', 'myspamblock_admin_action_handler');
	
	/*
	*	Basic information.
	*/
	function myspamblock_info()
	{
		return array(
			'name'			=> 'MySpamBlock',
			'description'	=> 'Prevent spam registration and posts from being made.',
			'website'		=> 'http://www.mybbplug.in/s/',
			'author'		=> 'Ethan',
			'authorsite'	=> 'http://www.mybbplug.in/s/',
			'version'		=> '0.10',
			'compatibility' => '16*'
		);
	}
	
	/*
	*
	*	Activating MySpamBlock
	*
	*	Creates tables:
	*		spamblock_flagged
	*		spamblock_logs
	*		spamblock_settings
	*
	*	Settings table created in ACP => Forums & Posts => MySpamBlock
	*
	*/
	function myspamblock_activate()
	{
		global $db, $lang;
		
		$lang->load('member');
	
		if(!$db->table_exists('myspamblock_flagged'))
		{
			$myspamblock_table = 'CREATE TABLE `'.TABLE_PREFIX.'myspamblock_flagged` (
				`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
				`dateline` INT( 11 ) NOT NULL ,
				`uid` INT( 10 ) NOT NULL DEFAULT 0 ,
				`ipaddress` TEXT NOT NULL ,
				`pid` INT( 10 ) NOT NULL DEFAULT 0 ,
				`reason` TEXT NOT NULL ,
				PRIMARY KEY ( `id` )
				) ENGINE = MYISAM ;
								';
			$db->query($myspamblock_table);
		}
		
		if(!$db->table_exists('myspamblock_logs'))
		{
			$myspamblock_table = 'CREATE TABLE `'.TABLE_PREFIX.'myspamblock_logs` (
				`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
				`dateline` INT( 11 ) NOT NULL ,
				`type` TEXT NOT NULL ,
				PRIMARY KEY ( `id` )
				) ENGINE = MYISAM ;
								';
			$db->query($myspamblock_table);
		}
		
		if(!$db->table_exists('myspamblock_rqlog'))
		{
			$myspamblock_table = "CREATE TABLE `".TABLE_PREFIX."myspamblock_rqlog` (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
				`qid` INT( 11 ) NOT NULL ,
				`key` TEXT NOT NULL ,
				`valid` INT( 11 ) NOT NULL DEFAULT 1 ,
				PRIMARY KEY ( `id` )
				) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
			";
			$db->query($myspamblock_table);
		}
		
		if(!$db->table_exists('myspamblock_settings'))
		{
			$myspamblock_table = "CREATE TABLE `".TABLE_PREFIX."myspamblock_settings` (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
				`name` TEXT NOT NULL ,
				`value` TEXT NOT NULL ,
				`type` TEXT NOT NULL ,
				`cat` VARCHAR( 1 ) NOT NULL ,
				PRIMARY KEY ( `id` )
				) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
			";
			$db->query($myspamblock_table);
		}
		
		$default_settings[] = array(
			'name' => 'enabled',
			'value' => '1',
			'type' => 'yesno',
			'cat' => 'm'
		);
		
		// Register default settings.
		
		$default_settings[] = array(
			'name' => 'register_enabled',
			'value' => '1',
			'type' => 'yesno',
			'cat' => 'r'
		);
		
		$default_settings[] = array(
			'name' => 'register_approve_flagged',
			'value' => '0',
			'type' => 'yesno',
			'cat' => 'r'
		);
	
		$default_settings[] = array(
			'name' => 'register_blockmessage',
			'value' => $lang->error_spam_deny,
			'type' => 'textarea',
			'cat' => 'r'
		);
	
		$default_settings[] = array(
			'name' => 'register_stopforumspam',
			'value' => '0',
			'type' => 'yesno',
			'cat' => 'r'
		);
	
		$default_settings[] = array(
			'name' => 'register_captcha',
			'value' => 'a:2:{s:8:"selected";i:0;s:7:"options";a:3:{i:0;s:7:"default";i:1;s:6:"asirra";i:2;s:9:"recaptcha";}}',
			'type' => 'select',
			'cat' => 'r'
		);
	
		$default_settings[] = array(
			'name' => 'register_captcha_publickey',
			'value' => '',
			'type' => 'textbox',
			'cat' => 'r'
		);
	
		$default_settings[] = array(
			'name' => 'register_captcha_privatekey',
			'value' => '',
			'type' => 'textbox',
			'cat' => 'r'
		);
	
		$default_settings[] = array(
			'name' => 'register_question',
			'value' => '0',
			'type' => 'yesno',
			'cat' => 'r'
		);
	
		$default_settings[] = array(
			'name' => 'register_question_options',
			'value' => "What is 2 + 2?//4;four\nWhat is the most popular search engine today?//google",
			'type' => 'textarea',
			'cat' => 'r'
		);
			
		// Post default settings.
		
		$default_settings[] = array(
			'name' => 'post_enabled',
			'value' => '1',
			'type' => 'yesno',
			'cat' => 'p'
		);
		
		$default_settings[] = array(
			'name' => 'post_approve_flagged',
			'value' => '0',
			'type' => 'yesno',
			'cat' => 'p'
		);
	
		$default_settings[] = array(
			'name' => 'post_links_postlimit',
			'value' => '5',
			'type' => 'textbox',
			'cat' => 'p'
		);
	
		$default_settings[] = array(
			'name' => 'post_repetition_block',
			'value' => '1',
			'type' => 'yesno',
			'cat' => 'p'
		);
	
		$default_settings[] = array(
			'name' => 'post_timelimit',
			'value' => '10',
			'type' => 'textbox',
			'cat' => 'p'
		);
		
		// Thread default settings.
		
		$default_settings[] = array(
			'name' => 'thread_enabled',
			'value' => '1',
			'type' => 'yesno',
			'cat' => 't'
		);
		
		$default_settings[] = array(
			'name' => 'thread_approve_flagged',
			'value' => '0',
			'type' => 'yesno',
			'cat' => 't'
		);
	
		$default_settings[] = array(
			'name' => 'thread_timelimit',
			'value' => '120',
			'type' => 'textbox',
			'cat' => 't'
		);
	
		$default_settings[] = array(
			'name' => 'thread_postlimit',
			'value' => '5',
			'type' => 'textbox',
			'cat' => 't'
		);
		
		// Other settings.
		
		$default_settings[] = array(
			'name' => 'other_numposts_override',
			'value' => '20',
			'type' => 'textbox',
			'cat' => 'u'
		);
		
		$default_settings[] = array(
			'name' => 'other_signature_postlimit',
			'value' => '5',
			'type' => 'textbox',
			'cat' => 'u'
		);
		
		$default_settings[] = array(
			'name' => 'other_website_postlimit',
			'value' => '5',
			'type' => 'textbox',
			'cat' => 'u'
		);
		
		$db->insert_query_multiple('myspamblock_settings', $default_settings);
	}
	
	function myspamblock_deactivate()
	{
		global $db;
		
		/* Drop the created tables. */
		
		if($db->table_exists('myspamblock_flagged'))
			$db->drop_table('myspamblock_flagged');
			
		if($db->table_exists('myspamblock_logs'))
			$db->drop_table('myspamblock_logs');
			
		if($db->table_exists('myspamblock_settings'))
			$db->drop_table('myspamblock_settings');
	}
	
	function myspamblock_settings()
	{
		global $db;
		
		$query = $db->simple_select('myspamblock_settings');
		while($result = $db->fetch_array($query)) $setting[$result['name']] = $result['value'];
		
		return $setting;
	}
	
	function myspamblock_register_form()
	{
		$setting = myspamblock_settings();
		
		if($setting['enabled'] && $setting['register_enabled'])
		{
			$setting['register_captcha'] = unserialize($setting['register_captcha']);
			if($setting['register_captcha']['selected'])
			{
				// Captcha other than default is set, so hide the default one.
				global $mybb;
				$mybb->settings['captchaimage'] = 0;
			}
		}
	}
	
	function myspamblock_register_form_check()
	{
		$setting = myspamblock_settings();
		
		if($setting['enabled'] && $setting['register_enabled'] && $setting['register_question'])
		{
			global $mybb;
			if($mybb->input['action'] == 'validate_question')
			{
				global $charset, $db, $lang, $session;
				header("Content-type: text/xml; charset={$charset}");
				$lang->load('myspamblock');
				$ans = strtolower($mybb->input['value']);
				$key = md5($session->ipaddress);
				$result = $db->simple_select('myspamblock_rqlog', '*', "`key`='{$key}'");
				$log = $db->fetch_array($result);
				$options = explode("\r\n", $setting['register_question_options']);
				$question = explode('//', $options[$log['qid']]);
				$answers = explode(';', $question[1]);
				$success = false;
				foreach($answers as $answer) if(strtolower($answer) == $ans) $success = true;
				if($success)
				{
					echo "<success>{$lang->question_success}</success>";
					exit;
				}
				else
				{
					echo "<fail>{$lang->question_fail}</fail>";
					exit;
				}
			}
		}
	}
	
	function myspamblock_register_form_end()
	{
		$setting = myspamblock_settings();
		
		if($setting['enabled'] && $setting['register_enabled'])
		{
			global $lang;
			$lang->load('myspamblock');
			require_once MYBB_ROOT.'inc/plugins/myspamblock/functions_register.php';
			
			$setting['register_captcha'] = unserialize($setting['register_captcha']);
			if($setting['register_captcha']['selected'])
			{
				// Get captcha html.
				global $regimage;
				$regimage = CaptchaHtml($setting);
			}
			
			if($setting['register_question'])
			{
				global $regimage, $validator_extra;
				$regimage = QuestionHtml($setting).$regimage;
				$validator_extra .= "\tregValidator.register('rq_answer', 'ajax', { url: 'xmlhttp.php?action=validate_question', extra_body: 'rq_question', loading_message: '{$lang->question_loading}', failure_message: '{$lang->question_fail}'} );\n";
			}
		}
	}
	
	function myspamblock_register_submit()
	{
		$setting = myspamblock_settings();
		
		if($setting['enabled'] && $setting['register_enabled'])
		{
			require_once MYBB_ROOT.'inc/plugins/myspamblock/functions_register.php';
			
			$setting['register_captcha'] = unserialize($setting['register_captcha']);
			if($setting['register_captcha']['selected'])
			{
				// We have something other than the default captcha set, so don't check for it.
				global $mybb;
				$mybb->settings['captchaimage'] = 0;
			}
			
			if($setting['register_stopforumspam'])
			{
				global $mybb, $session;
				
				if(CheckFlag($session->ipaddress))
				{
					if(!$setting['register_approve_flagged'])
					{
						error($setting['register_blockmessage']);
					}
				}
				else
				{
					$result = StopForumSpam($mybb->input['email'], $session->ipaddress);
					if(!$result || !$result['success'])
					{
						// No successful response from the server.
					}
					else
					{
						// Successful server response received. Check values now.
						$confidence = 0;
						if(isset($result['email']['confidence']))
							$confidence += intval($result['email']['confidence']);
						if(isset($result['ip']['confidence']))
							$confidence += intval($result['ip']['confidence']);
						$confidence = intval($confidence / 2);
						
						if($confidence > 25)
						{
							// Confidence average is greater than 25% for email and ip.
							if($setting['register_approve_flagged'])
							{
								AddFlag('register_check', $session->ipaddress);
							}
							else
							{
								AddLog('register_deny');
								error($setting['register_blockmessage']);
							}
						}
					}
				}
			}
		}
	}
	
	function myspamblock_register_submit_complete()
	{
		$setting = myspamblock_settings();
		
		if($setting['enabled'] && $setting['register_enabled'])
		{
			require_once MYBB_ROOT.'inc/plugins/myspamblock/functions_register.php';
			
			if($setting['register_approve_flagged'] && CheckFlag($session->ipaddress))
			{
				global $session, $user_info;
				AddFlag('register_check', $session->ipaddress, $user_info['uid']);
			}
		}
	}
	
	function myspamblock_register_submit_validate(&$user)
	{
		$setting = myspamblock_settings();
		
		if($user->method == 'insert' && $setting['enabled'] && $setting['register_enabled'])
		{
			global $lang;
			$lang->load('myspamblock');
			require_once MYBB_ROOT.'inc/plugins/myspamblock/functions_register.php';
			
			$setting['register_captcha'] = unserialize($setting['register_captcha']);
			if($setting['register_captcha']['selected'])
			{
				$error = CaptchaCheck($setting);
				if($error) $user->set_error($error);
			}
			
			if($setting['register_question'])
			{
				$error = QuestionCheck($setting);
				if($error) $user->set_error($error);
			}
		}
	}
	
	function myspamblock_post()
	{
		$setting = myspamblock_settings();
		
		if($setting['enabled'] && $setting['post_enabled'])
		{
			global $lang;
			$lang->load('myspamblock');
			require_once MYBB_ROOT.'inc/plugins/myspamblock/functions_register.php';
		}
	}
	
	function myspamblock_thread()
	{
		$setting = myspamblock_settings();
		
		if($setting['enabled'] && $setting['thread_enabled'])
		{
			global $lang;
			$lang->load('myspamblock');
			require_once MYBB_ROOT.'inc/plugins/myspamblock/functions_register.php';
		}
	}
	
	function myspamblock_admin_menu($sub_menu)
	{
		global $mybb, $lang;
		
		$lang->load('myspamblock');

		end($sub_menu);
		$key = (key($sub_menu))+10;

		if(!$key)
		{
			$key = '50';
		}
		$sub_menu[$key] = array('id' => 'myspamblock', 'title' => $lang->myspamblock, 'link' => "index.php?module=forum-myspamblock");
		return $sub_menu;
	}
	
	function myspamblock_admin_action_handler($action)
	{
		$action['myspamblock'] = array('active' => 'myspamblock', 'file' => 'myspamblock.php');
		return $action;
	}

?>
