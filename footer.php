<?php if( is_home() || is_singular('job') || is_page_template('page-dashboard.php') ||
		is_author() || is_post_type_archive( 'job' ) ||
		is_tax('job_type') || is_tax('job_category') || is_search() || apply_filters( 'je_footer_can_print_modal_template', false ) ){
	global $post, $user_ID;

	if ( current_user_can('edit_others_posts') || is_page_template('page-dashboard.php') || ( is_singular('job') && $post->post_author == $user_ID ) ) {
		//$job_categories = et_get_job_categories ();
		je_modal_edit_job_template ();
	}

	// insert modal reject job when logging in as administrators
	if( current_user_can('edit_others_posts') ){
		echo et_template_modal_reject();
	}
	?>

	<!-- move template of job list item here, used mostly in homepage & company page -->
	<script type="text/template" id="job_list_item">
		<?php  
		if(is_author())
		{
		    echo et_template_frontend_job_author() ;
		}
		else
		{
			echo et_template_frontend_job() ;
		}
		
		?>
	</script>
	<!-- end template of job list item -->

	<?php
}

if( is_page_template( 'page-upgrade-account.php' ) ||  is_page_template( 'page-dashboard.php' ) ) {
	global $applicant_detail;
	echo '<div style="display:none" >' ;
	wp_editor( $applicant_detail, 'call-to-add-tinymce', je_editor_settings () ) ;
	echo '</div>';
}

if( !is_user_logged_in() || is_page_template('page-post-a-job.php') || is_page_template( 'page-upgrade-account.php' ) ){

	get_template_part( 'template/modal', 'login' );

	et_template_modal_register();

	et_template_modal_forgot_pass();
}

$general_opt	=	new ET_GeneralOptions();
$copyright		=	$general_opt->get_copyright();
$has_footer_nav	=	false;
if( has_nav_menu('et_footer') ) {
	$has_footer_nav	=	true;
}

?>

	<footer class="bg-footer" style="overflow:hidden;height:auto">
		<div class="main-center">
			<div class="foot_smartjob">
			<h1 style="font-weight:normal">Jobs by skill tag</h1>
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="<?php bloginfo('url');?>/?s=agile" target="_blank">Agile</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=android" target="_blank">Android</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=angularJS" target="_blank">AngularJS</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=asp.net" target="_blank">ASP.NET</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=Bridge Engineer" target="_blank">Bridge Engineer</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=agile" target="_blank">Business Analyst</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=c#" target="_blank">C#</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=c++" target="_blank">C++</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=c language" target="_blank">C language</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="<?php bloginfo('url');?>/?s=css" target="_blank">CSS</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=database" target="_blank">Database</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=designer" target="_blank">Designer</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=django" target="_blank">Django</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=drupal" target="_blank">Drupal</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=embedded" target="_blank">Embedded</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=english" target="_blank">English</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=ERP" target="_blank">ERP</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=flash" target="_blank">Flash</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="<?php bloginfo('url');?>/?s=games" target="_blank">Games</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=html5" target="_blank">HTML5</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=iOS" target="_blank">iOS</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=it support" target="_blank">IT Support</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=j2ee" target="_blank">J2EE</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=japanese" target="_blank">Japanese</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=java" target="_blank">Java</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=javascript" target="_blank">JavaScript</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=jquery" target="_blank">JQuery</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="<?php bloginfo('url');?>/?s=json" target="_blank">JSON</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=linux" target="_blank">Linux</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=magento" target="_blank">Magento</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=manager" target="_blank">Manager</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=mobile apps" target="_blank">Mobile Apps</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=mvc" target="_blank">MVC</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=mysql" target="_blank">MySQL</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=.net" target="_blank">.NET</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=networking" target="_blank">Networking</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="<?php bloginfo('url');?>/?s=nodejs" target="_blank">NodeJS</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=objective c" target="_blank">Objective C</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=oop" target="_blank">OOP</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=oracle" target="_blank">Oracle</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=php" target="_blank">PHP</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=postgresql" target="_blank">PostgreSql</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=product manager" target="_blank">Product Manager</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=project manager" target="_blank">Project Manager</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=python" target="_blank">Python</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="<?php bloginfo('url');?>/?s=qa qc" target="_blank">QA QC</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=ruby" target="_blank">Ruby</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=ruby on rails" target="_blank">Ruby on Rails</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=sales engineer" target="_blank">Sales Engineer</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=sharepoint" target="_blank">Sharepoint</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=software architect" target="_blank">Software Architect</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=sql" target="_blank">SQL</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=system admin" target="_blank">System Admin</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=system engineer" target="_blank">System Engineer</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="<?php bloginfo('url');?>/?s=team leader" target="_blank">Team Leader</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=tester" target="_blank">Tester</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=ui ux" target="_blank">UI-UX</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=unity" target="_blank">Unity</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=windows phone" target="_blank">Windows Phone</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=wordpress" target="_blank">Wordpress</a></li>
						<li> <a href="<?php bloginfo('url');?>/?s=xml" target="_blank">XML</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=xml" target="_blank">XML</a></li>	
						<li> <a href="<?php bloginfo('url');?>/?s=xml" target="_blank">XML</a></li>	
					</ul>				  
				</div>							
			</div>
			<div class="copyright_smarjob">
			</div>
			<div class="f-left <?php if($has_footer_nav) echo 'margin15'; ?>">

				<?php
				if(has_nav_menu('et_footer')) {
					wp_nav_menu(array (
							'theme_location' => 'et_footer',
							'container' => 'ul',
							'menu_class'	=> 'menu-bottom'
						));

				}

				do_action ('je_footer_bar');

				?>
				<div class="copyright" style="float:left"><?php echo $copyright; ?> <?php printf(__('<span class="et-sem"><a target="_blank" href="%s" ></a> </span>', ET_DOMAIN), 'http://dcv.vn/' , 'http://dcv.vn/'); ?></div>
				
			</div>

				<?php
					et_follow_us();
					$http	=	et_get_http();

				 ?>

	</footer>
	<!--[if lt IE 9]>
		<script src="<?php echo $http; ?>://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!--[if lte IE 8]>
		<script type="text/javascript">
			Cufon.replace('.icon'); // Works without a selector engine
			Cufon.replace('.icon:before'); // Works without a selector engine
			jQuery(".icon").each( function(){
				var cthis = jQuery(this);
				cthis.append( cthis.attr("data-icon") );
			});

			Cufon.now();
		</script>
	<![endif]-->


	<?php wp_footer();?>
</body>
</html>