<?php

	/*
	*	Add flag for user that must be approved if they finish registration.
	*	Update flag for user that just registered and now has a uid.
	*/
	function AddFlag($ip, $uid=0)
	{
		global $db, $lang;
		$details = array(
			'uid' => intval($uid),
			'ipaddress' => $db->escape_string($ip),
			'reason' => $lang->flag_registration
		);
		$db->insert_query('myspamblock_flagged', $details);
	}
	
	function CheckFlag($ip)
	{
		
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