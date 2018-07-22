<div class="slpages-index-page-stats">
	<?php if ( !empty( $page_stats[ 'variants' ] ) ): ?>
		<?php foreach( $page_stats[ 'variants' ] as $variant_name => $variant_stats ): ?>
			<div class="variation <?php echo $variant_stats[ 'class' ]; ?>">
				<div class="variation-name"><?php echo $variant_name; ?></div>
				<div class="variation-stats"><strong><?php echo $variant_stats[ 'visits' ]; ?></strong> <?php echo __( 'visits with', 'slpages' ); ?> <strong><?php echo $variant_stats[ 'conversions' ]; ?></strong> <?php echo __( 'conversions', 'slpages' ); ?></div>
				<div class="variation-conversion-rate"><?php echo $variant_stats[ 'conversion_rate' ]; ?>%</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
