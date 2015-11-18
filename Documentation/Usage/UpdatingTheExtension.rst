

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

#. Make sure that you’re using at least PHP 5.5 and TYPO3 CMS 6.2.0.

#. Update the oelib and static\_info\_tables extensions to the latest
   versions.

#. Update the onetimeaccount extension.

#. Remove the contents of the typo3temp/llxml/ and
   typo3conf/l10n/\*/onetimeaccount/ directories (if they exist).
