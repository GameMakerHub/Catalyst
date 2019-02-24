/// @description  Test suite init
test_suite_init();

///Test string_explode and array_implode
test = string_explode(";", "this;ís; a ; 1234 test;;;");
test_assert_same("this", test[0]);
test_assert_same("ís", test[1]); //Mind the í and not i.
test_assert_same(" 1234 test", test[3]);

test2 = string_explode("--", "this-is--a--1234--test---withDashes--");
test_assert_same("this-is", test2[0]); //#4
test_assert_same("a", test2[1]);
test_assert_same("-withDashes", test2[4]);

//Check count
test3 = string_explode(" ", "a b c d e");
test_assert_same(5, array_length_1d(test3));
test_assert_same("a", test3[0]);
test_assert_same("b", test3[1]);
test_assert_same("c", test3[2]);
test_assert_same("d", test3[3]);
test_assert_same("e", test3[4]);

ar3[0] = "Hi";
ar3[1] = 123;
ar3[2] = "just testing";
testarr = array_implode(";", ar3);
test_assert_same("Hi;123;just testing", testarr);
testarr = array_implode("--++--", ar3);
test_assert_same("Hi--++--123--++--just testing", testarr);

ar3[0] = "Hi";
ar3[1] = 123;
ar3[5] = "test";
ar3[20] = "just testing";

//GM Does not support missing index in an array. Fills them with "0".
test_assert_same("Hi;123;just testing;0;0;test;0;0;0;0;0;0;0;0;0;0;0;0;0;0;just testing", array_implode(";", ar3))

test = string_explode(";", "No seperator");
test_assert_same("No seperator", test[0]); //#10

///Test string_reverse
test_assert_same(".gnitseT", string_reverse("Testing."));

///Test string_mask
test_assert_same("••••••", string_mask("•", "secret"));
test_assert_same("************", string_mask("**", "secret"));

///Test url_encode and url_decode
test_assert_same(3847, hextodec(dectohex(3847)));
test_assert_same(255, hextodec("FF"));
test_assert_same(0, hextodec("00"));
test_assert_same("0", dectohex(0)); //#17
test_assert_same("FF", dectohex(255));
test_assert_same("BADA55", dectohex(12245589));
test_assert_same("%20%20", url_encode("  ")); //#20
test_assert_same("a%20%20", url_encode("a  "));
test_assert_same("a%20%20a", url_encode("a  a"));
test_assert_same("a%20b%20a", url_encode("a b a"));

test_assert_same("  ", url_decode("%20%20")); //#24
test_assert_same(" a ", url_decode("%20a%20"));
test_assert_same("a  ", url_decode("a%20%20")); //#26
test_assert_same("  a", url_decode("%20%20a"));
test_assert_same("a b a", url_decode("a%20b%20a"));

test_assert_same("%20%7B%7D%5B%5D%7C%5C%5E%60%22%23%25%3C%3E%3B%2F%40%24%3D%3A%3F%26", url_encode(" {}[]|\\^`\"#%<>;/@$=:?&"));
test_assert_same(" {}[]|\\^`\"#%<>;/@$=:?&", url_decode("%20%7B%7D%5B%5D%7C%5C%5E%60%22%23%25%3C%3E%3B%2F%40%24%3D%3A%3F%26"));

test_assert_same("!%40%23%24%25%5E%26*()~%60129087%3F%3Fasdfjkh", url_encode("!@#$%^&*()~`129087??asdfjkh"));
test_assert_same("!@#$%^&*()~`129087??asdfjkh", url_decode("!%40%23%24%25%5E%26*()~%60129087%3F%3Fasdfjkh"));

urltestultimate = "(**(&hil&*O\" \"\"äë\"4ïlk@)?♣C{◙] ";
test_assert_same(urltestultimate, url_decode(url_encode(urltestultimate)));

///Test string_ends_with and string_starts_with
test_assert_same(true, string_ends_with("Hiya test", "test")); //34
test_assert_same(true, string_ends_with("Hiya t", "t"));
test_assert_same(true, string_ends_with("Hiya test", "Hiya test"));
test_assert_same(true, string_ends_with("Hiya test", " test"));
test_assert_same(true, string_ends_with("Hiya test", ""));

test_assert_same(false, string_ends_with("Hiya test", "a"));
test_assert_same(false, string_ends_with("Hiya test", "hiya test")); //40

test_assert_same(false, string_starts_with("Hiya test", "hiya")); //41
test_assert_same(false, string_starts_with("Hiya test", "h")); //42
test_assert_same(true, string_starts_with("Hiya test", "")); //43
test_assert_same(true, string_starts_with("Hiya test", "H")); //44
test_assert_same(true, string_starts_with("Hiya test", "Hiya")); //45
test_assert_same(true, string_starts_with("Hiya test", "Hiya test")); //#46

test_assert_same(true, string_ends_with("", ""));
test_assert_same(false, string_ends_with("", "a"));
test_assert_same(true, string_ends_with("A", ""));
test_assert_same(false, string_ends_with("testing", "longtesting")); //50

test_assert_same(true, string_starts_with("", ""));
test_assert_same(false, string_starts_with("", "a"));
test_assert_same(true, string_starts_with("A", ""));
test_assert_same(false, string_starts_with("testing", "longtesting"));

test_assert_same(true, string_ends_with("A", "A"));
test_assert_same(true, string_ends_with("a", "a"));
test_assert_same(false, string_ends_with("A", "a"));

test_assert_same(true, string_starts_with("a", "a"));
test_assert_same(true, string_starts_with("A", "A"));
test_assert_same(false, string_starts_with("A", "a"));

///test round_whole
test_assert_same(10, round_whole(10, 10));
test_assert_same(20, round_whole(20, 10));
test_assert_same(1230, round_whole(1234, 10));
test_assert_same(1240, round_whole(1235, 10));
test_assert_same(1230, round_whole(1234.99, 10));
test_assert_same(0, round_whole(.9, 10));
test_assert_same(92891, round_whole(92891.1, 1));

///test string_append and string_prepend
test_assert_same(string_prepend(1234, 0, 10), "0000001234" );
test_assert_same(string_prepend(1234, 0, 5), "01234" );
test_assert_same(string_prepend(1234567, 0, 5), "1234567" );
test_assert_same(string_prepend("te", "*", 5), "***te" );
test_assert_same(string_prepend("testing", "*", 3), "testing" );
test_assert_same(string_prepend("testing", "*", 0), "testing" );
test_assert_same(string_prepend("test", "*****", 10), "**********" ); //Too long
test_assert_same(string_prepend("test", "*", 10), "******test" );

test_assert_same(string_append(1234, 0, 10), "1234000000" );
test_assert_same(string_append(1234, 0, 5), "12340" );
test_assert_same(string_append(1234567, 0, 5), "1234567" );
test_assert_same(string_append("te", "*", 5), "te***" );
test_assert_same(string_append("testing", "*", 3), "testing" );
test_assert_same(string_append("testing", "*", 0), "testing" );
test_assert_same(string_append(123, "*-", 10), "123*-*-*-*" );

// Test netevent_to_string
test_assert_same("connect", netevent_to_string(network_type_connect));
test_assert_same("non-blocking connect", netevent_to_string(network_type_non_blocking_connect));
test_assert_same("disconnect", netevent_to_string(network_type_disconnect));
test_assert_same("data", netevent_to_string(network_type_data));
test_assert_same("unknown", netevent_to_string("stuff"));

// Test bit_from_byte
for (var i = 0; i < 8; i++) {
	test_assert_same(0, bit_from_byte(0, i)); // 00000000
}

test_assert_same(1, bit_from_byte(1, 0)); // 00000001
for (var i = 1; i < 8; i++) {
	test_assert_same(0, bit_from_byte(1, i)); // 00000001
}

for (var i = 0; i < 8; i++) {
	test_assert_same(1, bit_from_byte(255, i)); // 11111111
}

for (var i = 0; i < 16; i++) {
	test_assert_same(1, bit_from_byte(65535, i)); //1111111111111111
}

for (var i = 0; i < 16; i++) {
	test_assert_same(i%2, bit_from_byte(43690, i)); //1010101010101010
}

// Test string trim
test_assert_same("trimmed",string_trim("    trimmed    "));
test_assert_same("trimmed     with   spaces     inbetween",string_trim("    trimmed     with   spaces     inbetween    "));
test_assert_same(".     notrim right",string_trim(".     notrim right     "));
test_assert_same("notrim left     .",string_trim("      notrim left     ."));
test_assert_same(". .",string_trim("      . .       "));

//@todo make tests and functions for arrays
//@todo make tests for header from string

///End testsuite
test_suite_end();

