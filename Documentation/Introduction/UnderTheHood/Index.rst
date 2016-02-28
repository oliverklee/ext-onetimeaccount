

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Under the hood
^^^^^^^^^^^^^^

The user’s e-mail address will be used as the user name (with a number
appended if an account with that user name already exists), for
example “foo@test.com-1”. If the e-mail address has been left blank
(and is not set to be required), the resulting user names will be like
“-1” or “-2”.

In addition, a random 8-character password will be created, consisting
of lowercase and uppercase letters, digits or special characters.

The FE user's record is assigned to a FE user group. Due to the
configuration settings, the FE user might be able to choose between
different FE user groups.

When the FE user is created and logged in, the key “onetimeaccount”
with the value “1” will be written to the FE user session. This can be
used to recognized that the FE user is using a one-time account.

After the automatic login, a redirect to the URL set via the
“redirect\_url” GET will be done. If no such parameter is set, the
redirect will lead to the current page.

For the country field, the default value configured for
static\_info\_tables will be selected by default.
