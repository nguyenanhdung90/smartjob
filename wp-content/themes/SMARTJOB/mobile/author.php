<?php et_get_mobile_header('mobile'); ?>
<div data-role="content" class="post-content resume-content-home">
    <?php
    global $et_global, $post, $wp_query;
    $company_id = get_query_var('author');
    $company = et_create_companies_response($company_id);
    $company_logo = $company['user_logo']['company-logo'][0];
    if (empty($company_logo)) {
        $general_opt = new ET_GeneralOptions();
        $temp = $general_opt->get_website_logo();
        $company_logo = $temp[0];
    }
    //et_get_job_count(array('post_author' => $company_id));
    $author_id = get_the_author_ID();
    $user_info = get_userdata($author_id);
    $user_role = $user_info->roles;
    if ($user_role[0] == "company" || !isset($_GET["com_i"])) {
        ?>
        <h1 class="post-title job-title">
            <?php echo $company['display_name']; ?>
            <a href="#" class="post-title-link icon" data-icon="A"></a>
        </h1>
        <div class="company-info inset-shadow">
            <div class="company-detail">
			    <div class="content-info" style="text-align:justify;padding:15px">
				<?php echo $company['description']; ?>
				</div>
                <?php if (!empty($company['user_url'])) : ?>
                    <div class="content-info">
                        <a class="list-link job-employer" rel="nofollow" target="_blank"
                           href="<?php echo $company['user_url']; ?>"><?php echo $company['user_url']; ?></a>
                        <a data-icon="A" class="post-title-link icon ui-link" href="<?php echo $company['user_url']; ?>"
                           rel="nofollow" target="_blank"></a>
                    </div>
                <?php endif; ?>
                <div class="content-info">
                    <a class="list-link job-loc" href="">
                        <?php echo _n(sprintf('%d active job', $wp_query->found_posts), sprintf('%d active jobs', $wp_query->found_posts), $wp_query->found_posts); ?>
                    </a>
                </div>
            </div>
        </div>

        <ul class="listview" data-role="listview" id="job-content">
            <?php
            if (have_posts()) {
                $page = $wp_query->max_num_pages;
                $class_name = '';
                $first_post = $post->ID;
                $flag = 0;
                $flag_title = 0;
                while (have_posts()) {
                    the_post();
                    //print_r( $job_type );
                    $featured = et_get_post_field($post->ID, 'featured'); //echo $featured;
                    global $job;
                    $job = et_create_jobs_response($post);

                    if ($flag_title == 0 && $featured == 1) {
                        echo '<li class="list-divider">' . __("Featured Jobs", ET_DOMAIN) . '</li>';
                        $flag_title = 1;
                    } else if ($featured == $flag) {
                        $flag = 1;
                        echo '<li class="list-divider">' . __("Jobs", ET_DOMAIN) . '</li>';
                    }

                    $template_job = apply_filters('et_mobile_template_job', '');
                    if ($template_job != '')
                        load_template($template_job, false);
                    else {
                        get_template_part('mobile/mobile', 'template-job');
                    }

                    // load_template( apply_filters( 'et_mobile_template_job', dirname(__FILE__) . '/mobile-template-job.php'), false);
                }
            }
            ?>
        </ul>
        <?php
        $max_page_company = $wp_query->max_num_pages;
        $cur_page = (get_query_var('paged')) ? get_query_var('paged') : 1;
        if ($max_page_company > 1) {
            ?>
            <a href="#" class="btn-grey btn-wide btn-load-more ui-corner-all"
               id="lm_com_job"><?php _e('Load More Jobs', ET_DOMAIN); ?></a>
        <?php } ?>
        <input type="hidden" id="company" value="<?php echo $company_id; ?>">
        <input type="hidden" id="max_page_com" value="<?php echo $max_page_company; ?>">
        <input type="hidden" id="cur_page" value="<?php echo $cur_page; ?>">
    <?php
    } else {
        global $wpdb;
        $rows = $wpdb->get_results("SELECT decription,display_name,user_url FROM wp_post_company where ID ='" . $_GET["com_i"] . "' ");
        ?>
        <h1 class="post-title job-title">
            <?php echo $rows[0]->display_name; ?>
            <a href="#" class="post-title-link icon" data-icon="A"></a>
        </h1>
        <div class="company-info inset-shadow">
            <div class="company-detail">
				<div class="content-info" style="text-align:justify;padding:15px;">
	            <?php echo $rows[0]->decription; ?>
				</div>
                <?php if (!empty($rows[0]->user_url)) : ?>
                    <div class="content-info">
                        <a class="list-link job-employer" rel="nofollow" target="_blank"
                           href="<?php echo $rows[0]->user_url; ?>"><?php echo $rows[0]->user_url; ?></a>
                        <a data-icon="A" class="post-title-link icon ui-link" href="<?php echo $rows[0]->user_url; ?>"
                           rel="nofollow" target="_blank"></a>
                    </div>
                <?php endif; ?>
            </div>
			<div class="content-info" style="background-color:#F5F5F5;border-top:1px solid #ebebeb; ">
				<a class="list-link job-loc" href="" style="color:black;font-weight:bold">
					<?php
					$fivesdrafts = $wpdb->get_results("SELECT * FROM wp_posts where post_type ='job'  and post_status='publish' and company_editor_id='" . $_GET["com_i"] . "' ");
					echo $wpdb->num_rows;?> active jobs
				</a>
			
			</div>			
        </div>

        <ul class="listview" data-role="listview" id="job-content">
            <?php
            
            foreach ($fivesdrafts as $fivesdraft) {
                ?>
                <li data-icon="false" class="list-item ui-btn ui-btn-up-c ui-btn-icon-right ui-li" data-corners="false"
                    data-shadow="false" data-iconshadow="true" data-wrapperels="div" data-iconpos="right" data-theme="c"
                    style="padding-top:0px;padding-bottom:0px">
                    <div class="ui-btn-inner ui-li">
                        <div class="ui-btn-text">
                            <span class="arrow-right"></span>
                            <a data-ajax="false"
                               href="<?php echo get_bloginfo('url') . '/job/' . $fivesdraft->post_name; ?>"
                               class="ui-link-inherit">
                                <p class="name ui-li-desc"><?php echo $fivesdraft->post_title; ?></p>

                                <p class="list-function ui-li-desc">
                                    <span class="postions"
                                          style="font-size:15px"><?php echo $rows[0]->display_name; ?></span>
                                </p>

                                <p class="list-function ui-li-desc">
                                    <span class="locations" style="font-size:18px"><span class="icon"
                                                                                         data-icon="@"> </span><?php echo get_post_meta($fivesdraft->ID, 'et_full_location', true); ?></span>
                                </p>

                                <p class="list-function ui-li-desc">
                                    <span itemprop="addressLocality" style="font-size:16px">Salary:</span>
                                <span itemprop="addressLocality"
                                      style="color:#F0111B;font-size:16px"><?php echo get_post_meta($fivesdraft->ID, 'cfield-592', true); ?></span>
                                </p>
                            </a>

                            <p class="list-function ui-li-desc">
                                <?php
                                $posttags = get_the_tags($fivesdraft->ID);
                                if ($posttags) {
                                    foreach ($posttags as $tag) {
                                        ?>
                                        <a target="_blank" href="<?php bloginfo('url');
                                        echo '/?s=' . $tag->name; ?>"
                                           class="tag_smartjob_home ui-link"><?php echo $tag->name . ' '; ?> </a>
                                    <?php
                                    }
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </li>
            <?php
            }
            ?>
        </ul>

    <?php
    }
    ?>
</div><!-- /content -->
<?php et_get_mobile_footer('mobile'); ?> 