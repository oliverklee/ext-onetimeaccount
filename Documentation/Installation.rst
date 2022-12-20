.. include:: /Includes.rst.txt

.. _installation:

======================
Installation and setup
======================

Deciding on which plugin to use
===============================

This extension usually is used on the login page of the
`seminars extension <https://extensions.typo3.org/extension/seminars>`__.

If you are using seminars version 5 (or later), you should use the plugin
without autologin, as this version uses the new Extbase-/Fluid-based
registration form that also works without a front-end login.

If you are using seminars 4.3.0 (or a later 4.x version) and have enabled
the feature switch to use the new registration form, you should also use the
plugin without autologin.

If you are using seminars below 5.0.0 or seminars 4 with the legacy
registration form, you need to use the plugin with autologin.

.. note::
   The plugin with autologin is deprecated and will be removed in
   onetimeaccount 7.0

Installation steps
==================

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
#. In the tab "Plugin", select "One-time FE account creator with autologin"
   or "One-time FE account creator without autologin" as plugin type
   (depending on which version of seminars you are using; see above).
#. Select which fields should be displayed.
#. Select which fields should be required.
#. Select the system folder in which the created front-end users should be
   stored.
#. Select one or multiple user groups which the created front-end users should
   automatically be assigned to.
#. Save and close.
