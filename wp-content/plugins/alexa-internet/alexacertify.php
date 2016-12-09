<?php
/*
Plugin Name: Alexa Claim and Certify
Plugin URI: http://www.alexa.com/
Description: The official Alexa plugin for WordPress.
Version: 2.5
Author: Alexa Internet
Author URI: http://www.alexa.com/
Text Domain: alexa-internet
*/

if(!class_exists('WP_Alexa_Certify')) {
    
    class WP_Alexa_Certify {                

        public function __construct() {        
            $this->plugin_basename = plugin_basename(__FILE__); 
            $this->admin_page_id = "Alexa-Internet";           
            $this->certify_snippet_oid = "alexacertify_certify";
            $this->verify_tag_oid = "alexacertify_verify";            
            $this->admin_init();
        }

        /********************
         * Plugin Functions *
         ********************/

        /**
         * Add the metatag and certify snippet
         * actions
         */
        public function run() {
            //add the alexa metatag to the header
            add_action('wp_head', array(&$this, 'add_alexa_claim_metatag'));
            add_action('login_head', array(&$this, 'add_alexa_claim_metatag'));
            //Add the alexa certify snippet to the footer of the WP site            
            add_action('login_head', array(&$this, 'alexa_certify_snippet'));
            add_action('wp_head', array(&$this, 'alexa_certify_snippet'));
        }

        public function add_alexa_claim_metatag() {
            
            if(is_home() || is_front_page()) {
                if(!defined('AX_PLUGIN_VERIFY_TAG')) {
                    $verifyId = get_option("alexacertify_verify");
                    echo "<!-- Alexa WordPress Claim and Certify plugin. -->\n";
                    if ($verifyId) {
                        echo "<meta name=\"alexaVerifyID\" content=\"$verifyId\" />\n";
                    }
                    define('AX_PLUGIN_VERIFY_TAG', "verify_tag");                
                }                
            }            
        }

        public function alexa_certify_snippet() {
            if(!defined('AX_PLUGIN_CERT_SNIPPET')) {
                $certifyCode = get_option("alexacertify_certify");
                if ($certifyCode) {
                    echo $certifyCode . "\n";
                }
                define('AX_PLUGIN_CERT_SNIPPET', "certify_snippet");
            }
        }
    

        /*******************
         * Admin Setup Functions *
         *******************/

        /**
         * Initialize the admin capabilities
         */
        private function admin_init() {            
            //set up the settings admin page            
            add_action('admin_menu', array(&$this, 'add_settings_page'));   
            //create the link to the settings page
            add_filter("plugin_action_links_".$this->plugin_basename, array(&$this, 'add_settings_action_link'));
        }


        public function add_settings_page() {
            $pageTitle = "Alexa Internet";
            $menuTitle = "Alexa Internet";
            $capability = 'manage_options'; 
            $callback =  array(&$this,"alexa_plugin_settings");    
            add_options_page($pageTitle, $menuTitle, $capability, $this->admin_page_id, $callback);
        }

        /*
         * Modify the $links array to add a 'Settings' link.
         * The 'Settings' link will take users to a page where they can set up their claim code
         * and certify snippets
         */
        public function add_settings_action_link( $links ) {
            $links = isset($links) ? $links : array();
            $links["_ax_settings"] = '<a href="options-general.php?page='.$this->admin_page_id.'">Settings</a>';
            return $links;
        }



        /***************************
         * Settings Page Functions *
         ***************************/        
       
        /*
         * Render the settings page, where users can define their certify snippet
         * and claim ID
         */
        public function alexa_plugin_settings() {

            $current_certify_snippet = get_option($this->certify_snippet_oid);
            $current_verify_tag = get_option($this->verify_tag_oid);
            $errors = array();
            $updated = false;
            if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST["alexacertify_submit"])) {                
                //The user just sent us new data, fetch it, validate it and store it
                $form_certify_snippet = isset($_POST["alexacertify_certify"]) 
                    ? $this->_clean_input($_POST["alexacertify_certify"]) : $current_certify_snippet;
                $form_verify_tag = isset($_POST["alexacertify_verify"]) ? 
                    $this->_clean_input($_POST["alexacertify_verify"]) : $current_verify_tag; 

                if(strcmp($form_certify_snippet, $current_certify_snippet)) {
                    if($this->_validCertifySnippet($form_certify_snippet)) {
                        update_option($this->certify_snippet_oid, $form_certify_snippet);                        
                        $updated["certify"] = "Your certify snippet has been updated.";
                    } else {
                        $errors["certify"] = "We could not validate your snippet. "
                            ."Please make sure you copy the Alexa Certify Code exactly as it appears on your Certify page.";    
                    }
                }

                if(strcmp($form_verify_tag, $current_verify_tag)) {
                    if($this->_validVerifyTag($form_verify_tag)) {
                        update_option($this->verify_tag_oid, $form_verify_tag);
                        $updated["verify"] = "Your verification ID has been updated";
                    } else {
                         $errors["verify"] = "Invalid Verifcation ID";   
                    }                
                }
                if(empty($errors) && empty($updated)) {
                    $updated["nochange"] = "No changes found.";                    
                } 
            }

            //these values are needed in the admin script
            $this->updated = $updated;            
            $this->errors = $errors;
            $this->certify_snippet = get_option($this->certify_snippet_oid);
            $this->verify_tag = get_option($this->verify_tag_oid);       
            include('alexacertify_admin.php');
        }


        private function _validCertifySnippet($snippet) {
            $certifyRegex = "<script [^>]*>[^<].+atrk_acct:.{14}.+domain.+<\/script>[^<]*<noscript><img [^>].+d5nxst8fruw4z.cloudfront.net/atrk.gif.+<\/noscript>";
            return empty($snippet) || ereg($certifyRegex, $snippet);
        }

        private function _validVerifyTag($tag) {
            return empty($tag) || preg_match('/^([^\'"])+$/', $tag);
        }

        private function _clean_input($value) {
            return trim(stripslashes($value));
        }
    }
}

if(class_exists('WP_Alexa_Certify')) {
    $alexa_certify_plugin = new WP_Alexa_Certify();
    $alexa_certify_plugin->run();
}

?>
