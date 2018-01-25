

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


Adding fields to the registration form
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Beginning with onetimeaccount version 0.5.0 you can add additional
fields to the onetimeaccount registration form. The fields to be added
have to exist in the fe\_users table in your database.

Let's say you want to add a simple input field for a mobile phone
number. This is best done by creating a small extension which holds
the necessary stuff for the new field.

The extension to be created should:

#. add the field mobile\_phone to the fe\_users table,

#. have a locallang.xlf file containing the labels and the validation
   messages for the field mobile\_phone,

#. add the FORMidable configuration for the field mobile\_phone via
   TypoScript setup to plugin.tx\_onetimeaccount\_pi1.form.

The FORMidable configuration to be added via TypoScript setup in step
3 would look like this for our example:

::

   plugin.tx_onetimeaccount_pi1.form.elements.mobile_phone = renderlet:TEXT
   plugin.tx_onetimeaccount_pi1.form.elements.mobile_phone {
           name = mobile_phone
           process.userobj {
                   extension = this
                   method = isFormFieldEnabled
                   params {
                           10.name = elementName
                           10.value = mobile_phone
                   }
           }
           validators {
                   10 = validator:STANDARD
                   10.userobj {
                           message = LLL:EXT:yourextensionkey/locallang.xlf:message_mobile_phone
                           extension = this
                           method = validateStringField
                           params {
                                   10.name = elementName
                                   10.value = mobile_phone
                           }
                   }
                   20 = validator:PREG
                   20.pattern {
                           value = /^([\d\+][\d \-\+\/]*[\d\+])?$/
                           message = LLL:EXT:yourextensionkey/locallang.xlf:message_malformed_mobile_phone
                   }
           }
   }

The following has to be done manually:

#. add the field mobile\_phone via TypoScript setup to
   plugin.tx\_onetimeaccount\_pi1.feUserFieldsToDisplay,

#. add the field mobile\_phone via TypoScript setup to
   plugin.tx\_onetimeaccount\_pi1.requiredFeUserFields if necessary,

#. add a marker to the HTML template, e.g.:

   ::

      <!-- ###WRAPPER_MOBILE_PHONE### -->
       <dt>
       <label for="tx_onetimeaccount_pi1_form_mobile_phone"###MOBILE_HONE_REQUIRED###>
       ###LABEL_MOBILE_PHONE###
       </label>
       </dt>
       <dd>
       {mobile_phone}
       </dd>
      <!-- ###WRAPPER_MOBILE_PHONE### -->

Please notice that it is currently not possible to select non-default
fields via FlexForms.

If you want to add other types of form fields (textareas, radio
buttons, checkboxes, etc.) please have a look at the default
TypoScript setup configuration of onetimeaccount in
plugin.tx\_onetimeaccount\_pi1.form and/or the FORMidable manual.
