<?php
/**
 * Template Name: Authentication
 */
global $wp_query, $wp_rewrite, $post, $et_data;
if ( session_id() == '' ) session_start();
get_header();
?>

<!--end header Bottom-->
<div class="container-fluid main-center">
    <div class="row">
        <div class="col-md-12 marginTop30">
            <?php
                $labels = $et_data['auth_labels'];
                $auth = unserialize($_SESSION['et_auth']);
                $type = isset($_GET['type']) ? $_GET['type'] : '';
            ?>
            <div class="twitter-auth social-auth social-auth-step1">
            <?php
                if($type == 'facebook'){ ?>
                    <p class="text-page-not social-big"><?php _e('SIGN IN WITH FACEBOOK',ET_DOMAIN);?></p>
                    <p class="social-small">
                    <?php

                         printf(__("This seems to be your first time signing in using your Facebook account. <br />If you already have an account with %s, please log in using the form below to link it to your Facebook account. Otherwise, please enter an email address and a password on the form, and a username on the next page to create an account.<br />You will only do this step ONCE. Next time, you'll get logged in right away.", ET_DOMAIN), get_bloginfo('name') );?>
                    </p>
                    <?php
                } else if ($type == 'twitter'){ ?>
                    <p class="text-page-not social-big"><?php _e('SIGN IN WITH TWITTER',ET_DOMAIN);?></p>
                    <p class="social-small">
                        <?php
                        printf(__("This seems to be your first time signing in using your Twitter account.<br />If you already have an account with %s, please log in using the form below to link it to your Facebook account. Otherwise, please enter an email address and a password on the form, and a username on the next page to create an account.<br > You will only do this step ONCE. Next time, you'll get logged in right away.</p>", ET_DOMAIN), get_bloginfo('name') );
                    ?>
                    </p>

            <?php }   ?>
                <form id="form_auth" method="post" action="">
                    <div class="social-form">
                        <input type="hidden" name="et_nonce" value="<?php echo wp_create_nonce( 'authentication' ) ?>">
                        <input type="text" name="user_email" value="<?php if(isset($_SESSION['user_email'])) echo $_SESSION['user_email']; ?>"  placeholder="<?php _e('Email', ET_DOMAIN) ?>">
                        <input type="password" name="user_pass"  placeholder="<?php _e('Password', ET_DOMAIN) ?>">
                        <input type="submit" value="Submit">
                    </div>
                </form>
            </div>
            <div class="social-auth social-auth-step2">
                <p class="text-page-not social-big"><?php echo $labels['title'] ?></p>
                <p class="social-small"><?php _e('Please provide a username to continue',ET_DOMAIN);?></p>
                <form id="form_username" method="post" action="">
                    <div class="social-form">
                        <input type="hidden" name="et_nonce" value="<?php echo wp_create_nonce( 'authentication' ) ?>">
                        <input type="text" name="user_login" value="<?php echo isset($auth['user_login']) ? $auth['user_login'] : "" ?>" placeholder="<?php _e('Username', ET_DOMAIN) ?>">
                        <input type="submit" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>