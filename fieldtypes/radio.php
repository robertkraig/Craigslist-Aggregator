<?php
$argList    = explode(':', $field['argTitle']);
$titles     = explode('|', $argList[0]);
$args       = explode('|', $argList[1]);
$select     = explode('|', $argList[2]);

for($i = 0; $i < count($titles); $i++)
{
    $checked = '';
    if($select[$i] == '1')
        $checked = 'checked="checked"';
    $arg_name = str_replace(' ', '_', $titles[$i]);
?>
    <label class="fields" for="<?php echo $arg_name; ?>"><?php echo $titles[$i]; ?></label>
    <input <?php echo $checked; ?> class="fields" type="radio" name="<?php echo $field['argName']; ?>" value="<?php echo $args[$i]; ?>" id="<?php echo $arg_name; ?>" />
    <br style="margin:0;padding:0; height:1px; clear: left;" />
<?php
}