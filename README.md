Builder is a visual development tool. It shortens plugin development time by automating common development tasks and makes programming fun again. With Builder you can create a fully functional plugin scaffold in a matter of minutes.

Builder makes the learning curve less steep by providing a visual interface that naturally incorporates October's design patterns and documentation. Here’s an example, instead of looking into the documentation for a list of supported form controls and their features, you can just open the Form Builder, find a suitable control in the Control Palette, add the control to the form and explore its properties with the visual inspector.

Builder implements a Rapid Application Development process that automates the boring activities without sacrificing complete control. With this tool you can spend more time implementing the plugin's business logic in your favorite code editor rather than dealing with the more mundane tasks, like building forms or managing plugin versions.

Plugins created with the help of Builder are no different to plugins that you would usually create by hand. That means that you can continue to use your usual “hands on” workflow for updating your servers, managing the code versions and sharing work with your teammates.

**What's New!** Builder has been revamped to support October CMS v3, including dark mode, updated specifications, a Tailor import tool, and a new inline code editor.

## Requirements

- October CMS 3.3 or above

### Installation

Run the following to install this plugin:

```bash
php artisan plugin:install RainLab.Builder
```

To uninstall this plugin:

```bash
php artisan plugin:remove RainLab.Builder
```

If you are using October CMS v1 or v2, install v1.2 with the following commands:

```bash
composer require rainlab/builder-plugin "^1.2"
```

## Video Tutorial

We also recorded a video tutorial showing how to use the plugin to build a simple library plugin: [watch the video](https://vimeo.com/154415433).

## What You Can Do with Builder

This tool includes multiple features that cover almost all aspects of creating a plugin.

- Initializing a new plugin - this creates the plugin directory along with any necessary files.
- Creating and editing plugin database tables. All schema changes are saved as regular migration files, so you can easily update the plugin on other servers using your regular workflow.
- Creating model classes.
- Creating backend forms with the visual Form Builder.
- Creating backend lists.
- Managing a list of user permissions provided by the plugin.
- Creating plugin backend navigation - in the form of main menu items and sidebar items.
- Creating backend controllers and configuring their behaviors with a visual tool.
- Managing plugin versions and updates.
- Managing plugin localization files.
- A set of universal components - used for displaying data from the plugin on the front-end in form of lists and single record details.
- Managing raw code files directly in the backend (**New in v2**).
- Converting [Tailor blueprints](https://docs.octobercms.com/3.x/cms/tailor/blueprints.html) to plugin files (**New in v2**).

Put simply, you can create a multilingual plugin, that installs database tables, has backend lists and forms protected with user permissions, and adds CMS pages for displaying data managed with the plugin. After learning how Builder works, this process takes just a few minutes.

Builder is a productivity tool, it doesn't completely replace coding by hand and doesn't include a code editor for editing PHP files (the only exception is the version management interface). Builder never overwrites or deletes plugin PHP files, so you can rest assured knowing that the code you write never gets touched by Builder. However, Builder can create new PHP files, like models and controllers.

Most of the visual editors in Builder work with YAML configuration files, which are a native concept in October CMS. For example, after creating a model class in Builder, you can choose to add a form to the model. This operation creates a YAML file in the model's directory.

There are currently some limitations when using Builder. Some of them are missing features which will be added later. Others are ideas intentionally omitted to keep the things simple. As mentioned above, Builder doesn't want to replace coding, while at the same time, it doesn't go too far with visual programming either. The limitations are explained and described in the corresponding sections of the plugin documentation. Those limitations don't mean you can't create any plugin you want - the good old approach to writing the code manually is always applicable for plugins developed with Builder. Builder’s aim is to be a modest, yet powerful tool that is used to accelerate your development cycle.

## Getting Started

Before you create your first plugin with Builder you should configure it. Open the Settings page in October CMS backend and find Builder in the side menu. Enter your author name and namespace. The author name and namespace are required fields and should not change if you wish to publish your plugins on October CMS Marketplace.

If you already have a Marketplace account, use your existing author name and namespace.

## Initializing a New Plugin

On the Builder page in October CMS backend click the small arrow icon in the sidebar to expose the plugin list. After clicking the "Create plugin" button, enter the plugin name and namespace. The default author name and namespace can be pre-filled from the plugin settings. Select the plugin icon, enter the description text and plugin homepage URL (optional).

Please note that you cannot change the namespaces after you create the plugin.

When Builder initializes a plugin, it creates the following files and directories in October's **plugins** directory:

```
authornamespace
    pluginnamespace
       classes
       lang
           en
               lang.php
       updates
           version.yaml
       Plugin.php
       plugin.yaml
```

The file **plugin.yaml** contains the basic plugin information - name, description, permissions and backend navigation. This file is managed by the Builder user interface.

The initial contents of the **lang.php** localization file is the plugin name and description. The localization file is created in the default locale of your October installation.

When a new plugin is created, it's automatically selected as the current plugin that Builder works with. You can select another plugin in the plugin list if you need.

## Managing Plugin Database Tables

Tables are managed on the Database tab of Builder. You can create tables, update their structure and delete tables with the visual interface.

Click Add button to open the Create Table tab. Builder automatically generates prefixes for plugin tables. The prefixes are compliant with the [Developer Guidelines](http://octobercms.com/docs/help/developer-guide) for the Marketplace plugins.

Every time when you save changes in a table, Builder shows a popup window with the automatically generated migration PHP code. You can't edit the code in the popup, but you can inspect it or copy to the clipboard. After reviewing the migration, click Save & Apply button. Builder executes the migration immediately and saves the migration file to the plugin's **updates** directory. Afterwards you can find all plugin migrations on the Versions tab of Builder.

> **Important**: Although Builder generates migration files automatically, it can't prevent data loss in some cases when you significantly change the table structure. In some cases it's possible - for example, when you alter length of a string column. Always check the migration PHP code generated by Builder before applying the migration and consider possible consequences of running the migration in a production database.

Currently Builder doesn't allow to manage table indexes with the visual user interface. Unique column management is not supported yet as well. Please use the Version Management feature to manually create migration files.

Please note that the `enum` data type is not currently supported by the Builder due to limitations in the underlying Doctrine classes.

## Managing Models

You can edit models on the Models tab of Builder. Click the Add button, enter the model class name and select a database table from the drop-down list.

The model class name should not contain the namespace. Some examples: Post, Product, Category.

Please note that you cannot delete model files with builder because it would contradict the idea of not deleting or overwriting PHP files with the visual tool. If you need to delete a model, remove its files manually.

## Managing Backend Forms

In October CMS forms belong to models. For every model you can create as many backend forms as you need, but in most cases there is a single form per model.

> **Note**: When you create a form, it's not displayed in October CMS backend until you create a backend controller which uses the form. Read more about controllers below.

On the Models tab in Builder find a model you want to create a form for. Expand the model if needed, hover the **Forms** section and click the plus sign.

Forms in October CMS are defined with YAML files. The default form file name is **fields.yaml**. In the Form Builder, click a placeholder and select a control from the popup list. After that you can click the control and edit it parameters in Inspector.

Almost all form controls have these common properties:

* Field name - a model field name. It's an autocomplete field in Inspector, which allows you to select column names from the underlying database table. Currently relations are not displayed in the autocomplete hints, this will be implemented later. You can enter any model property manually.
* Label - The control label. You can enter a static text string to the field, or create a new localization string by clicking the plus sign in the input field. Almost all editors in Builder support this feature.
* Comment - the comment text - fixed text or localization string key.
* Span - position of the control on the form - left, right, full or automatic placement.

Most of the properties have descriptive names or have a description in Inspector. If you need more information about control properties please refer to the [Documentation](https://docs.octobercms.com/3.x/element/form-fields.html).

You can drag controls in the Form Builder to rearrange them or to move them to/from form tabs. A form tab should have at least one control, otherwise it will be ignored when Form Builder saves the YAML file.

Some form controls, for example the file upload control, require a relation to be created in the model class manually. The relation name should be entered in the Field name property. Please read the [Forms Documentation](https://docs.octobercms.com/3.x/element/form-fields.html) for details about specific form controls.

## Managing Backend Lists

Similarly to forms, backend lists in October CMS belong to models.

> **Note**: When you create a list, it's not displayed in October CMS backend until you create a backend controller which uses the list. Read about controllers below.

On the Models tab in Builder find a model you want to create a list for. Expand the model if needed, hover the **Lists** section and click the plus sign.

Lists in October CMS are defined with YAML files. The default list file name is **columns.yaml**. The grid in the list editor contains list column definitions. Column property names are self descriptive, although some of them require some explanations. Refer to the [Lists documentation](https://docs.octobercms.com/3.x/element/list-columns.html) for details about each property.

For the Label property you can either enter a static string or create a new localization string.

The Field property column has an autocompletion feature attached. It allows you to select columns from the database table that is bound to the model. At the moment it doesn't show relation properties, but you can still type them in manually.

## Managing Plugin Permissions

[Plugin permissions](https://docs.octobercms.com/3.x/extend/backend/users.html) define the features and backend plugin pages a user can access. You can manage permissions on the Permissions tab in Builder. For each permission you should specify a unique permission code, permission tab title and permission label. The tab title and label are displayed in the user management interface on the System page in October backend.

For the tab title and label you can either enter a static string or create a new localization string.

Later, when you create controllers and menu items, you can select what permissions users should have in order to access or see those objects.

## Managing Backend Menus

The [plugin navigation](https://docs.octobercms.com/3.x/extend/backend/navigation.html) is managed on the Backend Menus tab of the Builder. The user interface allows to create top level menu items and sidebar items.

To create a menu item click the placeholder rectangle and then click the new item to open Inspector. In the inspector you can enter the item label, select icon and assign user permissions. The **code** property is required for referring menu items from the controllers code (for marking menu items active).

> **Note**: When you create menu items for backend pages which don't exist yet in the plugin, it makes sense to leave the **URL** property empty until you create the plugin controllers. This property supports autocompletion, so you can just select your controller URLs from the drop-down list.

## Managing Backend Controllers, Forms and Lists

Back-end pages in October CMS are provided with backend controllers. Usually backend pages contain lists and forms for managing plugin records, although you can create any custom controller.

Please refer to the [backend forms](https://docs.octobercms.com/3.x/extend/forms/form-controller.html) and [lists](https://docs.octobercms.com/3.x/extend/lists/list-controller.html) documentation pages for more information about controller behaviors. Currently only List and Form behaviors can be configured with the Builder. If your controller contains other behaviors they won't be removed by the Builder, you just won't be able to edit them with the visual interface.

Builder also allows you to create empty controller classes which don't implement any behaviors and customize them manually.

> **Note**: Some behaviors require specific model features to be implemented. For example, the Reorder Controller behavior requires the model to implement Sortable or NestedTree traits. Always refer to the specific behavior documentation for the implementation details.

To create a controller, click the Add button list on the Controllers tab. Enter the controller class name, for example Posts.

If the controller is going to provide backend lists or forms, select a base model in the drop-down list and select behaviors you want to add. You can also select a top and sidebar menu items that should be active on the controller pages. If needed, choose permissions that users must have to access the controller pages.

> Please note that the settings you enter in the Create Controller popup cannot be changed with Builder. However you can update them manually by editing controller classes.

After creating a controller you can configure its behaviors. Click the controller in list and then click a behavior you want to configure. When Builder creates a controller it tries to apply default configuration to the behaviors, however you might want to change it. Inspector lists displays all supported behavior properties. URL properties (like the list records URLs) are autocomplete fields and populated with URLs of the existing plugin controllers.

## Managing Plugin Versions

Please read the [Version History](https://docs.octobercms.com/3.x/extend/system/plugins.html#version-history) documentation page to understand how versioning works in October CMS.

Basically there are 3 types of version updates:

1. Updates which change the database structure - migrations. Builder can generate migration files automatically when you make changes in the DB schema on the Database tab.
2. Seeding updates, which populate the database contents.
3. Version updates, which do not update anything in the database but are often used for releasing code changes.

Plugin versions are managed on the Versions tab of the Builder. This tab displays a list of existing plugin versions their status. Applied versions have a green checkbox marker. Pending versions have a grey clock marker.

You can create a new version with clicking the Add button and selecting the update time. The user interface automatically generates scaffold PHP code for the "Migration" and "Seeder" updates. The "Increase the version number" updates won’t contain any PHP code.

For every version you should specify the new version number and description. Builder generates the version number automatically by increasing the last digit in the existing version. You might want to change it if you're releasing a major version update and want to change the first or second digit.

When a version file is saved, Builder doesn't apply it immediately. You should click the "Apply version" button in the toolbar in order to apply the version and execute the update code (if applicable). You can also rollback already applied version updates, change their code and apply again. This allows you to edit database schema updates generated by Builder if you don't like the default code.

> **Note**: Your migration files should provide correct rollback code in the `down` method in order to use the rollback feature.

When you rollback a version, it automatically rolls back all newer versions. When you apply a version, it automatically applies all pending older versions. Please remember that when a user logs into the backend, October automatically applies all pending updates. Never edit versions on a production server or on a server with multiple backend users - it could cause unpredictable consequences.

Migrations that contain multiple scripts are not supported. They can't be created or edited with Builder.

## Managing Localization

Localization files are managed on the Localization tab of the Builder. When a new plugin is initialized, a single language file is created. This file is created in the default system locale specified in October CMS configuration scripts.

You can create as many language files as you want. Builder UI always displays strings in the October CMS locale, so you might want to update your configuration files to see your plugin in another language.

Please note that although [localization files in October CMS](https://docs.octobercms.com/3.x/extend/system/localization.html) are PHP scripts, they are translated to YAML to simplify the editing in the Builder user interface. When language files are saved, they are translated back to PHP again.

Builder tries to keep the user interface synchronized with your default language file. This means that when you save the language file, Builder automatically updates all localized strings in all editors. In some cases you might need to close and open Inspector in order to re-initialize the autocomplete fields.

In many cases you can create new localization strings on-the-fly from the Builder editors - the Form Builder, Menu Builder, etc. The localization input field has the plus icon on the right side. Clicking the plus icon opens a popup window that allows you to enter the localization string key and value. The string key can contain dots to mark the localization file sections. For example - if you add a string with the key `plugin.posts.category` and value "Enter a category name", Builder will create the following structure in the language file:

```
plugin:
    posts:
        category: Enter a category name
```

If you create a new localization string from the Inspector or other editor while you have the default language file tab open in the Builder, it will try to update the tab contents or merge the updated file contents from the server. It's a good idea to keep the default localization file always saved in the Builder to avoid possible content conflicts when you edit localization from another place.

> **Tip**: In YAML a single quote is escaped with two single quotes (http://yaml.org/spec/current.html#id2534365).

## Editing Code Files (New in v2)

Raw code and other files can be managed directly in the Code tab of the Builder. You can navigate to any file within the context of the selected plugin.

Use this to make code adjustments without the need for a code editor, which includes creating, moving, renaming and deleting files.

## Importing Tailor Blueprints (New in v2)

The Import tab of the Builder can generate scaffold files using [Tailor blueprints](https://docs.octobercms.com/3.x/cms/tailor/blueprints.html) as a source. In combination, Tailor and Builder work together to create a super-scaffolding tool since it can generate multiple files in one process. First, design your fields and preview them using Tailor, and then when you are ready, import them in to Builder to start working directly with the files.

It important to note that importing blueprints is a one-way function, and in some cases it is better to leave content within Tailor for the benefits that come with its dynamic models, especially for content. This design decision is up to you.

When visiting the Import tab, use the **Add Blueprint** button to select the Tailor blueprints you wish to import. You can select multiple blueprints and it is recommended to include related blueprints so the relationships between blueprints are preserved.

Once added, each blueprint can be customized, which includes the Controller Class, Model Class, Table Name, Permission Code and Menu Code names. When these fields are modified, they will adjust the filenames of the generated files.

Clicking the Import button will begin the conversion process. Some import options are shown to control how the import should proceed.

- **Migrate Database** performs a database migration after the import is finished. This optional and you can migrate the database later.

- **Disable Blueprints** will rename the blueprint files to use a backup extension (.bak) to disable them.

- **Delete Blueprint Data** will delete any existing data and tables for the selected blueprints found within Tailor.

Before clicking **Import**, be sure to double check the selected blueprints. The import process creates multiple scaffold files for the selected plugin. This process can be difficult to undo, so it is a good idea to practice on a test plugin first, without migrating the database or disabling the blueprints.

When everything is done, you should see the controller, model and migration files generated for your selected plugin.

## Displaying Plugin Records on CMS Pages

Builder provides universal CMS components that you can use for displaying records from your plugins on the front-end website pages. The components provide only basic functionality, for example they don't support a record search feature.

Please read the [CMS documentation](https://docs.octobercms.com/3.x/cms/themes/components.html) to learn more about the CMS components concept.

### Record List Component

The Record list component outputs a list of records provided by a plugin's model. The component supports the following optional features: pagination, links to the record details page, using a [model scope](https://docs.octobercms.com/3.x/extend/database/model.html#query-scopes) for the list filtering. The list can be sorted by any column, but the sorting cannot be changed by website visitors - it's set in the component configuration.

Add this component to a CMS page by dragging it to the page code from the component list and click it to configure its properties:

* `Model class` - select a model class you want to use to fetch data from the database.
* `Scope` - optional, select the model scope method used to filter the results
* `Scope Value` - optional, the value to provide to the selected scope method. URL parameters can be provided in the form of `{{ :nameOfParam }}`
* `Display column` - select the model column to display in the list. It's an autocomplete field that displays columns from the underlying database table. You can enter any value in this field. The value is used in the default component partial, you can customize the component by providing custom markup instead of the default partial.
* `Details page` - a drop-down list of CMS pages you want to create links to.
* `Details key column` - select a column you want to use as a record identifier in the record links. You can link your records by the primary identifier (id), slug column or any other - it depends on the your database structure.
* `URL parameter name` - enter the details page URL parameter name, which takes the record identifier. For example, if the record details page has a URL like "/blog/post/:slug", the URL parameter would be "slug".
* `Records per page` - enter a number to enable pagination for the records.
* `Page number` - specify the fixed page number or use the **external parameter editor** to enter a name of the URL parameter which holds the page number. For example - if your record list page URL was "/record-list-test/:page?", the Page number property value would be ":page".
* `Sorting` - select a database column name to use for sorting the list.
* `Direction` - select whether the sorting should be ascending or descending.

After configuring the component save and preview the page. Most likely you will want to customize the [default component markup](https://docs.octobercms.com/3.x/cms/themes/components.html#customizing-default-markup) to output more details about each record.

> **Tip**: In the CMS editor, you can right-click on the `{% component %}` tag and select "Expand Markup".

## Record Details Component

The Record details component loads a model from the database and outputs its details on a page. If the requested record cannot be found, the component outputs the "record not found" message.

Add this component to a CMS page by dragging it to the page code from the component list and click it to configure its properties:

* `Model class` - select a model class you want to use to fetch data from the database.
* `Identifier value` - specify a fixed value or use the **external parameter editor** to enter a name of the URL parameter. If the details page had the URL like "/blog/post/:slug", the identifier value would be ":slug".
* `Key column` - specify a name of the database table column to use for looking up the record. This is an autocomplete field that displays columns from the underlying database table.
* `Display column` - enter a name of the database table column to display on the details page. The value is used in the default component partial, you can customize the component by providing custom markup instead of the default partial.
* `Not found message` - a message to display if the record is not found. Used in the default partial.

After configuring the component save and preview the page. You will likely want to customize the [default component markup](https://docs.octobercms.com/3.x/cms/themes/components.html#customizing-default-markup) to output more details from the loaded model.

## Notes About Autocompletion

Builder updates the Inspector autocompletion fields every time when the underlying data is updated. For example, the “Field name” property of the Form Builder controls is populated with the database table column names. If you update the table structure with Builder, the autocompletion cache updates automatically. However you may need to reopen Inspector so that it can update its editors.

If you edit your plugin files or database structure with an external editor, Builder won’t be able to pick up those changes automatically. You might want to reload the Builder page after you add a database column with an external tool in order to refresh the autocompletion features.

## Editing Other Plugins

Although Builder allows you to edit plugins created by other authors, remember that you do it at your own risk. Plugins could be updated by their authors, which will eliminate your changes or break the plugin. In many cases, if you make updates to plugins developed by another author, you lose any technical support provided by the author.

## Adding Support for a Custom Form Widget

To add a custom widget to the Builder plugin, you must first [register a backend form widget](https://docs.octobercms.com/3.x/extend/forms/form-widgets.html#form-widget-registration) for your plugin.

Once it is registered, define a list of [inspector properties](https://docs.octobercms.com/3.x/element/inspector-types.html) within your plugin in the Plugin registration class `boot()` method and register the custom control. For example:

```php
public function boot()
{
    $properties = [
        'max_value' => [
            'title' => 'The maximum allowed',
            'type' => 'builderLocalization',
            'validation' => [
                'required' => [
                    'message' => 'Maxium value is required'
                ]
            ]
        ],
         'mode' => [
            'title' => 'The plugin mode',
            'type' => 'dropdown',
                'options' => [
                    'single' => 'Single',
                    'multiple' => 'Multiple',
                ],
            'ignoreIfEmpty' => true,
        ]
    ];

    Event::listen('pages.builder.registerControls', function($controlLibrary) {
        $controlLibrary->registerControl(
            'yourwidgetname',
            'My Widget',
            'Widget description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-file-image-o',
            $controlLibrary->getStandardProperties([], $properties),
            'Acme\Blog\Classes\ControlDesignTimeProvider'
        );
    });
}
```

> **Note**: See the `getStandardProperties()` method in the `rainlab/builder/classes/ControlLibrary.php` file for more examples.

Now, we need the `ControlDesignTimeProvider` class referenced above. Save the following as `classes/ControlDesignTimeProvider.php` within your plugin's directory (replacing `'yourwidgetname'` with what you used in your Plugin registration class `boot()` method).

```php
namespace Acme\Blog\Classes;

use RainLab\Builder\Widgets\DefaultControlDesignTimeProvider;

class ControlDesignTimeProvider extends DefaultControlDesignTimeProvider
{
    public function __construct()
    {
        $this->defaultControlsTypes[] = 'yourwidgetname';
    }
}
```

Then save the following as `class/controldesigntimeprovider/_control-yourwidgetname.htm` within your plugin's directory, and customize it how you like. Again, `yourwidgetname` in the file name must match:

```html
<div class="builder-blueprint-control-text">
    <?= e(trans('acme.blog::lang.mywidget.placeholder')) ?>
</div>
```

You should now be able to add and configure your custom widget within the Builder plugin just like any other plugin.

### License

This plugin is an official extension of the October CMS platform and is free to use if you have a platform license. See [EULA license](LICENSE.md) for more details.
