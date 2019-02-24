/// @description get_furthest_instances(object, closestto, maxnum); //Returns the MAXNUM furthest instances as a ds_stack.
/// @function get_furthest_instances
/// @param object
/// @param  closestto
/// @param  maxnum
//REMEMBER to destroy the stack after using it!
//THIS FUNCTION IS QUITE RESOURCE INTENSIVE WITH A LOT OF INSTANCES.
//Example:

/*
//Find the 8 furthest instances and draw a sprite there
nearest = get_furthest_instances(obj_find_this, id, 8);
while (!ds_stack_empty(nearest)) {
    inst = ds_stack_pop(nearest);
    draw_sprite(spr_this_one_is_far, 0, inst.x, inst.y);
}
ds_stack_destroy(nearest);
*/

global.__extfnc_closestto = argument1;
var stack = ds_stack_create();
var totalInstances = instance_number(argument0);
if (totalInstances <= argument2) {
    with (argument0) {
        ds_stack_push(stack, id);
    }
    return stack;
}

//Put all lights in a list
var instanceList;
var distanceList;
var iii=0;
with (argument0) {
    instanceList[iii] = id;
    distanceList[iii] = point_distance(id.x, id.y, global.__extfnc_closestto.x, global.__extfnc_closestto.y);
    iii++;
}
for (var p = 0; p < totalInstances -1; p +=1) {
  var swapped = false;
  for (var i = 0; i < totalInstances -1; i +=1) {
      if (distanceList[i] < distanceList[i+1]) {
          swapped = true;
          var temp = instanceList[i];
          instanceList[i] = instanceList[i+1];
          instanceList[i+1] = temp;

          var temp2 = distanceList[i];
          distanceList[i] = distanceList[i+1];
          distanceList[i+1] = temp2;
      }
  }
  if (!swapped) { break; }
}

for (var ii = 0; ii < argument2; ii++) {
    ds_stack_push(stack, instanceList[ii]);
}
return stack;