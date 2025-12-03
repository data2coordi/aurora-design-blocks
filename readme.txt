=== Aurora Design Blocks ===
Contributors: data2coordi
Tags: toc, analytics, ga4, gtm, ogp
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Short Description: Multi-functional plugin for GA4, GTM, AdSense, OGP, and automated Table of Contents (TOC), generally essential for blogs.


== Description ==

**✅ Manual: [Setup and Usage](https://integlight.auroralab-design.com/aurora-design-blocks/)**
<和訳>マニュアル：[設定方法と使い方](https://integlight.auroralab-design.com/aurora-design-blocks/)


Aurora Design Blocks is a multi-functional plugin that supports external asset management and provides essential features for content-rich websites.  
It allows you to configure GA4, GTM, AdSense, and OGP settings directly from the WordPress Customizer without editing any code.  
In addition, it includes a customizable Table of Contents (TOC) block that automatically generates navigation based on H2 and H3 headings.

All tracking IDs (GA4/GTM/AdSense) must be manually entered by the user.  
This plugin does not automatically send or collect any data.

== External Services ==
This plugin outputs scripts for external services **only when users manually enter their own tracking IDs**.  
The plugin itself does **not** send any data automatically, and no data is transmitted unless the user provides their own IDs.

Below are the external services that may be used when configured:

### 1. Google Analytics 4 (GA4)
- **Purpose**: To enable website traffic measurement via Google Analytics.  
- **What data is sent**: When enabled by the user, GA4 sends analytics data such as page views and event data directly from the browser to Google.  
- **When data is sent**: Only after the user enters a valid GA4 Measurement ID and the page is loaded.  
- **Terms / Privacy Policy**:  
  - https://marketingplatform.google.com/about/analytics/terms/us/  
  - https://policies.google.com/privacy

### 2. Google Tag Manager (GTM)
- **Purpose**: To load user-defined marketing tags via Google Tag Manager.  
- **What data is sent**: GTM itself does not collect data, but GTM may load additional scripts defined by the user, and those scripts may send data independently.  
- **When data is sent**: Only after the user enters their GTM Container ID and GTM loads in the page.  
- **Terms / Privacy Policy**:  
  - https://marketingplatform.google.com/about/analytics/tag-manager/use-policy/  
  - https://policies.google.com/privacy

### 3. Google AdSense (Auto Ads)
- **Purpose**: To display personalized or non-personalized ads using Google AdSense.  
- **What data is sent**: When enabled, AdSense may collect data such as ad interactions, device information, and user behavior.  
- **When data is sent**: Only after the user enters their AdSense Publisher ID and enables Auto Ads.  
- **Terms / Privacy Policy**:  
  - https://www.google.com/adsense/new/localized-terms  
  - https://policies.google.com/privacy

== Features ==

### 1. External Asset Management
* **GA4 Integration** – Add your Measurement ID and optionally enable speed-optimized loading.
* **GTM Integration** – Insert your Google Tag Manager container code.
* **Google AdSense Auto Ads** – Output the Auto Ads script via the Customizer.

### 2. SEO & Social Media Optimization
* **Open Graph Protocol (OGP)** – Set global or per-post values for title, description, and image for social media sharing.

### 3. Automated Table of Contents (TOC) Block
* Automatically generates a clean TOC from H2/H3 headings.
* Provides a compact, readable, and mobile-friendly design.

== Installation ==
1. Log in to your WordPress administration panel.
2. Navigate to **Plugins > Add New**, search for "Aurora Design Blocks", or upload the ZIP file.
3. Click **Install Now**, then **Activate**.
4. Configuration options are available under **Appearance > Customizer**.

== Frequently Asked Questions ==

= Does this plugin send any data? =
No. GA4/GTM/AdSense codes are output *only after the user enters their own IDs*.  
This plugin itself does not track or send any data.

== Changelog ==
* Initial public release.