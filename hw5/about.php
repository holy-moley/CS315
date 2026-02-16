<!--Not much here yet.-->
<?php require_once("template.php");
template_header("Welcome!");?>

<h1>About Me</h1>
<p>Hi! I'm Mungo. I sell magical items that I have way too many copies of due to a duplication glitch.</p>
<button id="wigglebutton">Press this to give me even more duplicates!</button>
<script>

        const btn = document.getElementById("wigglebutton");

        btn.addEventListener("click", () => {
            // Add animation class
            btn.classList.add("wiggle");

            // Remove class after animation ends,
            // so it can be triggered again later
            btn.addEventListener("animationend", () => {
                btn.classList.remove("wiggle");
            }, { once: true });
        });
</script>
<p>That button doesn't actually do anything, but it's still fun, right? It'll make you buy more things? Please buy my things. Please.</p>
<?php template_footer(); ?>
