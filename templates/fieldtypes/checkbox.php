<?php
list($title,$value) = explode(':', $field['argTitle']);
$arg_name = str_replace(' ', '_', $field['argName']);
?>
<label class="fields" for="<?php echo $arg_name; ?>"><?php echo $title; ?></label>
<input class="fields" type="checkbox" name="<?php echo $field['argName']; ?>" value="<?php echo $value; ?>" id="<?php echo $arg_name; ?>" />
<br style="margin:0;padding:0; height:1px; clear: left;" />