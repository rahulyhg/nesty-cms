<? html_header("account", $domain." account");

// check install.php output as success or failure

echo "<div>";
echo "<span class='button float_right'></span><a href='/' class='material-icons button float_right'>home</a>";
echo "<a href='/account/' class='material-icons button float_right'>account_circle</a>";
echo "<a href='/two-factor/' class='material-icons button float_right'>looks_two</a>";
if ($login['status'] == "admin"):
	echo "<a href='/settings/' class='material-icons button float_right'>settings</a>";
	echo "<a href='/security/' class='material-icons button float_right'>security</a>";
	echo "<a href='/supervisor/' class='material-icons button float_right'>supervisor_account</a>"; endif;
echo "<span class='button float_left'></span><a href='/logout/' class='material-icons button float_left'>cancel</a>";
echo "</div>";

if (empty($login['authenticator']) && ($page_temp == "account")):
	// create code
	$values_temp = [
		"user_id"=>$login['user_id'],
		"authenticator"=>random_code(20) ];
	$sql_temp = sql_setup($values_temp, "$database.users");
	$update_authenticator = $connection_pdo->prepare($sql_temp);
	$update_authenticator->execute($values_temp);
	$result = execute_checkup($update_authenticator->errorInfo(), "updating authenticator okay");
	if ($result == "success"): echo '<script> window.location.replace("https://'.$domain.'/account/"); </script>'; endif;
	endif;

$result_success = 0;

$result_failure = null;

// update site settings and security
if (!(empty($_POST['update'])) && ($login['status'] == "admin")):

	$values_temp = [
		"key"=>null,
		"value"=>null ];
	$sql_temp = sql_setup($values_temp, "$database.siteinfo");
	$update_siteinfo = $connection_pdo->prepare($sql_temp);

	// update basic site info
	if ($_POST['update'] == "settings"):
		if ($_POST['publisher'] !== $publisher):
			$update_siteinfo->execute(["key"=>"publisher", "value"=>trim($_POST['publisher'])]);
			$result = execute_checkup($update_siteinfo->errorInfo(), "updating publisher okay");
			if ($result !== "success"): $result_failure = "failure"; else: $result_success = 1; endif; endif;
		if ($_POST['description'] !== $description):
			$update_siteinfo->execute(["key"=>"description", "value"=>trim($_POST['description'])]);
			$result = execute_checkup($update_siteinfo->errorInfo(), "updating description okay");
			if ($result !== "success"): $result_failure = "failure"; else: $result_success = 1; endif; endif;
		if ($_POST['google_analytics_code'] !== $google_analytics_code):
			$update_siteinfo->execute(["key"=>"google_analytics_code", "value"=>trim($_POST['google_analytics_code'])]);
			$result = execute_checkup($update_siteinfo->errorInfo(), "updating google_analytics_code okay");
			if ($result !== "success"): $result_failure = "failure"; else: $result_success = 1; endif; endif;
		if ($_POST['color'] !== $color):
			$update_siteinfo->execute(["key"=>"color", "value"=>trim($_POST['color'])]);
			$result = execute_checkup($update_siteinfo->errorInfo(), "updating color okay");
			if ($result !== "success"): $result_failure = "failure"; else: $result_success = 1; endif; endif;
		endif;

	// update recaptcha
	if ($_POST['update'] == "recaptcha"):
		if ($_POST['recaptcha_site'] !== $recaptcha_site):
			$update_siteinfo->execute(["key"=>"recaptcha_site", "value"=>trim($_POST['recaptcha_site'])]);
			$result = execute_checkup($update_siteinfo->errorInfo(), "updating recaptcha_site okay");
			if ($result !== "success"): $result_failure = "failure"; else: $result_success = 1; endif; endif;
		if ($_POST['recaptcha_private'] !== $recaptcha_private):
			$update_siteinfo->execute(["key"=>"recaptcha_private", "value"=>trim($_POST['recaptcha_private'])]);
			$result = execute_checkup($update_siteinfo->errorInfo(), "updating recaptcha_private okay");
			if ($result !== "success"): $result_failure = "failure"; else: $result_success = 1; endif; endif;
		endif;

	// update two-factor authentication
	if (in_array($_POST['update'], ["google_authenticator_on", "google_authenticator_off"])):
		$value_temp = "on"; if ($_POST['update'] == "google_authenticator_off"): $value_temp = "off"; endif;
		$update_siteinfo->execute(["key"=>"google_authenticator_toggle", "value"=>$value_temp]);
		$result = execute_checkup($update_siteinfo->errorInfo(), "updating google_authenticator_toggle okay");
		if ($result !== "success"): $result_failure = "failure"; else: $result_success = 1; endif;
		endif;

	endif;

if (!(empty($_POST['update'])) && ($_POST['update'] == "check_authenticator")):
	if ($_POST[$login['user_id']]['check_authenticator'] == code_generator($login['authenticator'])):
		echo "<p style='color: olive; margin: 20px auto; text-align: center; font-style: italic; font-weight: 700;'>";
		echo "six-digit authenticator code succeeded</p>";
	else:
		echo "<p style='color: crimson; margin: 20px auto; text-align: center; font-style: italic; font-weight: 700;'>";
		echo "six-digit authenticator code failed</p>";
		endif;
	endif;

if (!(empty($_POST[$login['user_id']]['name'])) && ($_POST[$login['user_id']]['name'] !== $login['name'])):
	$values_temp = [
		"user_id"=>$login['user_id'],
		"name"=>trim($_POST[$login['user_id']]['name']) ];
	$sql_temp = sql_setup($values_temp, "$database.users");
	$update_userinfo = $connection_pdo->prepare($sql_temp);
	$update_userinfo->execute($values_temp);
	$result = execute_checkup($update_userinfo->errorInfo(), "updating your name okay");
	if ($result == "success"):
//		$login['name'] = trim($_POST[$login['user_id']]['name']);
		$redirect_update = 1; 
	else:
		$result_failure = "failure"; endif; endif;


if (!(empty($_POST[$login['user_id']]['email'])) && ($_POST[$login['user_id']]['email'] !== $login['email'])):
	$values_temp = [
		"user_id"=>$login['user_id'],
		"email"=>trim($_POST[$login['user_id']]['email']) ];
	$sql_temp = sql_setup($values_temp, "$database.users");
	$update_userinfo = $connection_pdo->prepare($sql_temp);
	$update_userinfo->execute($values_temp);
	$result = execute_checkup($update_userinfo->errorInfo(), "updating your email okay");
	if ($result == "success"):
		$login['email'] = trim($_POST[$login['user_id']]['email']);
		$redirect_update = 1;
	else:
		$result_failure = "failure"; endif; endif;


if (!(empty($_POST[$login['user_id']]['password_one'])) || !(empty($_POST[$login['user_id']]['password_two']))):

	$password_temp = [trim($_POST[$login['user_id']]['password_one']), trim($_POST[$login['user_id']]['password_two'])];

	if ($result_failure == "failure"):
		echo "<p style='margin: 20px auto; text-align: center; font-style: italic;'>";
		echo "password not updated<br><br></p>";
	elseif (empty($password_temp[0]) || empty($password_temp[1])):
		$redirect_update = 0;
		echo "<p style='margin: 20px auto; text-align: center; font-style: italic; color: crimson;'>";
		echo "passwords did not match, please try again<br><br></p>";
	elseif ($password_temp[0] !== $password_temp[1]):
		$redirect_update = 0;
		echo "<p style='margin: 20px auto; text-align: center; font-style: italic; color: crimson;'>";
		echo "passwords did not match, please try again<br><br></p>";
	else:
		$values_temp = [
			"user_id"=>$login['user_id'],
			"hash"=>sha1(strtolower($login['email']).$password_temp[0]) ];
		$sql_temp = sql_setup($values_temp, "$database.users");
		$update_userinfo = $connection_pdo->prepare($sql_temp);
		$update_userinfo->execute($values_temp);
		$result = execute_checkup($update_userinfo->errorInfo(), "updating your password<br><br>", "full");
		endif;
	endif;


if (!(empty($_POST['add_email']))):
	$existing_temp = 0;
	foreach ($users_list as $user_info):
		if ($user_info['email'] == $_POST['add_email']): $exiting_temp = 1; endif;
		endforeach;
	if ($existing_temp == 1):
		echo "<p style='margin: 20px auto; text-align: center; font-style: italic;'>";
		echo "email address is already in use</p>"; endif;
	if ($existing_temp == 0):
		// add user, with default pwd
		endif;
	endif;

if ($result_success == 1): echo '<script> window.location.replace("https://'.$domain.'/'.$page_temp.'/"); </script>'; endif;

echo "<style> input { width: 400px; max-width: 80%; border-radius: 0px; font-size: 17px; padding: 15px; margin: 20px auto 0; display: block; } </style>";
echo "<style> .button_action { font-size: 50px; margin: 40px auto; display: block; max-width: 60px; max-height: 60px; text-align: center; } </style>";

echo "<form action='' method='post'>";

if ($page_temp == "account"):

	echo "<h2 style='margin: 100px auto 40px; text-align: center;'>My Account</h2>";

	echo "<input type='email' name='".$login['user_id']."[email]' value='".htmlspecialchars($login['email'])."' placeholder='email' required><br>";
	echo "<input type='text' name='".$login['user_id']."[name]' value='".htmlspecialchars($login['name'])."' placeholder='name' required><br>";
	echo "<input type='password' name='".$login['user_id']."[password_one]' placeholder='enter new password' autocomplete='off'><br>";
	echo "<input type='password' name='".$login['user_id']."[password_two]' value='' placeholder='retype new password' autocomplete='off'><br>";

	echo "<p style='margin: 40px auto 20px; text-align: center; font-style: italic;'><b>confirm your identity to update your account</b></p>";
	echo "<input type='password' name='".$login['user_id']."[password_one]' placeholder='authenticator code' required><br>";
	echo "<p style='margin: 0 auto 5px; text-align: center; font-style: italic;'>or</p>";
	echo "<input type='password' name='".$login['user_id']."[password_one]' placeholder='current password' required><br>";

	echo "<button type='submit' name='update' value='account' class='material-icons button_action'>save</button>";

	echo "</form>";

endif;

if ($page_temp == "two-factor"):

	echo "<h2 style='margin: 100px auto 40px; text-align: center;'>Two-Factor</h2>";

	echo "<p style='margin: 0 auto 5px; text-align: center; font-style: italic;'>compatible with Google Authenticator and DUO</p>";
	echo '<script>
	$(document).ready(function(){
		$("#qrcode").click(function(){
			if (!$("#qrcode").is(":animated")) {
				$("#qrcode").fadeTo(500,1);
				$("#qrcode").delay(2000).fadeTo(500,0,"swing"); }
			});
		});
	</script>';
	echo "<div style='position: relative; background: rgba(0,0,0,0.5); display: block; width: 400px; height: 400px; padding: 2px 2px 0 2px; margin: 0 auto; text-align: center;'>";
	echo "<span class='button material-icons' style='color: rgba(255,255,255,1); position: absolute; z-index: 100; left: 50%; top: 50%; margin: -20px 0 0 -20px;'>visibility</span>";
	echo "<div id='qrcode' style='padding: 0; margin: 0; opacity: 0; z-index: 1000; position: relative;'></div></div>";
	$shortlink_code = 'otpauth://totp/'.$login['email'].'?secret='.encode_thirtytwo($login['authenticator']).'&issuer='.$domain;
	echo '<script type="text/javascript">
        var element = document.getElementById("qrcode");
        var bodyElement = document.body;
        element.appendChild(showQRCode("'.$shortlink_code.'"));
	</script>';
	echo "<br><br><a href='".$shortlink_code."' class='button material-icons button_action'>exit_to_app</a>";

	endif;

if ((count($users_list) == 1) || ($login['status'] == "admin")):

	if ($page_temp == "settings"):

		echo "<h2 style='margin: 120px auto 40px; text-align: center;'>Site information</h2>";

		echo "<input type='text' name='publisher' value='".htmlspecialchars($publisher)."' placeholder='My Website'><br>";
		echo "<input type='text' name='google_analytics_code' value='".htmlspecialchars($google_analytics_code)."' placeholder='Google Analytics code (UA-*******-*)'><br>";
		echo "<input type='color' name='color' value='".htmlspecialchars($color)."' placeholder='background colour'><br>";
		echo "<textarea name='description' placeholder='description'>".htmlspecialchars($description)."</textarea>";

		echo "<button type='submit' name='update' value='settings' class='material-icons button_action'>save</button>";

		endif;

	if ($page_temp == "security"):

		echo "<h2 style='margin: 120px auto 40px; text-align: center;'>reCAPTCHA</h2>";
		echo "<p style='margin: 20px auto; text-align: center; font-style: italic;'>";
		echo "reCAPTCHA is highly recommended against brute force attacks<br>";
		echo "obtain your keys at <a href='https://www.google.com/recaptcha/admin'>google.com/recaptcha/admin</a><br>";
		echo "enter ".$domain." as your domain to avoid being locked out</p>";

		echo "<input type='text' name='recaptcha_site' value='".htmlspecialchars($recaptcha_site)."' placeholder='reCAPTCHA site'><br>";
		echo "<input type='text' name='recaptcha_private' value='".htmlspecialchars($recaptcha_private)."' placeholder='reCAPTCHA private'><br>";

		echo "<button type='submit' name='update' value='recaptcha' class='material-icons button_action'>save</button>";

		$value_temp = "google_authenticator_off"; $phrase_temp = "active"; $icon_temp = "lock";
		if ($google_authenticator_toggle == "off"):
			$value_temp = "google_authenticator_on"; $phrase_temp = "off"; $icon_temp = "lock_open"; endif;

		echo "<h2 style='margin: 120px auto 40px; text-align: center;'>Two-Factor Authentication</h2>";
		echo "<p style='margin: 20px auto; text-align: center; font-style: italic;'>";
		echo "<b>two-factor authenticiation is currently ".$phrase_temp."</b>";
		if ($google_authenticator_toggle !== "off"): echo "<br>any users without 2fa require a password reset"; endif;
		echo "</p>";

		echo "<button type='submit' name='update' value='".$value_temp."' class='material-icons button_action'>".$icon_temp."</button>";

		endif;

	if ($page_temp == "supervisor"):

		echo "<form>";
		echo "<input type='email' name='add_email' placeholder='add new user (email address)'><br>";
		echo "<button type='submit' name='update' value='add_user' class='material-icons button_action'>add_circle</button>";
		echo "</form>";

		echo "<hr>";

		echo "<h2 style='margin: 120px auto 40px; text-align: center;'>User list</h2>";

		echo "<table><tbody>";
		foreach ($users_list as $user_id => $user_info):
			if ($user_info['status'] == "deactivated"): continue; endif;
			echo "<tr><td>".$user_id."</td><td>".$user_info['email']."</td><td>".$user_info['status']."</td><td>deactivate</td><td>reset password</td></tr>";
			endforeach;
		foreach ($users_list as $user_id => $user_info):
			if ($user_info['status'] !== "deactivated"): continue; endif;
			echo "<tr><td>".$user_id."</td><td>".$user_info['email']."</td><td>".$user_info['status']."</td><td>activate</td><td></td></tr>";
			endforeach;
		echo "</tbody></table>";

		endif;
	endif;

echo "</form>";

footer(); ?>
