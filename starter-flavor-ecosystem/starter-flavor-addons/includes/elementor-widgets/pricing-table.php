<?php
/**
 * Elementor Widget: Pricing Table
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_Pricing_Table extends \Elementor\Widget_Base {

	public function get_name(): string    { return 'sf-pricing-table'; }
	public function get_title(): string   { return esc_html__( 'SF Pricing Table', 'sf-addons' ); }
	public function get_icon(): string    { return 'eicon-price-table'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }
	public function get_keywords(): array { return [ 'pricing', 'price', 'table', 'plan' ]; }

	protected function register_controls(): void {

		// ── Plan Settings ──────────────────────────────────────────────────────
		$this->start_controls_section( 'section_plan', [
			'label' => esc_html__( 'Plan', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'plan_name', [
			'label'   => esc_html__( 'Plan Name', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Pro', 'sf-addons' ),
		]);

		$this->add_control( 'plan_description', [
			'label'   => esc_html__( 'Subtitle', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'For growing teams', 'sf-addons' ),
		]);

		$this->add_control( 'price', [
			'label'   => esc_html__( 'Price', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '49',
		]);

		$this->add_control( 'currency', [
			'label'   => esc_html__( 'Currency Symbol', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '$',
		]);

		$this->add_control( 'period', [
			'label'   => esc_html__( 'Billing Period', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'per month', 'sf-addons' ),
		]);

		$this->add_control( 'featured', [
			'label'     => esc_html__( 'Featured Plan', 'sf-addons' ),
			'type'      => \Elementor\Controls_Manager::SWITCHER,
			'default'   => '',
		]);

		$this->add_control( 'featured_label', [
			'label'     => esc_html__( 'Featured Badge Text', 'sf-addons' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => esc_html__( 'Most Popular', 'sf-addons' ),
			'condition' => [ 'featured' => 'yes' ],
		]);

		$this->end_controls_section();

		// ── Features ───────────────────────────────────────────────────────────
		$this->start_controls_section( 'section_features', [
			'label' => esc_html__( 'Features', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'feature_text', [
			'label'   => esc_html__( 'Feature', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Feature item', 'sf-addons' ),
		]);
		$repeater->add_control( 'feature_included', [
			'label'   => esc_html__( 'Included?', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		]);

		$this->add_control( 'features', [
			'label'       => esc_html__( 'Feature List', 'sf-addons' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'feature_text' => esc_html__( '10 Projects', 'sf-addons' ),      'feature_included' => 'yes' ],
				[ 'feature_text' => esc_html__( '50 GB Storage', 'sf-addons' ),    'feature_included' => 'yes' ],
				[ 'feature_text' => esc_html__( 'Priority Support', 'sf-addons' ), 'feature_included' => 'yes' ],
				[ 'feature_text' => esc_html__( 'Custom Domain', 'sf-addons' ),    'feature_included' => '' ],
			],
			'title_field' => '{{{ feature_text }}}',
		]);

		$this->end_controls_section();

		// ── Button ─────────────────────────────────────────────────────────────
		$this->start_controls_section( 'section_button', [
			'label' => esc_html__( 'Button', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'btn_text', [
			'label'   => esc_html__( 'Button Text', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Get Started', 'sf-addons' ),
		]);

		$this->add_control( 'btn_url', [
			'label'   => esc_html__( 'Button URL', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => [ 'url' => '#' ],
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();
		$featured = ! empty( $s['featured'] ) && 'yes' === $s['featured'];
		$classes  = 'sf-pricing-table' . ( $featured ? ' sf-pricing-table--featured' : '' );
		?>
		<div class="<?php echo esc_attr( $classes ); ?>">
			<?php if ( $featured && ! empty( $s['featured_label'] ) ) : ?>
				<div class="sf-pricing-badge"><?php echo esc_html( $s['featured_label'] ); ?></div>
			<?php endif; ?>

			<div class="sf-pricing-header">
				<h3 class="sf-pricing-name"><?php echo esc_html( $s['plan_name'] ); ?></h3>
				<?php if ( ! empty( $s['plan_description'] ) ) : ?>
					<p class="sf-pricing-desc"><?php echo esc_html( $s['plan_description'] ); ?></p>
				<?php endif; ?>
				<div class="sf-pricing-price">
					<span class="sf-pricing-currency"><?php echo esc_html( $s['currency'] ); ?></span>
					<span class="sf-pricing-amount"><?php echo esc_html( $s['price'] ); ?></span>
					<span class="sf-pricing-period"><?php echo esc_html( $s['period'] ); ?></span>
				</div>
			</div>

			<ul class="sf-pricing-features">
				<?php foreach ( $s['features'] as $feature ) :
					$included = ! empty( $feature['feature_included'] ) && 'yes' === $feature['feature_included'];
				?>
					<li class="sf-pricing-feature <?php echo $included ? 'sf-feature--yes' : 'sf-feature--no'; ?>">
						<span class="sf-feature-icon"><?php echo $included ? '✓' : '✗'; ?></span>
						<?php echo esc_html( $feature['feature_text'] ); ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( ! empty( $s['btn_text'] ) ) :
				$url    = ! empty( $s['btn_url']['url'] ) ? $s['btn_url']['url'] : '#';
				$target = ! empty( $s['btn_url']['is_external'] ) ? ' target="_blank"' : '';
				$rel    = ! empty( $s['btn_url']['nofollow'] ) ? ' rel="nofollow"' : '';
			?>
				<div class="sf-pricing-footer">
					<a href="<?php echo esc_url( $url ); ?>"<?php echo $target . $rel; // phpcs:ignore ?> class="sf-pricing-btn">
						<?php echo esc_html( $s['btn_text'] ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
