ShowSettings("account");

function ShowSettings(string){
  document.getElementById('general').style.display="none";
  document.getElementById('account').style.display="none";
  document.getElementById('about').style.display="none";
  const page = document.getElementById(string);
  page.style.display="flex";
}