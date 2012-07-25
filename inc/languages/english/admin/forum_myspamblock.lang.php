<?php

	$l['myspamblock'] = 'MySpamBlock';
	$l['myspamblock_desc'] = 'Here you can edit the settings for MySpamBlock.';
	$l['flagged'] = 'Flagged';
	$l['flagged_desc'] = 'These are users that have been flagged by MySpamBlock.';
	$l['logs'] = 'Logs';
	$l['logs_desc'] = 'These are all the actions performed by MySpamBlock.';
	
	$l['main_settings'] = 'Main Settings';
	$l['registration_settings'] = 'Registration Settings';
	$l['post_settings'] = 'Post Settings';
	$l['thread_settings'] = 'Thread Settings';
	$l['other_settings'] = 'Other Settings';
	
	$l['save_settings'] = 'Save Settings';
	
	$l['setting_enabled'] = 'Enable';
	$l['setting_enabled_desc'] = 'Would you like to enable MySpamBlock?';
	
	$l['setting_register_enabled'] = 'Enable Registration Functions';
	$l['setting_register_enabled_desc'] = "Turn MySpamBlock's registration functions on/off.";
	$l['setting_register_approve_flagged'] = 'Manually Approve Flags';
	$l['setting_register_approve_flagged_desc'] = 'Turn this on if you want to manually approve flagged registrations.';
	$l['setting_register_blockmessage'] = 'Blockmessage';
	$l['setting_register_blockmessage_desc'] = 'This is the message that will be displayed to denied registrants.';
	$l['setting_register_stopforumspam'] = 'StopForumSpam';
	$l['setting_register_stopforumspam_desc'] = 'Turn this on if you want to connect to the SFS database to check for known spammers.';
	$l['setting_register_captcha'] = 'Captcha';
	$l['setting_register_captcha_desc'] = "You can select a custom captcha here (default MyBB's captcha).<br /><span class=\"smalltext\">Recommended is Asirra. It uses a massive animal db to display clickable images to block spambots.</span>";
	$l['setting_register_captcha_options_default'] = 'Mybb Default';
	$l['setting_register_captcha_options_asirra'] = 'Asirra (Recommended)';
	$l['setting_register_captcha_options_recaptcha'] = 'reCaptcha (Requires Key)';
	$l['setting_register_captcha_key'] = 'Captcha Key';
	$l['setting_register_captcha_key_desc'] = "You only need to fill this in if you're using reCaptcha.";
	$l['setting_register_question'] = 'Registration Question';
	$l['setting_register_question_desc'] = 'Would you like to display a random custom question to registrants?';
	$l['setting_register_question_options'] = 'Registration Question Options';
	$l['setting_register_question_options_desc'] = 'Specify this only if you have Registration Question enabled.<br />Each question and answer combination is separated by a new line.<br />The format for questions/answers is as follows:<br />Question 1//Answer<br/ >Question 2//Answer 1;Answer 2;Answer 3;<br />You can specify any number of question/answer combinations.';
	
	$l['setting_post_enabled'] = 'Enable Post Functions';
	$l['setting_post_enabled_desc'] = "Turn MySpamBlock's posting functions on/off.";
	$l['setting_post_approve_flagged'] = 'Approve Flagged Posts';
	$l['setting_post_approve_flagged_desc'] = 'Turn this on to manually approve flagged posts.';
	$l['setting_post_links_postlimit'] = 'Links Postlimit';
	$l['setting_post_links_postlimit_desc'] = 'The number of posts a user must have before being allowed to post links.';
	$l['setting_post_repetition_block'] = 'Repetition Block';
	$l['setting_post_repetition_block_desc'] = 'Turn this on to block similar posts from being made by the same user.';
	$l['setting_post_timelimit'] = 'Post Timelimit';
	$l['setting_post_timelimit_desc'] = 'The number of seconds a user must wait between postings.';
	
	$l['setting_thread_enabled'] = 'Enable Thread Functions';
	$l['setting_thread_enabled_desc'] = "Turn MySpamBlock's thread functions on/off.";
	$l['setting_thread_approve_flagged'] = 'Approve Flagged Threads';
	$l['setting_thread_approve_flagged_desc'] = 'Turn this on to manually approve flagged threads.';
	$l['setting_thread_postlimit'] = 'Thread Postlimit';
	$l['setting_thread_postlimit_desc'] = 'The number of posts a user must have before being allowed to create threads.';
	$l['setting_thread_timelimit'] = 'Thread Timelimit';
	$l['setting_thread_timelimit_desc'] = 'The number of seconds a user must wait between creating threads.';
	
	$l['setting_other_numposts_override'] = 'Postcount Override';
	$l['setting_other_numposts_override_desc'] = 'Users above this postcount will be excluded from all flags. Set as -1 to disable excluding anyone.';
	$l['setting_other_signature_postlimit'] = 'Signature Postlimit';
	$l['setting_other_signature_postlimit_desc'] = 'The number of posts a user must have before being allowed to set a signature.';
	$l['setting_other_website_postlimit'] = 'Website Postlimit';
	$l['setting_other_website_postlimit_desc'] = 'The number of posts a user must have before being allowed to set a personal website.';
	
	$l['success_save'] = 'You have successfully saved your changes.';

?>