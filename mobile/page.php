<?php et_get_mobile_header('mobile'); 
if(have_posts()) { the_post ();
?>

<div data-role="content" class="post-content resume-contentpage">
        <h1 class="title-resume">
                <?php the_title(); ?>
                <a href="#" class="post-title-link icon" data-icon="A"></a>
        </h1>
        <div class="content-info content-text">
                <?php the_content(); ?>
        </div>
</div>
<?php 
}
et_get_mobile_footer('mobile'); ?>