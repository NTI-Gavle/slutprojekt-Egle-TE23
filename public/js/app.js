window.onscroll = function() {scrollFunction()};

function scrollFunction() {
if (this.oldScroll > this.scrollY) {
    document.querySelector("header").style.transform = "translateY(0px)"; 
} else {
    document.querySelector("header").style.transform = "translateY(-100px)";
}
this.oldScroll = this.scrollY; 
}

//new post open and close
function CloseCreatePost(){
    document.getElementById("create-post-popout").style.display="none";
}
function OpenCreatePost(){
    document.getElementById("create-post-popout").style.display="flex";
}

//text area
const textArea = document.getElementById('create-post-text');

textArea.style.height = textArea.scrollHeight + "px";
textArea.style.overflowY = "hidden";

textArea.addEventListener("input", function () {
    let characterPos =this.style.height.indexOf("p")
    console.log(characterPos);
    let areaHeight=200;
    if(characterPos>-1){
        areaHeight= this.style.height.slice(0,characterPos)
    }
    console.log(areaHeight);
    if(areaHeight >= 200)
    {
        textArea.style.overflowY = "scroll";
    }
    else{
        this.style.height = "auto";
        this.style.height = this.scrollHeight + "px";
        textArea.style.overflowY = "hidden";
    }
       
});