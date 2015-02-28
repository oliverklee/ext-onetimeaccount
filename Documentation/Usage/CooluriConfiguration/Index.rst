

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


CoolURI configuration
^^^^^^^^^^^^^^^^^^^^^

If you are using CoolURI, you need to add the following lines to your
CoolURI configuration:

::

   <uriparts>
     <part>
       <parameter>tx_seminars_pi1[seminar]</parameter>
     </part>
     <part>
       <parameter>tx_seminars_pi1[onetimeaccount]</parameter>
     </part>
   </uriparts>

