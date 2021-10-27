/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

function applyAutoSettings(int, pos)
{
    window.location.href = configure_url + "&task=applyAutoSettings&autosetting=s" + int;
}

function toggleSetting(setting)
{
    window.location.href = configure_url + "&task=toggleSetting&setting=" + setting;
}