<?php
if(!class_exists('Login_Location_Notifier')){
    class Login_Location_Notifier{
        function __construct(){
            $this->lln_admin_menu();
            wp_enqueue_script('login-notification-script', LLN_PLUGIN_URL."javascript/login-notification-script.js");
            wp_enqueue_script('login-notification-validate', LLN_PLUGIN_URL."javascript/jquery.validate.js");
            wp_enqueue_style( 'login-notification-style', LLN_PLUGIN_URL."css/login_location_style.css" );
        }
        function lln_admin_menu(){
            add_action( 'admin_menu',array(&$this,'lln_register_admin_menu'));
            add_action('wp_head', array(&$this,'lln_checkloginloc'));
            add_action('admin_head', array(&$this,'lln_checkloginloc'));
            add_action('wp_ajax_save_ip', array(&$this,'lln_save_login_location_detail'));
            
        }
        public function lln_register_admin_menu(){
            add_menu_page(__('Login Location Notifier','login-location-notifier'), __('Login Location Notifier','login-location-notifier'), 'manage_options', 'login-location', array(&$this,'lln_login_location_notifier' ));
        }
        public function lln_login_location_notifier() {
            require(LLN_PLUGIN_DIRPATH . "views/login-location-notifier.php");
            exit;
        }
        public function lln_getlocIp () {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                return $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                return $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                return  $ip = $_SERVER['REMOTE_ADDR'];
            }
        }
        public function lln_getBrowser() 
        { 
            $u_agent = $_SERVER['HTTP_USER_AGENT']; 
            $bname = 'Unknown';
            $platform = 'Unknown';
            $version= "";
            if (preg_match('/linux/i', $u_agent)) {
                $platform = 'linux';
            }
            elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
                $platform = 'mac';
            }
            elseif (preg_match('/windows|win32/i', $u_agent)) {
                $platform = 'windows';
            }
            if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
            { 
                $bname = 'Internet Explorer'; 
                $ub = "MSIE"; 
            } 
            elseif(preg_match('/Trident/i',$u_agent)) 
            { 
                $bname = 'Internet Explorer'; 
                $ub = "rv"; 
            } 
            elseif(preg_match('/Firefox/i',$u_agent)) 
            { 
                $bname = 'Mozilla Firefox'; 
                $ub = "Firefox"; 
            } 
            elseif(preg_match('/Chrome/i',$u_agent)) 
            { 
                $bname = 'Google Chrome'; 
                $ub = "Chrome"; 
            } 
            elseif(preg_match('/Safari/i',$u_agent)) 
            { 
                $bname = 'Apple Safari'; 
                $ub = "Safari"; 
            } 
            elseif(preg_match('/Opera/i',$u_agent)) 
            { 
                $bname = 'Opera'; 
                $ub = "Opera"; 
            } 
            elseif(preg_match('/Netscape/i',$u_agent)) 
            { 
                $bname = 'Netscape'; 
                $ub = "Netscape"; 
            } 
            $known = array('Version', $ub, 'other');
            $pattern = '#(?<browser>' . join('|', $known) .
             ')[/|: ]+(?<version>[0-9.|a-zA-Z.]*)#';
            if (!preg_match_all($pattern, $u_agent, $matches)) {
            }
            $i = count($matches['browser']);
            if ($i != 1) {
                if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                    $version= $matches['version'][0];
                }
                else {
                    $version= $matches['version'][1];
                }
            }
            else {
                $version= $matches['version'][0];
            }
            if ($version==null || $version=="") {$version="?";}

            return array(
                'userAgent' => $u_agent,
                'name'      => $ub,
                'version'   => $version,
                'platform'  => $platform,
                'pattern'    => $pattern
            );
        } 



        function lln_save_login_location_detail(){
            if(current_user_can( 'manage_options' )){
                $user_id = get_current_user_id();
                $reponse = array();
                check_ajax_referer( 'my-lln-secure-string', 'security' );
                $iploc=sanitize_text_field($_POST['ip']);
                $locdetails = json_decode(file_get_contents("http://ipinfo.io/{$iploc}"));
                $currentdate = date('Y-m-d H:i', time());
                $countryloc = $locdetails->country;
                $city=$locdetails->city;
                $state=$locdetails->region;
                $country=$locdetails->country;
                $postalcode=$locdetails->postal;
                $ip=$locdetails->ip;
                $locarray = array(
                    'ip' => $ip,
                    'city'=>$city,
                    'state'=>$state,
                    'postalcode'=>$postalcode,
                    'country' => $countryloc,
                    'date' => $currentdate
                );
                update_user_meta( $user_id, 'login_location_data', $locarray );
          
            $response['response'] ='Saved';
            header( "Content-Type: application/json" );
            echo json_encode($response);
            exit();
            }
        }

        public function lln_checkloginloc(){
		
            if ( is_user_logged_in() ) {
				
                $user_id = get_current_user_id();
                $iploc = $this->lln_getlocIp();
                $currentdate = date('Y-m-d H:i', time());
                $locdetails = json_decode(file_get_contents("http://ipinfo.io/{$iploc}"));

                if(isset($locdetails->country)){$countryloc = $locdetails->country;}else{$countryloc=false;}
                if(isset($locdetails->city)){$city=$locdetails->city;}else{$city=false;}
                if(isset($locdetails->region)){$state=$locdetails->region;}else{$state=false;}
                if(isset($locdetails->country)){$country=$locdetails->country;}else{$country=false;}
                if(isset($locdetails->postal)){$postalcode=$locdetails->postal;}else{$postalcode=false;}
                if(isset($locdetails->ip)){$ip=$locdetails->ip;}else{$ip=false;}
                $locarray = array(
                    'ip' => $ip,
                    'city'=>$city,
                    'state'=>$state,
                    'postalcode'=>$postalcode,
                    'country' => $countryloc,
                    'date' => $currentdate
                );
     
               if (get_user_meta($user_id, 'login_location_data')) {
                    $getloginlocdata = get_user_meta($user_id, 'login_location_data',true);
                    $country = $getloginlocdata['country'];
                    $date = $getloginlocdata['date'];
                    $compdate = date('Y-m-d H:i', strtotime('- 1 hours'));
					if ($compdate < $date) {
		
                        if($country == $countryloc){
							
                            update_user_meta( $user_id, 'login_location_data', $locarray );
                        }else{
						
                            $siteurl=get_site_url();
                            $recipient = get_userdata( $user_id )->user_email;
                            $admin_email=get_option( 'admin_email' );
                            $admin_blogname=get_option( 'blogname' );
                           //$useragent = $getloginlocdata['user_agent'];
                            $pageTitle = "Sign-in from New Location in: $siteurl";
                            $mailHeaders = "From: $admin_blogname<$admin_email>" . "\r\n";
                            $mailHeaders .= "Reply-To: $admin_blogname<$admin_email>" . "\r\n";
                            $mailHeaders .= "Return-Path: $admin_blogname<$admin_email>" . "\r\n";
                            $mailHeaders .= "Organization: $admin_blogname\r\n";
                            $mailHeaders .= "MIME-Version: 1.0\r\n";
                            $mailHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
                            $mailHeaders .= "X-Priority: 3\r\n";
                            $mailHeaders .= "X-Mailer: PHP". phpversion() ."\r\n";
                            $message = "Hi $recipient,\n\n";
                            $message .= "You recently logged in to $siteurl from new location \n";
                            $message .= "Below are the detail: \n\n";
                            $ua=$this->lln_getBrowser();
                            $binfo="Browser: ".$ua['name']."\n";
                            $binfo.="Operating System: ".$ua['platform']."\n";
                            $binfo.="City : ".$city."\n";
                            $binfo.="State : ".$state."\n";
                            $binfo.="Country Code : ".$postalcode."\n";
                            $binfo.="Postal Code : ".$countryloc."\n";
                            $binfo.="Ip Address : ".$ip."\n\n";
                            $message.=$binfo;
                            $message .= "To ensure your account security,Please make sure to update your passowrd regulary. \n\n";
                           $message .= "Thanks";
                           update_user_meta( $user_id, 'login_location_data', $locarray );
                           mail($recipient, $pageTitle, $message, $mailHeaders);

						   
                        }
                  }else{
                       update_user_meta( $user_id, 'login_location_data', $locarray );
                  }
                }else {
                    add_user_meta( $user_id, 'login_location_data', $locarray, true );
                }
            }
        }
        
    }
}
?>