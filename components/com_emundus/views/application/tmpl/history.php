<?php
use Joomla\CMS\Language\Text;

?>

<div class="flex items-center border-b-1 border-neutral-300">
	<?php foreach ($this->tabs as $key => $tab) : ?>
		<div class="py-4 px-5 border-b <?php if($key == 0) : ?>border-main-500<?php else : ?>border-neutral-400<?php endif; ?> cursor-pointer"
		     id="tab_<?php echo $tab; ?>"
		     onclick="selectTab('<?php echo $tab; ?>')">
			<span class="em-font-size-14"><?php echo Text::_('COM_EMUNDUS_APPLICATION_HISTORY_TAB_'.strtoupper($tab)); ?></span>
		</div>
	<?php endforeach; ?>
</div>

<div id="history">
</div>

<div id="application" class="mt-6">
</div>

<div id="attachments" class="mt-6">
</div>

<div id="comments" class="mt-6">
</div>
<script>
    //domready
    document.addEventListener("DOMContentLoaded", function (event) {
        displayHistory();
    });

    function emptyElements(elements = ['application', 'attachments', 'history', 'comments']) {
        elements.forEach((elementId) => {
            const foundElement = document.getElementById(elementId);

            if (foundElement) {
                foundElement.innerHTML = '';
            }
        });
    }

    function displayHistory() {
        toggleLoader();
        fetch('/index.php?option=com_emundus&view=application&layout=logs&format=raw&fnum=<?php echo $this->fnum ?>&ccid=<?php echo $this->ccid ?>', {
            method: 'get',
        }).then((response) => {
            if (response.ok) {
                return response.text();
            }
        }).then((res) => {
            emptyElements();
            document.getElementById('history').innerHTML = res;

            toggleLoader();
        });
    }

    function displayApplication() {
        toggleLoader();
        fetch('/index.php?option=com_emundus&view=application&layout=form&format=raw&fnum=<?php echo $this->fnum ?>&ccid=<?php echo $this->ccid ?>', {
            method: 'get',
        }).then((response) => {
            if (response.ok) {
                return response.text();
            }
        }).then((res) => {
            emptyElements();
            document.getElementById('application').innerHTML = res;
            toggleLoader();
        });
    }

    function displayAttachments() {
        toggleLoader();
        fetch('/index.php?option=com_emundus&view=application&layout=attachment&format=raw&fnum=<?php echo $this->fnum ?>&ccid=<?php echo $this->ccid ?>', {
            method: 'get',
        }).then((response) => {
            if (response.ok) {
                return response.text();
            }
        }).then((res) => {
            emptyElements();

            // Use jQuery is required to load javascript present in the html
            $('#attachments').append(res);
            toggleLoader();
        });
    }

    function displayComments() {
        toggleLoader();

        fetch('/index.php?option=com_emundus&view=application&layout=comment&format=raw&fnum=<?php echo $this->fnum ?>&ccid=<?php echo $this->ccid ?>', {
            method: 'get',
        }).then((response) => {
            if (response.ok) {
                return response.text();
            }
        }).then((res) => {
            emptyElements();
            document.getElementById('comments').innerHTML = res;

            // Use jQuery is required to load javascript present in the html
            $('#comments').append(res);
            toggleLoader();
        });
    }

    function selectTab(tab) {
        let selected_tab = document.getElementById('tab_' + tab);
        let old_tab = document.getElementsByClassName('border-main-500');

        if(selected_tab.classList.contains('border-main-500')) {
            return;
        }

        if (old_tab.length > 0) {
            old_tab[0].classList.add('border-neutral-400');
            old_tab[0].classList.remove('border-main-500');
        }

        if(selected_tab) {
            selected_tab.classList.remove('border-neutral-400');
            selected_tab.classList.add('border-main-500');
        }

        switch(tab) {
            case 'forms':
                displayApplication();
                break;
            case 'history':
                displayHistory();
                break;
            case 'attachments':
                displayAttachments();
                break;
            case 'comments':
                displayComments();
                break;
        }
    }

    function toggleLoader() {
        let loader = document.querySelector('.em-page-loader');

        if(loader.classList.contains('hidden') || loader.style.display === 'none') {
            loader.classList.remove('hidden');
            loader.style.display = 'block';
        } else {
            loader.style.removeProperty('display');
            loader.classList.add('hidden');
        }
    }
</script>