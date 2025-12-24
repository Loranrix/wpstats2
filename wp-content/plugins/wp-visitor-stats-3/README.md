# WP Visitor Stats - WordPress Plugin

A beautiful visitor statistics plugin for WordPress that displays daily, weekly, monthly, yearly visitors and real-time online users.

## Features

- **Multiple Display Formats**: Minimal, Compact, and Detailed
- **8 Color Schemes**: Blue, Green, Purple, Red, Orange, Pink, Indigo, Teal
- **Configurable Position**: Header (left/right/center), Before Content, Footer
- **IP Authorization**: Control which IP addresses can see statistics
- **Cookie Access**: Generate smartphone-friendly links with 1-year cookie access
- **Real-time Updates**: Live online user count with automatic refresh
- **Cache Compatible**: Works with most caching plugins (with warnings)
- **WordPress Compatible**: Tested with WordPress 5.0 to 6.9

## Installation

1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file
4. Activate the plugin
5. Go to Visitor Stats in the admin menu to configure

## Configuration

### Basic Settings

1. **Enable Statistics**: Toggle to show/hide statistics
2. **Display Position**: Choose where to display (Header Left/Right/Center, Before Content, Footer)
3. **Display Format**: Choose Minimal, Compact, or Detailed
4. **Color Scheme**: Select from 8 beautiful color schemes

### IP Authorization

- Add IP addresses that should see the statistics
- Only authorized IPs will see the display (unless "Track All Visitors" is enabled)

### Cookie Access

- Click "Generate Cookie Link" to create a smartphone-friendly link
- Opening this link on a device sets a 1-year cookie
- Users with the cookie can see statistics

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Database

The plugin creates two tables:
- `wp_visitor_stats_visits`: Stores visit records
- `wp_visitor_stats_online`: Stores online user data

## Cache Plugins

The plugin detects common cache plugins and shows warnings if detected. For best results:
- Exclude visitor tracking endpoints from caching
- Configure cache to not cache AJAX requests to `wp_visitor_stats_ping` and `wp_visitor_stats_get`

## Support

For issues or questions, please contact the plugin author.

## License

GPL v2 or later
