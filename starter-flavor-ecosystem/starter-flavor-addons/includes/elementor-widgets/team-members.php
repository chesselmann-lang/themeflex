<?php
/**
 * Elementor Widget: Team Members
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_Team_Members extends \Elementor\Widget_Base {

	public function get_name(): string    { return 'sf-team-members'; }
	public function get_title(): string   { return esc_html__( 'SF Team Members', 'sf-addons' ); }
	public function get_icon(): string    { return 'eicon-person'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }

	protected function register_controls(): void {

		$this->start_controls_section( 'section_query', [
			'label' => esc_html__( 'Query', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'source', [
			'label'   => esc_html__( 'Source', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'cpt'    => esc_html__( 'Team Members CPT', 'sf-addons' ),
				'custom' => esc_html__( 'Custom (Repeater)', 'sf-addons' ),
			],
			'default' => 'custom',
		]);

		$this->add_control( 'posts_per_page', [
			'label'     => esc_html__( 'Number of Members', 'sf-addons' ),
			'type'      => \Elementor\Controls_Manager::NUMBER,
			'default'   => 4,
			'condition' => [ 'source' => 'cpt' ],
		]);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'member_photo', [
			'label' => esc_html__( 'Photo', 'sf-addons' ),
			'type'  => \Elementor\Controls_Manager::MEDIA,
		]);
		$repeater->add_control( 'member_name', [
			'label'   => esc_html__( 'Name', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Alex Johnson', 'sf-addons' ),
		]);
		$repeater->add_control( 'member_role', [
			'label'   => esc_html__( 'Role', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Lead Designer', 'sf-addons' ),
		]);
		$repeater->add_control( 'member_bio', [
			'label' => esc_html__( 'Bio', 'sf-addons' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
			'rows'  => 3,
		]);
		foreach ( [ 'linkedin', 'twitter', 'github', 'email' ] as $social ) {
			$repeater->add_control( 'member_' . $social, [
				'label' => ucfirst( $social ),
				'type'  => \Elementor\Controls_Manager::TEXT,
			]);
		}

		$this->add_control( 'members', [
			'label'       => esc_html__( 'Team Members', 'sf-addons' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => [
				[ 'member_name' => 'Alex Johnson', 'member_role' => 'CEO & Founder' ],
				[ 'member_name' => 'Sarah Kim',    'member_role' => 'Lead Designer' ],
				[ 'member_name' => 'Mark Torres',  'member_role' => 'Head of Dev' ],
				[ 'member_name' => 'Lisa Chen',    'member_role' => 'Marketing Dir.' ],
			],
			'title_field' => '{{{ member_name }}}',
			'condition'   => [ 'source' => 'custom' ],
		]);

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', [
			'label' => esc_html__( 'Layout', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'columns', [
			'label'   => esc_html__( 'Columns', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [ '2' => '2', '3' => '3', '4' => '4' ],
			'default' => '4',
		]);

		$this->add_control( 'style', [
			'label'   => esc_html__( 'Card Style', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'card'    => esc_html__( 'Card with Shadow', 'sf-addons' ),
				'minimal' => esc_html__( 'Minimal', 'sf-addons' ),
				'overlap' => esc_html__( 'Photo Overlap', 'sf-addons' ),
			],
			'default' => 'card',
		]);

		$this->add_control( 'show_bio', [
			'label'   => esc_html__( 'Show Bio', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s       = $this->get_settings_for_display();
		$members = [];

		if ( 'cpt' === $s['source'] ) {
			$query = new WP_Query([
				'post_type'      => 'sf_team',
				'posts_per_page' => absint( $s['posts_per_page'] ?? 4 ),
				'post_status'    => 'publish',
			]);
			while ( $query->have_posts() ) {
				$query->the_post();
				$id = get_the_ID();
				$members[] = [
					'member_photo' => [ 'id' => get_post_thumbnail_id( $id ) ],
					'member_name'  => get_the_title(),
					'member_role'  => get_post_meta( $id, 'sf_team_role', true ),
					'member_bio'   => get_the_excerpt(),
					'member_linkedin' => get_post_meta( $id, 'sf_team_linkedin', true ),
					'member_twitter'  => get_post_meta( $id, 'sf_team_twitter', true ),
					'member_email'    => get_post_meta( $id, 'sf_team_email', true ),
				];
			}
			wp_reset_postdata();
		} else {
			$members = $s['members'] ?? [];
		}

		$cols = absint( $s['columns'] ?? 4 );
		?>
		<div class="sf-team-grid sf-team-cols-<?php echo esc_attr( $cols ); ?> sf-team-style-<?php echo esc_attr( $s['style'] ?? 'card' ); ?>">
			<?php foreach ( $members as $m ) :
				$photo_url = ! empty( $m['member_photo']['id'] )
					? wp_get_attachment_image_url( $m['member_photo']['id'], 'medium' )
					: SF_ADDONS_URL . 'assets/images/team-placeholder.jpg';
			?>
				<div class="sf-team-member">
					<div class="sf-team-photo">
						<img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $m['member_name'] ?? '' ); ?>" loading="lazy">
						<div class="sf-team-socials">
							<?php foreach ( [ 'linkedin', 'twitter', 'github' ] as $soc ) :
								if ( ! empty( $m[ 'member_' . $soc ] ) ) : ?>
									<a href="<?php echo esc_url( $m[ 'member_' . $soc ] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $soc ) ); ?>">
										<span class="sf-icon-<?php echo esc_attr( $soc ); ?>"></span>
									</a>
								<?php endif;
							endforeach; ?>
							<?php if ( ! empty( $m['member_email'] ) ) : ?>
								<a href="mailto:<?php echo esc_attr( $m['member_email'] ); ?>" aria-label="<?php esc_attr_e( 'Email', 'sf-addons' ); ?>">
									<span class="sf-icon-email"></span>
								</a>
							<?php endif; ?>
						</div>
					</div>
					<div class="sf-team-info">
						<h3 class="sf-team-name"><?php echo esc_html( $m['member_name'] ?? '' ); ?></h3>
						<?php if ( ! empty( $m['member_role'] ) ) : ?>
							<span class="sf-team-role"><?php echo esc_html( $m['member_role'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $s['show_bio'] ) && 'yes' === $s['show_bio'] && ! empty( $m['member_bio'] ) ) : ?>
							<p class="sf-team-bio"><?php echo esc_html( $m['member_bio'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
