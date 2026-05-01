<?php
/**
 * Elementor Widget: Portfolio Grid with Filter
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_Portfolio_Grid extends \Elementor\Widget_Base {

	public function get_name(): string  { return 'sf-portfolio-grid'; }
	public function get_title(): string { return esc_html__( 'SF Portfolio Grid', 'sf-addons' ); }
	public function get_icon(): string  { return 'eicon-gallery-grid'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }
	public function get_script_depends(): array { return [ 'sf-addons-widgets' ]; }

	protected function register_controls(): void {
		$this->start_controls_section( 'section_query', [
			'label' => esc_html__( 'Query', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'posts_per_page', [
			'label'   => esc_html__( 'Items to Show', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 6,
		]);

		$this->add_control( 'show_filter', [
			'label'   => esc_html__( 'Category Filter', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		]);

		$this->add_control( 'filter_label_all', [
			'label'     => esc_html__( '"All" Label', 'sf-addons' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => esc_html__( 'All', 'sf-addons' ),
			'condition' => [ 'show_filter' => 'yes' ],
		]);

		$this->add_control( 'columns', [
			'label'   => esc_html__( 'Columns', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [ '2' => '2', '3' => '3', '4' => '4' ],
			'default' => '3',
		]);

		$this->add_control( 'layout', [
			'label'   => esc_html__( 'Layout', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'grid'    => esc_html__( 'Grid', 'sf-addons' ),
				'masonry' => esc_html__( 'Masonry', 'sf-addons' ),
			],
			'default' => 'grid',
		]);

		$this->add_control( 'hover_effect', [
			'label'   => esc_html__( 'Hover Effect', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'overlay'   => esc_html__( 'Dark Overlay', 'sf-addons' ),
				'zoom'      => esc_html__( 'Zoom In', 'sf-addons' ),
				'slide-up'  => esc_html__( 'Slide Up Info', 'sf-addons' ),
				'none'      => esc_html__( 'None', 'sf-addons' ),
			],
			'default' => 'overlay',
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s   = $this->get_settings_for_display();
		$query = new WP_Query([
			'post_type'      => 'sf_portfolio',
			'posts_per_page' => absint( $s['posts_per_page'] ?? 6 ),
			'post_status'    => 'publish',
		]);

		// Collect categories for filter
		$cats_used = [];
		$posts_data = [];
		while ( $query->have_posts() ) {
			$query->the_post();
			$id    = get_the_ID();
			$terms = get_the_terms( $id, 'sf_portfolio_cat' );
			$term_slugs  = [];
			$term_names  = [];
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$term_slugs[]            = $term->slug;
					$term_names[]            = $term->name;
					$cats_used[ $term->slug ] = $term->name;
				}
			}
			$posts_data[] = [
				'id'          => $id,
				'title'       => get_the_title(),
				'permalink'   => get_permalink(),
				'thumb'       => get_the_post_thumbnail_url( $id, 'large' ),
				'client'      => get_post_meta( $id, 'sf_portfolio_client', true ),
				'services'    => get_post_meta( $id, 'sf_portfolio_services', true ),
				'term_slugs'  => $term_slugs,
				'term_names'  => $term_names,
			];
		}
		wp_reset_postdata();

		$cols    = absint( $s['columns'] ?? 3 );
		$layout  = esc_attr( $s['layout'] ?? 'grid' );
		$hover   = esc_attr( $s['hover_effect'] ?? 'overlay' );
		?>
		<div class="sf-portfolio-wrapper">
			<?php if ( ! empty( $s['show_filter'] ) && 'yes' === $s['show_filter'] && ! empty( $cats_used ) ) : ?>
				<div class="sf-portfolio-filter" role="tablist">
					<button class="sf-filter-tab active" data-filter="*" role="tab">
						<?php echo esc_html( $s['filter_label_all'] ?? esc_html__( 'All', 'sf-addons' ) ); ?>
					</button>
					<?php foreach ( $cats_used as $slug => $name ) : ?>
						<button class="sf-filter-tab" data-filter="<?php echo esc_attr( $slug ); ?>" role="tab">
							<?php echo esc_html( $name ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="sf-portfolio-grid sf-portfolio-cols-<?php echo esc_attr( $cols ); ?> sf-portfolio-<?php echo $layout; ?> sf-portfolio-hover-<?php echo $hover; ?>">
				<?php foreach ( $posts_data as $p ) : ?>
					<div class="sf-portfolio-item"
						 data-cats="<?php echo esc_attr( implode( ' ', $p['term_slugs'] ) ); ?>">
						<div class="sf-portfolio-media">
							<?php if ( $p['thumb'] ) : ?>
								<img src="<?php echo esc_url( $p['thumb'] ); ?>"
									 alt="<?php echo esc_attr( $p['title'] ); ?>"
									 loading="lazy">
							<?php endif; ?>
							<div class="sf-portfolio-overlay">
								<div class="sf-portfolio-overlay-content">
									<h3 class="sf-portfolio-title"><?php echo esc_html( $p['title'] ); ?></h3>
									<?php if ( ! empty( $p['services'] ) ) : ?>
										<span class="sf-portfolio-services"><?php echo esc_html( $p['services'] ); ?></span>
									<?php elseif ( ! empty( $p['term_names'] ) ) : ?>
										<span class="sf-portfolio-services"><?php echo esc_html( implode( ', ', $p['term_names'] ) ); ?></span>
									<?php endif; ?>
									<a href="<?php echo esc_url( $p['permalink'] ); ?>" class="sf-portfolio-link" aria-label="<?php echo esc_attr( $p['title'] ); ?>">
										<span>+</span>
									</a>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
