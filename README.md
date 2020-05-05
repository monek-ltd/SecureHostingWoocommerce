SecureHosting Payment Gateway for WooCommerce
============================================

Manual Installation
-------------------

1. Copy the folder 'securehosting-woocommerce' to the '\wp-content\plugins\' directory for your WordPress website.

2. Within the WordPress Admin Dashboard, open the plugins tab and activate the "SecureHosting Payment Gateway for WooCommerce" plugin.
        If you have not already activated and configured WooCommerce you should also do this first.

3. Once activated, you can enter the settings for the plugin and enable the payment gateway. You will also need to configure the below settings
		in order to complete the basic setup:

    - **Reference** - This is the reference for your SecureHosting account. This is also known as the client login,
	you will find the value for this within the company details section of your SecureHosting account.

    - **Check Code** - This is the second level security check code for your SecureHosting account, it is a second
		unique identifier for your account. The value of your check code can be found within the company
		details section of your SecureHosting account.

    - **File Name** - This is the file name of the payment page template you need to upload to your SecureHosting
		account. The file name of the example template provided with this integration module is "woo_template.html". 
		You can rename this file if you desire, you only need to ensure the name of the file you upload to
		your SecureHosting account is correctly set here.

4. Upload the HTML files from the "forms" directory to your SecureHosting account through the file manager. 
We recommend uploading the default files first, testing, then amending these files as required. 
File uploads are managed within your SecureHosting account via the menu option Settings > File Manager:
    - woo_template.html
    - htmlgood.html
    - htmlbad.html
    
5.	You are now ready to go.


Advanced Configuration
----------------------

### Advanced Secuitems

The SecureHosting system supports the facility to secure your checkout from tampering, the facility is supported by
the Advanced Secuitems feature. In order for the Advanced Secuitems to work correctly, it must be correctly
configured in both the WooCommerce admin interface and your SecureHosting account. The settings for the Advanced Secuitems
within your SecureHosting account are found within the Advanced Settings section of the account.

1.	Activate Advanced Secuitems - In order to activate use of the Advanced Secuitems, set to "Yes". You will also
need to activate the feature within your SecureHosting account, this is performed by checking the below setting:	
	Activate Advanced Secuitems Security
	
In addition to activating the Advanced Secuitems in your SecureHosting account, you must enter "transactionamount" into
the list of fields to be encrypted.
	
2.	Advanced Secuitems Phrase - When securing your checkout, the SecureHosting system uses a unique phrase to create it's
		encrypted string. The phrase entered into your WooCommerce web site here must match the phrase configured
		within the Advanced Settings section of your SecureHosting account otherwise the system will block your transactions.
	
3.	Advanced Secuitems Referrer - As part of the security in generating the encrypted string, the SecureHosting system needs
		to very the shopping cart request, this is done by checking the referrer. The referrer configured here must match
		the referrer configured within your SecureHosting account within the Advances Settings. An example referrer for your
		site would be "http://www.mysite.com/".
	
5.	Test Mode - The SecureHosting system can run in test mode and processes your transactions as test transaction. Change this
		setting to True to use the test mode. Don't forget to change this back to False before going live!

Troubleshooting
-------------------

- When I get transferred to the SecureHosting site the following message appears: "_The file SH2?????/ does not exist_"
    - You have not completed steps 3 and/or 4 of the installation.

- When a transaction is submitted the following error is displayed: Merchant Implementation Error - Incorrect client SH reference and check code combination
    - You have entered an incorrect client reference or check code into the WooCommerce Admin interface.

- When I get transferred to the SecureHosting site, the following message appears: Advanced Secuitems security check failed.
    - You have activated the Advanced Secuitems within your SecureHosting account but not correctly configured it. 
    Please see steps 1-3 of the Advanced Configuration section of this guide.


Change Log
----------

#### Version 1.0
**Date**: _27/04/2020_

##### Details:
* Rebranded to remove references to UPG.

#### Version 0.1
**Date**: _04/03/2016_

##### Details:
* Added support for WooCommerce versions >= 2.0.0
* AS hashes built locally.
* Redirect is done via WC-API and a self-posting form. This allows greater amounts of data from the cart.
* Rebranded for UPG Legacy.

