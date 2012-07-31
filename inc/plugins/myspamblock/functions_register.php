<?php

	/*
	*	Add flag for user that must be approved.
	*/
	function AddFlag($reason, $ip, $uid=0, $pid=0)
	{
		global $db;
		$details = array(
			'dateline' => TIME_NOW,
			'uid' => intval($uid),
			'pid' => intval($pid),
			'ipaddress' => $db->escape_string($ip),
			'reason' => $reason
		);
		$db->insert_query('myspamblock_flagged', $details);
	}
	
	function CheckFlag($ip='', $uid=0, $pid=0)
	{
		global $db;
		$checks = array();
		if(!empty($ip)) $checks[] = "`ipaddress`='".$db->escape_string($ip)."'";
		if($uid) $checks[] = '`uid`='.intval($uid);
		if($pid) $checks[] = '`pid`='.intval($pid);
		$checks = implode(' OR ', $checks);
		$result = $db->simple_select('myspamblock_flagged', '*', "{$checks}");
		if($result && $db->num_rows($result) > 0) return true;
		return false;
	}
	
	function AddLog($type)
	{
		global $db, $session;
		$db->insert_query('myspamblock_logs', array('dateline' => TIME_NOW, 'type' => $type, 'ip' => $session->ipaddress));
	}
	
	function CaptchaCheck($setting)
	{
		global $lang;
		$error = false;
		switch($setting['register_captcha']['selected'])
		{
			case 1:
				$resp = file_get_contents('http://challenge.asirra.com/cgi/Asirra?action=ValidateTicket&ticket='.$_POST['Asirra_Ticket']);
				if(strpos($resp, '<Result>Pass</Result>') === false)
				{
					// Incorrect answer.
					$error = $lang->error_failed_captcha;
				}
			break;
			
			case 2:
				// reCaptcha
				require_once MYBB_ROOT.'inc/plugins/myspamblock/recaptchalib.php';
				$resp = recaptcha_check_answer($setting['register_captcha_privatekey'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
				if(!$resp->is_valid)
				{
					// Incorrect answer.
					$error = $lang->error_failed_captcha;
				}
			break;
		}
		return $error;
	}
	
	function CaptchaHtml($setting)
	{
		global $lang;
		$html = '';
		switch($setting['register_captcha']['selected'])
		{
			case 1:
				$html = '<br />
<fieldset class="trow2">
<legend><strong>'.$lang->human_verification.'</strong></legend>
<table cellspacing="0" cellpadding="4">
<tr>
<td><span class="smalltext"><label for="regimage">'.$lang->human_verification_desc.'</label></span></td>
</tr>
<tr>
<td>
<noscript>'.$lang->human_verification_javascript.'</noscript>
<!-- Create hidden agreement input for asirra so the user gets redirected to the right page. -->
<input type="hidden" name="agree" value="true" />
<script type="text/javascript" src="http://challenge.asirra.com/js/AsirraClientSide.js"></script>
<script type="text/javascript">
var regform = $("registration_form");
var passed = false;
regform.setAttribute("onSubmit", "return AsirraCheck();");
function AsirraCheck()
{
	if(passed) return true;
	Asirra_CheckIfHuman(FinishedCheck);
	return false;
}
function FinishedCheck(passed)
{
	if(!passed)
	{
		alert("'.$lang->error_failed_captcha.'");
	}
	else
	{
		passed = true;
		regform.submit();
	}
}
</script>
</td>
</tr></table>
</fieldset>';
			break;
			
			case 2:
				require_once MYBB_ROOT.'inc/plugins/myspamblock/recaptchalib.php';
				$html = '<br />
<fieldset class="trow2">
<legend><strong>'.$lang->human_verification.'</strong></legend>
<table cellspacing="0" cellpadding="4">
<tr>
<td><span class="smalltext"><label for="regimage">'.$lang->human_verification_desc.'</label></span></td>
</tr>
<tr>
<td>
'.recaptcha_get_html($setting['register_captcha_publickey']).'
</td>
</tr></table>
</fieldset>';
			break;
		}
		return $html;
	}
	
	function QuestionCheck($setting)
	{
		global $db, $lang, $mybb, $session;
		$error = false;
		$ans = strtolower($mybb->input['rq_answer']);
		$key = md5($session->ipaddress);
		$result = $db->simple_select('myspamblock_rqlog', '*', "`key`='{$key}'");
		$log = $db->fetch_array($result);
		$options = explode("\r\n", $setting['register_question_options']);
		$question = explode('//', $options[$log['qid']]);
		$answers = explode(';', $question[1]);
		$success = false;
		foreach($answers as $answer) if(strtolower($answer) == $ans) $success = true;
		if(!$success) $error = $lang->question_fail;
		return $error;
	}
	
	function QuestionHtml($setting)
	{
		global $db, $lang, $mybb, $session;
		$options = explode("\r\n", $setting['register_question_options']);
		$qid = rand(0, count($options) - 1);
		$key = md5($session->ipaddress);
		$result = $db->simple_select('myspamblock_rqlog', '*', "`key`='{$key}'");
		if($result && $db->num_rows($result) > 0) $db->update_query('myspamblock_rqlog', array('qid' => $qid), "`key`='{$key}'");
		else $db->insert_query('myspamblock_rqlog', array('qid' => $qid, 'key' => $key));
		$option = explode('//', $options[$qid]);
		$question = $option[0];
		$html = '<br />
<fieldset class="trow2">
<legend><strong>'.$lang->random_question.'</strong></legend>
<table cellspacing="0" cellpadding="4">
<tr>
<td><span class="smalltext"><label for="rq_answer">'.$lang->random_question_desc.'<br /><br />'.$question.'</label></span></td>
</tr>
<tr>
<td>
<input type="text" class="textbox" name="rq_answer" id="rq_answer" value="'.$mybb->input['rq_answer'].'" style="width: 100%;" />
</td>
</tr></table>
</fieldset>';
		return $html;
	}

	/*
	*	Connect to the stopforumspam database with results for the current user.
	*	Returns the array of data for the user's details.
	*/
	function StopForumSpam($email, $ip)
	{
		// Make sure the variables can be written through the url.
		$email = urlencode($email);
		$ip = urlencode($ip);
		
		// Get the result of the sfs check.
		$serialized = file_get_contents("http://www.stopforumspam.com/api?email={$email}&ip={$ip}&f=serial");
		
		if(empty($serialized)) return false; // Couldn't collect any data.
		else return unserialize($serialized); // Returning array of information.
	}

?>