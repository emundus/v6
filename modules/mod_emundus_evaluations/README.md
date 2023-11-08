# mod_emundus_evaluations

## Project setup

```
yarn install
```

### Compiles and hot-reloads for development

```
yarn run watch
```

### Compiles and minifies for production

```
yarn run build
```

### Lints and fixes files

```
yarn run lint
```

## Evaluation form setup

Update Sweet php plugin to this to work properly

```
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>';
echo '<script
            src="https://code.jquery.com/jquery-3.3.1.slim.js"
            integrity="sha256-fNXJFIlca05BIO2Y5zh1xrShK3ME+/lYZ0j+ChxX2DA="
            crossorigin="anonymous">
      </script>';

echo '<style>
.em-swal-title{
  margin: 8px 8px 32px 8px !important;
  font-family: "Maven Pro", sans-serif;
}
</style>';

die("<script>
      $(document).ready(function () {
        Swal.fire({
          position: 'top',
          type: 'success',
          title: '".JText::_('COM_EMUNDUS_EVALUATION_SAVED')."',
          showConfirmButton: false,
          timer: 2000,
          customClass: {
            title: 'em-swal-title',
          },
          onClose: () => {
            window.parent.document.getElementById('evaluation-modal-close').click();
          }
        })
      });
      </script>");
```

### Customize configuration

See [Configuration Reference](https://cli.vuejs.org/config/).
