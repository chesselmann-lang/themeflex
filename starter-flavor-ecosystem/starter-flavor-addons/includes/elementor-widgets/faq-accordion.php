<?php
/**
 * Elementor Widget: FAQ Accordion
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_FAQ_Accordion extends \Elementor\Widget_Base {

	public function get_name(): string  { return 'sf-faq-accordion'; }
	public function get_title(): string { return esc_html__( 'SF FAQ Accordion', 'sf-addons' ); }
	public function get_icon(): string  { return 'eicon-accordion'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }
	public function get_script_depends(): array { return [ 'sf-addons-widgets' ]; }

	protected function register_controls(): void {
		$this->start_controls_section( 'section_faq', [
			'label' => esc_html__( 'FAQ Items', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'source', [
			'label'   => esc_html__( 'Source', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [ 'custom' => esc_html__( 'Custom', 'sf-addons' ), 'cpt' => esc_html__( 'FAQ CPT', 'sf-addons' ) ],
			'default' => 'custom',
		]);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'question', [
			'label'   => esc_html__( 'Question', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'How do I get started?', 'sf-addons' ),
		]);
		$repeater->add_control( 'answer', [
			'label'   => esc_html__( 'Answer', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::WYSIWYG,
			'default' => esc_html__( 'Getting started is simple. Just sign up and follow the onboarding steps.', 'sf-addons' ),
		]);
		$repeater->add_control( 'open_by_default', [
			'label' => esc_html__( 'Open by default?', 'sf-addons' ),
			'type'  => \Elementor\Controls_Manager::SWITCHER,
		]);

		$this->add_control( 'faq_items', [
			'label'       => esc_html__( 'FAQ Items', 'sf-addons' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'question' => 'What is included in the theme package?', 'answer' => 'The theme package includes the main theme, child theme, demo content, and detailed documentation.' ],
				[ 'question' => 'Do I need to know how to code?', 'answer' => 'No! The theme is built for everyone. With Elementor drag-and-drop builder, you can create beautiful pages without writing a single line of code.' ],
				[ 'question' => 'Is there support available?', 'answer' => 'Yes, we offer dedicated support via our helpdesk. Premium customers receive priority 24-hour responses.' ],
				[ 'question' => 'Can I use this theme on multiple sites?', 'answer' => 'Each license is valid for one site. If you need multiple site licenses, please contact us for bundle pricing.' ],
			],
			'title_field' => '{{{ question }}}',
			'condition'   => [ 'source' => 'custom' ],
		]);

		$this->add_control( 'accordion_type', [
			'label'   => esc_html__( 'Accordion Type', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'single'   => esc_html__( 'Single (closes others)', 'sf-addons' ),
				'multiple' => esc_html__( 'Multiple (stay open)', 'sf-addons' ),
			],
			'default' => 'single',
		]);

		$this->add_control( 'icon_open', [
			'label'   => esc_html__( 'Icon (Open)', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [ 'plus-minus' => '+ / −', 'chevron' => '▼ / ▲', 'arrow' => '→ / ↓' ],
			'default' => 'plus-minus',
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s     = $this->get_settings_for_display();
		$items = $s['faq_items'] ?? [];

		if ( 'cpt' === ( $s['source'] ?? 'custom' ) ) {
			$query = new WP_Query([ 'post_type' => 'sf_faq', 'posts_per_page' => -1, 'post_status' => 'publish' ]);
			$items = [];
			while ( $query->have_posts() ) {
				$query->the_post();
				$items[] = [ 'question' => get_the_title(), 'answer' => get_the_content(), 'open_by_default' => '' ];
			}
			wp_reset_postdata();
		}

		$type = esc_attr( $s['accordion_type'] ?? 'single' );
		$icon = esc_attr( $s['icon_open'] ?? 'plus-minus' );
		?>
		<div class="sf-faq-accordion sf-faq-icon-<?php echo $icon; ?>"
			 data-type="<?php echo $type; ?>"
			 role="list"
			 itemscope itemtype="https://schema.org/FAQPage">

			<?php foreach ( $items as $i => $item ) :
				$open = ! empty( $item['open_by_default'] ) && 'yes' === $item['open_by_default'];
				$id   = 'sf-faq-' . $this->get_id() . '-' . $i;
			?>
				<div class="sf-faq-item <?php echo $open ? 'is-open' : ''; ?>"
					 role="listitem"
					 itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">

					<button class="sf-faq-question"
							aria-expanded="<?php echo $open ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr( $id ); ?>">
						<span itemprop="name"><?php echo esc_html( $item['question'] ?? '' ); ?></span>
						<span class="sf-faq-icon" aria-hidden="true"></span>
					</button>

					<div class="sf-faq-answer"
						 id="<?php echo esc_attr( $id ); ?>"
						 itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"
						 <?php echo $open ? '' : 'hidden'; ?>>
						<div class="sf-faq-answer-inner" itemprop="text">
							<?php echo wp_kses_post( $item['answer'] ?? '' ); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
