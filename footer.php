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

	<footer class="bg-footer" style="overflow:hidden;height:auto;background:black">
		<div class="main-center">
			<div class="foot_smartjob">
			<h2 style="font-weight:normal">Quick Search by Skills</h2>
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-agile/" title="tuyen dung viec lam Agile" target="_blank">Agile</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-android/" title="tuyen dung viec lam Android" target="_blank">Android</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-angularjs/" title="tuyen dung viec làm AngularJS" target="_blank">AngularJS</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-asp-net/"  title="tuyen dung viec lam ASP.NET"target="_blank">ASP.NET</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-bridge-engineer/" title="tuyen dung viec lam Bridge Engineer" target="_blank">Bridge Engineer</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-business-analyst/" title="tuyen dung viec lam Business Analyst" target="_blank">Business Analyst</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-c-sharp/" title="tuyen dung viec lam C#" target="_blank">C#</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-c-plus/" title="tuyen dung viec lam C++" target="_blank">C++</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-c-language/" title="tuyen dung voec lam C" target="_blank">C language</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-css/" title="tuyen dung viec lam CSS" target="_blank">CSS</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-database/" title="tuyen dung viec lam Database" target="_blank">Database</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-designer/" title="tuyen dung viec lam Designer" target="_blank">Designer</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-django/" title="tuyen dung viec lam Django" target="_blank">Django</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-drupal/" title="tuyen dung viec lam Drupal" target="_blank">Drupal</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-embedded/" title="tuyen dung viec lam Embedded" target="_blank">Embedded</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-english/" title="tuyen dung viec lam English" target="_blank">English</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-erp/" title="tuyen dung viec lam ERP" target="_blank">ERP</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-flash/" title="tuyen dung viec lam Flash" target="_blank">Flash</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-game/" title="tuyen dung viec lam Games" target="_blank">Games</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-html5/" title="tuyen dung viec lam HTML5" target="_blank">HTML5</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-ios/" title="tuyen dung viec lam IOS" target="_blank">iOS</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-it-support/" title="tuyen dung viec lam IT support" target="_blank">IT Support</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-j2ee/" title="tuyen dung viec lam J2EE" target="_blank">J2EE</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-japanese/" title="tuyen dung viec lam Japanese" target="_blank">Japanese</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-java/" title="tuyen dung lap trinh Java" target="_blank">Java</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-javascript/" title="tuyen dung viec lam JavaScript" target="_blank">JavaScript</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-jquery/" title="tuyen dung viec lam JQuery" target="_blank">JQuery</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="http://smartjob.vn/tuyen-dung-it-phan-cung-mang/" title="tuyen dung viec lam Hardware" target="_blank">Hardware</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-linux/" title="tuyen dung viec lam Linux" target="_blank">Linux</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-magento/" title="tuyen dung viec lam Magento" target="_blank">Magento</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-manager/" title="tuyen dung lap trinh Manager" target="_blank">Manager</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-mobile-apps/" title="tuyen dung lap trinh Mobile Apps" target="_blank">Mobile Apps</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-mvc" title="tuyen dung viec lam MVC" target="_blank">MVC</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-mysql/" title="tuyen dung viec lam MySQL" target="_blank">MySQL</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-net/" title="tuyen dung viec lam .NET" target="_blank">.NET</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-networking/" title="tuyen dung viec lam Networking" target="_blank">Networking</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-nodejs/" title="tuyen dung viec lam NodeJS" target="_blank">NodeJS</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-objective-c/" title="tuyen dung viec lam Objective C" target="_blank">Objective C</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-oop/" title="tuyen dung viec lam OOP" target="_blank">OOP</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-oracle/" title="tuyen dung viec lam Oracle" target="_blank">Oracle</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-php/" title="tuyen dung viec lam PHP" target="_blank">PHP</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-postgresql/" title="tuyen dung viec lam PostgreSql" target="_blank">PostgreSql</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-product-manager/" title="tuyen dung viec lam Product Manager" target="_blank">Product Manager</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-project-manager/" title="tuyen dung viec lam Project Manager" target="_blank">Project Manager</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-python/" title="tuyen dung viec lam python" target="_blank">Python</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-qa-qc/" title="tuyen dung viec lam QA QC" target="_blank">QA QC</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-ruby/" title="tuyen dung viec lam Ruby" target="_blank">Ruby</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-ruby-on-rails/" title="tuyen dung viec la, Ruby on Rails" target="_blank">Ruby on Rails</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-sales-engineer/" title="tuyen dung viec lam Sales Engineer" target="_blank">Sales Engineer</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-sharepoint/" title="tuyen dung viec lam Sharepoint" target="_blank">Sharepoint</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-software-architect/" title="tuyen dung viec lam Software Architect" target="_blank">Software Architect</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-sql/" title="tuyen dung viec lam SQL" target="_blank">SQL</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-system-admin/" title="tuyen dung viec lam System Admin" target="_blank">System Admin</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-system-engineer/" title="tuyen dung viec lam System Engineer" target="_blank">System Engineer</a></li>	
					</ul>				  
				</div>				
				<div class="foot_comon firt_smart">
					<ul>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-team-leader/" title="tuyen dung viec lam Team Leader" target="_blank">Team Leader</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-tester/" title="tuyen dung viec lam Tester" target="_blank">Tester</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-ui-ux/" title="tuyen dung viec lam UI-UX" target="_blank">UI-UX</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-unity/" title="tuyen dung viec lam Unity" target="_blank">Unity</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-windows-phone/" title="tuyen dung viec lam Windows Phone" target="_blank">Windows Phone</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-wordpress/" title="tuyen dung viec lam wordpress" target="_blank">Wordpress</a></li>
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-seo/" title="tuyen dung viec lam SEO" target="_blank">Seo</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-mkt-online/" tuyen dung viec lam MKT online target="_blank">Marketing Online</a></li>	
						<li> <a href="http://smartjob.vn/tuyen-dung-viec-lam-xml/" title="tuyen dung viec lam XML" target="_blank">XML</a></li>	
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