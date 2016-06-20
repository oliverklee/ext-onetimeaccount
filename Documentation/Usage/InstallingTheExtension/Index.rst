

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


Installing the extension
^^^^^^^^^^^^^^^^^^^^^^^^

#. Make sure that you’re using at least PHP 5.5 and TYPO3 CMS 6.2.0.

#. Install the required extensions:

#. **static\_info\_tables**

#. **oelib**

#. **mkforms**

#. If you would like to have additional fields like gender or employment
   status, install the extension **sf\_register** or **sr\_feuser\_register**.
   It is not necessary to actually place the FE plug-in on a page; the
   extension just needs to be installed.

#. If you would like to use the checkbox “Receive e-mails as HTML”, the
   extension **direct\_mail** needs to be installed.

#. Install this extension.

#. Remove the contents of the typo3temp/llxml/ and
   typo3conf/l10n/\*/onetimeaccount/ directories (if they exist).

#. Include this extension’s static template in your site template under
   “Include static (from extensions)”.

#. Create a system folder where the FE user accounts will be stored.

#. On a page, create a new content element. From the wizard, select *One-
   time FE account creator* .

#. Set the plug-in access to “hide at login” (or set the page access to
   “hide at login”).

#. **Optional:** In the plug-in flexforms, select the fields which should
   be displayed. If you don’t select any fields, a default set of fields
   will be used. **Note (especially concerning the**  **Seminar Manager**
   **):** There are three fields for the name: the full name, the first
   name and the last name. Please check either the full name (this is
   recommended if you use this extension for the Seminar Manager
   extension) or both the first name and the last name, but not all
   three.Note: There are two country fields: the country name as
   localized clear text and the ISO 3166 alpha2 code. Both will appear as
   drop-down boxes in the front end and will look the same to the user.
   Please select only one (or none), but not both fields.

#. Select the system folder where the FE user accounts will be stored.

#. Select the FE user groups to which the new FE user records will be
   assigned.

#. **Optional:** If you like, you can use a custom HTML template. If you
   don’t choose anything here, the default HTML will be used.

#. If you're using all three extensions  *seminars* ,  *onetimeaccount*
   and  *cal\_ts\_service* , the extension  **seminarscalredirect** might
   be useful for you.
