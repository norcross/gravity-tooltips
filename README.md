Gravity Tooltips
================

Add custom tooltips in Gravity Forms.

**Note:** Version 2.0.0 has numerous changes from previous versions, including a switch to using the [hint.css library by Kushagra Gour](http://kushagragour.in/lab/hint/ "hint.css library by Kushagra Gour") and removing all JS requirements. It may not be compatible with current implementations, please backup and test before updating any production sites.

## FAQs

### How does it work?

##### Settings
1. Install and activate the plugin
1. Under the main "Forms" admin menu, select "Tooltips"
1. Make any changes to your settings and press save

##### Form Fields
1. Create a new form or edit an existing form
1. Select the individual field you want to add a tooltip to
1. Under the "Advanced" tab, enter the tooltip content


### The field is not showing. Why?
Look at the `show_field_item_types` function to see which fields types are included. Also, there may be another plugin conflicting with the field setup. Beyond that I do not know.


### Can I use HTML inside the tooltips?
For the most part, no. The tooltips can display linebreaks, but you'll need to use the HTML entity `&#xa;` and not a `<br>` for it to work.

## Changelog
See [CHANGES.md](CHANGES.md).

## License
Copyright (c) 2013 Andrew Norcross
Licensed under the [MIT license](http://opensource.org/licenses/MIT).