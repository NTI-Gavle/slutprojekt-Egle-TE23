ShowSettings("settings-general");

function ShowSettings(string){
  document.getElementById('settings-general').style.display="none";
  document.getElementById('settings-account').style.display="none";
  document.getElementById('settings-about').style.display="none";
  const page = document.getElementById(string);
  page.style.display="block";
}