<?php
/**
 * Elementor Widget: Timeline
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_Timeline extends \Elementor\Widget_Base {

	public function get_name(): string  { return 'sf-timeline'; }
	public function get_title(): string { return esc_html__( 'SF Timeline', 'sf-addons' ); }
	public function get_icon(): string  { return 'eicon-time-line'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }
	public function get_script_depends(): array { return [ 'sf-addons-widgets' ]; }

	protected function register_controls(): void {
		$this->start_controls_section( 'section_timeline', [
			'label' => esc_html__( 'Timeline Events', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'year',    [ 'label' => esc_html__( 'Year / Date', 'sf-addons' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '2024' ]);
		$repeater->add_control( 'title',   [ 'label' => esc_html__( 'Title', 'sf-addons' ),       'type' => \Elementor\Controls_Manager::TEXT, 'default' => esc_html__( 'Milestone', 'sf-addons' ) ]);
		$repeater->add_control( 'content', [ 'label' => esc_html__( 'Description', 'sf-addons' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ]);
		$repeater->add_control( 'icon',    [ 'label' => esc_html__( 'Icon', 'sf-addons' ),        'type' => \Elementor\Controls_Manager::ICONS ]);
		$repeater->add_control( 'image',   [ 'label' => esc_html__( 'Image', 'sf-addons' ),       'type' => \Elementor\Controls_Manager::MEDIA ]);

		$this->add_control( 'events', [
			'label'       => esc_html__( 'Events', 'sf-addons' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'year' => '2018', 'title' => 'Company Founded',  'content' => 'Started with a small team of 3 passionate people.' ],
				[ 'year' => '2019', 'title' => 'First Product',    'content' => 'Launched our flagship product to great reception.' ],
				[ 'year' => '2021', 'title' => 'Series A Funding', 'content' => 'Raised $5M to accelerate growth and expand the team.' ],
				[ 'year' => '2023', 'title' => '1M Users',         'content' => 'Reached 1 million users across 50 countries.' ],
			],
			'title_field' => '{{{ year }}} — {{{ title }}}',
		]);

		$this->add_control( 'layout', [
			'label'   => esc_html__( 'Layout', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'vertical'   => esc_html__( 'Vertical (centered)', 'sf-addons' ),
				'left'       => esc_html__( 'Left-aligned', 'sf-addons' ),
				'horizontal' => esc_html__( 'Horizontal', 'sf-addons' ),
			],
			'default' => 'vertical',
		]);

		$this->add_control( 'animate', [
			'label'   => esc_html__( 'Scroll Animation', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s       = $this->get_settings_for_display();
		$layout  = esc_attr( $s['layout'] ?? 'vertical' );
		$animate = ! empty( $s['animate'] ) && 'yes' === $s['animate'];
		?>
		<div class="sf-timeline sf-timeline--<?php echo $layout; ?> <?php echo $animate ? 'sf-timeline--animate' : ''; ?>">
			<?php foreach ( $s['events'] as $i => $ev ) :
				$side = ( $i % 2 === 0 ) ? 'left' : 'right';
			?>
				<div class="sf-timeline-item sf-timeline-item--<?php echo esc_attr( $side ); ?>" data-aos="fade-up">
					<div class="sf-timeline-dot">
						<?php if ( ! empty( $ev['icon']['value'] ) ) :
							\Elementor\Icons_Manager::render_icon( $ev['icon'], [ 'aria-hidden' => 'true' ] );
						else : ?>
							<span></span>
						<?php endif; ?>
					</div>
					<div class="sf-timeline-content">
						<?php if ( ! empty( $ev['year'] ) ) : ?>
							<span class="sf-timeline-year"><?php echo esc_html( $ev['year'] ); ?></span>
						<?php endif; ?>
						<h3 class="sf-timeline-title"><?php echo esc_html( $ev['title'] ?? '' ); ?></h3>
						<?php if ( ! empty( $ev['content'] ) ) : ?>
							<p class="sf-timeline-desc"><?php echo esc_html( $ev['content'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $ev['image']['id'] ) ) :
							echo wp_get_attachment_image( $ev['image']['id'], 'medium', false, [ 'class' => 'sf-timeline-img', 'loading' => 'lazy' ] );
						endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
