SET @element_id = (SELECT id
FROM jos_fabrik_elements
WHERE name = 'password' and plugin = 'password');

UPDATE jos_fabrik_jsactions
SET code = "var regex = /[#${};&lt;&gt;]/;
var password_value = this.form.formElements.get(&#039;jos_emundus_users___password&#039;).get(&#039;value&#039;);

var password = this.form.formElements.get(&#039;jos_emundus_users___password&#039;);
if (password_value.match(regex) != null) {
  Swal.fire({
    type: &quot;error&quot;,
    title: &#039;Invalid password&#039;,
    text: &#039;The character #${};&lt;&gt; are forbidden&#039;,
    customClass: {
      title: &#039;em-swal-title&#039;,
      cancelButton: &#039;em-swal-cancel-button&#039;,
      confirmButton: &#039;em-swal-confirm-button&#039;,
    }
  });

  password.set(&#039;&#039;);
}"
WHERE id = @element_id;