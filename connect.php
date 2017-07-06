<?php
// Error reporting settings
// Op dit moment worden alle errors weergegeven, de code is namelijk in debug
error_reporting(0);
ini_set('display_errors', 0);
// End error reporting settings

header('Content-Type: text/html; charset=UTF-8');

function sec_session_start() {
    $session_name = 'sec_session_id';   // Set a custom session name
    /*Sets the session name.
     *This must come before session_set_cookie_params due to an undocumented bug/feature in PHP.
     */
    session_name($session_name);

    $secure = false;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        die('Er is een probleem met uw browser.');
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"],
        $cookieParams["path"],
        $cookieParams["domain"],
        $secure,
        $httponly);

    session_start();            // Start the PHP session
    session_regenerate_id(true);    // regenerated the session, delete the old one.
}

sec_session_start();

try{								// Proberen verbinding te maken met de database
	$pdo = new PDO('mysql:host=37.139.27.235;dbname=catering-thai;port=3306', 'kasper', 'Test123');
}
catch(PDOException $error){			// Als de database connectie mislukt
	die('Er is een fout opgetreden.<br>'.$error);	// Error wordt op dit moment weergegeven, omdat we in ontwikkelfase zitten
}

$lang = 'NL_nl';
$url = parse_url($_SERVER['REQUEST_URI']);
$urlpath = rtrim($url['path'],"/");

if(!isset($admin) || $admin != true){
	function __autoload($className) {
      if (file_exists('../../classes/'.$className . '.php')) {
          require_once '../../classes/'.$className . '.php';
          return true;
      }
      return false; 
	} 
	
	if (isset($_GET['lang'])) {
        Cookie::put('lang', $_GET['lang']);
        Redirect::to($urlpath);
    }

    if (Cookie::exists('lang')) {
        $lang = Cookie::get('lang');
    } elseif (isset($_GET['lang'])) {
        $lang = $_GET['lang'];
    } else {
        $lang = 'NL_nl';
    }

    switch ($lang) {
        case 'NL_nl':
            $lang_id = 1;
            break;
        case 'EN_en':
            $lang_id = 2;
            break;
        case 'DE_de':
            $lang_id = 3;
            break;
        default:
            $lang_id = 1;
            break;
    }

    $translation = new Translation($lang);
    $json = $translation->getJson();
}
else{
	function __autoload($className) {
      if (file_exists('../../admin/classes/'.$className . '.php')) {
          require_once '../../admin/classes/'.$className . '.php';
          return true;
      }
      return false;
	} 
}

$userRepository = new UserRepository($pdo);
$login = new Login($userRepository);

if(Session::exists('user')) {
    $customer_id = $userRepository->getCustomerID(Session::get('user'));
}

