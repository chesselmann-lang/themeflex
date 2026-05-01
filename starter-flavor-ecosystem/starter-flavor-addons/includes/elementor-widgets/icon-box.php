<?php
/**
 * Elementor Widget: Icon Box (feature/service box)
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_Icon_Box extends \Elementor\Widget_Base {

	public function get_name(): string  { return 'sf-icon-box'; }
	public function get_title(): string { return esc_html__( 'SF Icon Box', 'sf-addons' ); }
	public function get_icon(): string  { return 'eicon-icon-box'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }

	protected function register_controls(): void {
		$this->start_controls_section( 'section_content', [
			'label' => esc_html__( 'Content', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'icon', [
			'label'   => esc_html__( 'Icon', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::ICONS,
			'default' => [ 'value' => 'fas fa-star', 'library' => 'fa-solid' ],
		]);

		$this->add_control( 'title', [
			'label'   => esc_html__( 'Title', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Powerful Feature', 'sf-addons' ),
		]);

		$this->add_control( 'description', [
			'label'   => esc_html__( 'Description', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => esc_html__( 'Describe this feature in a short paragraph. Keep it concise and benefit-focused.', 'sf-addons' ),
		]);

		$this->add_control( 'link', [
			'label' => esc_html__( 'Link', 'sf-addons' ),
			'type'  => \Elementor\Controls_Manager::URL,
		]);

		$this->add_control( 'link_text', [
			'label'     => esc_html__( 'Link Text', 'sf-addons' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => esc_html__( 'Learn more →', 'sf-addons' ),
			'condition' => [ 'link[url]!' => '' ],
		]);

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', [
			'label' => esc_html__( 'Style', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'box_style', [
			'label'   => esc_html__( 'Box Style', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'default'  => esc_html__( 'Default', 'sf-addons' ),
				'bordered' => esc_html__( 'Bordered', 'sf-addons' ),
				'filled'   => esc_html__( 'Filled Background', 'sf-addons' ),
				'shadow'   => esc_html__( 'Shadow Card', 'sf-addons' ),
				'minimal'  => esc_html__( 'Minimal (no box)', 'sf-addons' ),
			],
			'default' => 'shadow',
		]);

		$this->add_control( 'icon_style', [
			'label'   => esc_html__( 'Icon Style', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'plain'   => esc_html__( 'Plain', 'sf-addons' ),
				'circle'  => esc_html__( 'Circle', 'sf-addons' ),
				'square'  => esc_html__( 'Square', 'sf-addons' ),
				'rounded' => esc_html__( 'Rounded', 'sf-addons' ),
			],
			'default' => 'circle',
		]);

		$this->add_control( 'alignment', [
			'label'   => esc_html__( 'Alignment', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::CHOOSE,
			'options' => [
				'left'   => [ 'title' => esc_html__( 'Left', 'sf-addons' ),   'icon' => 'eicon-text-align-left' ],
				'center' => [ 'title' => esc_html__( 'Center', 'sf-addons' ), 'icon' => 'eicon-text-align-center' ],
				'right'  => [ 'title' => esc_html__( 'Right', 'sf-addons' ),  'icon' => 'eicon-text-align-right' ],
			],
			'default'   => 'left',
			'selectors' => [ '{{WRAPPER}} .sf-icon-box' => 'text-align: {{VALUE}};' ],
		]);

		$this->add_control( 'hover_effect', [
			'label'   => esc_html__( 'Hover Effect', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'none'       => esc_html__( 'None', 'sf-addons' ),
				'lift'       => esc_html__( 'Lift (rise up)', 'sf-addons' ),
				'glow'       => esc_html__( 'Glow', 'sf-addons' ),
				'icon-spin'  => esc_html__( 'Icon Spin', 'sf-addons' ),
				'icon-scale' => esc_html__( 'Icon Scale', 'sf-addons' ),
			],
			'default' => 'lift',
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();
		$box_class = implode( ' ', array_filter([
			'sf-icon-box',
			'sf-icon-box--' . esc_attr( $s['box_style'] ?? 'shadow' ),
			'sf-icon-box--icon-' . esc_attr( $s['icon_style'] ?? 'circle' ),
			'sf-icon-box--hover-' . esc_attr( $s['hover_effect'] ?? 'lift' ),
		]));
		$has_link  = ! empty( $s['link']['url'] );
		$tag       = $has_link ? 'a' : 'div';
		$attrs     = $has_link
			? ' href="' . esc_url( $s['link']['url'] ) . '"' . ( ! empty( $s['link']['is_external'] ) ? ' target="_blank"' : '' ) . ( ! empty( $s['link']['nofollow'] ) ? ' rel="nofollow"' : '' )
			: '';
		?>
		<<?php echo $tag; // phpcs:ignore ?> class="<?php echo esc_attr( $box_class ); ?>"<?php echo $attrs; // phpcs:ignore ?>>
			<?php if ( ! empty( $s['icon']['value'] ) ) : ?>
				<div class="sf-icon-box__icon">
					<?php \Elementor\Icons_Manager::render_icon( $s['icon'], [ 'aria-hidden' => 'true' ] ); ?>
				</div>
			<?php endif; ?>
			<div class="sf-icon-box__content">
				<?php if ( ! empty( $s['title'] ) ) : ?>
					<h3 class="sf-icon-box__title"><?php echo esc_html( $s['title'] ); ?></h3>
				<?php endif; ?>
				<?php if ( ! empty( $s['description'] ) ) : ?>
					<p class="sf-icon-box__desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( $has_link && ! $has_link && ! empty( $s['link_text'] ) ) : // inline link only when tag is div ?>
					<a href="<?php echo esc_url( $s['link']['url'] ); ?>" class="sf-icon-box__link">
						<?php echo esc_html( $s['link_text'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</<?php echo $tag; // phpcs:ignore ?>>
		<?php
	}
}
