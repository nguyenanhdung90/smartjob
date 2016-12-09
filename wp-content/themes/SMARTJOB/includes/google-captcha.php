<?php
/**
* Author: Dakachi
* Date created: 10-03-2014
* Description: Google recaptcha class
* GOOGLE CAPTCHA TUTORIAL
* To use google captcha, you need to have 2 variables public key and private key
* Register at https://www.google.com/recaptcha/admin/create
* //======================================================
* To generate Google recaptcha box:
* //========================================
* $GCaptcha = DCGoogleCaptcha::getInstance();
* $publicKey = '';
* $GCaptcha->generateCaptchaBox($publicKey)
* //======================================================
* To check words:
* //========================================
* $GCaptcha = DCGoogleCaptcha::getInstance();
* $privateKey = '';
* if ($GCaptcha->checkCaptcha($privateKey)) {
*   //Correct result
* }
* else {
*   //Incorrect result
* }
* //======================================================
*/

class ET_GoogleCaptcha
{
    private static $_instance;
    public function __construct(){ }
    private function __clone() {}

    public static function getInstance(){
        if ( ! self::$_instance instanceof ET_GoogleCaptcha )
            self::$_instance = new ET_GoogleCaptcha();
        return self::$_instance;
    }

    /**
    * generate captcha box to check security
    *
    * @param mixed $publicKey
    */
    public function generateCaptchaBox($customize = false)
    {
        $key        =   $this->get_api();
        $publicKey =   $key['public_key'];

        echo "<div style='width: 100%'>" . recaptcha_get_html( $publicKey, null, false, $customize,"recaptcha_widget") . '</div>';
    }
    public function generateCaptchaBoxSecond($customize = false,$div = "recaptcha_two")
    {
        $key        =   $this->get_api();
        $publicKey =   $key['public_key'];

        echo "<div style='width: 100%'>" . recaptcha_get_html( $publicKey, null, false, $customize, "recaptcha_two") . '</div>';
    }

    /**
    * check words typed correctly
    *
    * @param mixed $privateKey
    */
    public function checkCaptcha($challenge , $response )
    {
        $key        =   $this->get_api();
        $privateKey =   $key['private_key'];

        $bResult = false;
        if ( $response ) {
            $response = recaptcha_check_answer($privateKey, $_SERVER['REMOTE_ADDR'], $challenge, $response);

            if ($response->is_valid) {
                $bResult = true;
            }
        }
        return $bResult;
    }

    public static function get_api () {
        return get_option('et_google_api_key', array (
                                    'private_key' =>  /*'6LdmzO8SAAAAALkfFCb7Twppu4axyXtjm4maJ82Y'*/ '' , 
                                    'public_key' =>  '' /*'6LdmzO8SAAAAAOQgKCsol68zZ4ob8W4AFxss8USn'*/ )
                );
    }

    public static function set_api ( $api ) {
        update_option( 'et_google_api_key' , $api );
    }

}
add_action( 'init' , 'et_init_setup_captcha' );
function et_init_setup_captcha () {
    $job_option =   ET_JobOptions::get_instance();
    $useCaptcha =   $job_option->use_captcha () ;
    if($useCaptcha) {
        // je_mobile_job_post_form_fields
        /*
         *render captcha into post job form
        */
        add_action( 'je_post_job_after_author_info' , 'render_captcha_post_job' );


        add_action( 'je_jobseeker_signup_form' , 'render_captcha' );
        add_action( 'je_contact_jobseeker_form' , 'render_captcha' );
        add_action( 'je_apply_job_form' , 'render_captcha' );

        /*
        * render captcha into modal register form.
        */
        add_action( 'je_end_modal_login' , 'render_captcha_register_modal_login' );


        /*
        * render captcha into register form in page-post-job
        */
        add_action( 'je_render_captcha_register_form' , 'render_captcha_register_post_job' );

        /*
         * triger js captcha in page post job
        */
        add_action('wp_footer','je_footer_post_job',1000);
        // mobile
        add_action( 'je_mobile_captcha' , 'render_captcha' );
    }
}
function render_captcha_post_job(){

    $captcha    =   ET_GoogleCaptcha::getInstance();

    if(is_user_logged_in() && !current_user_can('manage_options') ) {
         echo '<div class="form-item captcha-item form-item-type"><div class="label"></div>';
        render_captcha();
        echo '</div>';

    } else if( !is_user_logged_in() ){
        echo '<div class="form-item captcha-item form-item-type"><div class="label"> &nbsp; </div>';
        $captcha->generateCaptchaBoxSecond(false, "recaptcha_three");
        echo '</div>';

    }
}
function render_captcha(){
    if(current_user_can("manage_options"))
        return '';
    $captcha    =   ET_GoogleCaptcha::getInstance();
    echo  "<div class='reCaptchaMain form-item-captcha' id='reCaptcha' >";
    $captcha->generateCaptchaBox();
    echo "</div>";
}

/*
* Trigger render html of captcha into modal register in page-post-job.
*/
function je_footer_post_job(){

    ?>
    <script type="text/javascript">
        (function($) {

            $(document).ready(function(){
                if( typeof Recaptcha != "undefined" ){

                    Recaptcha._alias_finish_reload = Recaptcha.finish_reload;
                    Recaptcha.finish_reload = function (challenge, b, c) {
                        //Do stuff with challenge here
                        Recaptcha._alias_finish_reload(challenge, b, c);
                        $('#recaptcha_two input[name=recaptcha_challenge_field]').val(challenge);
                        $('#recaptcha_two img#recaptcha_challenge_image').attr('src', '//www.google.com/recaptcha/api/image?c=' + challenge);
                        $('#recaptcha_three input[name=recaptcha_challenge_field]').val(challenge);
                        $('#recaptcha_three img#recaptcha_challenge_image').attr('src', '//www.google.com/recaptcha/api/image?c=' + challenge);
                    }
                }
            });

        })(jQuery);
    </script>
    <?php
}
/*
* render captcha for regiter form in page post -job.
*/
function render_captcha_register_modal_login(){
    if( is_page_template('page-post-a-job.php') ){

        $captcha    =   ET_GoogleCaptcha::getInstance();
        $captcha->generateCaptchaBoxSecond();

    } else{
        render_captcha();
    }

}

/*
* render captcha into register form in page-post -job
*/
function render_captcha_register_post_job(){
    if(et_is_mobile()){
        render_captcha();
    } else if(!is_user_logged_in()){
         $captcha    =   ET_GoogleCaptcha::getInstance();
        // user html from register form and append html into this area.
        echo  "<div class='reCaptchaMain form-item-recaptcha form-item-type' id='reCaptcha' ><div class='label'></div>";

        $captcha->generateCaptchaBox(true);
        echo "</div>";
    }

}
/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * The reCAPTCHA server URL's
 */
define("RECAPTCHA_API_SERVER", "http://www.google.com/recaptcha/api");
define("RECAPTCHA_API_SECURE_SERVER", "https://www.google.com/recaptcha/api");
define("RECAPTCHA_VERIFY_SERVER", "www.google.com");

/**
 * Encodes the given data into a query string format
 * @param $data - array of string elements to be encoded
 * @return string - encoded request
 */
function _recaptcha_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req=substr($req,0,strlen($req)-1);
        return $req;
}



/**
 * Submits an HTTP POST to a reCAPTCHA server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function _recaptcha_http_post($host, $path, $data, $port = 80) {

        $req = _recaptcha_qsencode ($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                die ('Could not open socket');
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
                $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
}



/**
 * Gets the challenge HTML (javascript and non-javascript version).
 * This is called from the browser, and the resulting reCAPTCHA HTML widget
 * is embedded within the HTML form it was called from.
 * @param string $pubkey A public key for reCAPTCHA
 * @param string $error The error given by reCAPTCHA (optional, default is null)
 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)
 * @return string - The HTML to be embedded in the user's form.
 */
function recaptcha_get_html ($pubkey, $error = null, $use_ssl = false, $customize = false, $id = "recaptcha_widget")
{
    if ($pubkey == null || $pubkey == '') {
        die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
    }

    if ($use_ssl)
        $server = RECAPTCHA_API_SECURE_SERVER;
    else
        $server = RECAPTCHA_API_SERVER;
    $errorpart = "";
    if ($error) {
       $errorpart = "&amp;error=" . $error;
    } ?>
        <script type="text/javascript">
             var RecaptchaOptions = {
                theme : 'custom',
                custom_theme_widget: '<?php echo $id;?>'
             };
        </script>
        <div id="<?php echo $id;?>" class="recaptcha-wrap">

            <div class="control-group">
                <div class="controls">
                    <a id="recaptcha_image" href="#" class="thumbnail"></a>
                    <?php if($id != "recaptcha_widget"){?>
                            <input name="recaptcha_challenge_field" class="recaptcha_challenge_field" type="hidden" />
                            <img id="recaptcha_challenge_image" class="recaptcha_challenge_image" src="" />

                    <?php } ?>

                    <div class="recaptcha_only_if_incorrect_sol" style="color:red; display:none;"><?php _e("Incorrect please try again", ET_DOMAIN); ?></div>
                </div>
            </div>

            <div class="control-group">
                <label class="recaptcha_only_if_image control-label"><?php _e("Enter the words above:", ET_DOMAIN); ?></label>
                <div class="controls">
                    <div class="input-append">
                        <input type="text" tabindex=3 id="recaptcha_response_field" name="recaptcha_response_field" class="required input-recaptcha" />
                        <span style="display:none" class="msg-custom"></span>
                        <span class="google-action">
                        <a class="button " href="javascript:Recaptcha.reload()"><i  data-icon="0" class="icon "></i></a>
                        <a class="button" href="javascript:Recaptcha.showhelp()"><i data-icon="?" class="icon"></i></a>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <script type="text/javascript"
           src="<?php echo $server . '/challenge?k=' . $pubkey . $errorpart; ?>">
        </script>

        <noscript>
            <iframe src="<?php echo $server . '/challenge?k=' . $pubkey . $errorpart; ?>"
               height="300" width="500" frameborder="0"></iframe><br>
            <textarea name="recaptcha_challenge_field" rows="3" cols="40">
            </textarea>
            <input type="hidden" name="recaptcha_response_field"
               value="manual_challenge">
        </noscript>

        <?php
        return '';
}




/**
 * A ReCaptchaResponse is returned from recaptcha_check_answer()
 */
class ReCaptchaResponse {
        var $is_valid;
        var $error;
}


/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @param array $extra_params an array of extra variables to post to the server
  * @return ReCaptchaResponse
  */
function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $extra_params = array()){
    if ($privkey == null || $privkey == '') {
        die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
    }

    if ($remoteip == null || $remoteip == '') {
        die ("For security reasons, you must pass the remote ip to reCAPTCHA");
    }
    //discard spam submissions
    if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
            $recaptcha_response = new ReCaptchaResponse();
            $recaptcha_response->is_valid = false;
            $recaptcha_response->error = 'incorrect-captcha-sol';
            return $recaptcha_response;
    }

    $response = _recaptcha_http_post (RECAPTCHA_VERIFY_SERVER, "/recaptcha/api/verify",
                                      array (
                                             'privatekey' => $privkey,
                                             'remoteip' => $remoteip,
                                             'challenge' => $challenge,
                                             'response' => $response
                                             ) + $extra_params
                                      );

    $answers = explode ("\n", $response [1]);
    $recaptcha_response = new ReCaptchaResponse();

    if (trim ($answers [0]) == 'true') {
            $recaptcha_response->is_valid = true;
    }
    else {
            $recaptcha_response->is_valid = false;
            $recaptcha_response->error = $answers [1];
    }
    return $recaptcha_response;

}

/**
 * gets a URL where the user can sign up for reCAPTCHA. If your application
 * has a configuration page where you enter a key, you should provide a link
 * using this function.
 * @param string $domain The domain where the page is hosted
 * @param string $appname The name of your application
 */
function recaptcha_get_signup_url ($domain = null, $appname = null) {
    return "https://www.google.com/recaptcha/admin/create?" .  _recaptcha_qsencode (array ('domains' => $domain, 'app' => $appname));
}

function _recaptcha_aes_pad($val) {
    $block_size = 16;
    $numpad = $block_size - (strlen ($val) % $block_size);
    return str_pad($val, strlen ($val) + $numpad, chr($numpad));
}

/* Mailhide related code */

function _recaptcha_aes_encrypt($val,$ky) {
    if (! function_exists ("mcrypt_encrypt")) {
        die ("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.");
    }
    $mode=MCRYPT_MODE_CBC;
    $enc=MCRYPT_RIJNDAEL_128;
    $val=_recaptcha_aes_pad($val);
    return mcrypt_encrypt($enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
}


function _recaptcha_mailhide_urlbase64 ($x) {
    return strtr(base64_encode ($x), '+/', '-_');
}

/* gets the reCAPTCHA Mailhide url for a given email, public key and private key */
function recaptcha_mailhide_url($pubkey, $privkey, $email) {
    if ($pubkey == '' || $pubkey == null || $privkey == "" || $privkey == null) {
        die ("To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
             "you can do so at <a href='http://www.google.com/recaptcha/mailhide/apikey'>http://www.google.com/recaptcha/mailhide/apikey</a>");
    }

    $ky = pack('H*', $privkey);
    $cryptmail = _recaptcha_aes_encrypt ($email, $ky);

    return "http://www.google.com/recaptcha/mailhide/d?k=" . $pubkey . "&c=" . _recaptcha_mailhide_urlbase64 ($cryptmail);
}

/**
 * gets the parts of the email to expose to the user.
 * eg, given johndoe@example,com return ["john", "example.com"].
 * the email is then displayed as john...@example.com
 */
function _recaptcha_mailhide_email_parts ($email) {
    $arr = preg_split("/@/", $email );

    if (strlen ($arr[0]) <= 4) {
        $arr[0] = substr ($arr[0], 0, 1);
    } else if (strlen ($arr[0]) <= 6) {
        $arr[0] = substr ($arr[0], 0, 3);
    } else {
        $arr[0] = substr ($arr[0], 0, 4);
    }
    return $arr;
}

/**
 * Gets html to display an email address given a public an private key.
 * to get a key, go to:
 *
 * http://www.google.com/recaptcha/mailhide/apikey
 */
function recaptcha_mailhide_html($pubkey, $privkey, $email) {
    $emailparts = _recaptcha_mailhide_email_parts ($email);
    $url = recaptcha_mailhide_url ($pubkey, $privkey, $email);
    return htmlentities($emailparts[0]) . "<a href='" . htmlentities ($url) .
        "' onclick=\"window.open('" . htmlentities ($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities ($emailparts [1]);

}


?>
