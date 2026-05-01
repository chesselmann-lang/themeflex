<?php
/**
 * Elementor Widget: Logo Carousel (auto-scrolling client logos)
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_Logo_Carousel extends \Elementor\Widget_Base {

	public function get_name(): string  { return 'sf-logo-carousel'; }
	public function get_title(): string { return esc_html__( 'SF Logo Carousel', 'sf-addons' ); }
	public function get_icon(): string  { return 'eicon-carousel'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }
	public function get_script_depends(): array { return [ 'sf-addons-widgets' ]; }

	protected function register_controls(): void {
		$this->start_controls_section( 'section_logos', [
			'label' => esc_html__( 'Logos', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'logo', [
			'label' => esc_html__( 'Logo', 'sf-addons' ),
			'type'  => \Elementor\Controls_Manager::MEDIA,
		]);
		$repeater->add_control( 'logo_url', [
			'label' => esc_html__( 'Link URL', 'sf-addons' ),
			'type'  => \Elementor\Controls_Manager::URL,
		]);
		$repeater->add_control( 'logo_alt', [
			'label'   => esc_html__( 'Brand Name / Alt Text', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Client Logo', 'sf-addons' ),
		]);

		$this->add_control( 'logos', [
			'label'       => esc_html__( 'Logo Items', 'sf-addons' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'logo_alt' => 'Acme Inc' ],
				[ 'logo_alt' => 'TechCorp' ],
				[ 'logo_alt' => 'StartupXYZ' ],
				[ 'logo_alt' => 'MegaBrand' ],
				[ 'logo_alt' => 'Innovate Co' ],
				[ 'logo_alt' => 'Future Labs' ],
			],
			'title_field' => '{{{ logo_alt }}}',
		]);

		$this->add_control( 'speed', [
			'label'   => esc_html__( 'Scroll Speed (px/sec)', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 60,
		]);

		$this->add_control( 'pause_on_hover', [
			'label'   => esc_html__( 'Pause on Hover', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		]);

		$this->add_control( 'grayscale', [
			'label'   => esc_html__( 'Grayscale (hover to colour)', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		]);

		$this->add_control( 'logos_per_view', [
			'label'   => esc_html__( 'Logos Visible', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 5,
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s         = $this->get_settings_for_display();
		$logos     = $s['logos'] ?? [];
		$speed     = absint( $s['speed'] ?? 60 );
		$pause     = ! empty( $s['pause_on_hover'] ) && 'yes' === $s['pause_on_hover'];
		$grayscale = ! empty( $s['grayscale'] ) && 'yes' === $s['grayscale'];
		$per_view  = absint( $s['logos_per_view'] ?? 5 );

		// Duplicate logos for seamless infinite loop
		$track_logos = array_merge( $logos, $logos );
		?>
		<div class="sf-logo-carousel <?php echo $grayscale ? 'sf-logo-carousel--grayscale' : ''; ?>"
			 data-speed="<?php echo esc_attr( $speed ); ?>"
			 data-pause="<?php echo esc_attr( $pause ? 'true' : 'false' ); ?>"
			 data-per-view="<?php echo esc_attr( $per_view ); ?>">
			<div class="sf-logo-track">
				<?php foreach ( $track_logos as $logo ) :
					$img_url = ! empty( $logo['logo']['url'] ) ? $logo['logo']['url'] : '';
					$alt     = ! empty( $logo['logo_alt'] ) ? $logo['logo_alt'] : esc_html__( 'Logo', 'sf-addons' );
					$link    = ! empty( $logo['logo_url']['url'] ) ? $logo['logo_url']['url'] : '';
				?>
					<div class="sf-logo-item">
						<?php if ( $link ) : ?>
							<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $alt ); ?>">
						<?php endif; ?>
						<?php if ( $img_url ) : ?>
							<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
						<?php else : ?>
							<div class="sf-logo-placeholder" aria-label="<?php echo esc_attr( $alt ); ?>"><?php echo esc_html( $alt ); ?></div>
						<?php endif; ?>
						<?php if ( $link ) : ?></a><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
