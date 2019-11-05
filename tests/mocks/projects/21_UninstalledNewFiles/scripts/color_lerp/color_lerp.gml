///@param color_array
///@param amount
var count, c1, c2, amount;

var color_array = argument0;
var amount = clamp(argument1, 0, 1);

count = array_length_1d(color_array);

c1 = floor((count*amount) mod count);
c2 = (c1+1) mod count;

lerp_amount = abs(amount*count-c1);

return merge_colour(color_array[c1],color_array[c2],lerp_amount);
