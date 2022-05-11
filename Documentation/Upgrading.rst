.. include:: /Includes.rst.txt

.. _upgrading:

=================================
Upgrading from version 4.x to 5.x
=================================

As version 5.0 is a complete rewrite switching to Extbase and Fluid, you will
not be able to automatically update your configuration or your HTML template.
In addition, the plugin configuration has completely moved from TypoScript to
Flexforms.

#. Make a screenshot of your onetimeaccount plugin content element configuration
   so you have a record of what you have before you upgrade.
#. Upgrade to onetimeaccount 5.x and install the dependencies as required.
#. Keep the static template "Onetimeaccount (onetimeaccount)" in your site
   TypoScript template.
#. Edit the onetimeaccount plugin content element and switch the plugin type
   to "One-time FE account with autologin".
#. Configure the plugin as recorded in your screenshot. If you have used
   TypoScript to configure your plugin, use the settings from TypoScript
   in the plugin configuration.
