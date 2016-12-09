<?php 
global $post, $resume;

$available_tax  =   ET_TaxFactory::get_instance('available');
$colors         =   $available_tax->get_color ();
?>
<li data-icon="false" class="clearfix"><span class="arrow-right"></span>
	<a href="<?php the_permalink() ?>" data-transition="slide">
		<span class="thumb-img">
			<?php echo et_get_resume_avatar($resume->post_author, 50); ?>
		</span>
		<span class="intro-text">
			<span class="fix-middle">
				<h1><?php the_title(); ?></h1>
				<p class="positions"><?php echo $resume->et_profession_title ?></p>
				<p class="locations"><span class="icon-locations"></span><?php echo $resume->et_location ?></p>
			</span>
		</span>
	</a>
</li>