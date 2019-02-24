///@param eventtype
switch (argument0) {
    case network_type_connect:
		return "connect";
	case network_type_non_blocking_connect:
		return "non-blocking connect";
    case network_type_disconnect:
		return "disconnect";
    case network_type_data:
		return "data";
    default:
		return "unknown";
}
