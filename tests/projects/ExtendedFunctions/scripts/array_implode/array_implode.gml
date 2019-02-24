/// @description array_implode(glue, array);
/// @function array_implode
/// @param glue
/// @param  array
/**
 * Implode an array based on a key
 * @param string argument0 Seperator - Implode using what character
 * @param array argument1 Array to implode
 * @return string
 *
 * Please note that GM does not support missing array indexes. Check out the test (Number 9)
 */
var output, seperator, array;
seperator = argument0;
array = argument1;

output = "";
for (i=0; i<array_length_1d(array); i++) {
    output = output + string(array[i]) + string(seperator);
}
length = string_length(seperator);
output = string_copy(output,0, string_length(output) - string_length(seperator));
return output;