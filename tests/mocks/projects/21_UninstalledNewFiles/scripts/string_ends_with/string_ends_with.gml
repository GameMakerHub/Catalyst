/// @description string_ends_with(haystack, needle);
/// @function string_ends_with
/// @param haystack
/// @param  needle
/**
 * Check if string ends with string
 * @param string argument0 haystack
 * @param string argument1 needle
 * @return boolean
 */
var sl0 = string_length(argument0);
var sl1 = string_length(argument1);

if (sl0+sl1 == 0) { return true; }
if (argument0 == argument1) { return true; }
if (sl1 == 0) { return true; }
if (sl0 == 0) { return false; }
if (sl1 > sl0) { return false; } //Quite sure.

return (string_copy(argument0, sl0-sl1+1, sl1) == argument1);