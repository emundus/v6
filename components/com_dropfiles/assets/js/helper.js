/** 
 * Dropfiles
 * 
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 * @package Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 * 
 */

bytesToSize = function(bytes) {
    var sizes = [Joomla.JText._('COM_DROPFILES_FIELD_FILE_BYTE','b'),Joomla.JText._('COM_DROPFILES_FIELD_FILE_KILOBYTE','kb'),Joomla.JText._('COM_DROPFILES_FIELD_FILE_MEGABYTE','mb'),Joomla.JText._('COM_DROPFILES_FIELD_FILE_GIGABYTE','gb'),Joomla.JText._('COM_DROPFILES_FIELD_FILE_TERRABYTE','tb'),Joomla.JText._('COM_DROPFILES_FIELD_FILE_PETABYTE','tb') ];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
};
