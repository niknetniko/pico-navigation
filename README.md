# Pico Navigation Plugin

This is a plugin for the Flat File Based CMS named [Pico](pico.dev7studios.com/).

This is a fork of Ahmet Topal's [plugin](https://github.com/ahmet2106/pico-navigation). It provides these additional functions:

* Manual page sorting. You can specify in what order pages are displayed.
* Exclude pages with regex functions. Thanks to [oliverlorenz](https://github.com/oliverlorenz).

## Usage

### Installation & Basics

To install the plugin, follow these steps:

1. Go to [releases](https://github.com/niknetniko/pico-navigation/releases) and download the latest version.
2. Copy downloaded file (`at_navigation.php`) to the `plugins` folder in the root of your Pico project.
3. Add the following line in your **theme** where your navigation should be:

    ```
    {{ at_navigation.navigation }}
    ```

    Alternatively, you can use an adapted version of the default theme (included in the download). This looks like this:

	![Default Theme with at_navigation Plugin](img.png)

	Just replace the `default` theme folder with the one in the download and add this line to your config.php

	```
    $config['at_navigation']['class'] = 'nav';
    ```


### Options

You can customize the plugin with a bunch of options. To use them, simply add them to your `config.php` file.

#### Set the id and class of the navigation

Set the **id** and/or **class** from `at-navigation` to anything you want:

```
$config['at_navigation']['id'] = 'at-navigation';
$config['at_navigation']['class'] = 'at-navigation';
```

#### Set class of list items and links

Set the **id** and/or **class** from the list items to anything you want:

```
$config['at_navigation']['class_li'] = 'list-item';
$config['at_navigation']['class_a'] = 'link-item';
```

### Exclude pages and folders
Exclude **single pages** and/or **folders**:

```
$config['at_navigation']['exclude']['single'] = array('a/site', 'another/site');
$config['at_navigation']['exclude']['folder'] = array('a/folder', 'another/folder');
```

You can also exclude pages based on **regular expressions**. For example, you can exclude all blog posts:

```
$config['at_navigation']['exclude']['regex'] = array('/^blog\/[\w-]+\/*$/');
```

### Order pages
To set the order of the pages manually, you must add a meta tag to the page header:

```
Order: 1
```

For child menu's, the `Order` tag from the `index.md` file in the subfolder will be used. The other pages in the child menu have their own order, so you can start counting at 0 again.

A few remarks about the sorting:

* Pages without `Order` tag will be sorted alphabetically.
* Pages with a tag will be sorted before pages without tag.
* Pages with the same `Order` tag will not be sorted.

## What it does

This plugin generates a better navigation with child menu's and editable configuration.

So the output looks like:

    <ul id="at-navigation" class="at-navigation">
        <li><a href="…" title="…">…</a></li>
        <li>
            <a href="…" title="…">…</a>
            <ul>
                <li class="is-active"><a href="#" class="is-active" title="…">…</a></li>
            </ul>
        </li>
    </ul>

As you can see it will add an `.is-active` class to the `<a>` and `<li>` element of the **active page**.


## Licence

CreativeCommons2.0 licence: [CC BY-SA](http://creativecommons.org/licenses/by-sa/2.0/)

You are free to share & remix this code only if you mention me as coder of this base.


## Credits

Original plugin: Ahmet Topal

Sorting functions: Niko Strijbol

Regex excluding: Oliver Lorenz
