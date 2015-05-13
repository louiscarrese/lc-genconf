=== lc-genconf ===
Contributors: louiscarrese
Tags: admin, configuration, development
Requires at least: 2.6
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin is used to ease the generation of administration pages for other plugins.

== Description ==

This plugin is a tool for plugin developers. With it, you don't have to worry anymore about formatting, storing and retrieving your plugin configuration.

## Features

- A configuration page is described in a PHP array, pass that to lc-genconf with an identifier and it takes care of the rest
- A configuration can be repeated ("add" and "remove" buttons)
- A set of fields can be displayed conditionnaly, based on the value of another field
- Shipped CSS is minimal and can be easily overloaded.

## Usage

- Define a function named `yourid_configuration_defintion()` returning the array describing the configuration (see [documentation](https://github.com/louiscarrese/lc-wpgenconf/wiki/configuration_array#configuration-array))
- Call `lc_wpgenconf('yourid')` to get the HTML fragment.

== Installation ==

1. Copy the lc-genconf directory in your wp-content/plugins/ directory
1. Settle for an arbitrary (but unique) id of your plugin
1. In your plugin code, define a function named `yourid_configuration_defintion()` returning the array describing the configuration (see [documentation](https://github.com/louiscarrese/lc-wpgenconf/wiki/configuration_array#configuration-array))
1. Call `lc_wpgenconf('yourid')` to get the HTML fragment.

== Frequently Asked Questions ==

= What field types are supported ? =
As of today, the following field types are supported : 
- Text
- Checkbox
- Dropdown list
- Category list (wp_dropdown_categories)

I may add more field types if I need them or if you ask for them.

= Why is it a plugin and not some source code I could copy in my own plugin ? =

I found it easier to handle the problem like this with my own code.
If the source code is copied in multiple plugins directories, it will conflict at runtime because of multiple declarations of the same classes (and functions).
I thought it would also be easier to handle upgrades this way.

== Changelog ==
= 1.0.0 =
- Inital version
