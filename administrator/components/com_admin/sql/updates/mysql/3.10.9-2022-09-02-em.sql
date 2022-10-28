-- Add js plugin to allow user to reveal his password. Not published by default

SELECT @element_id:=id
FROM jos_fabrik_elements
WHERE plugin = 'password';

INSERT INTO jos_fabrik_jsactions(element_id, action, code, params)
VALUES (@element_id,'load','var passwordInput = document.querySelector(&#039;#jos_emundus_users___password&#039;);
var passwordCheck = document.querySelector(&#039;#jos_emundus_users___password_check&#039;);

var spanShowPassword = document.createElement(&#039;span&#039;);
spanShowPassword.classList.add(&#039;material-icons-outlined&#039;);
spanShowPassword.classList.add(&#039;em-pointer&#039;);
spanShowPassword.innerText = &quot;visibility_off&quot;;
spanShowPassword.style.position = &quot;absolute&quot;;
spanShowPassword.style.top = &quot;21px&quot;;
spanShowPassword.style.right = &quot;10px&quot;;
spanShowPassword.style.opacity = &quot;0.3&quot;;


var spanShowPasswordCheck = document.createElement(&#039;span&#039;);
spanShowPasswordCheck.classList.add(&#039;material-icons-outlined&#039;);
spanShowPasswordCheck.classList.add(&#039;em-pointer&#039;);
spanShowPasswordCheck.innerText = &quot;visibility_off&quot;;
spanShowPasswordCheck.style.position = &quot;absolute&quot;;
spanShowPasswordCheck.style.top = &quot;87px&quot;;
spanShowPasswordCheck.style.right = &quot;10px&quot;;
spanShowPasswordCheck.style.opacity = &quot;0.3&quot;;

passwordInput.parentNode.style.position = &quot;relative&quot;;

passwordInput.parentNode.insertBefore(spanShowPassword, passwordInput.nextSibling);

spanShowPassword.addEventListener(&#039;click&#039;, function () {
  if (spanShowPassword.innerText == &quot;visibility&quot;) {
    spanShowPassword.innerText = &quot;visibility_off&quot;;
    passwordInput.type = &quot;password&quot;;
  } else {
    spanShowPassword.innerText = &quot;visibility&quot;;
    passwordInput.type = &quot;text&quot;;
  }
});

passwordCheck.parentNode.insertBefore(spanShowPasswordCheck, passwordCheck.nextSibling);

spanShowPasswordCheck.addEventListener(&#039;click&#039;, function () {
  if (spanShowPasswordCheck.innerText == &quot;visibility&quot;) {
    spanShowPasswordCheck.innerText = &quot;visibility_off&quot;;
    passwordCheck.type = &quot;password&quot;;
  } else {
    spanShowPasswordCheck.innerText = &quot;visibility&quot;;
    passwordCheck.type = &quot;text&quot;;
  }
});
','{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group640","js_e_condition":"","js_e_value":"","js_published":"0"}')