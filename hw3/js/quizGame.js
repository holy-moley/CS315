import{questions} from "./quizData.js";
export function Player(n){
    this.name=n;
    this.score=0;
}
export class QuizGame{
    constructor(p){
        this.player=p;
        this.index=0;
        this.timeLeft=60;
        this.timerId=null;
    }
    //Sets a timer that increments down every second.
    //Calls end game function when time is over.
    startTimer(updateTimer,endGame){
        this.timerId=setInterval(()=>{
            this.timeLeft --;
            updateTimer(this.timeLeft);
            if (this.timeLeft<=0){
                clearInterval(this.timerId);
                endGame();
            }
        },1000);
    }
    getQuestion(){
        return questions[this.index].q;
    }
    checkAnswer(ans){
        const corr=questions[this.index].a.toLowerCase();
        if(ans.toLowerCase()===corr){
            this.player.score ++;
            return true;
        }
        //No else statement because it will return true if the answer is right.
        return false;
    }
    //Increments the question array, restarts if all have been answered.
    nextQuestion(){
        this.index++;
        if(this.index>=questions.length) this.index=0;
    }
    stopTimer(){
        clearInterval(this.timerId);
    }
}