instnumber = get_integer("How many instances?", 30);
nearnum = get_integer("How many to fetch near?", 8);
repeat (instnumber) {
       instance_create_depth(random(room_width), random(room_height), 0, ExtendedFunctions_obj_near_instance);
}

