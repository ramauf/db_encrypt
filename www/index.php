<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tester');
require_once('db.class.php');
if (isset($_POST['email']) && isset($_POST['phone'])){
	$_POST['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
	preg_match_all('|[+\d]+|', $_POST['phone'], $m);
	$_POST['phone'] = implode('', $m[0]);
	if ($_POST['email']) {
        /*
        //Способ1
        DB::query('INSERT INTO `users2` (`email`, `phone`) VALUES (AES_ENCRYPT("'.$_POST['email'].'", "'.$_POST['email'].'"), AES_ENCRYPT("'.$_POST['phone'].'", "'.$_POST['email'].'")) ON DUPLICATE KEY UPDATE `email` = `email`');
        */
		//Способ2
		$email = openssl_encrypt($_POST['email'], 'aes128', $_POST['email'], OPENSSL_RAW_DATA, str_pad('', 16, $_POST['email']));
		$phone = openssl_encrypt($_POST['phone'], 'aes128', $_POST['email'], OPENSSL_RAW_DATA, str_pad('', 16, $_POST['email']));
		DB::query('INSERT INTO `users2` (`email`, `phone`) VALUES ("' . DB::escape($email) . '", "' . DB::escape($phone) . '") ON DUPLICATE KEY UPDATE `email` = `email`');
	}
}
if (isset($_POST['emailRetrieve'])){
	$_POST['emailRetrieve'] = filter_var($_POST['emailRetrieve'], FILTER_VALIDATE_EMAIL);
	if ($_POST['emailRetrieve']) {
		$data = array();
		/*
        //Способ1
        $result = DB::query('SELECT `id`, AES_DECRYPT(`email`, "'.$_POST['emailRetrieve'].'") AS `email`, AES_DECRYPT(`phone`, "'.$_POST['emailRetrieve'].'") AS `phone` FROM `users2` WHERE `email` = AES_ENCRYPT("'.$_POST['emailRetrieve'].'", "'.$_POST['emailRetrieve'].'")');
        if (!empty($result)) $data = $result[0];
        */
		//Способ2
		$email = openssl_encrypt($_POST['emailRetrieve'], 'aes128', $_POST['emailRetrieve'], OPENSSL_RAW_DATA, str_pad('', 16, $_POST['emailRetrieve']));
		$result = DB::query('SELECT * FROM `users2` WHERE `email` = "' . DB::escape($email) . '"');
		if (!empty($result)) {
			$data = $result[0];
			$data['email'] = openssl_decrypt($data['email'], 'aes128', $_POST['emailRetrieve'], OPENSSL_RAW_DATA, str_pad('', 16, $_POST['emailRetrieve']));
			$data['phone'] = openssl_decrypt($data['phone'], 'aes128', $_POST['emailRetrieve'], OPENSSL_RAW_DATA, str_pad('', 16, $_POST['emailRetrieve']));
		}
		if (!empty($data)) {
			mail($data['email'], 'Phone number retrieving', 'Your phone number is ' . $data['phone']);
			//echo 'Your phone number is '.$data['phone'].', email is '.$data['email'];
		} else {
			echo 'There is no data for ' . $_POST['emailRetrieve'];
		}
	}
}
?>
<table border = 1 width = 40%>
	<tr valign=top>
		<td width=50%>
			<form method='post'>
				Add your phone number<br/><br/>
				Enter your phone:<br/>
				<input type='text' name='phone'/><br/><br/>
				Enter your e-mail:<br/>
				<input type='text' name='email'/><br/><br/>
				You willbe able to retrieve your phone number later on using your email<br/><br/>
				<input type='button' onclick='this.form.submit();' value='submit'/>
			</form>
		</td>
		<td>
			<form method='post'>
				Retreive your phone number<br/><br/>
				Enter your email:<br/>
				<input type='text' name='emailRetrieve'/><br/><br/>
				The phone number will be e-mailed to you<br/><br/>
				<input type='button' onclick='this.form.submit();' value='submit'/>
			</form>
		</td>
	</tr>
</table>