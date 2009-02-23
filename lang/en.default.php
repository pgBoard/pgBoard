<?php
define("TITLE_BOARD","messageboard title");
define("TITLE_CHAT","chat");
define("LIST_DOT","&bull;");
define("LIST_FAV","!");
define("SPACE"," ");
define("NON_BREAKING_SPACE","&nbsp;");
define("STICKY_TEXT","<strong>Sticky:</strong>");
define("ARROW_RIGHT","&raquo;"); // if changed must fix core.js to match
define("ARROW_LEFT","&laquo;");  // if changed must fix core.js to match
define("ERROR_MEMBER_NAME","Your name must be between 3 and 15 characters.  Letters, numbers, underscores and hypens are allowed.");
define("ERROR_MEMBER_NAME_INUSE","That name is already in use.");
define("ERROR_MEMBER_EMAIL","Please enter a valid email address.");
define("ERROR_MEMBER_EMAIL_INUSE","This email address is already tied to an account.");
define("ERROR_MEMBER_EMAIL_NOMATCH","Your email addresses did not match.");
define("ERROR_MEMBER_POSTALCODE","Please enter a valid postal code");
define("ERROR_AUTH","Your name and/or password did not match our records.<br/>");
define("ERROR_MUST_LOGIN","You must be logged in to do that.");
define("ERROR_MEMBER_NOTFOUND","Member not found.");
define("ACCOUNT_CREATED","<h3>Your account has been created and a password has been emailed to you.</h3>");
define("SIGNUP_EMAIL_SUBJECT","welcome to the board.");
define("SIGNUP_EMAIL_BODY","Here are your login credentials:<br/><br/>Name: %NAME%<br/>Pass: %PASS%<br/><br/>Enjoy!");
define("NO_RESULTS","<h4>No results found!</h4>");
define("SAY_BUTTON","say it");
define("PREVIEW_BUTTON","preview");
define("HOME_BUTTON","back to it");
define("DONATION_THANKYOU","Thank you for your donation!");
define("DONATION_BUTTON","Paypal button code here.");
define("DONATION_TEXT","Donation details here.");

define("LEGAL","
<h3>Legal Mumbo Jumbo</h3><br/>
By creating this account, you acknowledge that all posts made to this message board reflect solely the views and opinions of the author, and administrators of this board shall not be held liable for any objectionable material posted.
<br/><br/>
You also agree to refrain from posting any material that may violate any applicable laws, including but not limited to material that is obscene, vulgar, slanderous or threatening. Should your posts contain aforementioned questionable material, you acknowledge that board administrators may suspend your account and / or remove any objectionable posts to this board. To enforce these conditions, the IP address of all posts is recorded and available to board administrators.
<br/><br/>
Additionally, board administrators may remove, edit, move or close any thread at any time. You acknowledge that all information posted is stored in a database, and while this information will not be disclosed to any third parties without your consent, board administrators will not be held responsible for any data compromised due to hacking attempts.
<br/><br/>
To obtain an account, you agree to provide the administrators of this board a 1) valid e-mail address, to be used only for confirming your registration details and password, and 2) your current zip / postal code, to be used for purposes of statistical analysis.
");

define("CSS_EVEN","even");
define("CSS_ODD","odd");
define("CSS_ME","me");
define("CSS_READ","read");
define("STYLESHEET","
body
{
  font-family: %FONT%;
  background-color: %BODY%;
  color: %BODY_FONT%;
}
html { font-size: %FONTSIZE%em; }
a { color: %BODY_FONT%; }
.even,div.dp-popup,table.jCalendar td.other-month
{
  background-color: %EVEN%;
  color: %EVEN_FONT%;
}
.even a {  color: %EVEN_FONT%; }
.odd,table.jCalendar td,.box
{
  background-color: %ODD%;
  color: %ODD_FONT%;
}
.odd a,.box a { color: %ODD_FONT%; }
.me,table.jCalendar th,table.jCalendar td.selected
{
  background-color: %ME%;
  color: %ME_FONT%;
}
.me a { color: %ME_FONT%; }
.list:hover, .listhover
{
  background-color: %HOVER%;
  color: %HOVER_FONT%;
}
.list:hover a, .listhover a { color: %HOVER_FONT%; }
.read
{
  border-left: 3px solid %READBAR%;
  border-right: 3px solid %READBAR%;
}
.list .lastpost
{
  font-size: 0.9em;
  line-height: %FONTSIZE-LH-SMALL%em
}
.dropmenu:hover a, .dropmenu:hover .control,
.active a, .active .control
{
  background-color: %EVEN%;
  color: %EVEN_FONT%;
}
.even .dropmenu:hover a, .even .dropmenu:hover .control,
.even .active a, .even .active .control
{
  background-color: %ODD%;
  color: %ODD_FONT%;
}
.top li
{
  background-color: %EVEN%;
  border-top: 3px solid %ODD%;
}
.bottom li
{
  background-color: %ODD%;
  border-bottom: 3px solid %EVEN%;
}
.nav li a { color: %EVEN_FONT%; }
.nav li a:hover, .nav li:hover
{
  background-color: %ME%;
  color: %ME_FONT%;
}
.searchwrap
{
  background-color: %EVEN%;
  border-top: 3px solid %ODD%;
}
.hr { background-color: %EVEN%; }
.view pre,.nophoto
{
  background-color: %EVEN%;
  border: 1px solid %BODY%;
  color: %EVEN_FONT%;
}
.spoiler
{
  cursor: pointer;
  background-color: %ME%;
  color: %ME_FONT%;
  border: 1px outset;
}
.small { font-size: %FONTSIZE-SMALL%em; }
.smaller { font-size: %FONTSIZE-SMALLER%em; }
blockquote { font-style: italic; }
legend { color: %BODY_FONT%; }
.box legend { color: %EVEN_FONT%; }
fieldset { border-top: 1px solid %EVEN%; }
.even fieldset { border-top: 1px solid %ODD%; }
");

define("BBCODE_GUIDE","
<div id='bbcode' class='view' style='font-size:.85em;display:none;'>
<pre>
<h4>TAGS:</h4>
http://www.google.com/ <-- automatic link
[url]http://www.google.com/[/url]
[url=http://www.google.com/]with my own link text[/url]
[img]http://www.google.com/intl/en_ALL/images/logo.gif[/img]
[u]underline[/u]
[strong]bold[/strong]
[b]bold[/b]
[i]italic[/i]
[em]italic[/em]
[strike]strikethrough[/strike]
[code]like pre[/code]
[sub]subscript[/sub]
[sup]superscript[/sup]
[youtube]http://www.youtube.com/watch?v=WAwLYJYsa0A[/youtube]
[quote]quote[/quote]
[spoiler]hidden text here[/spoiler]
</pre>
<div class='clear'></div>
</div>");
