<div class="blog-list-cmt">
	<ul class="comment-list">
		<?php if(have_comments()) {
			global $isMobile;
			if( !$isMobile )
				wp_list_comments( array ('callback' => 'et_blog_list_comments'));
			else 
				wp_list_comments( array ('callback' => 'et_mobile_list_comments'));
		 }?>
	</ul>
</div>
	<?php  if( comments_open() && ! is_page() && post_type_supports( get_post_type(), 'comments' ) ) { ?>
				<?php comment_form ( array (
						'comment_field'        => ' <div class="form-item"><label for="comment">' . __( 'Comment', ET_DOMAIN ) . '</label>
													<div class="input">
													<textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>
													</div> </div>',
						//'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.', ET_DOMAIN ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
						//'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', ET_DOMAIN ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
						'comment_notes_before' => '',
						'comment_notes_after'  => '',
						'id_form'              => 'commentform',
						'id_submit'            => 'submit',
						'title_reply'          => __("Add a comment", ET_DOMAIN),
						'title_reply_to'       => __( 'Leave a Reply to %s', ET_DOMAIN),
						'cancel_reply_link'    => __( 'Cancel reply',ET_DOMAIN ),
						'label_submit'         => __( 'Submit Comment', ET_DOMAIN ),

				) )?>

	<?php } else { ?>
		<div class="comment-form">
			<h3 class="widget-title"><?php _e("Comment closed!", ET_DOMAIN);?></h3>
		</div>
	<?php } ?>

<?php

function et_blog_list_comments ( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	$date	=	get_comment_date('d S M Y');
	$date_arr	=	explode(' ', $date );

	?>
	<li class="<?php comment_class(); ?>" id="li-comment-<?php comment_ID();?>">
		<div id="comment-<?php comment_ID(); ?>">
			<div class="thumb">
				<a href="#"><?php echo get_avatar( $comment, '' );?></a>
			</div>
			<div class="comment">
				<div class="author">
					<a href="#"><?php comment_author()?></a>
					<span class="icon" data-icon="t"></span>
					<?php echo $date_arr[2]?> <?php echo $date_arr[0]?><sup><?php echo strtoupper($date_arr[1])?></sup>, <?php echo $date_arr[3]?>
				</div>
				<?php if ( '0' == $comment->comment_approved ) : ?>
					<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', ET_DOMAIN ); ?></p>
				<?php endif; ?>
				<div class="content">
	                <?php comment_text ()?> 
	            </div>
	            <div class="reply">
	                <?php comment_reply_link(array_merge($args,	array(
												'reply_text' => __( 'Reply <span class="icon" data-icon="R"></span>', ET_DOMAIN ),
												'depth' => $depth,
												'max_depth' => $args['max_depth'] ) ));?>

				</div>
	       	</div>
		</div>
<?php

}

function et_mobile_list_comments ( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	$date	=	get_comment_date('d S M Y');
	$date_arr	=	explode(' ', $date );

	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID();?>">
		<div id="comment-<?php comment_ID(); ?>">
			<div class="comment detail-post">
				<span class="">
					<?php echo get_avatar( $comment, '50' );?>
				</span>
				<div class="name">
					<p><?php comment_author()?></p>
					<span class="icon" data-icon="t"><?php echo get_comment_date(); ?></span>
				</div>
			</div>
			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', ET_DOMAIN ); ?></p>
			<?php endif; ?>
			<div class="text-cmt">
                <?php comment_text ()?>
                <div class="reply">
	                <?php comment_reply_link(array_merge($args,	array(
												'reply_text' => __( 'Reply <span class="icon" data-icon="R"></span>', ET_DOMAIN ),
												'depth' => $depth,
												'max_depth' => $args['max_depth'] ) ));?>

				</div>
            </div>

		</div>
<?php

}
