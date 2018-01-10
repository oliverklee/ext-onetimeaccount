

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


Updating the extension from 1.0.x to 1.1.x
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

#. Make sure that you’re using at least TYPO3 CMS 6.2.0.

#. Update the oelib and static\_info\_tables extensions to the latest
   versions.

#. Temporarily disable the onetimaccount extension.

#. Remove the ameos\_formidable extension.

#. Update the onetimeaccount extension and re-enable it.

#. In your TS template, include this static template *before* the
   onetimeaccount static template::
   MKFORMS - Basics (mkforms)

#. If your site does not use jQuery by default, also include the following
   static template::
   MKFORMS JQuery-JS (mkforms)
