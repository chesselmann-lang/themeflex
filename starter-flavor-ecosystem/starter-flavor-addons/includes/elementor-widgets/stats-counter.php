<?php
/**
 * Elementor Widget: Stats Counter (animated number count-up)
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_Stats_Counter extends \Elementor\Widget_Base {

	public function get_name(): string  { return 'sf-stats-counter'; }
	public function get_title(): string { return esc_html__( 'SF Stats Counter', 'sf-addons' ); }
	public function get_icon(): string  { return 'eicon-counter'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }
	public function get_script_depends(): array { return [ 'sf-addons-widgets' ]; }

	protected function register_controls(): void {

		$this->start_controls_section( 'section_stats', [
			'label' => esc_html__( 'Stats', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'stat_icon', [
			'label' => esc_html__( 'Icon', 'sf-addons' ),
			'type'  => \Elementor\Controls_Manager::ICONS,
		]);
		$repeater->add_control( 'stat_number', [
			'label'   => esc_html__( 'Number', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 100,
		]);
		$repeater->add_control( 'stat_prefix', [
			'label' => esc_html__( 'Prefix (e.g. $)', 'sf-addons' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		]);
		$repeater->add_control( 'stat_suffix', [
			'label'   => esc_html__( 'Suffix (e.g. +)', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '+',
		]);
		$repeater->add_control( 'stat_label', [
			'label'   => esc_html__( 'Label', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Happy Clients', 'sf-addons' ),
		]);

		$this->add_control( 'stats', [
			'label'       => esc_html__( 'Stats', 'sf-addons' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'stat_number' => 1200, 'stat_suffix' => '+', 'stat_label' => 'Happy Clients' ],
				[ 'stat_number' => 350,  'stat_suffix' => '+', 'stat_label' => 'Projects Done' ],
				[ 'stat_number' => 15,   'stat_suffix' => '+', 'stat_label' => 'Years Experience' ],
				[ 'stat_number' => 99,   'stat_suffix' => '%', 'stat_label' => 'Satisfaction Rate' ],
			],
			'title_field' => '{{{ stat_label }}}',
		]);

		$this->add_control( 'animation_duration', [
			'label'   => esc_html__( 'Animation Duration (ms)', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 2000,
		]);

		$this->add_control( 'columns', [
			'label'   => esc_html__( 'Columns', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [ '2' => '2', '3' => '3', '4' => '4' ],
			'default' => '4',
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s    = $this->get_settings_for_display();
		$cols = absint( $s['columns'] ?? 4 );
		$dur  = absint( $s['animation_duration'] ?? 2000 );
		?>
		<div class="sf-stats-grid sf-stats-cols-<?php echo esc_attr( $cols ); ?>"
			 data-duration="<?php echo esc_attr( $dur ); ?>">
			<?php foreach ( $s['stats'] as $stat ) : ?>
				<div class="sf-stat-item">
					<?php if ( ! empty( $stat['stat_icon']['value'] ) ) : ?>
						<div class="sf-stat-icon">
							<?php \Elementor\Icons_Manager::render_icon( $stat['stat_icon'], [ 'aria-hidden' => 'true' ] ); ?>
						</div>
					<?php endif; ?>
					<div class="sf-stat-number">
						<span class="sf-stat-prefix"><?php echo esc_html( $stat['stat_prefix'] ?? '' ); ?></span>
						<span class="sf-count-up" data-target="<?php echo esc_attr( $stat['stat_number'] ?? 0 ); ?>">0</span>
						<span class="sf-stat-suffix"><?php echo esc_html( $stat['stat_suffix'] ?? '' ); ?></span>
					</div>
					<span class="sf-stat-label"><?php echo esc_html( $stat['stat_label'] ?? '' ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
