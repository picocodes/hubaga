<?php

/**
 * Outputs elements for save, reset, submit
 *
 * 
 */
$val = format_to_edit( json_encode( $this->get_options() ) );
?>

<div class="elementa-importer">
<a href="#" class="wpe-export-btn elementa-btn blue white-text">Export</a>
<a href="#" class="wpe-import-btn elementa-btn red white-text">Import</a>

<div class="wpe-import d-none">
	<p class='wpe-import-feedback alert large' role='alert'></p>
	<textarea rows="5" name="wpe-import" type="textarea" class="form-control"></textarea>
	<p class='alert alert-warning large' role='alert'>Copy your export data into this field and click the continue button below. Your existing data will be lost.</p>
	<button name="wpe-finish-import-btn" class="wpe-finish-import-btn elementa-btn red white-text">Continue</button>
</div>

<div class="wpe-export d-none">
<textarea rows="5" name="wpe-export" type="textarea" class="form-control"><?php echo $val ?></textarea>
<p class='alert alert-info large' role='alert'>Copy the contents of this textarea into a safe place or click the button below to download it.</p>
</div>
</div>