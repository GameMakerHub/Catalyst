///@param string
///@desc trim a string ("  trim .  . spaces   " -> "trim .  . spaces")
var str = argument0;
var strlen = string_length(str);
while (strlen > 0 && string_char_at(str, 0) == " ") {
	str = string_copy(str, 2, strlen-1);
	strlen -= 1;
}

while (strlen > 0 && string_char_at(str, strlen) == " ") {
	str = string_copy(str, 0, strlen-1);
	strlen -= 1;
}

return str;