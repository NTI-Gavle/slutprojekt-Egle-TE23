window.onscroll = function() {scrollFunction()};

function scrollFunction() {
if (this.oldScroll > this.scrollY) {
    document.querySelector("header").style.transform = "translateY(0px)"; 
} else {
    document.querySelector("header").style.transform = "translateY(-100px)";
}
this.oldScroll = this.scrollY; 
}