<?php
//Separate file purely because I named cart_functions way earlier, and do not want to make it inaccurate by including non-cart functions,
//or rename it and have to go across each page and change it there as well so it actually imports correctly. 
//I mostly only leave this kind of message in my code as proof that I was actually involved in the process and didn't just make ChatGPT spit out the entire thing.
//This would've been way easier if I did that.
function testInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
