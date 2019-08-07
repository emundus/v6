/**
 * @package    AkeebaFEF
 * @copyright Copyright (c)2017-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3, or later
 */

if (typeof akeeba === 'undefined')
{
    akeeba = {};
}

if (typeof akeeba.fef === 'undefined')
{
    akeeba.fef = {};
}

akeeba.fef.menuButton = function(selector)
{
    // Use the default selector, if necessary
    if ((typeof selector === 'undefined') || (selector === ''))
    {
        selector = 'a.akeeba-menu-button';
    }

    var menuButtonsList = document.querySelectorAll(selector);

    if (menuButtonsList.length === 0)
    {
        return;
    }

    menuButtonsList.forEach(function(elButton) {
        elButton.addEventListener('click', function(event) {
            var elNav = elButton.parentElement.parentElement.querySelector('nav');

            if (elNav.style.display !== 'flex')
            {
                elNav.style.display = 'flex';

                return;
            }

            elNav.style.display = null;
        });
    });
};
