<? // html_header, amp_header, admin_bar, footer, login, notfound

function html_header($title=null, $canonical=null) {
	global $domain;
	global $publisher;
	global $color;
	global $google_analytics_code;
	global $page_temp;
	global $slug_temp;
	global $command_temp;
	if (empty($title)): $title = $domain; endif;

	echo "<!doctype html>" . "<html lang='en'>" . "<head>" . "<meta charset='utf-8'>";

	echo "<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>";
	echo "<link rel='stylesheet' href='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css'>";
	echo "<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>";
	
	// recaptcha js
	if ($page_temp == "account"):
		echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
		endif;
	
	// qr code js
	if ($page_temp == "two-factor"):
		echo "<script src='https://".$domain."/qrcode.js'></script>";
		echo "<script src='https://".$domain."/html5-qrcode.js'></script>";
		endif;
	
	if (empty($canonical)): $canonical=$domain; endif; // do some sort of url validation here
	echo "<link rel='canonical' href='https://$canonical'>"; // must define canonical url for amp

	echo "<title>" . $title . "</title>";
	echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';

	echo "<style>";
	include_once('style.css');
	include_once('style_nesty.css');
	echo "</style>";

	if (!(empty($google_analytics_code))):
		echo "<script>"; ?>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
			ga('create', '<? echo $google_analytics_code ?>', 'auto');
			ga('send', 'pageview');
		<? echo "</script>"; endif;

	echo "</head><body>";
	
	global $page_confirmed;
	global $_SESSION; }


function amp_header($title=null, $canonical=null) {
	global $domain;
	global $publisher;
	global $google_analytics_code;
	global $color;
	global $page_temp;
	global $slug_temp;
	global $command_temp;
	global $_SESSION;
	global $page_confirmed;
	if (empty($title)): $title = $domain; endif;

	// https://www.ampproject.org/docs/tutorials/create/basic_markup

	// these must open the document
	echo "<!doctype html>" . "<html amp lang='en'>";

	// open html head
	echo "<head>" . "<meta charset='utf-8'>";

	// for google analytics, this must precede amp js
	if (!(empty($google_analytics_code))):
		echo '<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>';
		endif;

	// amp js
	echo "<script async src='https://cdn.ampproject.org/v0.js'></script>";

	if (empty($canonical)): $canonical=$domain; endif; // do some sort of url validation here
	echo "<link rel='canonical' href='https://$canonical'>"; // must define canonical url for amp

	// amp boilerplate code https://www.ampproject.org/docs/reference/spec/amp-boilerplate
	echo "<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>";

	// for amp-form
	echo '<script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>';

	// mostly for show-more features
	echo '<script async custom-element="amp-carousel" src="https://cdn.ampproject.org/v0/amp-carousel-0.1.js"></script>';
	
	// for lightbox search feature
	echo '<script async custom-element="amp-lightbox" src="https://cdn.ampproject.org/v0/amp-lightbox-0.1.js"></script>';

	// for text fitting on images in particular
	echo '<script async custom-element="amp-fit-text" src="https://cdn.ampproject.org/v0/amp-fit-text-0.1.js"></script>';	
	
	// for the parallax
	echo '<script async custom-element="amp-fx-collection" src="https://cdn.ampproject.org/v0/amp-fx-collection-0.1.js"></script>';

	echo "<title>" . $title . "</title>";
	echo '<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">';
	
//	echo "<base href='/' />";
	echo "<meta name='viewport' content='width=device-width,minimum-scale=1,initial-scale=1'>"; // must define viewport for amp

	echo "<style amp-custom>";
	include_once('style.css');
	include_once('style_nesty.css');
	echo "</style>";

	echo "</head><body>";
	
	if (!(empty($google_analytics_code))):
		echo '<amp-analytics type="googleanalytics">';
		echo '<script type="application/json">';
		$google_analytics_array = [
			"vars" => ["account"=>$google_analytics_code],
			"triggers" => ["trackPageview" => ["on"=>"visible", "request"=>"pageview"] ] ];
		echo json_encode($google_analytics_array);
		echo '</script></amp-analytics>';
		endif;

	// if there is no need for a search header
//	if (array_intersect([$slug_temp, $page_temp], ["new", "edit", "account"])): return; endif;
	
	echo "<amp-carousel height='150' layout='fixed-height' type='slides' id='navigation-carousel' class='navigation-carousel' data-parallax-factor='1.5'>";
	
	echo "<div class='background_1'>";

	echo "<div role='button' on='tap:navigation-carousel.goToSlide(index=1)' id='navigation-search-button'>search</div>";

	global $login;
	if (empty($login)): echo "<a href='/account/'><div id='navigation-signin-button'>sign in</div></a>"; endif;
	if (!(empty($login)) && ($login['cookie_time'] == "logged in")): echo "<a href='/account/'><div id='navigation-loggedin-button'>settings</div></a>"; endif;
	if (!(empty($login)) && ($login['cookie_time'] !== "logged in")): echo "<div id='navigation-loggedin-time'>time...</div>"; endif;

	echo "</div>";
	
	echo "<div class='background_2'>";

	echo "<div role='button' on='tap:navigation-carousel.goToSlide(index=0)' clas='navigation-search-back-button'>back</div>";

	echo "<div class='navigation-search-sitemap'>open sitemap</div>";

	echo "<div class='navigation-search-sitemap'>open history</div>";

	echo "<hr>";
	
	$search_value = null;
	if (array_intersect([$slug_temp, $page_temp], ["search"])): $search_value = htmlspecialchars($_SESSION['term'], ENT_QUOTES); endif;
	echo "<form method='get' action='/search/' target='_top'>";
	echo "<input type='search' name='term' placeholder='search' value='".$search_value."' maxlength='45' autocomplete='off' required>";	
	echo "</form>";
		
	echo "</div>";
	
	echo "</amp-carousel>";
	
	}

function admin_bar($login=null, $entry_confirmed=null) {
	global $publisher;
	global $page_temp;
	global $slug_temp;
	global $command_temp;
	
	echo "<div class='bottom_bar background_2'>";

	echo "<span class='button float_left'></span>";
	
	if (empty($page_temp) || in_array($page_temp, ["sitemap", "schedule"])):
		
		$chosen_temp = null; if (empty($page_temp)): $chosen_temp = "chosen"; endif;
		echo "<a href='/' class='material-icons button float_left ".$chosen_temp."'>dashboard</a>";

		$chosen_temp = null; if ($page_temp == "sitemap"): $chosen_temp = "chosen"; endif;
		echo "<a href='/sitemap/' class='material-icons button float_left ".$chosen_temp."'>format_list_bulleted</a>";
	
		$chosen_temp = null; if ($page_temp == "schedule"): $chosen_temp = "chosen"; endif;
		echo "<a href='/schedule/' class='material-icons button float_left ".$chosen_temp."'>schedule</a>";
	
		endif;
	
	if ($page_temp == "search"):	
	
		$chosen_temp = null; if (empty($slug_temp)): $chosen_temp = "chosen"; endif;
		echo "<a href='/search/' class='material-icons button float_left ".$chosen_temp."'>dashboard</a>";

		$chosen_temp = null; if ($slug_temp == "listing"): $chosen_temp = "chosen"; endif;
		echo "<a href='/search/listing/' class='material-icons button float_left ".$chosen_temp."'>format_list_bulleted</a>";
	
		endif;
	

	echo "<span class='button float_right'></span>";
	
	if (!(empty($page_temp))): echo "<a href='/' class='material-icons button float_right'>home</a>"; endif;

	if (!(empty($login)) && ($page_temp == "account")): echo "<a href='/logout/' class='material-icons button float_right'>cancel</a>";
	else: echo "<a href='/account/' class='material-icons button float_right'>account_circle</a>"; endif;

	if (!(empty($login)) && ($page_temp !== "account")):
		echo "<a href='/new/' class='material-icons button float_right'>note_add</a>";
		echo "<a href='/add/' class='material-icons button float_right'>playlist_add</a>";
		if (!(empty($entry_confirmed['page_id']))):
			echo "<a href='/".$entry_confirmed['page_id']."/edit/' class='material-icons button float_right'>edit</a>";
			endif; 
			if (!(empty($entry_confirmed['media_id']))):
			echo "<a href='/m/".$entry_confirmed['media_id']."/edit/' class='material-icons button float_right'>edit</a>";
			endif; endif;
	
	global $_SESSION;
	if (!(empty($entry_confirmed['password'])) && !(empty($_SESSION[$entry_confirmed['page_id']]))):
		echo "<a href='/".$entry_confirmed['page_id']."/*/' class='material-icons button float_right'>lock</a>";
		endif;

	echo "</div>"; }


function footer() {
	echo "<div class='footer_spacer'>&nbsp;</div>";
	echo "</body></html>"; exit; }


function login($disclaimer=null) {
	global $_POST;
	global $publisher;
	global $google_authenticator_toggle;
	global $slug_temp;
	if (empty($disclaimer) && ($slug_temp == "2")): $disclaimer = "login failed"; endif;
	html_header("login");
	if (isset($_POST['checkpoint_email'])): $email = $_POST['checkpoint_email']; else: $email = null; endif;
	echo "<div class='login'>";

	echo "<form method='post' action=''>";
	if (empty($disclaimer)): $disclaimer = $publisher; endif;
	echo "<span>$disclaimer</span>";
	foreach ((array)$_POST as $name_temp => $value_temp):
		if (is_array($value_temp)):
			foreach ($value_temp as $name_temp_temp => $value_temp_temp): echo "<input type='hidden' name='".$name_temp_temp."[]' value='$value_temp_temp'>"; endforeach;
		else:
			echo "<input type='hidden' name='$name_temp' value='$value_temp'>"; endif; endforeach;
	echo "<input type='email' name='checkpoint_email' placeholder='email' value='$email' autocomplete='off' required>";
//	echo "<input type='password' name='checkpoint_password' placeholder='password' autocomplete='off' required>";
	if ($google_authenticator_toggle == "on"):
		echo "<input type='number' name='checkpoint_authenticator' placeholder='authenticator code' autocomplete='off' max='999999' required>";
		endif;
	// if captcha key exists
	global $recaptcha_site; global $recaptcha_private; global $recaptcha_override;
//	if (!(empty($recaptcha_site)) && !(empty($recaptcha_private)) && ($recaptcha_override !== "yes")):
		echo '<script> $(document).on("keypress", "input", function (e) { var code = e.keyCode || e.which; if (code == 13) { e.preventDefault(); return false; } }); </script>';
		echo '<script> function recaptchaval(){ document.getElementById("submit_button").disabled = false; document.getElementById("submit_button").classList.remove("gray_background"); } </script>';
		echo '<script> function recaptchainval(){ document.getElementById("submit_button").disabled = true; document.getElementById("submit_button").classList.add("gray_background"); } </script>';
		echo "<style> .gray_background { background: #333 !important; } </style>";
		echo "<div class='g-recaptcha' data-sitekey='".$recaptcha_site."' data-callback='recaptchaval' data-expired-callback='recaptchainval' style='margin: 10px auto 0; display: inline-block;'></div>";
		echo "<button id='submit_button' type='submit' name='login' value='continue' class='gray_background' disabled>continue</button>";
//	else:
//		echo "<button id='submit_button' type='submit' name='login' value='login'>continue</button>";
//		endif;
	
	echo "</form></div>";
	echo "<a href='/' class='material-icons button'>home</a>";
	echo "</body></html>";
	footer(); }

function notfound () {
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
	amp_header("404");
	echo "<h1>not found</h1>";
	echo "<div class='bottom_bar'><span class='button float_right'></span><a href='/' class='material-icons button float_right'>home</a></div>";
	footer(); } ?>
