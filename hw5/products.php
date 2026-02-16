<?php require_once("template.php");
template_header("Welcome!"); ?>

<h2>Products</h2>

<?php
require "db.php";

$sql = "SELECT * FROM products";
$result = $pdo->query($sql);
//Not complicated, but I'm still kinda proud of it. 
// Loops through the entire product database and displays them in the card format, which is defined in the main CSS file. 
echo"<section id='product-grid'>";
foreach ($result as $i) {
    echo "<div class = 'product {$i['category']}'>";

    echo "
    <picture>
        <!-- Large screens first -->
        <source srcset='assets/{$i['imageName']}Max.jpg' media='(min-width: 1024px)'>
        <source srcset='assets/{$i['imageName']}Med.jpg' media='(min-width: 768px)'>
        <!-- Small/mobile default -->
        <img src='assets/{$i['imageName']}Min.jpg' alt='{$i['productName']}'>
    </picture>
    ";


    echo "<h3>{$i['productName']}</h3> <br> <p> $ {$i['price']}<br> {$i['description']} </p>";
    echo "<a class='details-btn' href='productdetails.php?id={$i['PID']}'>Details</a>";
    echo "</div>";
}
echo"</section>";
?>


<?php template_footer(); ?>