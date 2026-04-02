window.onscroll = function() {scrollFunction()};

function scrollFunction() {
if (this.oldScroll > this.scrollY) {
    document.querySelector("header").style.transform = "translateY(0px)"; 
} else {
    document.querySelector("header").style.transform = "translateY(-100px)";
}
this.oldScroll = this.scrollY; 
}

function CloseCreatePost(){
    document.getElementById("create-post-popout").style.display="none";
}
function OpenCreatePost(){
    document.getElementById("create-post-popout").style.display="flex";
}