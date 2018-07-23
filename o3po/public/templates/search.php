<?php

/**
 * The template for displaying search results pages.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/public/templates
 */

get_header(); ?>

	<div id="content" class="site-content">

		<div class="page-header">
			<div class="container">
				<h1 class="page-title"><?php printf( esc_html( 'Search Results for: %s' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
			</div>
		</div>

		<div id="content-inside" class="container right-sidebar">

			<section id="primary" class="content-area">
				<main id="main" class="site-main" role="main">
                <div class="search-results"> 
                <div class="hentry">
                    <?php get_search_form(); ?>
                </div>
                </div>
				<?php if ( have_posts() ) : ?>

					<?php /* Start the Loop */ ?>
					<?php while ( have_posts() ) : the_post(); ?>

						<?php
						/**
						 * Run the loop for the search to output the results.
						 * If you want to overload this in a child theme then include a file
						 * called content-search.php and that will be used instead.
						 */
						get_template_part( 'template-parts/content', 'search' );
						?>

					<?php endwhile; ?>

					<?php the_posts_navigation(); ?>

				<?php else : ?>
				<?php endif; ?>

<?php //get_template_part( 'template-parts/content', 'none' );
?>

                <?php if ( !have_posts() ||  (!empty($_GET["reason"]) and $_GET["reason"]==="title-click") ) : ?>
    
                <div class="hentry">
                    <?php if ( !have_posts()): ?>
                    <h2>Nothing found</h2>
    				<?php endif; ?>
                    <div class="important-box">
                    <?php
                    $settings = O3PO_Settings::instance();
                    $journal_title = $settings->get_plugin_option('journal_title');
                    if(!empty($_GET["reason"]) and $_GET["reason"]==="title-click") 
                    {
                        if(!have_posts())
                        {
                            echo "<h3>The manuscript whose title you clicked is not published in " . $journal_title . "</h3>";
                            echo "<p>This can mean two things:</p>";
                        }
                        else
                            {
                                echo "<h3>You came here to verify whether a manuscript is published in " . $journal_title . "</h3>";
                                echo "<p>If the manuscript you are looking for is not in the list above, this can mean two things:</p>";
                            }
                    } else 
                    {
                        echo "<h3>Important notice</h3>";
                        echo "<p>If you came here by clicking the title of a manuscript using the " . $journal_title . " LaTeX template to check whether it is published in " . $journal_title . ", this can mean two things:</p>";
                    }
                    ?>
                    <ol>
                    <li>If the manuscript states an acceptance date in the recent past, it will probably be published soon.</li>
                        <li>If the manuscript states no acceptance date or its latest version has been on the arXiv for some time, then this manuscript has not been published and almost certainly has not been accepted; it however might currently be under review.</li>
                    </ol>
                    <?php
                    echo "<p>Everyone is free to use the open source " . $journal_title . " LaTeX document class and its usage does not imply endorsement of the manuscript by " . $journal_title . " in any way.</p>";
                    ?>
                    </div>
                </div>

                <?php endif; ?>

				</main><!-- #main -->
			</section><!-- #primary -->

			<?php get_sidebar(); ?>

		</div><!--#content-inside -->
	</div><!-- #content -->

<?php get_footer(); ?>
