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


Setup for the front-end plug-in in plugin.tx\_onetimeaccount\_pi1
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can configure the plug-in using flexforms of the front-end plug-in
(for most values) or your TS template setup in the form
plugin.tx\_onetimeaccount\_pi1. *property = value.*

**Note: If you set any non-empty value in the flexforms, this will
override the corresponding value from TS Setup.**

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         Property:

   Data type
         Data type:

   Description
         Description:

   Default
         Default:


.. container:: table-row

   Property
         templateFile

   Data type
         string

   Description
         File name of the HTML template

   Default
         EXT:onetimeaccount/Resources/Private/Templates/FrontEnd\.html


.. container:: table-row

   Property
         salutation

   Data type
         string

   Description
         Switch whether to use formal/informal language on the front
         end.Allowed values are:formal \| informal

   Default
         formal


.. container:: table-row

   Property
         feUserFieldsToDisplay

   Data type
         string

   Description
         comma-separated list of the FE user fields that can be edited in the
         form; there needs to be at least one name field present (name,
         first\_name or last\_name)

   Default
         company, name, zip, country, email, telephone, fax


.. container:: table-row

   Property
         requiredFeUserFields

   Data type
         string

   Description
         comma-separated list of FE user fields which the user is required to
         fill inNote: If displayed, the gender, usergroup and
         module\_sys\_dmail\_html fieldsautomatically are required.

   Default
         name, email


.. container:: table-row

   Property
         systemFolderForNewFeUserRecords

   Data type
         page\_id

   Description
         PID of the system folder in which new FE user accounts will be stored

   Default


.. container:: table-row

   Property
         groupForNewFeUsers

   Data type
         record\_id

   Description
         IDs of the groups for new FE users

   Default


.. container:: table-row

   Property
         userNameSource

   Data type
         string

   Description
         The source from which to generate the user login name (email or name)

   Default
         email


.. container:: table-row

   Property
         form

   Data type
         array

   Description
         The FORMidable configuration for the onetimeaccount registration form.

   Default


.. ###### END~OF~TABLE ######

[tsref:plugin.tx\_onetimeaccount\_pi1]
