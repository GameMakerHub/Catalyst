/// @description pulse(max, freq); 0 to max
/// @function pulse
/// @param max
/// @param  freq
return ((1+sin(2  * pi * argument1 * current_time/1000))/2)*argument0;