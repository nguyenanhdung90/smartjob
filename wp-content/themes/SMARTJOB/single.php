<?php
get_header();
wpb_set_post_views(get_the_ID());
if (have_posts()) {
    global $post;
    the_post();
    $date = get_the_date('d S M Y');
    $date_arr = explode(' ', $date);

    $cat = wp_get_post_categories($post->ID);
    if (isset($cat[0]))
        $cat = get_category($cat[0]);

    ?>
    <!--
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.1.0/styles/default.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.1.0/highlight.min.js"></script>
    <script>hljs.initHighlightingOnLoad();</script> -->

    <div class="wrapper clearfix" id="<?php $roleaa = get_role('contributor');
    print_r($roleaa); ?>">
        <div class="main-center">
            <div class="main-column">
                <div class="entry-blog ">
                    <div style="padding-left:12px">
                        <div class="header font-quicksand">
                            <?php if (isset($cat->name)) { ?>
                                <a href="<?php echo get_category_link($cat) ?>">
                                    <?php echo $cat->name ?>
                                </a>
                            <?php } ?>
                            <a href="<?php the_permalink() ?>" class="comment">

                                <?php //comments_number('0','1','%')?>
                            </a>
                        </div>
                        <h1 class="title" style=" font-size: 30px;font-weight: normal;line-height: 38px;">
                            <a href="<?php the_permalink() ?>"
                               title="<?php the_title() ?>"><?php the_title() ?></a><span
                                style="font-size:16px"> (<?php echo wpb_get_post_views(get_the_ID()); ?>)</span>
                        </h1>

                        <div class="description tinymce-style"
                             style="font-size: 15px;font-weight:400;color:#221f20;text-rendering:auto !important;line-height:1.6;text-align:justify">
                            <?php the_content('') ?>
                            <div style="padding: 13px;"><label>Tags:</label>
                                <?php
                                if (get_the_tags()) {
                                    foreach (get_the_tags() as $tag) {
                                        ?>
                                        <a href="<?php echo get_tag_link($tag->term_id); ?>"
                                           style="padding: 11px;"><?php echo $tag->name; ?></a>,
                                    <?php }
                                } ?>
                            </div>
                            <div style="margin-bottom:4px">
                                <div class="social_destop "><!--https://www.facebook.com/smartjob.vn?fref=ts-->
                                    <div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="box_count"
                                         data-action="like" data-show-faces="true" data-share="false"></div>
                                </div>
                                <div class="social_destop two_social">
                                    <div class="fb-share-button" data-href="<?php the_permalink() ?>"
                                         data-layout="box_count"></div>
                                </div>
                                <!-- Place this tag where you want the +1 button to render. -->
                                <div class="social_destop three_social">
                                    <div class="g-plusone" data-annotation="inline" data-width="400"></div>
                                </div>
                            </div>
                            <div style="margin-bottom:12px">
                                <div class="fb-comments" data-href="<?php the_permalink() ?>" data-width="100%"
                                     data-numposts="4"></div>
                            </div>
                        </div>

                    </div>
                    <div style="margin-top: 27px;">
                        <div class="header font-quicksand"
                             style="border-top: 1px solid #8C9AA3;font-size:22px;padding-top:35px;padding-bottom:35px;font-weight:400">
                            May be interested posts:
                        </div>
						
                        <?php
                        $categories = get_the_category($post->ID);
                        if ($categories) {
                            $category_ids = array();
                            foreach ($categories as $individual_category) $category_ids[] = $individual_category->term_id;
                            $args = array(
                                'category__in' => $category_ids,
                                'post__not_in' => array($post->ID),
                                'showposts' => 6, // Number of related posts that will be shown.
                                'caller_get_posts' => 1
                            );
                            $my_query = new wp_query($args);

                            if ($my_query->have_posts()) {
                                while ($my_query->have_posts()) {
                                    $my_query->the_post();
                                    if (has_post_thumbnail()) {
                                        $large_image_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'thumbnail');
                                    } else {
                                        $ca = get_bloginfo('stylesheet_directory');
                                        $large_image_url[0] = 'http://2.gravatar.com/avatar/80702f13faa0f502a4fa30b32323dd5a?s=96&amp;d=wavatar&amp;r=g';
                                    }
                                    ?>
                                    <div style="padding: 4px;float:left;width:45%">
                                        <a href="<?php the_permalink(); ?>" style="padding-right: 12px;float:left">
                                            <img style="width:52px;height:auto"
                                                 src="<?php echo $large_image_url[0]; ?>">
                                        </a>
                                        <a style="color:#2b3942;font-weight:normal;font-size:16px"
                                           href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </div>
                                <?php
                                }

                            }
                        }
                        wp_reset_query();
                        ?>
						
                    </div>

                </div>
            </div>
            <div class="second-column widget-area " id="sidebar-blog">
                <aside class="widget widget_miw_multi_image_widget" id="miw_multi_image_widget-4">
                    <div class="widget-title"
                         style="margin-top: 12px; font-weight: 500;font-size: 21px;;background-color:#2a4560;padding-top:7px;padding-bottom:7px;color:white">
                        Chuyên mục Blog
                    </div>
                    <div class="miw-container">
                        <ul class="miw miw-linear">
                            <?php
                            $args2 = array(
                                'type' => 'post',
                                'child_of' => $category[0]->category_parent,
                                'parent' => '',
                                'orderby' => 'name',
                                'order' => 'ASC',
                                'hide_empty' => 1,
                                'hierarchical' => 1,
                                'exclude' => '',
                                'include' => '',
                                'number' => '',
                                'taxonomy' => 'category',
                                'pad_counts' => false
                            );
                            $categories = get_categories($args2);
                            foreach ($categories as $category) {
                                if ($category->parent == 0) {
                                    ?>
                                    <li style="text-align:left" class="miw-loop">
                                        <a href="<?php echo get_category_link($category->term_id); ?>"
                                           style="font-weight: 400; font-size: 17px;">  <?php echo $category->cat_name; ?></a>
                                        <?php
                                        $term_id = $category->term_id;
                                        $taxonomy_name = 'category';
                                        $termchildren = get_term_children($term_id, $taxonomy_name);

                                        echo '<ul style="margin-left:16px;">';
                                        foreach ($termchildren as $child) {
                                            $term = get_term_by('id', $child, $taxonomy_name);
                                            echo '<li><a href="' . get_term_link($child, $taxonomy_name) . '" style="font-weight:300;border-bottom:1px dotted #ededed ">- ' . $term->name . '</a></li>';
                                        }
                                        echo '</ul>';
                                        ?>
                                    </li>
                                <?php
                                }

                            }

                            ?>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
    <!-- them question cho mr vydn -->
    <script type="text/javascript">
        $("#faqs dd").hide();
        $("#faqs dt").click(function () {
            $(this).next("#faqs dd").slideToggle(500);
            $(this).toggleClass("expanded");
        });
    </script>
    <style type="text/css">
        #faqs dt, #faqs dd {
            padding: 0 0 0 50px
        }

        #faqs dt {
            font-size: 1.5em;
            color: #9d9d9d;
            cursor: pointer;
            height: 37px;
            line-height: 37px;
            margin: 0 0 15px 25px
        }

        #faqs dd {
            font-size: 1em;
            margin: 0 0 20px 25px
        }

        #faqs dt {
            background: url(http://www.designonslaught.com/files/2012/06/expand-icon.png) no-repeat left;
            color: #0000ff
        }

        #faqs .expanded {
            background: url(http://www.designonslaught.com/files/2012/06/expanded-icon.png) no-repeat left
        }
    </style>
    <!-- ket thuc question cho mr vydn -->
<?php
}
get_footer();
