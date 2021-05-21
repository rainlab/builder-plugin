Builder is a visual development tool. It shortens plugin development time by automating common development tasks and makes programming fun again. With Builder you can create a fully functional plugin scaffold in a matter of minutes.

Builder makes the learning curve less steep by providing a visual interface that naturally incorporates October’s design patterns and documentation. Here’s an example, instead of looking into the documentation for a list of supported form controls and their features, you can just open the Form Builder, find a suitable control in the Control Palette, add the control to the form and explore its properties with the visual inspector.

Builder implements a Rapid Application Development process that automates the boring activities without sacrificing complete control. With this tool you can spend more time implementing the plugin's business logic in your favorite code editor rather than dealing with the more mundane tasks, like building forms or managing plugin versions.

Plugins created with the help of Builder are no different to plugins that you would usually create by hand. That means that you can continue to use your usual “hands on” workflow for updating your servers, managing the code versions and sharing work with your teammates.

## Video tutorial

We also recorded a video tutorial showing how to use the plugin to build a simple library plugin: [watch the video](https://vimeo.com/154415433).

## What you can do with Builder

This tool includes multiple features that cover almost all aspects of creating a plugin.

* Initializing a new plugin - this creates the plugin directory along with any necessary files.
* Creating and editing plugin database tables. All schema changes are saved as regular migration files, so you can easily update the plugin on other servers using your regular workflow.
* Creating model classes.
* Creating back-end forms with the visual Form Builder.
* Creating back-end lists.
* Managing a list of user permissions provided by the plugin.
* Creating plugin back-end navigation - in the form of main menu items and sidebar items.
* Creating back-end controllers and configuring their behaviors with a visual tool.
* Managing plugin versions and updates.
* Managing plugin localization files.
* A set of universal components - used for displaying data from the plugin on the front-end in form of lists and single record details.

Put simply, you can create a multilingual plugin, that installs database tables, has back-end lists and forms protected with user permissions, and adds CMS pages for displaying data managed with the plugin. After learning how Builder works, this process takes just a few minutes.

Builder is a productivity tool, it doesn't completely replace coding by hand and doesn't include a code editor for editing PHP files (the only exception is the version management interface). Builder never overwrites or deletes plugin PHP files, so you can rest assured knowing that the code you write never gets touched by Builder. However, Builder can create new PHP files, like models and controllers.

Most of the visual editors in Builder work with YAML configuration files, which are a native concept in OctoberCMS. For example, after creating a model class in Builder, you can choose to add a form to the model. This operation creates a YAML file in the model's directory.

There are currently some limitations when using Builder. Some of them are missing features which will be added later. Others are ideas intentionally omitted to keep the things simple. As mentioned above, Builder doesn't want to replace coding, while at the same time, it doesn't go too far with visual programming either. The limitations are explained and described in the corresponding sections of the plugin documentation. Those limitations don't mean you can't create any plugin you want - the good old approach to writing the code manually is always applicable for plugins developed with Builder. Builder’s aim is to be a modest, yet powerful tool that is used to accelerate your development cycle.

## Getting started

Before you create your first plugin with Builder you should configure it. Open the Settings page in OctoberCMS back-end and find Builder in the side menu. Enter your author name and namespace. The author name and namespace are required fields and should not change if you wish to publish your plugins on OctoberCMS Marketplace.

If you already have a Marketplace account, use your existing author name and namespace.

## Initializing a new plugin

On the Builder page in OctoberCMS back-end click the small arrow icon in the sidebar to expose the plugin list. After clicking the "Create plugin" button, enter the plugin name and namespace. The default author name and namespace can be pre-filled from the plugin settings. Select the plugin icon, enter the description text and plugin homepage URL (optional).

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

The file **plugin.yaml** contains the basic plugin information - name, description, permissions and back-end navigation. This file is managed by the Builder user interface.

The initial contents of the **lang.php** localization file is the plugin name and description. The localization file is created in the default locale of your October installation.

When a new plugin is created, it's automatically selected as the current plugin that Builder works with. You can select another plugin in the plugin list if you need.

## Managing plugin database tables

Tables are managed on the Database tab of Builder. You can create tables, update their structure and delete tables with the visual interface.

Click Add button to open the Create Table tab. Builder automatically generates prefixes for plugin tables. The prefixes are compliant with the [Developer Guidelines](http://octobercms.com/docs/help/developer-guide) for the Marketplace plugins.

Every time when you save changes in a table, Builder shows a popup window with the automatically generated migration PHP code. You can't edit the code in the popup, but you can inspect it or copy to the clipboard. After reviewing the migration, click Save & Apply button. Builder executes the migration immediately and saves the migration file to the plugin's **updates** directory. Afterwards you can find all plugin migrations on the Versions tab of Builder.

> **Note:** Although Builder generates migration files automatically, it can't prevent the data loss in some cases when you significantly change the table structure. In some cases it's possible - for example, when you alter length of a string column. Always check the migration PHP code generated by Builder before applying the migration and consider possible consequences of running the migration in a production database.

Currently Builder doesn't allow to manage table indexes with the visual user interface. Unique column management is not supported yet as well. Please use the Version Management feature to manually create migration files.

Please note that the `enum` data type is not currently supported by the Builder due to limitations in the underlying Doctrine classes.

## Managing models

You can edit models on the Models tab of Builder. Click the Add button, enter the model class name and select a database table from the drop-down list.

The model class name should not contain the namespace. Some examples: Post, Product, Category.

Please note that you cannot delete model files with builder because it would contradict the idea of not deleting or overwriting PHP files with the visual tool. If you need to delete a model, remove its files manually.

## Managing back-end forms

In OctoberCMS forms belong to models. For every model you can create as many back-end forms as you need, but in most cases there is a single form per model.

> **Note:** when you create a form, it's not displayed in OctoberCMS back-end until you create a back-end controller which uses the form. Read about controllers below.

On the Models tab in Builder find a model you want to create a form for. Expand the model if needed, hover the **Forms** section and click the plus sign.

Forms in OctoberCMS are defined with YAML files. The default form file name is **fields.yaml**. In the Form Builder, click a placeholder and select a control from the popup list. After that you can click the control and edit it parameters in Inspector.

Almost all form controls have these common properties:

* Field name - a model field name. It's an autocomplete field in Inspector, which allows you to select column names from the underlying database table. Currently relations are not displayed in the autocomplete hints, this will be implemented later. You can enter any model property manually.
* Label - The control label. You can enter a static text string to the field, or create a new localization string by clicking the plus sign in the input field. Almost all editors in Builder support this feature.
* Comment - the comment text - fixed text or localization string key.
* Span - position of the control on the form - left, right, full or automatic placement.

Most of the properties have descriptive names or have a description in Inspector. If you need more information about control properties please refer to the [Documentation](http://octobercms.com/docs/backend/forms).

You can drag controls in the Form Builder to rearrange them or to move them to/from form tabs.

> **Note:** a form tab should have at least one control, otherwise it will be ignored when Form Builder saves the YAML file.


> **Note:** some form controls, for example the file upload control, require a relation to be created in the model class manually. The relation name should be entered in the Field name property. Please read the [Forms Documentation](http://octobercms.com/docs/backend/forms) for details about specific form controls.

## Managing back-end lists

Similarly to forms, back-end lists in OctoberCMS belong to models.

> **Note:** when you create a list, it's not displayed in OctoberCMS back-end until you create a back-end controller which uses the list. Read about controllers below.

On the Models tab in Builder find a model you want to create a list for. Expand the model if needed, hover the **Lists** section and click the plus sign.

Lists in OctoberCMS are defined with YAML files. The default list file name is **columns.yaml**. The grid in the list editor contains list column definitions. Column property names are self descriptive, although some of them require some explanations. Refer to the [Lists documentation](http://octobercms.com/docs/backend/lists#column-options) for details about each property.

For the Label property you can either enter a static string or create a new localization string.

The Field property column has an autocompletion feature attached. It allows you to select columns from the database table that is bound to the model. At the moment it doesn't show relation properties, but you can still type them in manually.

## Managing plugin permissions

[Plugin permissions](http://octobercms.com/docs/backend/users) define what features and back-end plugin pages a user can access. You can manage permissions on the Permissions tab in Builder. For each permission you should specify a unique permission code, permission tab title and permission label. The tab title and label are displayed in the user management interface on the System page in October back-end.

For the tab title and label you can either enter a static string or create a new localization string.

Later, when you create controllers and menu items, you can select what permissions users should have in order to access or see those objects.

## Managing back-end menus

The [plugin navigation](http://octobercms.com/docs/plugin/registration#navigation-menus) is managed on the Backend Menus tab of the Builder. The user interface allows to create top level menu items and sidebar items.

To create a menu item click the placeholder rectangle and then click the new item to open Inspector. In the inspector you can enter the item label, select icon and assign user permissions. The **code** property is required for referring menu items from the controllers code (for marking menu items active).

> **Note:** when you create menu items for back-end pages which don't exist yet in the plugin, it makes sense to leave the **URL** property empty until you create the plugin controllers. This property supports autocompletion, so you can just select your controller URLs from the drop-down list.

## Managing back-end controllers, forms and lists

Back-end pages in OctoberCMS are provided with back-end controllers. Usually back-end pages contain lists and forms for managing plugin records, although you can create any custom controller.

Please refer to the [back-end forms](http://octobercms.com/docs/backend/forms), [lists](http://octobercms.com/docs/backend/lists) and [reorder controller](http://octobercms.com/docs/backend/reorder) documentation pages for more information about controller behaviors. Currently only List, Form and Reorder Controller behaviors can be configured with the Builder. If your controller contains other behaviors they won't be removed by the Builder, you just won't be able to edit them with the visual interface.

Builder also allows you to create empty controller classes which don't implement any behaviors and customize them manually.

> **Note:** Some behaviors require specific model features to be implemented. For example, the Reorder Controller behavior requires the model to implement Sortable or NestedTree traits. Always refer to the specific behavior documentation for the implementation details.

To create a controller, click the Add button list on the Controllers tab. Enter the controller class name, for example Posts.

If the controller is going to provide back-end lists or forms, select a base model in the drop-down list and select behaviors you want to add. You can also select a top and sidebar menu items that should be active on the controller pages. If needed, choose permissions that users must have to access the controller pages.

> Please note that the settings you enter in the Create Controller popup cannot be changed with Builder. However you can update them manually by editing controller classes.

After creating a controller you can configure its behaviors. Click the controller in list and then click a behavior you want to configure. When Builder creates a controller it tries to apply default configuration to the behaviors, however you might want to change it. Inspector lists displays all supported behavior properties. URL properties (like the list records URLs) are autocomplete fields and populated with URLs of the existing plugin controllers.

## Managing plugin versions

Please read the [Version History](http://octobercms.com/docs/plugin/updates) documentation page to understand how versioning works in OctoberCMS.

Basically there are 3 types of version updates:

1. Updates which change the database structure - migrations. Builder can generate migration files automatically when you make changes in the DB schema on the Database tab.
2. Seeding updates, which populate the database contents.
3. Version updates, which do not update anything in the database but are often used for releasing code changes.

Plugin versions are managed on the Versions tab of the Builder. This tab displays a list of existing plugin versions their status. Applied versions have a green checkbox marker. Pending versions have a grey clock marker.

You can create a new version with clicking the Add button and selecting the update time. The user interface automatically generates scaffold PHP code for the "Migration" and "Seeder" updates. The "Increase the version number" updates won’t contain any PHP code.

For every version you should specify the new version number and description. Builder generates the version number automatically by increasing the last digit in the existing version. You might want to change it if you're releasing a major version update and want to change the first or second digit.

When a version file is saved, Builder doesn't apply it immediately. You should click the "Apply version" button in the toolbar in order to apply the version and execute the update code (if applicable). You can also rollback already applied version updates, change their code and apply again. This allows you to edit database schema updates generated by Builder if you don't like the default code.

> Note that your migration files should provide correct rollback code in the `down` method in order to use the rollback feature.

When you rollback a version, it automatically rolls back all newer versions. When you apply a version, it automatically applies all pending older versions. Please remember that when a user logs into the back-end, October automatically applies all pending updates. Never edit versions on a production server or on a server with multiple back-end users - it could cause unpredictable consequences.

Migrations that contain multiple scripts are not supported. They can't be created or edited with Builder.

## Managing localization

Localization files are managed on the Localization tab of the Builder. When a new plugin is initialized, a single language file is created. This file is created in the default system locale specified in OctoberCMS configuration scripts.

You can create as many language files as you want. Builder UI always displays strings in the OctoberCMS locale, so you might want to update your configuration files to see your plugin in another language.

Please note that although [localization files in OctoberCMS](http://octobercms.com/docs/plugin/localization) are PHP scripts, they are translated to YAML to simplify the editing in the Builder user interface. When language files are saved, they are translated back to PHP again.

Builder tries to keep the user interface synchronized with your default language file. This means that when you save the language file, Builder automatically updates all localized strings in all editors. In some cases you might need to close and open Inspector in order to re-initialize the autocomplete fields.

In many cases you can create new localization strings on-the-fly from the Builder editors - the Form Builder, Menu Builder, etc. The localization input field has the plus icon on the right side. Clicking the plus icon opens a popup window that allows you to enter the localization string key and value. The string key can contain dots to mark the localization file sections. For example - if you add a string with the key `plugin.posts.category` and value "Enter a category name", Builder will create the following structure in the language file:

```
plugin:
    posts:
        category: Enter a category name
```

If you create a new localization string from the Inspector or other editor while you have the default language file tab open in the Builder, it will try to update the tab contents or merge the updated file contents from the server. It's a good idea to keep the default localization file always saved in the Builder to avoid possible content conflicts when you edit localization from another place.

> Protip: In YAML a single quote is escaped with two single quotes (http://yaml.org/spec/current.html#id2534365).

## Displaying plugin records on the front-end pages

Builder provides universal CMS components that you can use for displaying records from your plugins on the front-end website pages. The components provide only basic functionality, for example they don't support a record search feature.

Please read the [CMS documentation](http://octobercms.com/docs/cms/components) to learn more about the CMS components concept.

### Record list component

The Record list component outputs a list of records provided by a plugin's model. The component supports the following optional features: pagination, links to the record details page, using a [model scope](https://octobercms.com/docs/database/model#query-scopes) for the list filtering. The list can be sorted by any column, but the sorting cannot be changed by website visitors - it's set in the component configuration.

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

After configuring the component save and preview the page. Most likely you will want to customize the [default component markup](https://octobercms.com/docs/cms/components#customizing-default-markup) to output more details about each record.

## Record details component

The Record details component loads a model from the database and outputs its details on a page. If the requested record cannot be found, the component outputs the "record not found" message.

Add this component to a CMS page by dragging it to the page code from the component list and click it to configure its properties:

* `Model class` - select a model class you want to use to fetch data from the database.
* `Identifier value` - specify a fixed value or use the **external parameter editor** to enter a name of the URL parameter. If the details page had the URL like "/blog/post/:slug", the identifier value would be ":slug".
* `Key column` - specify a name of the database table column to use for looking up the record. This is an autocomplete field that displays columns from the underlying database table.
* `Display column` - enter a name of the database table column to display on the details page. The value is used in the default component partial, you can customize the component by providing custom markup instead of the default partial.
* `Not found message` - a message to display if the record is not found. Used in the default partial.

After configuring the component save and preview the page. You will likely want to customize the [default component markup](http://octobercms.com/docs/cms/components#customizing-default-markup) to output more details from the loaded model.

## Notes about the autocompletion

Builder updates the Inspector autocompletion fields every time when the underlying data is updated. For example, the “Field name” property of the Form Builder controls is populated with the database table column names. If you update the table structure with Builder, the autocompletion cache updates automatically. However you may need to reopen Inspector so that it can update its editors.

If you edit your plugin files or database structure with an external editor, Builder won’t be able to pick up those changes automatically. You might want to reload the Builder page after you add a database column with an external tool in order to refresh the autocompletion features.

## Editing other plugins

Although Builder allows you to edit plugins created by other authors, remember that you do it at your own risk. Plugins could be updated by their authors, which will eliminate your changes or break the plugin. In many cases, if you make updates to plugins developed by another author, you lose any technical support provided by the author.

## Adding support for a custom FormWidget

To add a custom widget to the Builder plugin, you must first [register a backend form widget](https://octobercms.com/docs/backend/widgets#form-widget-registration) for your plugin.

Once it is registered, define a list of properties within your plugin in the Plugin registration class `boot()` method and register the custom control. For example:

```php
public function boot() {
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

> Note: See the `getStandardProperties()` method in the `rainlab/builder/classes/ControlLibrary.php` file for more examples.

Now, we need the `ControlDesignTimeProvider` class referenced above. Save the following as `classes/ControlDesignTimeProvider.php` within your plugin's directory (replacing `'yourwidgetname'` with what you used in your Plugin registration class `boot()` method).

```php
<?php namespace Acme\Blog\Classes;

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
