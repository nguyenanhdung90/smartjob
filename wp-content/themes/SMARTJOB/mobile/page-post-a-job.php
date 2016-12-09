<?php
$response   =   array();
if( isset($_REQUEST['post_title']) )  {
     $request    =   $_REQUEST;
     $response   =   je_mobile_post_job ($request);

    if( $response['success'] ) {
        wp_redirect ( $response['success_url'] );
        exit;
    }
}

et_get_mobile_header('mobile');   ?>

<div data-role="content" class="post-content post-job-new">

	<?php if( isset($_REQUEST['post_id'])  ) {
        get_template_part( 'mobile/template' , 'payment' );
    } else { ?>

        <?php

        if( !is_user_logged_in() ) {
            get_template_part( 'mobile/template' , 'register' );
        }else { ?>
            <h1 class="page-title" >
                <?php _e("Post a Job", ET_DOMAIN); ?>
                <span class="step-number"><?php _e("Step Job Content", ET_DOMAIN); ?></span>
            </h1>
            <?php

                if( isset($response['success']) && !$response['success'] ) {
                ?>
                    <span class="post-job-error"> <?php echo $response['msg'] ?> </span>
                <?php
                }

            get_template_part( 'mobile/template' , 'post-job' );
        }
    }
?>
</div>
<?php et_get_mobile_footer('mobile'); ?>
