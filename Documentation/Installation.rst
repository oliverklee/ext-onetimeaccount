.. include:: /Includes.rst.txt

.. _installation:

======================
Installation and setup
======================

#. Install the extension and its dependencies.
#. In your site TypoScript template under "Includes, Include static (from
   extensions)", include the static template "Onetimeaccount (onetimeaccount)".
#. If you have not done so yet, create a system folder where the extension
   should store the front-end user it creates.
#. Create one or multiple front-end user group(s) which the users created by
   this extension should have.
#. On the your front-end login page, add a plugin content element.
   This element can be next to your frontend login form, or it could
   sit their instead of the login form (if you do not need a regular login
   on your site).
#. In the tab "Plugin", select "One-time FE account with autologin" as plugin
   type.
#. Select which fields should be displayed.
#. Select which fields should be required.
#. Select the system folder in which the created front-end users should be
   stored.
#. Select one or multiple user groups which the created front-end users should
   automatically be assigned to.
#. Save and close.
