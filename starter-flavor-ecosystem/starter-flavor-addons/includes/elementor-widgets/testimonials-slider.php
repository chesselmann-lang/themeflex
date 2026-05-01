<?php
/**
 * Elementor Widget: Testimonials Slider
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_Testimonials_Slider extends \Elementor\Widget_Base {

	public function get_name(): string  { return 'sf-testimonials-slider'; }
	public function get_title(): string { return esc_html__( 'SF Testimonials Slider', 'sf-addons' ); }
	public function get_icon(): string  { return 'eicon-testimonial-carousel'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }
	public function get_script_depends(): array { return [ 'sf-addons-widgets' ]; }

	protected function register_controls(): void {
		$this->start_controls_section( 'section_testimonials', [
			'label' => esc_html__( 'Testimonials', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'source', [
			'label'   => esc_html__( 'Source', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'custom' => esc_html__( 'Custom (Repeater)', 'sf-addons' ),
				'cpt'    => esc_html__( 'Testimonials CPT', 'sf-addons' ),
			],
			'default' => 'custom',
		]);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'avatar', [ 'label' => esc_html__( 'Avatar', 'sf-addons' ), 'type' => \Elementor\Controls_Manager::MEDIA ] );
		$repeater->add_control( 'text', [ 'label' => esc_html__( 'Quote', 'sf-addons' ), 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => esc_html__( 'This product completely transformed our workflow. Highly recommended!', 'sf-addons' ) ]);
		$repeater->add_control( 'author', [ 'label' => esc_html__( 'Author', 'sf-addons' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Jane Doe' ]);
		$repeater->add_control( 'position', [ 'label' => esc_html__( 'Position', 'sf-addons' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'CEO, Acme Inc.' ]);
		$repeater->add_control( 'rating', [ 'label' => esc_html__( 'Rating (1-5)', 'sf-addons' ), 'type' => \Elementor\Controls_Manager::SELECT,
			'options' => [ '5' => '★★★★★', '4' => '★★★★', '3' => '★★★', '2' => '★★', '1' => '★' ], 'default' => '5' ]);

		$this->add_control( 'testimonials', [
			'label'       => esc_html__( 'Testimonials', 'sf-addons' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'author' => 'Sarah Mitchell', 'position' => 'CTO, StartupXYZ', 'rating' => '5' ],
				[ 'author' => 'David Park',     'position' => 'Founder, DesignCo', 'rating' => '5' ],
				[ 'author' => 'Maria Garcia',   'position' => 'CMO, Growth Ltd.', 'rating' => '4' ],
			],
			'title_field' => '{{{ author }}}',
			'condition'   => [ 'source' => 'custom' ],
		]);

		$this->add_control( 'layout', [
			'label'   => esc_html__( 'Layout', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'slider'   => esc_html__( 'Slider', 'sf-addons' ),
				'grid'     => esc_html__( 'Grid', 'sf-addons' ),
				'masonry'  => esc_html__( 'Masonry', 'sf-addons' ),
			],
			'default' => 'slider',
		]);

		$this->add_control( 'autoplay', [
			'label'     => esc_html__( 'Autoplay', 'sf-addons' ),
			'type'      => \Elementor\Controls_Manager::SWITCHER,
			'default'   => 'yes',
			'condition' => [ 'layout' => 'slider' ],
		]);

		$this->add_control( 'style', [
			'label'   => esc_html__( 'Card Style', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'default' => esc_html__( 'Default', 'sf-addons' ),
				'bubble'  => esc_html__( 'Speech Bubble', 'sf-addons' ),
				'minimal' => esc_html__( 'Minimal', 'sf-addons' ),
				'bold'    => esc_html__( 'Bold Quote', 'sf-addons' ),
			],
			'default' => 'default',
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$items = $s['testimonials'] ?? [];

		if ( 'cpt' === ( $s['source'] ?? 'custom' ) ) {
			$query = new WP_Query([ 'post_type' => 'sf_testimonial', 'posts_per_page' => 6, 'post_status' => 'publish' ]);
			$items = [];
			while ( $query->have_posts() ) {
				$query->the_post();
				$id = get_the_ID();
				$items[] = [
					'text'     => get_the_content(),
					'author'   => get_post_meta( $id, 'sf_testimonial_author', true ) ?: get_the_title(),
					'position' => get_post_meta( $id, 'sf_testimonial_position', true ),
					'rating'   => get_post_meta( $id, 'sf_testimonial_rating', true ) ?: '5',
					'avatar'   => [ 'id' => get_post_thumbnail_id( $id ) ],
				];
			}
			wp_reset_postdata();
		}

		$is_slider  = 'slider' === ( $s['layout'] ?? 'slider' );
		$autoplay   = ! empty( $s['autoplay'] ) && 'yes' === $s['autoplay'];
		$wrapper    = $is_slider ? 'sf-testimonials-slider' : 'sf-testimonials-' . esc_attr( $s['layout'] );
		?>
		<div class="<?php echo esc_attr( $wrapper ); ?> sf-testimonials-style-<?php echo esc_attr( $s['style'] ?? 'default' ); ?>"
			 <?php echo $is_slider ? 'data-slider="true" data-autoplay="' . esc_attr( $autoplay ? 'true' : 'false' ) . '"' : ''; ?>>

			<?php if ( $is_slider ) : ?><div class="sf-slider-track"><?php endif; ?>

			<?php foreach ( $items as $item ) :
				$avatar_url = ! empty( $item['avatar']['id'] )
					? wp_get_attachment_image_url( $item['avatar']['id'], 'thumbnail' )
					: SF_ADDONS_URL . 'assets/images/avatar-placeholder.jpg';
				$stars = (int) ( $item['rating'] ?? 5 );
			?>
				<div class="sf-testimonial-card">
					<?php if ( $stars ) : ?>
						<div class="sf-testimonial-stars" aria-label="<?php printf( esc_attr__( '%d out of 5 stars', 'sf-addons' ), $stars ); ?>">
							<?php echo str_repeat( '<span class="sf-star sf-star--filled">★</span>', $stars );
								  echo str_repeat( '<span class="sf-star">★</span>', 5 - $stars ); ?>
						</div>
					<?php endif; ?>
					<blockquote class="sf-testimonial-text">
						<?php echo wp_kses_post( $item['text'] ?? '' ); ?>
					</blockquote>
					<div class="sf-testimonial-author">
						<img src="<?php echo esc_url( $avatar_url ); ?>"
							 alt="<?php echo esc_attr( $item['author'] ?? '' ); ?>"
							 class="sf-testimonial-avatar" loading="lazy">
						<div>
							<strong class="sf-author-name"><?php echo esc_html( $item['author'] ?? '' ); ?></strong>
							<?php if ( ! empty( $item['position'] ) ) : ?>
								<span class="sf-author-position"><?php echo esc_html( $item['position'] ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( $is_slider ) : ?></div><!-- /.sf-slider-track -->
				<div class="sf-slider-controls">
					<button class="sf-slider-prev" aria-label="<?php esc_attr_e( 'Previous', 'sf-addons' ); ?>">&#8592;</button>
					<div class="sf-slider-dots"></div>
					<button class="sf-slider-next" aria-label="<?php esc_attr_e( 'Next', 'sf-addons' ); ?>">&#8594;</button>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
