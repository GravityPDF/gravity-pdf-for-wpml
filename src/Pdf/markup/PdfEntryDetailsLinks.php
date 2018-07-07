<?php
/** @var array $pdf_list The active PDFs for the current Gravity Forms Entry */
?>

<strong><?php esc_html_e( 'PDFs', 'gravity-forms-pdf-extended' ); ?></strong><br />
<?php foreach ( $pdf_list as $pdf ): ?>
	<div class="gfpdf_detailed_pdf_container">
		<span>
			<?php echo esc_html( $pdf['name'] ); ?>
			<?php if ( count( $pdf['languages'] ) > 0 ): ?>
				(<?= implode( ', ', $pdf['languages'] ); ?>)
			<?php endif; ?>
		</span>

		<div>
			<a href="<?php echo esc_url( $pdf['view'] ); ?>" target="_blank" class="button">
				<?php esc_html_e( 'View', 'gravity-forms-pdf-extended' ); ?>
			</a>

			<a href="<?php echo esc_url( $pdf['download'] ); ?>" class="button">
				<?php esc_html_e( 'Download', 'gravity-forms-pdf-extended' ); ?>
			</a>
		</div>
	</div>
<?php endforeach; ?>
