=== PageSpeed Portfolio ===
Contributors: developer
Tags: pagespeed, portfolio, lighthouse, performance, seo
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Showcase your website portfolio with live Google PageSpeed Insights scores for Performance, Accessibility, Best Practices, and SEO.

== Description ==

PageSpeed Portfolio lets marketing agencies and web developers showcase their website portfolio with real Google PageSpeed Insights scores. Display beautiful score gauges for Performance, Accessibility, Best Practices, and SEO directly on your WordPress site.

**Features:**

* Custom post type for managing portfolio sites
* Automatic Google PageSpeed Insights API integration
* Circular Lighthouse-style score gauges (color-coded: green, orange, red)
* Both mobile and desktop score tracking
* Historical score data stored in a custom database table
* Daily WP-Cron job to keep scores fresh and up to date
* Responsive CSS grid layout via shortcode
* Simple settings page for API key configuration
* Clean uninstall removes all plugin data

**How It Works:**

1. Add your Google PageSpeed Insights API key in Settings
2. Create a new Site post with the URL of the website
3. Scores are fetched automatically when you publish
4. Use the `[pagespeed_portfolio]` shortcode to display your portfolio
5. Scores refresh daily via WP-Cron

**Third-Party Service:**

This plugin connects to the [Google PageSpeed Insights API](https://developers.google.com/speed/docs/insights/v5/get-started) to fetch Lighthouse audit scores for the URLs you configure. API calls are made only for URLs explicitly added by the site administrator.

* Google PageSpeed Insights API: [https://developers.google.com/speed/docs/insights/v5/get-started](https://developers.google.com/speed/docs/insights/v5/get-started)
* Google Terms of Service: [https://developers.google.com/terms](https://developers.google.com/terms)
* Google Privacy Policy: [https://policies.google.com/privacy](https://policies.google.com/privacy)

No user data is collected or transmitted. The only external requests are PageSpeed audits for URLs configured by the administrator.

== Installation ==

1. Upload the `pagespeed-portfolio` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Settings > PageSpeed Portfolio and enter your Google PageSpeed Insights API key.
4. Create Site posts under the Sites menu in your admin dashboard.
5. Add the `[pagespeed_portfolio]` shortcode to any page or post.

**Obtaining an API Key:**

1. Visit the [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Create a new project or select an existing one
3. Enable the PageSpeed Insights API
4. Create an API key credential
5. Copy the key into the plugin settings

== Frequently Asked Questions ==

= Where do I get a Google PageSpeed API key? =

Visit the [Google Cloud Console](https://console.cloud.google.com/apis/credentials), enable the PageSpeed Insights API, and create an API key.

= How often are scores updated? =

Scores are refreshed automatically once daily via WP-Cron. You can also manually refresh scores from the Site edit screen.

= Can I display both mobile and desktop scores? =

Yes. Use the `strategy` attribute in the shortcode: `[pagespeed_portfolio strategy="desktop"]` or `[pagespeed_portfolio strategy="mobile"]` (default).

= What shortcode attributes are available? =

* `columns` - Number of grid columns (default: 3)
* `orderby` - Sort by: date, performance, accessibility, best_practices, seo
* `order` - ASC or DESC (default: DESC)
* `limit` - Number of sites to display (default: all)
* `strategy` - mobile or desktop (default: mobile)

= Does this plugin track users? =

No. This plugin does not collect, store, or transmit any visitor data. The only external requests are PageSpeed API calls for URLs configured by the administrator.

= What happens when I uninstall the plugin? =

All plugin data is completely removed including options, custom posts, post meta, database tables, and scheduled events.

== Screenshots ==

1. Portfolio grid display with Lighthouse-style score gauges
2. Site edit screen with URL field and score display
3. Settings page for API key configuration

== Changelog ==

= 1.0.0 =
* Initial release
* Custom post type for portfolio sites
* Google PageSpeed Insights API integration
* Circular score gauge display
* Mobile and desktop score tracking
* Historical score database table
* Daily cron score refresh
* Responsive grid shortcode
* Settings page with API key management
* Clean uninstall

== Upgrade Notice ==

= 1.0.0 =
Initial release of PageSpeed Portfolio.
