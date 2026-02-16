import{Player,QuizGame} from"./quizGame.js";
let player;
let game;

//Starts the game, making a player, starting the game, and making the relevant parts of the page interactable.
function startGame(){
    player = new Player("guest");
    game = new QuizGame(player);
    document.getElementById("score").textContent = "Score: 0";
    document.getElementById("answer").disabled = false;
    document.getElementById("submitBtn").disabled = false;
    showQuestion();
    game.startTimer(
    function (time) {
    document.getElementById("timer").textContent = "Time: " + time + "s";
    },
    function () {
    endGame();
    }
    );
}
// Displays current question.
function showQuestion() {
document.getElementById("question").textContent = game.getQuestion();
}

//Submission logic. Answer is retrieved, trimmed, checked to see if it has any value, checked to see if it's correct. If correct, proceeds and ups the score, if wrong, ends the game instantly.
function submitAnswer() {
const answerBox = document.getElementById("answer");
const userAnswer = answerBox.value.trim();
if (userAnswer === "") return;
const correct = game.checkAnswer(userAnswer);
if (correct) {
document.getElementById("score").textContent = "Score: " + game.player.score;
game.nextQuestion();
showQuestion();
} else {
endGame();
}
answerBox.value = "";
}
// End game and show final score
function endGame() {
game.stopTimer();
// display the final score by using div or paragraph element.
const message ="Game Over! Final Score: " + game.player.score;
document.getElementById("scoreMessage").textContent=message;
document.getElementById("answer").disabled = true;
document.getElementById("submitBtn").disabled = true;
}
window.startGame=startGame;
window.submitAnswer=submitAnswer;