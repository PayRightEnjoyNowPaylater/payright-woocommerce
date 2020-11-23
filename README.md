# Payright-Woocommerce

Payright Plugin for Wordpress

# 1.1 Payright Plugin Installation
This section outlines the steps to install the Payright plugin for the first time.

#### Prerequisite needed to obtain from Payright;
+ Payright Merchant Username
+ Merchant Password
+ Client Username
+ Client Password 
+ API Key

#### Install via Wordpress Dashborard

1. Download the payright-wordpress plugin - Available as a .zip or tar.gz file from the Payright GitHub directory.
2. Navigate to the "Add New" in the plugins dashboard
2. Navigate to the "Upload" area
3. Select the payright zip file from your computer
4. Click "Install Now"
5. Activate the plugin in the Plugin dashboard

# 1.2	Website Configuration
Payright operates under an assumptions based on Wordpress configurations. To align with these assumptions, the Wordpress configurations must reflect the below.

1. Store country set to Australia
2. Website Currency must be set to AUD
3. Woocommerce installed
 

# 1.3	Payright Merchant Setup
Complete the below steps to configure the merchant’s Payright Merchant Credentials in Wordpress Admin.

1. Go to plugins page and click configure.
2. Enter the Username, Password and API key.
3. Enter the Merchant Username and Merchant Password.
4. Enable Payright plugin using the Enabled checkbox.
5. Configure the Payright API Mode (Sandbox Mode for testing on a staging instance and Production Mode for a live website and legitimate transactions).
6. Save the configuration.

# 1.4	Payright Display Configuration

1. Navigate to Woocommerce > Settings > Payments
2. Enable Payright and Click on Manage button under Payment for Payright
3. Configure the display of the Payright installments details on;
+ Product Pages (individual product display pages)  - Recommended
+ Category  Pages (the listing of products, which would also include Search Pages) - Recommended
+ Related Products - Optional
+ Front Page  - Optional
3. Enter a Minimum amount to display the installments.
