<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap sf-addons-wrap sf-demo-importer">

	<div class="sf-addons-header">
		<h1><?php esc_html_e( 'One-Click Demo Importer', 'sf-addons' ); ?></h1>
		<p><?php esc_html_e( 'Choose a demo below and import everything with a single click — pages, settings, menus, and widgets.', 'sf-addons' ); ?></p>
	</div>

	<!-- Filter tabs -->
	<div class="sf-demo-filters">
		<button class="sf-filter-btn active" data-cat="all"><?php esc_html_e( 'All', 'sf-addons' ); ?></button>
		<?php
		$cats = array_unique( array_column( $demos, 'category' ) );
		foreach ( $cats as $cat ) :
		?>
		<button class="sf-filter-btn" data-cat="<?php echo esc_attr( $cat ); ?>"><?php echo esc_html( $cat ); ?></button>
		<?php endforeach; ?>
	</div>

	<!-- Demo Grid -->
	<div class="sf-demo-grid">
		<?php foreach ( $demos as $demo ) : ?>
		<div class="sf-demo-card" data-cat="<?php echo esc_attr( $demo['category'] ?? '' ); ?>">
			<div class="sf-demo-screenshot">
				<img src="<?php echo esc_url( SF_ADDONS_URL . 'demo-packs/' . $demo['slug'] . '/screenshot.jpg' ); ?>"
					 alt="<?php echo esc_attr( $demo['name'] ); ?>"
					 loading="lazy">
				<?php if ( ! empty( $demo['is_new'] ) ) : ?>
					<span class="sf-badge sf-badge--new"><?php esc_html_e( 'New', 'sf-addons' ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $demo['is_pro'] ) ) : ?>
					<span class="sf-badge sf-badge--pro"><?php esc_html_e( 'Pro', 'sf-addons' ); ?></span>
				<?php endif; ?>
				<div class="sf-demo-overlay">
					<a href="<?php echo esc_url( $demo['preview_url'] ?? '#' ); ?>" target="_blank" class="button">
						<?php esc_html_e( 'Preview', 'sf-addons' ); ?>
					</a>
					<button class="button button-primary sf-import-btn"
							data-demo="<?php echo esc_attr( $demo['slug'] ); ?>"
							data-name="<?php echo esc_attr( $demo['name'] ); ?>">
						<?php esc_html_e( 'Import', 'sf-addons' ); ?>
					</button>
				</div>
			</div>
			<div class="sf-demo-info">
				<h3><?php echo esc_html( $demo['name'] ); ?></h3>
				<span class="sf-demo-category"><?php echo esc_html( $demo['category'] ?? '' ); ?></span>
			</div>
		</div>
		<?php endforeach; ?>
	</div><!-- /.sf-demo-grid -->

	<!-- Import Progress Modal -->
	<div id="sf-import-modal" class="sf-modal" style="display:none">
		<div class="sf-modal-inner">
			<h2 id="sf-modal-title"><?php esc_html_e( 'Importing Demo…', 'sf-addons' ); ?></h2>
			<div class="sf-progress-bar">
				<div class="sf-progress-fill" style="width:0%"></div>
			</div>
			<p id="sf-modal-status"><?php esc_html_e( 'Preparing…', 'sf-addons' ); ?></p>
			<div id="sf-modal-steps">
				<div class="sf-step" data-step="0"><?php esc_html_e( '1. Checking plugins', 'sf-addons' ); ?></div>
				<div class="sf-step" data-step="1"><?php esc_html_e( '2. Importing content', 'sf-addons' ); ?></div>
				<div class="sf-step" data-step="2"><?php esc_html_e( '3. Applying customizer', 'sf-addons' ); ?></div>
				<div class="sf-step" data-step="3"><?php esc_html_e( '4. Loading page builder templates', 'sf-addons' ); ?></div>
				<div class="sf-step" data-step="4"><?php esc_html_e( '5. Setting homepage & menus', 'sf-addons' ); ?></div>
			</div>
			<div id="sf-modal-done" style="display:none">
				<p class="sf-success"><?php esc_html_e( '🎉 Demo installed successfully!', 'sf-addons' ); ?></p>
				<a id="sf-view-site" href="#" class="button button-primary" target="_blank">
					<?php esc_html_e( 'View Your Site', 'sf-addons' ); ?>
				</a>
			</div>
		</div>
	</div>
	<div id="sf-modal-overlay" style="display:none"></div>

</div><!-- /.wrap -->
