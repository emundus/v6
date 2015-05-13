/**
 * @package         SCLogin
 * @copyright (c)   2009-2014 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v4.3.0
 * @build-date      2015/03/19
 */
var sclogin =
{
    base: '',
    token: '',
    loginForms: new Array(),
    otp: {
        check: function (form)
        {
            var formId = '#' + form.target.id;
            if (jfbcJQuery(formId).attr('otpdone'))
                return true;

            var modId = jfbcJQuery(formId + ' input[name=mod_id]').val();
            sclogin.loginForms[modId] = jfbcJQuery('#sclogin-' + modId).clone();
            var username = jfbcJQuery(formId + ' input[name=username]').val();
            var password = jfbcJQuery(formId + ' input[name=password]').val();
            var url = 'u=' + username /*+ '&p=' + password */ + '&' + sclogin.token + '=1&mod_id=' + modId;
            jfbcJQuery.ajax({url: sclogin.base + 'modules/mod_sclogin/ajax/otpcheck.php', data: url, type: "POST", dataType: 'text json', success: function (ret)
            {
                if (ret.needsOtp == 'true')
                {
                    var otpForm = jfbcJQuery(ret.form);

                    // Copy all our hidden elements from the previous form
                    var inputs = jfbcJQuery(formId + ' :input[type=hidden]');
                    inputs.each(function (key, value)
                    {
                        jfbcJQuery(otpForm).find("form").append(value);
                    });
                    // Copy the username/password fields
                    var usernameField = jfbcJQuery(formId + ' :input[name=username]')[0].clone();
                    usernameField.type = 'hidden';
                    usernameField.val = username;
                    jfbcJQuery(otpForm).find("form").append(usernameField.outerHTML);

                    var passwordField = jfbcJQuery(formId + ' :input[name=password]')[0].clone();
                    passwordField.type = 'hidden';
                    passwordField.val = password;
                    jfbcJQuery(otpForm).find("form").append(passwordField.outerHTML);

                    jfbcJQuery('#sclogin-' + modId).fadeOut('1000', function ()
                    {
                        jfbcJQuery('#sclogin-' + modId).html(otpForm).fadeIn('1000', function ()
                        {
                            jfbcJQuery('#sclogin-input-secretkey').focus();
                        });
                    });
                }
                else
                {
                    jfbcJQuery(formId).attr('otpdone', true);
                    jfbcJQuery(formId).submit();
                }
            }
            });
            return false;
        },
        // Cancel button clicked on form. Restore the form and close the modal
        reset: function (id)
        {
            if (sclogin.loginForms[id] != undefined)
            {
                jfbcJQuery('#sclogin-' + id).html(sclogin.loginForms[id][0].outerHTML);
                sclogin.init();
            }
        }
    },
    init: function ()
    {
        if (typeof jfbcJQuery == "undefined")
            jfbcJQuery = jQuery;

        jfbcJQuery('form[id^="sclogin-form"]').submit(function (e)
        {
            return sclogin.otp.check(e);
        });
        jfbcJQuery('#login-modal').on('hidden', function ()
        {
            var modId = jfbcJQuery('#login-modal input[name=mod_id]').val();
            sclogin.otp.reset(modId);
        });
    }

}