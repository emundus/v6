/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(document => {

  const debugSectionTogglerSelector = '.dbg-header';
  const toggleTargetAttribute = 'data-debug-toggle';
  /**
   * Toggle an element by id
   * @param id
   */

  const toggle = id => {
    document.getElementById(id).classList.toggle('hidden');
  };
  /**
   * Register events
   */


  const registerEvents = () => {
    const sectionTogglers = [].slice.call(document.querySelectorAll(debugSectionTogglerSelector));
    sectionTogglers.forEach(toggler => {
      toggler.addEventListener('click', event => {
        event.preventDefault();
        toggle(toggler.getAttribute(toggleTargetAttribute));
      });
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    registerEvents();
  });
})(document);
