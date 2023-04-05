/**
  * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
  * @license     GNU General Public License version 2 or later; see LICENSE.txt
  */
const helpIndex = document.getElementById('help-index');

if (helpIndex) {
  [].slice.call(helpIndex.querySelectorAll('a')).map(element => element.addEventListener('click', () => {
    window.scroll(0, 0);
  }));
}
