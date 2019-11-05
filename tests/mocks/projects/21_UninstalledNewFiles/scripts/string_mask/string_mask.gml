/// @description string_mask(string);
/// @function string_mask
/// @param string
/**
 * Hide a string based on its length
 * @param string argument0 Character to use (e.g. "*" or "•")
 * @param string argument1 The string to mask
 * @return string
 */
return string_repeat(argument0, string_length(argument1));