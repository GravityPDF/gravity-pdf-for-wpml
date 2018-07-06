<label for="change_wpml_language"><?= esc_html__( 'Change Language:', 'gravity-pdf-for-wpml' ); ?></label>
<select name="gpdf_language" id="change_wpml_language" class="widefat">
	<?php foreach ( $languages as $lang ): ?>
		<option value="<?= esc_attr( $lang['code'] ); ?>" <?php selected( $languageCode, $lang['code'] ); ?>>
			<?= esc_attr( $lang['translated_name'] ); ?>
			<?php if ( $lang['native_name'] !== $lang['translated_name'] ): ?>
				(<?= esc_attr( $lang['native_name'] ); ?>)
			<?php endif; ?>
		</option>
	<?php endforeach; ?>
</select>

<input name="gpdf_original_language" value="<?= esc_attr( $languageCode ); ?>" type="hidden" />

<br><br>