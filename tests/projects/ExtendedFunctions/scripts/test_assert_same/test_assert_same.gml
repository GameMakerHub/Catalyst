///@param expected
///@param actual
///@desc Run a unit test (has nothing to do with scripts. Just testing of the scripts).
var expected = argument0;
var given = argument1;
global.testnumber++;

show_debug_message("Testing #" + string(global.testnumber));

if (expected == given) {
   show_debug_message("OK");
   global.testssucceeded++;
   return true;
} else {
  global.testsuccess = false;
  show_debug_message("             FAIL!");
  show_debug_message("Expected (+) Given (=)");
  show_debug_message("+" + string(expected));
  show_debug_message("=" + string(given));
  return false;
}