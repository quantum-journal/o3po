<?php

/**
 * The template for displaying single paper posts.
 *
 * Based on single.php of the OnePress theme.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/public/templates
 */

$publication_type_paper = O3PO_PublicationType::get_active_publication_types('paper');
$settings = O3PO_Settings::instance();

get_header();
$layout = get_theme_mod( 'onepress_layout', 'right-sidebar' );
?>

<div id="content" class="site-content">

<?php echo onepress_breadcrumb(); ?>

    <div id="content-inside" class="container <?php echo esc_attr( $layout ); ?>">
    <div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

<?php while ( have_posts() ) : the_post(); ?>

<?php
    $post_id = get_the_ID();


echo '<header class="entry-header">';
echo '<h1 class="entry-title title citation_title">' . esc_html ( get_the_title( $post_id ) ) . '</h1>';
echo '<p class="authors citation_author">';
echo $publication_type_paper->get_formated_authors_html( $post_id );
echo '</p>';
echo '<p class="affiliations">';
echo $publication_type_paper->get_formated_affiliations_html( $post_id );
echo '</p>';
echo '<table class="meta-data-table">';
echo '<tr><td>Published:</td><td>' . esc_html($publication_type_paper->get_formated_date_published( $post_id )) .  ', ' . $publication_type_paper->get_formated_volume_html($post_id) . ', page ' . esc_html(get_post_meta( $post_id, 'paper_pages', true )) . '</td></tr>';
echo '<tr><td>Eprint:</td><td><a href="' . esc_attr($settings->get_plugin_option('arxiv_url_abs_prefix') . get_post_meta( $post_id, 'paper_eprint', true ) ) . '">arXiv:' . esc_html(get_post_meta( $post_id, 'paper_eprint', true )) . '</a></td></tr>';
echo '<tr><td>Scirate:</td><td><a href="' . esc_attr($settings->get_plugin_option('scirate_url_abs_prefix') . get_post_meta( $post_id, 'paper_eprint', true ) ) . '">' . esc_html($settings->get_plugin_option('scirate_url_abs_prefix') . get_post_meta( $post_id, 'paper_eprint', true )) . '</a></td></tr>';
$paper_doi = get_post_meta( $post_id, 'paper_doi_prefix', true ) . '/' .  get_post_meta( $post_id, 'paper_doi_suffix', true );
echo '<tr><td>Doi:</td><td><a href="' . esc_attr($settings->get_plugin_option('doi_url_prefix') . $paper_doi) . '">' . esc_html($settings->get_plugin_option('doi_url_prefix') . $paper_doi ) . '</a></td></tr>';
if ( $publication_type_paper->show_fermats_library_permalink($post_id) ) {
    $paper_fermats_library_permalink = get_post_meta( $post_id, 'paper_fermats_library_permalink', true );
    echo '<tr><td>Fermat&#39;s library:</td><td><a href="' . esc_attr($paper_fermats_library_permalink) . '">' . esc_html($paper_fermats_library_permalink) . '</a></td></tr>';
}
echo '<tr><td>Citation:</td><td>' . esc_html($publication_type_paper->get_formated_citation($post_id)) . '</td></tr>';
echo '</table>';
//echo '<a id="fulltext" class="btn-theme-primary" href="' . esc_attr($publication_type_paper->get_pdf_pretty_permalink($post_id)) . '">full text pdf</a>';
echo '<form action="' . esc_attr($publication_type_paper->get_pdf_pretty_permalink($post_id)) . '" method="post">';
echo '<input id="fulltext" type="submit" value="full text pdf">';
echo '</form>';


echo '</header>';



echo '<div class="entry-content">';
echo '<p class="abstract">';
echo nl2br(esc_html( get_post_meta( $post_id, 'paper_abstract', true )) );
echo '</p>';
if ( has_post_thumbnail( ) ) {
    echo '<div class="featured-image-box">';
    echo '<div style="float:left; padding-right: 1rem; padding-bottom: 1rem">';
    the_post_thumbnail( 'onepress-blog-small' );
    echo '</div>';
    $paper_feature_image_caption = get_post_meta( $post_id, 'paper_feature_image_caption', true );
    if (!empty($paper_feature_image_caption))
        echo '<p class="feature-image-caption">' . "Featured image: " . nl2br(esc_html($paper_feature_image_caption)) . '</p>';
    echo '<div style="clear:both;"></div>';
    echo '</div>';
}

echo the_content();

$publication_type_paper->the_popular_summary($post_id);

$publication_type_paper->the_bibtex_data($post_id);

$publication_type_paper->the_bibliography($post_id);

$publication_type_paper->the_cited_by($post_id);
$publication_type_paper->the_license_information($post_id);
echo '</div>';

echo '<div class="entry-footer">';
echo '<span class="cat-links">Posted in: ';
the_category(', ');
echo '</span>';

//get_template_part( 'template-parts/content', 'single' );

    // If comments are open or we have at least one comment, load up the comment template.
if ( comments_open() || get_comments_number() ) :
    comments_template();
endif;
echo '</div>';
?>

<?php endwhile; // End of the loop. ?>

</main><!-- #main -->
</div><!-- #primary -->

<?php if ( $layout != 'no-sidebar' ) { ?>
<?php get_sidebar(); ?>
<?php } ?>

</div><!--#content-inside -->
</div><!-- #content -->

<?php get_footer(); ?>
