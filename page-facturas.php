<?php
/*
Template Name: Facturas MS
**/

/*extract(etheme_get_page_sidebar(true));

get_header(); ?>
        <div class="container">
            <div class="row">
                    <?php blog_breadcrumbs(); ?>
                <?php if($position && $responsive == 'top'): ?>
                    <div class="span3 sidebar_grid sidebar_<?php echo $position ?>">
                        <?php get_sidebar($sidebarname); ?>
                    </div>
                <?php endif; ?>
                <div class="<?php echo ($position)? 'span9':'span12'; ?> grid_content with-sidebar-<?php echo $position ?>">

                                <?php
                                /* Run the loop to output the posts.
                                 * If you want to overload this in a child theme then include a file
                                 * called loop-index.php and that will be used instead.
                                 */
                                 get_template_part( 'loop', 'single' );
                                ?>
                        </div><!-- #content -->
                <?php /* if($position && $responsive == 'bottom'): ?>
                    <div class="span3 sidebar_grid sidebar_<?php echo $position ?>">
                        <?php get_sidebar($sidebarname); ?>
                    </div>
                <?php endif; ?>
                <div class="clear"></div>
                </div>
                </div><!-- .container -->
<?php */

//get_footer(); 
?>