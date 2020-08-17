UPDATE jos_emundus_setup_emails t SET t.message = '<p>Dear Colleague,</p>
          <p><br />You have been chosen by [NAME] ([EMAIL]) to be his referee to complete her/his application to our program. <br />Please <a href="[UPLOAD_URL]">upload the recommendation letter</a> by using the following link.</p>
          <p>Notice that the application of the student will have an "incomplete status" unless you <a href="[UPLOAD_URL]">add the letter</a>.</p>
          <p>In case you would not like to add a recommendation letter for this student, please let her/him know.</p>
          <p> </p>
          <p>Thank you for your collaboration.</p>
          <p>Sincerely,<br /><br /></p>
          <p>P.S.: please note that you may only <a href="[UPLOAD_URL]">upload one document</a> for each student.</p>
          <p>Click <a href="[UPLOAD_URL]">HERE </a>to upload reference letter.</p>
          <p>If link does not work, please copy and paste that hyperlink in your browser: [UPLOAD_URL]</p>
          <hr />
          <p>Cher Collègue,</p>
          <p><br />Vous avez été sélectionné par [NAME] ([EMAIL]) pour être son référent afin qu''il/elle puisse finaliser leur candidature. <br />Veuillez <a href="[UPLOAD_URL]">télécharger votre lettre de recommandation</a> en suivant le lien.</p>
          <p>Le dossier de candidature restera en statut "incomplet" tant que vous <a href="[UPLOAD_URL]">n''envoyez pas la lettre</a>.</p>
          <p> </p>
          <p>Merci de votre collaboration.</p>
          <p>Cordialement,<br /><br /></p>
          <p>P.S.: Vous ne pouvez envoyer qu''un seul document.</p>
          <p>Si le lien ne fonctionne pas, veuillez copier et coller le lien suivant dans votre navigateur : [UPLOAD_URL]</p>' WHERE lbl = 'referent_letter';