///@param string
///@return array|false
///@description turns a header like "Host: www.dukesoft.nl" into an array: ["Host", "www.dukesoft.nl"]
var str = argument0;
var cut = string_pos(":", str);
if (cut == 0) {
	return false;
}

//Remove all newlines
str = string_replace_all(str, "\r", "");
str = string_replace_all(str, "\n", "");

var header = string_trim(string_copy(str, 0, cut-1));
var value = string_trim(string_copy(str, cut+1, string_length(str)-cut));
return [header, value];