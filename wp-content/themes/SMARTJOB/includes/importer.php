<?php

if(class_exists('ET_Import_XML')) {
	class JE_Import_XML extends ET_Import_XML {
		function __construct () {
			$this->file = TEMPLATEPATH . '/sampledata/sample_data.xml';
		}

		function process_authors( ) {
			/**
			 * if you have a new role for this you should improve another way
			*/
			global $wpdb;
			$import_data = $this->parse( $this->file );

			if ( ! empty( $import_data['authors'] ) ) {
				if ( is_multisite() ){
					$this->authors = $import_data['authors'];
				}
			// no author information, grab it from the posts
			}

			$userdata = array();
			foreach ($this->authors as $author){
				if($author['author_login'] == 'sample_youngworld' )
					$role	=	 'company';
				else $role =  'jobseeker' ;
				if ( username_exists( $author['author_login'] ) ) {
					$user = get_user_by('login', $author['author_login']);
					add_user_to_blog( get_current_blog_id(), $user->ID, $role );
				}
				else{
					$userdata = array(
						//'ID'			=> $author['author_id'],
						'user_login'	=> $author['author_login'],
						'user_pass'		=> '123456',
						'user_email'	=> $author['author_email'],
						'display_name'	=> $author['author_display_name'],
						'role'			=> $role,
						'first_name'	=> $author['author_first_name'],
						'last_name'		=> $author['author_last_name']
					);

					$user_id = wp_insert_user( $userdata );
					if ( !is_wp_error( $user_id ) ){
						if ( is_multisite() ){
							add_user_to_blog( get_current_blog_id(), $user_id, $userdata['role'] );
						}
					}
				}
			}

		}
	}
}
