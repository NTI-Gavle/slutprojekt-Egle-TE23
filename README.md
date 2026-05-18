Filer
CSS - style!
Style.css 
Innehåller all css för hela hemsidan. Det finns lite inline css på vissa objekt då det ansågs inte vara användbart med en klass men förutom det innehåller det här all css. Har variabler till färgerna för dark och light mode.

JS - javaskript filer
app.js
Innehåller generella JS funktioner som används på flera sidor.
chat.js
Innehåller all JS för chat sidan, hanterar ajax samt skapandet av nya chats och scrollar automatiskt ner till senaste meddelandet när sidan laddas.
createpost.js
Innehåller JS för ny post rutan. Varnar om man lägger till fler än 4 bilder, lägger till previews av bilderna och hanterar character countern för ny post.
feed.js
Innehåller JS för inlägg, hanterar Ajax funktioner för likes, dislikes och stjärnmarkeringar.
header.js
Har all JS för headern men i princip bara för sökfältet i headern. Visar och gömmer sök drop-down och hanterar raderandet av söktermer.  
lightbox.js
JS funktioner för förstorade visningen av bilder (lightbox). Gör det också möjligt att gå mellan bilderna med pilar och stänga bilden med escape.
settings.js
All JS för Settings sidan. Sätter cookies för bg animation och dark mode.
stars.js 
JS för animerade bakgrunden (lägger också till dark mode class på body).

Public - sidor som användare har direkt tillgång till
admin.php
Sida bara tillgänglig till användare med admin roll. Tillåter admins att hantera användare (banna, ge admin och radera) samt radera inlägg och kommentarer.
chat.php 
Sida där man kan skriva med personer man följer. Man kan starta nya konversationer, skriva meddelanden och radera konversationer.
contact.php
Kontakt sida där användare kan skicka mejl med ett meddelande.
GDPR.php
Privacy policy sida där det förklaras saker om hur data används och liknande, för att vara extra säker att jag följer gdpr.
get-search-data.php
Används av header.js för att visa och spara senaste och populära sökningar.
index.php
Huvudsidan med post feed. Det finns fyra olika feeds som visar inlägg i olika ordningar. Även inte inloggade användare kan se denna sida.
profile.php
Profilsidan, kan se sin egna profil där det finns en knapp som tar en till settings.php för att redigera profilen. Kan också gå in på andra användares profiler, settings knappen är istället en följa knapp. För alla användare kan man se deras inlägg, stjärnmarkeringar, kommentarer och media inlägg.
post.php
Sida för att visa enskilda posts, kan gå in på denna sida genom att trycka på headern av en post. Det är också denna sida som delas när man skickar en länk via share funktionen.
stars.php
Samma typ av feed som index.php fast det är bara inlägg som du har stjärnmarkerat.
settings.php
Inställningar för konto/profil och generella inställningar för färgtema och bakgrund animation. Har också en logout knapp och en about flik där det står kort om sidan.
search.php
Search sida som användaren tas till när de söker genom headern. Kan söka på användare och posts.
login.php
Login sidan, kan navigera till glömt lösenord och sign up.
logout.php
Startar om session för att logga ut användaren.
signup.php
Signup sidan för att skapa ett nytt konto.
reset-password.php
Sida för att påbörja lösenord återställning.
reset-password-confirm.php
Där användaren skapar ett nytt lösenord, länken till denna sida skickas till dem genom mejl med en token som går ut efter 30 min.
new-password.php
Sista sidan för återställningen av lösenordet, visar att lösenordet har blivit bytt om det fungerade.

Includes - Återanvändbara filer
createpost.php
Popout för skapandet av en ny post, används på alla sidor då det finns en site nav och öppnas genom den.
feednav.php
Nav för de olika feeds som finns på index sidan, används också på flera andra sidor för att navigera tillbaka till index.php.
sitenav.php
Nav för alla olika sidor, de flesta knapparna leder till login sidan om man inte är inloggad.
header.php
Headern som används på alla sidor, börjar också html filen. Headern har en logo som leder till index.php, ett sökfält och en login knapp eller profilbild beroende på om man är inloggad eller inte.
footer.php
Footern som används på nästan alla sidor, avslutar också filen.
functions.php
Generella php funktioner som används på flera sidor. Funktioner: skapa inlägg med php & kolla om användaren är admin

Private - hjälpfiler för de olika publik filerna och ajax saker
admin-actions.php
PHPMailer
Bibliotek som används för att skicka mejl, används för lösenordsåterställning och kontaktformuläret.
admin-actions.php
Hanterar admin åtgärder som att banna, ge admin-roll och radera användare, inlägg och kommentarer.
create-comment.php
Hanterar skapandet av nya kommentarer på inlägg, sparar kommentaren i databasen.
create-post.php
Hanterar skapandet av nya inlägg, sparar text och upp till 4 bilder i databasen.
db.php
Innehåller databasfrågor och hjälpfunktioner för att hämta och hantera data.
dbconnection.php
Hanterar anslutningen till databasen, används på alla sidor som behöver tillgång till data.
delete-account.php
Hanterar radering av användarkonton och all tillhörande data från databasen.
delete-post.php
Hanterar radering av inlägg och tillhörande media och kommentarer från databasen.
get-comments.php
Hämtar kommentarer för ett inlägg via Ajax och returnerar dem för visning.
loginlogic.php
Hanterar inloggningslogiken, validerar användaruppgifter och sätter session variabler.
poll-messages.php
Används av chat.js för att hämta nya meddelanden via Ajax polling.
reset-password-logic.php
Hanterar lösenordsåterställning, validerar token och uppdaterar lösenordet i databasen.
score.php
Hanterar likes och dislikes på inlägg via Ajax, uppdaterar databasen och returnerar nya räknare.
send-message.php
Hanterar skickandet av chattmeddelanden, sparar meddelandet i databasen.
send-post-message.php
Hanterar delning av inlägg som meddelanden, skickar en post-länk till en annan användare.
sendmail.php
Hjälpfil för att skicka mejl via PHPMailer, används av kontaktformuläret och lösenordsåterställningen.
settings_update.php
Hanterar uppdatering av profilinformation, sparar ändringar som namn, bio och profilbild i databasen.
signuplogic.php
Hanterar registreringslogiken, validerar indata och skapar nya användarkonton i databasen.
starmark.php
Hanterar stjärnmarkeringar av inlägg via Ajax, lägger till eller tar bort markeringen i databasen.
track-view.php
Spårar visningar av inlägg via Ajax, uppdaterar visningsräknaren i databasen.

