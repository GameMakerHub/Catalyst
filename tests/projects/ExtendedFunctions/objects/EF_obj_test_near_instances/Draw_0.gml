x = mouse_x;
y = mouse_y;

nearest = get_nearest_instances(ExtendedFunctions_obj_near_instance, id, nearnum);

while (!ds_stack_empty(nearest)) {
    inst = ds_stack_pop(nearest);
    draw_set_color(c_red);
    draw_ellipse(inst.x-8, inst.y-8, inst.x+8, inst.y+8, true);
    draw_ellipse(inst.x-10, inst.y-10, inst.x+10, inst.y+10, true);
}

ds_stack_destroy(nearest);

draw_set_color(c_black);
draw_text(10,10,string_hash_to_newline("FPS:" + string(fps) + " - REAL: " + string(fps_real)));

