<?php
/**
 * Elementor Widget: Map Embed (Google Maps / OpenStreetMap)
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SF_Widget_Map_Embed extends \Elementor\Widget_Base {

	public function get_name(): string  { return 'sf-map-embed'; }
	public function get_title(): string { return esc_html__( 'SF Map Embed', 'sf-addons' ); }
	public function get_icon(): string  { return 'eicon-google-maps'; }
	public function get_categories(): array { return [ 'starter-flavor' ]; }

	protected function register_controls(): void {
		$this->start_controls_section( 'section_map', [
			'label' => esc_html__( 'Map', 'sf-addons' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control( 'map_type', [
			'label'   => esc_html__( 'Map Provider', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'google'  => esc_html__( 'Google Maps (API Key)', 'sf-addons' ),
				'osm'     => esc_html__( 'OpenStreetMap (free, no key)', 'sf-addons' ),
				'embed'   => esc_html__( 'Custom iFrame Embed', 'sf-addons' ),
			],
			'default' => 'osm',
		]);

		$this->add_control( 'address', [
			'label'       => esc_html__( 'Address or Place Name', 'sf-addons' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => 'Berlin, Germany',
			'description' => esc_html__( 'Used for OpenStreetMap search.', 'sf-addons' ),
			'condition'   => [ 'map_type' => 'osm' ],
		]);

		$this->add_control( 'lat', [
			'label'     => esc_html__( 'Latitude', 'sf-addons' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => '52.5200',
			'condition' => [ 'map_type' => [ 'google', 'osm' ] ],
		]);

		$this->add_control( 'lng', [
			'label'     => esc_html__( 'Longitude', 'sf-addons' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => '13.4050',
			'condition' => [ 'map_type' => [ 'google', 'osm' ] ],
		]);

		$this->add_control( 'zoom', [
			'label'   => esc_html__( 'Zoom Level (1–20)', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SLIDER,
			'range'   => [ 'px' => [ 'min' => 1, 'max' => 20 ] ],
			'default' => [ 'size' => 14 ],
		]);

		$this->add_control( 'embed_url', [
			'label'     => esc_html__( 'iFrame Embed URL', 'sf-addons' ),
			'type'      => \Elementor\Controls_Manager::URL,
			'condition' => [ 'map_type' => 'embed' ],
		]);

		$this->add_control( 'height', [
			'label'   => esc_html__( 'Map Height (px)', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SLIDER,
			'range'   => [ 'px' => [ 'min' => 200, 'max' => 800 ] ],
			'default' => [ 'size' => 400 ],
		]);

		$this->add_control( 'marker_title', [
			'label'   => esc_html__( 'Marker Title', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => esc_html__( 'Our Office', 'sf-addons' ),
		]);

		$this->add_control( 'filter', [
			'label'   => esc_html__( 'Map Filter', 'sf-addons' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => [
				'none'        => esc_html__( 'Default', 'sf-addons' ),
				'grayscale'   => esc_html__( 'Grayscale', 'sf-addons' ),
				'sepia'       => esc_html__( 'Sepia', 'sf-addons' ),
				'dark'        => esc_html__( 'Dark Mode', 'sf-addons' ),
			],
			'default' => 'none',
		]);

		$this->end_controls_section();
	}

	protected function render(): void {
		$s      = $this->get_settings_for_display();
		$height = absint( $s['height']['size'] ?? 400 );
		$zoom   = absint( $s['zoom']['size'] ?? 14 );
		$filter = esc_attr( $s['filter'] ?? 'none' );
		$type   = $s['map_type'] ?? 'osm';

		switch ( $type ) {
			case 'osm':
				$lat    = floatval( $s['lat'] ?? 52.52 );
				$lng    = floatval( $s['lng'] ?? 13.405 );
				$marker = esc_attr( $s['marker_title'] ?? '' );
				$src    = sprintf(
					'https://www.openstreetmap.org/export/embed.html?bbox=%s,%s,%s,%s&layer=mapnik&marker=%s,%s',
					$lng - 0.01, $lat - 0.01, $lng + 0.01, $lat + 0.01, $lat, $lng
				);
				?>
				<div class="sf-map-wrapper sf-map-filter-<?php echo $filter; ?>" style="height:<?php echo $height; ?>px">
					<iframe
						title="<?php echo esc_attr( $marker ?: esc_html__( 'Map', 'sf-addons' ) ); ?>"
						src="<?php echo esc_url( $src ); ?>"
						width="100%"
						height="<?php echo $height; ?>"
						style="border:0"
						loading="lazy"
						referrerpolicy="no-referrer-when-downgrade"
						aria-label="<?php echo esc_attr( $marker ?: esc_html__( 'Map', 'sf-addons' ) ); ?>">
					</iframe>
				</div>
				<?php
				break;

			case 'embed':
				$embed_url = ! empty( $s['embed_url']['url'] ) ? $s['embed_url']['url'] : '';
				if ( empty( $embed_url ) ) {
					echo '<p>' . esc_html__( 'Please enter an embed URL.', 'sf-addons' ) . '</p>';
					break;
				}
				?>
				<div class="sf-map-wrapper sf-map-filter-<?php echo $filter; ?>" style="height:<?php echo $height; ?>px">
					<iframe
						title="<?php esc_attr_e( 'Map', 'sf-addons' ); ?>"
						src="<?php echo esc_url( $embed_url ); ?>"
						width="100%"
						height="<?php echo $height; ?>"
						style="border:0"
						loading="lazy"
						allowfullscreen>
					</iframe>
				</div>
				<?php
				break;

			default:
				echo '<p>' . esc_html__( 'Map provider not configured.', 'sf-addons' ) . '</p>';
		}
	}
}
